<?php

namespace Muzikdj\Models;

final class UserRoom extends \Phalcon\Mvc\Model {

	public $user_id;
	public $room_id;
	public $sort;
	public $created;

	public function initialize() {
		$this->setSource('user_room');
        $this->useDynamicUpdate(true);

        $this->belongsTo('room_id', 'Muzikdj\Models\Rooms', 'id');
        $this->belongsTo('user_id', 'Muzikdj\Models\Users', 'id');
    }

    public static function columnMap() {
        return array(
            'user_id' => 'user_id',
            'room_id' => 'room_id',
            'sort' => 'sort',
            'created' => 'created',
        );
    }
}