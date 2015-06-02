'use strict';

describe('clientApp.signup module', function() {    
    var user, alert;
    var $httpBackend, $rootScope, createController, authRequestHandler;
    
    beforeEach(module('clientApp.signup'));
    beforeEach(module('clientApp.services'));

    beforeEach(inject(function($injector) {
        // Set up the mock http service responses
        $httpBackend = $injector.get('$httpBackend');
        user = $injector.get('userService');
        alert = $injector.get('alertService');
        
        // backend definition common for all tests
        authRequestHandler = $httpBackend.when('GET', '/session/isauthenticated')
                .respond(401, {type : 'failed', reason : 'not authenticated'});

        // Get hold of a scope (i.e. the root scope)
        $rootScope = $injector.get('$rootScope');
        // The $controller service is used to create instances of controllers
        var $controller = $injector.get('$controller');

        createController = function() {
            return $controller('SignupCtrl', {'$scope': $rootScope});
        };
    }));

    describe('login controller', function() {  
        it('should send signup request to server', function() {
            var controller = createController();
            $httpBackend.expectPOST('/session/signup').respond(200, '');
            
            $rootScope.signup();
            $httpBackend.flush();
        });
        
        it('should send signup request to server in proper format', function() {
            var controller = createController();
            $rootScope.email = 'test@email.com';
            $rootScope.password = '1234';
            
            $httpBackend.expectPOST('/session/signup', {
                email : $rootScope.email,
                password : $rootScope.password
            }).respond(200, { type : 'success', data : { username : $rootScope.email }});
            
            $rootScope.signup();
            $httpBackend.flush();
            expect(user.username).to.be.equal($rootScope.email);
        });
        
        it('should populate error messages properly', function() {
            var controller = createController();
            
            $httpBackend.expectPOST('/session/signup', {
                email : $rootScope.email,
                password : $rootScope.password
            }).respond(400, { type : 'falied', reason : 'invalid username or password'});
            
            $rootScope.signup();
            $httpBackend.flush();
            expect(alert.get().length).to.not.be.equal(0);
            expect(alert.get().length).to.be.equal(1);
        });        
    });
});