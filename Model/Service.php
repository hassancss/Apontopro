<?php

class Appointmentpro_Model_Service extends Core_Model_Default
{

    /**
     * @var null
     */
    public static $acl = null;

    /**
     * @var string
     */
    protected $_db_table = Appointmentpro_Model_Db_Table_Service::class;

    /**
     * @param $valuesId
     * @param array $params
     * @return Appointmentpro_Model_Location[]
     */
    public function findByValueId($valuesId, $params = [])
    {
        return $this->getTable()->findByValueId($valuesId, $params);
    }

    /**
     * @param $valuesId
     * @param array $params
     * @return Appointmentpro_Model_Location[]
     */
    public function countAllForApp($valuesId, $params = [])
    {
        return $this->getTable()->countAllForApp($valuesId, $params);
    }

    /**
     * @param $location_id , $category_id
     * @param array $params
     * @return Appointmentpro_Model_Location[]
     */
    public function findLocationServiceByCategory($location_id, $category_id = 0)
    {
        return $this->getTable()->findLocationServiceByCategory($location_id, $category_id);
    }

}
