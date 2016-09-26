<?php
namespace SuitUp\Database;

/**
 * Class Persistence
 *
 * @author		Author: Vivek Wicky Aswal. (https://twitter.com/#!/VivekWickyAswal)
 * @git			https://github.com/indieteq/PHP-MySQL-PDO-Database-Class
 * @version		0.2ab
 *
 * @package SuitUp\Database
 */
abstract class Persistence
{
	/**
	 * @var \PDO
	 */
	protected $pdo;

	/**
	 * @var \PDOStatement PDO statement object
	 */
	protected $sQuery;

	/**
	 * @var bool Connected to the database
	 */
	protected $bConnected = false;

	/**
	 * @var Object for logging exceptions
	 */
	protected $log;

	/**
	 * @var array, The parameters of the SQL query
	 */
	protected $parameters;

	/**
	 * @var bool Query rodou com sucesso?
	 */
	protected $success = false;

	/** Monitoramento de SQL **/

	/**
	 * @var bool Enable/Disable SQL monitoring
	 */
	protected $monitoring = false;

	/**
	 * @var array Queries list to log
	 */
	protected $queryLogs = array();

	/**
	 * @var int contador para os logs de query
	 */
	private $i = 0;

	/**
	 * 	This method makes connection to the database.
	 * 	
	 * 	1. Reads the database settings from a ini file. 
	 * 	2. Puts  the ini content into the settings array.
	 * 	3. Tries to connect to the database.
	 * 	4. If connection failed, exception is displayed and a log file gets created.
	 *
	 * @param string $hostname Host
	 * @param string $database databaseschema
	 * @param string $username usuario
	 * @param string $password senha
	 * @throws \Exception
	 * @return void
	 */
	protected function Connect($hostname = '', $database = '', $username = '', $password = '') {
		$dsn = 'mysql:dbname=' . $database . ';host=' . $hostname;
		try {
			# Read settings from INI file, set UTF8
			$this->pdo = new \PDO($dsn, $username, $password, array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));

			# We can now log any exceptions on Fatal error. 
			$this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

			# Disable emulation of prepared statements, use REAL prepared statements instead.
			$this->pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);

