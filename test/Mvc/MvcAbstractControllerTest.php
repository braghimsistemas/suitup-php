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
//error_reporting(E_ALL | E_STRICT);
//ini_set('display_errors', '1');

use SuitUp\Mvc\MvcAbstractController;
use PHPUnit\Framework\TestCase;

class MvcAbstractControllerTest extends TestCase
{
  public function testCreateInstance() {

    $instance = new SuitUpStart(__DIR__.'/../resources/modules');

    // Add some params
    $instance->getConfig()->setParams(array(
      'keyParam1' => 'valueParam1',
      'keyParam2' => 'valueParam2',
    ));

    // Avoid dispatch output
    ob_start();
    $instance->run();
    ob_get_clean();

    //////////////////////////////////////////////////
    ///                                             //
    /// From Here is like to be inside a controller //
    ///                                             //
    //////////////////////////////////////////////////

    // Add variables to be returned
    $instance->getController()->addViewVar(array(
      'key1' => 'value1',
      'key2' => 'value2',
    ));

    $this->assertInstanceOf('SuitUpStart', $instance);
    return $instance;
  }

  /**
   * @param SuitUpStart $suitup
   * @depends testCreateInstance
   */
  public function testGetActionName(SuitUpStart $suitup)
  {
    $this->assertEquals('indexAction', $suitup->getController()->getActionName());
  }

  /**
   * @param SuitUpStart $suitup
   * @depends testCreateInstance
   */
  public function testGetViewVar(SuitUpStart $suitup)
  {
    $this->assertEquals('value1', $suitup->getController()->getViewVar('key1'));
  }

  /**
   * @param SuitUpStart $suitup
   * @depends testCreateInstance
   */
  public function testGetControllerName(SuitUpStart $suitup)
  {
    $this->assertEquals('IndexController', $suitup->getController()->getControllerName());
  }

  /**
   * @param SuitUpStart $suitup
   * @depends testCreateInstance
   */
  public function testGetFrontController(SuitUpStart $suitup)
  {
    $this->assertInstanceOf('SuitUp\Mvc\FrontController', $suitup->getController()->getFrontController());
  }

  /**
   * @param SuitUpStart $suitup
   * @depends testCreateInstance
   */
  public function testGetParams(SuitUpStart $suitup)
  {
    $this->assertIsArray($suitup->getController()->getParams());
    $this->assertArrayHasKey('keyParam1', $suitup->getController()->getParams());
    $this->assertContains('valueParam1', $suitup->getController()->getParams());
    $this->assertArrayHasKey('keyParam2', $suitup->getController()->getParams());
    $this->assertContains('valueParam2', $suitup->getController()->getParams());
  }

  /**
   * @param SuitUpStart $suitup
   * @depends testCreateInstance
   */
  public function testGetLayoutName(SuitUpStart $suitup)
  {
    $this->assertEquals('layout.phtml', $suitup->getController()->getLayoutName());
  }

  /**
   * @param SuitUpStart $suitup
   * @depends testCreateInstance
   */
  public function testGetParam(SuitUpStart $suitup)
  {
    $this->assertEquals('valueParam1', $suitup->getController()->getParam('keyParam1'));
    $this->assertEquals('valueParam2', $suitup->getController()->getParam('keyParam2'));

    $this->assertFalse($suitup->getController()->getParam('key-false', false));
    $this->assertNull($suitup->getController()->getParam('key-false', null));
    $this->assertIsInt($suitup->getController()->getParam('key-false', 321));
    $this->assertEmpty($suitup->getController()->getParam('key-false', 0));
  }

  /**
   * @param SuitUpStart $suitup
   * @depends testCreateInstance
   */
  public function testGetModuleName(SuitUpStart $suitup)
  {
    $this->assertEquals('ModuleDefault', $suitup->getController()->getModuleName());
  }

  /**
   * @param SuitUpStart $suitup
   * @depends testCreateInstance
   */
  public function testGetSessionFilterNamespace(SuitUpStart $suitup)
  {
    $discovered = $suitup->getController()->getModuleName();
    $discovered .= '.'.$suitup->getController()->getControllerName();
    $discovered .= '.'.$suitup->getController()->getActionName();

    $this->assertEquals($discovered, $suitup->getController()->getSessionFilterNamespace());
  }

  /**
   * @param SuitUpStart $suitup
   * @depends testCreateInstance
   */
  public function testGetSessionFilter(SuitUpStart $suitup)
  {
    $sessionFilter = $suitup->getController()->getSessionFilter();

    $this->assertIsArray($sessionFilter);

    $suitup->getController()->addSessionFilter('filter1', 'valueFilter1');
    $suitup->getController()->addSessionFilter(array(
      'filter2' => 'valueFilter2',
      'filter3' => 'valueFilter3',
      'filter4' => 'valueFilter4',
    ));

    $this->assertArrayHasKey('filter1', $suitup->getController()->getSessionFilter());
    $this->assertContains('valueFilter1', $suitup->getController()->getSessionFilter());
  }

