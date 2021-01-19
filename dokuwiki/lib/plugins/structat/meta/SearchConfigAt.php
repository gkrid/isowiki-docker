<?php

namespace dokuwiki\plugin\structat\meta;

use dokuwiki\plugin\struct\meta\Column;
use dokuwiki\plugin\struct\meta\QueryBuilder;
use dokuwiki\plugin\struct\meta\SearchConfig;
use dokuwiki\plugin\struct\meta\StructException;

/**
 * Class SearchConfig
 *
 * The same as @see SearchConfig but supports at parameter
 *
 * @package dokuwiki\plugin\structat\meta
 */
class SearchConfigAt extends SearchConfig
{
    /** @var  int show rows at this timestamp */
    protected $at = 0;

    /**
     * SearchConfig constructor.
     * @param array $config The parsed configuration for this search
     */
    public function __construct($config)
    {
        parent::__construct($config);
        // apply dynamic paramters
        $this->dynamicParameters = new SearchConfigAtParameters($this);
        $config = $this->dynamicParameters->updateConfig($config);

        if (!empty($config['at'])) {
            $this->setAt($config['at']);
        }

        $this->config = $config;
    }

    /**
     * Set the at parameter
     */
    public function setAt($at)
    {
        $this->at = $at;
    }

    /**
     * Transform the set search parameters into a statement
     *
     * @return array ($sql, $opts) The SQL and parameters to execute
     */
    public function getSQL()
    {
        if(!$this->columns) throw new StructException('nocolname');

        $QB = new QueryBuilder();

        // basic tables
        $first_table = '';
        foreach($this->schemas as $schema) {
            $datatable = 'data_' . $schema->getTable();
            if($first_table) {
                // follow up tables
                $QB->addLeftJoin($first_table, $datatable, $datatable, "$first_table.pid = $datatable.pid");
            } else {
                // first table
                $QB->addTable($datatable);

                // add conditional page clauses if pid has a value
                $subAnd = $QB->filters()->whereSubAnd();
                $subAnd->whereAnd("$datatable.pid = ''");
                $subOr = $subAnd->whereSubOr();
                $subOr->whereAnd("GETACCESSLEVEL($datatable.pid) > 0");
                $subOr->whereAnd("PAGEEXISTS($datatable.pid) = 1");
                $subOr->whereAnd('(ASSIGNED = 1 OR ASSIGNED IS NULL)');

                // add conditional schema assignment check
                $QB->addLeftJoin(
                    $datatable,
                    'schema_assignments',
                    '',
                    "$datatable.pid != ''
                    AND $datatable.pid = schema_assignments.pid
                    AND schema_assignments.tbl = '{$schema->getTable()}'"
                );

                $QB->addSelectColumn($datatable, 'rid');
                $QB->addSelectColumn($datatable, 'pid', 'PID');
                $QB->addSelectColumn($datatable, 'rev');
                $QB->addSelectColumn('schema_assignments', 'assigned', 'ASSIGNED');
                $QB->addGroupByColumn($datatable, 'pid');
                $QB->addGroupByColumn($datatable, 'rid');

                $first_table = $datatable;
            }
            if ($this->at) {
                $QB->filters()->whereAnd("$datatable.rev =
                (SELECT MAX(SUB.rev) FROM $datatable SUB
                    WHERE SUB.pid=$datatable.pid AND SUB.rev <= '$this->at')");
            } else {
                $QB->filters()->whereAnd("$datatable.latest = 1");
            }
        }

        // columns to select, handling multis
        $sep = self::CONCAT_SEPARATOR;
        $n   = 0;
        foreach($this->columns as $col) {
            $CN = 'C' . $n++;

            if($col->isMulti()) {
                $datatable  = "data_{$col->getTable()}";
                $multitable = "multi_{$col->getTable()}";
                $MN         = $QB->generateTableAlias('M');

                $QB->addLeftJoin(
                    $datatable,
                    $multitable,
                    $MN,
                    "$datatable.pid = $MN.pid AND $datatable.rid = $MN.rid AND
                     $datatable.rev = $MN.rev AND
                     $MN.colref = {$col->getColref()}"
                );

                $col->getType()->select($QB, $MN, 'value', $CN);
                $sel = $QB->getSelectStatement($CN);
                $QB->addSelectStatement("GROUP_CONCAT($sel, '$sep')", $CN);
            } else {
                $col->getType()->select($QB, 'data_' . $col->getTable(), $col->getColName(), $CN);
                $QB->addGroupByStatement($CN);
            }
        }

        // where clauses
        if(!empty($this->filter)) {
            $userWHERE = $QB->filters()->where('AND');
        }
        foreach($this->filter as $filter) {
            /** @var Column $col */
            list($col, $value, $comp, $op) = $filter;

            $datatable  = "data_{$col->getTable()}";
            $multitable = "multi_{$col->getTable()}";

            /** @var $col Column */
            if($col->isMulti()) {
                $MN = $QB->generateTableAlias('MN');

                $QB->addLeftJoin(
                    $datatable,
                    $multitable,
                    $MN,
                    "$datatable.pid = $MN.pid AND $datatable.rid = $MN.rid AND
                     $datatable.rev = $MN.rev AND
                     $MN.colref = {$col->getColref()}"
                );
                $coltbl = $MN;
                $colnam = 'value';
            } else {
                $coltbl = $datatable;
                $colnam = $col->getColName();
            }

            $col->getType()->filter($userWHERE, $coltbl, $colnam, $comp, $value, $op); // type based filter
        }

        // sorting - we always sort by the single val column
        foreach($this->sortby as $sort) {
            list($col, $asc, $nc) = $sort;
            /** @var $col Column */
            $colname = $col->getColName(false);
            if($nc) $colname .= ' COLLATE NOCASE';
            $col->getType()->sort($QB, 'data_' . $col->getTable(), $colname, $asc ? 'ASC' : 'DESC');
        }

        return $QB->getSQL();
    }
}
