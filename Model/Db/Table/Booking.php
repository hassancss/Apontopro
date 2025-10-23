<?php

class Appointmentpro_Model_Db_Table_Booking extends Core_Model_Db_Table
{
    protected $_name = "appointment";
    protected $_primary = "appointment_id";


    public function getInfo($param)
    {

        $select = $this->_db->select()
            ->from(['al' => 'appointment_location'], [
                "al.location_id",
                "al.name as location_name",
                "al.address",
                "al.about_us",
                "al.featured_image",
                "al.latitude",
                "al.longitude",
                "al.location",
            ])
            ->joinLeft(array('s' => 'appointment_service'), 's.service_id = ' . $param['service_id'], [
                "s.name as service_name",
                "s.service_time",
                "s.buffer_time",
                "s.service_id",
                "s.price",
                "s.service_time",
                "s.buffer_time",
                "s.capacity",
                "s.category_id",
                "s.class_date",
                "s.class_end_date",
                "s.numbers_of_days",
                "s.class_time",
                "s.schedule_type",
                "s.day_of_week",
                "s.day_of_month",
                "s.featured_image as service_image",
                "s.service_points",
                "s.special_price",
                "s.special_start",
                "s.special_end",
                "s.total_booking_per_slot"
            ])
            ->joinLeft(['p' => 'appointment_provider'], 'p.provider_id = ' . $param['provider_id'], [
                "p.name as provider_name",
                "p.provider_id",
                "p.email",
                "p.mobile_number",
                "p.designation",
                "p.image as provider_image"
            ])
            ->where('al.location_id = ?', $param['location_id']);


        return $this->_db->fetchRow($select);
    }


