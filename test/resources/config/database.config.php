<?php
return array(
  'adapter' => 'mysql',
  'host' =>     IS_TRAVIS_CI ? '127.0.0.1' : '127.0.0.1',
  'port' =>     IS_TRAVIS_CI ? '3306'      : '3406',
  'dbname' =>   IS_TRAVIS_CI ? 'suitup'    : 'suitup',
  'username' => IS_TRAVIS_CI ? 'root'      : 'root',
  'password' => IS_TRAVIS_CI ? ''          : '142536'
);
