<?php

class Appointmentpro_Model_ServiceLocation extends Core_Model_Default
{

    /**
     * @var null
     */
    public static $acl = null;

    /**
     * @var string
     */
    protected $_db_table = Appointmentpro_Model_Db_Table_ServiceLocation::class;

    public function updateDeleteStatus($columnName, $value)
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        return $db->query('UPDATE appointment_service_location SET is_delete = 1 WHERE ' . $columnName . ' = "' . $value . '";');
    }

    /**
     * @param $locationId
     * @param array $params
     * @return Appointmentpro_Model_Location[]
     */
    public function findByLocationId($locationId, $params = [])
    {
        return $this->getTable()->findByLocationId($locationId, $params);
    }

}
