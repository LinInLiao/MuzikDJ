<?php

use Muzikdj\Api\Plugins\Middleware;
use Muzikdj\Api\Plugins\ResponseMiddleware;

$collections = [];

$app = new \Phalcon\Mvc\Micro($di);

// Index Controller.
$apiEndPoint = new \Phalcon\Mvc\Micro\Collection();
$apiEndPoint->setHandler('Muzikdj\Api\Controllers\ApiController', true);
$apiEndPoint->setPrefix('/api');
$apiEndPoint->map('/account/rooms', 'accountRoomsAction', 'api-account-rooms');
$apiEndPoint->map('/account/save', 'accountEditAction', 'api-edit-account');
$apiEndPoint->map('/song/remove', 'removeSongAction', 'api-add-song');
$apiEndPoint->map('/song/create', 'createSongAction', 'api-add-song');
$apiEndPoint->map('/songs', 'getSongsAction', 'api-get-songs');
$apiEndPoint->map('/playlist/create', 'createPlaylistAction', 'api-create-playlist');
$apiEndPoint->map('/playlist', 'getPlaylistAction', 'api-get-playlist');
$apiEndPoint->map('/listen/rooms', 'getListenRoomsAction', 'api-get-listen-rooms');
$apiEndPoint->map('/room/{alias:[a-z0-9\-]+}', 'singleRoomAction', 'api-single-room');
$apiEndPoint->map('/room/check', 'checkRoomAction', 'api-get-check-rooms');
$apiEndPoint->map('/room/private', 'privateRoomAction', 'api-get-private-rooms');
$apiEndPoint->map('/room/remove', 'removeRoomAction', 'api-remove-room');
$apiEndPoint->map('/room/create', 'createRoomAction', 'api-create-room');
$apiEndPoint->map('/search', 'searchAction', 'api-search');
$apiEndPoint->map('/signup', 'signupAction', 'api-signup');
$apiEndPoint->map('/login', 'loginAction', 'api-login');
$apiEndPoint->map('/logout', 'logoutAction', 'api-logout');
$apiEndPoint->map('/auth/check', 'checkAuthAction', 'api-check-auth');

$app->mount($apiEndPoint);

$collections[$apiEndPoint->getHandler()] = [
    'prefix' => $apiEndPoint->getPrefix(),
    'handlers' => $apiEndPoint->getHandlers()
];
unset($apiEndPoint);

$app->setService('collections', function() use ($collections) {
    $collection_for_acl = [];
    foreach($collections as $main_handler => $params) {
        $main_handler = str_replace(['Muzikdj\\Api\\Controllers\\', 'Controller'], '', $main_handler);
        foreach($params['handlers'] as $param) {
            $collection_for_acl[$params['prefix'].$param[1]] = [
                'resource' => $main_handler,
                'access' => str_replace('Action', '', $param[2])
            ];
        }
    }
    return new \Phalcon\Config($collection_for_acl);
}, true);

$app->notFound(function() use ($app) {
    $middleware = new Middleware();
    $app = $middleware->setHeader($app);

    if (getenv('MUZIKDJ_TESTING') !== 'testing') {
        $app->stop();
    }
    $app->response->setStatusCode(404, 'Not Found')->sendHeaders();
    $app->response->setJsonContent([ 'status' => 'err', 'messages' => 'Page Not Found' ]);
    if (getenv('MUZIKDJ_TESTING') !== 'testing') {
        $app->response->send();
    }
});

// Hack...
$app->before(function() use ($app) {
    $middleware = new Middleware();
    if (true !== ($result = $middleware->call($app))) {
        if (getenv('MUZIKDJ_TESTING') !== 'testing') {
            $app->stop();
        }
        $app = $middleware->setHeader($app);
        $app->response->setStatusCode((int) $result['status'])->sendHeaders();
        $app->response->setJsonContent([
            'status' => ((int) $result['status'] === 200) ? 'ok' : 'err',
            'messages' => $result['messages']
        ]);

        if (getenv('MUZIKDJ_TESTING') !== 'testing') {
            $app->response->send();
        }
        return false;
    }
    return true;
});
$app->after(new ResponseMiddleware());
$app->finish(function () use ($app) {
    // This is executed when the request has been served
});
