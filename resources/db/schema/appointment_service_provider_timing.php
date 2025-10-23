<?php
/**
 * Schema for appointment_service_provider_timings table
 */
$schemas = $schemas ?? [];
$schemas['appointment_service_provider_timing'] = [
    'id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true
    ],
    'service_provider_id' => [
        'type' => 'int(11) unsigned'
    ],
    'location_id' => [
        'type' => 'int(11) unsigned'
    ],
    'provider_id' => [
        'type' => 'int(11) unsigned'
    ],
    'timing' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci'
    ]

];