    /**
     * @param $valuesId
     * @param int $limit
     * @return array
     */
    public function findByValueId($valuesId, $params = [])
    {

        $select = $this->_db->select()
            ->from(['a' => $this->_name], [
                "a.appointment_id",
                "a.value_id",
                "a.appointment_time",
                "a.appointment_end_time",
                "a.appointment_date",
                "a.status",
                "a.notes",
                "a.comments",
                "a.is_it_class",
                "a.booked_seat_class",
                "a.additional_info",
                "a.is_add_plc_points",
                "a.created_at",
                "a.updated_at"
            ])
            ->joinLeft(array('al' => 'appointment_location'), 'a.location_id = al.location_id', [
                "al.location_id",
                "al.name as location_name",
                "al.address",
                "al.is_allow_accept_payment"
            ])
            ->joinLeft(['p' => 'appointment_provider'], 'a.service_provider_id = p.provider_id', [
                "p.name as provider_name",
                "p.provider_id",
                "p.email",
                "p.mobile_number"
            ])
            ->joinLeft(array('s' => 'appointment_service'), 'a.service_id = s.service_id', [
                "s.name as service_name",
                "s.service_time",
                "s.buffer_time",
                "s.service_id",
                "s.price",
                "s.service_time",
                "s.buffer_time",
                "s.capacity",
                "s.category_id",
                "s.featured_image as service_image",
                "s.service_points",
                "s.class_date",
                "s.class_end_date",
                "s.class_time",
                "s.schedule_type",
                "s.day_of_week",
                "s.day_of_month",
                "s.service_type",
                "s.total_tickets_per_user",
                "s.special_price",
                "s.special_start",
                "s.special_end",
                "s.total_booking_per_slot"
            ])
            ->joinLeft(['t' => 'appointment_transactions'], 'a.appointment_id = t.booking_id', [
                "t.name as buyer_name",
                "t.email as buyer_email",
                "t.payment_type",
                "t.payment_mode_id",
                "t.additional_info",
                "t.amount",
                "t.total_amount",
                "t.tax_amount",
                "t.plc_points",
                "t.transaction_id",
                "t.status as payment_status",
                "t.total_booking",
                "t.payment_to"
            ])->joinLeft(['c' => 'customer'], 'c.customer_id = a.customer_id', [
                'c.customer_id',
                'c.email',
                'c.firstname',
                'c.lastname'
            ]);

        $select->where('a.value_id = ?', $valuesId);

        if (array_key_exists("type", $params)) {

            if (array_key_exists("todayDate", $params) && !empty($params['todayDate'])) {
                $todaysDate = $params['todayDate'];
            } else {
                $todaysDate = date('d-m-Y');
            }
            //$todaysDate = date('d-m-Y');
            $fromDate = strtotime($todaysDate);
            $toDate = strtotime($todaysDate . ' 23:59:59');

            if ($params['type'] == 'today') {
                $select->where('a.appointment_date >= ?', $fromDate);
                $select->where('a.appointment_date <= ?', $toDate);
            }

            if ($params['type'] == 'upcoming') {
                $select->where('a.appointment_date >= ?', $fromDate);
            }
        }

        if (array_key_exists("service_type", $params) && $params['service_type'] == 'services') {
            $select->where('a.is_it_class = ?', 0);
        }

        if (array_key_exists("service_type", $params) && $params['service_type'] == 'classes') {
            $select->where('a.is_it_class = ?', 1);
        }

        if (array_key_exists("queries", $params)) {

            $queries = $params['queries'];

            if (array_key_exists("from", $queries)) {
                $select->where('a.appointment_date >= ?', strtotime($queries['from']));
            }

            if (array_key_exists("to", $queries)) {
                $select->where('a.appointment_date <= ?', strtotime($queries['to']));
            }

            if (array_key_exists("status", $queries) && $queries['status'] != 'all' && $queries['status'] != '') {
                $select->where('a.status = ?', $queries['status']);
            }

            if (array_key_exists("location_id", $queries) && $queries['location_id'] != 'all' && $queries['location_id'] != '') {
                $select->where('a.location_id = ?', $queries['location_id']);
            }

            if (array_key_exists("service_id", $queries) && $queries['service_id'] != 'all' && $queries['service_id'] != '') {
                $select->where('a.service_id = ?', $queries['service_id']);
            }

            if (array_key_exists("customer_id", $queries) && $queries['customer_id'] != 'all' && $queries['customer_id'] != '') {
                $select->where('a.customer_id = ?', $queries['customer_id']);
            }


            if (array_key_exists("provider_id", $queries) && $queries['provider_id'] != 'all' && $queries['provider_id'] != '') {
                $select->where('a.service_provider_id = ?', $queries['provider_id']);
            }
        }

        if (array_key_exists("limit", $params) && array_key_exists("offset", $params)) {
            $select->limit($params["limit"], $params["offset"]);
        }

        if (array_key_exists("sorts", $params) && !empty($params["sorts"])) {
            $orders = [];
            foreach ($params["sorts"] as $key => $dir) {
                $order = ($dir == -1) ? "DESC" : "ASC";
                $orders = "a.{$key} {$order}";
            }
            $select->order($orders);
        } else {
            $select->order('a.created_at DESC');
        }

        $select->where('a.is_delete = ?', 0);

        return $this->toModelClass($this->_db->fetchAll($select));
    }
    public function findHistoryByValueId($valuesId, $params = [])
    {

        $query_option = "SELECT 
            a.appointment_id,
            a.value_id,
            a.appointment_time,
            a.appointment_end_time,
            a.appointment_date,
            a.status,
            a.notes,
            a.comments,
            a.is_it_class,
            a.booked_seat_class,
            a.additional_info,
            a.is_add_plc_points,
            a.created_at,
            a.updated_at,
            al.location_id,
            al.name as location_name,
            al.address,
            al.is_allow_accept_payment,
            p.name as provider_name,
            p.provider_id,
            p.email,
            p.mobile_number,
            s.name as service_name,
            s.service_time,
            s.buffer_time,
            s.service_id,
            s.price,
            s.service_time,
            s.buffer_time,
            s.capacity,
            s.category_id,
            s.featured_image as service_image,
            s.service_points,
            s.class_date,
            s.class_end_date,
            s.class_time,
            s.schedule_type,
            s.day_of_week,
            s.day_of_month,
            s.service_type,
            s.total_tickets_per_user,
            s.special_price,
            s.special_start,
            s.special_end,
            s.total_booking_per_slot,
            t.name as buyer_name,
            t.email as buyer_email,
            t.payment_type,
            t.payment_mode_id,
            t.additional_info,
            t.amount,
            t.total_amount,
            t.tax_amount,
            t.plc_points,
            t.transaction_id,
            t.status as payment_status,
            t.total_booking,
            t.payment_to,
            c.customer_id,
            c.email,
            c.firstname,
            c.lastname
        FROM appointment a 
        LEFT JOIN appointment_location al ON a.location_id = al.location_id
        LEFT JOIN appointment_provider p ON a.service_provider_id = p.provider_id
        LEFT JOIN appointment_service s ON a.service_id = s.service_id
        LEFT JOIN appointment_transactions t ON a.appointment_id = t.booking_id
        LEFT JOIN  customer c ON c.customer_id = a.customer_id
        WHERE a.value_id = $valuesId
        AND a.is_delete = 0";

        //Service id filter
        if (array_key_exists("service_id", $params) && $params['service_id'] != 'all' && $params['service_id'] != '') {
            $query_option .= " AND a.service_id = {$params['service_id']}";
        }
        //Customer filter
        if (array_key_exists("customer_id", $params) && $params['customer_id'] != 'all' && $params['customer_id'] != '') {
            $query_option .= " AND a.customer_id = {$params['customer_id']}";
        }
        // //Order By
        if (array_key_exists("sorts", $params) && !empty($params["sorts"])) {
            $orders = [];
            foreach ($params["sorts"] as $key => $dir) {
                $order = ($dir == -1) ? "DESC" : "ASC";
                $orders = "a.{$key} {$order}";
            }
            $query_option .= " ORDER BY {$orders}";
        } else {
            $query_option .= " ORDER BY a.created_at DESC";
        }
        //Limit
        if (array_key_exists("limit", $params) && array_key_exists("offset", $params)) {
            $query_option .= " LIMIT {$params["limit"]} OFFSET {$params["offset"]}";
        }
        //  return $query_option;                 
        return $this->_db->fetchAll($query_option);
    }


