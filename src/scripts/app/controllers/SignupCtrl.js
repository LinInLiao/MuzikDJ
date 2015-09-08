'use strict';

require('./_mCtrls');

angular.module('mCtrls').controller('SignupCtrl', ['$scope', '$http', '$state', '$timeout', function ($scope, $http, $state, $timeout) {

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

    $scope.error = false;

    $scope.signup = function() {
        if (angular.element('#username').parent().hasClass('is-invalid') || $scope.username === '' || $scope.username === undefined) {
            $scope.error = 'username';
            return false;
        }
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
            url: '/api/signup',
            timeout: 30000,
            data: {
                username: $scope.username,
                email: $scope.email,
                password: $scope.password
            }
        }).then(function(response) {
            if (response.data !== undefined && response.data.status === 'ok') {
                $state.transitionTo('home');
            }
        }, function(response) {

        });
    };
}]);
