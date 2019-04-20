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

include_once __DIR__ . "/Autoload/Psr4AutoloaderClass.php";
include_once __DIR__ . "/functions.php";

use SuitUp\Exception\NotFoundException;
use SuitUp\Exception\StructureException;
use SuitUp\Mvc\FrontController;
use SuitUp\Mvc\MvcAbstractController;
use SuitUp\Mvc\Routes;
use SuitUp\Database\DbAdapter\AdapterFactory;
use SuitUp\Database\Gateway\AbstractGateway;

/**
 * Define DEVELOPMENT constant
 */
defined('DEVELOPMENT') || define('DEVELOPMENT', (bool) getenv('DEVELOPMENT'));

/**
 * Define SHOW_ERRORS constant
 */
defined('SHOW_ERRORS') || define('SHOW_ERRORS', (bool) getenv('SHOW_ERRORS'));

/**
 * Define that system are running under test cases
 */
defined('IS_TESTCASE') || define('IS_TESTCASE', (bool) getenv('IS_TESTCASE'));

/**
 * Class SuitUpStart
 *
 * Everything in SuitUp Framework PHP begins from here.
 */
class SuitUpStart
{

  /**
   * Current system version
   */
  const VERSION = '2.0.0';

  /**
   * @var FrontController
   */
  private $config;

  /**
   * @var MvcAbstractController
   */
  private $controller;

  /**
   * SuitUpStart constructor.
   *
   * @param string $modulesPath
   * @throws Exception
   */
  public function __construct(string $modulesPath = null) {

    // Start the loader to setup auto include for the framework files.
    $loader = new Psr4AutoloaderClass();
    $loader->register();
    $loader->addNamespace('SuitUp', __DIR__);
    $loader->addNamespace('ModuleError', __DIR__ . '/ModuleError');

    // Start a config instance
    $this->config = new FrontController();

    if ($modulesPath) {
      $this->getConfig()->setModulesPath((realpath($modulesPath) === false) ? $modulesPath : realpath($modulesPath).'/');
    }

    // Get from config, there's a default value if empty
    $modulesPathDir = $this->getConfig()->getModulesPath();

    // Make sure modules path is a dir
    if (!is_dir($modulesPathDir)) {
      throw new StructureException('The $modulesPath parameter is not a directory');
    }

    // Add to the loader all directories from modules path dir.
    foreach (scandir($modulesPathDir) as $module) {
      if (!in_array($module, array('.', '..')) && is_dir($modulesPath.'/'.$module)) {
        $loader->addNamespace($module, $modulesPathDir);
      }
    }

    // Store on the configs the modules path
    $this->getConfig()->setModulesPath($modulesPathDir);

    // Resolve routes
    $routes = new Routes($this->getConfig());
    $routes->setupRoutes();
  }

  /**
   * Effectively runs the entire application
   */
  public function run(): void {

    try {

      $this->checkupDefaultAdapter();

      // If is everything ok this will be the one launch.
      $this->controller = $this->builder($this->getConfig());

    } catch (Throwable $originalError) {
      try {

        // Define what kind of view will be displayed
        $errorAction = 'error';
        if ($originalError instanceof NotFoundException) {
          $errorAction = 'not-found';
        }

        // We got an default-error so let's try to run
        // ErrorController inside the own module
        $this->controller = $this->builder(
          $this->getConfig()->mockUpTo($errorAction, 'error', 'error'),
          $originalError
        );

      } catch (Throwable $e) {
        try {

          // Well now we have to try to launch ErrorController
          // from framework itself
          $frameworkErrorModule = $this->getConfig()->mockUpTo($errorAction, 'default-error', 'error', __DIR__.'/ModuleError');

          // Try to build it
          $this->controller = $this->builder($frameworkErrorModule, DEVELOPMENT ? $e : $originalError);

        } catch (Throwable $ex) {

          // Sadly it came till here, it's such a shame and we
          // made everything that was possible. Now it's your
          // job champs! Good luck ='(
          throw (DEVELOPMENT ? $ex : $originalError);
        }
      }
    }
  }

