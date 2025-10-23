/**
 * appointmentpro Home version 2 controllers
 */
angular
    .module('starter')
    .controller('AppointmentproManagerBookingController', function (Dialog, Loader, Application, $ionicSideMenuDelegate, Customer,
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
        $scope.tab = $stateParams.tab;
        $scope.service_type = $stateParams.service_type;
        $scope.settings = Appointmentpro.getAppointmentproSettings();
        $scope.managerBoking = [];
        $scope.payout = {};
		$scope.postParams = {};
        $scope.page_title = $translate.instant("Bookings", "appointmentpro");
        $scope.can_load_older_manager_booking = true;


        $scope.loadContent = function () {
            $scope.managerBoking = [];
            $scope.is_loading = true;
            $scope.postParams.tab = $scope.tab;
            $scope.postParams.offset =  0;
            $scope.postParams.service_type = $scope.service_type;

            Appointmentpro.findManagerBooking($scope.postParams).success(function (data) {                
                $scope.payout = data;
                $scope.can_load_older_manager_booking = !!data.bookings.length;
                $scope.managerBoking =  data.bookings;
                $scope.is_loading = false;
            }).error(function (error) {
                $scope.is_loading = false;  
                Dialog.alert($translate.instant("Error", "appointmentpro") ,error.message , "OK", -1, "appointmentpro");         
            }).finally(function () {          
                $scope.$broadcast('scroll.infiniteScrollComplete');
            });
        };
   
        $scope.loadContent();



   /* Pagination load*/
    $scope.loadMoreManagerBooking = function () {
          if(!$scope.managerBoking.length){
            return false;
          }
         $scope.postParams.offset = $scope.managerBoking.length;
         Appointmentpro.findManagerBooking($scope.postParams).success(function (data) {
             $scope.can_load_older_manager_booking = !!data.bookings.length;
             $scope.managerBoking = $scope.managerBoking.concat(data.bookings);
         }).error(function (error ) {
            $scope.can_load_older_manager_booking = false;
            Dialog.alert($translate.instant("Error", "appointmentpro") ,error.message , "OK", -1, "appointmentpro");          
        }).finally(function () {          
            $scope.$broadcast('scroll.infiniteScrollComplete');
        });
    };

     /**
        * Serach booking
        */
        $scope.loadSearchBooking = function(){
            $timeout(function () {
                $scope.managerBoking = [];
                $scope.postParams.search  = document.getElementById("bookingSearch").value;                  
                $scope.loadContent();           
            }, 1000);
        }

    
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


          
      $scope.updatePaymentStatus = function (bookingId, Status, bookingIndex) {            
        Loader.show();
        Appointmentpro.updatePaymentStatus(bookingId, Status).success(function (data) { 
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

            if(!booking.is_rejected_accepted_action && !booking.is_payment_hide){
                sheetButtons[buttonPositon] = { text: $translate.instant("Mark As Paid", 'appointmentpro') };
                buttonIndexing[buttonPositon] = 'mark_as_paid';
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

                    if(buttonIndexing[index] == "mark_as_paid"){
                        $scope.updatePaymentStatus(booking.appointment_id, 2, bookingIndex);
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