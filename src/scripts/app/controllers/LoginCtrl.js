'use strict';

require('./_mCtrls');

angular.module('mCtrls').controller('LoginCtrl', ['$state', '$scope', '$http', '$timeout', function ($state, $scope, $http, $timeout) {

    $timeout(function() {
        var _element = angular.element('.mdl-js-textfield'), _e, i, l;
        for (i = 0, l = _element.length; i < l; i++) {
            _e = new MaterialTextfield(_element[i]);
        }
        _element = angular.element('.mdl-js-button')[0];
        _e = new MaterialButton(_element);
        _e = new MaterialRipple(_element);

        var passwordElement = angular.element('#password').parent();
        $scope.$watch('password', function(newValue) {
            if (newValue !== undefined) {
                if (newValue.length < 6) {
                    passwordElement.addClass('is-invalid');
                } else {
                    passwordElement.removeClass('is-invalid');
                }
            }
        });
    });

    $scope.error = false;
    $scope.user = $scope.$parent.user;

    $scope.login = function() {
        if (angular.element('#email').parent().hasClass('is-invalid') || $scope.email === '' || $scope.email === undefined) {
            $scope.error = 'email';
            return false;
        }
        if (angular.element('#password').parent().hasClass('is-invalid') || $scope.password === '' || $scope.password === undefined) {
            $scope.error = 'password';
            return false;
        }

        $scope.error = false;

        $http({
            method: 'post',
            responseType: 'json',
            url: '/api/login',
            timeout: 30000,
            data: {
                email: $scope.email,
                password: $scope.password
            }
        }).then(function(response) {
        	if (response.data !== undefined && response.data.status === 'ok') {
        		$scope.user = angular.extend({}, $scope.user, response.data.result);
        		$scope.user.login = true;
        		$scope.$parent.user = $scope.user;

                $state.transitionTo('home');
        	}
        }, function(response) {

        });
    };
}]);
