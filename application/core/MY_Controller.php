<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class MY_Controller extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        date_default_timezone_set('Asia/Jakarta');

        $tables = table_lists();
        $tablesList = [];
        foreach ($tables as $t) {
            $tablesList[$t]   = $t;
        }
        $this->config->set_item('db_table_list', json_encode($tablesList));

        $table_prefix   = isset($this->db->table_prefix) ? $this->db->table_prefix : '';

        if (isset($tablesList[$table_prefix . 'setting'])) {
            $setting        = get_data('setting')->result();
            foreach ($setting as $s) {
                if (!$this->config->item('setting_' . $s->_key)) {
                    $this->config->set_item('setting_' . $s->_key, $s->_value);
                }
            }
        }
        $pass_validation    = 'min-length:' . setting('password_min_length');
        if (setting('strong_password')) $pass_validation .= '|strong_password';
        $this->config->set_item('setting_password_validation', $pass_validation);
        $color              = setting('custom_color_primary') ? setting('color_primary') : '#417ff9';
        $this->config->set_item('setting_color', $color);

        $lang = get_cookie('app-language');
        if (!$lang || !is_dir(FCPATH . 'assets/lang/' . $lang)) $lang = 'id_bahasa_indonesia';
        $this->config->set_item('setting_language', $lang);
        $this->config->set_item('setting_language_code', explode('_', $lang)[0]);

        $lang_location  = FCPATH . 'assets/lang/' . $lang;
        if (is_dir($lang_location)) {

            $_lang  = scandir($lang_location);

            foreach ($_lang as $_l) {
                if (strtolower(substr($_l, -4)) == 'json') {
                    $json_to_array   = json_decode(file_get_contents($lang_location . '/' . $_l), true);
                    if (is_array($json_to_array)) {
                        foreach ($json_to_array as $jk => $jv) {
                            $this->config->set_item('lang_' . $jk, $jv);
                        }
                    }
                }
            }
        }

        if (MAINTENANCE_MODE) {
            render('maintenance_mode');
        }
    }
}

