<?php
/**
 * Schema for appointment_customer table
 */
$schemas = $schemas ?? [];
$schemas['appointment_customer'] = [
    'id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true
    ],
    'value_id' => [
        'type' => 'int(11)',
        'index' => [
            'key_name' => 'appointment_customer_value_id',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'customer_id' => [
        'type' => 'int(11) unsigned'
    ],
    'notification_time' => [
        'type' => 'tinyint(11)',
        'default' => 2
    ],
    'push_notification' => [
        'type' => 'tinyint(11)',
        'default' => 1
    ],
    'reminder_time' => [
        'type' => 'int(11)',
        'default' => 360
    ],
    'email_notification' => [
        'type' => 'tinyint(11)',
        'default' => 1
    ]
];
