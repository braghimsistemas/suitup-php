<?php
namespace Braghim\Paginate;

use Braghim\Database\Database;
use Braghim\Database\SqlFileManager;
use Iterator;

/**
 * Interface PaginateI
 * @package Braghim\Paginate
 */
interface PaginateI extends Iterator {

	/**
	 * @param \Braghim\Database\Database $db
	 * @return \Braghim\Paginate\Paginate
	 */
	public function setDb(Database $db);

	/**
	 * @return \Braghim\Database\Persistence
	 */
	public function getDb();

	/**
	 * @param \Braghim\Database\SqlFileManager $adapter
	 * @return \Braghim\Database\SqlFileManager
	 */
	public function setAdapter(SqlFileManager $adapter);

	/**
	 * @return \Braghim\Database\SqlFileManager
	 */
	public function getAdapter();

	/**
	 * @param $pageRange
	 * @return \Braghim\Paginate\Paginate
	 */
	public function setPageRange($pageRange);

	/**
	 * @return int
	 */
	public function getPageRange();

	/**
	 * @param $currentPage
	 * @return \Braghim\Paginate\Paginate
	 */
	public function setCurrentPage($currentPage);

	/**
	 * @return int
	 */
	public function getCurrentPage();

	/**
	 * @param $numberPerPage
	 * @return \Braghim\Paginate\Paginate
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
