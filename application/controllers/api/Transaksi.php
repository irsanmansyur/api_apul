<?php

defined('BASEPATH') or exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
// require APPPATH . '/libraries/REST_Controller.php';

use phpDocumentor\Reflection\Types\Object_;
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

class Transaksi extends RestController
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
        $this->load->helper("my_helper");
        $this->tbl = (object) initTable("tbtransaksi", "trs");
    }

    function index_get($id = null)
    {
        if ($id) {
            $this->db->where("user_id", $id);
        }
        $eks = $this->db->get("tbtransaksi")->result_array();
        if ($eks) {
            foreach ($eks as $key => $val) {
                $eks[$key]['tgl_transaksi'] = date("d/M/Y", $val['tgl_transaksi']);
                $waktmulai = explode(":", $val['waktu_mulai']);
                $eks[$key]['waktu_selesai'] = $waktmulai[0] + $val['jam'] . ":" . $waktmulai[1];
            }
            $this->response([
                "status" => true,
                "data" =>  $eks
            ]);
        }
        $this->response([
            "status" => false,
            "message" => "kesalahan"
        ]);
    }

    public function index_post()
    {
        $tbl = $this->tbl;
        $data = $tbl->field;
        $data["status"] = "belum";
        foreach ($data as $key => $val) {
            if ($key == "tgl_transaksi") {
                $data[$key] = time();
            } else if (array_key_exists($key, $this->post())) {
                $data[$key] = $this->post($key);
            }
        }
        $this->db->insert($tbl->name, $data);
        $eks = hasilCUD("Transaksi Insert Successfully");
        $eks->data = $data;
        $this->response($eks, 200);
    }

    public function register_post()
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
            $this->db->insert("tbuser", $data);
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
}
