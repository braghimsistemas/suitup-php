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

class SuitUp4FormValidationTest extends \PHPUnit\Framework\TestCase
{
  const TARGET = 'target_test';

  /**
   * @var TestValidations
   */
  protected $val;

  public function __construct() {
    parent::__construct();
    
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

    // Message
    $this->assertEquals('Este campo não pode ficar vazio', $this->val->notEmpty('')->message);
    $this->assertEquals('A custom message', $this->val->notEmpty('', 'A custom message')->message);
    $this->assertEquals('A custom message', $this->val->notEmpty('', array('message' => 'A custom message'))->message);
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

    // Message
    $this->assertEquals('Preencha com um endereço de e-mail válido', $this->val->isEmail('no')->message);
    $this->assertEquals('A custom message', $this->val->isEmail('no', 'A custom message')->message);
    $this->assertEquals('A custom message', $this->val->isEmail('no', array('message' => 'A custom message'))->message);
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

    // Message
    $this->assertEquals('Preencha com um número de CEP válido', $this->val->isCep('no')->message);
    $this->assertEquals('A custom message', $this->val->isCep('no', 'A custom message')->message);
    $this->assertEquals('A custom message', $this->val->isCep('no', array('message' => 'A custom message'))->message);
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

    // Invalid
    $this->assertTrue($this->val->minLen('abcd', 5)->error);
    $this->assertTrue($this->val->minLen('abcd', array('size' => 5))->error);
    $this->assertTrue($this->val->minLen(1000, '5')->error);

    // No size
    $this->assertInstanceof('\stdClass', $this->val->minLen(''));
    try{
      $this->val->minLen('something');
    } catch (\Exception $e) {}
    $this->assertInstanceof('\Exception', $e);

    // Messages
    $this->assertEquals('Este campo deve ter pelo menos 5 caractéres', $this->val->minLen('abcd', 5)->message);
    $this->assertEquals('A custom message', $this->val->minLen('abcd', array(
      'message' => 'A custom message',
      'size' => 5,
    ))->message);
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

    // Invalid
    $this->assertTrue($this->val->maxLen('abcd', 1)->error);
    $this->assertTrue($this->val->maxLen('abcd', array('size' => 1))->error);
    $this->assertTrue($this->val->maxLen(1000, '1')->error);

    // No size
    $this->assertInstanceof('\stdClass', $this->val->maxLen(''));
    try {
      $this->val->maxLen('something');
    } catch (\Exception $e) {}
    $this->assertInstanceof('\Exception', $e);

    // Messages
    $this->assertEquals('Este campo não deve ter mais que 1 caractéres', $this->val->maxLen('abcd', 1)->message);
    $this->assertEquals('A custom message', $this->val->maxLen('abcd', array(
      'message' => 'A custom message',
      'size' => 1,
    ))->message);
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

    // No target
    $this->assertInstanceof('\stdClass', $this->val->maiorQue(''));
    try {
      $this->val->maiorQue('something');
    } catch (\Exception $e) {}
    $this->assertInstanceof('\Exception', $e);

    // Message
    $this->assertEquals(
      'Verifique que este campo não pode ser menor que o início',
      $this->val->maiorQue(99, self::TARGET)->message
    );
    $this->assertEquals(
      'A custom message',
      $this->val->maiorQue(99, array('target' => self::TARGET, 'message' => 'A custom message'))->message
    );
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

    // No target
    $this->assertInstanceof('\stdClass', $this->val->menorQue(''));
    try {
      $this->val->menorQue('something');
    } catch (\Exception $e) {}
    $this->assertInstanceof('\Exception', $e);

    // Message
    $this->assertEquals(
      'Verifique que este campo não pode ser maior que o fim',
      $this->val->menorQue(101, self::TARGET)->message
    );
    $this->assertEquals(
      'A custom message',
      $this->val->menorQue(101, array('target' => self::TARGET, 'message' => 'A custom message'))->message
    );
  }

  /**
   * Test Validation identico - identicalTo
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

    // No target
    $this->assertInstanceof('\stdClass', $this->val->identico(''));
    try {
      $this->val->identico('something');
    } catch (\Exception $e) {}
    $this->assertInstanceof('\Exception', $e);

    // Messages
    $this->assertEquals('Campos não são idênticos', $this->val->identico('99', self::TARGET)->message);
    $this->assertEquals('A custom message', $this->val->identico('99', array(
      'message' => 'A custom message',
      'target' => self::TARGET,
    ))->message);
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

    // Messages
    $this->assertEquals('Este valor não está entre as opções', $this->val->inArray('99', array(100))->message);
    $this->assertEquals('A custom message', $this->val->inArray('99', array(
      'message' => 'A custom message',
      'values' => array(100),
    ))->message);
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