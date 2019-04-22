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

use SuitUp\Database\Business\AbstractBusiness;
use ModuleDefault\Model\ArtistBusiness;
use PHPUnit\Framework\TestCase;
use SuitUp\Database\DbAdapter\AdapterFactory;
use SuitUp\Database\Gateway\AbstractGateway;

class AbstractBusinessTest extends TestCase
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

    $this->bo = new ArtistBusiness();
  }

  public function testInstance() {
    $this->assertInstanceOf('SuitUp\Database\Business\AbstractBusiness', $this->bo);

    $this->expectException(SuitUp\Exception\DatabaseBusinessException::class);
    $this->getMockBuilder('SuitUp\Database\Business\AbstractBusiness')
      ->setConstructorArgs(array())
      ->getMockForAbstractClass();
  }

  public function testGet() {

    $row = $this->bo->get(1);

    $this->assertIsArray($row);
    $this->assertArrayHasKey('pk_artist', $row);
    $this->assertArrayHasKey('name', $row);
  }

  public function testInsert() {

    $id = $this->bo->insert(array(
      'name' => 'Chico Buarque',
      'status' => 0
    ));

    // Get the inserted row
    $row = $this->bo->get($id);

    $this->assertIsArray($row);
    $this->assertArrayHasKey('pk_artist', $row);
    $this->assertArrayHasKey('name', $row);
    $this->assertContains('Chico Buarque', $row);

    return $id;
  }

  /**
   * @depends testInsert
   * @param mixed $id
   * @throws \SuitUp\Exception\DatabaseGatewayException
   */
  public function testUpdate($id) {

    $this->bo->update(array('name' => 'Buarque Chico'), array('pk_artist' => $id));

    // Get the inserted row
    $row = $this->bo->get($id);

    $this->assertIsArray($row);
    $this->assertArrayHasKey('pk_artist', $row);
    $this->assertArrayHasKey('name', $row);
    $this->assertContains('Buarque Chico', $row);
  }

  /**
   * @depends testInsert
   * @param mixed $id
   * @throws \SuitUp\Exception\DatabaseGatewayException
   */
  public function testDelete($id) {

    $this->bo->delete(array('pk_artist' => $id));

    // Get the inserted row
    $this->assertFalse($this->bo->get($id));
  }

  /////------------------- Save

  public function testSaveInsert() {

    $id = $this->bo->save(array(
      'name' => 'Caetano Veloso',
      'status' => 1
    ));

    // Get the inserted row
    $row = $this->bo->get($id);

    $this->assertIsArray($row);
    $this->assertArrayHasKey('pk_artist', $row);
    $this->assertArrayHasKey('name', $row);
    $this->assertContains('Caetano Veloso', $row);

    return $id;
  }

  /**
   * @depends testSaveInsert
   * @param mixed $id
   * @throws \SuitUp\Exception\DatabaseGatewayException
   */
  public function testSaveUpdate($id) {

    $this->bo->save(array(
      'pk_artist' => $id,
      'name' => 'Veloso Caetano',
    ));

    // Get the inserted row
    $row = $this->bo->get($id);

    $this->assertIsArray($row);
    $this->assertArrayHasKey('pk_artist', $row);
    $this->assertArrayHasKey('name', $row);
    $this->assertContains('Veloso Caetano', $row);
  }

  /**
   * @depends testSaveInsert
   * @param mixed $id
   * @throws \SuitUp\Exception\DatabaseGatewayException
   */
  public function testSaveDelete($id) {

    $this->bo->delete(array('pk_artist' => $id));

    // Get the inserted row
    $this->assertFalse($this->bo->get($id));
  }
}
