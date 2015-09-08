<?php

namespace Muzikdj\Models;

final class Playlist extends \Phalcon\Mvc\Model {

	public $id;
	public $name;
	public $created;
	public $status;

	public function initialize() {
		$this->setSource('playlist');
        $this->useDynamicUpdate(true);

		$this->hasManyToMany('id', 'Muzikdj\Models\SongPlaylist', 'playlist_id', 'song_id', 'Muzikdj\Models\Songs', 'id', array( 'alias' => 'PlaylistSongs' ));
	}

	public static function columnMap() {
        return array(
            'id' => 'id',
            'name' => 'name',
            'created' => 'created',
            'status' => 'status',
        );
    }
}