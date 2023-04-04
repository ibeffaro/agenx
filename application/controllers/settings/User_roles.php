<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_roles extends BE_Controller {

    public function index() {
        $data   = [];
        if(user('id_group') == 1) {
            $data['menu'][0] = get_data('menu',[
                'where'     => "parent_id = 0 AND is_active = 1",
                'order_by'  => "urutan"
            ])->result_array();
            foreach($data['menu'][0] as $m0) {
                $data['menu'][$m0['id']] = get_data('menu',[
                    'where'     => "parent_id = {$m0['id']} AND is_active = 1",
                    'order_by'  => "urutan"
                ])->result_array();
                foreach($data['menu'][$m0['id']] as $m1) {
                    $data['menu'][$m1['id']] = get_data('menu',[
                        'where'     => "parent_id = {$m1['id']} AND is_active = 1",
                        'order_by'  => "urutan"
                    ])->result_array();
                }
            }
        } else {
            $data       = menu();
        }
        render($data);
    }

    public function data() {
        $data = generate_data();
        render($data,'json');
    }

    public function get_data() {
        $data           = get_data('user_group',get())->row();
        $data->akses    = get_data('user_akses','id_group',get('id'))->result();
        render($data,'json');
    }

    function save() {
        $data       = post();
        $id_menu    = post('id_menu');
        $view       = post('view');
        $input      = post('input');
        $edit       = post('edit');
        $delete     = post('delete');
        $additional = post('additional');

        $response   = save_data('user_group',$data);
        if($response['status'] == 'success' && is_array($id_menu)) {
            $listIdMenu = [];
            $accessMenu = [];
            foreach($id_menu as $m) {
                $listIdMenu[]       = $m;
                $data = [
                    'id_menu'		=> $m,
                    'id_group'		=> $response['data']['id'],
                    '_view'		    => isset($view[$m])     ? $view[$m]     : 0,
                    '_input'	    => isset($input[$m])    ? $input[$m]    : 0,
                    '_edit'         => isset($edit[$m])     ? $edit[$m]     : 0,
                    '_delete'       => isset($delete[$m])   ? $delete[$m]   : 0,
                    '_additional'   => isset($additional[$m]) && is_array($additional[$m]) ? json_encode($additional[$m]) : '[]',
                ];
                $accessMenu[$m]     = $data['_view'];
                $check = get_data('user_akses',[
                    'where'         => [
                        'id_menu'   => $m,
                        'id_group'  => $data['id_group']
                    ]
                ])->row();
                if(isset($check->id)) {
                    update_data('user_akses',$data,[
                        'id_menu'   => $m,
                        'id_group'  => $data['id_group']
                    ]);
                } else {
                    insert_data('user_akses',$data);
                }
            }
            if(count($listIdMenu)) {
                $c  = get_data('menu',[
                    'where' => [
                        'id !='     => $listIdMenu,
                        'is_active' => 1
                    ]
                ])->result();
                foreach($c as $b) {
                    $child  = get_data('menu',[
                        'where' => [
                            "(level1 = {$b->id} OR level2 = {$b->id})",
                            'is_active' => 1
                        ]
                    ])->result();
                    $countChild = 0;
                    foreach($child as $ch) {
                        if(isset($accessMenu[$ch->id]) && $accessMenu[$ch->id]) {
                            $countChild++;
                        }
                    }
                    $dt = [
                        'id_menu'       => $b->id,
                        'id_group'		=> $response['data']['id'],
                        '_view'		    => $countChild > 0  ? 1 : 0,
                        '_input'	    => 0,
                        '_edit'         => 0,
                        '_delete'       => 0,
                        '_additional'   => '[]',
                    ];
                    $check = get_data('user_akses',[
                        'where'         => [
                            'id_menu'   => $b->id,
                            'id_group'  => $dt['id_group']
                        ]
                    ])->row();
                    if(isset($check->id)) {
                        update_data('user_akses',$dt,[
                            'id_menu'   => $b->id,
                            'id_group'  => $dt['id_group']
                        ]);
                    } else {
                        insert_data('user_akses',$dt);
                    }
    
                }
            }
        }
        render($response,'json');
    }

	function delete() {
		$response 	= destroy_data('user_group',get(),'',['id_group'=>'user_akses']);
		render($response,'json');
	}
}