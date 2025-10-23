<?php

class Appointmentpro_Model_Slider extends Core_Model_Default
{

    /**
     * @var null
     */
    public static $acl = null;

    /**
     * @var string
     */
    protected $_db_table = Appointmentpro_Model_Db_Table_Slider::class;


    /**
     * @param $valuesId
     * @param array $params
     * @return Appointmentpro_Model_Slider[]
     */
    public function activeSlider($valuesId, $params = [])
    {
        return $this->getTable()->activeSlider($valuesId, $params);
    }

    /**
     * @param $valuesId
     * @param array $params
     * @return Eventpro_Model_Slider[]
     */
    public function notInEvent($valuesId, $params = [])
    {
        return $this->getTable()->notInEvent($valuesId, $params);
    }


}
