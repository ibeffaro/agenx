<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class General extends BE_Controller {

    public function index() {
        $target_menu	= $this->uri->uri_string();
        $check_menu		= get_data('menu','target',$target_menu)->row();
        if(isset($check_menu->id)) {
            $check_access	= get_data('user_akses',[
                'where'		=> [
                    'id_menu'	=> $check_menu->id,
                    'id_group'	=> (int) user('id_group'),
                    '_view'     => 1
                ]
            ])->row();
            if(isset($check_access->id)) {
                $list_menu = get_menu( 'user_akses', 'menu', (int) user('id_group') , $check_menu->id );
                if(count($list_menu)) {
                    $data['list_menu']  = $list_menu;
                } else {
                    $target = str_replace('-','_',strtolower($check_menu->target));
                    if(strpos($target,'/') !== false) {
                        if(file_exists(APPPATH . 'views/' . $target . '.php')) {
                            $view   = $target;
                        } elseif(file_exists(APPPATH . 'views/' . $target . '/index.php')) {
                            $view   = $target . '/index';
                        }
                    }
                    if(!isset($view) && $check_menu->ref) {
                        $file   = SCPATH . 'pages' . DIRECTORY_SEPARATOR . $check_menu->ref;
                        if(file_exists($file)) {
                            $define = @unserialize(file_get_contents($file));
                            if(is_array($define) && isset($define['key']) && isset($define['attribute'])) {
                                $table_name         = decode_string($define['key'], BUILDER_KEY);
                                $data['action_key'] = $define['key'].'/'.encode_string($target);
                                $data['form_ref']   = [];
                                $ref_form           = [];
                                $ref_field          = [];
                                $ref_is_active      = [];
                                $data['form_pk']    = '';
                                if($table_name && table_exists($table_name)) {
                                    $_fields    = get_fields($table_name);
                                    $fields     = [];
                                    foreach($_fields as $_f) {
                                        if($_f->primary_key) {
                                            $data['form_pk']    = $_f->name;
                                        }
                                        $fields[$_f->name]  = $_f->name;
                                    }
                                    foreach($define['attribute'] as $f_name => $f_attr) {
                                        if(!isset($fields[$f_name])) unset($define['attribute'][$f_name]);
                                        else {
                                            if($f_attr['type'] == 'select' && isset($f_attr['ref']) && isset($f_attr['refValue']) && isset($f_attr['refLabel'])) {
                                                $data['form_ref'][$f_attr['ref']] = [];
                                                if(!isset($ref_form[$f_attr['ref']])) {
                                                    $ref_form[$f_attr['ref']] = [];
                                                }
                                                if(!isset($ref_field[$f_attr['ref']])) {
                                                    if(table_exists($f_attr['ref'])) {
                                                        $ref_field[$f_attr['ref']]  = get_fields($f_attr['ref'],'name');
                                                    }
                                                }
                                                if(isset($ref_field['is_active'])) {
                                                    $ref_is_active[$f_attr['ref']]  = true;
                                                }
                                                if(isset($ref_field[$f_attr['ref']][$f_attr['refValue']])) {
                                                    $ref_form[$f_attr['ref']][$ref_field[$f_attr['ref']][$f_attr['refValue']]] = $ref_field[$f_attr['ref']][$f_attr['refValue']];
                                                }
                                                if(isset($ref_field[$f_attr['ref']][$f_attr['refLabel']])) {
                                                    $ref_form[$f_attr['ref']][$ref_field[$f_attr['ref']][$f_attr['refLabel']]] = $ref_field[$f_attr['ref']][$f_attr['refLabel']];
                                                }
                                            }
                                        }
                                    }
                                    foreach($ref_form as $ref_tbl => $ref_fld) {
                                        $where  = [];
                                        if(isset($ref_is_active[$ref_tbl])) {
                                            $where['is_active'] = 1;
                                        }
                                        $data['form_ref'][$ref_tbl] = get_data($ref_tbl,[
                                            'select'    => implode(',',$ref_fld),
                                            'where'     => $where
                                        ])->result_array();
                                    }

                                    $attr_field             = db_query('SELECT COLUMN_NAME, COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME="'.$table_name.'" AND TABLE_SCHEMA= "'.db_config('database').'"')->result();
                                    $af = [];
                                    foreach($attr_field as $a) {
                                        $t              = explode('|',str_replace(['(',')'],'|',$a->COLUMN_TYPE));
                                        $length         = isset($t[1]) ? $t[1] : '';
                                        $dec            = explode(',',$length);
                                        $af[$a->COLUMN_NAME] = [
                                            'length'    => isset($dec[0]) ? $dec[0] : '',
                                            'decimal'   => isset($dec[1]) ? $dec[1] : ''
                                        ];
                                    }

                                    $data['form_table']     = $table_name;
                                    $data['form_fields']    = $define['attribute'];
                                    foreach($data['form_fields'] as $kf => $ff) {
                                        if(isset($af[$kf])) {
                                            $data['form_fields'][$kf]['length']     = $af[$kf]['length'];
                                            $data['form_fields'][$kf]['decimal']    = $af[$kf]['decimal'];
                                        } else {
                                            $data['form_fields'][$kf]['length']     = '';
                                            $data['form_fields'][$kf]['decimal']    = '';
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        if(isset($data['list_menu'])) {
            render($data,'view:general_menu');
        } else if(isset($view) && strpos($target_menu,'development') === false) {
            render([],'view:' . $view);
        } else if(isset($data['form_ref']) && isset($data['form_table']) && isset($data['form_fields'])) {
            render($data,'view:form_builder');
        } else if(strpos($target_menu,'development') !== false && !is_dir(FCPATH . 'application/controllers/development')) {
            render('403');
        } else {
            render('404');
        }
    }

    public function search_menu() {
        $m      = get('menu');
        $menu   = get_data('menu a',[
            'select'    => 'a.level1, a.level2, a.level3, a.nama, a.target',
            'join'      => 'user_akses b ON a.id = b.id_menu TYPE LEFT',
            'where'     => 'a.nama LIKE "%'.$m.'%" AND b._view = 1 AND id_group = "'.user('id_group').'"',
            'order_by'  => 'a.id'
        ])->result_array();
        $data   = [];
        foreach($menu as $m) {
            $parent     = [];
            if($m['level2'] != 0) {
                $par    = get_data('menu','id',$m['level1'])->row();
                if(isset($par->id)) $parent[]   = $par->nama;
            }
            if($m['level3'] != 0) {
                $par    = get_data('menu','id',$m['level2'])->row();
                if(isset($par->id)) $parent[]   = $par->nama;
            }
            $parent[]   = $m['nama'];
            $data[]     = [
                'menu'  => implode(' > ',$parent),
                'link'  => base_url($m['target'])
            ];
        }
        render($data,'json');
    }

    public function data() {
        $data   = generate_data();
        render($data,'json');
    }

    public function get_data() {
        $header     = $this->input->request_headers();
        $key        = isset($header['X-Data-Key']) ? $header['X-Data-Key'] : '';
        if(!$key && isset($_SERVER['HTTP_X_DATA_KEY'])) $key    = $_SERVER['HTTP_X_DATA_KEY'];

        $table      = decode_string($key,TABLE_KEY);
        if($table && table_exists(table_prefix($table))) {
            $data   = get_data($table,get())->row();
            render($data,'json');
        } else render(['status' => 'failed', 'message' => lang('permintaan_tidak_valid')],'json');
    }

    public function save() {
        $target_menu	= $this->uri->uri_string();
        $check_menu		= get_data('menu','target',$target_menu)->row();

        $header     = $this->input->request_headers();
        $key        = isset($header['X-Data-Key']) ? $header['X-Data-Key'] : '';
        if(!$key && isset($_SERVER['HTTP_X_DATA_KEY'])) $key    = $_SERVER['HTTP_X_DATA_KEY'];

        $table      = decode_string($key,TABLE_KEY);
        if($table && table_exists(table_prefix($table))) {
            $data           = postUpper();
            $response   = save_data($table,$data);
            if($response['status'] == 'success' && $response['action']) {
                insert_data('user_log',[
                    'id_user'       => user('id'),
                    'tanggal'       => date_now(),
                    'keterangan'    => ($response['action'] == 'input' ? 'Menambah data ' : 'Mengubah data ') . $response['menu']
                ]);
            }
            render($response,'json');
        } else render(['status' => 'failed', 'message' => lang('permintaan_tidak_valid')],'json');
    }

    public function delete() {
        $header     = $this->input->request_headers();
        $key        = isset($header['X-Data-Key']) ? $header['X-Data-Key'] : '';
        if(!$key && isset($_SERVER['HTTP_X_DATA_KEY'])) $key    = $_SERVER['HTTP_X_DATA_KEY'];

        $table      = decode_string($key,TABLE_KEY);
        if($table && table_exists(table_prefix($table))) {
            $response   = destroy_data($table,get());
            if($response['status'] == 'success') {
                insert_data('user_log',[
                    'id_user'       => user('id'),
                    'tanggal'       => date_now(),
                    'keterangan'    => 'Menghapus data ' . $response['menu']
                ]);
            }
            render($response,'json');
        } else render(['status' => 'failed', 'message' => lang('permintaan_tidak_valid')],'json');
    }

    public function template_import($key='',$encode_target='') {
        $target = decode_string($encode_target);
        $access = false;
        if($target) {
            $acc    = get_access($target);
            $access = isset($acc['import']) && $acc['import'];
        }
        if($access) {
            $_ref   = decode_string($key, BUILDER_KEY);
            $file   = SCPATH . 'pages' . DIRECTORY_SEPARATOR . 'form.' . $_ref . '.txt';
            if(file_exists($file)) {
                $define = @unserialize(file_get_contents($file));
                if(is_array($define) && isset($define['key']) && isset($define['attribute'])) {
                    $table_name         = decode_string($define['key'], BUILDER_KEY);
                    $form_ref           = [];
                    $ref_form           = [];
                    $ref_field          = [];
                    $ref_is_active      = [];
                    $form_pk            = '';
                    if($define['key'] == $key && table_exists($table_name)) {
                        $_fields    = get_fields($table_name);
                        $fields     = [];
                        foreach($_fields as $_f) {
                            if($_f->primary_key) {
                                $form_pk    = $_f->name;
                            }
                            $fields[$_f->name]  = $_f->name;
                        }
                        foreach($define['attribute'] as $f_name => $f_attr) {
                            if(!isset($fields[$f_name]) || in_array($f_attr['type'],['imageupload','fileupload'])) unset($define['attribute'][$f_name]);
                            else {
                                if($f_attr['type'] == 'select' && isset($f_attr['ref']) && isset($f_attr['refValue']) && isset($f_attr['refLabel'])) {
                                    $form_ref[$f_attr['ref']] = [];
                                    if(!isset($ref_form[$f_attr['ref']])) {
                                        $ref_form[$f_attr['ref']] = [];
                                    }
                                    if(!isset($ref_field[$f_attr['ref']])) {
                                        if(table_exists($f_attr['ref'])) {
                                            $ref_field[$f_attr['ref']]  = get_fields($f_attr['ref'],'name');
                                        }
                                    }
                                    if(isset($ref_field['is_active'])) {
                                        $ref_is_active[$f_attr['ref']]  = true;
                                    }
                                    if(isset($ref_field[$f_attr['ref']][$f_attr['refValue']])) {
                                        $ref_form[$f_attr['ref']][$ref_field[$f_attr['ref']][$f_attr['refValue']]] = $ref_field[$f_attr['ref']][$f_attr['refValue']];
                                    }
                                    if(isset($ref_field[$f_attr['ref']][$f_attr['refLabel']])) {
                                        $ref_form[$f_attr['ref']][$ref_field[$f_attr['ref']][$f_attr['refLabel']]] = $ref_field[$f_attr['ref']][$f_attr['refLabel']];
                                    }
                                }
                            }
                        }
                        foreach($ref_form as $ref_tbl => $ref_fld) {
                            $where  = [];
                            if(isset($ref_is_active[$ref_tbl])) {
                                $where['is_active'] = 1;
                            }
                            $form_ref[$ref_tbl] = get_data($ref_tbl,[
                                'select'    => implode(',',$ref_fld),
                                'where'     => $where
                            ])->result_array();
                        }
                        foreach($define['attribute'] as $f_name => $f_attr) {
                            if($f_attr['type'] == 'select' && isset($f_attr['refData'])) {
                                $refData = explode(',',$f_attr['refData']);
                                $form_ref[$f_name]  = [];
                                foreach($refData as $rd) {
                                    $form_ref[$f_name][]    = [
                                        $f_name     => $rd
                                    ];
                                }
                            }
                        }

                        $form_table     = $_ref;
                        $form_fields    = $define['attribute'];
                    }
                }
            }

            if(isset($form_table) && isset($form_fields)) {
                $rc     = [];
                $lbl    = [];
                foreach($form_fields as $k => $v) {
                    $rc[$k]     = '';
                    $lbl[$k]    = $v['label'];
                }
                $autocodeField  = autocodeField($_ref);
                $data           = [];
                $data[]         = [
                    'data'      => [$rc],
                    'header'    => autocodeLabel($autocodeField,$lbl)
                ];

                foreach($form_ref as $k => $v) {
                    $data[] = [
                        'data'  => $v,
                        'title' => $k
                    ];
                }
                $data['filename']   = 'Template Import '.$acc['menu'];
                render($data,'excel');
            } else render('404');
        } else render('403');
    }

    function import($key='',$encode_target='') {
        ini_set('max_execution_time', '300');
        $target = decode_string($encode_target);
        $response   = [
            'status'    => 'failed',
            'message'   => lang('izin_ditolak')
        ];
        $access = false;
        if($target) {
            $acc    = get_access($target);
            $access = isset($acc['import']) && $acc['import'];
        }
        $filename   = post('file');
        $overwrite  = post('overwrite');
        $count      = 0;
        if($access && $filename) {
            $_ref   = decode_string($key, BUILDER_KEY);
            $file   = SCPATH . 'pages' . DIRECTORY_SEPARATOR . 'form.' . $_ref . '.txt';
            if(file_exists($file)) {
                $define = @unserialize(file_get_contents($file));
                if($define['key'] == $key && table_exists($_ref)) {
                    $table_field    = get_fields($_ref,'name');
                    $fields         = [];
                    foreach($define['attribute'] as $k => $v) {
                        if(isset($table_field[$k]) && !in_array($v['type'],['imageupload','fileupload'])) $fields[]   = $k;
                    }

                    if(count($fields) > 0) {
                        $response['message']    = lang('tidak_ada_data_yang_diproses');
                        $data                   = read_excel($filename,$fields);
                        $autocodeField          = autocodeField($_ref);
                        foreach($data as $k => $v) {
                            if($k > 0) {
                                $dt         = toUpper($v);
                                $check      = get_data($_ref,$dt)->row();
                                $dt['id']   = isset($check->id) ? $check->id : 0;
                                if(!$overwrite && $dt['id']) $dt['id']  = -1;
                                if((int) $dt['id'] != -1) {
                                    $save   = save_data($_ref,autocodeValidate($autocodeField,$dt),true);
                                    if($save['status'] == 'success') $count++;
                                }
                            }
                        }

                        if($count > 0) {
                            $response   = [
                                'status'    => 'success',
                                'message'   => lang('data_berhasil_diproses','',[$count])
                            ];
                            push_log('Import Data '.$acc['menu']);
                            delete_temp_folder($filename);
                        }
                    }
                }
            }
        }
        render($response,'json');
    }

    function export($key='',$encode_target='') {
        ini_set('max_execution_time', '300');

        $target = decode_string($encode_target);
        $access = false;
        if($target) {
            $acc    = get_access($target);
            $access = isset($acc['export']) && $acc['export'];
        }
        if($access) {
            $_ref   = decode_string($key, BUILDER_KEY);
            $file   = SCPATH . 'pages' . DIRECTORY_SEPARATOR . 'form.' . $_ref . '.txt';
            if(file_exists($file)) {
                $define = @unserialize(file_get_contents($file));
                if(is_array($define) && isset($define['key']) && isset($define['attribute'])) {
                    $table_name         = decode_string($define['key'], BUILDER_KEY);
                    $form_ref           = [];
                    $ref_form           = [];
                    $ref_field          = [];
                    $ref_is_active      = [];
                    $form_pk            = '';
                    $type_field         = [];
                    if($define['key'] == $key && table_exists($table_name)) {
                        $_fields    = get_fields($table_name);
                        $fields     = [];
                        foreach($_fields as $_f) {
                            if($_f->primary_key) {
                                $form_pk    = $_f->name;
                            }
                            $fields[$_f->name]      = $_f->name;
                            $type_field[$_f->name]  = $_f->type;
                        }
                        foreach($define['attribute'] as $f_name => $f_attr) {
                            if(!isset($fields[$f_name]) || in_array($f_attr['type'],['imageupload','fileupload'])) unset($define['attribute'][$f_name]);
                            else {
                                if($f_attr['type'] == 'select' && isset($f_attr['ref']) && isset($f_attr['refValue']) && isset($f_attr['refLabel'])) {
                                    $form_ref[$f_attr['ref']] = [];
                                    if(!isset($ref_form[$f_attr['ref']])) {
                                        $ref_form[$f_attr['ref']] = [];
                                    }
                                    if(!isset($ref_field[$f_attr['ref']])) {
                                        if(table_exists($f_attr['ref'])) {
                                            $ref_field[$f_attr['ref']]  = get_fields($f_attr['ref'],'name');
                                        }
                                    }
                                    if(isset($ref_field['is_active'])) {
                                        $ref_is_active[$f_attr['ref']]  = true;
                                    }
                                    if(isset($ref_field[$f_attr['ref']][$f_attr['refValue']])) {
                                        $ref_form[$f_attr['ref']][$ref_field[$f_attr['ref']][$f_attr['refValue']]] = $ref_field[$f_attr['ref']][$f_attr['refValue']];
                                    }
                                    if(isset($ref_field[$f_attr['ref']][$f_attr['refLabel']])) {
                                        $ref_form[$f_attr['ref']][$ref_field[$f_attr['ref']][$f_attr['refLabel']]] = $ref_field[$f_attr['ref']][$f_attr['refLabel']];
                                    }
                                }
                            }
                        }
                        foreach($ref_form as $ref_tbl => $ref_fld) {
                            $where  = [];
                            if(isset($ref_is_active[$ref_tbl])) {
                                $where['is_active'] = 1;
                            }
                            $form_ref[$ref_tbl] = get_data($ref_tbl,[
                                'select'    => implode(',',$ref_fld),
                                'where'     => $where
                            ])->result_array();
                        }

                        $form_table     = $_ref;
                        $form_fields    = $define['attribute'];
                    }
                }
            }

            if(isset($form_table) && isset($form_fields)) {
                $lbl    = [];
                $fld    = [];
                foreach($form_fields as $k => $v) {
                    $fld[]          = $k;
                    $lbl[$k]        = $v['label'];
                    if($v['type'] == 'currency') {
                        $lbl[$k]    = '-c' . $v['label'];
                    } elseif(isset($type_field) && isset($type_field[$k])) {
                        if( strpos($type_field[$k],'char') !== false || strpos($type_field[$k],'text') !== false) {
                            $lbl[$k]    = '-t' . $v['label'];
                        } elseif(strpos($type_field[$k],'date') !== false) {
                            $lbl[$k]    = '-d' . $v['label'];
                        }
                    }
                }
                $rc     = get_data($form_table,[
                    'select'    => implode(',',$fld)
                ])->result();

                $data   = [];
                $data[] = [
                    'data'      => $rc,
                    'header'    => $lbl
                ];

                foreach($form_ref as $k => $v) {
                    $data[] = [
                        'data'  => $v,
                        'title' => $k
                    ];
                }
                render($data,'excel');
            } else render('404');
        } else render('403');
    }

}