  /**
   * @param SuitUpStart $suitup
   * @depends testCreateInstance
   */
  public function testRemoveSessionFilter(SuitUpStart $suitup)
  {
    $this->assertArrayHasKey('filter1', $suitup->getController()->getSessionFilter());

    $suitup->getController()->removeSessionFilter('filter1');

    $this->assertArrayNotHasKey('filter1', $suitup->getController()->getSessionFilter());
  }

  /**
   * @param SuitUpStart $suitup
   * @depends testCreateInstance
   */
  public function testClearSessionFilter(SuitUpStart $suitup)
  {
    $suitup->getController()->addSessionFilter('filter2', 'valueFilter2');

    $this->assertNotEmpty($suitup->getController()->getSessionFilter());

    $suitup->getController()->clearSessionFilter();

    $this->assertEmpty($suitup->getController()->getSessionFilter());
  }

  /**
   * @param SuitUpStart $suitup
   * @depends testCreateInstance
   */
  public function testGetMessages(SuitUpStart $suitup)
  {
    $this->assertEmpty($suitup->getController()->getMessages());

    $suitup->getController()->addMsg('A message test', \SuitUp\Enum\MsgType::INFO);

    $this->assertNotEmpty($suitup->getController()->getMessages());
  }

  /**
   * @param SuitUpStart $suitup
   * @depends testCreateInstance
   */
  public function testIsPost(SuitUpStart $suitup)
  {
    $this->assertFalse($suitup->getController()->isPost());
  }

  /**
   * @param SuitUpStart $suitup
   * @depends testCreateInstance
   */
  public function testGetPost(SuitUpStart $suitup)
  {
    $this->assertEmpty($suitup->getController()->getPost());
  }

  /**
   * @param SuitUpStart $suitup
   * @depends testCreateInstance
   */
  public function testGetException(SuitUpStart $suitup)
  {
    $this->assertNull($suitup->getController()->getException());
  }

  /**
   * @param SuitUpStart $suitup
   * @depends testCreateInstance
   * @throws Exception
   */
  public function testIsViewVar(SuitUpStart $suitup)
  {
    $this->assertFalse($suitup->getController()->isViewVar('testViewVar'));

    $suitup->getController()->addViewVar('testViewVar', true);

    $this->assertTrue($suitup->getController()->isViewVar('testViewVar'));
  }

  /**
   * @param SuitUpStart $suitup
   * @depends testCreateInstance
   */
  public function testBasePath(SuitUpStart $suitup)
  {
    $this->assertEquals('/', $suitup->getController()->basePath());
    $this->assertEquals('/appended', $suitup->getController()->basePath('/appended'));
  }

  /**
   * @param SuitUpStart $suitup
   * @depends testCreateInstance
   */
  public function testBaseUrl(SuitUpStart $suitup)
  {
    $this->assertEquals('/', $suitup->getController()->baseUrl());
    $this->assertEquals('/appended', $suitup->getController()->baseUrl('appended'));
  }

  /**
   * @param SuitUpStart $suitup
   * @depends testCreateInstance
   */
  public function testGetReferer(SuitUpStart $suitup)
  {
    $this->assertFalse($suitup->getController()->getReferer());
  }

  /**
   * @param SuitUpStart $suitup
   * @depends testCreateInstance
   */
  public function testRenderView(SuitUpStart $suitup)
  {
    $thePath = __DIR__.'/../resources/files/mvc/mvc-abstract-controller/';
    $html = $suitup->getController()->renderView(
      'test-render-view.phtml',
      array('id' => 321),
      $thePath
    );

    $this->assertEquals("<h1>The Rendered view</h1><p>The id is: 321</p>\r\n", $html);
  }

  //////////////////////////////////////////// Static methods

  public function testIsLoggedAndSetLogin()
  {
    $this->assertFalse(MvcAbstractController::isLogged());

    MvcAbstractController::setLogin(array('some' => 'login data'));

    $this->assertTrue(MvcAbstractController::isLogged());
  }

  public function testGetLogin()
  {
    $this->assertIsArray(MvcAbstractController::getLogin());

    $this->assertEquals('login data', MvcAbstractController::getLogin('some'));
    $this->assertFalse(MvcAbstractController::getLogin('some-not-set', false));
    $this->assertNull(MvcAbstractController::getLogin('some-not-set', null));
    $this->assertIsInt(MvcAbstractController::getLogin('some-not-set', 321));
    $this->assertEmpty(MvcAbstractController::getLogin('some-not-set', 0));
  }

  public function testUpdateLoginKey()
  {
    $this->assertNotEquals('altered data', MvcAbstractController::getLogin('some'));

    MvcAbstractController::updateLoginKey('some', 'altered data');

    $this->assertEquals('altered data', MvcAbstractController::getLogin('some'));
  }

  public function testClearLogin()
  {
    $this->assertIsArray(MvcAbstractController::getLogin());

    MvcAbstractController::clearLogin();

    $this->assertEmpty(MvcAbstractController::getLogin());
  }

}
