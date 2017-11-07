<?php

return [
	'database' => [
		'development' => [
			'dbhost' => '127.0.0.1',
			'dbusername' => 'root',
			'dbpassword' => 'caline0125',
			'dbname' => 'muzikdj'
		],
		'staging' => [
			'dbhost' => '127.0.0.1',
			'dbusername' => 'root',
			'dbpassword' => 'caline0125',
			'dbname' => 'muzikdj'
		],
		'production' => [
			'dbhost' => '127.0.0.1',
			'dbusername' => 'root',
			'dbpassword' => 'Caline01@%',
			'dbname' => 'muzikdj'
		]
	],
	'whitelist' => [
		'name' => '#(api|create|delete|save|hinablue|hinachen|hana|hina|ajax|post|get|remove|add)#i'
	],
	'allow_origin' => [
		'development' => ['*'],
		'staging' => [],
		'production' => []
	],
	'logger' => [
		'folder' => 'logs',
		'level' => \Phalcon\Logger::ERROR
	],
	'metadata' => [
		'adapter' => 'Apc',
		'suffix' => 'muzikdj',
		'lifetime' => 86400
	],
	'memcached' => [
		'host' => 'localhost',
		'port' => 11211,
		'prefix' => 'muzikdj',
		'lifetime' => 8600,
		'persistent' => 'MuzikDJ'
	],
	'cookie' => [
		'crypt' => '9(*&^%$muzikdjERfgoboasdf19237865rvbnmkjhfde5678k',
		'secure' => false,
		'httponly' => false,
		'lifetime' => 86400
	]
];