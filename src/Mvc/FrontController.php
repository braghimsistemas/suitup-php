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

namespace Suitup\Mvc;

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
  private $layoutPath = '/views';

  /**
   * @var string
   */
  private $viewsPath = '/views';

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
  private $moduleDefault = 'default';

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
  private $actionFilename = 'index';

  /**
   * @var string
   */
  private $actionSuffix = '.phtml';

  /**
   * @var string
   */
  private $actionName = 'indexAction';

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
  private $routesPath = './config';

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
  private $routesFile;

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
   */
  public function __construct() {

    // Setup default value to the modules path.
    // It will relate modules path to the 'chdir' function
    // which must to be called in the index.php file
    $this->modulesPath = realpath('.');

    $this->setBasePath();
  }

  /**
   * Update FrontController config with given data.
   *
   * @param string $module
   * @param string $controller
   * @param string $action
   * @param array $params
   * @return FrontController
   */
  public function updateTo(string $module, string $controller, string $action, array $params = array()): FrontController {

    // Setting up new configs
    $this->setModuleName($module);
    $this->setControllerName($controller);
    $this->setActionName($action);
    $this->setParams($params);

    // Now we can set the module path
    $this->setModulePath($this->getModulesPath().'/'.$this->getModuleName());

    return $this;
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
   */
  public function setModulesPath(string $modulesPath): FrontController {
    $this->modulesPath = rtrim($modulesPath, '/');
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
    $this->layoutSuffix = $layoutSuffix;
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
    $this->layoutPath = $layoutPath;
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
    $this->viewsPath = $viewsPath;
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

    if ($basePath) {
      $this->basePath = $basePath ? "/$basePath" : '';
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
    $this->moduleName = $this->getModulePrefix().ucfirst(strtolower($moduleName));
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
   */
  public function setModulePath(string $modulePath): FrontController {
    $this->modulePath = $modulePath;
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
    $this->modulePrefix = $modulePrefix;
    return $this;
  }

  /**
   * @return string
   */
  public function getModuleDefault(): string {
    return $this->moduleDefault;
  }

  /**
   * @param string $moduleDefault
   * @return FrontController
   */
  public function setModuleDefault(string $moduleDefault): FrontController {
    $this->moduleDefault = $moduleDefault;
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
    $controller = ucwords(preg_replace("/\-/", " ", strtolower($controllerName)));
    $this->controllerName = preg_replace("/\s+/", "", $controller).'Controller';
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
  public function getActionName(): string {
    return $this->actionName;
  }

  /**
   * @param string $actionName
   * @return FrontController
   */
  public function setActionName(string $actionName): FrontController {
    $action = lcfirst(ucwords(preg_replace("/\-/", " ", strtolower($actionName))));
    $this->actionName = preg_replace("/\s+/", "", $action).'Action';
    return $this;
  }

  /**
   * @return string
   */
  public function getActionFilename(): string {
    return $this->actionFilename;
  }

  /**
   * @param string $actionFilename
   * @return FrontController
   */
  public function setActionFilename(string $actionFilename): FrontController {
    $this->actionFilename = $actionFilename;
    return $this;
  }

  /**
   * @return string
   */
  public function getActionSuffix(): string {
    return $this->actionSuffix;
  }

  /**
   * @param string $actionSuffix
   * @return FrontController
   */
  public function setActionSuffix(string $actionSuffix): FrontController {
    $this->actionSuffix = $actionSuffix;
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
    $this->formPath = $formPath;
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
    $this->businessPath = $businessPath;
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
    $this->gatewayPath = $gatewayPath;
    return $this;
  }

  /**
   * @return string
   */
  public function getRoutesPath(): string {
    return $this->routesPath;
  }

  /**
   * @param string $routesPath
   * @return FrontController
   */
  public function setRoutesPath(string $routesPath): FrontController {
    $this->routesPath = $routesPath;
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

      $filename = $this->getRoutesPath().'/'.$this->getModuleDefault().$this->getRoutesFileSuffix();
      if (file_exists($filename) && is_readable($filename)) {

        $this->routesFile = $filename;
      }
    }
    return $this->routesFile;
  }

  /**
   * @param string $routesFile
   * @return FrontController
   */
  public function setRoutesFile(string $routesFile): FrontController {
    $this->routesFile = $routesFile;
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
  public function getLogsPath(): string {
    return $this->logsPath;
  }

  /**
   * @param string $logsPath
   * @return FrontController
   */
  public function setLogsPath(string $logsPath): FrontController {
    $this->logsPath = $logsPath;
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

    $reflectionClass = new \ReflectionClass(get_class($this));
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
   * @param array $override
   * @return array
   * @throws \ReflectionException
   */
  public function mockUpTo(array $override = array()): array {

    $result = $this->toArray();
    foreach ($override as $key => $value) {
      $result[$key] = $value;
    }
    return $result;
  }
}
