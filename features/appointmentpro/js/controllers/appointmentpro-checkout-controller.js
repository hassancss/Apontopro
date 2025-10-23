/**
 * appointmentpro checkout version 1 controllers
 */
angular
    .module('starter')
    .controller('AppointmentproCheckoutController', function (Dialog, Loader, Application, $ionicSideMenuDelegate, Customer,
                                             $session, Modal, $ionicScrollDelegate, $timeout, $ionicModal, 
                                             $ionicHistory, $translate, $rootScope, Appointmentpro, $scope, $state, $stateParams, $controller, $compile) {
    angular.extend(this, $controller('AppointmentproCommanController', {
        Dialog: Dialog,
        $rootScope: $rootScope,
        $scope: $scope,
        $stateParams: $stateParams,
        Appointmentpro: Appointmentpro
    }));
    
    $scope.value_id = Appointmentpro.value_id = $stateParams.value_id;
    $scope.settings = Appointmentpro.getAppointmentproSettings();
    $scope.is_logged_in = Customer.isLoggedIn();

    $scope.payout = {};
    $scope.cart = Appointmentpro.cart;
 
    if(Customer.isLoggedIn()) {
        $scope.customer = Customer.customer;
        $scope.cart.customer = Customer.customer;
        $scope.cart.phone_number = Customer.mobile;;
    }else{
        $scope.cart.customer = {
            firstname: '',
            lastname: '',
            email: '',
            phone_number: ''
        }
    }

   
    $scope.confirmModal = function () {
        $scope.is_loading_modal = true;
        //Loader.show();
        $ionicModal.fromTemplateUrl('features/appointmentpro/assets/templates/modal/booking/confirm.html', {
            scope: $scope,
            animation: 'slide-in-right-left'        
        }).then(function(modal) {
            Appointmentpro
            .findConfirmBookingData($scope.cart)
            .success(function (data) {
                $scope.booking_info = data.data;
                $scope.cart.details = data.data;
                $scope.is_loading_modal = false;
            }, function (error) {                
                Dialog.alert($translate.instant("Error", "appointmentpro"), error.message, $translate.instant("Ok", "appointmentpro"), -1, 'appointmentpro');
            })
            .then(function () { // Finally!
               //Loader.hide();
            });

           $scope.modalConfirmBooking = modal;
           $scope.modalConfirmBooking.show();
        });

    };

    /**
     * close info
     */
    $scope.closeModalConfirmBooking = function () {
        $scope.modalConfirmBooking.remove();
    }


    /**
     *login
     */
    $scope.login = function(){ 
         
        if (!Customer.isLoggedIn()) {
            Customer.loginModal($scope, function () {
                $scope.is_logged_in = Customer.isLoggedIn();
            });
        } 
    }

    $scope.avatarUrl = function () {
        // Means the customer image was edited!
        if ($scope.customer.image &&
            $scope.customer.image.indexOf('data:') === 0) {
            return $scope.customer.image;
        }
        // Else we fetch it normally, first customer defined, then default image!
        return Customer.getAvatarUrl();
    };


    $scope.continueOrder = function () {
         
        $scope.closeModalConfirmBooking();
        Appointmentpro.cart = $scope.cart;
        setTimeout(function() {
           $state.go("appointmentpro-payment", { value_id: $scope.value_id }, { reload: true } );
        }, 300);
    };

    //Submit wihtout payment
    $scope.submitOrderWithoutPay = function () {
        $scope.cart.payment_method = '';
        Appointmentpro.cart = $scope.cart;
        Loader.show();
        Appointmentpro
            .bookingSubmit($scope.cart)
            .then(function (data) {  
                $scope.closeModalConfirmBooking();             
                if(!data.booking_status){
                        Dialog.alert($translate.instant("Error", "appointmentpro"), data.message, $translate.instant("OK", "appointmentpro"));
                }else{
                    Dialog.alert($translate.instant("Success", "appointmentpro"), data.message, $translate.instant("OK", "appointmentpro"));
                }                
                $state
                .go("home", { value_id: $scope.value_id }, { reload: true })
                .then(function () {
                    $state.go("appointmentpro-home", { value_id: $scope.value_id }, { reload: true }).then(function(){
                        Loader.hide();
                        $state.go("appointmentpro-booked-success", { value_id: $scope.value_id, booking_id: data.booking_id  }, { reload: true } );
                    });
                });
            });
    }




});