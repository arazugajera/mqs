<?php

ob_start();
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * ForgotPassword.php file contains functions for authenticate admin for login
 */

class ForgotPassword extends CI_Controller {

    public $data;

    public function __construct() {
        parent::__construct();
        $app_name = $this->common->selectRecordById('settings', '1', 'setting_id');
        $this->data['app_name'] = $app_name['setting_value'];
        $this->data['title'] = 'Reset Password : ' . $this->data['app_name'];
    }

    //forgot password
    public function index() {

        $forgotEmail = $this->input->post('forgot_email');
        $checkAuth = $this->common->selectRecordById('admin', $forgotEmail, 'email');
        if (!empty($checkAuth)) {
            $slug = $checkAuth['firstname'] . '_' . $checkAuth['lastname'] . '_' . rand(1000, 9999) . $checkAuth['admin_id'];
            $update_data = array('admin_slug' => $slug, 'modified_date' => date('Y-m-d H:i:s'));
            $this->common->update_data($update_data, 'admin', 'admin_id', $checkAuth['admin_id']);
            $name = $checkAuth['firstname'] . ' ' . $checkAuth['lastname'];
            $new_password_link = '<a title="Reset Password" href="' . site_url('ForgotPassword/reset_password/' . $slug) . '">Click Here</a>';

            if ($checkAuth['role'] == 1) {
                $mailData = $this->common->selectRecordById('email_format', '1', 'id');
                $subject = $mailData['subject'];
                $mailformat = $mailData['emailformat'];
                $app_name = $this->common->selectRecordById('settings', '2', 'setting_id');
                $app_email = $app_name['setting_value'];
                $this->data['mail_body'] = str_replace("%name%", $name, str_replace("%reset_link%", $new_password_link, str_replace("%site_name%", $this->data['app_name'], str_replace("%siteurl%", $this->data['app_name'], stripslashes($mailformat)))));
                //$this->data['mail_header'] = '<img id="headerImage campaign-icon" src="' . $site_logo . '" title="' . $this->data["site_name"] . '" width="250" /> ';
                $this->data['mail_header'] = $this->data['app_name'];
                $this->data['mail_footer'] = '<a href="' . site_url() . '">' . $this->data['app_name'] . '</a> | Copyright &copy;' . $year . ' | All rights reserved</p>';
                $mail_body = $this->load->view('mail', $this->data, true);
//            print_r($mail_body);
//            die();
                $this->sendEmail($this->data['app_name'], $app_email, $forgotEmail, $subject, $mail_body);
            } else if ($checkAuth['role'] == 2) {
                $mailData = $this->common->selectRecordById('email_format', '9', 'id');
                $subject = $mailData['subject'];
                $mailformat = $mailData['emailformat'];
                $app_name = $this->common->selectRecordById('settings', '2', 'setting_id');
                $app_email = $app_name['setting_value'];
                $this->data['mail_body'] = str_replace("%name%", $name, str_replace("%reset_link%", $new_password_link, str_replace("%site_name%", $this->data['app_name'], str_replace("%siteurl%", $this->data['app_name'], stripslashes($mailformat)))));
                //$this->data['mail_header'] = '<img id="headerImage campaign-icon" src="' . $site_logo . '" title="' . $this->data["site_name"] . '" width="250" /> ';
                $this->data['mail_header'] = $this->data['app_name'];
                $this->data['mail_footer'] = '<a href="' . site_url() . '">' . $this->data['app_name'] . '</a> | Copyright &copy;' . $year . ' | All rights reserved</p>';
                $mail_body = $this->load->view('mail', $this->data, true);
//            print_r($mail_body);
//            die();
                $this->sendEmail($this->data['app_name'], $app_email, $forgotEmail, $subject, $mail_body);
            }

            $this->session->set_flashdata('success', 'An Email has been sent to the supplied email address. Follow the instructions in the email to reset your password!');
            redirect('login', 'refresh');
        } else {
            $this->session->set_flashdata('error', 'Oops, it is not a registered email address!');
            redirect('login', 'refresh');
        }
    }

    //reset password
    public function reset_password($slug = '') {
        $checkAuth = $this->common->selectRecordById('admin', $slug, 'admin_slug');

        if ($this->input->method() == 'post') {
            $newpassword = $this->input->post('password');
            $confirmpass = $this->input->post('cnfpassword');
            if ($newpassword != $confirmpass) {
                $this->session->set_flashdata('error', 'New password and Confirm password must be same.');
                redirect('login', 'refresh');
            }
            $time = $checkAuth['modified_date'];
            if ($this->input->server('REQUEST_TIME') - strtotime($time) > 60 * 60 * 24) {
                $this->session->set_flashdata('error', 'You password reset link is expired.');
                redirect('login', 'refresh');
            }
            $updatedPassword = sha1($newpassword);
            $slug = $checkAuth['firstname'] . '_' . $checkAuth['lastname'] . '_' . rand(1000, 9999) . $checkAuth['admin_id'];
            if ($this->common->update_data(array('password' => $updatedPassword, 'admin_slug' => $slug, 'modified_date' => date('Y-m-d H:i:s')), 'admin', 'admin_id', $checkAuth['admin_id'])) {
                $this->session->set_flashdata('success', 'New password set successfully.');
                redirect('login', 'refresh');
            } else {
                $this->session->set_flashdata('error', 'Error Occurred. Try Again!');
                redirect('login', 'refresh');
            }
        }
        $this->data['slug'] = $this->uri->segment(2);
        $this->load->view('login/changePassword', $this->data);
    }

    //send email
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

}

/* 
 * End of file ForgotPassword.php
 * Location: ./application/admincp/controllers/ForgotPassword.php 
 */
