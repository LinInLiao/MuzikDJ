<?php

namespace Muzikdj\Muzikdj\Controllers;

use Muzikdj\Plugins\UUID,
    Muzikdj\Plugins\Slug,
    Muzikdj\Models\Playlist,
    Muzikdj\Models\RoomPlaylist,
    Muzikdj\Models\Rooms,
    Muzikdj\Models\SongPlaylist,
    Muzikdj\Models\Songs,
    Muzikdj\Models\UserPlaylist,
    Muzikdj\Models\UserRoom,
    Muzikdj\Models\Users;

final class ApiController extends \Phalcon\Mvc\Controller {

    /**
     * Application initialize.
     * @return null
     */
	public function initialize() {
        if ($this->request->isAjax() === false && $this->request->isPost() === false) {
            $this->responseJson(array(
                'status' => 'error',
                'message' => 'Method not allowed.',
            ), 405);
        }

        if (count($_POST) === 0) {
            $ajax = file_get_contents("php://input");
            $_POST = json_decode($ajax, true);
            unset($ajax);
        }
	}

    public function removeRoomAction() {
        $user = $this->session->has('USER') ? $this->session->get('USER') : null;

        if (is_null($user)) {
            $this->responseJson(array(
                'status' => 'error',
                'message' => 'Need login.',
            ), 403);
        }

        $data = $this->request->getPost();

        $user_room = UserRoom::findFirst(array(
            'conditions' => 'user_id = :user_id: AND room_id = :room_id:',
            'bind' => array(
                'user_id' => $user->id,
                'room_id' => $data['room_id'],
            ),
            'bindTypes' => array(
                'user_id' => \Phalcon\Db\Column::BIND_PARAM_STR,
                'room_id' => \Phalcon\Db\Column::BIND_PARAM_STR,
            ),
        ));

        if ($user_room) {
            $transaction = $this->transactionManager->get();
            try {
                if (false === $user_room->delete()) {
                    $transaction->rollback();
                }

                $room_playlist = RoomPlaylist::find(array(
                    'conditions' => 'room_id = :room_id:',
                    'bind' => array(
                        'room_id' => $data['room_id'],
                    ),
                    'bindTypes' => array(
                        'room_id' => \Phalcon\Db\Column::BIND_PARAM_STR,
                    ),
                ));

                $playlist = array();
                if ($room_playlist && $room_playlist->count() > 0) {
                    foreach($room_playlist as $room) {
                        array_push($playlist, $room->playlist_id);
                        if (false === $room->delete()) {
                            $transaction->rollback();
                        }
                    }
                }
                $room = Rooms::findFirst(array(
                    'conditions' => 'id = :room_id:',
                    'bind' => array(
                        'room_id' => $data['room_id'],
                    ),
                    'bindTypes' => array(
                        'room_id' => \Phalcon\Db\Column::BIND_PARAM_STR,
                    ),
                ));
                if (false === $room->delete()) {
                    $transaction->rollback();
                }

                if (count($playlist) > 0) {
                    $playlist = array_unique($playlist);
                    foreach($playlist as $playlist_id) {
                        $song_playlist = SongPlaylist::find(array(
                            'conditions' => 'playlist_id = :playlist_id:',
                            'bind' => array(
                                'playlist_id' => $playlist_id,
                            ),
                            'bindTypes' => array(
                                'playlist_id' => \Phalcon\Db\Column::BIND_PARAM_STR,
                            ),
                        ));

                        $songs = array();
                        if ($song_playlist && $song_playlist->count() > 0) {
                            foreach($song_playlist as $song) {
                                array_push($songs, $song->song_id);
                                if (false === $song->delete()) {
                                    $transaction->rollback();
                                }
                            }
                        }

                        $__playlist = Playlist::findFirst(array(
                            'conditions' => 'id = :playlist_id:',
                            'bind' => array(
                                'playlist_id' => $playlist_id,
                            ),
                            'bindTypes' => array(
                                'playlist_id' => \Phalcon\Db\Column::BIND_PARAM_STR,
                            ),
                        ));

                        if ($__playlist && false === $__playlist->delete()) {
                            $transaction->rollback();
                        }

                        if (count($songs) > 0) {
                            foreach($songs as $song_id) {
                                $song = Songs::findFirst(array(
                                    'conditions' => 'id = :song_id:',
                                    'bind' => array(
                                        'song_id' => $song_id,
                                    ),
                                    'bindTypes' => array(
                                        'song_id' => \Phalcon\Db\Column::BIND_PARAM_STR,
                                    ),
                                ));

                                if ($song && false === $song->delete()) {
                                    $transaction->rollback();
                                }
                            }
                        }
                    }
                }

                $transaction->commit();
                $this->responseJson(array(
                    'status' => 'ok',
                    'message' => 'Success.',
                ));
            } catch(\Phalcon\Mvc\Model\Transaction\Failed $e) {
                $this->responseJson(array(
                    'status' => 'error',
                    'message' => 'Data save failed.',
                ), 405);
            } catch(\PDOException $e) {
                $this->responseJson(array(
                    'status' => 'error',
                    'message' => 'Data save failed.',
                ), 405);
            }
        } else {
            $this->responseJson(array(
                'status' => 'error',
                'message' => 'Room not found.',
            ), 404);
        }
    }

