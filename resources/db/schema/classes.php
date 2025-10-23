<?php
/**
 * Schema for classes table
 */
$schemas = $schemas ?? [];
$schemas['classes'] = [
    'class_booking_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true
    ],
    'service_provider_id' => [
        'type' => 'int(11) unsigned'
    ],
    'class_provider_id' => [
        'type' => 'int(11) unsigned'
    ],
    'customer_id' => [
        'type' => 'int(11) unsigned'
    ],
    'class_time' => [
        'type' => 'bigint(20)'
    ],
    'class_end_time' => [
        'type' => 'bigint(20)'
    ],
    'class_date' => [
        'type' => 'bigint(20)'
    ],
    'is_recurrent_class' => [
        'type' => 'tinyint(4)'
    ],
    'recurrent_class_type' => [
        'type' => 'tinyint(4)'
    ],
    'status' => [
        'type' => 'tinyint(4)'
    ],
    'notes' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci'
    ],
    'comments' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci'
    ],
    'value_id' => [
        'type' => 'int(11) unsigned',
        'index' => [
            'key_name' => 'class_value_id',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'additional_info' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci'
    ],
    'is_add_plc_points' => [
        'type' => 'tinyint(4)'
    ],
    'created_at' => [
        'type' => 'datetime'
    ],
    'updated_at' => [
        'type' => 'datetime'
    ]
];
