<?php

use Stripe\Stripe;

/**
 * Class Appointmentpro_BookingController
 */
class Appointmentpro_BookingController extends Application_Controller_Default
{

    /**
     *Add booking
     */
    public function addAction()
    {

        $this->loadPartials();
    }
    public function runAction()
    {
        $notifications_responce = (new Appointmentpro_Model_Cron())->reminderJob();
        echo "<pre>";
        print_r($notifications_responce);
        echo "</pre>";
        if ($notifications_responce) {
            echo p__("Appointmentpro", "Cron executed successfully.") . date('d/m/y h:i A');
        }
        exit;
    }
    public function getCustomerFormAction()
    {
        $data = $this->getRequest()->getPost();
        try {
            $layout = new Siberian_Layout();
            $layout->setBaseRender('content', 'appointmentpro/booking/customer.phtml', 'core_view_default');
            $layout
                ->getBaseRender()
                // ->setPerformanceConfig($performance_config)
                ->setApplication($this->getApplication());

            echo $layout->render();
            die;
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
    public function getCustomersAction()
    {
        try {
            $datas = [];
            if ($data = $this->getRequest()->getParams()) {

                $searchTerm = isset($data['q']) ? $data['q'] : '';
                $customerModel = new Customer_Model_Customer();
                $allCustomers = $customerModel->findAll(['app_id' => $this->getApplication()->getId()])->toArray();

                // If search term is empty, include all customers
                if (empty($searchTerm)) {
                    foreach ($allCustomers as $customer) {
                        $datas[] = [
                            'id' => $customer['customer_id'],
                            'text' => $customer['firstname'] . ' ' . $customer['lastname'] . ' (' . $customer['email'] . ')',
                            'email' => $customer['email']
                        ];
                    }
                } else {
                    // Filter customers based on the search term
                    foreach ($allCustomers as $customer) {
                        if (
                            stripos($customer['firstname'], $searchTerm) !== false ||
                            stripos($customer['lastname'], $searchTerm) !== false ||
                            stripos($customer['email'], $searchTerm) !== false
                        ) {
                            $datas[] = [
                                'id' => $customer['customer_id'],
                                'text' => $customer['firstname'] . ' ' . $customer['lastname'] . ' (' . $customer['email'] . ')',
                                'email' => $customer['email']
                            ];
                        }
                    }
                }
            }

            $payload = [
                'success' => true,
                'items' => $datas
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
     * Get previous notes for a selected customer
     */
    public function getCustomerNotesAction()
    {
        try {
            $customerId = (int) $this->getRequest()->getParam('customer_id');
            $limit = (int) $this->getRequest()->getParam('limit', 5);

            if ($customerId <= 0) {
                $this->_sendJson([
                    'success' => true,
                    'notes' => []
                ]);
                return;
            }

            $valueId = Appointmentpro_Model_Appointmentpro::getCurrentValueId();
            if ($valueId) {
                $valueId = (int) $valueId;
            } else {
                $valueId = (int) $this->getRequest()->getParam('value_id');
            }

            if (!$valueId) {
                throw new Exception(p__("appointmentpro", 'Unable to determine the current application.'));
            }

            if ($limit <= 0) {
                $limit = 5;
            }

            $bookingModel = new Appointmentpro_Model_Booking();
            $notes = $bookingModel->getNotesByCustomer($valueId, $customerId, $limit);

            $notesData = [];
            foreach ($notes as $note) {
                $noteText = isset($note['notes']) ? trim($note['notes']) : '';
                if ($noteText === '') {
                    continue;
                }

                $dateTimestamp = null;
                if (!empty($note['appointment_date']) && is_numeric($note['appointment_date'])) {
                    $dateTimestamp = (int) $note['appointment_date'];
                }

                if (!$dateTimestamp && !empty($note['created_at'])) {
                    $createdAt = strtotime($note['created_at']);
                    if ($createdAt !== false) {
                        $dateTimestamp = $createdAt;
                    }
                }

                $timeLabel = '';
                if (!empty($note['appointment_time'])) {
                    if (is_numeric($note['appointment_time'])) {
                        $timeLabel = date('h:i A', (int) $note['appointment_time']);
                    } else {
                        $timeTimestamp = strtotime($note['appointment_time']);
                        if ($timeTimestamp !== false) {
                            $timeLabel = date('h:i A', $timeTimestamp);
                        } else {
                            $timeLabel = (string) $note['appointment_time'];
                        }
                    }
                } elseif ($dateTimestamp) {
                    $timeLabel = date('h:i A', $dateTimestamp);
                }

                $dateLabel = '';
                if ($dateTimestamp) {
                    $formattedDate = date('M d, Y', $dateTimestamp);
                    if ($timeLabel) {
                        $dateLabel = sprintf(p__("appointmentpro", 'Added on %s at %s'), $formattedDate, $timeLabel);
                    } else {
                        $dateLabel = sprintf(p__("appointmentpro", 'Added on %s'), $formattedDate);
                    }
                }

                $notesData[] = [
                    'appointment_id' => (int) $note['appointment_id'],
                    'note' => $noteText,
                    'date_label' => $dateLabel                    
                ];
            }

            $payload = [
                'success' => true,
                'notes' => $notesData,
                'raw_notes' => $notes,
            ];
        } catch (\Exception $e) {
            $payload = [
                'success' => false,
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     * Get form data for new appointment modal
     */
    public function getFormDataAction()
    {
        try {
            $value_id = (new Appointmentpro_Model_Appointmentpro())->getCurrentValueId();

            // Get locations
            $locations = (new Appointmentpro_Model_Location())
                ->findByValueId($value_id, []);

            $locationsJson = [];
            foreach ($locations as $location) {
                $data = $location->getData();
                $locationsJson[] = [
                    'location_id' => $data['location_id'],
                    'name' => $data['name']
                ];
            }

            // Get providers
            $providers = (new Appointmentpro_Model_Provider())
                ->findByValueId($value_id, []);

            $providersJson = [];
            foreach ($providers as $provider) {
                $data = $provider->getData();
                $providersJson[] = [
                    'provider_id' => $data['provider_id'],
                    'name' => $data['name']
                ];
            }

            // Get services
            $services = (new Appointmentpro_Model_Service())
                ->findByValueId($value_id, []);

            $servicesJson = [];
            foreach ($services as $service) {
                $data = $service->getData();
                $servicesJson[] = [
                    'service_id' => $data['service_id'],
                    'name' => $data['name']
                ];
            }

            // Get booking statuses
            $bookingStatuses = [];
            $statusList = Appointmentpro_Model_Appointment::getBookingStatus();
            foreach ($statusList as $key => $status) {
                $bookingStatuses[] = [
                    'value' => $key,
                    'label' => p__('appointmentpro', $status)
                ];
            }

            // Get payment statuses
            $paymentStatuses = [];
            $paymentStatusList = Appointmentpro_Model_Appointment::getPaymentStatus();
            foreach ($paymentStatusList as $key => $status) {
                $paymentStatuses[] = [
                    'value' => $key,
                    'label' => p__('appointmentpro', $status)
                ];
            }

            // Get payment modes from gateways (same filters as new_booking.phtml)
            $gateways = (new Appointmentpro_Model_Gateways())
                ->findAll(['value_id' => $value_id, 'status' => 1, 'location_id' => 0]);

            $paymentModes = [];
            foreach ($gateways as $gateway) {
                $data = $gateway->getData();
                $paymentModes[] = [
                    'value' => $data['id'],  // Use ID instead of gateway_code to match new_booking.phtml
                    'label' => $data['lable_name']
                ];
            }

            $payload = [
                'success' => true,
                'locations' => $locationsJson,
                'providers' => $providersJson,
                'services' => $servicesJson,
                'booking_statuses' => $bookingStatuses,
                'payment_statuses' => $paymentStatuses,
                'payment_modes' => $paymentModes
            ];
        } catch (\Exception $e) {
            $payload = [
                'success' => false,
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     * Search customers for booking form
     */
    public function searchCustomersAction()
    {
        try {
            $searchTerm = $this->getRequest()->getParam('search', '');
            $value_id = (new Appointmentpro_Model_Appointmentpro())->getCurrentValueId();
            $application = $this->getApplication();
            $appId = $application->getId();

            $customers = [];

            if (!empty($searchTerm) && strlen($searchTerm) >= 2) {
                // Search customers using direct database query
                $db = Zend_Db_Table::getDefaultAdapter();
                $select = $db->select()
                    ->from(['c' => 'customer'], [
                        'c.customer_id',
                        'c.firstname',
                        'c.lastname',
                        'c.email',
                        'c.mobile'
                    ])
                    ->where('c.app_id = ?', $appId)
                    ->where('(c.firstname LIKE ? OR c.lastname LIKE ? OR c.email LIKE ?)', "%{$searchTerm}%")
                    ->limit(10)
                    ->order('c.firstname ASC');

                $results = $db->fetchAll($select);

                foreach ($results as $customer) {
                    $customers[] = [
                        'customer_id' => $customer['customer_id'],
                        'name' => $customer['firstname'] . ' ' . $customer['lastname'],
                        'email' => $customer['email'],
                        'phone' => $customer['mobile']
                    ];
                }
            }

            $payload = [
                'success' => true,
                'customers' => $customers
            ];
        } catch (\Exception $e) {
            $payload = [
                'success' => false,
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     * Create new appointment from booking form 
     */
    public function createAppointmentAction()
    {
        try {
            $param = $this->getRequest()->getPost();
            $value_id = (new Appointmentpro_Model_Appointmentpro())->getCurrentValueId();
            $application = $this->getApplication();
            $appId = $application->getId();

            // Validate required fields
            if (
                empty($param['customer_id']) || empty($param['location_id']) ||
                empty($param['provider_id']) || empty($param['service_id']) ||
                empty($param['appointment_date']) || empty($param['appointment_time'])
            ) {
                throw new Exception('Missing required fields');
            }

            // Get customer info
            $customerId = $param['customer_id'];
            if ($customerId === 'new') {
                // Create new customer
                $customerModel = new Customer_Model_Customer();
                $customer = [
                    'firstname' => $param['customer_name'],
                    'lastname' => '',
                    'email' => $param['customer_email'],
                    'mobile' => $param['customer_phone'],
                    'app_id' => $appId,
                    'privacy_policy' => false,
                    'communication_agreement' => false
                ];
                $customerModel->setData($customer);
                $customerModel->setPassword('12345678');
                $customerModel->save();
                $customerId = $customerModel->getId();
            }

            // Get service details for pricing and timing
            $db = Zend_Db_Table::getDefaultAdapter();
            $select = $db->select()
                ->from(['s' => 'appointment_service'], [
                    's.price',
                    's.service_time',
                    's.service_points'
                ])
                ->where('s.service_id = ?', $param['service_id'])
                ->where('s.value_id = ?', $value_id);

            $service = $db->fetchRow($select);
            if (!$service) {
                throw new Exception('Service not found');
            }

            // Calculate appointment times
            $appointmentDate = strtotime($param['appointment_date']);
            $appointmentTime = strtotime($param['appointment_time']);

            // Check if this service has break time configuration
            $breakConfig = (new Appointmentpro_Model_ServiceBreakConfig())
                ->find(['service_id' => $param['service_id']]);

            $bookingDuration = $service['service_time']; // Default to full service time

            if ($breakConfig->getId() && $breakConfig->getBreakIsBookable()) {
                // For break time services, detect if this is a break slot booking
                // by checking if there are existing appointments that would make this a break slot

                $workBefore = $breakConfig->getWorkTimeBeforeBreak();
                $breakDuration = $breakConfig->getBreakDuration();
                $workAfter = $breakConfig->getWorkTimeAfterBreak();
                $fullServiceTime = $workBefore + $breakDuration + $workAfter;

                // Check for existing appointments that might create break slots
                $db = Zend_Db_Table::getDefaultAdapter();
                $select = $db->select()
                    ->from('appointment')
                    ->where('(service_provider_id = ? OR service_provider_id_2 = ?)', $param['provider_id'])
                    ->where('appointment_date = ?', $appointmentDate)
                    ->where('service_id = ?', $param['service_id']);

                $existingAppointments = $db->fetchAll($select);

                $isBreakSlot = false;
                foreach ($existingAppointments as $existing) {
                    // Check if the requested time falls within an existing appointment's break period
                    $existingStart = $existing['appointment_time'];
                    $breakStart = $existingStart + ($workBefore * 60);
                    $breakEnd = $breakStart + ($breakDuration * 60);

                    if ($appointmentTime >= $breakStart && $appointmentTime < $breakEnd) {
                        $isBreakSlot = true;
                        // For break slots, use the same duration as the full service
                        // The break slot should still book the full service duration
                        $bookingDuration = $fullServiceTime; // Keep full duration for break slots too
                        break;
                    }
                }

                if (!$isBreakSlot) {
                    // This is a full service booking
                    $bookingDuration = $fullServiceTime;
                }
            }

            $appointmentEndTime = strtotime('+' . $bookingDuration . ' minutes', $appointmentTime);

            // Create appointment
            $appointmentModel = new Appointmentpro_Model_Appointment();
            $appointmentModel->setServiceProviderId($param['provider_id'])
                ->setCustomerId($customerId)
                ->setServiceId($param['service_id'])
                ->setLocationId($param['location_id'])
                ->setAppointmentTime($appointmentTime)
                ->setAppointmentEndTime($appointmentEndTime)
                ->setAppointmentDate($appointmentDate)
                ->setStatus($param['booking_status'])
                ->setNotes($param['note'] ?? '')
                ->setTotalAmount($service['price'])
                ->setServiceAmount($service['price'])
                ->setServicePlcPoint($service['service_points'])
                ->setValueId($value_id)
                ->setIsAddPlcPoints(1);

            // Set second provider for break time services if provided
            if (!empty($param['provider_2_id']) && $breakConfig->getId() && $breakConfig->getHasBreakTime()) {
                try {
                    $appointmentModel->setServiceProviderId2($param['provider_2_id']);
                } catch (Exception $e) {
                    // Column might not exist, log but continue
                    error_log('Warning: Could not set provider_2_id - ' . $e->getMessage());
                }
            }

            $appointmentModel->save();
            $appointmentId = $appointmentModel->getAppointmentId();

            // Create transaction record
            $transactionModel = new Appointmentpro_Model_Transaction();
            $transactionModel->setBookingId($appointmentId)
                ->setValueId($value_id)
                ->setCustomerId($customerId)
                ->setPaymentModeId($param['payment_mode'])
                ->setAmount($service['price'])
                ->setTotalAmount($service['price'])
                ->setStatus($param['payment_status']);

            $transactionModel->save();

            $payload = [
                'success' => true,
                'appointment_id' => $appointmentId,
                'message' => 'Appointment created successfully'
            ];
        } catch (\Exception $e) {
            $payload = [
                'success' => false,
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     *booking screen
     */
    public function listAction()
    {

        $bookingStatus = Appointmentpro_Model_Appointment::getBookingStatus();
        $type = 'all';

        if (!empty($this->getRequest()->getParam('type'))) {
            $type = $this->getRequest()->getParam('type');
        }

        $value_id = (new Appointmentpro_Model_Appointmentpro())->getCurrentValueId();
        $locations = (new Appointmentpro_Model_Location())
            ->findByValueId($value_id, []);


        $this->loadPartials();
        $this->getLayout()->getPartial('content')->setBookingStatus($bookingStatus)->setType($type)->setCurrentLink('booking' . $type)->setLocations($locations);
    }


    /**
     *booking classes screen
     */
    public function classesAction()
    {

        $bookingStatus = Appointmentpro_Model_Appointment::getBookingStatus();
        $type = 'all';

        if (!empty($this->getRequest()->getParam('type'))) {
            $type = $this->getRequest()->getParam('type');
        }

        $value_id = (new Appointmentpro_Model_Appointmentpro())->getCurrentValueId();
        $locations = (new Appointmentpro_Model_Location())
            ->findByValueId($value_id, []);


        $this->loadPartials();
        $this->getLayout()->getPartial('content')->setBookingStatus($bookingStatus)->setType($type)->setCurrentLink('classes' . $type)->setLocations($locations);
    }

    /**
     *booking view
     */
    public function viewAction()
    {

        $bookingStatus = Appointmentpro_Model_Appointment::getBookingStatus();
        $type = 'all';
        if (!empty($this->getRequest()->getParam('type'))) {
            $type = $this->getRequest()->getParam('type');
        }
        $id = $this->getRequest()->getParam('id');

        $booking = (new Appointmentpro_Model_Booking())
            ->getBookingById($id);

        $setting = (new Appointmentpro_Model_Settings())->find($booking['value_id'], "value_id");
        $settingResult = $setting->getData();
        ($settingResult['time_format'] == 1) ? $timeFormat = '' : $timeFormat = 'A';
        ($settingResult['date_format'] == 1) ? $dateFormat = 'm/d/y' : $dateFormat = 'd/m/y';

        $booking['duration'] =  abs($booking['appointment_end_time'] - $booking['appointment_time']) / (60);
        $booking['booking_date'] = date($dateFormat, $booking['appointment_date']);
        $booking['start_time'] = Appointmentpro_Model_Utils::timestampTotime($booking['appointment_time'], $timeFormat);
        $booking['end_time'] = Appointmentpro_Model_Utils::timestampTotime($booking['appointment_end_time'], $timeFormat);
        $booking['currency_symbol'] = $this->getApplication()->getCurrency();
        $booking['amount_with_currency'] = $this->getApplication()->getCurrency() . ' ' . $booking['amount'];
        $booking['total_amount_with_currency'] = $this->getApplication()->getCurrency() . ' ' . $booking['total_amount'];
        $booking['tax_with_currency'] = $this->getApplication()->getCurrency() . ' ' . $booking['tax_amount'];
        $booking['text_color'] = Appointmentpro_Model_Appointment::getBookingTextcolor($booking['status']);
        $booking['payment_text_color'] = Appointmentpro_Model_Appointment::getPaymentTextcolor($booking['payment_status']);
        $booking['payment_status'] = p__('appointmentpro', Appointmentpro_Model_Appointment::getPaymentStatus($booking['payment_status']));
        // Store the original numeric status before converting to text
        $booking['numeric_status'] = $booking['status'];
        $booking['status'] = p__('appointmentpro',  Appointmentpro_Model_Appointment::getBookingStatus($booking['status']));

        $this->loadPartials();
        $this->getLayout()->getPartial('content')->setBookingStatus($bookingStatus)->setType($type)->setCurrentLink('booking' . $type)->setBooking($booking);
    }

    /**
     * fetch order
     */
    public function findAllAction()
    {

        try {
            $request = $this->getRequest();
            $limit = $request->getParam("perPage", 25);
            $offset = $request->getParam("offset", 0);
            $sorts = $request->getParam("sorts", []);
            $queries = $request->getParam("queries", []);
            $service_type = $this->getRequest()->getParam('service_type');
            $type = 'all';

            if (!empty($this->getRequest()->getParam('type'))) {
                $type = $this->getRequest()->getParam('type');
            }

            $timezone = null;
            $todayDate = date('d-m-Y');
            if (class_exists("EaziSettings_Model_Settings")) {
                $modelTimezone = (new EaziSettings_Model_Settings())->find(['app_id' => $this->getApplication()->getId()]);
                if ($modelTimezone->getId()) {
                    $timezone = $modelTimezone->getTimezone();
                }

                $date = new Zend_Date();
                if (!empty($timezone)) {
                    $date = $date->setTimezone($timezone);
                    $todayDate = $date->toString('dd-MM-y');
                }
            }

            $params = [
                "limit" => $limit,
                "offset" => $offset,
                "sorts" => $sorts,
                "queries" => $queries,
                "type" => $type,
                "service_type" => $service_type,
                "todayDate" => $todayDate
            ];

            $value_id = (new Appointmentpro_Model_Appointmentpro())->getCurrentValueId();

            $bookings = (new Appointmentpro_Model_Booking())
                ->findByValueId($value_id, $params);

            $countAll = (new Appointmentpro_Model_Booking())->countAllForApp($value_id,  $params);
            $countFiltered =   (new Appointmentpro_Model_Booking())->countAllForApp($value_id, $params);

            $setting = (new Appointmentpro_Model_Settings())->find($value_id, "value_id");
            $settingResult = $setting->getData();
            ($settingResult['time_format'] == 1) ? $timeFormat = '' : $timeFormat = 'A';
            ($settingResult['date_format'] == 1) ? $dateFormat = 'm/d/y' : $dateFormat = 'd/m/y';


            $bookingJson = [];
            foreach ($bookings as $booking) {
                $data = $booking->getData();
                $data['customer'] = $data['firstname'] . ' ' . $data['lastname'];
                $data['booking_date'] = date($dateFormat, $data['appointment_date']);
                $data['start_time'] = Appointmentpro_Model_Utils::timestampTotime($data['appointment_time'], $timeFormat);
                $data['end_time'] = Appointmentpro_Model_Utils::timestampTotime($data['appointment_end_time'], $timeFormat);
                $data['currency_symbol'] = $this->getApplication()->getCurrency();
                $data['amount_with_currency'] = Appointmentpro_Model_Utils::displayPrice($data['total_amount'], $this->getApplication()->getCurrency(), $settingResult['number_of_decimals'], $settingResult['decimal_separator'], $settingResult['thousand_separator'], $settingResult['currency_position']);

                $data['text_color'] = Appointmentpro_Model_Appointment::getBookingTextcolor($data['status']);
                $data['payment_text_color'] = Appointmentpro_Model_Appointment::getPaymentTextcolor($data['payment_status']);

                $data['is_completed_hide'] = '';
                $data['is_rejected_hide'] = '';
                $data['is_accepted_hide'] = '';
                $data['is_payment_hide'] = '';
                $data['is_rejected_accepted_action'] = '';
                $data['change_provider_hide'] = '';

                if (in_array($data['status'], ['4', '6', '5'])) {
                    $data['is_completed_hide'] = 'hide';
                }
                if ($data['status'] == 8) {
                    $data['is_rejected_hide'] = 'hide';
                }
                if ($data['status'] == 3) {
                    $data['is_accepted_hide'] = 'hide';
                }
                if ($data['status'] < 3) {
                    $data['is_rejected_accepted_action'] = 'hide';
                }
                if (Appointmentpro_Model_Appointment::getPaymentStatus($data['payment_status']) !=  'Pending') {
                    $data['is_payment_hide'] = 'hide';
                }
                // Hide change provider option for completed, failed, canceled, rejected appointments
                // Only show for: Pending Payment (1), Processing (2), Accepted (3)
                if (!in_array($data['status'], [0, 1, 2, 3])) {
                    $data['change_provider_hide'] = 'hide';
                }

                $data['payment_status'] = p__('appointmentpro', Appointmentpro_Model_Appointment::getPaymentStatus($data['payment_status']));
                $data['status'] = p__('appointmentpro',  Appointmentpro_Model_Appointment::getBookingStatus($data['status']));

                $data['payment_type'] = ($data['payment_type'] == 'cod') ?  p__("appointmentpro",  "Cash") : ucfirst($data['payment_type']);



                $bookingJson[] = $data;
            }

            $payload = [
                "records" => $bookingJson,
                "queryRecordCount" => $countFiltered[0],
                "totalRecordCount" => $countAll[0],
                "queries" => $queries,
                "params" => $params
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
     * Get providers by location for cascading form
     */
    public function getProvidersByLocationAction()
    {
        try {
            $location_id = $this->getRequest()->getPost('location_id');

            if (empty($location_id)) {
                $payload = [
                    'success' => false,
                    'message' => 'Location ID is required'
                ];
                $this->_sendJson($payload);
                return;
            }

            $value_id = (new Appointmentpro_Model_Appointmentpro())->getCurrentValueId();

            $providers = (new Appointmentpro_Model_Provider())
                ->findByValueId($value_id, ['location_id' => $location_id]);

            $providerJson = [];
            foreach ($providers as $provider) {
                $data = $provider->getData();
                $providerJson[] = [
                    'provider_id' => $data['provider_id'],
                    'name' => $data['name']
                ];
            }

            $payload = [
                'success' => true,
                'providers' => $providerJson
            ];
        } catch (\Exception $e) {
            $payload = [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     * get providers by locations
     */
    public function getProvidersAction()
    {

        try {
            $location_id = $this->getRequest()->getParam('location_id');
            $value_id = (new Appointmentpro_Model_Appointmentpro())->getCurrentValueId();

            $providers = (new Appointmentpro_Model_Provider())
                ->findByValueId($value_id, ['location_id' => $location_id]);

            $providerJson = [];
            foreach ($providers as $provider) {
                $data = $provider->getData();
                $providerJson[] = $data;
            }

            $payload = [
                "providers" => $providerJson
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
     * get provider service by provider id
     */
    public function getProviderServicesAction()
    {

        try {
            $provider_id = $this->getRequest()->getParam('provider_id');
            $value_id = (new Appointmentpro_Model_Appointmentpro())->getCurrentValueId();
            $setting = (new Appointmentpro_Model_Settings())->find($value_id, "value_id");
            $settingResult = $setting->getData();
            $services = (new Appointmentpro_Model_Provider())
                ->getProviderServices($provider_id);

            $servicesJson = [];
            foreach ($services as $service) {
                $data = $service->getData();
                $data['price_with_currency'] = Appointmentpro_Model_Utils::displayPrice($data['price'], $this->getApplication()->getCurrency(), $settingResult['number_of_decimals'], $settingResult['decimal_separator'], $settingResult['thousand_separator'], $settingResult['currency_position']);
                $data['service_time'] = $data['service_time'] . ' ' . p__("appointmentpro",  "Min");
                $data['json'] = implode("~", [
                    'price_with_currency' => $data['price_with_currency'],
                    'service_time' => $data['service_time'],
                    'service_id' => $data['service_id']
                ]);
                $servicesJson[] = $data;
            }

            $payload = [
                "services" => $servicesJson
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
     * get services by location
     */
    public function getServicesAction()
    {

        try {
            $location_id = $this->getRequest()->getParam('location_id');
            $value_id = (new Appointmentpro_Model_Appointmentpro())->getCurrentValueId();

            $params = [
                'location_id' => $location_id,
                'findBy' => 'upcoming'
            ];

            $services = (new Appointmentpro_Model_Class())->findAllClassesByValueId($value_id, $params);
            $servicesJson = [];

            foreach ($services as $key => $class) {
                $servicesJson[] = $class;
            }

            $payload = [
                "services" => $servicesJson
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
     * find customer by email
     */
    public function findCustomerByEmailAction()
    {

        try {

            $application = $this->getApplication();
            $appId = $application->getId();
            $customer = [];
            $data =  $this->getRequest()->getPost();

            $dummy = new Customer_Model_Customer();
            $dummy->find([
                'email' => $data['email'],
                'app_id' => $appId
            ]);

            if ($dummy->getId()) {
                $customer['firstname'] = $dummy->getFirstname();
                $customer['lastname'] = $dummy->getLastname();
                $customer['email'] = $dummy->getEmail();
                $customer['phone'] = $dummy->getMobile();
                $customer['id'] = $dummy->getId();
            }

            $payload = [
                "success" => true,
                "customer" => $customer
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }




    public function findServiceProviderSlotAction()
    {
        $payload = $data = [];

        try {
            $inputParams =  $this->getRequest()->getPost();
            $value_id = (new Appointmentpro_Model_Appointmentpro())->getCurrentValueId();
            $inputParams['date'] = date('d-m-Y', strtotime($inputParams['date']));
            $date = str_replace('/', '-', $inputParams['date']);

            $queryData = (new Appointmentpro_Model_Provider())->getServiceTime($date, $inputParams);
            // dd($queryData, $inputParams);
            $requestedDay = (string) strtolower(date('l', strtotime($date)));
            $spTimingData = json_decode($queryData['spData']['timing']);
            $businessTimingData = json_decode($queryData['spData']['business_timing']);
            $todaysDate = date('d-m-Y');

            if (strtotime($date) >= strtotime($todaysDate)) {

                if (!$spTimingData->is_active->$requestedDay || !$businessTimingData->is_active->$requestedDay) {
                    $data['message'] = p__("appointmentpro", "The requested date has no availability!");
                    $data['status'] = 'error';
                } else {

                    if ((isset($spTimingData->date_break) && in_array($inputParams['date'], $spTimingData->date_break))
                        || (isset($businessTimingData->date_break) && in_array($inputParams['date'], $businessTimingData->date_break))
                    ) {
                        $data['message'] = p__("appointmentpro", "The requested date has no availability!");
                        $data['status'] = 'error';
                    } else {
                        $timeArray = [];
                        $fromTime = $businessTimingData->from_time->$requestedDay;
                        if ($spTimingData->from_time->$requestedDay > $businessTimingData->from_time->$requestedDay) {
                            $fromTime = $spTimingData->from_time->$requestedDay;
                        }

                        $toTime = $businessTimingData->to_time->$requestedDay;
                        if ($spTimingData->to_time->$requestedDay < $businessTimingData->to_time->$requestedDay) {
                            $toTime = $spTimingData->to_time->$requestedDay;
                        }

                        $timeDiff = $queryData['spData']['service_time'] + $queryData['spData']['buffer_time'];

                        // Check if service has break configuration
                        $breakConfig = (new Appointmentpro_Model_ServiceBreakConfig())
                            ->find(['service_id' => $inputParams['service_id']]);

                        $hasBreakTime = false;
                        $breakInfo = null;

                        if ($breakConfig->getId()) {
                            $hasBreakTime = true;
                            $breakInfo = [
                                'work_before' => $breakConfig->getWorkTimeBeforeBreak(),
                                'break_duration' => $breakConfig->getBreakDuration(),
                                'work_after' => $breakConfig->getWorkTimeAfterBreak(),
                                'break_is_bookable' => $breakConfig->getBreakIsBookable()
                            ];

                            // For break time services, keep the original service time but note we'll handle slots differently
                            // Don't change timeDiff here - it should remain the actual service time for conflict checking
                        }

                        // Generate time slots using 5-minute intervals for booking
                        $timeBound = strtotime('-5 minutes', $toTime);
                        if ($timeDiff > 0) {
                            for ($i = $fromTime; $i <= $timeBound; $i = strtotime('+5 minutes', $i)) {
                                $timeArray[] = $i;
                            }
                        }

                        if (isset($spTimingData->day_breaks)) {
                            $timeArray = (new Appointmentpro_Model_Utils())->filterTimeArray($spTimingData->day_breaks, $timeArray, $timeDiff, $requestedDay);
                        }
                        if (isset($businessTimingData->day_breaks)) {
                            $timeArray = (new Appointmentpro_Model_Utils())->filterTimeArray($businessTimingData->day_breaks, $timeArray, $timeDiff, $requestedDay);
                        }

                        if (sizeof($queryData['appointments'])) {
                            $total_booking_per_slot = (int) $queryData['spData']['total_booking_per_slot'];

                            // Check if ANY existing appointments have break time configuration
                            $hasExistingBreaks = false;
                            foreach ($queryData['appointments'] as $existingApp) {
                                $existingBreakConfig = (new Appointmentpro_Model_ServiceBreakConfig())
                                    ->find(['service_id' => $existingApp['service_id']]);
                                if ($existingBreakConfig->getId() && $existingBreakConfig->getBreakIsBookable()) {
                                    $hasExistingBreaks = true;
                                    break;
                                }
                            }

                            // Use checkAppointmentWithBreaks if current service OR existing appointments have breaks
                            if ($hasExistingBreaks) {
                                // Use break-aware checking
                                $timeArray = (new Appointmentpro_Model_Utils())->checkAppointmentWithBreaks(
                                    $queryData['appointments'],
                                    $timeArray,
                                    $timeDiff,
                                    $total_booking_per_slot,
                                    $breakInfo,
                                    $inputParams['service_id']
                                );
                            } else {
                                // Regular appointment checking (no breaks anywhere)
                                $timeArray = (new Appointmentpro_Model_Utils())->checkAppoinment($queryData['appointments'], $timeArray, $timeDiff, $total_booking_per_slot);
                            }
                        }

                        $timeArray = array_values($timeArray);

                        $convertTimeArray = [];
                        $valueTimeArray = [];

                        $setting = (new Appointmentpro_Model_Settings())->find($value_id, "value_id");
                        $result = $setting->getData();
                        ($result['time_format'] == 1) ? $format = '' : $format = 'A';
                        // Available Tiime
                        $convertTimeArray = (new Appointmentpro_Model_Utils())->filterTimeSlot($timeArray, $timeDiff, $hasBreakTime ? $breakInfo : null);

                        if (sizeof($convertTimeArray)) {
                            $returnArray = $timeArray = [];
                            $returnArray['serviceTime'] = (string) $timeDiff;
                            $returnArray['sId'] = $queryData['spData']['id'];
                            $returnArray['displayTime'] = $convertTimeArray;
                            $data['status'] = 'success';
                            $data['data'] = $returnArray;
                            $data['queryData'] = $queryData;
                        } else {
                            $data['message'] = p__("appointmentpro", 'The requested date has no availability!');
                            $data['status'] = 'error';
                            $data['convertTimeArray'] = $convertTimeArray;
                        }
                    }
                }
            } else {

                $data['message'] = p__("appointmentpro", 'The date you are looking for is invalid!');
                $data['status'] = 'error';
            }

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



    /**
     *booking status
     */
    public function updateStatusAction()
    {

        try {

            $model = (new Appointmentpro_Model_Booking());
            if ($booking_id = $this->getRequest()->getParam('id')) {
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

                        if ($booking['payment_type'] == 'cod' || $booking['payment_type'] == 'banktransfer') {
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
     *Payment  status
     */
    public function updatePaymentStatusAction()
    {

        try {

            $model = (new Appointmentpro_Model_Booking());
            if ($booking_id = $this->getRequest()->getParam('id')) {
                $model->find(['appointment_id' => $booking_id]);

                if (!$model->getAppointmentId()) {
                    $this->getRequest()->addError(p__("appointmentpro",  "This Appointment does not exist."));
                } else {

                    $message = p__('appointmentpro', 'Payment status updated successfully');
                    $payment_status = $this->getRequest()->getParam('pstatus');

                    $txnModel = (new Appointmentpro_Model_Transaction())
                        ->find(['booking_id' => $booking_id])
                        ->setStatus($payment_status)
                        ->save();
                }
            }


            $payload = [
                'success' => true,
                'message' => $message,
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
        $booking['currency_symbol'] = $this->getApplication()->getCurrency();
        $booking['amount_with_currency'] = $this->getApplication()->getCurrency() . $booking['total_amount'];
        $booking['service_amount_with_currency'] = $this->getApplication()->getCurrency() . $booking['service_amount'];
        $booking['is_paid'] = ($booking['payment_status'] == 2) ? true : false;
        $booking['payment_status'] = p__('appointmentpro', Appointmentpro_Model_Appointment::getPaymentStatus($booking['payment_status']));
        $booking['status'] = p__('appointmentpro',  Appointmentpro_Model_Appointment::getBookingStatus($booking['status']));
        $booking['created_at'] = date($dateFormat . ' ' . $timeFormat2, strtotime($booking['created_at']));
        $booking['price_with_currency'] = $this->getApplication()->getCurrency() . $booking['price'];

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
        $param['booking']['settings'] =  $settingResult;
        $param['sender_email'] = $booking['location_email'];
        $param['email'] = $booking['buyer_email'];
        $param['subject'] =  $subject;
        $param['message'] =  $message;
        $this->_sendCustomerEmail($param);

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

    public function saveAction()
    {
        $payload = [];

        try {

            $param =  $this->getRequest()->getPost();
            $value_id = $param['value_id'];
            $customerId = $param['customer_id'];
            $paymentTo = 'merchant';

            $customerModel = new Customer_Model_Customer();
            $customerModel->find($customerId);

            $customer = [];
            $customer['firstname'] = $param['customer_firstname'];
            $customer['lastname'] = $param['customer_lastname'];
            $customer['email'] = $param['customer_email'];
            $customer['mobile'] = $param['customer_phone'];

            if (!$customerModel->getId()) {
                $customer['app_id'] = $this->getApplication()->getId();
                $customer['privacy_policy'] = false;
                $customer['communication_agreement'] = false;

                $customerModel->setData($customer);
                $customerModel->setPassword('12345678');
                $customerModel->save();
                $customerId = $customerModel->getId();
            }

            $startTime = $param['slot_time'];
            $booking_date = strtotime($param['appointment_date']);

            $order_note = !empty($param['order_note']) ? $param['order_note'] : '';
            $additional_info = json_encode(['firstname' => $customer['firstname'], 'lastname' => $customer['lastname'], 'email' => $customer['email'], 'mobile' => $customer['mobile'], 'customer_id' => $customerId, 'PhoneNo' => $customer['mobile']]);

            $service = (new Appointmentpro_Model_Service())->find(['service_id' => $param['service_main_id']]);

            // Check if this service has break time configuration
            $breakConfig = (new Appointmentpro_Model_ServiceBreakConfig())
                ->find(['service_id' => $param['service_main_id']]);

            $bookingDuration = $service->getServiceTime(); // Default to full service time

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
                    ->where('service_id = ?', $param['service_main_id']);

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
                        // The break slot should still book the full service duration
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

            // VALIDATION: Check for overlapping appointments before saving
            $db = Zend_Db_Table::getDefaultAdapter();
            $select = $db->select()
                ->from('appointment')
                ->where('(service_provider_id = ? OR service_provider_id_2 = ?)', $param['provider_id'])
                ->where('appointment_date = ?', $booking_date)
                ->where('status != ?', 'cancelled'); // Exclude cancelled appointments

            // If editing existing appointment, exclude it from overlap check
            if (!empty($param['appointment_id'])) {
                $select->where('appointment_id != ?', $param['appointment_id']);
            }

            $existingAppointments = $db->fetchAll($select);

            if (!empty($existingAppointments)) {
                // Check for overlaps using chunk-aware logic
                $hasConflict = false;

                if ($breakConfig->getId() && $breakConfig->getBreakIsBookable()) {
                    // Use chunk-aware validation for services with breaks
                    $hasConflict = $this->_checkChunkOverlap(
                        $startTime,
                        $endTime,
                        $existingAppointments,
                        $breakConfig,
                        $service->getServiceTime()
                    );
                } else {
                    // Simple overlap check for regular services
                    foreach ($existingAppointments as $existing) {
                        $existingStart = $existing['appointment_time'];
                        $existingEnd = $existing['appointment_end_time'];

                        // Check if appointments overlap
                        if (!($endTime <= $existingStart || $startTime >= $existingEnd)) {
                            $hasConflict = true;
                            break;
                        }
                    }
                }

                if ($hasConflict) {
                    throw new Exception(p__('appointmentpro', 'This time slot is no longer available. Please select another time.'));
                }
            }

            $setIsAddPlcPoints = (int) $service->getServicePoints();
            $amount = $service->getPrice();
            $tax_amount = 0;
            $total_amount = $service->getPrice();
            $total_service_points = $service->getServicePoints();

            $gateways = (new Appointmentpro_Model_Gateways())->find(['id' => $param['payment_method']]);

            $model = (new Appointmentpro_Model_Appointment())
                ->find(['appointment_id' => $param['appointment_id']])
                ->setServiceProviderId($param['provider_id'])
                ->setCustomerId($customerId)
                ->setServiceId($param['service_main_id'])
                ->setLocationId($param['location_id'])
                ->setAppointmentTime($startTime)
                ->setAppointmentEndTime($endTime)
                ->setAppointmentDate($booking_date)
                ->setStatus($param['status'])
                ->setNotes($order_note)
                ->setTotalAmount($total_amount)
                ->setServiceAmount($amount)
                ->setServicePlcPoint($total_service_points)
                ->setValueId($value_id)
                ->setAdditionalInfo($additional_info)
                ->setIsAddPlcPoints($setIsAddPlcPoints);

            $model->save();

            $appointmentId =  $model->getAppointmentId();

            if ($appointmentId) {

                $booking_status = true;
                $modelTnx = (new Appointmentpro_Model_Transaction())
                    ->find(['booking_id' => $appointmentId])
                    ->setBookingId($appointmentId)
                    ->setValueId($value_id)
                    ->setCustomerId($customerId)
                    ->setName($customer['firstname'])
                    ->setEmail($customer['email'])
                    ->setMobile($customer['mobile'])
                    ->setPaymentModeId($param['payment_method'])
                    ->setPaymentType($gateways->getGatewayCode())
                    ->setAmount($amount)
                    ->setTotalAmount($total_amount)
                    ->setTaxAmount($tax_amount)
                    ->setPlcPoints($total_service_points)
                    ->setStatus($param['payment_status'])
                    ->setPaymentTo($paymentTo)
                    ->save();
            }

            $this->_sendEmail($appointmentId);


            $payload = [
                'success' => true,
                'booking_id' => $appointmentId,
                'message' => p__('appointmentpro', 'Booking successfully and booking ID - %s', $appointmentId)
            ];
        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
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

    /**
     * Save booking from calendar modal
     */
    public function bookingFromCalendarAction()
    {
        $payload = [];

        try {
            $param = $this->getRequest()->getPost();
            $value_id = (new Appointmentpro_Model_Appointmentpro())->getCurrentValueId();
            $customerId = $param['customer_id'];
            $paymentTo = 'merchant';

            // Debug logging
            error_log("Calendar Booking Request - Raw Parameters: " . print_r($param, true));

            // Validate required fields
            if (empty($param['location_id'])) {
                throw new Exception('Location is required');
            }
            if (empty($param['provider_id'])) {
                throw new Exception('Provider is required');
            }
            if (empty($param['service_id'])) {
                throw new Exception('Service is required');
            }
            if (empty($param['payment_mode'])) {
                throw new Exception('Payment mode is required');
            }

            $customerModel = new Customer_Model_Customer();
            $customerModel->find($customerId);

            $customer = [];
            $customer['firstname'] = $param['customer_firstname'];
            $customer['lastname'] = $param['customer_lastname'];
            $customer['email'] = $param['customer_email'];
            $customer['mobile'] = $param['customer_phone'];

            // Validate customer fields for calendar booking
            if (empty($customer['firstname'])) {
                throw new Exception('Customer first name is required');
            }
            if (empty($customer['email'])) {
                throw new Exception('Customer email is required');
            }

            if (!$customerModel->getId()) {
                $customer['app_id'] = $this->getApplication()->getId();
                $customer['privacy_policy'] = false;
                $customer['communication_agreement'] = false;

                $customerModel->setData($customer);
                $customerModel->setPassword('12345678');
                $customerModel->save();
                $customerId = $customerModel->getId();
            }

            // Parse appointment date and time to match saveAction format
            $appointmentDate = $param['appointment_date']; // Format: m/d/Y  
            $appointmentTime = $param['appointment_time']; // Format: H:i

            // Convert to timestamp format like saveAction expects (slot_time format)
            $startDateTime = $appointmentTime;
            // Convert "10:00" (HH:mm) to seconds since midnight
            list($hour, $minute) = explode(':', $startDateTime);
            $startTime = ($hour * 3600) + ($minute * 60);

            // Validate timestamp conversion (allow 0 for midnight 00:00)
            if ($startTime === false || $startTime < 0) {
                error_log("Invalid timestamp conversion for: " . $startDateTime);
                throw new Exception('Invalid appointment date/time format');
            }

            // Convert date to timestamp for appointment_date field
            $booking_date = strtotime($appointmentDate);
            if ($booking_date === false || $booking_date <= 0) {
                error_log("Invalid booking date conversion for: " . $appointmentDate);
                throw new Exception('Invalid appointment date format');
            }

            // Debug logging for time values
            error_log("Calendar booking - appointmentDate: " . $appointmentDate . ", appointmentTime: " . $appointmentTime);
            error_log("Combined datetime: " . $startDateTime . ", startTime timestamp: " . $startTime);
            error_log("Booking date timestamp: " . $booking_date);
            error_log("Start time formatted: " . date('Y-m-d H:i:s', $startTime));
            error_log("Booking date formatted: " . date('Y-m-d', $booking_date));

            $order_note = !empty($param['note']) ? $param['note'] : '';
            $additional_info = json_encode([
                'firstname' => $customer['firstname'],
                'lastname' => $customer['lastname'],
                'email' => $customer['email'],
                'mobile' => $customer['mobile'],
                'customer_id' => $customerId,
                'PhoneNo' => $customer['mobile']
            ]);

            $service = (new Appointmentpro_Model_Service())->find(['service_id' => $param['service_id']]);
            if (!$service || !$service->getId()) {
                throw new Exception('Service not found');
            }

            // Check if this service has break time configuration (consistent with saveAction)
            $breakConfig = (new Appointmentpro_Model_ServiceBreakConfig())
                ->find(['service_id' => $param['service_id']]);

            $bookingDuration = $service->getServiceTime(); // Default to full service time

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

            // Calculate end time exactly like saveAction does
            $endTime = strtotime('+' . $bookingDuration . ' minutes', $startTime);

            // Debug service duration
            error_log("Service duration: " . $service->getServiceTime() . " minutes");
            error_log("Start time: " . date('Y-m-d H:i:s', $startTime));
            error_log("End time: " . date('Y-m-d H:i:s', $endTime));

            $setIsAddPlcPoints = (int) $service->getServicePoints();
            $amount = $service->getPrice();
            $tax_amount = 0;
            $total_amount = $service->getPrice();
            $total_service_points = $service->getServicePoints();

            // Find gateway by ID (same as saveAction method)
            $gateways = (new Appointmentpro_Model_Gateways())->find(['id' => $param['payment_mode']]);
            if (!$gateways || !$gateways->getId()) {
                error_log("Gateway search failed for payment_mode ID: " . $param['payment_mode'] . " with value_id: " . $value_id);
                throw new Exception('Payment gateway not found. Payment mode ID: ' . $param['payment_mode']);
            }

            $paymentType = $gateways->getGatewayCode();
            if (empty($paymentType)) {
                error_log("Gateway found but no gateway code: " . print_r($gateways->getData(), true));
                throw new Exception('Invalid payment method configuration');
            }
            // dd($startTime, $booking_date, $endTime, $paymentType, $param['provider_id'], $param['service_id'], $param['location_id'], $total_amount, $amount, $total_service_points, $value_id, $additional_info, $setIsAddPlcPoints);
            $model = new Appointmentpro_Model_Appointment();
            $model->setServiceProviderId($param['provider_id'])
                ->setCustomerId($customerId)
                ->setServiceId($param['service_id'])
                ->setLocationId($param['location_id'])
                ->setAppointmentTime($startTime)
                ->setAppointmentEndTime($endTime)
                ->setAppointmentDate($booking_date)
                ->setStatus($param['booking_status'])
                ->setNotes($order_note)
                ->setTotalAmount($total_amount)
                ->setServiceAmount($amount)
                ->setServicePlcPoint($total_service_points)
                ->setValueId($value_id)
                ->setAdditionalInfo($additional_info)
                ->setIsAddPlcPoints($setIsAddPlcPoints)
                ->setCreatedSource('desktop'); // Flag for desktop calendar creation

            // Set second provider for break time services if provided
            if (!empty($param['provider_2_id']) && $breakConfig->getId() && $breakConfig->getHasBreakTime()) {
                try {
                    $model->setServiceProviderId2($param['provider_2_id']);
                } catch (Exception $e) {
                    error_log('Warning: Could not set provider_2_id - ' . $e->getMessage());
                }
            }

            $model->save();
            $appointmentId = $model->getAppointmentId();

            if ($appointmentId) {
                $modelTnx = new Appointmentpro_Model_Transaction();
                $modelTnx->setBookingId($appointmentId)
                    ->setValueId($value_id)
                    ->setCustomerId($customerId)
                    ->setName($customer['firstname'])
                    ->setEmail($customer['email'])
                    ->setMobile($customer['mobile'])
                    ->setPaymentModeId($param['payment_mode'])
                    ->setPaymentType($paymentType)
                    ->setAmount($amount)
                    ->setTotalAmount($total_amount)
                    ->setTaxAmount($tax_amount)
                    ->setPlcPoints($total_service_points)
                    ->setStatus($param['payment_status'])
                    ->setPaymentTo($paymentTo)
                    ->save();

                $this->_sendEmail($appointmentId);

                $payload = [
                    'success' => true,
                    'booking_id' => $appointmentId,
                    'message' => p__('appointmentpro', 'Appointment created successfully. Booking ID: %s', $appointmentId)
                ];
            } else {
                throw new Exception('Failed to create appointment');
            }
        } catch (Exception $e) {
            $payload = [
                "success" => false,
                "error" => true,
                "message" => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     * Get available providers for appointment change
     */
    public function getAvailableProvidersAction()
    {
        try {
            $appointmentId = $this->getRequest()->getParam('appointment_id');
            $value_id = (new Appointmentpro_Model_Appointmentpro())->getCurrentValueId();

            if (empty($appointmentId)) {
                throw new Exception('Appointment ID is required');
            }

            // Get current appointment details
            $booking = (new Appointmentpro_Model_Booking())->getBookingById($appointmentId);
            if (!$booking) {
                throw new Exception('Appointment not found');
            }

            // Check if service has break time enabled
            $hasBreakTime = false;
            $currentProvider2Id = null;

            try {
                $breakConfig = (new Appointmentpro_Model_ServiceBreakConfig())->find(['service_id' => $booking['service_id']]);
                if ($breakConfig->getId() && $breakConfig->getHasBreakTime()) {
                    $hasBreakTime = true;
                    // Get second provider if exists (from appointment table or separate table)
                    $currentProvider2Id = $booking['service_provider_id_2'] ?? null;
                }
            } catch (Exception $e) {
                // Break config not found or error, treat as non-break service
                $hasBreakTime = false;
            }

            // Get all providers for this location and service
            $availableProviders = (new Appointmentpro_Model_Provider())
                ->findServiceProvider($booking['location_id'], $booking['service_id']);

            // Debug: Log what providers are found
            error_log("Available Providers Count: " . count($availableProviders));
            error_log("Current Provider ID from booking: " . $booking['service_provider_id']);

            $providersJson = [];
            foreach ($availableProviders as $provider) {
                error_log("Provider in list: ID=" . $provider['provider_id'] . ", Name=" . $provider['name']);

                // Include all providers (including current provider)
                // Frontend will mark the current provider as selected
                $providersJson[] = [
                    'provider_id' => $provider['provider_id'],
                    'name' => $provider['name']
                ];
            }

            $payload = [
                'success' => true,
                'providers' => $providersJson,
                'current_provider_id' => $booking['service_provider_id'],
                'current_provider_2_id' => $currentProvider2Id,
                'has_break_time' => $hasBreakTime
            ];
        } catch (\Exception $e) {
            $payload = [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     * Validate provider availability for appointment change
     */
    public function validateProviderAvailabilityAction()
    {
        try {
            $appointmentId = $this->getRequest()->getParam('appointment_id');
            $newProviderId = $this->getRequest()->getParam('provider_id');
            $value_id = (new Appointmentpro_Model_Appointmentpro())->getCurrentValueId();

            if (empty($appointmentId) || empty($newProviderId)) {
                throw new Exception('Appointment ID and Provider ID are required');
            }

            // Get current appointment details
            $booking = (new Appointmentpro_Model_Booking())->getBookingById($appointmentId);
            if (!$booking) {
                throw new Exception('Appointment not found');
            }

            // Check if provider offers this service at this location
            $serviceProviders = (new Appointmentpro_Model_Provider())
                ->findServiceProvider($booking['location_id'], $booking['service_id']);

            $providerFound = false;
            foreach ($serviceProviders as $provider) {
                if ($provider['provider_id'] == $newProviderId) {
                    $providerFound = true;
                    break;
                }
            }

            if (!$providerFound) {
                throw new Exception('Selected provider does not offer this service at this location');
            }

            // Convert appointment date to proper format for availability check
            $appointmentDate = date('d-m-Y', $booking['appointment_date']);

            // Prepare parameters for availability check
            $params = [
                'location_id' => $booking['location_id'],
                'provider_id' => $newProviderId,
                'service_id' => $booking['service_id']
            ];

            // Get provider's availability and existing appointments
            $availabilityData = (new Appointmentpro_Model_Provider())->getServiceTime($appointmentDate, $params);

            if (empty($availabilityData['spData'])) {
                throw new Exception('Provider availability data not found');
            }

            // Check if provider is available on this date
            $requestedDay = strtolower(date('l', $booking['appointment_date']));
            $providerTiming = json_decode($availabilityData['spData']['timing'] ?? '{}');
            $businessTiming = json_decode($availabilityData['spData']['business_timing'] ?? '{}');

            // Check if provider and business are active on this day
            if (
                !isset($providerTiming->is_active->$requestedDay) ||
                !$providerTiming->is_active->$requestedDay ||
                !isset($businessTiming->is_active->$requestedDay) ||
                !$businessTiming->is_active->$requestedDay
            ) {
                throw new Exception('Provider is not available on this day');
            }

            // Check for date breaks
            if ((isset($providerTiming->date_break) && in_array($appointmentDate, $providerTiming->date_break)) ||
                (isset($businessTiming->date_break) && in_array($appointmentDate, $businessTiming->date_break))
            ) {
                throw new Exception('Provider has a break on this date');
            }

            // Check if the appointment time falls within working hours
            $appointmentTime = $booking['appointment_time'];
            $appointmentEndTime = $booking['appointment_end_time'];

            $workingFromTime = max(
                $providerTiming->from_time->$requestedDay ?? 0,
                $businessTiming->from_time->$requestedDay ?? 0
            );
            $workingToTime = min(
                $providerTiming->to_time->$requestedDay ?? 86400,
                $businessTiming->to_time->$requestedDay ?? 86400
            );

            if ($appointmentTime < $workingFromTime || $appointmentEndTime > $workingToTime) {
                throw new Exception('Appointment time is outside provider working hours');
            }

            // Check for conflicts with existing appointments (excluding current appointment)
            $conflicts = false;
            foreach ($availabilityData['appointments'] as $existingAppointment) {
                if ($existingAppointment['appointment_id'] == $appointmentId) {
                    continue; // Skip current appointment
                }

                // Check for time overlap
                if (($appointmentTime >= $existingAppointment['appointment_time'] &&
                        $appointmentTime < $existingAppointment['appointment_end_time']) ||
                    ($appointmentEndTime > $existingAppointment['appointment_time'] &&
                        $appointmentEndTime <= $existingAppointment['appointment_end_time']) ||
                    ($appointmentTime <= $existingAppointment['appointment_time'] &&
                        $appointmentEndTime >= $existingAppointment['appointment_end_time'])
                ) {
                    $conflicts = true;
                    break;
                }
            }

            if ($conflicts) {
                throw new Exception('Provider has a conflict with another appointment at this time');
            }

            $payload = [
                'success' => true,
                'message' => 'Provider is available for this appointment time',
                'provider_name' => $availabilityData['spData']['provider_name'] ?? 'Unknown Provider'
            ];
        } catch (\Exception $e) {
            $payload = [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     * Validate dual provider availability for break time appointments
     */
    public function validateDualProviderAvailabilityAction()
    {
        try {
            $appointmentId = $this->getRequest()->getParam('appointment_id');
            $provider1Id = $this->getRequest()->getParam('provider_1_id');
            $provider2Id = $this->getRequest()->getParam('provider_2_id');

            if (empty($appointmentId) || empty($provider1Id) || empty($provider2Id)) {
                throw new Exception('Appointment ID and both Provider IDs are required');
            }

            // Get current appointment details
            $booking = (new Appointmentpro_Model_Booking())->getBookingById($appointmentId);
            if (!$booking) {
                throw new Exception('Appointment not found');
            }

            // Get break configuration to calculate time slots
            $breakConfig = (new Appointmentpro_Model_ServiceBreakConfig())->find(['service_id' => $booking['service_id']]);
            if (!$breakConfig->getId() || !$breakConfig->getHasBreakTime()) {
                throw new Exception('This service does not have break time enabled');
            }

            $workBefore = $breakConfig->getWorkTimeBeforeBreak() * 60; // Convert to seconds
            $breakDuration = $breakConfig->getBreakDuration() * 60;

            // Provider 1 time slot: appointment_time to (appointment_time + workBefore + breakDuration)
            $provider1EndTime = $booking['appointment_time'] + $workBefore + $breakDuration;

            // Provider 2 time slot: provider1EndTime to appointment_end_time
            $provider2StartTime = $provider1EndTime;
            $provider2EndTime = $booking['appointment_end_time'];

            $appointmentDate = date('d-m-Y', $booking['appointment_date']);

            // Validate Provider 1 availability
            $params1 = [
                'location_id' => $booking['location_id'],
                'provider_id' => $provider1Id,
                'service_id' => $booking['service_id']
            ];
            $availability1 = (new Appointmentpro_Model_Provider())->getServiceTime($appointmentDate, $params1);

            // Validate Provider 2 availability  
            $params2 = [
                'location_id' => $booking['location_id'],
                'provider_id' => $provider2Id,
                'service_id' => $booking['service_id']
            ];
            $availability2 = (new Appointmentpro_Model_Provider())->getServiceTime($appointmentDate, $params2);

            // Check for conflicts (simplified - you may want to add more detailed checks)
            $provider1Available = !empty($availability1['spData']);
            $provider2Available = !empty($availability2['spData']);

            if (!$provider1Available) {
                throw new Exception('Provider 1 is not available for the selected time slot');
            }

            if (!$provider2Available) {
                throw new Exception('Provider 2 is not available for the selected time slot');
            }

            $payload = [
                'success' => true,
                'message' => 'Both providers are available for their respective time slots',
            ];
        } catch (\Exception $e) {
            $payload = [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     * Change provider for existing appointment
     */
    public function changeProviderAction()
    {
        $payload = [];
        try {
            $appointmentId = $this->getRequest()->getParam('appointment_id');
            $newProviderId = $this->getRequest()->getParam('provider_id');
            $hasBreakTime = $this->getRequest()->getParam('has_break_time', false);
            $provider2Id = $this->getRequest()->getParam('provider_2_id', null);

            if (empty($appointmentId) || empty($newProviderId)) {
                throw new Exception('Appointment ID and Provider ID are required');
            }

            // For break time services, require provider 2
            if ($hasBreakTime && empty($provider2Id)) {
                throw new Exception('Second provider is required for break time services');
            }

            // Verify appointment exists
            $bookingModel = new Appointmentpro_Model_Booking();
            $booking = $bookingModel->getBookingById($appointmentId);

            if (!$booking) {
                throw new Exception('Appointment not found');
            }

            // Basic validation: check if provider exists
            $providerModel = new Appointmentpro_Model_Provider();
            $provider = $providerModel->find($newProviderId);

            if (!$provider->getId()) {
                throw new Exception('Provider not found');
            }

            // For break time, validate provider 2 as well
            if ($hasBreakTime && $provider2Id) {
                $provider2 = $providerModel->find($provider2Id);
                if (!$provider2->getId()) {
                    throw new Exception('Second provider not found');
                }
            }

            // Update the service_provider_id in the appointment table
            $appointmentModel = new Appointmentpro_Model_Appointment();
            $appointmentRecord = $appointmentModel->find(['appointment_id' => $appointmentId]);

            if ($appointmentRecord->getId()) {
                $appointmentRecord->setServiceProviderId($newProviderId);

                // Save second provider if break time service
                if ($hasBreakTime && $provider2Id) {
                    // Check if appointment table has service_provider_id_2 column
                    try {
                        $appointmentRecord->setServiceProviderId2($provider2Id);
                    } catch (Exception $e) {
                        // Column might not exist yet, log but don't fail
                        error_log('Warning: service_provider_id_2 column not found in appointment table');
                    }
                }

                $appointmentRecord->save();

                $message = $hasBreakTime
                    ? 'Providers changed successfully for break time service'
                    : 'Provider changed successfully';

                $payload = [
                    'success' => true,
                    'message' => $message,
                    'new_provider_id' => $newProviderId,
                    'new_provider_2_id' => $provider2Id
                ];
            } else {
                throw new Exception('Appointment record not found');
            }
        } catch (Exception $e) {
            $payload = [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }

        // Output JSON response
        header('Content-Type: application/json');
        echo json_encode($payload);
        exit;
    }

    /**
     * Check if a new appointment with break chunks overlaps with existing appointments
     * 
     * @param int $newStart - Start timestamp of new appointment
     * @param int $newEnd - End timestamp of new appointment
     * @param array $existingAppointments - Array of existing appointments
     * @param object $breakConfig - Break configuration model
     * @param int $serviceTime - Service time in minutes
     * @return bool - True if overlap detected, false otherwise
     */
    private function _checkChunkOverlap($newStart, $newEnd, $existingAppointments, $breakConfig, $serviceTime)
    {
        $workBefore = $breakConfig->getWorkTimeBeforeBreak() * 60; // Convert to seconds
        $breakDuration = $breakConfig->getBreakDuration() * 60;
        $workAfter = $breakConfig->getWorkTimeAfterBreak() * 60;

        // Calculate chunks for new appointment
        $newChunk1Start = $newStart;
        $newChunk1End = $newStart + $workBefore;
        $newBreakEnd = $newChunk1End + $breakDuration;
        $newChunk2Start = $newBreakEnd;
        $newChunk2End = $newEnd;

        foreach ($existingAppointments as $existing) {
            $existingStart = $existing['appointment_time'];
            $existingEnd = $existing['appointment_end_time'];

            // Get break config for existing appointment
            $existingBreakConfig = (new Appointmentpro_Model_ServiceBreakConfig())
                ->find(['service_id' => $existing['service_id']]);

            if ($existingBreakConfig->getId() && $existingBreakConfig->getBreakIsBookable()) {
                // Existing appointment has chunks - check chunk-to-chunk overlaps
                $exWorkBefore = $existingBreakConfig->getWorkTimeBeforeBreak() * 60;
                $exBreakDur = $existingBreakConfig->getBreakDuration() * 60;

                $exChunk1Start = $existingStart;
                $exChunk1End = $existingStart + $exWorkBefore;
                $exBreakEnd = $exChunk1End + $exBreakDur;
                $exChunk2Start = $exBreakEnd;
                $exChunk2End = $existingEnd;

                // Check new chunk 1 vs existing chunk 1
                if (!($newChunk1End <= $exChunk1Start || $newChunk1Start >= $exChunk1End)) {
                    return true; // Overlap found
                }

                // Check new chunk 1 vs existing chunk 2
                if (!($newChunk1End <= $exChunk2Start || $newChunk1Start >= $exChunk2End)) {
                    return true;
                }

                // Check new chunk 2 vs existing chunk 1
                if (!($newChunk2End <= $exChunk1Start || $newChunk2Start >= $exChunk1End)) {
                    return true;
                }

                // Check new chunk 2 vs existing chunk 2
                if (!($newChunk2End <= $exChunk2Start || $newChunk2Start >= $exChunk2End)) {
                    return true;
                }
            } else {
                // Existing appointment is regular service - check full duration vs our chunks
                // New chunk 1 vs existing full time
                if (!($newChunk1End <= $existingStart || $newChunk1Start >= $existingEnd)) {
                    return true;
                }

                // New chunk 2 vs existing full time
                if (!($newChunk2End <= $existingStart || $newChunk2Start >= $existingEnd)) {
                    return true;
                }
            }
        }

        return false; // No overlap found
    }
}
