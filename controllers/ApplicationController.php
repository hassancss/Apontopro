<?php

/**
 * Class Appointmentpro_ApplicationController
 */
class Appointmentpro_ApplicationController extends Application_Controller_Default {

	public function cropAction() {
       
        if($datas = $this->getRequest()->getPost()) {
            try {
                $uploader = new Core_Model_Lib_Uploader();
                $file = $uploader->savecrop($datas);
                $datas = [
                    'success' => 1,
                    'file' => $file,
                    'message_success' => p__("appointmentpro", "Upload successfully"),
                    'message_button' => 0,
                    'message_timeout' => 2,
                ];
                
            } catch (Exception $e) {
                $datas = [
                    'error' => 1,
                    'message' => $e->getMessage()
                ];
            }
            $this->getLayout()->setHtml(Zend_Json::encode($datas));
         }
    }

    /**
     *
     */
    public function editAction()
    {
        parent::editAction();
    }

    
}
