<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Account extends BE_Controller {

    public function index() {
        redirect('account/profile');
    }

    public function profile() {
        $data['title']  = lang('profil');
        render($data);
    }

    public function save_profile() {
        $data = post();
        if(isset($data['username'])) unset($data['username']);
        if(isset($data['foto'])) unset($data['foto']);
        if(post('foto')) {
            $img        = basename(post('foto'));
            $temp_dir   = str_replace($img, '', post('foto'));
            $e          = explode('.', $img);
            $ext        = $e[count($e)-1];
            $new_name   = md5(uniqid()).'.'.$ext;
            $dest       = upload_path('user').$new_name;
            if(@copy(post('foto'),$dest)) {
                delete_dir(FCPATH . $temp_dir);
                if(user('nama_foto') && strpos(user('nama_foto'),'default') != false) {
                    @unlink(upload_path('user').user('nama_foto'));
                }
                $data['foto'] = $new_name;
            }
        }
        $save   = update_data('user',$data,'id',user('id'));
        $res    = [
            'status'    => 'failed',
            'message'   => lang('profil_gagal_diperbarui')
        ];
        if($save) {
            $res    = [
                'status'    => 'success',
                'message'   => lang('profil_berhasil_diperbarui')
            ];    
        }
        render($res,'json');
    }

    public function change_password() {
        $data['title']  = lang('kata_sandi');
        render($data);
    }

    public function save_password() {
        $user   = get_data('user','id',user('id'))->row();
        $res    = [
            'status'    => 'failed',
            'message'   => lang('kata_sandi_lama_tidak_cocok')
        ];
        if(isset($user->id) && password_verify(post('last_password'), $user->password)) {
            $valid          = true;
            if(setting('history_password_limit')) {
                $last_password  = get_data('user_password',[
                    'where'     => [
                        'id_user'   => user('id')
                    ],
                    'order_by'  => 'tanggal',
                    'order'     => 'desc',
                    'limit'     => setting('history_password_limit')
                ])->result();
                foreach($last_password as $p) {
                    if(password_verify(post('password'), $p->password)) {
                        $valid  = false;
                    }
                }
            }
            if($valid) {
                $save           = update_data('user',[
                    'password'              => enc_password(post('password')),
                    'change_password_at'    => date_now()
                ],'id',user('id'));
                if($save) {
                    insert_data('user_password',[
                        'id_user'   => user('id'),
                        'password'  => enc_password(post('password')),
                        'tanggal'   => date_now()
                    ]);
                    $res    = [
                        'status'    => 'success',
                        'message'   => lang('kata_sandi_berhasil_diperbarui')
                    ];
                } else {
                    $res['message'] = lang('kata_sandi_gagal_diperbarui');
                }
            } else {
                $res['message'] = lang('kata_sandi_sudah_pernah_digunakan');
            }
        }
        render($res,'json');
    }

}
