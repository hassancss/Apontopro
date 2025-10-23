<?php

class Appointmentpro_Model_Db_Table_ServiceLocation extends Core_Model_Db_Table
{
    protected $_name = "appointment_service_location";
    protected $_primary = "service_location_id";


    /**
     * @param $locationId
     * @param array $params
     * @return Appointmentpro_Model_Location[]
     */
    public function findByLocationId($locationId, $params = [])
    {
        $select = $this->_db->select()
            ->from(['main' => $this->_name], [
                "service_location_id",
            ]);

        $select->joinLeft(['s' => 'appointment_service'], 's.service_id = main.service_id', [
            "service_id",
            "name",
            "price",
            "service_time",
            "buffer_time",
            "capacity",
            "category_id",
            "image",
            "service_points",
            "class_description",
            "class_date",
            "class_end_date",
            "numbers_of_days",
            "class_time",
            "service_type",
            "provider_id",
            "status",
            "class_details",
            "days_selected",
            "is_delete",
            "featured_image",
            "created_at"
        ]);

        $select->joinLeft(['c' => 'appointment_category'], 'c.category_id = s.category_id', ['c.name as category_name']);

        $select->where("main.location_id = ?", $locationId);
        $select->where("main.is_delete = ?", 0);
        $select->where("s.is_delete = ?", 0);
        $select->where("s.status = ?", 1);


        if (array_key_exists("service_type", $params)) {
            $select->where("s.service_type = ?", $params['service_type']);
        }


        $select->order('s.name ASC');


        return $this->toModelClass($this->_db->fetchAll($select));
    }

}
