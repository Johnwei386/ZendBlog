<?php
/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
chdir(dirname(__DIR__));
define('ROOT_PATH', dirname(__DIR__));
define("DS",DIRECTORY_SEPARATOR); //路径分隔符，当前系统为/
define("PS",PATH_SEPARATOR); //在当前系统下为:
define('CONFIG_PATH', dirname(__DIR__).DS.'config');

//设置时区
date_default_timezone_set('Asia/Chongqing');

// Decline static file requests back to the PHP built-in webserver
if (php_sapi_name() === 'cli-server' && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    return false;
}

// Setup autoloading
require 'init_autoloader.php';

// Run the application!
Zend\Mvc\Application::init(require 'config/application.config.php')->run();

