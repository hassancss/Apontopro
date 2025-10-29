<?php

class Appointmentpro_Model_Utils
{

    /**
     * @param string $start
     * @param string $end
     * @return array
     */
    public static function timeOptions($start = "00:00", $end = "23:30", $value_id = NULL)
    {
        $return = array();
        $tNow = $tStart = strtotime($start);
        $tEnd = strtotime($end);
        $setting_model = new Appointmentpro_Model_Settings();
        $setting = $setting_model->find($value_id, "value_id");
        $result = $setting->getData();
        ($result['time_format'] == 1) ? $format = 'H:i' : $format = 'g:i A';
        while ($tNow <= $tEnd) {
            $timestamp = (date("H", $tNow) * 3600) + (date("i", $tNow) * 60);
            $return[$timestamp] = date($format, $tNow);
            $tNow = strtotime('+5 minutes', $tNow);
        }
        return $return;
    }

    /**
     * @param string $timestamp
     * @param string $format
     * @return string
     */
    public static function timestampTotime($timestamp = "", $format = "")
    {
        $return = '';
        if (!empty($timestamp)) {
            $hr = (int)($timestamp / 3600);
            $min = (int)(($timestamp % 3600) / 60);
            $am = '';
            if ($format == 'A') {
                $am = 'AM';
                if ($hr >= 12) {
                    $hr = $hr == 12 ? $hr : $hr - 12;
                    $am = 'PM';
                }
            }
            $return = sprintf("%02d:%02d %s", $hr, $min, $am); //"$hr:$min $am";
        }
        return $return;
    }


    public function setCurl($params, $api_url)
    {

        $params = http_build_query($params);
        $curl = curl_init();
        $curlParams = array(
            CURLOPT_URL => $api_url,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $params,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_VERBOSE => 1,
            CURLOPT_SSL_VERIFYPEER => false, //si certificat SSL => true
            CURLOPT_SSL_VERIFYHOST => false, //si certificat SSL => 2
        );
        curl_setopt_array($curl, $curlParams);
        $response = curl_exec($curl);
        if ($response) {
            $responseArray = array();
            parse_str($response, $responseArray);
            return $responseArray;
        }
    }

    static public function displayPrice($price, $currency, $decimals = 2, $decimalpoint = '.', $seperator = ',', $currency_positions = 'left')
    {


        if ($currency_positions == 'left') {
            return $currency . '' . number_format(floor(($price * pow(10, $decimals))) / pow(10, $decimals), $decimals, $decimalpoint, $seperator);
        }
        if ($currency_positions == 'left_with_space') {
            return $currency . ' ' . number_format(floor(($price * pow(10, $decimals))) / pow(10, $decimals), $decimals, $decimalpoint, $seperator);
        }
        if ($currency_positions == 'right') {
            return number_format(floor(($price * pow(10, $decimals))) / pow(10, $decimals), $decimals, $decimalpoint, $seperator) . '' . $currency;
        }
        if ($currency_positions == 'right_with_space') {
            return number_format(floor(($price * pow(10, $decimals))) / pow(10, $decimals), $decimals, $decimalpoint, $seperator) . ' ' . $currency;;
        }

        return number_format(floor(($price * pow(10, $decimals))) / pow(10, $decimals), $decimals, $decimalpoint, $seperator);
    }