    /**
     * @param $valuesId
     * @param int $limit
     * @return array
     */
    public function findByValueIdForApp($valuesId, $params = [])
    {

        $select = $this->_db->select()
            ->from(['a' => $this->_name], [
                "a.appointment_id",
                "a.value_id",
                "a.appointment_time",
                "a.appointment_end_time",
                "a.appointment_date",
                "a.status",
                "a.notes",
                "a.comments",
                "a.is_it_class",
                "a.booked_seat_class",
                "a.additional_info",
                "a.is_add_plc_points",
                "a.created_at",
                "a.updated_at"
            ])
            ->joinLeft(array('al' => 'appointment_location'), 'a.location_id = al.location_id', [
                "al.location_id",
                "al.name as location_name",
                "al.address",
                "al.is_allow_accept_payment"
            ])
            ->joinLeft(['p' => 'appointment_provider'], 'a.service_provider_id = p.provider_id', [
                "p.name as provider_name",
                "p.provider_id",
                "p.email",
                "p.mobile_number"
            ])
            ->joinLeft(array('s' => 'appointment_service'), 'a.service_id = s.service_id', [
                "s.name as service_name",
                "s.service_time",
                "s.buffer_time",
                "s.service_id",
                "s.price",
                "s.service_time",
                "s.buffer_time",
                "s.capacity",
                "s.category_id",
                "s.featured_image as service_image",
                "s.service_points",
                "s.class_date",
                "s.class_end_date",
                "s.class_time",
                "s.schedule_type",
                "s.day_of_week",
                "s.day_of_month",
                "s.service_type",
                "s.total_tickets_per_user",
                "s.special_price",
                "s.special_start",
                "s.special_end",
                "s.total_booking_per_slot"
            ])
            ->joinLeft(['t' => 'appointment_transactions'], 'a.appointment_id = t.booking_id', [
                "t.name as buyer_name",
                "t.email as buyer_email",
                "t.payment_type",
                "t.payment_mode_id",
                "t.additional_info",
                "t.amount",
                "t.total_amount",
                "t.tax_amount",
                "t.plc_points",
                "t.transaction_id",
                "t.status as payment_status",
                "t.total_booking",
                "t.payment_to"
            ])->joinLeft(['c' => 'customer'], 'c.customer_id = a.customer_id', [
                'c.customer_id',
                'c.email',
                'c.firstname',
                'c.lastname'
            ]);

        $select->where('a.value_id = ?', $valuesId);

        if (array_key_exists("type", $params)) {

            $todaysDate = date('d-m-Y');
            $fromDate =   strtotime($todaysDate);
            $toDate =  strtotime($todaysDate . ' 23:59:59');

            if ($params['type'] == 'today') {
                $select->where('a.appointment_date >= ?', $fromDate);
                $select->where('a.appointment_date <= ?', $toDate);
            }

            if ($params['type'] == 'upcoming') {
                $select->where('a.appointment_date >= ?', $fromDate);
            }
        }

        if (array_key_exists("service_type", $params) && $params['service_type'] == 'services') {
            $select->where('a.is_it_class = ?', 0);
        }

        if (array_key_exists("service_type", $params) && $params['service_type'] == 'classes') {
            $select->where('a.is_it_class = ?', 1);
        }

        if (array_key_exists("from", $params)) {
            $select->where('a.appointment_date >= ?', strtotime($params['from']));
        }

        if (array_key_exists("to", $params)) {
            $select->where('a.appointment_date <= ?', strtotime($params['to']));
        }

        if (array_key_exists("status", $params) && $params['status'] != 'all' && $params['status'] != '') {
            $select->where('a.status = ?', $params['status']);
        }

        if (array_key_exists("location_id", $params) && $params['location_id'] != 'all' && $params['location_id'] != '') {
            $select->where('a.location_id = ?', $params['location_id']);
        }

        if (array_key_exists("service_id", $params) && $params['service_id'] != 'all' && $params['service_id'] != '') {
            $select->where('a.service_id = ?', $params['service_id']);
        }

        if (array_key_exists("provider_id", $params) && $params['provider_id'] != 'all' && $params['provider_id'] != '') {
            $select->where('a.service_provider_id = ?', $params['provider_id']);
        }

        if (array_key_exists("limit", $params) && array_key_exists("offset", $params)) {
            $select->limit($params["limit"], $params["offset"]);
        }

        if (array_key_exists("search", $params) && !empty($params["search"])) {
            $search = trim($params["search"]);
            $select->where("(c.firstname LIKE ? OR c.lastname LIKE ? OR c.email LIKE ? OR a.appointment_id LIKE ? OR p.name LIKE ? OR s.name LIKE ?)", "%" .  $search . "%");
        }

        $select->order('a.appointment_date ASC');
        $select->where('a.is_delete = ?', 0);

        return $this->toModelClass($this->_db->fetchAll($select));
    }


