<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Cron extends CI_Controller {

    public $data;

    public function __construct() {

        parent::__construct();
        $set_header = 'ALFA141GHOSTdeltathatRo34ger';

        $this->load->library('user_agent');
        $this->load->helper('security');
    }

    public function index() {
        $crontime = $this->common->select_data_by_condition('settings', array('setting_id' => 1), '*', '', '', '', '', array());
        $current_time = date('H:i');
//        echo $current_time.'<br>';
//        echo $crontime[0]['setting_value'];
        if ($crontime[0]['setting_value'] == $current_time) {
            $message = 'Hey! One more new quote added in your App.';
            $subject = 'MQS-Quotes';
            $curl_data = $this->curl($subject, $message);

            if (!empty($curl_data)) {

                $ncount = $this->common->select_data_by_condition('notification', array('id' => 1), '*', '', '', '', '', array());

                $update_data = array(
                    "count" => $ncount[0]['count'] + 1,
                );
                //echo '<pre>';            print_r($update_data); die;
                $this->common->update_data($update_data, 'notification', 'id', 1);

                $notification = array(
                    'subject' => $subject,
                    'message' => $message,
                    'created_datetime'=> date('Y-m-d H:i:s')
                );
                $this->common->insert_data_getid($notification, "notification_list");
            }
        }
    }

    public function curl($subject, $message) {
        $gettoken = $this->common->select_data_by_condition('device_token', array(), '*', '', '', '', '', array());

        $url = "https://fcm.googleapis.com/fcm/send";

        // api key
        $serverKey = 'AAAA23RMLOM:APA91bFabbxi9H1Lvv8XJe-nw65-XNqSSqjUZbXm9wlUht8leEvunO9E8uhYcDX3pQ0o3heZkcPKF5q1zTBIbC851B3t3dZlKj0tH3s9KKebMdWgzOy1LIQVV2HrrpdOvHjoFSI5lK5m'; // add api key here
        if (!empty($gettoken)) {
            for ($i = 0; $i < count($gettoken); $i++) {
                $token = $gettoken[$i]['device_token']; // Topic or Token devices here

                $notification = array('title' => $message, 'body' => $subject, 'sound' => 'default', 'badge' => '1');
                $data = array('extraInfo' => '');
                $arrayToSend = array('to' => $token, 'notification' => $notification, 'priority' => 'high', 'data' => $data);
                $json = json_encode($arrayToSend);
                $headers = array();
                $headers[] = 'Content-Type: application/json';
                $headers[] = 'Authorization: key=' . $serverKey;

                $ch = curl_init();

                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $json);

                $result = curl_exec($ch);

                curl_close($ch);
            }
            return $result;
        } else {
            $result = array();
            return $result;
        }
    }

}
