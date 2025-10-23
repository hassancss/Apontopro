<?php
/**
 * Schema for appointment_transactions table
 */
$schemas = $schemas ?? [];
$schemas['appointment_transactions'] = [
    'id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true
    ],
    'value_id' => [
        'type' => 'int(11) unsigned'
    ],
    'booking_id' => [
        'type' => 'int(11) unsigned'
    ],
    'customer_id' => [
        'type' => 'int(11) unsigned'
    ],
    'name' => [
        'type' => 'varchar(225)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci'
    ],
    'email' => [
        'type' => 'varchar(225)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci'
    ],
    'payment_type' => [
        'type' => 'varchar(60)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci'
    ],
    'payment_mode_id' => [
        'type' => 'int (11)',
        'default' => '0'
    ],
    'amount' => [
        'type' => 'varchar(60)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => '0'
    ],
    'additional_info' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'is_null' => true
    ],
    'total_amount' => [
        'type' => 'varchar(60)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => '0'
    ],
    'tax_amount' => [
        'type' => 'varchar(60)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => '0'
    ],
    'plc_points' => [
        'type' => 'varchar(60)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => '0'
    ],
    'plc_points_withdraw' => [
        'type' => 'tinyint(4)',
        'default' => 0
    ],
    'transaction_id' => [
        'type' => 'varchar(225)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci'
    ],
    'deposit' => [
        'type' => 'varchar(60)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci'
    ],
    'due_later' => [
        'type' => 'varchar(60)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci'
    ],
    'deposit_percentage' => [
        'type' => 'varchar(60)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci'
    ],
    'total_booking' => [
        'type' => 'int(11) unsigned'
    ],
    'booking_ids' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci'
    ],
    'payment_to' => [
        'type' => 'varchar(120)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'admin'
    ],
    'refund_info' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'is_null' => true
    ],
    'mobile' => [
        'type' => 'varchar(120)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'is_null' => true
    ],
    'status' => [
        'type' => 'tinyint(4)'
    ],
    'created_at' => [
        'type' => 'datetime'
    ],
    'updated_at' => [
        'type' => 'datetime'
    ]
];
