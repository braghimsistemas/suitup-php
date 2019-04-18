<?php

// Make the default dir path to the system
chdir(__DIR__);

// Setup DOCUMENT ROOT
$_ENV['DOCUMENT_ROOT'] = __DIR__;
$_SERVER['DOCUMENT_ROOT'] = $_ENV['DOCUMENT_ROOT'];
putenv("DOCUMENT_ROOT={$_ENV['DOCUMENT_ROOT']}");

