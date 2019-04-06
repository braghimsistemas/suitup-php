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
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
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

  private $modulesPath;

  private $loader;

  public function __construct(string $modulesPath) {

    $this->modulesPath = (realpath($modulesPath) === false) ? $modulesPath : realpath($modulesPath).DIRECTORY_SEPARATOR;

    // Validate modules dir path
    if (!$this->modulesPath || !is_dir($this->modulesPath)) {
      throw new \Exception("The directory '" . $this->modulesPath . "' could not be loaded");
    }

    $this->loader = new Psr4AutoloaderClass();
    $this->loader->register();
    $this->loader->addNamespace('SuitUp\\', __DIR__);
    $this->loader->addNamespace('ModuleError\\', __DIR__ . '/ModuleError');

    dump($this);
  }

  /**
   * Effectively runs the entire application
   */
  public function run(): void {

    exit('Rodou!');

    // Gatilho para fila de processos.
    // Se der alguma exception aqui vai
    // cair na funcao descrita acima.
    // $this->mvc->controller->preDispatch();
    // $this->mvc->controller->init();
    // $this->mvc->controller->{$this->mvc->actionName}();
    // $this->mvc->controller->posDispatch();
  }

  /**
   * Habilita ou desabilita monitoramento de SQL de banco de dados.
   *
   * @param boolean $status
   * @return SuitUpStart
   */
  public function setSqlMonitor($status = false): self {
    // @todo: Set this method
    return $this;
  }
}
