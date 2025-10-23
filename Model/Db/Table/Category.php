<?php

class Appointmentpro_Model_Db_Table_Category extends Core_Model_Db_Table
{
    protected $_name = "appointment_category";
    protected $_primary = "category_id";


    /**
     * @param $value_id
     * @param int $limit
     * @return array
     */
    public function findByValueId($value_id, $params = [])
    {
        $select = $this->_db->select()
            ->from(['main' => $this->_name], [
                "category_id",
                "name",
                "image",
                "category_for",
                "is_active",
                "position",
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

        if (array_key_exists("category_for", $params)) {
            $select->where("main.category_for = ?", $params['category_for']);
        }

        if (array_key_exists("sorts", $params) && !empty($params["sorts"])) {
            $orders = [];
            foreach ($params["sorts"] as $key => $dir) {
                $order = ($dir == -1) ? "DESC" : "ASC";
                $orders = "main.{$key} {$order}";
            }
            $select->order($orders);
        } else {
            $select->order('main.position ASC');
        }

        return $this->toModelClass($this->_db->fetchAll($select));
    }


    /**
     * @param $value_id
     * @param int $limit
     * @return array
     */
    public function findAppByValueId($value_id, $params = [])
    {
        $select = $this->_db->select()
            ->from(['main' => $this->_name], [
                "category_id",
                "name",
                "image",
                "category_for",
                "is_active",
                "position",
                "created_at"
            ]);

        $select->where("main.value_id = ?", $value_id);
        $select->where("main.is_delete = ?", 0);
        $select->where("main.is_active = ?", 1);
        $select->where("main.top_category = ?", 1);

        if (array_key_exists("category_for", $params)) {
            $select->where("main.category_for = ?", $params['category_for']);
        }

        $select->order('main.position ASC');

        return $this->toModelClass($this->_db->fetchAll($select));
    }


    /**
     * @param $value_id
     */
    public function countAllForApp($value_id, $params = [])
    {
        $select = $this->_db->select()
            ->from(['main' => $this->_name], [
                'COUNT(main.category_id)'
            ])
            ->where('main.value_id = ?', $value_id);

        $select->where("main.is_delete = ?", 0);

        if (array_key_exists("category_for", $params)) {
            $select->where("main.category_for = ?", $params['category_for']);
        }


        if (array_key_exists("filter", $params)) {
            $select->where("(main.name LIKE ?)", "%" . $params["filter"] . "%");
        }

        return $this->_db->fetchCol($select);
    }


    public function findByLocationId($locationId, $params = [])
    {
        $select = $this->_db->select()
            ->from(['main' => $this->_name], ['main.name as category_name',
                'main.image as category_image',
                "total_service" => new Zend_Db_Expr('(' . $this->_db->select()->from(array('t' => 'appointment_service'), array(new Zend_Db_Expr('COUNT(t.service_id)')))->where('main.category_id = t.category_id')->where("t.is_delete = ?", 0)->where("t.status = ?", 1)->joinLeft(['st' => 'appointment_service_location'], 't.service_id = st.service_id', [])->where("st.location_id = ?", $locationId) . ')')
            ]);

        $select->joinLeft(['s' => 'appointment_service'], 's.category_id = main.category_id', ["s.category_id as main_category_id"]);
        $select->joinLeft(['sl' => 'appointment_service_location'], 's.service_id = sl.service_id', ['service_location_id']);

        if (array_key_exists("category_for", $params)) {
            $select->where("main.category_for = ?", $params['category_for']);
        }

        $select->where("sl.location_id = ?", $locationId);
        $select->where("sl.is_delete = ?", 0);
        $select->where("main.is_delete = ?", 0);
        $select->where("main.is_active = ?", 1);

        $select->where("s.is_delete = ?", 0);
        $select->where("s.status = ?", 1);
        $select->group(["s.category_id"]);

        $select->order('main.position ASC');

        return $this->_db->fetchAll($select);
    }


    public function findLocationByCategoryId($category_id, $params = [])
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
            ->from(['main' => $this->_name], ['main.name as category_name',
                'main.category_id',
                'main.image as category_image',
                "total_service" => new Zend_Db_Expr('(' . $this->_db->select()->from(array('t' => 'appointment_service'), array(new Zend_Db_Expr('COUNT(t.service_id)')))->where('main.category_id = t.category_id')->where("t.is_delete = ?", 0)->where("t.status = ?", 1) . ')')
            ]);

        $select->joinLeft(['s' => 'appointment_service'], 's.category_id = main.category_id', ["s.category_id as main_category_id",]);
        $select->joinLeft(['sl' => 'appointment_service_location'], 's.service_id = sl.service_id', ['service_location_id'])
            ->joinLeft(array('al' => 'appointment_location'), 'sl.location_id = al.location_id', [
                "al.location_id",
                "al.name as location_name",
                "al.address",
                "al.is_allow_accept_payment",
                "al.about_us",
                "al.featured_image",
                "al.is_active",
                "al.latitude",
                "al.longitude",
                "al.location",
                "al.business_timing",
                "distance" => $formula,
            ]);

        if (array_key_exists("category_for", $params)) {
            $select->where("main.category_for = ?", $params['category_for']);
        }

        if (array_key_exists("value_id", $params)) {
            $select->where("al.value_id = ?", $params['value_id']);
        }

        $select->where("s.category_id = ?", $category_id);
        $select->where("sl.is_delete = ?", 0);
        $select->where("main.is_delete = ?", 0);
        $select->where("main.is_active = ?", 1);


        $select->where("s.is_delete = ?", 0);
        $select->where("s.status = ?", 1);
        $select->group(["sl.location_id"]);

        $select->where("al.is_delete = ?", 0);
        $select->where("al.is_active = ?", 1);
        $select->where("al.business_timing != ?", '');

        switch ($sortingType) {
            case "alpha":
                $select->order(["al.name ASC"]);
                break;
            case "position":
                $select->order(["al.featured_position ASC"]);
                break;
            case "date":
                $select->order(["al.location_id DESC"]);
                break;
            case "distance":
                $select->order(["distance ASC"]);
                break;
        }


        return $this->_db->fetchAll($select);
    }

    /**
     * @param $data
     */
    public function sortable($data)
    {
        try {
            foreach ($data as $key => $value) {

                if ($value != '') {
                    $id = explode('_', $value, 2);
                    $this->_db->update($this->_name, array('position' => $key + 1), array('category_id = ? ' => $id[1]));
                }

            }
        } catch (Exception $e) {
            $this->_db->rollBack();
        }
        return $this;
    }


}
