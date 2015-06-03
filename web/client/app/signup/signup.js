'use strict';

angular.module('clientApp.signup', ['ngRoute'])

.config(['$routeProvider', function($routeProvider) {
  $routeProvider.when('/signup', {
    templateUrl: 'client/app/signup/signup.html',
    controller: 'SignupCtrl'
  });
}])

.controller('SignupCtrl', function($scope, $http, alertService, $location, userService) {
    $scope.user = {};
    $scope.signup = function() {
        if(!($scope.user.email && $scope.user.password)){
            return;
        }

        $http.post('/session/signup', $scope.user)
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