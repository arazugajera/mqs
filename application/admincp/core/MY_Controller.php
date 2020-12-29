<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class MY_Controller extends CI_Controller {

    function __construct() {
        parent::__construct();

        if (!$this->session->userdata('mqs_admin')) {
            redirect('Login', 'refresh');
        }

        //Admin details
        $this->data['adminID'] = $this->session->userdata('mqs_admin');
        $adminDetails = $this->common->selectRecordById('admin', $this->data['adminID'], 'admin_id');
        $this->data['username'] = $adminDetails['username'];
        $this->data['name'] = ucwords($adminDetails['firstname'] . ' ' . $adminDetails['lastname']);
        $this->data['email'] = $adminDetails['email'];
        $this->data['adminprofileimage'] = $adminDetails['image'];
        $this->data['admindetail'] = $adminDetails;
        
    }

    function pr($content) {
        echo "<pre>";
        print_r($content);
        echo "</pre>";
    }

    function last_url() {
        return filter_input(INPUT_SERVER, 'HTTP_REFERER', FILTER_SANITIZE_STRING);
    }

    function datetime() {
        return date('Y-m-d H:i:s');
    }

    function last_query() {
        echo "<pre>";
        echo $this->db->last_query();
        echo "</pre>";
    }

    // Function to get the client IP address

    public function verify_google_auth($code) {
        require_once(BASEPATH . 'Authenticator/rfc6238.php');

        $checkAuth = $this->common->select_data_by_condition('admin', array('admin_id' => $this->session->userdata('eblockcoin_admin')), '*', '', '', '', '', array());

        if (TokenAuth6238::verify($checkAuth[0]['google_code'], $code)) {
            return true;
        } else {
            return false;
        }
    }

    function sendEmail($app_name, $app_email, $to_email, $subject, $mail_body) {

        $this->config->load('email', TRUE);
        $this->cnfemail = $this->config->item('email');

        //Loading E-mail Class
        $this->load->library('email');
        $this->email->initialize($this->cnfemail);

        $this->email->from($app_email, $app_name);

        $this->email->to($to_email);

        $this->email->subject($subject);
        $this->email->message("<table border='0' cellpadding='0' cellspacing='0'><tr><td></td></tr><tr><td>" . $mail_body . "</td></tr></table>");
        $this->email->send();
        return;
    }

    function sendEmail_news($app_name, $app_email, $to_email, $subject, $mail_body) {

        $this->config->load('email', TRUE);
        $this->cnfemail = $this->config->item('email');

        //Loading E-mail Class
        $this->load->library('email');
        $this->email->initialize($this->cnfemail);

        $this->email->from($app_email, $app_name);

        $this->email->to($to_email);

        $this->email->subject($subject);
        $this->email->message($mail_body);
        $this->email->send();
        return;
    }

    // Function to get the client IP address
    function get_client_ip() {
        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if (getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if (getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if (getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if (getenv('HTTP_FORWARDED'))
            $ipaddress = getenv('HTTP_FORWARDED');
        else if (getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }

    function slugify($text) {

        $text = preg_replace('~[^\pL\d]+~u', '-', $text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, '-');

        // remove duplicate -
        $text = preg_replace('~-+~', '-', $text);

        // lowercase
        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }


        return $text;
    }

    function annoucementdatetime($d, $current_format = 'Y-m-d H:i:s') {
        $global_datetimeformat = 'm/d/y';
        $date = DateTime::createFromFormat($current_format, $d);
        return $date->format($global_datetimeformat);
    }

    function diplaydatetimewithformat($d, $current_format = 'Y-m-d H:i:s') {
        $global_datetimeformat = 'm/d/y H:i A';
        $date = DateTime::createFromFormat($current_format, $d);
        return $date->format($global_datetimeformat);
    }

}