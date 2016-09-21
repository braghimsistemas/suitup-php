<?php
use Braghim\MvcAbstractController;

/**
 * Token para o sistema nao "confundir" as mensagens de sessao
 * atual com mensagens que ja existiam em outra pagina.
 * Utilizado dentro da classe Braghim\MvcAbstractController
 *
 * ¯\_(-.-)_/¯
 */
define('MSG_NSP_TOKEN', mctime());

/**
 * Define a Constante DEVELOPMENT caso ainda não tenha sido.
 */
defined('DEVELOPMENT') || define('DEVELOPMENT', false);

/**
 * Define a Constante SHOW_ERRORS caso ainda não tenha sido.
 */
defined('SHOW_ERRORS') || define('SHOW_ERRORS', false);

/**
 * Utilize este arquivo como entrada do framework.
 * Vide documentação online.
 */
class BraghimSistemas {

	/**
	 * Versão atual do sistema
	 */
	const VERSION = '1.1.5';

	/**
	 * @var BraghimSistemas
	 */
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
	 * @param string $modulesPath Caminho para as pastas de modulos do sistema.
	 * @return BraghimSistemas
	 * @throws \Exception
	 */
	public static function setup($modulesPath = null) {
		if (self::$instance == null) {
			if (!$modulesPath) {
				throw new \Exception("Necessário informar a pasta onde os módulos serão criados.");
			}
			self::$instance = new self(trim($modulesPath, DIRECTORY_SEPARATOR));
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
	 * @param string $modulesPath Caminho para os modulos
	 * @throws \Exception
	 */
	private function __construct($modulesPath) {
		$this->modulesPath = $modulesPath;

		/**
		 * Primeiro carrega as classes do usuario e da biblioteca
		 */
		$loader = null;
		if (file_exists('autoload.php')) {

			// Quando rodamos um teste apontamos o sistema
			// diretamente para dentro da pasta vendor ;)
			$loader = include 'autoload.php';
		} else if (file_exists('vendor/autoload.php')) {

			// O cara está utilizando o framework normalmente em seu projeto.
			$loader = include 'vendor/autoload.php';
		}

		// Os caminhos estão corretos, então adiciona as classes do projeto.
		if ($loader) {
			$loader->add('Braghim', __DIR__.DIRECTORY_SEPARATOR.'library/.');
			$loader->add('ModuleError', __DIR__.DIRECTORY_SEPARATOR.'library/.');
		} else {
			throw new \Exception("Não é possível carregar as bibliotecas do SuitUp. Há algo errado com as dependências");
		}

		// Define rotas
		Braghim\Routes::$modulesPath = $modulesPath;
		$routes = Braghim\Routes::getInstance();
		
		/**
		 * Este escopo aqui eh referente a montagem de parametros
		 * para o sistema saber quais classes e metodos chamar
		 * e de onde. NÃO SE REFERE AO ESCOPO DE RODAR O APP.
		 */
		try {
			if (!is_dir($this->modulesPath)) {
				throw new \Exception("O diretório de módulos '{$this->modulesPath}' não existe");
			}

			// Tenta criar um arquivo .htaccess para proteger a pasta de modulos
			if (!file_exists($this->modulesPath.DIRECTORY_SEPARATOR.'.htaccess') && is_writable($this->modulesPath)) {
				
				// Este .htaccess impede que esta pasta liste seus arquivos.
				file_put_contents($this->modulesPath.DIRECTORY_SEPARATOR.'.htaccess', "Options -Indexes\n");
				chmod($this->modulesPath.DIRECTORY_SEPARATOR.'.htaccess', 0644);
			}
			
			// Carrega todos os modulos automaticamente
			foreach (scandir($this->modulesPath) as $module) {
				if (!in_array($module, array('.', '..')) && is_dir($this->modulesPath.DIRECTORY_SEPARATOR.$module)) {
					$loader->add($module, $this->modulesPath);
				}
			}

			// Se aqui não der erro é porque está tudo configurado
			// corretamente
			$result = $this->resolve($routes->module, $routes->controller, $routes->action);
		} catch (\Exception $e) {
			try {
				// Aqui já deu merda, o usuário verá a tela de erro
				// do módulo
				$result = $this->resolve($routes->module, 'error', 'not-found');
			} catch (\Exception $ex) {
				try {
					// Aqui piorou, o sistema chama um módulo padrão de erros.
					$result = $this->resolve('ModuleError', 'error', 'not-found', __DIR__.DIRECTORY_SEPARATOR.'library');
				} catch (\Exception $ex2) {
					exit('Confira a estrutura de arquivos, pois parece que algo está fora do padrão. https://github.com/braghimsistemas/framework/wiki/Instala%C3%A7%C3%A3o#estrutura-do-projeto');
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
	 * @param string $module Modulo do sistema (pasta). Primeiro parametro da URL
	 * @param string $controller Controlador - Classe.
	 * @param string $action Acao - Metodo da classe do controlador
	 * @param string $path Caminho.
	 * 
	 * @return \stdClass
	 * @throws \Exception
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
			throw new \Exception("Módulo '$module' não existe");
		}
		$result->moduleName = $module;

		// Define nome do controlador e acao
		$controllerName = ucfirst(strtolower($controller)) . "Controller";
		
		// Verifica se controlador existe
		$controllerFile = $path . "/$module/Controllers" . DIRECTORY_SEPARATOR . $controllerName . ".php";
		if (!file_exists($controllerFile)) {
			throw new \Exception("Controlador '$controllerFile' não existe ou não pode ser lido");
		}
		$result->controllerName = $controllerName;

		// Tentando acessar o controlador encontrado.
		$controllerNsp = $module . "\\Controllers\\$controllerName";
		if (!class_exists($controllerNsp)) {
			throw new \Exception("O sistema não conseguiu encontrar a classe deste controlador '$controllerNsp'");
		}
		$result->controller = new $controllerNsp();
		if (!$result->controller instanceof Braghim\MvcAbstractController) {
			throw new \Exception("Todo controlador deve ser uma instância de 'MvcAbstractController'");
		}

		// Define nome da acao
		$actionName = preg_replace("/\s+/", "", lcfirst(ucwords(preg_replace("/\-/", " ", $action)))) . "Action";

		// Verifica se ação existe no controlador
		if (!method_exists($result->controller, $actionName)) {
			throw new \Exception("Ação '$actionName' não existe no controlador '$controllerNsp'");
		}
		$result->actionName = $actionName;

		// Diretorio de views do modulo
		if (!is_dir("$path/$module/views/")) {
			throw new \Exception("Diretório de views não existe para o módulo '$module'");
		}
		$result->layoutPath = "$path/$module/views/";

		/**
		 * !! ATT !!
		 * Aqui não é necessário validar a existência do arquivo da view,
		 * pois quando a acao devolve um ajax não renderiza html
		 */
		$result->viewName = $action . ".phtml";
		$result->viewPath = "$path/$module/views/$controller";

		/**
		 * Cada módulo pode ter um
		 * @var Braghim\MvcAbstractController
		 */
		$abstractController = "$module\\Controllers\\AbstractController";
		if (!class_exists($abstractController)) {
			$abstractController = "Braghim\\MvcAbstractController";
		}
		$abstractController::$params = $result;
		
		return $result;
	}
	
	/**
	 * Habilita ou desabilita monitoramento de SQL de banco de dados.
	 * @param boolean $status
	 * @return BraghimSistemas
	 */
	public function setSqlMonitor($status = false) {
		\Braghim\Database::getInstance()->setMonitoring($status);
		return $this;
	}

	/**
	 * Roda efetivamente a aplicacao.
	 * @return void
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
