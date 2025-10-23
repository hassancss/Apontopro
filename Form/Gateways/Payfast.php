<?php

class Appointmentpro_Form_Gateways_Payfast extends Siberian_Form_Abstract
{
    
    public function init() {
        parent::init();
        
        $this
            ->setAction(__path("/appointmentpro/gateways/addpayfast"))
            ->setAttrib("id", "form-add-gateways")
            ->addNav("nav-add-appointmentpro", "Submit");
        
        self::addClass("create", $this); 

        $value_id = $this->addSimpleHidden("value_id");
        $id = $this->addSimpleHidden("id");
        $gateway_code = $this->addSimpleHidden("gateway_code");
        $location_id = $this->addSimpleHidden("location_id")->setValue(0);
    

        /** Store name */
        $name = $this->addSimpleText('lable_name', p__('appointmentpro', 'label Name'))->setRequired(true);

        /** Textara with shot_description */
        $shot_description = $this->addSimpleTextarea('shot_description',  p__('appointmentpro', 'Small Description'));
        //$shot_description->setRichtext();

        $merchantId = $this->addSimpleText("merchant_id", p__('appointmentpro', "Merchant Id"))->setRequired(true);
        $merchantKey = $this->addSimpleText("merchant_key", p__('appointmentpro', "Merchant Key"))->setRequired(true);
       $this->addSimpleCheckbox('is_live', p__('appointmentpro', 'Is Live')); 

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
 