<?php

ob_start();
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class WebService extends CI_Controller {

    public $data;

    public function __construct() {

        parent::__construct();
        $set_header = 'ALFA141GHOSTdeltathatRo34ger';

        $this->load->library('user_agent');
        $this->load->helper('security');
    }

    private function return_form_validation_error($input) {
        $output = '';
        $error = array();
        if (!empty($input)) {
            foreach ($input as $key => $value) {
                array_push($error, $value);
            }
            for ($i = 0; $i < count($error); $i++) {
                $output = $error[0];
            }
        }
        return $output;
    }

    public function getUserQuotes() {
        $header = $this->input->request_headers();

//        if ($set_header == $header['Authorization']) {
//            echo json_encode(array('status' => '400', 'message' => 'Access Dendied.'));
//            die();
//        }
        if ($this->input->method() != 'post') {
            echo json_encode(array('status' => '403', 'message' => 'Request Not Allowed.'));
            die();
        }
        $this->form_validation->set_rules('user_id', 'User', 'required|trim|xss_clean');

        if ($this->form_validation->run() == FALSE) {
            $input = $this->form_validation->error_array();
            $output = $this->return_form_validation_error($input);

            echo json_encode(array('status' => '402', 'message' => $output));
            die();
        }
        $id = $this->security->xss_clean($this->input->post('user_id'));
        $condition = array(
            "user_id" => $id
        );

        $join_str = array();
        $join_str[0] = array(
            'table' => 'user',
            'join_table_id' => 'user.id',
            'from_table_id' => 'quotes.user_id',
            'join_type' => 'LEFT',
        );
        $checkAuth = $this->common->select_data_by_condition('quotes', $condition, 'user.name,quotes.*', '', '', '', '', $join_str);
        $userdata = $this->common->select_data_by_condition('user', array('id' => $id), '*', '', '', '', '', array());


        echo json_encode(array('status' => '200', 'message' => 'success', 'userdata' => $userdata, 'data' => $checkAuth));
        die();
    }

    public function getQuotes() {
        $header = $this->input->request_headers();

        if ($this->input->method() != 'get') {
            echo json_encode(array('status' => '403', 'message' => 'Request Not Allowed.'));
            die();
        }
        $condition = array();
        $join_str = array();
        $join_str[0] = array(
            'table' => 'user',
            'join_table_id' => 'user.id',
            'from_table_id' => 'quotes.user_id',
            'join_type' => 'LEFT',
        );
        $quotes = $this->common->select_data_by_condition('quotes', $condition, 'user.name,quotes.*', '', '', '', '', $join_str);
        shuffle($quotes);
        $random = array();
        if (!empty($quotes)) {
            foreach ($quotes as $k => $val) {
                array_push($random, $quotes[$k]);
            }
        }
        echo json_encode(array('status' => '200', 'message' => 'success', 'data' => $random));
        die();
    }

    public function getUsers() {
        $header = $this->input->request_headers();

        if ($this->input->method() != 'get') {
            echo json_encode(array('status' => '403', 'message' => 'Request Not Allowed.'));
            die();
        }
        $final_array = array();
        $condition = array();
        $join_str = array();
        $user = $this->common->select_data_by_condition('user', $condition, 'user.*', 'id', 'DESC', '', '', $join_str);
        if (!empty($user)) {
            for ($i = 0; $i < count($user); $i++) {
                $total_quotes = count($this->common->select_data_by_condition('quotes', array('user_id' => $user[$i]['id']), '*', 'id', 'DESC', '', '', array()));
                $user[$i]['total_quotes'] = $total_quotes;
            }
        }
        $image_url = base_url('../uploads/user/thumb/');
        $noimage = base_url('../assets/images/noimage.jpg');
        echo json_encode(array('status' => '200', 'message' => 'success', 'noimage' => $noimage, 'user_image' => $image_url, 'data' => $user));
        die();
    }

    public function getnotification() {
        $header = $this->input->request_headers();

        if ($this->input->method() != 'get') {
            echo json_encode(array('status' => '403', 'message' => 'Request Not Allowed.'));
            die();
        }
        $final_array = array();
        $condition = array();
        $join_str = array();
        $user = $this->common->select_data_by_condition('notification', $condition, 'notification.*', 'id', 'DESC', '', '', $join_str);

        echo json_encode(array('status' => '200', 'message' => 'success', 'data' => $user));
        die();
    }

    public function getnotificationlist() {
        $header = $this->input->request_headers();

        if ($this->input->method() != 'get') {
            echo json_encode(array('status' => '403', 'message' => 'Request Not Allowed.'));
            die();
        }
        $final_array = array();
        $condition = array();
        $join_str = array();
        $list = $this->common->select_data_by_condition('notification_list', $condition, '*', 'id', 'DESC', '', '', $join_str);

        echo json_encode(array('status' => '200', 'message' => 'success', 'data' => $list));
        die();
    }

    public function devicetoken() {
        $header = $this->input->request_headers();

        if ($this->input->method() != 'post') {
            echo json_encode(array('status' => '403', 'message' => 'Request Not Allowed.'));
            die();
        }
        $this->form_validation->set_rules('device_token', 'Device Token', 'required|trim|xss_clean');

        if ($this->form_validation->run() == FALSE) {
            $input = $this->form_validation->error_array();
            $output = $this->return_form_validation_error($input);

            echo json_encode(array('status' => '402', 'message' => $output));
            die();
        }
        $id = $this->security->xss_clean($this->input->post('device_token'));
        $check_token = $this->common->select_data_by_condition('device_token', array('device_token' => $id), '*', 'id', 'DESC', '', '', array());
        if (empty($check_token)) {
            $notification = array(
                'device_token' => $id,
            );
            $this->common->insert_data_getid($notification, "device_token");
        }

        echo json_encode(array('status' => '200', 'message' => 'success', 'data' => $id));
        die();
    }

}
