<?php

use \Phalcon\Mvc\Router,
    \Phalcon\Mvc\Router\Group as RouterGroup;

$router = new Router(false);
$router->setDefaultModule(SITENAME);
$router->removeExtraSlashes(true);
$router->setUriSource(Router::URI_SOURCE_SERVER_REQUEST_URI);

$router->add('/', array(
	'module'     	=> SITENAME,
    'controller'    => 'Index',
    'action'        => 'index'
))->setName('homepage');

$api = new RouterGroup(array(
    'module'     	=> SITENAME,
    'controller' 	=> 'Api'
));

$api->setPrefix('/api');

$api->add('/account/rooms', array(
    'action' => 'accountRooms'
))->setName('api-account-rooms');

$api->add('/account/save', array(
    'action' => 'accountEdit'
))->setName('api-edit-account');

$api->add('/song/remove', array(
    'action' => 'removeSong'
))->setName('api-add-song');

$api->add('/song/create', array(
    'action' => 'createSong'
))->setName('api-add-song');

$api->add('/songs', array(
    'action' => 'getSongs'
))->setName('api-get-songs');

$api->add('/playlist/create', array(
    'action' => 'createPlaylist'
))->setName('api-create-playlist');

$api->add('/playlist', array(
    'action' => 'getPlaylist'
))->setName('api-get-playlist');

$api->add('/listen/rooms', array(
    'action' => 'getListenRooms'
))->setName('api-get-listen-rooms');

$api->add('/room/{alias:[a-z0-9\-]+}', array(
    'action' => 'singleRoom'
))->setName('api-single-room');

$api->add('/room/check', array(
    'action' => 'checkRoom'
))->setName('api-get-check-rooms');

$api->add('/room/private', array(
    'action' => 'privateRoom'
))->setName('api-get-private-rooms');

$api->add('/room/remove', array(
    'action' => 'removeRoom'
))->setName('api-remove-room');

$api->add('/room/create', array(
    'action' => 'createRoom'
))->setName('api-create-room');

$api->add('/search', array(
    'action' => 'search'
))->setName('api-search');

$api->add('/signup', array(
    'action' => 'signup'
))->setName('api-signup');

$api->add('/login', array(
    'action' => 'login'
))->setName('api-login');

$api->add('/logout', array(
    'action' => 'logout'
))->setName('api-logout');

$api->add('/auth/check', array(
    'action' => 'checkAuth'
))->setName('api-check-auth');

$router->mount($api);
