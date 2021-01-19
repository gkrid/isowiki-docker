<?php
/**
 * DokuWiki Plugin telleveryone (Admin Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Szymon Olewniczak <it@rid.pl>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) {
    die();
}

class admin_plugin_telleveryone extends DokuWiki_Admin_Plugin
{

    /**
     * @return int sort number in admin menu
     */
    public function getMenuSort()
    {
        return 1;
    }

    /**
     * @return bool true if only access for superuser, false is for superusers and moderators
     */
    public function forAdminOnly()
    {
        return false;
    }

    /**
     * Should carry out any processing required by the plugin.
     */
    public function handle()
    {
        global $ID, $INPUT, $INFO;

        if (!$INPUT->param('action')) return;
        if (!$INPUT->arr('log')) return;
        if (!checkSecurityToken()) return;

        $log = $INPUT->arr('log');

        try {
            /** @var \helper_plugin_telleveryone_db $db_helper */
            $db_helper = plugin_load('helper', 'telleveryone_db');
            $sqlite = $db_helper->getDB();
        } catch (Exception $e) {
            msg($e->getMessage(), -1);
            return;
        }

        switch ($INPUT->str('action')) {
            case 'add':
                if (!isset($log['message'])) return;
                $message = $log['message'];
                $message_html = p_render('xhtml',p_get_instructions($message), $ignore);

                $sqlite->storeEntry('log', [
                    'timestamp' => date('c'),
                    'user' => $INFO['client'],
                    'message' => $message,
                    'message_html' => $message_html
                ]);
                break;
            case 'delete':
                if (!isset($log['id'])) return;
                $sqlite->query('DELETE FROM log WHERE id=?', $log['id']);
                break;
            case 'update':
                if (!isset($log['id']) || !isset($log['message'])) return;
                $message = $log['message'];
                $message_html = p_render('xhtml',p_get_instructions($message), $ignore);

                $sqlite->query('UPDATE log SET message=?, message_html=? WHERE id=?', $message, $message_html, $log['id']);
                break;
        }
        send_redirect(wl($ID, array('do' => 'admin', 'page' => 'telleveryone'), true, '&'));
    }

    /**
     * Render HTML output, e.g. helpful text and a form
     */
    public function html()
    {
        global $ID, $INPUT;

        ptln('<h1>' . $this->getLang('menu') . '</h1>');

        try {
            /** @var \helper_plugin_telleveryone_db $db_helper */
            $db_helper = plugin_load('helper', 'telleveryone_db');
            $sqlite = $db_helper->getDB();
        } catch (Exception $e) {
            msg($e->getMessage(), -1);
            return;
        }

        $res = $sqlite->query("SELECT value FROM config WHERE key='token'");
        $token = $sqlite->res2single($res);

        $remote_url = DOKU_URL;
        ptln('<p>API URI: <code>' . $remote_url . '</code><br>Token: <code>' . $token . '</code></p>');

        $res = $sqlite->query('SELECT id, timestamp, message, message_html FROM log ORDER BY timestamp DESC');
        $logs = $sqlite->res2arr($res);
        ptln('<ul>');
        foreach ($logs as $log) {
            $id = $log['id'];
            $timestamp = strtotime($log['timestamp']);
            $message = $log['message'];
            $message_html = $log['message_html'];

            ptln('<li class="level1"><div class="li">');

            if ($id == $INPUT->int('edit')) {
                ptln('<div>' . dformat($timestamp) . '</div>');
                ptln('<hr>');
                $form = $this->createForm('btn_update', $message);
                $form->setHiddenField('action', 'update');
                $form->setHiddenField('log[id]', $id);
                ptln($form->toHTML());
            } else {
                $edit_link = wl(
                    $ID, [
                        'do' => 'admin',
                        'page' => 'telleveryone',
                        'edit' => $id
                    ]
                );

                $edit_link = '<a href="' . $edit_link . '">' . $this->getLang('btn_edit') . '</a>';

                $delete_link = wl(
                    $ID, [
                        'do' => 'admin',
                        'page' => 'telleveryone',
                        'action' => 'delete',
                        'sectok' => getSecurityToken(),
                        'log[id]' => $id
                    ]
                );

                $delete_link = '<a href="' . $delete_link . '" class="plugin__telleveryone_delete">' . $this->getLang('btn_delete') . '</a>';
                ptln('<div>' . dformat($timestamp) . ' ' . $edit_link . ' ' . $delete_link . '</div>');
                ptln('<hr>');
                ptln('<div>' . $message_html . '</div>');
            }

            ptln('</div></li>');
        }
        ptln('</ul>');

        if (!$INPUT->param('edit')) {
            $form = $this->createForm();
            $form->setHiddenField('action', 'add');
            ptln($form->toHTML());
        }
    }

    protected function createForm($btn_label='btn_add', $textareaValue='') {
        global $ID;

        $form = new dokuwiki\Form\Form();
        $form->setHiddenField('id', '');
        $form->addHTML('<div class="toolbar group">');
        $form->addHTML('<div id="tool__bar" class="tool__bar" role="toolbar"></div>');
        $form->addHTML('</div>');

        $textarea = new \dokuwiki\Form\TextareaElement('log[message]', '');
        $textarea->id('wiki__text');
        $textarea->val($textareaValue);
        $textarea->attr('class', "edit");
        $textarea->attr('cols', 80);
        $textarea->attr('rows', 10);


        $form->addElement($textarea);


        $form->addHTML('<div id="wiki__editbar">');
        $form->addButton('', $this->getLang($btn_label));
        $cancel_link = wl(
            $ID, [
                'do' => 'admin',
                'page' => 'telleveryone',
            ]
        );

        $cancel_link = '<a href="' . $cancel_link . '" style="margin-left:1em" id="plugin__telleveryone_cancel">' . $this->getLang('btn_cancel') . '</a>';
        $form->addHTML($cancel_link);
        $form->addHTML('</div><br>');
        return $form;
    }
}

