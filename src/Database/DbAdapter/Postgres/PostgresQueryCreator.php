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

namespace SuitUp\Database\DbAdapter\Mysql;

use SuitUp\Database\DbAdapter\AdapterAbstract;
use SuitUp\Database\DbAdapter\QueryCreatorInterface;
use SuitUp\Exception\DatabaseGatewayException;

class PostgresQueryCreator implements QueryCreatorInterface
{
  public $sql;

  private $from = '';

  private $columns = array();

  private $join = array();

  private $where = array();

  private $group = array();

  private $order = array();

  private $having;

  private $limit;

  /**
   * Setup the FROM statement to append to the SQL instruction
   *
   * @param string|array $table `tablename as alias` OR array('tablename' => 'alias')
   * @param string $schema The schema name
   * @return QueryCreatorInterface
   * @throws DatabaseGatewayException
   */
  public function from($table, string $schema = null): QueryCreatorInterface {

    if (is_string($table)) {
      $this->from = $schema ? "$schema.$table" : $table;

    } elseif (is_array($table)) {

      // Validate array length
      if (count($table) != 1) {
        throw new DatabaseGatewayException('The array in the FROM statement must contain only one row');
      }

      // Setup from statement
      $tableName = $schema ? $schema.'.'.key($table) : key($table);
      $this->from = $tableName.' as '.current($table);

    } else {
      throw new DatabaseGatewayException('FROM statement must to be set as string or array with table name');
    }

    return $this;
  }

  /**
   * You can append only one column to the statement if you want to.
   *
   * @param string $name
   * @param string|null $alias
   * @return QueryCreatorInterface
   */
  public function column(string $name, string $alias = null): QueryCreatorInterface {
    if ($alias) {
      $this->columns[$name] = $alias;
    } else {
      $this->columns[] = $name;
    }
    return $this;
  }

  /**
   * Append a list of columns to the statement
   *
   * @param array $columns
   * @return QueryCreatorInterface
   */
  public function columns(array $columns): QueryCreatorInterface {
    foreach ($columns as $name => $alias) {
      if (is_string($name)) {
        $this->column($name, $alias);
      } else {
        $this->column($alias);
      }
    }
    return $this;
  }

  /**
   * Create whatever type of join by type given.
   *
   * @param string $type QueryCreatorInterface join type
   * @param string $table
   * @param string $onClause
   * @param string|null $schema
   * @return QueryCreatorInterface
   */
  public function join(string $type, string $table, string $onClause, string $schema = null): QueryCreatorInterface {
    $this->join[] = array(
      'type' => $type,
      'table' => ($schema ? "$schema.$table" : $table),
      'onClause' => $onClause
    );
    return $this;
  }

  /**
   * Append an INNER JOIN to the statement
   *
   * @param string $table
   * @param string $onClause
   * @param string|null $schema
   * @return QueryCreatorInterface
   */
  public function innerJoin(string $table, string $onClause, string $schema = null): QueryCreatorInterface {
    $this->join(AdapterAbstract::INNER_JOIN, $table, $onClause, $schema);
    return $this;
  }

  /**
   * Append a FULL INNER JOIN to the statement
   *
   * @param string $table
   * @param string $onClause
   * @param string|null $schema
   * @return QueryCreatorInterface
   */
  public function fullInnerJoin(string $table, string $onClause, string $schema = null): QueryCreatorInterface {
    $this->join(AdapterAbstract::FULL_INNER_JOIN, $table, $onClause, $schema);
    return $this;
  }

  /**
   * Append an OUTER JOIN to the statement
   *
   * @param string $table
   * @param string $onClause
   * @param string|null $schema
   * @return QueryCreatorInterface
   */
  public function outerJoin(string $table, string $onClause, string $schema = null): QueryCreatorInterface {
    $this->join(AdapterAbstract::OUTER_JOIN, $table, $onClause, $schema);
    return $this;
  }

  /**
   * Append a FULL OUTER JOIN to the statement
   *
   * @param string $table
   * @param string $onClause
   * @param string|null $schema
   * @return QueryCreatorInterface
   */
  public function fullOuterJoin(string $table, string $onClause, string $schema = null): QueryCreatorInterface {
    $this->join(AdapterAbstract::FULL_OUTER_JOIN, $table, $onClause, $schema);
    return $this;
  }

  /**
   * Append an RIGHT JOIN to the statement
   *
   * @param string $table
   * @param string $onClause
   * @param string|null $schema
   * @return QueryCreatorInterface
   */
  public function rightJoin(string $table, string $onClause, string $schema = null): QueryCreatorInterface {
    $this->join(AdapterAbstract::RIGHT_JOIN, $table, $onClause, $schema);
    return $this;
  }

  /**
   * Append an LEFT JOIN to the statement
   *
   * @param string $table
   * @param string $onClause
   * @param string|null $schema
   * @return QueryCreatorInterface
   */
  public function leftJoin(string $table, string $onClause, string $schema = null): QueryCreatorInterface {
    $this->join(AdapterAbstract::LEFT_JOIN, $table, $onClause, $schema);
    return $this;
  }

  /**
   * Append a WHERE command to the statement
   *
   * @param $where
   * @param null $value
   * @param null $type
   * @return QueryCreatorInterface
   */
  public function where($where, $value = null, $type = null): QueryCreatorInterface {

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
      if ($value instanceof QueryCreatorInterface) {
        $where = str_replace('?', $value, $where);
      } else {
        $where = str_replace('?', AdapterAbstract::quote($value, $type), $where);
      }
    }

    $this->where[] = array('AND_OR' => 'AND', 'statement' => $where);

    return $this;
  }

  /**
   * Append an OR WHERE statement to the command.
   *
   * @param $where
   * @param null $value
   * @param null $type
   * @return QueryCreatorInterface
   */
  public function orWhere($where, $value = null, $type = null): QueryCreatorInterface {

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
      if ($value instanceof QueryCreatorInterface) {
        $where = str_replace('?', $value, $where);
      } else {
        $where = str_replace('?', AdapterAbstract::quote($value, $type), $where);
      }
    }

    $this->where[] = array('AND_OR' => 'OR', 'statement' => $where);

    return $this;
  }

  /**
   * Setup grouping to the query.
   *
   * @param $column
   * @return self
   */
  public function group($column): QueryCreatorInterface {
    if (is_array($column)) {
      $this->group = array_merge($this->group, $column);
    } else {
      $this->group[] = (string) $column;
    }
    return $this;
  }

  /**
   * Setup the ORDER to the result.
   *
   * @param $column
   * @return QueryCreatorInterface
   */
  public function order($column): QueryCreatorInterface {
    if (is_array($column)) {
      $this->order = array_merge($this->order, $column);
    } else {
      $this->order[] = (string) $column;
    }
    return $this;
  }

  /**
   * Setting up a HAVING statement. Things are going wild I guess...
   *
   * @param $text
   * @return $this
   */
  public function having($text): QueryCreatorInterface {
    $this->having = $text;
    return $this;
  }

  /**
   * Limit the query results.
   *
   * @param $limit
   * @param null $offset
   * @return $this
   */
  public function limit($limit, $offset = null): QueryCreatorInterface {
    $this->limit = $limit;
    if ($offset) {
      $this->limit .= " OFFSET " . $offset;
    }
    return $this;
  }

  /**
   * Transform this class to an executable SQL.
   *
   * @return string
   */
  public function __toString(): string {
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
      $sql .= " {$join['type']} {$join['table']} ON {$join['onClause']}";
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
}
