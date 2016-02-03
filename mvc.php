<?php
// Define a constante que encontra a raíz do projeto na URL
$route = preg_replace("/\?(" . preg_quote($_SERVER['QUERY_STRING'], "/") . ")/", "", trim($_SERVER['REQUEST_URI'], '/'));

// Padroes de rota
$module = 'ModuleDefault';
$controller = 'index';
$action = 'index';

// Separa itens da URL.
if ($route) {
	
	// controller OR
	// module OR
	// controller/action OR
	// module/controller OR
	// module/controller/action
	$routeParts = explode('/', $route);
	switch (count($routeParts)) {
		case 1:
			if (is_dir("mvc/Module".ucfirst(strtolower($routeParts[0])))) {
				$module = "Module".ucfirst(strtolower($routeParts[0]));
			} else {
				$controller = $routeParts[0];
			}
		break;
		case 2:
			if (is_dir("mvc/Module".ucfirst(strtolower($routeParts[0])))) {
				$module = "Module".ucfirst(strtolower($routeParts[0]));
				$controller = $routeParts[1];
			} else {
				$controller = $routeParts[0];
				$action = $routeParts[1];
			}
		break;
		default:
			// Se tiver mais que 3 parametros na URL
			// ex.: /module/controller/action/naoseipraqueisso
			// o ultimo sera ignorado
			$module = "Module".ucfirst(strtolower($routeParts[0]));
			$controller = $routeParts[1];
			$action = $routeParts[2];
		break;
	}
}

/**
 * Token para o sistema nao "confundir" as mensagens de sessao
 * atual com mensagens que ja existian em outra pagina.
 * ¯\_(-.-)_/¯
 */
define('MSG_NSP_TOKEN', mctime());

$result = new stdClass();
try {
	
	// Se aqui não der erro é porque está tudo configurado
	// corretamente
	$result = resolve($module, $controller, $action);
} catch (Exception $e) {
	try {
		// Aqui já deu merda, o usuário verá a tela de erro
		// do módulo
		$result = resolve($module, 'error', 'not-found');
	} catch (Exception $ex) {
		try {
			// Aqui piorou, o sistema chama um módulo padrão de erros.
			$result = resolve('ModuleError', 'error', 'not-found', 'library');
			
		} catch (Exception $ex2) {
			exit('Mexeu no framework né Zé!?');
		}
	}
	$result->exception = $e;
}

/**
 * A partir das informacoes busca os arquivos correspondentes para
 * renderizar o sistema corretamente.
 * 
 * @param type $module
 * @param type $controller
 * @param type $action
 * @param type $path
 * @return \stdClass
 * @throws Exception
 */
function resolve($module, $controller, $action, $path = "mvc")
{
	$result = new stdClass();
	$result->layoutName = "layout.phtml";
	$result->mainPath = $path;
	
	// Define modulo
	if (!is_dir($path.DIRECTORY_SEPARATOR.$module) || !is_readable($path.DIRECTORY_SEPARATOR.$module)) {
		throw new Exception("Módulo '$module' não existe");
	}
	$result->moduleName = $module;
	
	// Define nome do controlador e acao
	$controllerName = ucfirst(strtolower($controller)) . "Controller";

	// Verifica se controlador existe
	$controllerFile = $path."/$module/Controllers" . DIRECTORY_SEPARATOR . $controllerName . ".php";
	if (!file_exists($controllerFile)) {
		throw new Exception("Controlador '$controllerFile' não existe ou não pode ser lido");
	}
	$result->controllerName = $controllerName;
	
	// Cria instancia do controlador se o módulo foi carregado no loader do config.php
	$controllerNsp = $module."\\Controllers\\$controllerName";
	if (!class_exists($controllerNsp)) {
		throw  new Exception("Tem que apontar no loader este novo módulo, cabeção. (config.php)");
	}
	$result->controller = new $controllerNsp();

	// Define nome da acao
	$actionName = preg_replace("/\s+/", "", lcfirst(ucwords(preg_replace("/\-/", " ", $action)))) . "Action";
	
	// Verifica se ação existe no controlador
	if (!method_exists($result->controller, $actionName)) {
		throw new Exception("Ação '$actionName' não existe no controlador '$controllerNsp'");
	}
	$result->actionName = $actionName;
	
	// Diretorio de views do modulo
	if (!is_dir("$path/$module/views/")) {
		throw new Exception("Diretório de views não existe para o módulo '$module'");
	}
	$result->layoutPath = "$path/$module/views/";
	
	/**
	 * !! ATT !!
	 * Aqui não é necessário validar a existência do arquivo da view,
	 * pois quando a acao devolve um ajax não renderiza html
	 */
	$result->viewName = $action . ".phtml";
	$result->viewPath = "$path/$module/views/" . $controller;
	
	// Cada módulo tem um
	$abstractController = "$module\\Controllers\\AbstractController";
	if (!class_exists($abstractController)) {
		$abstractController = "Braghim\\MvcAbstractController";
	}
	
	$abstractController::$params = $result;
	
	return $result;
}

return $result;
