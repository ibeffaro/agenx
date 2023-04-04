<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_lists extends BE_Controller {

    public function index() {
        $data['user_group'] = get_data('user_group','is_active = 1')->result();
        render($data);
    }

    public function data() {
        $acc    = get_access();
        $config = [];
        if(setting('wrong_password_limit') && $acc['edit']) {
            $config['buttons'][]    = button_data('success','btn-unlock',['fa-unlock','Buka Kunci'],'{invalid_password} >='.setting('wrong_password_limit'));
        }
        $data   = generate_data($config);
        render($data,'json');
    }

    public function get_data() {
        $data   = get_data('user',get())->row();
        render($data,'json');
    }

    public function save() {
        $data       = post();
        $response   = save_data('user',$data);
        render($response,'json');
    }

    public function delete() {
        $response   = destroy_data('user',get());
        render($response,'json');
    }

    public function template_import() {
        $role               = get_data('user_group','is_active=1')->result();
        $data[]['data'][]   = ['nama'=>'','email'=>'','telepon'=>'','id_role'=>'','username'=>'','password'=>''];
        $data[]         = [
            'data'      => $role,
            'header'    => ['id'=>'id','nama'=>'role'],
            'title'     => 'Data Role'
        ];
        $data['filename']   = 'template_user';
        render($data,'excel');
    }

    public function import() {
        ini_set('max_execution_time', '300');
        $access     = get_access();
        $response   = [
            'status'    => 'failed',
            'message'   => lang('izin_ditolak')
        ];
        $filename   = post('file');
        $overwrite  = post('overwrite');
        $count      = 0;
        if(isset($access['import']) && $access['import'] && $filename) {
            $response['message']    = lang('tidak_ada_data_yang_diproses');
            $data   = read_excel($filename,['nama','email','telepon','id_role','username','password']);
            foreach($data as $k => $v) {
                if($k > 0) {
                    $dt         = $v;
                    $check      = get_data('user','username',$dt['username'])->row();
                    $dt['id']   = isset($check->id) ? $check->id : 0;
                    if(!$overwrite && $dt['id']) $dt['id']  = -1;
                    if((int) $dt['id'] != -1) {
                        if($dt['password']) $dt['password'] = enc_password($dt['password']);
                        else {
                            if($dt['id']) unset($dt['password']);
                            else enc_password('12345678');
                        }
                        if($dt['id'] == 0) $dt['is_active']   = 1;
                        $save   = save_data('user',$dt,true);
                        if($save['status'] == 'success') $count++;
                    }
                }
            }
            if($count > 0) {
                $response   = [
                    'status'    => 'success',
                    'message'   => lang('data_berhasil_diproses','',[$count])
                ];
                delete_temp_folder($filename);
            }
        }
        render($response,'json');
    }

    function unlock() {
        $acc    = get_access();
        if($acc['edit']) {
            update_data('user',[
                'invalid_password'  => 0
            ],'id',get('i'));
            render([
                'status'    => 'success',
                'message'   => 'Pembukaan kunci akun berhasil.'
            ],'json');
        } else {
            render([
                'status'    => 'failed',
                'message'   => lang('izin_ditolak')
            ],'json');
        }
    }
}