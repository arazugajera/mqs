<?php

ob_start();
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * Setting.php file contains functions for managing general setting of site.
 */

class Setting extends My_Controller {

    public $data;

    public function __construct() {
        parent::__construct();
        $this->output->set_header('Last-Modified:' . gmdate('D, d M Y H:i:s') . 'GMT');
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate');
        $this->output->set_header('Cache-Control: post-check=0, pre-check=0', false);
        $this->output->set_header('Pragma: no-cache');
        $this->data['title'] = 'Settings : MQS - Motivational Quotes by Single';
        //Load header and save in variable
        $this->data['header'] = $this->load->view('header', $this->data, true);
        $this->data['sidebar'] = $this->load->view('sidebar', $this->data, true);
        $this->data['footer'] = $this->load->view('footer', $this->data, true);
        $this->data['redirect_url'] = $this->last_url();
    }

    //load listing setting view
    public function index() {
        //Addingg Setting Result to variable

        $this->data['settings'] = $this->common->select_data_by_condition('settings', array('status' => 'Enable'), '*', '', '', '', '', array());

        $this->load->view('setting/index', $this->data);
    }

    //update general setting record
    function update() {
        $setting_id = base64_decode($this->input->post('setting_id'));
        if ($this->input->is_ajax_request()) {
            if ($setting_id != '' && $setting_id != 0) {
                $this->data['setting'] = $this->common->selectRecordById('settings', $setting_id, 'setting_id');
                $this->load->view('setting/edit', $this->data);
            } else {
                echo '<div class="alert alert-danger">
                       <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                       <strong>Record not found with specified id. Try later!</strong>
                   </div>';
            }
            return;
        }
        if ($this->input->method() == 'post') {


            $this->form_validation->set_rules('setting_value', 'field_value', 'required');
            if ($this->form_validation->run() == FALSE) {
                $this->session->set_flashdata('error', 'Please follow validation rules!');
                redirect('Setting', 'refresh');
            }


            if ($setting_id != '' && $setting_id != 0) {
                $info = $this->common->selectRecordById('settings', $setting_id, 'setting_id');

                $fieldvalue = ($this->input->post('setting_value', TRUE));

                $settingdata = array('setting_value' => $fieldvalue);
                $settingInfo = $this->common->selectRecordById('settings', $setting_id, 'setting_id');
                $settingName = $settingInfo['setting_name'];
                if ($this->common->update_data($settingdata, "settings", "setting_id", $setting_id)) {

                    $this->session->set_flashdata('success', $settingName . ' updated successfully.');
                    redirect('Setting', 'refresh');
                } else {
                    $this->session->set_flashdata('error', 'There is error in updating ' . $settingName . '. Try later!');
                    redirect('Setting', 'refresh');
                }
            } else {
                $this->session->set_flashdata('error', 'Record not found with specified id. Try later!');
                redirect('Setting', 'refresh');
            }
            return;
        }
    }

}

/*  End of file Setting.php 
 *  Location: ./application/controllers/Setting.php 
 */
