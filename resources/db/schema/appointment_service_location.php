<?php
/**
 * Schema for appointment_service_locations table
 */
$schemas = $schemas ?? [];
$schemas['appointment_service_location'] = [
    'service_location_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true
    ],
    'service_id' => [
        'type' => 'int(11) unsigned'
    ],
    'location_id' => [
        'type' => 'int(11) unsigned'
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
