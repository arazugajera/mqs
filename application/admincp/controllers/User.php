<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
ini_set('display_errors',1);
class User extends MY_Controller {

    public $data;

    public function __construct() {

        parent::__construct();

        $this->output->set_header('Last-Modified:' . gmdate('D, d M Y H:i:s') . 'GMT');
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate');
        $this->output->set_header('Cache-Control: post-check=0, pre-check=0', false);
        $this->output->set_header('Pragma: no-cache');
        $this->data['title'] = 'User : MQS - Motivational Quotes by Single';

//Load header and save in variable
        $this->data['header'] = $this->load->view('header', $this->data, true);
        $this->data['sidebar'] = $this->load->view('sidebar', $this->data, true);
        $this->data['footer'] = $this->load->view('footer', $this->data, true);
        $this->data['redirect_url'] = $this->last_url();
        $this->load->helper('security');
        $this->load->library('user_agent');
    }

    public function index() {
        $this->load->view('user/index', $this->data);
    }

    public function gettabledata() {
        $columns = array('user.id', 'user.name','user.image');
        $request = $this->input->get();
        $condition = array();
        $join_str = array();
        $getfiled = "user.id,user.name,user.image";
        echo $this->common->getDataTableSource('user', $columns, $condition, $getfiled, $request, $join_str, '');
//echo '<pre>';        print_r($this->db->last_query());
        die();
    }

    public function add() {

        $this->load->view('user/add', $this->data);
    }

    public function addnew() {
        $this->form_validation->set_rules('name', 'User name', 'required|trim|strip_tags|xss_clean');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors('<div class="error">', '</div>'));
            redirect('User', 'refresh');
        } else {
            $dataimage = '';
            if (isset($_FILES['image']['name']) && $_FILES['image']['name'] != null && $_FILES['image']['size'] > 0) {

                $config['upload_path'] = $this->config->item('upload_path_user');
                $config['allowed_types'] = $this->config->item('upload_user_allowed_types');
                $config['file_name'] = rand(10, 99) . time();
                $this->load->library('upload');
                $this->load->library('image_lib');
//$this->load->library('image_lib');
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
                    $config['new_image'] = $this->config->item('upload_path_user_thumb') . $imgdata['file_name'];
//$config['create_thumb'] = TRUE;
                    $config['maintain_ratio'] = FALSE;
//$config['thumb_marker'] = '';
                    $config['width'] = $this->config->item('user_thumb_width');
                    $config['height'] = $this->config->item('user_thumb_height');

//Loading Image Library
                    $this->image_lib->initialize($config);
                    $dataimage = $imgdata['file_name'];

//Creating Thumbnail
                    $this->image_lib->resize();
                    $thumberror = $this->image_lib->display_errors();
                } else {
                    $thumberror = '';
                    $dataimage = '';
                }
            }
            $insert_data = array(
                "name" => $this->security->xss_clean(ucwords($this->input->post('name'))),
                "image" => $dataimage,
                "created_datetime" => date('Y-m-d H:i:s'),
                "created_ip" => $this->input->ip_address(),
                "modified_ip" => $this->input->ip_address(),
                "modified_datetime" => date('Y-m-d H:i:s'),
                "created_browser" => $this->security->xss_clean($this->agent->browser()),
                "created_os" => $this->security->xss_clean($this->agent->platform()),
                "added_by" => $this->data['adminID'],
                "modified_browser" => $this->security->xss_clean($this->agent->browser()),
                "modified_os" => $this->security->xss_clean($this->agent->platform()),
                "modified_by" => $this->data['adminID'],
            );
            $user = $this->common->insert_data_getid($insert_data, "user");
            if ($user) {

                $this->session->set_flashdata('success', 'User added successfully.');
                redirect('User', 'refresh');
            } else {
                $this->session->set_flashdata('error', 'There is an error occured. please try after again');
                redirect('User', 'refresh');
            }
        }
    }

    function password_generate($chars) {
        $data = '1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcefghijklmnopqrstuvwxyz';
        return substr(str_shuffle($data), 0, $chars);
    }
    public function view($id) {
        $sub_id = base64_decode($id);
        $this->data['info'] = $this->common->select_data_by_condition('user', array('user.id' => $sub_id), '*', '', '', '', '', array());
        if (empty($this->data['info'])) {
            $this->session->set_flashdata('error', 'No information found!');
            redirect('User', 'refresh');
        } else {
             $this->data['quotes'] = $this->common->select_data_by_condition('quotes', array('user_id' => $sub_id), '*', 'id', 'DESC', '', '', array());
       
            $this->load->view('user/view', $this->data);
        }
    }
    public function edit($id) {
        $sub_id = base64_decode($id);
        $this->data['info'] = $this->common->select_data_by_condition('user', array('user.id' => $sub_id), '*', '', '', '', '', array());
        if (empty($this->data['info'])) {
            $this->session->set_flashdata('error', 'No information found!');
            redirect('User', 'refresh');
        } else {
            $this->load->view('user/edit', $this->data);
        }
    }

    public function editnew($id) {
        $sub_id = base64_decode($id);

        $this->form_validation->set_rules('name', 'User name', 'required|trim|strip_tags|xss_clean');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors('<div class="error">', '</div>'));
            redirect('User/edit' . $id, 'refresh');
        } else {
            $info = $this->common->select_data_by_condition('user', array('user.id' => $sub_id), '*', '', '', '', '', array());

            $dataimage = $info[0]['image'];
            if (isset($_FILES['image']['name']) && $_FILES['image']['name'] != null && $_FILES['image']['size'] > 0) {

                $config['upload_path'] = $this->config->item('upload_path_user');
                $config['allowed_types'] = $this->config->item('upload_user_allowed_types');
                $config['file_name'] = rand(10, 99) . time();
                $this->load->library('upload');
                $this->load->library('image_lib');
//$this->load->library('image_lib');
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
                    $config['new_image'] = $this->config->item('upload_path_user_thumb') . $imgdata['file_name'];
//$config['create_thumb'] = TRUE;
                    $config['maintain_ratio'] = FALSE;
//$config['thumb_marker'] = '';
                    $config['width'] = $this->config->item('user_thumb_width');
                    $config['height'] = $this->config->item('user_thumb_height');

//Loading Image Library
                    $this->image_lib->initialize($config);
                    $dataimage = $imgdata['file_name'];

//Creating Thumbnail
                    $this->image_lib->resize();
                    $thumberror = $this->image_lib->display_errors();
                    
                    if ($info[0]['image'] != '') {
                        if (file_exists($this->config->item('upload_path_user') . $info[0]['image'])) {
                            @unlink($this->config->item('upload_path_user') . $info[0]['image']);
                        }
                        if (file_exists($this->config->item('upload_path_user_thumb') . $info[0]['image'])) {
                            @unlink($this->config->item('upload_path_user_thumb') . $info[0]['image']);
                        }
                    }
                } else {
                    $thumberror = '';
                    $dataimage = '';
                }
            }
            $update_data = array(
                "name" => $this->security->xss_clean(ucwords($this->input->post('name'))),
                "image" => $dataimage,
                "modified_ip" => $this->input->ip_address(),
                "modified_datetime" => date('Y-m-d H:i:s'),
                "modified_browser" => $this->security->xss_clean($this->agent->browser()),
                "modified_os" => $this->security->xss_clean($this->agent->platform()),
                "modified_by" => $this->data['adminID'],
            );
