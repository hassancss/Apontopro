<?php

/**
 * Class Appointmentpro_ClassController
 */
class Appointmentpro_ClassController extends Application_Controller_Default
{

     /**
     *Home screen
     */
    public function listAction() {
        $this->loadPartials();
    }

    /**
     *Class add
     */
    public function addAction() {
        $this->loadPartials();
    }

    /**
     *Class Edit
     */
    public function editAction() {
        $model = (new Appointmentpro_Model_Class());  
        if ($id = $this->getRequest()->getParam('id')) {
            $model->find($id); 
            if (!$model->getServiceId()) {
                    $this->getRequest()->addError( p__("appointmentpro",  "This class does not exist."));
            }
        }
        $this->loadPartials();
        $this->getLayout()->getPartial('content')->setService($model);
         
    }

    /**
     *Class delete
     */
    public function deleteAction() {
        
        try {

            $model = (new Appointmentpro_Model_Class());  
            if ($id = $this->getRequest()->getParam('id')) {
                $model->find($id); 
                if (!$model->getServiceId()) {
                        $this->getRequest()->addError( p__("appointmentpro",  "This class does not exist."));
                }else{
                    $model->setIsDelete(1);
                    $model->save();
                }
            }
        
            $payload = [
                'success' => true,
                'message' => p__('appointmentpro', 'Successfully deleted'),
                'datas' => $datas
            ];
       
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
         
    }
    
    /**
     *Class Save
     */
    public function saveAction() {
       
        if($param = $this->getRequest()->getPost()) {
      
        try {
       
          $status = $param['status'] == 1 ? 1: 0;
          $model = (new Appointmentpro_Model_Class())
                    ->find(['service_id' => $param['service_id']])
                    ->setValueId($param['value_id'])
                    ->setName($param['name'])
                    ->setPrice($param['price'])
                    ->setSpecialPrice($param['special_price'])
                    ->setSpecialStart($param['special_start'])
                    ->setSpecialEnd($param['special_end'])
                    ->setServiceTime($param['service_time'])
                    ->setServicePoints($param['service_points'])
                    ->setCategoryId($param['category_id'])
                    ->setDescription($param['description'])
                    ->setCapacity($param['capacity'])
                    ->setClassDate($param['class_date'])                    
                    ->setClassTime($param['class_time'])
                    ->setProviderId($param['provider_id'])
                    ->setTotalTicketsPerUser($param['total_tickets_per_user'])
                    ->setServiceType(2)
                    ->setScheduleType($param['schedule_type'])
                    ->setStatus($status);


            if(!empty($param['weekday_picker'])) {
                $model->setDayOfWeek(implode(",", $param['weekday_picker'])); 
            }else{
                $model->setDayOfWeek('');
            }

            if($param['schedule_type'] == 'monthly') {
                $model->setDayOfMonth(date("d", strtotime($param['class_date']))); 
            }

            if($param['schedule_type'] == 'never') {
                $model->setClassEndDate($param['class_date']); 
            }else{
                 $model->setClassEndDate($param['class_end_date']);
            }

            if(!empty($param['featured_image'])){
                foreach ($param['featured_image'] as $iKey => $iValue) {
                    if (file_exists(Core_Model_Directory::getTmpDirectory(true) . "/" . $iValue)) {
                            list($relativePath, $filename) = $this->_getImageData($iValue);
                            $imageURL = $relativePath . '/' . $filename;
                            $model->setFeaturedImage($imageURL);
                    }
                }
            }

            $model->save();            
            $service_id = $model->getId();

            if(!empty($param['location_id'])){
                $modelUpdateLocation =  (new Appointmentpro_Model_ServiceLocation())->updateDeleteStatus('service_id', $service_id);
                
                $modelServiceLocation = (new Appointmentpro_Model_ServiceLocation())
                ->find(['service_id' => $service_id, 'location_id' => $param['location_id']])
                ->setServiceId($service_id)
                ->setLocationId($param['location_id'])
                ->setIsDelete(0)
                ->save();
           
            }
  
            $payload = [
                    'success' => true,
                    'message' => p__('appointmentpro', 'Successfully save'),
                    'datas' => $datas
                ];

            } catch (\Exception $e) {
                $payload = [
                    'error' => true,
                    'message' => $e->getMessage(),
                ];
            }

            $this->_sendJson($payload);
        }
    }

       /**
     * @param $image
     * @return array
     * @throws Siberian_Exception
     */
    private function _getImageData($image) {

        $img_src = Core_Model_Directory::getTmpDirectory(true) . "/" . $image;

        $info = pathinfo($img_src);
        $filename = $info['basename'];
        $relativePath = '/appointmentpro/'.$this->getApplication()->getId().'/class';
        $img_dst = Application_Model_Application::getBaseImagePath() . $relativePath;

        if (!is_dir($img_dst)) {
            mkdir($img_dst, 0777, true);
        }
        $img_dst .= '/' . $filename;
        rename($img_src, $img_dst);
        
        if (!file_exists($img_dst)) {
            throw new Siberian_Exception(p__('appointmentpro', 'An error occurred while saving your picture. Please try againg later.'));
        }
        return [$relativePath, $filename];
    }
 


  /**
     * fetch Class
     */
     public function findAllAction() {
        
        try {
            $request = $this->getRequest();
            $limit = $request->getParam("perPage", 25);
            $offset = $request->getParam("offset", 0);
            $sorts = $request->getParam("sorts", []);
            $queries = $request->getParam("queries", []);
             
            $filter = null;
            if (array_key_exists("search", $queries)) {
                $filter = $queries["search"];
            }
            
            $params = [
                "limit" => $limit,
                "offset" => $offset,
                "sorts" => $sorts,
                "filter" => $filter,
           ];
          
            $value_id = (new Appointmentpro_Model_Appointmentpro())->getCurrentValueId();
        
            $setting = (new Appointmentpro_Model_Settings())->find($value_id, "value_id");
            $settingResult = $setting->getData();
            ($settingResult['time_format'] == 1) ? $timeFormat = '' : $timeFormat = 'A';
            ($settingResult['date_format'] == 1) ? $dateFormat = 'm/d/y' : $dateFormat = 'd/m/y';

            $services = (new Appointmentpro_Model_Class())
                ->findByValueId($value_id, $params);

            $countAll = (new Appointmentpro_Model_Class())->countAllForApp($value_id);
            $countFiltered =   (new Appointmentpro_Model_Class())->countAllForApp($value_id, $params);

            $servicesJson = [];
            foreach ($services as $service) {
                $data = $service->getData();
                $data['status'] = $data['status'] == 1 ? p__('appointmentpro', "Active") : p__('appointmentpro', "In Active"); 
                $data['featured_image'] = empty($data['featured_image']) ? '/app/local/modules/Appointmentpro/resources/design/desktop/flat/images/dummy-image.jpg' : '/images/application/'.$data['featured_image'];
                $data['currency'] = $this->getApplication()->getCurrency();
                $data['sale_tickets'] = empty($data['sale_tickets']) ? 0 : $data['sale_tickets'];
                $data['schedule_type'] =  $data['schedule_type'] == 'never'  ? 'Once' : $data['schedule_type'] ;
                $data['schedule_type'] = p__('appointmentpro', ucfirst($data['schedule_type']));
                
                $data['is_sale'] = false;
                $sedate = date('Y-m-d H:i:s', strtotime($data['special_end']. ' 23:59:59'));
                $ssdate = date('Y-m-d H:i:s', strtotime($data['special_start']. ' 00:00:00'));
                if(strtotime($sedate) > strtotime(date('Y-m-d H:i:s')) && strtotime($ssdate) < strtotime(date('Y-m-d H:i:s'))){
                    $data['is_sale'] = true;
                    $data['orginal_price'] = Appointmentpro_Model_Utils::displayPrice($data['price'], Core_Model_Language::getCurrencySymbol(), $settings['number_of_decimals'], $settings['decimal_separator'], $settings['thousand_separator'], $settings['currency_position']);;
                    $data['price'] = $data['special_price'];
                    
                }

                $price_with_currency = Appointmentpro_Model_Utils::displayPrice($data['price'], Core_Model_Language::getCurrencySymbol(), $settings['number_of_decimals'], $settings['decimal_separator'], $settings['thousand_separator'], $settings['currency_position']);
                $data['price_with_currency'] = $data['price'] >= 0 ?  $price_with_currency : p__('appointmentpro', 'Free');
                $data['class_date'] = date($dateFormat, strtotime($data['class_date']));
                $servicesJson[] = $data;

            }

            $payload = [
                "records" => $servicesJson,
                "queryRecordCount" => $countFiltered[0],
                "totalRecordCount" => $countAll[0]
            ];

        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    
}
