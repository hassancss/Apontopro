<?php

class Appointmentpro_Form_Gateways_Nmi extends Siberian_Form_Abstract
{

    public function init()
    {
        parent::init();

        $this
            ->setAction(__path("/appointmentpro/gateways/addnmi"))
            ->setAttrib("id", "form-add-gateways")
            ->addNav("nav-add-appointmentpro", "Submit");

        self::addClass("create", $this);

        $value_id = $this->addSimpleHidden("value_id");
        $id = $this->addSimpleHidden("id");
        $gateway_code = $this->addSimpleHidden("gateway_code");
        $location_id = $this->addSimpleHidden("location_id")->setValue(0);
        /*$hl1 = p__("appointmentpro", "API CREDENTIALS settings is available in Menu -> Payment Gateways -> Nmi");
        $helpText1 = '<div class="col-md-12"><div class="alert alert-info">1. '.  $hl1 .' .</div></div>';
        $this->addSimpleHtml("helper_text_home", $helpText1);*/

        /** Store name */
        $name = $this->addSimpleText('lable_name', p__('appointmentpro', 'label Name'))->setRequired(true);

        /** Textara with shot_description */
        $shot_description = $this->addSimpleTextarea('shot_description',  p__('appointmentpro', 'Small Description'));
        //$shot_description->setRichtext();

        // $publishable_key = $this->addSimpleText("publishable_key", p__('appointmentpro', "Publishable key"))->setRequired(true);
        $secret_key = $this->addSimpleText("secret_key", p__('appointmentpro', "Secret key"))->setRequired(true);
        $processing_fee = $this->addSimpleText("processing_fee", p__('appointmentpro', "Processing fee in %"))->setRequired(true)->setValue(0);
        $is_test_mode = $this->addSimpleCheckbox("is_test_mode", p__('appointmentpro', "Test mode"))->setValue(0);
        $this->addSimpleCheckbox('status', p__('appointmentpro', 'Active'));
    }

    public function setElementValueById($id, $value, $required = false)
    {
        $element = $this->getElement($id)->setValue($value);
        if ($required) {
            $element->setRequired(true);
        }
    }
}
