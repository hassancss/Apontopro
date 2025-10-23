<?php

class Appointmentpro_Model_Category extends Core_Model_Default
{

    /**
     * @var null
     */
    public static $acl = null;

    /**
     * @var string
     */
    protected $_db_table = Appointmentpro_Model_Db_Table_Category::class;

    /**
     * @param $valuesId
     * @param array $params
     * @return Appointmentpro_Model_Category[]
     */
    public function findByValueId($valuesId, $params = [])
    {
        return $this->getTable()->findByValueId($valuesId, $params);
    }


    /**
     * @param $valuesId
     * @param array $params
     * @return Appointmentpro_Model_Category[]
     */
    public function findLocationByCategoryId($category_id, $params = [])
    {
        return $this->getTable()->findLocationByCategoryId($category_id, $params);
    }


    /**
     * @param $valuesId
     * @param array $params
     * @return Appointmentpro_Model_Category[]
     */
    public function findAppByValueId($valuesId, $params = [])
    {
        return $this->getTable()->findAppByValueId($valuesId, $params);
    }


    /**
     * @param $valuesId
     * @param array $params
     * @return Appointmentpro_Model_Category[]
     */
    public function countAllForApp($valuesId, $params = [])
    {
        return $this->getTable()->countAllForApp($valuesId, $params);
    }

    /**
     * @param $locationId
     * @param array $params
     * @return Appointmentpro_Model_Category[]
     */
    public function findByLocationId($locationId, $params = [])
    {
        return $this->getTable()->findByLocationId($locationId, $params);
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

}
