<?php
error_reporting(E_ALL | E_STRICT);

if (is_dir('../../vendor')) {
	chdir('../../vendor');

} else if (is_dir('../../../vendor')) {
	chdir('../../../vendor');
}

include 'autoload.php';

if (class_exists('BraghimSistemas')) {
	echo "Classe BraghimSistemas encontrada =)\n\n";
} else {
	echo "Esses caminhos tão muito zoados, mano\n\n";
}

