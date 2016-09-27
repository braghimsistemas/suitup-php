<?php
/**
 * hybrid - Braghim Sistemas - Software Privado
 *
 * Copyright 20 de Setembro, 2016
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
 * @author Marco A. Braghim <braghim.sistemas@gmail.com>
 * @since 20/09/16
 */

namespace SuitUpTest;

// Define 'env' que indica que quem está rodando o teste é o Travis.
defined('TRAVIS') || define('TRAVIS', (bool) getenv('TRAVIS'));


use SuitUp\Database\Database;
use SuitUp\Database\Config;
use SuitUpStart;

/**
 * Um arquivo que tem classe
 *
 * @author Marco A. Braghim <braghim.sistemas@gmail.com>
 * @since 20/09/16
 */
class SuitUpStartTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Testa configuracao sem indicar pasta de modulos (erro)
	 */
	public function testExceptionSetup()
	{
		try {
			SuitUpStart::setup();

		} catch (\Exception $e) {

			// Exception instance
			$this->assertInstanceOf("\Exception", $e);

			// Error message indicate right error type
			$this->assertEquals("Necessário informar a pasta onde os módulos serão criados.", $e->getMessage());
		}
	}

	public function testExceptionWrongPath()
	{
		try {
			SuitUpStart::setup('wrong-path');

		} catch (\Exception $e) {

			// Exception instance
			$this->assertInstanceOf("\Exception", $e);

			// Error message indicate right error type
			$this->assertEquals("O diretório de módulos 'wrong-path' não existe", $e->getMessage());
		}
	}

	/**
	 * Um setup que funciona
	 */
	public function testSetup()
	{
		// Limpa a instancia.
		SuitUpStart::clearInstance();

		$a = SuitUpStart::setup(__DIR__.'/modulestest');
		$this->assertInstanceOf("SuitUpStart", $a);
	}

	/**
	 * getInstance() retorna instancia da classe?
	 */
	public function testSingleton() {

		// Não usar clearInstance aqui.
		$this->assertInstanceOf("SuitUpStart", SuitUpStart::getInstance());
	}

	// Habilitando ou desabilitando os logs de queries
	public function testSqlMonitor()
	{
		// Se quem está rodando o teste é o Travis, usa as configuracoes
		// dele, senão usa as configuracoes do arquivo database.config.php
		// que não é integrado ao git por questoes de seguranca...
		if (TRAVIS) {
			Database::setConfig(new Config(array(
				'host' => 'localhost',
				'database' => 'test',
				'username' => 'root',
				'password' => ''
			)));
		}

		// True
		SuitUpStart::getInstance()->setSqlMonitor(true);
		$this->assertEquals(true, Database::getInstance()->getMonitoring());

		// False
		SuitUpStart::getInstance()->setSqlMonitor(false);
		$this->assertNotTrue(Database::getInstance()->getMonitoring());
	}

	public function testRun()
	{
		// Captura a saida como se fosse html
		ob_start();
		SuitUpStart::getInstance()->run();
		$content = ob_get_clean();

		$this->assertEquals("Temos o layout\ne a view", $content);
	}

}