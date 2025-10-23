<?php

use Siberian\Feature;

try {
    $module = (new Installer_Model_Installer_Module())
        ->prepare('Appointmentpro');

    Feature::installCronjob(
        __('AppointmentPro Reminder'),
        'Appointmentpro_Model_Cron::reminderJob',
        -1,
        -1,
        -1,
        -1,
        -1,
        true,
        100,
        true,
        $module->getId()
    );
 
    Feature::installCronjob(
        __('AppointmentPro Approval Reminder'),
        'Appointmentpro_Model_Cron::approvalJob',
        -1,
        -1,
        -1,
        -1,
        -1,
        true,
        100,
        true,
        $module->getId()
    );

} catch (\Exception $e) {
   
}