    public function accountRoomsAction() {
        $user = $this->session->has('USER') ? $this->session->get('USER') : null;

        if (is_null($user)) {
            $this->responseJson(array(
                'status' => 'error',
                'message' => 'Need login.',
            ), 403);
        }
        $user_rooms = UserRoom::query()
            ->columns(array('r.id, r.name, r.alias, r.status, Muzikdj\Models\UserRoom.user_id AS owner'))
            ->innerJoin('Muzikdj\Models\Rooms', 'r.id = Muzikdj\Models\UserRoom.room_id', 'r')
            ->where('Muzikdj\Models\UserRoom.user_id = :user_id:', array('user_id' => $user->id), array('user_id' => \Phalcon\Db\Column::BIND_PARAM_STR))
            ->execute();

        $user_rooms = ($user_rooms) ? $user_rooms->toArray() : array();

        if (count($user_rooms) > 0) {
            array_walk($user_rooms, function(&$room) {
                $room['cover'] = false;
                $song = Songs::query()
                    ->columns(array('cover'))
                    ->innerJoin('Muzikdj\Models\RoomPlaylist', 'rp.room_id = \''.$room['id'].'\'', 'rp')
                    ->innerJoin('Muzikdj\Models\Playlist', 'p.id = rp.playlist_id', 'p')
                    ->innerJoin('Muzikdj\Models\SongPlaylist', 'sp.playlist_id = p.id', 'sp')
                    ->innerJoin('Muzikdj\Models\Songs', 's.id = sp.song_id', 's')
                    ->limit(1)
                    ->execute();

                if ($song && $song->count() > 0) {
                    $room['cover'] = $song->toArray()[0]['cover'];
                }
            });

            $this->responseJson(array(
                'status' => 'ok',
                'message' => 'Success.',
                'result' => $user_rooms,
            ));
        } else {
            $this->responseJson(array(
                'status' => 'err',
                'message' => 'Not found.',
            ), 404);
        }
    }

    public function accountEditAction() {
        $data = $this->request->getPost();

        $user = $this->session->has('USER') ? $this->session->get('USER') : null;

        if (is_null($user)) {
            $this->responseJson(array(
                'status' => 'error',
                'message' => 'Need login.',
            ), 403);
        }

        $user = Users::findFirst(array(
            'conditions' => 'id = :user_id:',
            'bind' => array(
                'user_id' => $user->id,
            ),
            'bindTypes' => array(
                'user_id' => \Phalcon\Db\Column::BIND_PARAM_STR,
            ),
        ));

        if ($user) {
            $transaction = $this->transactionManager->get();

            try {
                $username = isset($data['name']) ? trim($data['name']) : null;
                if (!empty($username)) {
                    $user->name = $username;
                }
                if (isset($data['password']) && !empty($data['password'])) {
                    $user->password = $this->security->hash($data['password']);
                }

                if (false === $user->save()) {
                    $transaction->rollback();
                }

                $transaction->commit();
                $this->responseJson(array(
                    'status' => 'ok',
                    'message' => 'Success.',
                ));
            } catch(\Phalcon\Mvc\Model\Transaction\Failed $e) {
                $this->responseJson(array(
                    'status' => 'error',
                    'message' => 'Data save failed.',
                ), 405);
            } catch(\PDOException $e) {
                $this->responseJson(array(
                    'status' => 'error',
                    'message' => 'Data save failed.',
                ), 405);
            }
        } else {
            $this->responseJson(array(
                'status' => 'error',
                'message' => 'User not found.',
            ), 404);
        }
    }

