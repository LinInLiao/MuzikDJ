<?php

namespace Muzikdj\Models;

final class RoomPlaylist extends \Phalcon\Mvc\Model {

	public $room_id;
	public $playlist_id;
	public $sort;
	public $created;

	public function initialize() {
		$this->setSource('room_playlist');
        $this->useDynamicUpdate(true);

        $this->belongsTo('playlist_id', 'Muzikdj\Models\Playlist', 'id');
        $this->belongsTo('room_id', 'Muzikdj\Models\Rooms', 'id');
    }

    public static function columnMap() {
        return array(
            'room_id' => 'room_id',
            'playlist_id' => 'playlist_id',
            'sort' => 'sort',
            'created' => 'created',
        );
    }
}