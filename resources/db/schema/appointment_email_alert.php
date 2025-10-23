<?php
/**
 * Schema for appointment_email_alert table
 */
$schemas = $schemas ?? [];
$schemas['appointment_email_alert'] = [
    'id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true
    ],
    'value_id' => [
        'type' => 'int(11) unsigned'
    ],
    'app_id' => [
        'type' => 'int(11) unsigned'
    ],
    'appointment_id' => [
        'type' => 'int(11) unsigned',
        'foreign_key' => [
            'table' => 'appointment',
            'column' => 'appointment_id',
            'name' => 'appointment_id',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ],
        'index' => [
            'key_name' => 'appointment_id',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'send_at' => [
        'type' => 'datetime'
    ],
    'email_text' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci'
    ],
    'sms_text' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
         'is_null' => true,
    ],
    
    'status' => [
        'type' => "enum('queued','delivered','sending','failed')",
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'queued',
    ],
    'sms_status' => [
        'type' => "enum('queued','delivered','sending','failed')",
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'queued',
    ],
    
    'error_text' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'is_null' => true,
    ]
];








