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

use ReflectionClass;
use SuitUp\Exception\StructureException;

class FrontController
{
  /**
   * Path to <b>all</b> modules
   *
   * @var string
   */
  private $modulesPath;

  /**
   * @var string
   */
  private $layoutName = 'layout.phtml';

  /**
   * @var string
   */
  private $layoutSuffix = '.phtml';

  /**
   * @var string
   */
  private $layoutPath = 'views';

  /**
   * @var string
   */
  private $viewsPath = 'views';

  /**
   * @var string
   */
  private $basePath;

  /**
   * Path to the <b>current</b> module
   *
   * @var string
   */
  private $modulePath;

  /**
   * @var string
   */
  private $modulePrefix = 'Module';

  /**
   * @var string
   */
  private $module = 'default';

  /**
   * @var string
   */
  private $moduleName = 'ModuleDefault';

  /**
   * @var string
   */
  private $controllersPath = 'Controllers';

  /**
   * @var string
   */
  private $controllerName = 'IndexController';

  /**
   * @var string
   */
  private $controller = 'index';

  /**
   * @var string
   */
  private $viewSuffix = '.phtml';

  /**
   * @var string
   */
  private $actionName = 'indexAction';

  /**
   * @var string
   */
  private $action = 'index';

  /**
   * @var bool
   */
  private $sqlMonitor = false;

  /**
   * @var string
   */
  private $formPath = '/Form';

  /**
   * @var string
   */
  private $businessPath = '/Model';

  /**
   * @var string
   */
  private $gatewayPath = '/Model/Gateway';

  /**
   * @var string
   */
  private $configsPath = '/config';

  /**
   * Every file with the list of routes must to end with this
   * name.
   *
   * @var string
   */
  private $routesFileSuffix = '.routes.php';

  /**
   * @var string
   */
  private $routesFile = null;

  /**
   * @var array
   */
  private $params = array();

  /**
   * @var string
   */
  private $logsPath;

  /**
   * FrontController constructor.
   * @param string $modulesPath
   */
  public function __construct(string $modulesPath = '.') {

    // Setup default value to the modules path.
    // It will relate modules path to the 'chdir' function
    // which must to be called in the index.php file
    $this->modulesPath = realpath($modulesPath);

    $this->setBasePath();
  }

  /**
   * Collect all information from here to resolve the name of view file.
   *
   * @return string
   */
  public function resolveViewFilename(): string {

    $viewFile = $this->getModulePath();
    $viewFile .= '/'.$this->getViewsPath();
    $viewFile .= '/'.$this->getController();
    $viewFile .= '/'.$this->getAction();
    $viewFile .= $this->getViewSuffix();

    return $viewFile;
  }

  /**
   * Collect all information from here to resolve the name of layout file.
   *
   * @return string
   */
  public function resolveLayoutFilename(): string {

    $layoutFilename = $this->getModulePath();
    $layoutFilename .= '/'.$this->getLayoutPath();
    $layoutFilename .= '/'.$this->getLayoutName();

    return $layoutFilename;
  }

  /**
   * @return string
   */
  public function getModulesPath(): string {
    return $this->modulesPath;
  }

  /**
   * Point to the framework where is the root folder to the modules.
   *
   * @param string $modulesPath
   * @return FrontController
   * @throws StructureException
   */
  public function setModulesPath(string $modulesPath): FrontController {

    if (realpath($modulesPath) === false) {
      throw new StructureException("The path to the modules does not exists: '$modulesPath'");
    }

    $this->modulesPath = rtrim(realpath($modulesPath), '/');
    return $this;
  }

  /**
   * @return string
   */
  public function getLayoutName(): string {
    return $this->layoutName;
  }

  /**
   * Name for the <b>file</b> to be rendered as layout.
   * If the file name you are setting up have the extension
   * file so the layout suffix extension file will be ignored.
   *
   * @param string $layoutName
   * @return FrontController
   */
  public function setLayoutName(string $layoutName): FrontController {
    $this->layoutName = (preg_match('/\./', $layoutName) === 0) ? $layoutName.$this->getLayoutSuffix() : $layoutName;
    return $this;
  }

  /**
   * @return string
   */
  public function getLayoutSuffix(): string {
    return $this->layoutSuffix;
  }

  /**
   * @param string $layoutSuffix
   * @return FrontController
   */
  public function setLayoutSuffix(string $layoutSuffix): FrontController {
    $this->layoutSuffix = '.'.trim($layoutSuffix, '.');
    return $this;
  }

  /**
   * @return string
   */
  public function getLayoutPath(): string {
    return $this->layoutPath;
  }

