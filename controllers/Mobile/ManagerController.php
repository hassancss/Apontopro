<?php

use Siberian\Exception;
use Siberian\File;
use Siberian\Json;
use Stripe\Stripe;;

/**
 * Class Appointmentpro_Mobile_ViewController
 */
class Appointmentpro_Mobile_ManagerController extends Application_Controller_Mobile_Default
{

    public function findBookingsAction()
    {
        $payload = [];

        try {
            $value_id = $this->getRequest()->getParam('value_id');
            $param = $this->getRequest()->getBodyParams();
            $application = $this->getApplication();

            $settingModel = (new Appointmentpro_Model_Settings())->find(['value_id' => $value_id]);
            $settingsData = $settingModel->getData();
            $time_format = (string) $settingsData['time_format'] == 0 ? 'g:i A' : 'H:i';
            $date_format = (string) $settingsData['date_format'] == 0 ? 'd/m/y' : 'm/d/y';
            $customerId = $this->_getCustomerId(false);

            $customer = (new Customer_Model_Customer())->find($customerId);
            $providerModel = (new Appointmentpro_Model_Provider())->find(['email' => $customer->getEmail(), 'value_id' => $value_id]);

            if ($providerModel && $providerModel->getProviderId()) {
                if ($providerModel->getIsMobileUser()) {
                    $providerInfo = [
                        'providerId' => (int) $providerModel->getProviderId(),
                        'is_mobile_user' => (bool) $providerModel->getIsMobileUser(),
                        'user_role' =>  $providerModel->getUserRole(),
                        'is_provider_layout' => (bool) $providerModel->getIsProviderLayout(),
                        'location_id' => (int) $providerModel->getLocationId()
                    ];
                }
            }

            $bookingParams = [];
            $bookingParams['user_role'] = $providerInfo['user_role'];
            $bookingParams['location_id'] = $providerInfo['location_id'];
            if ($bookingParams['user_role'] == 'provider') {
                $bookingParams['provider_id'] = $providerInfo['providerId'];
            }
            $bookingParams['service_type'] = $param['service_type'];
            $bookingParams['limit'] = 10;
            $bookingParams['offset'] = $param["offset"];
            $bookingParams['type'] = $param["tab"];
            $bookingParams['search'] = $param["search"];

            if ($param['tab'] == 'completed') {
                $bookingParams['status'] = 4;
            }

            $bookings = (new Appointmentpro_Model_Booking())->findByValueIdForApp($value_id, $bookingParams);
            ($settingsData['time_format'] == 1) ? $timeFormat = '' : $timeFormat = 'A';
            ($settingsData['date_format'] == 1) ? $dateFormat = 'm/d/y' : $dateFormat = 'd/m/y';

            $bookingJson = [];
            foreach ($bookings as $booking) {
                $data = $booking->getData();
                $data['customer'] = $data['firstname'] . ' ' . $data['lastname'];
                $data['booking_date'] = date($dateFormat, $data['appointment_date']);
                $data['start_time'] = Appointmentpro_Model_Utils::timestampTotime($data['appointment_time'], $timeFormat);
                $data['end_time'] = Appointmentpro_Model_Utils::timestampTotime($data['appointment_end_time'], $timeFormat);
                $data['currency_symbol'] = Core_Model_Language::getCurrencySymbol();
                $data['amount_with_currency'] = Appointmentpro_Model_Utils::displayPrice($data['total_amount'], Core_Model_Language::getCurrencySymbol(), $settingsData['number_of_decimals'], $settingsData['decimal_separator'], $settingsData['thousand_separator'], $settingsData['currency_position']);

                $data['text_color'] = Appointmentpro_Model_Appointment::getBookingTextcolor($data['status']);
                $data['payment_text_color'] = Appointmentpro_Model_Appointment::getPaymentTextcolor($data['payment_status']);

                $data['is_completed_hide'] = false;
                $data['is_rejected_hide'] = false;
                $data['is_accepted_hide'] = false;
                $data['is_rejected_accepted_action'] = false;
                $data['is_it_class'] = (bool) $data['is_it_class'];

                if (in_array($data['status'], ['4', '6', '5'])) {
                    $data['is_completed_hide'] = true;
                }
                if ($data['status'] == 8) {
                    $data['is_rejected_hide'] = true;
                }
                if ($data['status'] == 3) {
                    $data['is_accepted_hide'] = true;
                }
                if ($data['status'] < 3) {
                    $data['is_rejected_accepted_action'] = true;
                }

                $data['is_payment_hide'] = false;
                if (Appointmentpro_Model_Appointment::getPaymentStatus($data['payment_status']) !=  'Pending') {
                    $data['is_payment_hide'] = true;
                }

                $data['payment_status'] = p__('appointmentpro', Appointmentpro_Model_Appointment::getPaymentStatus($data['payment_status']));
                $data['status'] = p__('appointmentpro',  Appointmentpro_Model_Appointment::getBookingStatus($data['status']));

                $data['payment_type'] = ($data['payment_type'] == 'cod') ?  p__("appointmentpro",  "Cash") : ucfirst($data['payment_type']);
                $bookingJson[] = $data;
            }

            $payload = [
                'success' => true,
                'page_title' => $this->getCurrentOptionValue()->getTabbarName(),
                'bookings' => $bookingJson
            ];
        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }


    public function findCustomersAction()
    {
        $payload = [];

        try {
            $value_id = $this->getRequest()->getParam('value_id');
            $param = $this->getRequest()->getBodyParams();
            $application = $this->getApplication();

            $settingModel = (new Appointmentpro_Model_Settings())->find(['value_id' => $value_id]);
            $settingsData = $settingModel->getData();
            $customerId = $this->_getCustomerId(false);

            $customer = (new Customer_Model_Customer())->find($customerId);
            $providerModel = (new Appointmentpro_Model_Provider())->find(['email' => $customer->getEmail(), 'value_id' => $value_id]);

            if ($providerModel && $providerModel->getProviderId()) {
                if ($providerModel->getIsMobileUser()) {
                    $providerInfo = [
                        'providerId' => (int) $providerModel->getProviderId(),
                        'is_mobile_user' => (bool) $providerModel->getIsMobileUser(),
                        'user_role' =>  $providerModel->getUserRole(),
                        'is_provider_layout' => (bool) $providerModel->getIsProviderLayout(),
                        'location_id' => (int) $providerModel->getLocationId()
                    ];
                }
            }

            $customerParams = [];
            $customerParams['limit'] = 5;
            $customerParams['offset'] = $param["offset"];
            $customerParams['search'] = $param["serach"];

            $customersJson = [];
            $customers = (new Appointmentpro_Model_Customer())->findByLocationId($providerInfo['location_id'], $customerParams);

            foreach ($customers as $customer) {
                $data = $customer->getData();
                $customersJson[] = $data;
            }

            $payload = [
                'success' => true,
                'customers' => $customersJson
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
     *booking status
     */
    public function updateStatusAction()
    {

        try {

            $model = (new Appointmentpro_Model_Booking());
            if ($booking_id = $this->getRequest()->getParam('booking_id')) {
                $model->find(['appointment_id' => $booking_id]);
                if (!$model->getAppointmentId()) {
                    $this->getRequest()->addError(p__("appointmentpro",  "This Appointment does not exist."));
                } else {
                    $message = p__('appointmentpro', 'Booking status updated successfully');
                    $status = $this->getRequest()->getParam('bstatus');
                    $booking = (new Appointmentpro_Model_Booking())->getBookingById($booking_id);
                    $refundInfo = [];

                    $setting = (new Appointmentpro_Model_Settings())->find($booking['value_id'], "value_id");
                    $settingResult = $setting->getData();

                    if ($status == 4) {
                        $booking['start_time'] = Appointmentpro_Model_Utils::timestampTotime($booking['appointment_time']);
                        $booking['full_appointment_date'] = date('Y-m-d', $booking['appointment_date']) . ' ' . $booking['start_time'];

                        if (strtotime(date('Y-m-d H:i:s')) < strtotime($booking['full_appointment_date'])) {
                            throw new Exception(p__("appointmentpro",  "Sorry, Not allow to complete a booking before %s", $booking['full_appointment_date']));
                        }
                    }


                    //Manage refund amount
                    if ($status == '6' || $status == 'delete' || $status == '8') {
                        $cancellation_charges = (float) $settingResult['cancellation_charges'];
                        $redund_amount =   (((100 - $cancellation_charges) * $booking['total_amount']) / 100);

                        //PayPal Refund
                        if ($booking['payment_type'] == 'paypal' && $booking['payment_status'] == 2) {
                            $refundInfo =  $this->refundPaypal($booking, $redund_amount, $cancellation_charges);
                            $txnModel = (new Appointmentpro_Model_Transaction())
                                ->find(['booking_id' => $booking_id])
                                ->setRefundInfo(json_encode($refundInfo));

                            if ($refundInfo['ACK'] == 'Success') {
                                $message = p__('appointmentpro', 'Booking has been successfully cancelled and Refund amount will be credited to the user account');
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
                            ]);
                            $refundInfo = $chargeStripe->jsonSerialize();

                            $txnModel = (new Appointmentpro_Model_Transaction())
                                ->find(['booking_id' => $booking_id])
                                ->setRefundInfo(json_encode($refundInfo));
                            if ($refundInfo['status'] == 'succeeded') {
                                $message = p__('appointmentpro', 'Booking has been successfully cancelled and Refund amount will be credited to the user account');
                                $txnModel->setStatus(6);
                            } else {
                                $txnModel->setStatus(7);
                            }
                            $txnModel->save();
                        }
                        /*stripe end */
                    }

                    if ($status == 'delete') {
                        $model->setIsDelete(1);
                    } else {
                        $model->setStatus($status);
                    }
                    $model->save();

                    //Credit PLC point's when booking status complete(4)
                    if ($status == 4) {
                        $plcResult = $this->_creditPlcPoints($booking_id, $settingResult);

                        if ($booking['payment_type'] == 'cod') {
                            $txnModel = (new Appointmentpro_Model_Transaction())
                                ->find(['booking_id' => $booking_id])
                                ->setStatus(2)
                                ->save();
                        }
                    }

                    /*Send Email to customer and Vendor*/
                    $this->_sendEmail($booking_id);
                }
            }



            ($settingResult['time_format'] == 1) ? $timeFormat = '' : $timeFormat = 'A';
            ($settingResult['date_format'] == 1) ? $dateFormat = 'm/d/y' : $dateFormat = 'd/m/y';

            $booking = (new Appointmentpro_Model_Booking())->getBookingById($booking_id);
            $booking['customer'] = $booking['firstname'] . ' ' . $booking['lastname'];
            $booking['booking_date'] = date($dateFormat, $booking['appointment_date']);
            $booking['start_time'] = Appointmentpro_Model_Utils::timestampTotime($booking['appointment_time'], $timeFormat);
            $booking['end_time'] = Appointmentpro_Model_Utils::timestampTotime($booking['appointment_end_time'], $timeFormat);
            $booking['currency_symbol'] = Core_Model_Language::getCurrencySymbol();
            $booking['amount_with_currency'] = Appointmentpro_Model_Utils::displayPrice($booking['total_amount'], Core_Model_Language::getCurrencySymbol(), $settingResult['number_of_decimals'], $settingResult['decimal_separator'], $settingResult['thousand_separator'], $settingResult['currency_position']);

            $booking['text_color'] = Appointmentpro_Model_Appointment::getBookingTextcolor($booking['status']);
            $booking['payment_text_color'] = Appointmentpro_Model_Appointment::getPaymentTextcolor($booking['payment_status']);

            $booking['is_completed_hide'] = false;
            $booking['is_rejected_hide'] = false;
            $booking['is_accepted_hide'] = false;
            $booking['is_rejected_accepted_action'] = false;

            if (in_array($booking['status'], ['4', '6', '5'])) {
                $booking['is_completed_hide'] = true;
            }
            if ($booking['status'] == 8) {
                $booking['is_rejected_hide'] = true;
            }
            if ($booking['status'] == 3) {
                $booking['is_accepted_hide'] = true;
            }
            if ($booking['status'] < 3) {
                $booking['is_rejected_accepted_action'] = true;
            }
            $booking['is_payment_hide'] = false;
            if (Appointmentpro_Model_Appointment::getPaymentStatus($booking['payment_status']) !=  'Pending') {
                $booking['is_payment_hide'] = true;
            }
            $booking['payment_status'] = p__('appointmentpro', Appointmentpro_Model_Appointment::getPaymentStatus($booking['payment_status']));
            $booking['status'] = p__('appointmentpro',  Appointmentpro_Model_Appointment::getBookingStatus($booking['status']));

            $booking['payment_type'] = ($booking['payment_type'] == 'cod') ?  p__("appointmentpro",  "Cash") : ucfirst($booking['payment_type']);

            $payload = [
                'success' => true,
                'message' => $message,
                'refundInfo' => $refundInfo,
                'booking' => $booking
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
     *Payment  status
     */
    public function updatePaymentStatusAction()
    {

        try {

            $model = (new Appointmentpro_Model_Booking());
            if ($booking_id = $this->getRequest()->getParam('booking_id')) {
                $model->find(['appointment_id' => $booking_id]);
                dd($this->getRequest()->getParams());
                if (!$model->getAppointmentId()) {
                    // $this->getRequest()->addError( p__("appointmentpro",  "This Appointment does not exist."));

                } else {

                    $message = p__('appointmentpro', 'Payment status updated successfully');
                    $payment_status = $this->getRequest()->getParam('pstatus');

                    $txnModel = (new Appointmentpro_Model_Transaction())
                        ->find(['booking_id' => $booking_id])
                        ->setStatus($payment_status)
                        ->save();
                }

                $value_id = $this->getRequest()->getParam('value_id');
                $setting = (new Appointmentpro_Model_Settings())->find($value_id, "value_id");
                $settingResult = $setting->getData();

                $booking = (new Appointmentpro_Model_Booking())->getBookingById($booking_id);
                $booking['customer'] = $booking['firstname'] . ' ' . $booking['lastname'];
                $booking['booking_date'] = date($dateFormat, $booking['appointment_date']);
                $booking['start_time'] = Appointmentpro_Model_Utils::timestampTotime($booking['appointment_time'], $timeFormat);
                $booking['end_time'] = Appointmentpro_Model_Utils::timestampTotime($booking['appointment_end_time'], $timeFormat);
                $booking['currency_symbol'] = Core_Model_Language::getCurrencySymbol();
                $booking['amount_with_currency'] = Appointmentpro_Model_Utils::displayPrice($booking['total_amount'], Core_Model_Language::getCurrencySymbol(), $settingResult['number_of_decimals'], $settingResult['decimal_separator'], $settingResult['thousand_separator'], $settingResult['currency_position']);

                $booking['text_color'] = Appointmentpro_Model_Appointment::getBookingTextcolor($booking['status']);
                $booking['payment_text_color'] = Appointmentpro_Model_Appointment::getPaymentTextcolor($booking['payment_status']);

                $booking['is_completed_hide'] = false;
                $booking['is_rejected_hide'] = false;
                $booking['is_accepted_hide'] = false;
                $booking['is_rejected_accepted_action'] = false;

                if (in_array($booking['status'], ['4', '6', '5'])) {
                    $booking['is_completed_hide'] = true;
                }
                if ($booking['status'] == 8) {
                    $booking['is_rejected_hide'] = true;
                }
                if ($booking['status'] == 3) {
                    $booking['is_accepted_hide'] = true;
                }
                if ($booking['status'] < 3) {
                    $booking['is_rejected_accepted_action'] = true;
                }

                $booking['is_payment_hide'] = false;
                if (Appointmentpro_Model_Appointment::getPaymentStatus($booking['payment_status']) !=  'Pending') {
                    $booking['is_payment_hide'] = true;
                }
                $booking['payment_status'] = p__('appointmentpro', Appointmentpro_Model_Appointment::getPaymentStatus($booking['payment_status']));
                $booking['status'] = p__('appointmentpro',  Appointmentpro_Model_Appointment::getBookingStatus($booking['status']));

                $booking['payment_type'] = ($booking['payment_type'] == 'cod') ?  p__("appointmentpro",  "Cash") : ucfirst($booking['payment_type']);
            }


            $payload = [
                'success' => true,
                'message' => $message,
                'booking' => $booking
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }



    public function _creditPlcPoints($bookingId = '',  $settingResult = [])
    {

        if ($settingResult['enable_plc_points']) {
            if (class_exists("ProgressiveLoyaltyCards_Model_ProgressiveLoyaltyCards")) {
                $_module_deps = (new Installer_Model_Installer_Module())->find(['name' => 'progressiveloyaltycards']);
                $_module_info = $_module_deps->getData();

                if ($_module_info['version'] >= "1.4.0") {
                    $progressiveloyaltycards_value_id = (new ProgressiveLoyaltyCards_Model_ProgressiveLoyaltyCards())->getCurrentValueId();
                    if (!empty($progressiveloyaltycards_value_id)) {

                        $transactionModal = (new Appointmentpro_Model_Transaction())->find($bookingId, "booking_id");
                        $transaction = $transactionModal->getData();

                        if ($transaction['plc_points'] > 0) {
                            $plc_points = $transaction['plc_points'];
                            $customerId = $transaction['customer_id'];

                            $fcc = new ProgressiveLoyaltyCards_Model_Customer();
                            $cards = $fcc->findAllByOptionValue($progressiveloyaltycards_value_id, $customerId);
                            $current_card = new ProgressiveLoyaltyCards_Model_Customer();

                            foreach ($cards as $card) {
                                if ($card->getIsLocked()) {
                                    $cardIsLocked = true;
                                } elseif ($card->getNumberOfPoints() == $card->getMaxNumberOfPoints()) {
                                    $promotions[] = $card;
                                } elseif ($card->getNumberOfPoints() < $card->getMaxNumberOfPoints()) {
                                    $current_card = $card;
                                }
                            }


                            if ($current_card->getCardId()) {

                                if ($current_card->getNumberOfPoints() < $current_card->getMaxNumberOfPoints()) {
                                    $nbr = (int) $plc_points;
                                    $newnbr = $current_card->getNumberOfPoints() + $nbr;
                                    if ($newnbr > $current_card->getMaxNumberOfPoints()) {
                                        $newnbr = $current_card->getMaxNumberOfPoints();
                                    }

                                    $current_card->setNumberOfPoints($newnbr)
                                        ->setCustomerId($customerId)
                                        ->setNumberOfError(0)
                                        ->setLastError(null)
                                        ->save();
                                    $log = new ProgressiveLoyaltyCards_Model_Log;
                                    $is_visit = $log->visitcheck($customerId);
                                    if (!$is_visit) {
                                        $visits = new ProgressiveLoyaltyCards_Model_Visits;
                                        $visits->setCustomerId($customerId)->save();
                                    }

                                    $current_card->createLog(0, $newnbr);

                                    if ($current_card->getNumberOfPoints() == $current_card->getMaxNumberOfPoints()) {
                                        $rewards = new ProgressiveLoyaltyCards_Model_Rewards();
                                        $rewards = $rewards->findAllRewards($current_card, $customerId, $progressiveloyaltycards_value_id);
                                        foreach ($rewards as $reward) {
                                            if ($reward['redeem_id'] == 0) {
                                                $redeem_model = new ProgressiveLoyaltyCards_Model_Redeem();
                                                $redeem_model->setRewardId($reward['reward_id'])
                                                    ->setCustomerCardId($current_card->getCustomerCardId())
                                                    ->setValueId($progressiveloyaltycards_value_id)
                                                    ->save();
                                            }
                                        }
                                    }
                                }
                                return true;
                            }
                        }
                    }
                }
            }
        }
    }


    /*get paypal status*/
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
            $booking['full_appointment_date'] = date('Y-m-d', $booking['appointment_date']) . ' ' . $booking['start_time'];
        }

        $status = $booking['status'];
        $booking['currency_symbol'] = Core_Model_Language::getCurrencySymbol();
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
        if (in_array($status, [9])) {
            $subject = p__('appointmentpro', 'Request for Appointment  - %s', $this->getApplication()->getName());
            $message =  p__('appointmentpro', 'User %s %s just booking on - %s', $booking['firstname'], $booking['lastname'], $this->getApplication()->getName());
        }

        if (in_array($status, [3])) {
            $subject = p__('appointmentpro', 'A user booking on - %s', $this->getApplication()->getName());
            $message = p__('appointmentpro', 'User %s %s just booking on - %s', $booking['firstname'], $booking['lastname'], $this->getApplication()->getName());
        }

        $booking['buyer_name'] = '';
        $param = [];
        $param['booking'] = $booking;
        $param['sender_email'] = $settingResult['owner_email'];
        $param['email'] = $booking['location_email'];
        $param['subject'] =  $subject;
        $param['message'] =  $message;
        $this->_sendVendorEmail($param);

        /*Customer Push Notification*/
        $param = [];
        $subject = p__('appointmentpro', 'Booking %s - %s', $booking['status'],  $this->getApplication()->getName());
        $message = p__('appointmentpro', 'Your booking has been %s with %s on %s at %s.', '' . $booking['status'] . '', '' . $booking['location_name'] . '', '' . $booking['booking_date'] . '', '' . $booking['start_time'] . '');

        $param['app_id'] = $this->getApplication()->getId();
        $param['value_id'] = $booking['value_id'];
        $param['title'] = $subject;
        $param['text'] = $message;
        $param['receiver_id'] = $booking['customer_id'];
        $param['application'] = $this->getApplication();
        try {
            (new Appointmentpro_Model_Push())->sendv2($param); // Send to customer 
        } catch (\Exception $e) {
        }
        /*End Push notifications*/

        return true;
    }


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

    public function locationAction()
    {
        $payload = [];

        try {
            $value_id = $this->getRequest()->getParam('value_id');
            $application = $this->getApplication();
            $customerId = $this->_getCustomerId(true);

            $customer = (new Customer_Model_Customer())->find($customerId);
            $providerModel = (new Appointmentpro_Model_Provider())->find(['email' => $customer->getEmail(), 'value_id' => $value_id]);

            $location_id = $providerModel->getLocationId();
            $locationModel = (new Appointmentpro_Model_Location())->find(['location_id' => $location_id]);
            $location = $locationModel->getData();
            $location['is_active'] = (bool) $location['is_active'];

            $payload = [
                'success' => true,
                'location' => $location
            ];
        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }

    public function profileAction()
    {
        $payload = [];

        try {
            $value_id = $this->getRequest()->getParam('value_id');
            $customerId = $this->_getCustomerId(true);

            $customer = (new Customer_Model_Customer())->find($customerId);
            $providerModel = (new Appointmentpro_Model_Provider())->find(['email' => $customer->getEmail(), 'value_id' => $value_id]);

            $payload = [
                'success' => true,
                'profile' => $providerModel->getData()
            ];
        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }

    public function profileSaveAction()
    {
        $payload = [];

        try {
            $value_id = $this->getRequest()->getParam('value_id');
            $customerId = $this->_getCustomerId(true);
            $param = $this->getRequest()->getBodyParams();
            $appId = $this->getApplication()->getId();

            $model = (new Appointmentpro_Model_Provider())
                ->find(['provider_id' => $param['provider_id']])
                ->setName($param['name'])
                ->setMobileNumber($param['mobile_number'])
                ->setDescription($param['description']);

            $model->save();

            $customer = new Customer_Model_Customer();
            $customer->find([
                'email' => $param['email'],
                'app_id' => $appId
            ]);

            if (!$customer->getId()) {
                $customer->setName($param['name'])
                    ->setName($param['name'])
                    ->setMobileNumber($param['mobile_number'])
                    ->setDescription($param['description'])
                    ->save();
            }

            $payload = [
                'success' => true,
                'message' => p__('appointmentpro', 'Profile save successfully!')
            ];
        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }


    public function locationSaveAction()
    {
        $payload = [];

        try {
            $value_id = $this->getRequest()->getParam('value_id');
            $customerId = $this->_getCustomerId(true);
            $param = $this->getRequest()->getBodyParams();
            $appId = $this->getApplication()->getId();

            $model = (new Appointmentpro_Model_Location())
                ->find(['location_id' => $param['location_id']])
                ->setName($param['name'])
                ->setEmail($param['email'])
                ->setAddress($param['address'])
                ->setIsActive($param['is_active']);

            $model->save();

            $payload = [
                'success' => true,
                'message' => p__('appointmentpro', 'Location save successfully!')
            ];
        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }
}
