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

//$_SERVER['REQUEST_URI'] = '/test/index/index1/value1/index2/value2';

use PHPUnit\Framework\TestCase;

final class RoutesTest extends TestCase
{
  private $routes;

  public function __construct($name = null, array $data = [], $dataName = '') {
    parent::__construct($name, $data, $dataName);

    // Create an instance of routes.
    $suitup = new SuitUpStart(__DIR__.'/../resources/modules/');
    $this->routes = new \SuitUp\Mvc\Routes($suitup->getConfig());
  }

  public function testArrayToParams() {

    $values = explode('/', 'index1/value1/index2/value2');
    $params = $this->routes->arrayToParams($values);

    $this->assertArrayHasKey('index1', $params);
    $this->assertContains('value1', $params);

    $this->assertArrayHasKey('index2', $params);
    $this->assertContains('value2', $params);
  }

  public function testGetModule() {
    $this->assertEquals('default', $this->routes->getModule());
  }

  public function testGetController() {
    $this->assertEquals('index', $this->routes->getController());
  }

  public function testGetAction() {
    $this->assertEquals('index', $this->routes->getAction());
  }

  public function testGetParams() {
    $this->assertIsArray($this->routes->getParams());
    $this->assertEmpty($this->routes->getParams());
  }

  public function testSetupRoutes() {

    // After this method the Routes class will update
    // its configs based on the $_SERVER['REQUEST_URI'];
    // Here in the tests this REQUEST_URI env is set on
    // phpunit.xml.dist file, section <php>
    $this->routes->setupRoutes();

    // REQUEST_URI = /test/routes/index1/value1/index2/value2 <-- phpunit.xml.dist file, section <php>

    $this->assertEquals('default', $this->routes->getModule());
    $this->assertEquals('test', $this->routes->getController());
    $this->assertEquals('routes', $this->routes->getAction());
    $this->assertIsArray($this->routes->getParams());
    $this->assertNotEmpty($this->routes->getParams());

    $this->assertArrayHasKey('index1', $this->routes->getParams());
    $this->assertContains('value1', $this->routes->getParams());

    $this->assertArrayHasKey('index2', $this->routes->getParams());
    $this->assertContains('value2', $this->routes->getParams());
  }
}
