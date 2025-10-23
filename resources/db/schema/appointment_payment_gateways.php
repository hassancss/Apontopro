<?php

/**
 *
 * Schema definition for 'appointment_payment_gateways'
 *
 * Last update: 2020-12-31
 *
 */
$schemas = $schemas ?? [];
$schemas['appointment_payment_gateways'] = [
    'id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ],
    'value_id' => [
        'type' => 'int(11) unsigned',
        'foreign_key' => [
            'table' => 'application_option_value',
            'column' => 'value_id',
            'name' => 'FK_APPOINTMENTPRO_PAYMENT_GATEWAY_VID',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ],
        'index' => [
            'key_name' => 'value_id',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'location_id' => [
        'type' => 'int(11)',
        'default' => '0'
    ],
    'lable_name' => [
        'type' => 'varchar(255)',
        'is_null' => false,
    ],
    'gateway_code' => [
        'type' => 'varchar(255)',
        'is_null' => false,
    ],
    'shot_description' => [
        'type' => 'varchar(255)',
        'is_null' => true,
    ],
    'instructions' => [
        'type' => 'text',
        'is_null' => true,
    ],
    'status' => [
        'type' => 'enum(\'active\',\'inactive\')',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'payment_mode' => [
        'type' => 'varchar(255)',
        'is_null' => true,
    ],
    'username' => [
        'type' => 'varchar(255)',
        'is_null' => true,
    ],
    'signature' => [
        'type' => 'varchar(255)',
        'is_null' => true,
    ],
    'password' => [
        'type' => 'varchar(255)',
        'is_null' => true,
    ],
    'sandboxusername' => [
        'type' => 'varchar(255)',
        'is_null' => true,
    ],
    'sandboxsignature' => [
        'type' => 'varchar(255)',
        'is_null' => true,
    ],
    'sandboxpassword' => [
        'type' => 'varchar(255)',
        'is_null' => true,
    ],
    'publishable_key' => [
        'type' => 'varchar(255)',
        'is_null' => true,
    ],
    'secret_key' => [
        'type' => 'varchar(255)',
        'is_null' => true,
    ],
    'merchant_id' => [
        'type' => 'varchar(255)',
        'is_null' => true,
    ],
    'merchant_key' => [
        'type' => 'varchar(255)',
        'is_null' => true,
    ],
    'is_live' => [
        'type' => 'int(11)',
        'is_null' => 0,
    ],
    // Processing fee in %
    'processing_fee' => [
        'type' => 'int(11)',
        'is_null' => false,
        'default' => "0"
    ],
    'is_test_mode' => [
        'type' => 'int(11)',
        'is_null' => 0,
        'default' => 0,
    ],
    'created_at' => [
        'type' => 'datetime',
    ],
    'updated_at' => [
        'type' => 'datetime',
    ],
];
