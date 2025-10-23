<?php

/**
 * Schema for appointments table
 */
$schemas = $schemas ?? [];
$schemas['appointment'] = [
    'appointment_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true
    ],
    'parent_id' => [
        'type' => 'int(11) unsigned',
        'default' => 0
    ],
    'location_id' => [
        'type' => 'int(11) unsigned',
        'default' => 0
    ],
    'service_id' => [
        'type' => 'int(11) unsigned',
        'default' => 0
    ],
    'service_provider_id' => [
        'type' => 'int(11) unsigned'
    ],
    'service_provider_id_2' => [
        'type' => 'int(11) unsigned',
        'is_null' => true,
        'default' => null
    ],
    'customer_id' => [
        'type' => 'int(11) unsigned'
    ],
    'appointment_time' => [
        'type' => 'bigint(20)'
    ],
    'appointment_end_time' => [
        'type' => 'bigint(20)'
    ],
    'appointment_date' => [
        'type' => 'bigint(20)'
    ],
    'status' => [
        'type' => 'tinyint(4)'
    ],
    'notes' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci'
    ],
    'comments' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci'
    ],
    'value_id' => [
        'type' => 'int(11) unsigned',
        'index' => [
            'key_name' => 'appointment_value_id',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'additional_info' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci'
    ],
    'is_add_plc_points' => [
        'type' => 'tinyint(4)'
    ],
    'class_id' => [
        'type' => 'int(11) unsigned',
    ],
    'is_it_class' => [
        'type' => 'tinyint(2)',
        'default' => 0
    ],
    'booked_seat_class' => [
        'type' => 'int(4)',
        'default' => 1
    ],
    'days_recurent' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci'
    ],
    'push_message_id' => [
        'type' => 'int(11) unsigned',
        'is_null' => true,
    ],
    'total_amount' => [
        'type' => 'double',
        'default' => 0
    ],
    'service_amount' => [
        'type' => 'double',
        'default' => 0
    ],
    'service_plc_point' => [
        'type' => 'double',
        'default' => 0
    ],
    'is_delete' => [
        'type' => 'int(11) unsigned',
        'default' => 0
    ],
    'reminder_email' => [
        'type' => 'int(11) unsigned',
        'default' => 0
    ],
    'reminder_push' => [
        'type' => 'int(11) unsigned',
        'default' => 0
    ],
    'approval_reminder_email' => [
        'type' => 'int(11) unsigned',
        'default' => 0
    ],
    'g_calendar_id' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'is_null' => true,
    ],
    'created_source' => [
        'type' => 'varchar(20)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'desktop',
        'comment' => 'Source of appointment creation: app or desktop'
    ],
    'created_at' => [
        'type' => 'datetime'
    ],
    'updated_at' => [
        'type' => 'datetime'
    ]
];
