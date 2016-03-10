<?php
/**
 * Token para o sistema nao "confundir" as mensagens de sessao
 * atual com mensagens que ja existiam em outra pagina.
 * Utilizado dentro da classe Braghim\MvcAbstractController
 * 
 * ¯\_(-.-)_/¯
 */
define('MSG_NSP_TOKEN', mctime());

/**
 * Utilize este arquivo como entrada do framework.
 * Vide documentação online.
 */
class BraghimSistemas {

	/**
	 * Versão atual do sistema
	 */
	const VERSION = '1.0.4';
	
	/** Singleton **/
	private static $instance;
	
	/**
	 * Caminho, no projeto do usuario, onde se encontram as pastas dos módulos.
	 * @var string 
	 */
	private $modulesPath;
	
	/**
	 * Todos os parametros necessarios para rodar a aplicacao.
	 * @var stdClass
	 */
	public $mvc;
	
	/**
	 * Primeiro metodo a ser chamado.
	 * Configura o sistema.
	 * 
	 * @return BraghimSistemas
	 * @throws Exception
	 */
	public static function setup($modulesPath = null) {
		if (self::$instance == null) {
			if (!$modulesPath) {
				throw new \Exception("Necessário informar a pasta onde os módulos serão criados.");
			}
			self::$instance = new self($modulesPath);
		}
		return self::$instance;
	}
	
	/**
	 * Retorna instancia da classe
	 * 
	 * @return BraghimSistemas
	 */
	public static function getInstance() {
		return self::setup();
	}
	
	/**
	 * Construtor, mas substituido para dar a oportunidade de carregar de modo diferente.
	 */
	private function __construct($modulesPath) {
		$this->modulesPath = $modulesPath;
		
		/**
		 * Primeiro carrega as classes do usuario e da biblioteca
		 */
		$loader = include 'vendor/autoload.php';
		$loader->add('Braghim', __DIR__.DIRECTORY_SEPARATOR.'library/.');
		$loader->add('ModuleError', __DIR__.DIRECTORY_SEPARATOR.'library/.');
		
		// Carrega todos os modulos automaticamente
		foreach (scandir($this->modulesPath) as $module) {
			if (!in_array($module, array('.', '..')) && is_dir($this->modulesPath.DIRECTORY_SEPARATOR.$module)) {
				$loader->add($module, $this->modulesPath);
			}
		}
		
		// Define rotas
		$routes = Braghim\Routes::getInstance();
		
		/**
		 * Este escopo aqui eh referente a montagem de parametros
		 * para o sistema saber quais classes e metodos chamar
		 * e de onde. NÃO SE REFERE AO ESCOPO DE RODAR O APP.
		 */
		$result = new stdClass();
		try {
			// Se aqui não der erro é porque está tudo configurado
			// corretamente
			$result = $this->resolve($routes->module, $routes->controller, $routes->action);
		} catch (Exception $e) {
			try {
				// Aqui já deu merda, o usuário verá a tela de erro
				// do módulo
				$result = $this->resolve($routes->module, 'error', 'not-found');
			} catch (Exception $ex) {
				try {
					// Aqui piorou, o sistema chama um módulo padrão de erros.
					$result = $this->resolve('ModuleError', 'error', 'not-found', __DIR__.DIRECTORY_SEPARATOR.'library', true);
				} catch (Exception $ex2) {
					exit('Mexeu no framework né Zé!?');
				}
			}
			$result->exception = $e;
		}
		
		// Declara resultado para a variavel da classe.
		$this->mvc = $result;
	}

	/**
	 * A partir das informacoes busca os arquivos correspondentes para
	 * renderizar o sistema corretamente.
	 * 
	 * @param type $module
	 * @param type $controller
	 * @param type $action
	 * @param type $path
	 * 
	 * @return \stdClass
	 * @throws Exception
	 */
	public function resolve($module, $controller, $action, $path = null) {
		if (!$path) {
			$path = $this->modulesPath;
		}
		
		$result = new stdClass();
		$result->layoutName = "layout.phtml";
		$result->mainPath = $path;

		// Define modulo
		if (!is_dir($path . DIRECTORY_SEPARATOR . $module) || !is_readable($path . DIRECTORY_SEPARATOR . $module)) {
			throw new Exception("Módulo '$module' não existe");
		}
		$result->moduleName = $module;

		// Define nome do controlador e acao
		$controllerName = ucfirst(strtolower($controller)) . "Controller";
		
		// Verifica se controlador existe
		$controllerFile = $path . "/$module/Controllers" . DIRECTORY_SEPARATOR . $controllerName . ".php";
		if (!file_exists($controllerFile)) {
			throw new Exception("Controlador '$controllerFile' não existe ou não pode ser lido");
		}
		$result->controllerName = $controllerName;

		// Cria instancia do controlador se o módulo foi carregado no loader do config.php
		$controllerNsp = $module . "\\Controllers\\$controllerName";
		if (!class_exists($controllerNsp)) {
			throw new Exception("Tem que apontar no loader este novo módulo, cabeção. (config.php)");
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

	/**
	 * Roda efetivamente a aplicacao.
	 */
	public function run()
	{
		// Captura todas as exceções não tratadas do sistema
		set_exception_handler('throwNewExceptionFromAnywhere');
		
		// Gatilho para fila de processos.
		// Se der alguma exception aqui vai
		// cair na funcao descrita acima.
		$this->mvc->controller->preDispatch();
		$this->mvc->controller->init();
		$this->mvc->controller->{$this->mvc->actionName}();
		$this->mvc->controller->posDispatch();
	}
}
