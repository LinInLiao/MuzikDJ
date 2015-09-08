'use strict';

require('./_mCtrls');

angular.module('mCtrls').controller('ErrorCtrl', ['$http', '$state', '$scope', function ($http, $state, $scope) {
	var _element = angular.element('.mdl-js-textfield'), _e, i, l;
    for (i = 0, l = _element.length; i < l; i++) {
        _e = new MaterialTextfield(_element[i]);
    }

    _element = angular.element('.mdl-js-button')[0];
    _e = new MaterialButton(_element);
    _e = new MaterialRipple(_element);

    $scope.join = function() {

    };
}]);