    /**
     * @param $timeObj
     * @param $timeArray
     * @param $timeDiff
     * @param $requestedDay
     * @return mixed
     */
    public function filterTimeArray($timeObj, $timeArray, $timeDiff, $requestedDay)
    {
        $unsetTimes = [];
        foreach ($timeObj as $busTimeVal) {
            $decodeArray = $busTimeVal;
            if ($decodeArray->day == $requestedDay) {
                foreach ($timeArray as $timeKey => $timeVal) {
                    $checkFrom = $timeVal;
                    $checkTo = strtotime('+' . $timeDiff . ' minutes', $checkFrom);
                    // $checkTo = strtotime('+' . $timeDiff . ' minutes', $checkFrom);
                    // if ($checkFrom >= $decodeArray->start_time && $checkFrom < $decodeArray->end_time) {
                    //     unset($timeArray[$timeKey]);
                    // } elseif ($checkTo <= $decodeArray->start_time && $checkTo > $decodeArray->end_time) {
                    //     unset($timeArray[$timeKey]);
                    // } elseif ($decodeArray->start_time >= $checkFrom && $decodeArray->start_time < $checkTo) {
                    //     unset($timeArray[$timeKey]);
                    // } elseif ($decodeArray->end_time <= $checkFrom && $decodeArray->end_time > $checkTo) {
                    //     unset($timeArray[$timeKey]);
                    // }

                    if ($checkFrom >= $decodeArray->start_time && $checkFrom < $decodeArray->end_time) {
                        $unsetTimes[$timeKey] = $unsetTimes[$timeKey] + 1;
                    }
                }
            }
        }

        foreach ($unsetTimes as $tKey => $tValue) {
            unset($timeArray[$tKey]);
        }

        return $timeArray;
    }

    /**
     * @param $appointmentData
     * @param $timeArray
     * @param $timeDiff
     * @return mixed
     */
    public function checkAppoinment($appointmentData, $timeArray, $timeDiff, $totalBookingPerSlot = 1)
    {
        $unsetTimes = [];

        foreach ($appointmentData as $queryVal) {
            foreach ($timeArray as $timeKey => $timeVal) {
                $checkFrom = $timeVal;
                $checkTo = strtotime('+' . $timeDiff . ' minutes', $checkFrom);

                // Check if there's ANY overlap between the potential booking and existing appointment
                // Overlap occurs if: start time is before existing end AND end time is after existing start
                $hasOverlap = !($checkTo <= $queryVal['appointment_time'] || $checkFrom >= $queryVal['appointment_end_time']);

                if ($hasOverlap) {
                    if (!isset($unsetTimes[$timeKey])) {
                        $unsetTimes[$timeKey] = 0;
                    }
                    $unsetTimes[$timeKey] = $unsetTimes[$timeKey] + 1;
                }
            }
        }

        // Remove slots that have reached the booking limit
        foreach ($unsetTimes as $tKey => $tValue) {
            if ($totalBookingPerSlot <= $tValue) {
                unset($timeArray[$tKey]);
            }
        }

        return $timeArray;
    }