    public function removeSongAction() {
        $data = $this->request->getPost();

        $user = $this->session->has('USER') ? $this->session->get('USER') : null;

        if (is_null($user)) {
            $this->responseJson(array(
                'status' => 'error',
                'message' => 'Need login.',
            ), 403);
        }

        $song_playlist = SongPlaylist::query()
                ->columns(array(
                    'Muzikdj\Models\SongPlaylist.song_id',
                    'Muzikdj\Models\SongPlaylist.playlist_id',
                    's.user_id AS song_user_id',
                    'ur.user_id AS room_user_id',
                ))
                ->innerJoin('Muzikdj\Models\RoomPlaylist', 'rp.room_id = \''.$data['room'].'\'', 'rp')
                ->innerJoin('Muzikdj\Models\UserRoom', 'ur.room_id = rp.room_id', 'ur')
                ->innerJoin('Muzikdj\Models\Playlist', 'p.id = rp.playlist_id', 'p')
                ->innerJoin('Muzikdj\Models\Songs', 's.id = Muzikdj\Models\SongPlaylist.song_id', 's')
                ->where('Muzikdj\Models\SongPlaylist.song_id = :song_id:', array('song_id' => $data['id']), array('song_id' => \Phalcon\Db\Column::BIND_PARAM_STR))
                ->limit(1)
                ->execute();
        $song_playlist = $song_playlist->toArray();
        if (count($song_playlist) > 0) {
            $song_playlist = $song_playlist[0];

            if (false === in_array($user->id, array($song_playlist['song_user_id'], $song_playlist['room_user_id']), true)) {
                $this->responseJson(array(
                    'status' => 'error',
                    'message' => 'You can not remove this song.',
                ), 405);
            }

            $transaction = $this->transactionManager->get();

            try {
                $remove = SongPlaylist::findFirst(array(
                    'conditions' => 'song_id = :song_id: AND playlist_id = :playlist_id:',
                    'bind' => array(
                        'song_id' => $song_playlist['song_id'],
                        'playlist_id' => $song_playlist['playlist_id'],
                    ),
                    'bindTypes' => array(
                        'song_id' => \Phalcon\Db\Column::BIND_PARAM_STR,
                        'playlist_id' => \Phalcon\Db\Column::BIND_PARAM_STR,
                    ),
                ));

                if (false === $remove->delete()) {
                    $transaction->rollback();
                }
                $transaction->commit();
                $this->responseJson(array(
                    'status' => 'ok',
                    'message' => 'Success.',
                ));
            } catch(\Phalcon\Mvc\Model\Transaction\Failed $e) {
                $this->responseJson(array(
                    'status' => 'error',
                    'message' => 'Data save failed.',
                ), 405);
            } catch(\PDOException $e) {
                $this->responseJson(array(
                    'status' => 'error',
                    'message' => 'Data save failed.',
                ), 405);
            }
        } else {
            $this->responseJson(array(
                'status' => 'error',
                'message' => 'Song or Room not found.',
            ), 404);
        }
    }

