<?php
namespace Braghim;

include_once 'Database/Persistence.php';

/**
 * Class Database
 * @package Braghim
 */
class Database extends Database\Persistence
{
	/**
	 * Singleton
	 */
	private static $instance;

	/**
	 * Database constructor.
	 * @throws \Exception
	 */
	private function __construct() {
		if (!file_exists('config/database.config.php')) {
			throw new \Exception(
				"O arquivo 'config/database.config.php' não existe: return array('host' => '', 'database' => '', 'username' => '', 'password' => '');"
			);
		}
		$params = include 'config/database.config.php';
		$this->Connect($params['host'], $params['database'], $params['username'], $params['password']);
		$this->parameters = array();
	}

	/**
	 *
	 */
	private function __clone() { }
	
	/**
	 * Instancia do banco de dados.
	 * @return Database
	 */
	public static function getInstance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		
		// Em casos onde houve um bug relacionado a alguma query
		// pode acontecer do objeto (Database) estar ainda carregado
		// com as informações da ultima query, por isso resetamos ele
		// antes de retornar a instancia.
		self::$instance->reset();
		
		return self::$instance;
	}
	
	/**
	 * Renova a instancia do banco de dados.
	 * Pense bem antes de utilizar este método, pois ele remove a conexão antiga
	 * e cria uma nova. Isto pode deixar o sistema mais lento.
	 * 
	 * @return Database
	 */
	public static function renewInstance() {
		self::$instance = null;
		return self::getInstance();
	}
	
	/**
	 * Retorna o objeto de paginacao.
	 * 
	 * @param mixed $query Instrucao SQL para executar no banco de dados com paginacao.
	 * @param \Closure $clousureFunc Adiciona a paginacao uma funcao que sera executada em cada item retornado na query.
	 * @return \Braghim\Paginate
	 */
	public function paginate($query, $clousureFunc = null) {
		return new Paginate($this, $query, $clousureFunc);
	}
	
	/**
	 * Inicia transacao com o banco de dados
	 * 
	 * @return bool
	 */
	public static function beginTransaction() {
		return self::getInstance()->pdo->beginTransaction();
	}
	
	/**
	 * Confirma transacao com banco de dados
	 * 
	 * @return bool
	 */
	public static function commit() {
		return self::getInstance()->pdo->commit();
	}
	
	/**
	 * Desfaz alteracoes no banco de dados sob transacao.
	 * 
	 * @return bool
	 */
	public static function rollBack() {
		return self::getInstance()->pdo->rollBack();
	}
}
