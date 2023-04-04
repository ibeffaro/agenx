<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends BE_Controller
{

    public function index()
    {
        redirect('auth/login');
    }

    public function login()
    {
        $data['image']  = 'login';
        $data['title']  = lang('masuk');
        render($data, 'layout:auth');
    }

    public function do_login()
    {
        $username           = post('username');
        $password           = post('password');
        $remember           = post('remember');
        $data               = false;
        $response           = [
            'status'        => 'failed',
            'message'       => lang('invalid_login_desc'),
            'href'          => base_url('welcome')
        ];
        $where = [
            'username'  => $username,
            'is_active' => 1
        ];
        $user               = get_data('user', $where)->row();
        if (isset($user->id)) {
            if (!setting('wrong_password_limit') || (setting('wrong_password_limit') && setting('wrong_password_limit') > $user->invalid_password)) {
                if (password_verify($password, $user->password)) {
                    $data = [
                        'id'                => $user->id
                    ];
                    $current_date           = date_now();
                    if ($remember) {
                        $cookie1            = [
                            'name'          => 'user_token',
                            'value'         => encode_id([$user->id, strtotime($current_date)], 260992),
                            'expire'        => '86500',
                            'httponly'      => true
                        ];
                        set_cookie($cookie1);
                    }
                    update_data('user', array(
                        'last_login'        => $current_date,
                        'ip_address'        => $this->input->ip_address(),
                        'last_activity'     => $current_date,
                        'invalid_password'  => 0
                    ), 'id', $user->id);
                    $this->session->set_userdata($data);
                    $response['status']     = 'success';
                    $response['message']    = lang('berhasil_masuk');
                    if ($this->session->userdata('last_url')) {
                        $response['href']   = base_url() . $this->session->userdata('last_url');
                        $this->session->unset_userdata('last_url');
                    }
                } else {
                    $jml_invalid    = $user->invalid_password + 1;
                    update_data('user', ['invalid_password' => $jml_invalid], 'id', $user->id);
                }
            } else {
                $response['message']    = lang('akun_terkunci_desc');
            }
        }
        render($response, 'json');
    }

    public function logout()
    {
        $this->session->unset_userdata('id');
        $this->session->unset_userdata('last_url');
        delete_cookie('user_token');
        redirect('auth/login');
    }

    public function forgot_password()
    {
        $data['image']  = 'forgot_password';
        $data['title']  = lang('lupa_kata_sandi');
        render($data, 'layout:auth');
    }

    public function do_forgot()
    {
        $user       = get_data('user', [
            'where' => [
                'email'     => post('email'),
                'is_active' => 1
            ]
        ])->row();
        if (isset($user->id)) {
            $token  = encode_id([$user->id, strtotime(date('Y-m-d H:i:s'))]);
            $link   = base_url('auth/reset-password?t=' . $token);
            $res    = send_mail([
                'to'        => $user->email,
                'nama'      => $user->nama,
                'link'      => $link,
                'subject'   => 'Reset Kata Sandi'
            ]);
            $res['href']    = base_url('auth/login');
            render($res, 'json');
        } else {
            render([
                'status'    => 'failed',
                'message'   => lang('alamat_surel_tidak_terdaftar')
            ], 'json');
        }
    }

    public function reset_password()
    {
        $data['valid']  = false;
        $id             = 0;
        $token          = decode_id(get('t'));
        if (is_array($token) && count($token) == 2) {
            $id         = $token[0];
            $calculate  = abs(strtotime(date('Y-m-d H:i:s')) - $token[1]);
            if ($calculate < 1800) {
                $data['valid']  = true;
            }
        }
        $data['id']     = encode_id([$id, strtotime(date('Y-m-d H:i:s'))]);
        $data['image']  = 'reset_password';
        $data['title']  = lang('reset_kata_sandi');
        render($data, 'layout:auth');
    }

    public function do_reset()
    {
        $valid          = false;
        $id             = 0;
        $token          = decode_id(post('token'));
        if (is_array($token) && count($token) == 2) {
            $id         = $token[0];
            $calculate  = abs(strtotime(date('Y-m-d H:i:s')) - $token[1]);
            if ($calculate < 1800) {
                $valid  = true;
            }
        }
        $res            = [
            'status'    => 'failed',
            'message'   => lang('reset_kata_sandi_gagal')
        ];
        if ($valid && $id) {
            $valid2     = true;
            if (setting('history_password_limit')) {
                $last_password  = get_data('user_password', [
                    'where'     => [
                        'id_user'   => $id
                    ],
                    'order_by'  => 'tanggal',
                    'order'     => 'desc',
                    'limit'     => setting('history_password_limit')
                ])->result();
                foreach ($last_password as $p) {
                    if (password_verify(post('password'), $p->password)) {
                        $valid2  = false;
                    }
                }
            }

            if ($valid2) {
                $save       = update_data('user', [
                    'password'              => enc_password(post('password')),
                    'change_password_at'    => date_now()
                ], 'id', $id);
                if ($save) {
                    insert_data('user_password', [
                        'id_user'   => $id,
                        'password'  => enc_password(post('password')),
                        'tanggal'   => date_now()
                    ]);
                    $res    = [
                        'status'    => 'success',
                        'message'   => lang('reset_kata_sandi_berhasil'),
                        'href'      => base_url('auth/login')
                    ];
                }
            } else {
                $res['message']     = lang('kata_sandi_sudah_pernah_digunakan');
            }
        }
        render($res, 'json');
    }
}
