<?php

class Appointmentpro_Model_Db_Table_Class extends Core_Model_Db_Table
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
                "service_points",
                "class_description",
                "class_date",
                "class_end_date",
                "numbers_of_days",
                "class_time",
                "schedule_type",
                "day_of_week",
                "day_of_month",
                "service_type",
                "provider_id",
                "status",
                "class_details",
                "days_selected",
                "is_delete",
                "featured_image",
                "created_at",
                "special_price",
                "special_start",
                "special_end",
                "sale_tickets" => new Zend_Db_Expr('(' . $this->_db->select()->from(array('t' => 'appointment'), array(new Zend_Db_Expr('SUM(t.booked_seat_class)')))->where('t.service_id = main.service_id')->where('t.is_delete = ?', 0)->where('t.status IN (?)', [2, 3, 4, 9]) . ')'),
            ]);

        $select->joinLeft(['c' => 'appointment_category'], 'c.category_id = main.category_id', ['c.name as category_name']);

        $select->where("main.value_id = ?", $value_id);
        $select->where("main.is_delete = ?", 0);
        $select->where("main.service_type = ?", 2);

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
        $select->where("main.service_type = ?", 2);

        if (array_key_exists("filter", $params)) {
            $select->where("(main.name LIKE ?)", "%" . $params["filter"] . "%");
        }

        return $this->_db->fetchCol($select);
    }


    /**
     * @param $value_id
     * @param int $limit
     * @return array
     */
    public function findAllClassesByValueId($value_id, $params = [])
    {

        $formula = new Zend_Db_Expr("0");
        $sortingType = $params["sortingType"];

        if (!empty($params["latitude"]) && !empty($params["longitude"])) {
            $formula = Siberian_Google_Geocoding::getDistanceFormula(
                $params["latitude"],
                $params["longitude"],
                'al',
                "latitude",
                "longitude");
        } else {
            // If we don't have geo, remove distance sorting, fallback on alpha
            if ($sortingType === "distance") {
                $sortingType = "alpha";
            }
        }


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
                "schedule_type",
                "day_of_week",
                "day_of_month",
                "days_selected",
                "is_delete",
                "featured_image",
                "created_at",
                "special_price",
                "special_start",
                "special_end",
                "sale_tickets" => new Zend_Db_Expr('(' . $this->_db->select()->from(array('t' => 'appointment'), array(new Zend_Db_Expr('SUM(t.booked_seat_class)')))->where('t.service_id = main.service_id')->where('t.is_delete = ?', 0)->where('t.status IN (?)', [2, 3, 4, 9]) . ')'),
            ])
            ->joinLeft(['c' => 'appointment_category'], 'c.category_id = main.category_id', ['c.name as category_name'])
            ->joinLeft(['p' => 'appointment_provider'], ' main.provider_id = p.provider_id', [
                "p.name as provider_name",
                "p.provider_id",
                "p.email",
                "p.mobile_number"
            ])
            ->joinLeft(['asl' => 'appointment_service_location'], ' main.service_id = asl.service_id', [
                "asl.service_location_id",
            ])
            ->joinLeft(array('al' => 'appointment_location'), 'asl.location_id = al.location_id', [
                "al.location_id",
                "al.name as location_name",
                "al.address",
                "al.latitude",
                "al.longitude",
                "al.location",
                "distance" => $formula,
                "al.is_allow_accept_payment"
            ]);

        $select->where("main.value_id = ?", $value_id);
        $select->where("main.is_delete = ?", 0);
        $select->where("main.service_type = ?", 2);
        $select->where("asl.is_delete = ?", 0);
        $select->where("main.status = ?", 1);
        $select->where("al.is_active = ?", 1);

        if (array_key_exists("category_id", $params) && !empty($params['category_id']) && $params['category_id'] > 0) {
            $select->where("main.category_id = ?", $params['category_id']);
        }

        if (array_key_exists("location_id", $params)) {
            $select->where("asl.location_id = ?", $params['location_id']);
        }

        if (array_key_exists("limit", $params) && array_key_exists("offset", $params)) {
            $select->limit($params["limit"], $params["offset"]);
        }

        if (array_key_exists("filter", $params)) {
            $select->where("(main.name LIKE ?)", "%" . $params["filter"] . "%");
        }

        if (array_key_exists("findBy", $params) && ($params['findBy'] == 'upcoming')) {
            $select->where("main.class_date >= ?", date("m/d/Y"));
        }


        switch ($sortingType) {
            case "alpha":
                $select->order(["main.name ASC"]);
                break;
            case "position":
                $select->order(["main.featured_position ASC"]);
                break;
            case "date":
                $select->order(["main.service_id DESC"]);
                break;
            case "distance":
                $select->order(["distance ASC"]);
                break;
        }

        return $this->_db->fetchAll($select);
    }


    /**
     * @param $class_id
     * @param int $limit
     * @return array
     */
    public function findByClassId($class_id)
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
                "service_points",
                "description",
                "class_date",
                "class_end_date",
                "numbers_of_days",
                "class_time",
                "service_type",
                "provider_id",
                "schedule_type",
                "day_of_week",
                "day_of_month",
                "status",
                "class_details",
                "days_selected",
                "is_delete",
                "featured_image",
                "total_tickets_per_user",
                "created_at",
                "special_price",
                "special_start",
                "special_end",
                "sale_tickets" => new Zend_Db_Expr('(' . $this->_db->select()->from(array('t' => 'appointment'), array(new Zend_Db_Expr('SUM(t.booked_seat_class)')))->where('t.service_id = main.service_id')->where('t.is_delete = ?', 0)->where('t.status IN (?)', [2, 3, 4, 9]) . ')'),
            ])
            ->joinLeft(['c' => 'appointment_category'], 'c.category_id = main.category_id', ['c.name as category_name'])
            ->joinLeft(['p' => 'appointment_provider'], ' main.provider_id = p.provider_id', [
                "p.name as provider_name",
                "p.provider_id",
                "p.email as provider_email",
                "p.mobile_number as provider_number",
                "p.image as provider_image"
            ])
            ->joinLeft(['asl' => 'appointment_service_location'], ' main.service_id = asl.service_id', [
                "asl.service_location_id",
            ])
            ->joinLeft(array('al' => 'appointment_location'), 'asl.location_id = al.location_id', [
                "al.location_id",
                "al.name as location_name",
                "al.address",
                "al.latitude",
                "al.longitude",
                "al.location",
                "al.is_allow_accept_payment"
            ]);

        $select->where("asl.is_delete = ?", 0);
        $select->where("main.service_id = ?", $class_id);


        return $this->_db->fetchRow($select);
    }

}
