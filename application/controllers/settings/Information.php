<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Information extends BE_Controller {

    public function index() {
        $data['user_group'] = get_data('user_group','is_active = 1')->result();
        render($data);
    }

    public function data() {
        $data   = generate_data();
        render($data,'json');
    }

    public function get_data() {
        $data           = get_data('informasi',get())->row();
        $data->tanggal  = '';
        if(isset($data->id) && $data->tanggal_mulai && $data->tanggal_selesai) {
            $data->tanggal  = custom_date($data->tanggal_mulai) . ' - ' . custom_date($data->tanggal_selesai);
        }
        render($data,'json');
    }

    public function save() {
        $tanggal    = post('tanggal');
        $data       = [
            'id'                => post('id'),
            'informasi'         => post('informasi'),
            'tanggal_mulai'     => is_array($tanggal) && isset($tanggal[0]) ? $tanggal[0] : '',
            'tanggal_selesai'   => is_array($tanggal) && isset($tanggal[1]) ? $tanggal[1] : '',
            'id_group'          => is_array(post('id_group')) ? implode(',',post('id_group')) : ''
        ];
        $response   = save_data('informasi',$data);
        render($response,'json');
    }

    public function delete() {
        $response   = destroy_data('informasi',get());
        render($response,'json');
    }

}