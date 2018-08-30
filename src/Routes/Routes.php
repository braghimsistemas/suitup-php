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
namespace SuitUp\Routes;

/**
 * Class Routes
 * @package SuitUp\Routes
 */
class Routes
{

  /**
   * O nome da rota fica no final da URL, os parametros antes.
   * 
   * Ex.: Rota = minha-rota.html = /param1/param2/param3/minha-rota.html
   * 
   * 'minha-rota.html' => array(
   *		'controller' => 'index',
   *		'action' => 'index',
   *		'type' => SuitUp\Routes::TYPE_REVERSE
   * )
   * 
   */
  const TYPE_REVERSE = 'reverse';

  /**
   * O nome da rota fica no início da URL e os parametros vem a seguir.
   * 
   * <b>Rotas lineares não aceitam URL, apenas nomes:</b>
   * 
   * CERTO
   * Rota: 'colaboradores.html'
   * URL : /modulo/colaboradores.html/perfis/param1/param2
   * Resultado: params = ['perfis', 'param1', 'param2']
   * 
   * ERRADO
   * Rota: 'colaboradores/perfis'
   * URL : /modulo/colaboradores/perfis/param1/param2
   * Resultado: Não funciona!
   * 
   * <b>Exemplos de rotas lineares</b>
   * return array(
   *		'dashboard' => array(
   *			'controller' => 'index',
   *			'action' => 'index',
   *		),
   *		'meu-perfil.html' => array(
   *			'controller' => 'perfil',
   *			'action' => 'index',
   *		),
   *		'colaboradores.mrc' => array(
   *			'controller' => 'besteira',
   *			'action' => 'blabla',
   *			'params' => array('id' => '2'),
   *		),
   * );
   */
  const TYPE_LINEAR = 'linear';

  /**
   * Pasta com os modulos do sistema.
   *
   * @var string 
   */
  public static $modulesPath;

  /**
   *	Singleton
   *	@var Routes 
   */
  private static $instance;

  /**
   * Retorna a instancia da classe.
   * 
   * @return Routes
   */
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
  private function __construct() {
    // Define a constante que encontra a raíz do projeto na URL
    $route = preg_replace("/\?(" . preg_quote(getenv('QUERY_STRING'), "/") . ")/", "", trim(getenv('REQUEST_URI'), '/'));

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
          if (is_dir(self::$modulesPath . "/Module" . ucfirst(strtolower($routeParts[0])))) {
            $this->moduleName = strtolower($routeParts[0]);
            $this->module = "Module" . ucfirst($this->moduleName);
          } else {
            $this->controller = $routeParts[0];
          }
          break;
        case 2:
          if (is_dir(self::$modulesPath . "/Module" . ucfirst(strtolower($routeParts[0])))) {

            $this->moduleName = strtolower($routeParts[0]);
            $this->module = "Module" . ucfirst($this->moduleName);

            $this->controller = $routeParts[1];
          } else {
            $this->controller = $routeParts[0];
            $this->action = $routeParts[1];
          }
          break;
        default:

          // Temos duas situacoes possiveis aqui:
          // 1. Existe uma rota diretamente no modulo default, o que dificulta o rastreamento
          // 2. Comportamento ja esperado.
          // Para quando houver uma rota no modulo default que vai receber muitos parametros, temos
          // que prioriza-la

          // Existe um arquivo de rota para o modulo default
          if (! file_exists("config/default.routes.php")) {
            // Se tiver mais que 3 parametros na URL
            // ex.: /module/controller/action/naoseipraqueisso
            // o ultimo sera ignorado a menos que tenha arquivo de rota configurado

            $this->moduleName = strtolower($routeParts[0]);
            $this->module = "Module" . ucfirst($this->moduleName);
          }

          $this->controller = $routeParts[1];
          $this->action = $routeParts[2];
          break;
      }

