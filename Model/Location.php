<?php

class Appointmentpro_Model_Location extends Core_Model_Default
{

    /**
     * @var null
     */
    public static $acl = null;

    /**
     * @var string
     */
    protected $_db_table = Appointmentpro_Model_Db_Table_Location::class;

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
     * @param $valuesId
     * @param array $params
     * @return Appointmentpro_Model_Location[]
     */
    public function findByAppId($valuesId, $params = [])
    {
        return $this->getTable()->findByAppId($valuesId, $params);
    }

    /**
     * @param $LocationId
     * @param array $params
     * @return Appointmentpro_Model_Location[]
     */
    public function findById($LocationId, $params = [])
    {
        return $this->getTable()->findById($LocationId, $params);
    }


}
