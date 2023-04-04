<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends BE_Controller {

    public function index() {
        $data['title']  = lang('beranda');
        $data['record'] = get_data('fe_beranda','id = 1')->row_array();
        render($data);
    }

    public function save() {
        $data = post();
        $data['daftar_deskripsi2'] = post('daftar_deskripsi2')[0];
        /* if(isset($data['foto'])) unset($data['foto']);
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
        } */
        if(!$data['id']){
            $save   = insert_data('fe_beranda',$data);
        } else {
            $save   = update_data('fe_beranda',$data,'id',$data['id']);
        }
        $res    = [
            'status'    => 'failed',
            'message'   => lang('data_gagal_diperbarui')
        ];
        if($save) {
            $res    = [
                'status'    => 'success',
                'message'   => lang('data_berhasil_diperbarui')
            ];    
        }
        render($res,'json');
    }

}
