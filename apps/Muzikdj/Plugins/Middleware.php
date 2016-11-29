<?php

namespace Muzikdj\Muzikdj\Plugins;

use \Phalcon\Events\Event;
use \Phalcon\Mvc\Dispatcher;
use \Phalcon\Http\Request;
use \Phalcon\Acl;

final class Middleware extends \Phalcon\DI\Injectable {

    /**
     * Event Manager [beforeDispatch] Check the user login statement.
     * @param  Event      $event      Events\Event
     * @param  Dispatcher $dispatcher Dispatcher
     * @return boolean                return false will stop the current operation
     */
    public function beforeDispatchLoop(Event $event, Dispatcher $dispatcher) {
    }

    public function beforeExecuteRoute(Event $event, Dispatcher $dispatcher) {
    }

    /**
     * [beforeException description]
     * @param  Event      $event      [description]
     * @param  Dispatcher $dispatcher [description]
     * @param  [type]     $exception  [description]
     * @return [type]                 [description]
     */
    public function beforeException(Event $event, Dispatcher $dispatcher, $exception) {
        // In the production mode, handler and action not found will be forward to 404 Not Found page.
        if (defined(ENVIRONMENT) && ENVIRONMENT === 'production') {
            $dispatcher->forward(array(
                'controller' => 'Index',
                'action' => 'notFound'
            ));
            return false;
        } else {
            throw new \Exception($exception);
        }
    }
}
