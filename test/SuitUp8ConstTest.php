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

use SuitUp\Enum;

class SuitUp8ConstTest extends \PHPUnit_Framework_TestCase
{
  public function testMsgTypes()
  {
    $this->assertEquals('success', Enum\MsgType::SUCCESS);
    $this->assertEquals('danger', Enum\MsgType::DANGER);
    $this->assertEquals('info', Enum\MsgType::INFO);
    $this->assertEquals('warning', Enum\MsgType::WARNING);
  }
  
  public function testStatuses()
  {
    $this->assertEquals(0, Enum\Status::INATIVO);
    $this->assertEquals(1, Enum\Status::ATIVO);
    $this->assertEquals(2, Enum\Status::NAO_APROVADO);
  }
}




