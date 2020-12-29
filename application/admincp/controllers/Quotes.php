
<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
ini_set('display_errors', 0);

class Quotes extends MY_Controller {

    public $data;

    public function __construct() {

        parent::__construct();

        $this->output->set_header('Last-Modified:' . gmdate('D, d M Y H:i:s') . 'GMT');
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate');
        $this->output->set_header('Cache-Control: post-check=0, pre-check=0', false);
        $this->output->set_header('Pragma: no-cache');
        $this->data['title'] = 'Quotes : MQS - Motivational Quotes by Single';

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
        $this->load->view('quotes/index', $this->data);
    }

    public function gettabledata() {
        $columns = array('quotes.id', 'u.name', 'quotes.quote', 'quotes.color_code');
        $request = $this->input->get();
        $condition = array();
        $join_str = array();
        $join_str[0] = array(
            'table' => 'user as u',
            'join_table_id' => 'u.id',
            'from_table_id' => 'quotes.user_id',
            'join_type' => ''
        );
        $getfiled = "quotes.id,u.name,quotes.quote,quotes.color_code";
        echo $this->common->getDataTableSource('quotes', $columns, $condition, $getfiled, $request, $join_str, '');
        //echo '<pre>';        print_r($this->db->last_query());
        die();
    }

    public function add() {

        $this->load->view('quotes/add', $this->data);
    }

    public function addnew() {
        $this->form_validation->set_rules('name', 'User name', 'required|trim|strip_tags|xss_clean');
        $this->form_validation->set_rules('quote', 'Quote', 'required|xss_clean');
        $this->form_validation->set_rules('color_code', 'Color Code', 'required|trim|strip_tags|xss_clean');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors('<div class="error">', '</div>'));
            redirect('Quotes', 'refresh');
        } else {

            $insert_data = array(
                "user_id" => $this->security->xss_clean(($this->input->post('name'))),
                "quote" => $this->security->xss_clean(($this->input->post('quote'))),
                "color_code" => $this->security->xss_clean(($this->input->post('color_code'))),
                "created_datetime" => date('Y-m-d H:i:s'),
                "created_ip" => $this->input->ip_address(),
                "modified_ip" => $this->input->ip_address(),
                "modified_datetime" => date('Y-m-d H:i:s'),
            );
            $user = $this->common->insert_data_getid($insert_data, "quotes");
            if ($user) {
                $this->session->set_flashdata('success', 'Quote added successfully.');
                redirect('Quotes', 'refresh');
            } else {
                $this->session->set_flashdata('error', 'There is an error occured. please try after again');
                redirect('Quotes', 'refresh');
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

    public function edit($id) {
        $sub_id = base64_decode($id);
        $this->data['info'] = $this->common->select_data_by_condition('quotes', array('id' => $sub_id), '*', '', '', '', '', array());
        if (empty($this->data['info'])) {
            $this->session->set_flashdata('error', 'No information found!');
            redirect('Quotes', 'refresh');
        } else {
            $this->load->view('quotes/edit', $this->data);
        }
    }

    public function editnew($id) {
        $sub_id = base64_decode($id);

        $this->form_validation->set_rules('name', 'User name', 'required|trim|strip_tags|xss_clean');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors('<div class="error">', '</div>'));
            redirect('Quotes/edit' . $id, 'refresh');
        } else {
            $info = $this->common->select_data_by_condition('user', array('user.id' => $sub_id), '*', '', '', '', '', array());


            $update_data = array(
                "user_id" => $this->security->xss_clean(($this->input->post('name'))),
                "quote" => $this->security->xss_clean(($this->input->post('quote'))),
                "color_code" => $this->security->xss_clean(($this->input->post('color_code'))),
                "modified_ip" => $this->input->ip_address(),
                "modified_datetime" => date('Y-m-d H:i:s'),
            );
            //echo '<pre>';            print_r($update_data); die;
            $user = $this->common->update_data($update_data, 'quotes', 'id', $sub_id);

            if ($user) {
                $this->session->set_flashdata('success', 'Quote updated successfully.');
                redirect('Quotes', 'refresh');
            } else {
                $this->session->set_flashdata('error', 'There is an error occured. please try after again');
                redirect('Quotes', 'refresh');
            }
        }
    }

    function delete() {
        $json = array();
        $json['msg'] = '';
        $json['status'] = 'fail';
        $id = $this->input->post('id');
        //echo $id; die;
        $group = $this->common->select_data_by_condition('quotes', array('id' => $id), '*', '', '', '', '', array());
        if (!empty($group)) {

            $res = $this->common->delete_data('quotes', 'id', $id);
            if ($res) {
                $json['msg'] = 'Record has been deleted successfully';
                $json['status'] = 'success';
            } else {
                $json['msg'] = 'Sorry! something went wrong Please try later!';
            }
        } else {
            $json['msg'] = 'Sorry! No information found!';
        }
        echo json_encode($json);
        die();
    }

}
