<?php

/**
 * Schema for appointment_service_break_config table
 * This table handles the break time configuration for services
 */
$schemas = $schemas ?? [];
$schemas['appointment_service_break_config'] = [
    'config_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true
    ],
    'service_id' => [
        'type' => 'int(11) unsigned',
        'index' => [
            'key_name' => 'service_break_config_service_id',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'has_break_time' => [
        'type' => 'tinyint(1)',
        'default' => 0,
        'comment' => '1 if service has break time, 0 if not'
    ],
    'work_time_before_break' => [
        'type' => 'int(11)',
        'default' => 0,
        'comment' => 'Working time before break in minutes'
    ],
    'break_duration' => [
        'type' => 'int(11)',
        'default' => 0,
        'comment' => 'Break duration in minutes'
    ],
    'work_time_after_break' => [
        'type' => 'int(11)',
        'default' => 0,
        'comment' => 'Working time after break in minutes'
    ],
    'break_is_bookable' => [
        'type' => 'tinyint(1)',
        'default' => 0,
        'comment' => '1 if break time can be booked by others, 0 if not'
    ],
    'created_at' => [
        'type' => 'datetime',
    ],
    'updated_at' => [
        'type' => 'datetime',
    ]
];
