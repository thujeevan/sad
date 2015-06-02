'use strict';

angular.module('clientApp.menu', []).
controller('MenuCtrl', function($scope, $http, userService, $location) {
    $scope.user = userService;

    $scope.logout = function() {
        $http.get('/session/logout')
                .success(function(result) {
                    if (result.type === 'success') {
                        userService.username = '';
                        $location.path('/login');
                    }
                });
    };

    $scope.$watch('user.username', function(newVal) {
        if (newVal === '') {
            $scope.isLoggedIn = false;
        } else {
            $scope.username = newVal;
            $scope.isLoggedIn = true;
        }
    });
});