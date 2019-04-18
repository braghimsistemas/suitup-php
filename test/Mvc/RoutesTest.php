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

use SuitUp\Mvc\Routes;
use PHPUnit\Framework\TestCase;

final class RoutesTest extends TestCase
{
  private $routes;

  private $frontController;

  public function __construct($name = null, array $data = [], $dataName = '') {
    parent::__construct($name, $data, $dataName);

    // Create an instance of routes.
    $suitup = new SuitUpStart(__DIR__.'/../resources/modules/');

    $this->frontController = $suitup->getConfig();
    $this->routes = new Routes($this->frontController);
  }

  public function testArrayToParams() {

    $values = explode('/', 'index1/value1/index2/value2');
    $params = $this->routes->arrayToParams($values);

    $this->assertArrayHasKey('index1', $params);
    $this->assertContains('value1', $params);

    $this->assertArrayHasKey('index2', $params);
    $this->assertContains('value2', $params);
  }

  public function testModule() {
    $this->assertEquals('default', $this->routes->getModule());

    $this->routes->setModule('admin');
    $this->assertEquals('admin', $this->routes->getModule());

    $this->routes->setModule('default');
    $this->assertEquals('default', $this->routes->getModule());
  }

  public function testGetController() {
    $this->assertEquals('index', $this->routes->getController());
  }

  public function testGetAction() {
    $this->assertEquals('index', $this->routes->getAction());
  }

  public function testSetByUri() {

    // Default
    $residues1 = $this->routes->setByURI('');
    $this->assertEquals('default', $this->routes->getModule());
    $this->assertEquals('index', $this->routes->getController());
    $this->assertEquals('index', $this->routes->getAction());
    $this->assertEmpty($residues1);

    // Default With params
    $residues2 = $this->routes->setByURI('/index/index/id/1234/left');
    $this->assertEquals('default', $this->routes->getModule());
    $this->assertEquals('index', $this->routes->getController());
    $this->assertEquals('index', $this->routes->getAction());
    $this->assertEquals('id/1234/left', $residues2);

    // Default 2 paths
    $residues3 = $this->routes->setByURI('/index/index');
    $this->assertEquals('default', $this->routes->getModule());
    $this->assertEquals('index', $this->routes->getController());
    $this->assertEquals('index', $this->routes->getAction());
    $this->assertEquals('', $residues3);

    // Admin
    $residues4 = $this->routes->setByURI('admin');
    $this->assertEquals('admin', $this->routes->getModule());
    $this->assertEquals('index', $this->routes->getController());
    $this->assertEquals('index', $this->routes->getAction());
    $this->assertEquals('', $residues4);

    // Admin with params
    $residues5 = $this->routes->setByURI('admin/index/index/name/michael-jackson');
    $this->assertEquals('admin', $this->routes->getModule());
    $this->assertEquals('index', $this->routes->getController());
    $this->assertEquals('index', $this->routes->getAction());
    $this->assertEquals('name/michael-jackson', $residues5);

    // Only controller
    $residues6 = $this->routes->setByURI('index');
    $this->assertEquals('default', $this->routes->getModule());
    $this->assertEquals('index', $this->routes->getController());
    $this->assertEquals('index', $this->routes->getAction());
    $this->assertEquals('', $residues6);

    // Only module and controller
    $residues6 = $this->routes->setByURI('admin/index');
    $this->assertEquals('admin', $this->routes->getModule());
    $this->assertEquals('index', $this->routes->getController());
    $this->assertEquals('index', $this->routes->getAction());
    $this->assertEquals('', $residues6);
  }

  public function testSetupRoutes() {

    // After this method the Routes class will update
    $this->routes->setupRoutes('/test/routes/index1/value1/index2/value2?index3=value3&index4=value4');

    $this->assertEquals('default', $this->routes->getModule());
    $this->assertEquals('test', $this->routes->getController());
    $this->assertEquals('routes', $this->routes->getAction());
    $this->assertIsArray($this->routes->getParams());
    $this->assertNotEmpty($this->routes->getParams());

    // URI param must to exists
    $this->assertArrayHasKey('index1', $this->routes->getParams());
    $this->assertContains('value1', $this->routes->getParams());

    // URI param must to exists
    $this->assertArrayHasKey('index2', $this->routes->getParams());
    $this->assertContains('value2', $this->routes->getParams());

    // GET param must not to exists
    $this->assertArrayNotHasKey('index3', $this->routes->getParams());
    $this->assertNotContains('value3', $this->routes->getParams());

    // GET param must not to exists
    $this->assertArrayNotHasKey('index4', $this->routes->getParams());
    $this->assertNotContains('value4', $this->routes->getParams());

    return $this->routes;
  }