    public function createSongAction() {
        $data = $this->request->getPost();

        $url = trim($data['url']);

        $room = Rooms::findFirst(array(
            'conditions' => 'alias = :alias:',
            'bind' => array(
                'alias' => $data['room'],
            ),
            'bindTypes' => array(
                'alias' => \Phalcon\Db\Column::BIND_PARAM_STR,
            ),
        ));

        if ($room) {
            if (true === $this->session->has('USER')) {
                $user = $this->session->get('USER');
            } else {
                $user = Users::findFirst(array(
                    'conditions' => 'email = :email:',
                    'bind' => array(
                        'email' => 'muzikdj@no-email.com',
                    ),
                    'bindTypes' => array(
                        'email' => \Phalcon\Db\Column::BIND_PARAM_STR,
                    ),
                ));
            }

            parse_str(parse_url($url, PHP_URL_QUERY), $queries);
            if (isset($queries['v'])) {
                $youtube_data = file_get_contents('https://www.googleapis.com/youtube/v3/videos?id='.$queries['v'].'&part=snippet&key='.YOUTUBE_API_KEY);
                $youtube_data = json_decode($youtube_data, true);
                if (isset($youtube_data['items'][0]['snippet']['thumbnails'])) {
                    $cover = $youtube_data['items'][0]['snippet']['thumbnails'];
                    if (isset($cover['high'])) {
                        $cover = $cover['high']['url'];
                    } elseif (isset($cover['default'])) {
                        $cover = $cover['default']['url'];
                    } elseif (isset($cover['standard'])) {
                        $cover = $cover['standard']['url'];
                    } else {
                        $cover = 'https://i.ytimg.com/vi/79uuj5hXsOg/sddefault.jpg';
                    }
                }
                $youtube_data = array(
                    'title' => $youtube_data['items'][0]['snippet']['title'],
                    'cover' => $cover,
                );
            } else {
                $youtube_data = null;
            }

            $room_playlist = RoomPlaylist::findFirst(array(
                'conditions' => 'room_id = :room_id:',
                'bind' => array(
                    'room_id' => $room->id,
                ),
                'bindTypes' => array(
                    'room_id' => \Phalcon\Db\Column::BIND_PARAM_STR,
                ),
            ));

            $transaction = $this->transactionManager->get();

            try {
                $sort = (int) Songs::count(array(
                    'condition' => array('user_id = :user_id:'),
                    'bind' => array(
                        'user_id' => $user->id,
                    ),
                    'bindTypes' => array(
                        'user_id' => \Phalcon\Db\Column::BIND_PARAM_STR,
                    ),
                ));
                $song = new Songs();
                $song->id = UUID::v4();
                $song->user_id = $user->id;
                $song->type = 'youtube';
                $song->url = $url;
                $song->name = is_null($youtube_data) ? NULL : $youtube_data['title'];
                $song->cover = is_null($youtube_data) ? NULL : $youtube_data['cover'];
                $song->sort = $sort + 1;
                $song->created = date('Y-m-d H:i:s');
                $song->status = 'active';

                if (false === $song->create()) {
                    $transaction->rollback();
                }

                if (false === $room_playlist) {
                    $playlist = new Playlist();
                    $playlist->id = UUID::v4();
                    $playlist->name = $room->name;
                    $playlist->created = date('Y-m-d H:i:s');
                    $playlist->status = 'active';

                    if (false === $playlist->create()) {
                        $transaction->rollback();
                    }

                    $playlist_id = $playlist->id;

                    $sort = (int) RoomPlaylist::count(array(
                        'condition' => array('room_id = :room_id:'),
                        'bind' => array(
                            'room_id' => $room->id,
                        ),
                        'bindTypes' => array(
                            'room_id' => \Phalcon\Db\Column::BIND_PARAM_STR,
                        ),
                    ));
                    $room_playlist = new RoomPlaylist();
                    $room_playlist->room_id = $room->id;
                    $room_playlist->playlist_id = $playlist_id;
                    $room_playlist->sort = $sort + 1;
                    $room_playlist->created = date('Y-m-d H:i:s');

                    if (false === $room_playlist->create()) {
                        $transaction->rollback();
                    }
                } else {
                    $playlist_id = $room_playlist->playlist_id;
                }

                $sort = (int) SongPlaylist::count(array(
                    'condition' => array('playlist_id = :playlist_id:'),
                    'bind' => array(
                        'playlist_id' => $playlist_id,
                    ),
                    'bindTypes' => array(
                        'playlist_id' => \Phalcon\Db\Column::BIND_PARAM_STR,
                    ),
                ));
                $song_playlist = new SongPlaylist();
                $song_playlist->song_id = $song->id;
                $song_playlist->playlist_id = $playlist_id;
                $song_playlist->sort = $sort + 1;
                $song_playlist->created = date('Y-m-d H:i:s');

                if (false === $song_playlist->create()) {
                    $transaction->rollback();
                }

                $transaction->commit();

                $this->responseJson(array(
                    'status' => 'ok',
                    'message' => 'Success.',
                    'result' => (object) array(
                        'id' => $song->id,
                        'dj' => $user->name,
                        'cover' => $song->cover,
                        'name' => $song->name,
                        'sort' => $song->sort,
                        'type' => $song->type,
                        'url' => $song->url,
                    ),
                ));
            } catch(\Phalcon\Mvc\Model\Transaction\Failed $e) {
                $this->responseJson(array(
                    'status' => 'error',
                    'message' => 'Data save failed.',
                ), 405);
            } catch(\PDOException $e) {
                $this->responseJson(array(
                    'status' => 'error',
                    'message' => 'Data save failed.',
                ), 405);
            }
        } else {
            $this->responseJson(array(
                'status' => 'error',
                'message' => 'Room not found.',
            ), 404);
        }
    }

