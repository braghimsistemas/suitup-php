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

use SuitUpStart;

class SuitUp7FunctionsTest extends \PHPUnit_Framework_TestCase
{
  protected $suitUp;
  
  public function __construct()
  {
    $this->suitUp = new SuitUpStart(__DIR__.'/modulestest/');
  }
  
  public function testInstance() {
    $this->assertInstanceof('\SuitUpStart', $this->suitUp);
  }
  
  /**
   *  We'll not create all test cases to this function because
   * it's really impossible to get all situations
   */
  public function testThrowExceptionFromAnywhere() {
    
    $eM = '';
    try {
      throw new \Exception('This is just a test case');
    
    } catch (\Exception $e) {
      $eM = throwNewExceptionFromAnywhere($e, true);
    }
    
    $this->assertEquals('This is just a test case', $eM);
  }
  
  public function testDebug() {
    $this->assertEquals("<pre>string(17) \"Just another test\"\n</pre>", dump('Just another test', false));
  }
  
  public function testMctime()
  {
    $a = mctime();
    
    sleep(1);
    
    $b = mctime() - $a;
    
    $this->assertGreaterThan(1, $b);
    $this->assertLessThan(1.2, $b);
  }
  
  /** The functions bellow it's really hard to test so we'll review it as fast as possible **/
}













