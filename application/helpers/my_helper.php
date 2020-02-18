<?php



//pesan aksi
function hasilCUD($message = "Sukses.!")
{

    $response = ['status' => false, 'message' => $message];
    $ci = get_instance();
    if ($ci->db->affected_rows() < 1) {
        $response['message'] = ($ci->db->error()['message'] == "") ? "Data Utama pada tabel tidak Berubah" : $ci->db->error()['message'];
    } else
        $response['status'] = true;
    return (object) $response;
}
function initTable($table, $string = 'tbl')
{
    $ci = get_instance();
    $key = null;
    $field = null;
    foreach ($ci->db->field_data($table) as $row) {
        if ($row->primary_key == 1) {
            $key = $row->name;
            $val = '';
        } else if ($row->name == "date_created" || $row->name == "date_updated") {
            $val = time();
        } else
            $val = '';
        $field[$row->name] = $val;
    }

    $ci->db->select_max($key);
    $ci->db->from($table);
    $eks = $ci->db->get()->row_array()[$key];
    $noUrut = (int) substr($eks, -3, 3);
    $noUrut++;
    $field[$key] = $string . '_' . sprintf("%03s", $noUrut);
    return [
        "name" => $table,
        'key' => $key,
        'field' => $field
    ];
}
function getApi($url)
{
    // persiapkan curl
    $ch = curl_init();

    // set url 
    curl_setopt($ch, CURLOPT_URL, $url);

    // set user agent    
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');

    // return the transfer as a string 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    // $output contains the output string 
    $output = curl_exec($ch);

    // tutup curl 
    curl_close($ch);
    // mengembalikan hasil curl
    return $output;
}

function _sendEmail($data)
{
    $ci = get_instance();

    $config = [
        'protocol'  => 'smtp',
        'smtp_host' => 'ssl://smtp.googlemail.com',
        'smtp_user' => 'berkominfo@gmail.com',
        'smtp_pass' => 'ichaNK01',
        'smtp_port' => 465,
        'mailtype'  => 'html',
        'charset'   => 'utf-8',
        'newline'   => "\r\n"
    ];

    $ci->load->library('email');
    $ci->email->initialize($config);

    $ci->email->from('berkominfo@gmail.com', 'Berkominfo');

    $ci->email->to($data['email']);

    if ($data['type'] == 'verify') {
        $ci->email->subject('Account Verification');
        $ci->email->message('Your Token : ' . $data['token'] . ' ,</br>Click this link to verify you account : <a href="' . base_url() . 'admin/auth/verify?email=' . $data['email'] . '&token=' . urlencode($data['token']) . '">Activate</a>');
    } else if ($data['type'] == 'forgot') {
        $ci->email->subject('Reset Password');
        $ci->email->message('Your Token : ' . $data['token'] . ' ,</br>Click this link to reset your password : <a href="' . base_url() . 'admin/auth/resetpassword?email=' . $data['email'] . '&token=' . urlencode($data['token']) . '">Reset Password</a>');
    }
    if ($ci->email->send()) {
        return true;
    } else {
        return false;
    }
}
