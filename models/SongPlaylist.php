<?php

namespace Muzikdj\Models;

final class SongPlaylist extends \Phalcon\Mvc\Model {

	public $song_id;
	public $playlist_id;
	public $sort;
	public $created;

	public function initialize() {
		$this->setSource('song_playlist');
        $this->useDynamicUpdate(true);

        $this->belongsTo('playlist_id', 'Muzikdj\Models\Playlist', 'id');
        $this->belongsTo('song_id', 'Muzikdj\Models\Songs', 'id');
    }

    public static function columnMap() {
        return array(
            'song_id' => 'song_id',
            'playlist_id' => 'playlist_id',
            'sort' => 'sort',
            'created' => 'created',
        );
    }
}