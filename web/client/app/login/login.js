'use strict';

angular.module('clientApp.login', ['ngRoute']).config(['$routeProvider', function($routeProvider) {
        $routeProvider.when('/login', {
            templateUrl: 'client/app/login/login.html',
            controller: 'LoginCtrl',
            resolve: {
                authenticated: function($location, userService) {
                    return userService.isAuthenticated().then(function() {
                        $location.path('/');
                    }, function() {
                        return;
                    });
                }
            }
        });
    }
]).controller('LoginCtrl', function($scope, userService, $location, $log, $http, alertService) {
    $scope.user = {};

    $scope.login = function() {
        // early return on simple validation failure
        if (!($scope.user.email && $scope.user.password)) {
            return;
        }

        var req = {
            method: 'POST',
            url: '/session/login_check',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            transformRequest: function(obj) {
                var str = [];
                for (var p in obj)
                    str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
                return str.join("&");
            },
            data: $scope.user
        };
        
        $http(req).error(function(data, status) {
            if (status === 400) {
            } else if (status === 401) {
                alertService.add('danger', 'Invalid email or password!');
            } else if (status === 500) {
                alertService.add('danger', 'Internal server error!');
            } else {
                alertService.add('danger', data);
            }
        }).success(function(result) {
            $log.debug(result);
            if (result.type === 'success') {
                userService.username = result.data.username;
                $location.path('/');
            }
        });
    };
});