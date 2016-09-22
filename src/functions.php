<?php
/**
 * Funções uteis para serem usadas em qualquer lugar
 */

/**
 * Temp solution
 *
 * An example of a project-specific implementation.
 * http://www.php-fig.org/psr/psr-4/examples/
 *
 * After registering this autoload function with SPL, the following line
 * would cause the function to attempt to load the \Foo\Bar\Baz\Qux class
 * from /path/to/project/src/Baz/Qux.php:
 *
 * new \Foo\Bar\Baz\Qux;
 *
 * @param string $class The fully-qualified class name.
 * @return void
 */
spl_autoload_register(function($class) {

	// project-specific namespace prefix
	$prefix = 'Braghim\\';

	// base directory for the namespace prefix
	$base_dir = __DIR__.DIRECTORY_SEPARATOR;

	// does the class use the namespace prefix?
	$len = strlen($prefix);
	if (strncmp($prefix, $class, $len) !== 0) {
		// no, move to the next registered autoloader
		return;
	}

	// get the relative class name
	$relative_class = substr($class, $len);

	// replace the namespace prefix with the base directory, replace namespace
	// separators with directory separators in the relative class name, append
	// with .php
	$file = $base_dir.str_replace('\\', '/', $relative_class).'.php';

	// if the file exists, require it
	if (file_exists($file)) {
		include_once $file;
	}
});

spl_autoload_register(function($class) {

	// project-specific namespace prefix
	$prefix = 'ModuleError\\';

	// base directory for the namespace prefix
	$base_dir = __DIR__.DIRECTORY_SEPARATOR.'ModuleError'.DIRECTORY_SEPARATOR;

	// does the class use the namespace prefix?
	$len = strlen($prefix);
	if (strncmp($prefix, $class, $len) !== 0) {
		// no, move to the next registered autoloader
		return;
	}

	// get the relative class name
	$relative_class = substr($class, $len);

	// replace the namespace prefix with the base directory, replace namespace
	// separators with directory separators in the relative class name, append
	// with .php
	$file = $base_dir.str_replace('\\', '/', $relative_class).'.php';

	// if the file exists, require it
	if (file_exists($file)) {
		include_once $file;
	}
});

use Braghim\MvcAbstractController;

/**
 * Valores fixos de tamanhos diversos em Bytes
 */
define('KB', 1024);                // Em bytes
define('MB', 1048576);            // Em bytes
define('GB', 1073741824);        // Em bytes
define('TB', 1099511627776);    // Em bytes

/**
 * Captura todas as exceções não tratadas do sistema.
 *
 * @param Exception $e
 */
function throwNewExceptionFromAnywhere($e)
{
	$setup = BraghimSistemas::getInstance();

	// Tenta carregar a tela de erro do MODULO.
	try {
		$setup->mvc = $setup->resolve($setup->mvc->moduleName, 'error', 'error');
	} catch (Exception $ex) {

		// Tenta carregar a tela de erro
		// padrao do framework.
		try {
			$setup->mvc = $setup->resolve('ModuleError', 'error', 'error', __DIR__ . DIRECTORY_SEPARATOR . 'library');
		} catch (Exception $ex2) {
			if (function_exists('createSystemLog')) {
				createSystemLog($e);
			}

			echo "Exception sem possibilidade de tratamento.";
			dump($e);
		}
	}
	$setup->mvc->exception = $e;

	// Ultima tentativa de dar certo,
	// se chegar aqui e der erro então
	// o projeto esta configurado incorretamente.
	try {
		$setup->run();
	} catch (Exception $ex3) {
		if (function_exists('createSystemLog')) {
			createSystemLog($e);
		}

		echo "Exception sem possibilidade de tratamento.";
		dump($ex3);
	}
}

if (!function_exists('dump')) {

	/**
	 * Funçao para debug simplificada, semelhante ao Zend\Debug.
	 *
	 * @author Marco A. Braghim <marco.a.braghim@gmail.com>
	 * @param mixed $var
	 * @param bool $echo
	 * @return string
	 */
	function dump($var, $echo = true)
	{
		ob_start();
		var_dump($var);

		/**
		 * $argv vem quando o script eh executado por linha de comando.
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
 * @param Braghim\Paginate $object Objeto de paginacao criado na query.
 * @param string $renderViewName Nome do arquivo .phtml de paginacao
 * @return string Html pronto dos botoes de paginacao
 */
function paginateControl(Braghim\Paginate $object, $renderViewName = 'paginacao.phtml')
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
