<?php

class Appointmentpro_Model_Cron extends Core_Model_Default
{

    /**
     * Notification for approval
     */
    public function approvalJob()
    {

        $params = [
            "type" => 'upcoming',
            "service_type" => 'all',
            "status" => [9], //Pending Approval,
            "approval_reminder_email" => 0
        ];

        $bookings = (new Appointmentpro_Model_Booking())
            ->findDataForCronJob($params);

        $bookingJson = [];
        foreach ($bookings as $booking) {
            $booking = $booking->getData();
            $booking['is_class'] = (bool)$booking['is_it_class'];

            $timeDiff = (new Appointmentpro_Model_Cron())->calculate_time_span(date('Y-m-d H:i:s'), $booking['created_at']);
            $booking['timeDiff'] = $timeDiff;

            if ($booking['timeDiff']['mins'] >= 30) { // after 30 Min

                $subject = p__('appointmentpro', 'Action Reminder: Approval Pending #%s - %s', $booking['appointment_id'], $booking['app_name']);
                $message = p__('appointmentpro', 'This email is a reminder that booking #%s approval has been in pending, Please action immediately.', $booking['appointment_id']);

                $param = [];
                $param['sender_email'] = $booking['location_email'];
                $param['email'] = $booking['owner_email'];
                $param['name'] = '';
                $param['app_name'] = $booking['app_name'];
                $param['app_icon'] = $booking['app_icon'];
                $param['subject'] = $subject;
                $param['message'] = $message;

                (new Appointmentpro_Model_Cron())->_sendCustomerEmail($param); // Send to customer

                /*Customer Push Notification*/
                $providers = (new Appointmentpro_Model_Provider())->getMobileProvider($booking['location_id'],  $booking['app_id']);

                foreach ($providers as $key => $provider) {
                    $toSend = false;
                    if ($provider['user_role'] == 'manager') {
                        $toSend = true;
                    }
                    if ($provider['user_role'] == 'provider' && $provider['provider_id'] == $booking['provider_id']) {
                        $toSend = true;
                    }

                    if ($toSend) {
                        $param = [];
                        $param['app_id'] = $booking['app_id'];
                        $param['value_id'] = $booking['value_id'];
                        $param['title'] = $subject;
                        $param['text'] = $message;
                        $param['receiver_id'] = $provider['customer_id'];

                        $current_option = new Application_Model_Option_Value();
                        $current_option->find($booking['value_id']);
                        if ($current_option->getId() and $current_option->getApplication()) {
                            $param['application']  = $current_option->getApplication();
                        } else {
                            $param['application'] = "";
                        }

                        (new Appointmentpro_Model_Push())->sendv2($param);
                    }
                }
                /*End Push notifications*/

                $orderModel = (new Appointmentpro_Model_Booking())
                    ->find(['appointment_id' => $booking['appointment_id']])
                    ->setApprovalReminderEmail(1)
                    ->save();
                $bookingJson[] = $booking;
            }
        }

        return true;
    }


