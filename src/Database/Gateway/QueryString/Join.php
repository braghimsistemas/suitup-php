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

namespace SuitUp\Database\Gateway\QueryString;

/**
 * Class Join
 *
 * @package SuitUp\Database\Gateway\QueryString
 */
class Join
{
  const INNER_JOIN = 'INNER JOIN';

  const FULL_INNER_JOIN = 'FULL INNER JOIN';

  const OUTER_JOIN = 'OUTER JOIN';

  const FULL_OUTER_JOIN = 'FULL OUTER JOIN';

  const RIGHT_JOIN = 'RIGHT JOIN';

  const LEFT_JOIN = 'LEFT JOIN';

  private $type;

  private $table;

  private $onClause;

  /**
   * Join constructor.
   *
   * @param string $type
   * @param string $table
   * @param string $onClause
   * @param string|null $schema
   */
  public function __construct(string $type, string $table, string $onClause, string $schema = null)
  {
    $this->type = $type;
    $this->table = $schema ? "$schema.$table" : $table;
    $this->onClause = $onClause;
  }

  /**
   * Result the class to string
   *
   * @return string
   */
  public function __toString()
  {
    return "{$this->type} {$this->table} ON {$this->onClause}";
  }
}
