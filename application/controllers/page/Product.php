<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product extends BE_Controller {

    public function index() {
        $data['title']  = lang('produk');
        $data['record'] = get_data('fe_produk','id = 1')->row_array();
        render($data);
    }

    public function save() {
        $data = post();
        $data['daftar_deskripsi2'] = post('daftar_deskripsi2')[0];

        if(!$data['id']){
            $save   = insert_data('fe_produk',$data);
        } else {
            $save   = update_data('fe_produk',$data,'id',$data['id']);
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
