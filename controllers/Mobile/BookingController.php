<?php

use Siberian\Exception;
use Siberian\File;
use Siberian\Json;
use Stripe\Stripe;

/**
 * Class Appointmentpro_Mobile_BookingController
 */
class Appointmentpro_Mobile_BookingController extends Application_Controller_Mobile_Default
{
    const SET_EXPRESS_CHECKOUT = 'SetExpressCheckout';


    public function callbackAction()
    {

        echo "callback running";
        // Posted variables from ITN
        $pfData = $_POST;

        // Strip any slashes in data
        foreach ($pfData as $key => $val)
            $pfData[$key] = stripslashes($val);

        $logger = Zend_Registry::get("logger");
        $logger->log('Response payfast data with params: ' . print_r($pfData, true), Zend_Log::DEBUG);

        try {
            $booking_id = $pfData['m_payment_id'];
            $paymentStatus = $pfData['payment_status'];

            $orderModel = (new Appointmentpro_Model_Booking())
                ->find(['appointment_id' => $booking_id]);

            $settingModel = (new Appointmentpro_Model_Settings())->find(['value_id' => $orderModel->getValueId()]);
            $settingsData = $settingModel->getData();
            $autoAcceptReject = (bool) $settingsData['enable_acceptance_rejection'];


            if ($paymentStatus != "COMPLETE") {
                $booking_status = 5;
                $payment_status = 3;
                $message =  p__('appointmentpro', 'Payment failed');
            } else {
                $booking_status = $autoAcceptReject ? 3 : 9;
                $payment_status = 2;
                if ($autoAcceptReject) {
                    $message = p__('appointmentpro', 'Your booking has been confirmed');
                } else {
                    $message = p__('appointmentpro', 'Your booking has been submitted successfully, We will send you an email confirming that your request has been approve');
                }
            }

            $orderModel = (new Appointmentpro_Model_Booking())
                ->find(['appointment_id' => $booking_id])
                ->setStatus($booking_status)
                ->save();

            $txnModel = (new Appointmentpro_Model_Transaction())
                ->find(['booking_id' => $booking_id])
                ->setTransactionId($pfData['pf_payment_id'])
                ->setAdditionalInfo(json_encode($pfData))
                ->setStatus($payment_status)
                ->save();

            /*Send Email to customer and vendor*/
            $this->_sendEmail($booking_id);

            $logger->log('payfast success : ' . print_r($message, true), Zend_Log::DEBUG);
        } catch (\Exception $e) {
            $logger->log('Exception payfast : ' . print_r($e->getMessage(), true), Zend_Log::DEBUG);
        }

        die;
    }

