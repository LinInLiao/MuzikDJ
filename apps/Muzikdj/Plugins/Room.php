<?php

namespace Muzikdj\Muzikdj\Plugins;

use \Ratchet\Wamp\WampServerInterface;
use \Ratchet\ConnectionInterface;

final class Room implements WampServerInterface {
    protected $subscribedTopics = array();

    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible) {
        echo 'onPublish: '.$conn->resourceId."\n";
        if (isset($this->subscribedTopics[$topic->getId()])) {
            $this->subscribedTopics[$topic->getId()]->topic->broadcast($event);
        }
    }

    public function onCall(ConnectionInterface $conn, $id, $topic, array $params) {
        $conn->callError($id, $topic, 'You are not allowed to make calls')->close();
    }

    public function onSubscribe(ConnectionInterface $conn, $topic) {
        echo 'onSubscribe: '.$conn->resourceId."\n";
        $this->subscribedTopics[$topic->getId()] = (object) array(
            'resourceId' => $conn->resourceId,
            'topic' => $topic,
        );
    }
    public function onUnSubscribe(ConnectionInterface $conn, $topic) {}
    public function onOpen(ConnectionInterface $conn) {
        echo 'onOpen: '.$conn->resourceId."\n";
    }
    public function onClose(ConnectionInterface $conn) {
        echo 'onClose: '.$conn->resourceId."\n";
        foreach($this->subscribedTopics as $topic_id => $data) {
            if ($data->resourceId === $conn->resourceId) {
                $this->subscribedTopics[$topic_id]->topic->broadcast(array(
                    true, time()
                ));
            }
        }
    }
    public function onError(ConnectionInterface $conn, \Exception $e) {}
}