    /**
     * @param $valuesId
     * @param int $limit
     * @return array
     */
    public function countByValueIdForApp($valuesId, $params = [])
    {

        $select = $this->_db->select()
            ->from(['a' => $this->_name], [
                'COUNT(a.appointment_id)'
            ])
            ->joinLeft(array('al' => 'appointment_location'), 'a.location_id = al.location_id', [
                "al.location_id",
                "al.name as location_name",
                "al.address",
                "al.is_allow_accept_payment"
            ])
            ->joinLeft(['p' => 'appointment_provider'], 'a.service_provider_id = p.provider_id', [
                "p.name as provider_name",
                "p.provider_id",
                "p.email",
                "p.mobile_number"
            ])
            ->joinLeft(array('s' => 'appointment_service'), 'a.service_id = s.service_id', [
                "s.name as service_name",
                "s.service_time",
                "s.buffer_time",
                "s.service_id",
                "s.price",
                "s.service_time",
                "s.buffer_time",
                "s.capacity",
                "s.category_id",
                "s.featured_image as service_image",
                "s.service_points",
                "s.class_date",
                "s.class_end_date",
                "s.class_time",
                "s.schedule_type",
                "s.day_of_week",
                "s.day_of_month",
                "s.service_type",
                "s.total_tickets_per_user",
                "s.special_price",
                "s.special_start",
                "s.special_end",
                "s.total_booking_per_slot"
            ])
            ->joinLeft(['t' => 'appointment_transactions'], 'a.appointment_id = t.booking_id', [
                "t.name as buyer_name",
                "t.email as buyer_email",
                "t.payment_type",
                "t.payment_mode_id",
                "t.additional_info",
                "t.amount",
                "t.total_amount",
                "t.tax_amount",
                "t.plc_points",
                "t.transaction_id",
                "t.status as payment_status",
                "t.total_booking",
                "t.payment_to"
            ])->joinLeft(['c' => 'customer'], 'c.customer_id = a.customer_id', [
                'c.customer_id',
                'c.email',
                'c.firstname',
                'c.lastname'
            ]);

        $select->where('a.value_id = ?', $valuesId);

        if (array_key_exists("type", $params)) {

            $todaysDate = date('d-m-Y');
            $fromDate =   strtotime($todaysDate);
            $toDate =  strtotime($todaysDate . ' 23:59:59');

            if ($params['type'] == 'today') {
                $select->where('a.appointment_date >= ?', $fromDate);
                $select->where('a.appointment_date <= ?', $toDate);
            }

            if ($params['type'] == 'upcoming') {
                $select->where('a.appointment_date >= ?', $fromDate);
            }
        }

        if (array_key_exists("service_type", $params) && $params['service_type'] == 'services') {
            $select->where('a.is_it_class = ?', 0);
        }

        if (array_key_exists("service_type", $params) && $params['service_type'] == 'classes') {
            $select->where('a.is_it_class = ?', 1);
        }

        if (array_key_exists("from", $params)) {
            $select->where('a.appointment_date >= ?', strtotime($params['from']));
        }

        if (array_key_exists("to", $params)) {
            $select->where('a.appointment_date <= ?', strtotime($params['to']));
        }

        if (array_key_exists("status", $params) && $params['status'] != 'all' && $params['status'] != '') {
            $select->where('a.status = ?', $params['status']);
        }

        if (array_key_exists("location_id", $params) && $params['location_id'] != 'all' && $params['location_id'] != '') {
            $select->where('a.location_id = ?', $params['location_id']);
        }

        if (array_key_exists("service_id", $params) && $params['service_id'] != 'all' && $params['service_id'] != '') {
            $select->where('a.service_id = ?', $params['service_id']);
        }

        if (array_key_exists("provider_id", $params) && $params['provider_id'] != 'all' && $params['provider_id'] != '') {
            $select->where('a.service_provider_id = ?', $params['provider_id']);
        }

        if (array_key_exists("limit", $params) && array_key_exists("offset", $params)) {
            $select->limit($params["limit"], $params["offset"]);
        }

        if (array_key_exists("search", $params) && !empty($params["search"])) {
            $search = trim($params["search"]);
            $select->where("(c.firstname LIKE ? OR c.lastname LIKE ? OR c.email LIKE ? OR a.appointment_id LIKE ? OR p.name LIKE ? OR s.name LIKE ?)", "%" .  $search . "%");
        }

        if (array_key_exists("sorts", $params) && !empty($params["sorts"])) {
            $orders = [];
            foreach ($params["sorts"] as $key => $dir) {
                $order = ($dir == -1) ? "DESC" : "ASC";
                $orders = "a.{$key} {$order}";
            }
            $select->order($orders);
        } else {
            $select->order('a.appointment_date DESC');
        }

        $select->where('a.is_delete = ?', 0);

        return $this->_db->fetchCol($select);
    }


