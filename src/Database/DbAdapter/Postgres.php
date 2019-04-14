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


use SuitUp\Exception\DbAdapterException;

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
    $params = new \stdClass();
    $params->host = $parameters['host'] ?? 'localhost';
    $params->port = $parameters['port'] ?? '5432';
    $params->dbname = $parameters['dbname'] ?? null;

    // Setup dsn string
    $this->setDsn("pgsql:host={$params->host};port={$params->port};dbname={$params->dbname}");

    // User and options config
    $this->setUsername($parameters['username'] ?? 'root');
    $this->setPassword($parameters['password'] ?? '');
    $this->setOptions($parameters['options'] ?? array());
  }
}
