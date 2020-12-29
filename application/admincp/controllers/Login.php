<?php

ob_start();
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * Login.php file contains functions for authenticate admin for login
 */

class Login extends CI_Controller {

    public $data;

    public function __construct() {

        parent::__construct();


        /* $remember_user_id = $this->input->cookie('remember_user_id', true);
          if (isset($remember_admin_id) && $remember_admin_id != '') {
          $userInfo = $this->common->selectRecordById('admin', $remember_user_id, 'userid');
          $this->session->set_userdata('smarteach_admin', $userInfo['admin_id']);
          } */
        if ($this->session->userdata('mqs_admin')) {
            redirect('Dashboard', 'refresh');
        }
        //after logout not to open page on back in browser so clear cache
        $this->output->set_header('Last-Modified:' . gmdate('D, d M Y H:i:s') . 'GMT');
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate');
        $this->output->set_header('Cache-Control: post-check=0, pre-check=0', false);
        $this->output->set_header('Pragma: no-cache');
        //get site related setting details
//        $app_name = $this->common->selectRecordById('settings', '1', 'setting_id');
//        $this->data['app_name'] = $app_name['setting_value'];
        $this->data['title'] = 'Login : MQS - Motivational Quotes by Single' ;
    }

    //show the login page
    public function index() {
        $this->load->view('login/index', $this->data);
    }

    //authenticate admin
    public function authenticate() {
        $this->form_validation->set_rules('username', 'Username', 'required');
        $this->form_validation->set_rules('password', 'Password', 'required');
        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', 'Please follow validation rules!');
            redirect($_SERVER['HTTP_REFERER'], 'refresh');
        } else {
            $userName = $this->input->post('username');
            $password = sha1($this->input->post('password'));
            $remember = $this->input->post('remember');
            $checkAuth = $this->common->selectRecordById('admin', $userName, 'username');
            if (!empty($checkAuth)) {
                $dbPassword = $checkAuth['password'];
                $dbusername = $checkAuth['username'];
                if ($userName == $dbusername && $password === $dbPassword) {

                    if ($remember != '' && $remember == 1) {
                        $cookie = array(
                            'name' => 'remember_user_id',
                            'value' => $checkAuth['admin_id'],
                            'expire' => '86500',
                        );
                        $this->input->set_cookie($cookie);
                    }
                    $this->session->set_userdata('mqs_admin', $checkAuth['admin_id']);


                    //$this->session->set_flashdata('add_class', true);
                    redirect('Dashboard', 'refresh');
                } else {
                    $this->session->set_flashdata('error', 'Username or Password you entered is incorrect OR the account does not exist!');
                    redirect('Login', 'refresh');
                }
            } else {
                $this->session->set_flashdata('error', 'Username or Password you entered is incorrect OR the account does not exist!');
                redirect('Login', 'refresh');
            }
        }
    }

    /*  public function google_auth() {
      $this->load->view('login/google_auth', $this->data);
      }

      public function verify_google_auth() {
      require_once(BASEPATH . 'Authenticator/rfc6238.php');

      $checkAuth = $this->common->select_data_by_condition('admin', array('admin_id' => $this->session->userdata('auth_user')), '*', '', '', '', '', array());

      if (TokenAuth6238::verify($checkAuth[0]['google_code'], $this->input->post('code'))) {
      $this->session->set_userdata('smarteach_admin', $this->session->userdata('auth_user'));
      redirect('Dashboard', 'refresh');
      } else {
      $this->session->set_flashdata('error', 'Verification code is invalid. Try Again.');
      redirect('Login/google_auth');
      }
      } */
}

/* 
 * End of file Login.php
 * Location: ./application/admincp/controllers/Login.php 
 */
    
