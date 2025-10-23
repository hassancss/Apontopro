<?php

/**
 * Schema for appointment_providers table
 */
$schemas = $schemas ?? [];
$schemas['appointment_provider'] = [
    'provider_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true
    ],
    'name' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci'
    ],
    'email' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci'
    ],
    'mobile_number' => [
        'type' => 'varchar(13)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci'
    ],
    'image' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci'
    ],
    'calendar_url' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci'
    ],
    'calendar_header_bg' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'is_null' => true,
        'default' => '#0096bf'
    ],
    'calendar_header_color' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'is_null' => true,
        'default' => '#ffffff'
    ],
    'calendar_body_bg' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'is_null' => true,
        'default' => '#6abdd4'
    ],
    'calendar_body_color' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'is_null' => true,
        'default' => '#ffffff'
    ],
    'designation' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'is_null' => true
    ],
    'description' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci'
    ],
    'value_id' => [
        'type' => 'int(11) unsigned',
        'index' => [
            'key_name' => 'appointment_provider_value_id',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'is_active' => [
        'type' => 'int(11) unsigned',
        'default' => '1',
    ],
    'location_id' => [
        'type' => 'int(11) unsigned',
        'default' => '0',
    ],
    'is_delete' => [
        'type' => 'tinyint(2) unsigned',
        'default' => 0
    ],
    'position' => [
        'type' => 'int(11) unsigned',
        'default' => "1",
        'is_null' => true,
    ],
    'is_popular' => [
        'type' => 'int(11) unsigned',
        'default' => "0",
        'is_null' => true,
    ],
    'is_mobile_user' => [
        'type' => 'int(11) unsigned',
        'default' => 0,
    ],
    'is_provider_layout' => [
        'type' => 'int(11) unsigned',
        'default' => 0,
    ],
    'user_role' => [
        'type' => 'varchar(50)',
        'default' => 'manager',
    ],
    'google_refresh_token' => [
        'type' => 'text',
        'is_null' => true
    ],
    'created_at' => [
        'type' => 'datetime',
    ],
    'updated_at' => [
        'type' => 'datetime',
    ]

];
