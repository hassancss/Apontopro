/**
 * Appointmentpro factory
 */
angular
  .module("starter")
  .factory(
    "Appointmentpro",
    function (
      Url,
      $rootScope,
      $pwaRequest,
      $session,
      $sbhttp,
      $q,
      Application,
      $location,
      $window
    ) {
      var factory = {
        value_id: null,
        widget: null,
        cart: {},
        StripeInstance: null,
        publishable_key: null,
        settings: [],
        labels: [],
        isReadyPromise: $q.defer(),
      };

      factory.setValueId = function (valueId) {
        factory.value_id = valueId;
        return factory;
      };

      factory.getValueId = function () {
        return factory.value_id;
      };

      factory.isUndefined = function (thing) {
        return typeof thing === "undefined";
      };

      factory.get_local = function (key) {
        return $window.localStorage.getItem(key) || null;
      };

      factory.set_local = function (key, id) {
        $window.localStorage.setItem(key, id);
      };

      factory.unset_local = function (key) {
        $window.localStorage.removeItem(key);
      };

      factory.isUndefined = function (thing) {
        return typeof thing === "undefined";
      };

      factory.setAppointmentproSettings = function (settings) {
        factory.settings = settings;
        return factory;
      };

      factory.getAppointmentproSettings = function () {
        return factory.settings;
      };

      factory.setLabels = function (labels) {
        factory.labels = labels;
        return factory;
      };

      factory.getLabels = function () {
        return factory.labels;
      };

      factory.findAll = function (params) {
        return $pwaRequest.post("appointmentpro/mobile_view/findAll", {
          urlParams: {
            value_id: this.value_id,
          },
          data: params,
          cache: false,
          refresh: true,
        });
      };

      factory.findAllLocations = function (params) {
        return $pwaRequest.post("appointmentpro/mobile_location/findAll", {
          urlParams: {
            value_id: this.value_id,
          },
          data: params,
          cache: false,
          refresh: true,
        });
      };

      factory.findLocationInfo = function (location_id) {
        return $pwaRequest.post("appointmentpro/mobile_location/find", {
          urlParams: {
            value_id: this.value_id,
            location_id: location_id,
          },
          cache: false,
          refresh: true,
        });
      };

      factory.findLocationServiceByCategory = function (
        location_id,
        category_id
      ) {
        return $pwaRequest.post(
          "appointmentpro/mobile_location/find-services-by-category",
          {
            urlParams: {
              value_id: this.value_id,
              location_id: location_id,
              category_id: category_id,
            },
            cache: false,
            refresh: true,
          }
        );
      };

      factory.findCategoryByLocation = function (location_id) {
        return $pwaRequest.post(
          "appointmentpro/mobile_category/find-category-by-location",
          {
            urlParams: {
              value_id: this.value_id,
              location_id: location_id,
            },
            cache: false,
            refresh: true,
          }
        );
      };

      factory.findServiceByLocationCategory = function (params) {
        return $pwaRequest.post(
          "appointmentpro/mobile_service/find-service-by-location-category",
          {
            urlParams: {
              value_id: this.value_id,
            },
            data: {
              location_id: params.location_id,
              category_id: params.category_id,
            },
            cache: false,
            refresh: true,
          }
        );
      };

      factory.findServiceProvider = function (params) {
        return $pwaRequest.post(
          "appointmentpro/mobile_provider/find-service-provider",
          {
            urlParams: {
              value_id: this.value_id,
            },
            data: {
              location_id: params.location_id,
              service_id: params.service_id,
            },
            cache: false,
            refresh: true,
          }
        );
      };

      factory.findProviderServices = function (params) {
        return $pwaRequest.post(
          "appointmentpro/mobile_service/find-service-by-provider-id",
          {
            urlParams: {
              value_id: this.value_id,
            },
            data: {
              location_id: params.location_id,
              provider_id: params.provider_id,
            },
            cache: false,
            refresh: true,
          }
        );
      };

      factory.availabletimeSlot = function (params, date) {
        return $pwaRequest.post(
          "appointmentpro/mobile_provider/find-service-provider-slot",
          {
            urlParams: {
              value_id: this.value_id,
            },
            data: {
              location_id: params.location_id,
              service_id: params.service_id,
              provider_id: params.provider_id,
              category_id: params.category_id,
              date: date,
            },
            cache: false,
            refresh: true,
          }
        );
      };

      factory.findConfirmBookingData = function (params) {
        return $pwaRequest.post("appointmentpro/mobile_booking/find-info", {
          urlParams: {
            value_id: this.value_id,
          },
          data: params,
          cache: false,
          refresh: true,
        });
      };

      factory.findPaymentMethods = function (location_id) {
        return $pwaRequest.post(
          "appointmentpro/mobile_booking/find-payment-method",
          {
            urlParams: {
              value_id: this.value_id,
              location_id: location_id,
            },
            cache: false,
            refresh: true,
          }
        );
      };

      factory.fetchSettings = function () {
        return $pwaRequest.post("/paymentstripe/mobile_cards/fetch-settings");
      };

      factory.bookingSubmit = function (params) {
        params.is_webview = Application.is_webview;
        params.current_url = $location.url();
        params.BASE_PATH = BASE_PATH;

        return $pwaRequest.post("appointmentpro/mobile_booking/submit", {
          urlParams: {
            value_id: this.value_id,
          },
          data: params,
          cache: false,
          refresh: true,
        });
      };

      factory.bookingDetails = function (booking_id) {
        return $pwaRequest.post("appointmentpro/mobile_booking/details", {
          urlParams: {
            value_id: this.value_id,
            booking_id: booking_id,
          },
          cache: false,
          refresh: true,
        });
      };

      factory.updatePaymentStatus2 = function (params) {
        console.log("paramsparams", params);
        // return booking_id;
        return $pwaRequest.post(
          "appointmentpro/mobile_booking/update-payment-status",
          {
            urlParams: {
              value_id: factory.value_id,
            },
            data: params,
            refresh: true,
          }
        );
      };

      factory.findCustomerBooking = function (
        type,
        service_type,
        service_id = 0
      ) {
        return $pwaRequest.post(
          "appointmentpro/mobile_account/find-customer-booking",
          {
            urlParams: {
              value_id: this.value_id,
              type: type,
              service_type: service_type,
              service_id: service_id,
            },
            cache: false,
            refresh: true,
          }
        );
      };

      factory.findBookingDetailsById = function (booking_id) {
        return $pwaRequest.post(
          "appointmentpro/mobile_account/find-booking-by-id",
          {
            urlParams: {
              value_id: this.value_id,
              booking_id: booking_id,
            },
            cache: false,
            refresh: true,
          }
        );
      };

      factory.customerNotificationSettings = function (params) {
        return $pwaRequest.post(
          "appointmentpro/mobile_account/customer-notification-settings",
          {
            urlParams: {
              value_id: this.value_id,
            },
            data: params,
            cache: false,
            refresh: true,
          }
        );
      };

      factory.getNotificationSettings = function () {
        return $pwaRequest.post(
          "appointmentpro/mobile_account/get-notification-settings",
          {
            urlParams: {
              value_id: this.value_id,
            },
            cache: false,
            refresh: true,
          }
        );
      };

      factory.cancelBooking = function (appointment_id) {
        return $pwaRequest.post(
          "appointmentpro/mobile_account/cancel-booking",
          {
            urlParams: {
              value_id: this.value_id,
              appointment_id: appointment_id,
            },
            cache: false,
            refresh: true,
          }
        );
      };

      factory.fetchAppointmentproSettings = function (appointment_id) {
        return $pwaRequest.post(
          "appointmentpro/mobile_view/fetch-font-settings",
          {
            urlParams: {
              value_id: this.getValueId(),
            },
            cache: false,
            refresh: true,
          }
        );
      };

      factory.findAllClasses = function (params) {
        return $pwaRequest.post("appointmentpro/mobile_classes/findAll", {
          urlParams: {
            value_id: this.value_id,
          },
          data: params,
          cache: false,
          refresh: true,
        });
      };

      factory.getClassInfo = function (class_id) {
        return $pwaRequest.post("appointmentpro/mobile_classes/find", {
          urlParams: {
            value_id: this.value_id,
            class_id: class_id,
          },
          cache: false,
          refresh: true,
        });
      };

      factory.findLocationByCategoryId = function (category_id, params) {
        return $pwaRequest.post(
          "appointmentpro/mobile_location/find-location-by-category-id",
          {
            urlParams: {
              value_id: this.value_id,
              category_id: category_id,
            },
            data: params,
            cache: false,
            refresh: true,
          }
        );
      };

      factory.switchAccount = function (providerId) {
        return $pwaRequest.post(
          "appointmentpro/mobile_account/switch-account",
          {
            urlParams: {
              value_id: this.value_id,
              providerId: providerId,
            },
            cache: false,
            refresh: true,
          }
        );
      };

      factory.updateStatus = function (booking_id, status) {
        return $pwaRequest.post("appointmentpro/mobile_manager/update-status", {
          urlParams: {
            value_id: this.value_id,
            booking_id: booking_id,
            bstatus: status,
          },
          cache: false,
          refresh: true,
        });
      };

      factory.updatePaymentStatus = function (booking_id, status) {
        console.log("booking_id", booking_id);
        return booking_id;
        return $pwaRequest.post(
          "appointmentpro/mobile_manager/update-payment-status",
          {
            urlParams: {
              value_id: this.value_id,
              booking_id: booking_id,
              pstatus: status,
            },
            cache: false,
            refresh: true,
          }
        );
      };

      factory.findManagerBooking = function (params) {
        return $pwaRequest.post("appointmentpro/mobile_manager/find-bookings", {
          urlParams: {
            value_id: this.value_id,
          },
          data: params,
          cache: false,
          refresh: true,
        });
      };

      factory.findManagerCustomers = function (params) {
        return $pwaRequest.post(
          "appointmentpro/mobile_manager/find-customers",
          {
            urlParams: {
              value_id: this.value_id,
            },
            data: params,
            cache: false,
            refresh: true,
          }
        );
      };

      factory.managerProfile = function () {
        return $pwaRequest.post("appointmentpro/mobile_manager/profile", {
          urlParams: {
            value_id: this.value_id,
          },
          cache: false,
          refresh: true,
        });
      };

      factory.profileSave = function (params) {
        return $pwaRequest.post("appointmentpro/mobile_manager/profile-save", {
          urlParams: {
            value_id: this.value_id,
          },
          data: params,
          cache: false,
          refresh: true,
        });
      };

      factory.managerLocation = function () {
        return $pwaRequest.post("appointmentpro/mobile_manager/location", {
          urlParams: {
            value_id: this.value_id,
          },
          cache: false,
          refresh: true,
        });
      };

      factory.locationSave = function (params) {
        return $pwaRequest.post("appointmentpro/mobile_manager/location-save", {
          urlParams: {
            value_id: this.value_id,
          },
          data: params,
          cache: false,
          refresh: true,
        });
      };

      factory.findOnlinePaymentUrl = function () {
        return $pwaRequest.get(
          "appointment_id/mobile_booking/findonlinepaymenturl",
          {
            urlParams: {
              value_id: this.value_id,
            },
            cache: false,
            refresh: true,
          }
        );
      };

      factory.saveProviderGoogleToken = function (params) {
        return $pwaRequest.post("appointmentpro/mobile_provider/save-token", {
          urlParams: {
            value_id: this.value_id,
          },
          data: params,
          cache: false,
          refresh: true,
        });
      };

      return factory;
    }
  );
