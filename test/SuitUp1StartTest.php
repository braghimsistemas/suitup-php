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
class SuitUp1StartTest extends \PHPUnit_Framework_TestCase
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