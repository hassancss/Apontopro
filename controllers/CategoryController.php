<?php

/**
 * Class Appointmentpro_CategoryController
 */
class Appointmentpro_CategoryController extends Application_Controller_Default
{

    /**
     *Home screen
     */
    public function listAction() {
        $this->loadPartials();
    }

    /**
     *category add
     */
    public function addAction() {
        $this->loadPartials();
    }

    /**
     *category Edit
     */
    public function editAction() {
        $model = (new Appointmentpro_Model_Category());  
        if ($id = $this->getRequest()->getParam('id')) {
            $model->find($id); 
            if (!$model->getCategoryId()) {
                    $this->getRequest()->addError( p__("appointmentpro",  "This category does not exist."));
            }
        }
        $this->loadPartials();
        $this->getLayout()->getPartial('content')->setCategory($model);
         
    }

    /**
     *category delete
     */
    public function deleteAction() {
        
        try {

        $model = (new Appointmentpro_Model_Category());  
        if ($id = $this->getRequest()->getParam('id')) {
            $model->find($id); 
            if (!$model->getCategoryId()) {
                    $this->getRequest()->addError( p__("appointmentpro",  "This category does not exist."));
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
     *category Save
     */
    public function saveAction() {
       
        if($param = $this->getRequest()->getPost()) {
      
        try {
            
            $is_active = $param['is_active'] == 1 ? 1: 0;
            $top_category = $param['top_category'] == 1 ? 1: 0;
            $model = (new Appointmentpro_Model_Category())
                    ->find(['category_id' => $param['category_id']])
                    ->setValueId($param['value_id'])
                    ->setName($param['name'])
                    ->setCategoryFor($param['category_for'])
                    ->setTopCategory($top_category)
                    ->setIsActive($is_active);

                if(!empty($param['featured_image'])){
                    foreach ($param['featured_image'] as $iKey => $iValue) {
                        if (file_exists(Core_Model_Directory::getTmpDirectory(true) . "/" . $iValue)) {
                                list($relativePath, $filename) = $this->_getImageData($iValue);
                                $imageURL = $relativePath . '/' . $filename;
                                $model->setImage($imageURL);
                        }
                    }
                }

                $model->save();

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
        $relativePath = '/appointmentpro/'.$this->getApplication()->getId().'/category';
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
            
            $categories = (new Appointmentpro_Model_Category())
                ->findByValueId($value_id, $params);

            $countAll = (new Appointmentpro_Model_Category())->countAllForApp($value_id);
            $countFiltered =   (new Appointmentpro_Model_Category())->countAllForApp($value_id, $params);

            $categoryJson = [];
            foreach ($categories as $category) {
                $data = $category->getData();
                $data['is_active'] = $data['is_active'] == 1 ? p__('appointmentpro', "Active") : p__('appointmentpro', "In Active"); 
                $data['image'] = empty($data['image']) ? '/app/local/modules/Appointmentpro/resources/design/desktop/flat/images/dummy-image.jpg' : '/images/application/'.$data['image'];
                $data['category_for'] = $data['category_for'] == 1 ? p__('appointmentpro', "Service") : p__('appointmentpro', "Class");
                $categoryJson[] = $data;
            }

            $payload = [
                "records" => $categoryJson,
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
     * @param $param
     * @return array
     * @throws Siberian_Exception
     */
    public function sortableAction() {
        $payload = array();
        if($data = $this->getRequest()->getPost('data')) {  
            $model = new Appointmentpro_Model_Category();                     
            $model->sortable($data);                
            $payload = array("success" => 1 );
        }
        
        $this->_sendJson($payload);     
    }

    
}
