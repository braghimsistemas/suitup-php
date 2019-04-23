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

use SuitUp\Exception\StructureException;
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
  public function testSetModuleName(FrontController $instance)
  {
    $instance->setModuleName('Admin Mod');
    $this->assertEquals('ModuleAdminMod', $instance->getModuleName());

    $instance->setModuleName('default');
    $this->assertEquals('ModuleDefault', $instance->getModuleName());
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
  public function testAction(FrontController $instance)
  {
    $this->assertEquals('index', $instance->getAction());

    $instance->setAction('test act');
    $this->assertEquals('test-act', $instance->getAction());

    $instance->setAction('index');
    $this->assertEquals('index', $instance->getAction());
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
  public function testSetModulePrefix(FrontController $instance)
  {
    $instance->setModulePrefix('mod');
    $this->assertEquals('Mod', $instance->getModulePrefix());

    $instance->setModulePrefix('module');
    $this->assertEquals('Module', $instance->getModulePrefix());
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
    // Real case
    $another = realpath(__DIR__.'/../resources/config').'/another.routes.php';
    $instance->setRoutesFile($another);
    $this->assertEquals($another, $instance->getRoutesFile());

    // Default file
    $defaultRoute = realpath(__DIR__.'/../resources/config').'/default.routes.php';
    $instance->setRoutesFile($defaultRoute);
    $this->assertEquals($defaultRoute, $instance->getRoutesFile());

    // Exception case
    $this->expectException(SuitUp\Exception\StructureException::class);
    $instance->setRoutesFile('setting/whatever/name/to/throw/exception.php');
  }

  /**
   * @param FrontController $instance
   * @depends testCreateInstance
   */
  public function testLayoutName(FrontController $instance)
  {
    $this->assertEquals('layout.phtml', $instance->getLayoutName());

    $instance->setLayoutName('the-login');
    $this->assertEquals('the-login.phtml', $instance->getLayoutName());

    $instance->setLayoutName('layout');
    $this->assertEquals('layout.phtml', $instance->getLayoutName());
  }

  /**
   * @param FrontController $instance
   * @depends testCreateInstance
   */
  public function testLayoutPath(FrontController $instance)
  {
    $this->assertEquals('views', $instance->getLayoutPath());

    $instance->setLayoutPath('layout');
    $this->assertEquals('layout', $instance->getLayoutPath());

    $instance->setLayoutPath('views');
    $this->assertEquals('views', $instance->getLayoutPath());
  }

  /**
   * @param FrontController $instance
   * @depends testCreateInstance
   */
  public function testController(FrontController $instance)
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
   * @throws StructureException
   */
  public function testModulesPath(FrontController $instance)
  {
    $this->assertEquals(realpath(__DIR__.'/../resources/modules'), $instance->getModulesPath());

    $instance->setModulesPath(__DIR__.'/../resources');
    $this->assertEquals(realpath(__DIR__.'/../resources'), $instance->getModulesPath());

    $instance->setModulesPath(__DIR__.'/../resources/modules');
    $this->assertEquals(realpath(__DIR__.'/../resources/modules'), $instance->getModulesPath());

    $this->expectException(StructureException::class);
    $instance->setModulesPath('modules');
  }

  /**
   * @param FrontController $instance
   * @depends testCreateInstance
   */
  public function testControllersPath(FrontController $instance)
  {
    $this->assertEquals('Controllers', $instance->getControllersPath());

    $instance->setControllersPath('control');
    $this->assertEquals('control', $instance->getControllersPath());

    $instance->setControllersPath('Controllers');
    $this->assertEquals('Controllers', $instance->getControllersPath());
  }

  /**
   * @param FrontController $instance
   * @depends testCreateInstance
   */
  public function testResolveViewFilename(FrontController $instance)
  {
    $theFile = realpath(__DIR__.'/../resources/modules').'/ModuleDefault/views/index/index.phtml';
    $this->assertEquals($theFile, $instance->resolveViewFilename());
  }

  /**
   * @param FrontController $instance
   * @depends testCreateInstance
   */
  public function testResolveLayoutFilename(FrontController $instance)
  {
    $theFile = realpath(__DIR__.'/../resources/modules').'/ModuleDefault/views/layout.phtml';
    $this->assertEquals($theFile, $instance->resolveLayoutFilename());
  }

  /**
   * @param FrontController $instance
   * @depends testCreateInstance
   */
  public function testViewsPath(FrontController $instance)
  {
    $this->assertEquals('views', $instance->getViewsPath());

    $instance->setViewsPath('TestPath');
    $this->assertEquals('TestPath', $instance->getViewsPath());

    $instance->setViewsPath('views');
    $this->assertEquals('views', $instance->getViewsPath());
  }

  /**
   * @param FrontController $instance
   * @depends testCreateInstance
   */
  public function testModule(FrontController $instance)
  {
    $this->assertEquals('default', $instance->getModule());

    $instance->setModule('admin mod');
    $this->assertEquals('admin-mod', $instance->getModule());

    $instance->setModule('default');
    $this->assertEquals('default', $instance->getModule());
  }

  /**
   * @param FrontController $instance
   * @depends testCreateInstance
   */
  public function testFormPath(FrontController $instance)
  {
    $this->assertEquals('/Form', $instance->getFormPath());

    $instance->setFormPath('the-forms');
    $this->assertEquals('/the-forms', $instance->getFormPath());

    $instance->setFormPath('Form');
    $this->assertEquals('/Form', $instance->getFormPath());
  }

  /**
   * @param FrontController $instance
   * @depends testCreateInstance
   */
  public function testActionName(FrontController $instance)
  {
    $this->assertEquals('indexAction', $instance->getActionName());

    $instance->setActionName('another name');
    $this->assertEquals('anotherNameAction', $instance->getActionName());

    $instance->setActionName('index');
    $this->assertEquals('indexAction', $instance->getActionName());
  }

  /**
   * @param FrontController $instance
   * @depends testCreateInstance
   */
  public function testViewSuffix(FrontController $instance)
  {
    $this->assertEquals('.phtml', $instance->getViewSuffix());

    $instance->setViewSuffix('ccml');
    $this->assertEquals('.ccml', $instance->getViewSuffix());

    $instance->setViewSuffix('phtml');
    $this->assertEquals('.phtml', $instance->getViewSuffix());
  }

  /**
   * @param FrontController $instance
   * @depends testCreateInstance
   */
  public function testSetLayoutSuffix(FrontController $instance)
  {
    $this->assertEquals('.phtml', $instance->getLayoutSuffix());

    $instance->setLayoutSuffix('ccml');
    $this->assertEquals('.ccml', $instance->getLayoutSuffix());

    $instance->setLayoutSuffix('phtml');
    $this->assertEquals('.phtml', $instance->getLayoutSuffix());
  }

  /**
   * @param FrontController $instance
   * @depends testCreateInstance
   */
  public function testControllerName(FrontController $instance)
  {
    $this->assertEquals('IndexController', $instance->getControllerName());

    $instance->setControllerName('album');
    $this->assertEquals('AlbumController', $instance->getControllerName());

    $instance->setControllerName('index');
    $this->assertEquals('IndexController', $instance->getControllerName());
  }

  /**
   * @param FrontController $instance
   * @depends testCreateInstance
   */
  public function testBasePath(FrontController $instance)
  {
    $this->assertEquals('', $instance->getBasePath());

    $instance->setBasePath('/modules');
    $this->assertEquals('/modules', $instance->getBasePath());

    $instance->setBasePath('');
    $this->assertEquals('', $instance->getBasePath());

    // Append
    $this->assertEquals('/assets', $instance->getBasePath('assets'));
  }

  /**
   * @param FrontController $instance
   * @depends testCreateInstance
   */
  public function testMockUpTo(FrontController $instance)
  {
    $newFc = $instance->mockUpTo('add', 'album', 'admin', __DIR__);

    $this->assertEquals('ModuleAdmin', $newFc->getModuleName());
    $this->assertEquals('AlbumController', $newFc->getControllerName());
    $this->assertEquals('addAction', $newFc->getActionName());
    $this->assertEquals(__DIR__, $newFc->getModulePath());

    // Case 2
    $newFc2 = $instance->mockUpTo('add', 'album', 'admin');

    $this->assertEquals('ModuleAdmin', $newFc2->getModuleName());
    $this->assertEquals('AlbumController', $newFc2->getControllerName());
    $this->assertEquals('addAction', $newFc2->getActionName());
    $this->assertEquals($instance->getModulesPath().'/'.$newFc2->getModuleName(), $newFc2->getModulePath());
  }

  /**
   * @param FrontController $instance
   * @depends testCreateInstance
   */
  public function testSetRoutesFileSuffix(FrontController $instance)
  {
    $this->assertEquals('.routes.php', $instance->getRoutesFileSuffix());

    $instance->setRoutesFileSuffix('.pepperoni.phtml');
    $this->assertEquals('.pepperoni.phtml', $instance->getRoutesFileSuffix());

    $instance->setRoutesFileSuffix('.routes.php');
    $this->assertEquals('.routes.php', $instance->getRoutesFileSuffix());
  }

  /**
   * @param FrontController $instance
   * @depends testCreateInstance
   * @throws StructureException
   */
  public function testModulePath(FrontController $instance)
  {
    $modulePath = realpath(__DIR__.'/../resources/modules/ModuleDefault');

    $this->assertEquals($modulePath, $instance->getModulePath());

    $this->expectException(StructureException::class);
    $instance->setModulePath('./test');

    $instance->setModulePath($modulePath);
    $this->assertEquals($modulePath, $instance->getModulePath());
  }

  /**
   * @param FrontController $instance
   * @depends testCreateInstance
   * @throws StructureException
   */
  public function testLogsPath(FrontController $instance)
  {
    $this->assertNull($instance->getLogsPath());

    $instance->setLogsPath(__DIR__.'/../resources/var/log/');
    $this->assertEquals(realpath(__DIR__.'/../resources/var/log/'), $instance->getLogsPath());

    // Exception case
    $this->expectException(StructureException::class);
    $instance->setLogsPath('var/log/');
  }

  /**
   * @param FrontController $instance
   * @depends testCreateInstance
   */
  public function testParams(FrontController $instance)
  {
    $this->assertIsArray($instance->getParams());

    $instance->setParams(array('index1' => 'value1'));

    $this->assertArrayHasKey('index1', $instance->getParams());
    $this->assertContains('value1', $instance->getParams());
  }
}
