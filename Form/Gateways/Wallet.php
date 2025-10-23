<?php

class Appointmentpro_Form_Gateways_Wallet extends Siberian_Form_Abstract
{
    
    public function init() {
        parent::init();
        
        $this
            ->setAction(__path("/appointmentpro/gateways/addwallet"))
            ->setAttrib("id", "form-add-gateways")
            ->addNav("nav-add-appointmentpro", "Submit");
        
        self::addClass("create", $this); 

        $value_id = $this->addSimpleHidden("value_id");
        $id = $this->addSimpleHidden("id");
        $gateway_code = $this->addSimpleHidden("gateway_code");
      
        /** name */
        $name = $this->addSimpleText('lable_name', p__('appointmentpro', 'label Name'))->setRequired(true)->setValue('Wallet');

        /** Textara with shot_description */
        $shot_description = $this->addSimpleTextarea('shot_description',  p__('appointmentpro', 'Small Description'));
      
       $this->addSimpleCheckbox('status', p__('appointmentpro', 'Active'));
  }
    
    public function setElementValueById($id, $value, $required = false) {
        $element = $this->getElement($id)->setValue($value);
        if( $required ) {
            $element->setRequired(true);
        }
    }  
    
}
?>
 