    /**
     * @param $params
     * @param int $limit
     * @return array
     */
    public function findDataForCronJob($params = [])
    {

        $select = $this->_db->select()
            ->from(['a' => $this->_name], [
                "a.appointment_id",
                "a.value_id",
                "a.appointment_time",
                "a.appointment_end_time",
                "a.appointment_date",
                "a.status",
                "a.reminder_email", //Added by Donald
                "a.reminder_email as is_reminder_sent", //Added by Donald
                "a.is_it_class",
                "a.booked_seat_class",
                "a.created_at",
            ])
            ->joinLeft(array('al' => 'appointment_location'), 'a.location_id = al.location_id', [
                "al.location_id",
                "al.name as location_name",
                "al.email as location_email"
            ])
            ->joinLeft(['p' => 'appointment_provider'], 'a.service_provider_id = p.provider_id', [
                "p.name as provider_name",
                "p.provider_id",
                "p.email as provider_email",
                "p.mobile_number"
            ])
            ->joinLeft(array('s' => 'appointment_service'), 'a.service_id = s.service_id', [
                "s.name as service_name",
                "s.service_points",
                "s.class_date",
                "s.class_end_date",
                "s.class_time",
                "s.service_type",
                "s.total_booking_per_slot"
            ])
            ->joinLeft(['t' => 'appointment_transactions'], 'a.appointment_id = t.booking_id', [
                "t.name as buyer_name",
                "t.email as buyer_email",
                "t.payment_type",
                "t.amount",
                "t.total_amount",
                "t.tax_amount",
                "t.plc_points",
                "t.transaction_id",
                "t.status as payment_status",
            ])->joinLeft(['c' => 'customer'], 'c.customer_id = a.customer_id', [
                'c.customer_id',
                'c.email as customer_email',
                'c.firstname as customer_firstname',
                'c.lastname as customer_lastname',
                'c.language as customer_language'
            ])->joinLeft(['ac' => 'appointment_customer'], 'ac.customer_id = c.customer_id', [
                'ac.push_notification',
                'ac.reminder_time',
                'ac.email_notification'
            ])->joinLeft(['v' => 'appointment_setting'], 'v.value_id = a.value_id', [
                'v.owner_email',
                'v.date_format',
                'v.time_format'
            ])->joinLeft(['apv' => 'application_option_value'], 'apv.value_id = a.value_id', [
                'apv.app_id'
            ])->joinLeft(['app' => 'application'], 'app.app_id = apv.app_id', [
                'app.name as app_name',
                'app.icon as app_icon'
            ]);


        if (array_key_exists("type", $params)) {

            $todaysDate = date('d-m-Y');
            $fromDate = strtotime($todaysDate);
            $toDate = strtotime($todaysDate . ' 23:59:59');

            if ($params['type'] == 'today') {
                $select->where('a.appointment_date >= ?', $fromDate);
                $select->where('a.appointment_date <= ?', $toDate);
            }

            if ($params['type'] == 'upcoming') {
                $select->where('a.appointment_date >= ?', $fromDate);
            }
        }

        if (array_key_exists("service_type", $params) && $params['service_type'] == 'services') {
            $select->where('a.is_it_class = ?', 0);
        }

        if (array_key_exists("service_type", $params) && $params['service_type'] == 'classes') {
            $select->where('a.is_it_class = ?', 1);
        }

        if (array_key_exists("status", $params)) {
            $select->where('a.status IN (?)', $params['status']);
        }

        if (array_key_exists("reminder_email", $params)) {
            $select->where('a.reminder_email = ?', $params['reminder_email']);
        }

        if (array_key_exists("approval_reminder_email", $params)) {
            $select->where('a.approval_reminder_email = ?', $params['approval_reminder_email']);
        }

        $select->where('a.is_delete = ?', 0);

        return $this->toModelClass($this->_db->fetchAll($select));
    }

    /**
     * @param $valuesId
     * @param int $limit
     * @return array
     */
    public function findByCustomerId($valuesId, $params = [])
    {
        $select = $this->_db->select()
            ->from(['a' => $this->_name], [
                "a.appointment_id",
                "a.value_id",
                "a.appointment_time",
                "a.appointment_end_time",
                "a.appointment_date",
                "a.status",
                "a.notes",
                "a.comments",
                "a.is_it_class",
                "a.booked_seat_class",
                "a.additional_info",
                "a.is_add_plc_points",
                "a.created_at",
                "a.customer_id"
            ])
            ->joinLeft(array('al' => 'appointment_location'), 'a.location_id = al.location_id', [
                "al.location_id",
                "al.name as location_name",
                "al.address",
                "al.is_allow_accept_payment"
            ])
            ->joinLeft(['p' => 'appointment_provider'], 'a.service_provider_id = p.provider_id', [
                "p.name as provider_name",
                "p.provider_id",
                "p.email",
                "p.mobile_number"
            ])
            ->joinLeft(array('s' => 'appointment_service'), 'a.service_id = s.service_id', [
                "s.name as service_name",
                "s.service_time",
                "s.buffer_time",
                "s.service_id",
                "s.price",
                "s.service_time",
                "s.buffer_time",
                "s.capacity",
                "s.category_id",
                "s.featured_image as service_image",
                "s.service_points",
                "s.class_date",
                "s.class_end_date",
                "s.class_time",
                "s.service_type",
                "s.schedule_type",
                "s.day_of_week",
                "s.day_of_month",
                "s.total_tickets_per_user",
                "s.special_price",
                "s.special_start",
                "s.special_end",
                "s.total_booking_per_slot"
            ])
            ->joinLeft(['t' => 'appointment_transactions'], 'a.appointment_id = t.booking_id', [
                "t.name as buyer_name",
                "t.email as buyer_email",
                "t.payment_type",
                "t.payment_mode_id",
                "t.additional_info",
                "t.amount",
                "t.total_amount",
                "t.tax_amount",
                "t.plc_points",
                "t.transaction_id",
                "t.status as payment_status",
                "t.total_booking",
                "t.payment_to"
            ]);

        $select->where('a.value_id = ?', $valuesId);

        if (array_key_exists("type", $params)) {

            $todaysDate = date('d-m-Y');
            $fromDate = strtotime($todaysDate);
            $toDate = strtotime($todaysDate . ' 23:59:59');

            if ($params['type'] == 'today') {
                $select->where('a.appointment_date >= ?', $fromDate);
                $select->where('a.appointment_date <= ?', $toDate);
            }

            if ($params['type'] == 'upcoming') {
                $select->where('a.appointment_date >= ?', $fromDate);
            }
        }

        if (array_key_exists("status", $params) && $params['status'] != 'all' && $params['status'] != '') {
            $select->where('a.status = ?', $params['status']);
        }

        if (array_key_exists("service_type", $params) && $params['service_type'] == 'services') {
            $select->where('a.is_it_class = ?', 0);
        }

        if (array_key_exists("service_type", $params) && $params['service_type'] == 'classes') {
            $select->where('a.is_it_class = ?', 1);
        }

        $select->where('a.customer_id = ?', $params['customer_id']);

        if (array_key_exists("sorts", $params) && !empty($params["sorts"])) {
            $orders = [];
            foreach ($params["sorts"] as $key => $dir) {
                $order = ($dir == -1) ? "DESC" : "ASC";
                $orders = "a.{$key} {$order}";
            }
            $select->order($orders);
        } else {
            $select->order('a.created_at DESC');
        }

        $select->where('a.is_delete = ?', 0);

        return $this->_db->fetchAll($select);
    }

