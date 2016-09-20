<?php
error_reporting(E_ALL | E_STRICT);

if (is_dir('../../vendor')) {
	chdir('../../vendor');

} else if (is_dir('../../../vendor')) {
	chdir('../../../vendor');
} else if (is_dir('../vendor')) {
	chdir('../vendor');
}

if (file_exists('autoload.php')) {
	include 'autoload.php';
} else {
	echo "\n\n";
	print_r(scandir('../'));
	echo "\n\n";
}

if (class_exists('BraghimSistemas')) {
	echo "Classe BraghimSistemas encontrada =)\n\n";
} else {
	echo "Esses caminhos tão muito zoados, mano\n\n";
}

