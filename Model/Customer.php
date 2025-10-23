<?php

class Appointmentpro_Model_Customer extends Core_Model_Default
{

    /**
     * @var null
     */
    public static $acl = null;

    /**
     * @var string
     */
    protected $_db_table = Appointmentpro_Model_Db_Table_Customer::class;

    /**
     * @param $locationId
     * @param array $params
     * @return Appointmentpro_Model_Customer[]
     */
    public function findByLocationId($locationId, $params = [])
    {
        return $this->getTable()->findByLocationId($locationId, $params);
    }

}
