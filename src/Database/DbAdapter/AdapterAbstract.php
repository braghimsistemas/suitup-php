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

use PDO;
use SuitUp\Database\DbAdapter\AdapterInterface;
use SuitUp\Exception\DbAdapterException;

/**
 * Class AdapterAbstract
 *
 * @package SuitUp\Database\DbAdapter
 */
abstract class AdapterAbstract implements AdapterInterface
{
  // JOIN Types -----------

  const INNER_JOIN = 'INNER JOIN';

  const FULL_INNER_JOIN = 'FULL INNER JOIN';

  const OUTER_JOIN = 'OUTER JOIN';

  const FULL_OUTER_JOIN = 'FULL OUTER JOIN';

  const RIGHT_JOIN = 'RIGHT JOIN';

  const LEFT_JOIN = 'LEFT JOIN';

  // Data Types -----------

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
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
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

  /**
   * By this method we try to block injection to the SQL
   *
   * @param $value
   * @param null $type
   * @return string
   */
  public static function quote($value, $type = null) {

    $numericDataTypes = array(self::INT_TYPE, self::BIGINT_TYPE, self::FLOAT_TYPE);

    if ($type !== null && in_array($type = strtoupper($type), $numericDataTypes)) {

      $quotedValue = '0';
      switch ($type) {
        case self::INT_TYPE: // 32-bit integer
          $quotedValue = (string) intval($value);
          break;
        case self::BIGINT_TYPE: // 64-bit integer
          // ANSI SQL-style hex literals (e.g. x'[\dA-F]+')
          // are not supported here, because these are string
          // literals, not numeric literals.
          if (preg_match('/^(
                          [+-]?                  # optional sign
                          (?:
                            0[Xx][\da-fA-F]+     # ODBC-style hexadecimal
                            |\d+                 # decimal or octal, or MySQL ZEROFILL decimal
                            (?:[eE][+-]?\d+)?    # optional exponent on decimals or octals
                          )
                        )/x', (string) $value, $matches)) {
            $quotedValue = $matches[1];
          }
          break;
        case self::FLOAT_TYPE: // float or decimal
          $quotedValue = sprintf('%F', $value);
      }
      return $quotedValue;
    }

    // Int and float values
    if (is_int($value)) {
      $result = $value;
    } elseif (is_float($value)) {
      $result = sprintf('%F', $value);
    } else {

      // Quote to strings
      $result = "'" . addcslashes($value, "\000\n\r\\'\"\032") . "'";
    }
    return $result;
  }

  abstract public function getQueryCreator(): QueryCreatorInterface;
}
