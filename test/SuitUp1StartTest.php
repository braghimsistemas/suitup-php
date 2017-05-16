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
			new SuitUpStart(null);

		} catch (\Exception $e) {

			echo $e->getMessage();
			
			// Exception instance
			$this->assertInstanceOf("\Exception", $e);
		}
	}

	public function testExceptionWrongPath()
	{
		try {
			
			SuitUpStart::setup('wrong-path');
			throw new TesteException("Ops, will never get here if it is all right.");

		} catch (\Exception $e) {
			
			//dump($e);

			// Exception instance
			$this->assertInstanceOf("\Exception", $e); // Right
			//$this->assertNotInstanceOf("\SuitUpTest\TesteException", $e); // Error
		}
	}

	/**
	 * Um setup que funciona
	 */
	public function testSetup()
	{
		$a = new SuitUpStart(__DIR__.'/modulestest');
		$this->assertInstanceOf("SuitUpStart", $a);
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

		$mvc = new SuitUpStart(__DIR__.'/modulestest');
		
		// True
		$mvc->setSqlMonitor(true);
		$this->assertEquals(true, Database::getInstance()->getMonitoring());

		// False
		$mvc->setSqlMonitor(false);
		$this->assertNotTrue(Database::getInstance()->getMonitoring());
	}

	public function testRun()
	{
		// Captura a saida como se fosse html
		ob_start();
		$mvc = new SuitUpStart(__DIR__.'/modulestest');
		$mvc->run();
		$content = ob_get_clean();

		$this->assertEquals("Temos o layout\ne a view", $content);
	}
}