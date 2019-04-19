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

use PHPUnit\Framework\TestCase;

final class functionsTest extends TestCase
{
  private $filesDir;

  public function __construct($name = null, array $data = [], $dataName = '') {
    parent::__construct($name, $data, $dataName);

    $this->filesDir = __DIR__ . '/resources/files/functions';
  }

//  public function testDump() {
//
//    $this->assertIsString(dump('test', false));
//    $this->assertEquals("<pre>string(4) \"test\"\n</pre>", dump('test', false));
//  }

  public function testMctime() {
    $this->assertIsFloat(mctime());
  }

  public function testIsClosure() {

    // This is a real closure function
    $func = function($a, $b) { return $a+$b; };
    $this->assertIsBool(isClosure($func));
    $this->assertTrue(isClosure($func));

    // Force other types
    $this->assertNotTrue(isClosure('string'));
    $this->assertNotTrue(isClosure(array()));
    $this->assertNotTrue(isClosure(654));
    $this->assertNotTrue(isClosure(0.0));
    $this->assertNotTrue(isClosure(new \stdClass()));
  }

  public function testToCamelCase() {

    // First lower
    $this->assertEquals('myFearIsMyOnlyCourage', toCamelCase('my fear is my only courage'));
    $this->assertEquals('myFearIsMyOnlyCourage', toCamelCase('my*fear$is%my@only´courage'));

    // First upper
    $this->assertEquals(
      'HowLongWeShallTheyKillOurProphets',
      toCamelCase('how long we shall they kill our prophets', true)
    );
    $this->assertEquals(
      'HowLongWeShallTheyKillOurProphets',
      toCamelCase('how-long+we.shall#they!kill]our°prophets', true)
    );
  }

  public function testToDashCase() {

    $this->assertEquals(
      'most-people-think-great-god-will-come-from-the-sky',
      toDashCase('Most people#think Great@God will come+from¨the SkY')
    );
  }

  public function testRenderView() {

    // Without specify path
    $view1 = renderView($this->filesDir.'/render-view.phtml', array('test' => 'Expected result'));
    $this->assertStringEqualsFile($this->filesDir.'/render-view-result.phtml', $view1);

    // Specifying path
    $view2 = renderView('render-view.phtml', array('test' => 'Expected result'), $this->filesDir);
    $this->assertStringEqualsFile($this->filesDir.'/render-view-result.phtml', $view2);
  }

//  public function testPaginateControl() {
//    // @TODO: implement
//  }

//  public function testGetTraceArgsAsString() {
//    // @TODO: implement
//  }

  public function testFormSelect() {

    $select1 = formSelect('test');
    $this->assertStringEqualsFile($this->filesDir.'/form-select-1.phtml', $select1);

    $select2 = formSelect('test', array('class' => 'form-control'), array('1' => 'Active', '2' => 'Blocked'), '1');
    $this->assertStringEqualsFile($this->filesDir.'/form-select-2.phtml', $select2);

    $select3 = formSelect('test[0][status]', array('class' => 'form-control'), array('' => 'Select one!', '1' => 'Active'), '');
    $this->assertStringEqualsFile($this->filesDir.'/form-select-3.phtml', $select3);
  }

  public function testFormCheckbox() {

    $checkbox1 = formCheckbox('test');
    $this->assertStringEqualsFile($this->filesDir.'/form-checkbox-1.phtml', $checkbox1);

    $checkbox2 = formCheckbox('test', array('class' => 'form-control'), 'yes', false);
    $this->assertStringEqualsFile($this->filesDir.'/form-checkbox-2.phtml', $checkbox2);

    $checkbox3 = formCheckbox('test[other][464]', array('class' => 'form-control'), 'no', true);
    $this->assertStringEqualsFile($this->filesDir.'/form-checkbox-3.phtml', $checkbox3);
  }
}
