/**
 * appointmentpro classes version 1 controllers
 */
angular
    .module('starter')
    .controller('AppointmentproClassesController', function (Dialog, Loader, Application, Customer,
                                             $session, Modal, $timeout, $translate, $ionicModal, $rootScope, Location, Appointmentpro, $scope, $state, $stateParams, $controller) {
        angular.extend(this, $controller('AppointmentproCommanController', {
	        Dialog: Dialog,
	        $rootScope: $rootScope,
	        $scope: $scope,
	        $stateParams: $stateParams,
	        Appointmentpro: Appointmentpro
	    }));

        $scope.value_id = Appointmentpro.value_id = $stateParams.value_id;
        $scope.category_id = $stateParams.category_id;
        $scope.settings = Appointmentpro.getAppointmentproSettings();
        $scope.payout = {};
        $scope.postParams = {};
        
        $scope.loadContent = function () {
            $scope.postParams.category_id = $scope.category_id;
            $scope.is_loading = true;
           // Loader.show();
            Appointmentpro.findAllClasses($scope.postParams).success(function (data) {            
                $scope.payout = data;
               // Loader.hide();
                $scope.is_loading = false;    
            }).error(function (error) {
               // Loader.hide();
                $scope.is_loading = false;  
                Dialog.alert($translate.instant("Error", "appointmentpro") ,error.message , "OK", -1, "appointmentpro");         
            });
        };


        $scope.getLocation = function() {

            if (Location.isEnabled) {
                Location
                    .getLocation({timeout: 10000}, true)
                    .then(function (position) {
                        $scope.postParams.latitude = position.coords.latitude;
                        $scope.postParams.longitude = position.coords.longitude;
                        $scope.loadContent();
                    }, function () {
                        $scope.postParams.latitude = '';
                        $scope.postParams.longitude = '';
                        $scope.requestLocation();
                    });

            } else {
                $scope.postParams.latitude = '';
                $scope.postParams.longitude = '';
                $scope.requestLocation();
                $scope.loadContent();
            }
        }

        $scope.requestLocation = function(){
            Location.requestLocation(function () {
                $scope.loadContent();
            }, function () {
                $scope.loadContent();
            }); 
        }


        
        $scope.$on("$ionicView.beforeEnter", function(event, data) {  
           $scope.getLocation();
        });        
       

}).controller('AppointmentproBookAClassController', function (Dialog, Loader, Application, $ionicSideMenuDelegate, Customer,
                                             $session, Modal, $ionicScrollDelegate, $timeout, $ionicModal, 
                                             SB, $translate, $rootScope, Appointmentpro,$ionicPopup, $scope, $state, $stateParams, $controller, $compile) {
    angular.extend(this, $controller('AppointmentproCommanController', {
        Dialog: Dialog,
        $rootScope: $rootScope,
        $scope: $scope,
        $stateParams: $stateParams,
        Appointmentpro: Appointmentpro
    }));

    $scope.value_id = Appointmentpro.value_id = $stateParams.value_id;
    $scope.cart = Appointmentpro.cart;
    $scope.settings = Appointmentpro.getAppointmentproSettings();
     
 });