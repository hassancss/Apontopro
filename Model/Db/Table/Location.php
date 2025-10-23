<?php

class Appointmentpro_Model_Db_Table_Location extends Core_Model_Db_Table
{
    protected $_name = "appointment_location";
    protected $_primary = "location_id";

    /**
     * @param $app_id
     * @param int $limit
     * @return array
     */
    public function findByValueId($value_id, $params = [])
    {
        $select = $this->_db->select()
            ->from(['main' => $this->_name], [
                "location_id",
                "name",
                "address",
                "about_us",
                "featured_image",
                "latitude",
                "longitude",
                "location",
                "business_timing",
                "is_active",
                "created_at"
            ]);

        $select->where("main.value_id = ?", $value_id);
        $select->where("main.is_delete = ?", 0);

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
                'COUNT(main.location_id)'
            ])
            ->where('main.value_id = ?', $value_id);

        $select->where("main.is_delete = ?", 0);

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
    public function findByAppId($value_id, $params = [])
    {
        $formula = new Zend_Db_Expr("0");
        $sortingType = $params["sortingType"];

        if (!empty($params["latitude"]) && !empty($params["longitude"])) {
            $formula = Siberian_Google_Geocoding::getDistanceFormula(
                $params["latitude"],
                $params["longitude"],
                'main',
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
                "location_id",
                "name",
                "address",
                "about_us",
                "featured_image",
                "is_active",
                "latitude",
                "longitude",
                "location",
                "business_timing",
                "distance" => $formula,
                "created_at"
            ]);

        $select->where("main.value_id = ?", $value_id);
        $select->where("main.is_delete = ?", 0);
        $select->where("main.is_active = ?", 1);
        $select->where("main.business_timing != ?", '');

        if (array_key_exists("limit", $params) && array_key_exists("offset", $params)) {
            $select->limit($params["limit"], $params["offset"]);
        }

        if (array_key_exists("search", $params)) {
            $select->where("(main.name LIKE ?)", "%" . $params["search"] . "%");
            $select->where("(main.name LIKE ? OR main.address LIKE ?)", "%" . $params["search"] . "%");
        }


        switch ($sortingType) {
            case "alpha":
                $select->order(["main.name ASC"]);
                break;
            case "position":
                $select->order(["main.featured_position ASC"]);
                break;
            case "date":
                $select->order(["main.location_id DESC"]);
                break;
            case "distance":
                $select->order(["distance ASC"]);
                break;
        }

        return $this->_db->fetchAll($select);
    }


    /**
     * @param $locationId
     * @param int $limit
     * @return array
     */
    public function findById($locationId, $params = [])
    {
        $select = $this->_db->select()
            ->from(['main' => $this->_name], [
                "location_id",
                "name",
                "address",
                "about_us",
                "featured_image",
                "is_active",
                "latitude",
                "longitude",
                "location",
                "business_timing",
                "created_at"
            ]);

        $select->where("main.location_id = ?", $locationId);

        return $this->_db->fetchRow($select);
    }


}
