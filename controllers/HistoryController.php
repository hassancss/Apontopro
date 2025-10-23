<?php

use Stripe\Stripe;

/**
 * Class Appointmentpro_BookingController
 */
class Appointmentpro_HistoryController extends Application_Controller_Default
{

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
        $app_id = $this->getApplication()->getId();
        $value_id = (new Appointmentpro_Model_Appointmentpro())->getCurrentValueId();
        $customers = (new Customer_Model_Customer())->findAll(['app_id' => $app_id]);
        $services = (new Appointmentpro_Model_Service())
            ->findByValueId($value_id, []);
        $locations = (new Appointmentpro_Model_Location())
            ->findByValueId($value_id, []);


        $this->loadPartials();
        $this->getLayout()->getPartial('content')->setBookingStatus($bookingStatus)->setType($type)->setCurrentLink('historyall')->setLocations($locations)->setCustomers($customers)->setServices($services);
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
            $app_id = $this->getApplication()->getId();
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
                "customer_id" => $queries['customer_id'] ?? 'all',
                "service_id" => $queries['service_id'] ?? 'all',
                "type" => $type,
                "service_type" => $service_type,
                "todayDate" => $todayDate
            ];

            $value_id = (new Appointmentpro_Model_Appointmentpro())->getCurrentValueId();

            $bookings = (new Appointmentpro_Model_Booking())->findHistoryByValueId($value_id, $params);

            $countAll = (new Appointmentpro_Model_Booking())->countAllForApp($value_id,  $params);
            $countFiltered =   (new Appointmentpro_Model_Booking())->countAllForApp($value_id, $params);

            $setting = (new Appointmentpro_Model_Settings())->find($value_id, "value_id");
            $settingResult = $setting->getData();
            ($settingResult['time_format'] == 1) ? $timeFormat = '' : $timeFormat = 'A';
            ($settingResult['date_format'] == 1) ? $dateFormat = 'm/d/y' : $dateFormat = 'd/m/y';


            $bookingJson = [];
                   
            foreach ($bookings as $booking) {
                $data = $booking;
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

                $data['payment_status'] = p__('appointmentpro', Appointmentpro_Model_Appointment::getPaymentStatus($data['payment_status']));
                $data['status'] = p__('appointmentpro',  Appointmentpro_Model_Appointment::getBookingStatus($data['status']));

                $data['payment_type'] = ($data['payment_type'] == 'cod') ?  p__("appointmentpro",  "Cash") : ucfirst($data['payment_type']);



                $bookingJson[] = $data;
            }

            $totalCount = (int) ($countAll[0] ?? 0);
            $filteredCount = (int) ($countFiltered[0] ?? $totalCount);

            $payload = [
                "bookings" => $bookings,
                "records" => $bookingJson,
                "queryRecordCount" => $filteredCount,
                "totalRecordCount" => $totalCount,
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
}
