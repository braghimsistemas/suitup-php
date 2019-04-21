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

use PHPUnit\Framework\TestCase;
use SuitUp\Database\DbAdapter;
use SuitUp\Database\DbAdapter\Mysql;
use SuitUp\Database\DbAdapterInterface;
use SuitUp\Exception\DbAdapterException;

final class DbAdapterTest extends TestCase
{
  public function testCreateInstanceException() {

    $this->expectException(DbAdapterException::class);

    // This connection must to be refused
    new DbAdapter(new Mysql(array(
      'host' => '0.0.0.0',
      'port' => '8822',
      'dbname' => 'universe',
      'username' => 'snoop',
      'password' => 'notset'
    )));
  }

  public function testCreateInstance() {

    // Create Mysql Adapter
    $adapter = new Mysql(array(
      'host' =>     IS_TRAVIS_CI ? '127.0.0.1' : '127.0.0.1',
      'port' =>     IS_TRAVIS_CI ? '3306'      : '3406',
      'dbname' =>   IS_TRAVIS_CI ? 'suitup'    : 'suitup',
      'username' => IS_TRAVIS_CI ? 'root'      : 'root',
      'password' => IS_TRAVIS_CI ? ''          : '142536'
    ));

    // Create and test instance
    $db = new DbAdapter($adapter);
    $this->assertInstanceOf('SuitUp\Database\DbAdapterInterface', $db);

    return $db;
  }

  /**
   * @depends testCreateInstance
   * @param DbAdapter $db
   */
  public function testConnection(DbAdapter $db)
  {
    $this->assertInstanceOf('PDO', $db->getConnection());
  }

  /**
   * @depends testCreateInstance
   * @param DbAdapter $db
   */
  public function testAdapter(DbAdapter $db)
  {
    $this->assertInstanceOf('SuitUp\Database\DbAdapter\AdapterInterface', $db->getAdapter());
    $this->assertInstanceOf('SuitUp\Database\DbAdapter\AdapterAbstract', $db->getAdapter());
  }

  /**
   * @depends testCreateInstance
   * @param DbAdapter $db
   */
  public function testParameters(DbAdapter $db)
  {
    $this->assertEmpty($db->getParams());

    $db->bind('column1', 'value1');
    $this->assertContains(':column1'.DbAdapter::PARAM_SEPARATOR.'value1', $db->getParams());

    $db->param('column2', 'value2');
    $this->assertContains(':column2'.DbAdapter::PARAM_SEPARATOR.'value2', $db->getParams());

    $db->setParam('column3', 'value3');
    $this->assertContains(':column3'.DbAdapter::PARAM_SEPARATOR.'value3', $db->getParams());
  }

  /**
   * @depends testCreateInstance
   * @param DbAdapter $db
   * @throws Exception
   */
  public function testQuery(DbAdapter $db)
  {
    $db->clearParams();

    // It depends on database data from resources/files/mysql-database-test.sql
    $result1 = $db->query("SELECT * FROM artist");
    $this->assertIsArray($result1);

    $result2 = $db->fetchAll("SELECT * FROM artist");
    $this->assertIsArray($result2);
  }

  /**
   * @depends testCreateInstance
   * @param DbAdapter $db
   * @throws Exception
   */
  public function testRow(DbAdapter $db)
  {
    $db->clearParams();

    // It depends on database data from resources/files/mysql-database-test.sql
    $result1 = $db->row("SELECT * FROM artist");
    $this->assertIsArray($result1);
    $this->assertArrayHasKey('pk_artist', $result1);
    $this->assertArrayHasKey('name', $result1);

    $result2 = $db->fetchRow("SELECT * FROM artist");
    $this->assertIsArray($result2);
    $this->assertArrayHasKey('pk_artist', $result2);
    $this->assertArrayHasKey('name', $result2);
  }

  /**
   * @depends testCreateInstance
   * @param DbAdapter $db
   * @throws Exception
   */
  public function testSingle(DbAdapter $db)
  {
    $db->clearParams();

    // It depends on database data from resources/files/mysql-database-test.sql
    $result1 = $db->single("SELECT name FROM artist WHERE pk_artist = :id", array('id' => '2'));
    $this->assertEquals('Natiruts', $result1);

    $db->clearParams();

    $result2 = $db->fetchSingle("SELECT name FROM artist WHERE pk_artist = 3");
    $this->assertEquals('Bob Marley', $result2);
  }

  /**
   * @depends testCreateInstance
   * @param DbAdapter $db
   * @throws Exception
   */
  public function testPairs(DbAdapter $db)
  {
    $db->clearParams();

    // It depends on database data from resources/files/mysql-database-test.sql
    $result1 = $db->pairs("SELECT pk_artist, name FROM artist WHERE pk_artist = 3");
    $this->assertIsArray($result1);
    $this->assertArrayHasKey('3', $result1);
    $this->assertContains('Bob Marley', $result1);

    $db->clearParams();

    $result2 = $db->fetchPairs("SELECT pk_artist, name FROM artist");
    $this->assertIsArray($result2);
    $this->assertArrayHasKey('2', $result2);
    $this->assertContains('Natiruts', $result2);
  }

  /**
   * @depends testCreateInstance
   * @param DbAdapter $db
   * @throws Exception
   */
  public function testLastInsertId(DbAdapter $db)
  {
    $this->assertEmpty($db->lastInsertId());
  }
}
