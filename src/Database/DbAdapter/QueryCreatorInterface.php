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

interface QueryCreatorInterface
{
  /**
   * Provide FROM table
   * @param $table
   * @param string|null $schema
   * @return QueryCreatorInterface
   */
  public function from($table, string $schema = null): self;

  /**
   * Add a column to the return
   *
   * @param string $name
   * @param string|null $alias
   * @return QueryCreatorInterface
   */
  public function column(string $name, string $alias = null): self;

  /**
   * Append a list of columns to the return
   *
   * @param array $columns
   * @return QueryCreatorInterface
   */
  public function columns(array $columns): self;

  /**
   * A generic inclusion of join statement.
   *
   * @param string $type
   * @param string $table
   * @param string $onClause
   * @param string|null $schema
   * @return QueryCreatorInterface
   */
  public function join(string $type, string $table, string $onClause, string $schema = null): self;

  /**
   * INNER JOIN statement
   *
   * @param string $table
   * @param string $onClause
   * @param string|null $schema
   * @return QueryCreatorInterface
   */
  public function innerJoin(string $table, string $onClause, string $schema = null): self;

  /**
   * FULL INNER JOIN statement
   *
   * @param string $table
   * @param string $onClause
   * @param string|null $schema
   * @return QueryCreatorInterface
   */
  public function fullInnerJoin(string $table, string $onClause, string $schema = null): self;

  /**
   * OUTER JOIN statement
   *
   * @param string $table
   * @param string $onClause
   * @param string|null $schema
   * @return QueryCreatorInterface
   */
  public function outerJoin(string $table, string $onClause, string $schema = null): self;

  /**
   * FULL OUTER JOIN statement
   *
   * @param string $table
   * @param string $onClause
   * @param string|null $schema
   * @return QueryCreatorInterface
   */
  public function fullOuterJoin(string $table, string $onClause, string $schema = null): self;

  /**
   * RIGHT JOIN statement
   *
   * @param string $table
   * @param string $onClause
   * @param string|null $schema
   * @return QueryCreatorInterface
   */
  public function rightJoin(string $table, string $onClause, string $schema = null): self;

  /**
   * LEFT JOIN statement
   *
   * @param string $table
   * @param string $onClause
   * @param string|null $schema
   * @return QueryCreatorInterface
   */
  public function leftJoin(string $table, string $onClause, string $schema = null): self;

  /**
   * Add one or more item to the list of WHERE clauses
   *
   * @param $where
   * @param null $value
   * @param null $type
   * @return QueryCreatorInterface
   */
  public function where($where, $value = null, $type = null): self;

  /**
   * Add one or more item to the list of OR WHERE clauses
   *
   * @param $where
   * @param null $value
   * @param null $type
   * @return QueryCreatorInterface
   */
  public function orWhere($where, $value = null, $type = null): self;

  /**
   * Add one or a list of GROUP BY clauses
   *
   * @param $column
   * @return QueryCreatorInterface
   */
  public function group($column): self;

  /**
   * Add one or a list of ODER BY clauses
   *
   * @param $column
   * @return QueryCreatorInterface
   */
  public function order($column): self;

  /**
   * Provide a HAVING statement
   *
   * @param $text
   * @return mixed
   */
  public function having($text): self;

  /**
   * Provide LIMIT / OFFSET statements
   *
   * @param $limit
   * @param null $offset
   * @return mixed
   */
  public function limit($limit, $offset = null): self;

  /**
   * When called must to get values set to return a runnable SQL query string.
   *
   * @return mixed
   */
  public function __toString(): string;
}
