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
use Countable;
use SuitUp\Database\DbAdapter\AdapterAbstract;
use SuitUp\Database\DbAdapterInterface;
use SuitUp\Exception\PaginateException;

/**
 * Class Paginate
 *
 * @package SuitUp\Paginate
 */
class Paginate implements Countable, PaginateI
{
  /**
   * The name of parameter from URL
   */
  private static $paramName = 'page';

  /**
   * @var DbAdapterInterface
   */
  private $db;

  /**
   * The SQL Query object
   *
   * @var AdapterAbstract
   */
  private $adapter;

  /**
   * A list of parameters to append to the query
   *
   * @var array
   */
  private $params = array();

  /**
   * The result set.
   *
   * @var array
   */
  private $result;

  /**
   * Number total of pages
   *
   * @var integer
   */
  private $totalPages = 1;

  /**
   * Total pages shown to the user to choose
   *
   * @var integer
   */
  private $pageRange = 5;

  /**
   * The current page number
   *
   * @var integer
   */
  private $currentPage = 1;

  /**
   * Maximum registers shown on each page
   *
   * @var integer
   */
  private $numberPerPage = 50;

  /**
   * This callback function is executed for each row returned by the SQL Query
   *
   * @var Closure
   */
  private $itemCallback;

  /**
   * Change the name of parameter in the URL that identifies the current page.
   * It is used in the paginate.phtml file.
   *
   * @param string $name
   * @return void
   */
  public static function setParamName(string $name): void {
    self::$paramName = $name;
  }

  /**
   * Return the parameter in the URL that identifies the current page.
   * It is used in the paginate.phtml file.
   *
   * @return string
   */
  public static function getParamName(): string {
    return self::$paramName;
  }

  /**
   * Setup pagination by database connection and the object to construct the Sql query.
   *
   * @param DbAdapterInterface $db
   * @param AdapterAbstract $adapter
   * @param array $params A list of parameters to the query
   * @param Closure $closureFunc
   */
  public function __construct(DbAdapterInterface $db, AdapterAbstract $adapter, array $params = array(), Closure $closureFunc = null) {

    $this->setDb($db);

    $this->setAdapter($adapter);

    if ($params) {
      $this->setParams($params);
    }

    if ($closureFunc) {
      $this->setClosureFunc($closureFunc);
    }
  }

  /**
   * The Db Adapter responsible to the connection and to management of
   * SQL Queries
   *
   * @param DbAdapterInterface $db
   * @return Paginate
   */
  public function setDb(DbAdapterInterface $db): Paginate {
    $this->db = $db;
    return $this;
  }

  /**
   * The Db Adapter responsible to the connection and to management of
   * SQL Queries
   *
   * @return DbAdapterInterface
   */
  public function getDb(): DbAdapterInterface {
    return $this->db;
  }

  /**
   * This object is responsible for assembling SQL queries.
   *
   * @param AdapterAbstract $adapter
   * @return Paginate
   */
  public function setAdapter(AdapterAbstract $adapter): Paginate {
    $this->adapter = $adapter;
    return $this;
  }

  /**
   * Return the object is responsible for assembling SQL queries.
   *
   * @return AdapterAbstract
   */
  public function getAdapter(): AdapterAbstract {
    return $this->adapter;
  }

  /**
   * List of parameters to the SQL Query
   *
   * @param array $params
   * @return Paginate
   */
  public function setParams(array $params): Paginate {
    $this->params = $params;
    return $this;
  }

  /**
   * List of parameters to the SQL Query
   *
   * @return array
   */
  public function getParams(): array {
    return $this->params;
  }

  /**
   * Set page to range in view to user
   *
   * @param string $pageRange
   * @return Paginate
   * @throws PaginateException
   */
  public function setPageRange($pageRange): Paginate {
    if (is_integer($pageRange)) {
      if ($pageRange <= 2) {
        throw new PaginateException("The minimum range of pages must to be greater or equal to 3");
      }
    } else {
      $pageRange = 'total';
    }

    $this->pageRange = $pageRange;

    return $this;
  }

  /**
   * Return the number of pages in the range defined
   *
   * @return int
   */
  public function getPageRange(): int {
    return $this->pageRange;
  }

  /**
   * Set current page. The offset of slice.
   *
   * @param mixed $currentPage
   * @return Paginate
   */
  public function setCurrentPage($currentPage): Paginate {
    if ((int) $currentPage >= 1) {
      $this->currentPage = (int) $currentPage;
    }
    return $this;
  }

  /**
   * Return the number of current page (offset of slice).
   *
   * @return int
   */
  public function getCurrentPage(): int {
    return (int) $this->currentPage;
  }