  /**
   * This method will try to launch the application with given configuration.
   *
   * @param FrontController $frontController
   * @param Throwable|null $exception
   * @return MvcAbstractController|null
   * @throws Exception
   */
  public function builder(FrontController $frontController, Throwable $exception = null): ?MvcAbstractController {
    
    // Define modulo
    if (! is_dir($frontController->getModulePath()) || ! is_readable($frontController->getModulePath())) {
      throw new StructureException("Module folder '{$frontController->getModulePath()}' does not exists");
    }

    // Check if controller file exists
    $controllerFile = "{$frontController->getModulePath()}/{$frontController->getControllersPath()}/{$frontController->getControllerName()}.php";
    if (! file_exists($controllerFile)) {
      throw new NotFoundException("Controller file could not be found: $controllerFile");
    }

    // Try to discover the namespace for the controller
    $controllerBaseNsp = $frontController->getModuleName();
    $controllerBaseNsp .= '\\'.str_replace('/', '\\', $frontController->getControllersPath());
    $controllerNsp = $controllerBaseNsp.'\\'.$frontController->getControllerName();

    // Validate controller class namespace
    if (! class_exists($controllerNsp)) {
      throw new NotFoundException("Unable to find controller class with namespace '$controllerNsp'");
    }

    // Create a controller instance
    $controller = new $controllerNsp($frontController);

    // Check if it is a MvcAbstractController
    if (! $controller instanceof MvcAbstractController) {
      throw new StructureException("Every controller must to be an instance of 'MvcAbstractController'");
    }

    // Check if action exists on controller
    if (! method_exists($controller, $frontController->getActionName())) {
      throw new NotFoundException("Action '{$frontController->getActionName()}' does not exists on controller '$controllerNsp'");
    }

    // Check up for views folder
    if (! is_dir($frontController->getModulePath().'/'.$frontController->getViewsPath())) {
      throw new StructureException("Views directory was not found to module '{$frontController->getModuleName()}'");
    }

    // Set exception if exists
    if ($exception) {
      $controller->setException($exception);
    }

    // Launch methods for the win!
    $controller->preDispatch();
    $controller->init();
    $controller->{$frontController->getActionName()}();
    $controller->posDispatch();
    $controller->render();

    // Return it's instance
    return $controller;
  }

  /**
   * If was not set default adapter this method will
   * try to do it searching by the file database.config.php
   * in the config directory.
   *
   * @throws StructureException
   * @throws \SuitUp\Exception\DbAdapterException
   */
  private function checkupDefaultAdapter(): void {

    // Was already defined a database config?
    if (AbstractGateway::getDefaultAdapter() == null) {

      // Check if was defined a default database config file
      $dbDefaultFilename = realpath($this->getConfig()->getModulesPath().'/..'.$this->getConfig()->getConfigsPath());
      $dbDefaultFilename .= '/database.config.php';

      // If file exists
      if (file_exists($dbDefaultFilename)) {

        // Try to get the PHP file content
        $content = require $dbDefaultFilename;

        if (is_array($content)) {

          // Append to the Gateway as a Default Adapter
          $adapter = AdapterFactory::getAdapter($content);

          if ($adapter) {
            AbstractGateway::setDefaultAdapter($adapter);
          }
        }
      }
    }
  }

  /**
   * If is wanted to change some config before to run the application,
   * as change default path to the controllers for example...
   *
   * Ps.: We keep this name 'config' to seems more instinctive for user.
   *
   * @return FrontController
   */
  public function getConfig(): FrontController {
    return $this->config;
  }

  /**
   * @return MvcAbstractController
   */
  public function getController(): ?MvcAbstractController {
    return $this->controller;
  }

  /**
   * @param bool $status
   * @return SuitUpStart
   */
  public function setSqlMonitor(bool $status): SuitUpStart {
    $this->getConfig()->setSqlMonitor($status);
    return $this;
  }
}