    public function getListenRoomsAction() {
        $rooms = Rooms::find(array(
            'columns' => array('id', 'alias', 'name', 'status'),
        ));

        if ($rooms && $rooms->count() > 0) {
            $rooms = $rooms->toArray();
            array_walk($rooms, function(&$room) {
                $room['cover'] = false;

                $song = Songs::query()
                    ->columns(array('cover'))
                    ->innerJoin('Muzikdj\Models\RoomPlaylist', 'rp.room_id = \''.$room['id'].'\'', 'rp')
                    ->innerJoin('Muzikdj\Models\Playlist', 'p.id = rp.playlist_id', 'p')
                    ->innerJoin('Muzikdj\Models\SongPlaylist', 'sp.playlist_id = p.id', 'sp')
                    ->innerJoin('Muzikdj\Models\Songs', 's.id = sp.song_id', 's')
                    ->limit(1)
                    ->execute();

                if ($song && $song->count() > 0) {
                    $room['cover'] = $song->toArray()[0]['cover'];
                }
            });

            $this->responseJson(array(
                'status' => 'ok',
                'message' => 'Success.',
                'result' => $rooms,
            ));
        } else {
            $this->responseJson(array(
                'status' => 'err',
                'message' => 'Not found.',
            ), 404);
        }
    }

    public function getPlaylistAction() {
        $songs = Songs::query()
            ->columns(array(
                'Muzikdj\Models\Songs.id',
                'Muzikdj\Models\Songs.type',
                'Muzikdj\Models\Songs.url',
                'Muzikdj\Models\Songs.name',
                'Muzikdj\Models\Songs.cover',
                'Muzikdj\Models\Songs.sort',
                'u.name AS dj',
            ))
            ->innerJoin('Muzikdj\Models\Playlist', 'p.name = \'anonymous\'', 'p')
            ->innerJoin('Muzikdj\Models\SongPlaylist', 'sp.playlist_id = p.id', 'sp')
            ->innerJoin('Muzikdj\Models\Users', 'u.id = Muzikdj\Models\Songs.user_id', 'u')
            ->where('Muzikdj\Models\Songs.id = sp.song_id')
            ->order('Muzikdj\Models\Songs.sort DESC')
            ->execute();

        if ($songs && $songs->count() > 0) {
            $songs = $songs->toArray();
        } else {
            $songs = array();
        }
        $this->responseJson(array(
            'status' => 'ok',
            'message' => 'Success.',
            'result' => $songs,
        ));
    }

    public function singleRoomAction() {
        $room_alias = $this->getParams('alias');

        $room = Rooms::findFirst(array(
            'conditions' => 'alias = :alias:',
            'bind' => array(
                'alias' => $room_alias,
            ),
            'bindTypes' => array(
                'alias' => \Phalcon\Db\Column::BIND_PARAM_STR,
            ),
        ));

        if ($room) {
            $user = $this->session->has('USER') ? $this->session->get('USER') : null;

            if (!is_null($user)) {
                $user_room = UserRoom::findFirst(array(
                    'conditions' => 'user_id = :user_id: AND room_id = :room_id:',
                    'bind' => array(
                        'user_id' => $user->id,
                        'room_id' => $room->id,
                    ),
                    'bindTypes' => array(
                        'user_id' => \Phalcon\Db\Column::BIND_PARAM_STR,
                        'room_id' => \Phalcon\Db\Column::BIND_PARAM_STR,
                    ),
                ));
            } else {
                $user_room = false;
            }
            if ($room->status === 'private') {
                if (false === $user_room) {
                    if (false === $this->session->has($room->id)) {
                        $this->responseJson(array(
                            'status' => 'err',
                            'message' => 'This room need password.',
                        ), 403);
                    }
                }
            }

            $songs = Songs::query()
                ->columns(array(
                    'Muzikdj\Models\Songs.id',
                    'Muzikdj\Models\Songs.type',
                    'Muzikdj\Models\Songs.url',
                    'Muzikdj\Models\Songs.name',
                    'Muzikdj\Models\Songs.cover',
                    'Muzikdj\Models\Songs.sort',
                    'u.name AS dj',
                    'u.id AS user_id',
                    'ur.user_id AS room_user_id',
                ))
                ->innerJoin('Muzikdj\Models\RoomPlaylist', 'rp.room_id = \''.$room->id.'\'', 'rp')
                ->innerJoin('Muzikdj\Models\Playlist', 'p.id = rp.playlist_id', 'p')
                ->innerJoin('Muzikdj\Models\SongPlaylist', 'sp.playlist_id = p.id', 'sp')
                ->innerJoin('Muzikdj\Models\Users', 'u.id = Muzikdj\Models\Songs.user_id', 'u')
                ->leftJoin('Muzikdj\Models\UserRoom', 'ur.room_id = rp.room_id', 'ur')
                ->where('Muzikdj\Models\Songs.id = sp.song_id')
                ->order('Muzikdj\Models\Songs.sort DESC')
                ->execute();

            if ($songs && $songs->count() > 0) {
                $songs = $songs->toArray();
            } else {
                $songs = array();
            }
            $this->responseJson(array(
                'status' => 'ok',
                'message' => 'Success.',
                'result' => $songs,
                'room' => array(
                    'id' => $room->id,
                    'name' => $room->name,
                    'alias' => $room->alias,
                )
            ));
        } else {
            $this->responseJson(array(
                'status' => 'err',
                'message' => 'This room does not exists.',
            ), 404);
        }
    }

