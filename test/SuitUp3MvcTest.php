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

use SuitUp\Mvc\MvcAbstractController;
use SuitUpStart;

/**
 * Um arquivo que tem classe
 *
 * @author jackie
 * @since 26/09/16
 */
class SuitUp3MvcTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var MvcAbstractController;
	 */
	private $app;

	public function __construct($name = null, array $data = array(), $dataName = '')
	{
		parent::__construct($name, $data, $dataName);
		$this->app = SuitUpStart::setup(__DIR__.'/modulestest')->mvc->controller;
	}

	public function testMsgNsp()
	{
		$this->assertEquals("default_MSGRedir", $this->app->getMsgNsp());
	}

	public function testModuleName()
	{
		$this->assertEquals("default", $this->app->getModuleName());
	}

	public function testControllerName()
	{
		$this->assertEquals("index", $this->app->getControllerName());
	}

	public function testActionName()
	{
		$this->assertEquals("index", $this->app->getActionName());
	}

	public function testLayoutName()
	{
		$this->assertEquals("layout.phtml", $this->app->getLayoutName());
	}

	public function testSetLayout()
	{
		$this->app->setLayout('teste-layout.phtml', __DIR__.'/modulestest/ModuleDefault/views/');
		$this->assertEquals("teste-layout.phtml", $this->app->getLayoutName());
	}

	public function testRenderView()
	{
		$result = $this->app->renderView(
			'render-view-test.phtml',
			array('resposta' => 42),
			__DIR__.'/modulestest/ModuleDefault/views/'
		);

		$this->assertEquals("Qual a resposta para a vida, o universo e tudo mais?\nR: 42", $result);
	}

}