    /**
     * Get recent notes for a specific customer
     *
     * @param int $valueId
     * @param int $customerId
     * @param int $limit
     * @return array
     */
    public function getNotesByCustomer($valueId, $customerId, $limit = 5)
    {
        $select = $this->_db->select()
            ->from(['a' => $this->_name], [
                'a.appointment_id',
                'a.notes',
                'a.appointment_date',
                'a.appointment_time',
                'a.created_at'
            ])
            ->where('a.value_id = ?', $valueId)
            ->where('a.customer_id = ?', $customerId)
            ->where('a.is_delete = ?', 0)
            ->where('a.notes IS NOT NULL')
            ->where('a.notes != ?', '')
            ->order('a.appointment_date DESC');

        if ($limit > 0) {
            $select->limit((int) $limit);
        }

        return $this->_db->fetchAll($select);
    }

    /**
     * @param $value_id
     */
    public function countAllForApp($value_id, $params = [])
    {
        $select = $this->_db->select()
            ->from(['a' => $this->_name], [
                'COUNT(a.appointment_id)'
            ])
            ->where('a.value_id = ?', $value_id);


        if (array_key_exists("type", $params)) {

            $todaysDate = date('d-m-Y');
            $fromDate = strtotime($todaysDate);
            $toDate = strtotime($todaysDate . ' 23:59:59');

            if ($params['type'] == 'today') {
                $select->where('a.appointment_date >= ?', $fromDate);
                $select->where('a.appointment_date <= ?', $toDate);
            }

            if ($params['type'] == 'upcoming') {
                $select->where('a.appointment_date >= ?', $fromDate);
            }
        }

        if (array_key_exists("service_type", $params) && $params['service_type'] == 'services') {
            $select->where('a.is_it_class = ?', 0);
        }

        if (array_key_exists("service_type", $params) && $params['service_type'] == 'classes') {
            $select->where('a.is_it_class = ?', 1);
        }

        if (array_key_exists("queries", $params)) {

            $queries = $params['queries'];

            if (array_key_exists("from", $queries)) {
                $select->where('a.appointment_date >= ?', strtotime($queries['from']));
            }

            if (array_key_exists("to", $queries)) {
                $select->where('a.appointment_date <= ?', strtotime($queries['to']));
            }

            if (array_key_exists("status", $queries) && $queries['status'] != 'all' && $queries['status'] != '') {
                $select->where('a.status = ?', $queries['status']);
            }

            if (array_key_exists("location_id", $queries) && $queries['location_id'] != 'all' && $queries['location_id'] != '') {
                $select->where('a.location_id = ?', $queries['location_id']);
            }

            if (array_key_exists("service_id", $queries) && $queries['service_id'] != 'all' && $queries['service_id'] != '') {
                $select->where('a.service_id = ?', $queries['service_id']);
            }

            if (array_key_exists("customer_id", $queries) && $queries['customer_id'] != 'all' && $queries['customer_id'] != '') {
                $select->where('a.customer_id = ?', $queries['customer_id']);
            }

            if (array_key_exists("provider_id", $queries) && $queries['provider_id'] != 'all' && $queries['provider_id'] != '') {
                $select->where('a.service_provider_id = ?', $queries['provider_id']);
            }
        }


        $select->where('a.is_delete = ?', 0);

        return $this->_db->fetchCol($select);
    }


