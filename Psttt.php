<?php
/**
*    Psttt!
*    What is this? check this http://www.codeassembly.com/Psttt!-I-am-a-different-php-templating-system
*    Full documentation http://www.codeassembly.com/Psttt!-full-documentation
*    Copyright (C) 2010 Ziadin Givan www.CodeAssembly.com  
*
*    This program is free software: you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation, either version 3 of the License, or
*    (at your option) any later version.
*
*    This program is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with this program.  If not, see http://www.gnu.org/licenses/
*/

//define('PSTTT_DEBUG', true); uncomment to always enable debugging 

define('PSTTT_TYPE_LOAD',1);
define('PSTTT_TYPE_SAVE',2);
define('PSTTT_TYPE_SELECTOR',3);
define('PSTTT_TYPE_SELECTOR_STRING',4);
define('PSTTT_TYPE_SELECTOR_PHP',5);
define('PSTTT_TYPE_SELECTOR_VARIABLE',6);
define('PSTTT_TYPE_SELECTOR_FROM',7);
define('PSTTT_CSS_XPATH_TRANSFORM',8);
define('PSTTT_IMPORT_FILE_NOT_EXIST', 9);

define('PSTTT_DEBUG_SHOW_XPATH', false);
define('PSTTT_DEBUG_JQUERY', 'http://code.jquery.com/jquery-1.4.2.min.js');



class Psttt
{
	var $template;
	var $extension = 'pst';
	var $debug = false;
	var $debug_log;
	var $_modifiers = array('outerHTML','before','after','deleteAllButFirst','delete','if_exists','hide');

	function debug_type_to_string($type)
	{

	}

	function Psttt()
	{
		if (PSTTT_DEBUG)
		{
			$this->debug = true;
		}
		
		$this->document = new DomDocument();
	}

	function debug($type,$message)
	{
		if ($this->debug)
		{
			$this->debug_log[][$type]= $message;
		}
	}
	
	function add_debug_html_line($command, $parameters, $break = '<br/>')
	{
		$this->debug_html .= "<span>&nbsp;<b>$command</b> $parameters</span>$break";
	}
	
