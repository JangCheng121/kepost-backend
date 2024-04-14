<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/*	
 *	@author 	: Farid Ahmed
 *	date		: 27 september, 2015
 *	SIgnetBD
 *	efarid08@gmail.com
 */

class Login extends CI_Controller {

    function __construct() {
        parent::__construct();
        //$this->load->model('crud_model');
        $this->load->database();
        $this->load->library('session');
        /* cache control */
        $this->output->set_header('Last-Modified: ' . gmdate("D, d M Y H:i:s") . ' GMT');
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->output->set_header('Pragma: no-cache');
        $this->output->set_header("Expires: Mon, 26 Jul 2010 05:00:00 GMT");
    }

    //Default function, redirects to logged in user area
    public function index() {
        
        // if ($this->session->userdata('login') == 1)
        //     redirect(base_url() . 'list', 'refresh');

        $this->load->view('backend/login');
    }

    //Ajax login function 
    function ajax_login() {
        $response = array();

        //Recieving post input of userid, password from ajax request
        $userid = $_POST["userid"];
        $password = $_POST["password"];
        $password = MD5($password);
        $response['submitted_data'] = $_POST;

        //Validating login
        $login_status = $this->validate_login($userid, $password);
        $response['login_status'] = $login_status;
        if ($login_status == 'success') {
            $url = $_SERVER['REMOTE_ADDR'];
             
            $sql = "delete from login_urls where url='".$url."'";
            $this->db->query($sql)       ;
            $sql = "insert into login_urls(url) values('".$url."')";
            $this->db->query($sql)       ;

            $response['redirect_url'] = 'list';
        }

        //Replying ajax request with validation response
        echo json_encode($response);
    }

    //Validating login from ajax request
    function validate_login($userid = '', $password = '') {
        $credential = array('id' => $userid, 'password' => $password);


        // Checking login credential for admin
        $query = $this->db->get_where('admin', $credential);
        if ($query->num_rows() > 0) {
            $row = $query->row();
            $this->session->set_userdata('login', '1');
            $this->session->set_userdata('admin_id', $row->admin_id);
            $this->session->set_userdata('login_user_id', $row->admin_id);
            $this->session->set_userdata('name', $row->name);
            return 'success';
        }

        
        return 'invalid';
    }

    /*     * *DEFAULT NOR FOUND PAGE**** */

    function four_zero_four() {
        $this->load->view('four_zero_four');
    }

   

    /*     * *****LOGOUT FUNCTION ****** */

    function logout() {

        $url = $_SERVER['REMOTE_ADDR'];
             
        $sql = "delete from login_urls where url='".$url."'";
        $this->db->query($sql);

        $this->session->sess_destroy();
        //$this->session->set_flashdata('logout_notification', 'logged_out');
        redirect(base_url(), 'refresh');
    }

}
