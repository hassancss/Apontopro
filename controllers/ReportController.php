<?php

/**
 * Class Appointmentpro_ReportController
 */
class Appointmentpro_ReportController extends Application_Controller_Default
{

     /**
     *Report list
     */
    public function listAction() {

    	$paymentStatus = Appointmentpro_Model_Appointment::getPaymentStatus();  
        
        $value_id = (new Appointmentpro_Model_Appointmentpro())->getCurrentValueId();
        $locations = (new Appointmentpro_Model_Location())
                ->findByValueId($value_id, []);
             
        $this->loadPartials();
        $this->getLayout()->getPartial('content')->setPaymentStatus($paymentStatus)->setLocations($locations);
    }


    
     /**
     * fetch
     */
     public function findAllAction() {
        
        try {
            $request = $this->getRequest();
            $limit = $request->getParam("perPage", 25);
            $offset = $request->getParam("offset", 0);
            $sorts = $request->getParam("sorts", []);
            $queries = $request->getParam("queries", []);

            $type = 'all';

	       	if (!empty($this->getRequest()->getParam('type'))) {
	       		$type = $this->getRequest()->getParam('type');
	       	}
                        
            $params = [
                "limit" => $limit,
                "offset" => $offset,
                "sorts" => $sorts,
                "queries" => $queries
           ];
          
            $value_id = (new Appointmentpro_Model_Appointmentpro())->getCurrentValueId();
            
            $bookings = (new Appointmentpro_Model_Transaction())
                ->findByValueId($value_id, $params);

            $countAll = (new Appointmentpro_Model_Transaction())->countAllForApp($value_id);
            $countFiltered =   (new Appointmentpro_Model_Transaction())->countAllForApp($value_id, $params);

            $setting = (new Appointmentpro_Model_Settings())->find($value_id, "value_id");
            $settingResult = $setting->getData();
            ($settingResult['time_format'] == 1) ? $timeFormat = '' : $timeFormat = 'A';
            ($settingResult['date_format'] == 1) ? $dateFormat = 'm/d/y' : $dateFormat = 'd/m/y';
            

            $bookingJson = [];
            foreach ($bookings as $booking) {
                $data = $booking->getData();
                $data['customer'] = $data['firstname'].' '.$data['lastname'];
                $data['booking_date'] = date($dateFormat, $data['appointment_date']);
                $data['start_time'] = Appointmentpro_Model_Utils::timestampTotime($data['appointment_time'], $timeFormat);
                $data['end_time'] = Appointmentpro_Model_Utils::timestampTotime($data['appointment_end_time'], $timeFormat);
                $data['currency_symbol'] = $this->getApplication()->getCurrency();
	            
                $data['total_amount'] = str_replace( ',', '', $data['total_amount']);
                $data['amount_with_currency'] = Appointmentpro_Model_Utils::displayPrice($data['total_amount'], $this->getApplication()->getCurrency(), $settingResult['number_of_decimals'], $settingResult['decimal_separator'], $settingResult['thousand_separator'], $settingResult['currency_position']);

	            $data['text_color'] = Appointmentpro_Model_Appointment::getBookingTextcolor($data['status']);  
                $data['payment_text_color'] = Appointmentpro_Model_Appointment::getPaymentTextcolor($data['payment_status']);     
                $data['payment_status'] = p__('appointmentpro', Appointmentpro_Model_Appointment::getPaymentStatus($data['payment_status']));
	            $data['status'] = p__('appointmentpro',  Appointmentpro_Model_Appointment::getBookingStatus($data['status']));
                
                $data['payment_to'] = ucfirst($data['payment_to']);
                $data['payment_type'] = ($data['payment_type'] == 'cod') ?  p__("appointmentpro",  "Cash") : ucfirst($data['payment_type']);
                $bookingJson[] = $data;
            }

            $payload = [
                "records" => $bookingJson,
                "queryRecordCount" => $countFiltered[0],
                "totalRecordCount" => $countAll[0],
                "queries" => $queries
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