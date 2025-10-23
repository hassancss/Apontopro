<?php

/**
 * Schema for appointment_services table
 */
$schemas = $schemas ?? [];
$schemas['appointment_service'] = [
    'service_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true
    ],
    'name' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci'
    ],
    'price' => [
        'type' => 'float',
        'default' => '0'
    ],
    'special_price' => [
        'type' => 'float',
        'default' => '0'
    ],
    'special_start' => [
        'type' => 'varchar(120)',
        'is_null' => true
    ],
    'special_end' => [
        'type' => 'varchar(120)',
        'is_null' => true
    ],
    'service_time' => [
        'type' => 'int(11)'
    ],
    'buffer_time' => [
        'type' => 'int(11)'
    ],
    'value_id' => [
        'type' => 'int(11) unsigned',
        'index' => [
            'key_name' => 'appointment_service_value_id',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'app_id' => [
        'type' => 'int(11) unsigned'
    ],
    'capacity' => [
        'type' => 'int(2)',
        'default' => '1'
    ],
    'category_id' => [
        'type' => 'int(11) unsigned'
    ],
    'image' => [
        'type' => 'int(11)'
    ],
    'description' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'is_null' => true
    ],
    'service_points' => [
        'type' => 'varchar(20)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci'
    ],
    'class_description' => [
        'type' => 'varchar(520)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci'
    ],
    'class_date' => [
        'type' => 'varchar(120)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci'
    ],
    'class_end_date' => [
        'type' => 'varchar(120)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci'
    ],
    'numbers_of_days' => [
        'type' => 'int(11) unsigned'
    ],
    'class_time' => [
        'type' => 'varchar(120)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci'
    ],
    'service_type' => [
        'type' => 'tinyint(2)',
        //'comment' => '1 for service and 2 for classes',
        'default' => 1
    ],
    'provider_id' => [
        'type' => 'int(11) unsigned'
    ],
    'status' => [
        'type' => 'tinyint(2)',
        //'comment' => '1 for active and 0 for inactive',
        'default' => 1
    ],
    'class_details' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci'
    ],
    'days_selected' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci'
    ],
    'is_checked_recurrent' => [
        'type' => 'tinyint(2)',
        //'comment' => '0 for not recurring 1 for recurring ',
        'default' => 0
    ],
    'class_recurrent_in' => [
        'type' => 'varchar(60)',
        'charset' => 'utf8',
        // 'comment' =>   ' 0 for dialy 1 for weekly and 2 for monthly  ',
        'default' => 0
    ],
    'schedule_type' => [
        'type' => 'enum(\'never\',\'daily\',\'weekly\',\'monthly\')',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'never',
    ],
    'day_of_week' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'is_null' => true,
    ],
    'day_of_month' => [
        'type' => 'int (11)',
        'is_null' => true,
        'default' => 0
    ],
    'is_delete' => [
        'type' => 'tinyint(2) unsigned',
        'default' => 0
    ],
    'featured_image' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'is_null' => true
    ],
    'total_tickets_per_user' => [
        'type' => 'int(11)',
        'default' => 1
    ],
    'total_booking_per_slot' => [
        'type' => 'int(11)',
        'default' => 1
    ],
    'visible_to_user' => [
        'type' => 'tinyint(1)',
        'default' => 1,
        'comment' => '1 for visible to users, 0 for hidden from users'
    ],
    'created_at' => [
        'type' => 'datetime',
    ],
    'updated_at' => [
        'type' => 'datetime',
    ]

];
