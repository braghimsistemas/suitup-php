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

namespace SuitUp\Database\Gateway;


use phpDocumentor\Reflection\Types\Self_;
use SuitUp\Database\Gateway\QueryString\Join;
use SuitUp\Exception\DatabaseGatewayException;

class QueryString
{
  const INT_TYPE = 'INTEGER';

  const BIGINT_TYPE = 'BIGINT';

  const FLOAT_TYPE = 'FLOAT';

  public $sql;

  private $from = '';

  private $columns = array();

  private $join = array();

  private $where = array();

  private $group = array();

  private $order = array();

  private $having;

  private $limit;

  public function from($table, $schema = null): QueryString {

    if (is_string($table)) {
      $this->from = $schema ? "$schema.$table" : $table;

    } elseif (is_array($table)) {

      // Validate array length
      if (count($table) != 1) {
        throw new DatabaseGatewayException('The array in the FROM statement must contain only one row');
      }

      $tableName = $schema ? $schema.'.'.key($table) : key($table);
      $this->from = $tableName.' as '.current($table);

    } else {
      throw new DatabaseGatewayException('FROM statement must to be set as string or array with table name');
    }

    return $this;
  }

  public function column(string $name, string $alias = null): QueryString {
    if ($alias) {
      $this->columns[$name] = $alias;
    } else {
      $this->columns[] = $name;
    }
    return $this;
  }

  public function columns(array $columns): QueryString {
    $this->columns = array_merge($this->columns, $columns);
    return $this;
  }

  public function innerJoin(string $table, string $onClause, string $schema = null): QueryString {
    $this->join[] = new Join(Join::INNER_JOIN, $table, $onClause, $schema);
    return $this;
  }

  public function outerJoin(string $table, string $onClause, string $schema = null): QueryString {
    $this->join[] = new Join(Join::OUTER_JOIN, $table, $onClause, $schema);
    return $this;
  }

  public function rightJoin(string $table, string $onClause, string $schema = null): QueryString {
    $this->join[] = new Join(Join::RIGHT_JOIN, $table, $onClause, $schema);
    return $this;
  }

  public function leftJoin(string $table, string $onClause, string $schema = null): QueryString {
    $this->join[] = new Join(Join::LEFT_JOIN, $table, $onClause, $schema);
    return $this;
  }

  public function where($where, $value = null, $type = null): QueryString {

    // Loop under values if it is an array
    if (is_array($where)) {
      foreach ($where as $text => $val) {
        $this->where((string) $text, $val);
      }
      return $this;
    }

    // Needs Injection protection?
    if (! is_null($value)) {

      // If it is a sub query
      if ($value instanceof QueryString) {
        $where = str_replace('?', $value, $where);
      } else {
        $where = str_replace('?', $this->quote($value, $type), $where);
      }
    }

    $this->where[] = array('AND_OR' => 'AND', 'statement' => $where);

    return $this;
  }

  public function orWhere($where, $value = null, $type = null): QueryString {

    // Loop under values if it is an array
    if (is_array($where)) {
      foreach ($where as $text => $val) {
        $this->where((string) $text, $val);
      }
      return $this;
    }

    // Needs Injection protection?
    if (! is_null($value)) {

      // If it is a sub query
      if ($value instanceof QueryString) {
        $where = str_replace('?', $value, $where);
      } else {
        $where = str_replace('?', $this->quote($value, $type), $where);
      }
    }

    $this->where[] = array('AND_OR' => 'OR', 'statement' => $where);

    return $this;
  }

  public function group($column): QueryString {
    if (is_array($column)) {
      $this->group = array_merge($this->group, $column);
    } else {
      $this->group[] = (string) $column;
    }
    return $this;
  }

  public function order($column): QueryString {
    if (is_array($column)) {
      $this->order = array_merge($this->order, $column);
    } else {
      $this->order[] = (string) $column;
    }
    return $this;
  }

  public function having($text) {
    $this->having = $text;
    return $this;
  }

  public function limit($limit, $offset = null) {
    $this->limit = $limit;
    if ($offset) {
      $this->limit .= " OFFSET " . $offset;
    }
    return $this;
  }

  public function __toString() {
    $sql = "SELECT";

    // Columns
    if (!empty($this->columns)) {
      foreach ($this->columns as $name => $alias) {
        if (is_string($name)) {
          $sql .= " $name as $alias,";
        } else {
          $sql .= " $alias,"; // Alias here is actually the name
        }
      }
      $sql = rtrim($sql, ',');
    } else {
      $sql .= " *";
    }

    // FROM
    $sql .= " FROM ".$this->from;

    // JOIN statements
    foreach ($this->join as $join) {
      $sql .= " $join";
    }

    // Where statements
    if ($this->where) {

      $first = true;
      $wheres = array();
      foreach ($this->where as $item) {

        $row = !$first ? $item['AND_OR'].' ' : '';
        $row .= "(".$item['statement'].")";
        $wheres[] = $row;

        $first = false;
      }
      $sql .= ' WHERE '.implode(' ', $wheres);
    }

    // GROUP BY
    if ($this->group) {
      $sql .= ' GROUP BY ';
      $sql .= implode(', ', $this->group);
    }

    // ORDER
    if ($this->order) {
      $sql .= ' ORDER BY ';
      $sql .= implode(', ', $this->order);
    }

    // Having
    if ($this->having) {
      $sql .= ' HAVING '.$this->having;
    }

    // LIMIT
    if ($this->limit) {
      $sql .= ' LIMIT '.$this->limit;
    }

    return $sql;
  }

  /**
   * By this method
   * @param $value
   * @param null $type
   * @return string
   */
  public function quote($value, $type = null): string {

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

    // Quote to strings
    $result = "'" . addcslashes($value, "\000\n\r\\'\"\032") . "'";

    // Int and float values
    if (is_int($value)) {
      $result = $value;
    } elseif (is_float($value)) {
      $result = sprintf('%F', $value);
    }
    return $result;
  }
}
