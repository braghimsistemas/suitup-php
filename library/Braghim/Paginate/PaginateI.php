<?php
namespace Braghim\Paginate;

use Braghim\Database;
use Braghim\SqlFileManager;
use Iterator;

interface PaginateI extends Iterator {

	public function setDb(Database $db);
	
	public function getDb();
	
	public function setAdapter(SqlFileManager $adapter);

	public function getAdapter();

	public function setPageRange($pageRange);

	public function getPageRange();

	public function setCurrentPage($currentPage);

	public function getCurrentPage();

	public function setNumberPerPage($numberPerPage);

	public function getNumberPerPage();

	public function getTotalPages();

	public function getResult();
}