			# Connection succeeded, set the boolean to true.
			$this->bConnected = true;
		} catch (\PDOException $e) {
			# Write into log
			$this->ExceptionLog($e->getMessage());
			throw new \Exception($e->getMessage());
		}
	}

	/*
	 *   You can use this little method if you want to close the PDO connection
	 *
	 */

	public function CloseConnection() {
		# Set the PDO object to null to close the connection
		# http://www.php.net/manual/en/pdo.connections.php
		$this->pdo = null;
	}

	/**
	 * 	Every method which needs to execute a SQL query uses this method.
	 * 	
	 * 	1. If not connected, connect to the database.
	 * 	2. Prepare Query.
	 * 	3. Parameterize Query.
	 * 	4. Execute Query.	
	 * 	5. On exception : Write Exception into the log + SQL query.
	 * 	6. Reset the Parameters.
	 *
	 * @param mixed $query
	 * @param string $parameters
	 * @throws \Exception
	 */
	private function Init($query, $parameters = "") {
		# Connect to database
		if (!$this->bConnected) {
			$this->Connect();
		}
		try {
			# Prepare query
			$this->sQuery = $this->pdo->prepare($query);

			# Add parameters to the parameter array	
			$this->bindMore($parameters);

			// Lista de parametros que podem ser recuperados no log
			$logParamsList = array();
			
			# Bind parameters
			if (!empty($this->parameters)) {
				foreach ($this->parameters as $param) {
					$parameters = explode("\x7F", $param);
					$this->sQuery->bindParam($parameters[0], $parameters[1]);
					
					// Guarda parametros para exibir no log.
					$logParamsList[$parameters[0]] = $parameters[1];
				}
			}
			
			// Adiciona aos logs
			$this->queryLogs[(string) ++$this->i.' - '.mctime()] = array(
				'query' => (is_object($query) ? $query->__toString() : $query),
				'params' => $logParamsList
			);

			# Execute SQL
			$this->success = $this->sQuery->execute();
		} catch (\PDOException $e) {
			# Write into log and display Exception
			$this->ExceptionLog($e->getMessage(), $query);
			throw new \Exception($e->getMessage());
		}

		# Reset the parameters
		$this->parameters = array();
	}
	
	/**
	 * Reseta o objeto para poder rodar a proxima query.
	 * 
	 * @return Persistence
	 */
	public function reset() {
		$this->sQuery = null;
		$this->parameters = array();
		$this->success = false;
		
		return $this;
	}

	/**
	 * 	@void 
	 *
	 * 	Add the parameter to the parameter array
	 * 	@param string $para  
	 * 	@param string $value 
	 */
	public function bind($para, $value) {
		$this->parameters[sizeof($this->parameters)] = ":" . $para . "\x7F" . $value;
	}

	/**
	 * 	@void
	 * 	
	 * 	Add more parameters to the parameter array
	 * 	@param array $parray
	 */
	public function bindMore($parray) {
		if (empty($this->parameters) && is_array($parray)) {
			$columns = array_keys($parray);
			foreach ($columns as $i => &$column) {
				$this->bind($column, $parray[$column]);
			}
		}
	}

	/**
	 *  If the SQL query  contains a SELECT or SHOW statement it returns an array containing all of the result set row
	 * 	If the SQL statement is a DELETE, INSERT, or UPDATE statement it returns the number of affected rows
	 *
	 *  @param  string $query
	 * 	@param  array  $params
	 * 	@param  int    $fetchmode
	 * 	@return array
	 */
	public function query($query, $params = null, $fetchmode = \PDO::FETCH_ASSOC) {
		$query = trim($query);

		$this->Init($query, $params);

		$rawStatement = explode(" ", $query);

		# Which SQL statement is used 
		$statement = strtolower($rawStatement[0]);

		// Prepara para pagar o resultado
		$result = null;
		if ($statement === 'select' || $statement === 'show') {
			$result = $this->sQuery->fetchAll($fetchmode);
		} elseif ($statement === 'insert' || $statement === 'update' || $statement === 'delete') {
			$result = $this->sQuery->rowCount();
		}
		
		// Reseta a instancia para preparar
		// para proxima query
		$this->reset();
		
		return $result;
	}

	/**
	 *  Returns the last inserted id.
	 *  @return string
	 */
	public function lastInsertId() {
		return $this->pdo->lastInsertId();
	}

	/**
	 * 	Returns an array which represents a column from the result set 
	 *
	 * 	@param  string $query
	 * 	@param  array  $params
	 * 	@return array
	 */
	public function column($query, $params = null) {
		$this->Init($query, $params);
		$Columns = $this->sQuery->fetchAll(\PDO::FETCH_NUM);

		$column = null;

		foreach ($Columns as $cells) {
			$column[] = $cells[0];
		}

		// Reseta a instancia para preparar
		// para proxima query
		$this->reset();
		
		return $column;
	}

	/**
	 * 	Returns an array which represents a row from the result set 
	 *
	 * 	@param  string $query
	 * 	@param  array  $params
	 *   	@param  int    $fetchmode
	 * 	@return array
	 */
	public function row($query, $params = null, $fetchmode = \PDO::FETCH_ASSOC) {
		$this->Init($query, $params);
		$result = $this->sQuery->fetch($fetchmode);
		
		// Reseta a instancia para preparar
		// para proxima query
		$this->reset();
		
		return $result;
	}

	/**
	 * 	Returns the value of one single field/column
	 *
	 * 	@param  string $query
	 * 	@param  array  $params
	 * 	@return string
	 */
	public function single($query, $params = null) {
		$this->Init($query, $params);
		$result = $this->sQuery->fetchColumn();
		
		// Reseta a instancia para preparar
		// para proxima query
		$this->reset();
		
		return $result;
	}
	
	/**
	 * Enable/Disable SQL monitoring
	 * 
	 * @param bool $status
	 * @return \SuitUp\Database\Persistence
	 */
	public function setMonitoring($status) {
		$this->monitoring = (bool) $status;
		return $this;
	}

	/**
	 * Retorna o status do monitoramento de SQL.
	 * @return bool
	 */
	public function getMonitoring() {
		return $this->monitoring;
	}
	
	/**
	 * Se estiver habilitado retornara a lista de todas as queries rodadas nesta sessão.
	 * @return array
	 */
	public function getQueryLog() {
		if (!$this->monitoring) {
			return "";
		}
		
		$html = file_get_contents(__DIR__.'/query-log.min.html');
		
		$html .= '<div id="__SuitUp-query-log-tab__">SQL <span id="close">X</span></div>';
		
		$html .= '<div id="__SuitUp-query-log__">';
		$html .= '<div class="headding">Queries executadas nesta página <span id="closebox">X</span></div>';
		$html .= '<div class="mainbox">';
		
		// query e parametros
		foreach (array_reverse($this->queryLogs) as $k => $item) {
			
			if ($item['params']) {
				foreach ($item['params'] as $param => $value) {
					$item['query'] = str_replace($param, "'$value'", $item['query']);
				}
			}
			
			// $params = html_entity_decode(dump($item['params'], false));
			$html .= '<span>#'.$k.' - '.count($item['params']).' parâmetro(s)</span><p>'.$item['query'].'</p><hr/>';
		}
		$html .= '</div>';
		return $html;
	}

	/** 	
	 * Writes the log and returns the exception
	 *
	 * @param  string $message
	 * @param  string $sql
	 */
	private function ExceptionLog($message, $sql = "") {
		$exception = date('H:i:s')."\n";
		$exception .= "========================================================\n";
		$exception .= $message;

		if (!empty($sql)) {
			# Add the Raw SQL to the Log
			$exception .= "\r\nRaw SQL : " . $sql;
		}
		
		// Se nao existe pasta cria
		$dirlog = 'var/logs';
		if (!is_dir($dirlog)) {
			mkdir($dirlog, 0777, true);
		}
				
		# Write into log
		error_log($exception."\n\n", 3, $dirlog.'/database'.date('d-m-Y').'.log');
		chmod($dirlog.'/database'.date('d-m-Y').'.log', 0777);
	}
}
