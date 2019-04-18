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

namespace SuitUp\Mvc;

use Exception;
use SuitUp\Exception\StructureException;

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
   * @var FrontController
   */
  private $frontController;

  /**
   * @var string
   */
  private $module = 'default';

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

  /**
   * Routes constructor.
   * @param FrontController $frontController
   */
  public function __construct(FrontController $frontController) {

    // Dependency injection Design Pattern
    $this->frontController = $frontController;
  }

  /**
   * After receive the $requestURI this method will calculate which must
   * to be the current route called by URL.
   *
   * @param string|null $requestURI If not given it will be the value from getenv('REQUEST_URI')
   * @return Routes
   * @throws StructureException
   */
  public function setupRoutes(string $requestURI = null): Routes {

    if (!$requestURI) {
      $requestURI = getenv('REQUEST_URI');
    }

    // From URI remove slash from start and end. So remove everything after ?
    $uri = trim(preg_replace('/\?.+/', '', $requestURI), '/');

    // From basePath remove slash from start and end. So prepare it as a regex string
    $basePathRegExp = '/^('.preg_quote(trim($this->frontController->getBasePath(), '/'), '/').')/';

    // Remove from URI blank spaces and the BasePath
    $route = trim(preg_replace($basePathRegExp, '', preg_replace("/\s+/", '-', urldecode($uri))), '/');

    // First of all we set by URI that is useful always
    $routeResidue = $this->setByURI($route);

    // Setup FrontController with natural parameters
    $this->frontController->setModuleName($this->getModule());
    $this->frontController->setControllerName($this->getController());
    $this->frontController->setActionName($this->getAction());

    // Now we can set the module path
    $this->frontController->setModulePath($this->frontController->getModulesPath().'/'.$this->frontController->getModuleName());

    $routesFilename = $this->frontController->getRoutesFile();

    if ($routesFilename && file_exists($routesFilename)) {

      // Loop under defined routes
      $found = false;

      // Get from file the routes config
      $routesFile = include($routesFilename);

      // If the return of the file is an array
      if (is_array($routesFile)) {
        foreach ($routesFile as $routeName => $routeItem) {

          if ($found) {
            break;
          }

          if (!is_array($routeItem)) {
            continue;
          }

          // Store the route name
          $routeItem['name'] = is_string($routeName) ? trim($routeName, '/') : $routeName;

          // Define the route type
          $routeItem['type'] = $routeItem['type'] ?? Routes::TYPE_LINEAR;

          // We will search for routes by type
          switch ($routeItem['type']) {
            case Routes::TYPE_LITERAL:

              // If there's no parameter
              if (!isset($routeItem['url_list'])) {
                throw new StructureException('Every literal route must to implement the list of valid URL. It can be a closure function or an array list');
              }

              // We will check for the list if route match to that
              if (isClosure($routeItem['url_list'])) {
                $funcName = $routeItem['url_list'];
                $urlList = (array)$funcName();

              } else if (is_array($routeItem['url_list'])) {
                $urlList = $routeItem['url_list'];

              } else {
                // Here we try to force
                $urlList = (array)$routeItem['url_list'];
              }

              // Check if route is exactly equal to some item of the list
              foreach ($urlList as $item) {
                if (trim($route, '/') === trim($item, '/')) {

                  // Some details...
                  $routeItem['name'] = trim($item, '/');

                  // It's a literal route, there is no residue
                  $routeResidue = '';

                  // Set as found one!
                  $found = $routeItem;

                  break;
                }
              }

              break;
            case Routes::TYPE_REVERSE:
              // Check true if $routeName is equal to the last item from $route
              if ($routeName == preg_replace("/^.+\//", '', $route)) {
                $found = $routeItem;
              }
              break;
            default:
              // TYPE LINEAR
              // Check true if the $routeName is equal to the first item after module name
              if ($routeName == $this->getController()) {
                $found = $routeItem;
              }
          }
        }
      }

      if ($found) {

        // Set controller and action
        $this->setController($found['controller'] ?? $this->getController());
        $this->setAction($found['action'] ?? $this->getAction());

        // Remove from route string it's name
        $route = trim(preg_replace('/^('.preg_quote($found['name'], '/').')/', '', $route), '/');

        // Resolve it's params
        if (isset($found['params'])) {
          $this->setParams($this->resolveParams($found['params'], explode('/', $route)));
        }
      }

      // Setup FrontController with found parameters
      $this->frontController->setModuleName($this->getModule());
      $this->frontController->setControllerName($this->getController());
      $this->frontController->setActionName($this->getAction());

      // Now we can set the module path
      $this->frontController->setModulePath($this->frontController->getModulesPath().'/'.$this->frontController->getModuleName());
    }

    // If was not set params from pre defined routes we will do it
    // with residues of route
    if (!$this->params && $routeResidue) {
      $this->setParams($this->arrayToParams(explode('/', $routeResidue)));
    }

    // Merge GET params from URL
    $this->params = array_merge($this->getParams(), (array) filter_input_array(INPUT_GET));
    $this->frontController->setParams($this->getParams());

    return $this;
  }

  /**
   * @param array $routeParams
   * @param array $urlParams
   * @return array
   */
  private function resolveParams(array $routeParams, array $urlParams): array {
    $result = array();

    // At the end we will use residues
    // to set extra parameters
    $residues = array();

    if ($routeParams) {

      // Store route params organized
      $params = array();
      foreach ($routeParams as $rKey => $rParam) {
        if (is_string($rKey)) {
          $params[] = array('key' => $rKey, 'value' => $rParam);
        } else {
          $params[] = array('key' => $rParam, 'value' => '');
        }
      }

      // If there's param to translate
      foreach ($urlParams as $key => $param) {

        // More params in URL than expected by route file
        if (!isset($params[$key])) {
          $residues[] = $param;
          continue;
        }

        // Regular expression?
        if (preg_match("/^\/.+\/$/", (string) $params[$key]['value'])) {
          $result[$params[$key]['key']] = preg_replace($params[$key]['value'], "", $param);
        } else {
          $result[$params[$key]['key']] = $param;
        }
      }

      // Check that ones which exists, but was not feed
      foreach ($params as $param) {

        // If it's already set, skip it
        if (isset($result[$param['key']])) {
          continue;
        }

        // Isn't a Regex param
        if (! preg_match("/^\/.+\/$/", $param['value'])) {
          $result[$param['key']] = $param['value'];
        } else {
          $result[$param['key']] = '';
        }
      }
    } else {
      // Route doesn't expect any param, but exists
      $residues = $urlParams;
    }

    // Merge with residues params
    $result = array_merge($result, $this->arrayToParams($residues));

    return $result;
  }

  /**
   * @param string $routePath
   * @return string
   */
  public function setByURI(string $routePath): string {

    // Pieces of route with dashes instead of special chars
    $routeParts = array_map('toDashCase', explode('/', $routePath));

    // Pieces of route as original
    $residues = explode('/', $routePath);

    if ($routePath) {

      // controller OR
      // module OR
      // controller/action OR
      // module/controller OR
      // module/controller/action

      // Prefix to the module names
      $modulesPathPrefix = $this->frontController->getModulesPath() . "/" . $this->frontController->getModulePrefix();

      // By the quantity we know where controller is
      switch (count($routeParts)) {
        case 1:
          if (is_dir($modulesPathPrefix . toCamelCase($routeParts[0], true))) {
            $this->module = $routeParts[0];
          } else {
            $this->controller = $routeParts[0];
          }

          // Remove from residue
          unset($residues[0]);

          break;
        case 2:
          if (is_dir($modulesPathPrefix . toCamelCase($routeParts[0], true))) {
            $this->module = $routeParts[0];
            $this->controller = $routeParts[1];
          } else {
            $this->controller = $routeParts[0];
            $this->action = $routeParts[1];
          }

          // Remove found from residues
          unset($residues[0]);
          unset($residues[1]);

          break;
        default:
          if (is_dir($modulesPathPrefix . toCamelCase($routeParts[0], true))) {
            /**
             * Here we got 3 or more params from URL
             *
             * If the first one is the name of some module folder
             * we have no choice but point the system to that.
             */
            $this->module = $routeParts[0];
            $this->controller = $routeParts[1];
            $this->action = $routeParts[2];

            // Remove found from residues
            unset($residues[0]);
            unset($residues[1]);
            unset($residues[2]);

          } else {
            /** Module keeps being default */
            $this->controller = $routeParts[0];
            $this->action = $routeParts[1];

            // Remove found from residues
            unset($residues[0]);
            unset($residues[1]);
          }
      } // End switch

    }

    return implode('/', $residues);
  }

  /**
   * Get a list of values and store them to associative array list.
   *
   * @param $arrayOfValues
   * @return array
   */
  public function arrayToParams($arrayOfValues): array {
    $result = array();
    $last = '';
    foreach ($arrayOfValues as $i => $item) {
      if (is_string($item) && $item) {
        if ($i%2==0) {
          $result[$item] = '';
        } else {
          $result[$last] = $item;
        }
        $last = $item;
      }
    }
    return $result;
  }

  /** GETTERS and SETTERS */

  /**
   * @return string
   */
  public function getModule(): ?string {
    return $this->module;
  }

  /**
   * @param string $module
   * @return Routes
   */
  public function setModule(string $module): Routes {
    $this->module = strtolower($module);
    return $this;
  }

  /**
   * @return string
   */
  public function getController(): string {
    return $this->controller;
  }

  /**
   * @param string $controller
   * @return Routes
   */
  public function setController(string $controller): Routes {
    $this->controller = strtolower($controller);
    return $this;
  }

  /**
   * @return string
   */
  public function getAction(): string {
    return $this->action;
  }

  /**
   * @param string $action
   * @return Routes
   */
  public function setAction(string $action): Routes {
    $this->action = strtolower($action);
    return $this;
  }

  /**
   * @return array
   */
  public function getParams(): array {
    return $this->params;
  }

  /**
   * Get a specific param from route
   *
   * @param string $name
   * @param bool $default
   * @return bool|mixed
   */
  public function getParam(string $name, $default = false) {

    $result = $default;
    if (isset($this->params[$name])) {
      $result = $this->params[$name];
    }
    return $result;
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
