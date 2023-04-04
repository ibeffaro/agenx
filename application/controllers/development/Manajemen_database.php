<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Manajemen_database extends BE_Controller {

    var $dir;
    var $dirRelation;
    var $dirRules;
    var $dirPages;

    function __construct() {
        parent::__construct();
        $this->dir          = SCPATH . 'migrations';
        $this->dirRelation  = SCPATH . 'relations';
        $this->dirRules     = SCPATH . 'rules';
        $this->dirPages     = SCPATH . 'pages';
    }

    public function index() {
        $data['cur_table']          = get('table');
        $data['show']               = in_array(get('show'),["structure","rules","form"]) ? get('show') : 'data';
        if(!table_exists($data['cur_table'])) {
            $data['cur_table']      = 'tableList';
            $data['table_detail']   = db_query('SELECT TABLE_NAME, ENGINE, TABLE_COLLATION, TABLE_ROWS, AUTO_INCREMENT, TABLE_COMMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA="'.db_config('database').'"')->result();
            foreach($data['table_detail'] as $kd => $vd) {
                if($vd->TABLE_NAME == '_migration') unset($data['table_detail'][$kd]);
            }
        } else {
            if($data['show'] == 'data') {
                $data['fields']     = get_fields($data['cur_table']);
            } elseif(in_array($data['show'],['structure','rules','form'])) {
                $fields             = db_query('SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, COLUMN_KEY, EXTRA, COLUMN_COMMENT FROM information_schema.COLUMNS WHERE TABLE_NAME="'.$data['cur_table'].'" AND TABLE_SCHEMA= "'.db_config('database').'"')->result();
                $data['structure']  = [];
                foreach($fields as $f) {
                    $fld            = [
                        'field'         => $f->COLUMN_NAME,
                        'type'          => '',
                        'length'        => '',
                        'unsigned'      => strpos($f->COLUMN_TYPE,'unsigned') !== false ? true : false,
                        'null'          => $f->IS_NULLABLE == 'NO' ? false : true,
                        'key'           => $f->COLUMN_KEY == 'PRI' ? 'PRIMARY KEY' : '',
                        'default'       => $f->COLUMN_DEFAULT,
                        'auto_increment'=> $f->EXTRA == 'auto_increment' ? true : false,
                        'validation'    => '',
                        'dir'           => ''
                    ];
                    if($f->COLUMN_COMMENT == 'fk' && !$fld['key']) $fld['key']  = 'FOREIGN KEY';
                    $t              = explode('|',str_replace(['(',')'],'|',$f->COLUMN_TYPE));
                    $fld['type']    = $t[0];
                    $fld['length']  = isset($t[1]) ? $t[1] : '';
                    $data['structure'][]    = $fld;
                }

                if($data['show'] == 'rules') {
                    $filename   = $this->dirRules . DIRECTORY_SEPARATOR . $data['cur_table'] . '.txt';
                    if(file_exists($filename)) {
                        $dt     = @unserialize(file_get_contents($filename));
                        if(is_array($dt)) {
                            foreach($dt as $kd => $vd) {
                                foreach($data['structure'] as $ks => $vs) {
                                    if($vs['field'] == $kd) {
                                        if(is_array($vd)) {
                                            if(isset($vd['validation']))
                                                $data['structure'][$ks]['validation']   = str_replace(['{','}','|'],['[[',']]',','],$vd['validation']);
                                            if(isset($vd['path']))
                                                $data['structure'][$ks]['dir']          = $vd['path'];
                                        } else {
                                            $data['structure'][$ks]['validation']       = str_replace(['{','}','|'],['[[',']]',','],$vd);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        $data['primary_table']      = ['_migration','tbl_informasi','tbl_kode','tbl_menu','tbl_notifikasi','tbl_setting','tbl_user','tbl_user_akses','tbl_user_group','tbl_user_log','tbl_user_password'];
        $data['table']              = table_lists();
        foreach($data['table'] as $ktable => $vtable) {
            if($vtable == '_migration') unset($data['table'][$ktable]);
        }
        render($data);
    }

    function add_table() {
        $access         = get_access();
        if(!$access['input']) {
            render([
                'status'    => 'failed',
                'message'   => lang('izin_ditolak')
            ],'json'); die;
        }
        $table          = str_replace(['-',' '],'_',trim(post('table')));
        $field          = post('field');
        $type           = post('type');
        $length         = post('length');
        $default        = post('default');
        $unsigned       = post('unsigned');
        $null           = post('null');
        $key            = post('key');
        $auto_increment = post('auto_increment');
        $timestamp      = post('timestamp');

        $filename       = date('YmdHis').'c_'.$table.'.json';

        if($table && is_array($field) && count($field) > 0) {
            if(!table_exists($table)) {
                $data       = [
                    'table'             => $table,
                    'up'                => [
                        'create'        => [
                            'fields'    => [],
                            'engine'    => 'MyISAM'
                        ]
                    ],
                    'down'              => 'drop'
                ];

                $primary_key    = '';

                foreach($field as $k => $d) {
                    $new_field  = str_replace([' ','-'],'_',trim($field[$k]));
                    if(!isset($data['up']['create']['fields'][$new_field])) {
                        $f    = [
                            'type'              => $type[$k],
                            'null'              => trim($null[$k]) ? true : false
                        ];
                        if(trim($length[$k]))           $f['length']            = trim($length[$k]);
                        if(trim($default[$k]))          $f['default']           = trim($default[$k]);
                        if(trim($auto_increment[$k]) && substr($type[$k],-3) == 'int' && $key[$k] == 'pk') {
                            $f['auto_increment']    = true;
                        }
                        if($key[$k] == 'fk')            $f['comment']           = 'fk';
                        if(trim($unsigned[$k]) && substr($type[$k],-3) == 'int') {
                            $f['unsigned']          = true;
                        }

                        if(substr($type[$k],-4) == 'char' && !isset($f['length'])) {
                            $f['length']  = '100';
                        }

                        if($key[$k] == 'pk' && !$primary_key) $primary_key      = $new_field;

                        $data['up']['create']['fields'][$new_field]             = $f;
                    }
                }

                if($timestamp) {
                    $data['up']['create']['fields']['timestamp']    = true;
                }
                if($primary_key) {
                    $data['up']['create']['primary_key']            = $primary_key;
                }

                $json       = json_encode($data,JSON_PRETTY_PRINT);
                $pathFile   = $this->dir.DIRECTORY_SEPARATOR.$filename;
                $handle     = fopen($pathFile, "wb");
                if($handle) {
                    fwrite ( $handle, $json );
                }
                fclose($handle);
                $oldmask = umask(0);
                chmod($pathFile, 0777);
                umask($oldmask);
                if(file_exists($pathFile)) {
                    $res = $this->migrate($filename);
                    if($res['status'] == 'success') {
                        $res['data']['href']    = base_url('development/manajemen-database?table='.$table.'&show=structure');
                    }
                    render($res,'json');
                } else render(['status'=>'failed','message'=>lang('data_gagal_disimpan')],'json');
            } else  render(['status'=>'failed','message'=>lang('tabel_sudah_ada','',[$table])],'json');
        } else render(['status'=>'failed','message'=>lang('data_gagal_disimpan')],'json');
    }

    function add_field() {
        $access         = get_access();
        if(!$access['input']) {
            render([
                'status'    => 'failed',
                'message'   => lang('izin_ditolak')
            ],'json'); die;
        }
        $table          = str_replace(['-',' '],'_',trim(post('table')));
        $after          = post('after');
        $field          = post('field');
        $type           = post('type');
        $length         = post('length');
        $default        = post('default');
        $unsigned       = post('unsigned');
        $null           = post('null');
        $key            = post('key');
        $auto_increment = post('auto_increment');

        $filename       = date('YmdHis').'m_'.$table.'.json';

        $response       = [
            'status'    => 'failed',
            'message'   => lang('data_gagal_disimpan')
        ];
        if($table && table_exists($table) && is_array($field) && count($field) > 0) {
            $fields         = db_query('SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, COLUMN_KEY, EXTRA, COLUMN_COMMENT FROM information_schema.COLUMNS WHERE TABLE_NAME="'.$table.'" AND TABLE_SCHEMA= "'.db_config('database').'"')->result();
            $exists         = [];
            $match_after    = false;
            $lastField      = '';
            foreach($fields as $f) {
                $exists[$f->COLUMN_NAME] = true;
                if($after == $f->COLUMN_NAME) $match_after = true;
                $lastField  = $f->COLUMN_NAME;
            }
            if(!$match_after) $after = $lastField;

            $add_field      = [];
            $rm_field       = [];
            foreach($field as $k => $x) {
                $new_field  = str_replace([' ','-'],'_',trim($field[$k]));
                if(!isset($exists[$new_field]) && !isset($add_field[$new_field])) {
                    $f    = [
                        'type'              => $type[$k],
                        'null'              => trim($null[$k]) ? true : false
                    ];
                    if(trim($length[$k]))   $f['length']            = trim($length[$k]);
                    if(trim($default[$k]))  $f['default']           = trim($default[$k]);
                    if($key[$k] == 'fk')    $f['comment']           = 'fk';
                    if(trim($unsigned[$k]) && substr($type[$k],-3) == 'int') {
                        $f['unsigned']          = true;
                    }

                    if(substr($type[$k],-4) == 'char' && !isset($f['length'])) {
                        $f['length']            = '100';
                    }
                    $f['after']                 = $after;
                    $after                      = $new_field;

                    $add_field[$new_field]      = $f;
                    $rm_field[]                 = $new_field;
                }
            }

            if(count($add_field) > 0) {
                $data       = [
                    'table'             => $table,
                    'up'                => [
                        'add_column'    => $add_field
                    ],
                    'down'              => [
                        'drop_column'   => $rm_field
                    ]
                ];

                $json       = json_encode($data,JSON_PRETTY_PRINT);
                $pathFile   = $this->dir.DIRECTORY_SEPARATOR.$filename;
                $handle     = fopen($pathFile, "wb");
                if($handle) {
                    fwrite ( $handle, $json );
                }
                fclose($handle);
                $oldmask = umask(0);
                chmod($pathFile, 0777);
                umask($oldmask);
                if(file_exists($pathFile)) {
                    $response = $this->migrate($filename);
                }
            }

        }

        render($response,'json');
    }

    function edit_field() {
        $access         = get_access();
        if(!$access['edit']) {
            render([
                'status'    => 'failed',
                'message'   => lang('izin_ditolak')
            ],'json'); die;
        }
        $table          = str_replace(['-',' '],'_',trim(post('table')));
        $last_field     = post('last_field');
        $field          = post('field');
        $type           = post('type');
        $length         = post('length');
        $default        = post('default');
        $unsigned       = post('unsigned');
        $null           = post('null');
        $key            = post('key');
        $auto_increment = post('auto_increment');

        $filename       = date('YmdHis').'m_'.$table.'.json';

        $response       = [
            'status'    => 'failed',
            'message'   => lang('data_gagal_disimpan')
        ];
        if($table && table_exists($table) && is_array($field) && count($field) > 0) {
            $fields         = db_query('SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, COLUMN_KEY, EXTRA, COLUMN_COMMENT FROM information_schema.COLUMNS WHERE TABLE_NAME="'.$table.'" AND TABLE_SCHEMA= "'.db_config('database').'"')->result();
            $exists         = [];
            foreach($fields as $f) {
                $exists[$f->COLUMN_NAME] = $f;
            }

            $up         = [];
            $down       = [];
            foreach($last_field as $k => $x) {
                $old_field  = str_replace([' ','-'],'_',trim($last_field[$k]));
                $new_field  = str_replace([' ','-'],'_',trim($field[$k]));
                if(isset($exists[$old_field]) && !isset($up[$new_field])) {
                    $f    = [
                        'name'              => $new_field,
                        'type'              => $type[$k],
                        'null'              => trim($null[$k]) ? true : false
                    ];
                    if(trim($length[$k]))   $f['length']            = trim($length[$k]);
                    if(trim($default[$k]))  $f['default']           = trim($default[$k]);
                    if($key[$k] == 'fk')    $f['comment']           = 'fk';
                    if(trim($unsigned[$k]) && substr($type[$k],-3) == 'int') {
                        $f['unsigned']          = true;
                    }
                    if($exists[$old_field]->COLUMN_KEY == 'PRI') {
                        if(trim($auto_increment[$k])) {
                            $f['auto_increment']    = true;
                        } else {
                            $f['auto_increment']    = false;
                        }
                    }
                    if(substr($type[$k],-4) == 'char' && !isset($f['length'])) {
                        $f['length']            = '100';
                    }

                    $up[$old_field]         = $f;

                    $f2     = [
                        'name'      => $exists[$old_field]->COLUMN_NAME,
                        'type'      => '',
                        'null'      => $exists[$old_field]->IS_NULLABLE == 'NO' ? false : true,
                        'unsigned'  => strpos($exists[$old_field]->COLUMN_TYPE,'unsigned') !== false ? true : false,
                        'comment'   => ''
                    ];
                    if($exists[$old_field]->COLUMN_KEY == 'PRI') {
                        if($exists[$old_field]->EXTRA == 'auto_increment') {
                            $f2['auto_increment']    = true;
                        } else {
                            $f2['auto_increment']    = false;
                        }
                    }
                    if($exists[$old_field]->COLUMN_DEFAULT != null) {
                        $f2['default']  = $exists[$old_field]->COLUMN_DEFAULT;
                    }
                    if($exists[$old_field]->COLUMN_COMMENT == 'fk') {
                        $f2['comment']  = 'fk';
                    }
                    $t              = explode('|',str_replace(['(',')'],'|',$exists[$old_field]->COLUMN_TYPE));
                    $f2['type']     = $t[0];
                    if(isset($t[1])) {
                        $f2['length']  = $t[1];
                    }

                    $down[$new_field]   = $f2;
                }
            }

            if(count($up) > 0) {
                $data       = [
                    'table'             => $table,
                    'up'                => [
                        'modify_column' => $up
                    ],
                    'down'              => [
                        'modify_column' => $down
                    ]
                ];

                $json       = json_encode($data,JSON_PRETTY_PRINT);
                $pathFile   = $this->dir.DIRECTORY_SEPARATOR.$filename;
                $handle     = fopen($pathFile, "wb");
                if($handle) {
                    fwrite ( $handle, $json );
                }
                fclose($handle);
                $oldmask = umask(0);
                chmod($pathFile, 0777);
                umask($oldmask);
                if(file_exists($pathFile)) {
                    $response = $this->migrate($filename);
                }
            }

        }

        render($response,'json');
    }

    function delete_field() {
        $access         = get_access();
        if(!$access['delete']) {
            render([
                'status'    => 'failed',
                'message'   => lang('izin_ditolak')
            ],'json'); die;
        }

        $field  = post('fields');
        $table  = post('table');

        $filename       = date('YmdHis').'m_'.$table.'.json';

        $response       = [
            'status'    => 'failed',
            'message'   => lang('data_gagal_disimpan')
        ];
        if($table && table_exists($table) && is_array($field) && count($field) > 0) {
            $fields         = db_query('SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, COLUMN_KEY, EXTRA, COLUMN_COMMENT FROM information_schema.COLUMNS WHERE TABLE_NAME="'.$table.'" AND TABLE_SCHEMA= "'.db_config('database').'"')->result();
            $exists         = [];
            foreach($fields as $f) {
                $exists[$f->COLUMN_NAME] = $f;
            }

            $del_field  = [];
            $down       = [];
            foreach($field as $f) {
                if(isset($exists[$f]) && $exists[$f]->COLUMN_KEY != 'PRI') {
                    $del_field[]    = $f;

                    $f2     = [
                        'type'      => '',
                        'null'      => $exists[$f]->IS_NULLABLE == 'NO' ? false : true,
                        'unsigned'  => strpos($exists[$f]->COLUMN_TYPE,'unsigned') !== false ? true : false,
                        'comment'   => ''
                    ];
                    if($exists[$f]->COLUMN_DEFAULT != null) {
                        $f2['default']  = $exists[$f]->COLUMN_DEFAULT;
                    }
                    if($exists[$f]->COLUMN_COMMENT == 'fk') {
                        $f2['comment']  = 'fk';
                    }
                    $t              = explode('|',str_replace(['(',')'],'|',$exists[$f]->COLUMN_TYPE));
                    $f2['type']     = $t[0];
                    if(isset($t[1])) {
                        $f2['length']  = $t[1];
                    }

                    $down[$f]       = $f2;
                }
            }

            if(count($del_field) > 0) {
                $data       = [
                    'table'             => $table,
                    'up'                => [
                        'drop_column'   => $del_field
                    ],
                    'down'              => [
                        'add_column'    => $down
                    ]
                ];

                $json       = json_encode($data,JSON_PRETTY_PRINT);
                $pathFile   = $this->dir.DIRECTORY_SEPARATOR.$filename;
                $handle     = fopen($pathFile, "wb");
                if($handle) {
                    fwrite ( $handle, $json );
                }
                fclose($handle);
                $oldmask = umask(0);
                chmod($pathFile, 0777);
                umask($oldmask);
                if(file_exists($pathFile)) {
                    $response = $this->migrate($filename);
                }
            }
        }
        render($response,'json');
    }

    private function migrate($mp='') {
        $this->load->dbforge();
        $type       = 'up';
        $exec_time  = date('Y-m-d H:i:s');

        $file       = $this->dir.DIRECTORY_SEPARATOR.$mp;
        $content    = file_get_contents($file);
        $content    = str_replace('"length"','"constraint"',$content);
        $data       = json_decode($content,true);
        $process    = false;
        if(is_array($data) && isset($data[$type]) && isset($data['table']) && trim($data['table'])) {
            if(is_array($data[$type])) {
                foreach($data[$type] as $key => $param) {
                    if($key == 'create') {

                        if(isset($param['fields']) && is_array($param['fields']) && !$this->db->table_exists($data['table'])) {
                            $fields         = [];
                            $primary_key    = '';
                            foreach($param['fields'] as $k => $v) {
                                if(isset($param['primary_key']) && $param['primary_key'] == $k) {
                                    $primary_key    = $k;
                                }
                                if($k == 'timestamp') {
                                    $fields['created_by']   = [
                                        'type'              => 'varchar',
                                        'constraint'        => '100'
                                    ];
                                    $fields['created_at']   = [
                                        'type'              => 'datetime'
                                    ];
                                    $fields['updated_by']   = [
                                        'type'              => 'varchar',
                                        'constraint'        => '100'
                                    ];
                                    $fields['updated_at']   = [
                                        'type'              => 'datetime'
                                    ];
                                } else $fields[$k]   = $v;

                                $paramTable = [];
                                if(isset($param['engine'])) {
                                    $paramTable['ENGINE']   = $param['engine'];
                                }                
                            }
                            $this->dbforge->add_field($fields);
                            if($primary_key) {
                                $this->dbforge->add_key($primary_key, TRUE);
                            }
                            $this->dbforge->create_table($data['table'],TRUE,$paramTable);
                            $process    = true;
                        }

                    } elseif($key == 'add_column') {

                        if(count($param) && $this->db->table_exists($data['table'])) {
                            $this->dbforge->add_column($data['table'],$param);
                            $process    = true;
                        }

                    } elseif($key == 'modify_column') {

                        if(count($param) && $this->db->table_exists($data['table'])) {
                            $this->dbforge->modify_column($data['table'],$param);
                            $process    = true;
                        }

                    } elseif($key == 'drop_column') {

                        if(count($param) && $this->db->table_exists($data['table'])) {
                            foreach($param as $p) {
                                $this->dbforge->drop_column($data['table'],$p);
                            }
                            $process    = true;
                        }

                    } elseif($key == 'rename_table' && is_string($param)) {

                        if(isset($data['up']['rename_table']) && is_string($data['up']['rename_table']) && trim($data['up']['rename_table'])) {
                            if($type == 'up') {
                                $this->dbforge->rename_table($data['table'],$data['up']['rename_table']);
                            } else {
                                $this->dbforge->rename_table($data['up']['rename_table'],$data['table']);
                            }
                            $process = true;
                        }

                    }
                }
            } elseif(is_string($data[$type]) && $data[$type] == 'drop' && $this->db->table_exists($data['table'])) {
                $this->dbforge->drop_table($data['table']);
                $process    = true;
            }
        }

        $response   = [
            'status'    => 'failed',
            'message'   => lang('data_gagal_disimpan')
        ];
        if($process) {
            insert_data('_migration',[
                'migration' => str_replace('.json','',$mp),
                'exec_time' => $exec_time
            ]);
            $response   = [
                'status'    => 'success',
                'message'   => lang('data_berhasil_disimpan')
            ];
        }
        
        return $response;
    }

    public function relasi() {
        $data['relasi']     = [];
        $relations          = scandir($this->dirRelation);
        foreach($relations as $r) {
            if(substr($r,-4) == 'json') {
                $data['relasi'][]   = [
                    'title'         => str_replace(['---','.json'],[' ~ ',''],$r),
                    'link'          => base_url('development/manajemen-database/form-relasi/').encode_string($r)
                ];
            }
        }
        render($data);
    }

    public function form_relasi($key="") {
        $access             = get_access();
        $data['access']     = $access;
        $data['padding']    = false;
        $data['table']      = [];
        
        $decode             = decode_string($key);
        $data['default']    = "";
        if($decode && file_exists($this->dirRelation.DIRECTORY_SEPARATOR.$decode)) {
            $default        = file_get_contents($this->dirRelation.DIRECTORY_SEPARATOR.$decode);
            $test           = json_decode($default,true);
            if(is_array($test)) {
                $data['default']    = $default;
            }
        }
        if(!$data['default']) $key  = '';
        
        $data['key']        = $key;
        
        $table              = table_lists();
        foreach($table as $k => $v) {
            if($v != '_migration') {
                $fields = db_query('SELECT COLUMN_NAME, COLUMN_KEY, COLUMN_COMMENT FROM information_schema.COLUMNS WHERE TABLE_NAME="'.$v.'" AND TABLE_SCHEMA= "'.db_config('database').'"')->result();
                $pk = $fk = $def = [];
                foreach($fields as $f) {
                    if($f->COLUMN_KEY == 'PRI')         $pk[]   = $f->COLUMN_NAME;
                    else if($f->COLUMN_COMMENT == 'fk') $fk[]   = $f->COLUMN_NAME;
                    else                                $def[]  = $f->COLUMN_NAME;
                }
                $limit_field    = 8; // batas jumlah field yang tampil supaya tidak terlalu panjang
                $jumlah         = count($pk) + count($fk);
                $jumlah_total   = $jumlah + count($def);
                foreach($def as $x => $y) {
                    if($jumlah < $limit_field) $jumlah++;
                    else unset($def[$x]);
                }
                if($jumlah_total > $limit_field) $def[]   = '...';
                $data['table'][$v] = [
                    'pk'    => implode("|",$pk),
                    'fk'    => implode("|",$fk),
                    'def'   => implode("|",$def)
                ];
            }
        }
        if(($key && $access['edit']) || (!$key && $access['input'])) {
            render($data);
        } else {
            render('403');
        }
    }

    public function save_relasi() {
        $access     = get_access();
        $data       = post('data');
        $key        = decode_string(post('key'));
        $json       = json_decode($data,true);
        $tables     = [];
        $pos        = [];
        $relations  = [];
        $process    = false;
        if(($key && $access['edit']) || (!$key && $access['input'])) {
            $process    = true;
        }
        if(!$process) {
            render([
                'status'    => 'failed',
                'message'   => lang('izin_ditolak')
            ],'json'); die;
        }
        foreach($json['operators'] as $table => $val) {
            $tables[]       = $table;
            $pos[$table]    = [$val['left'], $val['top']];
        }
        foreach($json['links'] as $l) {
            $relations[]    = $l['fromOperator'].'.'.$l['fromConnector'].' = '.$l['toOperator'].'.'.$l['toConnector'];
        }
        $result = [
            'tables'            => $tables,
            'relations'         => $relations,
            'attr_flowchart'    => $pos
        ];

        if(count($relations) > 0) {
            if($key && file_exists($this->dirRelation.DIRECTORY_SEPARATOR.$key)) {
                @unlink($this->dirRelation.DIRECTORY_SEPARATOR.$key);
            }
            $filename   = implode("---",$tables).'.json';
            $json       = json_encode($result,JSON_PRETTY_PRINT);
            $pathFile   = $this->dirRelation.DIRECTORY_SEPARATOR.$filename;
            $handle     = fopen($pathFile, "wb");
            if($handle) {
                fwrite ( $handle, $json );
            }
            fclose($handle);
            $oldmask = umask(0);
            chmod($pathFile, 0777);
            umask($oldmask);
            if(file_exists($pathFile)) {
                render([
                    'status'    => 'success',
                    'message'   => lang('data_berhasil_disimpan')
                ],'json');
            } else {
                render([
                    'status'    => 'failed',
                    'message'   => lang('data_gagal_disimpan')
                ],'json');    
            }
        } else {
            render([
                'status'    => 'failed',
                'message'   => lang('data_gagal_disimpan')
            ],'json');
        }
    }

    public function delete_relasi() {
        $access     = get_access();
        if($access['delete']) {
            $key        = decode_string(post('key'));
            if($key && file_exists($this->dirRelation.DIRECTORY_SEPARATOR.$key)) {
                if(@unlink($this->dirRelation.DIRECTORY_SEPARATOR.$key)) {
                    render([
                        'status'    => 'success',
                        'message'   => lang('data_berhasil_dihapus')
                    ],'json');
                } else {
                    render([
                        'status'    => 'failed',
                        'message'   => lang('data_gagal_dihapus')
                    ],'json');
                }
            } else {
                render([
                    'status'    => 'failed',
                    'message'   => lang('data_gagal_dihapus')
                ],'json');
            }
        } else {
            render([
                'status'    => 'failed',
                'message'   => lang('izin_ditolak')
            ],'json');
        }
    }

    public function save_rules() {
        if(!is_dir($this->dirRules)){
            $oldmask = umask(0);
            mkdir($this->dirRules,0777);
            umask($oldmask);
        }

        $table      = post('table');
        $field      = post('field');
        $validation = post('validation');
        $path       = post('dir');

        if(table_exists($table) && is_array($field)) {
            $dt     = [];
            foreach($field as $k => $v) {
                if($validation[$k]) {
                    if($path[$k]) {
                        $dt[$field[$k]]['validation']   = str_replace(',','|',$validation[$k]);
                    } else {
                        $dt[$field[$k]] = str_replace(',','|',$validation[$k]);
                    }
                }
                if($path[$k]) {
                    $dt[$field[$k]]['path'] = $path[$k];
                }
            }

            if(count($dt) > 0) {
                $filename   = $this->dirRules . DIRECTORY_SEPARATOR . $table . '.txt';
                $content    = serialize($dt);
                $handle     = fopen($filename, "wb");
                if($handle) {
                    fwrite ( $handle, $content );
                }
                fclose($handle);
                $oldmask = umask(0);
                chmod($filename, 0777);
                umask($oldmask);
            }
        }

        render([
            'status'    => 'success',
            'message'   => lang('data_berhasil_disimpan')
        ],'json');
    }

    public function get_form() {
        $table  = get('r');

        $data   = [
            'field' => [],
            'ref'   => []
        ];
        $filename   = $this->dirPages . DIRECTORY_SEPARATOR . 'form.' . $table . '.txt';
        if(file_exists($filename)) {
            $dt     = @unserialize(file_get_contents($filename));
            if(is_array($dt) && isset($dt['attribute'])) {
                $data['field']  = $dt['attribute'];
                foreach($dt['attribute'] as $d) {
                    if($d['type'] == 'select' && $d['ref'] && table_exists($d['ref'])) {
                        $data['ref'][$d['ref']] = get_fields($d['ref'],'name');
                    }
                }
            }
        }

        render($data,'json');
    }

    public function get_field() {
        $data   = get_fields(get('t'),'name');
        render($data,'json');
    }

    public function save_form() {
        if(!is_dir($this->dirPages)){
            $oldmask = umask(0);
            mkdir($this->dirPages,0777);
            umask($oldmask);
        }

        $table      = post('cur_table');
        $field      = post('field');
        $label      = post('label');
        $show_table = post('show_table');
        $type       = post('tipe');
        $ref        = post('table');
        $refValue   = post('opt_value');
        $refLabel   = post('opt_label');
        $refTags    = post('opt_tags');
        $imgWidth   = post('img_width');
        $imgHeight  = post('img_height');
        $imgCrop    = post('img_crop');

        if(is_array($field)) {
            $dt     = [];
            foreach($field as $k => $v) {
                $dt[$field[$k]]     = [
                    'label'         => $label[$k],
                    'showOnTable'   => isset($show_table[$k]) && $show_table[$k],
                    'type'          => $type[$k],
                ];
                if($type[$k] == 'select' && $ref[$k] == 'manual') {
                    $dt[$field[$k]]['ref']      = $ref[$k];
                    $dt[$field[$k]]['refData']  = $refTags[$k];
                } elseif($type[$k] == 'select' && $ref[$k] && $refValue[$k] && $refLabel[$k]) {
                    $dt[$field[$k]]['ref']      = $ref[$k];
                    $dt[$field[$k]]['refValue'] = $refValue[$k];
                    $dt[$field[$k]]['refLabel'] = $refLabel[$k];
                } elseif($type[$k] == 'imageupload' && $imgWidth[$k] && $imgHeight[$k]) {
                    $dt[$field[$k]]['imgWidth']     = $imgWidth[$k];
                    $dt[$field[$k]]['imgHeight']    = $imgHeight[$k];
                    $dt[$field[$k]]['imgCrop']      = isset($imgCrop[$k]) && $imgCrop[$k];
                }
            }
            if(count($dt) > 0) {
                $filename   = $this->dirPages . DIRECTORY_SEPARATOR . 'form.' . $table . '.txt';
                $content    = serialize([
                    'key'       => encode_string($table, BUILDER_KEY),
                    'attribute' => $dt
                ]);
                $handle     = fopen($filename, "wb");
                if($handle) {
                    fwrite ( $handle, $content );
                }
                fclose($handle);
                $oldmask = umask(0);
                chmod($filename, 0777);
                umask($oldmask);
            }
        }
        render([
            'status'    => 'success',
            'message'   => lang('data_berhasil_disimpan')
        ],'json');
    }

    public function sql() {
        $data['table']              = table_lists();
        foreach($data['table'] as $ktable => $vtable) {
            if($vtable == '_migration') unset($data['table'][$ktable]);
        }
        render($data);
    }

    public function query() {
        $response   = [
            'status'    => 'failed',
            'message'   => lang('terjadi_kesalahan')
        ];

        $query      = str_replace(';','',trim(post('query')));
        $tesQuery   = explode(' ',strtolower($query));
        $command    = $tesQuery[0];

        if(in_array($command, ['select','insert','update','delete','truncate'])) {
            if($command == 'select') {
                $lQuery = strtolower($query);
                if(stripos($lQuery,'limit') === false) {
                    $query .= ' LIMIT 100';
                } else {
                    $q = substr($lQuery,stripos($lQuery,'limit'));
                    $e = explode(' ',$q);
                    if(count($e) >= 2) {
                        if($e[1] > 100) {
                            $query = str_ireplace('limit '.$e[1],'LIMIT 100',$query);
                        }
                    }
                }
                $q  = db_query($query)->result();
                $response['status']     = 'success';
                $response['message']    = count($q) ? 'OK' : lang('data_tidak_ditemukan');
                if(count($q) > 0) {
                    $response['data']   = $q;
                }
            } else {
                $idxTable       = 0;
                $primaryTable   = ['_migration','tbl_informasi','tbl_kode','tbl_menu','tbl_notifikasi','tbl_setting','tbl_user','tbl_user_akses','tbl_user_group','tbl_user_log','tbl_user_password'];
                if(in_array($command,['insert','delete'])) $idxTable = 2;
                else if(in_array($command,['update','truncate'])) $idxTable = 1;
                if(isset($tesQuery[$idxTable])) {
                    if(!in_array($tesQuery[$idxTable],$primaryTable)) {
                        $q  = db_query($query);
                        if($q) {
                            $response['status']     = 'success';
                            $response['message']    = lang('perintah_berhasil_diproses','',[strtoupper($command)]);
                        }
                    } else {
                        $response['message']    = lang('tabel_tidak_diizinkan_untuk_dimanipulasi','',[$tesQuery[$idxTable]]);
                    }
                }
            }
        } else {
            $response['message']    = lang('perintah_tidak_valid');
        }

        render($response,'json');
    }

}