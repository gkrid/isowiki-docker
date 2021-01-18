<?php

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();
 
/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_isowikitweaks_table extends DokuWiki_Syntax_Plugin {
 
	public function getType(){ return 'container'; }
	public function getAllowedTypes() { return array('container', 'substition'); }
	public function getSort(){ return 158; }
	public function connectTo($mode) {
		$this->Lexer->addEntryPattern('<table.*?>(?=.*?</table>)',$mode,'plugin_isowikitweaks_table');
	}
	
	public function postConnect() {
		$this->Lexer->addExitPattern('</table>','plugin_isowikitweaks_table');
	}
 
 
	/**
	 * Handle the match
	 */
	public function handle($match, $state, $pos, Doku_Handler $handler) {
		switch ($state) {
			case DOKU_LEXER_ENTER:
				$attrs = $this->element_attributes('table', $match);
				return array($state, $attrs);
 
			case DOKU_LEXER_UNMATCHED : 
				return array($state, $match);
 
			case DOKU_LEXER_EXIT :
				return array($state, '');
 
		}       
		return false;
	}
 

   public function render($mode, Doku_Renderer $renderer, $indata) {
		if($mode == 'xhtml') {
			list($state, $match) = $indata;
			switch ($state) {
			case DOKU_LEXER_ENTER :
				$classes = array();
				
				if (isset($match['border-horizontal']) &&
					$match['border-horizontal'] === '0') {
					$classes[] = 'isowikitweaks-table-no-border-horizontal';
				}
				
				if (isset($match['border-hide-empty']) &&
					$match['border-hide-empty'] === 'true') {
					$classes[] = 'isowikitweaks-table-border-hide-empty';
				}

                if (isset($match['merge-duplicates']) &&
                    $match['merge-duplicates'] === 'true') {
                    $classes[] = 'isowikitweaks-merge-duplicates';
                }
				
				$class = '';
				if (count($classes) > 0) {
					$class = ' class="'.implode(' ', $classes).'"';
				}
				$renderer->doc .= '<div'.$class.'>';

				break;
			  case DOKU_LEXER_UNMATCHED : 
				$renderer->doc .= $renderer->_xmlEntities($match);
				break;
 
			  case DOKU_LEXER_EXIT :
				$renderer->doc .= '</div>';
				break;
			}
			return true;
		}
		return false;
	}
	
	//http://www.bobulous.org.uk/coding/php-xml-regex-3.html
	public function element_attributes($element_name, $xml) {
		if ($xml == false) {
			return false;
		}
		// Grab the string of attributes inside an element tag.
		$found = preg_match('#<'.$element_name.
				'\s+([^>]+(?:"|\'))\s?/?>#',
				$xml, $matches);
		if ($found == 1) {
			$attribute_array = array();
			$attribute_string = $matches[1];
			// Match attribute-name attribute-value pairs.
			$found = preg_match_all(
					'#([^\s=]+)\s*=\s*(\'[^<\']*\'|"[^<"]*")#',
					$attribute_string, $matches, PREG_SET_ORDER);
			if ($found != 0) {
				// Create an associative array that matches attribute
				// names to attribute values.
				foreach ($matches as $attribute) {
					$attribute_array[$attribute[1]] =
							substr($attribute[2], 1, -1);
				}
				return $attribute_array;
			}
		}
		// Attributes either weren't found, or couldn't be extracted
		// by the regular expression.
		return false;
	}
}
 
//Setup VIM: ex: et ts=4 enc=utf-8 :
?>