    /**
     *Reminder Job screen
     */
    public function reminderJob()
    {

        $params = [
            "type" => 'upcoming',
            "service_type" => 'all',
            "status" => [3], //Accepted,
            "reminder_email" => 0
        ];

        $bookings = (new Appointmentpro_Model_Booking())
            ->findDataForCronJob($params);
        $temp = [];
        // $temp['bookings'][] = $bookings;
        $bookingJson = [];
        foreach ($bookings as $booking) {
            $booking = $booking->getData();
            $temp['item'][] = $booking;
            $booking['is_class'] = (bool)$booking['is_it_class'];
            $customer_language = $booking['customer_language'];
            // Load another locale
            if (!empty($customer_language)) {
                Core_Model_Language::setCurrentLanguage($customer_language);
                Core_Model_Translator::loadDefaultsAndUser($customer_language);
            } else {
                Core_Model_Language::setCurrentLanguage('en');
                Core_Model_Translator::loadDefaultsAndUser('en');
            }

            ($booking['time_format'] == 1) ? $timeFormat = '' : $timeFormat = 'A';
            ($booking['date_format'] == 1) ? $dateFormat = 'm/d/y' : $dateFormat = 'd/m/y';

            if ($booking['is_class']) {
                $booking['booking_date'] = date($dateFormat, $booking['appointment_date']);
                $booking['start_time'] = $booking['class_time'];
                $booking['end_time'] = $booking['class_time'];
                $booking['full_appointment_date'] = date($dateFormat, $booking['appointment_date']) . ' ' . $booking['class_time'];
            } else {
                $booking['booking_date'] = date($dateFormat, $booking['appointment_date']);
                $booking['start_time'] = Appointmentpro_Model_Utils::timestampTotime($booking['appointment_time'], $timeFormat);
                $booking['end_time'] = Appointmentpro_Model_Utils::timestampTotime($booking['appointment_end_time'], $timeFormat);
                $booking['full_appointment_date'] = date($dateFormat, $booking['appointment_date']) . ' ' . $booking['start_time'];
            }
            $booking['full_appointment_date'] = trim($booking['full_appointment_date']);

            if ((int)$booking['date_format']) {
                if ($booking['time_format'] == 0) {
                    // 12 hours
                    $timeDiff = (new Appointmentpro_Model_Cron())->calculate_time_span_m_d_y_12($booking['full_appointment_date']);
                } else {
                    //24 hours
                    $timeDiff = (new Appointmentpro_Model_Cron())->calculate_time_span($booking['full_appointment_date'], date('Y-m-d H:i:s'));
                }
            } else {
                $timeDiff = (new Appointmentpro_Model_Cron())->calculate_time_span_d_m_y($booking['full_appointment_date']);
            }

            $booking['timeDiff'] = $timeDiff;
            if ($timeDiff['months'] !== 0 && ($timeDiff['day'] >= 7 || $timeDiff['day'] < 0) && $timeDiff['hours'] < 0) continue;

            $daysMin = $timeDiff['day'] * 1440;
            $booking['totalMinRemaining'] = $daysMin + ($timeDiff['hours'] * 60) + $timeDiff['mins'];
            $booking['email_notification'] = empty($booking['email_notification']) ? 1 : (int)$booking['email_notification'];
            $booking['reminder_time'] = empty($booking['reminder_time']) ? 120 : (int)$booking['reminder_time'];
            if (($booking['reminder_time'] >= $booking['totalMinRemaining']) && $booking['is_reminder_sent'] != 1) {                
                $temp['found'][] = $booking['appointment_id'];
                $count++;
                $update=(new Appointmentpro_Model_Booking())->reminderSent($booking['appointment_id']);                          
                $subject = p__('appointmentpro', 'Booking Reminder - %s', $booking['app_name']);
                $message = p__('appointmentpro', 'This is reminder that you have a %s booking at %s on %s at %s.', '<b>' . $booking['service_name'] . '</b>', '<b>' . $booking['location_name'] . '</b>', '<b>' . $booking['booking_date'] . '</b>', '<b>' . $booking['start_time'] . '</b>');

                $param = [];
                $param['sender_email'] = $booking['owner_email'];
                $param['email'] = $booking['buyer_email'];
                $param['name'] = $booking['buyer_name'];
                $param['app_name'] = $booking['app_name'];
                $param['app_icon'] = $booking['app_icon'];
                $param['subject'] = $subject;
                $param['message'] = $message;

                if ($booking['email_notification'] == 1 && $update==1) {                    
                   (new Appointmentpro_Model_Cron())->_sendCustomerEmail($param); // Send to customer
                }

                if ($booking['push_notification'] == 1) {
                    $param = [];
                    $subject = p__('appointmentpro', 'Reminder - %s', $booking['app_name']);
                    $message = p__('appointmentpro', 'This is reminder that you have a %s booking at %s on %s at %s.', '' . $booking['service_name'] . '', '' . $booking['location_name'] . '', '' . $booking['booking_date'] . '', '' . $booking['start_time'] . '');

                    $param['app_id'] = $booking['app_id'];
                    $param['value_id'] = $booking['value_id'];
                    $param['title'] = $subject;
                    $param['text'] = $message;
                    $param['receiver_id'] = $booking['customer_id'];
                    (new Appointmentpro_Model_Push())->sendv2($param); // Send to customer
                }

                    
            }else {
                $temp['notfound'][] = $booking['appointment_id'];
            }
        }

        return $temp;
    }


