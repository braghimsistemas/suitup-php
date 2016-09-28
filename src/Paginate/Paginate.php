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
namespace SuitUp\Paginate;

include_once 'PaginateI.php';

use Countable;
use SuitUp\Database\Database;
use SuitUp\Database\SqlFileManager;
use SuitUp\Routes\Routes;

/**
 * Class Paginate
 * @package SuitUp\Paginate
 */
class Paginate implements Countable, PaginateI
{
	/**
	 * @var \SuitUp\Database\Database
	 */
	private $db;

	/**
	 * Query que foi construida
	 *
	 * @access private
	 * @var SqlFileManager
	 */
	private $adapter;

	/**
	 * Linhas retornadas pela consulta.
	 *
	 * @access private
	 * @var array
	 */
	private $result;

	/**
	 * Quantidade total de pagina que a consulta pode retornar
	 *
	 * @access private
	 * @var integer
	 */
	private $totalPages = 1;

	/**
	 * Numero de paginas que sera mostrada na paginacao
	 *
	 * @access private
	 * @var integer
	 */
	private $pageRange = 5;

	/**
	 * Numero da pagina atual
	 *
	 * @access private
	 * @var integer
	 */
	private $currentPage = 1;

	/**
	 * Quantidade maxima de registros mostrados por pagina
	 *
	 * @access private
	 * @var integer
	 */
	private $numberPerPage = 50;

	/**
	 * Funcao passada como parametro que executa uma determinada acao a cada
	 * item do resultado.
	 *
	 * @var \Closure
	 */
	private $itemCallback;

	/**
	 * Basta indicar o objeto de banco de dados e a query em forma de objeto SqlFileManager.
	 *
	 * @param \SuitUp\Database\Database $db
	 * @param \SuitUp\Database\SqlFileManager $adapter
	 * @param \Closure $clousureFunc
	 */
	public function __construct(Database $db, SqlFileManager $adapter, $clousureFunc = null)
	{
		$this->setDb($db);
		$this->setAdapter($adapter);
		if ($clousureFunc) {
			$this->setClosureFunc($clousureFunc);
		}

		// Seta automaticamente o numero da pagina atual
		$params = array_merge((array)filter_input_array(INPUT_GET), Routes::getInstance()->getParams());
		if (isset($params['pagina'])) {
			$this->setCurrentPage((int)$params['pagina']);
		}
	}

	/**
	 * Instancia do controlador de queries.
	 *
	 * @param \SuitUp\Database\Database $db
	 * @return \SuitUp\Paginate\Paginate
	 */
	public function setDb(Database $db)
	{
		$this->db = $db;
		return $this;
	}

	/**
	 * Retorna a classe que executa as queries.
	 *
	 * @return \SuitUp\Database\Persistence
	 */
	public function getDb()
	{
		return $this->db;
	}

	/**
	 * Set adapter data
	 *
	 * @param \SuitUp\Database\SqlFileManager $adapter
	 * @return \SuitUp\Paginate\Paginate
	 */
	public function setAdapter(SqlFileManager $adapter)
	{
		$this->adapter = $adapter;

		return $this;
	}

	/**
	 * Return SQL object stored
	 *
	 * @return SqlFileManager
	 */
	public function getAdapter()
	{
		return $this->adapter;
	}

	/**
	 * Set page to range in view to user
	 *
	 * @param string $pageRange
	 * @return \SuitUp\Paginate\Paginate
	 * @throws \Exception
	 */
	public function setPageRange($pageRange)
	{
		if (is_integer($pageRange)) {
			if ($pageRange <= 2) {
				throw new \Exception("O mínimo de paginas para o range é 3");
			}
		} else {
			$pageRange = 'total';
		}

		$this->pageRange = $pageRange;

		return $this;
	}

	/**
	 * Return number of pages to range
	 *
	 * @return int
	 */
	public function getPageRange()
	{
		return $this->pageRange;
	}

	/**
	 * Set current page. The offset of slice.
	 *
	 * @param int $currentPage
	 * @return \SuitUp\Paginate\Paginate
	 */
	public function setCurrentPage($currentPage)
	{
		if ((int)$currentPage >= 1) {
			$this->currentPage = (int)$currentPage;
		}

		return $this;
	}

	/**
	 * Return the number of current page (offset of slice).
	 *
	 * @return int
	 */
	public function getCurrentPage()
	{
		return (int)$this->currentPage;
	}

