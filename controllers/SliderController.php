<?php

/**
 * Class Appointmentpro_SliderController
 */
class Appointmentpro_SliderController extends Application_Controller_Default
{
    
     /**
     *
     */
    public function addAction()
    {   
        $payload = array();

        if ($data = $this->getRequest()->getPost()) {

            try {
                $form = new Appointmentpro_Form_Slider_Form();
                if ($form->isValid($data)) {

                    if (!empty($data['value_id'])) {

                        
                        if (!empty($data['image']) && file_exists(Core_Model_Directory::getTmpDirectory(true) . "/" . $data['image'])) {
                            list($relativePath, $filename) = $this->_getImageData($data['image']);
                            $data['image'] = $relativePath . '/' . $filename;
                        }else{
                           $data['image'] = empty($data['slider_id']) ? '' : $data['image'];
                        }

                        if($data['status']){
                            $data['status'] = 'active';
                        }else{
                            $data['status'] = 'inactive';
                        }

                        $valid_from = new Zend_Date($data['valid_from'], false, new Zend_Locale('en_US'));
                        $data['valid_from'] = $valid_from->toString('MM/dd/y');
                     
                        $valid_until = new Zend_Date($data['valid_until'], false, new Zend_Locale('en_US'));
                        $data['valid_until'] = $valid_until->toString('MM/dd/y');                   

                        $slider = new Appointmentpro_Model_Slider();
                        $slider
                            ->find($data['slider_id'])
                            ->setSliderName($data['slider_name'])
                            ->setValueId($data['value_id'])
                            ->setImage($data['image'])
                            ->setValidFrom($data['valid_from'])
                            ->setValidUntil($data['valid_until'])
                            ->setStatus($data['status'])
                            ->save();

                    } else {
                        throw new Siberian_Exception(p__('appointmentpro', 'Something went wrong with the update, will retry later.'));
                    }

                    $payload = array(
                        'success' => true,
                        'success_message' => p__('appointmentpro', 'Information successfully saved'),
                        'message_timeout' => 2,
                        'message_button' => 0,
                        'message_loader' => 0
                    );
                } else {
                    /** Do whatever you need when form is not valid */
                    $payload = array(
                        "error" => 1,
                        "message" => $form->getTextErrors(),
                        "errors" => $form->getTextErrors(true),
                    );
                }

            } catch (Exception $e) {
                $payload = array(
                    'error' => true,
                    'message' => $e->getMessage()
                );
            }

           
        }

         $this->_sendJson($payload);
    }



    public function loadformAction(){
        
        if ($slider_id = $this->getRequest()->getParam("slider_id")) {
            try {
                 
                $sliderModel = new Appointmentpro_Model_Slider();
                $sliderModel->find($slider_id);
                if($sliderModel->getId()) {

                    $data = $sliderModel->getData();
                    $data['status'] = $data['status'] == 'active' ? 1 : 0;
                    
                    $form = new Appointmentpro_Form_Slider_Form();
                    $form->populate($data);
                    $form->setElementValueById('value_id', $this->getCurrentOptionValue()->getId());
                    $form->addNav("edit-nav-slider", "Save", false); 
                    $form->removeNav("nav-add-appointmentpro");              
                    $form->setElementValueById('slider_id', $sliderModel->getId());
                    $form->getElement('valid_until')->setAttrib('id', 'valid_until-' . $sliderModel->getId());
                    $form->getElement('valid_from')->setAttrib('id', 'valid_from-' . $sliderModel->getId());
                    
                    $payload = array(
                        "check" => 'ready for use',
                        "success"   => true,
                        "form"      => $form->render(),
                        "message"   => p__('appointmentpro', "Success."),
                    );

                }else{
                    $payload = array(
                        "error"     => true,
                        "message"   => p__('appointmentpro', 'Provider you are trying to edit does not exists.'),
                    );
                }
              

            } catch (Exception $e) {
                $payload = array(
                    'error' => true,
                    'message' => $e->getMessage()
                );
            }
        }

        $this->_sendHtml($payload);
    }


    /**
     *
     */
    public function deleteAction()
    {   
        $payload = array();

        if ($data = $this->getRequest()->getPost()) {

            try {

                $sliderModel = new Appointmentpro_Model_Slider();
                $sliderModel->find($data['id']);
                if($sliderModel->getId()) {
                    $sliderModel->setStatus('deleted');
                    $sliderModel->save();
              
                    $payload = array(
                        'success' => true,
                        'success_message' => p__('appointmentpro', 'Deleted successfully saved'),
                        'message_timeout' => 2,
                        'message_button' => 0,
                        'message_loader' => 0
                    );

                }

            } catch (Exception $e) {
                $payload = array(
                    'error' => true,
                    'message' => $e->getMessage()
                );
            }           
        }

        $this->_sendJson($payload);
    }


    /**
     * @param $image
     * @return array
     * @throws Siberian_Exception
     */
    private function _getImageData($image)
    {

        $img_src = Core_Model_Directory::getTmpDirectory(true) . "/" . $image;

        $info = pathinfo($img_src);

        $filename = $info['basename'];

        $relativePath = $this->getCurrentOptionValue()->getImagePathTo();

        $img_dst = Application_Model_Application::getBaseImagePath() . $relativePath;

        if (!is_dir($img_dst)) {
            mkdir($img_dst, 0777, true);
        }
        $img_dst .= '/' . $filename;
        rename($img_src, $img_dst);
        
        if (!file_exists($img_dst)) {
            throw new Siberian_Exception(p__('appointmentpro', 'An error occurred while saving your picture. Please try againg later.'));
        }
        return array($relativePath, $filename);
    }
     
}