    public function checkRoomAction() {
        $data = $this->request->getPost();

        $room = Rooms::findFirst(array(
            'conditions' => 'alias = :alias:',
            'bind' => array(
                'alias' => $data['alias'],
            ),
            'bindTypes' => array(
                'alias' => \Phalcon\Db\Column::BIND_PARAM_STR,
            ),
        ));

        if ($room) {
            if ($room->status === 'private') {
                $user = $this->session->has('USER') ? $this->session->get('USER') : null;

                if (!is_null($user)) {
                    $user_room = UserRoom::findFirst(array(
                        'condotions' => 'user_id = :user_id: AND room_id = :room_id:',
                        'bind' => array(
                            'user_id' => $user->id,
                            'room_id' => $room->id,
                        ),
                        'bindTypes' => array(
                            'user_id' => \Phalcon\Db\Column::BIND_PARAM_STR,
                            'room_id' => \Phalcon\Db\Column::BIND_PARAM_STR,
                        ),
                    ));
                } else {
                    $user_room = false;
                }

                if (false === $user_room) {
                    if (false === $this->session->has($room->id)) {
                        $this->responseJson(array(
                            'status' => 'error',
                            'message' => 'Private room need password.',
                        ), 403);
                    }
                }
            }
            $this->responseJson(array(
                'status' => 'ok',
                'message' => 'Success.',
                'result' => $room->toArray(),
            ));
        } else {
            $this->responseJson(array(
                'status' => 'error',
                'message' => 'Room does not exists.',
            ), 404);
        }
    }

    public function privateRoomAction() {
        $data = $this->request->getPost();

        $room = Rooms::findFirst(array(
            'conditions' => 'alias = :alias:',
            'bind' => array(
                'alias' => $data['alias'],
            ),
            'bindTypes' => array(
                'alias' => \Phalcon\Db\Column::BIND_PARAM_STR,
            ),
        ));

        if ($room && true === $this->security->checkHash($data['password'], $room->password)) {
            $this->session->set($room->id, $data['password']);
            $this->responseJson(array(
                'status' => 'ok',
                'message' => 'Success.',
            ), 200);
        } else {
            $this->responseJson(array(
                'status' => 'err',
                'message' => 'Room password invalid or room does not exists.',
            ), 403);
        }
    }

    public function searchAction() {
        $data = $this->request->getPost();

        if (empty($data['keyword'])) {
            $this->responseJson(array(
                'status' => 'err',
                'message' => 'Room name is required.',
            ), 404);
        }

        $rooms = Rooms::find(array(
            'columns' => array('id', 'alias', 'name', 'status'),
            'conditions' => 'name LIKE :name:',
            'bind' => array(
                'name' => $data['keyword'].'%',
            ),
            'bindTypes' => array(
                'name' => \Phalcon\Db\Column::BIND_PARAM_STR,
            ),
        ));

        if ($rooms && $rooms->count() > 0) {
            $rooms = $rooms->toArray();
            array_walk($rooms, function(&$room) {
                $room['cover'] = false;

                $song = Songs::query()
                    ->columns(array('cover'))
                    ->innerJoin('Muzikdj\Models\RoomPlaylist', 'rp.room_id = \''.$room['id'].'\'', 'rp')
                    ->innerJoin('Muzikdj\Models\Playlist', 'p.id = rp.playlist_id', 'p')
                    ->innerJoin('Muzikdj\Models\SongPlaylist', 'sp.playlist_id = p.id', 'sp')
                    ->innerJoin('Muzikdj\Models\Songs', 's.id = sp.song_id', 's')
                    ->limit(1)
                    ->execute();

                if ($song && $song->count() > 0) {
                    $room['cover'] = $song->toArray()[0]['cover'];
                }
            });

            $this->responseJson(array(
                'status' => 'ok',
                'message' => 'Success.',
                'result' => $rooms,
            ));
        } else {
            $this->responseJson(array(
                'status' => 'err',
                'message' => 'Not found.',
            ), 404);
        }
    }

