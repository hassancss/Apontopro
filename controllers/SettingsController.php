<?php

/**
 * Class Appointmentpro_SettingsController
 */
class Appointmentpro_SettingsController extends Application_Controller_Default
{
 
   /**
     *
     */
    public function editpostAction()
    {
        try {
            $values = $this->getRequest()->getPost();
            $application = $this->getApplication();
            $form = new Appointmentpro_Form_Settings();
            if ($form->isValid($values))
            {   
                $appointment_setting = new Appointmentpro_Model_Settings();
                $appointment_setting->addData($values); 
                  
                if($values['enable_plc_points']){
                     if(!class_exists("ProgressiveLoyaltyCards_Model_ProgressiveLoyaltyCards")) {
                        throw new Exception(p__('appointmentpro', 'If you need to enable point system, please contact your administrator!'));
                     }else{

                        $_module_deps = (new Installer_Model_Installer_Module())->find(['name' => 'progressiveloyaltycards']);
                        $_module_info = $_module_deps->getData();

                        if($_module_info['version'] < "1.4.0") {
                           throw new Exception(p__('appointmentpro', "Your system doesn't meet the requirements for enable points(PLC) system , Progressive Loyalty Cards version >=%s is required.", "1.4.0"));
                        } else{
                           $progressiveloyaltycards_value_id = (new ProgressiveLoyaltyCards_Model_ProgressiveLoyaltyCards())->getCurrentValueId();
                            if(empty($progressiveloyaltycards_value_id)){
                               throw new Exception(p__('appointmentpro', 'For Point System Progressive Loyalty Cards module is not activated, Please add a feature in app!'.$progressiveloyaltycards_value_id));
                            }
                        }
                     }                  
                }
                
                $appointment_setting->save();

                $payload = ["success" => "1", 
                            "success_message" => p__('appointmentpro',  "Saved successfully") , 
                            'message_timeout' => 1, 
                            'message_button' => 0, 
                            'message_loader' => 0,
                        ];

            }
            else
            {
                /** Do whatever you need when form is not valid */
                $payload = ["error" => true, 
                            "message" => $form->getTextErrors() , 
                            "errors" => $form->getTextErrors(true) 
                        ];
            }

        }
        catch(\Exception $e)
        {
            $payload = ["error" => true, "message" => $e->getMessage() , ];
        }

        $this->_sendJson($payload);
    }

}