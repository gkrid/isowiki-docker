<?php
/**
 * AJAX call handler for ACL plugin
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

/**
 * Register handler
 */
class action_plugin_acl extends DokuWiki_Action_Plugin
{

    /**
     * Registers a callback function for a given event
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     * @return void
     */
    public function register(Doku_Event_Handler $controller)
    {

        $controller->register_hook('AJAX_CALL_UNKNOWN', 'BEFORE', $this, 'handleAjaxCallAcl');
    }

    /**
     * AJAX call handler for ACL plugin
     *
     * @param Doku_Event $event  event object by reference
     * @param mixed $param  empty
     * @return void
     */
    public function handleAjaxCallAcl(Doku_Event $event, $param)
    {
        if ($event->data !== 'plugin_acl') {
            return;
        }
        $event->stopPropagation();
        $event->preventDefault();

        global $ID;
        global $INPUT;

        /** @var $acl admin_plugin_acl */
        $acl = plugin_load('admin', 'acl');
        if (!$acl->isAccessibleByCurrentUser()) {
            echo 'for admins only';
            return;
        }
        if (!checkSecurityToken()) {
            echo 'CRSF Attack';
            return;
        }

        $ID = getID();
        $acl->handle();

        $ajax = $INPUT->str('ajax');
        header('Content-Type: text/html; charset=utf-8');

        if ($ajax == 'info') {
            $acl->printInfo();
        } elseif ($ajax == 'tree') {
            $ns = $INPUT->str('ns');
            if ($ns == '*') {
                $ns = '';
            }
            $ns = cleanID($ns);
            $lvl = count(explode(':', $ns));
            $ns = utf8_encodeFN(str_replace(':', '/', $ns));

            $data = $acl->makeTree($ns, $ns);

            foreach (array_keys($data) as $item) {
                $data[$item]['level'] = $lvl + 1;
            }
            echo html_buildlist(
                $data,
                'acl',
                array($acl, 'makeTreeItem'),
                array($acl, 'makeListItem')
            );
        }
    }
}
