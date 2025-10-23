<?php

class Appointmentpro_Model_ServiceProvider extends Core_Model_Default
{

    /**
     * @var null
     */
    public static $acl = null;

    /**
     * @var string
     */
    protected $_db_table = Appointmentpro_Model_Db_Table_ServiceProvider::class;

    public function updateDeleteStatus($columnName, $value)
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        return $db->query('UPDATE appointment_service_provider SET is_delete = 1 WHERE ' . $columnName . ' = "' . $value . '";');
    }


}