    /**
     * @param $date
     * @return string
     * @throws Exception
     * @throws Zend_Session_Exception
     */
    function calculate_time_span($dateFirst, $dateSecond)
    {
        $seconds = strtotime($dateFirst) - strtotime($dateSecond);
        $time['months'] = floor($seconds / (3600 * 24 * 30));
        $time['day'] = floor($seconds / (3600 * 24));
        $time['hours'] = floor($seconds / 3600);
        $time['mins'] = floor(($seconds - ($time['hours'] * 3600)) / 60);
        $time['secs'] = floor($seconds % 60);


        return $time;
    }

    function calculate_time_span_m_d_y_12($dateFirst)
    {
        // Define the date format
        $format = 'm/d/y h:i A';

        // Create DateTime objects from the provided date strings
        $dateTimeFirst = DateTime::createFromFormat($format, $dateFirst);
        $dateTimeSecond = new DateTime();

        // Check if the dates were parsed correctly
        if (!$dateTimeFirst || !$dateTimeSecond) {
            return "Error: One or both dates could not be parsed.";
        }

        // Calculate the difference in seconds between the two dates
        $seconds = $dateTimeFirst->getTimestamp() - $dateTimeSecond->getTimestamp();

        // Calculate the time span components
        $time['months'] = floor($seconds / (3600 * 24 * 30));  // Approximate months
        $time['days'] = floor($seconds / (3600 * 24));         // Total days
        $time['hours'] = floor($seconds / 3600);               // Total hours
        $time['mins'] = floor(($seconds % 3600) / 60);         // Minutes within the last hour
        $time['secs'] = $seconds % 60;                         // Remaining seconds

        return $time;
    }

    function calculate_time_span_d_m_y($date)
    {
        $time = [];
        $date = trim($date);
        $formats = [
            'd/m/y H:i',
            'd/m/y h:i A'
        ];

        // Get the current date and time
        $currentDate = new DateTime();

        // Loop through each format
        foreach ($formats as $format) {
            // Try to create a DateTime object from the provided date string and current format
            $futureDate = DateTime::createFromFormat($format, $date);

            // Check if the DateTime object creation was successful
            if ($futureDate === false) {
                // Debugging information
                echo "Error: Date string '$date' does not match the format '$format'.\n" . date('m/d/y H:i A');
                continue;
            }

            // Calculate the difference between the current date and the future date
            $interval = $currentDate->diff($futureDate);

            // Get the difference in terms of months, days, hours, minutes, and seconds
            $time['months'] = $interval->m + ($interval->y * 12); // months + years converted to months
            $time['day'] = $interval->d;
            $time['hours'] = $interval->h;
            $time['mins'] = $interval->i;
            $time['secs'] = $interval->s;

            // Calculate remaining minutes
            $seconds = ($interval->days * 24 * 3600) + ($interval->h * 3600) + ($interval->i * 60) + $interval->s;
            $time['remain_min'] = floor($seconds / 60);

            return $time;
        }

        // If none of the formats matched, throw an exception
        throw new Exception("Invalid date format: $date");
    }


    function _sendCustomerEmail($param)
    {
        if (empty($param)) {
            return false;
        }

        $layout = new Siberian_Layout();
        $layout = $layout->loadEmail('appointmentpro', 'appointmentpro_reminder');

        $layout->getPartial('content_email')
            ->setEmail($param['sender_email'])
            ->setMessage($param['message'])
            ->setName($param['name'])
            ->setApp($param['app_name'])->setIcon($param['app_icon']);

        $content = $layout->render();
        $mail = new Siberian_Mail();
        $mail->_is_default_mailer = false;
        $mail->setBodyHtml($content);
        $mail->setFrom($param['sender_email'], $param['app_name']);
        $mail->_sender_name = $param['app_name'];
        $mail->addTo($param['email'], "");        
        $mail->setSubject($param['subject']);

        $mail->send();
    }
}
