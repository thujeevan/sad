'use strict';

angular.module('clientApp.services', []).factory('userService', function($q, $http) {
    var username = '';

    return {
        isAuthenticated: function() {
            var deferred = $q.defer();
            var _this = this;
            if (this.username) {
                deferred.resolve(this.username);
            } else {
                $http.get('/session/isauthenticated')
                        .success(function(result) {
                            _this.username = result.data.username;
                            deferred.resolve(result);
                        })
                        .error(function(result) {
                            deferred.reject(result);
                        });
            }

            return deferred.promise;
        },
        username: username
    };
}).factory('alertService', function($timeout) {
    var ALERT_TIMEOUT = 5000;
    
    function add(type, msg, timeout) {
        if (timeout) {
            $timeout(function() {
                closeAlert(this);
            }, timeout);
        } else {
            $timeout(function() {
                closeAlert(this);
            }, ALERT_TIMEOUT);
        }

        return alerts.push({
            type: type,
            msg: msg,
            close: function() {
                return closeAlert(this);
            }
        });
    }

    function closeAlert(alert) {
        return closeAlertIdx(alerts.indexOf(alert));
    }

    function closeAlertIdx(index) {
        return alerts.splice(index, 1);
    }

    function clear() {
        alerts = [];
    }

    function get() {
        return alerts;
    }

    var service = {
        add: add,
        closeAlert: closeAlert,
        closeAlertIdx: closeAlertIdx,
        clear: clear,
        get: get
    },
    alerts = [];

    return service;
}
).controller('AlertsCtrl', function($scope, alertService) {
    $scope.alerts = alertService.get();
});