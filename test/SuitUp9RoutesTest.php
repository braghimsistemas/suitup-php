<?php
/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2016 Braghim Sistemas
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
namespace SuitUpTest;

use SuitUp\Routes\Routes;
use SuitUpStart;

class SuitUp9RoutesTest extends \PHPUnit_Framework_TestCase
{
  protected $app;
  protected $routes;
  
  public function __construct()
  {
    $this->app = new SuitUpStart(__DIR__ . '/modulestest');
    
    $this->routes = Routes::getInstance();
  }
  
  public function testConsts()
  {
    $this->assertEquals('reverse', Routes::TYPE_REVERSE);
    $this->assertEquals('linear', Routes::TYPE_LINEAR);
  }
  
  public function testInstance()
  {
    $this->assertInstanceof('\SuitUp\Routes\Routes', $this->routes);
  }
  
  public function testParams()
  {
    $this->assertCount(0, $this->routes->getParams());
    $this->assertEquals('default', $this->routes->getModuleName());
    $this->assertEquals('index', $this->routes->getControllerName());
    $this->assertEquals('index', $this->routes->getActionName());
  }
  
}
  
  
  
  
  
  
  
  