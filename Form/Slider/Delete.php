<?php

/**
 * Class Appointmentpro_Form_Slider_Delete
 */
class Appointmentpro_Form_Slider_Delete extends Siberian_Form_Abstract {

    public function init() {
        parent::init();

        $this
            ->setAction(__path("/appointmentpro/slider/delete"))
            ->setAttrib("id", "form-delete-appointmentpro-slider")
            ->setConfirmText("You are about to remove this slider ! Are you sure ?");
        ;

        /** Bind as a delete form */
        self::addClass("delete", $this);

        $db = Zend_Db_Table::getDefaultAdapter();
        $select = $db->select()
            ->from('appointment_slider')
            ->where('appointment_slider.id = :value')
        ;

        $id = $this->addSimpleHidden("id", p__('appointmentpro', 'Slider'));
        $id->addValidator("Db_RecordExists", true, $select);
        $id->setMinimalDecorator();

        $mini_submit = $this->addMiniSubmit();
    }
}