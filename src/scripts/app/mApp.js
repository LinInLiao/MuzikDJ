'use strict';

require('./animations/_loader');
require('./controllers/_loader');
require('./directives/_loader');
require('./services/_loader');

/* register main app */
angular.module('mApp', ['ngTouch', 'ngSanitize', 'ui.router', 'angularVideoBg', 'mAnimations', 'mCtrls'])
    .config(function ($provide, $stateProvider, $locationProvider, $httpProvider, $urlRouterProvider) {
        $provide.decorator('$rootScope', ['$delegate', function($delegate) {
            Object.defineProperty($delegate.constructor.prototype, '$onRootScope', {
                value: function(name, listener) {
                    var unsubscribe = $delegate.$on(name, listener);
                    this.$on('$destroy', unsubscribe);

                    return unsubscribe;
                }
            });
            return $delegate;
        }]);

        $stateProvider
            .state('home', {
                url: '/',
                templateUrl: 'tpls/views/home.html',
                controller: 'HomeCtrl'
            })
            .state('errorPage', {
                url: '/error',
                templateUrl: 'tpls/views/error.html',
                controller: 'ErrorCtrl'
            })
            .state('search', {
                url: '/search',
                templateUrl: 'tpls/views/search.html',
                controller: 'SearchCtrl'
            })
            .state('addPlaylist', {
                url: '/playlist',
                templateUrl: 'tpls/views/playlist.html',
                controller: 'PlaylistCtrl'
            })
            .state('listenNow', {
                url: '/listen',
                templateUrl: 'tpls/views/listen.html',
                controller: 'ListenCtrl'
            })
            .state('login', {
                url: '/login',
                templateUrl: 'tpls/views/login.html',
                controller: 'LoginCtrl'
            })
            .state('logout', {
                url: '/logout',
                controller: ['$state', '$http', '$scope', function($steate, $http, $scope) {
                    $scope.$parent.user = {
                        login: false,
                    };
                    $http({
                        method: 'post',
                        responseType: 'json',
                        url: '/api/logout',
                    }).then(function() {
                        $state.transitionTo('home');
                    });
                }]
            })
            .state('createRoom', {
                url: '/create/room',
                templateUrl: 'tpls/views/createRoom.html',
                controller: 'CreateRoomCtrl'
            })
            .state('signup', {
                url: '/signup',
                templateUrl: 'tpls/views/signup.html',
                controller: 'SignupCtrl'
            })
            .state('account', {
                url: '/account',
                templateUrl: 'tpls/views/account.html',
                controller: 'AccountCtrl'
            })
            .state('accountEdit', {
                url: '/account/edit',
                templateUrl: 'tpls/views/accountEdit.html',
                controller: 'AccountEditCtrl'
            })
            .state('accountFavorite', {
                url: '/account/favorite',
                templateUrl: 'tpls/views/accountFavorite.html',
                controller: 'AccountFavoriteCtrl'
            })
            .state('accountRooms', {
                url: '/account/rooms',
                templateUrl: 'tpls/views/accountRooms.html',
                controller: 'AccountRoomsCtrl'
            })
            .state('room', {
                url: '/room',
                templateUrl: 'tpls/views/room.html',
                controller: 'RoomCtrl'
            })
            .state('roomSingle', {
                url: '/room/{alias:[a-z0-9\-]+}',
                templateUrl: 'tpls/views/singleRoom.html',
                controller: 'SingleRoomCtrl'
            });

        $urlRouterProvider.otherwise('/error');

        $locationProvider.html5Mode({
            enabled: (window.history && window.history.pushState) ? true : false,
            requireBase: false
        });
        $locationProvider.hashPrefix('!');

        $httpProvider.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
    });
