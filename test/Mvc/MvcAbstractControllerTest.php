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

use SuitUp\Mvc\MvcAbstractController;
use PHPUnit\Framework\TestCase;

class MvcAbstractControllerTest extends TestCase
{
  public function testCreateInstance() {

    $instance = new SuitUpStart(__DIR__.'/../resources/modules');
    $this->assertInstanceOf('SuitUpStart', $instance);

    return $instance;
  }

//  public function testGetActionName()
//  {
//
//  }
//
//  public function testGetViewVar()
//  {
//
//  }
//
//  public function testGetControllerName()
//  {
//
//  }
//
//  public function testGetFrontController()
//  {
//
//  }
//
//  public function testGetParams()
//  {
//
//  }
//
//  public function testGetLayoutName()
//  {
//
//  }
//
//  public function testGetParam()
//  {
//
//  }
//
//  public function testIsLogged()
//  {
//
//  }
//
//  public function testGetModuleName()
//  {
//
//  }
//
//  public function testClearLogin()
//  {
//
//  }
//
//  public function testClearSessionFilter()
//  {
//
//  }
//
//  public function testGetMessages()
//  {
//
//  }
//
//  public function testGetSessionFilter()
//  {
//
//  }
//
//  public function testUpdateLoginKey()
//  {
//
//  }
//
//  public function testGetMsgNsp()
//  {
//
//  }
//
//  public function testGetPost()
//  {
//
//  }
//
//  public function testGetLogin()
//  {
//
//  }
//
//  public function testGetException()
//  {
//
//  }
//
//  public function testIsViewVar()
//  {
//
//  }
//
//  public function testBasePath()
//  {
//
//  }
//
//  public function testIsPost()
//  {
//
//  }
//
//  public function testBaseUrl()
//  {
//
//  }
}
