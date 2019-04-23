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
declare(strict_types=1);

namespace SuitUp\Database\Business;

use Throwable;
use SuitUp\Database\Gateway\AbstractGateway;
use SuitUp\Exception\DatabaseBusinessException;
use SuitUp\Exception\DatabaseGatewayException;

/**
 * Class AbstractBusiness
 * @package SuitUp\Database\Business
 */
abstract class AbstractBusiness
{
  /**
   * @var AbstractGateway
   */
  protected $gateway;

  /**
   * AbstractBusiness constructor.
   */
  public function __construct()
  {
    // Class name
    $className = explode('\\', get_class($this));
    $className = array_pop($className);

    // Gateway name
    $gateway = str_replace($className, 'Gateway', get_class($this)) . '\\' . str_replace('Business', '', $className);

    try {

      // Try to instance it
      $this->gateway = new $gateway();

    } catch (Throwable $exception) {
      throw new DatabaseBusinessException("Gateway not found: $gateway", 0, $exception);
    }
  }

  /**
   * When called, this method will get the first row
   * with the primaries keys given as implicit param.
   *
   * There's an attribute named $primary in your Gateway,
   * right? This attribute must to be an array what means
   * that one table can have more than one primary key.
   * This method will expect as much primary keys as is
   * provided in that attribute.
   *
   * @return mixed
   * @throws DatabaseGatewayException
   */
  public function get()
  {
    return call_user_func_array(array($this->gateway, 'get'), func_get_args());
  }

  /**
   * This method will checkup for the primary keys in the data set,
   * if found make an UPDATE else make an INSERT.
   *
   * If the number of primary keys is not zero, but not enough as
   * the number in the $primary attribute throws an exception.
   *
   * @param array $data
   * @return bool
   * @throws DatabaseGatewayException
   */
  public function save(array $data)
  {
    return $this->gateway->save($data);
  }

  /**
   * Perform an INSERT statement into database.
   *
   * @param array $data
   * @return mixed
   * @throws DatabaseGatewayException
   */
  public function insert(array $data)
  {
    return $this->gateway->insert($data);
  }

  /**
   * Perform an UPDATE statement into database.
   *
   * @param array $data
   * @param array $where
   * @param bool $noWhereForSure If you really want to perform an UPDATE without WHERE =S
   * @return bool
   * @throws DatabaseGatewayException
   */
  public function update(array $data, array $where, $noWhereForSure = false)
  {
    return $this->gateway->update($data, $where, $noWhereForSure);
  }

  /**
   * DELETE rows from database.
   *
   * @param array $where
   * @return bool
   * @throws DatabaseGatewayException
   */
  public function delete(array $where)
  {
    return $this->gateway->delete($where);
  }
}
