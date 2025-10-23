<?php

class Appointmentpro_Model_Provider extends Core_Model_Default
{

    /**
     * @var null
     */
    public static $acl = null;

    /**
     * @var string
     */
    protected $_db_table = Appointmentpro_Model_Db_Table_Provider::class;

    /**
     * @param $valuesId
     * @param array $params
     * @return Appointmentpro_Model_Provider[]
     */
    public function findByValueId($valuesId, $params = [])
    {
        return $this->getTable()->findByValueId($valuesId, $params);
    }

    /**
     * @param $valuesId
     * @param array $params
     * @return Appointmentpro_Model_Provider[]
     */
    public function findActiveAllProviderForApp($valuesId, $params = [])
    {
        return $this->getTable()->findActiveAllProviderForApp($valuesId, $params);
    }

    /**
     * @param $valuesId
     * @param array $params
     * @return Appointmentpro_Model_Provider[]
     */
    public function countAllForApp($valuesId, $params = [])
    {
        return $this->getTable()->countAllForApp($valuesId, $params);
    }

    /**
     * @param $locationId ,
     * @param array $params
     * @return Appointmentpro_Model_Provider[]
     */
    public function findServiceProvider($locationId, $serviceId)
    {
        return $this->getTable()->findServiceProvider($locationId, $serviceId);
    }

    /**
     * @param $date , $param
     * @param array $params
     * @return Appointmentpro_Model_Provider[]
     */
    public function getServiceTime($date, $param = [])
    {
        return $this->getTable()->getServiceTime($date, $param);
    }

    /**
     * @param $date , $param
     * @param array $params
     * @return Appointmentpro_Model_Provider[]
     */
    public function getProviderServices($providerId, $param = [])
    {
        return $this->getTable()->getProviderServices($providerId, $param);
    }

    /**
     * @param $valuesId
     * @param array $params
     * @return Appointmentpro_Model_Provider[]
     */
    public function sortable($params)
    {
        return $this->getTable()->sortable($params);
    }

     /**
     * @param $locationId
     * @param array $params
     * @return Appointmentpro_Model_Provider[]
     */
    public function getMobileProvider($locationId, $appId)
    {
        return $this->getTable()->getMobileProvider($locationId, $appId);
    }

}
