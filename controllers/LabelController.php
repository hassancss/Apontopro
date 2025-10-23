<?php

/**
 * Class Appointmentpro_LabelController
 */
class Appointmentpro_LabelController extends Application_Controller_Default
{

  
    /*edit input fields lable */
    public function loadformAction() {
        try
        {
            $id = $this->getRequest()->getParam("id");
            $lableModel = new Appointmentpro_Model_Labelname();
            $lableModel->find(array('id'=> $id));

            if($lableModel->getId()) {
                $form = new Appointmentpro_Form_Label();
                $form->populate($lableModel->getData());
                $form->setValueId($this->getCurrentOptionValue()->getId());
                $form->removeNav("nav-appointmentpro-label");
                $form->addNav("edit-nav-appointmentpro", "Save", false);

            $payload = array(
                "check" => 'ready for use',
                "success"   => 1,
                "form"      => $form->render(),
                "message"   => p__('appointmentpro',"Success."),
            );
        } else {
            /** Do whatever you need when form is not valid */
            $payload = array(
                "error"     => 1,
                "message"   => p__('appointmentpro',"The field you are trying to edit doesn't exists."),
            );
        }
        }
        catch(\Exception $e)
        {
            $payload = ["error" => true,
             "message" => $e->getMessage() ,
             ];
        }

        $this->_sendJson($payload);
    }
  


    /* save label */
     public function saveAction() {
        try
        {
            $values = $this->getRequest()->getPost();
            $form = new Appointmentpro_Form_Label();
            if ($form->isValid($values))
            {
                
                $lableModel = new Appointmentpro_Model_Labelname();
                $lableModel->setValueId($this->getCurrentOptionValue()->getId());
                $lableModel->addData($values);
                $lableModel->save();
                
                $payload = [
                 "success" => "1",
                 "message" => p__('appointmentpro',"Saved successfully") ,
                 'message_timeout' => 1,
                 'message_button' => 0,
                 'message_loader' => 0,
                ];
            }
    
            else
            {
                /** Do whatever you need when form is not valid */
                $payload = [
                 "error" => true,
                 "message" => $form->getTextErrors() ,
                 "errors" => $form->getTextErrors(true) ,
                ];
            }
        }
        catch(\Exception $e)
        {
          $payload = [
             "error" => true,
             "message" => $e->getMessage() ,
         ];
        }
        $this->_sendJson($payload);
    }


}