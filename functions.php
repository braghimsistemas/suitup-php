<?php

use Braghim\MvcAbstractController;
use ModuleManager\ModelLogs\Gateway\Log;

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
			if (function_exists('createSystemLog')) {
				createSystemLog($e, Log::EMERG);
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
			createSystemLog($e, Log::EMERG);
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
	 * @param type $var
	 * @param type $echo
	 */
	function dump($var, $echo = true) {
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
	function mctime() {
		list($usec, $sec) = explode(" ", microtime());
		return ((float) $usec + (float) $sec);
	}
}

/**
 * Renderiza um html incluindo variaveis
 * 
 * @param type $renderViewName
 * @param type $vars
 * @param type $renderViewPath
 * @return type
 */
function renderView($renderViewName, $vars = array(), $renderViewPath = null) {
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
 * Traduz um trace de exception para string.
 * !!! CUIDADO !!! funcao recursiva....
 * 
 * @param type $args
 * @param type $root
 * @return type
 */
function getTraceArgsAsString($args, $root = true) {
	
	$argString = "";
	
	switch (gettype($args)) {
		case 'string':
			$argString .= '"'.$args.'"';
		break;
		case 'integer':
		case 'float':
		case 'double':
			$argString .= '('.gettype($args).') '.$args;
		break;
		case 'boolean':
			$argString .= ($args ? 'true' : 'false');
		break;
		case 'array':
			if ($root) {
				foreach($args as $key => $arg) {
					$argString .= getTraceArgsAsString($arg, false).", ";
				}
				$argString = preg_replace("/,(\s)?$/", "", $argString);
				
			} else {
				foreach($args as $key => $arg) {
					$argString .= '"'.$key.'" => '.getTraceArgsAsString($arg, false).", ";
				}
				$argString = "array(".preg_replace("/,(\s)?$/", "", $argString).")";
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