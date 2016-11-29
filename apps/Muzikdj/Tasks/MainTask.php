<?php

use \Ratchet\Server\IoServer;
use \Ratchet\Http\HttpServer;
use \Ratchet\Wamp\WampServer;
use Muzikdj\Muzikdj\Plugins\Room;

final class MainTask extends \Phalcon\CLI\Task {

    public function initialize() {
        set_time_limit(0);
    }

    /**
     * CLI Helper
     * @return null
     */
    public function mainAction() {
        $loop = \React\EventLoop\Factory::create();
        $web_socket = new \React\Socket\Server($loop);
        $web_socket->listen(9000, '0.0.0.0');
        $web_server = new \Ratchet\Server\IoServer(
            new \Ratchet\Http\HttpServer(
                new \Ratchet\WebSocket\WsServer(
                    new \Ratchet\Wamp\WampServer(
                        new Room()
                    )
                )
            ),
            $web_socket
        );
        $loop->run();
    }

}