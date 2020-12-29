<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
ini_set('display_errors', 0);

class Notification extends MY_Controller {

    public $data;

    public function __construct() {

        parent::__construct();

        $this->output->set_header('Last-Modified:' . gmdate('D, d M Y H:i:s') . 'GMT');
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate');
        $this->output->set_header('Cache-Control: post-check=0, pre-check=0', false);
        $this->output->set_header('Pragma: no-cache');
        $this->data['title'] = 'Notification : MQS - Motivational Quotes by Single';

        //Load header and save in variable
        $this->data['header'] = $this->load->view('header', $this->data, true);
        $this->data['sidebar'] = $this->load->view('sidebar', $this->data, true);
        $this->data['footer'] = $this->load->view('footer', $this->data, true);
        $this->data['redirect_url'] = $this->last_url();
        $this->load->helper('security');
        $this->load->library('user_agent');
        $this->data['users'] = $this->common->select_data_by_condition('user', array(), '*', '', '', '', '', array());
    }

    public function index() {
        $this->load->view('notification/index', $this->data);
    }

    public function addnew() {
        $this->form_validation->set_rules('quote', 'Quote of the day', 'required|xss_clean');
       
        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors('<div class="error">', '</div>'));
            redirect('Notification', 'refresh');
        } else {


            $message = $this->input->post('quote');
            $subject = 'MQS:Quote of the Day';
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
            $this->session->set_flashdata('success', 'Notification sent successfully.');
            redirect('Notification', 'refresh');
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