    public function findInfoAction()
    {
        $payload = [];

        try {
            $value_id = $this->getRequest()->getParam('value_id');
            $param = $this->getRequest()->getBodyParams();
            $isClass = $param['is_class'];

            $currency = Core_Model_Language::getCurrencySymbol();
            $data = (new Appointmentpro_Model_Booking())->getInfo($param);

            $settingModel = (new Appointmentpro_Model_Settings())->find(['value_id' => $value_id]);
            $settingsData = $settingModel->getData();
            $data['is_sale'] = false;
            $data['special_end'] = str_replace('/', '-', $data['special_end']);
            $data['special_start'] = str_replace('/', '-', $data['special_start']);

            if (!empty($data['special_end']) && !empty($data['special_start'])) {
                $sedate = date('Y-m-d H:i:s', strtotime($data['special_end'] . ' 23:59:59'));
                $ssdate = date('Y-m-d H:i:s', strtotime($data['special_start'] . ' 00:00:00'));
                if (strtotime($sedate) > strtotime(date('Y-m-d H:i:s')) && strtotime($ssdate) < strtotime(date('Y-m-d H:i:s'))) {
                    $data['is_sale'] = true;
                    $data['orginal_price'] = Appointmentpro_Model_Utils::displayPrice($data['price'], Core_Model_Language::getCurrencySymbol(), $settingsData['number_of_decimals'], $settingsData['decimal_separator'], $settingsData['thousand_separator'], $settingsData['currency_position']);;
                    $data['price'] = $data['special_price'];
                }
            }

            $data['amount'] = (float) $data['price'];
            $tax_percentage = (float) $settingsData['tax_percentage'];

            $data['tax_percentage'] = (float) $tax_percentage;
            $data['tax_amount'] = (float) (($tax_percentage * $data['amount']) / 100);
            $data['total_amount'] =  (float) ($data['amount'] + $data['tax_amount']);

            $data['is_class'] = (bool) $param['is_class'];
            $data['tickets_qty'] = (int) $param['tickets_qty'];

            if ($isClass) {
                $data['tax_amount'] = ($data['tax_amount'] * $data['tickets_qty']);
                $data['total_amount'] = ($data['total_amount'] * (int) $data['tickets_qty']);
            }

            $data['service_image'] = $data['service_image'] == '0' ? '' : $data['service_image'];

            ($settingsData['date_format'] == 1) ? $dateFormat = 'm/d/y' : $dateFormat = 'd/m/y';

            if ($isClass) {
                $data['slot_date'] = $data['class_date'];
                $data['slot_display_date'] = date($dateFormat,  strtotime(str_replace('/', '-', $data['class_date'])));
                $data['class_end_date'] = date($dateFormat,  strtotime(str_replace('/', '-', $data['class_end_date'])));

                if ($data['schedule_type'] != 'never') {
                    $data['slot_display_date'] = $data['slot_display_date'] . ' - ' . $data['class_end_date'];
                }

                $data['slot_display_time'] = $data['class_time'];
                $data['slot_time'] = $data['class_time'];
            } else {
                $data['slot_date'] = $param['slot_date'];
                $data['slot_display_time'] = $param['slot_display_time'];
                $data['dateFormat'] = $dateFormat;

                $data['slot_display_date'] = date($dateFormat,  strtotime(str_replace('/', '-', $data['slot_date'])));
                $data['slot_time'] = $param['slot_time'];
            }

            $data['total_amount_with_currency'] = Appointmentpro_Model_Utils::displayPrice($data['total_amount'], Core_Model_Language::getCurrencySymbol(), $settingsData['number_of_decimals'], $settingsData['decimal_separator'], $settingsData['thousand_separator'], $settingsData['currency_position']);
            $data['amount_with_currency'] = Appointmentpro_Model_Utils::displayPrice($data['amount'], Core_Model_Language::getCurrencySymbol(), $settingsData['number_of_decimals'], $settingsData['decimal_separator'], $settingsData['thousand_separator'], $settingsData['currency_position']);
            $data['tax_amount_with_currency'] = Appointmentpro_Model_Utils::displayPrice($data['tax_amount'], Core_Model_Language::getCurrencySymbol(), $settingsData['number_of_decimals'], $settingsData['decimal_separator'], $settingsData['thousand_separator'], $settingsData['currency_position']);
            $data['service_amount_with_currency'] = Appointmentpro_Model_Utils::displayPrice($data['amount'], Core_Model_Language::getCurrencySymbol(), $settingsData['number_of_decimals'], $settingsData['decimal_separator'], $settingsData['thousand_separator'], $settingsData['currency_position']);


            $payload = [
                'success' => true,
                'data' => $data
            ];
        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }


    public function findPaymentMethodAction()
    {
        $payload = [];

        try {
            $value_id = $this->getRequest()->getParam('value_id');
            $location_id = $this->getRequest()->getParam('location_id');
            $onlineModeList = ['stripe', 'paypal', 'ewallet', 'nmi'];

            $modelLocation = (new Appointmentpro_Model_Location());
            $modelLocation->find(['location_id' => $location_id]);

            if ($modelLocation->getIsAllowAcceptPayment() == 0) {
                $location_id = 0;
            }

            $gateways = (new Appointmentpro_Model_Gateways())->findAll(['value_id' => $value_id, 'status' => 1, 'location_id' => $location_id]);
            // dd($gateways);

            $stripeGateway = (new Appointmentpro_Model_Gateways())->find(['gateway_code' => 'stripe', 'value_id' => $value_id, 'location_id' => $location_id]);

            $settingModel = (new Appointmentpro_Model_Settings())->find(['value_id' => $value_id]);
            $settingsData = $settingModel->getData();

            $gatewaysjson = [];
            foreach ($gateways as $key => $gateway) {
                $data = $gateway->getData();

                (in_array($data['gateway_code'], $onlineModeList)) ?  $data['is_online'] = true : $data['is_online'] = false;

                (!in_array($data['gateway_code'], $onlineModeList)) ?  $data['is_offline'] = true : $data['is_offline'] = false;

                if ($data['is_online'] && $settingsData['online_payment']) {
                    $gatewaysjson[] =  $data;
                }

                if ($data['is_offline'] && $settingsData['offline_payment']) {
                    $gatewaysjson[] =  $data;
                }
            }

            $publishable_key = null;
            if ($stripeGateway->getId()) {
                $publishable_key = $stripeGateway->getPublishableKey();
            }


            $payload = [
                'success' => true,
                'gateways' => $gatewaysjson,
                'publishable_key' => $stripeGateway->getPublishableKey()
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
     * @param array $data
     * @param null $passPhrase
     * @return string
     */
    public function generateSignature($data, $passPhrase = null)
    {
        // Create parameter string
        $pfOutput = '';
        foreach ($data as $key => $val) {
            if ($val !== '') {
                $pfOutput .= $key . '=' . urlencode(trim($val)) . '&';
            }
        }
        // Remove last ampersand
        $getString = substr($pfOutput, 0, -1);
        if ($passPhrase !== null) {
            $getString .= '&passphrase=' . urlencode(trim($passPhrase));
        }

        return md5($getString);
    }

    /**
     * @param array $data
     * @return string
     */
    public function submitAction()
    {
        $payload = [];

        try {
            $value_id = $this->getRequest()->getParam('value_id');
            $param = $this->getRequest()->getBodyParams();
            $customerId = $this->_getCustomerId();
            $paymentTo = 'admin';
            // dd($param, $customerId);

            $settingModel = (new Appointmentpro_Model_Settings())->find(['value_id' => $value_id]);
            $settingsData = $settingModel->getData();
            $autoAcceptReject = (bool) $settingsData['enable_acceptance_rejection'];

            $customerModel = new Customer_Model_Customer();
            $customerModel->find($customerId);

            if (!$param['is_class']) {
                $startTime = $param['slot_time'];
                $booking_date = strtotime(str_replace('/', '-', $param['slot_date']));

                // Get service for break time configuration (consistent with main BookingController)
                $service = (new Appointmentpro_Model_Service())->find(['service_id' => $param['service_id']]);

                // Check if this service has break time configuration
                $breakConfig = (new Appointmentpro_Model_ServiceBreakConfig())
                    ->find(['service_id' => $param['service_id']]);

                $bookingDuration = $param['details']['service_time']; // Default to service time from params

                if ($breakConfig->getId() && $breakConfig->getBreakIsBookable()) {
                    // For break time services, detect if this is a break slot booking
                    $workBefore = $breakConfig->getWorkTimeBeforeBreak();
                    $breakDuration = $breakConfig->getBreakDuration();
                    $workAfter = $breakConfig->getWorkTimeAfterBreak();
                    $fullServiceTime = $workBefore + $breakDuration + $workAfter;

                    // Check for existing appointments that might create break slots
                    $db = Zend_Db_Table::getDefaultAdapter();
                    $select = $db->select()
                        ->from('appointment')
                        ->where('(service_provider_id = ? OR service_provider_id_2 = ?)', $param['provider_id'])
                        ->where('appointment_date = ?', $booking_date)
                        ->where('service_id = ?', $param['service_id']);

                    $existingAppointments = $db->fetchAll($select);

                    $isBreakSlot = false;
                    foreach ($existingAppointments as $existing) {
                        // Check if the requested time falls within an existing appointment's break period
                        $existingStart = $existing['appointment_time'];
                        $breakStart = $existingStart + ($workBefore * 60);
                        $breakEnd = $breakStart + ($breakDuration * 60);

                        if ($startTime >= $breakStart && $startTime < $breakEnd) {
                            $isBreakSlot = true;
                            // For break slots, use the same duration as the full service
                            $bookingDuration = $fullServiceTime; // Keep full duration for break slots too
                            break;
                        }
                    }

                    if (!$isBreakSlot) {
                        // This is a full service booking
                        $bookingDuration = $fullServiceTime;
                    }
                }

                $endTime = strtotime('+' . $bookingDuration . ' minutes', $startTime);
            } else {
                $startTime = strtotime($param['details']['slot_time']);
                $endTime = strtotime('+' . $param['details']['service_time'] . ' minutes', strtotime($param['details']['slot_time']));
                $booking_date = strtotime($param['details']['slot_date']);
            }

            $order_note = !empty($param['order_note']) ? $param['order_note'] : '';
            $customer = $param['customer'];
            $additional_info = json_encode(['firstname' => $customer['firstname'], 'lastname' => $customer['lastname'], 'email' => $customer['email'], 'mobile' => $customer['mobile'], 'customer_id' => $customerId, 'PhoneNo' => $customer['mobile']]);
            $setIsAddPlcPoints = (int) $param['details']['service_points'];
            $amount = $param['details']['amount'];
            $tax_amount = $param['details']['tax_amount'];
            $total_amount = $param['details']['total_amount'];
            $total_service_points = $param['details']['service_points'];
            //  $total_amount  = number_format($total_amount, 2);

            $model = (new Appointmentpro_Model_Appointment())
                ->find(['appointment_id' => $param['appointment_id']])
                ->setServiceProviderId($param['provider_id'])
                ->setCustomerId($customerId)
                ->setServiceId($param['service_id'])
                ->setLocationId($param['location_id'])
                ->setAppointmentTime($startTime)
                ->setAppointmentEndTime($endTime)
                ->setAppointmentDate($booking_date)
                ->setStatus(1)
                ->setNotes($order_note)
                ->setTotalAmount($total_amount)
                ->setServiceAmount($param['details']['price'])
                ->setServicePlcPoint($param['details']['service_points'])
                ->setValueId($value_id)
                ->setAdditionalInfo($additional_info)
                ->setIsAddPlcPoints($setIsAddPlcPoints)
                ->setCreatedSource('app'); // Flag for mobile app creation

            // Set second provider for break time services if provided
            if (!empty($param['provider_2_id']) && isset($breakConfig) && $breakConfig->getId() && $breakConfig->getHasBreakTime()) {
                try {
                    $model->setServiceProviderId2($param['provider_2_id']);
                } catch (Exception $e) {
                    error_log('Warning: Could not set provider_2_id in mobile - ' . $e->getMessage());
                }
            }

            if ($param['is_class']) {
                $model->setClassId($param['service_id'])
                    ->setBookedSeatClass($param['tickets_qty'])
                    ->setIsItClass(1);
            }

            $model->save();

            $appointmentId =  $model->getAppointmentId();
            $gateways = (new Appointmentpro_Model_Gateways())->find(['id' => $param['payment_method']]);

            $modelLocation = (new Appointmentpro_Model_Location());
            $modelLocation->find(['location_id' => $param['location_id']]);

            if ($gateways->getLocationId() != 0 && $modelLocation->getIsAllowAcceptPayment() != 0) {
                $paymentTo = 'merchant';
            }

            $paymentType = p__('appointmentpro', 'Not Available');
            $message = p__('appointmentpro', 'Your booking has been confirmed');
            if ($gateways->getId()) {
                $paymentType = $gateways->getGatewayCode();
            }
            // dd($param, $paymentType, $gateways->getGatewayCode(), $param['payment_method'], $param['is_webview']);
            if ($appointmentId) {

                $booking_status = true;
                $price_status = 0;
                if ((int)$param['details']['price'] == 0) {
                    $price_status = 2;
                }
                $modelTnx = (new Appointmentpro_Model_Transaction())
                    ->find(['booking_id' => $appointmentId])
                    ->setBookingId($appointmentId)
                    ->setValueId($value_id)
                    ->setCustomerId($customerId)
                    ->setName($customer['firstname'])
                    ->setEmail($customer['email'])
                    ->setMobile($customer['mobile'])
                    ->setPaymentModeId($param['payment_method'])
                    ->setPaymentType($paymentType)
                    ->setAmount($amount)
                    ->setTotalAmount($total_amount)
                    ->setTaxAmount($tax_amount)
                    ->setPlcPoints($total_service_points)
                    ->setStatus($price_status)
                    ->setPaymentTo($paymentTo)
                    ->save();
            }


            if (in_array($gateways->getGatewayCode(), ['stripe', 'cod', 'banktransfer', 'nmi'])) {
                if ($autoAcceptReject) {
                    $message = p__('appointmentpro', 'Your booking has been confirmed');
                } else {
                    $message = p__('appointmentpro', 'Your booking has been submitted successfully, We will send you an email confirming that your request has been approve');
                }
            }

            $is_paypal = false;
            $paypalURL = '';
            // October 16, 2024 - Added line after discussion with Team Leader to enable auto accept/reject functionality
            if ($autoAcceptReject) {
                $orderModel = (new Appointmentpro_Model_Appointment())
                    ->find(['appointment_id' => $appointmentId])
                    ->setStatus(3)
                    ->save();
            }
            // October 16, 2024 - Added line after discussion with Team Leader to enable auto accept/reject functionality END
            /*Payment method COD*/
            if ($gateways->getGatewayCode() == 'cod' || $gateways->getGatewayCode() == 'banktransfer') {

                $orderModel = (new Appointmentpro_Model_Appointment())
                    ->find(['appointment_id' => $appointmentId])
                    ->setStatus($autoAcceptReject ? 3 : 9)
                    ->save();
            }

            /*Payment Method Stripe*/
            if ($gateways->getGatewayCode() == 'stripe') {

                /*Charge at stripe */
                Stripe::setApiKey($gateways->getSecretKey());
                $charge_array = array(
                    "currency" => Core_Model_Language::getCurrentCurrency()->getShortName(),
                    "amount" => (float) $total_amount * 100,
                    "source" =>  $param["stripe"]['token'],
                    "description" =>  p__('appointmentpro', 'Order N') . ' ' . $appointmentId . " from app " . $this->getApplication()->getName(),
                    "metadata" => array(
                        "Payment from app" => $this->getApplication()->getName(),
                        "Order N" => $appointmentId,
                        "First Name" => $customerModel->getFirstname(),
                        "Last Name" => $customerModel->getLastname(),
                        "Email" => $customerModel->getEmail(),
                        "Order N" => p__('appointmentpro', 'Order N') . ' ' . $appointmentId,
                    ),
                );


                $chargeStripe = \Stripe\Charge::create($charge_array);
                $stripeData = $chargeStripe->jsonSerialize();
                $payment_status = $stripeData['status'];

                if ($payment_status == 'succeeded') {
                    $payment_status = 2;
                    $booking_main_status = $autoAcceptReject ? 3 : 9;
                    $booking_status = true;
                }
                if ($payment_status == 'processing') {
                    $payment_status = 1;
                    $booking_main_status = $autoAcceptReject ? 2 : 9;
                    $booking_status = true;
                }
                if ($payment_status == 'payment_failed') {
                    $payment_status = 3;
                    $booking_main_status = 5;
                    $booking_status = false;
                    $message = p__('appointmentpro', 'Payment is failed, Please try again later');
                }

                $orderModel = (new Appointmentpro_Model_Booking())
                    ->find(['appointment_id' => $appointmentId])
                    ->setStatus($booking_main_status)
                    ->save();

                $txnModel = (new Appointmentpro_Model_Transaction())
                    ->find(['booking_id' => $appointmentId])
                    ->setTransactionId($stripeData['id'])
                    ->setStatus($payment_status)
                    ->save();
            }

            /*Payment Method NMI like stripe not add url*/
            if ($gateways->getGatewayCode() == 'nmi') {
                // processing_fee
                $processing_fee = $gateways->getProcessingFee();
                $processing_fee_percentage = $total_amount * ($processing_fee / 100);
                $total_amount = $total_amount + $processing_fee_percentage;
                $is_nmi = true;
                // $message = p__('appointmentpro', 'Your booking is confirmed with payment due');

                $nmiParam = [
                    'is_webview' => $param['is_webview'],
                    'total_amount' => $total_amount,
                    'current_url' => $param['current_url'],
                    'BASE_PATH' => $param['BASE_PATH'],
                    'value_id' => $value_id,
                    'firstname' => $customer['firstname'],
                    'lastname' => $customer['lastname'],
                    'email' => $customer['email'],
                    'item_name' => 'Order#' . $appointmentId,
                    'm_payment_id' => $appointmentId
                ];
                $app_id = $this->getApplication()->getId();
                // $data['expiry_date'] = str_replace('-', '/', $data['expiry_date']);
                $param['nmi']['expiry_date'] = str_replace('/', '', $param['nmi']['expiry_date']);
                // dd($nmiParam);
                // dd($gateways->getSecretKey(), $total_amount, $param['nmi']['card_number'], $param['nmi']['expiry_date'], $param['nmi']['card_cvc'], $gateways->getIsTestMode());
                $responseNmi = $this->processNmiTransaction(
                    $app_id,
                    $value_id,
                    $gateways->getSecretKey(),
                    $total_amount,
                    $param['nmi']['card_number'],
                    $param['nmi']['expiry_date'],
                    $param['nmi']['card_cvc'],
                    $gateways->getIsTestMode()
                );
                $payment_status = $responseNmi['status'];
                if ($payment_status == 'success') {
                    $payment_status = 2;
                    $booking_main_status = $autoAcceptReject ? 3 : 9;
                    $booking_status = true;
                } else {
                    $payment_status = 3;
                    $booking_main_status = 5;
                    $booking_status = false;
                    $message = p__('appointmentpro', $responseNmi['message'] ?? 'Payment is failed, Please try again later');
                }
                $orderModel = (new Appointmentpro_Model_Booking())
                    ->find(['appointment_id' => $appointmentId])
                    ->setStatus($booking_main_status)
                    ->save();

                $txnModel = (new Appointmentpro_Model_Transaction())
                    ->find(['booking_id' => $appointmentId])
                    ->setTransactionId($responseNmi['transaction_id'])
                    ->setStatus($payment_status)
                    ->save();
            }

            /*Payment Method PayPal*/
            if ($gateways->getGatewayCode() == 'paypal') {
                $is_paypal = true;
                $message = p__('appointmentpro', 'Your booking is confirmed with payment due');

                $paypalParam = [
                    'is_webview' => $param['is_webview'],
                    'total_amount' => $total_amount,
                    'current_url' => $param['current_url'],
                    'BASE_PATH' => $param['BASE_PATH'],
                    'value_id' => $value_id,
                    'additionsal_param' => $param
                ];
                $responsePaypal = $this->_getPayPalPaymentUrl($gateways, $paypalParam);
                // dd($responsePaypal);
                /*Failed case*/
                if ($responsePaypal['ACK'] == 'Failure') {
                    $booking_status = false;

                    $txnModel = (new Appointmentpro_Model_Transaction())
                        ->find(['booking_id' => $appointmentId])
                        ->setStatus(3)
                        ->save();

                    $orderModel = (new Appointmentpro_Model_Appointment())
                        ->find(['appointment_id' => $appointmentId])
                        ->setStatus(5)
                        ->save();

                    $message = p__('appointmentpro', 'Payment is failed, Please try again later') . '. Error:' . $responsePaypal['L_LONGMESSAGE0'];

                    $this->_sendEmail($appointmentId); //Send Email 

                } else {
                    if ($responsePaypal['ACK'] == 'Success') {
                        $booking_status = true;
                        $paypalURL = $responsePaypal['pay_url'] . '&webview=1';
                    }
                }
            }

            /*Payment Method PayFast*/
            if ($gateways->getGatewayCode() == 'payfast') {
                $is_payfast = true;
                $message = p__('appointmentpro', 'Your booking is confirmed with payment due');

                $payfastParam = [
                    'is_webview' => $param['is_webview'],
                    'total_amount' => $total_amount,
                    'current_url' => $param['current_url'],
                    'BASE_PATH' => $param['BASE_PATH'],
                    'value_id' => $value_id,
                    'firstname' => $customer['firstname'],
                    'lastname' => $customer['lastname'],
                    'email' => $customer['email'],
                    'item_name' => 'Order#' . $appointmentId,
                    'm_payment_id' => $appointmentId
                ];

                $responsePayfast = $this->_getPayFastPaymentUrl($gateways, $payfastParam);
                $paypalURL = $responsePayfast['url'];
            }

            if (in_array($gateways->getGatewayCode(), ['stripe', 'cod', 'banktransfer'])) {
                $this->_sendEmail($appointmentId);
            }

            if ($paymentType == 'Not Available') {
                $this->_sendEmail($appointmentId);
            }

            $payload = [
                'success' => true,
                'booking_id' => $appointmentId,
                'message' => $message,
                'booking_status' => $booking_status,
                'is_paypal' => $is_paypal,
                'paypalURL' => $paypalURL,
                'stripeData' => $stripeData,
                'responsePayfast' => $responsePayfast,
                'is_payfast' => $is_payfast,
                'responsePaypal' => $responsePaypal
            ];
        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }
    public function processNmiTransaction($app_id, $value_id, $securityKey, $amount, $ccNumber, $ccExp, $cvv, $testMode = 1)
    {
        if ($testMode == 1) {
            $testMode = 'enabled';
        } else {
            $testMode = 'disabled';
        }
        if (empty($securityKey)) {
            return [
                'status' => 'error',
                'message' => p__("appointmentpro", 'Security key is required')
            ];
        }

        $url = 'https://secure.nmi.com/api/transact.php';
        $orderid = time(); // Generate a unique order ID
        $postData = http_build_query([
            'security_key' => $securityKey,
            'amount' => $amount,
            'type' => 'sale',
            'ccnumber' => $ccNumber,
            'ccexp' => $ccExp,
            'cvv' => $cvv,
            'test_mode' => $testMode,
            'orderid' => $orderid // Ensure unique order ID
        ]);
        // dd($postData);

        try {
            $curl = curl_init();
            if (!$curl) {
                throw new Exception('Failed to initialize cURL');
            }

            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $postData,
                CURLOPT_HTTPHEADER => [
                    'Accept: application/x-www-form-urlencoded',
                    'Content-Type: application/x-www-form-urlencoded'
                ],
            ]);

            $response = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            if (curl_errno($curl)) {
                throw new Exception('cURL Error: ' . curl_error($curl));
            }

            curl_close($curl);

            if ($httpCode !== 200) {
                return [
                    'status' => 'error',
                    'message' => 'HTTP Error: ' . $httpCode,
                    'response' => $response
                ];
            }

            parse_str($response, $parsedResponse);
            // dd($parsedResponse);
            // Check if response_code exists and is 1 (Success)
            if ($parsedResponse['response_code'] == "100" && $parsedResponse['response'] == "1") {
                // dd($parsedResponse);
                return [
                    'response' => $parsedResponse,
                    'status' => 'success',
                    'transaction_id' => $parsedResponse['transactionid'] ?? null,
                    'amount' => $parsedResponse['amount'] ?? $amount,
                    'message' => $parsedResponse['responsetext'] ?? 'Transaction successful'
                ];
            }
            // dd($parsedResponse, 'response_code');

            return [
                'status' => 'error',
                'message' => $parsedResponse['responsetext'] ?? 'Transaction failed',
                'error_code' => $parsedResponse['response_code'] ?? null
            ];
        } catch (Exception $e) {
            if (isset($curl)) {
                curl_close($curl);
            }
            return [
                'status' => 'error',
                'message' => 'Exception: ' . $e->getMessage()
            ];
        }
    }

    public function _getPayPalPaymentUrl($gatewayModel, $param = [])
    {
        // dd($gatewayModel->getData(), $param);
        if (empty($param)) return false;

        $logger = Zend_Registry::get("logger");
        $paypalData = [];
        $paypalData["PAYMENTREQUEST_0_CURRENCYCODE"] = Core_Model_Language::getCurrentCurrency()->getShortName();
        $base_url = $this->getRequest()->getBaseUrl();

        if ($param['is_webview']) {
            $paypalData["RETURNURL"] = trim($base_url . '/var/apps/browser/index-prod.html#' . $param['BASE_PATH'] . '/appointmentpro/mobile_paypalreturn/index/value_id/' . $param['value_id']);
            $paypalData["CANCELURL"] = trim($base_url . '/var/apps/browser/index-prod.html#' . $param['BASE_PATH'] . '/appointmentpro/mobile_paypalreturn/index/value_id/' . $param['value_id']);
        } else {
            $paypalData["RETURNURL"] = trim($base_url . '/var/apps/browser/index-prod.html#' . $param['current_url'] . 'confirm');
            $paypalData["CANCELURL"] = trim($base_url . '/var/apps/browser/index-prod.html#' . $param['current_url'] . 'cancel');
        }
        $paypalData["PAYMENTREQUEST_0_AMT"] = number_format($param['total_amount'], 2, '.', '');

        if ($gatewayModel->getPaymentMode() == 0) {
            $paypal_api_user = $gatewayModel->getSandboxusername();
            $paypal_api_user_pwd = $gatewayModel->getSandboxpassword();
            $paypal_api_user_signature = $gatewayModel->getSandboxsignature();
            $api_url = "https://api-3t.sandbox.paypal.com/nvp";
            $paypal_url = "https://www.sandbox.paypal.com/webscr?cmd=_express-checkout&useraction=commit&token=";
        } else {
            $paypal_api_user = $gatewayModel->getUsername();
            $paypal_api_user_pwd = $gatewayModel->getPassword();
            $paypal_api_user_signature = $gatewayModel->getSignature();
            $api_url = "https://api-3t.paypal.com/nvp";
            $paypal_url = "https://www.paypal.com/webscr?cmd=_express-checkout&useraction=commit&token=";
        }
        // dd($param);
        $params = array_merge($paypalData, array(
            'METHOD' => self::SET_EXPRESS_CHECKOUT,
            'VERSION' => '93',
            'USER' => $paypal_api_user,
            'PWD' => $paypal_api_user_pwd,
            'SIGNATURE' => $paypal_api_user_signature,
        ));
        $additional_params = array(
            'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale',
            'PAYMENTREQUEST_0_ITEMAMT' => $param['additionsal_param']['details']['price'],
            'L_PAYMENTREQUEST_0_NAME0' => $param['additionsal_param']['details']['service_name'],
            'L_PAYMENTREQUEST_0_AMT0' => $param['additionsal_param']['details']['price'],
            'L_PAYMENTREQUEST_0_QTY0' => 1,
            'L_PAYMENTREQUEST_0_ITEMCATEGORY0' => 'Digital',
            'NOSHIPPING' => 1
        );
        $additional_params = array(
            'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale',
            'PAYMENTREQUEST_0_ITEMAMT' => round($param['additionsal_param']['details']['total_amount'], 2),
            'L_PAYMENTREQUEST_0_NAME0' => $param['additionsal_param']['details']['service_name'],
            'L_PAYMENTREQUEST_0_AMT0' => round($param['additionsal_param']['details']['total_amount'], 2),
            'L_PAYMENTREQUEST_0_QTY0' => 1,
            'L_PAYMENTREQUEST_0_ITEMCATEGORY0' => 'Digital',
            'NOSHIPPING' => 1
        );

        $params = array_merge($params, $additional_params);
        // dd($params);
        $params = http_build_query($params);
        $curl = curl_init();
        $curlParams = array(
            CURLOPT_URL             => $api_url,
            CURLOPT_POST            => 1,
            CURLOPT_POSTFIELDS      => $params,
            CURLOPT_RETURNTRANSFER  => 1,
            CURLOPT_VERBOSE         => 1,
            CURLOPT_SSL_VERIFYPEER  => false, //si certificat SSL => true
            CURLOPT_SSL_VERIFYHOST  => false, //si certificat SSL => 2
        );

        curl_setopt_array($curl, $curlParams);

        if (APPLICATION_ENV == "development") {
            curl_setopt($curl, CURLOPT_SSLVERSION, 6);
        }
        $response = curl_exec($curl);

        $responseArray = array();
        parse_str($response, $responseArray);
        $responseArray['paypal_url'] = $paypal_url;
        if (curl_errno($curl)) {
            $responseArray['errorMessage'] = curl_error($curl);
            $responseArray['error'] = true;
            curl_close($curl);
            $logger->log("CURL error n° " . print_r($this->_errors, true) . ' - response: ' . print_r($response, true), Zend_Log::DEBUG);
            return $responseArray;
        } else {

            if ($responseArray['ACK'] === 'Success') {
                curl_close($curl);
                if (!empty($responseArray['TOKEN']) and $token = $responseArray['TOKEN']) {
                    $responseArray['pay_url']  = $paypal_url . $responseArray['TOKEN'];
                }
                return $responseArray;
            } else {
                $responseArray['errorMessage'] = $responseArray;
                $responseArray['error'] = true;
                curl_close($curl);
                $logger->log("CURL error: " . print_r($this->_errors, true), Zend_Log::DEBUG);
                return $responseArray;
            }
        }

        return $responseArray;
    }





    public function detailsAction()
    {
        $payload = [];

        try {

            $value_id = $this->getRequest()->getParam('value_id');
            $booking_id = $this->getRequest()->getParam('booking_id');

            $setting = (new Appointmentpro_Model_Settings())->find($value_id, "value_id");
            $settingResult = $setting->getData();
            ($settingResult['time_format'] == 1) ? $timeFormat = '' : $timeFormat = 'A';
            ($settingResult['date_format'] == 1) ? $dateFormat = 'm/d/y' : $dateFormat = 'd/m/y';

            $booking = (new Appointmentpro_Model_Booking())->getBookingById($booking_id);
            //$booking['appointment_time'] = Appointmentpro_Model_Utils::timestampTotime($booking['appointment_time'], $timeFormat);
            $booking['is_class'] = (bool) $booking['is_it_class'];
            if ($booking['is_class']) {
                $booking['appointment_time'] = $booking['class_time'];
                $booking['start_time'] =  $booking['class_time'];
                $booking['end_time'] =  $booking['class_time'];
                $booking['full_appointment_date'] = $booking['class_date'] . ' - ' . $booking['class_end_date'];
            } else {
                $booking['booking_date'] = date($dateFormat, $booking['appointment_date']);
                $booking['start_time'] = Appointmentpro_Model_Utils::timestampTotime($booking['appointment_time'], $timeFormat);
                $booking['end_time'] = Appointmentpro_Model_Utils::timestampTotime($booking['appointment_end_time'], $timeFormat);
                $booking['full_appointment_date'] = date('Y-m-d', $booking['appointment_date']) . ' ' . $booking['start_time'];
            }

            $booking['currency_symbol'] = Core_Model_Language::getCurrencySymbol();

            $booking['total_amount'] = str_replace(',', '', $booking['total_amount']);
            $booking['amount_with_currency'] = Appointmentpro_Model_Utils::displayPrice($booking['total_amount'], Core_Model_Language::getCurrencySymbol(), $settingResult['number_of_decimals'], $settingResult['decimal_separator'], $settingResult['thousand_separator'], $settingResult['currency_position']);

            $booking['payment_status'] = p__('appointmentpro', Appointmentpro_Model_Appointment::getPaymentStatus($booking['payment_status']));
            $booking['status'] = p__('appointmentpro',  Appointmentpro_Model_Appointment::getBookingStatus($booking['status']));
            $booking['appointment_date'] = date($dateFormat, $booking['appointment_date']);

            if ($booking['payment_to'] == 'merchant') {
                $gatewayModal = (new Appointmentpro_Model_Gateways())->find(['gateway_code' => $booking['payment_type'], 'value_id' => $value_id, 'location_id' => $booking['location_id']]);
            } else {
                $gatewayModal = (new Appointmentpro_Model_Gateways())->find(['gateway_code' => $booking['payment_type'], 'value_id' => $value_id]);
            }
            $booking['payment_type'] =  $gatewayModal->getLableName();

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



    public function updatePaymentStatusAction()
    {
        $payload = [];

        try {
            // dd('ooo');
            if ($param = $this->getRequest()->getBodyParams()) {
                $value_id = $this->getRequest()->getParam('value_id');
                $booking_id = $param['booking_id'];
                $payerId = $param['payerId'];
                $tokenId = $param['tokenId'];
                $paymentStatus = $param['status'];
                $paymentResult = [];
                $settingModel = (new Appointmentpro_Model_Settings())->find(['value_id' => $value_id]);
                $settingsData = $settingModel->getData();
                $autoAcceptReject = (bool) $settingsData['enable_acceptance_rejection'];

                $orderModel = (new Appointmentpro_Model_Transaction())
                    ->find(['booking_id' => $param['booking_id']]);
                if ($orderModel->getPaymentType() == 'paypal' && $paymentStatus == 'success') {
                    $paymentResult = $this->GetPaypalCheckoutDetails($param['booking_id'], $tokenId, $payerId);
                    if ($paymentResult) {
                        $param['status'] = 'success';
                    } else {
                        $param['status'] = 'failed';
                    }
                }

                if ($param['status'] != "success") {
                    $booking_status = 5;
                    $payment_status = 3;
                    $message =  p__('appointmentpro', 'Payment failed');
                } else {
                    $booking_status = $autoAcceptReject ? 3 : 9;
                    $payment_status = 2;
                    if ($autoAcceptReject) {
                        $message = p__('appointmentpro', 'Your booking has been confirmed');
                    } else {
                        $message = p__('appointmentpro', 'Your booking has been submitted successfully, We will send you an email confirming that your request has been approve');
                    }
                }

                $orderModel = (new Appointmentpro_Model_Booking())
                    ->find(['appointment_id' => $param['booking_id']])
                    ->setStatus($booking_status)
                    ->save();

                $txnModel = (new Appointmentpro_Model_Transaction())
                    ->find(['booking_id' => $param['booking_id']])
                    ->setTransactionId($param['payerId'])
                    ->setAdditionalInfo(json_encode($paymentResult))
                    ->setStatus($payment_status)
                    ->save();

                /*Send Email to customer and vendor*/
                $this->_sendEmail($param['booking_id']);

                $payload = [
                    'success' => true,
                    'message' => $message,
                    'booking_id' => $param['booking_id'],
                    'paymentResult' => $paymentResult,
                    'param' => $param
                ];
            } else {
                $payload = [
                    "error" => true,
                    "message" => p__('appointmentpro', 'Something went wrong!'),
                ];
            }
        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }



    /*get paypal status*/
    public function GetPaypalCheckoutDetails($booking_id, $token, $payerId)
    {
        $modelOrder = (new Appointmentpro_Model_Booking());
        $order =  $modelOrder->getBookingById($booking_id);

        $location_id = $order['location_id'];
        if ($order['is_allow_accept_payment'] == 0) {
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

        $params = array(
            'METHOD' => 'GetExpressCheckoutDetails',
            'VERSION' => '124.0',
            'USER' => $paypal_api_user,
            'PWD' => $paypal_api_user_pwd,
            'SIGNATURE' => $paypal_api_user_signature,
            'TOKEN' => $token
        );

        $response = $this->setCurl($params, $api_url);
        $response['value_id'] =  $order['value_id'];

        if ($response['CHECKOUTSTATUS'] === 'PaymentActionCompleted') {
            return true;
        }

        if ($response['CHECKOUTSTATUS'] != 'PaymentActionCompleted') {
            $params = array(
                'METHOD' => 'DoExpressCheckoutPayment',
                'VERSION' => '124.0',
                'USER' => $paypal_api_user,
                'PWD' => $paypal_api_user_pwd,
                'SIGNATURE' => $paypal_api_user_signature,
                'TOKEN' => $token,
                'PAYERID' => $payerId,
                'PAYMENTREQUEST_0_AMT' => $order['total_amount'],
                'PAYMENTREQUEST_0_CURRENCYCODE' => Core_Model_Language::getCurrentCurrency()->getShortName()
            );

            $result2 = $this->setCurl($params, $api_url);
            if ($result2) {
                $response['result'] = $result2;
                return $response;
            } else {
                return $response;
            }
        }
    }



    public function setCurl($params, $api_url)
    {

        $params = http_build_query($params);
        $curl = curl_init();
        $curlParams = array(
            CURLOPT_URL             => $api_url,
            CURLOPT_POST            => 1,
            CURLOPT_POSTFIELDS      => $params,
            CURLOPT_RETURNTRANSFER  => 1,
            CURLOPT_VERBOSE         => 1,
            CURLOPT_SSL_VERIFYPEER  => false, //si certificat SSL => true
            CURLOPT_SSL_VERIFYHOST  => false, //si certificat SSL => 2
        );
        curl_setopt_array($curl, $curlParams);
        $response = curl_exec($curl);
        if ($response) {
            $responseArray = array();
            parse_str($response, $responseArray);
            return $responseArray;
        }
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
        // dd("ok");
        $booking = (new Appointmentpro_Model_Booking())->getBookingById($booking_id);
        $setting = (new Appointmentpro_Model_Settings())->find($booking['value_id'], "value_id");
        $settingResult = $setting->getData();
        ($settingResult['time_format'] == 1) ? $timeFormat = '' : $timeFormat = 'A';
        ($settingResult['date_format'] == 1) ? $dateFormat = 'm/d/y' : $dateFormat = 'd/m/y';

        $booking['is_class'] = (bool) $booking['is_it_class'];
        $value_id = $booking['value_id'];
        if ($booking['is_class']) {
            $booking['booking_date'] = date($dateFormat, $booking['appointment_date']);
            $booking['start_time'] =  $booking['class_time'];
            $booking['end_time'] =  $booking['class_time'];
            $booking['full_appointment_date'] =  $booking['class_date'] . ' - ' . $booking['class_end_date'];

            if ($booking['schedule_type'] == 'weekly') {
                $booking['full_appointment_date'] = $booking['full_appointment_date'] . '<br> (On Every - ' . $booking['day_of_week'] . ")";
            }
            if ($booking['schedule_type'] == 'monthly') {
                $booking['full_appointment_date'] = $booking['full_appointment_date'] . ' <br>(On Every - ' . $booking['day_of_month'] . '' . date("S", mktime(0, 0, 0, 0, $booking['day_of_month'], 0)) . ")";
            }
            if ($booking['schedule_type'] == 'daily') {
                $booking['full_appointment_date'] = $booking['full_appointment_date'] . ' <br>(Daily)';
            }
        } else {
            $booking['booking_date'] = date($dateFormat, $booking['appointment_date']);
            $booking['start_time'] = Appointmentpro_Model_Utils::timestampTotime($booking['appointment_time'], $timeFormat);
            $booking['end_time'] = Appointmentpro_Model_Utils::timestampTotime($booking['appointment_end_time'], $timeFormat);
            $booking['full_appointment_date'] = date('Y-m-d', $booking['appointment_date']);
        }

        $status = $booking['status'];
        $booking['currency_symbol'] = Core_Model_Language::getCurrencySymbol();
        $booking['amount_with_currency'] = Core_Model_Language::getCurrencySymbol() . $booking['total_amount'];
        $booking['service_amount_with_currency'] = Core_Model_Language::getCurrencySymbol() . $booking['service_amount'];
        $booking['is_paid'] = ($booking['payment_status'] == 2) ? true : false;
        $booking['payment_status'] = p__('appointmentpro', Appointmentpro_Model_Appointment::getPaymentStatus($booking['payment_status']));
        $booking['status'] = p__('appointmentpro',  Appointmentpro_Model_Appointment::getBookingStatus($booking['status']));
        $booking['created_at'] = date($dateFormat . ' ' . $timeFormat, strtotime($booking['created_at']));
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

        if (in_array($status, [3, 4, 5, 6, 8, 1])) {
            $subject = p__('appointmentpro', 'Booking %s - %s', $booking['status'],  $this->getApplication()->getName());
            $message = p__('appointmentpro', 'Your booking has been %s, Please see the important details below regarding your information', $booking['status']);
        }
        $param = [];
        $param['booking'] = $booking;
        $param['booking']['settings'] =  $settingResult;
        $param['sender_email'] = $booking['location_email'];
        $param['email'] = $booking['buyer_email'];
        $param['subject'] =  $subject;
        $param['message'] =  $message;
        $param['receiver_id'] =  $booking['customer_id'];
        $param['value_id'] =  $booking['value_id'];
        $param['app_id'] = $this->getApplication()->getId();
        $param['title'] = $subject;
        $param['text'] = $message;

        $this->_sendCustomerEmail($param);
        // new customer push part
        try {
            (new Appointmentpro_Model_Push())->sendv2($param);
        } catch (\Exception $e) {
        }
        // new customer push part end
        $subject = p__('appointmentpro', 'A user booking on - %s', $this->getApplication()->getName());
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
        $param['booking']['settings'] =  $settingResult;
        $param['sender_email'] = $settingResult['owner_email'];
        $param['email'] = $booking['location_email'];
        $param['subject'] =  $subject;
        $param['message'] =  $message;

        $this->_sendVendorEmail($param);


        /*Customer Push Notification*/
        $providers = (new Appointmentpro_Model_Provider())->getMobileProvider($booking['location_id'],  $this->getApplication()->getId());
        // dd("sending push");
        foreach ($providers as $key => $provider) {
            $toSend = false;
            if ($provider['user_role'] == 'manager') {
                $toSend = true;
            }
            if ($provider['user_role'] == 'provider' && $provider['provider_id'] == $booking['provider_id']) {
                $toSend = true;
            }
            $toSend = true;
            if ($toSend) {
                $param = [];
                $param['app_id'] = $this->getApplication()->getId();
                $param['value_id'] = $booking['value_id'];
                $param['title'] = $subject;
                $param['text'] = $message;
                $param['receiver_id'] = $provider['customer_id'];
                $param['application'] = $this->getApplication();
                // dd($param);
                try {
                    (new Appointmentpro_Model_Push())->sendv2($param);
                } catch (\Exception $e) {
                }
            }
        }
        /*End Push notifications*/

        /*Send Booking to google calendar*/
        if (in_array($status, [3, 9])) {
            $eventId = $this->_sendEventToGoogle($booking, $settingResult);

            $orderModel = (new Appointmentpro_Model_Booking())
                ->find(['appointment_id' => $booking_id])
                ->setGCalendarId($eventId)
                ->save();
        }

        return true;
    }


    private function _sendEventToGoogle($booking, $settingResult)
    {

        $enable_google_calendar = (bool)$settingResult['enable_google_calendar'];

        if (!$enable_google_calendar &&  empty($settingResult['client_id']) && empty($settingResult['client_secret']) && empty($booking['google_refresh_token'])) return false;

        $application = $this->getApplication();
        $appKey = $application->getKey();

        //Make URL
        $google_redirect_URL = trim($application->getBaseUrl() . '/' . $appKey . '/appointmentpro/mobile_view/retrun-calendar');

        //Set client id, Client Secret and URL
        $google = (new Appointmentpro_Model_GoogleService());
        $google->setClientId($settingResult['client_id']);
        $google->setRedirectUri($google_redirect_URL);
        $google->setClientSecret($settingResult['client_secret']);
        $accessTokenResponce = $google->GetAccessTokenUsingRefreshToken($booking['google_refresh_token']);

        if (!empty($accessTokenResponce['error'])) return $accessTokenResponce['error']['message'];
        $accessToken = $accessTokenResponce['access_token'];

        //Get Time Zone
        $google->setAction('GET');
        $google->setAccessToken($accessToken);
        $timezone = $google->GetUserCalendarTimezone($accessToken);

        $event_timezone = $timezone['value'];
        if (empty($event_timezone)) return $timezone['error']['message'];

        //Create event Date
        $event_date = date('Y-m-d', $booking['appointment_date']);
        $start_time = trim(Appointmentpro_Model_Utils::timestampTotime($booking['appointment_time'])) . ':00';
        $end_time = trim(Appointmentpro_Model_Utils::timestampTotime($booking['appointment_end_time'])) . ':00';


        //Make Post Array 
        $curlPost = array(
            'summary' =>  $booking['buyer_name'] . '@' . $booking['service_name'],
            'location' => $booking['location_name'],
            'description' => $booking['notes']
        );
        $curlPost['start'] = array('dateTime' => $event_date . 'T' . $start_time, 'timeZone' => $event_timezone);
        $curlPost['end'] = array('dateTime' =>  $event_date . 'T' . $end_time, 'timeZone' => $event_timezone);

        $curlPost['attendees'] = array(
            array('email' =>  $booking['buyer_email'], 'self' => true)
        );

        $curlPost['reminders'] = array(
            'useDefault' => FALSE,
            'overrides' => array(
                array('method' => 'email', 'minutes' => 24 * 60),
                array('method' => 'popup', 'minutes' => 30),
            ),
        );

        $eventResponce = $google->CreateCalendarEvent($accessToken, $curlPost);

        if (empty($eventResponce['id'])) return $eventResponce['error']['message'];

        return $eventResponce['id'];
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

    public function _getPayFastPaymentUrl($gatewayModel, $param = [])
    {

        $base_url = $this->getRequest()->getBaseUrl();
        $cartTotal = $param['total_amount'];
        $data = array();

        // If in testing mode make use of either sandbox.payfast.co.za or www.payfast.co.za
        $data['paymentMode'] = (bool) $gatewayModel->getIsLive();
        $action_url = $data['paymentMode'] ? 'www.payfast.co.za' : 'sandbox.payfast.co.za';
        $data['action_url'] = 'https://' . $action_url . '/eng/process';

        $fieldsArray = array(
            // Merchant details,
            'paymentMode' => $gatewayModel->getIsLive(),
            'merchant_id' => $gatewayModel->getMerchantId(),
            'merchant_key' => $gatewayModel->getMerchantKey(),
            'name_first' => $param['firstname'],
            'name_last'  => $param['lastname'],
            'email_address' => $param['email'],
            // Transaction details
            'amount' => number_format(sprintf('%.2f', $cartTotal), 2, '.', ''),
            'item_name' => $param['item_name'],
            'm_payment_id' => $param['m_payment_id'],
            'is_webview' => $param['is_webview']
        );

        $data['url']  = parent::getUrl('appointmentpro/mobile_booking/payfasturl', ['value_id' => $param['value_id'], 'data' => urlencode(serialize($fieldsArray))]);

        return $data;
    }


    /**
     *
     */
    public function payfasturlAction()
    {
        $basePath =  $this->getApplication()->getKey();
        $base_url = $this->getRequest()->getBaseUrl();
        $value_id = $this->getRequest()->getParam('value_id');
        $data = $this->getRequest()->getParam('data');
        $data = unserialize($data);

        if ($data['is_webview']) {
            $data['return_url'] = trim($base_url . '/var/apps/browser/index-prod.html#' . $basePath . '/appointmentpro/mobile_payfastreturn/index/value_id/' . $value_id);
            $data['cancel_url'] = trim($base_url . '/var/apps/browser/index-prod.html#' . $basePath . '/appointmentpro/mobile_payfastcancel/index/value_id/' . $value_id);
        } else {
            $data['return_url'] = trim($base_url . '/var/apps/browser/index-prod.html#' . $basePath . '/confirm');
            $data['cancel_url'] = trim($base_url . '/var/apps/browser/index-prod.html#' . $basePath  . '/cancel');
        }

        //$data['notify_url'] = trim($base_url . '' . $basePath .'/appointmentpro/mobile_booking/callback');

        $action_url = (bool) $data['paymentMode'] ? 'www.payfast.co.za' : 'sandbox.payfast.co.za';
        $action_url = 'https://' . $action_url . '/eng/process';
        unset($data['paymentMode']);


        $htmlForm = '<form name="paymentform" id="paymentform"  action="' . $action_url . '" method="post">';

        echo "<h2>";
        echo __("Please wait while you are redirected to the payment server.");
        echo "</h2>";

        foreach ($data as $name => $value) {
            $htmlForm .= '<input name="' . $name . '" type="hidden" value="' . $value . '" />';
        }
        $htmlForm .= '<input type="submit" value="' . __("Proceed") . '" /></form>';
        $htmlForm .= '<script type="text/javascript">
                        window.onload=function(){
                            document.forms["paymentform"].submit();
                        }
                    </script>
                    <style>
                    body {
                      padding-top:40%;  
                      text-align: center;
                    }
                    </style>

                    ';
        echo $htmlForm;

        die;
    }
}
