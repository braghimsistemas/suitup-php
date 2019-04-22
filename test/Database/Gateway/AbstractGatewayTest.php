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
declare(strict_types=1);

use \ModuleDefault\Model\MusicBusiness;
use SuitUp\Database\DbAdapter;
use SuitUp\Database\Gateway\AbstractGateway;
use PHPUnit\Framework\TestCase;
use SuitUp\Exception\DatabaseGatewayException;

class AbstractGatewayTest extends TestCase
{
  private $bo;

  public function __construct($name = null, array $data = [], $dataName = '')
  {
    parent::__construct($name, $data, $dataName);

    $suitup = new SuitUpStart(__DIR__.'/../../resources/modules/');

    // Doesn't matter the output
    ob_start();
    $suitup->run();
    ob_clean();

    $this->bo = new MusicBusiness();
  }

  public function testConstructorWithoutArgs() {

    // Exception because there's no Gateway to populate name and primary attributes
    $this->expectException(SuitUp\Exception\DatabaseGatewayException::class);

    $this->getMockBuilder(AbstractGateway::class)
      ->setConstructorArgs(array())
      ->getMockForAbstractClass();
  }

  public function testConstructorWithArgs() {

    // Exception because there's no Gateway to populate name and primary attributes
    $this->expectException(SuitUp\Exception\DatabaseGatewayException::class);

    // Try to get the PHP file content
    $content = require __DIR__.'/../../resources/config/database.config.php';

    // Append to the Gateway as a Default Adapter
    $adapter = DbAdapter::factory($content);

    $this->getMockBuilder(AbstractGateway::class)
      ->setConstructorArgs(array($adapter))
      ->getMockForAbstractClass();
  }

  public function testSelect1() {
    $query = $this->bo->gateway()->select('SELECT m.* FROM music m')
      ->where('status = ?', 1);

    $this->assertEquals('SELECT m.* FROM music m WHERE (status = 1)', $query->__toString());
  }

  public function testSelect2() {
    $query = $this->bo->gateway()->select(array('m.*'))
      ->from('music m')
      ->where('status = ?', 1);

    $this->assertEquals('SELECT m.* FROM music m WHERE (status = 1)', $query->__toString());
  }
}
