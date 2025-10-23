/**
 * appointmentpro Home version 1 controllers
 */
angular
    .module('starter')
    .controller('AppointmentproHomeController', function (Dialog, Loader, Application, $ionicSideMenuDelegate, Customer,
                                             $session, Modal, $ionicScrollDelegate, $timeout, $ionicActionSheet, $filter,
                                             $ionicHistory, $translate, $rootScope, $ionicModal, Appointmentpro, Location, $scope, $state, $stateParams, $controller) {
        angular.extend(this, $controller('AppointmentproCommanController', {
	        Dialog: Dialog,
	        $rootScope: $rootScope,
	        $scope: $scope,
	        $stateParams: $stateParams,
	        Appointmentpro: Appointmentpro
	    }));

        $scope.value_id = Appointmentpro.value_id = $stateParams.value_id;
        $scope.widget = Appointmentpro.widget = $stateParams.widget;        
        $scope.settings = Appointmentpro.getAppointmentproSettings();
        Appointmentpro.cart = {};
        $scope.managerBoking = [];
        $scope.payout = {};
		$scope.postParams = {};
        $scope.activeTab = 'about';
        $scope.action = {
            tab: 'today'
        };
        $scope.can_load_older_manager_booking = true; 

        if(Appointmentpro.widget !== null && Appointmentpro.widget == '213848'){
           /* $ionicHistory.nextViewOptions({
                disableBack: true
            });*/
             $ionicSideMenuDelegate.canDragContent(false);
        }

        $scope.findLocationInfo = function () {           
            $scope.is_loading = true;
            //Loader.show();
            Appointmentpro.findLocationInfo($scope.payout.single_location_id).success(function (data) {
                $scope.location_info = data.location;
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


        $scope.loadContent = function () {
            $scope.managerBoking = [];
            $scope.is_loading = true;
            $scope.postParams.tab = $scope.action.tab;
            $scope.postParams.offset =  0;

	        //Loader.show();
	        Appointmentpro.findAll($scope.postParams).success(function (data) {
	            $scope.page_title = data.page_title;
	            $scope.payout = data;
	            $scope.settings = data.settings;
	            Appointmentpro.setAppointmentproSettings(data.settings); 
                Appointmentpro.setLabels(data.labels);
	            if(data.is_single_location){
                    $scope.findLocationInfo();  //Single Location find
                }else{
                    //Loader.hide();
                    $scope.is_loading = false; 
                }

                if($scope.payout.settings.providerInfo.is_provider_layout){
                    $scope.sub_title = data.sub_title;
                    $scope.can_load_older_manager_booking = !!data.bookingJson.length;
                    $scope.managerBoking = $scope.managerBoking.concat(data.bookingJson);
                }               
	             
	        }).error(function (error) {
	           // Loader.hide();
	            $scope.is_loading = false;  
	            Dialog.alert($translate.instant("Error", "appointmentpro") ,error.message , "OK", -1, "appointmentpro");         
	        }).finally(function () {          
	            $scope.$broadcast('scroll.infiniteScrollComplete');
	        });
        };
        
         $scope.getLocation = function() {
            if (Location.isEnabled && $scope.settings.default_location_sorting === 'distance') {
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
                if($scope.settings.default_location_sorting === 'distance'){
                    $scope.requestLocation();
                }                
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
            $scope.settings = Appointmentpro.getAppointmentproSettings();
            if($scope.settings.enable_location){
                $scope.getLocation();
            }else{
                $scope.loadContent();
            }          
        });

        $scope.loadManagerBooking = function(){
            $scope.loadContent();
        }


       /* Pagination load*/
        $scope.loadMoreManagerBooking = function () {
              if(!$scope.managerBoking.length){
                return false;
              }
             $scope.postParams.offset = $scope.managerBoking.length;
             Appointmentpro.findAll($scope.postParams).success(function (data) {
                 $scope.can_load_older_manager_booking = !!data.bookingJson.length;
                 $scope.managerBoking = $scope.managerBoking.concat(data.bookingJson);
             }).error(function (error ) {
                $scope.can_load_older_manager_booking = false;
                Dialog.alert($translate.instant("Error", "appointmentpro") ,error.message , "OK", -1, "appointmentpro");          
            }).finally(function () {          
                $scope.$broadcast('scroll.infiniteScrollComplete');
            });
        };

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

        // When entering the feature, we check the origin!
       // $scope.loadContent();   

        $scope.clickTabs = function (value) {
           $scope.activeTab = value;  
        }

        $scope.locationServiceModal = function (category_id) {
            $scope.location_service = []; 
            Loader.show();
            $ionicModal.fromTemplateUrl('features/appointmentpro/assets/templates/modal/location/service.html', {
                scope: $scope,
                animation: 'slide-in-right-left'        
            }).then(function(modal) {
                Appointmentpro
                .findLocationServiceByCategory($scope.payout.single_location_id, category_id)
                .success(function (data) {
                    $scope.location_service = data.services;
                }, function (error) {                
                    Dialog.alert($translate.instant("Error", "appointmentpro"), error.message, $translate.instant("Ok", "appointmentpro"), -1, 'appointmentpro');
                })
                .then(function () { // Finally!
                   Loader.hide();
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

        $scope.bookNow = function () {
            if(!Customer.isLoggedIn()){
                $scope.login(); return false;
            }

            console.log('location_info', $scope.location_info);
            
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



        $scope.updateStatus = function (bookingId, Status, bookingIndex) {            
            Loader.show();
            Appointmentpro.updateStatus(bookingId, Status).success(function (data) { 
                $scope.managerBoking[bookingIndex] = data.booking;         
                Loader.hide();
            }).error(function (error) {
                Loader.hide();
                Dialog.alert($translate.instant("Error", "appointmentpro") ,error.message , "OK", -1, "appointmentpro");         
           });              
        };

        /**
         * Redirect To location and category services
         */
        $scope.actionManagerBooking = function (booking, bookingIndex) {
            console.log('bookingIndex', bookingIndex);
            var buttonIndexing = [];
            var buttonPositon = 0;
            var sheetButtons =  [];
            
            sheetButtons[buttonPositon] = { text: $translate.instant("View", 'appointmentpro') };
            buttonIndexing[buttonPositon] = 'view';
            buttonPositon = buttonPositon + 1;
            if(!booking.is_accepted_hide && !booking.is_completed_hide && !booking.is_rejected_hide){
                sheetButtons[buttonPositon] = { text: $translate.instant("Accept", 'appointmentpro') };
                buttonIndexing[buttonPositon] = 'accept';
                buttonPositon = buttonPositon + 1;
            }            

            if(!booking.is_accepted_hide && !booking.is_completed_hide && !booking.is_rejected_hide){
                sheetButtons[buttonPositon] = { text: $translate.instant("Reject", 'appointmentpro') };
                buttonIndexing[buttonPositon] = 'reject';
                buttonPositon = buttonPositon + 1;
            }

            if(!booking.is_rejected_accepted_action && !booking.is_completed_hide && !booking.is_rejected_hide){
                sheetButtons[buttonPositon] = { text: $translate.instant("Mark As Complete", 'appointmentpro') };
                buttonIndexing[buttonPositon] = 'mark_as_complete';
                buttonPositon = buttonPositon + 1;
            }

            if(!booking.is_rejected_accepted_action && !booking.is_completed_hide && !booking.is_rejected_hide){
                sheetButtons[buttonPositon] = { text: $translate.instant("Cancel", 'appointmentpro') };
                buttonIndexing[buttonPositon] = 'cancel';
                buttonPositon = buttonPositon + 1;
            }

            if(!booking.is_completed_hide){
                sheetButtons[buttonPositon] = { text: $translate.instant("Delete", 'appointmentpro') };
                buttonIndexing[buttonPositon] = 'delete';
                buttonPositon = buttonPositon + 1;
            }

            // Show the action sheet
            var hideSheet = $ionicActionSheet.show({
                buttons: sheetButtons,
                cancelText: $translate.instant("Dismiss", "appointmentpro"),
                titleText: $translate.instant("Booking Actions", "appointmentpro"),
                cancel: function () {
                    hideSheet();
                },
                buttonClicked: function (index) {
                    if(buttonIndexing[index] == "view"){
                       $scope.bookingDetailsById(booking.appointment_id);
                    }
                    
                    if(buttonIndexing[index] == "accept"){
                        $scope.updateStatus(booking.appointment_id, 3, bookingIndex);
                    }

                    if(buttonIndexing[index] == "reject"){
                        $scope.updateStatus(booking.appointment_id, 8, bookingIndex);
                    }

                    if(buttonIndexing[index] == "mark_as_complete"){
                        $scope.updateStatus(booking.appointment_id, 4, bookingIndex);
                    }

                    if(buttonIndexing[index] == "cancel"){
                        $scope.updateStatus(booking.appointment_id, 6, bookingIndex);
                    }

                    if(buttonIndexing[index] == "delete"){
                        $scope.updateStatus(booking.appointment_id, 'delete', bookingIndex);
                    }

                    return true;
                }
            });

        }

    $scope.bookingDetailsById = function (booking_id) {
        $scope.is_loading_modal = true;
        Loader.show();
        $ionicModal.fromTemplateUrl('features/appointmentpro/assets/templates/l1/manager/modal/booking_detail.html', {
            scope: $scope,
            animation: 'slide-in-right-left'        
        }).then(function(modal) {
               
            Appointmentpro.findBookingDetailsById(booking_id).success(function (data) {            
                $scope.booking_info = data.booking;
                Loader.hide();
                $scope.is_loading_modal = false;
            }).error(function (error) {
                Loader.hide();
                $scope.is_loading_modal = false;
                Dialog.alert($translate.instant("Error", "appointmentpro") ,error.message , "OK", -1, "appointmentpro");         
                $scope.closeModalBookingDetails();
             
           });
           $scope.modalBookingDetails = modal;
           $scope.modalBookingDetails.show();
        });

    };

     /**
     * close info
     */
    $scope.closeModalBookingDetails = function () {
        $scope.modalBookingDetails.remove();
    }


});
