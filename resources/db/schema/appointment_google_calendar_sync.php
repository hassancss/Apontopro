<?php
/**
 * Schema for appointment_google_calendar_sync table
 */
$schemas = $schemas ?? [];
$schemas['appointment_google_calendar_sync'] = [
    'id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true
    ],
    'value_id' => [
        'type' => 'int(11)'
    ],
     'customer_id' => [
        'type' => 'int(11)'
     ],
    'provider_id' => [
        'type' => 'int(11)'
    ],
    'google_refresh_token' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci'
    ],
    'google_calendar_id' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci'
    ],
    'google_calendar_name' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci'
    ],
    'status' => [
        'type' => 'tinyint(4)',
        'default'=>1
    ],
    'created_at' => [
        'type' => 'datetime'
    ],
    'updated_at' => [
        'type' => 'datetime'
    ]
];
