'use strict';

require('./_mCtrls');

angular.module('mCtrls').controller('PlaylistCtrl', ['$http', '$timeout', '$state', '$scope', '$window', function ($http, $timeout, $state, $scope, $window) {

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

    var conn = new ab.Session('ws://'+$window.location.host+':9000',
        function() {
            conn.subscribe('anonymous', function(topic, data) {
                $timeout(function() {
                    $scope.$parent.songs.unshift(data[0]);
                    $scope.$digest();
                });
            });
            conn.subscribe('anonymousplay', function(topic, data) {
                $timeout(function() {
                    if (data !== undefined && data[0] !== undefined) {
                        $scope.$parent.play(null, parseInt(data[0], 10));
                        $scope.$digest();
                    }
                });
            });
        },
        function() {
            console.warn('WebSocket connection closed');
        },
        {'skipSubprotocolCheck': true}
    );

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
            url: '/api/playlist',
            timeout: 30000,
        }).then(function(response) {
            if (response.data !== undefined && response.data.status === 'ok') {
                if (response.data.result.length > 0) {
                    // auto play first song.
                    $scope.$parent.songs = response.data.result;
                    $scope.$parent.play(null, 0);
                }

                $timeout(function() {
                    var _element, _e, i, l;
                    _element = angular.element('.mdl-js-button');
                    for (i = 0, l = _element.length; i < l; i++) {
                        _e = new MaterialButton(_element[i]);
                        _e = new MaterialRipple(_element[i]);
                    }
                    _element = angular.element('.mdl-js-data-table')[0];
                    if (_element) {
                        _e = new MaterialDataTable(_element);
                    }
                });
            }
        }, function(response) {
        });
    };
    $scope.add = function () {
        if ($scope.url === undefined || $scope.url === '' || false === /^(([^:/?#]+):)?(\/\/([^/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?/.test($scope.url)) {
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
                url: $scope.url,
            }
        }).then(function(response) {
            if (response.data !== undefined && response.data.status === 'ok') {
                $timeout(function() {
                    var conn = new ab.Session('ws://'+$window.location.host+':9000', function() {
                            conn.publish('anonymous', [response.data.result]);
                        },
                        function() {
                            console.warn('WebSocket connection closed');
                        },
                        {'skipSubprotocolCheck': true}
                    );
                });
            }
        }, function(response) {

        });
    };
}]);
