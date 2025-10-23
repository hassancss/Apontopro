/**
 * appointmentpro payment version 1 controllers
 */
angular
  .module("starter")
  .controller(
    "AppointmentproPaymentController",
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
      $ionicHistory,
      $translate,
      $rootScope,
      $ionicLoading,
      Appointmentpro,
      $scope,
      $state,
      $stateParams,
      $controller,
      $compile,
      $window
    ) {
      $scope.value_id = Appointmentpro.value_id = $stateParams.value_id;
      $scope.settings = Appointmentpro.getAppointmentproSettings();
      $scope.payout = {};
      $scope.years = [];
      $scope.cardElement = null;
      $scope.responsePayfast = "";

      /*Cart Update*/
      $scope.cart = Appointmentpro.cart;
      $scope.cart.payment_method = "";
      $scope.gateway_code = "";

      $scope.loadContent = function () {
        $scope.is_loading = true;
        //Loader.show();
        Appointmentpro.findPaymentMethods($scope.cart.location_id)
          .success(function (data) {
            $scope.payout = data;
            Appointmentpro.publishable_key = data.publishable_key;
            $scope.is_loading = false;
            // Loader.hide();
          })
          .error(function (error) {
            // Loader.hide();
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
      };

      /*Stripe Loaded*/
      var stripeJS = document.createElement("script");
      stripeJS.type = "text/javascript";
      stripeJS.src = "https://js.stripe.com/v3/";
      stripeJS.onload = function () {
        Appointmentpro.isReadyPromise.resolve(Stripe);
      };
      document.body.appendChild(stripeJS);

      // When entering the feature, we check the origin!
      $scope.loadContent();

      /*Populate Payment URl*/
      $scope.populateURL = function (param) {
        console.log("param", param);
        // return param.paypal_url;
        if (!Application.is_webview) {
          $ionicLoading.show({
            content: "Please Wait...",
            animation: "fade-in",
            showBackdrop: true,
            maxWidth: 200,
            showDelay: 0,
          });
          var browser = $window.open(
            param.paypal_url,
            $rootScope.getTargetForLink(),
            "location=yes"
          );
          browser.addEventListener("loadstart", function (event) {
            var newurl = event.url.split("/?__goto__=");
            var res = newurl[0].concat(newurl[1]);
            if (/(confirm)/.test(event.url)) {
              var url = event.url;
              var first_split = url.split("?");
              var second_split = first_split[1].split("&");
              var token_split = second_split[0].split("=");
              var tokenId = token_split[1];
              var payer_split = second_split[1].split("=");
              var payerId = payer_split[1];
              if (tokenId != "" && payerId != "") {
                $scope.cart = param;
                $scope.cart.status = "success";
                $scope.cart.tokenId = tokenId;
                $scope.cart.txn = payerId;
                $scope.updateBookingStatus($scope.cart);
                browser.close();
                $ionicLoading.hide();
              } else {
                $scope.cart = param;
                $scope.cart.status = "failed";
                $scope.cart.tokenId = "";
                $scope.cart.txn = "";
                $scope.updateBookingStatus($scope.cart);
                browser.close();
                $ionicLoading.hide();
              }
            } else if (/(cancel)/.test(event.url)) {
              $scope.cart = param;
              $scope.cart.status = "failed";
              $scope.cart.tokenId = "";
              $scope.cart.txn = "";
              $scope.updateBookingStatus($scope.cart);
              browser.close();
              $ionicLoading.hide();
            }
          });
        } else {
          $window.location = param.paypal_url;
        }
      };

      /*Populate Payment URl*/
      $scope.populatePayfastURL = function (param) {
        if (!Application.is_webview) {
          $ionicLoading.show({
            content: "Please Wait...",
            animation: "fade-in",
            showBackdrop: true,
            maxWidth: 200,
            showDelay: 0,
          });
          var browser = $window.open(
            param.paypal_url,
            $rootScope.getTargetForLink(),
            "location=yes"
          );
          browser.addEventListener("loadstart", function (event) {
            if (/(confirm)/.test(event.url)) {
              $scope.cart = param;
              $scope.cart.status = "success";
              $scope.cart.tokenId = "";
              $scope.cart.txn = "";
              $scope.updateBookingStatus($scope.cart);
              browser.close();
              $ionicLoading.hide();
            } else if (/(cancel)/.test(event.url)) {
              $scope.cart = param;
              $scope.cart.status = "failed";
              $scope.cart.tokenId = "";
              $scope.cart.txn = "";
              $scope.updateBookingStatus($scope.cart);
              browser.close();
              $ionicLoading.hide();
            }
          });
        } else {
          $window.location = param.paypal_url;
        }
      };

      $scope.updateBookingStatus = function (param) {
        var booking_id = param.booking_id;
        Loader.show();
        Appointmentpro.updatePaymentStatus2(param).then(
          function (data) {
            if (param.status == "success") {
              Dialog.alert(
                $translate.instant("Success", "appointmentpro"),
                $translate.instant(
                  "Payment has been successfully",
                  "appointmentpro"
                ),
                $translate.instant("OK", "appointmentpro"),
                -1,
                "appointmentpro"
              );
            }
            if (param.status == "failed") {
              Dialog.alert(
                $translate.instant("Error", "appointmentpro"),
                $translate.instant("Payment has been failed", "appointmentpro"),
                $translate.instant("OK", "appointmentpro"),
                -1,
                "appointmentpro"
              );
            }

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
                    $state.go(
                      "appointmentpro-booked-success",
                      {
                        value_id: $scope.value_id,
                        booking_id: param.booking_id,
                      },
                      { reload: true }
                    );
                  });
              });
          },
          function (error) {
            Loader.hide();
            Dialog.alert(
              $translate.instant("Error", "appointmentpro"),
              error.message,
              $translate.instant("OK", "appointmentpro"),
              -1,
              "appointmentpro"
            );
          }
        );
      };

      $scope.placeOrder = function () {
        if ($scope.cart.payment_method == "") {
          Dialog.alert(
            $translate.instant("Error", "Appointmentpro"),
            $translate.instant(
              "Please select a payment method!",
              "Appointmentpro"
            ),
            $translate.instant("OK", "Appointmentpro"),
            -1
          );
          return true;
        }

        Appointmentpro.cart = $scope.cart;
        Loader.show();
        console.log("cart", $scope.cart);
        // return;
        Appointmentpro.bookingSubmit($scope.cart).then(
          function (data) {
            console.log("data-", data);
            // return data;
            console.log("data-", data);
            //Loader.hide();
            if (data.is_paypal && data.booking_status) {
              $scope.cart.booking_id = data.booking_id;
              $scope.cart.paypal_url = data.paypalURL;
              Appointmentpro.set_local(
                "PAYPAL_BEFORE_DATA",
                JSON.stringify($scope.cart)
              );
              $scope.populateURL($scope.cart);
            } else {
              if (data.is_payfast) {
                $scope.cart.booking_id = data.booking_id;
                $scope.responsePayfast = data.responsePayfast;
                $scope.cart.paypal_url = data.paypalURL;
                Appointmentpro.set_local(
                  "PAYFAST_BEFORE_DATA",
                  JSON.stringify($scope.cart)
                );

                $scope.populatePayfastURL($scope.cart);
              } else {
                if (!data.booking_status) {
                  Dialog.alert(
                    $translate.instant("Error", "appointmentpro"),
                    data.message,
                    $translate.instant("OK", "appointmentpro")
                  );
                } else {
                  Dialog.alert(
                    $translate.instant("Success", "appointmentpro"),
                    data.message,
                    $translate.instant("OK", "appointmentpro")
                  );
                }

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
                        $state.go(
                          "appointmentpro-booked-success",
                          {
                            value_id: $scope.value_id,
                            booking_id: data.booking_id,
                          },
                          { reload: true }
                        );
                      });
                  });
              }
            }
          },
          function (error) {
            Loader.hide();
            Dialog.alert(
              $translate.instant("Error", "appointmentpro"),
              error.message,
              $translate.instant("OK", "appointmentpro"),
              -1
            );
          }
        );
      };

      // Select Payment method
      $scope.selectPaymentMethod = function (method) {
        if (method.gateway_code == "stripe") {
          if (
            !Appointmentpro.publishable_key ||
            Appointmentpro.publishable_key == null
          ) {
            Dialog.alert(
              $translate.instant("Error", "appointmentpro"),
              $translate.instant(
                "This payment methods are temporarily unavailable",
                "appointmentpro"
              ),
              $translate.instant("OK", "appointmentpro"),
              -1
            );
            return false;
          }

          $scope.gateway_code = method.gateway_code;
          var current_year = new Date().getFullYear();
          for (var i = current_year; i <= current_year + 20; i++) {
            $scope.years.push({ value: i + "" });
          }

          $timeout(function () {
            try {
              Appointmentpro.StripeInstance = Stripe(
                Appointmentpro.publishable_key
              );
              var elements = Appointmentpro.StripeInstance.elements();
              var style = {
                base: {
                  color: "#32325d",
                  fontFamily: "'Helvetica Neue', Helvetica, sans-serif",
                  fontSmoothing: "antialiased",
                  fontSize: "16px",
                  "::placeholder": {
                    color: "#aab7c4",
                  },
                },
                invalid: {
                  color: "#fa755a",
                  iconColor: "#fa755a",
                },
              };

              $scope.cardElement = elements.create("card", {
                hidePostalCode: true,
                style: style,
              });

              var saveElement = document.getElementById(
                "appointmentpro_save_element"
              );
              var displayError = document.getElementById(
                "appointmentpro_card_errors"
              );
              var displayErrorParent = document.getElementById(
                "appointmentpro_card_errors_parent"
              );

              saveElement.setAttribute("disabled", "disabled");

              $scope.cardElement.removeEventListener("change");
              $scope.cardElement.addEventListener("change", function (event) {
                if (event.error) {
                  displayErrorParent.classList.remove("ng-hide");
                  displayError.textContent = event.error.message;
                  saveElement.setAttribute("disabled", "disabled");
                } else {
                  displayErrorParent.classList.add("ng-hide");
                  displayError.textContent = "";
                  saveElement.removeAttribute("disabled");
                }
              });

              $scope.cardElement.mount("#appointmentpro_card_element");
            } catch (error) {
              Dialog.alert(
                $translate.instant("Error", "appointmentpro"),
                error.message,
                $translate.instant("Ok", "appointmentpro")
              );
            }
          }, 300);
        } else {
          $scope.gateway_code = method.gateway_code;
        }

        $rootScope.$broadcast("refreshPageSize");
        $scope.$broadcast("scroll.infiniteScrollComplete");
      };
      // nmi payment method
      // payNmiNow
      $scope.nmi = {
        card_number: "",
        card_cvc: "",
        expiry_date: "",
      };
      $scope.showPopup = function (title, message) {
        // var popup = $ionicPopup.alert({
        //   title: $translate.instant(title),
        //   template: $translate.instant(message),
        //   okText: $translate.instant("OK"), // Change the text of the OK button
        // });
        Dialog.alert(
          $translate.instant(title, "appointmentpro"),
          $translate.instant(message, "appointmentpro"),
          $translate.instant("OK", "appointmentpro"),
          -1
        );
      };
      // nmi validation
      $scope.validateNmiForm = function () {
        var cardNumber = $scope.nmi.card_number;
        var expiryDate = $scope.nmi.expiry_date;
        var cvv = $scope.nmi.card_cvc;
        var errors = [];

        if (!cardNumber || cardNumber.length < 16 || isNaN(cardNumber)) {
          errors.push("Please enter a valid card number");
        }

        if (!expiryDate || !/^(0[1-9]|1[0-2])-\d{4}$/.test(expiryDate)) {
          errors.push("Please enter a valid expiry date in MM-YYYY format");
        } else {
          var parts = expiryDate.split("-");
          var month = parseInt(parts[0], 10);
          var year = parseInt(parts[1], 10);
          var currentDate = new Date();
          var currentMonth = currentDate.getMonth() + 1; // Months are 0-based
          var currentYear = currentDate.getFullYear();

          if (
            year < currentYear ||
            (year === currentYear && month < currentMonth)
          ) {
            errors.push("Expiry date must be greater than the current date");
          }
        }

        if (!cvv || cvv.length < 3 || isNaN(cvv)) {
          errors.push("Please enter a valid CVV");
        }

        if (errors.length > 0) {
          $scope.showPopup("Error", errors.join("<br>"));
          return false;
        }

        return true;
      };

      $scope.payNmiNow = function () {
        if ($scope.gateway_code == "nmi") {
          if ($scope.cart.payment_method == "") {
            Dialog.alert(
              $translate.instant("Error", "appointmentpro"),
              $translate.instant(
                "Please select a payment method!",
                "appointmentpro"
              ),
              $translate.instant("OK", "appointmentpro"),
              -1
            );
            return true;
          }
          if (!$scope.validateNmiForm()) {
            return;
          }
          $scope.cart.nmi = $scope.nmi;
          $scope.placeOrder();
        }
      };
      // payStripeNow
      $scope.payStripeNow = function () {
        Appointmentpro.StripeInstance.createToken($scope.cardElement).then(
          function (result) {
            _stripeResponseHandler(result);
          }
        );
      };

      var _stripeResponseHandler = function (result) {
        if (result.error) {
          Dialog.alert("", result.error.message, "OK");
          $scope.is_loading = false;
          $scope.isProcessing = false;
          Loader.hide();
        } else {
          $scope.card = {
            token: result.token.id,
            last4: result.token.card.last4,
            brand: result.token.card.brand,
            exp_month: result.token.card.exp_month,
            exp_year: result.token.card.exp_year,
            exp:
              Math.round(
                +new Date(
                  new Date(
                    result.token.card.exp_year,
                    result.token.card.exp_month,
                    1
                  ) - 1
                ) / 1000
              ) | 0,
          };

          $scope.cart.stripe = $scope.card;
          $scope.placeOrder();
        }
      };

      // for range
      $scope.range = function (min, max, step) {
        step = step || 1;
        var input = [];
        for (var i = min; i <= max; i += step) input.push(i);
        return input;
      };

      //get stripe publishable keys
      /*  $scope.getPublishableKey = function() {
            Appointmentpro
            .fetchSettings()
            .then(function (payload) {
                Appointmentpro.publishable_key = payload.settings.publishable_key;
             }, function (error) {
                console.error(error.message);
            });
        };
        $scope.getPublishableKey();*/
    }
  )
  .controller(
    "AppointmentproPaypalReturnController",
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
      Appointmentpro.cart = JSON.parse(
        Appointmentpro.get_local("PAYPAL_BEFORE_DATA")
      );
      Appointmentpro.unset_local("PAYPAL_BEFORE_DATA");
      $scope.cart = Appointmentpro.cart;
      Loader.show();

      $scope.updateBookingStatus = function (param) {
        console.log("param updateBookingStatus", param);
        // return false;
        var booking_id = param.booking_id;
        Loader.show();
        Appointmentpro.updatePaymentStatus2(param).then(
          function (data) {
            console.log("data", data, "params", param);
            // return data;
            if (param.status == "success") {
              Dialog.alert(
                $translate.instant("Success", "appointmentpro"),
                $translate.instant(
                  "Payment has been successfully",
                  "appointmentpro"
                ),
                $translate.instant("OK", "appointmentpro"),
                -1,
                "appointmentpro"
              );
            }
            if (param.status == "failed") {
              Dialog.alert(
                $translate.instant("Error", "appointmentpro"),
                $translate.instant("Payment has been failed", "appointmentpro"),
                $translate.instant("OK", "appointmentpro"),
                -1,
                "appointmentpro"
              );
            }

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
                    $state.go(
                      "appointmentpro-booked-success",
                      {
                        value_id: $scope.value_id,
                        booking_id: param.booking_id,
                      },
                      { reload: true }
                    );
                  });
              });
          },
          function (error) {
            Loader.hide();
            Dialog.alert(
              $translate.instant("Error", "appointmentpro"),
              error.message,
              $translate.instant("OK", "appointmentpro"),
              -1,
              "appointmentpro"
            );
          }
        );
      };

      $scope.loadContent = function () {
        Loader.show();
        /*Check web or mobile device*/
        /*Add in wallet cart clear*/
        if (Application.is_webview) {
          var url = $location.absUrl();
          if (url.indexOf("?") > -1) {
            var first_split = url.split("?");
            var second_split = first_split[1].split("&");

            if (second_split.length > 1) {
              var token_split = second_split[0].split("=");
              var tokenId = token_split[1];

              var payer_split = second_split[1].split("=");
              var payerId = payer_split[1];

              $scope.cart.status = "success";
              $scope.cart.tokenId = tokenId;
              $scope.cart.payerId = payerId;
              Appointmentpro.cart = $scope.cart;
              $scope.updateBookingStatus($scope.cart);
            } else {
              $scope.cart.status = "failed";
              $scope.cart.tokenId = "";
              $scope.cart.payerId = "";
              Appointmentpro.cart = $scope.cart;
              $scope.updateBookingStatus($scope.cart);
            }
          }
        }
      };

      Application.loaded.then(function () {
        Loader.show();
        $scope.loadContent();
      });
    }
  )
  .controller(
    "AppointmentproPayfastReturnController",
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
      Appointmentpro.cart = JSON.parse(
        Appointmentpro.get_local("PAYFAST_BEFORE_DATA")
      );
      Appointmentpro.unset_local("PAYFAST_BEFORE_DATA");
      $scope.cart = Appointmentpro.cart;
      Loader.show();

      $scope.updateBookingStatus = function (param) {
        var booking_id = param.booking_id;
        Loader.show();
        Appointmentpro.updatePaymentStatus2(param).then(
          function (data) {
            if (param.status == "success") {
              Dialog.alert(
                $translate.instant("Success", "appointmentpro"),
                $translate.instant(
                  "Payment has been successfully",
                  "appointmentpro"
                ),
                $translate.instant("OK", "appointmentpro"),
                -1,
                "appointmentpro"
              );
            }
            if (param.status == "failed") {
              Dialog.alert(
                $translate.instant("Error", "appointmentpro"),
                $translate.instant("Payment has been failed", "appointmentpro"),
                $translate.instant("OK", "appointmentpro"),
                -1,
                "appointmentpro"
              );
            }

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
                    $state.go(
                      "appointmentpro-booked-success",
                      {
                        value_id: $scope.value_id,
                        booking_id: param.booking_id,
                      },
                      { reload: true }
                    );
                  });
              });
          },
          function (error) {
            Loader.hide();
            Dialog.alert(
              $translate.instant("Error", "appointmentpro"),
              error.message,
              $translate.instant("OK", "appointmentpro"),
              -1,
              "appointmentpro"
            );
          }
        );
      };

      $scope.loadContent = function () {
        Loader.show();
        $scope.cart.status = "success";
        $scope.cart.tokenId = "";
        $scope.cart.payerId = "";
        Appointmentpro.cart = $scope.cart;
        $scope.updateBookingStatus($scope.cart);
      };

      Application.loaded.then(function () {
        Loader.show();
        $timeout(function () {
          $scope.loadContent();
        }, 1500);
      });
    }
  )
  .controller(
    "AppointmentproPayfastCancelController",
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
      Appointmentpro.cart = JSON.parse(
        Appointmentpro.get_local("PAYFAST_BEFORE_DATA")
      );
      Appointmentpro.unset_local("PAYFAST_BEFORE_DATA");
      $scope.cart = Appointmentpro.cart;
      Loader.show();

      $scope.updateBookingStatus = function (param) {
        var booking_id = param.booking_id;
        Loader.show();
        Appointmentpro.updatePaymentStatus2(param).then(
          function (data) {
            if (param.status == "success") {
              Dialog.alert(
                $translate.instant("Success", "appointmentpro"),
                $translate.instant(
                  "Payment has been successfully",
                  "appointmentpro"
                ),
                $translate.instant("OK", "appointmentpro"),
                -1,
                "appointmentpro"
              );
            }
            if (param.status == "failed") {
              Dialog.alert(
                $translate.instant("Error", "appointmentpro"),
                $translate.instant("Payment has been failed", "appointmentpro"),
                $translate.instant("OK", "appointmentpro"),
                -1,
                "appointmentpro"
              );
            }

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
                    $state.go(
                      "appointmentpro-booked-success",
                      {
                        value_id: $scope.value_id,
                        booking_id: param.booking_id,
                      },
                      { reload: true }
                    );
                  });
              });
          },
          function (error) {
            Loader.hide();
            Dialog.alert(
              $translate.instant("Error", "appointmentpro"),
              error.message,
              $translate.instant("OK", "appointmentpro"),
              -1,
              "appointmentpro"
            );
          }
        );
      };

      $scope.loadContent = function () {
        Loader.show();
        $scope.cart.status = "failed";
        $scope.cart.tokenId = "";
        $scope.cart.payerId = "";
        Appointmentpro.cart = $scope.cart;
        $scope.updateBookingStatus($scope.cart);
      };

      Application.loaded.then(function () {
        Loader.show();
        $timeout(function () {
          $scope.loadContent();
        }, 1500);
      });
    }
  );