  /**
   * @param string $layoutPath
   * @return FrontController
   */
  public function setLayoutPath(string $layoutPath): FrontController {
    $this->layoutPath = ltrim($layoutPath, '/');
    return $this;
  }

  /**
   * @return string
   */
  public function getViewsPath(): string {
    return $this->viewsPath;
  }

  /**
   * @param string $viewsPath
   * @return FrontController
   */
  public function setViewsPath(string $viewsPath): FrontController {
    $this->viewsPath = ltrim($viewsPath, '/');
    return $this;
  }

  /**
   * @param string $append
   * @return string
   */
  public function getBasePath(string $append = null): string {
    if ($append) {
      return $this->basePath."/$append";
    }
    return $this->basePath;
  }

  /**
   * @param string $basePath
   * @return FrontController
   */
  public function setBasePath(string $basePath = null): FrontController {

    if (!is_null($basePath)) {
      $this->basePath = ($basePath === '') ? '' : '/'.trim($basePath, '/');
    } else {

      // Remove 'DocRoot' from the path to the project root
      $pathFromRoot = trim(str_replace(getenv('DOCUMENT_ROOT'), '', realpath('.')), DIRECTORY_SEPARATOR);

      // Choose the basePath
      $this->basePath = $pathFromRoot ? "/$pathFromRoot" : '';
    }
    return $this;
  }

  /**
   * @return string
   */
  public function getModuleName(): string {
    return $this->moduleName;
  }

  /**
   * @param string $moduleName
   * @return FrontController
   */
  public function setModuleName(string $moduleName): FrontController {

    // Does not make sense change the module name without change the module itself
    $this->setModule(toDashCase($moduleName));

    $this->moduleName = $this->getModulePrefix().toCamelCase($moduleName, true);
    return $this;
  }

  /**
   * @return string
   */
  public function getModulePath(): string {
    return $this->modulePath ?? $this->getModulesPath().'/'.$this->getModuleName();
  }

  /**
   * @param string $modulePath
   * @return FrontController
   * @throws StructureException
   */
  public function setModulePath(string $modulePath): FrontController {

    if (realpath($modulePath) === false) {
      throw new StructureException("The path does not exists: '$modulePath'");
    }

    $this->modulePath = realpath($modulePath);
    return $this;
  }

  /**
   * @return string
   */
  public function getModulePrefix(): string {
    return $this->modulePrefix;
  }

  /**
   * @param string $modulePrefix
   * @return FrontController
   */
  public function setModulePrefix(string $modulePrefix): FrontController {
    $this->modulePrefix = toCamelCase($modulePrefix, true);
    return $this;
  }

  /**
   * @return string
   */
  public function getModule(): string {
    return $this->module;
  }

  /**
   * @param string $module
   * @return FrontController
   */
  public function setModule(string $module): FrontController {
    $this->module = toDashCase($module);
    return $this;
  }

  /**
   * @return string
   */
  public function getControllerName(): string {
    return $this->controllerName;
  }

