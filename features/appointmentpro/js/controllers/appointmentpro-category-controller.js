/**
 * appointmentpro Category version 1 controllers
 */
angular
    .module('starter')
    .controller('AppointmentproCategoryController', function (Dialog, Loader, Application, $ionicSideMenuDelegate, Customer,
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
        $scope.location_id = $stateParams.location_id;
        $scope.payout = {};
        $scope.page_title = '';
        
        /*Cart Update*/
        $scope.cart = Appointmentpro.cart;
        $scope.cart.location_id = $scope.location_id;
      
        $scope.loadContent = function () {
            $scope.is_loading = true;
	        //Loader.show();
            Appointmentpro.findCategoryByLocation($scope.location_id).success(function (data) {	           
	            $scope.payout = data;
	            $scope.is_single = false;
	            if(data.categories.length == 1 && (Appointmentpro.isUndefined($scope.cart.category_id) || $scope.cart.category_id === 0 )){
	            	$state.go("appointmentpro-service", { value_id: $scope.value_id, category_id : data.categories[0].main_category_id }, { reload: true } );
	           		$scope.is_single = true;
	            }
	            if((!Appointmentpro.isUndefined($scope.cart.category_id) || $scope.cart.category_id > 0) && data.categories.length == 1 ){
		        	$ionicHistory.goBack(); // Back to location if category lenth is 1
		        	$scope.is_single = true;
		        	delete $scope.cart.category_id;
		        }		        

		        if(!$scope.is_single){
		         	$scope.is_loading = false; 
	            	$scope.page_title = $translate.instant("Select Category", "appointmentpro"); 
	            }  
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

});
