<?php
namespace Braghim\Paginate;

use Braghim\Database;
use Braghim\SqlFileManager;
use Iterator;

/**
 * Interface PaginateI
 * @package Braghim\Paginate
 */
interface PaginateI extends Iterator {

	/**
	 * @param \Braghim\Database $db
	 * @return \Braghim\Paginate
	 */
	public function setDb(Database $db);

	/**
	 * @return \Braghim\Database\Persistence
	 */
	public function getDb();

	/**
	 * @param \Braghim\SqlFileManager $adapter
	 * @return \Braghim\SqlFileManager
	 */
	public function setAdapter(SqlFileManager $adapter);

	/**
	 * @return \Braghim\SqlFileManager
	 */
	public function getAdapter();

	/**
	 * @param $pageRange
	 * @return \Braghim\Paginate
	 */
	public function setPageRange($pageRange);

	/**
	 * @return int
	 */
	public function getPageRange();

	/**
	 * @param $currentPage
	 * @return \Braghim\Paginate
	 */
	public function setCurrentPage($currentPage);

	/**
	 * @return int
	 */
	public function getCurrentPage();

	/**
	 * @param $numberPerPage
	 * @return \Braghim\Paginate
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
