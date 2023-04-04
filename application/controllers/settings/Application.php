<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Application extends BE_Controller {

    public function index() {
        $fonts  = scandir(FCPATH . 'assets/fonts');
        $data['fonts']  = [];
        foreach($fonts as $f) {
            if(!in_array($f,['.','..']) && ($f == 'roboto' || file_exists(FCPATH . 'assets/fonts/' . $f . '/font.css'))) {
                $data['fonts'][]    = [
                    'key'           => $f,
                    'label'         => ucwords(str_replace(['-','.'],' ',$f))
                ];
            }
        }
        render($data);
    }

    public function save() {
        $data                   = post();
        $table_style            = post('table_style');
        $data['table_style']    = '';
        if(is_array($table_style)) $data['table_style'] = implode(',',$table_style);

        foreach($data as $k => $v) {
            if(in_array($k, ['favicon','logo','company_logo','background_login'])) {
                if($v) {
                    $img        = basename($v);
                    $temp_dir   = str_replace($img, '', $v);
                    $e          = explode('.', $img);
                    $ext        = $e[count($e)-1];
                    $new_name   = md5(uniqid()).'.'.$ext;
                    $dest       = upload_path('settings').$new_name;
                    if(@copy($v,$dest)) {
                        delete_dir(FCPATH . $temp_dir);
                        @unlink(upload_path('settings').setting($k));
                        $v = $new_name;
                        $check          = get_data('setting','_key',$k)->row();
                        if(isset($check->_key)) {
                            update_data('setting',array('_value'=>$v),'_key',$k);
                        } else {
                            insert_data('setting',array('_value'=>$v,'_key'=>$k));
                        }
                    }
                }
            } else {
                $check          = get_data('setting','_key',$k)->row();
                if(isset($check->_key)) {
                    update_data('setting',array('_value'=>$v),'_key',$k);
                } else {
                    insert_data('setting',array('_value'=>$v,'_key'=>$k));
                }
            }
        }
        $response 	= array(
            'status'        => 'success',
            'message'       => lang('pengaturan_berhasil_diperbaharui')
        );
        render($response,'json');
    }

    public function send_mail() {
        $data = array(
            'subject'       => post('subject'),
            'message'       => post('message'),
            'to'            => post('email')
        );
        $response = send_mail($data,false);
        render($response,'json');
    }
}
