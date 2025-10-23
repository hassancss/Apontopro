<?php

use Siberian\Exception;
use Siberian\File;
use Siberian\Json;
use Stripe\Stripe;

/**
 * Class Appointmentpro_Mobile_AccountController
 */
class Appointmentpro_Mobile_AccountController extends Application_Controller_Mobile_Default
{

    /**
     * @param $param
     * @return mixed|null
     * @throws Exception
     * @throws Zend_Session_Exception
     */
    public function findCustomerBookingAction()
    {
        $payload = [];

        try {
            $param = $this->getRequest()->getBodyParams();
            $value_id = $this->getRequest()->getParam('value_id');
            $type = $this->getRequest()->getParam('type');
            $service_type = $this->getRequest()->getParam('service_type');
            $service_id = $this->getRequest()->getParam('service_id');

            $customerId = $this->_getCustomerId();
            $params =  [
                "limit" => 10,
                "offset" => $param['offset'],
                "customer_id" => $customerId,
                "type" => $type,
                "status" => ($type == 'history') ? 4 : 'all',
                "service_type" => $service_type
            ];

            $setting = (new Appointmentpro_Model_Settings())->find($value_id, "value_id");
            $settingResult = $setting->getData();
            ($settingResult['time_format'] == 1) ? $timeFormat = '' : $timeFormat = 'A';
            ($settingResult['date_format'] == 1) ? $dateFormat = 'm/d/y' : $dateFormat = 'd/m/y';

            $bookings = (new Appointmentpro_Model_Booking())->findByCustomerId($value_id, $params);
            $bookingsJson = [];
            ($settingResult['time_format'] == 1) ? $timeFormat2 = 'H:i' : $timeFormat2 = 'g:i A';

            $timezone = null;
            if (class_exists("EaziSettings_Model_Settings")) {
                $modelTimezone = (new EaziSettings_Model_Settings())->find(['app_id' => $this->getApplication()->getId()]);
                if ($modelTimezone->getId()) {
                    $timezone = $modelTimezone->getTimezone();
                }
            }

            foreach ($bookings as $key => $booking) {
                if ($service_id && $booking['service_id'] != $service_id) {
                    continue;
                }
                $booking['is_class'] = (bool) $booking['is_it_class'];

                if ($booking['is_class']) {
                    $booking['booking_date'] = date($dateFormat, $booking['appointment_date']);
                    $booking['start_time'] =  $booking['class_time'];
                    $booking['end_time'] =  $booking['class_time'];
                } else {
                    $booking['booking_date'] = date($dateFormat, $booking['appointment_date']);
                    $booking['start_time'] = Appointmentpro_Model_Utils::timestampTotime($booking[''], $timeFormat);
                    $booking['end_time'] = Appointmentpro_Model_Utils::timestampTotime($booking['appointment_appointment_timeend_time'], $timeFormat);
                }

                $booking['total_amount'] = str_replace(',', '', $booking['total_amount']);
                $booking['currency_symbol'] = Core_Model_Language::getCurrencySymbol();
                $booking['amount_with_currency'] = Appointmentpro_Model_Utils::displayPrice($booking['total_amount'], Core_Model_Language::getCurrencySymbol(), $settingResult['number_of_decimals'], $settingResult['decimal_separator'], $settingResult['thousand_separator'], $settingResult['currency_position']);

                $booking['total_amount_with_currency'] = $booking['currency_symbol'] . $booking['total_amount'];

                $booking['payment_status'] = p__('appointmentpro', Appointmentpro_Model_Appointment::getPaymentStatus($booking['payment_status']));
                $booking['status'] = p__('appointmentpro',  Appointmentpro_Model_Appointment::getBookingStatus($booking['status']));
                $created_at = $booking['created_at'];
                $booking['created_at'] = date($dateFormat . ' ' . $timeFormat2, strtotime($booking['created_at']));

                if (class_exists("EaziSettings_Model_Settings")) {
                    $date = new Zend_Date($created_at, 'y-MM-dd HH:mm:ss');
                    if (!empty($timezone)) {
                        $date = $date->setTimezone($timezone);
                        $booking['created_at'] = $date->toString('y-MM-dd HH:mm:ss');
                        $booking['created_at'] = date($dateFormat . ' ' . $timeFormat2, strtotime($booking['created_at']));
                    }
                }

                $bookingsJson[] = $booking;
            }

            $services = (new Appointmentpro_Model_Service())->findByValueId($value_id, [])->toArray();

            // Filter services to only show those visible to user
            $filteredServices = [];
            foreach ($services as $service) {
                if (isset($service['visible_to_user']) && $service['visible_to_user'] == 1) {
                    $filteredServices[] = $service;
                }
            }

            array_unshift($filteredServices, [
                'service_id' => 0,
                'name' => p__('appointmentpro', 'All Services')
            ]);

            $payload = [
                'success' => true,
                'bookings' => $bookingsJson,
                'services' => $filteredServices,
                'value_id' => $value_id,
            ];
        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     * @param $param
     * @return mixed|null
     * @throws Exception
     * @throws Zend_Session_Exception
     */
    public function findBookingByIdAction()
    {
        $payload = [];

        try {
            $param = $this->getRequest()->getBodyParams();
            $value_id = $this->getRequest()->getParam('value_id');
            $booking_id = $this->getRequest()->getParam('booking_id');

            $setting = (new Appointmentpro_Model_Settings())->find($value_id, "value_id");
            $settingResult = $setting->getData();
            ($settingResult['time_format'] == 1) ? $timeFormat = '' : $timeFormat = 'A';
            ($settingResult['time_format'] == 1) ? $timeFormat2 = 'H:i' : $timeFormat2 = 'g:i A';
            ($settingResult['date_format'] == 1) ? $dateFormat = 'm/d/y' : $dateFormat = 'd/m/y';

            $booking = (new Appointmentpro_Model_Booking())->getBookingById($booking_id);

            $booking['is_class'] = (bool) $booking['is_it_class'];
            $booking['schedule_type'] = $booking['schedule_type'] == 'never' ? 'Once' : $booking['schedule_type'];
            $booking['schedule_type'] =  p__('appointmentpro', $booking['schedule_type']);

            if ($booking['is_class']) {
                $booking['booking_date'] = date($dateFormat, $booking['appointment_date']);

                if ($booking['schedule_type'] != 'Once') {
                    $booking['class_end_date'] = date($dateFormat,  strtotime(str_replace('/', '-', $booking['class_end_date'])));
                    $booking['booking_date'] = $booking['booking_date'] . ' - ' . $booking['class_end_date'];
                }

                $booking['start_time'] =  $booking['class_time'];
                $booking['end_time'] =  $booking['class_time'];
                $booking['full_appointment_date'] = date('Y-m-d', $booking['appointment_date']) . ' ' . $booking['class_time'];
            } else {
                $booking['booking_date'] = date($dateFormat, $booking['appointment_date']);
                $booking['start_time'] = Appointmentpro_Model_Utils::timestampTotime($booking['appointment_time'], $timeFormat);
                $booking['end_time'] = Appointmentpro_Model_Utils::timestampTotime($booking['appointment_end_time'], $timeFormat);
                $booking['full_appointment_date'] = date('Y-m-d', $booking['appointment_date']) . ' ' . $booking['start_time'];
            }

            $status = $booking['status'];
            $booking['currency_symbol'] = Core_Model_Language::getCurrencySymbol();
            $booking['is_paid'] = ($booking['payment_status'] == 2) ? true : false;
            $booking['payment_status'] = p__('appointmentpro', Appointmentpro_Model_Appointment::getPaymentStatus($booking['payment_status']));
            $booking['status'] = p__('appointmentpro',  Appointmentpro_Model_Appointment::getBookingStatus($booking['status']));
            $created_at = $booking['created_at'];
            $booking['created_at'] = date($dateFormat . ' ' . $timeFormat2, strtotime($booking['created_at']));

            $booking['price_with_currency'] = Appointmentpro_Model_Utils::displayPrice($booking['price'], Core_Model_Language::getCurrencySymbol(), $settingResult['number_of_decimals'], $settingResult['decimal_separator'], $settingResult['thousand_separator'], $settingResult['currency_position']);
            $booking['service_amount_with_currency'] = Appointmentpro_Model_Utils::displayPrice($booking['service_amount'], Core_Model_Language::getCurrencySymbol(), $settingResult['number_of_decimals'], $settingResult['decimal_separator'], $settingResult['thousand_separator'], $settingResult['currency_position']);

            $booking['total_amount'] = str_replace(',', '', $booking['total_amount']);
            $booking['amount_with_currency'] = Appointmentpro_Model_Utils::displayPrice($booking['total_amount'], Core_Model_Language::getCurrencySymbol(), $settingResult['number_of_decimals'], $settingResult['decimal_separator'], $settingResult['thousand_separator'], $settingResult['currency_position']);


            if ($booking['payment_to'] == 'merchant') {
                $gatewayModal = (new Appointmentpro_Model_Gateways())->find(['gateway_code' => $booking['payment_type'], 'value_id' => $value_id, 'location_id' => $booking['location_id']]);
            } else {
                $gatewayModal = (new Appointmentpro_Model_Gateways())->find(['gateway_code' => $booking['payment_type'], 'value_id' => $value_id]);
            }
            $booking['payment_mode'] =  $gatewayModal->getLableName();
            $booking['instructions'] =  $gatewayModal->getInstructions();

            $booking['is_enable_cancel'] = 0;
            if (in_array($status, [0, 1, 2, 3, 9])) {
                $booking['is_enable_cancel'] = 1;
            }

            if ($booking['is_enable_cancel']) {
                $settingResult['cancel_criteria'] = (int) $settingResult['cancel_criteria'];
                ($settingResult['cancel_criteria'] == 0) ? $booking['is_enable_cancel'] = 0 : $booking['is_enable_cancel'] = 1;

                if ($settingResult['cancel_criteria'] == 1) {
                    $booking['is_enable_cancel'] = 1;
                } else {

                    if (in_array($settingResult['cancel_criteria'], [2, 3, 4, 5, 6, 7])) {
                        $time_count = $this->calculate_time_span($booking['full_appointment_date']);
                        $booking['time_count'] = $time_count;
                        $booking['is_enable_cancel'] = 0;

                        if ($settingResult['cancel_criteria'] == 2) {
                            if (($time_count['months'] >= 0) &&  ($time_count['day'] >= 0)  && ($time_count['hours'] >= 1)) {
                                $booking['is_enable_cancel'] = 1;
                            }
                        }
                        if ($settingResult['cancel_criteria'] == 3) {
                            if (($time_count['months'] >= 0) &&  ($time_count['day'] >= 0)  && ($time_count['hours'] >= 2)) {
                                $booking['is_enable_cancel'] = 1;
                            }
                        }
                        if ($settingResult['cancel_criteria'] == 4) {
                            if (($time_count['months'] >= 0) &&  ($time_count['day'] >= 0)  && ($time_count['hours'] >= 6)) {
                                $booking['is_enable_cancel'] = 1;
                            }
                        }

                        if ($settingResult['cancel_criteria'] == 5) {
                            if (($time_count['months'] >= 0) &&  ($time_count['day'] >= 0)  && ($time_count['hours'] >= 12)) {
                                $booking['is_enable_cancel'] = 1;
                            }
                        }

                        if ($settingResult['cancel_criteria'] == 6) {
                            if (($time_count['months'] >= 0) &&  ($time_count['day'] >= 1)) {
                                $booking['is_enable_cancel'] = 1;
                            }
                        }

                        if ($settingResult['cancel_criteria'] == 6) {
                            if (($time_count['months'] >= 0) &&  ($time_count['day'] >= 7)) {
                                $booking['is_enable_cancel'] = 1;
                            }
                        }
                    }
                }
            }

            $timezone = null;
            if (class_exists("EaziSettings_Model_Settings")) {
                $modelTimezone = (new EaziSettings_Model_Settings())->find(['app_id' => $this->getApplication()->getId()]);
                if ($modelTimezone->getId()) {
                    $timezone = $modelTimezone->getTimezone();
                }

                $date = new Zend_Date($created_at, 'y-MM-dd HH:mm:ss');
                if (!empty($timezone)) {
                    $date = $date->setTimezone($timezone);
                    $booking['created_at'] = $date->toString('y-MM-dd HH:mm:ss');
                    $booking['created_at'] = date($dateFormat . ' ' . $timeFormat2, strtotime($booking['created_at']));
                }
            }

            $payload = [
                'success' => true,
                'booking' => $booking
            ];
        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     * @param $date
     * @return string
     * @throws Exception
     * @throws Zend_Session_Exception
     */
    function calculate_time_span($date)
    {
        $seconds  = strtotime($date) - strtotime(date('Y-m-d H:i:s'));

        $time['months'] = floor($seconds / (3600 * 24 * 30));
        $time['day'] = floor($seconds / (3600 * 24));
        $time['hours'] = floor($seconds / 3600);
        $time['mins'] = floor(($seconds - ($time['hours'] * 3600)) / 60);
        $time['secs'] = floor($seconds % 60);


        return $time;
    }

    /**
     * @param $param
     * @return mixed|null
     * @throws Exception
     * @throws Zend_Session_Exception
     */
    public function customerNotificationSettingsAction()
    {
        $payload = [];

        try {
            $param = $this->getRequest()->getBodyParams();
            $value_id = $this->getRequest()->getParam('value_id');
            $customerId = $this->_getCustomerId();

            $model = (new Appointmentpro_Model_Customer())
                ->find(['customer_id' => $customerId])
                ->setValueId($value_id)
                ->setReminderTime($param['reminder_time'])
                ->setPushNotification($param['push_notification'])
                ->setEmailNotification($param['email_notification'])
                ->save();

            $setting = [
                'reminder_time' => (int) $model->getReminderTime(),
                'email_notification' => (bool) $model->getEmailNotification(),
                'push_notification' =>  (bool) $model->getPushNotification(),
            ];

            $payload = [
                'success' => true,
                'setting' =>  $setting
            ];
        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }


    /**
     * @param $param
     * @return mixed|null
     * @throws Exception
     * @throws Zend_Session_Exception
     */
    public function getNotificationSettingsAction()
    {
        $payload = [];

        try {
            $param = $this->getRequest()->getBodyParams();
            $value_id = $this->getRequest()->getParam('value_id');
            $customerId = $this->_getCustomerId();

            $model = (new Appointmentpro_Model_Customer())
                ->find(['customer_id' => $customerId]);

            if (!$model->getId()) {
                $model->setValueId($value_id)
                    ->setCustomerId($customerId)
                    ->save();
            }

            $setting = [
                'reminder_time' => (int) $model->getReminderTime(),
                'email_notification' => (bool) $model->getEmailNotification(),
                'push_notification' =>  (bool) $model->getPushNotification(),
            ];

            $payload = [
                'success' => true,
                'setting' =>  $setting
            ];
        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }



    /**
     *cancel status
     */
    public function cancelBookingAction()
    {

        try {

            $model = (new Appointmentpro_Model_Booking());
            if ($booking_id = $this->getRequest()->getParam('appointment_id')) {
                $model->find(['appointment_id' => $booking_id]);
                $message = p__('appointmentpro', 'Your booking has been successfully cancelled');
                if (!$model->getAppointmentId()) {
                    $this->getRequest()->addError(p__("appointmentpro",  "This Appointment does not exist."));
                } else {

                    $status = '6';
                    $booking = (new Appointmentpro_Model_Booking())->getBookingById($booking_id);
                    $refundInfo = [];

                    $setting = (new Appointmentpro_Model_Settings())->find($booking['value_id'], "value_id");
                    $settingResult = $setting->getData();
                    $cancellation_charges = (float) $settingResult['cancellation_charges'];
                    $booking['total_amount'] = str_replace(',', '', $booking['total_amount']);
                    $redund_amount =   (((100 - $cancellation_charges) * $booking['total_amount']) / 100);

                    //Manage refund amount
                    if ($status == '6') {

                        //PayPal Refund
                        if ($booking['payment_type'] == 'paypal' && $booking['payment_status'] == 2) {
                            $refundInfo =  $this->refundPaypal($booking, $redund_amount, $cancellation_charges);
                            $txnModel = (new Appointmentpro_Model_Transaction())
                                ->find(['booking_id' => $booking_id])
                                ->setRefundInfo(json_encode($refundInfo));

                            if ($refundInfo['ACK'] == 'Success') {
                                $message = p__('appointmentpro', 'Your booking has been successfully cancelled and Refund amount will be credited to the your account');
                                $txnModel->setStatus(6);
                            } else {
                                $txnModel->setStatus(7);
                            }
                            $txnModel->save();
                        }
                        //PayPal End 

                        //Stripe Refund
                        if ($booking['payment_type'] == 'stripe' && $booking['payment_status'] == 2) {

                            $location_id = $booking['location_id'];
                            if ($booking['payment_to'] == 'admin') {
                                $location_id = 0;
                            }
                            /*check stripe access setting*/
                            $gatewayModel = (new Appointmentpro_Model_Gateways())->find(['gateway_code' => 'stripe', 'value_id' => $booking['value_id'], 'location_id' => $location_id]);

                            /*refund at stripe */
                            Stripe::setApiKey($gatewayModel->getSecretKey());
                            $chargeStripe = \Stripe\Refund::create([
                                'charge' => $booking['transaction_id'],
                                'amount' => (float) $redund_amount * 100
                            ]);
                            $refundInfo = $chargeStripe->jsonSerialize();

                            $txnModel = (new Appointmentpro_Model_Transaction())
                                ->find(['booking_id' => $booking_id])
                                ->setRefundInfo(json_encode($refundInfo));
                            if ($refundInfo['status'] == 'succeeded') {
                                $message = p__('appointmentpro', 'Your booking has been successfully cancelled and Refund amount will be credited to the your account');
                                $txnModel->setStatus(6);
                            } else {
                                $txnModel->setStatus(7);
                            }
                            $txnModel->save();
                        }
                        /*stripe end */
                    }

                    $model->setStatus($status);
                    $model->save();
                }
            }


            $this->_sendEmail($booking_id); // Main Send

            $payload = [
                'success' => true,
                'message' => $message,
                'refundInfo' => $refundInfo
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }



    /**
     * @param Array $throw
     * @return Array|null
     * @throws Exception
     * @throws Zend_Session_Exception
     */
    public function refundPaypal($order, $redund_amount, $cancellation_charges)
    {

        $location_id = $order['location_id'];
        if ($order['payment_to'] == 'admin') {
            $location_id = 0;
        }

        /*check paypal access setting*/
        $gatewayModel = (new Appointmentpro_Model_Gateways())->find(['gateway_code' => 'paypal', 'value_id' => $order['value_id'], 'location_id' => $location_id]);

        if ($gatewayModel->getPaymentMode() == 0) {
            $paypal_api_user = $gatewayModel->getSandboxusername();
            $paypal_api_user_pwd = $gatewayModel->getSandboxpassword();
            $paypal_api_user_signature = $gatewayModel->getSandboxsignature();
            $api_url = "https://api-3t.sandbox.paypal.com/nvp";
        } else {
            $paypal_api_user = $gatewayModel->getUsername();
            $paypal_api_user_pwd = $gatewayModel->getPassword();
            $paypal_api_user_signature = $gatewayModel->getSignature();
            $api_url = "https://api-3t.paypal.com/nvp";
        }

        $additional_info = json_decode($order['additional_info']);
        $params = array(
            'METHOD' => 'RefundTransaction',
            'VERSION' => '124.0',
            'USER' => $paypal_api_user,
            'PWD' => $paypal_api_user_pwd,
            'SIGNATURE' => $paypal_api_user_signature,
            'TRANSACTIONID' => $additional_info->result->PAYMENTINFO_0_TRANSACTIONID,
        );

        if ($cancellation_charges > 0) {
            $params['REFUNDTYPE'] = 'Partial';
            $params['AMT'] = $redund_amount;
        }

        return (new Appointmentpro_Model_Utils())->setCurl($params, $api_url);
    }

    /**
     * @param bool $throw
     * @return mixed|null
     * @throws Exception
     * @throws Zend_Session_Exception
     */
    private function _getCustomerId($throw = true)
    {
        $request = $this->getRequest();
        $session = $this->getSession();
        $customerId = $session->getCustomerId();
        if ($throw && empty($customerId)) {
            throw new Exception(p__('appointmentpro', 'Customer login required!'));
        }
        return $customerId;
    }

    /**
     * @param bool $throw
     * @return mixed|null
     * @throws Exception
     * @throws Zend_Session_Exception
     */
    private function _sendEmail($booking_id)
    {

        $booking = (new Appointmentpro_Model_Booking())->getBookingById($booking_id);
        $setting = (new Appointmentpro_Model_Settings())->find($booking['value_id'], "value_id");
        $settingResult = $setting->getData();
        ($settingResult['time_format'] == 1) ? $timeFormat = '' : $timeFormat = 'A';
        ($settingResult['date_format'] == 1) ? $dateFormat = 'm/d/y' : $dateFormat = 'd/m/y';

        $booking['is_class'] = (bool) $booking['is_it_class'];
        $booking['is_class'] = (bool) $booking['is_it_class'];
        $value_id = $booking['value_id'];
        if ($booking['is_class']) {
            $booking['booking_date'] = date($dateFormat, $booking['appointment_date']);
            $booking['start_time'] =  $booking['class_time'];
            $booking['end_time'] =  $booking['class_time'];
            $booking['full_appointment_date'] = date('Y-m-d', $booking['appointment_date']) . ' ' . $booking['class_time'];
        } else {
            $booking['booking_date'] = date($dateFormat, $booking['appointment_date']);
            $booking['start_time'] = Appointmentpro_Model_Utils::timestampTotime($booking['appointment_time'], $timeFormat);
            $booking['end_time'] = Appointmentpro_Model_Utils::timestampTotime($booking['appointment_end_time'], $timeFormat);
            $booking['full_appointment_date'] = date('Y-m-d', $booking['appointment_date']) . ' ' . $booking['appointment_time'];
        }

        $status = $booking['status'];
        $booking['currency_symbol'] = Core_Model_Language::getCurrencySymbol();
        $booking['total_amount'] = str_replace(',', '', $booking['total_amount']);
        $booking['amount_with_currency'] = Core_Model_Language::getCurrencySymbol() . $booking['total_amount'];
        $booking['service_amount_with_currency'] = Core_Model_Language::getCurrencySymbol() . $booking['service_amount'];
        $booking['is_paid'] = ($booking['payment_status'] == 2) ? true : false;
        $booking['payment_status'] = p__('appointmentpro', Appointmentpro_Model_Appointment::getPaymentStatus($booking['payment_status']));
        $booking['status'] = p__('appointmentpro',  Appointmentpro_Model_Appointment::getBookingStatus($booking['status']));
        $booking['created_at'] = date($dateFormat . ' ' . $timeFormat2, strtotime($booking['created_at']));
        $booking['price_with_currency'] = Core_Model_Language::getCurrencySymbol() . $booking['price'];

        if ($booking['payment_to'] == 'merchant') {
            $gatewayModal = (new Appointmentpro_Model_Gateways())->find(['gateway_code' => $booking['payment_type'], 'value_id' => $value_id, 'location_id' => $booking['location_id']]);
        } else {
            $gatewayModal = (new Appointmentpro_Model_Gateways())->find(['gateway_code' => $booking['payment_type'], 'value_id' => $value_id]);
        }
        $booking['payment_mode'] =  $gatewayModal->getLableName();



        /*Email Customer*/
        if (in_array($status, [2, 9])) {
            $subject = p__('appointmentpro', 'Booking Confirmation - %s', $this->getApplication()->getName());
            $message = p__('appointmentpro', 'Thank you for booking with %s, Please see the important details below regarding your information', $booking['location_name']);
        }

        if (in_array($status, [3, 4, 5, 6, 8])) {
            $subject = p__('appointmentpro', 'Booking %s - %s', $booking['status'],  $this->getApplication()->getName());
            $message = p__('appointmentpro', 'Your booking has been %s, Please see the important details below regarding your information', $booking['status']);
        }
        $param = [];
        $param['booking'] = $booking;
        $param['sender_email'] = $booking['location_email'];
        $param['email'] = $booking['buyer_email'];
        $param['subject'] =  $subject;
        $param['message'] =  $message;

        $this->_sendCustomerEmail($param);

        /*Email Vendor*/
        $message = '';
        if (in_array($status, [6])) {
            $subject = p__('appointmentpro', 'Booking %s - %s', $booking['status'],  $this->getApplication()->getName());
        }

        $booking['buyer_name'] = '';
        $param = [];
        $param['booking'] = $booking;
        $param['sender_email'] = $settingResult['owner_email'];
        $param['email'] = $booking['location_email'];
        $param['subject'] =  $subject;
        $param['message'] =  $message;

        $this->_sendVendorEmail($param);

        return true;
    }

    /**
     * @param bool $throw
     * @return mixed|null
     * @throws Exception
     * @throws Zend_Session_Exception
     */
    private function _sendCustomerEmail($param)
    {
        if (empty($param)) {
            return false;
        }

        $config = Zend_Controller_Front::getInstance()->getParam('bootstrap');
        $sender = $config->getOption('sendermail');
        $layout = $this->getLayout()->loadEmail('appointmentpro', 'appointmentpro_booking');

        $layout->getPartial('content_email')
            ->setEmail($param['sender_email'])
            ->setMessage($param['message'])
            ->setBooking($param['booking'])
            ->setApp($this->getApplication()->getName())->setIcon($this->getApplication()->getIcon());

        $content = $layout->render();
        $mail = new Siberian_Mail();
        $mail->_is_default_mailer = false;
        $mail->setBodyHtml($content);
        $mail->setFrom($param['sender_email'], $this->getApplication()->getName());
        $mail->_sender_name = $this->getApplication()->getName();
        $mail->addTo($param['email'], "");
        $mail->setSubject($param['subject']);

        $mail->send();
    }

    /**
     * @param bool $throw
     * @return mixed|null
     * @throws Exception
     * @throws Zend_Session_Exception
     */
    private function _sendVendorEmail($param)
    {
        if (empty($param)) {
            return false;
        }

        $config = Zend_Controller_Front::getInstance()->getParam('bootstrap');
        $sender = $config->getOption('sendermail');
        $layout = $this->getLayout()->loadEmail('appointmentpro', 'appointmentpro_booking');

        $layout->getPartial('content_email')
            ->setEmail($param['sender_email'])
            ->setMessage($param['message'])
            ->setBooking($param['booking'])
            ->setApp($this->getApplication()->getName())->setIcon($this->getApplication()->getIcon());

        $content = $layout->render();
        $mail = new Siberian_Mail();
        $mail->_is_default_mailer = false;
        $mail->setBodyHtml($content);
        $mail->setFrom($param['sender_email'], $this->getApplication()->getName());
        $mail->_sender_name = $this->getApplication()->getName();
        $mail->addTo($param['email'], "");
        $mail->setSubject($param['subject']);

        $mail->send();
    }


    public function switchAccountAction()
    {
        $payload = [];

        try {
            $value_id = $this->getRequest()->getParam('value_id');
            $model = (new Appointmentpro_Model_Provider());
            if ($providerId = $this->getRequest()->getParam('providerId')) {
                $model->find($providerId);
                if (!$model->getProviderId()) {
                    $payload = [
                        "error" => true,
                        "message" => p__('appointmentpro', 'Invalid customer Id')
                    ];
                } else {

                    if ($model->getIsProviderLayout() == 1) {
                        $model->setIsProviderLayout(0);
                    } else {
                        $model->setIsProviderLayout(1);
                    }
                    $model->save();

                    $payload = [
                        'success' => true
                    ];
                }
            }
        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }
}
