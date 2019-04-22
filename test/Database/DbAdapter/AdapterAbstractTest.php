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
 * FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use SuitUp\Database\DbAdapter\AdapterAbstract;

class AdapterAbstractTest extends TestCase
{
  private $stub;

  public function __construct($name = null, array $data = [], $dataName = '')
  {
    parent::__construct($name, $data, $dataName);

    $this->stub = $this->getMockBuilder(AdapterAbstract::class)
      ->disableOriginalConstructor()
      ->getMock();
  }

  public function testValidateParams()
  {
    $this->stub
      ->expects($this->any())
      ->method('validateParams')
      ->will($this->returnValue(null));

    $this->assertNull($this->stub->validateParams(array('pwd' => '3213')));
  }

//  public function testAppendOptions()
//  {
//
//  }
//
//  public function testGetDsn()
//  {
//
//  }
//
//  public function testGetOptions()
//  {
//
//  }
//
//  public function testQuote()
//  {
//
//  }
//
//  public function testGetUsername()
//  {
//
//  }
//
//  public function testGetPassword()
//  {
//
//  }
}
