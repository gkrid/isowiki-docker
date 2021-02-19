<?php
/**
 * DokuWiki Plugin watchcycle (Admin Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Szymon Olewniczak <dokuwiki@cosmocode.de>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class admin_plugin_metro4tiles extends DokuWiki_Admin_Plugin {

    /**
     * @return int sort number in admin menu
     */
    public function getMenuSort() {
        return 1;
    }

    /**
     * @return bool true if only access for superuser, false is for superusers and moderators
     */
    public function forAdminOnly() {
        return false;
    }

    /**
     * Should carry out any processing required by the plugin.
     */
    public function handle() {
    }

    protected function getTplPath($name, $cache=false) {
        global $conf;
        $name = str_replace('/', '', $name);
        $path = $conf['metadir'] . "/metro4tiles";
        if (!is_dir($path)) {
            mkdir($path);
            mkdir($path . '/cache');
        }

        if ($cache) {
            return array($path. "/$name.html", $path . "/cache/$name.html");
        }

        return $path . "/$name.html";
    }

    protected function parseTpl($content) {
        //replace links
        $content = preg_replace_callback('/\[\[(.*?)\]\]/', function ($matches) {
            return wl($matches[1]);
        }, $content);

        $content = preg_replace_callback('/\{\{(.*?)\}\}/', function ($matches) {
            $id = $matches[1];
            return ml($id);
        }, $content);

        return $content;
    }

    protected function process_input() {
        /* @var Input */
        global $INPUT;

        //load file from disk
        if ($INPUT->has('name') && !$INPUT->has('content')) {
            $name = $INPUT->str('name');
            $path = $this->getTplPath($name);
            if (file_exists($path)) {
                $INPUT->set('content', file_get_contents($path));
            } else {
                msg("File $name doesn't exists.", -1);
            }
        } elseif($INPUT->has('name') && $INPUT->has('content')) { //save content
            $name = $INPUT->str('name');

            if ($name == '') {
                msg($this->getLang('admin msg name validation empty'), -1);
                return;
            } elseif (!preg_match('/^\w*$/', $name)) {
                msg($this->getLang('admin msg name validation error'), -1);
                return;
            }

            $content = $INPUT->str('content');
            list($path, $path_cache) = $this->getTplPath($name, true);

            if ($content == '') {
                unlink($path);
                unlink($path_cache);

                $msg = sprintf($this->getLang('admin msg removed'), $name);
                msg($msg, 1);
            } else {
                file_put_contents($path, $content);
                $cache = $this->parseTpl($content);
                file_put_contents($path_cache, $cache);

                $msg = sprintf($this->getLang('admin msg saved'), $name);
                msg($msg, 1);
            }
        }
    }

    /**
     * Render HTML output, e.g. helpful text and a form
     */
    public function html() {

        $this->process_input();

        $form = new \dokuwiki\Form\Form();
        $form->addElement(new \dokuwiki\Form\FieldsetOpenElement($this->getLang("admin legend add_edit")));
        $name = new \dokuwiki\Form\InputElement('text', 'name',
            $this->getLang('admin label name'));
        $form->addElement($name);

        $textarea = new \dokuwiki\Form\TextareaElement('content', '');
        $textarea->attr('rows', '15');
        $textarea->attr('cols', '80');
        $textarea->id('metro4tiles__editor');
        $form->addElement($textarea);

        $form->addButton('', $this->getLang('admin button save'));
        $form->addElement(new \dokuwiki\Form\FieldsetCloseElement());
        ptln($form->toHTML());
    }

    function getTOC() {
        global $conf;
        global $ID;

        $toc = array();
        $path = $conf['metadir'].'/metro4tiles/*.html';
        $tiles = glob($path);

        foreach($tiles as $filename) {
            $name = basename($filename, '.html');
            $toc[] = array(
                'link'  => wl($ID, array('do'=> 'admin', 'page'=> 'metro4tiles', 'name' => $name, 'sectok'=> getSecurityToken())),
                'title' => $name,
                'level' => 1,
                'type'  => 'ul',
            );
        }

        return $toc;
    }
}

// vim:ts=4:sw=4:et: