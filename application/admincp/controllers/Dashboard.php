<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * Dashboard.php file contains functions for show admin dashboard, logout, admin account, change password etc.
 * 
 *  
 */

class Dashboard extends MY_Controller {

    public $data;

    public function __construct() {

        parent::__construct();

        $this->output->set_header('Last-Modified:' . gmdate('D, d M Y H:i:s') . 'GMT');
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate');
        $this->output->set_header('Cache-Control: post-check=0, pre-check=0', false);
        $this->output->set_header('Pragma: no-cache');
        $this->data['title'] = 'Dashboard : MQS - Motivational Quotes by Single';

        //Load header and save in variable
        $this->data['header'] = $this->load->view('header', $this->data, true);
        $this->data['sidebar'] = $this->load->view('sidebar', $this->data, true);
        $this->data['footer'] = $this->load->view('footer', $this->data, true);
        $this->data['redirect_url'] = $this->last_url();
        $this->load->helper('security');
    }

    public function index() {
        //users
          $this->data['total_member'] = count($this->common->select_data_by_condition('user', array(), '*', '', '', '', '', array()));
          $this->data['total_quote'] = count($this->common->select_data_by_condition('quotes', array(), '*', '', '', '', '', array()));
          $this->load->view('dashboard/index', $this->data);
    }

    //logout from admin
    function logout() {
        if (isset($this->session->userdata['mqs_admin'])) {
            $this->session->unset_userdata('mqs_admin');
            $this->session->sess_destroy();
            redirect('Login', 'refresh');
        } else {
            $this->session->unset_userdata('mqs_admin');
            $this->session->sess_destroy();
            redirect('Login', 'refresh');
        }
    }

    //check admin name,admin email value is unique in database 
    public function checkExits() {
        $fval = $this->input->post('filed_name');
        switch ($fval) {
            case 'admin_name':
                $fieldName = 'username';
                $fieldValue = ($this->input->post('admin_name'));
                break;

            case 'admin_email':
                $fieldName = 'email';
                $fieldValue = ($this->input->post('admin_email'));
                break;

            default:
                $fieldValue = '';
                $fieldName = '';
                break;
        }

        if (trim($fieldValue) != '') {
            $res = $this->common->checkName('admin', $fieldName, $fieldValue, 'admin_id', $this->data['adminID']);
            if (empty($res)) {
                echo 'true';
                die();
            } else {
                echo 'false';
                die();
            }
        }
    }

    public function changePassword() {
        $this->load->view('dashboard/changepass', $this->data);
    }

    public function Profile() {
        $this->data['admin'] = $this->common->selectRecordById('admin', $this->data['adminID'], 'admin_id');

        $this->load->view('dashboard/profile', $this->data);
    }

    //update record 
    public function editProfile() {

        $this->form_validation->set_rules('first_name', 'firstname', 'trim|required|regex_match[/^([a-zA-Z])+$/i]|min_length[2]|max_length[20]|xss_clean|htmlspecialchars');
        $this->form_validation->set_rules('last_name', 'lastname', 'trim|required|regex_match[/^([a-zA-Z])+$/i]|min_length[2]|max_length[20]|xss_clean|htmlspecialchars');
        //$this->form_validation->set_rules('user_name', 'username', 'trim|required|regex_match[/^([a-zA-Z])+$/i]|min_length[2]|max_length[20]|xss_clean|htmlspecialchars');
        $this->form_validation->set_rules('email', 'email', 'required|valid_email|trim|xss_clean|htmlspecialchars');
        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors('<p>', '</p>'));
            redirect($this->data['redirect_url'], 'refresh');
        } else {


            $dataimage = $this->data['adminprofileimage'];
            if (isset($_FILES['image']['name']) && $_FILES['image']['name'] != null && $_FILES['image']['size'] > 0) {

                $config['upload_path'] = $this->config->item('upload_path_admin');
                $config['allowed_types'] = $this->config->item('upload_admin_allowed_types');
                $config['file_name'] = rand(10, 99) . time();
                $this->load->library('upload');
                $this->load->library('image_lib');
                // Initialize the new config
                $this->upload->initialize($config);
                //Uploading Image
                $this->upload->do_upload('image');
                //Getting Uploaded Image File Data
                $imgdata = $this->upload->data();
                $imgerror = $this->upload->display_errors();

                // print_r($imgerror);die();
                if ($imgerror == '') {
                    $config['source_image'] = $config['upload_path'] . $imgdata['file_name'];
                    $config['new_image'] = $this->config->item('upload_path_admin_thumb') . $imgdata['file_name'];
                    //$config['create_thumb'] = TRUE;
                    $config['maintain_ratio'] = FALSE;
                    //$config['thumb_marker'] = '';
                    $config['width'] = $this->config->item('admin_thumb_width');
                    $config['height'] = $this->config->item('admin_thumb_height');

                    //Loading Image Library
                    $this->image_lib->initialize($config);
                    $dataimage = $imgdata['file_name'];

                    //Creating Thumbnail
                    $this->image_lib->resize();
                    $thumberror = $this->image_lib->display_errors();

                    // delete previous images
                    if ($this->data['adminprofileimage'] != '') {
                        if (file_exists($this->config->item('upload_path_admin') . $this->data['adminprofileimage'])) {
                            @unlink($this->config->item('upload_path_admin') . $this->data['adminprofileimage']);
                        }
                        if (file_exists($this->config->item('upload_path_admin_thumb') . $this->data['adminprofileimage'])) {
                            @unlink($this->config->item('upload_path_admin_thumb') . $this->data['adminprofileimage']);
                        }
                    }
                } else {
                    $thumberror = '';
                    $dataimage = '';
                }
            }

            $updateData = array(
                "firstname" => $this->input->post('first_name'),
                "lastname" => $this->input->post('last_name'),
                //"username" => $this->input->post('user_name'),
                "email" => $this->input->post('email'),
                "image" => $dataimage,
                "modified_date" => date('Y-m-d H:i:s'),
                "modified_ip" => $this->input->ip_address()
            );

            $res = $this->common->update_data($updateData, 'admin', 'admin_id', $this->data['adminID']);
            if ($res) {
                $this->session->set_flashdata('success', 'Profile updated successfully.');
                redirect('Dashboard', 'refresh');
            } else {
                $this->session->set_flashdata('error', 'There is error in updated profile. Try later!');
                redirect('Dashboard', 'refresh');
            }
        }
    }

    //password exist
    function pwdexist() {

        $pwd = sha1($this->input->post('old_password'));
        $res = $this->common->select_data_by_id('admin', 'admin_id', $this->data['adminID'], 'password', array());
        $encryptedPassword = $res[0]['password'];
        if ($pwd != '') {
            if ($pwd === $encryptedPassword) {
                echo 'true';
                die();
            } else {
                echo 'false';
                die();
            }
        } else {
            echo 'false';
            die();
        }
    }

    //change password
    public function change_password() {

        if ($this->input->method() == 'post') {
            $redirect = '';
            $last_url = $this->last_url();
            if ($last_url != '') {
                $redirect = $last_url;
            } else {
                $redirect = 'dashboard';
            }
            $this->load->library('form_validation');
            $this->form_validation->set_rules('old_password', 'Old Password', 'trim|required|min_length[6]|max_length[16]|xss_clean|htmlspecialchars');
            $this->form_validation->set_rules('new_password', 'New Password', 'trim|required|min_length[6]|max_length[16]|xss_clean|htmlspecialchars');
            $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'trim|required|min_length[6]|max_length[16]|xss_clean|htmlspecialchars');
            if ($this->form_validation->run() == FALSE) {
                $this->session->set_flashdata('error', 'Please follow all validation rules.');
                redirect($redirect, 'refresh');
            }
            $checkAuth = $this->common->selectRecordById('admin', $this->data['adminID'], 'admin_id');
            $password = sha1($this->input->post('old_password'));
            $dbPassword = $checkAuth['password'];
            if ($password !== $dbPassword) {
                $this->session->set_flashdata('error', 'Please enter correct old password.');
                redirect($redirect, 'refresh');
            }
            $newpassword = $this->input->post('new_password');
            $confirmpass = $this->input->post('confirm_password');
            if ($newpassword != $confirmpass) {
                $this->session->set_flashdata('error', 'New password and Confirm password must be same.');
                redirect($redirect, 'refresh');
            }
            $updatedPassword = sha1($newpassword);
            $data = array('password' => $updatedPassword, 'modified_date' => date('Y-m-d H:i:s'));
            if ($this->common->update_data($data, 'admin', 'admin_id', $this->data['adminID'])) {
                //echo $this->last_query();die();
                $this->session->set_flashdata('success', 'Password changed successfully.');
                redirect($redirect, 'refresh');
            } else {
                $this->session->set_flashdata('error', 'Error Occurred. Try Again!');
                redirect($redirect, 'refresh');
            }
        }
    }

}

/* End of file Dashboard.php */
/* Location: ./application/admincp/controllers/Dashboard.php */
    
