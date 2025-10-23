<?php

/**
 * Class Appointmentpro_DashboardController
 */
class Appointmentpro_DashboardController extends Application_Controller_Default
{
    /**
     *Home screen
     */
    public function listAction() {

        $value_id = (new Appointmentpro_Model_Appointmentpro())->getCurrentValueId();
     
     	$params = [];
		$monday = strtotime("last monday");
		$monday = date('w', $monday) == date('w') ? $monday+7*86400 : $monday;
		$sunday = strtotime(date("Y-m-d",$monday)." +6 days");
		$params['week_start_date'] = date("Y-m-d", $monday);
		$params['week_end_date'] = date("Y-m-d", $sunday);         
          
        
        $settingModel = (new Appointmentpro_Model_Settings())->find(['value_id' => $value_id]);
        $settings = $settingModel->getData();
        
        $params['total_confirm_booking'] = (new Appointmentpro_Model_Booking())
            ->countTotalDateRangeConfirmBooking($value_id, $params);

        $params['status'] = [4];
        $params['total_confirm_revenue'] = (new Appointmentpro_Model_Booking())
            ->sumTotalConfirmProjectedEstimatedRevenue($value_id, $params);

        $params['status'] = [2, 3, 9];
        $params['total_projected_revenue'] = (new Appointmentpro_Model_Booking())
            ->sumTotalConfirmProjectedEstimatedRevenue($value_id, $params);

        $params['status'] = [2, 3, 4, 9];
        $params['total_projected_estimated'] = (new Appointmentpro_Model_Booking())
            ->sumTotalConfirmProjectedEstimatedRevenue($value_id, $params);

        $params['total_confirm_revenue'] = Appointmentpro_Model_Utils::displayPrice($params['total_confirm_revenue'],$this->getApplication()->getCurrency(), $settings['number_of_decimals'], $settings['decimal_separator'], $settings['thousand_separator'], $settings['currency_position']);
        $params['total_projected_revenue'] = Appointmentpro_Model_Utils::displayPrice($params['total_projected_revenue'],$this->getApplication()->getCurrency(), $settings['number_of_decimals'], $settings['decimal_separator'], $settings['thousand_separator'], $settings['currency_position']);
        $params['total_projected_estimated'] = Appointmentpro_Model_Utils::displayPrice($params['total_projected_estimated'],$this->getApplication()->getCurrency(), $settings['number_of_decimals'], $settings['decimal_separator'], $settings['thousand_separator'], $settings['currency_position']);
  
        $this->loadPartials();
        $this->getLayout()->getPartial('content')->setWeekDates($params);
    }


    public function getappointmentmetricAction()
    {
       try {
                    
            $params = [];
	        $params['status'] = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9];
			$params['week_start_date'] = date('Y-m-d',strtotime('first day of this month'));
			$params['week_end_date'] = date('Y-m-d',strtotime('last day of this month'));

			$value_id = (new Appointmentpro_Model_Appointmentpro())->getCurrentValueId();
	        $total_bookings_by_date = (new Appointmentpro_Model_Booking())
	            ->countMonthTotalBooking($value_id, $params);
 			
 			$params['status'] = [4];
 			$total_complete_bookings = (new Appointmentpro_Model_Booking())
	            ->countMonthTotalBooking($value_id, $params);

            $params['status'] = [2, 3, 4, 9];
            $total_confirm_bookings = (new Appointmentpro_Model_Booking())
                ->countMonthTotalBooking($value_id, $params);


	        $monthTotalData = [];
	        foreach ($total_bookings_by_date as $key => $value) {
 				$newData = [
	 				'date' => date("M d", $value['appointment_date']), 
	 				'value' => $value['count'],
	 				'day' => date("j", $value['appointment_date'])
	 				];
	            $monthTotalData[$newData['day']] = $newData;
	        }

	        $monthCompleteData = [];
	        foreach ($total_complete_bookings as $key => $value) {
 				$newData = [
	 				'date' => date("M d", $value['appointment_date']), 
	 				'value' => $value['count'],
	 				'day' => date("j", $value['appointment_date'])
	 				];
	            $monthCompleteData[$newData['day']] = $newData;
	        }

            $monthConfirmData = [];
            foreach ($total_confirm_bookings as $key => $value) {
                $newData = [
                    'date' => date("M d", $value['appointment_date']), 
                    'value' => $value['count'],
                    'day' => date("j", $value['appointment_date'])
                    ];
                $monthConfirmData[$newData['day']] = $newData;
            }

	        $first_day = date('j',strtotime('first day of this month'));
			$last_day = date('j',strtotime('last day of this month'));

			$lableName = [];
			$countTotalMonthsJson = [];
			$countTotalCompleteMonthsJson = [];
            $countTotalConfirmMonthsJson = [];
	       	for ($x = $first_day; $x <= $last_day; $x++) {			   
			   array_push($countTotalMonthsJson, (int)$monthTotalData[$x]['value']);
			   array_push($countTotalCompleteMonthsJson, (int)$monthCompleteData[$x]['value']);
               array_push($countTotalConfirmMonthsJson, (int)$monthConfirmData[$x]['value']);
			   array_push($lableName, $x.' '.date("M"));
			}
        
        
            $payload = [
            	"success" => true,
                "countTotalMonthsJson" => $countTotalMonthsJson,
                "countTotalCompleteMonthsJson" => $countTotalCompleteMonthsJson,
                "countTotalConfirmMonthsJson" => $countTotalConfirmMonthsJson,
                "lableName" => $lableName
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
