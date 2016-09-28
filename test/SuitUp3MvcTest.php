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
			array('resposta' => 42)
		);

		$this->assertEquals("Qual a resposta para a vida, o universo e tudo mais?\nR: 42", $result);
	}

}
