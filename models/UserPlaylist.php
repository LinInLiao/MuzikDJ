<?php

namespace Muzikdj\Models;

final class UserPlaylist extends \Phalcon\Mvc\Model {

	public $user_id;
	public $playlist_id;
	public $sort;
	public $created;

	public function initialize() {
		$this->setSource('user_playlist');
        $this->useDynamicUpdate(true);

        $this->belongsTo('playlist_id', 'Muzikdj\Models\Playlist', 'id');
        $this->belongsTo('user_id', 'Muzikdj\Models\Users', 'id');
    }

    public static function columnMap() {
        return array(
            'user_id' => 'user_id',
            'playlist_id' => 'playlist_id',
            'sort' => 'sort',
            'created' => 'created',
        );
    }
}