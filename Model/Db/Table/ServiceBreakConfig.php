<?php

class Appointmentpro_Model_Db_Table_ServiceBreakConfig extends Core_Model_Db_Table
{
    /**
     * @var string
     */
    protected $_name = "appointment_service_break_config";

    /**
     * @var string
     */
    protected $_primary = "config_id";

    /**
     * Find break configuration by service ID
     * 
     * @param int $serviceId
     * @return array
     */
    public function findByServiceId($serviceId)
    {
        $select = $this->_db->select()
            ->from($this->_name)
            ->where('service_id = ?', $serviceId);

        $result = $this->_db->fetchRow($select);

        if ($result) {
            return $this->toModelClass($result);
        }

        return new Appointmentpro_Model_ServiceBreakConfig();
    }

    /**
     * Convert result to model class
     * 
     * @param array $result
     * @return Appointmentpro_Model_ServiceBreakConfig
     */
    public function toModelClass($result)
    {
        $model = new Appointmentpro_Model_ServiceBreakConfig();
        $model->setData($result);
        return $model;
    }
}
