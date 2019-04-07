<?php
/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2019 Braghim Sistemas
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
 * FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

declare(strict_types=1);

namespace Suitup\Router;


use Suitup\Storage\Config;

/**
 * Class Routes
 *
 * @package Router
 */
class Routes
{
  /**
   * In the reverse routes the key name that determines the route came in the END of URL.
   * Eg.: Rota = my-route.html = /param1/param2/param3/my-route.html
   *
   * 'my-route.html' => array(
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
   * The route will match exactly what getenv('REQUEST_URI') returns
   */
  const TYPE_LITERAL = 'literal';

  /**
   * @var string
   */
  private $module;

  /**
   * @var string
   */
  private $controller = 'index';

  /**
   * @var string
   */
  private $action = 'index';

  /**
   * @var array
   */
  private $params = array();

  public function __construct() {

    // Default module name
    $this->module = Config::getInstance()->getModuleDefault();
  }

  /**
   * @param string $routePath
   * @param string|null $modulesPath
   * @return $this
   */
  public function setByURI(string $routePath, string $modulesPath = null) {

    $config = \Suitup\Storage\Config::getInstance();

    if ($routePath) {

      if (!$modulesPath) {
        // Get modules path defined on Configs
        $modulesPath = $config->getModulesPath();
      }

      // controller OR
      // module OR
      // controller/action OR
      // module/controller OR
      // module/controller/action
      $routeParts = explode('/', $routePath);

      // Prefix to the module names
      $modulesPathPrefix = $modulesPath . "/" . $config->getModulePrefix();

      // By the quantity we know where controller is
      switch (count($routeParts)) {
        case 1:
          if (is_dir($modulesPathPrefix . ucfirst(strtolower($routeParts[0])))) {
            $this->module = $routeParts[0];
          } else {
            $this->controller = $routeParts[0];
          }
          break;
        case 2:
          if (is_dir($modulesPathPrefix . ucfirst(strtolower($routeParts[0])))) {
            $this->module = $routeParts[0];
            $this->controller = $routeParts[1];
          } else {
            $this->controller = $routeParts[0];
            $this->action = $routeParts[1];
          }
          break;
        default:
          if (is_dir($modulesPathPrefix . ucfirst(strtolower($routeParts[0])))) {
            /**
             * Here we got 3 or more params from URL
             *
             * If the first one is the name of some module folder
             * we have no choice but point the system to that.
             */
            $this->module = strtolower($routeParts[0]);
            $this->controller = $routeParts[1];
            $this->action = $routeParts[2];

            unset($routeParts[0]);
            unset($routeParts[1]);
            unset($routeParts[2]);

          } else {
            /** Module keeps being default */
            $this->controller = $routeParts[0];
            $this->action = $routeParts[1];

            unset($routeParts[0]);
            unset($routeParts[1]);
          }

          if ($routeParts) {

            // Loop under remain values with reset number keys
            // It will last as params
            $last = '';
            foreach (array_values($routeParts) as $i => $item) {
              if ($i%2==0) {
                $this->params[$item] = null;
              } else {
                $this->params[$last] = $item;
              }
              $last = $item;
            }
          }
          break;
      } // End switch

    }

    // Merge GET params from URL
    $this->params = array_merge($this->params, (array) filter_input_array(INPUT_GET));

    return $this;
  }

  /** GETTERS and SETTERS */

  /**
   * @return string
   */
  public function getModule(): string {
    return ucfirst(strtolower($this->module));
  }

  /**
   * @return string
   */
  public function getModuleName(): string {
    return Config::getInstance()->getModulePrefix().$this->getModule();
  }

  /**
   * @param string $module
   * @return Routes
   */
  public function setModule(string $module): Routes {
    $this->module = $module;
    return $this;
  }

  /**
   * @return string
   */
  public function getController(): string {
    return $this->controller;
  }

  /**
   * @return string
   */
  public function getControllerName(): string {
    $controller = lcfirst(ucwords(preg_replace("/\-/", " ", strtolower(urldecode($this->getController())))));
    return ucfirst(preg_replace("/\s+/", "", $controller)).'Controller';
  }

  /**
   * @param string $controller
   * @return Routes
   */
  public function setController(string $controller): Routes {
    $this->controller = $controller;
    return $this;
  }

  /**
   * @return string
   */
  public function getAction(): string {
    return $this->action;
  }

  /**
   * @return string
   */
  public function getActionName(): string {
    $action = lcfirst(ucwords(preg_replace("/\-/", " ", strtolower(urldecode($this->getAction())))));
    return preg_replace("/\s+/", "", $action).'Action';
  }

  /**
   * @param string $action
   * @return Routes
   */
  public function setAction(string $action): Routes {
    $this->action = $action;
    return $this;
  }

  /**
   * @return array
   */
  public function getParams(): array {
    return $this->params;
  }

  /**
   * @param array $params
   * @return Routes
   */
  public function setParams(array $params): Routes {
    $this->params = $params;
    return $this;
  }

  /** GETTERS and SETTERS */
}
