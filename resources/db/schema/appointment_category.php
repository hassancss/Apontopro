<?php
/**
 * Schema for appointment_categories table
 */
$schemas = $schemas ?? [];
$schemas['appointment_category'] = [
    'category_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true
    ],
    'name' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci'
    ],
    'value_id' => [
        'type' => 'int(11) unsigned',
        'index' => [
            'key_name' => 'appointment_category_value_id',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'category_for' => [
        'type' => 'tinyint(2) unsigned',
        'default' => 1
    ],
    'image' => [
        'type' => 'text',
        'is_null' => true,
    ],
    'is_active' => [
        'type' => 'int(11) unsigned',
        'default' => '1',
    ],
    'top_category' => [
        'type' => 'int(11) unsigned',
        'default' => '0',
    ],
    'position' => [
        'type' => 'int(11) unsigned',
        'default' => '0',
    ],
    'is_delete' => [
        'type' => 'tinyint(2) unsigned',
        'default' => 0
    ],
    'created_at' => [
        'type' => 'datetime',
    ],
    'updated_at' => [
        'type' => 'datetime',
    ]
];
