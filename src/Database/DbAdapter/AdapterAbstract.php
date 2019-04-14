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


abstract class AdapterAbstract implements AdapterInterface
{
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
  private $options = array();

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
}
