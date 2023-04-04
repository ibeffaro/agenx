<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notification extends BE_Controller {

    public function index() {
        $data['title']  = lang('pemberitahuan');
        render($data,'view:notification');
    }

    public function load() {
        $limit  = get('limit')  ?: 25;
        $offset = get('offset') ?: 0;

        $data   = get_data('notifikasi',[
            'where'     => ['id_user'=>user('id')],
            'limit'     => $limit,
            'offset'    => $offset,
            'order_by'  => 'notif_date',
            'order'     => 'desc'
        ])->result();
        foreach($data as $k => $v) {
            $data[$k]->time = timeago($v->notif_date);
        }
        render(['data' => $data],'json');
    }

    public function read($encode_id='') {
        $d_id   = decode_id($encode_id);
        $id     = isset($d_id[0]) ? $d_id[0] : 0;
        $get    = get_data('notifikasi','id',$id)->row();
        if(isset($get->id)) {
            update_data('notifikasi',['is_read'=>1],'id',$id);
            redirect($get->notif_link);
        } else render('404');
    }

    public function read_all($encode='') {
        $d  = decode_id($encode);
        if(is_array($d) && count($d) && user('id')) {
            update_data('notifikasi',['is_read'=>1],'id_user',user('id'));
        }
    }

}