class BE_Controller extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();

        $post       = $this->input->post();
        $headers    = $this->input->request_headers();
        if (count($post) > 0 && strpos(base_url(), 'assets') === false) {
            if ((isset($headers['X-Requested-With']) && $headers['X-Requested-With'] == 'XMLHttpRequest') || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
                $request_token  = $client_token = '';
                if (isset($headers['X-Request-Token']))  $request_token  = $headers['X-Request-Token'];
                if (isset($headers['X-Client-Token']))   $client_token   = $headers['X-Client-Token'];

                if (!$request_token && isset($_SERVER['HTTP_X_REQUEST_TOKEN']))  $request_token  = $_SERVER['HTTP_X_REQUEST_TOKEN'];
                if (!$client_token && isset($_SERVER['HTTP_X_CLIENT_TOKEN']))    $client_token   = $_SERVER['HTTP_X_CLIENT_TOKEN'];

                if (!token_validation($request_token, $client_token)) {
                    $this->output->set_status_header(400);
                    $res    = [
                        'status'    => '400',
                        'message'   => lang('invalid_token_desc')
                    ];
                    render($res, 'json');
                    die;
                }
            } else {
                $request_token  = $this->input->post('request_token');
                $client_token   = $this->input->post('client_token');
                if (!token_validation($request_token, $client_token)) {
                    render(['message' => lang('invalid_token_desc')], 'error');
                }
            }
        }

        $module         = $this->uri->segment(1);
        $class          = $this->router->fetch_class();
        $method         = $this->router->fetch_method();
        $login_id       = $this->session->userdata('id');

        $table_prefix   = isset($this->db->table_prefix) ? $this->db->table_prefix : '';
        $table_req      = ['kode', 'menu', 'notifikasi', 'setting', 'user', 'user_akses', 'user_group', 'user_log', 'user_password'];
        $tablesList     = $this->config->item('db_table_list');
        $lt             = [];
        $jsonLt         = json_decode($tablesList, true);
        if (isset($jsonLt) && is_array($jsonLt)) $lt = $jsonLt;
        foreach ($table_req as $ktr => $tr) {
            if (isset($lt[$table_prefix . $tr])) unset($table_req[$ktr]);
        }
        if (count($table_req) > 0) {
            $viewx      = $this->load->view('errors/no_table', ['table' => $table_req, 'prefix' => $table_prefix], true);
            echo $viewx;
            die;
        }

        if (!$login_id) {
            $check_cookie = decode_id(get_cookie('user_token'), 260992);
            if (count($check_cookie) == 2) {
                $check_user = get_data('user', [
                    'where' => [
                        'id'            => $check_cookie[0],
                        'last_login'    => date('Y-m-d H:i:s', $check_cookie[1])
                    ]
                ])->row();
                if (isset($check_user->id)) {
                    $data = array(
                        'id'            => $check_cookie[0]
                    );
                    $this->session->set_userdata($data);
                    $login_id   = $check_cookie[0];
                }
            }
        }
        if ($login_id && setting('single_login')) {
            $_user      = get_data('user', 'id', $login_id)->row();
            if ($module != 'auth' && isset($_user->id) && $_user->ip_address != $this->input->ip_address()) {
                $data['title']  = lang('keluar');
                $data['ip']     = $_user->ip_address;
                $data['image']  = 'force_logout';
                render($data, 'view:auth/force_logout layout:auth');
            }
        }
        if ($module == 'auth' && $login_id && $method != 'logout') {
            redirect('/welcome');
        } else {
            if (!$login_id && $module != 'auth') {
                $param_url          = get();
                $param_url_string   = '';
                if (is_array($param_url)) {
                    foreach ($param_url as $param_key => $param_val) {
                        $param_url_string   .= $param_url_string ? '&' : '?';
                        $param_url_string   .= $param_key . '=' . $param_val;
                    }
                }
                $current_url        = $this->uri->uri_string() ?: 'welcome';
                $this->session->set_userdata('last_url', $current_url . $param_url_string);
                redirect('auth/login');
            }
            $user   = get_data('user a', array(
                'select'        => 'a.*,b.nama AS grp',
                'join'          => [
                    'user_group b ON a.id_group = b.id TYPE LEFT'
                ],
                'where'         => [
                    'a.id'      => $login_id
                ]
            ))->row_array();
            if (isset($user['id'])) {
                $appColor       = '417ff9';
                $appTextColor   = 'ffffff';
                if (setting('custom_color_primary')) {
                    $appColor   = str_replace('#', '', setting('color_primary'));
                    if (getBrightness(setting('color_primary')) == 'light') {
                        $appTextColor = '333537';
                    }
                }
                $foto       = 'https://ui-avatars.com/api/?name=' . urlencode($user['nama']) . '&background=' . $appColor . '&color=' . $appTextColor . '&bold=true&size=256';
                $rFoto      = 'https://ui-avatars.com/api/?name=' . urlencode($user['nama']) . '&color=' . $appColor . '&background=' . $appTextColor . '&bold=true&size=256';
                if ($user['foto'] && file_exists(FCPATH . upload_path('user') . $user['foto'])) {
                    $foto   = base_url(upload_path('user') . $user['foto']);
                    $rFoto  = base_url(upload_path('user') . $user['foto']);
                }
                foreach ($user as $ku => $vu) {
                    $this->config->set_item('user_' . $ku, $vu);
                }
                $this->config->set_item('user_foto', $foto);
                $this->config->set_item('user_foto2', $rFoto);
                $this->config->set_item('user_nama_foto', $user['foto']);
                update_data('user', ['last_activity' => date('Y-m-d H:i:s')], 'id', $user['id']);
            }
        }
        if (user('id') && setting('expired_password')) {
            $expired_password       = setting('expired_password');
            $date                   = strtotime(date('Y-m-d H:i:s'));
            $exp                    = strtotime(date('Y-m-d H:i:s', strtotime('+' . $expired_password . ' days', strtotime(user('change_password_at')))));
            if ($date >= $exp) {
                $this->config->set_item('user_expired_password', true);
                if ($module != 'auth' && $module != 'account') {
                    redirect('account/change-password');
                }
            }
        }

        $check_module   = get_data('menu', 'target', $module)->row();
        // if((isset($check_module->id) && $check_module->dev_only) && (user('id_group') != 1 || ENVIRONMENT != 'development')) {
        //     render('403');
        // }

        if (setting('write_log_activity')) {
            $dt_log     = '';
            $metode     = 'GET';
            if (count(post())) {
                $dt_log = serialize(post());
                $metode = 'POST';
            } elseif (count(get())) {
                $dt_log = serialize(get());
            }
            $data_log   = [
                'ip_address'    => $this->input->ip_address(),
                'tanggal'       => date('Y-m-d H:i:s'),
                'id_user'       => user('id'),
                'nama_user'     => user('nama'),
                'keterangan'    => 'Mengakses ' . base_url($this->uri->uri_string()),
                'data'          => $dt_log,
                'metode'        => $metode,
                'respon'        => 200
            ];
            $save_log   = insert_data('user_log', $data_log);
            $this->config->set_item('setting_last_id_log', $save_log);
        }
    }
}

class FE_Controller extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->config->set_item('setting_default_layout', 'frontend');
        $this->load->library('pagination');
    }
}

class CLI_Controller extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    function reset()
    {
        // RESET
        echo "\e[0m\e[39m\e[49m";
        echo "\n\n";
    }

    function markSuccess($string)
    {
        return "\e[1m\e[42m" . $string . "\e[0m\e[49m";
    }

    function textSuccess($string)
    {
        return "\e[1m\e[32m" . $string . "\e[0m\e[39m";
    }

    function markError($string)
    {
        return "\e[1m\e[41m" . $string . "\e[0m\e[49m";
    }

    function textError($string)
    {
        return "\e[1m\e[31m" . $string . "\e[0m\e[39m";
    }

    function textBold($string)
    {
        return "\e[1m" . $string . "\e[0m";
    }

    function textUnderline($string)
    {
        return "\e[4m" . $string . "\e[0m";
    }
}
