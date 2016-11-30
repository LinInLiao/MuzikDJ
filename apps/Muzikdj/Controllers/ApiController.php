<?php

namespace Muzikdj\Api\Controllers;

use Muzikdj\Plugins\UUID;
use Muzikdj\Plugins\Slug;
use Muzikdj\Models\Playlist;
use Muzikdj\Models\RoomPlaylist;
use Muzikdj\Models\Rooms;
use Muzikdj\Models\SongPlaylist;
use Muzikdj\Models\Songs;
use Muzikdj\Models\UserPlaylist;
use Muzikdj\Models\UserRoom;
use Muzikdj\Models\Users;

final class ApiController extends \Phalcon\Mvc\Controller {

    public function removeRoomAction() {
        $user = $this->session->has('USER') ? $this->session->get('USER') : null;

        if (is_null($user)) {
            return [[
                'status' => 'error',
                'message' => 'Need login.',
            ], 403];
        }

        $data = $this->request->getJsonRawBody(true);

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
                return [[
                    'status' => 'ok',
                    'message' => 'Success.',
                ], 200];
            } catch(\Phalcon\Mvc\Model\Transaction\Failed $e) {
                return [[
                    'status' => 'error',
                    'message' => 'Data save failed.',
                ], 405];
            } catch(\PDOException $e) {
                return [[
                    'status' => 'error',
                    'message' => 'Data save failed.',
                ], 405];
            }
        } else {
            return [[
                'status' => 'error',
                'message' => 'Room not found.',
            ], 404];
        }
    }

    public function accountRoomsAction() {
        $user = $this->session->has('USER') ? $this->session->get('USER') : null;

        if (is_null($user)) {
            return [[
                'status' => 'error',
                'message' => 'Need login.',
            ], 403];
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

            return [[
                'status' => 'ok',
                'message' => 'Success.',
                'result' => $user_rooms,
            ], 200];
        } else {
            return [[
                'status' => 'err',
                'message' => 'Not found.',
            ], 404];
        }
    }

    public function accountEditAction() {
        $data = $this->request->getJsonRawBody(true);

        $user = $this->session->has('USER') ? $this->session->get('USER') : null;

        if (is_null($user)) {
            return [[
                'status' => 'error',
                'message' => 'Need login.',
            ], 403];
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
                return [[
                    'status' => 'ok',
                    'message' => 'Success.',
                ], 200];
            } catch(\Phalcon\Mvc\Model\Transaction\Failed $e) {
                return [[
                    'status' => 'error',
                    'message' => 'Data save failed.',
                ], 405];
            } catch(\PDOException $e) {
                return [[
                    'status' => 'error',
                    'message' => 'Data save failed.',
                ], 405];
            }
        } else {
            return [[
                'status' => 'error',
                'message' => 'User not found.',
            ], 404];
        }
    }

    public function removeSongAction() {
        $data = $this->request->getJsonRawBody(true);

        $user = $this->session->has('USER') ? $this->session->get('USER') : null;

        if (is_null($user)) {
            return [[
                'status' => 'error',
                'message' => 'Need login.',
            ], 403];
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
                return [[
                    'status' => 'error',
                    'message' => 'You can not remove this song.',
                ], 405];
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
                return [[
                    'status' => 'ok',
                    'message' => 'Success.',
                ], 200];
            } catch(\Phalcon\Mvc\Model\Transaction\Failed $e) {
                return [[
                    'status' => 'error',
                    'message' => 'Data save failed.',
                ], 405];
            } catch(\PDOException $e) {
                return [[
                    'status' => 'error',
                    'message' => 'Data save failed.',
                ], 405];
            }
        } else {
            return [[
                'status' => 'error',
                'message' => 'Song or Room not found.',
            ], 404];
        }
    }

    public function createSongAction() {
        $data = $this->request->getJsonRawBody(true);

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

                return [[
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
                        'userId' => $user->id,
                    ),
                ], 200];
            } catch(\Phalcon\Mvc\Model\Transaction\Failed $e) {
                return [[
                    'status' => 'error',
                    'message' => 'Data save failed.',
                ], 405];
            } catch(\PDOException $e) {
                return [[
                    'status' => 'error',
                    'message' => 'Data save failed.',
                ], 405];
            }
        } else {
            return [[
                'status' => 'error',
                'message' => 'Room not found.',
            ], 404];
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

            return [[
                'status' => 'ok',
                'message' => 'Success.',
                'result' => $rooms,
            ], 200];
        } else {
            return [[
                'status' => 'err',
                'message' => 'Not found.',
            ], 404];
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
        return [[
            'status' => 'ok',
            'message' => 'Success.',
            'result' => $songs,
        ], 200];
    }

    public function singleRoomAction($room_alias = null) {
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
                        return [[
                            'status' => 'err',
                            'message' => 'This room need password.',
                        ], 403];
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
                array_walk($songs, function(&$item) {
                    $item = array_combine(
                        array_map(function($key) {
                            return lcfirst(
                                str_replace(
                                    ' ',
                                    '',
                                    ucwords(str_replace('_', ' ', $key))
                                )
                            );
                        }, array_keys($item)), $item);

                    $item['sort'] = (int) $item['sort'];
                });
            } else {
                $songs = array();
            }
            return [[
                'status' => 'ok',
                'message' => 'Success.',
                'result' => $songs,
                'room' => array(
                    'id' => $room->id,
                    'name' => $room->name,
                    'alias' => $room->alias,
                )
            ], 200];
        } else {
            return [[
                'status' => 'err',
                'message' => 'This room does not exists.',
            ], 404];
        }
    }

    public function checkRoomAction() {
        $data = $this->request->getJsonRawBody(true);

        $room = Rooms::findFirst(array(
            'columns' => 'id, name, alias, status',
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
                        return [[
                            'status' => 'error',
                            'message' => 'Private room need password.',
                        ], 403];
                    }
                }
            }
            return [[
                'status' => 'ok',
                'message' => 'Success.',
                'result' => $room->toArray(),
            ], 200];
        } else {
            return [[
                'status' => 'error',
                'message' => 'Room does not exists.',
            ], 404];
        }
    }

    public function privateRoomAction() {
        $data = $this->request->getJsonRawBody(true);

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
            return [[
                'status' => 'ok',
                'message' => 'Success.',
            ], 200];
        } else {
            return [[
                'status' => 'err',
                'message' => 'Room password invalid or room does not exists.',
            ], 403];
        }
    }

    public function searchAction() {
        $data = $this->request->getJsonRawBody(true);

        if (empty($data['keyword'])) {
            return [[
                'status' => 'err',
                'message' => 'Room name is required.',
            ], 404];
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

            return [[
                'status' => 'ok',
                'message' => 'Success.',
                'result' => $rooms,
            ], 200];
        } else {
            return [[
                'status' => 'err',
                'message' => 'Not found.',
            ], 404];
        }
    }

    /**
     * Create new room.
     * @return null
     */
    public function createRoomAction() {
        if (false === $this->session->has('USER')) {
            return [[
                'status' => 'error',
                'message' => 'Need login.',
            ], 403];
        }

        $user = $this->session->get('USER');
        $data = $this->request->getJsonRawBody(true);

        if (empty($data)) {
            return [[
                'status' => 'error',
                'message' => 'Data not found.',
            ], 404];
        }

        $room_name = isset($data['roomId']) ? trim($data['roomId']) : null;
        $room_type = isset($data['roomType']) && in_array($data['roomType'], array('private',  'public'), true) ? $data['roomType'] : 'public';
        $room_password = isset($data['roomPassword']) ? trim($data['roomPassword']) : null;

        if (empty($room_name)) {
            return [[
                'status' => 'error',
                'message' => 'Room ID is required.',
            ], 405];
        }
        $room_alias = Slug::get($room_name);
        if (strlen($room_alias) > 200 || true === preg_match(NAME_WHITE_LIST, $room_name) || str_replace('-', '', $room_alias) === '') {
            return [[
                'status' => 'error',
                'message' => 'Room ID is invalid.',
            ], 405];
        }

        if ($room_type === 'private') {
            if (mb_strlen($room_password) < 6) {
                return [[
                    'status' => 'error',
                    'message' => 'Password is invalid or empty.',
                ], 404];
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

            return [[
                'status' => 'ok',
                'message' => 'Success.',
                'result' => $room->alias,
            ], 200];
        } catch(\Phalcon\Mvc\Model\Transaction\Failed $e) {
            return [[
                'status' => 'error',
                'message' => 'Data save failed.',
            ], 405];
        } catch(\PDOException $e) {
            return [[
                'status' => 'error',
                'message' => 'Data save failed.',
            ], 405];
        }
    }

    /**
     * Logout user.
     * @return null
     */
    public function logoutAction() {
        $this->session->remove('USER');
        return [[
            'status' => 'ok',
            'message' => 'Success.',
        ], 200];
    }

    /**
     * Check the user login status.
     * @return null
     */
    public function checkAuthAction() {
        if (true === $this->session->has('USER')) {
            return [[
                'status' => 'ok',
                'message' => 'Success.',
                'user' => $this->session->get('USER'),
            ], 200];
        } else {
            return [[
                'status' => 'error',
                'message' => 'Failed.',
            ], 200];
        }
    }

    /**
     * Login user.
     * @return null
     */
    public function loginAction() {
        $data = $this->request->getJsonRawBody(true);

        if (empty($data)) {
            return [[
                'status' => 'error',
                'message' => 'Data not found.',
            ], 404];
        }

        if (!isset($data['email']) || false === filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return [[
                'status' => 'error',
                'message' => 'E-mail is invalid or empty.',
            ], 404];
        }
        if (!isset($data['password']) || mb_strlen($data['password']) < 6) {
            return [[
                'status' => 'error',
                'message' => 'Password is invalid or empty.',
            ], 404];
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
            return [[
                'status' => 'error',
                'message' => 'Password is incurrect or e-mail does not exists.',
            ], 405];
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

        return [[
            'status' => 'ok',
            'message' => 'Success.',
            'user' => $user
        ], 200];
    }

    /**
     * New user signup.
     * @return null
     */
    public function signupAction() {
        $data = $this->request->getJsonRawBody(true);

        if (empty($data)) {
            return [[
                'status' => 'error',
                'message' => 'Data not found.',
            ], 404];
        }

        if (!isset($data['username'])) {
            return [[
                'status' => 'error',
                'message' => 'Username is required.',
            ], 404];
        }
        if (!isset($data['email']) || false === filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return [[
                'status' => 'error',
                'message' => 'E-mail is invalid or empty.',
            ], 404];
        }
        if (!isset($data['password']) || mb_strlen($data['password']) < 6) {
            return [[
                'status' => 'error',
                'message' => 'Password is invalid or empty.',
            ], 404];
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
            return [[
                'status' => 'error',
                'message' => 'E-mail already exists.',
            ], 405];
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

            return [[
                'status' => 'ok',
                'message' => 'Success.',
            ], 200];
        } catch(\Phalcon\Mvc\Model\Transaction\Failed $e) {
            return [[
                'status' => 'error',
                'message' => 'Data save failed.',
            ], 405];
        } catch(\PDOException $e) {
            return [[
                'status' => 'error',
                'message' => 'Data save failed.',
            ], 405];
        }
    }
}
