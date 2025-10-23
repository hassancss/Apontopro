<?php
/**
 * Schema for appointment_activation_details table
 */
$schemas = $schemas ?? [];
$schemas['appointment_activation_details'] = [
    'id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true
    ],
    'activation_key' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'is_null' => true,
    ],
    'license_limit' => [
        'type' => 'varchar(60)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'is_null' => true,
    ],
    'activations_left' => [
        'type' => 'varchar(60)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'is_null' => true,
    ],
    'site_count' => [
        'type' => 'varchar(60)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'is_null' => true,
    ],
    'created_at' => [
        'type' => 'datetime'
    ],
    'updated_at' => [
        'type' => 'datetime'
    ]
];
