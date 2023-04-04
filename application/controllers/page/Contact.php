<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Contact extends BE_Controller {

    public function index() {
        $data['title']  = lang('kontak');
        $data['record'] = get_data('fe_kontak','id = 1')->row_array();
        render($data);
    }

    public function save() {
        $data = post();

        if(!$data['id']){
            $save   = insert_data('fe_kontak',$data);
        } else {
            $save   = update_data('fe_kontak',$data,'id',$data['id']);
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
