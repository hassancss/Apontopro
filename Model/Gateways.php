<?php

class Appointmentpro_Model_Gateways extends Core_Model_Default
{

    /**
     * @var null
     */
    public static $acl = null;

    /**
     * @var string
     */
    protected $_db_table = Appointmentpro_Model_Db_Table_Gateways::class;

    /**
     * @var array
     */
    protected static $payment_method = [
        [
            'code' => 'paypal',
            'name' => 'Paypal',
            'online_payment' => 1
        ],
        [
            'code' => 'stripe',
            'name' => 'Stripe (Credit Card)',
            'online_payment' => 1
        ],
        [
            'code' => 'cod',
            'name' => 'Cash',
            'online_payment' => 1
        ],
        [
            'code' => 'banktransfer',
            'name' => 'Bank Transfer',
            'online_payment' => 1
        ],
        [
            'code' => 'payfast',
            'name' => 'PayFast',
            'online_payment' => 1
        ],
        // nmi payment
        [
            'code' => 'nmi',
            'name' => 'NMI (Credit Card)',
            'online_payment' => 1
        ],
    ];

    /**
     * @var array
     */
    public static function getDefaults()
    {
        return self::$payment_method;
    }
}
