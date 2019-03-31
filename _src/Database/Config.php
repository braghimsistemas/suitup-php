<?php
/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2016 Braghim Sistemas
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
namespace SuitUp\Database;

/**
 * Class Config
 * @package SuitUp\Database
 */
class Config
{

  /**
   * @var string
   */
  private $host = 'localhost';

  /**
   * @var string
   */
  private $database = 'mysql';

  /**
   * @var string
   */
  private $username = 'root';

  /**
   * @var string
   */
  private $password = '';

  /**
   * Config constructor.
   * @param array $configs
   */
  public function __construct(array $configs = array()) {

    // Host
    if (isset($configs['host'])) {
      $this->setHost($configs['host']);
    }

    // Database
    if (isset($configs['database'])) {
      $this->setDatabase($configs['database']);
    }

    // Username
    if (isset($configs['username'])) {
      $this->setUsername($configs['username']);
    }

    // Password
    if (isset($configs['password'])) {
      $this->setPassword($configs['password']);
    }
  }

  /**
   * @return string
   */
  public function getHost() {
    return $this->host;
  }

  /**
   * @param string $host
   * @return Config
   */
  public function setHost($host) {
    $this->host = $host;
    return $this;
  }

  /**
   * @return string
   */
  public function getDatabase() {
    return $this->database;
  }

  /**
   * @param string $database
   */
  public function setDatabase($database) {
    $this->database = $database;
  }

  /**
   * @return string
   */
  public function getUsername() {
    return $this->username;
  }

  /**
   * @param string $username
   */
  public function setUsername($username) {
    $this->username = $username;
  }

  /**
   * @return string
   */
  public function getPassword() {
    return $this->password;
  }

  /**
   * @param string $password
   */
  public function setPassword($password) {
    $this->password = $password;
  }
}