      // Verifica se o usuario definiu um
      // arquivo para rotas personalizadas
      // @todo: Usar caminho absoluto para incluir arquivos aqui.
      if (file_exists("config/{$this->moduleName}.routes.php")) {
        $this->custom = include "config/{$this->moduleName}.routes.php";

        // Remove o item que é o nome do modulo
        $itemModuleName = array_search($this->moduleName, $routeParts);
        if (false !== $itemModuleName) {
          unset($routeParts[$itemModuleName]);
          $routeParts = array_values($routeParts);
        }

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
   * @return \SuitUp\Routes\Routes
   */
  private function resolveLinearRoutes(array $routeParts) {
    // Caminho da rota
    $routeName = $routeParts[0];

    // Reseta array para pegar os parametros da url
    unset($routeParts[0]);
    $pathParams = array_values($routeParts);

    // Procura por esta rota na lista de rotas do usuario
    if (isset($this->custom[$routeName])) {
      $configs = $this->custom[$routeName];

      // Verifica se eh do tipo linear
      if (! isset($configs['type']) || ($configs['type'] == self::TYPE_LINEAR)) {

        // Sobrescreve os atributos da rota
        $this->controller = isset($configs['controller']) ? $configs['controller'] : $this->controller;
        $this->action = isset($configs['action']) ? $configs['action'] : $this->action;

        // Metodo para separar corretamente os parametros
        $this->resolveParams(isset($configs['params']) ? $configs['params'] : array(), $pathParams);
      }
    } else {

      // REGEX
      // Pode ser que esta rota seja expressão regular
      foreach ($this->custom as $route => $configs) {
        if (preg_match("/^\/.+\/$/", $route) && preg_match($route, $routeName)) {

          // Verifica se eh do tipo linear
          if (! isset($configs['type']) || ($configs['type'] == self::TYPE_LINEAR)) {

            // Sobrescreve os atributos da rota
            $this->controller = isset($configs['controller']) ? $configs['controller'] : $this->controller;
            $this->action = isset($configs['action']) ? $configs['action'] : $this->action;

            // Metodo para separar corretamente os parametros
            $this->resolveParams(isset($configs['params']) ? $configs['params'] : array(), $pathParams);
          }
          break;
        }
      }
    }

    return $this;
  }

  /**
   * Procura por rotas definidas pelo usuario que sao do tipo reverso.
   * 
   * @param string $routeString
   * @return Routes
   */
  private function resolveReverseRoutes($routeString) {
    // Procura a rota no arquivo
    foreach ($this->custom as $routeName => $configs) {

      // REGEX route
      if (preg_match("/^\/.+\/$/", $routeName)) {

        // Split array parts
        $regRouteParts = explode("/", $routeString);
        $regRouteValue = array_pop($regRouteParts); // Remove route name

        // Add route value, because if it's a regex may needed to be used for something
        $configs['params']['routeMatch'] = $regRouteValue;

        // The regex matches to the URL AND is reverse?
        if (preg_match($routeName, $regRouteValue) && isset($configs['type']) && ($configs['type'] == self::TYPE_REVERSE)) {

          // Define controlador e acao.
          $this->controller = isset($configs['controller']) ? $configs['controller'] : $this->controller;
          $this->action = isset($configs['action']) ? $configs['action'] : $this->action;

          // Metodo para separar corretamente os parametros
          $this->resolveParams(isset($configs['params']) ? $configs['params'] : array(), $regRouteParts);
          break;
        }
      } else {

        // Prepara para verificar se a rota eh correta atraves de regex
        $routeName = preg_replace("/\//", "\/", $routeName);
        if (preg_match("/$routeName$/", $routeString) && isset($configs['type']) && ($configs['type'] == self::TYPE_REVERSE)) {

          // Define controlador e acao.
          $this->controller = isset($configs['controller']) ? $configs['controller'] : $this->controller;
          $this->action = isset($configs['action']) ? $configs['action'] : $this->action;

          // Remove da URL o nome do módulo e a rota em si.
          $routeString = trim(preg_replace(array(
            "/^{$this->moduleName}/",
            "/$routeName$/"
          ), array(
            '',
            ''
          ), $routeString), '/');

          // Separa as partes em array
          $routeParts = explode("/", $routeString);

          // Metodo para separar corretamente os parametros
          $this->resolveParams(isset($configs['params']) ? $configs['params'] : array(), $routeParts);
          break;
        }
      }
    }

    return $this;
  }

  /**
   * Traduz os parametros da URL com os parametros esperados pela rota customizada do usuario.
   * 
   * @param array $routeParams
   * @param array $urlParams
   * @return Routes
   */
  private function resolveParams(array $routeParams, array $urlParams) {
    if ($routeParams) {

      // Organiza parametros do arquivo de rotas
      $params = array();
      foreach ($routeParams as $rKey => $rParam) {
        $params[] = array(
          'key' => $rKey,
          'value' => $rParam
        );
      }

      // Se houverem parametros para traduzir..
      foreach ($urlParams as $key => $param) {

        if (! isset($params[$key])) {
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
        if (! preg_match("/^\/.+\/$/", $param['value'])) {
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
   * Returns the custom routes list.
   *
   * @return array
   */
  public function getCustom() {
    return $this->custom;
  }

  /**
   * Retorna os parametros.
   * 
   * @return array
   */
  public function getParams() {
    return $this->params;
  }

  /**
   * Retorna nome do modulo atual.
   * 
   * @return string
   */
  public function getModuleName() {
    return $this->moduleName;
  }

  /**
   * Retorna o nome do controlador atual.
   * 
   * @return string
   */
  public function getControllerName() {
    return $this->controller;
  }

  /**
   * Retorna o nome da acao atual.
   * 
   * @return string
   */
  public function getActionName() {
    return $this->action;
  }
}