    public function getBookingById($booking_id)
    {

        $select = $this->_db->select()
            ->from(['a' => $this->_name], [
                "a.appointment_id",
                "a.value_id",
                "a.appointment_time",
                "a.appointment_end_time",
                "a.appointment_date",
                "a.status",
                "a.notes",
                "a.comments",
                "a.is_it_class",
                "a.booked_seat_class",
                "a.additional_info",
                "a.total_amount as total_booking_amount",
                "a.is_add_plc_points",
                "a.service_amount",
                "a.service_plc_point",
                "a.created_at",
                "a.service_provider_id",      // First provider
                "a.service_provider_id_2"     // Second provider for break time services
            ])
            ->joinLeft(array('al' => 'appointment_location'), 'a.location_id = al.location_id', [
                "al.location_id",
                "al.name as location_name",
                "al.address",
                "al.about_us",
                "al.email as location_email",
                "al.featured_image",
                "al.latitude",
                "al.longitude",
                "al.location",
                "al.is_allow_accept_payment"
            ])
            ->joinLeft(['p' => 'appointment_provider'], 'a.service_provider_id = p.provider_id', [
                "p.name as provider_name",
                "p.provider_id",
                "p.email",
                "p.mobile_number",
                "p.designation",
                "p.image as provider_image",
                "p.google_refresh_token"
            ])
            ->joinLeft(array('s' => 'appointment_service'), 'a.service_id = s.service_id', [
                "s.name as service_name",
                "s.service_time",
                "s.buffer_time",
                "s.service_id",
                "s.price",
                "s.service_time",
                "s.buffer_time",
                "s.capacity",
                "s.category_id",
                "s.featured_image as service_image",
                "s.service_points",
                "s.class_date",
                "s.class_end_date",
                "s.class_time",
                "s.service_type",
                "s.schedule_type",
                "s.day_of_week",
                "s.day_of_month",
                "s.total_tickets_per_user",
                "s.special_price",
                "s.special_start",
                "s.special_end",
                "s.total_booking_per_slot"
            ])
            ->joinLeft(['t' => 'appointment_transactions'], 'a.appointment_id = t.booking_id', [
                "t.name as buyer_name",
                "t.email as buyer_email",
                "t.mobile as buyer_mobile",
                "t.payment_type",
                "t.payment_mode_id",
                "t.additional_info",
                "t.amount",
                "t.total_amount",
                "t.tax_amount",
                "t.plc_points",
                "t.transaction_id",
                "t.status as payment_status",
                "t.total_booking",
                "t.payment_to",
                "t.refund_info",
                "t.plc_points_withdraw"
            ])->joinLeft(['c' => 'customer'], 'c.customer_id = a.customer_id', [
                'c.customer_id',
                'c.email as customer_email',
                'c.firstname',
                'c.lastname'
            ])
            ->where('a.appointment_id = ?', $booking_id);


        return $this->_db->fetchRow($select);
    }


