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

namespace BraghimTest;

use BraghimSistemas;

/**
 * Um arquivo que tem classe
 *
 * @author Marco A. Braghim <braghim.sistemas@gmail.com>
 * @since 20/09/16
 */
class BraghimSistemasTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Testa configuracao sem indicar pasta de modulos (erro)
	 */
	public function testExceptionSetup()
	{
		try {
			BraghimSistemas::setup();

			// Force error
			$this->assertEquals(false, true);

		} catch (\Exception $e) {
			$this->assertInstanceOf("\Exception", $e);
		}
	}

	/**
	 * Um setup que funciona
	 */
	public function testSetup()
	{
		$a = BraghimSistemas::setup(__DIR__.'/modulestest');
		$this->assertEquals(true, $a instanceof BraghimSistemas);
	}

	// Habilitando ou desabilitando os logs de queries
	public function testSqlMonitor()
	{
		$a = BraghimSistemas::setup(__DIR__.'/modulestest');

		// True
		$a->setSqlMonitor(true);
		$this->assertEquals(true, \Braghim\Database::getInstance()->getMonitoring());

		// False
		$a->setSqlMonitor(false);
		$this->assertNotTrue(\Braghim\Database::getInstance()->getMonitoring());
	}

}