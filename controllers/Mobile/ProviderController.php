<?php

use Siberian\Exception;
use Siberian\File;
use Siberian\Json;

/**
 * Class Appointmentpro_Mobile_ProviderController
 */
class Appointmentpro_Mobile_ProviderController extends Application_Controller_Mobile_Default
{

    public function findServiceProviderAction()
    {
        $payload = [];

        try {
            $param = $this->getRequest()->getBodyParams();
            $value_id = $this->getRequest()->getParam('value_id');
            $location_id = $param['location_id'];
            $service_id = $param['service_id'];

            $providers = (new Appointmentpro_Model_Provider())->findServiceProvider($location_id, $service_id);
            $providersJson = [];

            foreach ($providers as $key => $provider) {
                $providersJson[] = $provider;
            }

            $payload = [
                'success' => true,
                'providers' => $providersJson,
                'param' => $param
            ];
        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }

    public function findServiceProviderSlotAction()
    {
        $payload = $data = [];

        try {
            $inputParams = $this->getRequest()->getBodyParams();
            $value_id = $this->getRequest()->getParam('value_id');
            $date = str_replace('/', '-', $inputParams['date']);

            $queryData = (new Appointmentpro_Model_Provider())->getServiceTime($date, $inputParams);
            $requestedDay = (string) strtolower(date('l', strtotime($date)));
            $spTimingData = json_decode($queryData['spData']['timing']);
            $businessTimingData = json_decode($queryData['spData']['business_timing']);
            $todaysDate = date('d-m-Y');

            if (strtotime($date) >= strtotime($todaysDate)) {

                if (!$spTimingData->is_active->$requestedDay || !$businessTimingData->is_active->$requestedDay) {
                    $data['message'] = p__("appointmentpro", "The requested date has no availability!");
                    $data['status'] = 'error';
                    $data['error_code'] = '101';
                } else {

                    if ((isset($spTimingData->date_break) && in_array($inputParams['date'], $spTimingData->date_break))
                        || (isset($businessTimingData->date_break) && in_array($inputParams['date'], $businessTimingData->date_break))
                    ) {
                        $data['message'] = p__("appointmentpro", "The requested date has no availability!");
                        $data['status'] = 'error';
                        $data['error_code'] = '102';
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
                        $timeBound = strtotime('-30 minutes', $toTime);
                        if ($timeDiff > 0) {
                            for ($i = $fromTime; $i <= $timeBound; $i = strtotime('+30 minutes', $i)) {
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
                        // foreach ($timeArray as $timeVal) {
                        //     $key = (string) $timeVal;
                        //     $convertTimeArray[$key] = Appointmentpro_Model_Utils::timestampTotime($timeVal, $format);
                        // }
                        // Available Tiime
                        $convertTimeArray = (new Appointmentpro_Model_Utils())->filterTimeSlot($timeArray, $timeDiff, $hasBreakTime ? $breakInfo : null);


                        if (sizeof($convertTimeArray)) {
                            $returnArray = [];
                            $returnArray['serviceTime'] = (string) $timeDiff;
                            $returnArray['sId'] = $queryData['spData']['id'];
                            $returnArray['displayTime'] = $convertTimeArray;
                            $data['status'] = 'success';
                            $data['data'] = $returnArray;
                            $data['queryData'] = $queryData;
                            // $data['timeArray'] = $timeArray;
                        } else {
                            $data['message'] = p__("appointmentpro", 'The requested date has no availability!');
                            $data['status'] = 'error';
                            $data['error_code'] = '103';
                            $data['timeArray'] = $timeArray;
                        }
                    }
                }
            } else {

                $data['message'] = p__("appointmentpro", 'The date you are looking for is invalid!');
                $data['status'] = 'error';
                $data['error_code'] = '104';
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
     *Provider google calendar token Save
     */
    public function saveTokenAction()
    {

        if ($value_id = $this->getRequest()->getParam('value_id')) {

            try {
                $param = $this->getRequest()->getBodyParams();
                $setting = (new Appointmentpro_Model_Settings())->find($value_id, "value_id");
                $settingResult = $setting->getData();

                $application = $this->getApplication();
                $appKey = $application->getKey();
                $google_redirect_URL = trim($application->getBaseUrl() . '/' . $appKey . '/appointmentpro/mobile_view/retrun-calendar');

                $google = (new Appointmentpro_Model_GoogleService());
                $google->setClientId($settingResult['client_id']);
                $google->setRedirectUri($google_redirect_URL);
                $google->setClientSecret($settingResult['client_secret']);
                $accessToken = $google->GetAccessToken(base64_decode($param['code']));
                if (empty($accessToken['error'])) {
                    $model = (new Appointmentpro_Model_Provider())
                        ->find(['provider_id' => $param['provider_id']])
                        ->setGoogleRefreshToken($accessToken['access_token']);

                    $model->save();
                }
                $payload = [
                    'success' => true,
                    'message' => p__('appointmentpro', 'Enable google calendar successfully!')
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
}
