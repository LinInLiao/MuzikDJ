'use strict';

require('./_mCtrls');

angular.module('mCtrls').controller('ListenCtrl', ['$state', '$http', '$scope', function ($state, $http, $scope) {
    $scope.user = $scope.$parent.user;
    $scope.rooms = [];

    var _element = angular.element('.mdl-js-button')[0], _e;
    _e = new MaterialButton(_element);
    _e = new MaterialRipple(_element);

    $scope.roomCover = function(room) {
        if (room.cover !== false) {
            return {
                backgroundImage: 'url('+room.cover+')'
            };
        }
        return '';
    };

    $http({
        method: 'post',
        responseType: 'json',
        url: '/api/listen/rooms',
        timeout: 30000,
    }).then(function(response) {
        if (response.data !== undefined && response.data.status === 'ok') {
            $scope.rooms = response.data.result;
        }
    }, function(response) {

    });

}]);