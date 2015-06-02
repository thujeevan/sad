'use strict';

angular.module('clientApp.login', ['ngRoute'])

.config(['$routeProvider', function($routeProvider) {
  $routeProvider.when('/login', {
    templateUrl: 'client/app/login/login.html',
    controller: 'LoginCtrl'
  });
}])

.controller('LoginCtrl', function($scope, userService, $location, $log, $http, alertService) {
    $scope.isAuthenticated = function() {
        if (userService.username) {
            $log.debug(userService.username);
            $location.path('/');
        } else {
            $http.get('/session/isauthenticated')
                    .error(function() {
                        $location.path('/login');
                    })
                    .success(function(data) {
                        if (data.hasOwnProperty('success')) {
                            userService.username = data.success.user;
                            $location.path('/');
                        }
                    });
        }
    };

    $scope.isAuthenticated();

    $scope.login = function() {

        var payload = {
            email: this.email,
            password: this.password
        };

        $http.post('/session/login', payload)
                .error(function(data, status) {
                    if (status === 400) {
                        angular.forEach(data, function(value, key) {
                            if (key === 'email' || key === 'password') {
                                alertService.add('danger', key + ' : ' + value);
                            } else {
                                alertService.add('danger', value.message);
                            }
                        });
                    } else if (status === 401) {
                        alertService.add('danger', 'Invalid login or password!');
                    } else if (status === 500) {
                        alertService.add('danger', 'Internal server error!');
                    } else {
                        alertService.add('danger', data);
                    }
                })
                .success(function(result) {
                    $log.debug(result);
                    if (result.type === 'success') {
                        userService.username = result.data.username;
                        $location.path('/');
                    }
                });
    };
});