    /**
     * @param $valuesId
     * @param int $limit
     * @return array
     */
    public function findByLocationIdActiveBooking($valuesId, $params = [])
    {

        $select = $this->_db->select()
            ->from(['a' => $this->_name], [
                "a.appointment_id",
                "a.value_id",
                "a.appointment_time",
                "a.appointment_end_time",
                "a.appointment_date",
                "a.status",
                "a.notes",
                "a.comments",
                "a.is_it_class",
                "a.booked_seat_class",
                "a.additional_info",
                "a.is_add_plc_points",
                "a.created_at",
                "a.created_source", // Add created_source column
                "a.service_provider_id_2", // Add second provider ID for break time services
                "total_bookings" => new Zend_Db_Expr('(' . $this->_db->select()->from(array('b' =>  $this->_name), array(new Zend_Db_Expr('COUNT(b.appointment_id)')))->where('b.appointment_date = a.appointment_date')->where('b.appointment_time = a.appointment_time')->where('b.service_provider_id = a.service_provider_id')->where('b.status NOT IN (?)', [5, 6, 8])->where('b.is_delete = ?', 0) . ')')
                //  "total_bookings" => new Zend_Db_Expr('(' . $this->_db->select()->from(array('b' =>  $this->_name), array(new Zend_Db_Expr('COUNT(b.appointment_id)')))->where('b.appointment_date = a.appointment_date')->where('b.appointment_time = a.appointment_time')->where('b.service_provider_id = a.service_provider_id')->where('b.status IN (?)', [2, 3, 4, 9]) . ')')
            ])
            ->joinLeft(array('al' => 'appointment_location'), 'a.location_id = al.location_id', [
                "al.location_id",
                "al.name as location_name",
                "al.address",
                "al.is_allow_accept_payment"
            ])
            ->joinLeft(['p' => 'appointment_provider'], 'a.service_provider_id = p.provider_id', [
                "p.name as provider_name",
                "p.provider_id",
                "p.email",
                "p.mobile_number"
            ])
            ->joinLeft(array('s' => 'appointment_service'), 'a.service_id = s.service_id', [
                "s.name as service_name",
                "s.service_time",
                "s.buffer_time",
                "s.service_id",
                "s.price",
                "s.service_time",
                "s.buffer_time",
                "s.capacity",
                "s.category_id",
                "s.featured_image as service_image",
                "s.service_points",
                "s.class_date",
                "s.class_end_date",
                "s.class_time",
                "s.service_type",
                "s.schedule_type",
                "s.day_of_week",
                "s.day_of_month",
                "s.total_tickets_per_user",
                "s.special_price",
                "s.special_start",
                "s.special_end",
                "s.total_booking_per_slot"
            ])
            ->joinLeft(['t' => 'appointment_transactions'], 'a.appointment_id = t.booking_id', [
                "t.name as buyer_name",
                "t.email as buyer_email",
                "t.payment_type",
                "t.payment_mode_id",
                "t.additional_info",
                "t.amount",
                "t.total_amount",
                "t.tax_amount",
                "t.plc_points",
                "t.transaction_id",
                "t.status as payment_status",
                "t.total_booking",
                "t.payment_to"
            ])->joinLeft(['c' => 'customer'], 'c.customer_id = a.customer_id', [
                'c.customer_id',
                'c.email',
                'c.firstname',
                'c.lastname'
            ]);

        // Join break config table only if chunk_mode parameter is enabled
        if (array_key_exists("chunk_mode", $params) && $params['chunk_mode'] == true) {
            $select->joinLeft(['sbc' => 'appointment_service_break_config'], 'sbc.service_id = s.service_id', [
                'sbc.work_time_before_break',
                'sbc.break_duration',
                'sbc.work_time_after_break',
                'sbc.break_is_bookable'
            ]);
        }

        $select->where('a.value_id = ?', $valuesId);

        if (array_key_exists("service_type", $params) && $params['service_type'] == 'services') {
            $select->where('a.is_it_class = ?', 0);
        }

        if (array_key_exists("appointment_start_date", $params) && $params['appointment_start_date'] != '') {
            $select->where('a.appointment_date >= ?', $params['appointment_start_date']);
        }

        if (array_key_exists("appointment_end_date", $params) && $params['appointment_end_date'] != '') {
            $select->where('a.appointment_date <= ?', $params['appointment_end_date']);
        }

        if (array_key_exists("service_type", $params) && $params['service_type'] == 'classes') {
            $select->where('a.is_it_class = ?', 1);
        }

        if (array_key_exists("location_id", $params) && $params['location_id'] != '') {
            $select->where('a.location_id = ?', $params['location_id']);
        }

        $select->order('a.created_at DESC');
        $select->where('a.is_delete = ?', 0);
        //$select->where('a.status IN (?)', [2, 3, 4, 9]); // Show all appointments except failed, canceled, and rejected
        // Show all appointments except failed, canceled, and rejected
        $select->where('a.status NOT IN (?)', [5, 6, 8]);

        return $this->toModelClass($this->_db->fetchAll($select));
    }


    /**
     * @param $value_id
     */
    public function countTotalDateRangeConfirmBooking($value_id, $params = [])
    {
        $select = $this->_db->select()
            ->from(['main' => $this->_name], [
                'count' => new Zend_Db_Expr('COUNT(main.appointment_id)')
            ])
            ->where('main.value_id = ?', $value_id);

        $select->where('main.appointment_date >= ?', strtotime($params['week_start_date']));
        $select->where('main.appointment_date <= ?', strtotime($params['week_end_date'] . ' 23:59:59'));

        $select->where('main.is_delete = ?', 0);
        $select->where('main.status IN (?)', [2, 3, 4, 9]);


        return (int)$this->_db->fetchOne($select);
    }


    /**
     * @param $value_id
     */
    public function sumTotalConfirmProjectedEstimatedRevenue($value_id, $params = [])
    {
        $select = $this->_db->select()
            ->from(['main' => $this->_name], [])
            ->where('main.value_id = ?', $value_id);

        $select->joinLeft(['t' => 'appointment_transactions'], 'main.appointment_id = t.booking_id', [
            'total' => new Zend_Db_Expr('SUM(t.total_amount)')
        ]);

        $select->where('main.appointment_date >= ?', strtotime($params['week_start_date']));
        $select->where('main.appointment_date <= ?', strtotime($params['week_end_date'] . ' 23:59:59'));

        $select->where('main.is_delete = ?', 0);
        $select->where('main.status IN (?)', $params['status']);


        return (int)$this->_db->fetchOne($select);
    }


    /**
     * @param $value_id
     */
    public function countMonthTotalBooking($value_id, $params = [])
    {
        $select = $this->_db->select()
            ->from(['main' => $this->_name], [
                'appointment_date',
                'count' => new Zend_Db_Expr('COUNT(main.appointment_id)')
            ])
            ->where('main.value_id = ?', $value_id);

        $select->where('main.appointment_date >= ?', strtotime($params['week_start_date']));
        $select->where('main.appointment_date <= ?', strtotime($params['week_end_date'] . ' 23:59:59'));

        $select->where('main.is_delete = ?', 0);
        $select->where('main.status IN (?)', $params['status']);

        $select->group('main.appointment_date');

        return $this->_db->fetchAll($select);
    }
    public function reminderSent($uid)
    {
        $update_data['reminder_email'] = 1;
        return $this->_db->update('appointment', $update_data, ['appointment_id = ?' => $uid]);
    }
}
