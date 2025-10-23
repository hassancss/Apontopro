<?php

use Siberian\Exception;
use Siberian\File;
use Siberian\Json;

/**
 * Class Appointmentpro_Mobile_CategoryController
 */
class Appointmentpro_Mobile_CategoryController extends Application_Controller_Mobile_Default {

	public function findCategoryByLocationAction() {
		$payload = [];     

        try{
            $value_id = $this->getRequest()->getParam('value_id'); 
            $location_id = $this->getRequest()->getParam('location_id');
          
            $categories = (new Appointmentpro_Model_Category())->findByLocationId($location_id, ['category_for' => 1]);
            $categoriesJson = [];
            
            foreach ($categories as $key => $category) {
                $categoriesJson[] = $category;
            }
        
            $payload = [
                'success' => true,
                'categories' => $categoriesJson
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