<?php
/**
 * DokuWiki Plugin structrowcolor (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Szymon Olewniczak <it@rid.pl>
 */


use dokuwiki\plugin\struct\meta\SearchConfig;
use dokuwiki\plugin\struct\meta\Value;

// must be run within Dokuwiki
if (!defined('DOKU_INC')) {
    die();
}

class action_plugin_structrowcolor_struct extends DokuWiki_Action_Plugin
{

    protected $row_colors = [];

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
        $controller->register_hook('PLUGIN_STRUCT_AGGREGATIONTABLE_RENDERRESULTROW', 'BEFORE', $this, 'handle_plugin_struct_aggregationtable_renderresultrow_before');
        $controller->register_hook('PLUGIN_STRUCT_AGGREGATIONTABLE_RENDERRESULTROW', 'AFTER', $this, 'handle_plugin_struct_aggregationtable_renderresultrow_after');

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
        if ($key != 'rowcolor') return;

        $event->preventDefault();
        $event->stopPropagation();

        $val = trim($data['val']);
        $data['config'][$key] = $val;
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
    public function handle_plugin_struct_aggregationtable_renderresultrow_before(Doku_Event $event)
    {
        $mode = $event->data['mode'];
        $renderer = $event->data['renderer'];
        $data = $event->data['data'];

        if ($mode != 'xhtml') return;

        $rowcolor = $data['rowcolor'];
        if (!$rowcolor) return;

        /** @var SearchConfig $searchConfig */
        $searchConfig = $event->data['searchConfig'];
        $searchConfig_hash = spl_object_hash($searchConfig);
        if (!isset($this->row_colors[$searchConfig_hash])) {
            $this->row_colors[$searchConfig_hash] = [];

            $rowcolor_column = $searchConfig->findColumn($rowcolor);

            $searchConfig->addColumn('*');
            $result = $searchConfig->execute();
            foreach ($result as $rownum => $row) {
                /** @var Value $value */
                foreach ($row as $colnum => $value) {
                    if ($value->getColumn() === $rowcolor_column) {
                        $this->row_colors[$searchConfig_hash][$rownum] = $value->getRawValue();
                        break;
                    }
                }
            }

        }

        // save row start position
        $event->data['rowstart']= mb_strlen($renderer->doc);
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
    public function handle_plugin_struct_aggregationtable_renderresultrow_after(Doku_Event $event)
    {
        $mode = $event->data['mode'];
        if ($mode != 'xhtml') return;

        $renderer = $event->data['renderer'];
        /** @var SearchConfig $searchConfig */
        $searchConfig = $event->data['searchConfig'];
        $data = $event->data['data'];

        $rownum  = $event->data['rownum'];

        $rowstart = $event->data['rowstart'];
        $rowcolor = $data['rowcolor'];

        if (!$rowcolor) return;
        if (!$rowstart) return;

        $searchConfig_hash = spl_object_hash($searchConfig);
        $bgcolor = $this->row_colors[$searchConfig_hash][$rownum];

        if (!$bgcolor) return;

        $rest = mb_substr($renderer->doc, 0,  $rowstart);
        $row = mb_substr($renderer->doc, $rowstart);
        $row = ltrim($row);
        //check if we processing row
        if (mb_substr($row, 0, 3) != '<tr') return;

        $tr_tag = mb_substr($row, 0, 3);
        $tr_rest = mb_substr($row, 3);

        $renderer->doc = $rest . $tr_tag . ' style="background-color: '.$bgcolor.'" ' . $tr_rest;
    }

}

