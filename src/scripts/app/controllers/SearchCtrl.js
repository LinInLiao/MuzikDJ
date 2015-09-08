'use strict';

require('./_mCtrls');

angular.module('mCtrls').controller('SearchCtrl', ['$http', '$state', '$scope', '$timeout', function ($http, $state, $scope, $timeout) {

    $timeout(function() {
        var _element = angular.element('.mdl-js-textfield'), _e, i, l;
        for (i = 0, l = _element.length; i < l; i++) {
            _e = new MaterialTextfield(_element[i]);
        }

        _element = angular.element('.mdl-js-button')[0];
        _e = new MaterialButton(_element);
        _e = new MaterialRipple(_element);
    });

    $scope.roomCover = function(room) {
        if (room.cover !== false) {
            return {
                backgroundImage: 'url('+room.cover+')'
            };
        }
        return '';
    };

    $scope.search = function() {
        if ($scope.keyword === undefined || $scope.keyword === '') {
            $scope.error = 'keyword';
            return false;
        }

        $scope.error = false;

        $http({
            method: 'post',
            responseType: 'json',
            url: '/api/search',
            timeout: 30000,
            data: {
                keyword: $scope.keyword,
            }
        }).then(function(response) {
            if (response.data !== undefined && response.data.status === 'ok') {
                $scope.rooms = response.data.result;
            }
        }, function(response) {

        });
    };
}]);
