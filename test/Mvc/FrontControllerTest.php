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

use SuitUp\Mvc\FrontController;
use PHPUnit\Framework\TestCase;

class FrontControllerTest extends TestCase
{
  /**
   * @var FrontController
   */
  private $instance;

  public function testCreateInstance() {
    $instance = new FrontController(realpath(__DIR__.'/../resources/modules'));
    $this->assertInstanceOf('\SuitUp\Mvc\FrontController', $instance);

    return $instance;
  }

  /**
   * @param FrontController $instance
   * @depends testCreateInstance
   */
  public function testGetModuleName(FrontController $instance)
  {
    $this->assertEquals('ModuleDefault', $instance->getModuleName());
  }

  /**
   * @param FrontController $instance
   * @depends testCreateInstance
   */
  public function testSetConfigsPath(FrontController $instance)
  {
    $this->assertEquals('/config', $instance->getConfigsPath());

    $instance->setConfigsPath('/modules');
    $this->assertEquals('/modules', $instance->getConfigsPath());

    $instance->setConfigsPath('/config');
    $this->assertEquals('/config', $instance->getConfigsPath());
  }

  /**
   * @param FrontController $instance
   * @depends testCreateInstance
   */
  public function testSetAction(FrontController $instance)
  {
    $this->assertEquals('index', $instance->getAction());

    $instance->setAction('test');
    $this->assertEquals('test', $instance->getAction());

    $instance->setAction('index');
    $this->assertEquals('index', $instance->getAction());
  }

  /**
   * @param FrontController $instance
   * @depends testCreateInstance
   */
  public function testSetGatewayPath(FrontController $instance)
  {
    $this->assertEquals($instance->getModulePath().'/Model/Gateway', $instance->getGatewayPath());

    $instance->setGatewayPath('/test');
    $this->assertEquals($instance->getModulePath().'/test', $instance->getGatewayPath());

    $instance->setGatewayPath($instance->getModulePath().'/Model/Gateway');
    $this->assertEquals($instance->getModulePath().'/Model/Gateway', $instance->getGatewayPath());
  }

  /**
   * @param FrontController $instance
   * @depends testCreateInstance
   * @throws ReflectionException
   */
  public function testToArray(FrontController $instance)
  {
    $this->assertIsArray($instance->toArray());
  }

  /**
   * @param FrontController $instance
   * @depends testCreateInstance
   */
  public function testGetModulePrefix(FrontController $instance)
  {
    $this->assertEquals('Module', $instance->getModulePrefix());
  }

  /**
   * @param FrontController $instance
   * @depends testCreateInstance
   */
  public function testGetRoutesFile(FrontController $instance)
  {
    $this->assertEquals(realpath(__DIR__.'/../resources/config').'/default.routes.php', $instance->getRoutesFile());
  }

  /**
   * @param FrontController $instance
   * @depends testCreateInstance
   * @throws \SuitUp\Exception\StructureException
   */
  public function testSetRoutesFile(FrontController $instance)
  {
    // Exception case
    $this->expectException(SuitUp\Exception\StructureException::class);
    $instance->setRoutesFile('setting/whatever/name/to/throw/exception.php');

    // Real case
    $another = realpath(__DIR__.'/../resources/config').'/another.routes.php';
    $this->assertEquals($another, $instance->getRoutesFile());

    // Default file
    $defaultRoute = realpath(__DIR__.'/../resources/config').'/default.routes.php';
    $this->assertEquals($defaultRoute, $instance->getRoutesFile());
  }

  /**
   * @param FrontController $instance
   * @depends testCreateInstance
   */
  public function testSetLayoutName(FrontController $instance)
  {
    $this->assertEquals('layout.phtml', $instance->getLayoutName());
  }

  /**
   * @param FrontController $instance
   * @depends testCreateInstance
   */
  public function testGetLayoutPath(FrontController $instance)
  {
    $this->assertEquals('views', $instance->getLayoutPath());
  }

  /**
   * @param FrontController $instance
   * @depends testCreateInstance
   */
  public function testSetController(FrontController $instance)
  {
    $this->assertEquals('index', $instance->getController());

    $instance->setController('test');
    $this->assertEquals('test', $instance->getController());

    $instance->setController('index');
    $this->assertEquals('index', $instance->getController());
  }

  /**
   * @param FrontController $instance
   * @depends testCreateInstance
   */
  public function testGetModulesPath(FrontController $instance)
  {
    $this->assertEquals(realpath(__DIR__.'/../resources/modules'), $instance->getModulesPath());
  }

//  public function testSetModuleName()
//  {
//
//  }
//
//  public function testSetModulePrefix()
//  {
//
//  }
//
//  public function testIsSqlMonitor()
//  {
//
//  }
//
//  public function testSetControllersPath()
//  {
//
//  }
//
//  public function testResolveViewFilename()
//  {
//
//  }
//
//  public function testGetViewsPath()
//  {
//
//  }
//
//  public function testGetModule()
//  {
//
//  }
//
//  public function testGetGatewayPath()
//  {
//
//  }
//
//  public function testSetFormPath()
//  {
//
//  }
//
//  public function testGetAction()
//  {
//
//  }
//
//  public function testGetControllersPath()
//  {
//
//  }
//
//  public function testGetBusinessPath()
//  {
//
//  }
//
//  public function testSetViewsPath()
//  {
//
//  }
//
//  public function testGetActionName()
//  {
//
//  }
//
//  public function testGetLayoutName()
//  {
//
//  }
//
//  public function testSetViewSuffix()
//  {
//
//  }
//
//  public function testSetControllerName()
//  {
//
//  }
//
//  public function testSetBasePath()
//  {
//
//  }
//
//  public function testSetSqlMonitor()
//  {
//
//  }
//
//  public function testGetControllerName()
//  {
//
//  }
//
//  public function testMockUpTo()
//  {
//
//  }
//
//  public function testSetLayoutSuffix()
//  {
//
//  }
//
//  public function testGetFormPath()
//  {
//
//  }
//
//  public function testGetController()
//  {
//
//  }
//
//  public function testGetBasePath()
//  {
//
//  }
//
//  public function testSetRoutesFileSuffix()
//  {
//
//  }
//
//  public function testGetModulePath()
//  {
//
//  }
//
//  public function testSetLogsPath()
//  {
//
//  }
//
//  public function testGetViewSuffix()
//  {
//
//  }
//
//  public function testGetLogsPath()
//  {
//
//  }
//
//  public function testSetActionName()
//  {
//
//  }
//
//  public function testGetLayoutSuffix()
//  {
//
//  }
//
//  public function testSetBusinessPath()
//  {
//
//  }
//
//  public function testGetRoutesFileSuffix()
//  {
//
//  }
//
//  public function testResolveLayoutFilename()
//  {
//
//  }
//
//  public function testSetLayoutPath()
//  {
//
//  }
//
//  public function testSetModulePath()
//  {
//
//  }
//
//  public function test__construct()
//  {
//
//  }
//
//  public function testSetModule()
//  {
//
//  }
//
//  public function testGetParams()
//  {
//
//  }
//
//  public function testSetParams()
//  {
//
//  }
//
//  public function testSetModulesPath()
//  {
//
//  }
}
