<?php

class Appointmentpro_Model_Db_Table_Provider extends Core_Model_Db_Table
{
    protected $_name = "appointment_provider";
    protected $_primary = "provider_id";

    /**
     * @param $value_id
     * @param int $limit
     * @return array
     */
    public function findByValueId($value_id, $params = [])
    {
        $select = $this->_db->select()
            ->from(['main' => $this->_name], [
                "provider_id",
                "name",
                "email",
                "mobile_number",
                "designation",
                "image",
                "calendar_url",
                "calendar_header_bg",
                "calendar_header_color",
                "calendar_body_bg",
                "calendar_body_color",
                "description",
                "is_popular",
                "is_active",
                "is_delete",
                "is_mobile_user",
                "user_role",
                "created_at"
            ]);

        $select->where("main.value_id = ?", $value_id);
        $select->where("main.is_delete = ?", 0);

        $select->joinLeft(['l' => 'appointment_location'], 'l.location_id = main.location_id', ['l.name as location_name']);


        if (array_key_exists("limit", $params) && array_key_exists("offset", $params)) {
            $select->limit($params["limit"], $params["offset"]);
        }

        if (array_key_exists("location_id", $params)) {
            $select->where("main.location_id = ?", $params["location_id"]);
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
            $select->order('main.position ASC');
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
                'COUNT(main.provider_id)'
            ])
            ->where('main.value_id = ?', $value_id);

        $select->where("main.is_delete = ?", 0);

        if (array_key_exists("location_id", $params)) {
            $select->where("main.location_id = ?", $params["location_id"]);
        }

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
    public function findActiveAllProviderForApp($value_id, $param)
    {
        $select = $this->_db->select()
            ->from(['main' => $this->_name], [
                "provider_id",
                "name",
                "email",
                "mobile_number",
                "designation",
                "image",
                "location_id",
                "is_popular",
                "calendar_url",
                "description",
                "is_active",
                "is_mobile_user",
                "user_role",
                "is_delete",
                "created_at"
            ]);

        $select->joinLeft(['l' => 'appointment_location'], 'l.location_id = main.location_id', []);

        $select->where("main.value_id = ?", $value_id);
        $select->where("l.is_delete = ?", 0);
        $select->where("l.is_active = ?", 1);
        $select->where("main.is_delete = ?", 0);
        $select->where("main.is_active = ?", 1);
        $select->where("main.is_popular = ?", 1);


        $select->order('main.position ASC');

        return $this->_db->fetchAll($select);
    }

    /**
     * @param $app_id
     * @param int $limit
     * @return array
     */
    public function findServiceProvider($locationId, $serviceId)
    {
        $select = $this->_db->select()
            ->from(['main' => $this->_name], [
                "provider_id",
                "name",
                "email",
                "mobile_number",
                "designation",
                "image",
                "is_popular",
                "calendar_url",
                "description",
                "is_active",
                "is_mobile_user",
                "user_role",
                "is_delete",
                "created_at"
            ]);

        $select->joinLeft(['sp' => 'appointment_service_provider'], 'sp.provider_id = main.provider_id', ['sp.id as service_provider_id']);

        $select->joinLeft(['sl' => 'appointment_service_location'], 'sp.service_location_id = sl.service_location_id', ['sl.service_location_id']);

        $select->where("main.location_id = ?", $locationId);
        $select->where("sl.service_id = ?", $serviceId);
        $select->where("sl.is_delete = ?", 0);
        $select->where("sp.is_delete = ?", 0);
        $select->where("main.is_delete = ?", 0);

        $select->order('main.position ASC');

        return $this->_db->fetchAll($select);
    }

