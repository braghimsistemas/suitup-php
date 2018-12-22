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

class SuitUp5FormFiltersTest extends \PHPUnit_Framework_TestCase
{
  /**
   * @var TestValidations
   */
  protected $val;

  public function __construct() {
    $this->val = new TestValidations();
  }

  /**
   * Test string method
   */
  public function testString()
  {
    $this->assertEquals('something', $this->val->string('something'));
    $this->assertEquals('<span>something</span>', $this->val->string('  <span>something</span>  '));
    $this->assertEquals(100, $this->val->string('100'));
    $this->assertEquals(100.96, $this->val->string('100.96'));
    $this->assertEquals(true, $this->val->string('1'));
    $this->assertEquals(false, $this->val->string(''));

    $this->assertNotEquals(true, $this->val->string('true'));
    $this->assertNotEquals(false, $this->val->string('false'));
    $this->assertNotEquals(true, $this->val->string('0'));
    $this->assertNotEquals(false, $this->val->string('0'));
    $this->assertNotEquals(true, $this->val->string('something else'));
    $this->assertNotEquals(true, $this->val->string('2'));
  }

  /**
   * Test trim method
   */
  public function testTrim()
  {
    $this->assertEquals('something', $this->val->trim('something'));
    $this->assertEquals('something', $this->val->trim('  something  '));
    $this->assertEquals('something', $this->val->trim('  something'));
    $this->assertEquals('something', $this->val->trim('something  '));
    $this->assertEquals(100, $this->val->trim('100'));
    $this->assertEquals(100.96, $this->val->trim('100.96'));
    $this->assertEquals(true, $this->val->trim('1'));
    $this->assertEquals(false, $this->val->trim(''));

    $this->assertNotEquals('something', $this->val->trim('  <span>something</span>  '));
    $this->assertNotEquals(true, $this->val->trim('true'));
    $this->assertNotEquals(false, $this->val->trim('false'));
    $this->assertNotEquals(true, $this->val->trim('0'));
    $this->assertNotEquals(false, $this->val->trim('0'));
    $this->assertNotEquals(true, $this->val->trim('something else'));
    $this->assertNotEquals(true, $this->val->trim('2'));
  }

  /**
   * Test toDbDate method
   */
  public function testToDbDate()
  {
    $this->assertEquals('2015-12-31', $this->val->toDbDate('31/12/2015'));
    $this->assertEquals(date('Y-m-d'), $this->val->toDbDate(date('d/m/Y')));

    $this->assertNotEquals('2015-12-31', $this->val->toDbDate('31122015'));

    // False positive
    $this->assertEquals('', $this->val->toDbDate(''));
  }

  /**
   * Test digits method
   */
  public function testDigits()
  {
    $this->assertEquals('15549843', $this->val->digits('15549843'));
    $this->assertEquals('15549843', $this->val->digits('"1@5#5%4$9¨8&4*3()-_=+-*/.,?:><[]{}`´\''));
    $this->assertEquals('0123456789', $this->val->digits('"0@1#2%3$4¨5&6*7(8)9-_=+-*/.,?:><[]{}`´\'\\'));

    // False positive
    $this->assertEquals('', $this->val->digits(''));
  }

  /**
   *  toDouble @see SuitUp4FormValidationTest
   */
}


















