<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Webservice1 extends MY_Controller {

    public $data;

    public function __construct() {

        parent::__construct();
        $this->load->model('Webservice_model');
        $this->load->model('search');
        $this->load->library('user_agent');
    }

    /*
     * Register User webservice
     */

    public function signup() {

        if ($this->input->method() != 'post') {
            echo json_encode(array('status' => '403', 'message' => 'Request Not Allowed.'));
            die();
        }

// validations check
        $this->form_validation->set_rules('firstname', 'firstname', 'required');
        $this->form_validation->set_rules('lastname', 'lastname', 'required');
        $this->form_validation->set_rules('email', 'email', 'required|valid_email');
        $this->form_validation->set_rules('password', 'password', 'required');
        // $this->form_validation->set_rules('repassword', 'repassword', 'required');

        if ($this->form_validation->run() === FALSE) {

            echo json_encode(array('status' => '402', 'message' => 'Invalid Data Format.'));
            die();
        }

//check email exist
        if (!$this->check_email($this->input->post('email'), '')) {
            echo json_encode(array('status' => '402', 'message' => 'Email id already exists.'));
            die();
        }

        $firstname = $this->input->post('firstname', TRUE);
        $lastname = $this->input->post('lastname', TRUE);
        $email = $this->input->post('email', TRUE);
        $encry_pass = password_hash($this->input->post('password', TRUE), PASSWORD_BCRYPT);
        $rand = rand(100, 999);
        $user_register = array(
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email_id' => $this->input->post('email', TRUE),
            'user_slug' => $firstname . '-' . $lastname . dechex(mt_rand(11111111, 99999999)),
            'password' => $encry_pass,
            'status' => 'Disable',
            'profile_image' => '',
            "activation_code" => uniqid($rand, TRUE),
            "activation_code_expire" => date("Y-m-d H:i:s", strtotime('+24 hours')),
            'auth_enable' => 'No',
            'created_datetime' => date('Y-m-d H:i:s'),
            'created_ip' => $this->input->ip_address(),
            'modified_datetime' => date('Y-m-d H:i:s'),
            "color_code" => 'D7DCDE',
            'modified_ip' => $this->input->ip_address(),
        );




        $user_id = $this->common->insert_data_getid($user_register, 'users');

        if ($user_id) {
            $this->common->insert_data(array('user_id' => $user_id), 'email_notification_setting');
            $login_log = array(
                'userid' => $user_id,
                'browser' => $this->agent->browser() . '-' . $this->agent->version(),
                'operating_system' => $this->agent->platform(),
                'created_datetime' => date('Y-m-d H:i:s'),
                'activity_type' => "Register",
                'is_active' => 0,
                'login_ip' => $this->get_client_ip(),
            );

            $this->common->insert_data($login_log, 'login_log');

            $link = '<a href="' . site_url('Verifyemail/index/' . $user_register['activation_code']) . '" class="btn-primary" itemprop="url" style="font-family: \'Helvetica Neue\',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; color: #FFF; text-decoration: none; line-height: 2em; font-weight: bold; text-align: center; cursor: pointer; display: inline-block; border-radius: 5px; text-transform: capitalize; background-color: #348eda; margin: 0; border-color: #348eda; border-style: solid; border-width: 10px 20px;">Confirm email address</a>';

            $mailData = $this->common->select_data_by_id('mailformat', 'mail_id', '5', '*', array());
            $subject = $mailData[0]['subject'];
            $mailformat = $mailData[0]['mailformat'];

            $mail_body = str_replace("%email%", $email, $mailformat);
            $mail_body = str_replace("%active_link%", $link, $mail_body);
            $mail_body = str_replace("%sitename%", $this->data['site_name'], $mail_body);

            $this->sendEmail($this->data['site_name'], $this->data['site_email'], $email, $subject, $mail_body);
            echo json_encode(array('status' => '200', 'message' => 'Registration successfull. Activation email has been sent.'));
            die();
        } else {
            echo json_encode(array('status' => '403', 'message' => 'Error Occured!. Please Try again'));
            die();
        }
    }

    /*
     * Check User Already Register or Not
     */

    public function check_email_exist() {
        if ($this->input->method() != 'post') {
            echo json_encode(array('status' => '403', 'message' => 'Request Not Allowed.'));
            die();
        }
        $this->form_validation->set_rules('email', 'email', 'required');

        if ($this->form_validation->run() === FALSE) {
            echo json_encode(array('status' => '402', 'message' => 'Invalid Data Format.'));
            die();
        }
        $email = $this->input->post('email');
        $check_data = $this->common->select_data_by_id('users', 'email_id', $email, 'id', array());

        if (empty($check_data)) {
            echo json_encode(array('status' => '200', 'message' => 'Email does not exist'));
            die();
        } else {
            echo json_encode(array('status' => '403', 'message' => 'email already exists'));
            die();
        }
    }

    /*
     * Display Country List
     */

    public function get_country() {

        $country_record = $this->common->get_all_record('country', 'id,nicename');
        if (!empty($country_record)) {
            echo json_encode(array('status' => '200', 'message' => 'List of country', 'country_data' => $country_record));
            die();
        } else {
            echo json_encode(array('status' => '404', 'message' => 'Data Not found!', 'country_data' => array()));
            die();
        }
    }

    /*
     * User Login
     */

    public function login() {
        if ($this->input->method() != 'post') {
            echo json_encode(array('status' => '403', 'message' => 'Request Not Allowed.'));
            die();
        }

        $this->form_validation->set_rules('email', 'email', 'required|valid_email');
        $this->form_validation->set_rules('password', 'password', 'required');
        $this->form_validation->set_rules('device_type', 'device_type', 'required');
        $this->form_validation->set_rules('device_id', 'device_id', 'required');



        if ($this->form_validation->run() === FALSE) {
            echo json_encode(array('status' => '402', 'message' => 'Invalid Data Format.'));
            die();
        }

        $post_email = $this->input->post('email');
        $post_password = $this->input->post('password');
        //$remember = $this->input->post('remember');
        $checkAuth = $this->common->select_data_by_id('users', 'email_id', $post_email, '*', array());
//echo $this->db->last_query(); die();

        if (empty($checkAuth)) {
            echo json_encode(array('status' => '402', 'message' => 'Email id or Password is Invalid.'));
            die();
        } elseif ($checkAuth[0]['activation_code'] != null) {
            echo json_encode(array('status' => '402', 'message' => 'Your email is not verified, verification email was sent to your email.'));
            die();
        } elseif ($checkAuth[0]['status'] == 'Disable') {
            echo json_encode(array('status' => '402', 'message' => 'Your account is disabled by admin. Please contact to support.'));
            die();
        }
        $login_log = array(
            'userid' => $checkAuth[0]['id'],
            'browser' => $this->agent->browser() . '-' . $this->agent->version(),
            'operating_system' => $this->agent->platform(),
            'created_datetime' => date('Y-m-d H:i:s'),
            'activity_type' => "Login",
            'is_active' => 1,
            'login_ip' => $this->get_client_ip(),
        );

        $this->common->insert_data($login_log, 'login_log');
        if ($this->input->post('email') === $checkAuth[0]['email_id'] && password_verify($this->input->post('password'), $checkAuth[0]['password'])) {
            $api_token = time() . rand(10000, 99999);
            $user_token_data = array(
                'device_type' => $this->input->post('device_type'),
                'notify_id' => $this->input->post('device_id'),
                'created_date' => date('Y-m-d H:i:s'),
                'user_id' => $checkAuth[0]['id'],
                'fcm_id' => $this->input->post('fcm_id'),
                'ios_id' => $this->input->post('ios_id'),
                'api_token' => $api_token
            );

            $this->common->insert_data($user_token_data, 'users_token');
            if ($checkAuth[0]['profile_image'] == '') {
                $checkAuth[0]['profile_image'] = base_url() . '../uploads/profile.png';
            } else {
                $checkAuth[0]['profile_image'] = base_url() . $this->config->item('user_profile_upload_thumb_path') . $checkAuth[0]['profile_image'];
            }

            $checkAuth[0]['user_token'] = $api_token;
            unset($checkAuth[0]['password']);
            echo json_encode(array('status' => '200', 'message' => 'Login Succesfully.', 'user_data' => $checkAuth));
            die();
        } else {
            echo json_encode(array('status' => '402', 'message' => 'Email id or Password is Invalid.'));
            die();
        }
    }

    /*
     * Reset Password webservice
     */

    public function forgot_password() {
        if ($this->input->method() != 'post') {
            echo json_encode(array('status' => '403', 'message' => 'Request Not Allowed.'));
            die();
        }

        $this->form_validation->set_rules('email', 'email', 'required|valid_email');

        if ($this->form_validation->run() === FALSE) {
            echo json_encode(array('status' => '402', 'message' => 'Invalid Data Format.'));
            die();
        }
        $forgotEmail = $this->input->post('email');
        $rand = rand(100, 999);
        $password_token = uniqid($rand, TRUE);
        $update_data = array('password_token' => $password_token);
        $this->common->update_data($update_data, 'users', 'email_id', $forgotEmail);
        $userData = $this->common->select_data_by_id('users', 'email_id', $forgotEmail, '*');


        if (empty($userData)) {
            echo json_encode(array('status' => '402', 'message' => 'The email you entered is not valid.'));
            die();
        } else {
            if ($userData[0]['status'] == 'Disable') {
                echo json_encode(array('status' => '402', 'message' => 'Please Active your account.'));
                die();
            }
            $firstname = $userData[0]['firstname'];
            $lastname = $userData[0]['lastname'];
            $link = base_url('../Login/resetPassword') . '/' . $password_token;
            $resetlink = "<a href='$link' title='Reset Password' target='_blank'>" . $link . "</a>";
            $site_logo = base_url() . '../assets/img/logo.png';
            $mailData = $this->common->select_data_by_id('mailformat', 'mail_id', '6', '*', array());
            $subject = $mailData[0]['subject'];
            $mailformat = $mailData[0]['mailformat'];
            $year = date('Y');
            $subject = str_replace("%site-name%", $this->data['site_name'], $subject);

            $mail_body = str_replace("%firstname%", $firstname, stripslashes($mailformat));
            $mail_body = str_replace("%lastname%", $lastname, $mail_body);
            $mail_body = str_replace("%reset-link%", $resetlink, $mail_body);
            $mail_body = str_replace("%site-name%", $this->data['site_name'], $mail_body);

            $this->sendEmail($this->data['site_name'], $this->data['site_email'], $forgotEmail, $subject, $mail_body);
            echo json_encode(array('status' => '200', 'message' => 'Reset password link has been successfully sent to your email.'));
            die();
        }
    }

    /*
     * Display User information
     */

    public function get_profile() {

        if ($this->input->method() != 'post') {
            echo json_encode(array('status' => '403', 'message' => 'Request not allowed.'));
            die();
        }

        $this->form_validation->set_rules('user_id', 'user_id', 'required');

        if ($this->form_validation->run() === FALSE) {
            echo json_encode(array('status' => '402', 'message' => 'Invalid Data Format.'));
            die();
        }
// check for user token
        $this->user_auth($this->input->post('user_id'));

        $user_join[0]['table'] = 'country';
        $user_join[0]['join_table_id'] = 'country.id';
        $user_join[0]['from_table_id'] = 'users.country_id';
        $user_join[0]['join_type'] = 'LEFT';

        $selected = 'users.id,users.firstname,users.lastname,users.contact_no,users.email_id,users.addressline1,users.addressline2,users.city,country.nicename as country, country.id as country_id, users.profile_image';
        $user_data = $this->common->select_data_by_condition('users', array('users.id' => $this->input->post('user_id')), $selected, '', '', '', '', $user_join, '');


        if (!empty($user_data)) {
            if ($user_data[0]['country'] == '' && $user_data[0]['country_id']) {
                $user_data[0]['country'] = '';
                $user_data[0]['country_id'] = '';
            }
            $profile_image = $user_data[0]['profile_image'];
            // $user_data[0]['profile_image'] = base_url() . $this->config->item('user_profile_upload_path') . $user_data[0]['profile_image'];
            if ($profile_image == '') {
                $user_data[0]['profile_image_thumb'] = base_url() . '../uploads/profile.png';
            } else {
                $user_data[0]['profile_image_thumb'] = base_url() . $this->config->item('user_profile_upload_thumb_path') . $profile_image;
            }


            echo json_encode(array('status' => '200', 'message' => 'Record Found', 'user_profile_data' => $user_data));
            die();
        } else {
            echo json_encode(array('status' => '402', 'message' => 'Incorrect User id'));
            die();
        }
    }

    /*
     * Edit the use information
     */

    public function edit_profile() {
        if ($this->input->method() != 'post') {
            echo json_encode(array('status' => '403', 'message' => 'Request Not Allowed.'));
            die();
        }

        $this->form_validation->set_rules('user_id', 'user_id', 'required');
        $this->form_validation->set_rules('firstname', 'firstnmae', 'required');
        $this->form_validation->set_rules('lastname', 'lastname', 'required');
        $this->form_validation->set_rules('email', 'email', 'required');
        $this->form_validation->set_rules('country', 'country', 'required');

        if ($this->form_validation->run() === FALSE) {
            echo json_encode(array('status' => '402', 'message' => 'Invalid Data Format.'));
            die();
        }
// check for user token
        $id = $this->input->post('user_id', TRUE);
        $this->user_auth($id);


        $old_record = $this->common->select_data_by_id('users', 'id', $this->input->post('user_id'), 'profile_image');

        $profile_image = $this->input->post('profile_image');
        $upadtedata = array(
            'firstname' => $this->input->post('firstname'),
            'lastname' => $this->input->post('lastname'),
            'email_id' => $this->input->post('email'),
            'contact_no' => $this->input->post('contact_no'),
            'addressline1' => $this->input->post('addressline1'),
            'addressline2' => $this->input->post('addressline2'),
            "city" => $this->input->post('city', TRUE),
            "is_first" => 1,
            //"profile_image" => $filename,
            "country_id" => $this->input->post('country', TRUE),
            "color_code" => 'D7DCDE',
            "modified_datetime" => date('Y-m-d H:i:s'),
            "modified_ip" => $this->input->ip_address(),
        );
//        print_r($upadtedata);exit;
        if (isset($profile_image) && !empty($profile_image)) {

            if ($old_record != '') {
                $pic = $this->config->item('user_profile_upload_path') . $old_record[0]['profile_image'];
                $pic_thumb = $this->config->item('user_profile_upload_thumb_path') . $old_record[0]['profile_image'];
                if (file_exists($pic)) {
                    unlink($pic);
                }
                if (file_exists($pic_thumb)) {
                    unlink($pic_thumb);
                }
            }

            $img = str_replace('data:image/png;base64,', '', $profile_image);
            //  $user_id=$this->session->userdata();
            $img . "<br>";
            //   $img = str_replace(' ', '+', $img);
            $data = base64_decode($img);

            $path = $this->config->item('user_profile_upload_thumb_path');
            // $path1 = $this->config->item('user_profile_upload_path');

            $filename = uniqid() . "_" . $this->input->post('user_id') . '.png';


            // $filename_array[] = $filename;
            $file = $path . $filename;
            //   $file1 = $path1 . $filename;
            // $success1 = file_put_contents($file1, $data);
            $success = file_put_contents($file, $data);
            $upadtedata['profile_image'] = $filename;
        }




        if ($this->common->update_data($upadtedata, 'users', 'id', $id)) {

            echo json_encode(array('status' => '200', 'message' => 'Profile Updated Successfully.'));
            die();
        } else {

            echo json_encode(array('status' => '200', 'message' => 'Something went wrong please try aftersome time.'));
            die();
        }
    }

    /*
     * User Logout Webservice
     */

    public function edit_cancel() {
        if ($this->input->method() != 'post') {
            echo json_encode(array('status' => '403', 'message' => 'Request Not Allowed.'));
            die();
        }

        $this->form_validation->set_rules('user_id', 'user_id', 'required');
        if ($this->form_validation->run() === FALSE) {
            echo json_encode(array('status' => '402', 'message' => 'Invalid Data Format.'));
            die();
        }
// check for user token
        $id = $this->input->post('user_id', TRUE);
        $this->user_auth($id);

        $upadtedata = array(
            "is_first" => 1,
        );

        if ($this->common->update_data($upadtedata, 'users', 'id', $id)) {

            echo json_encode(array('status' => '200', 'message' => 'Back to project screen'));
            die();
        } else {

            echo json_encode(array('status' => '200', 'message' => 'Something went wrong please try aftersome time.'));
            die();
        }
    }

    public function logout() {
        if ($this->input->method() != 'post') {
            echo json_encode(array('status' => '403', 'message' => 'Request Not Allowed.'));
            die();
        }

        $this->form_validation->set_rules('user_id', 'user_id', 'required');
        if ($this->form_validation->run() === FALSE) {
            echo json_encode(array('status' => '402', 'message' => 'Invalid data format.'));
            die();
        }

        $request = $this->input->post();
        $this->user_auth($request['user_id']);

        $header = $this->input->request_headers();

        if (isset($header['UserAuth'])) {
            $user_token = $header['UserAuth'];
        } elseif (isset($header['Userauth'])) {
            $user_token = $header['Userauth'];
        }

        $delete = $this->Webservice_model->delete_user_token($request['user_id'], $user_token);
        if ($delete) {
            echo json_encode(array('status' => '200', 'message' => 'Logout succesfully.'));
            die();
        } else {
            echo json_encode(array('status' => '403', 'message' => 'Error occured. Please try again later!.'));
            die();
        }
    }

    public function pages() {
        if ($this->input->method() != 'post') {
            echo json_encode(array('status' => '403', 'message' => 'Pedido nÃ£o permitido.'));
            die();
        }

        $request = $this->input->post();

        $this->form_validation->set_rules('page_id', 'page_id', 'required');

        if ($this->form_validation->run() === FALSE) {
            echo json_encode(array('status' => '402', 'message' => 'Formato de dados invÃ¡lido.'));
            die();
        }

        $condition_pages_data = array('pages.page_id' => $request['page_id']);
        $page_data = $this->common->select_data_by_condition('pages', $condition_pages_data, 'description', '', '', '', '', array(), '');

        if (!empty($page_data)) {
            $page['status'] = '200';
            $page['message'] = "Registros encontrados";
            $page['page_content'] = $page_data[0]['description'];
            echo json_encode($page);
            die();
        } else {
            echo json_encode(array('status' => '404', 'message' => 'Nenhum registro foi encontrado.'));
            die();
        }
    }

    /*
     * User Can Change password
     */

    public function change_password() {
        if ($this->input->method() != 'post') {
            echo json_encode(array('status' => '403', 'message' => 'Request Not allowed.'));
            die();
        }

        $this->form_validation->set_rules('user_id', 'user_id', 'required');
        $this->form_validation->set_rules('old_password', 'old_password', 'required');
        $this->form_validation->set_rules('new_password', 'new_password', 'required');

        if ($this->form_validation->run() === FALSE) {
            echo json_encode(array('status' => '402', 'message' => 'Invalid Data Format.'));
            die();
        }

        $this->user_auth($this->input->post('user_id'));

        $user_data = $this->common->select_data_by_condition('users', array('id' => $this->input->post('user_id')), 'id,password', '', '', '', '', array(), '');


        if (!empty($user_data) && password_verify($this->input->post('old_password'), $user_data[0]['password'])) {
            $update_array = array('password' => password_hash($this->input->post('new_password'), PASSWORD_BCRYPT), 'modified_datetime' => date('Y-m-d H:i:s'), 'modified_ip' => $this->input->ip_address());

            if ($this->common->update_data($update_array, 'users', 'id', $this->input->post('user_id'))) {
                echo json_encode(array('status' => '200', 'message' => 'Password change successfully.'));
                die();
            } else {
                echo json_encode(array('status' => '403', 'message' => 'An error has occurred. Try again!'));
                die();
            }
        } else {
            echo json_encode(array('status' => '402', 'message' => 'Old password does not match.'));
            die();
        }
    }

    /*
     * Display the Project List
     */

    public function get_my_projects() {
        if ($this->input->method() != 'post') {
            echo json_encode(array('status' => '403', 'message' => 'Request Not allowed.'));
            die();
        }

        $this->form_validation->set_rules('user_id', 'user_id', 'required');


        if ($this->form_validation->run() === FALSE) {
            echo json_encode(array('status' => '402', 'message' => 'Invalid Data Format.'));
            die();
        }

        $this->user_auth($this->input->post('user_id'));

        $my_project = $this->common->select_data_by_condition('project', array('user_id' => $this->input->post('user_id')), '*', 'ASC', '', '', '');

        if (!empty($my_project)) {


            foreach ($my_project as $key => $val) {
                $my_project[$key]['def_pending'] = count($this->common->select_data_by_condition('project_floor_worker', array('project_id' => $val['id'], 'status' => 'pending', 'tradeworker_id !=' => $this->input->post('user_id'))));

                $my_project[$key]['def_approved'] = count($this->common->select_data_by_condition('project_floor_worker', array('project_id' => $val['id'], 'status' => 'approve')));
                $con = array('message.to_user_id' => $this->input->post('user_id'), 'message.project_id' => $val['id'], 'message.read_by_to_id' => 0);
                $unread_count_message = count($this->common->select_data_by_condition('message', $con, '*'));
                $my_project[$key]['unread_msg_count'] = $unread_count_message;
                $con1 = array('comment.to_id' => $this->input->post('user_id'), 'comment.project_id' => $val['id'], 'comment.read_by_to_id' => 0);
                $unread_comment_count = count($this->common->select_data_by_condition('comment', $con1, '*'));
                $my_project[$key]['unread_comment_count'] = $unread_comment_count;
                $my_project[$key]['unread_total_count'] = $unread_count_message + $unread_comment_count;
                $my_project[$key]['mytask'] = count($this->common->select_data_by_condition('project_floor_worker', array('project_id' => $val['id'], 'tradeworker_id' => $this->input->post('user_id'), 'status' => 'pending')));
                $con_notification = array('user_notification.receiver_id ' => $this->input->post('user_id'), 'user_notification.project_id' => $val['id'], 'user_notification.status' => 'Unread');
                $unread_notification_count = count($this->common->select_data_by_condition('user_notification', $con_notification, '*'));
                $my_project[$key]['unread_notification_message_count'] = $unread_notification_count + $unread_count_message;
                $project_image = $my_project[$key]['project_images'];
                $my_project[$key]['project_images'] = base_url() . $this->config->item('upload_projectimage_path') . $my_project[$key]['project_images'];
                $my_project[$key]['project_images_thumb'] = base_url() . $this->config->item('upload_projectimage_thumb') . $project_image;
            }

            //  echo '<pre>';print_r($my_project);exit;
            echo json_encode(array('status' => '200', 'message' => 'Record Found', 'user_project_data' => $my_project));
            die();
        } else {
            echo json_encode(array('status' => '404', 'message' => 'Record Not Found'));
            die();
        }
    }

    /*
     * Display Project List Where User included
     */

    public function get_assigned_projects() {
        if ($this->input->method() != 'post') {
            echo json_encode(array('status' => '403', 'message' => 'Request Not allowed.'));
            die();
        }

        $this->form_validation->set_rules('user_id', 'user_id', 'required');


        if ($this->form_validation->run() === FALSE) {
            echo json_encode(array('status' => '402', 'message' => 'Invalid Data Format.'));
            die();
        }

        $this->user_auth($this->input->post('user_id'));

        $join_str[0] = array(
            'table' => 'tradeworker_associate',
            'join_table_id' => 'tradeworker_associate.project_id',
            'from_table_id' => 'project.id',
            'join_type' => 'LEFT',
        );

        $project_im_included = $this->common->select_data_by_id('project', 'tradeworker_associate.associated_tradeworker', $this->input->post('user_id'), 'project.*', $join_str);


        if (!empty($project_im_included)) {

            foreach ($project_im_included as $key => $val) {
                $project_im_included[$key]['def'] = count($this->common->select_data_by_condition('project_floor_worker', array('project_id' => $val['id'], 'tradeworker_id' => $this->input->post('user_id'), 'status' => 'pending')));
                $project_im_included[$key]['new_def'] = count($this->common->select_data_by_condition('project_floor_worker', array('project_id' => $val['id'], 'tradeworker_id' => $this->input->post('user_id'), 'status' => 'pending', 'viewed' => 0)));
                $con = array('message.to_user_id' => $this->input->post('user_id'), 'message.project_id' => $val['id'], 'message.read_by_to_id' => 0);
                $unread_count_message = count($this->common->select_data_by_condition('message', $con, '*'));
                $project_im_included[$key]['unread_msg_count'] = $unread_count_message;
                $con1 = array('comment.to_id' => $this->input->post('user_id'), 'comment.project_id' => $val['id'], 'comment.read_by_to_id' => 0);
                $unread_comment_count = count($this->common->select_data_by_condition('comment', $con1, '*'));
                $project_im_included[$key]['unread_comment_count'] = $unread_comment_count;
                $project_im_included[$key]['unread_total_count'] = $unread_count_message + $unread_comment_count;
                //my task
                $project_im_included[$key]['mytask'] = count($this->common->select_data_by_condition('project_floor_worker', array('project_id' => $val['id'], 'tradeworker_id' => $this->input->post('user_id'), 'status' => 'pending')));
                $project_im_included[$key]['twsendpm'] = count($this->common->select_data_by_condition('project_floor_worker', array('project_id' => $val['id'], 'when_drop_tw_to_pm' => $this->input->post('user_id'), 'created_by' => 'tw_to_pm', 'status' => 'pending')));
                $project_im_included[$key]['approve'] = count($this->common->select_data_by_condition('project_floor_worker', array('project_id' => $val['id'], 'tradeworker_id' => $this->input->post('user_id'), 'created_by' => 'pm_to_tw', 'status' => 'approve')));

                $con_notification = array('user_notification.receiver_id ' => $this->input->post('user_id'), 'user_notification.project_id' => $val['id'], 'user_notification.status' => 'Unread');
                $unread_notification_count = count($this->common->select_data_by_condition('user_notification', $con_notification, '*'));
                $project_im_included[$key]['unread_notification_message_count'] = $unread_notification_count + $unread_count_message;

                $project_image = $project_im_included[$key]['project_images'];
                $project_im_included[$key]['project_images'] = base_url() . $this->config->item('upload_projectimage_path') . $project_im_included[$key]['project_images'];
                $project_im_included[$key]['project_images_thumb'] = base_url() . $this->config->item('upload_projectimage_thumb') . $project_image;
            }
            echo json_encode(array('status' => '200', 'message' => 'Record Found', 'assisgned_project_data' => $project_im_included));
            die();
        } else {
            echo json_encode(array('status' => '404', 'message' => 'Record Not Found'));
            die();
        }
    }

    /*
     * User can Delete project
     */

    public function delete_project() {
        if ($this->input->method() != 'post') {
            echo json_encode(array('status' => '403', 'message' => 'Request Not allowed.'));
            die();
        }

        $this->form_validation->set_rules('user_id', 'user_id', 'required');
        $this->form_validation->set_rules('project_id', 'project_id', 'required');


        if ($this->form_validation->run() === FALSE) {
            echo json_encode(array('status' => '402', 'message' => 'Invalid Data Format.'));
            die();
        }

        $project_id = $this->input->post('project_id');
        $this->user_auth($this->input->post('user_id'));

        if (!empty($project_id)) {
            $project = $this->common->select_data_by_id('project', 'id', $project_id);
            $project_floor = $this->common->select_data_by_id('project_floor', 'project_id', $project_id);

            $delete_floor = $this->common->delete_data('project_floor', 'project_id', $project_id);
            $delete_project = $this->common->delete_data('project', 'id', $project_id);

            if ($delete_floor && $delete_project) {
                $this->common->delete_data('user_notification', 'project_id', $project_id);
                $this->common->delete_data('message', 'project_id', $project_id);
                $this->common->delete_data('comment', 'project_id', $project_id);
                $this->common->delete_data('project_floor_worker', 'project_id', $project_id);
                $this->common->delete_data('project_order', 'project_id', $project_id);
                $this->common->delete_data('screen_order', 'project_id', $project_id);
                $this->common->delete_data('tradeworker_associate', 'project_id', $project_id);
                $this->common->delete_data('project_profile', 'project_id', $project_id);

                if (file_exists($this->config->item('upload_projectimage_path') . $project[0]["project_images"])) {
                    @unlink($this->config->item('upload_projectimage_path') . $project[0]["project_images"]);
                }
                if (file_exists($this->config->item('upload_projectimage_thumb') . $project[0]["project_images"])) {
                    @unlink($this->config->item('upload_projectimage_thumb') . $project[0]["project_images"]);
                }
                foreach ($project_floor as $val) {
                    if (file_exists($this->config->item('upload_floor_path') . $val["screen_image"])) {
                        @unlink($this->config->item('upload_floor_path') . $val["screen_image"]);
                    }
                }



                echo json_encode(array('status' => '200', 'message' => 'Project Deleted Successfully'));
                die();
            } else {
                echo json_encode(array('status' => '403', 'message' => 'Error Occured please try after sometime'));
                die();
            }
        } else {
            echo json_encode(array('status' => '403', 'message' => 'Incorrect identifier for this Project'));
            die();
        }
    }

    /*
     * User (Manager) Can add project
     */

    public function add_new_project() {
        //     echo "<pre>";       print_r($this->input->post()); die();
        if ($this->input->method() != 'post') {
            echo json_encode(array('status' => '403', 'message' => 'Request Not allowed.'));
            die();
        }



        $this->form_validation->set_rules('user_id', 'user_id', 'required');
        $this->form_validation->set_rules('project_name', 'project_name', 'required');
        $this->form_validation->set_rules('project_address', 'Project Address', 'required');
        // $this->form_validation->set_rules('project_manager', 'Project manager', 'required');
        $this->form_validation->set_rules('company_name', 'Company name', 'required');


        if ($this->form_validation->run() === FALSE) {
            echo json_encode(array('status' => '402', 'message' => 'Invalid Data Format.'));
            die();
        }

        $multiple_scrren_image = $this->input->post('screen_multiple_image');
        $project_photo = $this->input->post('project_photo');

        $this->user_auth($this->input->post('user_id'));
        if (!empty($multiple_scrren_image)) {
            if (!empty($project_photo)) {
                $project_img = str_replace('data:image/png;base64,', '', $project_photo);
                //  $user_id=$this->session->userdata();
                $project_img . "<br>";
                //   $img = str_replace(' ', '+', $img);
                $data = base64_decode($project_img);

                $path = $this->config->item('upload_projectimage_path');
                $path1 = $this->config->item('upload_projectimage_thumb');

                $filename = uniqid() . "_" . $this->input->post('project_name') . '.png';


                // $filename_array[] = $filename;
                $file = $path . $filename;
                $file1 = $path1 . $filename;
                $success1 = file_put_contents($file1, $data);
                $success = file_put_contents($file, $data);
                $project_image = $filename;
            }
            /* if (isset($_FILES['project_photo']) && $_FILES['project_photo']['name'] != '' && $_FILES['project_photo']['size'] > 0) {
              $this->load->library('upload');

              $config['upload_path'] = $this->config->item('upload_projectimage_path');
              $config['encrypt_name'] = TRUE;
              $config['allowed_types'] = $this->config->item('allowed_types');
              $this->upload->initialize($config);
              if ($this->upload->do_upload('project_photo')) {
              $upload_data = $this->upload->data();
              $project_image = $upload_data['file_name'];
              }
              $config['source_image'] = $this->config->item('upload_projectimage_path') . $project_image;
              $config['new_image'] = $this->config->item('upload_projectimage_thumb');
              $config['create_thumb'] = TRUE;
              $config['maintain_ratio'] = TRUE;
              $config['thumb_marker'] = '';
              $config['width'] = $this->config->item('upload_project_thumb_width');
              $config['height'] = $this->config->item('upload_project_thumb_height');
              $config['allowed_types'] = $this->config->item('allowed_types');
              $this->load->library('image_lib');
              $this->image_lib->initialize($config);
              $this->image_lib->resize();
              } */

            $insert_project = array(
                'user_id' => $this->input->post('user_id'),
                'project_title' => $this->input->post('project_name'),
                'project_address' => $this->input->post('project_address'),
                'project_company' => $this->input->post('company_name'),
                'project_images' => $project_image,
                'status' => 'active',
                'created_datetime' => date('Y-m-d h:i:s'),
                'created_ip' => $this->get_client_ip(),
            );

            $project_id = $this->common->insert_data_getid($insert_project, 'project');

            /* $files = $_FILES['screen_multiple_image'];
              $number_of_files = sizeof($_FILES['screen_multiple_image']['tmp_name']); */

            if ($project_id) {
                $j = 1;


                foreach ($multiple_scrren_image as $ky => $val) {

                    //define('UPLOAD_//DIR', 'images/');
                    //$img = $_POST['image'];
                    $img = str_replace('data:image/png;base64,', '', $val);
                    //  $user_id=$this->session->userdata();
                    $img . "<br>";
                    //   $img = str_replace(' ', '+', $img);
                    $data = base64_decode($img);

                    $path = $this->config->item('upload_floor_path');

                    $filename = uniqid() . "_" . $this->input->post('user_id') . '.png';
                    $filename_array[] = $filename;

                    $file = $path . $filename;

                    $success = file_put_contents($file, $data); //die();                
                }


                foreach ($filename_array as $val) {
                    $floor_data = array(
                        'project_id' => $project_id,
                        'screen_image' => $val,
                        'floor_title' => "Screen_" . $j,
                        'status' => 'Enable',
                        'created_datetime' => date('Y-m-d h:i:s'),
                        'created_ip' => $this->get_client_ip(),
                    );
                    $res = $this->common->insert_data($floor_data, 'project_floor');
                    $j++;
                }


                if ($res) {

                    echo json_encode(array('status' => '200', 'message' => 'Your Project Added Successfully.'));
                    die();
                } else {
                    echo json_encode(array('status' => '403', 'message' => 'Error Occured please try after sometime!'));
                    die();
                }
            }
        } else {
            echo json_encode(array('status' => '403', 'message' => 'Please select images!'));
            die();
        }
    }

    /*
     * Change Project Image
     */

    public function change_project_photo() {
        if ($this->input->method() != 'post') {
            echo json_encode(array('status' => '403', 'message' => 'Request Not allowed.'));
            die();
        }


        $this->form_validation->set_rules('user_id', 'user_id', 'required');
        $this->form_validation->set_rules('project_id', 'project_id', 'required');


        if ($this->form_validation->run() === FALSE) {
            echo json_encode(array('status' => '402', 'message' => 'Invalid Data Format.'));
            die();
        }

        $this->user_auth($this->input->post('user_id'));

        $project_id = $this->input->post('project_id');
        $project = $this->common->select_data_by_id('project', 'id', $project_id, '*');
        $old_image = $project[0]['project_images'];

        if ($_FILES['project_photo']['name'] != '' && $_FILES['project_photo']['size'] > 0) {
            $this->load->library('upload');
            $config['upload_path'] = $this->config->item('upload_projectimage_path');
            $config['encrypt_name'] = TRUE;
            $config['allowed_types'] = 'jpg|jpeg|png';

            $this->upload->initialize($config);
            if ($this->upload->do_upload('project_photo')) {
                $upload_data = $this->upload->data();

                $updatedData['project_images'] = $upload_data['file_name'];
                $imgerror = $this->upload->display_errors();

                if ($imgerror == '') {
                    $config['source_image'] = $config['upload_path'] . $upload_data['file_name'];
                    $config['new_image'] = $this->config->item('upload_projectimage_thumb') . $upload_data['file_name'];
                    $config['create_thumb'] = TRUE;
                    $config['maintain_ratio'] = FALSE;
                    //$config['thumb_marker'] = '';
                    $config['width'] = $this->config->item('upload_project_thumb_height');
                    $config['height'] = $this->config->item('upload_project_thumb_width');
                    $this->load->library('image_lib');
                    $this->image_lib->initialize($config);
                    //Creating Thumbnail
                    $this->image_lib->resize();
                    //Loading Image Library
                } else {

                    echo json_encode(array('status' => '403', 'message' => 'Something went wrong.. please try again later.', 'error' => $imgerror));
                    die();
                }

                if ($this->common->update_data($updatedData, 'project', 'id', $project_id)) {
                    if (file_exists($this->config->item('upload_projectimage_path') . $old_image)) {
                        @unlink($this->config->item('upload_projectimage_path') . $old_image);
                    }

                    if (file_exists($this->config->item('upload_projectimage_thumb') . $old_image)) {
                        @unlink($this->config->item('upload_projectimage_thumb') . $old_image);
                    }
                } else {
                    echo json_encode(array('status' => '403', 'message' => 'Something went wrong.. please try again later.'));
                    die();
                }
                echo json_encode(array('status' => '200', 'message' => 'Image uploaded sucessfully.'));
                die();
            } else {
                echo json_encode(array('status' => '403', 'message' => 'Something went wrong.. please try again later.'));
                die();
            }
        } else {
            echo json_encode(array('status' => '403', 'message' => 'Please select imagefile first.'));
            die();
        }
    }

    /*
     *  Invited Tradeworker List
     */

    public function get_invite_tradeworker_list() {
        if ($this->input->method() != 'post') {
            echo json_encode(array('status' => '403', 'message' => 'Request Not allowed.'));
            die();
        }




        $this->form_validation->set_rules('user_id', 'user_id', 'required');
        $this->form_validation->set_rules('project_id', 'project_id', 'required');
        //   $this->form_validation->set_rules('role', 'role', 'required');

        if ($this->form_validation->run() === FALSE) {
            echo json_encode(array('status' => '402', 'message' => 'Invalid Data Format.'));
            die();
        }

        $this->user_auth($this->input->post('user_id'));
        $projectid = $this->input->post('project_id');
        $role = $this->input->post('role');
        $user_id = $this->input->post('user_id');
        if ($role == 'My_project') {
            $manager = $this->common->select_data_by_id('users', 'id', $user_id);
            if ($manager[0]['profile_image'] != '') {
                $manager[0]['manager_image'] = base_url() . $this->config->item('user_profile_upload_thumb_path') . $manager[0]['profile_image'];
            } else {
                $manager[0]['manager_image'] = base_url('uploads/profile.png');
            }

            $manager_blue = array(
                'status' => 'pending',
                'tradeworker_id' => $user_id,
                'project_id' => $projectid,
            );

            $blue_manager = $this->common->select_data_by_condition('project_floor_worker', $manager_blue, 'id');
            $blue_manager = count($blue_manager);
            $manager[0]['manager_blue'] = $blue_manager;
            $manager_data = $manager;
            $str[0] = array(
                'table' => 'tradeworker_associate',
                'join_table_id' => 'tradeworker_associate.associated_tradeworker',
                'from_table_id' => 'users.id',
                'join_type' => 'LEFT',
            );

            $col = 'users.id,users.email_id,users.firstname,users.lastname,users.profession,users.color_code,users.profile_image,tradeworker_associate.pm_pin_allow,tradeworker_associate.created_datetime';
            $tradeworker = $this->common->select_data_by_allcondition('users', array('tradeworker_associate.project_id' => $projectid), $col, 'tradeworker_associate.order_no', 'ASC', '', '', $str, 'users.id');
            // echo "<pre>"; print_r($tradeworker); die();
            if (!empty($tradeworker)) {//echo "hii";exit;
                foreach ($tradeworker as $key => $val) {
                    $con = array('project_id' => $projectid, 'tradeworker_id' => $val['id']);
                    $email = $val['email_id'];
                    $tradeworker[$key]['email'] = $email;


                    //  echo "<pre>"; print_r($pro_profile_exist); 
                    $profile_image = $val['profile_image'];
                    $profession = $val['profession'];
                    $pm_pin_allow = $val['pm_pin_allow'];
                    $tradeworker[$key]['email'] = $email;
                    $tradeworker[$key]['profile_name'] = $val['firstname'] . " " . $val['lastname'];
                    $tradeworker[$key]['profession'] = $profession;
                    $tradeworker[$key]['profile_image'] = $profile_image;
                    if (empty($tradeworker[$key]['profile_image'])) {

                        $tradeworker[$key]['profile_image_thumb'] = base_url() . '../uploads/profile.png';
                    } else {

                        $tradeworker[$key]['profile_image_thumb'] = base_url() . $this->config->item('user_profile_upload_thumb_path') . $tradeworker[$key]['profile_image'];
                    }
                    $pro_profile_exist = $this->common->select_data_by_condition('project_profile', $con, '*');
                    if (!empty($pro_profile_exist)) {

                        //  unset($tradeworker[$key]);
                        $tradeworker[$key]['id'] = $pro_profile_exist[0]['tradeworker_id'];
                        $tradeworker[$key]['profile_name'] = $pro_profile_exist[0]['name'];

                        $tradeworker[$key]['pm_pin_allow'] = $pm_pin_allow;
                    }
                    $con = array('message.to_user_id' => $user_id, 'message.project_id' => $projectid, 'message.from_user_id' => $val['id'], 'message.read_by_to_id' => 0);
                    $unread_count = count($this->common->select_data_by_condition('message', $con, '*'));
                    $tradeworker[$key]['unread_count'] = $unread_count;

                    //count the approve def and pending def
                    $con = array(
                        'created_by' => 'pm_to_tw',
                        'status' => 'pending',
                        'tradeworker_id' => $val['id'],
                        'project_id' => $projectid,
                    );
                    $green = $this->common->select_data_by_condition('project_floor_worker', $con, 'id');
                    $green = count($green);
                    $tradeworker[$key]['green'] = $green;
                    $con = array(
                        'created_by' => 'pm_to_tw',
                        'status' => 'approve',
                        'tradeworker_id' => $val['id'],
                        'project_id' => $projectid,
                    );
                    $purple = $this->common->select_data_by_condition('project_floor_worker', $con, 'id');
                    $purple = count($purple);
                    $tradeworker[$key]['purple'] = $purple;
                    //end
                }
                ksort($tradeworker);

                $invite_tradeworker_list = $tradeworker;
                echo json_encode(array('status' => '200', 'message' => 'Record found.', 'invite_tradeworker_list' => $invite_tradeworker_list, 'manager_data' => $manager_data));
                die();
            } else {
                echo json_encode(array('status' => '200', 'message' => 'Manager Record found.', 'manager_data' => $manager_data));
                die();
            }
        } else {

            $proj = $this->common->select_data_by_id("project", "id", $projectid, "user_id,project.id as project_id,id,project_title");


            $manager = $this->common->select_data_by_id('users', 'id', $proj[0]['user_id']);

            $con = array('project_id' => $projectid, 'tradeworker_id' => $proj[0]['user_id']);
            //   $manager_pin = $this->common->select_data_by_condition("project_profile", $con, 'project_profile.color_code,project_profile.name');
            // $manager[0]['manager_pin'] = $manager_pin;
            $con = array(
                'created_by' => 'tw_to_pm',
                'status' => 'pending',
                'tradeworker_id' => $manager[0]['id'],
                'project_id' => $projectid,
            );
            $green = $this->common->select_data_by_condition('project_floor_worker', $con, 'id');
            $green = count($green);
            if ($manager[0]['profile_image'] != '') {
                $manager[0]['manager_image'] = base_url() . $this->config->item('user_profile_upload_thumb_path') . $manager[0]['profile_image'];
            } else {
                $manager[0]['manager_image'] = base_url('uploads/profile.png');
            }
            $manager[0]['green'] = $green;
            $manager_data = $manager;
            $str[0] = array(
                'table' => 'tradeworker_associate',
                'join_table_id' => 'tradeworker_associate.associated_tradeworker',
                'from_table_id' => 'users.id',
                'join_type' => 'LEFT',
            );

            $col = 'users.id,users.email_id,users.firstname,users.lastname,users.profession,users.color_code,users.profile_image,tradeworker_associate.pm_pin_allow,tradeworker_associate.created_datetime';
            $tradeworker = $this->common->select_data_by_condition('users', array('tradeworker_associate.project_id' => $projectid, 'users.id' => $user_id), $col, '', '', '', '', $str);
            //echo "<pre>"; print_r($this->db->last_query()); die();
            if (!empty($tradeworker)) {


                foreach ($tradeworker as $key => $val) {
                    $email = $val['email_id'];
                    $tradeworker[$key]['email'] = $email;
                    $con_blue = array(
                        'status' => 'pending',
                        'tradeworker_id' => $user_id,
                        'project_id' => $projectid,
                    );

                    $blue = $this->common->select_data_by_condition('project_floor_worker', $con_blue, 'id');
                    $blue = count($blue);

                    $con_purple = array(
                        'created_by' => 'pm_to_tw',
                        'status' => 'approve',
                        'tradeworker_id' => $user_id,
                        'project_id' => $projectid,
                    );
                    $purple = $this->common->select_data_by_condition('project_floor_worker', $con_purple, 'id');
                    //print_r($this->db->last_query());exit;
                    $purple = count($purple);
                    $profile_image = $val['profile_image'];
                    if ($tradeworker[$key]['profile_image'] == '') {

                        $tradeworker[$key]['profile_image_thumb'] = base_url() . '../uploads/profile.png';
                    } else {

                        $tradeworker[$key]['profile_image_thumb'] = base_url() . $this->config->item('user_profile_upload_thumb_path') . $tradeworker[$key]['profile_image'];
                    }

                    /* $con = array('project_id' => $projectid, 'tradeworker_id' => $val['id']);
                      $pro_profile_exist = $this->common->select_data_by_condition('project_profile', $con, '*');
                      if (!empty($pro_profile_exist)) {





                      //  unset($tradeworker[$key]);
                      $tradeworker[$key]['id'] = $pro_profile_exist[0]['tradeworker_id'];
                      $tradeworker[$key]['profile_name'] = $pro_profile_exist[0]['name'];

                      $tradeworker[$key]['color_code'] = $pro_profile_exist[0]['color_code'];
                      } */
                }
                $tradeworker[$key]['blue'] = $blue;
                $tradeworker[$key]['purple'] = $purple;
                ksort($tradeworker);

                $invite_tradeworker_list = $tradeworker;
                echo json_encode(array('status' => '200', 'message' => 'Record found.', 'invite_tradeworker_list' => $invite_tradeworker_list, 'manager_data' => $manager_data));
                die();
            } else {
                echo json_encode(array('status' => '200', 'message' => 'Manager Record Found.', 'manager_data' => $manager_data));
                die();
            }
        }
        /* if ($this->form_validation->run() === FALSE) {
          echo json_encode(array('status' => '402', 'message' => 'Invalid Data Format.'));
          die();
          }

          $this->user_auth($this->input->post('user_id'));
          $project_id = $this->input->post('project_id');

          $join_str[0] = array(
          'table' => 'tradeworker_associate',
          'join_table_id' => 'tradeworker_associate.associated_tradeworker',
          'from_table_id' => 'users.id',
          'join_type' => 'LEFT',
          );

          $project_invite_data = $this->common->select_data_by_id('users', 'tradeworker_associate.project_id', $project_id, 'users.*', $join_str);

          if (!empty($project_invite_data)) {

          foreach ($project_invite_data as $key => $val) {
          if ($project_invite_data[$key]['profile_image'] == '') {
          // $project_invite_data[$key]['profile_image'] = base_url() . '../uploads/profile.png';
          $project_invite_data[$key]['profile_image_thumb'] = base_url() . '../uploads/profile.png';
          } else {
          //$project_invite_data[$key]['profile_image'] = base_url() . $this->config->item('user_profile_upload_path') . $project_invite_data[$key]['profile_image'];
          $project_invite_data[$key]['profile_image_thumb'] = base_url() . $this->config->item('user_profile_upload_thumb_path') . $project_invite_data[$key]['profile_image'];
          }

          $con = array('project_id' => $project_id, 'tradeworker_id' => $val['id']);
          $res = $this->common->select_data_by_condition("project_profile", $con, 'project_profile.color_code,project_profile.name');
          if (!empty($res)) {
          $project_invite_data[$key]['color_code'] = $res[0]['color_code'];
          $project_invite_data[$key]['profile_name'] = $res[0]['name'];
          } else {

          $project_invite_data[$key]['color_code'] = $val['color_code'];
          $project_invite_data[$key]['profile_name'] = $val['firstname'] . " " . $val['lastname'];
          }
          unset($project_invite_data[$key]['password']);
          }


          echo json_encode(array('status' => '200', 'message' => 'Record found.', 'invite_tradeworker_list' => $project_invite_data));
          die();
          } else {
          echo json_encode(array('status' => '404', 'message' => 'Record not found.'));
          die();
          } */
    }

    /*
     * Remove a invited tradeworker invite by Manager
     */

    public function remove_invite_tradeworker() {
        if ($this->input->method() != 'post') {
            echo json_encode(array('status' => '403', 'message' => 'Request Not allowed.'));
            die();
        }
        $this->form_validation->set_rules('user_id', 'user_id', 'required');
        $this->form_validation->set_rules('project_id', 'project_id', 'required');

        if ($this->form_validation->run() === FALSE) {
            echo json_encode(array('status' => '402', 'message' => 'Invalid Data Format.'));
            die();
        }

        $this->user_auth($this->input->post('user_id'));

        $worker_id = $this->input->post('worker_id');
        $projectid = $this->input->post('project_id');

        $conditon = array(
            'tradeworker_associate.associated_tradeworker' => $worker_id,
            'tradeworker_associate.project_id' => $projectid,
        );
        $conditon2 = array(
            'project_profile.tradeworker_id' => $worker_id,
            'project_profile.project_id' => $projectid,
        );
        $del1 = $this->common->delete_data_by_condition('tradeworker_associate', $conditon);
        $del2 = $this->common->delete_data_by_condition('project_profile', $conditon2);

        if ($del1 && $del2) {
            echo json_encode(array('status' => '200', 'message' => 'Invited Tradeworker deleted succesfully .'));
            die();
        } else {
            echo json_encode(array('status' => '404', 'message' => 'Something Went Wrong. Please try again later!'));
            die();
        }
    }

    /*
     * Invite Tradeworker by Manager
     */

    public function invite_tradeworker() {

        if ($this->input->method() != 'post') {
            echo json_encode(array('status' => '403', 'message' => 'Request Not Allowed.'));
            die();
        }

        $this->form_validation->set_rules('user_id', 'user_id', 'required');
        $this->form_validation->set_rules('project_id', 'project_id', 'required');

        if ($this->form_validation->run() === FALSE) {
            echo json_encode(array('status' => '402', 'message' => 'Invalid Data Format.'));
            die();
        }

        $pid = $this->input->post('project_id');
        $data_email = $this->input->post('to_email');

        $data_message = $this->input->post('message');
        if (empty($data_email)) {
            echo json_encode(array('status' => '402', 'message' => 'Email required to send invitation.'));
            die();
        }
        $manager_data = $this->common->select_data_by_id('users', 'id', $this->input->post('user_id'));
        $manager_code = base64_encode($this->input->post('user_id'));
        $proj_data = $this->common->select_data_by_id('project', 'id', $pid);

        foreach ($data_email as $email) {

            if ($manager_data[0]['email_id'] === $email) {

                echo json_encode(array('status' => '403', 'message' => 'Email is not send.'));
                die();
            } else {
                if (!empty($email)) {
                    $get_worker = $this->common->select_data_by_id('users', 'email_id', $email);
//                    print_r($get_worker);exit;
                    if (empty($get_worker)) {
                        $link = "<a href='" . site_url('../Invitation/invite_link_associate/' . $pid . '/' . $manager_code) . "' title='Register account' class='bg-primary' target='_blank'>" . site_url('../Login/invite_link_associate/' . $pid . '/' . $manager_code) . "</a>";
                        $mailData = $this->common->select_data_by_id('mailformat', 'mail_id', '8', '*', array());
                        $sitename = $this->common->select_data_by_id('setting', 'setting_id', '1', '*', array());
                        $subject = $mailData[0]['subject'];
                        $mailformat = $mailData[0]['mailformat'];
                        $mail_body = str_replace("%register_link%", $link, $mailformat);
                        $mail_body = str_replace("%project_name%", $proj_data[0]['project_title'], $mail_body);
                        $mail_body = str_replace("%project_desc%", $proj_data[0]['project_description'], $mail_body);
                        $mail_body = str_replace("%project_address%", $proj_data[0]['project_address'], $mail_body);
                        $mail_body = str_replace("%project_company%", $proj_data[0]['project_company'], $mail_body);
                        $mail_body = str_replace("%invite_message%", $data_message, $mail_body);
                        $mail_body = str_replace("%project_manager%", $manager_data[0]['firstname'] . ' ' . $manager_data[0]['lastname'], $mail_body);
                        $mail_body = str_replace("%sitename%", $sitename[0]['field_value'], $mail_body);

                        $this->sendEmail($sitename[0]['field_value'], $this->data['site_email'], $email, $subject, $mail_body);
                        echo json_encode(array('status' => '200', 'message' => 'Invitation link has been successfully sent to your email.'));
                        die();
                    } else {
                        $sitename = $this->common->select_data_by_id('setting', 'setting_id', '1', '*', array());
                        $manager_data = $this->common->select_data_by_id('users', 'id', $this->input->post('user_id'));
                        $tradeworker_code = base64_encode($get_worker[0]['id']);
                        $link = "<a href='" . site_url('../Invitation/invite_link_associate/' . $pid . '/' . $manager_code . '/' . $tradeworker_code) . "' title='Click on link' class='bg-primary' target='_blank'>" . site_url('../Login/invite_link_associate/' . $pid . '/' . $manager_code . '/' . $tradeworker_code) . "</a>";
                        $mailData = $this->common->select_data_by_id('mailformat', 'mail_id', '8', '*', array());
                        $subject = $mailData[0]['subject'];
                        $mailformat = $mailData[0]['mailformat'];
                        $mail_body = str_replace("%register_link%", $link, $mailformat);
                        $mail_body = str_replace("%project_name%", $proj_data[0]['project_title'], $mail_body);
                        $mail_body = str_replace("%project_desc%", $proj_data[0]['project_description'], $mail_body);
                        $mail_body = str_replace("%project_address%", $proj_data[0]['project_address'], $mail_body);
                        $mail_body = str_replace("%project_company%", $proj_data[0]['project_company'], $mail_body);
                        $mail_body = str_replace("%invite_message%", $data_message, $mail_body);
                        $mail_body = str_replace("%project_manager%", $manager_data[0]['firstname'] . ' ' . $manager_data[0]['lastname'], $mail_body);
                        $mail_body = str_replace("%sitename%", $sitename[0]['field_value'], $mail_body);

                        $this->sendEmail($sitename[0]['field_value'], $this->data['site_email'], $email, $subject, $mail_body);
                        echo json_encode(array('status' => '200', 'message' => 'Invitation link has been successfully sent to your email.'));
                        die();
                    }
                } else {
                    echo json_encode(array('status' => '403', 'message' => 'Something went wrong please try later.'));
                    die();
                }
            }
        }
    }

    /*
     * Display the Email list of all registerd user 
     */

    public function get_registered_email() {

        if ($this->input->method() != 'post') {
            echo json_encode(array('status' => '403', 'message' => 'Request Not Allowed.'));
            die();
        }

        $this->form_validation->set_rules('user_id', 'user_id', 'required');


        if ($this->form_validation->run() === FALSE) {
            echo json_encode(array('status' => '402', 'message' => 'Invalid Data Format.'));
            die();
        }

        $this->user_auth($this->input->post('user_id'));

        $registered_email_record = $this->common->get_all_record('users', 'id,email_id');
        if (!empty($registered_email_record)) {
            echo json_encode(array('status' => '200', 'message' => 'List of Registered Mail', 'registered_email_data' => $registered_email_record));
            die();
        } else {
            echo json_encode(array('status' => '404', 'message' => 'Data Not found!', 'registered_email_data' => array()));
            die();
        }
    }

    /*
     * Display project screen
     */

    public function get_project_screen() {
        if ($this->input->method() != 'post') {
            echo json_encode(array('status' => '403', 'message' => 'Request Not Allowed.'));
            die();
        }

        $this->form_validation->set_rules('user_id', 'user_id', 'required');
        $this->form_validation->set_rules('project_id', 'project_id', 'required');


        if ($this->form_validation->run() === FALSE) {
            echo json_encode(array('status' => '402', 'message' => 'Invalid Data Format.'));
            die();
        }
        $project_id = $this->input->post('project_id');
        $this->user_auth($this->input->post('user_id'));

        $user_id = $this->input->post('user_id');
        $join_str[0] = array(
            'table' => 'project',
            'join_table_id' => 'project.id',
            'from_table_id' => 'project_floor.project_id',
            'join_type' => 'LEFT',
        );
        //$project_screen= $this->common->select_data_by_id('project_floor', 'project_floor.project_id', $project_id, '*', '');
        $project_screen = $this->common->select_data_by_id('project_floor', 'project_floor.project_id', $project_id, 'project_floor.*,project.project_title', $join_str);

        if (!empty($project_screen)) {
            foreach ($project_screen as $key => $val) {

                $project_screen[$key]['def_pending'] = count($this->common->select_data_by_condition('project_floor_worker', array('project_id' => $val['project_id'], 'tradeworker_id !=' => $user_id, 'floor_id' => $val['id'], 'status' => 'pending')));

                $project_screen[$key]['def_approved'] = count($this->common->select_data_by_condition('project_floor_worker', array('project_id' => $val['project_id'], 'tradeworker_id !=' => $user_id, 'floor_id' => $val['id'], 'status' => 'approve')));
                $con = array('message.to_user_id' => $this->input->post('user_id'), 'message.project_id' => $val['project_id'], 'message.read_by_to_id' => 0);
                $unread_count_message = count($this->common->select_data_by_condition('message', $con, '*'));
                $project_screen[$key]['unread_msg_count'] = $unread_count_message;
                $con1 = array('comment.to_id' => $this->input->post('user_id'), 'comment.project_id' => $val['project_id'], 'comment.floor_id' => $val['id'], 'comment.read_by_to_id' => 0);
                $unread_comment_count = count($this->common->select_data_by_condition('comment', $con1, '*'));
                $project_screen[$key]['unread_comment_count'] = $unread_comment_count;
                $project_screen[$key]['unread_total_count'] = $unread_count_message + $unread_comment_count;
                //count
                $con_green = array('floor_id' => $val['id'], 'status' => 'pending', 'tradeworker_id !=' => $user_id);
                $project_screen[$key]['sent_task_green'] = count($this->common->select_data_by_condition('project_floor_worker', $con_green));

                $project_screen[$key]['mytasks_blue'] = count($this->common->select_data_by_condition('project_floor_worker', array('floor_id' => $val['id'], 'tradeworker_id' => $user_id, 'status' => 'pending')));

                $con_purple = array('floor_id' => $val['id'], 'status' => 'approve');
                $project_screen[$key]['def_completed_purple'] = count($this->common->select_data_by_condition('project_floor_worker', $con_purple));

                $project_screen[$key]['screen_image'] = base_url() . $this->config->item('upload_floor_path') . $project_screen[$key]['screen_image'];
            }


            echo json_encode(array('status' => '200', 'message' => 'List of Project Screen', 'project_screen_data' => $project_screen));
            die();
        } else {
            echo json_encode(array('status' => '404', 'message' => 'Data Not found!', 'project_screen_data' => array()));
            die();
        }
    }

    /*
     * list of  project screen where user or Manager involved and assigned by itself  
     */

    public function get_my_task_project_screen() {
        if ($this->input->method() != 'post') {
            echo json_encode(array('status' => '403', 'message' => 'Request Not Allowed.'));
            die();
        }

        $this->form_validation->set_rules('user_id', 'user_id', 'required');
        $this->form_validation->set_rules('project_id', 'project_id', 'required');


        if ($this->form_validation->run() === FALSE) {
            echo json_encode(array('status' => '402', 'message' => 'Invalid Data Format.'));
            die();
        }
        $project_id = $this->input->post('project_id');
        $this->user_auth($this->input->post('user_id'));

        $user_id = $this->input->post('user_id');
        $join_str[0] = array(
            'table' => 'project',
            'join_table_id' => 'project.id',
            'from_table_id' => 'project_floor.project_id',
            'join_type' => 'LEFT',
        );

        $project_screen_my = $this->common->select_data_by_id('project_floor', 'project_floor.project_id', $project_id, 'project_floor.*,project.project_title', $join_str);
        if (!empty($project_screen_my)) {
            foreach ($project_screen_my as $key => $val) {
                $project_screen_my[$key]['def_pending'] = count($this->common->select_data_by_condition('project_floor_worker', array('project_id' => $val['project_id'], 'tradeworker_id' => $user_id, 'floor_id' => $val['id'], 'status' => 'pending')));

                $project_screen_my[$key]['def_approved'] = count($this->common->select_data_by_condition('project_floor_worker', array('project_id' => $val['project_id'], 'tradeworker_id' => $user_id, 'floor_id' => $val['id'], 'status' => 'approve')));
                $con = array('message.to_user_id' => $this->input->post('user_id'), 'message.project_id' => $val['project_id'], 'message.read_by_to_id' => 0);
                $unread_count_message = count($this->common->select_data_by_condition('message', $con, '*'));
                $project_screen_my[$key]['unread_msg_count'] = $unread_count_message;
                $con1 = array('comment.to_id' => $this->input->post('user_id'), 'comment.project_id' => $val['project_id'], 'comment.floor_id' => $val['id'], 'comment.read_by_to_id' => 0);
                $unread_comment_count = count($this->common->select_data_by_condition('comment', $con1, '*'));
                $project_screen_my[$key]['unread_comment_count'] = $unread_comment_count;
                $project_screen_my[$key]['unread_total_count'] = $unread_count_message + $unread_comment_count;

                $project_screen_my[$key]['screen_image'] = base_url() . $this->config->item('upload_floor_path') . $project_screen_my[$key]['screen_image'];
            }



            echo json_encode(array('status' => '200', 'message' => 'List of Project Screen', 'my_task_project_screen' => $project_screen_my));
            die();
        } else {
            echo json_encode(array('status' => '404', 'message' => 'Data Not found!', 'my_task_project_screen' => array()));
            die();
        }
    }

    /*
     * Delete project screen
     */

    public function delete_project_screen() {
        if ($this->input->method() != 'post') {
            echo json_encode(array('status' => '403', 'message' => 'Request Not Allowed.'));
            die();
        }

        $this->form_validation->set_rules('user_id', 'user_id', 'required');
        $this->form_validation->set_rules('project_id', 'project_id', 'required');
        $this->form_validation->set_rules('screen_id', 'screen_id', 'required');


        if ($this->form_validation->run() === FALSE) {
            echo json_encode(array('status' => '402', 'message' => 'Invalid Data Format.'));
            die();
        }

        $this->user_auth($this->input->post('user_id'));

        $project_id = $this->input->post('project_id');
        $screen_id = $this->input->post('screen_id');
        $con1 = array('project_id' => $project_id, 'floor_id' => $screen_id);
        $screen_def = $this->common->select_data_by_condition("project_floor_worker", $con1, '*');
        $screen_id = $this->input->post('screen_id');
        $con = array('project_id' => $project_id, 'id' => $screen_id);
        $res = $this->common->delete_data_by_condition('project_floor', $con);
        $con1 = array('project_id' => $project_id, 'floor_id' => $screen_id);
        $res1 = $this->common->delete_data_by_condition('project_floor_worker', $con1);

        if ($res && $res1) {

            foreach ($screen_def as $value) {
                $delete_notification = $this->common->delete_data('user_notification', 'def_id', $value['id']);
                $delete_comment = $this->common->delete_data('comment', 'def_id', $value['id']);
            }

            echo json_encode(array('status' => '200', 'message' => 'Screen Deleted succesfully.'));
            die();
        } else {
            echo json_encode(array('status' => '403', 'message' => 'Something Went wrong try again later.'));
            die();
        }
    }

    /*
     * Display or get Project Data
     */

    public function get_project_setting() {
        if ($this->input->method() != 'post') {
            echo json_encode(array('status' => '403', 'message' => 'Request Not Allowed.'));
            die();
        }

        $this->form_validation->set_rules('user_id', 'user_id', 'required');
        $this->form_validation->set_rules('project_id', 'project_id', 'required');


        if ($this->form_validation->run() === FALSE) {
            echo json_encode(array('status' => '402', 'message' => 'Invalid Data Format.'));
            die();
        }
        $project_id = $this->input->post('project_id');
        $this->user_auth($this->input->post('user_id'));

        $join_str[0] = array(
            'table' => 'users',
            'join_table_id' => 'users.id',
            'from_table_id' => 'project.user_id',
            'join_type' => '',
        );
        $project_data = $this->common->select_data_by_id('project', 'project.id', $project_id, 'project.*, users.firstname,users.lastname', $join_str);

        if (!empty($project_data)) {
            $project_image = $project_data[0]['project_images'];
            $project_data[0]['project_images'] = base_url() . $this->config->item('upload_projectimage_path') . $project_image;
            $project_data[0]['project_images_thumb'] = base_url() . $this->config->item('upload_projectimage_thumb') . $project_image;
            $project_data[0]['project_manager'] = $project_data[0]['firstname'] . ' ' . $project_data[0]['lastname'];
            echo json_encode(array('status' => '200', 'message' => 'Record found', 'project_data' => $project_data));
            die();
        } else {
            echo json_encode(array('status' => '404', 'message' => 'Record not found!'));
            die();
        }
    }

    /*
     * Edit Poject Data
     */

    public function edit_project_setting() {
        if ($this->input->method() != 'post') {
            echo json_encode(array('status' => '403', 'message' => 'Request Not Allowed.'));
            die();
        }

        $this->form_validation->set_rules('user_id', 'user_id', 'required');
        $this->form_validation->set_rules('project_id', 'project_id', 'required');
        $this->form_validation->set_rules('project_name', 'Project_title', 'required');
        $this->form_validation->set_rules('project_address', 'Project Address', 'required');
        $this->form_validation->set_rules('project_manager', 'Project manager', 'required');
        $this->form_validation->set_rules('project_company', 'Company name', 'required');

        if ($this->form_validation->run() === FALSE) {
            echo json_encode(array('status' => '402', 'message' => 'Invalid Data Format.'));
            die();
        }

        $this->user_auth($this->input->post('user_id'));

        $title = $this->input->post('project_name');
        $address = $this->input->post('project_address');
        $company = $this->input->post('project_company');

        $project_id = $this->input->post('project_id');

        $project = $this->common->select_data_by_id('project', 'id', $project_id, 'id,project_images');
        $old_image = $project[0]['project_images'];

        $update_data = array(
            'project_title' => $title,
            'project_address' => $address,
            'project_company' => $company,
        );

        if (isset($_FILES['project_photo']['name']) && $_FILES['project_photo']['name'] != null && $_FILES['project_photo']['size'] > 0) {
            $this->load->library('upload');
            $config['upload_path'] = $this->config->item('upload_projectimage_path');
            $config['encrypt_name'] = TRUE;
            $config['allowed_types'] = $this->config->item('allowed_types');
            $this->upload->initialize($config);

            if ($this->upload->do_upload('project_photo')) {
                $upload_data = $this->upload->data();
                $project_image = $upload_data['file_name'];
                $update_data['project_images'] = $project_image;
                $imageUploaderror = $this->upload->display_errors();
                //  $this->session->set_userdata('project_image', $project_image);
                if ($imageUploaderror == '') {
                    $config['source_image'] = $this->config->item('upload_projectimage_path') . $project_image;
                    $config['new_image'] = $this->config->item('upload_projectimage_thumb');
                    $config['create_thumb'] = TRUE;
                    $config['maintain_ratio'] = TRUE;
                    $config['thumb_marker'] = '';
                    $config['width'] = $this->config->item('upload_project_thumb_width');
                    $config['height'] = $this->config->item('upload_project_thumb_height');
                    $config['allowed_types'] = $this->config->item('allowed_types');
                    $this->load->library('image_lib');
                    $this->image_lib->initialize($config);
                    $this->image_lib->resize();

                    if (file_exists($this->config->item('upload_projectimage_path') . $old_image)) {
                        @unlink($this->config->item('upload_projectimage_path') . $old_image);
                    }

                    if (file_exists($this->config->item('upload_projectimage_thumb') . $old_image)) {
                        @unlink($this->config->item('upload_projectimage_thumb') . $old_image);
                    }
                } else {
                    echo json_encode(array('status' => '403', 'message' => 'Error in project image uploading.'));
                    die();
                }
            } else {
                echo json_encode(array('status' => '403', 'message' => 'Error in project image upload.'));
                die();
            }
        }

        if ($this->common->update_data($update_data, 'project', 'id', $project_id)) {
            echo json_encode(array('status' => '200', 'message' => 'Project Data Updated Succesfully.'));
            die();
        } else {

            echo json_encode(array('status' => '200', 'message' => 'Something went wrong please try later.'));
            die();
        }
    }

    /*
     * Edit Screen Name
     */

    public function change_screen_name() {
        if ($this->input->method() != 'post') {
            echo json_encode(array('status' => '403', 'message' => 'Request Not Allowed.'));
            die();
        }

        $this->form_validation->set_rules('user_id', 'user_id', 'required');
        $this->form_validation->set_rules('project_id', 'project_id', 'required');
        $this->form_validation->set_rules('screen_id', 'screen_id', 'required');
        $this->form_validation->set_rules('screen_name', 'screen_name', 'required');


        if ($this->form_validation->run() === FALSE) {
            echo json_encode(array('status' => '402', 'message' => 'Invalid Data Format.'));
            die();
        }

        $this->user_auth($this->input->post('user_id'));

        $screen_name = $this->input->post('screen_name');
        $screen_id = $this->input->post('screen_id');
        $project_id = $this->input->post('project_id');

        $data = array('floor_title' => $screen_name);
        $condition = array('id' => $screen_id);

        $res = $this->common->update_data($data, 'project_floor', 'id', $screen_id);

        if ($res) {
            echo json_encode(array('status' => '200', 'message' => 'Screen Name Change Succesfully.'));
            die();
        } else {
            echo json_encode(array('status' => '403', 'message' => 'Something went wrong! please try again later.'));
            die();
        }
    }

    /*
     * Get List Of Project all Action Items
     */

    public function get_all_action_item() {
        if ($this->input->method() != 'post') {
            echo json_encode(array('status' => '403', 'message' => 'Request Not Allowed.'));
            die();
        }

        $this->form_validation->set_rules('user_id', 'user_id', 'required');
        $this->form_validation->set_rules('project_id', 'project_id', 'required');



        if ($this->form_validation->run() === FALSE) {
            echo json_encode(array('status' => '402', 'message' => 'Invalid Data Format.'));
            die();
        }

        $this->user_auth($this->input->post('user_id'));
        $user_id = $this->input->post('user_id');
        $project_id = $this->input->post('project_id');
        $tradeworker_id = $this->input->post('tradeworker_id');
        /* $join_str[0] = array(
          'table' => 'users',
          'join_table_id' => 'users.id',
          'from_table_id' => 'project_floor_worker.tradeworker_id',
          'join_type' => 'LEFT',
          );
          $join_str[1] = array(
          'table' => 'project_floor',
          'join_table_id' => 'project_floor.id',
          'from_table_id' => 'project_floor_worker.floor_id',
          'join_type' => 'LEFT',
          ); */
        //list view of the deficiency

        $pfw = $this->common->select_data_by_id('project_floor_worker', 'project_id', $project_id, '*');
        // print_r($this->db->last_query());exit;
        if (!empty($pfw)) {
            foreach ($pfw as $value) {
                if ($user_id == $value['tradeworker_id']) {

                    $join_str[0] = array(
                        'table' => 'users',
                        'join_table_id' => 'users.id',
                        'from_table_id' => 'project_floor_worker.tradeworker_id',
                        'join_type' => 'LEFT',
                    );

                    $join_str[1] = array(
                        'table' => 'project_floor',
                        'join_table_id' => 'project_floor.id',
                        'from_table_id' => 'project_floor_worker.floor_id',
                        'join_type' => 'LEFT',
                    );
                } else {

                    $join_str[0] = array(
                        'table' => 'users',
                        'join_table_id' => 'users.id',
                        'from_table_id' => 'project_floor_worker.tradeworker_id',
                        'join_type' => 'LEFT',
                    );

                    $join_str[1] = array(
                        'table' => 'project_floor',
                        'join_table_id' => 'project_floor.id',
                        'from_table_id' => 'project_floor_worker.floor_id',
                        'join_type' => 'LEFT',
                    );
                }
            }
        } else {
            $join_str[0] = array(
                'table' => 'users',
                'join_table_id' => 'users.id',
                'from_table_id' => 'project_floor_worker.tradeworker_id',
                'join_type' => 'LEFT',
            );

            $join_str[1] = array(
                'table' => 'project_floor',
                'join_table_id' => 'project_floor.id',
                'from_table_id' => 'project_floor_worker.floor_id',
                'join_type' => 'LEFT',
            );
        }

        if ($tradeworker_id) {
            $condtn = array("project_floor_worker.project_id" => $project_id, 'project_floor_worker.status !=' => "completed", 'project_floor_worker.tradeworker_id' => $tradeworker_id, 'project_floor_worker.created_by !=' => 'tw_to_self');
        } else {
            $condtn = array("project_floor_worker.project_id" => $project_id, 'project_floor_worker.status !=' => "completed");
        }

        $project_deficiency_list = $this->common->select_data_by_allcondition('project_floor_worker', $condtn, 'users.id as user_id, project_floor.floor_title,project_floor.screen_image,project_floor_worker.*,users.firstname,users.lastname,users.profile_image,users.color_code', 'project_floor_worker.modified_datetime', 'DESC', '', '', $join_str, 'project_floor_worker.id');
        //print_r($project_deficiency_list);exit;
        //print_r($this->db->last_query());exit;
        if (!empty($project_deficiency_list)) {
            foreach ($project_deficiency_list as $key => $val) {
                $img = $this->common->select_data_by_condition('deficiency_image', array('floor_worker_id' => $val['id']), '*', 'deficiency_image.id', 'ASC');
                if (empty($img)) {
                    $project_deficiency_list[$key]['def_image'] = base_url() . '../uploads/noimage.png';
                } else {
                    $project_deficiency_list[$key]['def_image'] = base_url() . $this->config->item('upload_floor_deficiency_thumb') . $img[0]['image'];
                }

                if ($project_deficiency_list[$key]['profile_image'] == '') {

                    $project_deficiency_list[$key]['profile_image'] = base_url() . '../uploads/profile.png';
                } else {

                    $project_deficiency_list[$key]['profile_image'] = base_url() . $this->config->item('user_profile_upload_thumb_path') . $project_deficiency_list[$key]['profile_image'];
                }

                $timestamp = strtotime($val['created_datetime']);
                $project_deficiency_list[$key]['created_datetime'] = date('d F Y', $timestamp);



                if ($project_deficiency_list[$key]['status'] == "completed" || $project_deficiency_list[$key]['status'] == "reassigned") {

                    unset($project_deficiency_list[$key]);
                    continue;
                }

                if ($project_deficiency_list[$key]['status'] == 'canceled' || $project_deficiency_list[$key]['created_by'] == 'tw_to_self') {
                    unset($project_deficiency_list[$key]);
                    continue;
                }

                $con = array('comment.to_id' => $this->input->post('user_id'), 'comment.def_id' => $val['id'], 'comment.read_by_to_id' => 0);
                $unread_comment = $this->common->select_data_by_allcondition('comment', $con, '*', '', '', '', '', array(), 'comment.def_id');
                if (!empty($unread_comment)) {
                    $project_deficiency_list[$key]['unread_comment'] = 1;
                    $a[] = $project_deficiency_list[$key];
                    unset($project_deficiency_list[$key]);
                } else {
                    $project_deficiency_list[$key]['unread_comment'] = 0;
                }
            }

            if (isset($a) && !empty($a)) {
                foreach ($a as $ke => $va) {
                    array_unshift($project_deficiency_list, $a[$ke]);
                }
            }

            $project_deficiency_list = array_values($project_deficiency_list);
            // echo '<pre>';            print_r($project_deficiency_list);exit;

            echo json_encode(array('status' => '200', 'message' => 'Record found', 'get_action_item_list' => $project_deficiency_list));
            die();
        } else {
            echo json_encode(array('status' => '404', 'message' => 'Record not found'));
            die();
        }
    }

    /*
     * Get List of Project Screen Action Item
     */

    public function get_screen_action_item() {
        if ($this->input->method() != 'post') {
            echo json_encode(array('status' => '403', 'message' => 'Request Not Allowed.'));
            die();
        }

        $this->form_validation->set_rules('user_id', 'user_id', 'required');
        $this->form_validation->set_rules('project_id', 'project_id', 'required');
        $this->form_validation->set_rules('screen_id', 'screen_id', 'required');



        if ($this->form_validation->run() === FALSE) {
            echo json_encode(array('status' => '402', 'message' => 'Invalid Data Format.'));
            die();
        }

        $this->user_auth($this->input->post('user_id'));

        $project_id = $this->input->post('project_id');
        $screen_id = $this->input->post('screen_id');

        $join_str[0] = array(
            'table' => 'users',
            'join_table_id' => 'users.id',
            'from_table_id' => 'project_floor_worker.tradeworker_id',
            'join_type' => 'LEFT',
        );
        $join_str[1] = array(
            'table' => 'project_floor',
            'join_table_id' => 'project_floor.id',
            'from_table_id' => 'project_floor_worker.floor_id',
            'join_type' => 'LEFT',
        );

        $condition_array = array(
            'project_id' => $project_id,
            'screen_id' => $screen_id
        );

        // $filter_data = $this->common->select_data_by_condition('deflist_webview', $condition_array, '*', '', '', '', '', array());

        /* if (!empty($filter_data)) {
          $condtn = array("project_floor_worker.project_id" => $project_id, "project_floor_worker.floor_id" => $screen_id, "project_floor_worker.tradeworker_id" => $filter_data[0]['tradeworker_id'], 'project_floor_worker.status !=' => "completed");
          } else {
          $condtn = array("project_floor_worker.project_id" => $project_id, "project_floor_worker.floor_id" => $screen_id, 'project_floor_worker.status !=' => "completed");
          } */

        $condtn = array("project_floor_worker.project_id" => $project_id, "project_floor_worker.floor_id" => $screen_id, 'project_floor_worker.status !=' => "completed");

        $project_deficiency_list = $this->common->select_data_by_allcondition('project_floor_worker', $condtn, 'project_floor.floor_title,project_floor.screen_image,project_floor_worker.*,users.firstname,users.lastname,users.profile_image,users.color_code', 'project_floor_worker.modified_datetime', 'DESC', '', '', $join_str, 'project_floor_worker.id');

        //  echo '<pre>';print_r($project_deficiency_list);exit;
        if (!empty($project_deficiency_list)) {
            foreach ($project_deficiency_list as $key => $val) {
                $img = $this->common->select_data_by_condition('deficiency_image', array('floor_worker_id' => $val['id']), '*', 'deficiency_image.id', 'ASC');
                if (empty($img)) {
                    $project_deficiency_list[$key]['def_image'] = base_url() . '../uploads/noimage.png';
                } else {
                    $project_deficiency_list[$key]['def_image'] = base_url() . $this->config->item('upload_floor_deficiency_thumb') . $img[0]['image'];
                }

                if ($project_deficiency_list[$key]['profile_image'] == '') {

                    $project_deficiency_list[$key]['profile_image'] = base_url() . '../uploads/profile.png';
                } else {

                    $project_deficiency_list[$key]['profile_image'] = base_url() . $this->config->item('user_profile_upload_thumb_path') . $project_deficiency_list[$key]['profile_image'];
                }

                $timestamp = strtotime($val['created_datetime']);
                $project_deficiency_list[$key]['created_datetime'] = date('d F Y', $timestamp);



                if ($project_deficiency_list[$key]['status'] == "completed" || $project_deficiency_list[$key]['status'] == "reassigned") {
                    unset($project_deficiency_list[$key]);
                    continue;
                }

                if ($project_deficiency_list[$key]['status'] == 'canceled' || $project_deficiency_list[$key]['created_by'] == 'tw_to_self') {
                    unset($project_deficiency_list[$key]);
                    continue;
                }
                $con = array('comment.to_id' => $this->input->post('user_id'), 'comment.def_id' => $val['id'], 'comment.read_by_to_id' => 0);
                $unread_comment = $this->common->select_data_by_allcondition('comment', $con, '*', '', '', '', '', array(), 'comment.def_id');

                if (!empty($unread_comment)) {
                    $project_deficiency_list[$key]['unread_comment'] = 1;
                    $a[] = $project_deficiency_list[$key];
                    unset($project_deficiency_list[$key]);
                } else {
                    $project_deficiency_list[$key]['unread_comment'] = 0;
                }
            }
            //   echo '<pre>';print_r($project_deficiency_list);exit;
            if (isset($a) && !empty($a)) {
                foreach ($a as $ke => $va) {
                    array_unshift($project_deficiency_list, $a[$ke]);
                }
            }

            $project_deficiency_list = array_values($project_deficiency_list);

            echo json_encode(array('status' => '200', 'message' => 'Record found', 'get_screen_action_item_list' => $project_deficiency_list));
            die();
        } else {
            echo json_encode(array('status' => '404', 'message' => 'Record not found'));
            die();
        }
    }

    /*
     * Add More Scrrens to Project
     */

    public function add_more_screens() {
        if ($this->input->method() != 'post') {
            echo json_encode(array('status' => '403', 'message' => 'Request Not Allowed.'));
            die();
        }

        $this->form_validation->set_rules('user_id', 'user_id', 'required');
        $this->form_validation->set_rules('project_id', 'project_id', 'required');




        if ($this->form_validation->run() === FALSE) {
            echo json_encode(array('status' => '402', 'message' => 'Invalid Data Format.'));
            die();
        }

        $this->user_auth($this->input->post('user_id'));

        $project_id = $this->input->post('project_id');
        //$multiple_screen_image = array();
        $multiple_screen_image = $this->input->post('screen_multiple_image');
        // print_r($multiple_screen_image);exit;
        if (!empty($multiple_screen_image)) {
            if (!empty($project_id)) {
                $j = 1;


                foreach ($multiple_screen_image as $ky => $val) {


                    $img = str_replace('data:image/png;base64,', '', $val);

                    $img . "<br>";

                    $data = base64_decode($img);

                    $path = $this->config->item('upload_floor_path');

                    $filename = uniqid() . "_" . $this->input->post('user_id') . '.png';
                    $filename_array[] = $filename;

                    $file = $path . $filename;

                    $success = file_put_contents($file, $data);
                }


                foreach ($filename_array as $val) {
                    $floor_data = array(
                        'project_id' => $project_id,
                        'screen_image' => $val,
                        'floor_title' => "Screen_" . $j,
                        'status' => 'Enable',
                        'created_datetime' => date('Y-m-d h:i:s'),
                        'created_ip' => $this->get_client_ip(),
                    );
                    $res = $this->common->insert_data($floor_data, 'project_floor');
                    $j++;
                }

                if ($res) {

                    echo json_encode(array('status' => '200', 'message' => 'Your Project Added Successfully.'));
                    die();
                } else {
                    echo json_encode(array('status' => '403', 'message' => 'Error Occured please try after sometime!'));
                    die();
                }
            }
        } else {
            echo json_encode(array('status' => '403', 'message' => 'Please select images!'));
            die();
        }
    }

    /*
     * Get List of Project Messages
     */

    public function get_all_messages() {

        if ($this->input->method() != 'post') {
            echo json_encode(array('status' => '403', 'message' => 'Request Not Allowed.'));
            die();
        }

        $this->form_validation->set_rules('user_id', 'user_id', 'required');
        $this->form_validation->set_rules('project_id', 'project_id', 'required');




        if ($this->form_validation->run() === FALSE) {
            echo json_encode(array('status' => '402', 'message' => 'Invalid Data Format.'));
            die();
        }

        $this->user_auth($this->input->post('user_id'));


        $project_id = $this->input->post('project_id');
        $user_id = $this->input->post('user_id');

        $included_message_tradeworker = $this->search->web_api_get_message_tradeworker_exist($project_id, $user_id);

        $excluded_message_tradeworker = $this->search->web_api_get_message_tradeworker_notexist($project_id, $user_id);


        $unread_msg = 0;
        $this->data['last_msg_user'][0]['id'] = 0;
        foreach ($included_message_tradeworker as $k => $v) {
            $con = array('project_id' => $project_id, 'tradeworker_id' => $v['id']);
            $pro_profile_exist = $this->common->select_data_by_condition('project_profile', $con, '*');
            if (!empty($pro_profile_exist)) {
                $included_message_tradeworker[$k]['color_code'] = $pro_profile_exist[0]['color_code'];
            }

            $con = array('message.to_user_id' => $user_id, 'message.project_id' => $project_id, 'message.from_user_id' => $v['id'], 'message.read_by_to_id' => 0);
            $unread_count = count($this->common->select_data_by_condition('message', $con, '*'));
            if ($unread_count > 0) {
                $unread_msg += 1;
            }
            // $last_msg = $this->common->select_data_by_condition('message', $con, '*','message.id', 'DESC','1');
            $where = array("message.to_user_id" => $user_id, "message.from_user_id" => $v['id'], "message.project_id" => $project_id);
            $or_where = array("message.to_user_id" => $v['id'], "message.from_user_id" => $user_id, "message.project_id" => $project_id);
            $last_msg = $this->search->last_msg($user_id, $v['id'], $project_id);

            if (!empty($last_msg)) {
                if (intval($last_msg[0]['id']) > intval($this->data['last_msg_user'][0]['id'])) {
                    $this->data['last_msg_user'][0]['id'] = $last_msg[0]['id'];
                    $this->data['last_msg_user'][0]['project_id'] = $last_msg[0]['project_id'];
                    if ($last_msg[0]['from_user_id'] == $user_id) {
                        $this->data['last_msg_user'][0]['userid'] = $last_msg[0]['to_user_id'];
                    } else {
                        $this->data['last_msg_user'][0]['userid'] = $last_msg[0]['from_user_id'];
                    }
                }


                if (!empty($last_msg)) {
                    $included_message_tradeworker[$k]['last_msg'] = $last_msg[0]['text'];
                    $included_message_tradeworker[$k]['created_datetime'] = $last_msg[0]['created_datetime'];
                    $included_message_tradeworker[$k]['msg_id'] = $last_msg[0]['id'];
                } else {
                    $included_message_tradeworker[$k]['last_msg'] = "";
                }
                $included_message_tradeworker[$k]['unread_count'] = $unread_count;
                array_unshift($included_message_tradeworker[$k], $last_msg[0]['id']);
            }
            if ($included_message_tradeworker[$k]['profile_image'] == '') {
                $included_message_tradeworker[$k]['profile_image'] = base_url() . '../uploads/profile.png';
            } else {
                $included_message_tradeworker[$k]['profile_image'] = base_url() . $this->config->item('user_profile_upload_thumb_path') . $included_message_tradeworker[$k]['profile_image'];
            }

            if ($included_message_tradeworker[$k]['id'] == $user_id) {
                unset($included_message_tradeworker[$k]);
            }
        }
        arsort($included_message_tradeworker);

        // $included_message_tradeworker['unread_msg'] = $unread_msg;
        $included_message_tradeworker = array_values($included_message_tradeworker);

        foreach ($excluded_message_tradeworker as $key => $value) {
            $con = array('project_id' => $project_id, 'tradeworker_id' => $value['id']);
            $pro_profile_exist = $this->common->select_data_by_condition('project_profile', $con, '*');
            if (!empty($pro_profile_exist)) {
                $excluded_message_tradeworker[$key]['color_code'] = $pro_profile_exist[0]['color_code'];
            } else {
                $excluded_message_tradeworker[$key]['color_code'] = "D7DCDE";
            }

            if ($value['profile_image'] != '') {
                $excluded_message_tradeworker[$key]['profile_image'] = base_url() . $this->config->item('user_profile_upload_thumb_path') . $value['profile_image'];
            } else {
                $excluded_message_tradeworker[$key]['profile_image'] = base_url('../uploads/profile.png');
            }

            $excluded_message_tradeworker[$key]['last_msg'] = "";
            $excluded_message_tradeworker[$key]['msg_id'] = "";
            $excluded_message_tradeworker[$key]['unread_count'] = 0;
            $excluded_message_tradeworker[$key]['created_datetime'] = "";
            $excluded_message_tradeworker[$key]['0'] = "";
        }

        if (!empty($included_message_tradeworker || !empty($excluded_message_tradeworker))) {




            $message_list = array_merge($included_message_tradeworker, $excluded_message_tradeworker);


            echo json_encode(array('status' => '200', 'message' => 'record found.', "message_result" => $message_list));
            die();
        } else {
            echo json_encode(array('status' => '404', 'message' => 'record not found.'));
            die();
        }
    }

    /*
     * Get Assigned Project Screen 
     */

    public function get_assigned_project_screen() {
        if ($this->input->method() != 'post') {
            echo json_encode(array('status' => '403', 'message' => 'Request Not Allowed.'));
            die();
        }

        $this->form_validation->set_rules('user_id', 'user_id', 'required');
        $this->form_validation->set_rules('project_id', 'project_id', 'required');




        if ($this->form_validation->run() === FALSE) {
            echo json_encode(array('status' => '402', 'message' => 'Invalid Data Format.'));
            die();
        }

        $this->user_auth($this->input->post('user_id'));


        $project_id = $this->input->post('project_id');
        $user_id = $this->input->post('user_id');

        $join_str[0] = array(
            'table' => 'project_floor',
            'join_table_id' => 'project_floor.project_id',
            'from_table_id' => 'project.id',
            'join_type' => 'LEFT',
        );
        $project = $this->common->select_data_by_id('project', 'project.id', $project_id, '*,project.id as pro_id', $join_str);

        foreach ($project as $key => $val) {
            //  $project[$key]['def'] = count($this->common->select_data_by_id('project_floor_worker', 'floor_id', $val['id']));
            $con = array('floor_id' => $val['id'], 'status' => 'pending', 'tradeworker_id' => $user_id);
            $project[$key]['def'] = count($this->common->select_data_by_condition('project_floor_worker', $con));
            $con = array('floor_id' => $val['id'], 'status' => 'pending', 'viewed' => '0', 'tradeworker_id' => $user_id);
            $project[$key]['new_def'] = count($this->common->select_data_by_condition('project_floor_worker', $con));
            $con = array('comment.to_id' => $user_id, 'comment.floor_id' => $val['id'], 'comment.read_by_to_id' => 0);
            $unread_comment_count = count($this->common->select_data_by_allcondition('comment', $con, '*', '', '', '', '', array(), 'comment.def_id'));
            $project[$key]['unread_comment_count'] = $unread_comment_count;

            $project[$key]['sent_task_green'] = count($this->common->select_data_by_condition('project_floor_worker', array('floor_id' => $val['id'], 'when_drop_tw_to_pm' => $user_id, 'created_by' => 'tw_to_pm', 'status' => 'pending')));
            $project[$key]['mytasks_blue'] = count($this->common->select_data_by_condition('project_floor_worker', array('floor_id' => $val['id'], 'tradeworker_id' => $user_id, 'status' => 'pending')));

            $con_purple = array('floor_id' => $val['id'], 'status' => 'approve');
            $project[$key]['def_completed_purple'] = count($this->common->select_data_by_condition('project_floor_worker', $con_purple));


            $project[$key]['screen_image'] = base_url() . $this->config->item('upload_floor_path') . $project[$key]['screen_image'];
        }

        if (!empty($project)) {
            echo json_encode(array('status' => '200', 'message' => 'record found.', "get_assigned_project_screen_list" => $project));
            die();
        } else {
            echo json_encode(array('status' => '404', 'message' => 'record not found.'));
            die();
        }
    }

    /*
     * Get Assigned Project invited tradeworker/manager name
     */

    public function get_assigned_invited_tradeworker() {

        if ($this->input->method() != 'post') {
            echo json_encode(array('status' => '403', 'message' => 'Request Not allowed.'));
            die();
        }


        $this->form_validation->set_rules('user_id', 'user_id', 'required');
        $this->form_validation->set_rules('project_id', 'project_id', 'required');


        if ($this->form_validation->run() === FALSE) {
            echo json_encode(array('status' => '402', 'message' => 'Invalid Data Format.'));
            die();
        }

        $this->user_auth($this->input->post('user_id'));
        $project_id = $this->input->post('project_id');
        $user_id = $this->input->post('user_id');


        $join_str[0] = array(
            'table' => 'project',
            'join_table_id' => 'project.user_id',
            'from_table_id' => 'users.id',
            'join_type' => 'LEFT',
        );

        $project_manager_data = $this->common->select_data_by_id('users', 'project.id', $project_id, 'users.*', $join_str);


        if (!empty($project_manager_data)) {

            foreach ($project_manager_data as $key => $val) {
                if ($project_manager_data[$key]['profile_image'] == '') {
                    $project_manager_data[$key]['profile_image'] = base_url() . '../uploads/profile.png';
                    $project_manager_data[$key]['profile_image_thumb'] = base_url() . '../uploads/profile.png';
                } else {
                    $project_manager_data[$key]['profile_image'] = base_url() . $this->config->item('user_profile_upload_path') . $project_manager_data[$key]['profile_image'];
                    $project_manager_data[$key]['profile_image_thumb'] = base_url() . $this->config->item('user_profile_upload_path_thumb') . $project_manager_data[$key]['profile_image'];
                }
            }
            echo json_encode(array('status' => '200', 'message' => 'Record found.', 'asssigned_tradeworker_invite_tradeworker_list' => $project_manager_data));
            die();
        } else {
            echo json_encode(array('status' => '200', 'message' => 'Record not found.'));
            die();
        }
    }

    /*
     * Get All Assigned Project Action Item
     */

    public function get_all_assigned_action_item() {
        if ($this->input->method() != 'post') {
            echo json_encode(array('status' => '403', 'message' => 'Request Not Allowed.'));
            die();
        }

        $this->form_validation->set_rules('user_id', 'user_id', 'required');
        $this->form_validation->set_rules('project_id', 'project_id', 'required');



        if ($this->form_validation->run() === FALSE) {
            echo json_encode(array('status' => '402', 'message' => 'Invalid Data Format.'));
            die();
        }

        $this->user_auth($this->input->post('user_id'));

        $project_id = $this->input->post('project_id');
        $user_id = $this->input->post('user_id');

        $condi = array('project.id' => $project_id);
        $project = $this->common->select_data_by_condition('project', $condi, '*', '', '', '', '', array());

        $this->data['manager'] = $manager = $this->common->select_data_by_id('users', 'id', $project[0]['user_id']);

        // $con = array('project_id' => $projectid, 'tradeworker_id' => $user_id);
        // $manager_pin = $this->common->select_data_by_condition("project_profile", $con, 'project_profile.color_code,project_profile.name');
        // $this->data['manager_pin'] = $manager_pin;
        //listview for tradeworker to manager pins


        $join_str[0] = array(
            'table' => 'users',
            'join_table_id' => 'users.id',
            'from_table_id' => 'project_floor_worker.tradeworker_id',
            'join_type' => 'LEFT',
        );

        $join_str[1] = array(
            'table' => 'project_floor',
            'join_table_id' => 'project_floor.id',
            'from_table_id' => 'project_floor_worker.floor_id',
            'join_type' => 'LEFT',
        );


        $condtn = array("project_floor_worker.project_id" => $project_id, 'project_floor_worker.tradeworker_id' => $project[0]['user_id'], 'project_floor_worker.created_by' => 'tw_to_pm', 'when_drop_tw_to_pm' => $user_id);
        $this->data['project_deficiency_list'] = $project_deficiency_list = $this->common->select_data_by_allcondition('project_floor_worker', $condtn, 'project_floor_worker.*,project_floor.floor_title,project_floor.screen_image,users.firstname,users.lastname,users.profile_image,users.color_code', 'project_floor_worker.modified_datetime', 'DESC', '', '', $join_str, 'project_floor_worker.id');
        // echo '<pre>';                print_r($project_deficiency_list);exit;
        foreach ($project_deficiency_list as $k => $pdl) {
            if ($pdl['status'] == 'canceled') {
                unset($project_deficiency_list[$k]);
                continue;
            }

            $con = array('project_id' => $pdl['project_id'], 'tradeworker_id' => $pdl['tradeworker_id']);
            $res = $this->common->select_data_by_condition("project_profile", $con, 'project_profile.color_code,project_profile.name');
            if (!empty($res)) {
                $project_deficiency_list[$k]['color_code'] = $res[0]['color_code'];
                $project_deficiency_list[$k]['profile_name'] = $res[0]['name'];
            } else {

                $con = array('id' => $pdl['tradeworker_id']);
                $res = $this->common->select_data_by_condition("users", $con, '*');
                $project_deficiency_list[$k]['profile_name'] = $res[0]['firstname'] . " " . $res[0]['lastname'];
            }
            $img = $this->common->select_data_by_condition('deficiency_image', array('floor_worker_id' => $pdl['id']), '*', 'deficiency_image.id', 'ASC');

            if (!empty($img)) {
                $project_deficiency_list[$k]['def_image'] = $img[0]['image'];
            } else {
                $project_deficiency_list[$k]['def_image'] = '';
            }
            if ($project_deficiency_list[$k]['profile_image'] == '') {

                $project_deficiency_list[$k]['profile_image'] = base_url() . '../uploads/profile.png';
            } else {


                $project_deficiency_list[$k]['profile_image'] = base_url() . $this->config->item('user_profile_upload_thumb_path') . $project_deficiency_list[$k]['profile_image'];
            }
            $con = array('comment.to_id' => $user_id, 'comment.def_id' => $pdl['id'], 'comment.read_by_to_id' => 0);
            $unread_comment = $this->common->select_data_by_allcondition('comment', $con, '*', '', '', '', '', array(), 'comment.def_id');
            if (!empty($unread_comment)) {
                $project_deficiency_list[$k]['unread_comment'] = 1;
                $a[] = $project_deficiency_list[$k];
                unset($project_deficiency_list[$k]);
                continue;
            } else {
                $project_deficiency_list[$k]['unread_comment'] = 0;
            }

            if ($pdl['status'] == "completed" || $pdl['status'] == "reassigned") {
                $history[$k] = $project_deficiency_list[$k];
                unset($project_deficiency_list[$k]);
                continue;
            }
        }

        $trade_droped_manager_pin = $project_deficiency_list;
        $join_str[0] = array(
            'table' => 'users',
            'join_table_id' => 'users.id',
            'from_table_id' => 'project_floor_worker.tradeworker_id',
            'join_type' => 'LEFT',
        );
        $join_str[1] = array(
            'table' => 'project_floor',
            'join_table_id' => 'project_floor.id',
            'from_table_id' => 'project_floor_worker.floor_id',
            'join_type' => 'LEFT',
        );


        $condtn = array("project_floor_worker.project_id" => $project_id, "project_floor_worker.tradeworker_id" => $user_id, 'project_floor_worker.status !=' => "completed");
        $project_assigned_deficiency_list = $this->common->select_data_by_allcondition('project_floor_worker', $condtn, 'project_floor_worker.*,users.id as user_id, project_floor.floor_title,project_floor.screen_image,users.firstname,users.lastname,users.profile_image,users.color_code', 'project_floor_worker.modified_datetime', 'DESC', '', '', $join_str, 'project_floor_worker.id');

        // echo '<pre>'; print_r($project_assigned_deficiency_list);exit;
        if (!empty($project_assigned_deficiency_list)) {
            foreach ($project_assigned_deficiency_list as $key => $val) {
                $img = $this->common->select_data_by_condition('deficiency_image', array('floor_worker_id' => $val['id']), '*', 'deficiency_image.id', 'ASC');
                if (empty($img)) {
                    $project_assigned_deficiency_list[$key]['def_image'] = base_url() . '../uploads/noimage.png';
                } else {
                    $project_assigned_deficiency_list[$key]['def_image'] = base_url() . $this->config->item('upload_floor_deficiency_thumb') . $img[0]['image'];
                }
                $project = $this->common->select_data_by_id('project', 'project.id', $project_id, '*,project.id as pro_id', array());
                $this->data['manager'] = $manager = $this->common->select_data_by_id('users', 'id', $project[0]['user_id']);

                if ($val['created_by'] == 'pm_to_tw') {
                    $project_assigned_deficiency_list[$key]['firstname'] = $manager[0]['firstname'];
                    $project_assigned_deficiency_list[$key]['lastname'] = $manager[0]['lastname'];
                }


                if ($project_assigned_deficiency_list[$key]['profile_image'] == '') {

                    $project_assigned_deficiency_list[$key]['profile_image'] = base_url() . '../uploads/profile.png';
                } else {
                    if ($val['created_by'] == 'pm_to_tw') {
                        $project_assigned_deficiency_list[$key]['profile_image'] = base_url() . $this->config->item('user_profile_upload_thumb_path') . $manager[0]['profile_image'];
                    } else {

                        $project_assigned_deficiency_list[$key]['profile_image'] = base_url() . $this->config->item('user_profile_upload_thumb_path') . $project_assigned_deficiency_list[$key]['profile_image'];
                    }
                }

                $timestamp = strtotime($val['created_datetime']);
                $project_assigned_deficiency_list[$key]['created_datetime'] = date('d F Y', $timestamp);

                if ($project_assigned_deficiency_list[$key]['status'] == "completed" || $project_assigned_deficiency_list[$key]['status'] == "reassigned") {

                    unset($project_assigned_deficiency_list[$key]);
                    continue;
                }


                $con = array('comment.to_id' => $this->input->post('user_id'), 'comment.def_id' => $val['id'], 'comment.read_by_to_id' => 0);
                $unread_comment = $this->common->select_data_by_allcondition('comment', $con, '*', '', '', '', '', array(), 'comment.def_id');
                if (!empty($unread_comment)) {
                    $project_assigned_deficiency_list[$key]['unread_comment'] = 1;
                    $a[] = $project_assigned_deficiency_list[$key];
                    unset($project_assigned_deficiency_list[$key]);
                } else {
                    $project_assigned_deficiency_list[$key]['unread_comment'] = 0;
                }
            }



            $project_assigned_deficiency_list = array_values($project_assigned_deficiency_list);


            $project_assigned_deficiency_list = array_merge($project_assigned_deficiency_list, $trade_droped_manager_pin);

            arsort($project_assigned_deficiency_list);
            $project_assigned_deficiency_list = array_values($project_assigned_deficiency_list);
            // print_r($project_assigned_deficiency_list);exit;
            echo json_encode(array('status' => '200', 'message' => 'Record found', 'get_action_assigned_item_list' => $project_assigned_deficiency_list));
            die();
        } else {
            echo json_encode(array('status' => '404', 'message' => 'Record not found'));
            die();
        }
    }

    /*
     * Get Assigned project Screen Action Item
     */

    public function get_assigned_screen_action_item() {
        if ($this->input->method() != 'post') {
            echo json_encode(array('status' => '403', 'message' => 'Request Not Allowed.'));
            die();
        }

        $this->form_validation->set_rules('user_id', 'user_id', 'required');
        $this->form_validation->set_rules('project_id', 'project_id', 'required');
        $this->form_validation->set_rules('screen_id', 'screen_id', 'required');



        if ($this->form_validation->run() === FALSE) {
            echo json_encode(array('status' => '402', 'message' => 'Invalid Data Format.'));
            die();
        }

        $this->user_auth($this->input->post('user_id'));

        $project_id = $this->input->post('project_id');
        $user_id = $this->input->post('user_id');
        $floor_id = $this->input->post('screen_id');

        $condi = array('project.id' => $project_id);
        $project = $this->common->select_data_by_condition('project', $condi, '*', '', '', '', '', array());

        $this->data['manager'] = $manager = $this->common->select_data_by_id('users', 'id', $project[0]['user_id']);

        // $con = array('project_id' => $projectid, 'tradeworker_id' => $user_id);
        // $manager_pin = $this->common->select_data_by_condition("project_profile", $con, 'project_profile.color_code,project_profile.name');
        // $this->data['manager_pin'] = $manager_pin;
        //listview for tradeworker to manager pins


        $join_str[0] = array(
            'table' => 'users',
            'join_table_id' => 'users.id',
            'from_table_id' => 'project_floor_worker.tradeworker_id',
            'join_type' => 'LEFT',
        );

        $join_str[1] = array(
            'table' => 'project_floor',
            'join_table_id' => 'project_floor.id',
            'from_table_id' => 'project_floor_worker.floor_id',
            'join_type' => 'LEFT',
        );


        $condtn = array("project_floor_worker.project_id" => $project_id, "project_floor_worker.floor_id" => $floor_id, 'project_floor_worker.tradeworker_id' => $project[0]['user_id'], 'project_floor_worker.created_by' => 'tw_to_pm', 'when_drop_tw_to_pm' => $user_id);


        $this->data['project_deficiency_list'] = $project_deficiency_list = $this->common->select_data_by_allcondition('project_floor_worker', $condtn, 'project_floor_worker.*,project_floor.floor_title,project_floor.screen_image,users.firstname,users.lastname,users.profile_image,users.color_code', 'project_floor_worker.modified_datetime', 'DESC', '', '', $join_str, 'project_floor_worker.id');
        //  echo '<pre>';                print_r($project_deficiency_list);exit;
        foreach ($project_deficiency_list as $k => $pdl) {
            if ($pdl['status'] == 'canceled') {
                unset($project_deficiency_list[$k]);
                continue;
            }

            $con = array('project_id' => $pdl['project_id'], 'tradeworker_id' => $pdl['tradeworker_id']);
            $res = $this->common->select_data_by_condition("project_profile", $con, 'project_profile.color_code,project_profile.name');
            if (!empty($res)) {
                $project_deficiency_list[$k]['color_code'] = $res[0]['color_code'];
                $project_deficiency_list[$k]['profile_name'] = $res[0]['name'];
            } else {

                $con = array('id' => $pdl['tradeworker_id']);
                $res = $this->common->select_data_by_condition("users", $con, '*');
                $project_deficiency_list[$k]['profile_name'] = $res[0]['firstname'] . " " . $res[0]['lastname'];
            }
            $img = $this->common->select_data_by_condition('deficiency_image', array('floor_worker_id' => $pdl['id']), '*', 'deficiency_image.id', 'ASC');

            if (!empty($img)) {
                $project_deficiency_list[$k]['def_image'] = $img[0]['image'];
            } else {
                $project_deficiency_list[$k]['def_image'] = '';
            }
            if ($project_deficiency_list[$k]['profile_image'] == '') {

                $project_deficiency_list[$k]['profile_image'] = base_url() . '../uploads/profile.png';
            } else {


                $project_deficiency_list[$k]['profile_image'] = base_url() . $this->config->item('user_profile_upload_thumb_path') . $project_deficiency_list[$k]['profile_image'];
            }
            $con = array('comment.to_id' => $user_id, 'comment.def_id' => $pdl['id'], 'comment.read_by_to_id' => 0);
            $unread_comment = $this->common->select_data_by_allcondition('comment', $con, '*', '', '', '', '', array(), 'comment.def_id');
            if (!empty($unread_comment)) {
                $project_deficiency_list[$k]['unread_comment'] = 1;
                $a[] = $project_deficiency_list[$k];
                unset($project_deficiency_list[$k]);
                continue;
            } else {
                $project_deficiency_list[$k]['unread_comment'] = 0;
            }

            if ($pdl['status'] == "completed" || $pdl['status'] == "reassigned") {
                $history[$k] = $project_deficiency_list[$k];
                unset($project_deficiency_list[$k]);
                continue;
            }
        }

        $trade_droped_manager_pin = $project_deficiency_list;
        $join_str[0] = array(
            'table' => 'users',
            'join_table_id' => 'users.id',
            'from_table_id' => 'project_floor_worker.tradeworker_id',
            'join_type' => 'LEFT',
        );
        $join_str[1] = array(
            'table' => 'project_floor',
            'join_table_id' => 'project_floor.id',
            'from_table_id' => 'project_floor_worker.floor_id',
            'join_type' => 'LEFT',
        );

        $condition_array = array(
            'project_id' => $project_id,
            'screen_id' => $floor_id
        );


        $condtn = array("project_floor_worker.project_id" => $project_id, "project_floor_worker.floor_id" => $floor_id, "project_floor_worker.tradeworker_id" => $user_id, 'project_floor_worker.status !=' => "completed");


        $project_assigned_deficiency_list = $this->common->select_data_by_allcondition('project_floor_worker', $condtn, 'project_floor_worker.*,users.id as user_id, project_floor.floor_title,project_floor.screen_image,users.firstname,users.lastname,users.profile_image,users.color_code', 'project_floor_worker.modified_datetime', 'DESC', '', '', $join_str, 'project_floor_worker.id');

        // echo '<pre>'; print_r($project_assigned_deficiency_list);exit;
        if (!empty($project_assigned_deficiency_list)) {
            foreach ($project_assigned_deficiency_list as $key => $val) {
                $img = $this->common->select_data_by_condition('deficiency_image', array('floor_worker_id' => $val['id']), '*', 'deficiency_image.id', 'ASC');
                if (empty($img)) {
                    $project_assigned_deficiency_list[$key]['def_image'] = base_url() . '../uploads/noimage.png';
                } else {
                    $project_assigned_deficiency_list[$key]['def_image'] = base_url() . $this->config->item('upload_floor_deficiency_thumb') . $img[0]['image'];
                }
                $project = $this->common->select_data_by_id('project', 'project.id', $project_id, '*,project.id as pro_id', array());
                $this->data['manager'] = $manager = $this->common->select_data_by_id('users', 'id', $project[0]['user_id']);

                if ($val['created_by'] == 'pm_to_tw') {
                    $project_assigned_deficiency_list[$key]['firstname'] = $manager[0]['firstname'];
                    $project_assigned_deficiency_list[$key]['lastname'] = $manager[0]['lastname'];
                }


                if ($project_assigned_deficiency_list[$key]['profile_image'] == '') {

                    $project_assigned_deficiency_list[$key]['profile_image'] = base_url() . '../uploads/profile.png';
                } else {
                    if ($val['created_by'] == 'pm_to_tw') {
                        $project_assigned_deficiency_list[$key]['profile_image'] = base_url() . $this->config->item('user_profile_upload_thumb_path') . $manager[0]['profile_image'];
                    } else {

                        $project_assigned_deficiency_list[$key]['profile_image'] = base_url() . $this->config->item('user_profile_upload_thumb_path') . $project_assigned_deficiency_list[$key]['profile_image'];
                    }
                }

                $timestamp = strtotime($val['created_datetime']);
                $project_assigned_deficiency_list[$key]['created_datetime'] = date('d F Y', $timestamp);

                if ($project_assigned_deficiency_list[$key]['status'] == "completed" || $project_assigned_deficiency_list[$key]['status'] == "reassigned") {

                    unset($project_assigned_deficiency_list[$key]);
                    continue;
                }


                $con = array('comment.to_id' => $this->input->post('user_id'), 'comment.def_id' => $val['id'], 'comment.read_by_to_id' => 0);
                $unread_comment = $this->common->select_data_by_allcondition('comment', $con, '*', '', '', '', '', array(), 'comment.def_id');
                if (!empty($unread_comment)) {
                    $project_assigned_deficiency_list[$key]['unread_comment'] = 1;
                    $a[] = $project_assigned_deficiency_list[$key];
                    unset($project_assigned_deficiency_list[$key]);
                } else {
                    $project_assigned_deficiency_list[$key]['unread_comment'] = 0;
                }
            }



            $project_assigned_deficiency_list = array_values($project_assigned_deficiency_list);

            $condition_array = array(
                'project_id' => $project_id,
                'screen_id' => $floor_id,
            );

            /* $filter_data = $this->common->select_data_by_condition('deflist_webview', $condition_array, '*', '', '', '', '', array());
              if(!empty($filter_data)){
              if($filter_data[0]['tradeworker_id'] == $user_id){
              $project_assigned_deficiency_list = $project_assigned_deficiency_list;
              }else{
              $project_assigned_deficiency_list = $trade_droped_manager_pin;
              }

              }else{
              $project_assigned_deficiency_list = array_merge($project_assigned_deficiency_list, $trade_droped_manager_pin);
              } */

            $project_assigned_deficiency_list = array_merge($project_assigned_deficiency_list, $trade_droped_manager_pin);
            arsort($project_assigned_deficiency_list);

            $project_assigned_deficiency_list = array_values($project_assigned_deficiency_list);
            // print_r($project_assigned_deficiency_list);exit;
            echo json_encode(array('status' => '200', 'message' => 'Record found', 'get_screen_action_assigned_item_list' => $project_assigned_deficiency_list));
            die();
        } else {
            echo json_encode(array('status' => '404', 'message' => 'Record not found'));
            die();
        }
    }

    /*
     * Get All Assigned project message
     */

    public function get_all_assigned_message() {

        if ($this->input->method() != 'post') {
            echo json_encode(array('status' => '403', 'message' => 'Request Not Allowed.'));
            die();
        }

        $this->form_validation->set_rules('user_id', 'user_id', 'required');
        $this->form_validation->set_rules('project_id', 'project_id', 'required');




        if ($this->form_validation->run() === FALSE) {
            echo json_encode(array('status' => '402', 'message' => 'Invalid Data Format.'));
            die();
        }

        // $this->user_auth($this->input->post('user_id'));


        $project_id = $this->input->post('project_id');
        $user_id = $this->input->post('user_id');

        $condtn = array("id" => $project_id);

        $project_manager_detail = $this->common->select_data_by_condition("project", $condtn, '*');

        $manager_id = $project_manager_detail[0]['user_id'];
        $project_name = $project_manager_detail[0]['project_title'];

        $included_message_data = $this->search->get_assigned_message_api($project_id, $manager_id, $user_id);


        $unread_msg = 0;
        $this->data['last_msg_user'][0]['id'] = 0;
        foreach ($included_message_data as $k => $v) {
            $con = array('project_id' => $project_id, 'tradeworker_id' => $v['id']);
            $pro_profile_exist = $this->common->select_data_by_condition('project_profile', $con, '*');
            if (!empty($pro_profile_exist)) {
                $included_message_data[$k]['color_code'] = $pro_profile_exist[0]['color_code'];
            }

            $con = array('message.to_user_id' => $user_id, 'message.project_id' => $project_id, 'message.from_user_id' => $v['id'], 'message.read_by_to_id' => 0);
            $unread_count = count($this->common->select_data_by_condition('message', $con, '*'));
            if ($unread_count > 0) {
                $unread_msg += 1;
            }
            // $last_msg = $this->common->select_data_by_condition('message', $con, '*','message.id', 'DESC','1');
            $where = array("message.to_user_id" => $user_id, "message.from_user_id" => $v['id'], "message.project_id" => $project_id);
            $or_where = array("message.to_user_id" => $v['id'], "message.from_user_id" => $user_id, "message.project_id" => $project_id);
            $last_msg = $this->search->last_msg($user_id, $manager_id, $project_id);
            //	echo $this->db->last_query();
            // print_r($last_msg);exit;
            if (!empty($last_msg)) {
                if (intval($last_msg[0]['id']) > intval($this->data['last_msg_user'][0]['id'])) {
                    $this->data['last_msg_user'][0]['id'] = $last_msg[0]['id'];
                    $this->data['last_msg_user'][0]['project_id'] = $last_msg[0]['project_id'];
                    if ($last_msg[0]['from_user_id'] == $user_id) {
                        $this->data['last_msg_user'][0]['userid'] = $last_msg[0]['to_user_id'];
                    } else {
                        $this->data['last_msg_user'][0]['userid'] = $last_msg[0]['from_user_id'];
                    }
                }
            }

            if (!empty($last_msg)) {
                $included_message_data[$k]['last_msg'] = $last_msg[0]['text'];
                $included_message_data[$k]['created_datetime'] = $last_msg[0]['created_datetime'];
                $included_message_data[$k]['msg_id'] = $last_msg[0]['id'];
            } else {
                $included_message_data[$k]['last_msg'] = "";
                $included_message_data[$k]['created_datetime'] = "";
            }
            $included_message_data[$k]['unread_count'] = $unread_count;
        }


//        print_r($this->db->last_query());exit;

        if (!empty($included_message_data)) {//echo "hii";exit;
            foreach ($included_message_data as $j => $val) {
                if ($included_message_data[$j]['profile_image'] == '') {
                    $included_message_data[$j]['profile_image'] = base_url() . '../uploads/profile.png';
                } else {
                    $included_message_data[$j]['profile_image'] = base_url() . $this->config->item('user_profile_upload_thumb_path') . $included_message_data[$j]['profile_image'];
                }
            }

            echo json_encode(array('status' => '200', 'message' => 'record found.', "message_result" => $included_message_data));
            die();
        } else {
//           
            echo json_encode(array('status' => '404', 'message' => 'record not found.'));
            die();
        }
    }

    /*
     * Get Deficiency Information
     */

    public function get_deficiency_information() {

        if ($this->input->method() != 'post') {
            echo json_encode(array('status' => '403', 'message' => 'Request Not Allowed.'));
            die();
        }

        $this->form_validation->set_rules('user_id', 'user_id', 'required');
        $this->form_validation->set_rules('project_id', 'project_id', 'required');
        $this->form_validation->set_rules('screen_id', 'screen_id', 'required');
        $this->form_validation->set_rules('deficiency_id', 'deficiency_id', 'required');



        if ($this->form_validation->run() === FALSE) {
            echo json_encode(array('status' => '402', 'message' => 'Invalid Data Format.'));
            die();
        }

        $this->user_auth($this->input->post('user_id'));

        $project_id = $this->input->post('project_id');
        $screen_id = $this->input->post('screen_id');
        $deficiency_id = $this->input->post('deficiency_id');

        $join_str[0] = array(
            'table' => 'users',
            'join_table_id' => 'users.id',
            'from_table_id' => 'project_floor_worker.tradeworker_id',
            'join_type' => 'LEFT',
        );
        $join_str[1] = array(
            'table' => 'project_floor',
            'join_table_id' => 'project_floor.id',
            'from_table_id' => 'project_floor_worker.floor_id',
            'join_type' => 'LEFT',
        );


        $condtn = array("project_floor_worker.project_id" => $project_id, "project_floor_worker.floor_id" => $screen_id, "project_floor_worker.id" => $deficiency_id, 'project_floor_worker.status !=' => "completed");
        $project_deficiency = $this->common->select_data_by_allcondition('project_floor_worker', $condtn, 'project_floor.floor_title,project_floor.screen_image,project_floor_worker.*,users.firstname,users.lastname,users.profile_image,users.color_code', 'project_floor_worker.modified_datetime', 'DESC', '', '', $join_str, 'project_floor_worker.id');

        if (!empty($project_deficiency)) {


            $this->data['proj'] = $this->data['project'] = $proj = $this->common->select_data_by_id("project", "id", $project_id, "user_id,project.id as project_id,id,project_title");
            $this->data['manager'] = $manager = $this->common->select_data_by_id('users', 'id', $proj[0]['user_id']);

            $project_deficiency[0]['manager_name'] = $manager[0]['firstname'] . " " . $manager[0]['lastname'];
            $project_deficiency[0]['manager_profile_image'] = base_url() . $this->config->item('user_profile_upload_thumb_path') . $manager[0]['profile_image'];
            if ($project_deficiency[0]['created_by'] == 'tw_to_pm') {
                $mngr = $this->common->select_data_by_id('users', 'id', $project_deficiency[0]['when_drop_tw_to_pm']);
                //echo "<pre>"; print_r($mngr);die();
                $project_deficiency[0]['manager_name'] = ucfirst($mngr[0]['firstname']) . " " . ucfirst($mngr[0]['lastname']);
                if ($mngr[0]['profile_image'] != '') {
                    $project_deficiency[0]['manager_profile_image'] = base_url() . $this->config->item('user_profile_upload_thumb_path') . $mngr[0]['profile_image'];
                } else {
                    $project_deficiency[0]['manager_profile_image'] = base_url('../uploads/profile.png');
                }
            }
            if ($project_deficiency[0]['created_by'] == 'tw_to_self') {
                if ($project_deficiency[0]['profile_image'] != '') {
                    $project_deficiency[0]['manager_profile_image'] = base_url() . $this->config->item('user_profile_upload_thumb_path') . $project_deficiency[0]['profile_image'];
                    $project_deficiency[0]['manager_name'] = ucfirst($project_deficiency[0]['firstname']) . " " . ucfirst($project_deficiency[0]['lastname']);
                } else {
                    $project_deficiency[0]['manager_profile_image'] = base_url('../uploads/profile.png');
                    $project_deficiency[0]['manager_name'] = ucfirst($project_deficiency[0]['firstname']) . " " . ucfirst($project_deficiency[0]['lastname']);
                }
            }

            if ($project_deficiency[0]['profile_image'] == '') {

                $project_deficiency[0]['profile_image'] = base_url() . '../uploads/profile.png';
            } else {

                $project_deficiency[0]['profile_image'] = base_url() . $this->config->item('user_profile_upload_thumb_path') . $project_deficiency[0]['profile_image'];
            }

            if ($project_deficiency[0]['screen_image'] == '') {

                $project_deficiency[0]['screen_image'] = base_url() . '../uploads/noimage.png';
            } else {

                $project_deficiency[0]['screen_image'] = base_url() . $this->config->item('upload_floor_path') . $project_deficiency[0]['screen_image'];
            }

            $deficiency_img = $this->common->select_data_by_condition('deficiency_image', array('floor_worker_id' => $deficiency_id), '*', 'deficiency_image.id', 'ASC');

            if (empty($deficiency_img)) {
                $deficiency_img[0]['def_image'] = base_url() . '../uploads/noimage.png';
            } else {
                foreach ($deficiency_img as $key => $val) {
                    if (empty($deficiency_img[$key]['image'])) {
                        $deficiency_img[$key]['def_image'] = base_url() . '../uploads/noimage.png';
                    } else {
                        $deficiency_img[$key]['def_image'] = base_url() . $this->config->item('upload_floor_deficiency_thumb') . $deficiency_img[$key]['image'];
                    }
                }
            }


            echo json_encode(array('status' => '200', 'message' => 'Record found', 'deficiency_information' => $project_deficiency, 'deficiency_images' => $deficiency_img));
            die();
        } else {
            echo json_encode(array('status' => '404', 'message' => 'Record not found'));
            die();
        }
    }

    /*
     * Get Project Comment
     */

    public function get_my_project_comment() {

        if ($this->input->method() != 'post') {
            echo json_encode(array('status' => '403', 'message' => 'Request Not Allowed.'));
            die();
        }

        $this->form_validation->set_rules('user_id', 'user_id', 'required');
        $this->form_validation->set_rules('project_id', 'project_id', 'required');
        $this->form_validation->set_rules('screen_id', 'screen_id', 'required');
        $this->form_validation->set_rules('deficiency_id', 'deficiency_id', 'required');



        if ($this->form_validation->run() === FALSE) {
            echo json_encode(array('status' => '402', 'message' => 'Invalid Data Format.'));
            die();
        }

        $this->user_auth($this->input->post('user_id'));

        $project_id = $this->input->post('project_id');
        $screen_id = $this->input->post('screen_id');
        $deficiency_id = $this->input->post('deficiency_id');

        $join[0] = array(
            'table' => 'users a',
            'join_table_id' => 'a.id',
            'from_table_id' => 'comment.from_id',
            'join_type' => 'LEFT',
        );

        $datacolum = 'comment.id,comment.comment,CONCAT(a.firstname," ",a.lastname) as fromname,
                           ,a.profile_image as sendprofile';
        $comments = $this->common->select_data_by_condition('comment', array('def_id' => $deficiency_id, 'project_id' => $project_id, 'floor_id' => $screen_id), $datacolum, '', '', 'comment.id', 'ASC', $join);

        if (!empty($comments)) {

            foreach ($comments as $key => $val) {
                if ($comments[$key]['sendprofile'] == '') {
                    $comments[$key]['sendprofile'] = base_url() . '../uploads/profile.png';
                } else {

                    $comments[$key]['sendprofile'] = base_url() . $this->config->item('user_profile_upload_thumb_path') . $comments[$key]['sendprofile'];
                }
            }

            echo json_encode(array('status' => '200', 'message' => 'Record found', 'comments' => $comments));
            die();
        } else {
            echo json_encode(array('status' => '404', 'message' => 'Comment Not found'));
            die();
        }
    }

    /*
     * Add Project Comment 
     */

    public function add_my_project_comment() {

        if ($this->input->method() != 'post') {
            echo json_encode(array('status' => '403', 'message' => 'Request Not Allowed.'));
            die();
        }

        $this->form_validation->set_rules('user_id', 'user_id', 'required');
        $this->form_validation->set_rules('to_user_id', 'to_user_id', 'required');
        $this->form_validation->set_rules('project_id', 'project_id', 'required');
        $this->form_validation->set_rules('screen_id', 'screen_id', 'required');
        $this->form_validation->set_rules('deficiency_id', 'deficiency_id', 'required');
        $this->form_validation->set_rules('comment', 'comment', 'required');




        if ($this->form_validation->run() === FALSE) {
            echo json_encode(array('status' => '402', 'message' => 'Invalid Data Format.'));
            die();
        }

        $this->user_auth($this->input->post('user_id'));


        $def_id = $this->input->post('deficiency_id');
        $user_id = $this->input->post('user_id');
        $to_user_id = $this->input->post('to_user_id');
        $project_id = $this->input->post('project_id');
        $project_Data = $this->common->select_data_by_id('project', 'id', $project_id);
        $project_name = $project_Data[0]['project_title'];

        if ($user_id != $to_user_id) {
            $insert_data = array(
                'comment' => trim($this->input->post('comment')),
                'from_id' => $this->input->post('user_id'),
                'to_id' => $this->input->post('to_user_id'),
                'def_id' => $this->input->post('deficiency_id'),
                'floor_id' => $this->input->post('screen_id'),
                'project_id' => $this->input->post('project_id'),
                'created_datetime' => date('Y-m-d H:i:s'),
            );

            if ($this->common->insert_data($insert_data, 'comment')) {
                $def_data = $this->common->select_data_by_id('project_floor_worker', 'id', $def_id);

                // sent notification
                if ($def_data[0]['tradeworker_id'] == $user_id) {
                    $role = "My_project";
                } else {
                    $role = "Included_project";
                }

                $notification_id = 4;
                $insert_notifcation = array(
                    'sender_id' => $user_id,
                    'receiver_id' => $this->input->post('to_user_id'),
                    'notification_id' => $notification_id,
                    'def_id' => $def_id,
                    'role' => $role,
                    'project_id' => $this->input->post('project_id'),
                    'created_datetime' => date('Y-m-d H:i:s'),
                );
                $this->common->insert_data($insert_notifcation, 'user_notification');

                $from_user_info = $this->common->select_data_by_condition('users', array('id' => $user_id), '*', '', '', '', '', array(), '');
                $to_user_info = $this->common->select_data_by_condition('users', array('id' => $this->input->post('to_user_id')), '*', '', '', '', '', array(), '');

                $messageData['from_name'] = $from_user_info[0]['firstname'] . ' ' . $from_user_info[0]['lastname'];

                $messageData['to_name'] = $to_user_info[0]['firstname'] . ' ' . $to_user_info[0]['lastname'];



// send push notification             
                $userid = $user_id;
                $user_token_data = $this->common->select_data_by_id('users_token', 'user_id', $this->input->post('to_user_id'), '*', array());

                foreach ($user_token_data as $user_token_data) {
                    $devicetype = $user_token_data['device_type'];
                    if ($devicetype == "Ios") {
                        $deviceToken = $user_token_data['ios_id'];
                        $msg = $messageData['from_name'] . " has made a comment on pin";
                        $notification = array(
                            'title' => "New Comment $project_name",
                            'body' => $msg,
                            'icon' => '',
                            'sound' => '',
                            'user_id' => $this->input->post('to_user_id'),
                            'project_id' => $this->input->post('project_id'),
                            'screen_id' => $this->input->post('screen_id'),
                            'deficiency_id' => $this->input->post('deficiency_id'),
                            'to_user_id' => $user_id,
                            'notification_type' => 'new comment',
                            'project_type' => $role,
                            'to_user_name' => $messageData['from_name'],
                        );
                        //echo $token." AND ".$msg; die();
                        $this->send_ios_notification($deviceToken, $notification);
                    } else {
                        $token = $user_token_data['fcm_id'];
                        $msg = $messageData['from_name'] . " has made a comment on pin";
                        $notification = array(
                            'title' => "New Comment $project_name",
                            'body' => $msg,
                            'icon' => '',
                            'sound' => '',
                            'user_id' => $this->input->post('to_user_id'),
                            'project_id' => $this->input->post('project_id'),
                            'screen_id' => $this->input->post('screen_id'),
                            'deficiency_id' => $this->input->post('deficiency_id'),
                            'to_user_id' => $user_id,
                            'notification_type' => 'new comment',
                            'project_type' => $role,
                            'to_user_name' => $messageData['from_name'],
                        );
                        //echo $token." AND ".$msg; die();
                        $this->sendPushToANDROID($token, $notification, $msg);
                    }
                }
            }
            //   die();
            // send push notification end

            echo json_encode(array('status' => '200', 'message' => 'Your comment added succesfully.'));
            die();
        } else {
            echo json_encode(array('status' => '403', 'message' => 'Something Went wrong try again letter.'));
            die();
        }
    }

    /*
     * For Delete Deficiency
     */

public function delete_deficiency() {

        if ($this->input->method() != 'post') {
            echo json_encode(array('status' => '403', 'message' => 'Request Not Allowed.'));
            die();
        }

        $this->form_validation->set_rules('user_id', 'user_id', 'required');
        $this->form_validation->set_rules('project_id', 'project_id', 'required');
        $this->form_validation->set_rules('screen_id', 'screen_id', 'required');
        $this->form_validation->set_rules('deficiency_id', 'deficiency_id', 'required');


        if ($this->form_validation->run() === FALSE) {
            echo json_encode(array('status' => '402', 'message' => 'Invalid Data Format.'));
            die();
        }

        $this->user_auth($this->input->post('user_id'));
        $user_id = $this->input->post('user_id');
        $project_id = $this->input->post('project_id');
        $defid = $this->input->post('deficiency_id');
        $floorid = $this->input->post('screen_id');

        $condition_array = array('project_id' => $project_id,
            'floor_id' => $floorid,
            'id' => $defid);
        $def_data = $this->common->select_data_by_id('project_floor_worker', 'id', $defid);
        if ($this->common->delete_data_by_condition('project_floor_worker', $condition_array)) {
            $delete_notification = $this->common->delete_data('user_notification', 'def_id', $defid);
            $delete_comment = $this->common->delete_data('comment', 'def_id', $defid);
            $def_images = $this->common->select_data_by_id('deficiency_image', 'floor_worker_id', $defid);

            if (!empty($def_images)) {
                foreach ($def_images as $val) {
                    if ($val['image']) {

                        if (file_exists($this->config->item('upload_floor_deficiency') . $val['image'])) {
                            @unlink($this->config->item('upload_floor_deficiency') . $val['image']);
                        }
                        if (file_exists($this->config->item('upload_floor_deficiency_thumb') . $val['image'])) {
                            @unlink($this->config->item('upload_floor_deficiency_thumb') . $val['image']);
                        }
                    }
                }
            }

            // send push notification             


            $user_token_data = $this->common->select_data_by_id('users_token', 'user_id', $user_id, '*', array());
	    
            if ($def_data[0]['tradeworker_id'] == $user_id) {
                $Role = "My_project";
            } else {
                $Role = "Reassign Project";
            }


            if ($def_data[0]['tradeworker_id'] != $user_id) {
		$devicetype = $user_token_data[0]['device_type'];
                foreach ($user_token_data as $user_token_data) {
                    if ($devicetype == "Ios") {
                        $deviceToken = $user_token_data['ios_id'];
                        $msg = 'The pin has been removed.';
                        $notification = array(
                            'title' => "Assign Task",
                            'body' => $msg,
                            'icon' => '',
                            'sound' => '',
                            'user_id' => $user_id,
                            'project_id' => $project_id,
                            'screen_id' => $floorid,
                            'deficiency_id' => $defid,
                            'to_user_id' => $def_data[0]['tradeworker_id'],
                            'notification_type' => 'Delete deficiency',
                            'project_type' => $Role,
                            'to_user_name' => '',
                        );
			 $this->send_ios_notification($deviceToken, $notification);
                       
                    } else {
                        $token = $user_token_data['fcm_id'];
                        $msg = 'The pin has been removed.';
                        $notification = array(
                            'title' => "Assign Task",
                            'body' => $msg,
                            'icon' => '',
                            'sound' => '',
                            'user_id' => $user_id,
                            'project_id' => $project_id,
                            'screen_id' => $floorid,
                            'deficiency_id' => $defid,
                            'to_user_id' => $def_data[0]['tradeworker_id'],
                            'notification_type' => 'Delete deficiency',
                            'project_type' => $Role,
                            'to_user_name' => '',
                        );
                        $this->sendPushToANDROID($token, $notification, $msg);
                    }
                    //print_r($notification);exit;
                    
                }
            }
            //send push notification end

            echo json_encode(array('status' => '200', 'message' => 'Deficiency deleted successfully.'));
            die();
        } else {
            echo json_encode(array('status' => '403', 'message' => 'Something Went wrong try again letter.'));
            die();
        }
    }
    /*
     * Status change for Reject and Complete/approve
     */

    public function deficiency_status_complete_reject() {
        if ($this->input->method() != 'post') {
            echo json_encode(array('status' => '403', 'message' => 'Request Not Allowed.'));
            die();
        }

        $this->form_validation->set_rules('user_id', 'user_id', 'required');
        $this->form_validation->set_rules('project_id', 'project_id', 'required');
        $this->form_validation->set_rules('screen_id', 'screen_id', 'required');
        $this->form_validation->set_rules('deficiency_id', 'defociency_id', 'required');
        $this->form_validation->set_rules('deficiency_status', 'defociency_status', 'required');

        if ($this->form_validation->run() == FALSE) {
            echo json_encode(array('status' => '402', 'message' => 'Invalid Data Format.'));
            die();
        }

        $this->user_auth($this->input->post('user_id'));

        $user_id = $this->input->post('user_id');
        $project_id = $this->input->post('project_id');
        $def_id = $this->input->post('deficiency_id');
        $floorid = $this->input->post('screen_id');
        $deficiency_status = $this->input->post('deficiency_status');
        $project_Data = $this->common->select_data_by_id('project', 'id', $project_id);
        $project_name = $project_Data[0]['project_title'];
        if ($deficiency_status == 'Completed') {
            $condition_array = array('project_id' => $project_id,
                'floor_id' => $floorid,
                'id' => $def_id);

            $update_data = array(
                'status' => 'completed',
            );

            if ($this->common->update_data_by_conditions($update_data, 'project_floor_worker', $condition_array)) {
                $def_data = $this->common->select_data_by_id('project_floor_worker', 'id', $def_id);
                $join_str[0] = array(
                    'table' => 'users a',
                    'join_table_id' => 'a.id',
                    'from_table_id' => 'project_floor_worker.tradeworker_id',
                    'join_type' => 'LEFT',
                );

                $join_str[2] = array(
                    'table' => 'project',
                    'join_table_id' => 'project.id',
                    'from_table_id' => 'project_floor_worker.project_id',
                    'join_type' => 'LEFT',
                );

                $join_str[1] = array(
                    'table' => 'users b',
                    'join_table_id' => 'b.id',
                    'from_table_id' => 'project.user_id',
                    'join_type' => 'LEFT',
                );

                $column = 'a.id as tid, b.id as mid, CONCAT(b.firstname," ",b.lastname) as manager,CONCAT(a.firstname," ",a.lastname) as tradeworker
                  ,a.email_id as trade_email,project.project_title, project_floor_worker.project_id';

                $condtn = array("project_floor_worker.id" => $def_id);
                $email_data = $this->common->select_data_by_condition('project_floor_worker', $condtn, $column, '', '', '', '', $join_str);
                if ($email_data[0]['mid'] != $def_data[0]['tradeworker_id']) {
                    // Send Notification
                    if ($email_data[0]['tid'] == $user_id) {
                        $role = "My_project";
                    } else {
                        $role = "Included_project";
                    }
                    $notification_id = 6;
                    $insert_notifcation = array(
                        'sender_id' => $email_data[0]['mid'],
                        'receiver_id' => $def_data[0]['tradeworker_id'],
                        'notification_id' => $notification_id,
                        'def_id' => $def_id,
                        'project_id' => $email_data[0]['project_id'],
                        'role' => $role,
                        'created_datetime' => date('Y-m-d H:i:s')
                    );
                    $this->common->insert_data($insert_notifcation, 'user_notification');
                }
                $from_user_info = $this->common->select_data_by_condition('users', array('id' => $email_data[0]['mid']), '*', '', '', '', '', array(), '');
                $to_user_info = $this->common->select_data_by_condition('users', array('id' => $def_data[0]['tradeworker_id']), '*', '', '', '', '', array(), '');

                $messageData['from_name'] = $from_user_info[0]['firstname'] . ' ' . $from_user_info[0]['lastname'];

                $messageData['to_name'] = $to_user_info[0]['firstname'] . ' ' . $to_user_info[0]['lastname'];

                // send push notification             
                // $userid = $user_id;
                $user_token_data = $this->common->select_data_by_id('users_token', 'user_id', $def_data[0]['tradeworker_id'], '*', array());

                if ($email_data[0]['mid'] != $def_data[0]['tradeworker_id']) {

                    foreach ($user_token_data as $user_token_data) {
                        if ($deviceToken == "Ios") {
                            $token = $user_token_data['ios_id'];
                            $msg = $messageData['from_name'] . ' approved a task.';
                            $notification = array(
                                'title' => "Approved Task $project_name",
                                'body' => $msg,
                                'icon' => '',
                                'sound' => '',
                                'user_id' => $email_data[0]['mid'],
                                'project_id' => $email_data[0]['project_id'],
                                'screen_id' => $floorid,
                                'deficiency_id' => $def_id,
                                'to_user_id' => $def_data[0]['tradeworker_id'],
                                'notification_type' => 'approved task',
                                'project_type' => $role,
                                'to_user_name' => $messageData['from_name'],
                            );
                            $this->send_ios_notification($deviceToken, $notification);
                        } else {
                            $token = $user_token_data['fcm_id'];
                            $msg = $messageData['from_name'] . ' approved a task.';
                            $notification = array(
                                'title' => "Approved Task $project_name",
                                'body' => $msg,
                                'icon' => '',
                                'sound' => '',
                                'user_id' => $email_data[0]['mid'],
                                'project_id' => $email_data[0]['project_id'],
                                'screen_id' => $floorid,
                                'deficiency_id' => $def_id,
                                'to_user_id' => $def_data[0]['tradeworker_id'],
                                'notification_type' => 'approved task',
                                'project_type' => $role,
                                'to_user_name' => $messageData['from_name'],
                            );
                            $this->sendPushToANDROID($token, $notification, $msg);
                        }
                    }
                }
                // send push notification end

                echo json_encode(array('status' => '200', 'message' => 'You have approved task successfully.'));
                die();
            } else {
                echo json_encode(array('status' => '403', 'message' => 'Request not found.'));
                die();
            }
        } else if ($deficiency_status == 'Reject') {

            $comment = $this->input->post('comment');

            $condition_array_reject = array('project_id' => $project_id,
                'floor_id' => $floorid,
                'id' => $def_id);

            $update_data_reject = array(
                'status' => 'pending',
            );

            $result = $this->common->update_data_by_conditions($update_data_reject, 'project_floor_worker', $condition_array_reject);

            $def_data = $this->common->select_data_by_id('project_floor_worker', 'id', $def_id);

            $insert_comment = array(
                'comment' => trim($comment),
                'from_id' => $user_id,
                'to_id' => $def_data[0]['tradeworker_id'],
                'def_id' => $def_id,
                'floor_id' => $floorid,
                'project_id' => $project_id,
                'created_datetime' => date('Y-m-d H:i:s'),
            );
            $insert_result = $this->common->insert_data($insert_comment, 'comment');

            if ($result && $insert_result) {
                $join_str[0] = array(
                    'table' => 'users a',
                    'join_table_id' => 'a.id',
                    'from_table_id' => 'project_floor_worker.tradeworker_id',
                    'join_type' => 'LEFT',
                );

                $join_str[2] = array(
                    'table' => 'project',
                    'join_table_id' => 'project.id',
                    'from_table_id' => 'project_floor_worker.project_id',
                    'join_type' => 'LEFT',
                );

                $join_str[1] = array(
                    'table' => 'users b',
                    'join_table_id' => 'b.id',
                    'from_table_id' => 'project.user_id',
                    'join_type' => 'LEFT',
                );

                $check_settting = $this->common->select_data_by_condition('email_notification_setting', array('user_id' => $email_data[0]['tid']), '*', '', '', '', '');
                if ($check_settting[0]['deficiency_assigned'] == 'On') {
                    $column = 'a.id as tid, CONCAT(b.firstname," ",b.lastname) as manager,CONCAT(a.firstname," ",a.lastname) as tradeworker
                        ,a.email_id as trade_email,project.project_title, project_floor_worker.project_id';
                    $condtn = array("project_floor_worker.id" => $def_id);
                    $email_data = $this->common->select_data_by_condition('project_floor_worker', $condtn, $column, '', '', '', '', $join_str);
                    $mailData = $this->common->select_data_by_id('mailformat', 'mail_id', '13', '*', array());
                    $subject = str_replace("%project_name%", $email_data[0]['project_title'], $mailData[0]['subject']);
                    $def_link = '<a href="' . base_url('../Project_def/view_def/') . $def_id . "/Included_project" . '" target="_blank" title="deficiency_view">' . base_url('../Project_def/view_def/') . $def_id . "/Included_project" . '</a>';
                    $mailformat = $mailData[0]['mailformat'];
                    $mail_body = str_replace("%comment%", $comment, $mailformat);
                    $mail_body = str_replace("%name%", $email_data[0]['tradeworker'], $mail_body);
                    $mail_body = str_replace("%manager%", $email_data[0]['manager'], $mail_body);
                    $mail_body = str_replace("%project_name%", $email_data[0]['project_title'], $mail_body);
                    $mail_body = str_replace("%def_link%", $def_link, $mail_body);
                    $mail_body = str_replace("%sitename%", $this->data['site_name'], $mail_body);
                    //  echo $mail_body; die();
                    $this->sendEmail($this->data['site_name'], $this->data['site_email'], $email_data[0]['trade_email'], $subject, $mail_body);
                }
                // Send Notification   
                if ($def_data[0]['tradeworker_id'] == $user_id) {
                    $role = "My_project";
                } else {
                    $role = "Included_project";
                }
                $notification_id = 3;
                $insert_notifcation = array(
                    'sender_id' => $user_id,
                    'receiver_id' => $def_data[0]['tradeworker_id'],
                    'notification_id' => $notification_id,
                    'def_id' => $def_id,
                    'project_id' => $project_id,
                    'role' => $role,
                    'created_datetime' => date('Y-m-d H:i:s')
                );
                $this->common->insert_data($insert_notifcation, 'user_notification');

                echo json_encode(array('status' => '200', 'message' => 'You have rejected task successfully.'));
                die();
            } else {
                echo json_encode(array('status' => '403', 'message' => 'Request not found.'));
                die();
            }
        } else {
            echo json_encode(array('status' => '403', 'message' => 'Request not found.'));
            die();
        }
    }

    /*
     * Completed Task Image Upload
     */

    public function upload_completed_def_image() {

        if ($this->input->method() != 'post') {
            echo json_encode(array('status' => '403', 'message' => 'Request Not Allowed.'));
            die();
        }

        $this->form_validation->set_rules('user_id', 'user_id', 'required');
        $this->form_validation->set_rules('deficiency_id', 'deficiency_id', 'required');


        if ($this->form_validation->run() == FALSE) {
            echo json_encode(array('status' => '402', 'message' => 'Invalid Data Format.'));
            die();
        }

        $this->user_auth($this->input->post('user_id'));

        $def_id = $this->input->post('deficiency_id');


        $insert_data = array(
            'floor_worker_id' => $def_id,
            'upload_by' => 'tradeworker',
            'created_datetime' => date('Y-m-d H:i:s'),
            'modified_datetime' => date('Y-m-d H:i:s'),
            'created_ip' => $this->input->ip_address(),
            'modified_ip' => $this->input->ip_address()
        );

        /* if (isset($_FILES['def_image']) && $_FILES['def_image']['name'] != '' && $_FILES['def_image']['size'] > 0) {
          $this->load->library('upload');
          $config['upload_path'] = $this->config->item('upload_floor_deficiency');

          $config['encrypt_name'] = TRUE;
          $config['allowed_types'] = 'jpg|jpeg|png';

          $this->upload->initialize($config);
          //print_r($_FILES['def_image']);exit;
          if ($this->upload->do_upload('def_image')) {//echo "hii";exit;
          $upload_data = $this->upload->data();
          $insert_data['image'] = $upload_data['file_name'];
          $config_path = $this->config->item('upload_floor_deficiency');
          $config['source_image'] = $config_path . $upload_data['file_name'];
          $config['new_image'] = $this->config->item('upload_floor_deficiency_thumb');
          $config['create_thumb'] = TRUE;
          //$config['maintain_ratio'] = TRUE;
          $config['thumb_marker'] = '';
          $config['width'] = $this->config->item('upload_floor_thumb_width');
          $config['height'] = $this->config->item('upload_floor_thumb_height');
          $config['allowed_types'] = 'jpg|jpeg|png';
          $this->load->library('image_lib');
          $this->image_lib->initialize($config);
          $this->image_lib->resize();

          if ($this->common->insert_data($insert_data, 'deficiency_image')) {
          echo json_encode(array('status' => '200', 'message' => 'Image is uploaded. please complete the task.'));
          die();
          } else {
          echo json_encode(array('status' => '403', 'message' => 'Something went wrong please try later.'));
          die();
          }
          } else {
          $ree = $this->upload->display_errors();
          echo json_encode(array('status' => '403', 'message' => 'Image not uploaded, Something went wrong please try later.','error'=>$ree));
          die();
          }
          } */ /* else {
          echo json_encode(array('status' => '403', 'message' => 'Select Image, Something went wrong please try later.'));
          die();
          } */
        $image = $this->input->post('def_image');

        if (isset($image) && !empty($image)) {
            $img = str_replace('data:image/png;base64,', '', $image);

            $img . "<br>";

            $data = base64_decode($img);

            $path = $this->config->item('upload_floor_deficiency_thumb');
            $path1 = $this->config->item('upload_floor_deficiency');
            $filename = rand(10, 99) . time() . '.png';
            $file = $path . $filename;
            $file1 = $path1 . $filename;
            $success = file_put_contents($file, $data);
            $config['create_thumb'] = TRUE;
            //$config['maintain_ratio'] = TRUE;
            $config['new_image'] = $this->config->item('upload_floor_deficiency_thumb');
            $config['width'] = $this->config->item('upload_floor_thumb_width');
            $config['height'] = $this->config->item('upload_floor_thumb_height');


            $this->load->library('image_lib');
            $this->image_lib->initialize($config);
            $this->image_lib->resize();
            //print_r($path);exit;

            $success1 = file_put_contents($file1, $data);
            $insert_data['image'] = $filename;
        }
        if ($this->common->insert_data($insert_data, 'deficiency_image')) {
            echo json_encode(array('status' => '200', 'message' => 'Image is uploaded. please complete the task.'));
            die();
        } else {
            echo json_encode(array('status' => '403', 'message' => 'Something went wrong please try later.'));
            die();
        }
    }

    /* else {

      echo json_encode(array('status' => '403', 'message' => 'Select Image, Something went wrong please try later.'));
      die();
      } */


    /*
     * For Assigned Task Mark as Complete 
     */

    public function assigned_mark_as_complete() {

        if ($this->input->method() != 'post') {
            echo json_encode(array('status' => '403', 'message' => 'Request Not Allowed.'));
            die();
        }

        $this->form_validation->set_rules('user_id', 'user_id', 'required');
        $this->form_validation->set_rules('project_id', 'project_id', 'required');
        $this->form_validation->set_rules('screen_id', 'screen_id', 'required');
        $this->form_validation->set_rules('deficiency_id', 'defociency_id', 'required');


        if ($this->form_validation->run() == FALSE) {
            echo json_encode(array('status' => '402', 'message' => 'Invalid Data Format.'));
            die();
        }

        $this->user_auth($this->input->post('user_id'));

        $user_id = $this->input->post('user_id');
        $project_id = $this->input->post('project_id');
        $def_id = $this->input->post('deficiency_id');
        $floorid = $this->input->post('screen_id');
        $project_Data = $this->common->select_data_by_id('project', 'id', $project_id);
        $project_name = $project_Data[0]['project_title'];

        if ($def_id != '') {

            $condition_array = array('project_id' => $project_id,
                'floor_id' => $floorid,
                'id' => $def_id);

            $update_data = array(
                'status' => 'approve',
                'modified_datetime' => date('Y-m-d H:i:s'),
            );
            if ($this->common->update_data_by_conditions($update_data, 'project_floor_worker', $condition_array)) {

                //Send email notification to tradeworker                    
                $join_str[0] = array(
                    'table' => 'users a',
                    'join_table_id' => 'a.id',
                    'from_table_id' => 'project_floor_worker.tradeworker_id',
                    'join_type' => 'LEFT',
                );
                $join_str[2] = array(
                    'table' => 'project',
                    'join_table_id' => 'project.id',
                    'from_table_id' => 'project_floor_worker.project_id',
                    'join_type' => 'LEFT',
                );
                $join_str[1] = array(
                    'table' => 'users b',
                    'join_table_id' => 'b.id',
                    'from_table_id' => 'project.user_id',
                    'join_type' => 'LEFT',
                );
                $column = 'project.user_id as manager_id, project_floor_worker.tradeworker_id as tradeworker_id, CONCAT(b.firstname," ",b.lastname) as manager,b.email_id as manager_email,CONCAT(a.firstname," ",a.lastname) as tradeworker
                            ,a.email_id as trade_email,project.project_title,project_floor_worker.deficiency_title, project_floor_worker.project_id';
                $condtn = array("project_floor_worker.id" => $def_id);
                $email_data = $this->common->select_data_by_condition('project_floor_worker', $condtn, $column, '', '', '', '', $join_str);
                $def_link = '<a href="' . base_url('../Project_def/view_def/') . $def_id . "/My_project" . '" target="_blank" title="deficiency_view">' . base_url('../Project_def/view_def/') . $def_id . "/My_project" . '</a>';
                $mailData = $this->common->select_data_by_id('mailformat', 'mail_id', '12', '*', array());
                $subject = str_replace("%project%", ucfirst($email_data[0]['project_title']), $mailData[0]['subject']);
                $mailformat = $mailData[0]['mailformat'];
                $mail_body = str_replace("%project%", ucfirst($email_data[0]['project_title']), $mailformat);
                $mail_body = str_replace("%name%", ucfirst($email_data[0]['manager']), $mail_body);
                $mail_body = str_replace("%tradeworker%", ucfirst($email_data[0]['tradeworker']), $mail_body);
                $mail_body = str_replace("%def_link%", $def_link, $mail_body);
                $mail_body = str_replace("%location%", ucfirst($email_data[0]['deficiency_title']), $mail_body);
                $mail_body = str_replace("%sitename%", $this->data['site_name'], $mail_body);
                //email sent
                $this->sendEmail($this->data['site_name'], $this->data['site_email'], $email_data[0]['manager_email'], $subject, $mail_body);

                // Send Notification
                if ($email_data[0]['tradeworker_id'] == $user_id) {
                    $role = "My_project";
                } else {
                    $role = "Included_project";
                }
                $sender_id = $email_data[0]['tradeworker_id'];
                $manager_id = $email_data[0]['manager_id'];
                $notification_id = 2;
                $insert_notifcation = array(
                    'sender_id' => $sender_id,
                    'receiver_id' => $manager_id,
                    'notification_id' => $notification_id,
                    'def_id' => $def_id,
                    'project_id' => $email_data[0]['project_id'],
                    'role' => $role,
                    'created_datetime' => date('Y-m-d H:i:s')
                );

                $from_user_info = $this->common->select_data_by_condition('users', array('id' => $sender_id), '*', '', '', '', '', array(), '');
                $to_user_info = $this->common->select_data_by_condition('users', array('id' => $manager_id), '*', '', '', '', '', array(), '');

                $messageData['from_name'] = $from_user_info[0]['firstname'] . ' ' . $from_user_info[0]['lastname'];

                $messageData['to_name'] = $to_user_info[0]['firstname'] . ' ' . $to_user_info[0]['lastname'];

                // send push notification             
                // $userid = $user_id;
                $user_token_data = $this->common->select_data_by_id('users_token', 'user_id', $manager_id, '*', array());
                if ($sender_id != $manager_id) {

                    foreach ($user_token_data as $user_token_data) {
                        if ($devicetype == "Ios") {
                            $deviceToken = $user_token_data['ios_id'];
                            $msg = $messageData['from_name'] . " has completed a task.";
                            $notification = array(
                                'title' => "Completed for review $project_name",
                                'body' => $msg,
                                'icon' => '',
                                'sound' => '',
                                'user_id' => $sender_id,
                                'project_id' => $project_id,
                                'screen_id' => $floorid,
                                'deficiency_id' => $def_id,
                                'to_user_id' => $manager_id,
                                'notification_type' => 'Completed for review',
                                'project_type' => $role,
                                'to_user_name' => $messageData['from_name'],
                            );
                            $this->send_ios_notification($deviceToken, $notification);
                        } else {
                            $token = $user_token_data['fcm_id'];
                            $msg = $messageData['from_name'] . " has completed a task.";
                            $notification = array(
                                'title' => "Completed for review $project_name",
                                'body' => $msg,
                                'icon' => '',
                                'sound' => '',
                                'user_id' => $sender_id,
                                'project_id' => $project_id,
                                'screen_id' => $floorid,
                                'deficiency_id' => $def_id,
                                'to_user_id' => $manager_id,
                                'notification_type' => 'Completed for review',
                                'project_type' => $role,
                                'to_user_name' => $messageData['from_name'],
                            );

                            $this->sendPushToANDROID($token, $notification, $msg);
                        }
                    }
                }
                // send push notification end


                $this->common->insert_data($insert_notifcation, 'user_notification');
                echo json_encode(array('status' => '200', 'message' => 'you have completed task.'));
                die();
            } else {
                echo json_encode(array('status' => '403', 'message' => 'Something went wrong.. please try later.'));
                die();
            }
        } else {
            echo json_encode(array('status' => '403', 'message' => 'Request Not allowed.'));
            die();
        }
    }

    /*
     * For List Chat Messages
     */

    public function get_chat_message() {
        if ($this->input->method() != 'post') {
            echo json_encode(array('status' => '403', 'message' => 'Request Not Allowed.'));
            die();
        }

        $this->form_validation->set_rules('user_id', 'user_id', 'required');
        $this->form_validation->set_rules('project_id', 'project_id', 'required');
        $this->form_validation->set_rules('to_user_id', 'to_user_id', 'required');




        if ($this->form_validation->run() === FALSE) {
            echo json_encode(array('status' => '402', 'message' => 'Invalid Data Format.'));
            die();
        }

        $this->user_auth($this->input->post('user_id'));


        $project_id = $this->input->post('project_id');
        $user_id = $this->input->post('user_id');
        $to_user_id = $this->input->post('to_user_id');


        $message_array_result = $this->db->select("message.id,message.from_user_id,
                                                message.to_user_id,message.project_id,message.text,
                                                message.created_datetime,a.firstname as sender_firstname, a.lastname as sender_lastname,
                                                b.firstname as reciever_firstname, b.lastname as reciever_lastname, a.profile_image as sender_image,
                                                b.profile_image as reciever_image,message.image")
                ->from($this->db->dbprefix . 'message')
                ->where($this->db->dbprefix . 'message.from_user_id', $user_id)
                ->where($this->db->dbprefix . 'message.to_user_id', $to_user_id)
                ->where($this->db->dbprefix . 'message.project_id', $project_id)
                ->or_where($this->db->dbprefix . 'message.from_user_id', $to_user_id)
                ->where($this->db->dbprefix . 'message.to_user_id', $user_id)
                ->where($this->db->dbprefix . 'message.project_id', $project_id)
                ->join($this->db->dbprefix . 'users a', 'a.id = from_user_id')
                ->join($this->db->dbprefix . 'users b', 'b.id = to_user_id')
                ->order_by('message.id', 'ASC')
                ->get()
                ->result_array();

        for ($m = 0; $m < count($message_array_result); $m++) {

            if ($message_array_result[$m]['image'] != "" && $message_array_result[$m]['text'] != "") {
                $message_array_result[$m]['image'] = base_url() . $this->config->item('upload_my_path') . $message_array_result[$m]['image'];
                $message_array_result[$m]['content_type'] = "all";
            } else if ($message_array_result[$m]['text'] != "") {
                $message_array_result[$m]['content_type'] = "text";
            } else if ($message_array_result[$m]['image'] != "") {
                $message_array_result[$m]['image'] = base_url() . $this->config->item('upload_my_path') . $message_array_result[$m]['image'];
                $message_array_result[$m]['content_type'] = "image";
            }
        }
        if (!empty($message_array_result)) {

            echo json_encode(array('status' => '200', 'message' => 'record found.', "message_result" => $message_array_result));
            die();
        } else {
            echo json_encode(array('status' => '404', 'message' => 'record not found.'));
            die();
        }
    }

    /*
     * For Read Comment
     */

    public function read_comment() {

        if ($this->input->method() != 'post') {
            echo json_encode(array('status' => '403', 'message' => 'Request Not Allowed.'));
            die();
        }

        $this->form_validation->set_rules('user_id', 'user_id', 'required');
        $this->form_validation->set_rules('project_id', 'project_id', 'required');
        $this->form_validation->set_rules('screen_id', 'screen_id', 'required');
        $this->form_validation->set_rules('deficiency_id', 'deficiency_id', 'required');




        if ($this->form_validation->run() === FALSE) {
            echo json_encode(array('status' => '402', 'message' => 'Invalid Data Format.'));
            die();
        }

        $this->user_auth($this->input->post('user_id'));

        $project_id = $this->input->post('project_id');
        $user_id = $this->input->post('user_id');
        $floor_id = $this->input->post('screen_id');
        $def_id = $this->input->post('deficiency_id');

        $update_array_con = array(
            'project_id' => $project_id,
            'floor_id' => $floor_id,
            'def_id' => $def_id,
            'to_id' => $user_id,
        );

        if ($this->common->update_data_by_conditions(array('read_by_to_id' => 1), 'comment', $update_array_con)) {
            echo json_encode(array('status' => '200', 'message' => 'Comment Read succesfully.'));
            die();
        } else {
            echo json_encode(array('status' => '403', 'message' => 'something went wrong! please try again latter.'));
            die();
        }
    }

    /*
     * For Send Message one to one
     */

    public function send_message() {

        if ($this->input->method() != 'post') {
            echo json_encode(array('status' => '403', 'message' => 'Request Not Allowed.'));
            die();
        }
        $this->form_validation->set_rules('user_id', 'User Id', 'required');
        $this->form_validation->set_rules('project_id', 'Project Id', 'required');
        $this->form_validation->set_rules('to_user_id', 'To id', 'required');
        $this->form_validation->set_rules('msg', 'Message', 'required');
        //$this->form_validation->set_rules('project_type', 'Project Type', 'required');



        if ($this->form_validation->run() === FALSE) {
            echo json_encode(array('status' => '402', 'message' => 'Invalid Data Format.'));
            die();
        }

        $this->user_auth($this->input->post('user_id'));

        $user_id = $this->input->post('user_id');
        $project_id = $this->input->post('project_id');
        $to_id = $this->input->post('to_user_id');
        $msg = $this->input->post('msg');
        $seg = $this->input->post('project_type');

        $proj_data = $this->common->select_data_by_id('project', 'id', $project_id, 'project_title');
        $project_name = $proj_data[0]['project_title'];
        $to_user = $this->common->select_data_by_id('users', 'id', $to_id, '*');
        $user_info = $this->common->select_data_by_id('users', 'id', $user_id, '*');

        if ($seg == "My_project") {

            $from = "project manager " . $user_info[0]['firstname'] . ' ' . $user_info[0]['lastname'];
        } else {

            $from = "project tradeworker " . $user_info[0]['firstname'] . ' ' . $user_info[0]['lastname'];
        }
        $insert_data = array(
            "from_user_id" => $user_id,
            "to_user_id" => $to_id,
            "project_id" => $project_id,
            "text" => $msg,
            "read_by_to_id" => 0,
            "created_datetime" => date('Y-m-d H:i:s'),
        );
        $image = $this->input->post('image');
        if (isset($image) && !empty($image)) {
            $img = str_replace('data:image/png;base64,', '', $image);

            $img . "<br>";

            $data = base64_decode($img);

            $path = $this->config->item('upload_my_path');
            $filename = uniqid() . "_" . $this->input->post('user_id') . '.png';
            $file = $path . $filename;
            $success = file_put_contents($file, $data);
            $insert_data['image'] = $filename;
        }

        $res = $this->common->insert_data($insert_data, 'message');
        $from_user_info = $this->common->select_data_by_condition('users', array('id' => $user_id), '*', '', '', '', '', array(), '');
        $to_user_info = $this->common->select_data_by_condition('users', array('id' => $to_id), '*', '', '', '', '', array(), '');

        $messageData['from_name'] = $from_user_info[0]['firstname'] . ' ' . $from_user_info[0]['lastname'];

        $messageData['to_name'] = $to_user_info[0]['firstname'] . ' ' . $to_user_info[0]['lastname'];
        $check_settting = $this->common->select_data_by_condition('email_notification_setting', array('user_id' => $to_user[0]['id']), '*', '', '', '', '');

        if ($check_settting[0]['message_sent'] == 'On') {

            if ($res) {
                $mailData = $this->common->select_data_by_id('mailformat', 'mail_id', '9', '*', array());
                $subject = str_replace("%from%", $from, $mailData[0]['subject']);
                $mailformat = $mailData[0]['mailformat'];
                $mail_body = str_replace("%from%", $from, $mailformat);
                $mail_body = str_replace("%link%", ' ', $mail_body);
                $mail_body = str_replace("%project_name%", $proj_data[0]['project_title'], $mail_body);
                $mail_body = str_replace("%name%", $to_user[0]['firstname'], $mail_body);
                $mail_body = str_replace("%msg%", $msg, $mail_body);
                $mail_body = str_replace("%sitename%", $this->data['site_name'], $mail_body);
                $this->sendEmail($this->data['site_name'], $this->data['site_email'], $to_user[0]['email_id'], $subject, $mail_body);
            }
        }
        // send push notification             
        $userid = $user_id;
        $user_token_data = $this->common->select_data_by_id('users_token', 'user_id', $to_id, '*', array());
        //print_r($user_token_data); die();
        $role = $seg;
        if ($user_id != $to_id) {
            foreach ($user_token_data as $user_token_data) {
                $devicetype = $user_token_data['device_type'];
                if ($devicetype == "Ios") { //echo "hii if";exit;
                   $deviceToken=  $user_token_data['ios_id'];
//		s	print_r($deviceToken);exit;
                   
                    $notification = array(
                        'title' => $messageData['from_name'] . " sent you message in $project_name",
                        'body' => $msg,
                        'icon' => '',
                        'sound' => '',
                        'user_id' => $to_id,
                        'project_id' => $project_id,
                        'screen_id' => '',
                        'deficiency_id' => '',
                        'to_user_id' => $user_id,
                        'notification_type' => 'new message',
                        'project_type' => $role,
                        'to_user_name' => $messageData['from_name'],
                    );

		    $message = $messageData['from_name'] . " sent you message in $project_name";
                    $res = $this->send_ios_notification($deviceToken, $notification,$message);
                } else {//echo "hii else";exit;
                    $token = $user_token_data['fcm_id'];

                    $notification = array(
                        'title' => $messageData['from_name'] . " sent you message in $project_name",
                        'body' => $msg,
                        'icon' => '',
                        'sound' => '',
                        'user_id' => $to_id,
                        'project_id' => $project_id,
                        'screen_id' => '',
                        'deficiency_id' => '',
                        'to_user_id' => $user_id,
                        'notification_type' => 'new message',
                        'project_type' => $role,
                        'to_user_name' => $messageData['from_name'],
                    );
                    // print_r($notification);
                    //echo $token." AND ".$msg; die();
                    $this->sendPushToANDROID($token, $notification, $msg);
                }
            }

            //   die();
            // send push notification end

            echo json_encode(array('status' => '200', 'message' => 'Message send successfully.'));
            die();
        } else {
            echo json_encode(array('status' => '403', 'message' => 'Message not sent. Please try later.'));
            die();
        }
    }

    /*
     * For Read Message
     */

    public function read_message() {

        if ($this->input->method() != 'post') {
            echo json_encode(array('status' => '403', 'message' => 'Request Not Allowed.'));
            die();
        }

        $this->form_validation->set_rules('user_id', 'user_id', 'required');
        $this->form_validation->set_rules('project_id', 'project_id', 'required');

        if ($this->form_validation->run() === FALSE) {
            echo json_encode(array('status' => '402', 'message' => 'Invalid Data Format.'));
            die();
        }

        $this->user_auth($this->input->post('user_id'));

        $project_id = $this->input->post('project_id');
        $user_id = $this->input->post('user_id');


        $update_array_msg = array(
            'project_id' => $project_id,
            'to_user_id' => $user_id,
        );

        if ($this->common->update_data_by_conditions(array('read_by_to_id' => 1), 'message', $update_array_msg)) {
            echo json_encode(array('status' => '200', 'message' => 'Message Read succesfully.'));
            die();
        } else {
            echo json_encode(array('status' => '403', 'message' => 'something went wrong! please try again latter.'));
            die();
        }
    }

    /*
     * For Change Tradeworker Color Code and Tradeworker Name
     */

    public function project_profile_changes() {

        if ($this->input->method() != 'post') {
            echo json_encode(array('status' => '403', 'message' => 'Request Not Allowed.'));
            die();
        }

        $this->form_validation->set_rules('user_id', 'user_id', 'required');
        $this->form_validation->set_rules('project_id', 'project_id', 'required');
        $this->form_validation->set_rules('tradeworker_id', 'tradeworker_id', 'required');
        $this->form_validation->set_rules('tradeworker_name', 'tradeworker name', 'required');
        $this->form_validation->set_rules('tradeworker_color', 'tradeworker colore', 'required');

        if ($this->form_validation->run() == FALSE) {
            echo json_encode(array('status' => '402', 'message' => 'Invalid Data Format.'));
        }

        $this->user_auth($this->input->post('user_id'));

        $proid = $this->input->post('project_id');
        $tradeid = $this->input->post('tradeworker_id');
        $name = $this->input->post('tradeworker_name');
        $color_code = $this->input->post('tradeworker_color');

        $insert_data = array(
            'project_id' => $proid,
            'tradeworker_id' => $tradeid,
            'name' => $name,
            'color_code' => $color_code,
            'created_datetime' => date('Y-m-d h:i:s'),
        );


        $con = array('project_id' => $proid, 'tradeworker_id' => $tradeid);
        $res = $this->common->select_data_by_condition('project_profile', $con, '*');
        if (!empty($res)) {
            $this->common->delete_data_by_condition('project_profile', $con);
            $this->common->insert_data($insert_data, 'project_profile');
            echo json_encode(array('status' => '200', 'message' => 'Profile Updated Succesfully.'));
            die();
        } else {
            $this->common->insert_data($insert_data, 'project_profile');
            echo json_encode(array('status' => '200', 'message' => 'Profile Updated Succesfully.'));
            die();
        }
    }

    public function get_notifications() {
        if ($this->input->method() != 'post') {
            echo json_encode(array('status' => '403', 'message' => 'Request Not Allowed.'));
            die();
        }

        $this->form_validation->set_rules('user_id', 'user_id', 'required');
        $this->form_validation->set_rules('project_id', 'project_id', 'required');

        if ($this->form_validation->run() == FALSE) {
            echo json_encode(array('status' => '402', 'message' => 'Invalid Data Format.'));
        }

        $this->user_auth($this->input->post('user_id'));

        $user_id = $this->input->post('user_id');
        $projectid = $this->input->post('project_id');
        $join_str[0] = array(
            'table' => 'users',
            'join_table_id' => 'users.id',
            'from_table_id' => 'user_notification.sender_id',
            'join_type' => 'LEFT',
        );
        $join_str[2] = array(
            'table' => 'project_floor_worker',
            'join_table_id' => 'project_floor_worker.id',
            'from_table_id' => 'user_notification.def_id',
            'join_type' => 'LEFT',
        );
        $join_str[3] = array(
            'table' => 'project_floor',
            'join_table_id' => 'project_floor.id',
            'from_table_id' => 'project_floor_worker.floor_id',
            'join_type' => 'LEFT',
        );
        $join_str[4] = array(
            'table' => 'notification',
            'join_table_id' => 'notification.id',
            'from_table_id' => 'user_notification.notification_id',
            'join_type' => 'LEFT',
        );

        $column = 'user_notification.project_id, notification.id as notification_id, users.id as sender_id, project_floor_worker.deficiency_title  as location, user_notification.id as user_notification_id, CONCAT(buildster_users.firstname," ",buildster_users.lastname) as sender_name, users.profile_image as sender_image, project_floor.floor_title,project_floor.screen_image,notification.title,notification.description, user_notification.status, user_notification.role, user_notification.def_id, user_notification.created_datetime, project_floor_worker.floor_id';
        $condtn = array("user_notification.receiver_id" => $user_id, "user_notification.project_id" => $projectid);
        $notification = $this->common->select_data_by_condition('user_notification', $condtn, $column, 'user_notification.id', 'DESC', '', '', $join_str);
//        print_r($notification);exit;
        foreach ($notification as $key => $val) {
            $notification[$key]['description'] = str_replace("%name%", $notification[$key]['sender_name'], $notification[$key]['description']);

            $timestamp = strtotime($notification[$key]['created_datetime']);
            $notification[$key]['created_datetime'] = date("d-m-Y", $timestamp);

            $notification[$key]['screen_image'] = base_url() . $this->config->item('upload_floor_path') . $notification[$key]['screen_image'];

            if ($notification[$key]['location'] == '') {

                $notification[$key]['location'] = "";
            }
            if ($notification[$key]['floor_id'] == '') {

                $notification[$key]['floor_id'] = "";
            }
            if ($notification[$key]['floor_title'] == '') {

                $notification[$key]['floor_title'] = "";
            }
            if ($notification[$key]['sender_image'] == '') {

                $notification[$key]['sender_image_thumb'] = base_url() . '../uploads/profile.png';
            } else {

                $notification[$key]['sender_image_thumb'] = base_url() . $this->config->item('user_profile_upload_thumb_path') . $notification[$key]['sender_image'];
            }
        }

        if (!empty($notification)) {
            echo json_encode(array('status' => '200', 'message' => 'Record Found.', 'notification_data' => $notification));
            die();
        } else {

            echo json_encode(array('status' => '404', 'message' => 'Record not Found.'));
            die();
        }
    }

    public function get_message_notification_count() {
        if ($this->input->method() != 'post') {
            echo json_encode(array('status' => '403', 'message' => 'Request Not Allowed.'));
            die();
        }

        $this->form_validation->set_rules('user_id', 'user_id', 'required');
        $this->form_validation->set_rules('project_id', 'project_id', 'required');

        if ($this->form_validation->run() == FALSE) {
            echo json_encode(array('status' => '402', 'message' => 'Invalid Data Format.'));
        }

        $this->user_auth($this->input->post('user_id'));

        $user_id = $this->input->post('user_id');
        $projectid = $this->input->post('project_id');

        $con = array('message.to_user_id' => $user_id, 'message.project_id' => $projectid, 'message.read_by_to_id' => 0);
        $unread_message_count = count($this->common->select_data_by_condition('message', $con, '*'));

        $unread_notification_count = count($this->common->select_data_by_condition('user_notification', array("user_notification.status" => "Unread", "user_notification.receiver_id" => $user_id, 'user_notification.project_id' => $projectid), '*', 'user_notification.id', 'DESC', '', '', ''));


        echo json_encode(array('status' => '200', 'message' => 'Record Found.', 'unread_message_count' => $unread_message_count, 'unread_notification_count' => $unread_notification_count));
        die();
    }

    public function read_notification() {

        if ($this->input->method() != 'post') {
            echo json_encode(array('status' => '403', 'message' => 'Request Not Allowed.'));
            die();
        }

        $this->form_validation->set_rules('user_id', 'user_id', 'required');
        $this->form_validation->set_rules('project_id', 'project_id', 'required');
        $this->form_validation->set_rules('def_id', 'def_id', 'required');

        if ($this->form_validation->run() == FALSE) {
            echo json_encode(array('status' => '402', 'message' => 'Invalid Data Format.'));
        }

        $this->user_auth($this->input->post('user_id'));

        $user_id = $this->input->post('user_id');
        $projectid = $this->input->post('project_id');
        $def_id = $this->input->post('def_id');

        $update_data = array(
            'status' => 'Read'
        );
        $condition = array(
            'def_id' => $def_id,
            'receiver_id' => $user_id,
            'project_id' => $projectid
        );
        if ($this->common->update_data_by_conditions($update_data, 'user_notification', $condition)) {
            echo json_encode(array('status' => '200', 'message' => 'Notification read Succesfully.'));
            die();
        } else {
            echo json_encode(array('status' => '403', 'message' => 'Something went wrong try again later.'));
            die();
        }
    }

    public function get_manager_assigned_action_item() {
        if ($this->input->method() != 'post') {
            echo json_encode(array('status' => '403', 'message' => 'Request Not Allowed.'));
            die();
        }

        $this->form_validation->set_rules('user_id', 'user_id', 'required');
        $this->form_validation->set_rules('project_id', 'project_id', 'required');
        $this->form_validation->set_rules('tradeworker_id', 'tradeworker_id', 'required');



        if ($this->form_validation->run() === FALSE) {
            echo json_encode(array('status' => '402', 'message' => 'Invalid Data Format.'));
            die();
        }

        $this->user_auth($this->input->post('user_id'));

        $project_id = $this->input->post('project_id');

        $user_id = $this->input->post('user_id');
        $tradeworker_id = $this->input->post('tradeworker_id');
        //print_r($tradeworker_id);exit;
        $condi = array('project.id' => $project_id);
        $project = $this->common->select_data_by_condition('project', $condi, '*', '', '', '', '', array());

        $this->data['manager'] = $manager = $this->common->select_data_by_id('users', 'id', $project[0]['user_id']);

        // $con = array('project_id' => $projectid, 'tradeworker_id' => $user_id);
        // $manager_pin = $this->common->select_data_by_condition("project_profile", $con, 'project_profile.color_code,project_profile.name');
        // $this->data['manager_pin'] = $manager_pin;
        //listview for tradeworker to manager pins


        $join_str[0] = array(
            'table' => 'users',
            'join_table_id' => 'users.id',
            'from_table_id' => 'project_floor_worker.tradeworker_id',
            'join_type' => 'LEFT',
        );

        $join_str[1] = array(
            'table' => 'project_floor',
            'join_table_id' => 'project_floor.id',
            'from_table_id' => 'project_floor_worker.floor_id',
            'join_type' => 'LEFT',
        );


        $condtn = array("project_floor_worker.project_id" => $project_id, 'project_floor_worker.tradeworker_id' => $tradeworker_id, 'project_floor_worker.created_by' => 'tw_to_pm', 'when_drop_tw_to_pm' => $user_id);

        $this->data['project_deficiency_list'] = $project_deficiency_list = $this->common->select_data_by_allcondition('project_floor_worker', $condtn, 'project_floor_worker.*,project_floor.floor_title,project_floor.screen_image,users.firstname,users.lastname,users.profile_image,users.color_code', 'project_floor_worker.modified_datetime', 'DESC', '', '', $join_str, 'project_floor_worker.id');


        if (!empty($project_deficiency_list)) {
            foreach ($project_deficiency_list as $k => $pdl) {
                if ($pdl['status'] == 'canceled') {
                    unset($project_deficiency_list[$k]);
                    continue;
                }

                $con = array('project_id' => $pdl['project_id'], 'tradeworker_id' => $pdl['tradeworker_id']);
                $res = $this->common->select_data_by_condition("project_profile", $con, 'project_profile.color_code,project_profile.name');
                if (!empty($res)) {
                    $project_deficiency_list[$k]['color_code'] = $res[0]['color_code'];
                    $project_deficiency_list[$k]['profile_name'] = $res[0]['name'];
                } else {

                    $con = array('id' => $pdl['tradeworker_id']);
                    $res = $this->common->select_data_by_condition("users", $con, '*');
                    $project_deficiency_list[$k]['profile_name'] = $res[0]['firstname'] . " " . $res[0]['lastname'];
                }
                $img = $this->common->select_data_by_condition('deficiency_image', array('floor_worker_id' => $pdl['id']), '*', 'deficiency_image.id', 'ASC');

                if (!empty($img)) {
                    $project_deficiency_list[$k]['def_image'] = base_url() . $this->config->item('upload_floor_deficiency_thumb') . $img[0]['image'];
                    ;
                } else {
                    $project_deficiency_list[$k]['def_image'] = base_url() . '../uploads/noimage.png';
                }
                if ($project_deficiency_list[$k]['profile_image'] == '') {

                    $project_deficiency_list[$k]['profile_image'] = base_url() . '../uploads/profile.png';
                } else {


                    $project_deficiency_list[$k]['profile_image'] = base_url() . $this->config->item('user_profile_upload_thumb_path') . $project_deficiency_list[$k]['profile_image'];
                }
                $con = array('comment.to_id' => $user_id, 'comment.def_id' => $pdl['id'], 'comment.read_by_to_id' => 0);
                $unread_comment = $this->common->select_data_by_allcondition('comment', $con, '*', '', '', '', '', array(), 'comment.def_id');
                if (!empty($unread_comment)) {
                    $project_deficiency_list[$k]['unread_comment'] = 1;
                    $a[] = $project_deficiency_list[$k];
                    unset($project_deficiency_list[$k]);
                    continue;
                } else {
                    $project_deficiency_list[$k]['unread_comment'] = 0;
                }

                if ($pdl['status'] == "completed" || $pdl['status'] == "reassigned") {
                    $history[$k] = $project_deficiency_list[$k];
                    unset($project_deficiency_list[$k]);
                    continue;
                }
            }





            //print_r($project_deficiency_list);exit;
            echo json_encode(array('status' => '200', 'message' => 'Record found', 'get_action_assigned_item_list' => $project_deficiency_list));
            die();
        } else {
            echo json_encode(array('status' => '404', 'message' => 'Record not found'));
            die();
        }
    }

    public function get_tradeworker_assigned_action_item() {
        if ($this->input->method() != 'post') {
            echo json_encode(array('status' => '403', 'message' => 'Request Not Allowed.'));
            die();
        }

        $this->form_validation->set_rules('user_id', 'user_id', 'required');
        $this->form_validation->set_rules('project_id', 'project_id', 'required');
        $this->form_validation->set_rules('tradeworker_id', 'tradeworker_id', 'required');



        if ($this->form_validation->run() === FALSE) {
            echo json_encode(array('status' => '402', 'message' => 'Invalid Data Format.'));
            die();
        }

        $this->user_auth($this->input->post('user_id'));

        $project_id = $this->input->post('project_id');
        $user_id = $this->input->post('user_id');
        $tradeworker_id = $this->input->post('tradeworker_id');
        $condi = array('project.id' => $project_id);
        $project = $this->common->select_data_by_condition('project', $condi, '*', '', '', '', '', array());

        $this->data['manager'] = $manager = $this->common->select_data_by_id('users', 'id', $project[0]['user_id']);

        // $con = array('project_id' => $projectid, 'tradeworker_id' => $user_id);
        // $manager_pin = $this->common->select_data_by_condition("project_profile", $con, 'project_profile.color_code,project_profile.name');
        // $this->data['manager_pin'] = $manager_pin;
        //listview for tradeworker to manager pins



        $join_str[0] = array(
            'table' => 'users',
            'join_table_id' => 'users.id',
            'from_table_id' => 'project_floor_worker.tradeworker_id',
            'join_type' => 'LEFT',
        );
        $join_str[1] = array(
            'table' => 'project_floor',
            'join_table_id' => 'project_floor.id',
            'from_table_id' => 'project_floor_worker.floor_id',
            'join_type' => 'LEFT',
        );


        $condtn = array("project_floor_worker.project_id" => $project_id, "project_floor_worker.tradeworker_id" => $tradeworker_id, 'project_floor_worker.status !=' => "completed");
        $project_assigned_deficiency_list = $this->common->select_data_by_allcondition('project_floor_worker', $condtn, 'users.id as user_id, project_floor.floor_title,project_floor.screen_image,project_floor_worker.*,users.firstname,users.lastname,users.profile_image,users.color_code', 'project_floor_worker.modified_datetime', 'DESC', '', '', $join_str, 'project_floor_worker.id');

        // echo '<pre>'; print_r($project_assigned_deficiency_list);exit;
        if (!empty($project_assigned_deficiency_list)) {
            foreach ($project_assigned_deficiency_list as $key => $val) {
                $img = $this->common->select_data_by_condition('deficiency_image', array('floor_worker_id' => $val['id']), '*', 'deficiency_image.id', 'ASC');
                if (empty($img)) {
                    $project_assigned_deficiency_list[$key]['def_image'] = base_url() . '../uploads/noimage.png';
                } else {
                    $project_assigned_deficiency_list[$key]['def_image'] = base_url() . $this->config->item('upload_floor_deficiency_thumb') . $img[0]['image'];
                }
                $project = $this->common->select_data_by_id('project', 'project.id', $project_id, '*,project.id as pro_id', array());
                $this->data['manager'] = $manager = $this->common->select_data_by_id('users', 'id', $project[0]['user_id']);

                if ($val['created_by'] == 'pm_to_tw') {
                    $project_assigned_deficiency_list[$key]['firstname'] = $manager[0]['firstname'];
                    $project_assigned_deficiency_list[$key]['lastname'] = $manager[0]['lastname'];
                }


                if ($project_assigned_deficiency_list[$key]['profile_image'] == '') {

                    $project_assigned_deficiency_list[$key]['profile_image'] = base_url() . '../uploads/profile.png';
                } else {
                    if ($val['created_by'] == 'pm_to_tw') {
                        $project_assigned_deficiency_list[$key]['profile_image'] = base_url() . $this->config->item('user_profile_upload_thumb_path') . $manager[0]['profile_image'];
                    } else {

                        $project_assigned_deficiency_list[$key]['profile_image'] = base_url() . $this->config->item('user_profile_upload_thumb_path') . $project_assigned_deficiency_list[$key]['profile_image'];
                    }
                }

                $timestamp = strtotime($val['created_datetime']);
                $project_assigned_deficiency_list[$key]['created_datetime'] = date('d F Y', $timestamp);

                if ($project_assigned_deficiency_list[$key]['status'] == "completed" || $project_assigned_deficiency_list[$key]['status'] == "reassigned") {

                    unset($project_assigned_deficiency_list[$key]);
                    continue;
                }


                $con = array('comment.to_id' => $this->input->post('user_id'), 'comment.def_id' => $val['id'], 'comment.read_by_to_id' => 0);
                $unread_comment = $this->common->select_data_by_allcondition('comment', $con, '*', '', '', '', '', array(), 'comment.def_id');
                if (!empty($unread_comment)) {
                    $project_assigned_deficiency_list[$key]['unread_comment'] = 1;
                    $a[] = $project_assigned_deficiency_list[$key];
                    unset($project_assigned_deficiency_list[$key]);
                } else {
                    $project_assigned_deficiency_list[$key]['unread_comment'] = 0;
                }
            }

            if (isset($a) && !empty($a)) {
                foreach ($a as $ke => $va) {
                    array_unshift($project_assigned_deficiency_list, $a[$ke]);
                }
            }

            $project_assigned_deficiency_list = array_values($project_assigned_deficiency_list);






            //  print_r($project_assigned_deficiency_list);exit;
            echo json_encode(array('status' => '200', 'message' => 'Record found', 'get_action_assigned_item_list' => $project_assigned_deficiency_list));
            die();
        } else {
            echo json_encode(array('status' => '404', 'message' => 'Record not found'));
            die();
        }
    }

    public function reassign_task() {

        if ($this->input->method() != 'post') {
            echo json_encode(array('status' => '403', 'message' => 'Request Not Allowed.'));
            die();
        }

        $this->form_validation->set_rules('user_id', 'user_id', 'required');
        $this->form_validation->set_rules('project_id', 'project_id', 'required');
        $this->form_validation->set_rules('def_id', 'def_id', 'required');
        $this->form_validation->set_rules('new_recipeint_id', 'new_recipeint_id', 'required');
        $this->form_validation->set_rules('comment', 'comment', 'required');


        if ($this->form_validation->run() === FALSE) {
            echo json_encode(array('status' => '402', 'message' => 'Invalid Data Format.'));
            die();
        }

        $this->user_auth($this->input->post('user_id'));
        $user_id = $this->input->post('user_id');
        $def_id = $this->input->post('def_id');
        $new_recipeint_id = $this->input->post('new_recipeint_id');
        $original_comment = $this->input->post('comment');
        $col = 'project_id,tradeworker_id,floor_id,deficiency_title,deficiency_desc,posX,posY,location,created_by,when_drop_tw_to_pm';
        $def_data = $this->common->select_data_by_id('project_floor_worker', 'id', $def_id, $col);
        $created_by = $def_data[0]['created_by'];
        $original_reciept = $def_data[0]['tradeworker_id'];
        $when_drop_tw_to_pm = $def_data[0]['when_drop_tw_to_pm'];
        $column = 'floor_worker_id,image,upload_by,created_datetime,modified_datetime,created_ip,modified_ip';
        $def_images = $this->common->select_data_by_id('deficiency_image', 'floor_worker_id', $def_id, $column);
        //print_r($this->db->last_query());exit;
        if (!empty($def_data)) {
            $update_def = array('status' => 'reassigned');
            $this->common->update_data($update_def, 'project_floor_worker', 'id', $def_id);
            $def_data[0]['tradeworker_id'] = $new_recipeint_id;
            $def_data[0]['status'] = 'pending';
            $def_data[0]['created_datetime'] = date('Y-m-d H:i:s');
            $def_data[0]['when_drop_tw_to_pm'] = NULL;
            $def_data[0]['created_by'] = 'pm_to_tw';
            $def_data[0]['created_ip'] = $this->input->ip_address();
            $def_data[0]['modified_datetime'] = date('Y-m-d H:i:s');
            $def_data[0]['modified_ip'] = $this->input->ip_address();
            $def_data[0]['deficiency_desc'] = $original_comment;
            //echo "<pre>"; print_r($def_data); die();
            $new_assigned_defid = $this->common->insert_data_getid($def_data[0], 'project_floor_worker');

            if ($new_assigned_defid) {
                if (!empty($def_images)) {
                    foreach ($def_images as $k => $v) {
                        $def_images[$k]['upload_by'] = 'manager';
                        $def_images[$k]['floor_worker_id'] = $new_assigned_defid;
                        $def_images[$k]['created_datetime'] = date('Y-m-d H:i:s');
                        $def_images[$k]['modified_datetime'] = date('Y-m-d H:i:s');
                        $def_images[$k]['created_ip'] = $this->input->ip_address();
                        $def_images[$k]['modified_ip'] = $this->input->ip_address();
                    }
                    $this->db->insert_batch('deficiency_image', $def_images);
                }
                if ($original_reciept != $this->input->post('user_id')) {
                    $notification_id = 7;

                    $insert_notifcation = array(
                        'sender_id' => $this->input->post('user_id'),
                        'receiver_id' => $original_reciept,
                        'notification_id' => $notification_id,
                        'def_id' => $def_id,
                        'project_id' => $this->input->post('project_id'),
                        'role' => 'Included_project',
                        'created_datetime' => date('Y-m-d H:i:s'),
                        'created_ip' => $this->get_client_ip(),
                    );

                    $this->common->insert_data($insert_notifcation, 'user_notification');
                }

                //Send email notification to tradeworker for new task                    
                $join_str[0] = array(
                    'table' => 'users a',
                    'join_table_id' => 'a.id',
                    'from_table_id' => 'project_floor_worker.tradeworker_id',
                    'join_type' => 'LEFT',
                );
                $join_str[2] = array(
                    'table' => 'project',
                    'join_table_id' => 'project.id',
                    'from_table_id' => 'project_floor_worker.project_id',
                    'join_type' => 'LEFT',
                );
                $join_str[1] = array(
                    'table' => 'users b',
                    'join_table_id' => 'b.id',
                    'from_table_id' => 'project.user_id',
                    'join_type' => 'LEFT',
                );
                $column = 'CONCAT(b.firstname," ",b.lastname) as manager, a.id as tid, CONCAT(a.firstname," ",a.lastname) as tradeworker
                                        ,a.email_id as trade_email,project.project_title, project_floor_worker.project_id';
                $condtn = array("project_floor_worker.id" => $new_assigned_defid);
                $email_data = $this->common->select_data_by_condition('project_floor_worker', $condtn, $column, '', '', '', '', $join_str);
                //print_r($email_data);exit;
                //check for email notification setting on/off

                $check_settting = $this->common->select_data_by_condition('email_notification_setting', array('user_id' => $email_data[0]['tid']), '*', '', '', '', '');
                if ($check_settting[0]['deficiency_assigned'] == 'On') {
                    $def_link = '<a href="' . base_url('Project_def/view_def/') . $new_assigned_defid . "/Included_project" . '" target="_blank" title="deficiency_view">' . base_url('Project_def/view_def/') . $new_assigned_defid . "/Included_project" . '</a>';
                    $mailData = $this->common->select_data_by_id('mailformat', 'mail_id', '11', '*', array());
                    $subject = str_replace("%project%", ucfirst($email_data[0]['project_title']), $mailData[0]['subject']);
                    $mailformat = $mailData[0]['mailformat'];
                    $mail_body = str_replace("%project%", ucfirst($email_data[0]['project_title']), $mailformat);
                    $mail_body = str_replace("%name%", ucfirst($email_data[0]['tradeworker']), $mail_body);
                    $mail_body = str_replace("%manager%", ucfirst($email_data[0]['manager']), $mail_body);
                    $mail_body = str_replace("%def_link%", $def_link, $mail_body);
                    $mail_body = str_replace("%comment%", $def_data[0]['deficiency_desc'], $mail_body);
                    $mail_body = str_replace("%location%", $def_data[0]['deficiency_title'], $mail_body);
                    $mail_body = str_replace("%sitename%", $this->data['site_name'], $mail_body);
                    $this->sendEmail($this->data['site_name'], $this->data['site_email'], $email_data[0]['trade_email'], $subject, $mail_body);
                    //email sent
                }
                $role = $this->input->post('role_reassign');

                if ($role == 'My_project') {
                    if ($email_data[0]['tid'] == $this->input->post('user_id')) {
                        $roles = "My_project";
                    } else {
                        $roles = "Included_project";
                    }
                } else {
                    if ($email_data[0]['tid'] == $this->input->post('user_id')) {
                        $roles = "Included_project";
                    } else {
                        $roles = "My_project";
                    }
                }

                // send for notification

                $notification_id = 1;
                $insert_notifcation = array(
                    'sender_id' => $this->input->post('user_id'),
                    'receiver_id' => $new_recipeint_id,
                    'notification_id' => $notification_id,
                    'def_id' => $new_assigned_defid,
                    'project_id' => $email_data[0]['project_id'],
                    'role' => $roles,
                    'created_datetime' => date('Y-m-d H:i:s'),
                    'created_ip' => $this->get_client_ip(),
                );

                $this->common->insert_data($insert_notifcation, 'user_notification');
                if ($created_by == 'tw_to_pm') {
                    $def_manager_id = $when_drop_tw_to_pm;
                    $def_manager = $this->common->select_data_by_id('users', 'id', $def_manager_id);
                    $def_link = '<a href="' . base_url('Project_def/view_def/') . $def_id . "/Included_project" . '" target="_blank" title="deficiency_view">' . base_url('Project_def/view_def/') . $def_id . "/Included_project" . '</a>';
                    $mailData = $this->common->select_data_by_id('mailformat', 'mail_id', '14', '*', array());
                    $subject = $mailData[0]['subject'];
                    $mailformat = $mailData[0]['mailformat'];
                    $mail_body = str_replace("%project_name%", ucfirst($email_data[0]['project_title']), $mailformat);
                    $mail_body = str_replace("%name%", ucfirst($def_manager[0]['firstname'] . ' ' . $def_manager[0]['lastname']), $mail_body);
                    $mail_body = str_replace("%project_title%", ucfirst($email_data[0]['project_title']), $mail_body);
                    $mail_body = str_replace("%def_link%", $def_link, $mail_body);
                    $mail_body = str_replace("%date%", $def_data[0]['created_datetime'], $mail_body);
                    $mail_body = str_replace("%location%", $def_data[0]['deficiency_title'], $mail_body);
                    $mail_body = str_replace("%sitename%", $this->data['site_name'], $mail_body);
                    $this->sendEmail($this->data['site_name'], $this->data['site_email'], $def_manager[0]['email_id'], $subject, $mail_body);
                }
                $condition = array("project_floor_worker.id" => $new_assigned_defid);
                $reassign_data = $this->common->select_data_by_condition('project_floor_worker', $condition, '*', '', '', '', '', array());

                //send Push notification		
                $from_user_info = $this->common->select_data_by_condition('users', array('id' => $email_data[0]['tid']), '*', '', '', '', '', array(), '');
                $to_user_info = $this->common->select_data_by_condition('users', array('id' => $def_data[0]['tradeworker_id']), '*', '', '', '', '', array(), '');

                $messageData['from_name'] = $from_user_info[0]['firstname'] . ' ' . $from_user_info[0]['lastname'];

                $messageData['to_name'] = $to_user_info[0]['firstname'] . ' ' . $to_user_info[0]['lastname'];

                // send push notification             
                // $userid = $user_id;
                $user_token_data = $this->common->select_data_by_id('users_token', 'user_id', $def_data[0]['tradeworker_id'], '*', array());
                if ($def_data[0]['tradeworker_id'] == $user_id) {
                    $Role = "My_project";
                } else {
                    $Role = "Reassign Project";
                }

                foreach ($user_token_data as $user_token_data) {
		$devicetype = $user_token_data['device_type'];
                    if ($devicetype == "Ios") {
                        $deviceToken = $user_token_data['ios_id'];
                        $msg = $messageData['from_name'] . ' Reassign a task.';
                        $notification = array(
                            'title' => "Assign Task",
                            'body' => $msg,
                            'icon' => '',
                            'sound' => '',
                            'user_id' => $email_data[0]['tid'],
                            'project_id' => $email_data[0]['project_id'],
                            'screen_id' => $reassign_data[0]['floor_id'],
                            'deficiency_id' => $def_id,
                            'to_user_id' => $def_data[0]['tradeworker_id'],
                            'notification_type' => 'reassign task',
                            'project_type' => $Role,
                            'to_user_name' => $messageData['from_name'],
                        );
                        //print_r($notification);exit;
                        $this->send_ios_notification($deviceToken, $notification,$msg);
                    } else {
                        $token = $user_token_data['fcm_id'];
                        $msg = $messageData['from_name'] . ' Reassign a task.';
                        $notification = array(
                            'title' => "Assign Task",
                            'body' => $msg,
                            'icon' => '',
                            'sound' => '',
                            'user_id' => $email_data[0]['tid'],
                            'project_id' => $email_data[0]['project_id'],
                            'screen_id' => $reassign_data[0]['floor_id'],
                            'deficiency_id' => $def_id,
                            'to_user_id' => $def_data[0]['tradeworker_id'],
                            'notification_type' => 'reassign task',
                            'project_type' => $Role,
                            'to_user_name' => $messageData['from_name'],
                        );
                        //print_r($notification);exit;
                        $this->sendPushToANDROID($token, $notification, $msg);
                    }
                }

                //echo json_encode(array('status' => '200', 'message' => 'Reassign Task Successfully'));
                // send push notification end

                $reassign_task_data = array(
                    'to_user_id' => $reassign_data[0]['tradeworker_id'],
                    'project_id' => $reassign_data[0]['project_id'],
                    'def_id' => $new_assigned_defid,
                    'screen_id' => $reassign_data[0]['floor_id']
                );

                //send email tradeworker if task by TW TO PM
                //email sent
                // $this->session->set_flashdata('success', "Deficiency reassigned successfully");
                echo json_encode(array('status' => '200', 'message' => 'Deficiency reassigned successfully', 'reassign_data' => $reassign_task_data));
                die();
            } else {
                echo json_encode(array('status' => '404', 'message' => 'Something went wrong.. please try later'));
                die();
            }
        } else {
            echo json_encode(array('status' => '404', 'message' => 'Something went wrong.. please try later'));
            die();
        }
    }

//send mail to buildster admin

    public function send_email() {//echo "hii";exit;
        if ($this->input->method() != 'post') {
            echo json_encode(array('status' => '403', 'message' => 'Request Not Allowed.'));
            die();
        }

        $this->form_validation->set_rules('user_id', 'user_id', 'required');
        $this->form_validation->set_rules('subject', 'subject', 'required');
        $this->form_validation->set_rules('description', 'description', 'required');
        if ($this->form_validation->run() === FALSE) {
            echo json_encode(array('status' => '402', 'message' => 'Invalid Data Format.'));
            die();
        }

        $this->user_auth($this->input->post('user_id'));
        $user_id = $this->input->post('user_id');
        //print_r($user_id);exit;
        $subject = $this->input->post('subject');
        $mail_body = $this->input->post('description');
        //$user_id = $this->session->userdata('builderster_user');
        //print_r($user_id);
        $email = $this->common->select_data_by_id('users', 'id', $user_id, '*');
        //print_r($this->db->last_query());exit;
        $app_email = $email[0]['email_id'];
        $app_name = $email[0]['firstname'];
	$last_name = $email[0]['lastname'];
	$full_name = $email[0]['firstname']." ".$email[0]['lastname'];
	$this->email->from($app_email, $app_name);

        $this->email->to($this->data['site_email']);

        $this->email->subject($subject);
        $this->email->message("<p>
	Hello <strong>Admin,</strong>
	</br>
	</br>
	<p><strong>Sender:</strong>&nbsp;&nbsp;This mail from $full_name.<p>
	<p>$mail_body.</p>
	</br>
        Thanks,</p>
");
        $this->email->send();
        echo json_encode(array('status' => '200', 'message' => 'Email sent to the buildster admin.'));
        die();
    }

public function sendPushToANDROID($token, $notification, $message) {

        $path_to_firebase_cm = 'https://fcm.googleapis.com/fcm/send';

        $fields = array(
            'to' => $token,
            'data' => $notification,
            'priority' => 'high',
            'sound' => "default"
        );

        $headers = array(
            'Authorization:key=' . API_ACCESS_KEY,
            'Content-Type:application/json'
        );
//	echo "<pre>"; print_r($fields);
        //echo "=============";
        // print_r($headers); die();
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $path_to_firebase_cm);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

        $result = curl_exec($ch);



        // print_r($result); die();
        return $result;
        curl_close($ch);
    }

    function send_ios_notification($deviceToken, $notification, $message) {
        //$passphrase = 'mxi123';
 	$passphrase = 'buildster123';
        $ctx = stream_context_create();
        stream_context_set_option($ctx, 'ssl', 'local_cert', $_SERVER['DOCUMENT_ROOT'] . '/projects/buildster/Certificates1.pem');
        stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

      //$fp = stream_socket_client('ssl://gateway.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);
	$fp = stream_socket_client('ssl://gateway.sandbox.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT, $ctx);
        if (!$fp)
            exit("Failed to connect: $err $errstr" . PHP_EOL);

        $body['aps'] = array(
            'data' => $notification,
            'alert'=>trim($message),
            'sound' => "default"
        );

       
        $payload = json_encode($body);

        $msg = chr(0) . pack('n', 32) . pack('H*', trim($deviceToken)) . pack('n', strlen($payload)) . $payload;
	 
        $result = fwrite($fp, $msg, strlen($msg));
	
        if (!$result) {
            return $result;
        } else {
            return $result;
        }

        fclose($fp);
    }

}
