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

namespace SuitUp\Database\DbAdapter;

use stdClass;
use SuitUp\Database\DbAdapter\AdapterAbstract;
use SuitUp\Exception\DbAdapterException;

/**
 * Class Postgres
 *
 * @package SuitUp\Database\DbAdapter
 */
class Postgres extends AdapterAbstract
{

  /**
   * Postgres constructor.
   *
   * @param array $parameters
   * @throws DbAdapterException
   */
  public function __construct(array $parameters) {

    // Check if user setup parameters as right
    $this->validateParams($parameters);

    // The heart of matter
    $params = new stdClass();
    $params->host = $parameters['host'] ?? 'localhost';
    $params->port = $parameters['port'] ?? '5432';
    $params->dbname = $parameters['dbname'] ?? null;

    // Setup dsn string
    $this->setDsn("pgsql:host={$params->host};port={$params->port};dbname={$params->dbname}");

    // User and options config
    $this->setUsername($parameters['username'] ?? 'root');
    $this->setPassword($parameters['password'] ?? '');
    $this->appendOptions($parameters['options'] ?? array());
  }

  public function __toString() {
    // @TODO: Implement
  }

  public function column(string $name, mixed $alias = null): AdapterAbstract {
    // @TODO: Implement
  }

  public function columns(array $columns): AdapterAbstract {
    // @TODO: Implement
  }

  public function from($table, mixed $schema = null): AdapterAbstract {
    // @TODO: Implement
  }

  public function fullInnerJoin(string $table, string $onClause, mixed $schema = null): AdapterAbstract {
    // @TODO: Implement
  }

  public function fullOuterJoin(string $table, string $onClause, mixed $schema = null): AdapterAbstract {
    // @TODO: Implement
  }

  public function group($column): AdapterAbstract {
    // @TODO: Implement
  }

  public function having($text) {
    // @TODO: Implement
  }

  public function innerJoin(string $table, string $onClause, mixed $schema = null): AdapterAbstract {
    // @TODO: Implement
  }

  public function join(string $type, string $table, string $onClause, mixed $schema = null): AdapterAbstract {
    // @TODO: Implement
  }

  public function leftJoin(string $table, string $onClause, mixed $schema = null): AdapterAbstract {
    // @TODO: Implement
  }

  public function limit($limit, $offset = null) {
    // @TODO: Implement
  }

  public function orWhere($where, $value = null, $type = null): AdapterAbstract {
    // @TODO: Implement
  }

  public function order($column): AdapterAbstract {
    // @TODO: Implement
  }

  public function outerJoin(string $table, string $onClause, mixed $schema = null): AdapterAbstract {
    // @TODO: Implement
  }

  public function resetQuery(): AdapterAbstract {
    // @TODO: Implement
  }

  public function rightJoin(string $table, string $onClause, mixed $schema = null): AdapterAbstract {
    // @TODO: Implement
  }

  public function where($where, $value = null, $type = null): AdapterAbstract {
    // @TODO: Implement
  }

}
