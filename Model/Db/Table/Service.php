<?php

class Appointmentpro_Model_Db_Table_Service extends Core_Model_Db_Table
{
    protected $_name = "appointment_service";
    protected $_primary = "service_id";

    /**
     * @param $app_id
     * @param int $limit
     * @return array
     */
    public function findByValueId($value_id, $params = [])
    {
        $select = $this->_db->select()
            ->from(['main' => $this->_name], [
                "service_id",
                "name",
                "price",
                "service_time",
                "buffer_time",
                "capacity",
                "category_id",
                "image",
                "description",
                "service_points",
                "class_description",
                "class_date",
                "class_end_date",
                "numbers_of_days",
                "class_time",
                "service_type",
                "schedule_type",
                "day_of_week",
                "day_of_month",
                "provider_id",
                "status",
                "class_details",
                "days_selected",
                "is_delete",
                "featured_image",
                "special_price",
                "special_start",
                "total_booking_per_slot",
                "visible_to_user",
                "special_end",
                "created_at"
            ]);

        $select->joinLeft(['c' => 'appointment_category'], 'c.category_id = main.category_id', ['c.name as category_name']);

        $select->where("main.value_id = ?", $value_id);
        $select->where("main.is_delete = ?", 0);
        $select->where("main.service_type = ?", 1);

        if (array_key_exists("limit", $params) && array_key_exists("offset", $params)) {
            $select->limit($params["limit"], $params["offset"]);
        }

        if (array_key_exists("filter", $params)) {
            $select->where("(main.name LIKE ?)", "%" . $params["filter"] . "%");
        }

        if (array_key_exists("sorts", $params) && !empty($params["sorts"])) {
            $orders = [];
            foreach ($params["sorts"] as $key => $dir) {
                $order = ($dir == -1) ? "DESC" : "ASC";
                $orders = "main.{$key} {$order}";
            }
            $select->order($orders);
        } else {
            $select->order('main.created_at ASC');
        }

        return $this->toModelClass($this->_db->fetchAll($select));
    }


    /**
     * @param $value_id
     */
    public function countAllForApp($value_id, $params = [])
    {
        $select = $this->_db->select()
            ->from(['main' => $this->_name], [
                'COUNT(main.service_id)'
            ])
            ->where('main.value_id = ?', $value_id);

        $select->where("main.is_delete = ?", 0);

        if (array_key_exists("filter", $params)) {
            $select->where("(main.name LIKE ?)", "%" . $params["filter"] . "%");
        }

        return $this->_db->fetchCol($select);
    }

    /**
     * @param $app_id
     * @param int $limit
     * @return array
     */
    public function findLocationServiceByCategory($location_id, $category_id = 0)
    {
        $select = $this->_db->select()
            ->from(['main' => $this->_name], [
                "service_id",
                "name",
                "price",
                "service_time",
                "buffer_time",
                "capacity",
                "category_id",
                "image",
                "description",
                "service_points",
                "class_description",
                "class_date",
                "class_end_date",
                "numbers_of_days",
                "class_time",
                "service_type",
                "schedule_type",
                "day_of_week",
                "day_of_month",
                "provider_id",
                "status",
                "class_details",
                "days_selected",
                "is_delete",
                "featured_image",
                "created_at",
                "special_price",
                "total_booking_per_slot",
                "special_start",
                "special_end",
                "visible_to_user",
            ]);/*  $select->joinLeft(['c' => 'appointment_category'], 'c.category_id = main.category_id', ['c.name as category_name'])*/;

        $select->joinLeft(['sl' => 'appointment_service_location'], 'main.service_id = sl.service_id', ['service_location_id']);

        $select->where("sl.location_id = ?", $location_id);

        if ($category_id) {
            $select->where("main.category_id = ?", $category_id);
        }

        $select->where("main.status = ?", 1);
        $select->where("main.is_delete = ?", 0);
        $select->order('main.name ASC');

        return $this->_db->fetchAll($select);
    }
}
