<?php

class Appointmentpro_Model_Db_Table_Transaction extends Core_Model_Db_Table {
    protected $_name                    = "appointment_transactions";
    protected $_primary                 = "id";



     /**
     * @param $valuesId
     * @param int $limit
     * @return array
     */
     public function findByValueId($valuesId, $params = []) {

        $select = $this->_db->select()
          ->from(['t' => $this->_name],  [
          		"t.id as transactionId",
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
            ])
            ->joinLeft(['a' => 'appointment'], 'a.appointment_id = t.booking_id', [
                "a.appointment_id",      
                "a.value_id",          
                "a.appointment_time",
                "a.appointment_end_time",
                "a.appointment_date",
                "a.status",
                "a.notes",
                "a.comments",
                "a.additional_info",
                "a.is_add_plc_points",
                "a.created_at",
              ])->joinLeft(array('al' => 'appointment_location'), 'a.location_id = al.location_id', [
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
              "s.image as service_image",
              "s.service_points",
            ])
            ->joinLeft(['c' => 'customer'], 'c.customer_id = a.customer_id', [
                'c.customer_id',
                'c.email',  
                'c.firstname', 
                'c.lastname'
              ]);

        $select->where('t.value_id = ?', $valuesId);
 

        if(array_key_exists("queries", $params)){
          
          $queries = $params['queries'];

          if(array_key_exists("from", $queries)){
            $select->where('t.created_at >= ?', date('Y-m-d', strtotime($queries["from"])));
          }

          if(array_key_exists("to", $queries)){
            $select->where('t.created_at <= ?', date('Y-m-d', strtotime($queries["from"])));
          }

          if(array_key_exists("status", $queries) && $queries['status'] != 'all' && $queries['status'] != ''){
            $select->where('t.status = ?', $queries['status'] );
          }

          if(array_key_exists("location_id", $queries) && $queries['location_id'] != 'all' && $queries['location_id'] != ''){
            $select->where('a.location_id = ?', $queries['location_id'] );
          }

        }

        if(array_key_exists("limit", $params) && array_key_exists("offset", $params)) {
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


        /**
     * @param $value_id
     */
    public function countAllForApp($value_id, $params = [])
    {
        $select =$this->_db->select()
            ->from(['t' => $this->_name], [ 
               'COUNT(t.id)'
                ])
            ->joinLeft(['a' => 'appointment'], 'a.appointment_id = t.booking_id', [
                "a.appointment_id",      
                "a.value_id",          
                "a.appointment_time",
                "a.appointment_end_time",
                "a.appointment_date",
                "a.status",
                "a.notes",
                "a.comments",
                "a.additional_info",
                "a.is_add_plc_points",
                "a.created_at",
              ])->joinLeft(array('al' => 'appointment_location'), 'a.location_id = al.location_id', [
                "al.location_id",                
                "al.name as location_name",
                "al.address",
                "al.is_allow_accept_payment"      
            ])
            ->where('t.value_id = ?', $value_id);


        if(array_key_exists("queries", $params)){
          
          $queries = $params['queries'];

          if(array_key_exists("from", $queries)){
            $select->where('t.created_at >= ?', date('Y-m-d', strtotime($queries["from"])));
          }

          if(array_key_exists("to", $queries)){
            $select->where('t.created_at <= ?', date('Y-m-d', strtotime($queries["from"])));
          }

          if(array_key_exists("status", $queries) && $queries['status'] != 'all' && $queries['status'] != ''){
            $select->where('t.status = ?', $queries['status'] );
          }

          if(array_key_exists("location_id", $queries) && $queries['location_id'] != 'all' && $queries['location_id'] != ''){
            $select->where('a.location_id = ?', $queries['location_id'] );
          }

        }
 
       $select->where('a.is_delete = ?', 0);
 
        return $this->_db->fetchCol($select);
    }


}