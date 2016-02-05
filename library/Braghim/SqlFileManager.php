<?php
namespace Braghim;

use Exception;

class SqlFileManager
{
	const INT_TYPE = 'INTEGER';
	const BIGINT_TYPE = 'BIGINT';
	const FLOAT_TYPE = 'FLOAT';
	
	public $sql;
	
	private $select;
	private $where;
	private $group;
	private $order;
	private $having;
	private $limit;

	public function __construct($filename = null, $tablename = null) {
		if ($filename && $tablename) {
			$params = MvcAbstractController::$params;

			// Caminho para chegar aos arquivos SQL
			$file = implode(DIRECTORY_SEPARATOR, array(
				$params->mainPath,
				$params->moduleName,
				'Model',
				'SqlFiles',
				$tablename,
				(string) $filename.'.sql'
			));

			// Arquivo não existe =(
			if (!file_exists($file) || !is_readable($file)) {
				throw new Exception("O arquivo '$file' não existe ou não pode ser lido, impossível criar query");
			}

			// Corrige espaços sobrando no arquivo de sql
			$this->sql = trim(preg_replace("/\s+/", " ", file_get_contents($file)));

			$this->split();
		}
	}
	
	/**
	 * Transforma este objeto em string.
	 * 
	 * @return type
	 */
	public function __toString() {
		$sql = $this->select;
		$sql .= $this->where ? " WHERE ".$this->where : '';
		$sql .= $this->group ? " GROUP BY ".$this->group : '';
		$sql .= $this->order ? " ORDER BY ".$this->order : '';
		$sql .= $this->having ? " HAVING ".$this->having : '';
		$sql .= $this->limit ? " LIMIT ".$this->limit : '';
			
		return $sql;
	}
	
	/**
	 * Separa as clausulas da query em objetos
	 * 
	 * @return SqlFileManager
	 */
	public function split()
	{
		// Ultimo objeto utilizado
		$last = 'select';
		$ahead = $this->sql;
		
		// Separa a query por partes
		$inst = array('where' => 'WHERE', 'group' => 'GROUP BY', 'order' => 'ORDER BY', 'having' => 'HAVING', 'limit' => 'LIMIT');
		foreach($inst as $next => $instrucao) {
			
			// Instrucao existe na query
			if (preg_match("/$instrucao/", $ahead)) {
				
				// Separa em duas partes
				$parts = explode($instrucao, $ahead);
				$this->$last = trim($parts[0]);
				$ahead = trim($parts[1]);
				
				$last = $next;
			}
		}
		
		// Adiciona o ultimo
		$this->$last = $ahead;
		return $this;
	}
	
	/**
	 * Adiciona coluna na query
	 * 
	 * @param type $column
	 * @return SqlFileManager
	 */
	public function select($column, $alias = null)
	{
		$left = trim(preg_replace("/(FROM).+/", '', $this->select));
		$right = trim(preg_replace("/.+(FROM)/", '', $this->select));
		
		$left .= ($left) ? ", ".$column : $column;
		if ($alias) {
			$left .= " AS $alias";
		}
		
		$this->select = $left." FROM ".$right;
		return $this;
	}
	
	/**
	 * Adiciona um parametro no WHERE da instrucao
	 * 
	 * @param type $where
	 * @param type $value
	 * @param type $type
	 * @return SqlFileManager
	 */
	public function where($where, $value = null, $type = null)
	{
		// Parametro passado como array
		if (is_array($where)) {
			foreach ($where as $text => $val) {
				$this->where((string) $text, $val);
			}
			return $this;
		}
		
		// Se tem valor protege contra injection
		if ($value) {
			// No caso de subquery
			if ($value instanceof SqlFileManager) {
				$where = str_replace('?', $value, $where);
			} else {
				$where = str_replace('?', $this->quote($value, $type), $where);
			}
		}
		
		// Adiciona () se possível
		$where = preg_match("/^\(.+\)$/", $where) ? $where : "(".$where.")";
		
		// Se já tem alguma condição no WHERE adiciona 'AND'
		if ($this->where) {
			$where = " AND ".$where;
		}
		
		// Junta tudo
		$this-> where .= $where;
		return $this;
	}
	
