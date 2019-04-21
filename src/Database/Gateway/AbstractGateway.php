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
namespace SuitUp\Database\Gateway;

use SuitUp\Database\Gateway\QueryString;
use SuitUp\Database\DbAdapterInterface;
use SuitUp\Exception\DatabaseGatewayException;
use SuitUp\Mvc\MvcAbstractController;
use SuitUp\Database\SqlFileManager;
use SuitUp\Database\Database;

/**
 * Gateways are our way to treat  the Model classes that will
 * effectively create the  SQL queries and  run into database
 * defined  by  the  /config/database.config.php  or directly
 * with the AbstractGateway::setDefaultAdapter static method.
 *
 * @see setDefaultAdapter static method
 * @package SuitUp\Database\Gateway
 */
abstract class AbstractGateway
{

  protected $name;

  protected $primary;

  protected $onUpdate;

  protected $db;

  private static $defaultAdapter;

  /**
   * Constructor
   *
   * AbstractGateway constructor.
   * @param DbAdapterInterface|null $dbAdapter
   * @throws DatabaseGatewayException
   */
  public function __construct(DbAdapterInterface $dbAdapter = null) {

    if ($dbAdapter) {

      // Set database adapter
      $this->db = $dbAdapter;

    } else {

      // If was not set default adapter
      if (self::getDefaultAdapter() == null) {
        throw new DatabaseGatewayException('There is no database connection defined');
      }

      // Set database adapter
      $this->db = self::getDefaultAdapter();
    }

    // Checkup gateway
    $this->checkGateway();
  }

  /**
   * Add to the system a fixed adapter to the database connections.
   *
   * @param DbAdapterInterface $dbAdapter
   */
  public static function setDefaultAdapter(DbAdapterInterface $dbAdapter): void {
    self::$defaultAdapter = $dbAdapter;
  }

  /**
   * The default adapter connection.
   *
   * @return DbAdapterInterface
   */
  public static function getDefaultAdapter(): ?DbAdapterInterface {
    return self::$defaultAdapter;
  }

