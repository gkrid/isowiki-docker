<?php
/**
 * DokuWiki Plugin metro4tiles (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Szymon Olewniczak <it@rid.pl>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) {
    die();
}

class syntax_plugin_metro4tiles extends DokuWiki_Syntax_Plugin
{
    /**
     * @return string Syntax mode type
     */
    public function getType()
    {
        return 'disabled';
    }

    /**
     * @return string Paragraph type
     */
    public function getPType()
    {
        return 'normal';
    }

    /**
     * @return int Sort order - Low numbers go before high numbers
     */
    public function getSort()
    {
        return 100;
    }

    /**
     * Connect lookup pattern to lexer.
     *
     * @param string $mode Parser mode
     */
    public function connectTo($mode)
    {
        $this->Lexer->addSpecialPattern('<metro.*?/>', $mode, 'plugin_metro4tiles');
    }

    /**
     * Handle matches of the metro4tiles syntax
     *
     * @param string       $match   The match of the syntax
     * @param int          $state   The state of the handler
     * @param int          $pos     The position in the document
     * @param Doku_Handler $handler The handler
     *
     * @return array Data for the renderer
     */
    public function handle($match, $state, $pos, Doku_Handler $handler)
    {
        $data = array();

        $metro = new SimpleXMLElement($match);

        return current($metro->attributes());
    }

    /**
     * Render xhtml output or metadata
     *
     * @param string        $mode     Renderer mode (supported modes: xhtml)
     * @param Doku_Renderer $renderer The renderer
     * @param array         $data     The data from the handler() function
     *
     * @return bool If rendering was successful.
     */
    public function render($mode, Doku_Renderer $renderer, $data)
    {
        global $conf;

        if ($mode !== 'xhtml') {
            return false;
        }

        if (!isset($data['name'])) {
            msg('Provide name of tile set.', -1);
            return false;
        }
        $name = $data['name'];
        $path = $conf['metadir'] . "/metro4tiles/cache/$name.html";
        if (!file_exists($path)) {
            msg("Tiles set: $name doesn't exists." , -1);
            return false;
        }
        $src = DOKU_URL . 'lib/plugins/metro4tiles/iframe.php?name=' . urlencode($name);

        $renderer->doc .= '<iframe  src="'.$src.'"
                                    width="100%"
                                    style="border:0"
                                    class="metro4tiles__iframe"></iframe>';

        return true;
    }
}

