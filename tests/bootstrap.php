<?php

error_reporting(-1);
$_SERVER['SCRIPT_NAME'] = '/' . __DIR__;
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
define('IS_TESTING', true);

require_once __DIR__ . '/../vendor/autoload.php';
