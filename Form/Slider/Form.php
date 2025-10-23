<?php

class Appointmentpro_Form_Slider_Form extends Siberian_Form_Abstract
{
    
    public function init() {
        parent::init();
        
        $this
            ->setAction(__path("/appointmentpro/slider/add"))
            ->setAttrib("id", "form-add-slider")
            ->addNav("nav-add-appointmentpro", "Submit");
        
        self::addClass("create", $this); 

        $value_id = $this->addSimpleHidden("value_id");
        $slider_id = $this->addSimpleHidden("slider_id");

        /** Slider name */
        $slider_name = $this->addSimpleText('slider_name', p__('appointmentpro', 'Name'))->setRequired(true);

        $this->addSimpleDatetimepicker(
            'valid_from', 
             p__('appointmentpro', 'Valid From'), 
            false, 
            Siberian_Form_Abstract::DATETIMEPICKER
        )->setRequired(true);

        $this->addSimpleDatetimepicker(
            'valid_until', 
            p__('appointmentpro', 'Valid Until'), 
            false, 
            Siberian_Form_Abstract::DATETIMEPICKER
        )->setRequired(true); 
      
     
        /** Store Image */
        $image = $this->addSimpleImage(
            'image', 
            p__('appointmentpro','Image'), 
            p__('appointmentpro','Import an image'), 
            [
                'width' => 512, 
                'height' => 320
            ]
        )->setRequired(true);

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
 