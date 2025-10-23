<?php

/**
 * Class Appointmentpro_LocationController
 */
class Appointmentpro_LocationController extends Application_Controller_Default
{

    /**
     *Home screen
     */
    public function listAction() {
        $this->loadPartials();
    }

    /**
     *Location add
     */
    public function addAction() {
        $this->loadPartials();
    }

    /**
     *Location bussiness days
     */
    public function bussinessAction() {
       $model = (new Appointmentpro_Model_Location());  
        if ($id = $this->getRequest()->getParam('id')) {
            $model->find(['location_id' => $id ]); 
            if (!$model->getLocationId()) {
                    $this->getRequest()->addError( p__("appointmentpro",  "This location does not exist."));
            }
        }
        $this->loadPartials();
        $this->getLayout()->getPartial('content')->setLocation($model);
    }

    /**
     *Location Edit
     */
    public function editAction() {
       
        if ($id = $this->getRequest()->getParam('id')) { 
        $model = (new Appointmentpro_Model_Location())->find(['location_id' => $id]);
        
            if (!$model->getLocationId()) {
                    $this->getRequest()->addError( p__("appointmentpro",  "This location does not exist."));
            }
        }
        $this->loadPartials();
        $this->getLayout()->getPartial('content')->setLocation($model);
         
    }

    /**
     *Location delete
     */
    public function deleteAction() {
        
        try {

        $model = (new Appointmentpro_Model_Location());  
        if ($id = $this->getRequest()->getParam('id')) {
            $model->find(['location_id' => $id]); 
            if (!$model->getLocationId()) {
                    $this->getRequest()->addError( p__("appointmentpro",  "This location does not exist."));
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
     *Location Save
     */
    public function saveAction() {
       
        if($param = $this->getRequest()->getPost()) {
      
        try {

          $is_active = $param['is_active'] == 1 ? 1: 0;
          $is_allow_accept_payment = $param['is_allow_accept_payment'] == 1 ? 1: 0;
           $model = (new Appointmentpro_Model_Location())
                    ->find(['location_id' => $param['location_id']])
                    ->setValueId($param['value_id'])
                    ->setName($param['name'])
                    ->setAddress($param['address'])
                    ->setLocation($param['location'])
                    ->setEmail($param['email'])
                    ->setLatitude($param['latitude'])
                    ->setLongitude($param['longitude'])
                    ->setIsAllowAcceptPayment($is_allow_accept_payment)
                    ->setIsActive($is_active)
                    ->setAboutUs($param['about_us']);

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

              $payload = [
                    'success' => true,
                    'message' => p__('appointmentpro', 'Successfully save')                    
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
     *Location savebussiness
     */
    public function savebussinessAction() {
       
        if($param = $this->getRequest()->getPost()) {
      
        try {
                $model = (new Appointmentpro_Model_Location())
                    ->find(['location_id' => $param['location_id']])
                    ->setBusinessTiming(Siberian_Json::encode($param['timing']))
                    ->save();

                $payload = [
                    'success' => true,
                    'message' => p__('appointmentpro', 'Successfully save')
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
     * fetch category
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
            
            $locations = (new Appointmentpro_Model_Location())
                ->findByValueId($value_id, $params);

            $countAll = (new Appointmentpro_Model_Location())->countAllForApp($value_id);
            $countFiltered =   (new Appointmentpro_Model_Location())->countAllForApp($value_id, $params);

            $locationsJson = [];
            foreach ($locations as $location) {
                $data = $location->getData();
                $data['is_active'] = $data['is_active'] == 1 ? p__('appointmentpro', "Active") : p__('appointmentpro', "In Active");
                $data['featured_image'] = empty($data['featured_image']) ? '/app/local/modules/Appointmentpro/resources/design/desktop/flat/images/dummy-image.jpg' : '/images/application/'.$data['featured_image'];
                $locationsJson[] = $data;
            }

            $payload = [
                "records" => $locationsJson,
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

    /**
     *Location Gallery
     */
    public function galleryAction() {
        $model = (new Appointmentpro_Model_Location());  
        if ($location_id = $this->getRequest()->getParam('id')) {
            $model->find(['location_id' => $location_id ]); 
            if (!$model->getLocationId()) {
                    $this->getRequest()->addError( p__("appointmentpro",  "This location does not exist."));
            }
        }
        $this->loadPartials();
        $this->getLayout()->getPartial('content')->setLocation($model);
    }

    /**
     *Location Gallery save
     */
    public function savegalleryAction() {
       
        if($param = $this->getRequest()->getPost()) {
      
        try {
                if(!empty($param['gallery_image'])){
                    foreach ($param['gallery_image'] as $iKey => $iValue) {
                        if (file_exists(Core_Model_Directory::getTmpDirectory(true) . "/" . $iValue)) {
                                list($relativePath, $filename) = $this->_getImageData($iValue);
                                $imageURL = $relativePath . '/' . $filename;

                           $model = (new Appointmentpro_Model_LocationGallery())
                                ->find(['id' => $param['id']])
                                ->setValueId($param['value_id'])
                                ->setLocationId($param['location_id'])
                                ->setImage($imageURL)
                                ->save();
                        }
                    }
                }
 
              $payload = [
                    'success' => true,
                    'message' => p__('appointmentpro', 'Successfully save')                    
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
     *Location delete
     */
    public function deletegalleryAction() {
        
        try {

        $model = (new Appointmentpro_Model_LocationGallery());  
        if ($id = $this->getRequest()->getParam('id')) {
                $model->find($id); 
                if (!$model->getLocationId()) {
                        $this->getRequest()->addError( p__("appointmentpro",  "This location does not exist."));
                }else{
                    $model->setIsDelete(1);
                    $model->save();
                }
            }
            
            $payload = [
                'success' => true,
                'message' => p__('appointmentpro', 'Successfully deleted')

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
     * @param $image
     * @return array
     * @throws Siberian_Exception
     */
    private function _getImageData($image) {

        $img_src = Core_Model_Directory::getTmpDirectory(true) . "/" . $image;

        $info = pathinfo($img_src);
        $filename = $info['basename'];
        $relativePath = '/appointmentpro/'.$this->getApplication()->getId().'/location';
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
     *Gateways screen
     */
    public function gatewaysAction() {
        $model = (new Appointmentpro_Model_Location());  
        if ($id = $this->getRequest()->getParam('id')) {
            $model->find(['location_id' => $id ]); 
            if (!$model->getLocationId()) {
                    $this->getRequest()->addError( p__("appointmentpro",  "This location does not exist."));
            }
        }
        $this->loadPartials();
        $this->getLayout()->getPartial('content')->setLocation($model);
    }

    
}
