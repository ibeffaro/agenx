<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class About_us extends BE_Controller {

    public function index() {
        $data['title']  = lang('tentang');
        $data['record'] = get_data('fe_tentang','id = 1')->row_array();
        render($data);
    }

    public function save() {
        $data = post();
        $data['daftar_deskripsi3'] = post('daftar_deskripsi3')[0];
        $data['daftar_deskripsi4'] = post('daftar_deskripsi4')[0];

        if(!$data['id']){
            $save   = insert_data('fe_tentang',$data);
        } else {
            $save   = update_data('fe_tentang',$data,'id',$data['id']);
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
