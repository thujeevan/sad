'use strict';

angular.module('clientApp.signup', ['ngRoute'])

.config(['$routeProvider', function($routeProvider) {
  $routeProvider.when('/signup', {
    templateUrl: 'client/app/signup/signup.html',
    controller: 'SignupCtrl'
  });
}])

.controller('SignupCtrl', function($scope, $http, $log, alertService, $location, userService) {

    $scope.signup = function() {
        var payload = {
            email: $scope.email,
            password: $scope.password
        };

        $http.post('/session/signup', payload)
                .error(function(result, status) {
                    if (status === 400) {
                        alertService.add('danger', result.reason);
                    }
                    if (status === 500) {
                        alertService.add('danger', 'Internal server error!');
                    }
                })
                .success(function(result) {
                    if (result.type === 'success') {
                        userService.username = $scope.email;
                        $location.path('/');
                    }
                });
    };
});