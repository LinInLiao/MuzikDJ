'use strict';

require('./_mAnimations');

angular.module('mAnimations').animation('.view-change-animate', function () {
    return {
        enter: function (element, done) {
            jQuery(element).hide();
            jQuery(element).fadeIn(500, done);
        },
        leave: function (element, done) {
            jQuery(element).show();
            jQuery(element).fadeOut(500, done);
        }
    };
});
