<?php
/**
 * Schema for appointment_user_management_location table
 */
$schemas = $schemas ?? [];
$schemas['appointment_user_management_location'] = [
    'id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true
    ],
    'customer_id' => [
        'type' => 'int(11) unsigned',
    ],
    'locations' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci'
    ],
];
