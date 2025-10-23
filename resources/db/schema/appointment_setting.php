<?php
/**
 * Schema for appointments table
 */
$schemas = $schemas ?? [];
$schemas['appointment_setting'] = [
    'appointment_setting_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true
    ],
    'booking_type' => [
        'type' => 'varchar(11)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default'=> '1'
    ],
    'design_style' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'cover_image' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'booking_image' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'notify_image' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'owner_email' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'time_format' => [
        'type' => 'int(2) unsigned',
        'default'=> 0
    ],
    'date_format' => [
        'type' => 'int(2) unsigned',
        'default'=> 0
    ],
    'cancel_criteria' => [
        'type' => 'int(2) unsigned',
        'default'=> 0
    ],
    'enable_acceptance_rejection' => [
        'type' => 'int(2) unsigned',
        'default'=> 0
    ],
    'cancel_policy' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci'
    ],
    'confirmation_email' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'reminder_email' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
   'confirmation_sms' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
   ],
   'owner_mobile' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
   ],
    'reminder_sms' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'value_id' => [
        'type' => 'int(11) unsigned',
        'index' => [
            'key_name' => 'appointment_setting_value_id',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'price_hide' => [
        'type' => 'int(2) unsigned',
        'default'=> 0
    ],
    'display_appointments' => [
        'type' => 'tinyint(2)',
        'default'=> 1
    ],
     'display_classes' => [
         'type' => 'tinyint(2)',
        'default'=> 0
     ],
    'display_multi_appointments' => [
         'type' => 'tinyint(2)',
        'default'=> 0
    ],
    'advance_user_management' => [
        'type' => 'tinyint(2)',
        'default'=> 0
    ],
    'number_of_decimals' => [
        'type' => 'int(11)',
        'is_null' => true,
        'default' => 2
    ],
    'decimal_separator' => [
        'type' => 'varchar(11)',
        'is_null' => true,
        'default' => "."
    ],
    'thousand_separator' => [
        'type' => 'varchar(11)',
        'is_null' => true,
        'default' => ","
    ],
    'currency_position' => [
        'type' => 'varchar(100)',
        'is_null' => true,
        'default' => "left"
    ],
    'timezone' => [
        'type' => 'varchar(50)',
        'is_null' => true
    ],    
    'home_slider' => [
        'type' => 'tinyint(11)',
        'default'=> 1
    ],
    'home_provider' => [
        'type' => 'tinyint(11)',
        'default'=> 0
    ],
    'home_category' => [
        'type' => 'tinyint(11)',
        'default'=> 0
    ],
    'cancellation_charges' => [
        'type' => 'double',
        'default'=> 0
    ],
    'online_payment' => [
        'type' => 'tinyint(11)',
        'default'=> 0
    ],
    'offline_payment' => [
        'type' => 'tinyint(11)',
        'default'=> 1
    ],
    'enable_booking' => [
        'type' => 'tinyint(11)',
        'default'=> 1
    ],
    'display_tax' => [
        'type' => 'tinyint(11)',
        'default'=> 1
    ],
    'tax_percentage' => [
        'type' => 'double',
        'default'=> 0
    ],
    'enable_search' => [
        'type' => 'tinyint(11)',
        'default'=> 0
    ],
    'default_location_sorting' => [
        'type' => 'varchar(50)',
        'default'=> 'distance'
    ],
    'list_design' => [
        'type' => 'tinyint(11)',
        'default'=> 0
    ],
    'enable_plc_points' => [
        'type' => 'tinyint(11)',
        'default'=> 0
    ],
    'distance_unit' => [
        'type' => 'varchar(50)',
        'default'=> 'km'
    ],
    'message_at_home' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'is_null' => true
    ],
    'enable_location' => [
        'type' => 'tinyint(11)',
        'default'=> 0
    ],
    'client_id' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'is_null' => true
    ],
    'client_secret' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'is_null' => true
    ],
    'enable_google_calendar' => [
        'type' => 'tinyint(11)',
        'default'=> 0
    ],
    'booking_without_payment' => [
        'type' => 'tinyint(11)',
        'default'=> 0
    ], 
    'created_at' => [
        'type' => 'datetime',
    ],
    'updated_at' => [
        'type' => 'datetime',
    ]
];