  /**
   * @param Routes $routes
   * @depends testSetupRoutes
   */
  public function testParams(Routes $routes) {

    $this->assertIsArray($routes->getParams());
    $this->assertNotEmpty($routes->getParams());

    $this->assertEquals('value1', $routes->getParam('index1'));
    $this->assertEquals('value2', $routes->getParam('index2'));
    $this->assertFalse($routes->getParam('testFalse', false));
    $this->assertTrue($routes->getParam('testFalse', true));
    $this->assertNull($routes->getParam('testFalse', null));
    $this->assertIsInt($routes->getParam('testFalse', 123));
    $this->assertIsFloat($routes->getParam('testFalse', 0.8));
  }

  /**
   *
   * @depends testSetupRoutes
   */
  public function testSetupRoutesTypeLinear() {

    $this->routes->setupRoutes('/album-detail/1234/album-detail-test.html');

    $this->assertEquals('default', $this->routes->getModule());
    $this->assertEquals('album', $this->routes->getController());
    $this->assertEquals('index', $this->routes->getAction());
    $this->assertIsArray($this->routes->getParams());
    $this->assertNotEmpty($this->routes->getParams());

    // URI param must to exists
    $this->assertArrayHasKey('id', $this->routes->getParams());
    $this->assertContains('1234', $this->routes->getParams());

    // URI param must to exists
    $this->assertArrayHasKey('name', $this->routes->getParams());
    $this->assertContains('album-detail-test', $this->routes->getParams());
  }

  /**
   *
   * @depends testSetupRoutes
   */
  public function testSetupRoutesTypeReverse() {

    $this->routes->setupRoutes('1234/album-edit.html');

    $this->assertEquals('default', $this->routes->getModule());
    $this->assertEquals('album', $this->routes->getController());
    $this->assertEquals('edit', $this->routes->getAction());
    $this->assertIsArray($this->routes->getParams());
    $this->assertNotEmpty($this->routes->getParams());

    // URI param must to exists
    $this->assertArrayHasKey('id', $this->routes->getParams());
    $this->assertContains('1234', $this->routes->getParams());
  }

  /**
   *
   * @depends testSetupRoutes
   */
  public function testSetupRoutesTypeLiteralClosure() {

    // Closure Type 1
    $this->routes->setupRoutes('the/type/album-add.html');
    $this->assertEquals('default', $this->routes->getModule());
    $this->assertEquals('album', $this->routes->getController());
    $this->assertEquals('literal-add-closure', $this->routes->getAction());
    $this->assertEmpty($this->routes->getParams());

    // Closure Type 2
    $this->routes->setupRoutes('the/type/album-add');
    $this->assertEquals('default', $this->routes->getModule());
    $this->assertEquals('album', $this->routes->getController());
    $this->assertEquals('literal-add-closure', $this->routes->getAction());
    $this->assertEmpty($this->routes->getParams());

    // Closure Type 3
    $this->routes->setupRoutes('/the/type/albun-add.html');
    $this->assertEquals('default', $this->routes->getModule());
    $this->assertEquals('album', $this->routes->getController());
    $this->assertEquals('literal-add-closure', $this->routes->getAction());
    $this->assertEmpty($this->routes->getParams());

    // Closure Type 4
    $this->routes->setupRoutes('the/type/albun-add');
    $this->assertEquals('default', $this->routes->getModule());
    $this->assertEquals('album', $this->routes->getController());
    $this->assertEquals('literal-add-closure', $this->routes->getAction());
    $this->assertEmpty($this->routes->getParams());
  }

  /**
   *
   * @depends testSetupRoutes
   */
  public function testSetupRoutesTypeLiteralArray() {

    // Closure Array
    $this->routes->setupRoutes('/the-literal-route/like-array.html');
    $this->assertEquals('default', $this->routes->getModule());
    $this->assertEquals('album', $this->routes->getController());
    $this->assertEquals('literal-add-array', $this->routes->getAction());
    $this->assertEmpty($this->routes->getParams());
  }

  /**
   *
   * @depends testSetupRoutes
   */
  public function testSetupRoutesTypeLiteralForceString() {

    // Closure Array
    $this->routes->setupRoutes('the-literal-route/like-force-string.html');
    $this->assertEquals('default', $this->routes->getModule());
    $this->assertEquals('album', $this->routes->getController());
    $this->assertEquals('literal-add-force-string', $this->routes->getAction());
    $this->assertEmpty($this->routes->getParams());
  }
}
