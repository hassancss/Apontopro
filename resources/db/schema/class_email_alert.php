<?php
/**
 * Schema for class_email_alert table
 */
$schemas = $schemas ?? [];
$schemas['class_email_alert'] = [
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

    ],
    'send_at' => [
        'type' => 'datetime'
    ],
    'class_start_date' => [
        'type' => 'datetime'
    ],
    'class_end_date' => [
        'type' => 'datetime'
    ],
    'email_text' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci'
    ],
    'status' => [
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








