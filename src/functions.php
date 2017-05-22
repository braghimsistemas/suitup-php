<?php
/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2016 Braghim Sistemas
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

/**
 * Funções uteis para serem usadas em qualquer lugar
 */

use SuitUp\Mvc\MvcAbstractController;

/**
 * Valores fixos de tamanhos diversos em Bytes
 */
define('KB', 1024);             // Em bytes
define('MB', 1048576);          // Em bytes
define('GB', 1073741824);       // Em bytes
define('TB', 1099511627776);    // Em bytes

/**
 * Get all non treated exceptions on the system.
 *
 * @param Exception $e The exception
 * @param bool $isTest In test environment will no cause exception outputs
 */
function throwNewExceptionFromAnywhere(\Exception $e, $isTest = false)
{
	$setup = new SuitUpStart();

	// Module ErrorController
	try {
		$setup->mvc = $setup->resolve($setup->mvc->moduleName, 'error', 'error');
	} catch (\Exception $ex) {

		// SuitUp default module error.
		try {
			$setup->mvc = $setup->resolve('ModuleError', 'error', 'error', __DIR__ . DIRECTORY_SEPARATOR . '.');
		} catch (\Exception $ex2) {
			
			// It's possible to create this function in your project to
			// generate your own logs controll.
			if (function_exists('createSystemLog')) {
				createSystemLog($e);
			}

			if (!$isTest) {
				echo "Non treated exception thrown";
				
				(DEVELOPMENT) ? dump($e) : exit;
				
			} else {
				return $e->getMessage();
			}
		}
	}
	
	// Store exception to be used on the controller or view from ErrorController
	$setup->mvc->exception = $e;

	// Last try
	try {
		$setup->run();
	} catch (\Exception $ex3) {
		
		// It's possible to create this function in your project to
		// generate your own logs controll.
		if (function_exists('createSystemLog')) {
			createSystemLog($e);
		}

		if (!$isTest) {
			echo "Non treated exception thrown";

			(DEVELOPMENT) ? dump($ex3) : exit;

		} else {
			return $ex3->getMessage();
		}
	}
}

if (!function_exists('dump')) {

	/**
	 * Simple debug function, just like Zend\Debug.
	 *
	 * @param mixed $var What you want to debug.
	 * @param bool $echo If false, this function will return the result.
	 * @return string
	 */
	function dump($var, $echo = true)
	{
		ob_start();
		var_dump($var);

		/**
		 * $argv when you run this function by command line.
		 */
		if (isset($argv)) {
			$output = preg_replace("/\]\=\>\n(\s+)/m", "] => ", ob_get_clean()) . "\n\n";
		} else {
			$output = "<pre>" . preg_replace("/\]\=\>\n(\s+)/m", "] => ", ob_get_clean()) . "</pre>";
		}
		if ($echo) {
			echo $output;
			exit;
		}
		return $output;
	}
}

if (!function_exists('mctime')) {
	/**
	 * Retorna o microtime em float.
	 * @return float
	 */
	function mctime()
	{
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}
}

/**
 * Renderiza um html incluindo variaveis
 *
 * @param string $renderViewName Nome do arquivo .phtml que será renderizado.
 * @param array|mixed $vars Variaveis que estarão disponíveis na views
 * @param string $renderViewPath Caminho para o arquivo .phtml que será renderizado
 * @return string
 */
function renderView($renderViewName, $vars = array(), $renderViewPath = null)
{
	if (!$renderViewPath) {
		$renderViewPath = MvcAbstractController::$params->layoutPath;
	}

	// Injeta variaveis na view
	foreach ($vars as $n => $v) {
		$$n = $v;
	}

	ob_start();
	include $renderViewPath . DIRECTORY_SEPARATOR . $renderViewName;
	return ob_get_clean();
}

/**
 * Renderiza um template de paginacao.
 *
 * @param SuitUp\Paginate\Paginate $object Objeto de paginacao criado na query.
 * @param string $renderViewName Nome do arquivo .phtml de paginacao
 * @return string Html pronto dos botoes de paginacao
 */
