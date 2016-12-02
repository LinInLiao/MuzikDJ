<?php

defined('ROOT') || define('ROOT', dirname( dirname( __FILE__ ) ) );
defined('DS') || define('DS', DIRECTORY_SEPARATOR);
defined('SITENAME') || define('SITENAME', 'Muzikdj');

if (!defined('ENVIRONMENT')) {
    $environment = getenv('ENVIRONMENT');
    if (!$environment || !in_array($environment, array('development', 'staging', 'production'), true)) {
        define('ENVIRONMENT', 'development');
    } else {
        define('ENVIRONMENT', $environment);
    }
    unset($environment);
}

if (ENVIRONMENT === 'production') {
    define('YOUTUBE_API_KEY', getenv('YOUTUBE_API_KEY'));
} else {
    define('YOUTUBE_API_KEY', 'AIzaSyCw8tJZsPinlT35pjMMAtyp_OFFuNtyx70');
}

require (ROOT . DS .'vendor'. DS .'autoload.php');

use \Phalcon\Config\Adapter\Php as configPhpAdapter;
use \Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;
use \Phalcon\Session\Adapter\Libmemcached as Session;

/**
 * The FactoryDefault Dependency Injector automatically register the right services providing a full stack framework
 */
$di = new \Phalcon\DI\FactoryDefault();

$config = new \Phalcon\Config\Adapter\Php(ROOT . DS .'configs'. DS .'config.php');
$di->setShared('config', $config);

try {
    if (ENVIRONMENT !== 'production') {
        $whoops = new Whoops\Provider\Phalcon\WhoopsServiceProvider($di);
    }
    /**
     * Setting logger first
     */
    $loggerFolder = ROOT. DS .'cache'. DS .$config->logger->folder;
    if (!file_exists($loggerFolder)) {
        mkdir($loggerFolder, 0777, true);
    }
    $logger = new \Phalcon\Logger\Adapter\File($loggerFolder . DS . SITENAME . '.log');
    $logger->setLogLevel($config->logger->level);
    $di->setShared('logger', $logger);
    unset($loggerFolder);

    defined('NAME_WHITE_LIST') || define('NAME_WHITE_LIST', $config->whitelist->name);

    /**
     * Registering a set of directories taken from the configuration file
     */
    $loader = new \Phalcon\Loader();

    $loader->registerNamespaces(array(
        'Muzikdj\Library' => ROOT . DS . 'library',
        'Muzikdj\Models' => ROOT . DS . 'models',
        'Muzikdj\Plugins' => ROOT . DS . 'plugins',
        'Muzikdj\Api' => ROOT . DS . 'apps' . DS . 'Muzikdj'
    ))->register();

    /**
     * Set the global encryption key.
     */
    $di->setShared('crypt', function() use ($config) {
        $crypt = new \Phalcon\Crypt();
        $crypt->setKey($config->cookie->crypt);

        return $crypt;
    });
    /**
     * Database connection is created based in the parameters defined in the configuration file
     */
    $di->setShared('db', function() use ($config) {
        $db_config = $config->database[ENVIRONMENT];
        return new DbAdapter(array(
            'host'      => $db_config->dbhost,
            'username'  => $db_config->dbusername,
            'password'  => $db_config->dbpassword,
            'dbname'    => $db_config->dbname
        ));
    });

    /**
     * Start the session the first time some component request the session service
     */
    $di->setShared('cookies', function() use ($config) {
        $cookies = new \Phalcon\Http\Response\Cookies();
        return $cookies;
    });

    $di->setShared('session', function() use ($config) {
        $session = new Session(array(
            'servers' => array(array(
                'host' => $config->memcached->host,
                'port' => (int) $config->memcached->port,
                'weight' => 1,
            )),
            'client' => array(
                \Memcached::OPT_HASH => \Memcached::HASH_MD5,
                \Memcached::OPT_PREFIX_KEY => $config->memcached->prefix,
                \Memcached::OPT_RECV_TIMEOUT => 1000,
                \Memcached::OPT_SEND_TIMEOUT => 1000,
                \Memcached::OPT_TCP_NODELAY => true,
                \Memcached::OPT_SERVER_FAILURE_LIMIT => 50,
                \Memcached::OPT_CONNECT_TIMEOUT => 500,
                \Memcached::OPT_RETRY_TIMEOUT => 300,
                \Memcached::OPT_DISTRIBUTION => \Memcached::DISTRIBUTION_CONSISTENT,
                \Memcached::OPT_REMOVE_FAILED_SERVERS => true,
                \Memcached::OPT_LIBKETAMA_COMPATIBLE => true
            ),
            'lifetime' => (int) $config->memcached->lifetime,
            'prefix' => $config->memcached->prefix
        ));
        if ($session->isStarted() === false) {
            $session->start();
        }
        return $session;
    });

    /**
     * Security
     */
    $di->setShared('security', function() use ($config) {
        $security = new \Phalcon\Security();
        $security->setWorkFactor(12);
        return $security;
    });

    require ROOT . DS . 'configs' . DS . 'routes.php';

    if (getenv('MUZIKDJ_TESTING') === 'testing') {
        return $app;
    } else {
        $app->handle();
    }
} catch (\Phalcon\Mvc\Dispatcher\Exception $e) {
    if (isset($logger)) {
        $logger->error($e->getMessage().PHP_EOL.$e->getTraceAsString().PHP_EOL);
    }
    if (ENVIRONMENT !== 'production') {
        $di['whoops']->handleException($e);
        return;
    }
} catch (\Phalcon\Exception $e) {
    if (isset($logger)) {
        $logger->error($e->getMessage().PHP_EOL.$e->getTraceAsString().PHP_EOL);
    }
    if (ENVIRONMENT !== 'production') {
        $di['whoops']->handleException($e);
        return;
    }
} catch (\PDO\Exception $e) {
    if (isset($logger)) {
        $logger->error($e->getMessage().PHP_EOL.$e->getTraceAsString().PHP_EOL);
    }
    if (ENVIRONMENT !== 'production') {
        $di['whoops']->handleException($e);
        return;
    }
} catch (\Exception $e) {
    if (isset($logger)) {
        $logger->error($e->getMessage().PHP_EOL.$e->getTraceAsString().PHP_EOL);
    }
    if (ENVIRONMENT !== 'production') {
        $di['whoops']->handleException($e);
        return;
    }
}
