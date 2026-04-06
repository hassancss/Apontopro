<?php

/**
 * Class Appointmentpro_CalendarController
 */
class Appointmentpro_CalendarController extends Application_Controller_Default
{

    /**
     * screen
     */
    public function listAction()
    {

        $location_id = $this->getRequest()->getParam('location');
        $value_id = (new Appointmentpro_Model_Appointmentpro())->getCurrentValueId();
        $bookingJson = [];
        $providersJson = [];
        $location = [];

        $locations = (new Appointmentpro_Model_Location())
            ->findByValueId($value_id, []);

        if (empty($location_id)) {
            foreach ($locations as $defaultLocation) {
                $location_id = $defaultLocation->getLocationId();
                break;
            }
        }

        $setting = (new Appointmentpro_Model_Settings())->find($value_id, "value_id");
        $settingResult = $setting->getData();

        if (!empty($location_id) && $location_id > 0) {

            $times = Appointmentpro_Model_Utils::timeOptions();
            $location = (new Appointmentpro_Model_Location())->findById($location_id);
            $location['business_timing'] =  Siberian_Json::decode($location['business_timing']);
            $fromTime = $location['business_timing']['from_time'];
            $toTime = $location['business_timing']['to_time'];
            $location['fromTime'] = min($fromTime);
            $location['toTime'] = max($toTime);
            $location['fromTime'] = date("H", strtotime($times[$location['fromTime']]));
            $location['toTime'] = date("H", strtotime($times[$location['toTime']]));
            $location['is_business_timing'] = (bool)count($location['business_timing']['from_time']);

            $params = [
                "location_id" => $location_id,
                "service_type" => 'services',
                "appointment_start_date" => strtotime(date('d-m-Y', strtotime("-7 days"))),
                "appointment_end_date" => strtotime(date('d-m-Y', strtotime("+30 days"))),
                "chunk_mode" => true  // Enable chunk mode to load break configuration
            ];


            $providers = (new Appointmentpro_Model_Provider())
                ->findByValueId($value_id, ['location_id' => $location_id]);

            $bookings = (new Appointmentpro_Model_Booking())
                ->findByLocationIdActiveBooking($value_id, $params);

            foreach ($bookings as $booking) {

                $booking = $booking->getData();
                // dd($booking);
                $booking['payment_type'] = ($booking['payment_type'] == 'cod') ?  p__("appointmentpro",  "Cash") : ucfirst($booking['payment_type']);
                $booking['apttime'] = date('F d, Y', $booking['appointment_date']) . ' ' . trim(Appointmentpro_Model_Utils::timestampTotime($booking['appointment_time'])) . ':00';
                $booking['aptendtime'] = date('F d, Y', $booking['appointment_date']) . ' ' . trim(Appointmentpro_Model_Utils::timestampTotime($booking['appointment_end_time'])) . ':00';

                $booking['title'] = '# ' . $booking['appointment_id'] . '<br><i class="fa fa-user-o" aria-hidden="true"></i> ' . $booking['firstname'] . ' ' . $booking['lastname'] . '<br>&nbsp;<i class="fa fa-tag"></i>' . $booking['service_name'] . '<br><i class="fa fa-first-order" aria-hidden="true"></i> ' . p__('appointmentpro',  Appointmentpro_Model_Appointment::getBookingStatus($booking['status'])) . '<br><i class="fa fa-tags" aria-hidden="true"></i>' . $booking['amount_with_currency'] = $this->getApplication()->getCurrency() . ' ' . $booking['total_amount'] . ' / ' . p__('appointmentpro', Appointmentpro_Model_Appointment::getPaymentStatus($booking['payment_status'])) . ' / ' . $booking['payment_type'];

                $booking['is_completed_hide'] = '';
                $booking['is_rejected_hide'] = '';
                $booking['is_accepted_hide'] = '';
                $booking['is_rejected_accepted_action'] = '';

                if (in_array($booking['status'], ['4', '6', '5'])) {
                    $booking['is_completed_hide'] = 'hide';
                }
                if ($booking['status'] == 8) {
                    $booking['is_rejected_hide'] = 'hide';
                }
                if ($booking['status'] == 3) {
                    $booking['is_accepted_hide'] = 'hide';
                }
                if ($booking['status'] < 3) {
                    $booking['is_rejected_accepted_action'] = 'hide';
                }
                $booking['total_bookings'] = (int) $booking['total_bookings'];
                $bookingJson[] = $booking;
            }
            // dd($providers);
            foreach ($providers as $key => $provider) {
                $provider = $provider->getData();
                $providersJson[] = $provider;
            }
        }

        $this->loadPartials();
        $this->getLayout()->getPartial('content')->setBookings($bookingJson)->setProvider($providersJson)->setLocations($locations)->setLocationId($location_id)->setCurrentLocation($location)->setSettings($settingResult);
    }
}