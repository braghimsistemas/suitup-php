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

use SuitUpTest\Forms\TestValidations;
use SuitUpStart;

class SuitUp4FormValidationTest extends \PHPUnit_Framework_TestCase
{
  const TARGET = 'target_test';
  
  /**
   * @var TestValidations
   */
  protected $val;
  
  public function __construct() {
    $this->val = new TestValidations();
    
    $this->val->post[self::TARGET] = 100;
  }
  
  /**
   * Test Validation notEmpty
   */
  public function testNotEmpty()
  {
    // Error = true
    $this->assertTrue($this->val->notEmpty('')->error);
    $this->assertTrue($this->val->notEmpty('0')->error);
    $this->assertTrue($this->val->notEmpty(0)->error);
    $this->assertTrue($this->val->notEmpty(0.0)->error);
    $this->assertTrue($this->val->notEmpty(false)->error);
    $this->assertTrue($this->val->notEmpty(array())->error);
    $this->assertTrue($this->val->notEmpty(null)->error);
    
    // Error = false
    $this->assertFalse($this->val->notEmpty('Something')->error);
    $this->assertFalse($this->val->notEmpty('1')->error);
    $this->assertFalse($this->val->notEmpty(1)->error);
    $this->assertFalse($this->val->notEmpty(0.1)->error);
    $this->assertFalse($this->val->notEmpty(true)->error);
    $this->assertFalse($this->val->notEmpty(array('something'))->error);
  }
  
  /**
   *  Test validation isEmail
   */
  public function testIsEmail()
  {
    // Is email
    $this->assertFalse($this->val->isEmail('braghim.sistemas@gmail.com')->error);
    
    // Don't is email
    $this->assertTrue($this->val->isEmail('braghim.sistemas@com')->error);
    $this->assertTrue($this->val->isEmail('braghim.sistemas')->error);
    $this->assertTrue($this->val->isEmail('@gmail.com')->error);
    
    // Does nothing (false positive)
    $this->assertFalse($this->val->isEmail('')->error);
  }
  
  /**
   * Test Validation isCep
   */
  public function testIsCep()
  {
    // Valid CEP format
    $this->assertFalse($this->val->isCep('99999-999')->error);
    $this->assertFalse($this->val->isCep('99999999')->error);
    
    // Invalid CEP format
    $this->assertTrue($this->val->isCep('9')->error);
    $this->assertTrue($this->val->isCep('999999999999999999999999999')->error);
    $this->assertTrue($this->val->isCep('something')->error);
    $this->assertTrue($this->val->isCep('a99999999b')->error);
    $this->assertTrue($this->val->isCep('a99999-999b')->error);
    
    // Does nothing (false positive)
    $this->assertFalse($this->val->isCep('')->error);
  }
  
  /**
   * Test Validation minLen
   */
  public function testMinLen()
  {
    // Valid
    $this->assertFalse($this->val->minLen('abcd', 4)->error);
    $this->assertFalse($this->val->minLen('abcd', array('size' => 4))->error);
    $this->assertFalse($this->val->minLen(1000, '4')->error);
    $this->assertEquals('A message', $this->val->minLen('abcd', array(
      'size' => 5,
      'message' => 'A message'
    ))->message);
    
    // Invalid
    $this->assertTrue($this->val->minLen('abcd', 5)->error);
    $this->assertTrue($this->val->minLen('abcd', array('size' => 5))->error);
    $this->assertTrue($this->val->minLen(1000, '5')->error);
    $this->assertNotEquals('A wrong message', $this->val->minLen('abcd', array(
      'size' => 5,
      'message' => 'A message'
    ))->message);
    
    // Does nothing (false positive)
    $this->assertFalse($this->val->minLen('', 0)->error);
  }
  
  /**
   * Test Validation maxLen
   */
  public function testMaxLen()
  {
    // Valid
    $this->assertFalse($this->val->maxLen('abcd', 4)->error);
    $this->assertFalse($this->val->maxLen('abcd', array('size' => 4))->error);
    $this->assertFalse($this->val->maxLen(1000, '4')->error);
    
    // Error message
    $this->assertEquals('A message', $this->val->maxLen('abcd', array(
      'size' => 1,
      'message' => 'A message'
    ))->message);
    
    // Invalid
    $this->assertTrue($this->val->maxLen('abcd', 1)->error);
    $this->assertTrue($this->val->maxLen('abcd', array('size' => 1))->error);
    $this->assertTrue($this->val->maxLen(1000, '1')->error);
    
    // Error message
    $this->assertNotEquals('A wrong message', $this->val->maxLen('abcd', array(
      'size' => 1,
      'message' => 'A message'
    ))->message);
    
    // Does nothing (false positive)
    $this->assertFalse($this->val->maxLen('', 0)->error);
  }
  
