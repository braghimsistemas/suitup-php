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

class DbAdapterTest extends TestCase
{
  public function testCreateInstance() {

    if (IS_TRAVIS_CI) {

      // Running tests from TRAVIS CI
      $adapter = new Mysql(array(
        'host' => '0.0.0.0',
        'port' => '3306',
        'dbname' => 'suitup',
        'username' => 'root',
        'password' => ''
      ));

    } else {

      // Running tests from local machine with docker
      $adapter = new Mysql(array(
        'host' => '127.0.0.1',
        'port' => '3406',
        'dbname' => 'suitup',
        'username' => 'root',
        'password' => '142536'
      ));
    }

    // Create and test instance
    $db = new DbAdapter($adapter);
    $this->assertInstanceOf('SuitUp\Database\DbAdapterInterface', $db);

    return $db;
  }

  /**
   * @depends testCreateInstance
   * @param DbAdapterInterface $db
   */
  public function testConnection(DbAdapterInterface $db)
  {
    $this->assertInstanceOf('PDO', $db->getConnection());
  }

  /**
   * @depends testCreateInstance
   * @param DbAdapterInterface $db
   */
  public function testAdapter(DbAdapterInterface $db)
  {
    $this->assertInstanceOf('SuitUp\Database\DbAdapter\AdapterInterface', $db->getAdapter());
  }
}
