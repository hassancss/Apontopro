<?php
/**
 * Schema for appointment_payments table
 */
$schemas = $schemas ?? [];
$schemas['appointment_payments'] = [
    'id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true
    ],
     'app_id' => [
        'type' => 'int(11) unsigned'
     ],
    'value_id' => [
        'type' => 'int(11) unsigned'
    ],
    'require_payment_for_booking' => [
        'type' => 'varchar(60)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci'
    ],
   
    'paid_on_booking' => [
        'type' => 'tinyint(4)'
    ],
    'created_at' => [
        'type' => 'datetime'
    ],
    'updated_at' => [
        'type' => 'datetime'
    ]
];
