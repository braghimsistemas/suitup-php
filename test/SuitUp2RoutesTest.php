<?php
/**
 * hybrid - Braghim Sistemas - Software Privado
 *
 * Copyright 26 de Setembro, 2016
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
 * @since 26/09/16
 */
namespace SuitUpTest;

use SuitUp\Routes\Routes;
use SuitUpStart;

/**
 * Class RoutesTest
 * @package SuitUpTest
 */
class SuitUp2RoutesTest extends \PHPUnit_Framework_TestCase
{
	public function testParams()
	{
		$this->assertEquals(array(), Routes::getInstance()->getParams());
	}

	public function testModule()
	{
		$this->assertEquals('default', Routes::getInstance()->getModuleName());
	}

	public function testController()
	{
		$this->assertEquals('index', Routes::getInstance()->getControllerName());
	}

	public function testAction()
	{
		$this->assertEquals('index', Routes::getInstance()->getActionName());
	}
}