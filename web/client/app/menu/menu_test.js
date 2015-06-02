'use strict';

describe('clientApp.menu module', function() {

    var scope, ctrl;
    
    beforeEach(module('clientApp.menu'));
    beforeEach(module('clientApp.services'));

    describe('menu controller', function() {

        it('should ....', inject(function($controller, $rootScope) {
            scope = $rootScope.$new();
            ctrl = $controller('MenuCtrl', {
                $scope: scope
            });
            expect(ctrl).to.not.be.undefined;
        }));
        
         it('should have an initial logged out state', function() {
            expect(scope.isLoggedIn).to.be.undefined;
            expect(scope.username).to.be.undefined;
        });
        
        it('should ....', inject(function($controller, $rootScope, $httpBackend) {
            scope = $rootScope.$new();
            ctrl = $controller('MenuCtrl', {
                $scope: scope
            });
            
            $httpBackend
                .when('GET', '/session/logout')
                .respond({ type : 'success', data : []});
            
            scope.logout();
            $httpBackend.flush();
            
            expect(scope.user.username).to.be.equal('');
        }));

    });
});