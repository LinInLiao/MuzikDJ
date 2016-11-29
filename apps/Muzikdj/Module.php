<?php

namespace Muzikdj\Muzikdj;

use Muzikdj\Muzikdj\Plugins\Middleware as Middleware;
use \Phalcon\Dispatcher;
use \Phalcon\Mvc\Dispatcher as MvcDispatcher;
use \Phalcon\Mvc\Dispatcher\Exception as DispatchException;
use \Phalcon\Loader;
use \Phalcon\Events\Manager;
use \Phalcon\Cache\Multiple;
use \Phalcon\Cache\Backend\Apc as ApcCache;
use \Phalcon\Cache\Backend\File as FileCache;
use \Phalcon\Cache\Frontend\Data as DataFrontend;
use \Phalcon\Cache\Backend\Libmemcached as MemcachedCache;

final class Module {
    public function registerAutoloaders($di) {
        $loader = new Loader();
        $loader->registerNamespaces(array(
            'Muzikdj\Muzikdj\Controllers' => ROOT . DS . 'apps' . DS . SITENAME . DS . 'Controllers',
            'Muzikdj\Muzikdj\Plugins' => ROOT . DS . 'apps' . DS . SITENAME . DS . 'Plugins',
            'Muzikdj\Muzikdj\Views' => ROOT . DS . 'apps' . DS . SITENAME . DS . 'Views',
        ))->register();
    }

    public function registerServices($di) {
        $config = $di->get('config');

        /**
         * Set multiple cache
         */
        $di->set('cache', function() use ($config) {
            $lifetime = (int) $config->memcached->lifetime; // 8600
            return new Multiple(array(
                // new ApcCache(new DataFrontend(array(
                //     'lifetime' => $lifetime - 5000
                // )), array(
                //     'prefix' => 'bountyHunterCache'
                // )),
                new MemcachedCache(new DataFrontend(array(
                    'lifetime' => $lifetime
                )), array(
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
                )),
                new FileCache(new DataFrontend(array(
                    'lifetime' => $lifetime * 10
                )), array(
                    'prefix' => 'muzikdj.',
                    'cacheDir' => ROOT . DS .'cache'. DS . 'data_cache' . DS
                ))
            ));
        });

        $di->set('dispatcher', function() use ($di, $config) {
            $eventsManager = new Manager();
            $eventsManager = $di->getShared('eventsManager');

            /**
             * Middleware
             * @var Middleware
             */
            $eventsManager->attach('dispatch', new Middleware());

            $dispatcher = new MvcDispatcher();
            $dispatcher->setEventsManager($eventsManager);
            $dispatcher->setDefaultNamespace('Muzikdj\Muzikdj\Controllers\\');

            return $dispatcher;
        });
    }
}