//echo '<pre>';            print_r($update_data); die;
            $user = $this->common->update_data($update_data, 'user', 'user.id', $sub_id);

            if ($user) {
                $this->session->set_flashdata('success', 'User updated successfully.');
                redirect('User', 'refresh');
            } else {
                $this->session->set_flashdata('error', 'There is an error occured. please try after again');
                redirect('User', 'refresh');
            }
        }
    }

    function delete() {
        $json = array();
        $json['msg'] = '';
        $json['status'] = 'fail';
        $id = $this->input->post('id');
//echo $id; die;
        $group = $this->common->select_data_by_condition('user', array('user.id' => $id), '*', '', '', '', '', array());
        if (!empty($group)) {

            $res = $this->common->delete_data('user', 'user.id', $id);
            if ($res) {
                if (file_exists($this->config->item('upload_path_user') . $group[0]['image'])) {
                    @unlink($this->config->item('upload_path_user') . $group[0]['image']);
                }
                if (file_exists($this->config->item('upload_path_user_thumb') . $group[0]['image'])) {
                    @unlink($this->config->item('upload_path_user_thumb') . $group[0]['image']);
                }
                $this->common->delete_data('quotes', 'user_id', $id);
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

    function update_status() {
        $json = array();
        $json['status'] = 'fail';
        $json['msg'] = '';
        $id = $this->input->post('id');
        $status = $this->input->post('status');
        $reason = $this->input->post('reason');
        if ($status == 'Enable') {
            $status = 'Disable';
        } else {
            $status = 'Enable';
        }

        $data = $this->common->select_data_by_condition('user', array('user.id' => $id), '*', '', '', '', '', array());
        if (empty($data)) {
            $json['msg'] = 'No information Found!';
        } else {
            $result = $this->common->update_data(array('status' => $status), 'user', 'user.id', $id);
            if ($result) {
                $name = ucfirst($data[0]['name']);
                $email = $data[0]['email'];
                $site_logo = base_url() . '/assets/images/logo.jpg';
                $year = date('Y');
                if ($status == 'Enable') {

                    $mailData = $this->common->select_data_by_id('email_format', 'id', 13, '*', array());
                    $subject = str_replace('%site_name%', $this->data['app_name'], $mailData[0]['subject']);
                    $mailformat = $mailData[0]['emailformat'];
                    $this->data['mail_body'] = str_replace("%site_logo%", $site_logo, str_replace("%name%", $name, str_replace("%reason%", $reason, str_replace("%site_name%", $this->data['app_name'], str_replace("%year%", $year, stripslashes($mailformat))))));
//$this->data['mail_header'] = '<img id="headerImage campaign-icon" src="' . $site_logo . '" title="' . $this->data["site_name"] . '" width="250" /> ';
                    $this->data['mail_header'] = $this->data['app_name'];
                    $this->data['mail_footer'] = '<a href="' . site_url() . '">' . $this->data['app_name'] . '</a> | Copyright &copy;' . $year . ' | All rights reserved</p>';
                    $mail_body = $this->load->view('mail', $this->data, true);
// echo '<pre>';                    print_r($mail_body); die;
                    $this->sendEmail($this->data['app_name'], $this->data['app_email'], $email, $subject, $mail_body);
                } else {
                    $mailData = $this->common->select_data_by_id('email_format', 'id', 16, '*', array());
                    $subject = str_replace('%site_name%', $this->data['app_name'], $mailData[0]['subject']);
                    $mailformat = $mailData[0]['emailformat'];
                    $this->data['mail_body'] = str_replace("%site_logo%", $site_logo, str_replace("%name%", $name, str_replace("%reason%", $reason, str_replace("%site_name%", $this->data['app_name'], str_replace("%year%", $year, stripslashes($mailformat))))));
//$this->data['mail_header'] = '<img id="headerImage campaign-icon" src="' . $site_logo . '" title="' . $this->data["site_name"] . '" width="250" /> ';
                    $this->data['mail_header'] = $this->data['app_name'];
                    $this->data['mail_footer'] = '<a href="' . site_url() . '">' . $this->data['app_name'] . '</a> | Copyright &copy;' . $year . ' | All rights reserved</p>';
                    $mail_body = $this->load->view('mail', $this->data, true);
//  echo '<pre>';                    print_r($mail_body); die;
                    $this->sendEmail($this->data['app_name'], $this->data['app_email'], $email, $subject, $mail_body);
                }
                $json['status'] = 'success';
                $json['msg'] = 'Status has been updated';
            } else {
                $json['msg'] = 'Sorry! Something went wrong please try again!';
            }
        }
        echo json_encode($json);
        die();
    }

    public function emailExits() {
        $email = $this->input->post('email');

        if (trim($email) != '') {
            $res = $this->common->check_unique_avalibility('user', 'email', $email, '', '', array('is_deleted' => '0'));

            if (empty($res)) {
                echo 'true';
                die();
            } else {
                echo 'false';
                die();
            }
        } else {
            echo 'true';
            die();
        }
    }

    public function emailExitsedit() {
        $email = $this->input->post('email');
        $id = $this->input->post('id');

        if (trim($email) != '') {
            $res = $this->common->check_unique_avalibility('user', 'email', $email, 'user.id', $id, array('is_deleted' => '0'));

            if (empty($res)) {
                echo 'true';
                die();
            } else {
                echo 'false';
                die();
            }
        } else {
            echo 'true';
            die();
        }
    }

    public function resendPass() {
        $json = array();
        $json['status'] = 'fail';
        $json['msg'] = '';
        $id = $this->input->post('id');
        $info = $this->common->select_data_by_condition('user', array('user.id' => $id), '*', '', '', '', '', array());
//echo '<pre>';        print_r($info); die;
        if (empty($info)) {
            $json['msg'] = 'No information found!';
            echo json_encode($json);
            die();
        }
        $password = $this->input->post('newpass');

        $info1 = array(
            "password" => sha1($password),
            "modified_ip" => $this->input->ip_address(),
            "modified_datetime" => date('Y-m-d H:i:s'),
        );
        $res = $this->common->update_data($info1, 'user', 'user.id', $info[0]['user.id']);

        $name = ucfirst($info[0]['name']);
        $email = $info[0]['email'];

        $site_logo = base_url() . '/assets/images/logo.jpg';

        $year = date('Y');
        $mailData = $this->common->select_data_by_id('email_format', 'id', 5, '*', array());
        $subject = str_replace('%site_name%', $this->data['app_name'], $mailData[0]['subject']);
        $mailformat = $mailData[0]['emailformat'];
        $this->data['mail_body'] = str_replace("%site_logo%", $site_logo, str_replace("%name%", $name, str_replace("%password%", $password, str_replace("%email%", $email, str_replace("%site_name%", $this->data['app_name'], str_replace("%year%", $year, stripslashes($mailformat)))))));
//$this->data['mail_header'] = '<img id="headerImage campaign-icon" src="' . $site_logo . '" title="' . $this->data["site_name"] . '" width="250" /> ';
        $this->data['mail_header'] = $this->data['app_name'];
        $this->data['mail_footer'] = '<a href="' . site_url() . '">' . $this->data['app_name'] . '</a> | Copyright &copy;' . $year . ' | All rights reserved</p>';
        $mail_body = $this->load->view('mail', $this->data, true);
//  echo '<pre>';                    print_r($mail_body); die;
        $this->sendEmail($this->data['app_name'], $this->data['app_email'], $email, $subject, $mail_body);

        if ($res) {
            $json['status'] = 'success';
            $json['msg'] = 'Password has been resent successfully.';
        } else {
            $json['msg'] = 'Sorry! something went wrong please try later!';
        }
        echo json_encode($json);
        die();
    }

}
