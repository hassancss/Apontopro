/**
 * appointmentpro account version 1 controllers
 */
angular
  .module("starter")
  .controller(
    "AppointmentproAccountController",
    function (
      Dialog,
      Loader,
      Application,
      $ionicSideMenuDelegate,
      Customer,
      $session,
      Modal,
      $ionicScrollDelegate,
      $timeout,
      $ionicModal,
      SB,
      $translate,
      $rootScope,
      $ionicLoading,
      $window,
      Appointmentpro,
      $scope,
      $state,
      $stateParams,
      $controller,
      $compile,
      $ionicPopup
    ) {
      angular.extend(
        this,
        $controller("AppointmentproCommanController", {
          Dialog: Dialog,
          $rootScope: $rootScope,
          $scope: $scope,
          $stateParams: $stateParams,
          Appointmentpro: Appointmentpro,
        })
      );

      $scope.value_id = Appointmentpro.value_id = $stateParams.value_id;
      $scope.settings = Appointmentpro.getAppointmentproSettings();
      $scope.payout = {};
      $scope.is_logged_in = Customer.isLoggedIn();
      $scope.customer = Customer.customer;
      $scope.notification_setting = {};
      var callbacks = {
        loaderror: logEvent,
        loadstart: logEvent,
        loadstop: logEvent,
        exit: logEvent,
      };

      $scope.activeGoogle = function () {
        if (!$scope.settings.enable_google_calendar) return false;

        var message = $translate.instant(
          "Activate Calendar syncing?",
          "appointmentpro"
        );
        if ($scope.settings.providerInfo.google_refresh_token != null) {
          message = $translate.instant(
            "Reactivate Calendar syncing?",
            "appointmentpro"
          );
        }

        $ionicPopup
          .show({
            title: $translate.instant("Confirm", "appointmentpro"),
            template: message,
            cssClass: "cancel-status",
            scope: $scope,
            buttons: [
              {
                text: $translate.instant("Cancel", "appointmentpro"),
                type: "button-default",
                onTap: function (e) {
                  return false;
                },
              },
              {
                text: $translate.instant("Yes", "appointmentpro"),
                type: "button-positive",
                onTap: function (e) {
                  return true;
                },
              },
            ],
          })
          .then(function (result) {
            if (result) {
              var CLIENT_REDIRECT_URL = $scope.settings.google_redirect_URL;
              var CLIENT_ID = $scope.settings.client_id;
              var state =
                $scope.value_id +
                "," +
                $scope.settings.providerInfo.providerId +
                "," +
                device.platform;
              var googleURL =
                "https://accounts.google.com/o/oauth2/auth?scope=" +
                encodeURI("https://www.googleapis.com/auth/calendar") +
                "&redirect_uri=" +
                encodeURI(CLIENT_REDIRECT_URL) +
                "&response_type=code&client_id=" +
                CLIENT_ID +
                "&access_type=offline&include_granted_scopes=false&prompt=consent&state=" +
                state;

              if (!Application.is_webview) {
                $ionicPopup
                  .show({
                    title: $translate.instant("Info", "appointmentpro"),
                    template: $translate.instant(
                      "Redirect to an absolute URL outside of the App",
                      "appointmentpro"
                    ),
                    cssClass: "cancel-status",
                    scope: $scope,
                    buttons: [
                      {
                        text: $translate.instant("Cancel", "appointmentpro"),
                        type: "button-default",
                        onTap: function (e) {
                          return false;
                        },
                      },
                      {
                        text: $translate.instant("Continue", "appointmentpro"),
                        type: "button-positive",
                        onTap: function (e) {
                          return true;
                        },
                      },
                    ],
                  })
                  .then(function (result) {
                    if (result) {
                      var strWindowFeatures =
                        "location=yes,scrollbars=yes,clearsessioncache=yes,clearcache=yes";
                      var browser = $window.open(
                        googleURL,
                        "_system",
                        strWindowFeatures,
                        callbacks
                      );
                      browser.addEventListener("loadstart", function (event) {
                        if (event.url.indexOf(redirect_uri) === 0) {
                          browser.removeEventListener(
                            "exit",
                            function (event) {}
                          );
                          browser.close();

                          var url = event.url;
                          var first_split = url.split("?");
                          var second_split = first_split[1].split("&");
                          var token_split = second_split[0].split("=");
                          var code = token_split[1];

                          $scope.param = {
                            provider_id:
                              $scope.settings.providerInfo.providerId,
                            code: code,
                          };

                          Appointmentpro.saveProviderGoogleToken(
                            $scope.param
                          ).then(
                            function (data) {
                              $state
                                .go(
                                  "home",
                                  { value_id: $scope.value_id },
                                  { reload: true }
                                )
                                .then(function () {
                                  $state
                                    .go(
                                      "appointmentpro-home",
                                      { value_id: $scope.value_id },
                                      { reload: true }
                                    )
                                    .then(function () {
                                      Dialog.alert(
                                        $translate.instant(
                                          "Success",
                                          "appointmentpro"
                                        ),
                                        data.message,
                                        $translate.instant(
                                          "OK",
                                          "appointmentpro"
                                        )
                                      );
                                    });
                                });
                            },
                            function (error) {
                              Loader.hide();
                              Dialog.alert(
                                $translate.instant("Error", "appointmentpro"),
                                error.message,
                                $translate.instant("OK", "appointmentpro")
                              );
                            }
                          );

                          $ionicLoading.hide();
                        } else if (/(cancel)/.test(event.url)) {
                          browser.close();
                          $ionicLoading.hide();
                        }
                      });
                    }
                  });

                browser.addEventListener("exit", function (event) {
                  deferred.reject("The sign in flow was canceled");
                  alert("exit event=" + JSON.stringify(event));
                  $ionicLoading.hide();
                });
              } else {
                $window.location = googleURL;
              }
            }
          });
      };

      function logEvent(e) {
        alert("LOG event=" + JSON.stringify(e));
      }
      /**
       *Customer avatar
       */
      $scope.customer_avatar = function (image) {
        if (image != "" && image != null && image != "null") {
          return IMAGE_URL + "images/customer" + image;
        } else {
          return "./features/ewallet/assets/media/customer-placeholder.png";
        }
      };

      /**
       *login
       */
      $scope.login = function () {
        if (!Customer.isLoggedIn()) {
          Customer.loginModal($scope, function () {
            //Loader.show();
            $timeout(function () {
              $scope.is_logged_in = Customer.isLoggedIn();
              $scope.customer = Customer.customer;
              $state.go("home").then(function () {
                $state
                  .go(
                    "appointmentpro-home",
                    { value_id: $scope.value_id },
                    { reload: true }
                  )
                  .then(function () {
                    //Loader.hide();
                    $state.go(
                      "appointmentpro-account",
                      { value_id: $scope.value_id },
                      { reload: true }
                    );
                  });
              });
            }, 1000);
          });
        } else {
          Customer.loginModal();
        }
      };

      $rootScope.$on(SB.EVENTS.AUTH.loginSuccess, function () {
        $scope.is_logged_in = Customer.isLoggedIn();
        $scope.customer = Customer.customer;
      });

      $rootScope.$on(SB.EVENTS.AUTH.logoutSuccess, function () {
        $scope.is_logged_in = Customer.isLoggedIn();
        $scope.customer = Customer.customer;
      });

      $scope.notificationSettings = function () {
        $scope.is_loading_modal = true;
        Loader.show();
        $ionicModal
          .fromTemplateUrl(
            "features/appointmentpro/assets/templates/modal/settings/notification_setttings.html",
            {
              scope: $scope,
              animation: "slide-in-right-left",
            }
          )
          .then(function (modal) {
            Appointmentpro.getNotificationSettings()
              .success(function (data) {
                $scope.notification_setting = data.setting;
                Loader.hide();
                $scope.is_loading_modal = false;
              })
              .error(function (error) {
                Loader.hide();
                $scope.is_loading_modal = false;
                Dialog.alert(
                  $translate.instant("Error", "appointmentpro"),
                  error.message,
                  "OK",
                  -1,
                  "appointmentpro"
                );
              });

            $scope.modalNotificationSettings = modal;
            $scope.modalNotificationSettings.show();
          });
      };

      /**
       * close info
       */
      $scope.closeModalNotificationSettings = function () {
        $scope.modalNotificationSettings.remove();
      };

      $scope.updateNotificationSettings = function () {
        console.log($scope.notification_setting);
        Appointmentpro.customerNotificationSettings($scope.notification_setting)
          .success(function (data) {})
          .error(function (error) {
            Dialog.alert(
              $translate.instant("Error", "appointmentpro"),
              error.message,
              "OK",
              -1,
              "appointmentpro"
            );
          });
      };

      $scope.switchAccount = function () {
        Loader.show();
        Appointmentpro.switchAccount($scope.settings.providerInfo.providerId)
          .success(function (data) {
            $timeout(function () {
              $state.go("home").then(function () {
                Loader.hide();
                $state.go(
                  "appointmentpro-home",
                  { value_id: $scope.value_id },
                  { reload: true }
                );
              });
            }, 300);
          })
          .error(function (error) {
            Loader.hide();
            Dialog.alert(
              $translate.instant("Error", "appointmentpro"),
              error.message,
              "OK",
              -1,
              "appointmentpro"
            );
          });
      };
    }
  )
  .controller(
    "AppointmentproAccountBookingController",
    function (
      Dialog,
      Loader,
      Application,
      $ionicSideMenuDelegate,
      Customer,
      $session,
      Modal,
      $ionicScrollDelegate,
      $timeout,
      $ionicModal,
      SB,
      $translate,
      $rootScope,
      Appointmentpro,
      $ionicPopup,
      $scope,
      $state,
      $stateParams,
      $controller,
      $compile
    ) {
      angular.extend(
        this,
        $controller("AppointmentproCommanController", {
          Dialog: Dialog,
          $rootScope: $rootScope,
          $scope: $scope,
          $stateParams: $stateParams,
          Appointmentpro: Appointmentpro,
        })
      );

      $scope.value_id = Appointmentpro.value_id = $stateParams.value_id;
      $scope.type = $stateParams.type;
      $scope.service_type = $stateParams.service_type;
      $scope.booking_id = $stateParams.booking_id;
      $scope.settings = Appointmentpro.getAppointmentproSettings();
      $scope.payout = {};

      $scope.bookingDetailsById = function (booking_id) {
        $scope.is_loading_modal = true;
        Loader.show();
        $ionicModal
          .fromTemplateUrl(
            "features/appointmentpro/assets/templates/modal/booking/details.html",
            {
              scope: $scope,
              animation: "slide-in-right-left",
            }
          )
          .then(function (modal) {
            Appointmentpro.findBookingDetailsById(booking_id)
              .success(function (data) {
                $scope.booking_info = data.booking;
                Loader.hide();
                $scope.is_loading_modal = false;
              })
              .error(function (error) {
                Loader.hide();
                $scope.is_loading_modal = false;
                Dialog.alert(
                  $translate.instant("Error", "appointmentpro"),
                  error.message,
                  "OK",
                  -1,
                  "appointmentpro"
                );
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
      };
      $scope.is_type_history = false;
      $scope.history = {
        service_id: 0,
        searchText: "",
      };
      //   setService
      $scope.setService = function (service_id) {
        $scope.history.service_id = service_id;
        $scope.loadContent();
      };
      $scope.loadContent = function () {
        if ($stateParams.type == "history") {
          $scope.is_type_history = true;
        } else {
          $scope.is_type_history = false;
        }
        $scope.is_loading = true;
        //Loader.show();
        Appointmentpro.findCustomerBooking(
          $scope.type,
          $scope.service_type,
          $scope.history.service_id
        )
          .success(function (data) {
            $scope.payout = data;
            $scope.services = data.services;
            // Loader.hide();
            $scope.is_loading = false;
          })
          .error(function (error) {
            //Loader.hide();
            $scope.is_loading = false;
            Dialog.alert(
              $translate.instant("Error", "appointmentpro"),
              error.message,
              "OK",
              -1,
              "appointmentpro"
            );
          })
          .finally(function () {
            $scope.$broadcast("scroll.infiniteScrollComplete");
          });

        if ($scope.booking_id > 0) {
          $scope.bookingDetailsById($scope.booking_id);
        }
      };

      // When entering the feature, we check the origin!
      $scope.loadContent();

      $scope.cancelBooking = function (appointment_id) {
        if ($scope.booking_info.is_paid) {
          var redund_amount =
            ((100 - $scope.settings.cancellation_charges) *
              $scope.booking_info.total_amount) /
            100;
          var message = $translate.instant(
            "Are you sure want to Cancel Booking?, You will get $1 in your account.",
            "appointmentpro"
          );
          message = message.replace("$1", redund_amount);
        } else {
          var message = $translate.instant(
            "Are you sure want to Cancel Booking?",
            "appointmentpro"
          );
        }

        $ionicPopup
          .show({
            title: $translate.instant("Confirm", "appointmentpro"),
            template: message,
            cssClass: "cancel-status",
            scope: $scope,
            buttons: [
              {
                text: $translate.instant("No", "appointmentpro"),
                type: "button-default",
                onTap: function (e) {
                  return false;
                },
              },
              {
                text: $translate.instant("Yes", "appointmentpro"),
                type: "button-positive",
                onTap: function (e) {
                  return true;
                },
              },
            ],
          })
          .then(function (result) {
            if (result) {
              Loader.show();
              Appointmentpro.cancelBooking(appointment_id)
                .then(
                  function (data) {
                    Dialog.alert(
                      $translate.instant("Success", "appointmentpro"),
                      data.message,
                      $translate.instant("OK", "appointmentpro")
                    );
                    $scope.loadContent();
                    $scope.closeModalBookingDetails();
                  },
                  function (error) {
                    Loader.hide();
                    Dialog.alert(
                      $translate.instant("Error", "appointmentpro"),
                      error.message,
                      $translate.instant("OK", "appointmentpro")
                    );
                  }
                )
                .then(function () {
                  Loader.hide();
                });
            }
          });
      };

      $scope.readInformation = function (content) {
        $ionicModal
          .fromTemplateUrl(
            "features/appointmentpro/assets/templates/modal/booking/information.html",
            {
              scope: $scope,
              animation: "slide-in-right-left",
            }
          )
          .then(function (modal) {
            $scope.content_details = $scope.settings.cancel_policy;
            $scope.modalReadInformation = modal;
            $scope.modalReadInformation.show();
          });
      };

      /**
       * close info
       */
      $scope.closeModalInformation = function () {
        $scope.modalReadInformation.remove();
      };
    }
  )
  .controller(
    "AppointmentproGooglereturnController",
    function (
      Dialog,
      Appointmentpro,
      SB,
      $ionicHistory,
      $ionicLoading,
      $location,
      Application,
      $ionicPlatform,
      $rootScope,
      $ionicScrollDelegate,
      Loader,
      $timeout,
      $ionicSlideBoxDelegate,
      Customer,
      $scope,
      $filter,
      $state,
      $stateParams,
      $translate,
      $ionicModal
    ) {
      $scope.value_id = Appointmentpro.value_id = $stateParams.value_id;
      $scope.code = $stateParams.code;
      Loader.show();

      $scope.loadContent = function () {
        $scope.settings = Appointmentpro.getAppointmentproSettings();

        $scope.param = {
          provider_id: $scope.settings.providerInfo.providerId,
          code: $scope.code,
        };

        Appointmentpro.saveProviderGoogleToken($scope.param).then(
          function (data) {
            $state
              .go("home", { value_id: $scope.value_id }, { reload: true })
              .then(function () {
                $state
                  .go(
                    "appointmentpro-home",
                    { value_id: $scope.value_id },
                    { reload: true }
                  )
                  .then(function () {
                    Loader.hide();
                    console.log("return to google back 2", $scope.code);
                    Dialog.alert(
                      $translate.instant("Success", "appointmentpro"),
                      data.message,
                      $translate.instant("OK", "appointmentpro")
                    );
                  });
              });
          },
          function (error) {
            Loader.hide();
            Dialog.alert(
              $translate.instant("Error", "appointmentpro"),
              error.message,
              $translate.instant("OK", "appointmentpro")
            );
          }
        );
      };

      Application.loaded.then(function () {
        Loader.show();
        /* Appointmentpro
            .setValueId($scope.value_id)
            .fetchAppointmentproSettings()
            .then(function (data) {
                Appointmentpro.setAppointmentproSettings(data.settings); 
                Appointmentpro.setLabels(data.labels);
                $scope.loadContent();                 
            });*/

        $state
          .go("home", { value_id: $scope.value_id }, { reload: true })
          .then(function () {
            $state
              .go(
                "appointmentpro-home",
                { value_id: $scope.value_id },
                { reload: true }
              )
              .then(function () {
                Loader.hide();
                Dialog.alert(
                  $translate.instant("Success", "appointmentpro"),
                  data.message,
                  $translate.instant("OK", "appointmentpro")
                );
              });
          });
      });
    }
  );
