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

/**
 * Class AdapterAbstract
 *
 * @package SuitUp\Database\DbAdapter
 */
abstract class AdapterAbstract implements AdapterInterface
{
  const INT_TYPE = 'INTEGER';

  const BIGINT_TYPE = 'BIGINT';

  const FLOAT_TYPE = 'FLOAT';

  /**
   * @var string
   */
  private $dsn;

  /**
   * @var string
   */
  private $username;

  /**
   * @var string
   */
  private $password;

  /**
   * @var string
   */
  private $options = array(
    \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
  );

  /**
   * @param array $parameters
   * @throws DbAdapterException
   */
  public function validateParams(array $parameters) {
    foreach ($parameters as $name => $value) {

      // Suggest to password
      if (in_array($name, array('pwd', 'senha', 'passwd'))) {
        throw new DbAdapterException("Parameter '$name' is not valid. You would say: password?");
      }

      // Suggest to username
      if (in_array($name, array('user', 'usuario'))) {
        throw new DbAdapterException("Parameter '$name' is not valid. You would say: username?");
      }

      // Suggest to dbname
      if (in_array($name, array('database', 'db'))) {
        throw new DbAdapterException("Parameter '$name' is not valid. You would say: dbname?");
      }

      // Check all parameters
      if (!in_array($name, array('host', 'port', 'dbname', 'username', 'password', 'options'))) {
        throw new DbAdapterException("$name is not a valid parameter to create connection");
      }
    }
  }

  /**
   * @return mixed
   */
  public function getDsn(): string {
    return $this->dsn;
  }

  /**
   * @param mixed $dsn
   * @return AdapterAbstract
   */
  public function setDsn($dsn): AdapterAbstract {
    $this->dsn = $dsn;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getUsername(): string {
    return $this->username;
  }

  /**
   * @param mixed $username
   * @return AdapterAbstract
   */
  public function setUsername($username): AdapterAbstract {
    $this->username = $username;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getPassword(): string {
    return $this->password;
  }

  /**
   * @param mixed $password
   * @return AdapterAbstract
   */
  public function setPassword($password): AdapterAbstract {
    $this->password = $password;
    return $this;
  }

  /**
   * @return array
   */
  public function getOptions(): array {
    return $this->options;
  }

  /**
   * @param array $options
   * @return AdapterAbstract
   */
  public function setOptions(array $options): AdapterAbstract {
    $this->options = $options;
    return $this;
  }

  /**
   * @param array $options
   * @return AdapterAbstract
   */
  public function appendOptions(array $options): AdapterAbstract {
    $this->options = array_merge($this->options, $options);
    return $this;
  }
}
