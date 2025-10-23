/**
 * appointmentpro Location version 1 controllers
 */
angular
    .module('starter')
    .controller('AppointmentproLocationController', function (Dialog, Loader, Customer,
                                             $session, Modal, $timeout, $translate, $rootScope, Appointmentpro, Location, $scope, $state, $stateParams, $controller) {
        angular.extend(this, $controller('AppointmentproCommanController', {
	        Dialog: Dialog,
	        $rootScope: $rootScope,
	        $scope: $scope,
	        $stateParams: $stateParams,
	        Appointmentpro: Appointmentpro
	    }));

        $scope.value_id = Appointmentpro.value_id = $stateParams.value_id;
        $scope.settings = Appointmentpro.getAppointmentproSettings();
        $scope.payout = {};
        $scope.postParams = {};
        
	    $scope.loadContent = function () {
            $scope.is_loading = true;
	        //Loader.show();
	        Appointmentpro.findAllLocations($scope.postParams).success(function (data) {
	            $scope.payout = data;
	           // Loader.hide();
	            $scope.is_loading = false;    
	        }).error(function (error) {
	            //Loader.hide();
	            $scope.is_loading = false;  
	            Dialog.alert($translate.instant("Error", "appointmentpro") ,error.message , "OK", -1, "appointmentpro");         
	        }).finally(function () {          
	            $scope.$broadcast('scroll.infiniteScrollComplete');
	        });
        };
        
        // When entering the feature, we check the origin!
        $scope.loadContent();


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


}).controller('AppointmentproLocationByCategoryController', function (Dialog, Loader, Customer,
                                             $session, Modal, $timeout, $translate, $rootScope, Appointmentpro, Location, $scope, $state, $stateParams, $controller) {
        angular.extend(this, $controller('AppointmentproCommanController', {
            Dialog: Dialog,
            $rootScope: $rootScope,
            $scope: $scope,
            $stateParams: $stateParams,
            Appointmentpro: Appointmentpro
        }));

        $scope.value_id = Appointmentpro.value_id = $stateParams.value_id;
        $scope.category_id =  $stateParams.category_id;
        $scope.settings = Appointmentpro.getAppointmentproSettings();
        $scope.payout = {};
        $scope.postParams = {};
        
        $scope.loadContent = function () {
            $scope.is_loading = true;
            //Loader.show();
            Appointmentpro.findLocationByCategoryId($scope.category_id, $scope.postParams).success(function (data) {
                $scope.payout = data;
                Loader.hide();
                $scope.is_loading = false;    
            }).error(function (error) {
                //Loader.hide();
                $scope.is_loading = false;  
                Dialog.alert($translate.instant("Error", "appointmentpro") ,error.message , "OK", -1, "appointmentpro");         
            }).finally(function () {          
                $scope.$broadcast('scroll.infiniteScrollComplete');
            });
        };
        
        // When entering the feature, we check the origin!
        $scope.loadContent();


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


});