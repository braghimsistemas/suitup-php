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

use http\QueryString;
use SuitUp\Database\DbAdapter\AdapterInterface;
use SuitUp\Database\Gateway\AbstractGateway;
use SuitUp\Exception\DatabaseGatewayException;
use SuitUp\Exception\DbAdapterException;
use SuitUp\Exception\QueryTypeException;

/**
 * This class is the connection itself.
 *
 *
 * @package SuitUp\Database
 */
class DbAdapter implements DbAdapterInterface
{
  const PARAM_SEPARATOR = "\x7F";

  /**
   * @var AdapterInterface
   */
  private $adapter;

  /**
   * @var \PDO
   */
  private $connection;

  /**
   * Query parameters to be bind to the statement
   *
   * @var array
   */
  private $params = array();

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

      # We can now log any exceptions on Fatal error.
      $connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

      # Disable emulation of prepared statements, use REAL prepared statements instead.
      $connection->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);

      // Add connection to the instance
      $this->connection = $connection;

      if (AbstractGateway::getDefaultAdapter() == null) {
        AbstractGateway::setDefaultAdapter($this);
      }

    } catch (\PDOException $e) {
      throw new DbAdapterException("Database connection error: {$e->getMessage()}<br/>DSN: {$adapter->getDsn()}", $e->getCode(), $e);
    }
  }

  /**
   * @return AdapterInterface|null
   */
  public function getAdapter(): ?AdapterInterface {
    return $this->adapter;
  }

  /**
   * @return \PDO
   */
  public function getConnection(): \PDO {
    return $this->connection;
  }

  /**
   * Add to the query the parameters
   *
   * @param string $name
   * @param $value
   * @return DbAdapterInterface
   */
  public function bind(string $name, $value): DbAdapterInterface {
    $this->params[sizeof($this->params)] = ":".$name.self::PARAM_SEPARATOR.$value;
    return $this;
  }

  /**
   * Alias to bind method
   *
   * @see bind
   * @param string $name
   * @param $value
   * @return DbAdapterInterface
   */
  public function param(string $name, $value): DbAdapterInterface {
    $this->bind($name, $value);
    return $this;
  }

  /**
   * Alias to bind method
   *
   * @param string $name
   * @param $value
   * @return DbAdapterInterface
   */
  public function setParam(string $name, $value): DbAdapterInterface {
    $this->bind($name, $value);
    return $this;
  }

  /**
   * Return the list of bind params.
   *
   * @return array
   */
  public function getParams(): array {
    return $this->params;
  }

  /**
   * This method will get the query statement, append the parameters and return
   * the result with the fetch mode defined.
   *
   * @param string $query
   * @param array $params
   * @param int $fetchMode
   * @throws \Exception
   * @return array|int|null
   */
  public function query(string $query, array $params = array(), int $fetchMode = \PDO::FETCH_ASSOC) {

    // Prepare the statement
    $stmt = $this->getConnection()->prepare(trim($query));

    // Append params if need
    foreach ($params as $name => $value) {
      $this->bind($name, $value);
    }

    // Bind parameters
    foreach ($this->getParams() as $param) {

      $parameters = explode(self::PARAM_SEPARATOR, $param);
      $stmt->bindParam($parameters[0], $parameters[1]);
    }

    // Get the first instruction to let know what kind of query it is
    $rawStmtParts = explode(" ", trim($query));
    $stmtType = strtolower($rawStmtParts[0]);

    try {

      // Let's do it baby!
      $stmt->execute();

      $result = null;
      switch ($stmtType) {
        case 'select':
        case 'show':

          $result = $stmt->fetchAll($fetchMode);
          break;
        case 'insert':
        case 'update':
        case 'delete':

          $result = $stmt->rowCount();
          break;
        default:
          throw new QueryTypeException('The statement does not seems to be a valid SQL Query');
      }
    } catch (\Throwable $exception) {
      throw new DatabaseGatewayException('Database QUERY error: '.$exception->getMessage(), 0, $exception);
    }

    // Clean params array
    $this->params = array();

    return $result;
  }

  /**
   * Alias to the query method.
   * This method will get the query statement, append the parameters and return
   * the result with the fetch mode defined.
   *
   * @see query
   * @param string $query
   * @param array $params
   * @param int $fetchMode
   * @return array|int|null
   * @throws \Exception
   */
  public function fetchAll(string $query, array $params = array(), int $fetchMode = \PDO::FETCH_ASSOC) {
    return $this->query($query, $params, $fetchMode);
  }

  /**
   * Return only the first row found by the query.
   *
   * @param string $query
   * @param array $params
   * @param int $fetchMode
   * @return mixed
   * @throws DatabaseGatewayException
   */
  public function row(string $query, array $params = array(), int $fetchMode = \PDO::FETCH_ASSOC) {
    // Prepare the statement
    $stmt = $this->getConnection()->prepare(trim($query));

    // Append params if need
    foreach ($params as $name => $value) {
      $this->bind($name, $value);
    }

    // Bind parameters
    foreach ($this->getParams() as $param) {

      $parameters = explode(self::PARAM_SEPARATOR, $param);
      $stmt->bindParam($parameters[0], $parameters[1]);
    }

    try {

      // Let's do it baby!
      $stmt->execute();
      $result = $stmt->fetch($fetchMode);

    } catch (\Throwable $exception) {
      throw new DatabaseGatewayException('Database QUERY error: '.$exception->getMessage(), 0, $exception);
    }

    // Clean params array
    $this->params = array();

    return $result;
  }

  /**
   * Alias to the row method.
   * Return only the first row found by the query.
   *
   * @see row
   * @param string $query
   * @param array $params
   * @param int $fetchMode
   * @return mixed
   * @throws DatabaseGatewayException
   */
  public function fetchRow(string $query, array $params = array(), int $fetchMode = \PDO::FETCH_ASSOC) {
    return $this->row($query, $params, $fetchMode);
  }

  /**
   * Fetch only the given or first column from the statement. <b>Only the first row</b>
   *
   * @param string $query
   * @param array $params
   * @param int $columnNumber
   * @return mixed
   * @throws DatabaseGatewayException
   */
  public function single(string $query, array $params = array(), int $columnNumber = 0) {
    // Prepare the statement
    $stmt = $this->getConnection()->prepare(trim($query));

    // Append params if need
    foreach ($params as $name => $value) {
      $this->bind($name, $value);
    }

    // Bind parameters
    foreach ($this->getParams() as $param) {

      $parameters = explode(self::PARAM_SEPARATOR, $param);
      $stmt->bindParam($parameters[0], $parameters[1]);
    }

    try {

      // Let's do it baby!
      $stmt->execute();
      $result = $stmt->fetchColumn($columnNumber);

    } catch (\Throwable $exception) {
      throw new DatabaseGatewayException('Database QUERY error: '.$exception->getMessage(), 0, $exception);
    }

    // Clean params array
    $this->params = array();

    return $result;
  }

  /**
   * Alias to the single method.
   * Fetch only the given or first column from the statement. <b>Only the first row</b>.
   *
   * @see single
   * @param string $query
   * @param array $params
   * @param int $columnNumber
   * @return mixed
   * @throws DatabaseGatewayException
   */
  public function fetchSingle(string $query, array $params = array(), int $columnNumber = 0) {
    return $this->single($query, $params, $columnNumber);
  }

  /**
   * Return the rows from the query in pairs. Requires the statement to fetch only 2 columns.
   *
   * @param string $query
   * @param array $params
   * @return array
   * @throws DatabaseGatewayException
   */
  public function pairs(string $query, array $params = array()) {

    // Prepare the statement
    $stmt = $this->getConnection()->prepare(trim($query));

    // Append params if need
    foreach ($params as $name => $value) {
      $this->bind($name, $value);
    }

    // Bind parameters
    foreach ($this->getParams() as $param) {

      $parameters = explode(self::PARAM_SEPARATOR, $param);
      $stmt->bindParam($parameters[0], $parameters[1]);
    }

    try {

      // Let's do it baby!
      $stmt->execute();
      $result = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);

    } catch (\Throwable $exception) {
      throw new DatabaseGatewayException('Database QUERY error: '.$exception->getMessage(), 0, $exception);
    }

    // Clean params array
    $this->params = array();

    return $result;
  }

  /**
   * Alias to the pairs method.
   * Return the rows from the query in pairs. Requires the statement to fetch only 2 columns.
   *
   * @see pairs
   * @param string $query
   * @param array $params
   * @return array
   * @throws DatabaseGatewayException
   */
  public function fetchPairs(string $query, array $params = array()) {
    return $this->pairs($query, $params);
  }

  /**
   *  Returns the last inserted id.
   *  @return string
   */
  public function lastInsertId() {
    return $this->getConnection()->lastInsertId();
  }
}
