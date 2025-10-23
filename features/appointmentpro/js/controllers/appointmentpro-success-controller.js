/**
 * appointmentpro success version 1 controllers
 */
angular
    .module('starter')
    .controller('AppointmentproSuccessController', function (Dialog, Loader, $controller, Application, Customer, $session, Modal, $timeout, $translate, $rootScope, Appointmentpro, $scope, $state, $stateParams) {
    angular.extend(this, $controller('AppointmentproCommanController', {
            Dialog: Dialog,
            $rootScope: $rootScope,
            $scope: $scope,
            $stateParams: $stateParams,
            Appointmentpro: Appointmentpro
        }));

    $scope.value_id = Appointmentpro.value_id = $stateParams.value_id;
    $scope.settings = Appointmentpro.getAppointmentproSettings();
    $scope.booking_id = $stateParams.booking_id;
    $scope.payout = {};
    $scope.service_type = 'services';

    $scope.loadContent = function () {
        $scope.is_loading = true;
       // Loader.show();
        Appointmentpro.bookingDetails($scope.booking_id).success(function (data) {            
            $scope.payout = data;
            if($scope.payout.booking.is_it_class == 1){
                $scope.service_type = 'classes';
            }
            $scope.is_loading = false;
          //  Loader.hide();    
        }).error(function (error) {
           // Loader.hide();
            $scope.is_loading = false;  
            Dialog.alert($translate.instant("Error", "appointmentpro") ,error.message , "OK", -1, "appointmentpro");         
        }).finally(function () {          
            $scope.$broadcast('scroll.infiniteScrollComplete');
        });
    };

    $scope.loadContent();


    $scope.goToBookingDetails = function () {
        $state.go("home", { value_id: $scope.value_id }, { reload: true })
            .then(function () {
                $state.go("appointmentpro-home", { value_id: $scope.value_id }, { reload: true }).then(function(){
                    Loader.hide();
                    $state.go("appointmentpro-booking", { value_id: $scope.value_id, type: 'upcoming', service_type: $scope.service_type, booking_id: $scope.payout.booking.appointment_id  }, { reload: true } );
                });
            });

    };


});