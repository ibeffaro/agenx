<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Menu extends BE_Controller {

    public function index() {
        $data['ref']    = [];
        if(is_dir(SCPATH . 'pages')) {
            $ref            = scandir(SCPATH . 'pages');
            foreach($ref as $r) {
                if(strlen($r) > 5) {
                    $x      = explode('.',$r);
                    $clear  = str_replace('.'.$x[count($x)-1],'',$r);
                    $data['ref'][]  = [
                        'value'     => $r,
                        'label'     => str_replace('.',' ',$clear)
                    ];
                }
            }
        }
        render($data);
    }

    public function data() {
        $rec['menu'][0]    = get_data('menu',[
            'where'     => 'parent_id = 0',
            'order_by'  => 'urutan'
        ])->result_array();
        foreach($rec['menu'][0] as $m1) {
            $rec['menu'][$m1['id']]    = get_data('menu',[
                'where'     => 'parent_id = '.$m1['id'],
                'order_by'  => 'urutan'
            ])->result_array();
            foreach($rec['menu'][$m1['id']] as $m2) {
                $rec['menu'][$m2['id']]    = get_data('menu',[
                    'where'     => 'parent_id = '.$m2['id'],
                    'order_by'  => 'urutan'
                ])->result_array();    
            }
        }
        $rec['access']  = get_access();
        $data['html']   = view($rec,true);
        render($data,'json');
    }

    public function get_data() {
        $data           = get_data('menu',get())->row();
        $x_target       = explode('/',$data->target);
        $data->target   = end($x_target);
        render($data,'json');
    }

    function save() {
        $data           = post();
        $data['target'] = str_replace(['_',' '],['-',''],$data['target']);
        $data['level1'] = 0;
        $data['level2'] = 0;
        $data['level3'] = 0;
        if($data['parent_id'] != 0) {
            $parent     = get_data('menu','id',$data['parent_id'])->row();
            if(isset($parent->id)) {
                $data['target'] = explode('/',$parent->target)[0] . '/' . $data['target'];
                $data['level1'] = $parent->level1;
                $data['level2'] = $parent->level2;
                $data['level3'] = $parent->level3;
            }
        }
        $response   = save_data('menu',$data);
        if($response['status'] == 'success') {
            $dt     = ['id' => $response['data']['id']];
            if(!isset($parent)) $dt['level1']       = $dt['id'];
            else {
                if(!$parent->level2) $dt['level2']  = $dt['id'];
                else $dt['level3']                  = $dt['id'];
            }
            update_data('menu',$dt,'id',$dt['id']);
        }
        render($response,'json');
    }

    function delete() {
        $response   = destroy_data('menu',get(),'',[
			'level1'	=> 'menu',
			'level2'	=> 'menu',
			'id_menu'	=> 'user_akses'
        ]);
        render($response,'json');
    }

    function get_sort() {
        $rec['menu']    = [
            'top'       => [],
            'bottom'    => []
        ];
        foreach(['top','bottom'] as $b) {
            $urutan         = $b == 'top' ? '< 100' : '>= 100';
            $rec['menu'][$b][0]     = get_data('menu',[
                'where'     => 'urutan '.$urutan.' AND parent_id = 0',
                'order_by'  => 'urutan'
            ])->result_array();
            foreach($rec['menu'][$b][0] as $m1) {
                $rec['menu'][$b][$m1['id']]    = get_data('menu',[
                    'where'     => 'parent_id = '.$m1['id'],
                    'order_by'  => 'urutan'
                ])->result_array();
                foreach($rec['menu'][$b][$m1['id']] as $m2) {
                    $rec['menu'][$b][$m2['id']]    = get_data('menu',[
                        'where'     => 'parent_id = '.$m2['id'],
                        'order_by'  => 'urutan'
                    ])->result_array();    
                }
            }
        }

        render($rec,'layout:false');
    }

    function save_sort() {
        $data = post('menuItem');
        update_data('menu',['urutan'=>0]);
        foreach($data as $id => $parent_id) {
            if(!in_array($id,['top','bottom'])) {
                $parent_orig    = $parent_id;
                if(in_array($parent_id,['top','bottom'])) $parent_id = 0;

                $where          = [
                    'parent_id' => $parent_id
                ];
                if($parent_orig == 'bottom') {
                    $where['urutan >=']  = 100;
                }
                $get_urutan	= get_data('menu',[
                    'select'	=> 'MAX(urutan) urutan',
                    'where'		=> $where
                ])->row();
                $urutan 	= $get_urutan->urutan ? $get_urutan->urutan + 1 : 1;
                if($parent_orig == 'bottom' && $urutan < 100) {
                    $urutan += 100;
                }
                $save 		= update_data('menu',['parent_id'=>$parent_id,'urutan'=>$urutan],'id',$id);
                if($save) {
                    $mn = get_data('menu','id',$id)->row_array();
                    if($mn['parent_id'] == 0) {
                        update_data('menu',array('level1'=>$mn['id']),'id',$mn['id']);
                    } else {
                        $parent = get_data('menu','id',$mn['parent_id'])->row_array();
                        $data_update = array(
                            'level1' => $parent['level1'],
                            'level2' => $parent['level2'],
                            'level3' => $parent['level3']
                        );
                        if(!$parent['level2']) $data_update['level2'] = $mn['id'];
                        else if(!$parent['level3']) $data_update['level3'] = $mn['id'];
                        update_data('menu',$data_update,'id',$mn['id']);					
                    }
                }
            }
        }
        render([
            'status'	=> 'success',
            'message'	=> lang('data_berhasil_diperbarui')
        ],'json');
    }
}