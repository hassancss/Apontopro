<?php

use Siberian\Exception;
use Siberian\File;
use Siberian\Json;

/**
 * Class Appointmentpro_Mobile_ViewController
 */
class Appointmentpro_Mobile_ViewController extends Application_Controller_Mobile_Default {
 
	public function findallAction() {
		$payload = [];       
        
        try{
            
            $value_id = $this->getRequest()->getParam('value_id');
            $param = $this->getRequest()->getBodyParams();  
            $application = $this->getApplication(); 
            $is_single_location = false;
            $sliders = (new Appointmentpro_Model_Slider())->activeSlider($value_id, array());
            $settingModel = (new Appointmentpro_Model_Settings())->find(['value_id' => $value_id]);
            $settingsData = $settingModel->getData();
            $time_format = (string) $settingsData['time_format'] == 0 ? 'g:i A' : 'H:i';
            $date_format = (string) $settingsData['date_format'] == 0 ? 'd/m/y' : 'm/d/y';
            $customerId = $this->_getCustomerId(false);
            $isProvider = false;
            $providerInfo = [];

            if(!empty($customerId) && $customerId > 0){
                $customer = (new Customer_Model_Customer())->find($customerId);
                if ($customer && $customer->getId()) {
                    $providerModel = (new Appointmentpro_Model_Provider())->find(['email' => $customer->getEmail(), 'value_id' => $value_id]);
                    if ($providerModel && $providerModel->getProviderId()) {
                        if($providerModel->getIsMobileUser()){
                            $isProvider = true;
                            $providerInfo = ['providerId' => (int) $providerModel->getProviderId(),
                                            'is_mobile_user' => (boolean) $providerModel->getIsMobileUser(),
                                            'user_role' =>  $providerModel->getUserRole(),
                                            'is_provider_layout' => (boolean) $providerModel->getIsProviderLayout(),
                                            'location_id' => (int) $providerModel->getLocationId(),
                                            'google_refresh_token' => !empty($providerModel->getGoogleRefreshToken()) ?  $providerModel->getGoogleRefreshToken() : null,
                                            'is_active_google_calendar' => (boolean) !empty($providerModel->getGoogleRefreshToken()) ? true : false
                                        ];

                        }
                    }

                }
            }

            $application = $this->getApplication();
            $appKey = $application->getKey();
            $google_redirect_URL = trim($application->getBaseUrl() . '/' . $appKey .'/appointmentpro/mobile_view/retrun-calendar');

            $settings = [
                'home_slider' => (boolean) $settingsData['home_slider'],
                'booking_type' => (int) $settingsData['booking_type'],
                'is_service_booking' => (boolean) ($settingsData['booking_type'] == 1 || $settingsData['booking_type'] == 3) ? true : false,
                'is_class_booking' => (boolean) ($settingsData['booking_type'] == 2 || $settingsData['booking_type'] == 3) ? true : false,
                'enable_booking' => (boolean) $settingsData['enable_booking'],
                'online_payment' => (boolean) $settingsData['online_payment'],
                'offline_payment' => (boolean) $settingsData['offline_payment'],
                'price_hide' => (boolean) $settingsData['price_hide'],
                'display_tax' => (boolean) $settingsData['display_tax'],
                'tax_percentage' => (double) $settingsData['tax_percentage'],
                'enable_acceptance_rejection' => (boolean) $settingsData['enable_acceptance_rejection'],
                'display_multi_appointments' => (boolean) $settingsData['display_multi_appointments'],
                'time_format' => (string) $settingsData['time_format'] == 0 ? 'g:i A' : 'H:i',
                'date_format' => (string) $settingsData['date_format'] == 0 ? 'd/m/y' : 'm/d/y',
                'cancel_criteria' => (int) $settingsData['cancel_criteria'],
                'cancellation_charges' => (double) $settingsData['cancellation_charges'],
                'cancel_policy' =>  !empty($settingsData['cancel_policy']) ? $settingsData['cancel_policy'] : false ,
                'owner_email' => (string) $settingsData['owner_email'],
                'confirmation_email' => (int) $settingsData['confirmation_email'],
                'reminder_email' => (int) $settingsData['reminder_email'],
                'confirmation_sms' => (int) $settingsData['confirmation_sms'],
                'reminder_sms' => (int) $settingsData['reminder_sms'],
                'currency_position' => (string) $settingsData['currency_position'],
                'decimal_separator' => (string) $settingsData['decimal_separator'],
                'thousand_separator' => (string) $settingsData['thousand_separator'],
                'number_of_decimals' => (string) $settingsData['number_of_decimals'],
                'enable_search' => (double) $settingsData['enable_search'],
                'list_design' => (double) $settingsData['list_design'],                
                'enable_plc_points'=> (boolean) $settingsData['enable_plc_points'],
                'home_provider' => (boolean) $settingsData['home_provider'],
                'home_category' => (boolean) $settingsData['home_category'],
                'default_location_sorting' => (string) $settingsData['default_location_sorting'],
                'isProvider' => $isProvider,
                'providerInfo' => $providerInfo,
                'google_redirect_URL' => $google_redirect_URL,
                'client_id' => $settingsData['client_id'],
                'client_secret' => $settingsData['client_secret'],
                'enable_google_calendar' => (boolean) $settingsData['enable_google_calendar'],
                'booking_without_payment' => (boolean) $settingsData['booking_without_payment']
            ];

            if($settings['isProvider'] && $settings['providerInfo']['is_provider_layout']){
 
                $bookingParams = [];
                $bookingParams['user_role'] = $settings['providerInfo']['user_role'];
                $bookingParams['location_id'] = $settings['providerInfo']['location_id'];
                if($bookingParams['user_role'] == 'provider'){
                    $bookingParams['provider_id'] = $settings['providerInfo']['providerId'];
                }
                if($settings['is_service_booking']){
                    $bookingParams['service_type'] = 'services';
                }else{
                    $bookingParams['service_type'] = 'classes';
                }                
                
                $bookingParams['limit'] = 10;
                $bookingParams['offset'] = $param["offset"];
                if($param['tab'] != 'new' ){
                    $bookingParams['type'] = $param['tab'];
                }else{
                    $bookingParams['type'] = 'upcoming';
                    $bookingParams['status'] = 9;
                }                

                $bookings = (new Appointmentpro_Model_Booking())->findByValueIdForApp($value_id, $bookingParams);

                ($settingsData['time_format'] == 1) ? $timeFormat = '' : $timeFormat = 'A';
                ($settingsData['date_format'] == 1) ? $dateFormat = 'm/d/y' : $dateFormat = 'd/m/y';        

                $bookingJson = [];
                foreach ($bookings as $booking) {
                    $data = $booking->getData();
                    $data['customer'] = $data['firstname'].' '.$data['lastname'];
                    $data['booking_date'] = date($dateFormat, $data['appointment_date']);                    
                    $data['start_time'] = Appointmentpro_Model_Utils::timestampTotime($data['appointment_time'], $timeFormat);
                    $data['end_time'] = Appointmentpro_Model_Utils::timestampTotime($data['appointment_end_time'], $timeFormat);
                    $data['currency_symbol'] = Core_Model_Language::getCurrencySymbol();
                    $data['amount_with_currency'] = Appointmentpro_Model_Utils::displayPrice($data['total_amount'], Core_Model_Language::getCurrencySymbol(), $settingsData['number_of_decimals'], $settingsData['decimal_separator'], $settingsData['thousand_separator'], $settingsData['currency_position']);

                    $data['text_color'] = Appointmentpro_Model_Appointment::getBookingTextcolor($data['status']);
                    $data['payment_text_color'] = Appointmentpro_Model_Appointment::getPaymentTextcolor($data['payment_status']);     
                   
                    $data['is_completed_hide'] = false;
                    $data['is_rejected_hide'] = false;
                    $data['is_accepted_hide'] = false;
                    $data['is_rejected_accepted_action'] = false;

                    if(in_array($data['status'], ['4', '6', '5'])){
                        $data['is_completed_hide'] = true;
                    }
                    if($data['status'] == 8){
                        $data['is_rejected_hide'] = true;
                    }
                    if($data['status'] == 3){
                        $data['is_accepted_hide'] = true;
                    }
                    if($data['status'] < 3){
                        $data['is_rejected_accepted_action'] = true;
                    }

                    $data['payment_status'] = p__('appointmentpro', Appointmentpro_Model_Appointment::getPaymentStatus($data['payment_status']));
                    $data['status'] = p__('appointmentpro',  Appointmentpro_Model_Appointment::getBookingStatus($data['status']));

                    $data['payment_type'] = ($data['payment_type'] == 'cod') ?  p__("appointmentpro",  "Cash") : ucfirst($data['payment_type']);                   
                    $bookingJson[] = $data;
                }

                $labels = (new Appointmentpro_Model_Labelname())->findAll(array('value_id' => $value_id));
                
                $bookingParams = [];
                $bookingParams['user_role'] = $settings['providerInfo']['user_role'];
                $bookingParams['location_id'] = $settings['providerInfo']['location_id'];
                if($bookingParams['user_role'] == 'provider'){
                    $bookingParams['provider_id'] = $settings['providerInfo']['providerId'];
                }                
                if($settings['is_service_booking']){
                    $bookingParams['service_type'] = 'services';
                }else{
                    $bookingParams['service_type'] = 'classes';
                }            
                $bookingParams['type'] = 'upcoming';
                $bookingParams['status'] = 9;                            
                $countNew =   (new Appointmentpro_Model_Booking())->countByValueIdForApp($value_id, $bookingParams);
 
                $labelJson = [];
                foreach ($labels as $lKey => $lValue) {
                    $lValue = $lValue->getData();
                    $labelJson[$lValue['label_key']] = $lValue['label_name'];
                }
                $param['tab'] = ($param['tab'] == 'new') ? 'Pending Approval' : $param['tab'];
                if($settings['is_service_booking']){
                    $sub_title = p__('appointmentpro', ucfirst($param['tab']).' Appointments' );
                }else{
                    $sub_title = p__('appointmentpro', ucfirst($param['tab']).' Bookings' );
                }
             
                $payload = [
                    'success' => true,
                    'page_title' => $this->getCurrentOptionValue()->getTabbarName(),
                    'settings' => $settings,
                    'bookingJson' => $bookingJson,
                    'bookingParams' => $bookingParams,
                    'labels' => $labelJson,
                    'sub_title' => $sub_title,
                    'countNew' => (integer) $countNew[0]
                ];
                
            }else{

                $timezones = DateTimeZone::listIdentifiers();
                $time_zone = $timezones[$sv['timezone']]; 
             

                $params = [
                    'latitude' => $param['latitude'],
                    'longitude' => $param['longitude'],
                    'sortingType' => $settingsData['default_location_sorting'],
                    'offset' => 0,
                    'limit' => 10,
                    'findBy' => 'upcoming',
                    'timezone' => $time_zone,
                ];

                $times = Appointmentpro_Model_Utils::timeOptions(); 
                $locations = (new Appointmentpro_Model_Location())->findByAppId($value_id, $params);
                $locationsJson = [];

                
                foreach ($locations as $key => $location) {
                    $location['business_timing'] =  $this->getActiveBusinessTiming($location['business_timing']);
                    $fromTime = $location['business_timing']['from_time'];
                    $toTime = $location['business_timing']['to_time'];

                    $location['fromTime'] = $times[min($fromTime)];
                    $location['toTime'] = $times[max($toTime)]; 
                    $location['fromTime'] = date($time_format, strtotime($location['fromTime']));
                    $location['toTime'] = date($time_format, strtotime($location['toTime']));
                    $location['is_business_timing'] = (boolean)count($location['business_timing']['from_time']);
                   
                    $location['distance'] = round($location['distance'] / 1000, 2); // KM
                    if($settingsData['distance_unit'] == 'm'){
                        $location['distance'] = round($location['distance'] * 1000, 2); // Meters
                    }
                    if($settingsData['distance_unit'] == 'mi'){
                        $location['distance'] = round($location['distance'] * 0.621, 2); // Miles
                    }
                    $location['distanceUnit'] = p__('appointmentpro', $settingsData['distance_unit']);

                    if(empty($location['latitude']) || empty($location['longitude'])){
                        $location['distance'] = 0;
                    }
                                    
                    $locationsJson[] = $location;
                }
     
                $classes = (new Appointmentpro_Model_Class())->findAllClassesByValueId($value_id, $params);
                $classesJson = [];   

                foreach ($classes as $key => $class) {
                    
                    $class['distance'] = round($class['distance'] / 1000, 2); // KM
                    if($settingsData['distance_unit'] == 'm'){
                        $class['distance'] = round($class['distance'] * 1000, 2); // Meters
                    }
                    if($settingsData['distance_unit'] == 'mi'){
                        $class['distance'] = round($class['distance'] * 0.621, 2); // Miles
                    }
                    $class['distanceUnit'] = p__('appointmentpro', $settingsData['distance_unit']);
                    $class['service_time'] = $class['service_time'].p__('appointmentpro', 'Min');               

                    $class['class_date'] = date($date_format, strtotime($class['class_date']));
                    $class['class_time'] = date($time_format, strtotime($class['class_time']));
                    $class['sale_tickets'] = (int) empty($class['sale_tickets']) ? 0 : $class['sale_tickets'];
                    $class['seats_left'] = ($class['capacity'] - $class['sale_tickets']);
                    $class['is_sale'] = false;
                    $class['is_recurring'] = $class['schedule_type'] == 'never' ? false : true;
                    $class['schedule_type'] = ucfirst($class['schedule_type']);

                    if(!empty($class['special_end']) && !empty($class['special_start'])){ 
                        $sedate = date('Y-m-d H:i:s', strtotime($class['special_end']. ' 23:59:59'));
                        $ssdate = date('Y-m-d H:i:s', strtotime($class['special_start']. ' 00:00:00'));
                        if(strtotime($sedate) > strtotime(date('Y-m-d H:i:s')) && strtotime($ssdate) < strtotime(date('Y-m-d H:i:s'))){
                            $class['is_sale'] = true;
                            $class['orginal_price'] = Appointmentpro_Model_Utils::displayPrice($class['price'], Core_Model_Language::getCurrencySymbol(), $settingsData['number_of_decimals'], $settingsData['decimal_separator'], $settingsData['thousand_separator'], $settingsData['currency_position']);;
                            $class['price'] = $class['special_price'];
                            
                        }
                    }

                    $price_with_currency = Appointmentpro_Model_Utils::displayPrice($class['price'], Core_Model_Language::getCurrencySymbol(), $settingsData['number_of_decimals'], $settingsData['decimal_separator'], $settingsData['thousand_separator'], $settingsData['currency_position']);
                    $class['price_with_currency'] = $class['price'] >= 0 ?  $price_with_currency : p__('appointmentpro', 'Free');
                   
                    $classesJson[] = $class;
                }  

                if(count($locationsJson) == 1 && count($classesJson) == 0){
                    $is_single_location = true;
                    $single_location_id = $locationsJson[0]['location_id'];
                }

                $providers = [];
                if($settings['home_provider']){
                    $providers = (new Appointmentpro_Model_Provider())
                                    ->findActiveAllProviderForApp($value_id);
                }

                $labels = (new Appointmentpro_Model_Labelname())->findAll(array('value_id' => $value_id)); 
                $labelJson = [];
                foreach ($labels as $lKey => $lValue) {
                    $lValue = $lValue->getData();
                    $labelJson[$lValue['label_key']] = $lValue['label_name'];
                }
               
                $categoriesJson = [];
                if($settings['home_category']){               
                    $categories = (new Appointmentpro_Model_Category())->findAppByValueId($value_id);
                    foreach ($categories as $cKey => $cValue) {
                        $cValue = $cValue->getData();
                        $cValue['category_for'] = (int) $cValue['category_for'];
                        $categoriesJson[] = $cValue;
                    }
                }

                $payload = [
                    'success' => true,
                    'page_title' => $this->getCurrentOptionValue()->getTabbarName(),
                    'sliders' => $sliders,
                    'settings' => $settings,
                    'classes' => $classesJson,
                    'locations' => $locationsJson,
                    'is_single_location' => $is_single_location,
                    'single_location_id' => $single_location_id,
                    'providers' => $providers,
                    'labels' => $labelJson,
                    'categories' => $categoriesJson
                ];

            }

        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }



    private function getActiveBusinessTiming($business_timing){
        $business_timing = Siberian_Json::decode($business_timing);
        $business_timing_active = [];
        foreach ($business_timing['from_time'] as $key => $value) {
             if($business_timing['is_active'][$key] == 1){
                $business_timing_active['from_time'][$key] = $value;
                $business_timing_active['is_active'][$key] = 1;
                $business_timing_active['to_time'][$key] = $business_timing['to_time'][$key];
             }
        }
        return $business_timing_active;
    }


    public function fetchFontSettingsAction() {
        $payload = [];       
        
        try{            
            $value_id = $this->getRequest()->getParam('value_id'); 
            $settingModel = (new Appointmentpro_Model_Settings())->find(['value_id' => $value_id]);
            $settingsData = $settingModel->getData();

            $customerId = $this->_getCustomerId(false);
            $isProvider = false;
            $providerInfo = [];

            if(!empty($customerId) && $customerId > 0){
                $customer = (new Customer_Model_Customer())->find($customerId);
                if ($customer && $customer->getId()) {
                    $providerModel = (new Appointmentpro_Model_Provider())->find(['email' => $customer->getEmail(), 'value_id' => $value_id ]);
                    if ($providerModel && $providerModel->getProviderId()) {
                        if($providerModel->getIsMobileUser()){
                            $isProvider = true;
                            $providerInfo = ['is_provider_layout' => (int) $providerModel->getIsProviderLayout(),
                                            'providerId' => (int) $providerModel->getProviderId(),
                                            'is_mobile_user' => (boolean) $providerModel->getIsMobileUser(),
                                            'user_role' =>  $providerModel->getUserRole(),
                                            'is_provider_layout' => (boolean) $providerModel->getIsProviderLayout(),
                                            'google_refresh_token' => !empty($providerModel->getGoogleRefreshToken()) ?  $providerModel->getGoogleRefreshToken() : null,
                                            'is_active_google_calendar' => (boolean) !empty($providerModel->getGoogleRefreshToken()) ? true : false
                                        ];
                        }
                    }

                }
            }

            $application = $this->getApplication();
            $appKey = $application->getKey();           
            $google_redirect_URL = trim($application->getBaseUrl() . '/' . $appKey .'/appointmentpro/mobile_view/retrun-calendar');
         
            $settings = [
                'home_slider' => (boolean) $settingsData['home_slider'],
                'booking_type' => (int) $settingsData['booking_type'],
                'is_service_booking' => (boolean) ($settingsData['booking_type'] == 1 || $settingsData['booking_type'] == 3) ? true : false,
                'is_class_booking' => (boolean) ($settingsData['booking_type'] == 2 || $settingsData['booking_type'] == 3) ? true : false,
                'enable_booking' => (boolean) $settingsData['enable_booking'],
                'online_payment' => (boolean) $settingsData['online_payment'],
                'offline_payment' => (boolean) $settingsData['offline_payment'],
                'price_hide' => (boolean) $settingsData['price_hide'],
                'display_tax' => (boolean) $settingsData['display_tax'],
                'tax_percentage' => (double) $settingsData['tax_percentage'],
                'enable_acceptance_rejection' => (boolean) $settingsData['enable_acceptance_rejection'],
                'display_multi_appointments' => (boolean) $settingsData['display_multi_appointments'],
                'time_format' => (string) $settingsData['time_format'] == 0 ? 'g:i A' : 'H:i',
                'date_format' => (string) $settingsData['date_format'] == 0 ? 'd/m/y' : 'm/d/y',
                'cancel_criteria' => (int) $settingsData['cancel_criteria'],
                'cancellation_charges' => (double) $settingsData['cancellation_charges'],
                'cancel_policy' =>  !empty($settingsData['cancel_policy']) ? $settingsData['cancel_policy'] : false ,
                'owner_email' => (string) $settingsData['owner_email'],
                'confirmation_email' => (int) $settingsData['confirmation_email'],
                'reminder_email' => (int) $settingsData['reminder_email'],
                'confirmation_sms' => (int) $settingsData['confirmation_sms'],
                'reminder_sms' => (int) $settingsData['reminder_sms'],
                'currency_position' => (string) $settingsData['currency_position'],
                'decimal_separator' => (string) $settingsData['decimal_separator'],
                'thousand_separator' => (string) $settingsData['thousand_separator'],
                'number_of_decimals' => (string) $settingsData['number_of_decimals'],
                'enable_search' => (double) $settingsData['enable_search'],
                'list_design' => (double) $settingsData['list_design'],                
                'enable_plc_points'=> (boolean) $settingsData['enable_plc_points'],
                'home_provider' => (boolean) $settingsData['home_provider'],
                'default_location_sorting' => (string) $settingsData['default_location_sorting'],
                'isProvider' => $isProvider,
                'providerInfo' => $providerInfo,
                'enable_location' => (boolean) $settingsData['enable_location'],
                'google_redirect_URL' => $google_redirect_URL,
                'client_id' => $settingsData['client_id'],
                'client_secret' => $settingsData['client_secret'],
                'enable_google_calendar' => (boolean) $settingsData['enable_google_calendar'],
                'booking_without_payment' => (boolean) $settingsData['booking_without_payment']        
            ];

            $labels = (new Appointmentpro_Model_Labelname())->findAll(array('value_id' => $value_id)); 

            $labelJson = [];
            foreach ($labels as $lKey => $lValue) {
                $lValue = $lValue->getData();
                $labelJson[$lValue['label_key']] = $lValue['label_name'];
            }

            $payload = [
                'success' => true,
                'settings' => $settings,
                'labels' => $labelJson
                 
            ];

        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }


    public function retrunCalendarAction()
    {   
        if(!empty($_GET['code']) && !empty($_GET['state'])){
            $state = $_GET['state'];
            $stateArray = explode(",", $state);
            $value_id = $stateArray[0];
            $provider_id = $stateArray[1];
            $platfrom = $stateArray[2];

            $this->saveTokenAction($value_id, $_GET['code'], $provider_id);
        }       

        if($platfrom == 'browser' || $platfrom == 'overview'){
           
            $value_id = (new Appointmentpro_Model_Appointmentpro())->getCurrentValueId();
            $application = $this->getApplication();
            $appKey = $application->getKey();  
            $appointmentURl = trim($application->getBaseUrl() . '/' . $appKey .'/appointmentpro/'. $value_id.'/google/'.base64_encode($_GET['code']));
            
            header("Location: ". $appointmentURl);        
            
        }else{
          
            echo '<h1 style="top: 50%;text-align: center;">Your Google Calendar Enabled now!<h1>';
        }     
        die();
    }


    private function saveTokenAction($value_id, $code, $provider_id ) { 
 
        $setting = (new Appointmentpro_Model_Settings())->find($value_id, "value_id");
        $settingResult = $setting->getData();
        
        $application = $this->getApplication();
        $appKey = $application->getKey();
        $google_redirect_URL = trim($application->getBaseUrl() . '/' . $appKey .'/appointmentpro/mobile_view/retrun-calendar');
        
        $google = (new Appointmentpro_Model_GoogleService());
        $google->setClientId($settingResult['client_id']);
        $google->setRedirectUri($google_redirect_URL);
        $google->setClientSecret($settingResult['client_secret']);
        $accessToken = $google->GetAccessToken($code);
 
        if(empty($accessToken['error'])){
            $model = (new Appointmentpro_Model_Provider())
                ->find(['provider_id' => $provider_id ])
                ->setGoogleRefreshToken($accessToken['refresh_token']);

            $model->save();   
        }              
    
        return true;
    }
  



         /**
     * @param bool $throw
     * @return mixed|null
     * @throws Exception
     * @throws Zend_Session_Exception
     */
        private function _getCustomerId($throw = true)
        {
            $request = $this->getRequest();
            $session = $this->getSession();
            $customerId = $session->getCustomerId();

            if ($throw && empty($customerId)) {
                throw new Exception(p__('appointmentpro', 'Customer login required!'));
            }
            return $customerId;
        }

 
    
}