function paginateControl(\SuitUp\Paginate\Paginate $object, $renderViewName = 'paginacao.phtml')
{

	// Return
	$items = array();

	$currentPage = ($object->getCurrentPage() > 0) ? $object->getCurrentPage() : 1;
	$totalPages = $object->getTotalPages();
	$pageRange = ($object->getPageRange() === 'total') ? $totalPages : $object->getPageRange();

	// Page range odd
	if ($pageRange % 2 == 0) {
		$pageRange--;
	}

	if ($currentPage <= $totalPages) {
		$bothSides = ($pageRange - 1) / 2;

		// Mount beginning
		for ($i = 0; $i < $bothSides; $i++) {
			$page = $currentPage - ($bothSides - $i);

			if ($page <= $totalPages) {
				if ($page >= 1)
					$items[] = $page;
			}
		}

		// Half
		if ($currentPage <= $totalPages) {
			if (!in_array($currentPage, $items)) {
				$items[] = (int)$currentPage;
			}
		}

		$itemsCount = count($items);
		$last = end($items);
		$need = $pageRange - $itemsCount;

		// End
		for ($i = 0; $i < $need; $i++) {
			if (($last + $i + 1) <= $totalPages) {
				$items[] = ($last + $i + 1);
			}
		}

		// If missed any in the beginning.
		if (count($items) < $pageRange) {
			$need = $pageRange - count($items);

			for ($i = 0; $i < $need; $i++) {
				if ($items[0] - 1 <= $totalPages && ($items[0] - 1) > 0) {
					array_unshift($items, $items[0] - 1);
				}
			}
		}
	} else {
		for ($i = 0; $i < $totalPages; $i++) {
			$items[] = $totalPages - $i;
		}

		foreach ($items as $kI => $fI) {
			if ($kI > $pageRange - 1) {
				unset($items[$kI]);
			}
		}
		$items = array_reverse($items);
	}

	if (count($items) < 2) {
		$items = array();
	}

	// Define a url base.
	$url = '/' . preg_replace("/\?(" . preg_quote(getenv('QUERY_STRING'), "/") . ")/", "", trim(getenv('REQUEST_URI'), '/')) . "?";
	foreach ((array)filter_input_array(INPUT_GET) as $i => $value) {
		if ($i != 'pagina') {
			$url .= $i . '=' . $value . '&';
		}
	}
	$url = trim(trim($url, '?'), '&');

	// Envia para view que monta o html da paginacao
	return renderView($renderViewName, array(
		'items' => $items,
		'totalPages' => $totalPages,
		'currentPage' => $currentPage,
		'nextPage' => in_array(($currentPage + 1), $items) ? $currentPage + 1 : false,
		'previousPage' => in_array(($currentPage - 1), $items) ? $currentPage - 1 : false,
		'baseUrl' => $url . (preg_match("/\?/", $url) ? '&' : '?'),
	));
}

/**
 * Traduz um trace de exception para string.
 * !!! CUIDADO !!! funcao recursiva....
 *
 * @param mixed $args
 * @param bool $root
 * @return string
 */
function getTraceArgsAsString($args, $root = true)
{

	$argString = "";

	switch (gettype($args)) {
		case 'string':
			$argString .= '"' . $args . '"';
			break;
		case 'integer':
		case 'float':
		case 'double':
			$argString .= '(' . gettype($args) . ') ' . $args;
			break;
		case 'boolean':
			$argString .= ($args ? 'true' : 'false');
			break;
		case 'array':
			if ($root) {
				foreach ($args as $key => $arg) {
					$argString .= getTraceArgsAsString($arg, false) . ", ";
				}
				$argString = preg_replace("/,(\s)?$/", "", $argString);

			} else {
				foreach ($args as $key => $arg) {
					$argString .= '"' . $key . '" => ' . getTraceArgsAsString($arg, false) . ", ";
				}
				$argString = "array(" . preg_replace("/,(\s)?$/", "", $argString) . ")";
			}
			break;
		case 'NULL':
			$argString .= "NULL";
			break;
		case 'object':
			$argString .= ($args == null) ? "NULL" : get_class($args);
			break;
		default:
			// O proprio type
			$argString .= gettype($args);
	}
	return $argString;
}
