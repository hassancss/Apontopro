<?php

class Appointmentpro_Model_Db_Table_Customer extends Core_Model_Db_Table
{
    protected $_name = "appointment_customer";
    protected $_primary = "id";


         /**
     * @param $locationId
     * @param int $limit
     * @return array
     */
     public function findByLocationId($locationId, $params = []) {

        $select = $this->_db->select()
          ->from(['a' => 'appointment'],  [
          		"a.appointment_id",
            ])
            ->joinLeft(['c' => 'customer'], 'c.customer_id = a.customer_id', [
                'c.customer_id',
                'c.email',  
                'c.firstname', 
                'c.lastname',
                'c.mobile',
                "c.image",
                "registration_date" => "c.created_at",
                "registration_timestamp" => new Zend_Db_Expr("UNIX_TIMESTAMP(c.created_at)"),
              ]);

        $select->where('a.location_id = ?', $locationId);
 
        if(array_key_exists("limit", $params) && array_key_exists("offset", $params)) {
              $select->limit($params["limit"], $params["offset"]);
        }

        if (array_key_exists("search", $params) && !empty($params["search"])) {
            $search = trim($params["search"]);
            $select->where("(c.firstname LIKE ? OR c.lastname LIKE ? OR c.nickname LIKE ? OR c.email LIKE ?)", "%" .  $search . "%");
        }
         
        $select->order('c.firstname ASC');
        $select->group("a.customer_id");

        return $this->toModelClass($this->_db->fetchAll($select));
     
    }

}
