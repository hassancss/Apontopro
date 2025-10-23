<?php

use Push2\Model\Onesignal\Scheduler;

class Appointmentpro_Model_Push extends Core_Model_Default
{

    /**
     * Notification for approval
     */
    public function sendv2($data)
    {
        try {
            // $this->sendPush2($data);
            $application = (new \Application_Model_Application())->find($data['app_id']);
            $values = [
                'app_id' => $data['app_id'], // The application ID, required
                'value_id' =>  $data['value_id'], // The value ID, optional
                'title' => $data['title'], // Required
                'body' => $data['text'], // Required
                'send_after' => null,
                'delayed_option' => null,
                'delivery_time_of_day' => null,
                'is_for_module' => true, // If true, the message is linked to a module, push will not be listed in the admin
                'is_test' => false, // If true, the message is a test push, it will not be listed in the admin
                'open_feature' => false, // If true, the message will open a feature, it works with feature_id
                'feature_id' => null, // The feature ID, required if open_feature is true
            ];
            // dd($data);
            // dd($data['application'], $data);
            // $scheduler = new Scheduler($data['application']);
            $scheduler = new Scheduler($application);
            $scheduler->buildMessageFromValues($values);
            $scheduler->sendToCustomer($data['receiver_id']); // This part will automatically sets the player_id and is_individual to true             

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }


    /**
     * Notification for approval
     */
    public function send($data)
    {
        if (class_exists("Push_Model_Message") && Push_Model_Message::hasIndividualPush() && class_exists("Push_Model_Customer_Message") && $data['receiver_id'] > 0) {

            $message_push = new Push_Model_Message();
            $message_push->setMessageType(Push_Model_Message::TYPE_PUSH);
            $data_push = [
                "title" => $data['title'],
                "text" => $data['text'],
                "send_at" => time(),
                "action_value" => $data['value_id'],
                "value_id" => $data['value_id'],
                "type_id" => $message_push->getMessageType(),
                "app_id" => $data['app_id'],
                "send_to_all" => 0,
                "send_to_specific_customer" => 1,
            ];
            $message_push->setData($data_push)->save();

            $customer_message = new Push_Model_Customer_Message();
            $customer_message_data = [
                "customer_id" => $data['receiver_id'],
                "message_id" => $message_push->getId(),
            ];
            $customer_message->setData($customer_message_data);
            $customer_message->save();

            return true;
        }
    }
}
