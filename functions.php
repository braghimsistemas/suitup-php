<?php

/**
 * Funções uteis para serem usadas em qualquer lugar
 */

/**
 * Captura todas as exceções não tratadas do sistema.
 * 
 * @param Exception $e
 */
function throwNewExceptionFromAnywhere($e) {
	$setup = BraghimSistemas::getInstance();
	
	// Tenta carregar a tela de erro do MODULO.
	try {
		$setup->mvc = $setup->resolve($setup->mvc->moduleName, 'error', 'error');
	} catch (Exception $ex) {
		
		// Tenta carregar a tela de erro
		// padrao do framework.
		try {
			$setup->mvc = $setup->resolve('ModuleError', 'error', 'error', __DIR__.DIRECTORY_SEPARATOR.'library');
		} catch (Exception $ex2) {
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
		echo "Exception sem possibilidade de tratamento.";
		dump($ex3);
	}
}

if (!function_exists('dump')) {
	
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