    /**
     * Create new room.
     * @return null
     */
    public function createRoomAction() {
        if (false === $this->session->has('USER')) {
            $this->responseJson(array(
                'status' => 'error',
                'message' => 'Need login.',
            ));
        }

        $user = $this->session->get('USER');
        $data = $this->request->getPost();

        if (empty($data)) {
            $this->responseJson(array(
                'status' => 'error',
                'message' => 'Data not found.',
            ), 404);
        }

        $room_name = isset($data['roomId']) ? trim($data['roomId']) : null;
        $room_type = isset($data['roomType']) && in_array($data['roomType'], array('private',  'public'), true) ? $data['roomType'] : 'public';
        $room_password = isset($data['roomPassword']) ? trim($data['roomPassword']) : null;

        if (empty($room_name)) {
            $this->responseJson(array(
                'status' => 'error',
                'message' => 'Room ID is required.',
            ), 405);
        }
        $room_alias = Slug::get($room_name);
        if (strlen($room_alias) > 200 || true === preg_match(NAME_WHITE_LIST, $room_name) || str_replace('-', '', $room_alias) === '') {
            $this->responseJson(array(
                'status' => 'error',
                'message' => 'Room ID is invalid.',
            ), 405);
        }

        if ($room_type === 'private') {
            if (mb_strlen($room_password) < 6) {
                $this->responseJson(array(
                    'status' => 'error',
                    'message' => 'Password is invalid or empty.',
                ), 404);
            }
        } else {
            $room_password = NULL;
        }

        $transaction = $this->transactionManager->get();

        try {
            $room = new Rooms();
            $room->id = UUID::v4();
            $room->name = $room_name;
            $room->alias = Slug::get($room_name);
            $room->password = $this->security->hash($room_password);
            $room->created = date('Y-m-d H:i:s');
            $room->status = $room_type;

            if (false === $room->save()) {
                $transaction->rollback();
            }

            $user_room = new UserRoom();
            $user_room->user_id = $user->id;
            $user_room->room_id = $room->id;
            $user_room->sort = UserRoom::count(array(
                'condition' => array('user_id = :user_id:'),
                'bind' => array(
                    'user_id' => $user->id,
                ),
                'bindTypes' => array(
                    'user_id' => \Phalcon\Db\Column::BIND_PARAM_STR,
                ),
            ));
            $user_room->created = date('Y-m-d H:i:s');

            if (false === $user_room->save()) {
                $transaction->rollback();
            }

            $transaction->commit();

            $this->responseJson(array(
                'status' => 'ok',
                'message' => 'Success.',
                'result' => $room->alias,
            ));
        } catch(\Phalcon\Mvc\Model\Transaction\Failed $e) {
            $this->responseJson(array(
                'status' => 'error',
                'message' => 'Data save failed.',
            ), 405);
        } catch(\PDOException $e) {
            $this->responseJson(array(
                'status' => 'error',
                'message' => 'Data save failed.',
            ), 405);
        }
    }

    /**
     * Logout user.
     * @return null
     */
    public function logoutAction() {
        $this->session->remove('USER');
        $this->responseJson(array(
            'status' => 'ok',
            'message' => 'Success.',
        ));
    }

    /**
     * Check the user login status.
     * @return null
     */
    public function checkAuthAction() {
        if (true === $this->session->has('USER')) {
            $this->responseJson(array(
                'status' => 'ok',
                'message' => 'Success.',
                'result' => $this->session->get('USER'),
            ));
        } else {
            $this->responseJson(array(
                'status' => 'error',
                'message' => 'Failed.',
            ));
        }
    }

    /**
     * Login user.
     * @return null
     */
    public function loginAction() {
        $data = $this->request->getPost();

        if (empty($data)) {
            $this->responseJson(array(
                'status' => 'error',
                'message' => 'Data not found.',
            ), 404);
        }

        if (!isset($data['email']) || false === filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->responseJson(array(
                'status' => 'error',
                'message' => 'E-mail is invalid or empty.',
            ), 404);
        }
        if (!isset($data['password']) || mb_strlen($data['password']) < 6) {
            $this->responseJson(array(
                'status' => 'error',
                'message' => 'Password is invalid or empty.',
            ), 404);
        }

        $user = Users::findFirst(array(
            'conditions' => 'email = :email:',
            'bind' => array(
                'email' => $data['email'],
            ),
            'bindTypes' => array(
                'email' => \Phalcon\Db\Column::BIND_PARAM_STR,
            ),
        ));

        if (false === $user || false === $this->security->checkHash($data['password'], $user->password)) {
            $this->responseJson(array(
                'status' => 'error',
                'message' => 'Password is incurrect or e-mail does not exists.',
            ), 405);
        }

        $transaction = $this->transactionManager->get();

        try {
            $user->modified = date('Y-m-d H:i:s');
            if (false === $user->update()) {
                $transaction->rollback();
            }
            $transaction->commit();
        } catch(\Phalcon\Mvc\Model\Transaction\Failed $e) {
        } catch(\PDOException $e) {
        }

        $user = (object) $user->toArray();
        $user = array(
            'id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'token' => $user->token,
        );
        $this->session->set('USER', (object) $user);

        $this->responseJson(array(
            'status' => 'ok',
            'message' => 'Success.',
            'result' => $user,
        ));
    }

