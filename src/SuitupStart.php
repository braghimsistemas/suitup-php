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
  public function __construct(string $modulesPath) {

    $modulesPathDir = (realpath($modulesPath) === false) ? $modulesPath : realpath($modulesPath).'/';

    if (!is_dir($modulesPathDir)) {
      throw new \Exception('The $modulesPath parameter is not a directory');
    }

    // Start the loader to setup auto include for the framework files.
    $loader = new Psr4AutoloaderClass();
    $loader->register();
    $loader->addNamespace('Suitup', __DIR__);
    $loader->addNamespace('ModuleError', __DIR__ . '/ModuleError');

    // Add to the loader all directories from modules path dir.
    foreach (scandir($modulesPathDir) as $module) {
      if (!in_array($module, array('.', '..')) && is_dir($modulesPath.'/'.$module)) {
        $loader->addNamespace($module, $modulesPathDir);
      }
    }

    // Store on the configs the modules path
    $config = \Suitup\Storage\Config::getInstance();
    $config->setModulesPath($modulesPathDir);
    $config->setBasePath();
    $config->setupRoutes();
  }

  /**
   * If is wanted to change some config before to run the application, as change default path to the controllers for
   * example...
   *
   * @return \Suitup\Storage\Config
   */
  public function getConfig(): \Suitup\Storage\Config {
    return \Suitup\Storage\Config::getInstance();
  }

  /**
   * Effectively runs the entire application
   */
  public function run(): void {

    dump(\Suitup\Storage\Config::getInstance()->toArray());

    // Gatilho para fila de processos.
    // Se der alguma exception aqui vai
    // cair na funcao descrita acima.
    // $this->mvc->controller->preDispatch();
    // $this->mvc->controller->init();
    // $this->mvc->controller->{$this->mvc->actionName}();
    // $this->mvc->controller->posDispatch();
  }

  /**
   * @param bool $status
   * @return SuitupStart
   */
  public function setSqlMonitor(bool $status): SuitupStart {
    \Suitup\Storage\Config::getInstance()->setSqlMonitor($status);
    return $this;
  }
}
