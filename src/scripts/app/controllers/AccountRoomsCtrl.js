'use strict';

require('./_mCtrls');

angular.module('mCtrls').controller('AccountRoomsCtrl', ['$http', '$state', '$scope', function ($http, $state, $scope) {

    $scope.user = $scope.$parent.user;

    if ($scope.user.login === false) {
        $state.transitionTo('errorPage');
    }

    $scope.roomCover = function(room) {
        if (room.cover !== false) {
            return {
                backgroundImage: 'url('+room.cover+')'
            };
        }
        return '';
    };

    $scope.removeRoom = function(room_id, index) {
        $http({
            method: 'post',
            responseType: 'json',
            url: '/api/room/remove',
            timeout: 30000,
            data: {
                room_id: room_id,
            }
        }).then(function(response) {
            if (response.data !== undefined && response.data.status === 'ok') {
                $scope.rooms.splice(index, 1);
            }
        }, function(response) {

        });
    };

    $scope.rooms = [];

    $http({
        method: 'post',
        responseType: 'json',
        url: '/api/account/rooms',
        timeout: 30000,
    }).then(function(response) {
        if (response.data !== undefined && response.data.status === 'ok') {
            $scope.rooms = response.data.result;
        }
    }, function(response) {

    });
}]);
