<?php

namespace Muzikdj\Models;

use Muzikdj\Models\RoomPlaylist;

final class Rooms extends \Phalcon\Mvc\Model {

	public $id;
	public $name;
	public $alias;
	public $password;
	public $created;
	public $status;

	public function initialize() {
		$this->setSource('rooms');
        $this->useDynamicUpdate(true);

		$this->hasManyToMany('id', 'Muzikdj\Models\UserRoom', 'room_id', 'user_id', 'Muzikdj\Models\Users', 'id', array( 'alias' => 'UsersRooms' ));
		$this->hasManyToMany('id', 'Muzikdj\Models\RoomPlaylist', 'room_id', 'playlist_id', 'Muzikdj\Models\Playlist', 'id', array( 'alias' => 'RoomsPlaylist' ));
	}

	public static function columnMap() {
        return array(
            'id' => 'id',
            'name' => 'name',
            'alias' => 'alias',
            'password' => 'password',
            'created' => 'created',
            'status' => 'status',
        );
    }
}