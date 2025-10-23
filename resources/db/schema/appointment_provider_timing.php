<?php
/**
 * Schema for appointment_provider_timing table
 */
$schemas = $schemas ?? [];
$schemas['appointment_provider_timing'] = [
    'id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true
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
