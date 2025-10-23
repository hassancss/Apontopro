<?php

class Appointmentpro_Model_Class extends Core_Model_Default
{

    /**
     * @var null
     */
    public static $acl = null;

    /**
     * @var string
     */
    protected $_db_table = Appointmentpro_Model_Db_Table_Class::class;

    /**
     * @param $valuesId
     * @param array $params
     * @return Appointmentpro_Model_Class[]
     */
    public function findByValueId($valuesId, $params = [])
    {
        return $this->getTable()->findByValueId($valuesId, $params);
    }

    /**
     * @param $valuesId
     * @param array $params
     * @return Appointmentpro_Model_Class[]
     */
    public function countAllForApp($valuesId, $params = [])
    {
        return $this->getTable()->countAllForApp($valuesId, $params);
    }

    /**
     * @param $valuesId
     * @param array $params
     * @return Appointmentpro_Model_Class[]
     */
    public function findAllClassesByValueId($valuesId, $params = [])
    {
        return $this->getTable()->findAllClassesByValueId($valuesId, $params);
    }

    /**
     * @param $class_id
     * @param array $params
     * @return Appointmentpro_Model_Class[]
     */
    public function findByClassId($class_id)
    {
        return $this->getTable()->findByClassId($class_id);
    }


}
