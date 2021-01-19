<?php
/**
 * DokuWiki Plugin isowikitweaks (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  S <a@a.pl>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) {
    die();
}

class action_plugin_isowikitweaks_pagetitle extends DokuWiki_Action_Plugin
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
        $controller->register_hook('TPL_METAHEADER_OUTPUT', 'BEFORE', $this, 'handle_tpl_metaheader_output');
   
    }

    /**
     * [Custom event handler which performs action]
     *
     * Called for event:
     *
     * @param Doku_Event $event  event object by reference
     * @param mixed      $param  [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     *
     * @return void
     */
    public function handle_tpl_metaheader_output(Doku_Event $event, $param)
    {
        if ($this->getConf('hide_pagetitle')) {
            $event->data['style'][] = array('type'  => 'text/css',
                                            '_data' => '#dokuwiki__header h1 a span {display: none !important;}');
        }
    }

}

