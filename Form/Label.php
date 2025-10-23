<?php

class Appointmentpro_Form_Label extends Siberian_Form_Abstract
{
    
    public function init() {
        parent::init();
        
        $this
            ->setAction(__path("/appointmentpro/label/save"))
            ->setAttrib("id", "form-appointmentpro-label")
            ->addNav("nav-appointmentpro-label", "Submit");
        
        
        self::addClass("create", $this); 
        $value_id = $this->addSimpleHidden("value_id");
        $id = $this->addSimpleHidden("id");

        $label_key = $this->addSimpleHidden("label_key");
        $label_name=$this->addSimpleText('label_name', p__('appointmentpro', 'Label Name'))->setRequired(true);
    }


    public function setElementValueById($id, $value, $required = false) {
        $element = $this->getElement($id)->setValue($value);
        if( $required ) {
            $element->setRequired(true);
        }
    }
}