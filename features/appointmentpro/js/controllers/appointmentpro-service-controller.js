/**
 * appointmentpro Category version 1 controllers
 */
angular
    .module('starter')
    .controller('AppointmentproServiceController', function (Dialog, Loader, Application, $ionicSideMenuDelegate, Customer,
                                             $session, Modal, $ionicScrollDelegate, $timeout,
                                             $ionicHistory, $translate, $rootScope, Appointmentpro, $scope, $state, $stateParams, $controller) {
        angular.extend(this, $controller('AppointmentproCommanController', {
	        Dialog: Dialog,
	        $rootScope: $rootScope,
	        $scope: $scope,
	        $stateParams: $stateParams,
	        Appointmentpro: Appointmentpro
	    }));

        $scope.value_id = Appointmentpro.value_id = $stateParams.value_id;
        $scope.settings = Appointmentpro.getAppointmentproSettings();
        $scope.category_id = $stateParams.category_id;
        $scope.payout = {};
        $scope.page_title = '';

        /*Cart Update*/
        $scope.cart = Appointmentpro.cart;
        $scope.cart.category_id = $scope.category_id;
                
        $scope.loadContent = function () {
            $scope.is_loading = true;
	        //Loader.show();
	        Appointmentpro.findServiceByLocationCategory($scope.cart).success(function (data) {	           
	            $scope.payout = data;
	            $scope.is_single = false;
	            if(data.services.length == 1 && (Appointmentpro.isUndefined($scope.cart.service_id) || $scope.cart.service_id === 0 )){
	            	$state.go("appointmentpro-provider", { value_id: $scope.value_id, service_id : data.services[0].service_id }, { reload: true } );
	            	$scope.is_single = true;
	            }
	            if((!Appointmentpro.isUndefined($scope.cart.service_id) || $scope.cart.service_id > 0) && data.services.length == 1 ){
		        	$ionicHistory.goBack(); // Back to location if category lenth is 1
		        	$scope.is_single = true;
		         	delete $scope.cart.service_id;
		        }

		        if(!$scope.is_single){
		        	$scope.is_loading = false;   
	            	$scope.page_title = $translate.instant("Select Service", "appointmentpro");
		        }
		        
	             
	        }).error(function (error) {
	          //  Loader.hide();
	            $scope.is_loading = false;  
	            Dialog.alert($translate.instant("Error", "appointmentpro") ,error.message , "OK", -1, "appointmentpro");         
	        }).finally(function () {          
	            $scope.$broadcast('scroll.infiniteScrollComplete');
	        });
        };

        $scope.descriptionInfo = function(description) {
        	Dialog.alert($translate.instant("Description", "appointmentpro") ,description , "OK", 60000, "appointmentpro");         
	    }

		$scope.goTo = function(service_id) {
       		$state.go("appointmentpro-provider", { value_id: $scope.value_id, service_id: service_id  }, { reload: true } );
        }	    
        
        // When entering the feature, we check the origin!
        $scope.loadContent();

}).controller('AppointmentproProviderServiceController', function (Dialog, Loader, Application, $ionicSideMenuDelegate, Customer,
                                             $session, Modal, $ionicScrollDelegate, $timeout,
                                             $ionicHistory, $translate, $rootScope, Appointmentpro, $scope, $state, $stateParams, $controller) {
        angular.extend(this, $controller('AppointmentproCommanController', {
	        Dialog: Dialog,
	        $rootScope: $rootScope,
	        $scope: $scope,
	        $stateParams: $stateParams,
	        Appointmentpro: Appointmentpro
	    }));

        $scope.value_id = Appointmentpro.value_id = $stateParams.value_id;
        $scope.provider_id = Appointmentpro.provider_id = $stateParams.provider_id;
        $scope.settings = Appointmentpro.getAppointmentproSettings();
        $scope.payout = {};

        /*Cart Update*/
        $scope.cart = Appointmentpro.cart;
         
        $scope.loadContent = function () {
            $scope.is_loading = true;
	        //Loader.show();
	        Appointmentpro.findProviderServices($scope.cart).success(function (data) {	           
	            $scope.payout = data;
	          //  Loader.hide();
	            $scope.is_loading = false;    
	        }).error(function (error) {
	          //  Loader.hide();
	            $scope.is_loading = false;  
	            Dialog.alert($translate.instant("Error", "appointmentpro") ,error.message , "OK", -1, "appointmentpro");         
	        }).finally(function () {          
	            $scope.$broadcast('scroll.infiniteScrollComplete');
	        });
        };
        
        // When entering the feature, we check the origin!
        $scope.loadContent();



});