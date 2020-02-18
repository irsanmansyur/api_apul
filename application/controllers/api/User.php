<?php

defined('BASEPATH') or exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
// require APPPATH . '/libraries/REST_Controller.php';


use Restserver\Libraries\RestController;

require(APPPATH . 'libraries/RestController.php');
/**
 * This is an example of a few basic user interaction methods you could use
 * all done with a hardcoded array
 *
 * @package         CodeIgniter
 * @subpackage      Rest Server
 * @category        Controller
 * @author          Phil Sturgeon, Chris Kacerguis
 * @license         MIT
 * @link            https://github.com/chriskacerguis/codeigniter-restserver
 */

class User extends RestController
{
    function __construct()
    {
        // Construct the parent class
        parent::__construct();

        // Configure limits on our controller methods
        // Ensure you have created the 'limits' table and enabled 'limits' within application/config/rest.php
        $this->methods['index_get']['limit'] = 10; // 500 requests per hour per user/key
        $this->methods['index_post']['limit'] = 100; // 100 requests per hour per user/key
        $this->methods['index_delete']['limit'] = 50; // 50 requests per hour per user/key
        $this->load->library('form_validation');
    }
    public function index_get()
    {
        $this->db->select("tbuser.*");
        $this->db->from("tbuser");
        $user = $this->db->get();
        if ($user) {
            if (count($user->result_array()) > 0)
                $this->response([
                    "status" => true,
                    "message" => "User Di temukan",
                    "data" => $user->result_array()
                ], 200);
            else
                $this->response([
                    "status" => false,
                    "message" => "User Tidak ada",
                    "data" => []
                ], 200);
        } else {
            $this->response([
                "status" => false,
                "message" => "Kesalahan Siste",
                "data" => []
            ], 400);
        }
    }


    public function login_post()
    {
        $this->load->library("form_validation");
        $this->form_validation->set_data($this->post());
        $this->form_validation->set_rules("username", "username", "required");
        $this->form_validation->set_rules("password", "Password", "required");
        if ($this->form_validation->run()) {
            $username = $this->post("username");
            $password = $this->post("password");
            $user = $this->db->get_where("tbuser", [
                "username" => $username
            ])->row_array();
            if ($user) {
                if ($password == $user['password']) {
                    $this->response([
                        "status" => true,
                        "message" => "user di temukan",
                        "data" => $user
                    ], 200);
                } else {
                    $this->response([
                        "status" => false,
                        "message" => "Password Salah",
                        "data" => $this->post()
                    ], 200);
                }
            } else $this->response([
                "status" => false,
                "message" => "User tidak di temukan"
            ], 200);
        } else {
            $this->response([
                "status" => false,
                "message" => "Lengkapi data anda",
                "data" => $this->form_validation->error_array()
            ], 400);
        }
    }

    public function register_put()
    {
        $this->load->library("form_validation");
        $this->form_validation->set_data($this->post());
        $this->form_validation->set_rules("username", "username", "required");
        $this->form_validation->set_rules("password", "Password", "required");
        $this->form_validation->set_rules("noHp", "No hp", "required");
        $this->form_validation->set_rules("email", "email", "required");
        $this->form_validation->set_rules("status", "status", "required");
        if ($this->form_validation->run()) {
            $username = $this->post("username");
            $password = $this->post("password");
            $email = $this->post("email");
            $noHp = $this->post("noHp");
            $status = $this->post("status");
            $data = [
                'status' => $status,
                "password" => $password,
                "email" => $email,
                "username" => $username,
                "noHp" => $noHp
            ];
            $this->db->insert("tbl_user", $data);
            $eks = hasilCUD("berhasil ditambahkan");
            $eks->data = $data;
            $this->response($eks, 200);
        } else {
            $this->response([
                "status" => false,
                "message" => "Lengkapi data anda",
                "data" => $this->form_validation->error_array()
            ], 200);
        }
    }
    public function index_delete()
    {
        $where = $this->input->get();
        if (count($this->delete()) > 0) {
            foreach ($this->delete() as $row => $value) {
                $where[$row] = $value;
            }
        }

        $respon = $this->db->delete("web_aspirasi", $where);
        if ($respon) {
            $eks = hasilCUD("deleted.!");
            $this->response($eks, 200);
        } else {
            $this->response([
                'status' => false,
                "message" => "Terjadi Kesalahan"
            ], 502); // BAD_REQUEST (400) being the HTTP response code
        }
    }
}
