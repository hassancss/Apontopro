<?php
/**
 *
 * Schema definition for 'appointment_label_names'
 *
 * Last update: 2021-03-12
 *
 */
$schemas = $schemas ?? [];
$schemas['appointment_label_names'] = [
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
            'name' => 'FK_APPOINTMENTPRO_LABEL_NAMES_VID',
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
    'original_label_name' => [
        'type' => 'varchar(255)',
    ],
    'label_name' => [
        'type' => 'varchar(255)',
    ],
    'label_key' => [
        'type' => 'varchar(255)',
    ],
    'created_at' => [
        'type' => 'datetime',
    ],
    'updated_at' => [
        'type' => 'datetime',
    ],
];