    /**
     * Check appointments with break time considerations
     * 
     * @param array $appointmentData
     * @param array $timeArray
     * @param int $timeDiff
     * @param int $totalBookingPerSlot
     * @param array $breakInfo
     * @param int $currentServiceId
     * @return array
     */
    public function checkAppointmentWithBreaks($appointmentData, $timeArray, $timeDiff, $totalBookingPerSlot, $breakInfo, $currentServiceId, $currentProviderId = null)
    {
        $slotConflictCount = [];

        $db = Zend_Db_Table::getDefaultAdapter();

        // Determine the structure of the service being booked
        $currentServiceBreakData = null;
        $currentServiceDuration = $timeDiff * 60; // Default to service time + buffer

        if (!empty($breakInfo)) {
            $currentServiceBreakData = [
                'work_time_before_break' => (int) $breakInfo['work_before'],
                'break_duration' => (int) $breakInfo['break_duration'],
                'work_time_after_break' => (int) $breakInfo['work_after'],
                'break_is_bookable' => !empty($breakInfo['break_is_bookable'])
            ];

            $currentServiceDuration = (
                $currentServiceBreakData['work_time_before_break'] +
                $currentServiceBreakData['break_duration'] +
                $currentServiceBreakData['work_time_after_break']
            ) * 60;
        } else {
            // Fallback to database lookup when break info is not provided explicitly
            $select = $db->select()
                ->from('appointment_service_break_config')
                ->where('service_id = ?', $currentServiceId);
            $currentServiceBreakData = $db->fetchRow($select);

            if ($currentServiceBreakData && !empty($currentServiceBreakData['break_is_bookable'])) {
                $currentServiceDuration = (
                    $currentServiceBreakData['work_time_before_break'] +
                    $currentServiceBreakData['break_duration'] +
                    $currentServiceBreakData['work_time_after_break']
                ) * 60;
            }
        }

        $breakConfigCache = [];

        foreach ($timeArray as $timeKey => $potentialStartTime) {
            $conflictCount = 0;
            $potentialEndTime = $potentialStartTime + $currentServiceDuration;

            foreach ($appointmentData as $existingAppointment) {
                $existingStart = $existingAppointment['appointment_time'];
                $existingEnd = $existingAppointment['appointment_end_time'];
                $hasConflict = false;

                $serviceId = $existingAppointment['service_id'];
                if (!array_key_exists($serviceId, $breakConfigCache)) {
                    $select = $db->select()
                        ->from('appointment_service_break_config')
                        ->where('service_id = ?', $serviceId);
                    $breakConfigCache[$serviceId] = $db->fetchRow($select) ?: null;
                }

                $existingBreakData = $breakConfigCache[$serviceId];

                if ($existingBreakData && !empty($existingBreakData['break_is_bookable'])) {
                    $existingWorkBefore = (int) $existingBreakData['work_time_before_break'] * 60;
                    $existingBreakDuration = (int) $existingBreakData['break_duration'] * 60;
                    $existingWorkAfter = (int) $existingBreakData['work_time_after_break'] * 60;

                    $firstWorkStart = $existingStart;
                    $firstWorkEnd = $existingStart + $existingWorkBefore;
                    $breakStart = $firstWorkEnd;
                    $breakEnd = $breakStart + $existingBreakDuration;
                    $secondWorkStart = $breakEnd;
                    $secondWorkEnd = $existingEnd;

                    $hasSecondaryProvider = !empty($existingAppointment['service_provider_id_2'])
                        && $existingAppointment['service_provider_id_2'] != $existingAppointment['service_provider_id'];

                    $checkFirstWork = true;
                    $checkSecondWork = true;

                    if ($hasSecondaryProvider && $currentProviderId) {
                        if ($existingAppointment['service_provider_id'] == $currentProviderId) {
                            $checkSecondWork = false;
                        } elseif ($existingAppointment['service_provider_id_2'] == $currentProviderId) {
                            $checkFirstWork = false;
                        }
                    }

                    if ($currentServiceBreakData && !empty($currentServiceBreakData['break_is_bookable'])) {
                        $newWorkBefore = (int) $currentServiceBreakData['work_time_before_break'] * 60;
                        $newBreakDuration = (int) $currentServiceBreakData['break_duration'] * 60;

                        $newFirstWorkEnd = $potentialStartTime + $newWorkBefore;
                        $newBreakEnd = $newFirstWorkEnd + $newBreakDuration;
                        $newSecondWorkStart = $newBreakEnd;

                        $overlapsExistingFirstWork = !($newFirstWorkEnd <= $firstWorkStart || $potentialStartTime >= $firstWorkEnd);
                        $overlapsExistingSecondWork = !($potentialEndTime <= $secondWorkStart || $newSecondWorkStart >= $secondWorkEnd);
                        $newFirstOverlapsExistingSecond = !($newFirstWorkEnd <= $secondWorkStart || $potentialStartTime >= $secondWorkEnd);
                        $newSecondOverlapsExistingFirst = !($potentialEndTime <= $firstWorkStart || $newSecondWorkStart >= $firstWorkEnd);

                        if (
                            ($checkFirstWork && ($overlapsExistingFirstWork || $newSecondOverlapsExistingFirst)) ||
                            ($checkSecondWork && ($overlapsExistingSecondWork || $newFirstOverlapsExistingSecond))
                        ) {
                            $hasConflict = true;
                        }
                    } else {
                        $overlapsFirstWork = !($potentialEndTime <= $firstWorkStart || $potentialStartTime >= $firstWorkEnd);
                        $overlapsSecondWork = !($potentialEndTime <= $secondWorkStart || $potentialStartTime >= $secondWorkEnd);

                        if (($checkFirstWork && $overlapsFirstWork) || ($checkSecondWork && $overlapsSecondWork)) {
                            $hasConflict = true;
                        }
                    }
                } else {
                    $hasOverlap = !($potentialEndTime <= $existingStart || $potentialStartTime >= $existingEnd);
                    if ($hasOverlap) {
                        $hasConflict = true;
                    }
                }

                if ($hasConflict) {
                    $conflictCount++;
                }
            }

            $slotConflictCount[$timeKey] = $conflictCount;
        }

        $availableSlots = [];
        foreach ($timeArray as $timeKey => $timeVal) {
            $conflicts = isset($slotConflictCount[$timeKey]) ? $slotConflictCount[$timeKey] : 0;
            if ($conflicts < $totalBookingPerSlot) {
                $availableSlots[$timeKey] = $timeVal;
            }
        }

        return $availableSlots;
    }


