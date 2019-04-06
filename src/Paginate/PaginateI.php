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
namespace Suitup\Paginate;

use Suitup\Database\Database;
use Suitup\Database\SqlFileManager;
use Iterator;

/**
 * Interface PaginateI
 * @package SuitUp\Paginate
 */
interface PaginateI extends Iterator
{

  /**
   * @param \SuitUp\Database\Database $db
   * @return \SuitUp\Paginate\Paginate
   */
  public function setDb(Database $db);

  /**
   * @return \SuitUp\Database\Persistence
   */
  public function getDb();

  /**
   * @param \SuitUp\Database\SqlFileManager $adapter
   * @return \SuitUp\Database\SqlFileManager
   */
  public function setAdapter(SqlFileManager $adapter);

  /**
   * @return \SuitUp\Database\SqlFileManager
   */
  public function getAdapter();

  /**
   * @param $pageRange
   * @return \SuitUp\Paginate\Paginate
   */
  public function setPageRange($pageRange);

  /**
   * @return int
   */
  public function getPageRange();

  /**
   * @param $currentPage
   * @return \SuitUp\Paginate\Paginate
   */
  public function setCurrentPage($currentPage);

  /**
   * @return int
   */
  public function getCurrentPage();

  /**
   * @param $numberPerPage
   * @return \SuitUp\Paginate\Paginate
   */
  public function setNumberPerPage($numberPerPage);

  /**
   * @return int
   */
  public function getNumberPerPage();

  /**
   * @return int
   */
  public function getTotalPages();

	/**
	 * @return array
	 */
	public function getResult();
}
