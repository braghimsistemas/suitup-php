<?php
namespace SuitUp\Database;

/**
 * Class Config
 * @package SuitUp\Database
 */
class Config
{
	/**
	 * @var string
	 */
	private $host = 'localhost';

	/**
	 * @var string
	 */
	private $database = '';

	/**
	 * @var string
	 */
	private $username = 'root';

	/**
	 * @var string
	 */
	private $password = '';

	/**
	 * Config constructor.
	 * @param array $configs
	 */
	public function __construct(array $configs = array()) {

		// Host
		if (isset($configs['host'])) {
			$this->setHost($configs['host']);
		}

		// Database
		if (isset($configs['database'])) {
			$this->setDatabase($configs['database']);
		}

		// Username
		if (isset($configs['username'])) {
			$this->setUsername($configs['username']);
		}

		// Password
		if (isset($configs['password'])) {
			$this->setPassword($configs['password']);
		}
	}

	/**
	 * @return string
	 */
	public function getHost() {
		return $this->host;
	}

	/**
	 * @param string $host
	 * @return Config
	 */
	public function setHost($host) {
		$this->host = $host;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getDatabase() {
		return $this->database;
	}

	/**
	 * @param string $database
	 */
	public function setDatabase($database) {
		$this->database = $database;
	}

	/**
	 * @return string
	 */
	public function getUsername() {
		return $this->username;
	}

	/**
	 * @param string $username
	 */
	public function setUsername($username) {
		$this->username = $username;
	}

	/**
	 * @return string
	 */
	public function getPassword() {
		return $this->password;
	}

	/**
	 * @param string $password
	 */
	public function setPassword($password) {
		$this->password = $password;
	}
}