  /**
   * Test Validation maiorQue
   */
  public function testMaiorQue()
  {
    // Valid
    $this->assertFalse($this->val->maiorQue(101, self::TARGET)->error);
    $this->assertFalse($this->val->maiorQue(101.3, self::TARGET)->error);
    $this->assertFalse($this->val->maiorQue('101', self::TARGET)->error);
    $this->assertFalse($this->val->maiorQue('101,99', self::TARGET)->error);
    
    // Invalid
    $this->assertTrue($this->val->maiorQue(99, self::TARGET)->error);
    $this->assertTrue($this->val->maiorQue(99.3, self::TARGET)->error);
    $this->assertTrue($this->val->maiorQue('99', self::TARGET)->error);
    $this->assertTrue($this->val->maiorQue('99,99', self::TARGET)->error);
    
    // Does nothing (false positive)
    $this->assertFalse($this->val->maiorQue('0', 0)->error);
  }
  
  /**
   * Test Validation menorQue
   */
  public function testMenorQue()
  {
    // Valid
    $this->assertFalse($this->val->menorQue(99, self::TARGET)->error);
    $this->assertFalse($this->val->menorQue(99.3, self::TARGET)->error);
    $this->assertFalse($this->val->menorQue('99', self::TARGET)->error);
    $this->assertFalse($this->val->menorQue('99,99', self::TARGET)->error);
    
    // Invalid
    $this->assertTrue($this->val->menorQue(101, self::TARGET)->error);
    $this->assertTrue($this->val->menorQue(101.3, self::TARGET)->error);
    $this->assertTrue($this->val->menorQue('101', self::TARGET)->error);
    $this->assertTrue($this->val->menorQue('101,99', self::TARGET)->error);
    
    // Does nothing (false positive)
    $this->assertFalse($this->val->menorQue('0', 0)->error);
  }
  
  /**
   * Test Validation identico
   */
  public function testIdentico()
  {
    // Valid
    $this->assertFalse($this->val->identico('100', self::TARGET)->error);
    $this->assertFalse($this->val->identico(100, self::TARGET)->error);
    $this->assertFalse($this->val->identico((float) 100, self::TARGET)->error);
    $this->assertFalse($this->val->identico((double) 100, self::TARGET)->error);
    
    // Invalid
    $this->assertTrue($this->val->identico('99', self::TARGET)->error);
    $this->assertTrue($this->val->identico(99, self::TARGET)->error);
    $this->assertTrue($this->val->identico((float) 99.9, self::TARGET)->error);
    $this->assertTrue($this->val->identico((double) 99.9, self::TARGET)->error);
    
    // Does nothing (false positive)
    $this->assertFalse($this->val->identico('', '')->error);
  }
  
  /**
   * Test Validation identico
   */
  public function testInArray()
  {
    // Valid
    $this->assertFalse($this->val->inArray('100', array(100))->error);
    $this->assertFalse($this->val->inArray(100, array(100))->error);
    $this->assertFalse($this->val->inArray((float) 100, array(100))->error);
    $this->assertFalse($this->val->inArray((double) 100, array(100))->error);
    
    // Invalid
    $this->assertTrue($this->val->inArray('99', array(100))->error);
    $this->assertTrue($this->val->inArray(99, array(100))->error);
    $this->assertTrue($this->val->inArray((float) 99.9, array(100))->error);
    $this->assertTrue($this->val->inArray((double) 99.9, array(100))->error);
    
    // Does nothing (false positive)
    $this->assertFalse($this->val->inArray('')->error);
  }
  
  /**
   * Test Validation toDouble
   */
  public function testToDouble()
  {
    // Valid
    $this->assertEquals((float) 100, $this->val->toDouble('100'));
    $this->assertEquals((float) 100, $this->val->toDouble(100));
    $this->assertEquals((float) 100, $this->val->toDouble((float) 100));
    $this->assertEquals((float) 100, $this->val->toDouble((double) 100));
    
    // Invalid
    $this->assertNotEquals((float) 100, $this->val->toDouble('99'));
    $this->assertNotEquals((float) 100, $this->val->toDouble(99));
    $this->assertNotEquals((float) 100, $this->val->toDouble((float) 99.9));
    $this->assertNotEquals((float) 100, $this->val->toDouble((double) 99.9));
  }
}