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

namespace SuitUp\Database;

use SuitUp\Database\DbAdapter\AdapterInterface;
use SuitUp\Exception\DbAdapterException;

/**
 * This class is the connection itself.
 *
 *
 * @package SuitUp\Database
 */
class DbAdapter implements DbAdapterInterface
{
  private $adapter;

  private $connection;

  /**
   * DbAdapter constructor.
   * @param AdapterInterface $adapter
   * @throws DbAdapterException
   */
  public function __construct(AdapterInterface $adapter) {
    $this->adapter = $adapter;

    try {

      // Try to create a PDO connection object
      $connection = new \PDO(
        $adapter->getDsn(),
        $adapter->getUsername(),
        $adapter->getPassword(),
        $adapter->getOptions()
      );

      // Add connection to the instance
      $this->setConnection($connection);

    } catch (\PDOException $e) {
      throw new DbAdapterException("Database connection error: {$e->getMessage()} (DSN: {$adapter->getDsn()})", $e->getCode(), $e);
    }
  }

  public function setAdapter(AdapterInterface $adapter): DbAdapterInterface {
    $this->adapter = $adapter;
    return $this;
  }

  /**
   * @return AdapterInterface|null
   */
  public function getAdapter(): ?AdapterInterface {
    return $this->adapter;
  }

  /**
   * @param \PDO $connection
   * @return DbAdapterInterface
   */
  public function setConnection(\PDO $connection): DbAdapterInterface {
    $this->connection = $connection;
    return $this;
  }

  /**
   * @return \PDO
   */
  public function getConnection(): \PDO {
    return $this->connection;
  }
}
