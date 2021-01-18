<?php

use dokuwiki\plugin\struct\meta\Assignments;
use dokuwiki\plugin\struct\meta\SearchConfig;
use dokuwiki\plugin\struct\meta\SearchConfigParameters;
use dokuwiki\plugin\structat\meta\SearchConfigAtParameters;

/**
 * Handle caching of pages containing struct aggregations
 */
class action_plugin_structat_cache extends DokuWiki_Action_Plugin
{

    /**
     * Registers a callback function for a given event
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     * @return void
     */
    public function register(Doku_Event_Handler $controller)
    {
        $controller->register_hook('PARSER_CACHE_USE', 'BEFORE', $this, 'handleCacheAggregation', null, 1);
        $controller->register_hook('PARSER_CACHE_USE', 'AFTER', $this, 'handleCacheDynamic', null, 1);
    }

    /**
     * For pages containing an aggregation, add the last modified date of the database itself
     * to the cache dependencies
     *
     * @param Doku_Event $event event object by reference
     * @param mixed $param [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     * @return bool
     */
    public function handleCacheAggregation(Doku_Event $event, $param)
    {
        global $INPUT;

        /** @var \cache_parser $cache */
        $cache = $event->data;
        if ($cache->mode != 'xhtml') return true;
        if (!$cache->page) return true; // not a page cache

        $meta = p_get_metadata($cache->page, 'plugin struct');
        if (isset($meta['hasaggregation'])) {
            // dynamic renders should never overwrite the default page cache
            // we need this in additon to handle_cache_dynamic() below because we can only
            // influence if a cache is used, not that it will be written
            if (
                $INPUT->has(SearchConfigAtParameters::$PARAM_AT)
            ) {
                // check if we have an user
                $user_key = '';
                if ($meta['hasaggregation'] & SearchConfig::$CACHE_USER) {
                    //remove user part
                    $user_key = ';' . $INPUT->server->str('REMOTE_USER');
                    $cache->key = substr($cache->key, 0, -strlen($user_key));
                }
                // not dynamic yet
                if (substr($cache->key, -strlen('dynamic')) != 'dynamic') {
                    $cache->key .= 'dynamic';
                }
                $cache->key .= $user_key;
            }

            // rebuild cachename
            $cache->cache = getCacheName($cache->key, $cache->ext);
        }

        return true;
    }

    /**
     * Disable cache when dymanic parameters are present
     *
     * @param Doku_Event $event event object by reference
     * @param mixed $param [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     * @return bool
     */
    public function handleCacheDynamic(Doku_Event $event, $param)
    {
        /** @var \cache_parser $cache */
        $cache = $event->data;
        if ($cache->mode != 'xhtml') return true;
        if (!$cache->page) return true; // not a page cache
        global $INPUT;

        // disable cache use when one of these parameters is present
        foreach (
            array(
                    SearchConfigAtParameters::$PARAM_AT
                ) as $key
        ) {
            if ($INPUT->has($key)) {
                $event->result = false;
                return true;
            }
        }

        return true;
    }
}
