/**
 * appointmentpro Location details version 1 controllers
 */
angular
    .module('starter')
    .controller('AppointmentproLocationDetailsController', function (Dialog, Loader, Application, $ionicSideMenuDelegate, Customer,
                                             $session, Modal, $ionicScrollDelegate, $timeout,
                                             $ionicHistory, $translate, $rootScope, Appointmentpro, $scope, $state, $stateParams, $controller, $ionicModal) {
       
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
        $scope.is_logged_in = Customer.isLoggedIn(); 
        $scope.payout = {};
        $scope.activeTab = 'about';
        Appointmentpro.cart = {};

        $scope.loadContent = function () {        	 
            $scope.is_loading = true;
	       // Loader.show();
	        Appointmentpro.findLocationInfo($scope.location_id).success(function (data) {
	            $scope.location_info = data.location;
	            //Loader.hide();
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


        $scope.clickTabs = function (value) {
           $scope.activeTab = value;  
        }

        $scope.descriptionInfo = function(description) {
            Dialog.alert($translate.instant("Description", "appointmentpro") ,description , "OK", 60000, "appointmentpro");         
        }

        $scope.locationServiceModal = function (category_id) {
            $scope.is_loading_modal = true;
            $scope.location_service = []; 
            $ionicModal.fromTemplateUrl('features/appointmentpro/assets/templates/modal/location/service.html', {
                scope: $scope,
                animation: 'slide-in-right-left'        
            }).then(function(modal) {
                Appointmentpro
                .findLocationServiceByCategory($scope.location_id, category_id)
                .success(function (data) {
                    $scope.location_service = data.services;
                }, function (error) {                
                    Dialog.alert($translate.instant("Error", "appointmentpro"), error.message, $translate.instant("Ok", "appointmentpro"), -1, 'appointmentpro');
                })
                .then(function () { // Finally!
                    $scope.is_loading_modal = false;
                });

               $scope.modalLocationService = modal;
               $scope.modalLocationService.show();
            });

        };

        /**
         * close info
         */
        $scope.closeModalLocationService = function () {
            $scope.modalLocationService.remove();
        }

         /**
         *login
         */
        $scope.login = function(){
            if (!Customer.isLoggedIn()) {
                Customer.loginModal($scope, function () { 
                    //Loader.show();
                    $timeout(function () {
                        $scope.is_logged_in = Customer.isLoggedIn();
                        $scope.customer = Customer.customer;
                        $state
                        .go('home')
                        .then(function () {
                            $state
                                .go("appointmentpro-home", { value_id: $scope.value_id }, { reload: true });
                        });
                    }, 1000);
                });
            }else{
                 Customer.loginModal();
            }
        }

        $scope.bookNow = function () {
           if(!Customer.isLoggedIn()){
                $scope.login(); return false;
           }
            
           $state.go("appointmentpro-category", { value_id: $scope.value_id, location_id: $scope.location_info.location_id }, { reload: true } );
        };


        $scope.bookAService = function (service) {

            if(!Customer.isLoggedIn()){
                $scope.login(); return false;
           }
           
            if(!$scope.settings.enable_booking){
                return false;
            }
            
            $scope.closeModalLocationService();
            $scope.cart = Appointmentpro.cart;
            $scope.cart.location_id = $scope.location_info.location_id;
            $scope.cart.category_id = service.category_id;
            $scope.cart.service_id = service.service_id;
            Appointmentpro.cart = $scope.cart;

            $state.go("appointmentpro-provider", { value_id: $scope.value_id, service_id: service.service_id }, { reload: true } );
        
        };
});