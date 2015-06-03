'use strict';

describe('clientApp.login module', function() {    
    var user;
    var $httpBackend, $rootScope, $q, createController, authRequestHandler;
    
    beforeEach(module('clientApp.login'));
    beforeEach(module('clientApp.services'));

    beforeEach(inject(function($injector) {
        // Set up the mock http service responses
        $httpBackend = $injector.get('$httpBackend');
        user = $injector.get('userService');

        // Get hold of a scope (i.e. the root scope)
        $rootScope = $injector.get('$rootScope');
        // The $controller service is used to create instances of controllers
        var $controller = $injector.get('$controller');
        $q = $injector.get('$q');

        createController = function() {
            return $controller('LoginCtrl', {'$scope': $rootScope });
        };
    }));

    describe('login controller', function() {  
        it('should have an initial logged out state', inject(function($httpBackend) {
            expect(user.username).to.be.equal('');
        }));
        
        it('should send login request to server', function() {
            var controller = createController();
            $rootScope.user = {
                email : 'test@email.com',
                password : '1234'
            };

            $httpBackend.expectPOST('/session/login_check').respond(200, '');
            
            $rootScope.login();
            $httpBackend.flush();
            expect(user.username).to.be.equal('');
        });
        
        it('should send login request to server in proper format', function() {
            var controller = createController();
            $rootScope.user = {
                email : 'test@email.com',
                password : '1234'
            };

            $httpBackend.expectPOST('/session/login_check').respond(200, { type : 'success', data : { username : 'test' }});
            
            $rootScope.login();
            $httpBackend.flush();
            expect(user.username).to.be.equal('test');
        });        
    });
});