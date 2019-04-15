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

// Initial configs
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', '1');

// Environment variables
define('DEVELOPMENT', true);
define('SHOW_ERRORS', true);

if (file_exists(__DIR__.'/../vendor/autoload.php')) {

  // We are developing SuitUp itself.

	$autoload = include __DIR__.'/../vendor/autoload.php';
	$autoload->addPsr4('SuitUpTest\\', __DIR__.DIRECTORY_SEPARATOR.'.');

} else if (file_exists(__DIR__.'/../../../autoload.php')) {

  // We are testing directly from production (developing a project)

	$autoload = include __DIR__.'/../../../autoload.php';
	$autoload->addPsr4('SuitUpTest\\', __DIR__.DIRECTORY_SEPARATOR.'.');
}

final class SuitUpStartTest extends PHPUnit\Framework\TestCase
{

  public function testCreateInstance() {

    $suitup = new SuitUpStart('.');

    $this->assertInstanceOf('SuitUpStart', $suitup);

    return $suitup;
  }

  /**
   * Certify that getConfig method will return a instance of FrontController
   *
   * @depends testCreateInstance
   * @param SuitUpStart $suitup
   */
  public function testGetConfig(SuitUpStart $suitup) {

    $this->assertInstanceOf('SuitUp\Mvc\FrontController', $suitup->getConfig());
  }

  /**
   * @depends testCreateInstance
   * @param SuitUpStart $suitup
   */
  public function testSetSqlMonitor(SuitUpStart $suitup) {

    // Must to be false by default
    $this->assertFalse($suitup->getConfig()->isSqlMonitor());

    // True case
    $suitup->setSqlMonitor(true);
    $this->assertTrue($suitup->getConfig()->isSqlMonitor());

    // False case
    $suitup->setSqlMonitor(false);
    $this->assertFalse($suitup->getConfig()->isSqlMonitor());
  }

  /**
   * @throws Exception
   */
  public function testSetSqlMonitorTrueByDefault() {

    $suitup = new SuitUpStart('.');
    $suitup->setSqlMonitor(true);

    $this->assertTrue($suitup->getConfig()->isSqlMonitor());
  }
}
