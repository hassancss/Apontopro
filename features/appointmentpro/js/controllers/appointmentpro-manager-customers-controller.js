/**
 * appointmentpro Home version 2 controllers
 */
angular
    .module('starter')
    .controller('AppointmentproManagerCustomersController', function (Dialog, Loader, Application, $ionicSideMenuDelegate, Customer,
                                             $session, Modal, $ionicScrollDelegate, $timeout, $ionicActionSheet, $filter,
                                             $ionicHistory, $translate, $rootScope, $ionicModal, Appointmentpro, Location, $scope, $state, $stateParams, $controller, LinkService) {
        angular.extend(this, $controller('AppointmentproCommanController', {
	        Dialog: Dialog,
	        $rootScope: $rootScope,
	        $scope: $scope,
	        $stateParams: $stateParams,
	        Appointmentpro: Appointmentpro
	    }));

        $scope.value_id = Appointmentpro.value_id = $stateParams.value_id;        
        $scope.settings = Appointmentpro.getAppointmentproSettings();
        $scope.managerCustomers = [];
        $scope.payout = {
            search_text: '',
        };
		$scope.postParams = {};
        $scope.page_title = $translate.instant("Customers", "appointmentpro");
        $scope.can_load_older_manager_customers = true;


        $scope.loadContent = function () {
            $scope.managerCustomers = [];
            $scope.is_loading = true;
            $scope.postParams.offset =  0;

            Appointmentpro.findManagerCustomers($scope.postParams).success(function (data) {
                $scope.payout = data;
                $scope.can_load_older_manager_customers = !!data.customers.length;
                $scope.managerCustomers = data.customers;
                $scope.is_loading = false;
            }).error(function (error) {
                $scope.is_loading = false;  
                Dialog.alert($translate.instant("Error", "appointmentpro") ,error.message , "OK", -1, "appointmentpro");         
            }).finally(function () {          
                $scope.$broadcast('scroll.infiniteScrollComplete');
            });
        };
   
        $scope.loadContent();

        $scope.isMobileNumber = function(mobile_number){
            if(mobile_number == ''){
                return false;
            }
            return true;
        }

        /**
         *Customer avatar
         */
        $scope.customer_avatar = function (image) {
            if (image !== '' && image != null && image !== 'null') {
                return IMAGE_URL + 'images/customer' + image;
            }
            return './features/emenu/assets/media/customer-placeholder.png';
        };

        $scope.call = function (phone) {
            LinkService.openLink("tel:" + phone, {}, true);
        };

        /**
        * Serach customer
        */
        $scope.loadSearchContent = function(){
            $timeout(function () {
                $scope.managerCustomers = [];
                $scope.postParams.serach  = document.getElementById("customerSearch").value;                  
                $scope.loadContent();
           
            }, 1000);

        }

        /* Pagination load*/
        $scope.loadMoreManagerCustomer = function () {
              if(!$scope.managerCustomers.length){
                return false;
              }
             $scope.postParams.offset = $scope.managerCustomers.length;
             Appointmentpro.findManagerCustomers($scope.postParams).success(function (data) {
                 $scope.can_load_older_manager_customers = !!data.customers.length;
                 $scope.managerCustomers = $scope.managerCustomers.concat(data.customers);
             }).error(function (error ) {
                $scope.can_load_older_manager_customers = false;
                Dialog.alert($translate.instant("Error", "appointmentpro") ,error.message , "OK", -1, "appointmentpro");          
            }).finally(function () {          
                $scope.$broadcast('scroll.infiniteScrollComplete');
            });
        };


});