    /**
     * @param $appointmentData
     * @param $timeArray
     * @param $timeDiff
     * @return mixed
     */
    public function filterTimeSlot($timeArray, $timeDiff, $breakInfo = null)
    {
        // If service has break time configuration, handle it specially
        if ($breakInfo && !empty($breakInfo)) {
            return $this->filterTimeSlotWithBreaks($timeArray, $breakInfo);
        }

        // Original logic for services without breaks
        $perSlotTime = 30; // minutes per slot
        $totalRequiredSlots = ($timeDiff / $perSlotTime);
        $convertTimeArray = [];
        $format = ''; // Default format

        // Convert array to indexed array for easier access
        $timeArrayIndexed = array_values($timeArray);
        $arrayCount = count($timeArrayIndexed);

        // Check each slot to see if enough consecutive slots exist from that point
        foreach ($timeArrayIndexed as $index => $startTime) {
            $canFitService = true;

            // Check if we have enough consecutive slots for this service
            for ($i = 0; $i < $totalRequiredSlots; $i++) {
                $expectedTime = strtotime('+' . ($i * $perSlotTime) . ' minutes', $startTime);
                $actualIndex = $index + $i;

                // Check if slot exists and matches expected time
                if ($actualIndex >= $arrayCount || $timeArrayIndexed[$actualIndex] != $expectedTime) {
                    $canFitService = false;
                    break;
                }
            }

            // If service fits, add this start time to available slots
            if ($canFitService) {
                $keyTime = (string) $startTime;
                $convertTimeArray[$keyTime] = Appointmentpro_Model_Utils::timestampTotime($startTime, $format);
            }
        }

        return $convertTimeArray;
    }

    /**
     * Filter time slots for services with break times
     */
    public function filterTimeSlotWithBreaks($timeArray, $breakInfo)
    {
        $perSlotTime = 30; // minutes per slot
        $convertTimeArray = [];
        $format = '';

        $workBefore = isset($breakInfo['work_before']) ? (int) $breakInfo['work_before'] : 0;
        $breakDuration = isset($breakInfo['break_duration']) ? (int) $breakInfo['break_duration'] : 0;
        $workAfter = isset($breakInfo['work_after']) ? (int) $breakInfo['work_after'] : 0;

        $totalServiceTime = $workBefore + $breakDuration + $workAfter;

        $closingTime = null;
        if (!empty($timeArray)) {
            $lastSlot = end($timeArray);
            $closingTime = strtotime('+5 minutes', $lastSlot);
            reset($timeArray);
        }

        foreach ($timeArray as $timeVal) {
            $serviceEndTime = strtotime('+' . $totalServiceTime . ' minutes', $timeVal);

            if ($closingTime && $serviceEndTime > $closingTime) {
                continue;
            }

            $keyTime = (string) $timeVal;
            $displayTime = Appointmentpro_Model_Utils::timestampTotime($timeVal, $format);

            $convertTimeArray[$keyTime] = [
                'time' => $displayTime,
                'has_break' => true,
                'work_before' => $workBefore,
                'break_duration' => $breakDuration,
                'work_after' => $workAfter
            ];
        }

        return $convertTimeArray;
    }
}
