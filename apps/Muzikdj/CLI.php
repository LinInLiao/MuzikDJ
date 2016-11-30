<?php

define('VERSION', '0.1.0');
defined('ROOT') || define('ROOT', realpath(dirname(dirname(dirname(__FILE__)))));
defined('DS') || define('DS', DIRECTORY_SEPARATOR);
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__)));

if (!defined('ENVIRONMENT')) {
    $environment = getenv('ENVIRONMENT');
    if (!$environment || !in_array($environment, array('development', 'staging', 'production'), true)) {
        define('ENVIRONMENT', 'development');
    } else {
        define('ENVIRONMENT', $environment);
    }
    unset($environment);
}

define('YOUTUBE_API_KEY', getenv('YOUTUBE_API_KEY'));

require (ROOT . DS .'vendor'. DS .'autoload.php');

$loader = new \Phalcon\Loader();
$loader->registerNamespaces(array(
    'Muzikdj\Library' => ROOT . DS . 'library',
    'Muzikdj\Models' => ROOT . DS . 'models',
    'Muzikdj\Plugins' => ROOT . DS . 'plugins',
    'Muzikdj\Api\Plugins' => APPLICATION_PATH . DS . 'Plugins',
))->register();

$di = new \Phalcon\DI\FactoryDefault\CLI();

$config = new \Phalcon\Config\Adapter\Ini(ROOT . DS .'configs'. DS .'config.ini');
$di->set('config', $config, true);

/**
 * Set the global encryption key.
 */
$di->set('crypt', function() use ($config) {
    $crypt = new \Phalcon\Crypt();
    $crypt->setKey($config->cookie->crypt);

    return $crypt;
}, true);
/**
 * Database connection is created based in the parameters defined in the configuration file
 */
$di->set('db', function() use ($config) {
    $db_config = $config->database[ENVIRONMENT];
    return new DbAdapter(array(
        'host'      => $db_config->dbhost,
        'username'  => $db_config->dbusername,
        'password'  => $db_config->dbpassword,
        'dbname'    => $db_config->dbname
    ));
}, true);

/**
 * If the configuration specify the use of metadata adapter use it or use memory otherwise
 */
$di->set('modelsMetadata', function() use ($config) {
    if (isset($config->metadata) && ENVIRONMENT === 'production') {
        $metadataAdapter = 'Metadata\\' . $config->metadata->adapter;
        return new $metadataAdapter();
    }
    return new Metadata\Memory();
}, true);

$console = new \Phalcon\CLI\Console();
$console->setDI($di);

$arguments = array();

foreach($argv as $k => $arg) {
    if($k == 1) {
       $arguments['task'] = $arg;
    } elseif($k == 2) {
       $arguments['action'] = $arg;
    } elseif($k >= 3) {
      array_push($arguments, $arg);
    }
}

define('CURRENT_TASK', (isset($argv[1]) ? $argv[1] : null));
define('CURRENT_ACTION', (isset($argv[2]) ? $argv[2] : null));

$di->setShared('console', $console);

try {
    $console->handle($arguments);
} catch (\Phalcon\Exception $e) {
    echo '[ERROR] '.$e->getMessage()."\n".$e->getTraceAsString()."\n";
    exit(255);
} catch (\Exception $e) {
    echo '[ERROR] '.$e->getMessage()."\n".$e->getTraceAsString()."\n";
    exit(255);
}