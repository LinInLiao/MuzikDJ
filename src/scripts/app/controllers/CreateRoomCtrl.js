'use strict';

require('./_mCtrls');

angular.module('mCtrls').controller('CreateRoomCtrl', ['$http', '$state', '$scope', '$timeout', function ($http, $state, $scope, $timeout) {

    $scope.user = $scope.$parent.user;

    $scope.roomType = 'private';
    $scope.error = false;

    $timeout(function() {
    	if ($scope.user.login === false) {
    		$state.transitionTo('home');
    	}

    	var _element = angular.element('.mdl-js-textfield'), _e, i, l;
    	for (i = 0, l = _element.length; i < l; i++) {
    		_e = new MaterialTextfield(_element[i]);
    	}
    	_element = angular.element('.mdl-js-radio');
    	for (i = 0, l = _element.length; i < l; i++) {
    		_e = new MaterialRadio(_element[i]);
    		_e = new MaterialRipple(_element[i]);
    	}
    	_element = angular.element('.mdl-js-button')[0];
    	if (_element) {
    		_e = new MaterialButton(_element);
    		_e = new MaterialRipple(_element);
    	}
    });

	$scope.create = function() {
		if (angular.element('#room_id').parent().hasClass('is-invalid') || $scope.roomId === '' || $scope.roomId === undefined) {
            $scope.error = 'room_id';
            return false;
        }
        if ($scope.roomType === 'private' && ($scope.roomPassword === '' || $scope.roomPassword === undefined || $scope.roomPassword.length < 6)) {
            $scope.error = 'password';
            return false;
        }

        $scope.error = false;

        $http({
            method: 'post',
            responseType: 'json',
            url: '/api/room/create',
            timeout: 30000,
            data: {
                roomType: $scope.roomType,
                roomId: $scope.roomId,
                roomPassword: $scope.roomPassword
            }
        }).then(function(response) {
        	if (response.data !== undefined && response.data.status === 'ok') {
        		$state.path('/room/'+response.data.result);
        	}
        }, function(response) {

        });
	};
}]);
