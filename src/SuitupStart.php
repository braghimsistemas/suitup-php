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

use Suitup\Storage\Config;
use Suitup\Mvc\MvcAbstractController;

/**
 * Token para o sistema nao "confundir" as mensagens de sessao
 * atual com mensagens que ja existiam em outra pagina.
 * Utilizado dentro da classe \SuitUp\Mvc\MvcAbstractController
 *
 * ¯\_(-.-)_/¯
 */
define('MSG_NSP_TOKEN', mctime());

/**
 * Define DEVELOPMENT constant
 */
defined('DEVELOPMENT') || define('DEVELOPMENT', (bool) getenv('DEVELOPMENT'));

/**
 * Define SHOW_ERRORS constant
 */
defined('SHOW_ERRORS') || define('SHOW_ERRORS', (bool) getenv('SHOW_ERRORS'));

/**
 * Class SuitupStart
 *
 * Everything in Suitup Framework PHP begins from here.
 */
class SuitupStart
{

  /**
   * Current system version
   */
  const VERSION = '2.0.0';

  /**
   * SuitupStart constructor.
   *
   * @param string $modulesPath
   * @throws Exception
   */
  public function __construct(string $modulesPath = null) {

    // Start the loader to setup auto include for the framework files.
    $loader = new Psr4AutoloaderClass();
    $loader->register();
    $loader->addNamespace('Suitup', __DIR__);
    $loader->addNamespace('ModuleError', __DIR__ . '/ModuleError');

    if ($modulesPath) {
      $this->getConfig()->setModulesPath((realpath($modulesPath) === false) ? $modulesPath : realpath($modulesPath).'/');
    }

    // Get from config, there's a default value if empty
    $modulesPathDir = $this->getConfig()->getModulesPath();

    // Make sure modules path is a dir
    if (!is_dir($modulesPathDir)) {
      throw new \Exception('The $modulesPath parameter is not a directory');
    }

    // Add to the loader all directories from modules path dir.
    foreach (scandir($modulesPathDir) as $module) {
      if (!in_array($module, array('.', '..')) && is_dir($modulesPath.'/'.$module)) {
        $loader->addNamespace($module, $modulesPathDir);
      }
    }

    // Store on the configs the modules path
    $this->getConfig()->setModulesPath($modulesPathDir);
    $this->getConfig()->setBasePath();
    $this->getConfig()->getRoutes()->setupRoutes();
  }

  /**
   * Effectively runs the entire application
   */
  public function run(): void {
    try {

      // If is everything ok this will be the one launch.
      $this->launcher($this->getConfig());

    } catch (\Throwable $originalError) {
      try {

        dump($originalError);

        // We got an error so let's try to run
        // ErrorController inside the own module

      } catch (\Throwable $e) {
        try {
          // Well now we have to try to launch ErrorController
          // from framework itself

        } catch (\Throwable $ex) {
          dump($originalError);
        }
      }
    }
  }

  /**
   * This method will try to launch the application with given configuration.
   *
   * @param Config $configs
   * @param Throwable|null $exception
   * @return MvcAbstractController|null
   * @throws Exception
   */
  public function launcher(Config $configs, \Throwable $exception = null): ?MvcAbstractController {

    // Define modulo
    if (! is_dir($configs->getModulePath()) || ! is_readable($configs->getModulePath())) {
      throw new \Exception("Module folder '{$configs->getModulePath()}' does not exists");
    }

    // Check if controller file exists
    $controllerFile = "{$configs->getModulePath()}/{$configs->getControllersPath()}/{$configs->getControllerName()}.php";
    if (! file_exists($controllerFile)) {
      throw new \Exception("Controller file could not be found: $controllerFile");
    }

    // Try to discover the namespace for the controller
    $controllerBaseNsp = $configs->getModulePrefix().ucfirst($configs->getModuleName());
    $controllerBaseNsp .= '\\'.str_replace('/', '\\', $configs->getControllersPath());
    $controllerNsp = $controllerBaseNsp.'\\'.$configs->getControllerName();

    // Validate controller class namespace
    if (! class_exists($controllerNsp)) {
      throw new \Exception("Unable to find controller class with namespace '$controllerNsp'");
    }

    // Create a controller instance
    $controller = new $controllerNsp();

    // Check if it is a MvcAbstractController
    if (! $controller instanceof MvcAbstractController) {
      throw new \Exception("Every controller must to be an instance of 'MvcAbstractController'");
    }

    // Check if action exists on controller
    if (! method_exists($controller, $configs->getActionName())) {
      throw new \Exception("Action '{$configs->getActionName()}' does not exists on controller '$controllerNsp'");
    }

    // Check up for views folder
    if (! is_dir($configs->getModulePath().'/'.$configs->getViewsPath())) {
      throw new \Exception("Views directory was not found to module '{$configs->getModuleName()}'");
    }

    // Set given configs
    $controller->setConfig($configs);

    // Launch methods for the win!
    $controller->preDispatch();
    $controller->init();
    $controller->{$configs->getActionName()}();
    $controller->posDispatch();

    // Return it's instance
    return $controller;
  }

  /**
   * If is wanted to change some config before to run the application, as change default path to the controllers for
   * example...
   *
   * @return \Suitup\Storage\Config
   */
  public function getConfig(): Config {
    return Config::getInstance();
  }

  /**
   * @param bool $status
   * @return SuitupStart
   */
  public function setSqlMonitor(bool $status): SuitupStart {
    Config::getInstance()->setSqlMonitor($status);
    return $this;
  }
}
