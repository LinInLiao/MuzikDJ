'use strict';

require('./_mCtrls');

angular.module('mCtrls').controller('RootCtrl', ['$scope', '$http', '$timeout', '$window', '$state', '$sce', function ($scope, $http, $timeout, $window, $state, $sce) {
    ga('send', 'pageview', { 'page': $window.location.pathname });
    $scope.user = {
    	login: false,
        checked: false,
    };
    $scope.youtubeId = undefined;
    $scope.youtubePlayer = undefined;
    // $scope.youtubePlayerVars = {
    //     autoplay: 0,
    //     controls: 0,
    // };
    $scope.playingStatus = {
        playing: false,
        index: undefined,
        id: undefined,
    };
    $scope.songs = [];
    $scope.room = {
        id: undefined,
        name: undefined,
        password: undefined,
        user_id: undefined,
        join: new Date().getTime(),
    };

    // $scope.$onRootScope('youtube.player.error', function ($event, player) {
    //
    // });
    // $scope.$onRootScope('youtube.player.playing', function ($event, player) {
    //
    // });
    // $scope.$onRootScope('youtube.player.ready', function ($event, player) {
    //     $scope.youtubePlayer = player;
    //     player.playVideo();
    // });
    // $scope.$onRootScope('youtube.player.ended', function ($event, player) {
    //     var index = $scope.playingStatus.index + 1;
    //
    //     if (index >= $scope.songs.length) {
    //         $scope.play($event, 0);
    //     } else {
    //         $scope.play($event, index);
    //     }
    // });

    $scope.wsconn = new ab.Session('ws://'+$window.location.host+':9000', function() {
        console.log('open');
    });
    $scope.$watch('room', function(newValue, oldValue) {
        if (false === angular.equals(newValue, oldValue)) {
            $scope.wsconn.subscribe(newValue.id, function(topic, data) {
                $timeout(function() {
                    if (data !== undefined && data[0] !== undefined) {
                        if (data[0] === true) {

                        } else {
                            $scope.songs.unshift(data[0]);
                        }
                    }
                    $scope.$digest();
                });
            });
            $scope.wsconn.subscribe(newValue.id+'-play', function(topic, data) {
                $timeout(function() {
                    if (data !== undefined && data[0] !== undefined) {
                        if (data[0] === true) {

                        } else {
                            if ($scope.room.join >= data[1]) {
                                $scope.play(undefined, parseInt(data[0], 10));
                            } else {
                                $scope.wsconn.publish($scope.room.id+'-play', [$scope.playingStatus.index, 0]);
                            }
                        }
                        $scope.$digest();
                    }
                });
            });
        }
    }, true);

    $scope.playerStateChange = function(event) {
        if (event.data === YT.PlayerState.ENDED) {
            $scope.play(null, ($scope.playingStatus.index + 1));
        }
    };

    $scope.videoCallback = function(player) {
        $scope.youtubePlayer = player;

        if ($scope.playingStatus.playing === false) {
            $scope.play(null, 0);
        }
    };

    $scope.play = function (event, index) {
        if ($scope.youtubePlayer !== undefined) {
            if ($scope.songs.length > 0 && $scope.playingStatus.index !== index) {
                var song = $scope.songs[index];

                if (song === undefined) {
                    song = $scope.songs[0];
                    index = 0;
                }

                var youtubeId = (function(url) {
                  var videoId = url.split('v=')[1];
                  var ampersandPosition = videoId.indexOf('&');
                  if (ampersandPosition >= 0) {
                    videoId = videoId.substring(0, ampersandPosition);
                  }
                  return videoId;
                })(song.url);
                $scope.playingStatus.id = song.id;
                $scope.playingStatus.index = index;

                $scope.youtubePlayer.loadVideoById(youtubeId);

                $scope.youtubeId = youtubeId;

                if ($scope.user.login === true && event !== undefined) {
                    $scope.wsconn.publish($scope.room.id+'-play', [index, $scope.room.join]);
                }
            }
        }
    };

    $http({
        method: 'post',
        responseType: 'json',
        url: '/api/auth/check',
        timeout: 30000,
    }).then(function(response) {
        $scope.user.checked = true;
    	if (response.data.status === 'ok') {
    		$scope.user = angular.extend({}, $scope.user, response.data.result);
    		$scope.user.login = true;
    	}
    }, function(response) {
        $scope.user.checked = true;
    });
}]);
