<?php

use Siberian\Exception;
use Siberian\File;
use Siberian\Json;

/**
 * Class Appointmentpro_Mobile_LocationController
 */
class Appointmentpro_Mobile_LocationController extends Application_Controller_Mobile_Default
{

    public function findallAction()
    {
        $payload = [];

        try {
            $value_id = $this->getRequest()->getParam('value_id');
            $application = $this->getApplication();
            $param = $this->getRequest()->getBodyParams();

            $settingModel = (new Appointmentpro_Model_Settings())->find(['value_id' => $value_id]);
            $settingsData = $settingModel->getData();
            $time_format = (string) $settingsData['time_format'] == 0 ? 'g:i A' : 'H:i';
            $date_format = (string) $settingsData['date_format'] == 0 ? 'd/m/y' : 'm/d/y';
            $params = [
                'latitude' => $param['latitude'],
                'longitude' => $param['longitude'],
                'sortingType' => $settingsData['default_location_sorting']
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

                $location['is_business_timing'] = (bool)count($location['business_timing']['from_time']);

                $location['distance'] = round($location['distance'] / 1000, 2); // KM
                if ($settingsData['distance_unit'] == 'm') {
                    $location['distance'] = round($location['distance'] * 1000, 2); // Meters
                }
                if ($settingsData['distance_unit'] == 'mi') {
                    $location['distance'] = round($location['distance'] * 0.621, 2); // Miles
                }
                $location['distanceUnit'] = p__('appointmentpro', $settingsData['distance_unit']);

                if (empty($location['latitude']) || empty($location['longitude'])) {
                    $location['distance'] = 0;
                }


                $locationsJson[] = $location;
            }

            $payload = [
                'success' => true,
                'locations' => $locationsJson
            ];
        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }


    public function findLocationByCategoryIdAction()
    {
        $payload = [];

        try {
            $value_id = $this->getRequest()->getParam('value_id');
            $category_id = $this->getRequest()->getParam('category_id');
            $application = $this->getApplication();
            $param = $this->getRequest()->getBodyParams();

            $settingModel = (new Appointmentpro_Model_Settings())->find(['value_id' => $value_id]);
            $settingsData = $settingModel->getData();

            $time_format = (string) $settingsData['time_format'] == 0 ? 'g:i A' : 'H:i';
            $date_format = (string) $settingsData['date_format'] == 0 ? 'd/m/y' : 'm/d/y';

            $params = [
                'latitude' => $param['latitude'],
                'longitude' => $param['longitude'],
                'sortingType' => $settingsData['default_location_sorting'],
                'value_id' => $value_id
            ];

            $times = Appointmentpro_Model_Utils::timeOptions();
            $locations = (new Appointmentpro_Model_Category())->findLocationByCategoryId($category_id, $params);
            $locationsJson = [];

            foreach ($locations as $key => $location) {
                $location['business_timing'] =  $this->getActiveBusinessTiming($location['business_timing']);
                $fromTime = $location['business_timing']['from_time'];
                $toTime = $location['business_timing']['to_time'];

                $location['fromTime'] = $times[min($fromTime)];
                $location['toTime'] = $times[max($toTime)];
                $location['fromTime'] = date($time_format, strtotime($location['fromTime']));
                $location['toTime'] = date($time_format, strtotime($location['toTime']));


                $location['is_business_timing'] = (bool)count($location['business_timing']['from_time']);

                $location['distance'] = round($location['distance'] / 1000, 2); // KM
                if ($settingsData['distance_unit'] == 'm') {
                    $location['distance'] = round($location['distance'] * 1000, 2); // Meters
                }
                if ($settingsData['distance_unit'] == 'mi') {
                    $location['distance'] = round($location['distance'] * 0.621, 2); // Miles
                }
                $location['distanceUnit'] = p__('appointmentpro', $settingsData['distance_unit']);

                if (empty($location['latitude']) || empty($location['longitude'])) {
                    $location['distance'] = 0;
                }

                $locationsJson[] = $location;
            }

            $payload = [
                'success' => true,
                'locations' => $locationsJson
            ];
        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }

    private function getActiveBusinessTiming($business_timing)
    {
        $business_timing = Siberian_Json::decode($business_timing);
        $business_timing_active = [];
        foreach ($business_timing['from_time'] as $key => $value) {
            if ($business_timing['is_active'][$key] == 1) {
                $business_timing_active['from_time'][$key] = $value;
                $business_timing_active['is_active'][$key] = 1;
                $business_timing_active['to_time'][$key] = $business_timing['to_time'][$key];
            }
        }
        return $business_timing_active;
    }



    public function findAction()
    {
        $payload = [];

        try {
            $value_id = $this->getRequest()->getParam('value_id');
            $location_id = $this->getRequest()->getParam('location_id');

            $settingModel = (new Appointmentpro_Model_Settings())->find(['value_id' => $value_id]);
            $settingsData = $settingModel->getData();
            $time_format = (string) $settingsData['time_format'] == 0 ? 'g:i A' : 'H:i';
            $date_format = (string) $settingsData['date_format'] == 0 ? 'd/m/y' : 'm/d/y';


            $times = Appointmentpro_Model_Utils::timeOptions();
            $location = (new Appointmentpro_Model_Location())->findById($location_id);

            $location['business_timing'] =  Siberian_Json::decode($location['business_timing']);
            $fromTime = $location['business_timing']['from_time'];
            $toTime = $location['business_timing']['to_time'];

            $location['fromTime'] = $times[min($fromTime)];
            $location['toTime'] = $times[max($toTime)];
            $location['fromTime'] = date($time_format, strtotime($location['fromTime']));
            $location['toTime'] = date($time_format, strtotime($location['toTime']));

            $location['is_business_timing'] = (bool)count($location['business_timing']['from_time']);

            $openings = [];
            foreach ($location['business_timing']['is_active'] as $key => $value) {
                $openings[$key]['name'] =  p__('appointmentpro', ucfirst($key));
                $openings[$key]['status'] =  (bool) $value;

                $openings[$key]['fromTime'] = date($time_format, strtotime($times[$fromTime[$key]]));
                $openings[$key]['toTime'] = date($time_format, strtotime($times[$toTime[$key]]));
            }
            $location['openings'] = $openings;

            /*Locations photos*/
            $galleries = (new Appointmentpro_Model_LocationGallery())
                ->findAll(['location_id' => $location_id, 'is_delete' => 0, 'is_active' => 1])->toArray();
            $location['images'] = $galleries;

            /*Location Categories*/
            $location['categories'] = (new Appointmentpro_Model_Category())->findByLocationId($location_id, ['category_for' => 1]);

            /*Locations providers*/
            $providers = (new Appointmentpro_Model_Provider())
                ->findAll(['location_id' => $location_id, 'is_delete' => 0, 'is_active' => 1], 'position ASC')->toArray();
            $location['providers'] = $providers;



            $payload = [
                'success' => true,
                'location' => $location
            ];
        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }


    public function findServicesByCategoryAction()
    {
        $payload = [];

        try {
            $value_id = $this->getRequest()->getParam('value_id');
            $location_id = $this->getRequest()->getParam('location_id');
            $category_id = $this->getRequest()->getParam('category_id');
            $settingModel = (new Appointmentpro_Model_Settings())->find(['value_id' => $value_id]);
            $settings = $settingModel->getData();
            $services = (new Appointmentpro_Model_Service())->findLocationServiceByCategory($location_id, $category_id);

            $servicesJson = [];
            foreach ($services as $key => $value) {
                // Only show services that are visible to user
                if (isset($value['visible_to_user']) && $value['visible_to_user'] != 1) {
                    continue;
                }
                $value['is_sale'] = false;
                $value['special_end'] = str_replace('/', '-', $value['special_end']);
                $value['special_start'] = str_replace('/', '-', $value['special_start']);

                if (!empty($value['special_end']) && !empty($value['special_start'])) {

                    $sedate = date('Y-m-d H:i:s', strtotime($value['special_end'] . ' 23:59:59'));
                    $ssdate = date('Y-m-d H:i:s', strtotime($value['special_start'] . ' 00:00:00'));
                    if (strtotime($sedate) > strtotime(date('Y-m-d H:i:s')) && strtotime($ssdate) < strtotime(date('Y-m-d H:i:s'))) {
                        $value['is_sale'] = true;
                        $value['orginal_price'] = Appointmentpro_Model_Utils::displayPrice($value['price'], Core_Model_Language::getCurrencySymbol(), $settings['number_of_decimals'], $settings['decimal_separator'], $settings['thousand_separator'], $settings['currency_position']);;
                        $value['price'] = $value['special_price'];
                    }
                }

                $value['currency_symbol'] = Core_Model_Language::getCurrencySymbol();
                $value['price_with_currency'] = Appointmentpro_Model_Utils::displayPrice($value['price'], Core_Model_Language::getCurrencySymbol(), $settings['number_of_decimals'], $settings['decimal_separator'], $settings['thousand_separator'], $settings['currency_position']);

                $servicesJson[] = $value;
            }

            $payload = [
                'success' => true,
                'services' => $servicesJson
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
