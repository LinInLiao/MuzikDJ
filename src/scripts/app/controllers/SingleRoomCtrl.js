'use strict';

require('./_mCtrls');

angular.module('mCtrls').controller('SingleRoomCtrl', ['$http', '$state', '$window', '$q', '$timeout', '$scope',
    function ($http, $state, $window, $q, $timeout, $scope) {
        $scope.song = {
            url: undefined
        };
        $scope.error = false;
        $scope.checkRoom = false;
        $scope.room = {
            id: '',
            name: '',
            password: undefined,
            user_id: undefined,
        };
        $scope.user = $scope.$parent.user;

        $timeout(function() {
            var _element, _e, i, l;
            _element = angular.element('.mdl-js-textfield')
            for (i = 0, l = _element.length; i < l; i++) {
                _e = new MaterialTextfield(_element[i]);
            }

            _element = angular.element('.mdl-js-button');
            for (i = 0, l = _element.length; i < l; i++) {
                _e = new MaterialButton(_element[i]);
                _e = new MaterialRipple(_element[i]);
            }
        });

        var getSongs = function() {
            return $http({
                method: 'post',
                responseType: 'json',
                url: '/api/room/'+$state.params.alias,
                timeout: 30000,
            }).then(function(response) {
                if (response.data !== undefined && response.data.status === 'ok') {
                    $scope.room = response.data.room;
                    ga('send', 'event', 'room', $scope.room.name);

                    $scope.$parent.room = angular.extend({}, $scope.$parent.room, $scope.room);

                    $timeout(function() {
                        var _element, _e, i, l;
                        _element = angular.element('.mdl-js-textfield')
                        for (i = 0, l = _element.length; i < l; i++) {
                            _e = new MaterialTextfield(_element[i]);
                        }

                        _element = angular.element('.mdl-js-button');
                        for (i = 0, l = _element.length; i < l; i++) {
                            _e = new MaterialButton(_element[i]);
                            _e = new MaterialRipple(_element[i]);
                        }
                        _element = angular.element('.mdl-js-data-table')[0];
                        if (_element) {
                            _e = new MaterialDataTable(_element);
                        }

                        if (response.data.result.length > 0) {
                            // auto play first song.
                            $scope.$parent.songs = response.data.result;
                            $scope.$parent.play(null, 0);
                        }
                    });
                }
            }, function(response) {
            });
        };

        var promise = $http({
            method: 'post',
            responseType: 'json',
            url: '/api/room/check',
            timeout: 30000,
            data: {
                alias: $state.params.alias
            }
        }).then(function(response) {
            var deferred = $q.defer();
            if (response.data !== undefined && response.data.status === 'ok') {
                deferred.resolve(response);
            } else {
                deferred.reject(response);
            }
            return deferred.promise;
        }, function(response) {
            var deferred = $q.defer();
            deferred.reject(response);
            return deferred.promise;
        }).then(function(response) {
            $scope.checkRoom = true;
            getSongs();
        }, function(response) {
            $scope.checkRoom = 'login';
            $scope.room.name = 'Private Room';

            $timeout(function() {
                var _element, _e, i, l;
                _element = angular.element('.mdl-js-textfield')
                for (i = 0, l = _element.length; i < l; i++) {
                    _e = new MaterialTextfield(_element[i]);
                }

                _element = angular.element('.mdl-js-button');
                for (i = 0, l = _element.length; i < l; i++) {
                    _e = new MaterialButton(_element[i]);
                    _e = new MaterialRipple(_element[i]);
                }
            });
        });

        $scope.join = function() {
            if ($scope.room.password === undefined || $scope.room.password === '') {
                $scope.error = 'password';
                return false;
            }

            $scope.error = false;

            $http({
                method: 'post',
                responseType: 'json',
                url: '/api/room/private',
                timeout: 30000,
                data: {
                    alias: $state.params.alias,
                    password: $scope.room.password,
                }
            }).then(function(response) {
                if (response.data !== undefined && response.data.status === 'ok') {
                    $scope.checkRoom = true;
                    getSongs();
                } else {
                    $scope.error = 'password';
                }
            }, function(response) {
                $scope.error = 'password';
            });
        };

        $scope.add = function () {
        	if ($scope.song.url === undefined || $scope.song.url === '') {
        		$scope.error = 'url';
        		return false;
        	}

        	$scope.error = false;

        	$http({
    	    	method: 'post',
    	        responseType: 'json',
    	        url: '/api/song/create',
    	        timeout: 30000,
    	        data: {
    	        	room: $state.params.alias,
    	        	url: $scope.song.url,
    	        }
    	    }).then(function(response) {
                if (response.data !== undefined && response.data.status === 'ok') {
                    ga('send', 'event', 'song', 'add', $scope.song.url);
                    $scope.$parent.wsconn.publish($scope.room.id, [response.data.result, new Date().getTime()]);
                }
    	    }, function(response) {

    	    });
        };

        $scope.remove = function(index) {
            var song = $scope.$parent.songs[index];

            if (song !== undefined) {
                $http({
                    method: 'post',
                    responseType: 'json',
                    url: '/api/song/remove',
                    timeout: 30000,
                    data: {
                        id: song.id,
                        room: $scope.room.id,
                    }
                }).then(function(response) {
                    if (response.data !== undefined && response.data.status === 'ok') {
                        if ($scope.$parent.playingStatus.index === index) {
                            $scope.$parent.play(null, index + 1);
                        }
                        $scope.$parent.songs.splice(index, 1);
                    }
                }, function(response) {

                });
            }
        }
    }
]);
