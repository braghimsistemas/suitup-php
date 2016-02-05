<?php
namespace Braghim;

class Database extends Database\Persistence
{
	/**
	 * Singleton
	 */
	private static $instance;
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
	private function __clone() { }
	
	public static function getInstance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
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