	function debug_log_to_html()
	{
		foreach ($this->debug_log as $line)
		{
			$type = key($line);
			$message = $line[$type];
			switch ($type)
			{
				case PSTTT_TYPE_LOAD:
					$this->add_debug_html_line('LOAD',$message);
					break;
				case PSTTT_TYPE_SAVE:
					$this->add_debug_html_line('SAVE',$message);
					break;
				case PSTTT_TYPE_SELECTOR:
					$this->add_debug_html_line('SELECTOR',
					"<a href='#' 
					onclick=\"return psttt_selector_click('$message')\" 
					onmouseover=\"return psttt_selector_over('$message')\"
					onmouseout=\"return psttt_selector_out('$message')\">
					$message</a>", '');
					break;
				case PSTTT_TYPE_SELECTOR_STRING:
					$this->add_debug_html_line('INJECT STRING',$message);
					break;
				case PSTTT_TYPE_SELECTOR_PHP:
					$this->add_debug_html_line('INJECT PHP',htmlentities($message));
					break;
				case PSTTT_TYPE_SELECTOR_VARIABLE:
					$this->add_debug_html_line('INJECT VARIABLE',$message);
					break;
				case PSTTT_TYPE_SELECTOR_FROM:
					$this->add_debug_html_line('EXTERNAL HTML',$message);
					break;
				case PSTTT_CSS_XPATH_TRANSFORM:
					if (PSTTT_DEBUG_SHOW_XPATH) 
					$this->add_debug_html_line('RESULTED XPATH',
					"<a href='#' 
					onclick=\"return psttt_selector_click('$message')\" 
					onmouseover=\"return psttt_selector_over('$message')\"
					onmouseout=\"return psttt_selector_out('$message')\">
					$message</a>");
					break;
				default:
					$this->add_debug_html_line('',$message);
					break; 
			}
			
		}
	}


	function load_template_file($template_file)
	{
		$this->debug(PSTTT_TYPE_LOAD, $this->template_path . $template_file);
		$this->template = @file_get_contents($this->template_path . $template_file);
		if (!$this->template) return false;
		//echo '<pre>' . htmlentities($this->template) .'</pre>';

		/*
		 * imports
		 *
		 * */
		$found_imports = true;
		//expand imports
		while ($found_imports)
		{
			$found_imports = preg_match_all("/import\(([^\&%'`\@{}~!#\(\)&\^\+,=\[\]]*?\.$this->extension)\)/", $this->template, $imports);
			for ($i=0;$i<count($imports[0]);$i++)
			{
				if (file_exists($this->template_path . $imports[1][$i]))
				{
					$this->template = str_replace($imports[0][$i], file_get_contents($this->template_path . $imports[1][$i]), $this->template);
				} else
				{
					$this->template = str_replace($imports[0][$i], '', $this->template);
					$this->debug(PSTTT_IMPORT_FILE_NOT_EXIST, $this->template_path . $imports[1][$i]);
				}
			}
		}
		
		/**
		 * placeholders
		 *
		 */
		preg_match_all("/(?<![\"'])\/\*.*?\*\/|\s*(?<![\"'])\/\/[^\n]*/s", $this->template, $comments);
		preg_match_all("/(?<![\"'])<\?php(.*?)\?>/s", $this->template, $php_code);
		preg_match_all("/[\"'][^\"'\\\r\n]*(?:\\.[^\"'\\\r\n]*)*[\"']/s", $this->template, $strings);
		//preg_match_all("/([\"'])[^\\\\]*?(\\\\.[^\\\\]*?)*?\\1/s", $str, $matches);

		$strings[0] = array_values( array_unique($strings[0]) );

		for ($i=0;$i<count($strings[0]);$i++)
		{
			$patterns_strings[] = "/".preg_quote($strings[0][$i], '/')."/";
			//  $patterns[]	= preg_quote($matches[0][$i], '/');
			$placeholders_strings[]="replace_string-$i";
			// double backslashes must be escaped if we want to use them in the replacement argument
			$strings[0][$i] = str_replace('\\\\', '\\\\\\\\', $strings[0][$i]);
		}

		$comments[0] = array_values( array_unique($comments[0]) );

		$placeholders_comments = array();
		for ($i=0;$i<count($comments[0]);$i++)
		{
			$patterns_comments[] = "/".preg_quote($comments[0][$i], '/')."/";
			$placeholders_comments[]="\nreplacecomments-$i\n";
			// double backslashes must be escaped if we want to use them in the replacement argument
			$comments[0][$i] =  str_replace('\\\\', '\\\\\\\\', $comments[0][$i]);
		}


		$php_code[0] = array_values( $php_code[0] );

		for ($i=0;$i<count($php_code[1]);$i++)
		{
			$patterns_php[] = "/".preg_quote($php_code[0][$i], '/')."/";
			//  $patterns[]	= preg_quote($matches[0][$i], '/');
			$placeholders_php[]="replace_php_code-$i";
			// double backslashes must be escaped if we want to use them in the replacement argument
			$php_code[0][$i] = str_replace('\\\\', '\\\\\\\\', $php_code[1][$i]);
		}


		if ($placeholders_php)
		{
			$this->template = preg_replace($patterns_php, $placeholders_php, $this->template);
		}

		if ( $strings[0] )
		{
			$this->template = preg_replace($patterns_strings, $placeholders_strings, $this->template);
		}

		/* 
		 *Variables
		 */
		
		preg_match_all("/(?<![\"'])(\\$[a-zA-Z0-9->\\[\\]\\']*)/s", $this->template, $variables);
		
		$variables[0] = array_values( $variables[0] );

		for ($i=0;$i<count($variables[1]);$i++)
		{
			$patterns_variables[] = "/".preg_quote($variables[0][$i], '/')."/";
			//  $patterns[]	= preg_quote($matches[0][$i], '/');
			$placeholders_variables[]="replace_variable-$i";
			// double backslashes must be escaped if we want to use them in the replacement argument
			$variables[0][$i] = str_replace('\\\\', '\\\\\\\\', $variables[1][$i]);
		}
				
		if ( $variables[0] )
		{
			$this->template = preg_replace($patterns_variables, $placeholders_variables, $this->template);
		}
		
		/*
		 *Froms 
		 */ 	
		preg_match_all('/from\(([^\|\)]*)(|[^\)]*)?\)/s', $this->template, $froms);
		
		$froms[0] = array_values($froms[0]);

		for ($i=0;$i<count($froms[1]);$i++)
		{
			$patterns_froms[] = "/".preg_quote($froms[0][$i], '/')."/";
			//  $patterns[]	= preg_quote($matches[0][$i], '/');
			$placeholders_froms[]="replace_from-$i";
			// double backslashes must be escaped if we want to use them in the replacement argument
			$froms[0][$i] = str_replace('\\\\', '\\\\\\\\', $froms[1][$i]);
		}

		if ($froms[0])
		{
			$this->template = preg_replace($patterns_froms, $placeholders_froms, $this->template);
		}
		
		
		//remove comments
		$this->template = preg_replace("/(?<![\"'])\/\*.*?\*\/|\s*(?<![\"'])\/\/[^\n]*/s", '', $this->template);
		$this->template = preg_replace('/\n+/',"\n", $this->template);
		$this->template = preg_replace('/(?<=\=)\s*\n/','', $this->template);

		
		$this->strings = $strings[0];
		$this->php_code = $php_code[0];
		$this->variables = $variables[0];
		$this->froms = $froms;

 		$lines = explode("\n",$this->template);
		foreach ($lines as $line)
		{
			$arr = explode('=', trim($line));
			if ($arr[0])
			{
				$this->selectors[] = $arr;
			}
		}
		//echo '<hr/><pre>' . htmlentities($this->template) .'</pre>';
		//die();

	}

	/**
	 * Convert a CSS-selector into an xPath-query
	 *
	 * @return    string
	 * @param    string $selector    The CSS-selector
	 */
	function css_to_xpath($selector)
	{
		$selector = (string) $selector;

		$css_selector = array(
		// E > F: Matches any F element that is a child of an element E
			'/([a-zA-Z#._-])\s*>\s*([a-zA-Z#._-])/',
		// E + F: Matches any F element immediately preceded by an element
			'/([a-zA-Z#._-])\s*\+\s*([a-zA-Z#._-])/',
		// E F: Matches any F element that is a descendant of an E element
			'/([a-zA-Z#._-])\s+([a-zA-Z#._-])/',
		// E:first-child: Matches element E when E is the first child of its parent
			'/(\w):first-child/',
		// E[foo]: Matches any E element with the "foo" attribute set (whatever the value)
			'/(\w)\[([\w\-]+)]/',
		// E[foo="warning"]: Matches any E element whose "foo" attribute value is exactly equal to "warning"
			'/(\w)\[([\w\-]+)\=\"(.*)\"]/',
		// [foo]: Matches any element with the "foo" attribute set (whatever the value)
			'/\[([\w\-]+)]/',
		// [foo="warning"]: Matches any element whose "foo" attribute value is exactly equal to "warning"
			'/\[([\w\-]+)\=\"(.*)\"]/',
		// div.warning: HTML only. The same as DIV[class~="warning"]
			'/(\w+|\*)\.([\w\-]+)+/',
		// .warning: HTML only. The same as [class~="warning"]
			'/\.([\w\-]+)+/',
		// E#myid: Matches any E element with id-attribute equal to "myid"
			'/(\w+)+\#([\w\-]+)/',
		// #myid: Matches any E element with id-attribute equal to "myid"
			'/\#([\w\-]+)/'
			);

			$xpath_query = array(
			'\1/\2',
			'\1/following-sibling::*[1]/self::\2',
			'\1//\2',
            '*[1]/self::\1',
            '\1 [ @\2 ]',
            '\1[ contains( concat( " ", @\1, " " ), concat( " ", "\2", " " ) ) ]',
            '* [ @\2 ]',
            '*[ contains( concat( " ", @\1, " " ), concat( " ", "\2", " " ) ) ]',
            '\1[ contains( concat( " ", @class, " " ), concat( " ", "\2", " " ) ) ]',
            '*[ contains( concat( " ", @class, " " ), concat( " ", "\1", " " ) ) ]',
            '\1[ @id = "\2" ]',
            '*[ @id = "\1" ]'
            );

            $result = (string) '//'. preg_replace($css_selector, $xpath_query, $selector);
            $this->debug(PSTTT_CSS_XPATH_TRANSFORM, $result);
            return $result;
	}

	//function
	function _process_template()
	{
		if ($this->selectors)
		foreach($this->selectors as $data)
		{
			$selector = trim($data[0]);
			$selector_components = explode('|', $selector);
			$selector = $selector_components[0];
			$modifier = (isset($selector_components[1])) ? trim($selector_components[1]) : '';
			$value = (isset($data[1])) ? trim($data[1]) : '';
			//enable disable debugging
			if (!$selector) continue;
			$this->debug(PSTTT_TYPE_SELECTOR, $selector);
			
			if ($selector == 'debug') $this->debug = ($value=='true') ? true:false;else
			{
				$value_elements = explode('-', $value);
				switch($value_elements[0])
				{
					case 'replace_string':
						$val = trim($this->strings[(int) $value_elements[1]],'"\'');
						$this->debug(PSTTT_TYPE_SELECTOR_STRING, $this->strings[(int) $value_elements[1]]);
						break;
					case 'replace_php_code':
						if ($modifier && !in_array($modifier, $this->_modifiers))
						{
							$val = '<script language="php">' . $this->php_code[(int) $value_elements[1]] . '</script>';
						}  else
						{
							$val = '<script language="php"><![CDATA[' . $this->php_code[(int) $value_elements[1]] . ']]></script>';
						}
						$this->debug(PSTTT_TYPE_SELECTOR_PHP, $this->php_code[(int) $value_elements[1]]);
						break;
					case 'replace_variable':
						if ($modifier)
						{
							if ($modifier == 'if_exists' || $modifier == 'hide')
							{
								$val = $this->variables[(int) $value_elements[1]];
							} else if (!in_array($modifier, $this->_modifiers))
							{
							 	$val = '<script language="php"> echo ' . $this->variables[(int) $value_elements[1]] . ';</script>';
							}
						}  else
						{
							$val = '<script language="php"><![CDATA[ echo ' . $this->variables[(int) $value_elements[1]] . ';]]></script>';
						}
						$this->debug(PSTTT_TYPE_SELECTOR_VARIABLE, $this->variables[(int) $value_elements[1]]);
						break;
					case 'replace_from':
						$from = $this->froms[0][(int) $value_elements[1]];//external html file
						$from_selector = substr($this->froms[2][(int) $value_elements[1]],1);
						//load specified selector if available otherwise load html with the same selector
						if (empty($from_selector))
						{
							//override default selector with the provided one
							$from_selector = $selector;
							
						}
						//get html
						$this->debug(PSTTT_TYPE_SELECTOR_FROM,$from);
						$val = $this->load_from_external_html($from, $from_selector);
						break;
				}
				
				$elements = $this->xpath->query($this->css_to_xpath($selector));
				switch ($modifier)
				{
					case 'outerHTML':
						$this->outerHTML($elements, $val);
						break;
					case 'before':
						$this->insertBefore($elements, $val);
						break;
					case 'after':
						$this->insertAfter($elements, $val);
						break;
					case 'deleteAllButFirst':
						$this->deleteAllButFirst($elements, $val);
						break;
					case 'delete':
						$this->delete($elements, $val);
					case 'if_exists':
						$this->if_exists($elements, $val);
						break;
					case 'hide':
						$this->hide($elements, $val);
						break;
					case '':
						$this->innerHTML($elements, $val);
						break;
					default:
						$this->setAttribute($elements, $modifier, $val);
						break;
				}
			}
		}
	}

	function remove_children(&$node)
	{
		while ($node->firstChild)
		{
			while ($node->firstChild->firstChild)
			{
				$this->remove_children(&$node->firstChild);
			}
			$node->removeChild($node->firstChild);
		}
	}

	function innerHTML(&$node_list, $html = false)
	{
		foreach ($node_list as $node)
		{
			if($html === false)
			{

				$doc = new DOMDocument();
				foreach ($node->childNodes as $child)
				$doc->appendChild($doc->importNode($child, true));

				return $doc->saveHTML();

			} else
			{

				$this->remove_children(&$node);
				if($html == '') continue;

				$f = $this->document->createDocumentFragment();
				$f->appendXML($html);
				$node->appendChild($f);
			}
		}

	}

	function if_exists(&$node_list, $variable = false)
	{
		foreach ($node_list as $node)
		{
			if($variable == '') continue;

			//before
			$html = "<script language=\"php\">if ($variable) {</script>";
			$f = $this->document->createDocumentFragment();
			$f->appendXML($html);
			$node->parentNode->insertBefore( $f, $node);

			//after
			$html = "<script language=\"php\">}</script>";
			$f = $this->document->createDocumentFragment();
			$f->appendXML($html);
			//$node->parentNode->appendChild( $f );
			$node->parentNode->insertBefore( $f, $node->nextSibling);
		}
	}
	
	function hide(&$node_list, $variable = false)
	{
		foreach ($node_list as $node)
		{
			if($variable == '') continue;

			//before
			$html = "<script language=\"php\">if (!$variable) {</script>";
			$f = $this->document->createDocumentFragment();
			$f->appendXML($html);
			$node->parentNode->insertBefore( $f, $node);

			//after
			$html = "<script language=\"php\">}</script>";
			$f = $this->document->createDocumentFragment();
			$f->appendXML($html);
			//$node->parentNode->appendChild( $f );
			$node->parentNode->insertBefore( $f, $node->nextSibling);
		}
	}
	
	
	function insertBefore(&$node_list, $html = false)
	{
		foreach ($node_list as $node)
		{
			if($html == '') continue;

			$f = $this->document->createDocumentFragment();
			$f->appendXML($html);
			$node->parentNode->insertBefore( $f, $node);

		}
	}


	function insertAfter(&$node_list, $html = false)
	{
		foreach ($node_list as $node)
		{
			if($html == '') continue;

			$f = $this->document->createDocumentFragment();
			$f->appendXML($html);
			//$node->parentNode->appendChild( $f );
			$node->parentNode->insertBefore( $f, $node->nextSibling);
		}
	}
	
	function deleteAllButFirst(&$node_list, $html = false)
	{
		$first = true;
		foreach ($node_list as $node)
		{
			if (!$first)
			{ 
				$this->remove_children(&$node);
				$node->parentNode->removeChild($node);
			}
			$first = false;
		}
	}
	
	function delete(&$node_list, $html = false)
	{
		foreach ($node_list as $node)
		{
				$this->remove_children(&$node);
				$node->parentNode->removeChild($node);
		}
	}
	
	function setAttribute(&$node_list, $attribute, $val)
	{
		foreach ($node_list as $node)
		{
		
			$node->setAttribute($attribute,$val);
		}
	}
	
	
	function get_inner_html($element)
	{
	    $innerHTML = "";
        $tmp_dom = new DOMDocument();
        $tmp_dom->appendChild($tmp_dom->importNode($element, true));
        $innerHTML.=trim($tmp_dom->saveHTML());
	    return '<![CDATA[' . $innerHTML . ']]>';
	} 
	
	function load_from_external_html($from, $selector)
	{
		$document = new DomDocument(); 
		@$document->loadHTMLFile($this->html_path . $from);
		$xpath = new DOMXpath($document);
		$elements = $xpath->query($this->css_to_xpath($selector));
		return $this->get_inner_html($elements->item(0));
	}
	
	function load_html_template($html_file)
	{
		$this->debug(PSTTT_TYPE_LOAD, $this->html_path . $html_file);
		@$this->document->loadHTMLFile($this->html_path . $html_file);

		//original document used to extract selectors
		$this->original_document = clone($this->document);
		$this->xpath = new DOMXpath($this->document );
	}

	function save_compiled_template($compiled_file)
	{
		$this->_process_template();
		$html = $this->document ->saveHTML();
		$html = preg_replace_callback('@&lt;script(%20|\s)language=(%22|")php(%22|")&gt;.*?&lt;/script&gt;@s',
			create_function(
            '$matches',
            'return urldecode(html_entity_decode($matches[0]));'
        	), $html);//sad hack :(
		
        $this->debug(PSTTT_TYPE_SAVE, $compiled_file);        	

        //show debug console if needed
        if ($this->debug_log)
        {
        	$this->debug_log_to_html();
        	$PSTTT_DEBUG_JQUERY = PSTTT_DEBUG_JQUERY;
        	echo <<<HTML
<script src="$PSTTT_DEBUG_JQUERY"></script>     
<script>
function psttt_selector_over(selector)
{
	jQuery(selector).addClass('pstt_selected');
	return false;
}
function psttt_selector_out(selector)
{
	jQuery(selector).removeClass('pstt_selected');
	return false;
}

//this needs firebug or equivalent
function psttt_selector_click(selector)
{
	console.log(jQuery(selector));
	return false;
}

function psttt_hide(selector)
{
	if (jQuery(".psttt_console_log_content").css('display') == 'none')
	{
		jQuery(".psttt_console_log").css({height:"350px"});
    } else
    {
		jQuery(".psttt_console_log").css({height:"30px"});
    }
	jQuery(".psttt_console_log_content").toggle("slow");
	return false;
}

function psttt_close()
{
	jQuery(".psttt_console_log").remove()
	return false;
}

</script>   	
<style>
.pstt_selected
{
	border:5px solid red !important;        
}
html
{
	padding-bottom:350px;
}
.psttt_console_log
{
	background:#fff;z-index:10000;position:fixed;bottom:0;width:100%;height:300px;overflow:auto;border:1px solid #000;
}
</style>
<div class="psttt_console_log">
<a href="http://www.codeassembly.com/Psttt!-full-documentation/">Psttt!</a>
<a href="#" onclick="psttt_hide()">Toggle</a>
<a href="#" onclick="psttt_close()">Close</a>
<div class="psttt_console_log_content">
$this->debug_html;
</div>
</div>
HTML;
        }
		return file_put_contents($compiled_file, $html);
	}
}