<?php
/**
 * Schema for class_recurring_details table
 */
$schemas = $schemas ?? [];
$schemas['class_recurring_details'] = [
    'id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true
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
    'appointment_id' => [
        'type' => 'int(11) unsigned'
    ],
      'customer_id' => [
        'type' => 'int(11) unsigned'
      ],
      
    'class_id' => [
        'type' => 'int(11) unsigned'
    ],

    'provider_id' => [
        'type' => 'int(11) unsigned'
    ],
    'class_start_date' => [
        'type' => 'bigint(20)'
    ],

    'class_end_time' => [
        'type' => 'bigint(20)'
    ],
    
    'class_time' => [
        'type' => 'bigint(20)'
    ],
   
    'repeat_on' => [
         'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci'
    ],
    'class_description' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci'
    ],

    'number_of_bookings' => [
        'type' => 'tinyint(4)'
    ],
    'next_booking_date_time' => [
         'type' => 'bigint(20)'
    ],
    'created_at' => [
        'type' => 'datetime'
    ],
    'updated_at' => [
        'type' => 'datetime'
    ]
];
