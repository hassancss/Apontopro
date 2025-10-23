/**
 * appointmentpro provider version 1 controllers
 */
angular
    .module('starter')
    .controller('AppointmentproProviderController', function (Dialog, Loader, Application, $ionicSideMenuDelegate, Customer,
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
        $scope.service_id = $stateParams.service_id;
        $scope.payout = {};
        $scope.page_title = '';

       /*Cart Update*/
        $scope.cart = Appointmentpro.cart;
        $scope.cart.service_id = $scope.service_id;
        console.log("Cart -> ", $scope.cart);

         $scope.descriptionInfo = function(description) {
            Dialog.alert($translate.instant("Description", "appointmentpro") ,description , "OK", 60000, "appointmentpro");         
        }
        
        $scope.loadContent = function () {
            $scope.is_loading = true;
            //Loader.show();
            Appointmentpro.findServiceProvider($scope.cart).success(function (data) {            
                $scope.payout = data;
                $scope.is_single = false;

                if(data.providers.length == 1 && (Appointmentpro.isUndefined($scope.cart.provider_id) || $scope.cart.provider_id == 0 )){
                    $state.go("appointmentpro-calendar", { value_id: $scope.value_id, provider_id : data.providers[0].provider_id }, { reload: true } );
                    $scope.is_single = true;
                }

                if((!Appointmentpro.isUndefined($scope.cart.provider_id) || $scope.cart.provider_id > 0) && data.providers.length == 1 ){
                    $ionicHistory.goBack(); // Back to location if category lenth is 1
                    $scope.is_single = true;
                    delete $scope.cart.provider_id;
                }
                
                if(!$scope.is_single){
                    $scope.is_loading = false; 
                    $scope.page_title = $translate.instant("Select Provider", "appointmentpro"); 
                }  
            }).error(function (error) {
               // Loader.hide();
                $scope.is_loading = false;  
                Dialog.alert($translate.instant("Error", "appointmentpro") ,error.message , "OK", -1, "appointmentpro");         
            }).finally(function () {          
                $scope.$broadcast('scroll.infiniteScrollComplete');
            });
        };
        
        // When entering the feature, we check the origin!
        $scope.loadContent();

});