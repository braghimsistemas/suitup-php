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

use PDO;
use SuitUp\Database\DbAdapter\AdapterAbstract;
use SuitUp\Database\DbAdapterInterface;

/**
 * Interface DbAdapterInterface
 * @package SuitUp\Database
 */
interface DbAdapterInterface
{
  /**
   * Must to setup the adapter database PDO type
   *
   * DbAdapterInterface constructor.
   * @param AdapterAbstract $adapter
   */
  public function __construct(AdapterAbstract $adapter);

  /**
   * Must return the adapter performed
   *
   * @return AdapterAbstract|null
   */
  public function getAdapter(): ?AdapterAbstract;

  /**
   * Must return a PDO connection instance
   *
   * @return PDO
   */
  public function getConnection(): PDO;

  /**
   * Bind one parameter to the followed query
   *
   * @param string $name
   * @param $value
   * @return DbAdapterInterface
   */
  public function bind(string $name, $value): DbAdapterInterface;

  /**
   * The list of parameters set
   *
   * @return array
   */
  public function getParams(): array;

  /**
   * Must to clear all params
   *
   * @return DbAdapterInterface
   */
  public function clearParams(): DbAdapterInterface;

  /**
   * Must to perform whatever query in the database and return its result
   *
   * @param string $query
   * @param array $params
   * @param int $fetchMode
   * @return mixed
   */
  public function query(string $query, array $params = array(), int $fetchMode = PDO::FETCH_ASSOC);

  /**
   * Must to return only the first row with the SQL Query.
   *
   * @param string $query
   * @param array $params
   * @param int $fetchMode
   * @return mixed
   */
  public function row(string $query, array $params = array(), int $fetchMode = PDO::FETCH_ASSOC);

  /**
   * Must to return only the first column of first row with the SQL Query.
   *
   * @param string $query
   * @param array $params
   * @param int $columnNumber
   * @return mixed
   */
  public function single(string $query, array $params = array(), int $columnNumber = 0);

  /**
   * Must to return the result set in pairs. For that your SQL Query can SELECT only two columns.
   *
   * @param string $query
   * @param array $params
   * @return mixed
   */
  public function pairs(string $query, array $params = array());

  /**
   * The last inserted ID.
   *
   * @return mixed
   */
  public function lastInsertId();
}
