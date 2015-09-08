'use strict';

require('./_mCtrls');

angular.module('mCtrls').controller('AccountEditCtrl', ['$http', '$state', '$scope', '$timeout', function ($http, $state, $scope, $timeout) {
    $scope.user = $scope.$parent.user;

    if ($scope.user.login === false) {
        $state.transitionTo('errorPage');
    }

    $scope.password = undefined;

    $timeout(function() {
        var _element = angular.element('.mdl-js-textfield'), _e, i, l;
        for (i = 0, l = _element.length; i < l; i++) {
            _e = new MaterialTextfield(_element[i]);
        }
        _element = angular.element('.mdl-js-button')[0];
        if (_element) {
            _e = new MaterialButton(_element);
            _e = new MaterialRipple(_element);
        }
    });

    $scope.save = function() {
        $http({
            method: 'post',
            responseType: 'json',
            url: '/api/account/save',
            timeout: 30000,
            data: {
                name: $scope.user.name,
                password: $scope.password,
            }
        }).then(function(response) {
            if (response.data !== undefined && response.data.status === 'ok') {
                $scope.user.name = response.data.result.name;
            }
        }, function(response) {

        });
    };
}]);
