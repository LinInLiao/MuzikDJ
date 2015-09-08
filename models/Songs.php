<?php

namespace Muzikdj\Models;

final class Songs extends \Phalcon\Mvc\Model {

	public $id;
	public $user_id;
	public $type;
	public $url;
	public $name;
	public $cover;
	public $sort;
	public $created;
	public $status;

	public function initialize() {
		$this->setSource('songs');
        $this->useDynamicUpdate(true);

		$this->hasMany('id', 'Muzikdj\Models\SongPlaylist', 'song_id', array( 'alias' => 'SongsPlaylist' ));
	}

	public static function columnMap() {
        return array(
            'id' => 'id',
            'user_id' => 'user_id',
            'type' => 'type',
            'url' => 'url',
            'name' => 'name',
            'cover' => 'cover',
            'sort' => 'sort',
            'created' => 'created',
            'status' => 'status',
        );
    }
}