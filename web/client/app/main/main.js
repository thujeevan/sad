'use strict';

angular.module('clientApp.main', ['ngRoute']).config(['$routeProvider', function($routeProvider) {
        $routeProvider.when('/', {
            templateUrl: 'client/app/main/main.html',
            controller: 'MainCtrl',
            resolve: {
                authenticated: function($q, $location, userService) {
                    return userService.isAuthenticated().then(null, function() {
                        $location.path('/login');
                        return $q.reject();
                    });
                }
            }
        });
    }
]).controller('MainCtrl', function($scope, $http, alertService) {
    $scope.contacts = [];
    $scope.master = {};
    $scope.searchTerm;
    $scope.loadingContacts = false;
    
    // search based on name or phone or email
    $scope.$watch('searchTerm', function(newVal, oldVal){ 
        var trimmed = newVal && newVal.trim();
        
        if (!(trimmed && (trimmed.split('').length))) {
            return oldVal;
        }
        var req = {
            method: 'GET',
            url: '/api/contacts',
            params : { q : trimmed }
        };
        $scope.loadingContacts = true;
        $http(req).success(function(result) {
            if(result.data){
                $scope.contacts = result.data;
            }
            $scope.loadingContacts = false;
        });
    });

    $scope.makeRequest = function(type, url, data) {
        return $http[type](url, data);
    };

    $scope.reset = function(form) {
        $scope.contact = angular.copy($scope.master);
        if (form) {
            form.$setPristine();
        }
    };
    $scope.reset();

    $scope.createNew = function(form) {
        var item = angular.copy($scope.contact);
        item.id = null;

        if (item.name && item.email && item.phone) {
            $scope.makeRequest('post', '/api/contacts', item).success(function(result) {
                $scope.contacts.push(result.data);
                $scope.reset(form);
                alertService.add('success', 'Contact item added successfully');
            }).error(function() {
                alertService.add('warning', 'Failed to add new entry, please re-try');
            });
        }
    };

    $scope.getBook = function() {
        $scope.makeRequest('get', '/api/contacts', {}).success(function(result) {
            if(result.data){
                $scope.contacts = $scope.contacts.concat(result.data);
            }
        }).error(function() {
            alertService.add('warning', 'Failed to fetch address book, please try again');
        });
    };

    $scope.getBook();
}).controller('ContactCtrl', function($scope, $window, alertService) {
    // Controller which responsible for each item in the list
    
    // savig the initial state of the item
    // will be used for resetting form
    $scope.master = angular.copy($scope.item);
    $scope.isEditOpen = false;

    // function to update properties without destroying object reference
    function updateItem(source) {
        for (var i in source) {
            if (source.hasOwnProperty(i)) {
                $scope.item[i] = source[i];
            }
        }
    };
    
    // reset the scope item to previous state
    // reset the form too when passed
    $scope.reset = function(form) {   
        updateItem($scope.master);
        if (form) {
            form.$setPristine();
        }
    };

    // function to update the current record
    $scope.update = function(form) {
        var item = $scope.item;

        if (item.name && item.email && item.phone) {
            $scope.makeRequest('put', '/api/contact/' + item.id, item).success(function(result) {
                $scope.toggleEdit();
                $scope.reset(form);
                updateItem(result.data);
                $scope.master = result.data;
                alertService.add('success', 'Contact item updated successfully');
            }).error(function(error) {
                alertService.add('warning', 'Failed to update, please re-check and try again');
            });
        }
    };

    // edit form visibility handler
    $scope.toggleEdit = function() {
        $scope.reset();
        $scope.isEditOpen = !$scope.isEditOpen;
    };

    // current item remove handler
    $scope.remove = function() {
        // NOTE: indexOf() works in IE 9+.
        var index = $scope.contacts.indexOf($scope.item);
        if (index >= 0) {
            if ($window.confirm('Do you really want to remove contact :: ' + $scope.item.name + ' ?')) {
                $scope.makeRequest('delete', '/api/contact/' + $scope.item.id).success(function() {
                    $scope.contacts.splice(index, 1);
                    alertService.add('success', 'Contact item deleted successfully');
                }).error(function(error) {
                    alertService.add('danger', 'Failed to remove, please re-check and try again');
                });
            }
        }
    };
});