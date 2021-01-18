<?php

/**
 * DokuWiki Plugin struct (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr, Michael Große <dokuwiki@cosmocode.de>
 */

use dokuwiki\plugin\struct\meta\AggregationTable;
use dokuwiki\plugin\struct\meta\ConfigParser;
use dokuwiki\plugin\struct\meta\SearchConfig;
use dokuwiki\plugin\struct\meta\StructException;
use dokuwiki\plugin\struct\meta\Value;

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

class syntax_plugin_structgroupby_one extends syntax_plugin_struct_table
{
    /**
     * Connect lookup pattern to lexer.
     *
     * @param string $mode Parser mode
     */
    public function connectTo($mode)
    {
        $this->Lexer->addSpecialPattern('----+ *struct one *-+\n.*?\n----+', $mode, 'plugin_structgroupby_one');
    }

    /**
     * Render xhtml output or metadata
     *
     * @param string $mode Renderer mode (supported modes: xhtml)
     * @param Doku_Renderer $renderer The renderer
     * @param array $data The data from the handler() function
     * @return bool If rendering was successful.
     */
    public function render($mode, Doku_Renderer $renderer, $data)
    {
        if (!$data) return false;
        global $INFO;
        global $conf;

        if (count($data['cols']) > 1) {
            $renderer->cdata('The syntax accepts only one column.');
            return true;
        }

        try {
            foreach ($data['cols'] as &$col) {
                $col = trim($col);
                $match = 'SUM(';
                if (substr($col, 0, strlen($match)) == $match) {
                    $col = substr($col, strlen($match), -1);
                    $sum_col = $col;
                }
            }

            if (!$sum_col) {
                $renderer->cdata('No aggregation function provided or incorrect function name.');
                return true;
            }

            $search = new SearchConfig($data);
            $search->setLimit(0);
            $search->setOffset(0);

            $sum = 0;
            $result = $search->execute();
            foreach ($result as $rownum => $row) {
                /** @var Value $value */
                foreach ($row as $colnum => $value) {
                    // summarize
                    if ($colnum == $sum_col && is_numeric($value->getValue())) {
                        $sum += $value->getValue();
                    }
                }
            }
            $renderer->cdata($sum);

            if ($mode == 'metadata') {
                /** @var Doku_Renderer_metadata $renderer */
                $renderer->meta['plugin']['struct']['hasaggregation'] = $search->getCacheFlag();
            }
        } catch (StructException $e) {
            msg($e->getMessage(), -1, $e->getLine(), $e->getFile());
            if ($conf['allowdebug']) msg('<pre>' . hsc($e->getTraceAsString()) . '</pre>', -1);
        }

        return true;
    }
}
