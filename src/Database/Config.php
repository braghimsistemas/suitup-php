<?php
/**
 * hybrid - Braghim Sistemas - Software Privado
 *
 * Copyright 25 de Setembro, 2016
 *
 * Este é um sistema privado pertencente à empresa Braghim Sistemas.
 * Este software não foi desenvolvido com intuito de ser vendido ou mesmo
 * liberado para ser distribuído a nenhuma pessoa, mesmo que este seja ou
 * tenha sido um dia colaborador da empresa em nível de funcionário, freelancer
 * (trabalhos temporários) ou gerenciador de servidores aos quais a empresa
 * pode ter utilizado para manter os arquivos ou versões.
 *
 * Apenas a própria empresa Braghim Sistemas tem o direito de utilizá-lo a
 * menos que isto tenha sido contestado através de contrato devidamente
 * legal firmado com a empresa.
 *
 * Em poucas palavras: NENHUMA LINHA DE CÓDIGO DESTE SISTEMA DEVE SER COPIADA!
 *
 * @author jackie
 * @since 25/09/16
 */
namespace Braghim\Database;

/**
 * Um arquivo que tem classe
 *
 * @author jackie
 * @since 25/09/16
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
