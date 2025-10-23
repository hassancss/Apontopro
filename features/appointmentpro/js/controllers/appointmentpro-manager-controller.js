/**
 * appointmentpro version 2 controllers
 */
angular
    .module('starter')
    .controller('AppointmentproManagerController', function (Dialog, Loader, Application, Customer, Modal, $timeout, $filter, $translate, Appointmentpro, $scope, $state, $stateParams, LinkService) {
        
        $scope.value_id = Appointmentpro.value_id = $stateParams.value_id;        
        $scope.settings = Appointmentpro.getAppointmentproSettings();
  
        $scope.loadContent = function () {
            $scope.is_loading = true;
            Appointmentpro.managerProfile().success(function (data) {
                $scope.payout = data;
                $scope.profile = data.profile;
                $scope.is_loading = false;
            }).error(function (error) {
                $scope.is_loading = false;  
                Dialog.alert($translate.instant("Error", "appointmentpro") ,error.message , "OK", -1, "appointmentpro");         
            }).finally(function () {          
                $scope.$broadcast('scroll.infiniteScrollComplete');
            });
        };
   
        $scope.loadContent();

        $scope.profileSave = function () {
            Loader.show();
            Appointmentpro.profileSave($scope.profile).success(function (data) {
               Loader.hide();
               Dialog.alert($translate.instant("Success", "appointmentpro") ,data.message , "OK", -1, "appointmentpro");
            }).error(function (error) {
                Loader.hide();
                Dialog.alert($translate.instant("Error", "appointmentpro") ,error.message , "OK", -1, "appointmentpro");         
            });
        };

}).controller('AppointmentproManagerLocationController', function (Dialog, Loader, Application, Customer, Modal, $timeout, $filter, $translate, Appointmentpro, $scope, $state, $stateParams, LinkService) {
        $scope.value_id = Appointmentpro.value_id = $stateParams.value_id;        
        $scope.settings = Appointmentpro.getAppointmentproSettings();
  
        $scope.loadContent = function () {
            $scope.is_loading = true;
            Appointmentpro.managerLocation().success(function (data) {
                $scope.payout = data;
                $scope.location = data.location;
                $scope.is_loading = false;
            }).error(function (error) {
                $scope.is_loading = false;  
                Dialog.alert($translate.instant("Error", "appointmentpro") ,error.message , "OK", -1, "appointmentpro");         
            }).finally(function () {          
                $scope.$broadcast('scroll.infiniteScrollComplete');
            });
        };
   
        $scope.loadContent();

         $scope.locationSave = function () {
            Loader.show();
            Appointmentpro.locationSave($scope.location).success(function (data) {
               Loader.hide();
               Dialog.alert($translate.instant("Success", "appointmentpro") ,data.message , "OK", -1, "appointmentpro");
            }).error(function (error) {
                Loader.hide();
                Dialog.alert($translate.instant("Error", "appointmentpro") ,error.message , "OK", -1, "appointmentpro");         
            });
        };
});