'use strict';

describe('clientApp.login module', function() {    
    var user;
    var $httpBackend, $rootScope, createController, authRequestHandler;
    
    beforeEach(module('clientApp.login'));
    beforeEach(module('clientApp.services'));

    beforeEach(inject(function($injector) {
        // Set up the mock http service responses
        $httpBackend = $injector.get('$httpBackend');
        user = $injector.get('userService');
        
        // backend definition common for all tests
        authRequestHandler = $httpBackend.when('GET', '/session/isauthenticated')
                .respond(401, {type : 'failed', reason : 'not authenticated'});

        // Get hold of a scope (i.e. the root scope)
        $rootScope = $injector.get('$rootScope');
        // The $controller service is used to create instances of controllers
        var $controller = $injector.get('$controller');

        createController = function() {
            return $controller('LoginCtrl', {'$scope': $rootScope});
        };
    }));

    describe('login controller', function() {  
        it('should have an initial logged out state', inject(function($httpBackend) {
            expect(user.username).to.be.equal('');
        }));
        
        it('should call backend to check authentication', inject(function($httpBackend) {
            $httpBackend.expectGET('/session/isauthenticated');
            var controller = createController();
            $httpBackend.flush();
        }));
        
        it('should fail authentication', function() {
            $httpBackend.expectGET('/session/isauthenticated');
            var controller = createController();
            $httpBackend.flush();
            expect(user.username).to.be.equal('');
        });
        
        it('should send login request to server', function() {
            var controller = createController();
            $httpBackend.flush();

            $httpBackend.expectPOST('/session/login').respond(200, '');
            
            $rootScope.login();
            $httpBackend.flush();
            expect(user.username).to.be.equal('');
        });
        
        it('should send login request to server in proper format', function() {
            var controller = createController();
            $httpBackend.flush();

            $httpBackend.expectPOST('/session/login', {
                email : controller.email,
                password : controller.password
            }).respond(200, { type : 'success', data : { username : 'test' }});
            
            $rootScope.login();
            $httpBackend.flush();
            expect(user.username).to.be.equal('test');
        });        
    });
});