  /**
   * @param array $columns
   * @return QueryString
   * @throws DatabaseGatewayException
   */
  public function select($columns = array()): QueryString {

    // Start the instance
    $querySelector = new QueryString();

    // By type we will start to populate it
    switch (gettype($columns)) {
      case 'string':

        // Here we try to keep a compatibility with SuitUp 1
        // Sadly won't works always
        if (preg_match('/^(SELECT)/', $columns) !== false) {

          // Remove the word SELECT and FROM to ahead to catch the columns
          $theColumns = preg_replace("/^(SELECT)\s+/", '', $columns);
          $theColumns = preg_replace("/(FROM).+/", '', $theColumns);
          $theColumns = preg_replace("/\s+/", '', $theColumns);

          // Try to setup columns and the from table
          $querySelector->columns(explode(',', $theColumns));
          $querySelector->from(preg_replace("/.+FROM /", '', $columns));

        } else {

          // Normal behavior, the list of columns expected
          $theColumns = preg_replace("/\s+/", '', $columns);
          $querySelector->columns(explode(',', $theColumns));
        }

        break;
      case 'array':

        if ($columns) {
          // Normal behavior, the list of columns expected
          $querySelector->columns($columns);
        }

        break;
      default:
        throw new DatabaseGatewayException('Accepted array or string type');
    }

    return $querySelector;
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
  public function get() {
    $this->checkGateway();

    $id = func_get_args();

    // Create the SQL Query
    $sql = "SELECT * FROM {$this->name} WHERE ";
    foreach ((array) $this->primary as $key => $primary) {

      // More primary keys than parameters
      if (! isset($id[$key])) {
        throw new DatabaseGatewayException("The 'get' method requires every primary keys as parameters to be given in the same order");
      }

      // Query parameter
      $sql .= $primary . " = :$primary AND ";

      // Safe query
      $this->db->bind($primary, $id[$key]);
    }
    $sql = trim($sql, " AND ");

    // Result
    return $this->db->row($sql);
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
  public function save(array $data) {
    $this->checkGateway();

    // There's no PK informed? INSERT
    // There's PK informed? UPDATE
    // Not enough PK's informed? EXCEPTION
    $validPks = array();
    foreach ((array) $this->primary as $primary) {
      if (isset($data[$primary])) {
        $validPks[$primary] = $data[$primary];
        unset($data[$primary]);
      }
    }

    // Wrong parameters count.
    if ($validPks && (count($validPks) != count($this->primary))) {
      throw new DatabaseGatewayException("Method 'save' requires all primary keys to make an UPDATE and in the same order");
    }

    // Select the method and run it
    return ($validPks) ? $this->update($data, $validPks) : $this->insert($data);
  }

  /**
   * Perform an INSERT statement into database.
   *
   * @param array $data
   * @return mixed
   * @throws DatabaseGatewayException
   */
  public function insert(array $data) {

    $this->checkGateway();

    // Create the SQL Query
    $sql = "INSERT INTO {$this->name} (";

    // columns
    foreach (array_keys($data) as $column) {
      $sql .= $column . ", ";
    }
    $sql = trim($sql, ', ') . ") VALUES (";

    // Values
    foreach ($data as $column => $value) {
      if (! is_null($value)) {
        $sql .= ":" . $column . ", ";

        // Safe query
        $this->db->bind($column, $value);
      } else {
        $sql .= $column . " = NULL, ";
      }
    }
    $sql = trim($sql, ', ') . ")";

    // Runs
    $this->db->query($sql);

    // Return the last insert ID.
    return $this->db->lastInsertId();
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
  public function update(array $data, array $where, $noWhereForSure = false) {
    $this->checkGateway();

    $sql = "UPDATE {$this->name} SET ";

    // Columns
    foreach ($data as $column => $value) {
      if (! is_null($value)) {
        $sql .= $column . " = :" . $column . ", ";

        // Safe query
        $this->db->bind($column, $value);
      } else {
        $sql .= $column . " = NULL, ";
      }
    }

    // There is some command to run every update?
    if ($this->onUpdate && is_array($this->onUpdate)) {
      foreach ($this->onUpdate as $column => $value) {
        if (! isset($data[$column])) {
          $sql .= $column . " = " . str_replace(';', '', $value) . ", ";
        }
      }
    }

    $sql = trim($sql, ', ');

    // UPDATE without WHERE? =S
    if (! $where && ! $noWhereForSure) {
      throw new DatabaseGatewayException("UPDATE without WHERE clause. We do not encourage it");

    } else if ($where) {

      // Where
      $sql .= " WHERE ";
      foreach ($where as $column => $value) {
        $sql .= $column . " = :w_" . $column . " AND ";

        // Safe query
        $this->db->bind("w_" . $column, $value);
      }
      $sql = trim($sql, " AND ");
    }

    // Runs query
    return (bool) $this->db->query($sql);
  }

  /**
   * DELETE rows from database.
   *
   * @param array $where
   * @return bool
   * @throws DatabaseGatewayException
   */
  public function delete(array $where) {
    $this->checkGateway();

    // Query
    $sql = "DELETE FROM {$this->name} WHERE ";
    foreach ($where as $column => $value) {

      // Parameter
      $sql .= $column . " = :" . $column . " AND ";

      // Safe query
      $this->db->bind($column, $value);
    }
    $sql = preg_replace("/\sAND\s$/", '', $sql);

    // Result
    return (bool) $this->db->query($sql);
  }

  /**
   * Checkup under gateway attributes.
   *
   * @return bool
   * @throws DatabaseGatewayException
   */
  private function checkGateway() {
    if (! $this->name || ! $this->primary) {
      throw new DatabaseGatewayException("Every Gateway file must to inform name and primary fields (".get_class($this).")");
		}
		return true;
	}
}