  /**
   * Number of rows per page
   *
   * @param int $numberPerPage
   * @return Paginate
   */
  public function setNumberPerPage(int $numberPerPage): Paginate {
    if ((int) $numberPerPage >= 1) {
      $this->numberPerPage = (int) $numberPerPage;
    }
    return $this;
  }

  /**
   * Return number of rows per page
   *
   * @return int
   */
  public function getNumberPerPage(): int {
    return (int) $this->numberPerPage;
  }

  /**
   * Append to the Paginate object a function that will be
   * executed for each row in the dataset.
   *
   * <b>It was made like that to avoid you to make loops
   * under the result set as array, what is a great lost of
   * performance.</b>
   *
   * @param Closure $func
   * @return $this
   */
  public function setClosureFunc(Closure $func): Paginate {
    $this->itemCallback = $func;
    return $this;
  }

  /**
   * Return the function that must to be called for each row
   * in the dataset.
   *
   * @return Closure
   */
  public function getClosureFunc(): Closure {
    return $this->itemCallback;
  }

  /**
   * Return number total of pieces that data was sliced
   *
   * @return int
   */
  public function getTotalPages(): int {
    return (int) $this->totalPages;
  }

  /**
   * Return data sliced
   *
   * @return array
   */
  public function getResult(): array {
		if ($this->result === null) {
			$this->rewind();
		}
		return $this->result;
	}

  /**
   * Countable SPL. This class can count the number of results the object
   * will retrieve as an array.
   *
   * @return int
   */
  public function count(): int {
    if ($this->result === null) {
      $this->rewind();
    }

    // Get the query as string
    $query = $this->getAdapter()->__toString();

    /**
     * Count the total rows possible with this SQL Query
     *
     * @todo Check with other types of database (postgres, db2, etc...) if the base query to do it must to be change.
     */
    return (int) $this->getDb()->single("/* SuitUp Paginate */\r\nSELECT COUNT(1) FROM ($query) as tmp", $this->getParams());
  }

  /**
   * Iterator. This method set in each loop the actual result
   *
   * @return Paginate
   */
  public function rewind(): Paginate {
    $this->_setResult();
    return $this;
  }

  /**
   * Iterator SPL. Gives the current data of object as an array
   *
   * @return mixed
   */
  public function current() {
    if ($this->result === null) {
      $this->rewind();
    }

    $item = current($this->result);

    // If there's an encapsulated function call it
    if ($this->itemCallback) {
      $callBack = $this->itemCallback;
      $callBack($item);
    }
    return $item;
  }

  /**
   * Iterator SPL. Gives the key data of object as an array
   *
   * @return mixed
   */
  public function key() {
    return key($this->result);
  }

  /**
   * Iterator SPL. Gives the next data of object as an array
   *
   * @return Paginate
   */
  public function next(): Paginate {
    next($this->result);
    return $this;
  }

  /**
   * Iterator SPL. Gives if is valid loop of object as an array
   *
   * @return bool
   */
  public function valid(): bool {
    return $this->current() !== false;
  }

  /**
   * Determine the total of pages the data will sliced
   *
   * @return Paginate
   */
  private function _setTotalPages(): Paginate {

    // Get the query string
    $query = $this->getAdapter()->__toString();
    $count = $this->getDb()->single("/* SuitUp Paginate */\r\nSELECT COUNT(1) FROM ($query) as tmp", $this->getParams());

    // Calculate
    $this->totalPages = (int) ceil($count / $this->getNumberPerPage());

    return $this;
  }

  /**
   * Fetch SQL query to retrieve data sliced. Add to the SQL the limit clause
   * based on number per page and the relation between current page with number
   * per page.
   *
   * @return Paginate
   */
  private function _setResult(): Paginate {
    $this->_setTotalPages();

    if ($this->getCurrentPage() > $this->getTotalPages()) {
      $this->setCurrentPage($this->getTotalPages());
    }

    $offSet = (($this->getCurrentPage() - 1) * $this->getNumberPerPage());

    if ($offSet < 0) {
      $offSet = 0;
    }

    // Clone the object to avoid modification on it
    $adapter = clone $this->getAdapter();

    // Effectuate the query
    $query = "/* SuitUp Paginate */\r\n".$adapter->limit($this->getNumberPerPage(), $offSet)->__toString();
    $this->result = $this->getDb()->query($query, $this->getParams());

    // If there's callback function
    if ($this->itemCallback) {
      $callBack = $this->itemCallback;
      foreach ($this->result as $key => $item) {

        // Replace the item with what was returned by the
        // user function
        $this->result[$key] = $callBack($item);
      }
      reset($this->result);
    }
    return $this;
  }
}
