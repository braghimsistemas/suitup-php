<?php
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', true);

include_once __DIR__."/../src/BraghimSistemas.php";

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
