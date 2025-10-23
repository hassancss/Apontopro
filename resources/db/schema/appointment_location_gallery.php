<?php
/**
 *
 * Schema definition for 'appointment_location_gallery'
 *
 * Last update: 2020-12-29
 *
 */
$schemas = $schemas ?? [];
$schemas['appointment_location_gallery'] = [
    'id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ],
    'value_id' => [
        'type' => 'int(11) unsigned',
        'foreign_key' => [
            'table' => 'application_option_value',
            'column' => 'value_id',
            'name' => 'FK_APPOINTMENTPRO_LOCATION_GALLERY_VID',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ],
        'index' => [
            'key_name' => 'value_id',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ]
    ],
    'location_id' => [
        'type' => 'int(11) unsigned',
        'foreign_key' => [
            'table' => 'appointment_location',
            'column' => 'location_id',
            'name' => 'FK_APPOINTMENTPRO_LOCATION_GALLERY_LID',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ],
        'index' => [
            'key_name' => 'location_id',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ]
    ],
    'image' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'is_null' => true,
    ],
    'is_delete' => [
        'type' => 'tinyint(1)',
        'default' => '0'
    ],
    'is_active' => [
        'type' => 'tinyint(1)',
        'default' => '1'
    ],
    'created_at' => [
        'type' => 'datetime',
    ],
    'updated_at' => [
        'type' => 'datetime',
    ]
];
