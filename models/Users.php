<?php

namespace Muzikdj\Models;

final class Users extends \Phalcon\Mvc\Model {

	public $id;
	public $name;
	public $email;
	public $password;
	public $created;
	public $modified;
	public $status;
	public $token;

	public function initialize() {
		$this->setSource('users');
        $this->useDynamicUpdate(true);

		$this->hasMany('id', 'Muzikdj\Models\UserRoom', 'user_id', array( 'alias' => 'UsersRooms'));
		$this->hasMany('id', 'Muzikdj\Models\Songs', 'user_id', array( 'alias' => 'UsersSongs'));
		$this->hasManyToMany('id', 'Muzikdj\Models\UserPlaylist', 'user_id', 'playlist_id', 'Muzikdj\Models\Playlist', 'id', array( 'alias' => 'UsersPlaylist'));
	}

	public static function columnMap() {
        return array(
            'id' => 'id',
            'name' => 'name',
            'email' => 'email',
            'password' => 'password',
            'created' => 'created',
            'modified' => 'modified',
            'status' => 'status',
            'token' => 'token',
        );
    }
}