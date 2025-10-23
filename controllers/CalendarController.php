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

        $setting = (new Appointmentpro_Model_Settings())->find($value_id, "value_id");
        $settingResult = $setting->getData();

        if (!empty($location_id) && $location_id > 0) {

            $times = Appointmentpro_Model_Utils::timeOptions();
            $location = (new Appointmentpro_Model_Location())->findById($location_id);
            $location['business_timing'] = Siberian_Json::decode($location['business_timing']);

            if (!is_array($location['business_timing'])) {
                $location['business_timing'] = [];
            }

            $normalizedTiming = $this->normalizeBusinessTiming($location['business_timing']);
            $businessHourSummary = $this->buildBusinessHoursSummary($normalizedTiming, $times);

            $location['business_timing'] = $normalizedTiming;
            $location['business_hours_by_day'] = $businessHourSummary['by_day'];
            $location['fromTime'] = $businessHourSummary['min_hour'];
            $location['toTime'] = $businessHourSummary['max_hour'];
            $location['is_business_timing'] = $businessHourSummary['has_active'];

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
        $this->getLayout()->getPartial('content')
            ->setBookings($bookingJson)
            ->setProvider($providersJson)
            ->setLocations($locations)
            ->setLocationId($location_id)
            ->setCurrentLocation($location)
            ->setSettings($settingResult);
    }

    /**
     * Ensure timing array has all expected keys and integer values.
     *
     * @param array $businessTiming
     * @return array
     */
    private function normalizeBusinessTiming(array $businessTiming)
    {
        $defaultStructure = [
            'from_time' => [],
            'to_time' => [],
            'is_active' => [],
        ];

        $businessTiming = array_merge($defaultStructure, $businessTiming);

        foreach ($businessTiming['from_time'] as $day => $value) {
            $businessTiming['from_time'][$day] = (int)$value;
        }

        foreach ($businessTiming['to_time'] as $day => $value) {
            $businessTiming['to_time'][$day] = (int)$value;
        }

        foreach ($businessTiming['is_active'] as $day => $value) {
            $businessTiming['is_active'][$day] = (int)$value;
        }

        return $businessTiming;
    }

    /**
     * Build a summary of business hours per weekday with helper metadata.
     *
     * @param array $businessTiming
     * @param array $timeOptions
     * @return array{by_day: array<int, array>, min_hour: ?int, max_hour: ?int, has_active: bool}
     */
    private function buildBusinessHoursSummary(array $businessTiming, array $timeOptions)
    {
        $days = Appointmentpro_Model_Appointmentpro::getDefaultDays();
        $dayMap = [
            'sunday' => 0,
            'monday' => 1,
            'tuesday' => 2,
            'wednesday' => 3,
            'thursday' => 4,
            'friday' => 5,
            'saturday' => 6,
        ];

        $byDay = array_fill(0, 7, [
            'day_key' => null,
            'is_active' => false,
            'start_seconds' => null,
            'end_seconds' => null,
            'start_minutes' => null,
            'end_minutes' => null,
            'start_label' => null,
            'end_label' => null,
        ]);

        $minHour = null;
        $maxHour = null;
        $hasActive = false;

        foreach ($days as $dayKey) {
            $weekday = isset($dayMap[$dayKey]) ? $dayMap[$dayKey] : null;
            if ($weekday === null) {
                continue;
            }

            $isActive = !empty($businessTiming['is_active'][$dayKey])
                && isset($businessTiming['from_time'][$dayKey], $businessTiming['to_time'][$dayKey])
                && $businessTiming['from_time'][$dayKey] < $businessTiming['to_time'][$dayKey];

            $byDay[$weekday]['day_key'] = $dayKey;
            $byDay[$weekday]['is_active'] = $isActive;

            if ($isActive) {
                $startSeconds = (int)$businessTiming['from_time'][$dayKey];
                $endSeconds = (int)$businessTiming['to_time'][$dayKey];
                $startMinutes = (int)floor($startSeconds / 60);
                $endMinutes = (int)floor($endSeconds / 60);

                $byDay[$weekday]['start_seconds'] = $startSeconds;
                $byDay[$weekday]['end_seconds'] = $endSeconds;
                $byDay[$weekday]['start_minutes'] = $startMinutes;
                $byDay[$weekday]['end_minutes'] = $endMinutes;
                $byDay[$weekday]['start_label'] = $this->formatBusinessHourLabel($startSeconds, $timeOptions);
                $byDay[$weekday]['end_label'] = $this->formatBusinessHourLabel($endSeconds, $timeOptions);

                $currentStartHour = (int)floor($startSeconds / 3600);
                $currentEndHour = (int)ceil($endSeconds / 3600);

                $minHour = ($minHour === null) ? $currentStartHour : min($minHour, $currentStartHour);
                $maxHour = ($maxHour === null) ? $currentEndHour : max($maxHour, $currentEndHour);
                $hasActive = true;
            }
        }

        return [
            'by_day' => $byDay,
            'min_hour' => $minHour,
            'max_hour' => $maxHour,
            'has_active' => $hasActive,
        ];
    }

    /**
     * Format a business hour label using precomputed time options.
     *
     * @param int $seconds
     * @param array $timeOptions
     * @return string|null
     */
    private function formatBusinessHourLabel($seconds, array $timeOptions)
    {
        if ($seconds === null) {
            return null;
        }

        if (isset($timeOptions[$seconds])) {
            return $timeOptions[$seconds];
        }

        return gmdate('H:i', $seconds);
    }
}
