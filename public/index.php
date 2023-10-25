<?php

error_reporting(E_ALL);

define('BASE_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('APP_PATH', BASE_PATH . 'app' . DIRECTORY_SEPARATOR);

include_once APP_PATH . '/core/init.php';

use Amon\Di\FactoryDefault;

$di = new FactoryDefault();

include_once APP_PATH . '/config/services.php';

$application = $di->getApplication($di);

$application->run();
