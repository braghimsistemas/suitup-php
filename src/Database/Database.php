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
namespace SuitUp\Database;

use SuitUp\Mvc\MvcAbstractController;
use SuitUp\Paginate\Paginate;

/**
 * Class Database
 * @package SuitUp\Database
 */
class Database extends Persistence
{
	/**
	 * Singleton
	 */
	private static $instance;

	/**
	 * @var Config
	 */
	private static $config;

	/**
	 * Database constructor.
	 * @throws \Exception
	 */
	private function __construct() {

		/**
		 * Não foram setadas configuracoes para DB. Vamos
		 * procurar o arquivo database.config.php na pasta
		 * config que deveria estar na raiz do projeto.
		 */
		if (null == self::$config) {
			$dbConfigFile = dirname(MvcAbstractController::$params->mainPath).'/config/database.config.php';

			if (!file_exists($dbConfigFile)) {

				// O arquivo não existe, mas é um erro do sistema (provavelmente estrutura) então nao faz nada.
				if (MvcAbstractController::$params->moduleName == 'ModuleError') {
					return;
				}
				throw new \Exception("Nenhuma configuração de banco de dados configurada.");
			}

			// Encontrou arquivo, então vamos usa-lo para configurar.
			self::setConfig(include $dbConfigFile);
		}

		// Inclui as configuracoes
		$this->Connect(
			self::getConfig()->getHost(),
			self::getConfig()->getDatabase(),
			self::getConfig()->getUsername(),
			self::getConfig()->getPassword()
		);
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
	 * Inclui a configuração do banco de dados.
	 *
	 * @param array|\SuitUp\Database\Config $configs
	 * @throws \Exception
	 */
	public static function setConfig($configs) {

		if (is_array($configs)) {
			$configs = new Config($configs);
		}

		if (!$configs instanceof Config) {
			throw new \Exception("As configurações de banco de dados devem ser um 'array' ou uma instância de '\\SuitUp\\Database\\Config'");
		}

		// Instancia de Database\Config
		self::$config = $configs;
	}

	/**
	 * @return \SuitUp\Database\Config
	 */
	public static function getConfig() {
		if (null == self::$config) {
			self::setConfig(new Config());
		}
		return self::$config;
	}

	/**
	 * Retorna o objeto de paginacao.
	 * 
	 * @param mixed $query Instrucao SQL para executar no banco de dados com paginacao.
	 * @param \Closure $clousureFunc Adiciona a paginacao uma funcao que sera executada em cada item retornado na query.
	 * @return \SuitUp\Paginate\Paginate
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