    /**
     * New user signup.
     * @return null
     */
    public function signupAction() {
        $data = $this->request->getPost();

        if (empty($data)) {
            $this->responseJson(array(
                'status' => 'error',
                'message' => 'Data not found.',
            ), 404);
        }

        if (!isset($data['username'])) {
            $this->responseJson(array(
                'status' => 'error',
                'message' => 'Username is required.',
            ), 404);
        }
        if (!isset($data['email']) || false === filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->responseJson(array(
                'status' => 'error',
                'message' => 'E-mail is invalid or empty.',
            ), 404);
        }
        if (!isset($data['password']) || mb_strlen($data['password']) < 6) {
            $this->responseJson(array(
                'status' => 'error',
                'message' => 'Password is invalid or empty.',
            ), 404);
        }

        $transaction = $this->transactionManager->get();

        $user = Users::findFirst(array(
            'conditions' => 'email = :email:',
            'bind' => array(
                'email' => $data['email'],
            ),
            'bindTypes' => array(
                'email' => \Phalcon\Db\Column::BIND_PARAM_STR,
            ),
        ));

        if ($user) {
            $this->responseJson(array(
                'status' => 'error',
                'message' => 'E-mail already exists.',
            ), 405);
        }

        try {
            $user = new Users();
            $user->id = UUID::v4();
            $user->name = $data['username'];
            $user->email = $data['email'];
            $user->password = $this->security->hash($data['password']);
            $user->created = date('Y-m-d H:i:s');
            $user->modified = date('Y-m-d H:i:s');
            $user->status = 'active';
            $user->token = $this->security->getSaltBytes(36);

            if (false === $user->save()) {
                $transaction->rollback();
            }

            $transaction->commit();

            $this->responseJson(array(
                'status' => 'ok',
                'message' => 'Success.',
            ));
        } catch(\Phalcon\Mvc\Model\Transaction\Failed $e) {
            $this->responseJson(array(
                'status' => 'error',
                'message' => 'Data save failed.',
            ), 405);
        } catch(\PDOException $e) {
            $this->responseJson(array(
                'status' => 'error',
                'message' => 'Data save failed.',
            ), 405);
        }
    }

    protected function getParams( $key = null, $default = null ) {
        if (is_null($key)) {
            return $this->dispatcher->getParams();
        } else {
            $params = $this->dispatcher->getParams();
            if (count($params) > 0 && isset($params[$key])) {
                return $params[$key];
            } else {
                return $default;
            }
        }
    }

    /**
     * Prepare JSON response.
     * @param  array   $response Data for json response.
     * @param  integer $state    HTTP Status Code
     * @return null
     */
    protected function responseJson(array $response = array(), $state = 200) {
        $datetime = gmdate("D, d M Y H:i:s").' GMT';

        $this->response->setHeader('Server', 'MuzikDJ');
        $this->response->setHeader('Pragma', 'no-cache');
        $this->response->setHeader('Cache-Control', 'no-cache, private, no-store, must-revalidate, pre-check=0, post-check=0, max-age=0, max-stale=0');
        $this->response->setHeader('Last-Modified', $datetime);
        $this->response->setHeader('X-Frame-Options', 'SAMEORIGIN');
        $this->response->setHeader('X-Content-Type-Options', 'nosniff');
        $this->response->setHeader('X-Powered-By', 'Hina');
        $this->response->setContentType('application/json', 'UTF-8');
        $this->response->setExpires(new \DateTime());
        $this->response->setStatusCode($state);
        $this->response->setEtag(md5($datetime));
        $this->response->setJsonContent($response, JSON_UNESCAPED_UNICODE);
        $this->response->send();

        // Force exit the application.
        exit;
    }
}