    /**
     * @param $date , param
     * @param int $limit
     * @return array
     */
    public function getServiceTime($date, $param)
    {

        $location_id = $param['location_id'];
        $provider_id = $param['provider_id'];
        $service_id = $param['service_id'];

        $select = $this->_db->select()
            ->from(['asp' => 'appointment_service_provider'], ['asp.id'])
            ->joinLeft(array('apt' => 'appointment_provider_timing'), 'apt.location_id = ' . $location_id . ' AND apt.provider_id = ' . $provider_id, ['apt.timing'])
            ->joinLeft(array('asl' => 'appointment_service_location'), 'asl.service_location_id = asp.service_location_id', [])
            ->joinLeft(array('al' => 'appointment_location'), 'al.location_id = ' . $location_id, ['al.name as location_name', 'al.business_timing'])
            ->joinLeft(array('s' => 'appointment_service'), 'asl.service_id = s.service_id', ['s.name as service_name', 's.service_time', 's.buffer_time', "s.total_booking_per_slot"])
            ->joinLeft(array('p' => 'appointment_provider'), 'p.provider_id = ' . $provider_id, ['p.name as provider_name', "is_mobile_user", "user_role"])
            ->where('p.location_id = ?', $location_id)
            ->where('asp.provider_id = ?', $provider_id)
            ->where('s.service_id = ?', $service_id);

        $service_provider_data = $this->_db->fetchRow($select);

        if (sizeof($service_provider_data)) {

            $fromDate = strtotime($date);
            $toDate = strtotime($date . ' 23:59:59');

            $select2 = $this->_db->select()
                ->from(array('apt' => 'appointment'))
                ->where('apt.status  IN (?)', [2, 3, 4, 9])
                ->where('(apt.service_provider_id = ? OR apt.service_provider_id_2 = ?)', $provider_id)
                ->where('apt.is_delete = ?', 0)
                ->where('apt.appointment_date >= ?', $fromDate)
                ->where('apt.appointment_date <= ?', $toDate);

            $appointmentData = $this->_db->fetchAll($select2);

            $returnArray = [
                'spData' => $service_provider_data,
                'appointments' => $appointmentData
            ];
        } else {

            $returnArray = ['spData' => $service_provider_data, 'appointments' => []];
        }

        return $returnArray;
    }


    /**
     * @param $providerId , param
     * @param int $limit
     * @return array
     */
    public function getProviderServices($providerId, $param = [])
    {

        $select = $this->_db->select()
            ->from(['asp' => 'appointment_service_provider'], ['asp.id as service_provider_id'])
            ->joinLeft(array('asl' => 'appointment_service_location'), 'asl.service_location_id = asp.service_location_id', [])
            ->joinLeft(
                array('s' => 'appointment_service'),
                'asl.service_id = s.service_id',
                [
                    's.name as service_name',
                    's.service_time',
                    's.buffer_time',
                    's.price',
                    's.service_id',
                    "s.featured_image",
                    "s.special_price",
                    "s.special_start",
                    "s.special_end",
                    "s.total_booking_per_slot",
                    "s.description",
                    "s.visible_to_user"
                ]
            );

        $select->where('asp.provider_id = ?', $providerId);
        $select->where("asp.is_delete = ?", 0);
        $select->where("asl.is_delete = ?", 0);
        $select->where("s.is_delete = ?", 0);
        $select->where("s.status = ?", 1);

        $select->order('s.name ASC');

        return $this->toModelClass($this->_db->fetchAll($select));
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
                    $this->_db->update($this->_name, array('position' => $key + 1), array('provider_id = ? ' => $id[1]));
                }
            }
        } catch (Exception $e) {
            $this->_db->rollBack();
        }
        return $this;
    }


    /**
     * @param $app_id, $locationId, $appId
     * @param int $limit
     * @return array
     */
    public function getMobileProvider($locationId, $appId)
    {
        $select = $this->_db->select()
            ->from(['main' => $this->_name], [
                "provider_id",
                "name",
                "email",
                "user_role",
            ]);

        $select->joinLeft(['c' => 'customer'], 'c.email = main.email AND c.app_id = ' . $appId . '', ['c.customer_id']);

        $select->where("main.location_id = ?", $locationId);
        $select->where("main.is_active = ?", 1);
        $select->where("main.is_mobile_user = ?", 1);
        $select->where("main.is_delete = ?", 0);
        $select->order('main.provider_id ASC');

        return $this->_db->fetchAll($select);
    }
}
