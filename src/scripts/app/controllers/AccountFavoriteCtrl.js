'use strict';

require('./_mCtrls');

angular.module('mCtrls').controller('AccountFavoriteCtrl', ['$http', '$state', '$scope', function ($http, $state, $scope) {
    $scope.user = $scope.$parent.user;

    if ($scope.user.login === false) {
        $state.transitionTo('errorPage');
    }

}]);
