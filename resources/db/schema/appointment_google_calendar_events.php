<?php
/**
 * Schema for appointment_google_calendar_events table
 */
$schemas = $schemas ?? [];
$schemas['appointment_google_calendar_events'] = [
    'id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true
    ],
    'appointment_id' => [
        'type' => 'int(11)'
    ],
    'provider_id' => [
        'type' => 'int(11)'
    ],
     'event_id' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci'
     ],
    'g_calendar_id' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci'
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
