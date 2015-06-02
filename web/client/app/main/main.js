'use strict';

angular.module('clientApp.main', ['ngRoute'])

.config(['$routeProvider', function($routeProvider) {
  $routeProvider.when('/', {
    templateUrl: 'client/app/main/main.html',
    controller: 'MainCtrl'
  });
}])

.controller('MainCtrl', function($scope, $location, $log, $http) {
    $scope.getBook = function() {
        $http.get('api/contacts')
            .success(function(result) {
              $scope.contacts = result.data;
            })
            .error(function() {
                $location.path('/login');
            });
    };
    $scope.getBook();      
});