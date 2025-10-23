<?php

use Siberian\Exception;
use Siberian\File;
use Siberian\Json;

/**
 * Class Appointmentpro_Mobile_ServiceController
 */
class Appointmentpro_Mobile_ServiceController extends Application_Controller_Mobile_Default
{

    public function findServiceByLocationCategoryAction()
    {
        $payload = [];

        try {
            $value_id = $this->getRequest()->getParam('value_id');
            $param = $this->getRequest()->getBodyParams();
            $location_id = $param['location_id'];
            $category_id = $param['category_id'];

            $settingModel = (new Appointmentpro_Model_Settings())->find(['value_id' => $value_id]);
            $settings = $settingModel->getData();

            $services = (new Appointmentpro_Model_Service())->findLocationServiceByCategory($location_id, $category_id);
            $serviceJson = [];

            foreach ($services as $key => $service) {
                // Only show services that are visible to user
                if (isset($service['visible_to_user']) && $service['visible_to_user'] != 1) {
                    continue;
                }
                $service['is_sale'] = false;
                $service['special_end'] = str_replace('/', '-', $service['special_end']);
                $service['special_start'] = str_replace('/', '-', $service['special_start']);

                if (!empty($service['special_end']) && !empty($service['special_start'])) {
                    $sedate = date('Y-m-d H:i:s', strtotime($service['special_end'] . ' 23:59:59'));
                    $ssdate = date('Y-m-d H:i:s', strtotime($service['special_start'] . ' 00:00:00'));
                    if (strtotime($sedate) > strtotime(date('Y-m-d H:i:s')) && strtotime($ssdate) < strtotime(date('Y-m-d H:i:s'))) {
                        $service['is_sale'] = true;
                        $service['orginal_price'] = Appointmentpro_Model_Utils::displayPrice($service['price'], Core_Model_Language::getCurrencySymbol(), $settings['number_of_decimals'], $settings['decimal_separator'], $settings['thousand_separator'], $settings['currency_position']);;
                        $service['price'] = $service['special_price'];
                    }
                }
                $service['price_with_currency'] = Appointmentpro_Model_Utils::displayPrice($service['price'], Core_Model_Language::getCurrencySymbol(), $settings['number_of_decimals'], $settings['decimal_separator'], $settings['thousand_separator'], $settings['currency_position']);

                $service['currency_symbol'] = Core_Model_Language::getCurrencySymbol();
                $serviceJson[] = $service;
            }

            $payload = [
                'success' => true,
                'services' => $serviceJson
            ];
        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }


    public function findServiceByProviderIdAction()
    {
        $payload = [];

        try {
            $value_id = $this->getRequest()->getParam('value_id');
            $param = $this->getRequest()->getBodyParams();
            $location_id = $param['location_id'];
            $provider_id = $param['provider_id'];

            $settingModel = (new Appointmentpro_Model_Settings())->find(['value_id' => $value_id]);
            $settings = $settingModel->getData();

            $services = (new Appointmentpro_Model_Provider())
                ->getProviderServices($provider_id);
            $serviceJson = [];

            foreach ($services as $key => $service) {
                $service = $service->getData();
                // Only show services that are visible to user
                if (isset($service['visible_to_user']) && $service['visible_to_user'] != 1) {
                    continue;
                }
                $service['is_sale'] = false;
                $service['special_end'] = str_replace('/', '-', $service['special_end']);
                $service['special_start'] = str_replace('/', '-', $service['special_start']);
                if (!empty($service['special_end']) && !empty($service['special_start'])) {
                    $sedate = date('Y-m-d H:i:s', strtotime($service['special_end'] . ' 23:59:59'));
                    $ssdate = date('Y-m-d H:i:s', strtotime($service['special_start'] . ' 00:00:00'));
                    if (strtotime($sedate) > strtotime(date('Y-m-d H:i:s')) && strtotime($ssdate) < strtotime(date('Y-m-d H:i:s'))) {
                        $service['is_sale'] = true;
                        $service['orginal_price'] = Appointmentpro_Model_Utils::displayPrice($service['price'], Core_Model_Language::getCurrencySymbol(), $settings['number_of_decimals'], $settings['decimal_separator'], $settings['thousand_separator'], $settings['currency_position']);;
                        $service['price'] = $service['special_price'];
                    }
                }


                $service['price_with_currency'] = Appointmentpro_Model_Utils::displayPrice($service['price'], Core_Model_Language::getCurrencySymbol(), $settings['number_of_decimals'], $settings['decimal_separator'], $settings['thousand_separator'], $settings['currency_position']);
                $service['currency_symbol'] = Core_Model_Language::getCurrencySymbol();

                $serviceJson[] = $service;
            }


            $provider = (new Appointmentpro_Model_Provider())
                ->find(['provider_id' => $provider_id]);

            $payload = [
                'success' => true,
                'services' => $serviceJson,
                'provider' => $provider->getData()
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
