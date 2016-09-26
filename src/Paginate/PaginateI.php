<?php
namespace SuitUp\Paginate;

use SuitUp\Database\Database;
use SuitUp\Database\SqlFileManager;
use Iterator;

/**
 * Interface PaginateI
 * @package SuitUp\Paginate
 */
interface PaginateI extends Iterator {

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
