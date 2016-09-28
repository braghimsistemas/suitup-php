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

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', true);

include_once __DIR__ . "/../src/SuitUpStart.php";

// Para quando estamos mexendo diretamente no código
if (file_exists(__DIR__.'/../vendor/autoload.php')) {
	$autoload = include __DIR__.'/../vendor/autoload.php';
	$autoload->add('BraghimTest\\', __DIR__.DIRECTORY_SEPARATOR.'.');

// Para quando estamos alterando dentro do projeto que usa o SuitUp
} else if (file_exists(__DIR__.'/../../../autoload.php')) {
	$autoload = include __DIR__.'/../../../autoload.php';
	$autoload->add('BraghimTest\\', __DIR__.DIRECTORY_SEPARATOR.'.');

// Para os testes dentro do Travis.ci
} else {
	echo "\n\nNão encontramos a pasta vendor, o sistema não presseguirá com os testes.\n\n";
}
