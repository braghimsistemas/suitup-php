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

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', '1');

// Para quando estamos mexendo diretamente no código
if (file_exists(__DIR__.'/../vendor/autoload.php')) {
	$autoload = include __DIR__.'/../vendor/autoload.php';
	$autoload->addPsr4('SuitUpTest\\', __DIR__.DIRECTORY_SEPARATOR.'.');
	
// Para quando estamos alterando dentro do projeto que usa o SuitUp
} else if (file_exists(__DIR__.'/../../../autoload.php')) {
	$autoload = include __DIR__.'/../../../autoload.php';
	$autoload->addPsr4('SuitUpTest\\', __DIR__.DIRECTORY_SEPARATOR.'.');
	
}

/**
 * Define a Constante DEVELOPMENT caso ainda não tenha sido.
 */
defined('DEVELOPMENT') || define('DEVELOPMENT', (bool) getenv('DEVELOPMENT'));

/**
 * Define a Constante SHOW_ERRORS caso ainda não tenha sido.
 */
defined('SHOW_ERRORS') || define('SHOW_ERRORS', (bool) getenv('DEVELOPMENT'));

final class SuitUpStartTest extends PHPUnit\Framework\TestCase
{
  public function testRun(): void {
    // $this->assertEquals(1, 1);
  }

  public function testSetSqlMonitor(): void {
    // $this->assertEquals(2, 2);
  }
}
