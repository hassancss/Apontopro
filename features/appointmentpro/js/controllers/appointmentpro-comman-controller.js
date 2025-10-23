/**
 * appointmentpro Comman version 1 controllers
 */
angular
    .module('starter')
    .controller('AppointmentproCommanController', function ($rootScope, SB, $scope, $stateParams, $state, $translate, Loader, $timeout, Appointmentpro, $ionicModal, Customer, $ionicActionSheet) {
    $scope.settings = Appointmentpro.getAppointmentproSettings(); 
    $scope.value_id = Appointmentpro.value_id = $stateParams.value_id;

   /**
     * Translate html text
     */
    $scope.forTranslate = function (text) {
        return $translate.instant(text, "appointmentpro");
    };


     /**
     * get label name
     */
    $scope.getLabelName = function (key) {
        $scope.labels = Appointmentpro.getLabels(); 
        return $translate.instant($scope.labels[key], "appointmentpro");
    };

    /**
     *Slider Image 
     */
    $scope.appointmentproSliderImage = function (image) {      
        if (image != '' && image != null && image != "null") {
            return IMAGE_URL + 'images/application' + image;
        } else {
            return "./features/appointmentpro/assets/media/default-image.png"
        }
    };


    /**
     *Default Image 
     */
    $scope.appointmentproImage = function (image) {      
        if (image != '' && image != null && image != "null" && image != '0') {
            return IMAGE_URL + 'images/application' + image;
        } else {
            return "./features/appointmentpro/assets/media/customer-placeholder.png"
        }
    };

    $scope.classDetails = function (service_id) {
            
            $scope.is_loading_modal = true;
            Loader.show();
            $ionicModal.fromTemplateUrl('features/appointmentpro/assets/templates/modal/classes/details.html', {
                scope: $scope,
                animation: 'slide-in-right-left'        
            }).then(function(modal) {

                 Appointmentpro.getClassInfo(service_id).success(function (data) {            
                    $scope.class_info = data.class;
                    Loader.hide();
                    $scope.is_loading_modal = false;
                }).error(function (error) {
                    Loader.hide();
                    $scope.is_loading_modal = false;
                    Dialog.alert($translate.instant("Error", "appointmentpro") ,error.message , "OK", -1, "appointmentpro");         
               }); 
                
               $scope.modalclassDetails = modal;
               $scope.modalclassDetails.show();
            });

        };

         /**
         * close info
         */
        $scope.closeModalClassDetails = function () {
            $scope.modalclassDetails.remove();
        }

         /**
         *login
         */
        $scope.loginRequired = function(){
            if (!Customer.isLoggedIn()) {
                Customer.loginModal($scope, function () { 
                    Loader.show();
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

        /**
         * close info
         */
        $scope.bookClassNow = function (service) {
            if(!Customer.isLoggedIn()){
                $scope.loginRequired(); return false;
            }
            $scope.cart = Appointmentpro.cart;
            $scope.cart.provider_id = service.provider_id;
            $scope.cart.service_id = service.service_id;
            $scope.cart.category_id = service.category_id;
            $scope.cart.location_id = service.location_id;
            $scope.cart.is_class = true;
            $scope.cart.class = service;
            $scope.cart.tickets_qty = 1
            $scope.cart.total_amount = ($scope.cart.tickets_qty * service.price);

            Appointmentpro.cart = $scope.cart;
            $scope.closeModalClassDetails();
           
            $state.go("book-a-class", { value_id: $scope.value_id }, { reload: true });
        }

        $scope.range = function(min, max) {
            var input = [];
            for (var i = min; i <= max; i++) input.push(i);
            return input;
        };


        /**
         * Redirect To Provider Service
         */
        $scope.goToProviderService = function (provider) {
            if(!Customer.isLoggedIn()){
                $scope.loginRequired(); return false;
            }
            $scope.cart = Appointmentpro.cart;
            $scope.cart.provider_id = provider.provider_id;
            $scope.cart.location_id = provider.location_id;
            $scope.cart.tickets_qty = 1
            Appointmentpro.cart = $scope.cart;
            console.log($scope.value_id);
            $state.go("provider-services", { value_id: $scope.value_id, provider_id: provider.provider_id }, { reload: true });
        }


        /**
         * Redirect To Provider calendar
         */
        $scope.goToProviderCalendar = function (service_id) {           
            $scope.cart = Appointmentpro.cart;
            $scope.cart.service_id = service_id;         
            Appointmentpro.cart = $scope.cart;            
            $state.go("appointmentpro-calendar", { value_id: $scope.value_id, provider_id: $scope.cart.provider_id }, { reload: true });
        }
       
       /**
         * Redirect To category location
         */
        $scope.goToCategoriesLocation = function (category) {
            if(!Customer.isLoggedIn()){
                $scope.loginRequired(); return false;
            }
            $scope.cart = Appointmentpro.cart;
            $scope.cart.category_id = category.category_id;
            $scope.cart.tickets_qty = 1
            Appointmentpro.cart = $scope.cart;
            console.log('category_for', category.category_for);
            
            if(category.category_for == 2){
                $state.go("appointmentpro-classes", { value_id: $scope.value_id, category_id: category.category_id }, { reload: true });
            }else{
                $state.go("appointmentpro-category-location", { value_id: $scope.value_id, category_id: category.category_id }, { reload: true });
            }
       }

        /**
         * Redirect To location and category services
         */
        $scope.goToLocationServices = function (location) {
            if(!Customer.isLoggedIn()){
                $scope.loginRequired(); return false;
            }
            $scope.cart = Appointmentpro.cart;
            $scope.cart.location_id = location.location_id;            
            Appointmentpro.cart = $scope.cart;
           
            $state.go("appointmentpro-service", { value_id: $scope.value_id, category_id: $scope.cart.category_id }, { reload: true });
        }



});
