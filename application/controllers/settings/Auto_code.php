<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auto_code extends BE_Controller {

    public function index() {
        $data['table']      = table_lists();
        foreach($data['table'] as $k => $v) {
            if(strpos($v,table_prefix('user')) !== false || in_array($v,table_prefix(['menu','setting','kode','notofikasi']))) {
                unset($data['table'][$k]);
            }
        }
        render($data);
    }

    public function data() {
        $data = generate_data();
        render($data,'json');
    }

    public function get_field($table='') {
        if(!$table) $table = get('t');
        $dt     = get_fields($table);
        $field  = [];
        foreach($dt as $d) {
            if(in_array($d->type,['varchar','text'])) $field[] = $d->name;
        }
        $res    = render_option($field);
        if(get('t')) echo $res;
        else return $res;
    }

    public function get_data() {
        $data               = get_data('kode',get())->row();
        $data->opt_kolom    = $this->get_field($data->tabel);
        render($data,'json');
    }

    function save() {
        $data       = post();
        $response   = save_data('kode',$data);
        render($response,'json');
    }

	function delete() {
		$response 	= destroy_data('kode',get());
		render($response,'json');
	}
}