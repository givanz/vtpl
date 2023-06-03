<?php

/*
Copyright 2023 Ziadin Givan

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

   http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.

https://github.com/givanz/vtpl
*/


class VtplDebug {
	private $enabled = false;

	private $debugLog = [];

	private $debugHtml;

	function enabled() {
		return $this->enabled;
	}

	function enable($switch) {
		$this->enabled = $switch;
	}

	function log($type,$message) {
		if ($this->enabled) {
			$this->debugLog[][$type]= $message;
		}
	}

	function addDebugHtmlLine($command, $parameters, $break = '<br/>') {
		$this->debugHtml .= "<span>&nbsp;<b>$command</b> $parameters</span>$break";
	}

	function debugLogToHtml() {
		foreach ($this->debugLog as $line) {
			$type    = key($line);
			$message = $line[$type];

			switch ($type) {
				case 'LOAD':
					$this->addDebugHtmlLine('LOAD',$message);

				break;

				case 'SAVE':
					$this->addDebugHtmlLine('SAVE',$message);

				break;

				case 'SELECTOR':
					$this->addDebugHtmlLine('SELECTOR',
							   /*$this->cssToXpath($message) . */"<a href='#' 
							onclick=\"return vtpl_selector_click('$message')\" 
							onmouseover=\"return vtpl_selector_over('$message')\"
							onmouseout=\"return vtpl_selector_out('$message')\">
							$message</a>", '');

				break;

				case 'SELECTOR_STRING':
					$this->addDebugHtmlLine('INJECT STRING',$message);

				break;

				case 'SELECTOR_PHP':
					$this->addDebugHtmlLine('INJECT PHP',htmlentities($message));

				break;

				case 'SELECTOR_VARIABLE':
					$this->addDebugHtmlLine('INJECT VARIABLE',$message);

				break;

				case 'SELECTOR_FROM':
					$this->addDebugHtmlLine('EXTERNAL HTML',$message);

				break;

				case 'CSS_XPATH_TRANSFORM':
					if (VTPL_DEBUG_SHOW_XPATH) {
						$this->addDebugHtmlLine('RESULTED XPATH',
								   "<a href='#' 
							onclick=\"return vtpl_selector_click('$message')\" 
							onmouseover=\"return vtpl_selector_over('$message')\"
							onmouseout=\"return vtpl_selector_out('$message')\">
							$message</a>");
					}

				break;

				case 'CSS_SELECTOR':
					$this->addDebugHtmlLine('INVALID CSS SELECTOR',htmlentities($message));

				break;

				default:
					$this->addDebugHtmlLine('',$message);

				break;
				}
		}
	}

	function printLog() {
		$this->debugLogToHtml();
		echo
<<<HTML
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.0/dist/jquery.min.js"></script>

<script>
function vtplSelectorOver(selector)
{
	jQuery(selector).addClass('vtpl_selected');
	return false;
}
function vtplSelectorOut(selector)
{
	jQuery(selector).removeClass('vtpl_selected');
	return false;
}

//this needs firebug or equivalent
function vtplSelectorClick(selector)
{
	console.log(jQuery(selector));
	return false;
}

function vtplHide(selector)
{
	if (jQuery(".vtpl_console_log_content").css('display') == 'none')
	{
		jQuery(".vtpl_console_log").css({height:"350px"});
	} else
	{
		jQuery(".vtpl_console_log").css({height:"30px"});
	}
	jQuery(".vtpl_console_log_content").toggle("slow");
	return false;
}

function vtplClose()
{
	jQuery(".vtpl_console_log").remove();
	return false;
}
</script>   	
<style>
.vtpl_selected {
	border:5px solid red !important;        
}
html {
	padding-bottom:350px;
}
.vtpl_console_log  {
	background:#fff;
	z-index:10000;
	position:fixed;
	line-height: 1.6;
	left:0;
	bottom:0;
	width:100%;
	height:300px;
	padding:1rem;
	overflow:auto;
	border:1px dashed #ccc;
}
.vtpl_console_log_content {
	margin-top:1rem;
}
</style>
<div class="vtpl_console_log">
	<a href="#" onclick="return vtplHide()">Toggle</a>
	<a href="#" onclick="return vtplClose()">Close</a>
	<div class="vtpl_console_log_content">
		$this->debugHtml;
	</div>
</div>
HTML;
	}
}
