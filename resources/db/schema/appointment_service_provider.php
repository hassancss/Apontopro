<?php
/**
 * Schema for appointment_service_provider table
 */
$schemas = $schemas ?? [];
$schemas['appointment_service_provider'] = [
    'id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true
    ],
    'service_location_id' => [
        'type' => 'int(11) unsigned'
    ],
    'provider_id' => [
        'type' => 'int(11) unsigned'
    ],
    'provider_timing_id' => [
        'type' => 'int(11) unsigned'
    ],
    'multiple_booking_a_slot' => [
        'type' => 'tinyint(1)'
    ],
    'is_enabled' => [
        'type' => 'tinyint(1)',
        'default' => 1,
    ],
    'timing' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci'
    ],
    'is_delete' => [
        'type' => 'int(11) unsigned',
        'default' => '0',
    ],
    'created_at' => [
        'type' => 'datetime',
    ],
    'updated_at' => [
        'type' => 'datetime',
    ]
];
