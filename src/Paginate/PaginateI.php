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

namespace SuitUp\Paginate;

use Closure;
use Iterator;
use SuitUp\Database\DbAdapter\AdapterAbstract;
use SuitUp\Database\DbAdapter\QueryCreatorInterface;
use SuitUp\Database\DbAdapterInterface;

/**
 * Interface PaginateI
 * @package SuitUp\Paginate
 */
interface PaginateI extends Iterator
{

  /**
   * @param DbAdapterInterface $db
   * @return Paginate
   */
  public function setDb(DbAdapterInterface $db): Paginate;

  /**
   * @return DbAdapterInterface
   */
  public function getDb(): DbAdapterInterface;

  /**
   * @param QueryCreatorInterface $adapter
   * @return Paginate
   */
  public function setAdapter(QueryCreatorInterface $adapter): Paginate;

  /**
   * @return QueryCreatorInterface
   */
  public function getAdapter(): QueryCreatorInterface;

  /**
   * @param int $pageRange
   * @return Paginate
   */
  public function setPageRange(int $pageRange): Paginate;

  /**
   * @return int
   */
  public function getPageRange(): int;

  /**
   * @param mixed $currentPage
   * @return Paginate
   */
  public function setCurrentPage($currentPage): Paginate;

  /**
   * @return int
   */
  public function getCurrentPage(): int;

  /**
   * @param int $numberPerPage
   * @return Paginate
   */
  public function setNumberPerPage(int $numberPerPage): Paginate;

  /**
   * @return int
   */
  public function getNumberPerPage(): int;

  /**
   * @param Closure $func
   * @return Paginate
   */
  public function setClosureFunc(Closure $func): Paginate;

  /**
   * @return Closure
   */
  public function getClosureFunc(): Closure;

  /**
   * @return int
   */
  public function getTotalPages(): int;

  /**
   * @return array
   */
	public function getResult(): array;
}
