<?php

class Appointmentpro_Form_Gateways_Paypal extends Siberian_Form_Abstract
{
    
    public function init() {
        parent::init();
        
        $this
            ->setAction(__path("/appointmentpro/gateways/addpaypal"))
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
       // $shot_description->setRichtext();

        
        $paypal_payment_mode = $this->addSimpleSelect('payment_mode',  p__('appointmentpro', 'Payment Mode'),
            ['Sandbox', 'Live']
        );
        $paypal_payment_mode->addClass("select_payment_paypal");
        $paypal_payment_mode->setRequired(true);

        $username = $this->addSimpleText("username", p__('appointmentpro', "Username"));
        $signature = $this->addSimpleText("signature", p__('appointmentpro', "Signature"));
        $password = $this->addSimpleText("password", p__('appointmentpro', "Password"));

        $sandboxusername = $this->addSimpleText("sandboxusername", p__('appointmentpro', "Sandbox Username"));
        $sandboxsignature = $this->addSimpleText("sandboxsignature",p__('appointmentpro', "Sandbox Signature"));
        $sandboxpassword = $this->addSimpleText("sandboxpassword", p__('appointmentpro', "Sandbox Password"));

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
 