  /**
   * @param string $controllerName
   * @return FrontController
   */
  public function setControllerName(string $controllerName): FrontController {

    // Does not make sense to change the controller name without change the controller itself
    $this->setController(toDashCase($controllerName));

    $this->controllerName = toCamelCase($controllerName, true).'Controller';
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
   * @return FrontController
   */
  public function setController(string $controller): FrontController {
    $this->controller = $controller;
    return $this;
  }

  /**
   * @return string
   */
  public function getControllersPath(): string {
    return $this->controllersPath;
  }

  /**
   * @param string $controllersPath
   * @return FrontController
   */
  public function setControllersPath(string $controllersPath): FrontController {
    $this->controllersPath = trim($controllersPath, '/');
    return $this;
  }

  /**
   * @return string
   */
  public function getViewSuffix(): string {
    return $this->viewSuffix;
  }

  /**
   * @param string $viewSuffix
   * @return FrontController
   */
  public function setViewSuffix(string $viewSuffix): FrontController {
    $this->viewSuffix = '.'.trim($viewSuffix, '.');
    return $this;
  }

  /**
   * @return string
   */
  public function getActionName(): string {
    return $this->actionName;
  }

  /**
   * @param string $actionName
   * @return FrontController
   */
  public function setActionName(string $actionName): FrontController {

    // Does not make sense to change the action name without change the action itself
    $this->setAction(toDashCase($actionName));

    $this->actionName = toCamelCase($actionName).'Action';
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
   * @return FrontController
   */
  public function setAction(string $action): FrontController {
    $this->action = toDashCase($action);
    return $this;
  }

  /**
   * @return bool
   */
  public function isSqlMonitor(): bool {
    return $this->sqlMonitor;
  }

  /**
   * @param bool $sqlMonitor
   * @return FrontController
   */
  public function setSqlMonitor(bool $sqlMonitor): FrontController {
    $this->sqlMonitor = $sqlMonitor;
    return $this;
  }

  /**
   * @return string
   */
  public function getFormPath(): string {
    return $this->formPath;
  }

  /**
   * @param string $formPath
   * @return FrontController
   */
  public function setFormPath(string $formPath): FrontController {
    $this->formPath = '/'.trim($formPath, '/');
    return $this;
  }

  /**
   * @return string
   */
  public function getBusinessPath(): string {
    return $this->businessPath;
  }

  /**
   * @param string $businessPath
   * @return FrontController
   */
  public function setBusinessPath(string $businessPath): FrontController {
    $this->businessPath = '/'.trim($businessPath, '/');
    return $this;
  }

  /**
   * @return string
   */
  public function getGatewayPath(): string {
    return $this->gatewayPath;
  }

  /**
   * @param string $gatewayPath
   * @return FrontController
   */
  public function setGatewayPath(string $gatewayPath): FrontController {
    $this->gatewayPath = $this->getBusinessPath().'/'.trim($gatewayPath, '/');
    return $this;
  }

  /**
   * @return string
   */
  public function getConfigsPath(): string {
    return $this->configsPath;
  }

  /**
   * @param string $configsPath
   * @return FrontController
   */
  public function setConfigsPath(string $configsPath): FrontController {
    $this->configsPath = '/'.ltrim($configsPath, '/');
    return $this;
  }

  /**
   * @return string
   */
  public function getRoutesFileSuffix(): string {
    return $this->routesFileSuffix;
  }

  /**
   * @param string $routesFileSuffix
   * @return FrontController
   */
  public function setRoutesFileSuffix(string $routesFileSuffix): FrontController {
    $this->routesFileSuffix = $routesFileSuffix;
    return $this;
  }

  /**
   * @return string
   */
  public function getRoutesFile(): ?string {

    if (!$this->routesFile) {

      $filename = realpath($this->getModulesPath().'/..'.$this->getConfigsPath()).'/'.$this->getModule().$this->getRoutesFileSuffix();
      if (file_exists($filename) && is_readable($filename)) {
        $this->routesFile = $filename;
      }
    }
    return $this->routesFile;
  }

  /**
   * @param string $routesFile
   * @throws StructureException
   * @return FrontController
   */
  public function setRoutesFile(string $routesFile): FrontController {

    // Validate file
    if (!file_exists($routesFile)) {
      throw new StructureException("File to feed the route not found: '$routesFile'");
    }

    // Return the realpath to the file
    $info = pathinfo($routesFile);
    $this->routesFile = $info['dirname'].'/'.$info['basename'];

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
   * @return FrontController
   */
  public function setParams(array $params): FrontController {
    $this->params = $params;
    return $this;
  }

  /**
   * @return string
   */
  public function getLogsPath(): ?string {
    return $this->logsPath;
  }

  /**
   * @param string $logsPath
   * @return FrontController
   * @throws StructureException
   */
  public function setLogsPath(string $logsPath): FrontController {

    if (realpath($logsPath) === false) {
      throw new StructureException("The path to the logs files does not exists: '$logsPath'");
    }

    $this->logsPath = realpath($logsPath);
    return $this;
  }

  /**
   * Return a list of values as array
   *
   * @return array
   * @throws \ReflectionException
   */
  public function toArray(): array {

    $result = array();

    $reflectionClass = new ReflectionClass(get_class($this));
    foreach ($reflectionClass->getProperties() as $property) {
      if ($property->isStatic()) {
        continue;
      }
      $result[$property->getName()] = $this->{$property->getName()};
    }

    return $result;
  }

  /**
   * Override values from original config to mock up a new
   * list of configs without interfering with.
   *
   * @param string $action
   * @param string|null $controller
   * @param string|null $module
   * @param string|null $modulePath
   * @return FrontController
   */
  public function mockUpTo(string $action, string $controller = null, string $module = null, string $modulePath = null): FrontController {

    $result = clone $this;

    // Setup FrontController with natural parameters
    $result->setActionName($action);

    // Optional controller param
    if ($controller) {
      $result->setControllerName($controller);
    }

    // Optional module param
    if ($module) {
      $result->setModuleName($module);

      // Optional module path
      if ($modulePath) {
        $result->setModulePath($modulePath);
      } else {

        // Set the module path to the current modules path directory
        $result->setModulePath($result->getModulesPath().'/'.$result->getModuleName());
      }
    }

    return $result;
  }
}
