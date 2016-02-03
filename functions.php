<?php

/**
 * Funções uteis para serem usadas em qualquer lugar
 */

/**
 * Captura todas as exceções do sistema
 * 
 * @param Exception $e
 */
function throwNewExceptionFromAnywhere($e) {
	$result = resolve('ModuleError', 'error', 'error', 'library');
	$result->exception = $e;

	// Chama metodos por ordem
	$result->controller->preDispatch();
	$result->controller->init();
	$result->controller->{$result->actionName}();
	$result->controller->posDispatch();
}

if (!function_exists('')) {
	
	/**
	 * Funçao para debug simplificada, semelhante ao Zend\Debug.
	 * 
	 * @author Marco A. Braghim <marco.a.braghim@gmail.com>
	 * @param type $var
	 * @param type $echo
	 */
	function dump($var, $echo = true) {
		ob_start();
		var_dump($var);
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