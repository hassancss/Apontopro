<?php
/**
 * Schema for appointment_status table
 */
$schemas = $schemas ?? [];
$schemas['appointment_status'] = [
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
			'name' => 'appointment_status_ibfk_1',
			'on_update' => 'CASCADE',
			'on_delete' => 'CASCADE',
        ],
    ],
	'app_id' => [
		'type' => 'int(11) unsigned',

    ],
	'activation' => [
		'type' => 'varchar(255)',
		'charset' => 'utf8',
		'collation' => 'utf8_unicode_ci',
    ],
	'app_status' => [
        'type' => 'tinyint(2)',
        'default'=>1
    ],


];
