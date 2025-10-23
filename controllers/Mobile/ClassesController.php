<?php

use Siberian\Exception;
use Siberian\File;
use Siberian\Json;

/**
 * Class Appointmentpro_Mobile_LocationController
 */
class Appointmentpro_Mobile_ClassesController extends Application_Controller_Mobile_Default {

	public function findallAction() {
		$payload = [];     

        try{
            $value_id = $this->getRequest()->getParam('value_id'); 
            $param = $this->getRequest()->getBodyParams(); 

            $settingModel = (new Appointmentpro_Model_Settings())->find(['value_id' => $value_id]);
            $settingsData = $settingModel->getData();
            $time_format = (string) $settingsData['time_format'] == 0 ? 'g:i A' : 'H:i';
            $date_format = (string) $settingsData['date_format'] == 0 ? 'd/m/y' : 'm/d/y';
         
            $params = [
                'latitude' => $param['latitude'],
                'longitude' => $param['longitude'],
                'category_id' => $param['category_id'],
                'sortingType' => $settingsData['default_location_sorting'],
                'findBy' => 'upcoming'
            ];

          
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

                $class['seats_left'] = $class['capacity'];
                $class['service_time'] = $class['service_time'].p__('appointmentpro', 'Min');                 
                $class['class_date'] = date($date_format, strtotime($class['class_date']));
                $class['class_time'] = date($time_format, strtotime($class['class_time']));
                $class['sale_tickets'] = (int) empty($class['sale_tickets']) ? 0 : $class['sale_tickets'];
                $class['seats_left'] = ($class['capacity'] - $class['sale_tickets']);
                $class['is_recurring'] = $class['schedule_type'] == 'never' ? false : true;

                $class['schedule_type'] =  ($class['schedule_type'] == 'never')  ? p__('appointmentpro', 'Once') : ucfirst($class['schedule_type']);
                $class['is_sale'] = false;
                $class['special_end'] = str_replace('/', '-', $class['special_end']);
                $class['special_start'] = str_replace('/', '-', $class['special_start']);

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
        
            $payload = [
                'success' => true,
                'classes' => $classesJson
            ];

        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }

    public function findAction() {
        $payload = [];     

        try{
            $value_id = $this->getRequest()->getParam('value_id');
            $class_id = $this->getRequest()->getParam('class_id'); 
           
            $settingModel = (new Appointmentpro_Model_Settings())->find(['value_id' => $value_id]);
            $settingsData = $settingModel->getData();
            $time_format = (string) $settingsData['time_format'] == 0 ? 'g:i A' : 'H:i';
            $date_format = (string) $settingsData['date_format'] == 0 ? 'd/m/y' : 'm/d/y';
   
          
            $class = (new Appointmentpro_Model_Class())->findByClassId($class_id);          
         
            $class['distance'] = round($class['distance'] / 1000, 2);
            $class['distanceUnit'] = p__('appointmentpro', 'km');
            $class['service_time'] = $class['service_time'].' '.p__('appointmentpro', 'Min');
            $class['price'] = (float) $class['price'];

            
            $class['class_date'] = date($date_format, strtotime($class['class_date']));
            $class['class_end_date'] = date($date_format, strtotime($class['class_end_date']));

            if($class['schedule_type'] != 'never'){
                $class['class_date'] = $class['class_date'].' - '.$class['class_end_date'];
            }

            $class['class_time'] = date($time_format, strtotime($class['class_time']));
            $class['total_tickets_per_user'] = (int) $class['total_tickets_per_user'];
            $class['currency'] =  Core_Model_Language::getCurrencySymbol();
            $class['sale_tickets'] = (int) empty($class['sale_tickets']) ? 0 : $class['sale_tickets'];
            $class['seats_left'] = ($class['capacity'] - $class['sale_tickets']);
            $class['schedule_type'] =  ($class['schedule_type'] == 'never')  ? p__('appointmentpro', 'Once') : ucfirst($class['schedule_type']);
            if($class['seats_left'] < $class['total_tickets_per_user']){
                $class['total_tickets_per_user'] = $class['seats_left'];
            }
            $class['is_sale'] = false;
            $class['special_end'] = str_replace('/', '-', $class['special_end']);
            $class['special_start'] = str_replace('/', '-', $class['special_start']);

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

            
            $payload = [
                'success' => true,
                'class' => $class
            ];

        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }


}