/**
 * Appointmentpro factory init
 */
angular
    .module('starter')
    .factory('AppointmentproInit', function (Application, Pages, Appointmentpro, $location, $stateParams, $timeout, $translate,
                                    $ionicHistory, $state, $session, Loader,  $ionicSideMenuDelegate, $location) {
        var factory = {};



        factory.loadAppointmentproWidget = function (parts) {
                if (parts !== null && parts.length === 3) {
                    Loader.show($translate.instant('Loading...', 'appointmentpro'));
                    // Redirect to the module!
                    $timeout(function () {
                        Loader.hide();
                        $ionicHistory.nextViewOptions({
                            disableBack: true
                        });
                        $ionicSideMenuDelegate.canDragContent(false);
                        $state.go('appointmentpro-home', {
                            value_id: parts[1],
                            widget: parts[2]
                        });
                    }, 100);
                }
        };


        factory.loadAppointmentproGoogle = function (parts) { console.log('parts', parts);
                if (parts !== null && parts.length === 4) {
                    Loader.show($translate.instant('Loading...', 'appointmentpro'));
                    // Redirect to the module!
                    $timeout(function () {                                          
                        $state
                        .go("home")
                        .then(function () {
                            $state.go("appointmentpro-home", { value_id: parts[1] }, { reload: true }).then(function(){
                                Loader.hide();
                                $state.go("appointmentpro-mobile_googlereturn", { value_id: parts[1], code: parts[3]  }, { reload: true } );
                            });
                        });


                    }, 100);
                }
        };

         factory.onStart = function () {
          
          // Checking start_hash, start hash always has the priority over the session!
            // /appointmentpro/mobile_list/index
            var hash = HASH_ON_START.match(/\?__goto__=(.*)/);
            if (hash && hash.length >= 2) {
                // We use a short path here!
                var path = hash[1]; console.log(path);
                var parts = path.match(/\/appointmentpro\/([0-9]+)\/([0-9]+)/);
                console.log('parts 1',parts);
                if (parts !== null && parts.length === 3 && parts[2] == '213848') {
                    factory.loadAppointmentproWidget(parts);
                }
                
                var parts = path.match(/\/appointmentpro\/([0-9]+)\/([a-z]+)\/([a-zA-Z0-9!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]+)/);
                console.log('parts 2',parts);
                if (parts !== null && parts.length === 4 && parts[2] == 'google') {
                    factory.loadAppointmentproGoogle(parts);
                }
            }

            
            Application.loaded.then(function () {                    
                    // App runtime!
                    var appointmentpro = _.find(Pages.getActivePages(), {
                        code: 'appointmentpro'
                    });

                    // Module is not in the App!
                    if (!appointmentpro) {
                        return;
                    }
                     
                    Appointmentpro
                        .setValueId(appointmentpro.value_id)
                        .fetchAppointmentproSettings()
                        .then(function (data) {
                            Appointmentpro.setAppointmentproSettings(data.settings); 
                            Appointmentpro.setLabels(data.labels);                 
                        });
 
            });
        }
 
        return factory;
    });
