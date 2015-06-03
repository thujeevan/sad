'use strict';

describe('clientApp.main module', function() { 
    var user, alert;
    var $httpBackend, $rootScope, createController, bookRequest;

    beforeEach(module('clientApp.main'));
    beforeEach(module('clientApp.services'));
    
    beforeEach(inject(function($injector) {
        // Set up the mock http service responses
        $httpBackend = $injector.get('$httpBackend');
        user = $injector.get('userService');
        alert = $injector.get('alertService');
        
        bookRequest = $httpBackend.when('GET', '/api/contacts').respond(200, { type : 'success', data : []});

        // Get hold of a scope (i.e. the root scope)
        $rootScope = $injector.get('$rootScope');
        // The $controller service is used to create instances of controllers
        var $controller = $injector.get('$controller');

        createController = function(name) {
            return $controller(name, {'$scope': $rootScope});
        };
    }));
    
    afterEach(function() {
        $httpBackend.verifyNoOutstandingExpectation();
        $httpBackend.verifyNoOutstandingRequest();
    });

    describe('main controller get book', function() {
        it('should have initial empty state', function(){
            $httpBackend.expectGET('/api/contacts').respond(200, { type : 'success', data : []});
            var controller = createController('MainCtrl'); 
            $httpBackend.flush();
            
            expect($rootScope.contacts.length).to.be.equal(0);            
        });
        
        it('should call api for getting address books', function(){
            $httpBackend.expectGET('/api/contacts').respond(200, { type : 'success', data : [{}]});
            var controller = createController('MainCtrl');
            $httpBackend.flush();
        });
        
        it('should update contacts after resolving to remote data', function(){
            $httpBackend.expectGET('/api/contacts').respond(200, { type : 'success', data : [{}, {}]});
            var controller = createController('MainCtrl');
            $httpBackend.flush();
            expect($rootScope.contacts.length).to.be.equal(2);  
        });
    });
    
    describe('main controller create new', function() {
        it('should not create new record if validation failed', function(){
            var controller = createController('MainCtrl');
            $httpBackend.flush();
            
            expect($rootScope.contacts.length).to.be.equal(0);            
        });
        
        it('should call api for create new record', function(){
            var data = {
                name : 'test',
                email : 'test@email.com',
                phone : '123213213123'
            };
            
            var controller = createController('MainCtrl');
            
            $httpBackend.expectPOST('/api/contacts').respond(200, { type : 'success', data : [data]});
            $rootScope.contact = data;
            $rootScope.createNew();
            $httpBackend.flush();
        });
        
        it('should update contatcts when remote creation is success', function(){
            var data = {
                id : 1,
                name : 'test',
                email : 'test@email.com',
                phone : '123213213123'
            };
            
            var controller = createController('MainCtrl');
            
            $httpBackend.expectPOST('/api/contacts').respond(200, { type : 'success', data : data});
            $rootScope.contact = data;
            $rootScope.createNew();
            $httpBackend.flush();
            
            expect($rootScope.contacts.length).to.be.equal(1);
            expect($rootScope.contacts[0].name).to.be.equal('test');
        });
    });
    
    describe('main module contact/item controller', function() {        
        it('should update with valid data', function(){
            var parent = createController('MainCtrl');
            $httpBackend.flush();
            
            var controller = createController('ContactCtrl');            
            $rootScope.item = {
                id : 1,
                name : 'test',
                email : 'test@email.com',
                phone : '123213213123'
            };
            var updated = angular.copy($rootScope.item);
            updated.name = 'test updated';
            
            $httpBackend.expectPUT('/api/contact/1').respond(200, { type : 'success', data : updated });
            $rootScope.update();
            $httpBackend.flush();
            
            expect($rootScope.item.name).to.be.equal('test updated');
        });
        
        it('should update with valid data', function(){
            var parent = createController('MainCtrl');
            $httpBackend.flush();
            
            var controller = createController('ContactCtrl');            
            $rootScope.item = {
                id : 1,
                name : 'test',
                email : 'test@email.com',
                phone : '123213213123'
            };
            $rootScope.contacts.push($rootScope.item);
            expect($rootScope.contacts.length).to.be.equal(1);
            
            $httpBackend.expectDELETE('/api/contact/1').respond(200, { type : 'success', data : {} });
            $rootScope.remove();
            $httpBackend.flush();
            
            expect($rootScope.contacts.length).to.be.equal(0);
        });
    });
});