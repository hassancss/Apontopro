<?php

class Appointmentpro_Model_Booking extends Core_Model_Default
{

    /**
     * @var null
     */
    public static $acl = null;

    /**
     * @var string
     */
    protected $_db_table = Appointmentpro_Model_Db_Table_Booking::class;

    /**
     * @param $params
     * @param array $params
     * @return Appointmentpro_Model_Booking[]
     */
    public function getInfo($params = [])
    {
        return $this->getTable()->getInfo($params);
    }

    /**
     * @param $params
     * @param array $params
     * @return Appointmentpro_Model_Booking[]
     */
    public function getBookingById($booking_id)
    {
        return $this->getTable()->getBookingById($booking_id);
    }

    /**
     * @param $valuesId
     * @param array $params
     * @return Appointmentpro_Model_Booking[]
     */
    public function findByValueId($valuesId, $params = [])
    {
        return $this->getTable()->findByValueId($valuesId, $params);
    }
    /**
     * @param $valuesId
     * @param array $params
     * @return Appointmentpro_Model_Booking[]
     */
    public function findHistoryByValueId($valuesId, $params = [])
    {
        return $this->getTable()->findHistoryByValueId($valuesId, $params);
    }

    /**
     * @param $valuesId
     * @param array $params
     * @return Appointmentpro_Model_Booking[]
     */
    public function findByCustomerId($valuesId, $params = [])
    {
        return $this->getTable()->findByCustomerId($valuesId, $params);
    }

    /**
     * @param int $valueId
     * @param int $customerId
     * @param int $limit
     * @return array
     */
    public function getNotesByCustomer($valueId, $customerId, $limit = 5)
    {
        return $this->getTable()->getNotesByCustomer($valueId, $customerId, $limit);
    }

    /**
     * @param $valuesId
     * @param array $params
     * @return Appointmentpro_Model_Booking[]
     */
    public function countAllForApp($valuesId, $params = [])
    {
        return $this->getTable()->countAllForApp($valuesId, $params);
    }

    /**
     * @param $valuesId
     * @param array $params
     * @return Appointmentpro_Model_Booking[]
     */
    public function findByLocationIdActiveBooking($valuesId, $params = [])
    {
        return $this->getTable()->findByLocationIdActiveBooking($valuesId, $params);
    }


    /**
     * @param $valuesId
     * @param array $params
     * @return Appointmentpro_Model_Booking[]
     */
    public function countTotalDateRangeConfirmBooking($valuesId, $params = [])
    {
        return $this->getTable()->countTotalDateRangeConfirmBooking($valuesId, $params);
    }

    /**
     * @param $valuesId
     * @param array $params
     * @return Appointmentpro_Model_Booking[]
     */
    public function sumTotalConfirmProjectedEstimatedRevenue($valuesId, $params = [])
    {
        return $this->getTable()->sumTotalConfirmProjectedEstimatedRevenue($valuesId, $params);
    }


    /**
     * @param $valuesId
     * @param array $params
     * @return Appointmentpro_Model_Booking[]
     */
    public function countMonthTotalBooking($valuesId, $params = [])
    {
        return $this->getTable()->countMonthTotalBooking($valuesId, $params);
    }

    /**
     * @param array $params
     * @return Appointmentpro_Model_Booking[]
     */
    public function findDataForCronJob($params = [])
    {
        return $this->getTable()->findDataForCronJob($params);
    }
    /**
     * @param array $params
     * @return Appointmentpro_Model_Booking[]
     */
    public function reminderSent($uid )
    {
        return $this->getTable()->reminderSent($uid);
    }

     /**
     * @param $valuesId
     * @param array $params
     * @return Appointmentpro_Model_Booking[]
     */
    public function findByValueIdForApp($valuesId, $params = [])
    {
        return $this->getTable()->findByValueIdForApp($valuesId, $params);
    }

     /**
     * @param $valuesId
     * @param array $params
     * @return Appointmentpro_Model_Booking[]
     */
    public function countByValueIdForApp($valuesId, $params = [])
    {
        return $this->getTable()->countByValueIdForApp($valuesId, $params);
    }


}
