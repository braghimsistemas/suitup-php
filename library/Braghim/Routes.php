<?php
namespace Braghim;

/**
 * Description of Routes
 *
 * @author pedepano
 */
class Routes
{
	/**
	 * O nome da rota fica no final da URL, os parametros veem antes.
	 */
	const TYPE_REVERSE = 'reverse';
	
	/**
	 * O nome da rota fica no início da URL e os parametros vem a seguir.
	 */
	const TYPE_LINEAR = 'linear';
	
	/**
	 *	Singleton
	 *	@var type 
	 */
	private static $instance;
	public static function getInstance() {
		if (self::$instance == null) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	private function __clone() {}
	
	// Valores padrão de URL
	public $module = 'ModuleDefault';
	private $moduleName = 'default';
	public $controller = 'index';
	public $action = 'index';
	public $params = array();
	
	/**
	 * Rotas definidas pelo usuario.
	 *
	 * @var array
	 */
	private $custom = array();
	
	/**
	 * Define inicialmente as rotas
	 */
	private function __construct()
	{
		// Define a constante que encontra a raíz do projeto na URL
		$route = preg_replace("/\?(" . preg_quote($_SERVER['QUERY_STRING'], "/") . ")/", "", trim($_SERVER['REQUEST_URI'], '/'));

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
					if (is_dir("mvc/Module" . ucfirst(strtolower($routeParts[0])))) {
						$this->moduleName = strtolower($routeParts[0]);
						$this->module = "Module" . ucfirst($this->moduleName);
					} else {
						$this->controller = $routeParts[0];
					}
					break;
				case 2:
					if (is_dir("mvc/Module" . ucfirst(strtolower($routeParts[0])))) {
						
						$this->moduleName = strtolower($routeParts[0]);
						$this->module = "Module" . ucfirst($this->moduleName);
						
						$this->controller = $routeParts[1];
					} else {
						$this->controller = $routeParts[0];
						$this->action = $routeParts[1];
					}
					break;
				default:
					// Se tiver mais que 3 parametros na URL
					// ex.: /module/controller/action/naoseipraqueisso
					// o ultimo sera ignorado a menos que tenha arquivo de rota configurado
					
					$this->moduleName = strtolower($routeParts[0]);
					$this->module = "Module" . ucfirst($this->moduleName);
					
					$this->controller = $routeParts[1];
					$this->action = $routeParts[2];
					break;
			}
			
			// Verifica se o usuario definiu um
			// arquivo para rotas personalizadas
			if (file_exists("config/{$this->moduleName}.routes.php")) {
				$this->custom = include "config/{$this->moduleName}.routes.php";

				// Remove o item que é o nome do modulo
				unset($routeParts[array_search($this->moduleName, $routeParts)]);
				$routeParts = array_values($routeParts);
				
				// Procura por rotas do tipo linear
				if ($routeParts) {
					$this->resolveLinearRoutes($routeParts);
				}
				
				// Procura por rotas do tipo reverso
				$this->resolveReverseRoutes($route);
			}
		}
	}
	
	/**
	 * Procura por rotas que sejam lineares, ou seja, o nome fica no inicio da rota e os parametros no final.
	 * 
	 * @param array $routeParts
	 * @return \Braghim\Routes
	 */
	private function resolveLinearRoutes(array $routeParts)
	{
		// Caminho da rota
		$routeName = $routeParts[0];

		// Reseta array para pegar os parametros da url
		unset($routeParts[0]);
		$pathParams = array_values($routeParts);
		
		// Procura por esta rota na lista de rotas do usuario
		if (isset($this->custom[$routeName])) {
			$configs = $this->custom[$routeName];
			
			// Verifica se eh do tipo linear
			if (!isset($configs['type']) || ($configs['type'] == self::TYPE_LINEAR)) {
				
				// Sobrescreve os atributos da rota
				$this->controller = isset($configs['controller']) ? $configs['controller'] : $this->controller;
				$this->action = isset($configs['action']) ? $configs['action'] : $this->action;


				// Metodo para separar corretamente os parametros
				$this->resolveParams(
					isset($configs['params']) ? $configs['params'] : array(),
					$pathParams
				);
			}
		}
		
		return $this;
	}
	
	/**
	 * Procura por rotas definidas pelo usuario que sao do tipo reverso.
	 * 
	 * @param string $routeString
	 */
	private function resolveReverseRoutes($routeString)
	{
		// Procura a rota no arquivo
		foreach ($this->custom as $routeName => $configs) {
			if (preg_match("/$routeName$/", $routeString) && isset($configs['type']) && ($configs['type'] == self::TYPE_REVERSE)) {
				
				// Define controlador e acao.
				$this->controller = isset($configs['controller']) ? $configs['controller'] : $this->controller;
				$this->action = isset($configs['action']) ? $configs['action'] : $this->action;
				
				// Remove da URL o nome do módulo e a rota em si.
				$routeString = trim(preg_replace(
					array("/^{$this->moduleName}/", "/$routeName$/"),
					array('', ''),
					$routeString
				), '/');
				
				// Separa as partes em array
				$routeParts = explode("/", $routeString);
				
				// Metodo para separar corretamente os parametros
				$this->resolveParams(
					isset($configs['params']) ? $configs['params'] : array(),
					$routeParts
				);
				break;
			}
		}
		
		return $this;
	}
	
	/**
	 * Traduz os parametros da URL com os parametros esperados pela rota customizada do usuario.
	 * 
	 * @param array $routeParams
	 * @param array $urlParams
	 */
	private function resolveParams(array $routeParams, array $urlParams)
	{
		if ($routeParams) {
			
			// Organiza parametros do arquivo de rotas
			$params = array();
			foreach($routeParams as $rKey => $rParam) {
				$params[] = array('key' => $rKey, 'value' => $rParam);
			}
			
			// Se houverem parametros para traduzir..
			foreach ($urlParams as $key => $param) {

				if (!isset($params[$key])) {
					$this->params[] = $param;
					continue;
				}
				
				// Eh expressao regular?
				if (preg_match("/^\/.+\/$/", $params[$key]['value'])) {
					$this->params[$params[$key]['key']] = preg_replace($params[$key]['value'], "", $param);

				// Apenas tem valor default
				} else {
					$this->params[$params[$key]['key']] = $param;
				}
			}
			
			// Verifica os que existem, mas nao foram passados
			foreach ($params as $param) {
				if (isset($this->params[$param['key']])) {
					continue;
				}

				// Nao eh um parametro que valida ER.
				if (!preg_match("/^\/.+\/$/", $param['value'])) {
					$this->params[$param['key']] = $param['value'];
				} else {
					$this->params[$param['key']] = null;
				}
			}
			
		// A rota não espera nenhum parametro,
		// mas se foram passados eles poderao
		// ser recuperados.
		} else {
			$this->params = $urlParams;
		}
		
		return $this;
	}
	
	/**
	 * Retorna os parametros.
	 * 
	 * @return type
	 */
	public function getParams() {
		return $this->params;
	}
}