	/**
	 * Number of rows per page
	 *
	 * @param int $numberPerPage
	 * @return \SuitUp\Paginate\Paginate
	 */
	public function setNumberPerPage($numberPerPage)
	{
		if ((int)$numberPerPage >= 1) {
			$this->numberPerPage = (int)$numberPerPage;
		}

		return $this;
	}

	/**
	 * Return number of rows per page
	 *
	 * @return int
	 */
	public function getNumberPerPage()
	{
		return (int)$this->numberPerPage;
	}

	/**
	 * Adiciona a paginacao uma funcao que sera executada
	 * em cada item retornado na query.
	 * Isto faz com que a paginacao seja rapida como de costume,
	 * mas evita que seja necessario fazer loops no controlador
	 * para buscar itens dependentes de algum item no banco.
	 * Como por exemplo os logs operacionais de um usuário.
	 *
	 * @param \Closure $func
	 * @return $this
	 */
	public function setClosureFunc($func)
	{
		$this->itemCallback = $func;
		return $this;
	}

	/**
	 * Retorna a funcao de callback para os itens da paginacao.
	 * @return \Closure
	 */
	public function getClosureFunc()
	{
		return $this->itemCallback;
	}

	/**
	 * Return number total of pieces that data was sliced
	 *
	 * @return int
	 */
	public function getTotalPages()
	{
		return (int)$this->totalPages;
	}

	/**
	 * Countable SPL. This class can count the number of results the object
	 * will retrieve as an array.
	 *
	 * @return int
	 */
	public function count()
	{
		if ($this->result === null) {
			$this->rewind();
		}

		/**
		 * Contagem total de registros, direto pelo banco de dados.
		 */
		return (int)$this->db->single("SELECT COUNT(1) FROM ({$this->getAdapter()}) as tmp");
	}

	/**
	 * Iterator. This method set in each loop the actual result
	 *
	 * @return \SuitUp\Paginate\Paginate
	 */
	public function rewind()
	{
		$this->_setResult();
		return $this;
	}

	/**
	 * Iterator SPL. Gives the current data of object as an array
	 *
	 * @return array
	 */
	public function current()
	{
		if ($this->result === null) {
			$this->rewind();
		}

		// Recupera item atual
		$item = current($this->result);

		// Se há funcao de callback executa-a
		if ($this->itemCallback) {
			$callBack = $this->itemCallback;
			$callBack($item);
		}
		return $item;
	}

	/**
	 * Iterator SPL. Gives the key data of object as an array
	 *
	 * @return int
	 */
	public function key()
	{
		return key($this->result);
	}

	/**
	 * Iterator SPL. Gives the next data of object as an array
	 *
	 * @return \SuitUp\Paginate\Paginate
	 */
	public function next()
	{
		next($this->result);
		return $this;
	}

	/**
	 * Iterator SPL. Gives if is valid loop of object as an array
	 *
	 * @return bool
	 */
	public function valid()
	{
		return $this->current() !== false;
	}

	/**
	 * Determine the total of pages the data will sliced
	 *
	 * @return \SuitUp\Paginate\Paginate
	 */
	private function _setTotalPages()
	{
		$this->totalPages = (int)ceil(
			count($this->db->query($this->getAdapter())) / $this->getNumberPerPage()
		);

		return $this;
	}

	/**
	 * Fetch SQL query to retrieve data sliced. Add to the SQL the limit clause
	 * based on number per page and the relation between current page with number
	 * per page.
	 *
	 * @return \SuitUp\Paginate\Paginate
	 */
	private function _setResult()
	{
		$this->_setTotalPages();

		$adapter = clone $this->getAdapter();

		if ($this->getCurrentPage() > $this->getTotalPages()) {
			$this->setCurrentPage($this->getTotalPages());
		}

		$offSet = (($this->getCurrentPage() - 1) * $this->getNumberPerPage());

		if ($offSet < 0) {
			$offSet = 0;
		}

		$this->result = $this->db->query(
			$adapter->limit($this->getNumberPerPage(), $offSet)
		);

		// Se há funcao de callback executa-a
		if ($this->itemCallback) {
			$callBack = $this->itemCallback;
			foreach ($this->result as $key => $item) {
				$this->result[$key] = $callBack($item);
			}
			reset($this->result);
		}
		return $this;
	}

	/**
	 * Return data sliced
	 *
	 * @return array
	 */
	public function getResult()
	{
		// @TODO: Watch
		if ($this->result === null) {
			$this->rewind();
		}
		return $this->result;
	}
}
