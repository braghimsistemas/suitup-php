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

namespace Suitup\Storage;


use Router\Routes;

class Config
{
  private static $instance;

  /**
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
   * @var string
   */
  private $moduleName;

  /**
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
  private $controllerName = 'index';

  /**
   * @var string
   */
  private $controllersPath = 'Controllers';

  /**
   * @var string
   */
  private $actionName = 'index';

  /**
   * @var string
   */
  private $actionFilename = 'index';

  /**
   * @var string
   */
  private $actionSuffix = '.phtml';

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
   * @var \Suitup\Router\Routes
   */
  private $routes;

  /**
   * @var string
   */
  private $logsPath;

  /**
   * Config constructor.
   *
   * Doesn't allow new instances
   */
  private function __construct() {

    // Setup default value to the modules path.
    // It will relate modules path to the 'chdir' function
    // which must to be called in the index.php file
    $this->modulesPath = realpath('.');

    // Set initialized the routes manager
    $this->routes = new \Suitup\Router\Routes();
    $this->routes->setModule($this->getModuleDefault());
  }

  /**
   * Doesn't allow clones
   */
  private function __clone() {}

  /**
   * Return the instance for the class but always the same instance.
   * It's singleton baby.
   *
   * @return Config
   */
  public static function getInstance(): self {
    if (null == self::$instance) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  /**
   * GETTERS AND SETTERS
   */
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
   * @return Config
   */
  public function setModulesPath(string $modulesPath): Config {
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
   * @return Config
   */
  public function setLayoutName(string $layoutName): Config {
    $this->layoutName = (preg_match('/\./', $layoutName) === 0) ? $layoutName.'.phtml' : $layoutName;
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
   * @return Config
   */
  public function setLayoutPath(string $layoutPath): Config {
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
   * @return Config
   */
  public function setViewsPath(string $viewsPath): Config {
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
   * @return Config
   */
  public function setBasePath(string $basePath = null): Config {

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
   * @return Config
   */
  public function setModuleName(string $moduleName): Config {
    $this->moduleName = $moduleName;
    return $this;
  }

  /**
   * @return string
   */
  public function getModulePath(): string {
    return $this->modulePath;
  }

  /**
   * @param string $modulePath
   * @return Config
   */
  public function setModulePath(string $modulePath): Config {
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
   * @return Config
   */
  public function setModulePrefix(string $modulePrefix): Config {
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
   * @return Config
   */
  public function setModuleDefault(string $moduleDefault): Config {
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
   * @return Config
   */
  public function setControllerName(string $controllerName): Config {
    $this->controllerName = $controllerName;
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
   * @return Config
   */
  public function setControllersPath(string $controllersPath): Config {
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
   * @return Config
   */
  public function setActionName(string $actionName): Config {
    $this->actionName = $actionName;
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
   * @return Config
   */
  public function setActionFilename(string $actionFilename): Config {
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
   * @return Config
   */
  public function setActionSuffix(string $actionSuffix): Config {
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
   * @return Config
   */
  public function setSqlMonitor(bool $sqlMonitor): Config {
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
   * @return Config
   */
  public function setFormPath(string $formPath): Config {
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
   * @return Config
   */
  public function setBusinessPath(string $businessPath): Config {
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
   * @return Config
   */
  public function setGatewayPath(string $gatewayPath): Config {
    $this->gatewayPath = $gatewayPath;
    return $this;
  }

  /**
   * @return \Suitup\Router\Routes
   */
  public function getRoutes(): \Suitup\Router\Routes {
    return $this->routes;
  }

  /**
   * @param \Suitup\Router\Routes $routes
   * @return Config
   */
  public function setRoutes(\Suitup\Router\Routes $routes): Config {
    $this->routes = $routes;
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
   * @return Config
   */
  public function setLogsPath(string $logsPath): Config {
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