	/**
	 * Adiciona instrucao GROUP
	 * 
	 * @param type $column
	 * @return SqlFileManager
	 */
	public function group($column)
	{
		$group = "";
		if (is_array($column)) {
			foreach ($column as $value) {
				$group .= ", ".$value;
			}
		} else {
			$group .= ", ".$column;
		}
		$group = trim($group, ', ');
		
		$this->group .= ($this->group) ? ", ".$group : $group;
		return $this;
	}
	
	/**
	 * Adiciona instrucao ORDER
	 * 
	 * @param type $column
	 * @return SqlFileManager
	 */
	public function order($column)
	{
		$order = "";
		if (is_array($column)) {
			foreach ($column as $value) {
				$order .= ", ".$value;
			}
		} else {
			$order .= ", ".$column;
		}
		$order = trim($order, ', ');
		
		$this->order .= ($this->order) ? ", ".$order : $order;
		return $this;
	}
	
	/**
	 * Substitui drasticamente a instrucao HAVING
	 * @todo Melhorar... =P
	 * 
	 * @param type $text
	 * @return SqlFileManager
	 */
	public function having($text) {
		$this->having = $text;
		return $this;
	}
	
	/**
	 * Substitui parametro LIMIT na consulta.
	 * 
	 * @param type $limit
	 * @param type $offset
	 * @return SqlFileManager
	 */
	public function limit($limit, $offset = null) {
		$this->limit = $limit;
		if ($offset) {
			$this->limit .= " OFFSET ".$offset;
		}
		return $this;
	}
	
	/**
     * Quote a raw string.
	 * 
	 * Zend Framework V = 1.11.4
     *
     * @param string $value     Raw string
     * @return string           Quoted string
     */
	protected function _quote($value)
    {
        if (is_int($value)) {
            return $value;
        } elseif (is_float($value)) {
            return sprintf('%F', $value);
        }
        return "'" . addcslashes($value, "\000\n\r\\'\"\032") . "'";
    }
	
	/**
     * Safely quotes a value for an SQL statement.
	 * 
	 * Zend Framework V = 1.11.4
     *
     * @param mixed $value The value to quote.
     * @param mixed $type  OPTIONAL the SQL datatype name, or constant, or null.
     * @return mixed An SQL-safe quoted value (or string of separated values).
     */
    public function quote($value, $type = null)
	{
		$numericDataTypes = array(self::INT_TYPE, self::BIGINT_TYPE, self::FLOAT_TYPE);
		if ($type !== null && in_array($type = strtoupper($type), $numericDataTypes)) {
			
            $quotedValue = '0';
            switch ($type) {
                case self::INT_TYPE: // 32-bit integer
                    $quotedValue = (string) intval($value);
                    break;
				
                case self::BIGINT_TYPE: // 64-bit integer
                    // ANSI SQL-style hex literals (e.g. x'[\dA-F]+')
                    // are not supported here, because these are string
                    // literals, not numeric literals.
                    if (preg_match('/^(
                          [+-]?                  # optional sign
                          (?:
                            0[Xx][\da-fA-F]+     # ODBC-style hexadecimal
                            |\d+                 # decimal or octal, or MySQL ZEROFILL decimal
                            (?:[eE][+-]?\d+)?    # optional exponent on decimals or octals
                          )
                        )/x',
                        (string) $value, $matches)) {
                        $quotedValue = $matches[1];
                    }
                    break;
					
                case self::FLOAT_TYPE: // float or decimal
                    $quotedValue = sprintf('%F', $value);
            }
            return $quotedValue;
        }
        return $this->_quote($value);
	}
}
