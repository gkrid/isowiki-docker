<?php

namespace dokuwiki\plugin\structat\meta;

use dokuwiki\plugin\struct\meta\SearchConfig;
use dokuwiki\plugin\struct\meta\SearchConfigParameters;

/**
 * Manage dynamic parameters for aggregations
 *
 * @package dokuwiki\plugin\struct\meta
 */
class SearchConfigAtParameters extends SearchConfigParameters {

    /** @var string parameter name to pass at */
    public static $PARAM_AT = 'structat';

    /** @var int */
    protected $at = 0;

    public function __construct(SearchConfig $searchConfig) {
        global $INPUT;

        parent::__construct($searchConfig);

        if ($INPUT->has(self::$PARAM_AT)) {
            $this->setAt($INPUT->int(self::$PARAM_AT));
        }
    }

    /**
     * Set the at
     *
     * @param int $at
     */
    public function setAt($at)
    {
        $this->at = $at;
    }

    /**
     * Updates the given config array with the values currently set
     *
     * This should only be called once at the initialization
     *
     * @param array $config
     * @return array
     */
    public function updateConfig($config)
    {
        $config = parent::updateConfig($config);
        if ($this->at) {
            $config['at'] = $this->at;
        }

        return $config;
    }
}
