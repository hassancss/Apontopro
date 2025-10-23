<?php

class Appointmentpro_Model_Db_Table_Slider extends Core_Model_Db_Table
{
    protected $_name = "appointment_slider";
    protected $_primary = "id";

    /**
     * @param $value_id
     * @param int $limit
     * @return array
     */
    public function activeSlider($value_id, $params = [])
    {
        $current_date = (new Siberian_Date())->toString("MM/dd/yyyy");

        $select = $this->_db->select()
            ->from(['main' => $this->_name], [
                "id",
                "image",
                "event_id",
                "slider_name",
                "valid_from",
                "valid_until",
                "created_at"
            ]);

        $select->where("main.value_id = ?", $value_id);
        $select->where("main.status = ?", 'active');
        $select->where("main.valid_from <= ?", $current_date);
        $select->where("main.valid_until >= ?", $current_date);

        $select->order('main.id DESC');

        return $this->_db->fetchAll($select);
    }


    /**
     * @param $value_id
     * @param int $limit
     * @return array
     */
    public function notInEvent($value_id, $params = [])
    {
        $current_date = (new Siberian_Date())->toString("MM/dd/yyyy");

        $select = $this->_db->select()
            ->from(['main' => $this->_name], [
                "id",
                "image",
                "event_id",
                "slider_name",
                "valid_from",
                "valid_until",
                "created_at"
            ]);


        $select->where("main.value_id = ?", $value_id);
        $select->where("main.status = ?", 'active');
        $select->where("main.valid_until >= ?", $current_date);

        $select->order('main.id DESC');

        return $this->_db->fetchAll($select);
    }


}
