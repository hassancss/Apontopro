<?php

class Appointmentpro_Model_Appointment extends Core_Model_Default
{
    /**
     * @var null
     */
    public static $acl = null;

    /**
     * @var string
     */
    protected $_db_table = Appointmentpro_Model_Db_Table_Appointment::class;


    public static function getBookingStatus($key = null)
    {

        $status = [];
        $status[0] = 'Pending';
        $status[1] = 'Pending Payment';
        $status[2] = 'Processing';
        $status[3] = 'Accepted';
        $status[4] = 'Completed';
        $status[5] = 'Failed';
        $status[6] = 'Canceled';
        $status[7] = 'On Hold';
        $status[8] = 'Rejected';
        $status[9] = 'Pending approval';

        return $key != null ? $status[$key] : $status;
    }


    public static function getPaymentStatus($key = null)
    {

        $status = [];
        $status[0] = 'Pending';
        $status[1] = 'Processing';
        $status[2] = 'Paid';
        $status[3] = 'Failed';
        $status[4] = 'Canceled';
        $status[5] = 'On Hold';
        $status[6] = 'Refunded';
        $status[7] = 'Refunded Failed';

        return $key != null ? $status[$key] : $status;
    }

    public static function getBookingTextcolor($key = null)
    {

        $status = [];
        $status[0] = 'text-warning';
        $status[1] = 'text-warning';
        $status[2] = 'text-info';
        $status[3] = 'text-primary';
        $status[4] = 'text-success';
        $status[5] = 'text-danger';
        $status[6] = 'text-muted';
        $status[7] = 'text-secondary';
        $status[8] = 'text-dark';
        $status[9] = 'text-danger';

        return $key != null ? $status[$key] : $status;
    }


    public static function getPaymentTextcolor($key = null)
    {

        $status = [];
        $status[0] = 'text-warning';
        $status[1] = 'text-warning';
        $status[2] = 'text-success';
        $status[3] = 'text-danger';
        $status[4] = 'text-dark';
        $status[5] = 'text-muted';
        $status[6] = 'text-primary';

        return $key != null ? $status[$key] : $status;
    }


    public static function getDefaultLabelName($key = null)
    {

        $status = [];
        $status['providers'] = 'Providers';
        $status['locations'] = 'Locations';
        $status['classes'] = 'Classes';
        $status['services'] = 'Services';
        $status['category'] = 'Category';
        $status['service'] = 'Service';
        $status['provider'] = 'Provider';
        $status['location'] = 'Location';
        $status['class'] = 'Class';
        $status['categories'] = 'Categories';
        $status['popular_providers'] = 'Popular Providers';
        $status['top_categories'] = 'Top Categories';

        return $key != null ? $status[$key] : $status;
    }

}
