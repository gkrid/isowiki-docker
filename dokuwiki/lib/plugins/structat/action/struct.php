<?php
/**
 * DokuWiki Plugin structrowcolor (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Szymon Olewniczak <it@rid.pl>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) {
    die();
}

class action_plugin_structat_struct extends DokuWiki_Action_Plugin
{

    /**
     * Registers a callback function for a given event
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     *
     * @return void
     */
    public function register(Doku_Event_Handler $controller)
    {
        $controller->register_hook('PLUGIN_STRUCT_CONFIGPARSER_UNKNOWNKEY', 'BEFORE', $this, 'handle_plugin_struct_configparser_unknownkey');
    }

    /**
     * [Custom event handler which performs action]
     *
     * Called for event:
     *
     * @param Doku_Event $event  event object by reference
     *
     * @return void
     */
    public function handle_plugin_struct_configparser_unknownkey(Doku_Event $event)
    {
        $data = $event->data;
        $key = $data['key'];
        if ($key != 'at') return;

        $event->preventDefault();
        $event->stopPropagation();

        $val = $data['val'];
        switch ($key) {
            case 'at':
                $val = trim($val);
                break;
        }
        $data['config'][$key] = $val;
    }
}

