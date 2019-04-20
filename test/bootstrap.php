<?php

// Make the default dir path to the system
chdir(__DIR__);

// Define false when it's not Travis CI
defined('IS_TRAVIS_CI') || define('IS_TRAVIS_CI', false);

// Setup DOCUMENT ROOT
$_ENV['DOCUMENT_ROOT'] = __DIR__;
$_SERVER['DOCUMENT_ROOT'] = $_ENV['DOCUMENT_ROOT'];
putenv("DOCUMENT_ROOT={$_ENV['DOCUMENT_ROOT']}");

