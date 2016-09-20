<?php
error_reporting(E_ALL | E_STRICT);

// Para quando estamos mexendo diretamente no código
if (is_dir('../../vendor')) {
	chdir('../../vendor');

// Para quando estamos alterando dentro do projeto que usa o SuitUp
} else if (is_dir('../../../vendor')) {
	chdir('../../../vendor');

// Para os testes dentro do Travis.ci
} else if (is_dir(__DIR__ . '/../vendor')) {
	chdir(__DIR__ . '/../vendor');
}

if (file_exists('autoload.php')) {
	include 'autoload.php';
} else {
	echo "\n\nNão encontramos a pasta vendor, o sistema não presseguirá com os testes.\n\n";
}

