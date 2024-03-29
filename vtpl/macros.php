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

/*
	preg_match helper
 */ 
function pmatch($pattern, $subject) {
	if (preg_match($pattern, $subject, $matches)) {
		if (count($matches) > 2) {
			return array_slice($matches, 1);
		} else {
			return $matches[1];
		}
	}
}

/*
	Convert var.key1.key2 > var['key1']['key2']
*/
function dotToArrayKey($key) {
	//var.key1.key2 > var['key1']['key2']
	//var.key1 > var['key1']

	return preg_replace_callback('/\.([-_\w]+)/', function ($matches) {
		return "['" . str_replace("'", "\'", $matches[1]) . "']";
	}, $key);
}


function stripExtraSpaces($string) {
	foreach (['\t', '\n', '\r', ' '] as $space) {
		$string = preg_replace('/(' . $space . ')' . $space . '+/', '\1', $string);
	}

	return $string;
}

/*
 * Regex filter to allow only matched content
 *
 * @param string $regex 
 * @param string $input 
 * @param integer $maxInputSize [100] 
 */
function filter($regex, $input, $maxInputSize = 100) {
	$matches = [];

	if (preg_match($regex, substr($input, 0, $maxInputSize), $matches)) {
		return $matches[0];
	} else {
		return false;
	}
}

/*
 * Get values from multidimensional arrays based on path
 * For example for array ['item' => ['desc' => ['name' => 'test']]] the path "item.description.name" will return "test".
 * 
 * 
*/

function arrayPath(array $a, $path, $default = null, $token = '.') {
	$p = strtok($path, $token);

	while ($p !== false) {
		if (! isset($a[$p])) {
			return $default;
		}

		$a = $a[$p];
		$p = strtok($token);
	}

	return $a;
}

/*
 If macro enables elements with data-v-if="condition = true" to be visible only if condition is true also data-v-if-not="condition = true"

 Parameter can have the following formats
 variable = variable ex product.price = price this will result in $product['price'] == $price
 variable = 'string' ex this.stock = 'available' this will result in $this->price == $price
 */
function ifCondition($string = '') {
	$logic      = ['&&', '\|\|', 'AND', 'OR'];
	$regex      = '/\s+(' . implode(')\s+|\s+(', $logic) . ')\s+/i';
	$conditions = preg_split($regex, $string, 0, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

	$return = '( ';

	foreach ($conditions as $condition) {
		if (in_array(str_replace('|', '\|', $condition),  $logic)) {
			$return .= " ) $condition ( ";
		} else {
			$operators         = ['>', '<', '<=', '>=', '=', '!='];
			$operatorsMatch    = implode('',array_unique(str_split(implode('', $operators))));
			$condition         = html_entity_decode($condition);

			$key      = $condition;
			$compare  = $condition;
			$operator = false;
			$value    = false;

			if ($key = strpbrk($condition, $operatorsMatch)) {
				$operator = trim(pmatch("/^([ $operatorsMatch]+)/", $key));
				$value    = pmatch("/[ $operatorsMatch]+(.+)$/", $key);
				$compare  = pmatch("/(.+?)[ $operatorsMatch]/", $condition);
			}

			if (strpos($value, 'this') === 0) {
				$value = str_replace('this.', 'this->', $value);
			}

			if (strpos($compare, 'this') === 0) {
				$compare = str_replace('this.', 'this->', $compare);
			}

			if (($compare && $compare[0] != "'") && ! is_numeric($compare)) {
				$compare = '$' . $compare;
			}

			if (($value && $value[0] != "'") && ! is_numeric($value)) {
				$value = '$' . $value;
			}

			if ($operator == '=') {
				$operator = '==';
			}

			$value   = dotToArrayKey($value);
			$compare = dotToArrayKey($compare);

			$return .= "(isset($compare) && ($compare $operator $value))";
		}
	}
	$return .= ' )';

	return $return;
}

function vtplIfCondition($vtpl, $node, $string = '') {
	return ifCondition($string);
}

/*
Remove class from node, used by  data-v-class-if
 */
function vtplRemoveClass($vtpl, $node, $className = '') {
	if (! $className) {
		return;
	}
	$value = $node->getAttribute('class');

	if (strpos($value, $className) !== false) {
		$node->setAttribute('class', str_replace($className, '', $value));
	}
}

function vtplIfClass($vtpl, $node) {
	$if      = [];
	$ifnot   = [];
	$length  = $node->attributes->length;

	for ($i = 0; $i < $length; ++$i) {
		if ($item = $node->attributes->item($i)) {
			$name = $item->name;

			if (strpos($name, 'data-v-class-if-not-') !== false) {
				$name           = str_replace('data-v-class-if-not-', '', $name);
				$ifnot[$name]   = $item->value;
			} else {
				if (strpos($name, 'data-v-class-if-') !== false) {
					$name           = str_replace('data-v-class-if-', '', $name);
					$if[$name]      = $item->value;
				}
			}
		}
	}

	$return = '';
	$value  = $node->getAttribute('class');

	foreach ($ifnot as $class => $cond) {
		$value = str_replace($class, '',$value);
		//$node->removeAttribute("data-v-class-if-not-$class");
		$condition = ifCondition($cond);
		$return .= "if  (!($condition)) {echo ' $class';}";
	}

	foreach ($if as $class => $cond) {
		$value = str_replace($class, '',$value);
		//$node->removeAttribute("data-v-class-if-$class");
		$condition = ifCondition($cond);
		$return .= "if  ($condition) {echo ' $class';}";
	}

	$value = preg_replace('/\s+/', ' ', $value);

	$node->setAttribute('class', $value);

	return $return;
}
