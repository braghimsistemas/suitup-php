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

if (file_exists(__DIR__.'/../vendor/autoload.php')) {

  // We are developing SuitUp itself.

	$autoload = include __DIR__.'/../vendor/autoload.php';
	$autoload->addPsr4('SuitUpTest\\', __DIR__.'/.');

} else if (file_exists(__DIR__.'/../../../autoload.php')) {

  // We are testing directly from production (developing a project)

	$autoload = include __DIR__.'/../../../autoload.php';
	$autoload->addPsr4('SuitUpTest\\', __DIR__.'/.');
}

use SuitUp\Mvc\Routes;
use SuitUp\Exception\NotFoundException;
use SuitUp\Exception\StructureException;
use SuitUp\Database\Gateway\AbstractGateway;
use SuitUp\Database\DbAdapter;

final class SuitUpStartTest extends PHPUnit\Framework\TestCase
{

  public function testCreateInstanceException() {

    $this->expectException(StructureException::class);
    new SuitUpStart(__DIR__.'/resources/not-a-directory/');
  }

  public function testCreateInstance() {

    $suitup = new SuitUpStart(__DIR__.'/resources/modules/');
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
    $this->assertFalse(DbAdapter::isSqlMonitor());

    // True case
    $suitup->setSqlMonitor(true);
    $this->assertTrue(DbAdapter::isSqlMonitor());

    // False case
    $suitup->setSqlMonitor(false);
    $this->assertFalse(DbAdapter::isSqlMonitor());
  }

  /**
   * @throws Exception
   */
  public function testSetSqlMonitorTrueByDefault() {

    $suitup = new SuitUpStart(__DIR__.'/resources/modules/');
    $suitup->setSqlMonitor(true);

    $this->assertTrue(DbAdapter::isSqlMonitor());
  }

  /**
   *
   * @depends testCreateInstance
   * @throws Throwable
   *
   */
  public function testRunNormal() {

    $suitup = new SuitUpStart(__DIR__.'/resources/modules/');

    // Clear adapter to force system find default database.config.php file
    AbstractGateway::setDefaultAdapter(null);

    ob_start();
    $suitup->run();
    $result = ob_get_clean();

    $this->assertEquals(file_get_contents(__DIR__ . '/resources/files/suitup-start/run-normal.txt'), $result);
  }

  /**
   *
   * @depends testCreateInstance
   * @throws Throwable
   *
   */
  public function testRunRouteAdmin() {

    $suitup = new SuitUpStart(__DIR__.'/resources/modules/');
    (new Routes($suitup->getConfig()))->setupRoutes('/admin');

    ob_start();
    $suitup->run();
    $result = ob_get_clean();

    $this->assertEquals(file_get_contents(__DIR__ . '/resources/files/suitup-start/run-route-admin.txt'), $result);
  }

  /**
   *
   * @depends testCreateInstance
   * @throws Throwable
   */
  public function testRunRouteNotFound() {

    $suitup = new SuitUpStart(__DIR__.'/resources/modules/');
    (new Routes($suitup->getConfig()))->setupRoutes('/not-found-route');

    ob_start();
    $suitup->run();
    $result = ob_get_clean();

    $this->assertEquals(file_get_contents(__DIR__ . '/resources/files/suitup-start/run-route-not-found.txt'), $result);
  }
}
