<?php defined('BASEPATH') OR exit('No direct script access allowed');

function render($data=[],$output='',$forceAccess=false) {
    if(!setting('load_page')) {
        $CI         = get_instance();
        $CI->load->helper('menu');
        $CI->config->set_item('setting_load_page',true);
        if(is_object($data)) $data  = (array) $data;
        if(is_array($data) || in_array($data,['403','404','maintenance_mode', 'err'])) {
            $init_page = '';
            if(!is_array($data)) {
                $init_page  = $data;
                $data       = [];
            }
            $f_segment  = $CI->uri->segment(1);
            $class      = $CI->router->fetch_class();
            $method     = $CI->router->fetch_method();

            if(!file_exists(FCPATH . 'application/controllers/'.$f_segment)) {
                $f_segment  = '';
            }

            $view       = $f_segment == $class ? str_replace('-','_',$class) . '/' . $method : str_replace('-','_',$f_segment) . '/' . $class . '/' . $method;
            $str_view   = $f_segment == $class ? str_replace('-','_',$class) . '_' . $method : str_replace('-','_',$f_segment) . '_' . $class . '_' . $method;

            if($method == 'index' && !file_exists(FCPATH . 'application/views/' . $view .'.php')) {
                $view       = $f_segment == $class ? str_replace('-','_',$class) . '/' . $method : str_replace('-','_',$f_segment) . '/' . $class;
                $str_view   = $f_segment == $class ? str_replace('-','_',$class) . '_' . $method : str_replace('-','_',$f_segment) . '_' . $class;
            }

            if(strtolower($output) == 'json') {
                header('Content-Type: application/json');
                if(function_exists('info_data_menu')) {
                    $badge              = info_data_menu();
                    if(is_array($badge) && count($badge)) {
                        $data['__badge']    = $badge;
                    }
                }
                echo json_encode($data);
            } elseif(strtolower($output) == 'pdf') {
                $view                   .= '.pdf.php';
                $data['view_content']   = $CI->load->view($view,$data,true);
                $html                   = $CI->load->view('layout/pdf',$data,true);
                $html                   = preg_replace('/>\s+</', '><', $html);
                $title                  = 'pdf_'.date('YmdHis');
                $arr_orientation        = ['portrait','landscape'];
                $arr_size_paper         = ["4a0","2a0","a0","a1","a2","a3","a4","a5","a6","a7","a8","a9","a10","b0","b1","b2","b3","b4","b5","b6","b7","b8","b9","b10","c0","c1","c2","c3","c4","c5","c6","c7","c8","c9","c10","ra0","ra1","ra2","ra3","ra4","sra0","sra1","sra2","sra3","sra4","letter","legal","ledger","tabloid","executive","folio","commercial #10 envelope","catalog #10 1/2 envelope","8.5x11","8.5x14","11x17"];
                $pdf_orientation        = isset($data['pdf_orientation']) && in_array(strtolower($data['pdf_orientation']),$arr_orientation) ? strtolower($data['pdf_orientation']) : 'portrait';
                $pdf_letter_size        = 'a4';
                if(isset($data['pdf_size'])) {
                    if(in_array(strtolower($data['pdf_size']),$arr_size_paper)) {
                        $pdf_letter_size    = strtolower($data['pdf_size']);
                    } else {
                        $x_size         = explode('x',strtolower($data['pdf_size']));
                        if(count($x_size) == 2 && is_numeric(trim($x_size[0])) && is_numeric(trim($x_size[1]))) {
                            // size diset default dalam cm, 1cm = 28.3465pt
                            $width_size         = $x_size[0] * 28.3465;
                            $height_size        = $x_size[1] * 28.3465;
                            $pdf_letter_size    = [0, 0, $width_size, $height_size];
                        }
                    }
                }
                $CI->load->library('pdf');
                $CI->pdf->generate($html,$title,true,$pdf_letter_size,$pdf_orientation);
            } elseif(in_array(strtolower($output),['xls','xlsx','excel'])) {
                $filename               = '';
                $background             = '417ff9';
                $border_color           = '3269d8';
                $color                  = 'ffffff';
                if(setting('custom_color_primary')) {
                    $rgb                = hexToRgb(setting('color_primary'),0.25);
                    $background         = str_replace('#','',setting('color_primary'));
                    if(getBrightness(setting('color_primary')) == 'light') {
                        $border_color   = str_replace('#','',adjustBrightness(setting('color_primary'),-20));
                        $color          = '000000';
                    } else {
                        $border_color   = str_replace('#','',adjustBrightness(setting('color_primary'),-30));
                    }
                }

    
                if(isset($data['filename'])) {
                    $filename   = $data['filename'];
                    unset($data['filename']);
                }
                $CI->load->library('simpleexcel');
                $CI->simpleexcel->write($data,$filename,[
                    'background'    => $background,
                    'border_color'  => $border_color,
                    'color'         => $color
                ]);
            } else {
                $show_act_header    = isset($data['action_header']) && $data['action_header'] == false ? false : true;
                $menu               = menu();
                if($menu['active']['id']) {
                    $check_access   = get_data('user_akses',[
                        'where'     => [
                            'id_group'  => user('id_group'),
                            'id_menu'   => $menu['active']['id'],
                            '_view'     => 1
                        ]
                    ])->row();
                    if(!isset($check_access->id) && !$forceAccess) $init_page    = 403;
                }
    
                $CI->load->library('asset');
                $layout                 = 'default';
                if($output) {
                    $attr_output = explode(' ', $output);
                    foreach($attr_output as $av) {
                        $attr_av = explode(':', $av);
                        if(count($attr_av) == 2) {
                            if($attr_av[0] == 'view') {
                                $view       = $attr_av[1];
                                $str_view   = str_replace('/', '_', $view);
                            }
                            else if($attr_av[0] == 'layout') $layout = $attr_av[1];
                        }
                    }
                }
                if(strtolower($output) == 'error') {
                    $init_page  = 'err';
                }
                if($layout == 'default' && setting('default_layout')) {
                    $layout     = setting('default_layout');
                }
                if($init_page) {
                    $error_code = in_array($init_page,[404,403]) ? $init_page : 400;
                    if($init_page == 'maintenance_mode') $error_code = 200;
                    $CI->output->set_status_header($error_code);
                    $layout             = false;
                    $view               = 'errors/error_all';
                    $data['error_code'] = $init_page;
                    $data['title']      = '';
                    $data['image']      = '';
                    $data['message']    = !isset($data['message']) ? lang($init_page.'_desc') : $data['message'];
                    switch($init_page) {
                        case "403" : 
                            $data['title']      = lang('dilarang');
                            $data['image']      = '403';
                            break;
                        case "404" :
                            $data['title']      = lang('halaman_tidak_ditemukan');
                            $data['image']      = '404';
                            break;
                        case "maintenance_mode" :
                            $data['error_code'] = '';
                            $data['title']      = lang('dalam_masa_pemeliharaan');
                            $data['image']      = 'maintenance';
                            break;
                        default : 
                            $data['error_code'] = "Err";
                            $data['image']      = 'error';
                            $data['title']      = lang('kesalahan');
                    }
                    update_data('user_log',['respon'=>$data['error_code']],'id',setting('last_id_log'));
                }
                $data['__js']           = '';
                $data['__css']          = '';
                $data['uri_string']	    = $CI->uri->uri_string();
                $data['__theme']        = get_cookie('app-theme');
                $__token                = generate_token();
                $data['__app_token']    = $__token[0];
                $data['__client_token'] = $__token[1];
                $data['__lang']         = [];
                $list_lang              = scandir(FCPATH . 'assets/lang');
                foreach($list_lang as $ll) {
                    if(!in_array($ll,['..','.'])) {
                        $x_ll               = explode('_',$ll,2);
                        if(count($x_ll) == 2) {
                            $data['__lang'][]   = [
                                'id'            => $ll,
                                'code'          => $x_ll[0],
                                'label'         => ucwords(str_replace('_',' ',$x_ll[1]))
                            ];
                        }
                    }
                }
                $custom_css             = '';
                if(setting('font_type') != 'roboto') {
                    $custom_css         .= '--app-font-family:"'.ucwords(str_replace(['.','-'],' ',setting('font_type'))).'",Roboto,"Helvetica Neue",Helvetica,Arial,sans-serif;';
                }
                if(setting('font_size') && setting('font_size') > 12 && setting('font_size') <= 15) {
                    $custom_css         .= '--app-font-size:' . setting('font_size').'px;--app-font-size-int:'.setting('font_size').';';
                }
                if(setting('border_radius') >= 0 && setting('border-radius') <= 16) {
                    $custom_css         .= '--app-border-radius:' . setting('border_radius').'px;';
                }
                if(setting('custom_color_primary')) {
                    $rgb                = hexToRgb(setting('color_primary'),0.25);
                    $custom_css         .= '--app-color:' . setting('color_primary').';';
                    $custom_css         .= "--app-color-transparent: {$rgb};";
                    $custom_css         .= '--app-color-light:' . adjustBrightness(setting('color_primary'),110).';';
                    if(getBrightness(setting('color_primary')) == 'light') {
                        $custom_css         .= '--app-color-dark:' . adjustBrightness(setting('color_primary'),-20).';';
                        $custom_css         .= '--app-color-dark2:' . adjustBrightness(setting('color_primary'),-30).';';
                        $custom_css         .= '--app-color-text:' . '#333537;';
                    } else {
                        $custom_css         .= '--app-color-dark:' . adjustBrightness(setting('color_primary'),-30).';';
                        $custom_css         .= '--app-color-dark2:' . adjustBrightness(setting('color_primary'),-40).';';
                        $custom_css         .= '--app-color-text:' . '#fff;';
                    }
                }
                $_custom_css    = '';
                if($custom_css) {
                    $_custom_css    .= ':root{'.$custom_css.'}';
                }
                $data['__custom_css']   = $_custom_css ? '<style type="text/css">'.$_custom_css.'</style>' : '';
                if($layout && $layout != 'false') {
                    $data['__menu']         = $menu['menu'];
                    $data['__active']       = $menu['active'];
                    if(!isset($data['title']) || !$data['title']) {
                        $data['title']      = isset($menu['title']) ? $menu['title'] : setting('title');
                    }
                    if(!isset($data['sub_title']) || !$data['sub_title']) {
                        $data['sub_title']  = isset($menu['subtitle']) ? $menu['subtitle'] : '';
                    }
                    $data['__cur_uri']      = $menu['uri'];
                    if(!$data['__cur_uri'] && substr($CI->uri->uri_string(),0,7) == 'account') {
                        $data['__cur_uri']  = 'account';
                    }
                    $data['access']         = get_access();
                    $content                = preg_replace('/<!--(.|\s)*?-->/', '',$CI->load->view($view,$data,true));
                    if(isset($data['access_input'])) $data['access']['input'] = $data['access_input'];

                    $data['type_module']    = '';
                    if($layout == 'default' && setting('pos_account_notif') == 'header' && isset($menu['menu'][0])) {
                        if(count($menu['menu'][0]) <= 1) {
                            $data['type_module']    = ' single-module';
                        } elseif(count($menu['menu'][0]) <= 5) {
                            $data['type_module']    = ' vertical-tab-module';
                        }
                    }

                    $additional_button      = [];
                    preg_match_all('/<action-header-additional.*?>(.*?)<\/action-header-additional>/si', $content, $act_header_add);
                    if(count($act_header_add[1]) > 0) {
                        $data['action_header']          = '';
                        $ii = 0;
                        foreach($act_header_add[1] as $k_act => $v_act) {
                            preg_match_all('/<action (.*?)\/>/si', $v_act, $act_attr);
                            if(count($act_attr[1]) > 0) {
                                $str_attr       = end($act_attr[1]);
                                $act_attr       = explode(' ',$str_attr);
                                $new_act_attr   = [];
                                $i              = 0;
                                $open_attr      = false;
                                foreach($act_attr as $l) {
                                    if(!$open_attr) {
                                        $new_act_attr[$i]  = $l;
                                    } else {
                                        $new_act_attr[$i]  .= ' '.$l;
                                    }
                                    if(!$open_attr && strpos($l,'="') !== false && count(explode('"',$l)) == 2) $open_attr = true;
                                    else if($open_attr && strpos($l,'"') !== false) $open_attr   = false;
                                    if(!$open_attr) $i++;
                                }
                                foreach($new_act_attr as $l) {
                                    if(trim($l)) {
                                        $attr_param = explode('=',$l);
                                        $attr       = trim($attr_param[0]);
                                        $val        = isset($attr_param[1]) ? trim(trim($attr_param[1]),'"') : '';
                                        $additional_button[$ii][$attr] = $val;
                                    }
                                }
                                $ii++;
                            }
                        }
                    }

                    $action_param               = isset($data['action_key']) ? $data['action_key'] : '';
                    $data['action_header']      = access_button($data['access'],'default',$additional_button,$action_param);
                    preg_match_all('/<action-header.*?>(.*?)<\/action-header>/si', $content, $act_header);
                    if(count($act_header[1]) > 0) {
                        $data['action_header']          = '';
                        foreach($act_header[1] as $k_act => $v_act) {
                            $data['action_header']      .= trim($v_act);
                        }
                    }
                    $CI->config->set_item('setting_action_header',$data['action_header']);

                    $data['__content']      = define_custom_tag(
                                                clear_custom_tag(
                                                    clear_js(
                                                        clear_css($content)
                                                    )
                                                )
                                            , $data);

                    if(file_exists(FCPATH . 'application/views/layout/print_header.php')) {
                        $data['__print_header'] = $CI->load->view('layout/print_header',[],true);
                    }

                    $data['__notif_lists']              = get_data('notifikasi',[
                        'where'                         => [
                            'id_user'                   => user('id')
                        ],
                        'order_by'                      => 'notif_date',
                        'order'                         => 'desc',
                        'limit'                         => 5
                    ])->result();
                    $count_notif                        = get_data('notifikasi',[
                        'select'                        => 'COUNT(id) AS c',
                        'where'                         => [
                            'id_user'                   => user('id'),
                            'is_read'                   => 0
                        ]
                    ])->row();
                    $CI->load->library('user_agent');
                    $data['__badge']                    = [];
                    if(function_exists('info_data_menu')) {
                        $data['__badge']                = info_data_menu();
                    }
                    $data['__device']                   = $CI->agent->is_mobile() ? 'mobile' : 'desktop';
                    $data['__notif_count']              = (int) $count_notif->c;
                    $right_panel                        = right_panel($content,$data);
                    $data['action_header']              = $show_act_header ? define_custom_tag($data['action_header'],$data) : '';
                    $data['__right_panel']              = define_custom_tag($right_panel['html'],$data);
                    $data['__right_panel_toggle_icon']  = $right_panel['icon'];
                    $data['__right_panel_title']        = $right_panel['title'];
                    $data['__css']                      = render_css($content,$str_view);
                    $data['__js']                       = render_js($content,$str_view);
                    if(strpos($data['__content'],'input-icon') !== false && strpos($data['__js'],'jquery.iconpicker') === false) {
                        $data['__js']                   = '<script type="text/javascript" src="' . asset_url('js/jquery.iconpicker.js') . '"></script>' . "\n" . $data['__js'];
                    }
                    $data['file_upload_max_size']       = file_upload_max_size();
                    $CI->load->view('layout/'.$layout,$data);
                } else {
                    if(isset($data['error_code'])) {
                        $CI->load->view($view,$data);
                    } else {
                        $content = $CI->load->view($view,$data,true);
                        echo define_custom_tag(
                            clear_custom_tag(
                                clear_js(
                                    clear_css($content)
                                )
                            )
                        , $data);
                    }
                }
            }
        } else {
            header('Content-Type: text/plain');
            echo $data;
        }
    }
}

function view($data=[],$return = false) {
    $CI         = get_instance();
    $f_segment  = $CI->uri->segment(1);
    $class      = $CI->router->fetch_class();
    $method     = $CI->router->fetch_method();

    $view       = $f_segment == $class ? $class . '/' . $method : $f_segment . '/' . $class . '/' . $method;

    if($method == 'index' && !file_exists(FCPATH . 'application/views/' . $view . '.php')) {
        $view       = $f_segment == $class ? $class . '/' . $method : $f_segment . '/' . $class;
    }

    if(is_object($data)) $data  = (array) $data;
    $html       =  $CI->load->view($view,$data,true);
    $html       = preg_replace('/\s+/', ' ', define_custom_tag(
                    clear_custom_tag(
                        clear_js(
                            clear_css($html)
                        )
                    )
                , $data));
    $res        = str_replace('> <','><',$html);
    if($return) return $res;
    else echo $res;
}

function menu() {
    $CI                     = get_instance();
    $menu                   = [];
    if(user('id')) {
        $menu[0]                = get_menu( 'user_akses', 'menu', (int) user('id_group') );
        foreach( $menu[0] as $km => $m ){
            if(ENVIRONMENT == 'production' && $m['dev_only']) {
                unset($menu[0][$km]);
            } else {
                $menu[$m['id']]     = get_menu( 'user_akses', 'menu', (int) user('id_group') , $m['id'] );
                foreach($menu[$m['id']] as $s) {
                    $menu[$s['id']] = get_menu( 'user_akses', 'menu', (int) user('id_group') , $s['id'] );
                }
            }
        }
        $target_menu            = $CI->uri->segment(1);
        if($CI->uri->segment(2)) $target_menu   .= '/'.$CI->uri->segment(2);
        $target_menu            = str_replace('_','-',$target_menu);
        $curent_menu            = get_data('menu','target',$target_menu)->row();
    }
    return [
        'menu'              => $menu,
        'title'             => isset($curent_menu->id) ? lang('_'.strtolower(str_replace([' ','/'],'_',$curent_menu->target)),$curent_menu->nama) : setting('title'),
        'subtitle'          => isset($curent_menu->id) ? trim(lang('_subtitle_'.strtolower(str_replace([' ','/'],'_',$curent_menu->target)),$curent_menu->deskripsi.' ')) : '' ,
        'uri'               => isset($curent_menu->id) ? $curent_menu->target : '',
        'active'            => [
            'id'            => isset($curent_menu->id) ? $curent_menu->id : 0,
            'l1'            => isset($curent_menu->id) ? $curent_menu->level1 : 0,
            'l2'            => isset($curent_menu->id) ? $curent_menu->level2 : 0,
            'l3'            => isset($curent_menu->id) ? $curent_menu->level3 : 0
        ]
    ];
}

function get_access($target='') {
    $CI                 = get_instance();
    $menu               = [];
    if(user('id')) {
        if(!$target) {
            $target     = $CI->uri->segment(1);
            if($CI->uri->segment(2)) $target   .= '/'.$CI->uri->segment(2);
        }
        $target         = str_replace('_','-',$target);
        $parsing_target = explode('/',$target);
        if(count($parsing_target) > 2) {
            $target     = $parsing_target[0].'/'.$parsing_target[1];
        }
        $menu           = get_data('menu',[
            'where'     => [
                'target'    => $target,
                'is_active' => 1
            ]
        ])->row();
        if(!isset($menu->id)) {
            $menu       = get_data('menu',[
                'where' => [
                    'target'    => isset($parsing_target[0]) ? $parsing_target[0] : '',
                    'is_active' => 1
                ]
            ])->row();
        }
    }
    $access         = [
        'menu'      => '',
        'target'    => $target,
        'view'      => 0,
        'input'     => 0,
        'edit'      => 0,
        'delete'    => 0
    ];
    if(isset($menu->id)) {
        $roles =  get_data('user_akses',[
            'where' => [
                'id_menu'   => $menu->id,
                'id_group'  => user('id_group')
            ]
        ])->row();
        if(isset($roles->id)) {
            $access['menu']     = $menu->nama;
            $access['target']   = $menu->target;
            if($roles->_view)   $access['view']     = 1;
            if($roles->_input)  $access['input']    = 1;
            if($roles->_edit)   $access['edit']     = 1;
            if($roles->_delete) $access['delete']   = 1;
            $additional         = json_decode($roles->_additional, true);
            if(is_array($additional)) {
                foreach($additional as $ka => $va) {
                    $access[$ka]    = $va;
                }
            }
        }
    }
    return $access;
}

function access_button($access=[],$appLink='default',$btn_additional=[],$action_key='') {
    $accessButton       = '';
    $define             = [
        'input'         => [
            'class'     => 'btn-input',
            'icon'      => 'fa-plus',
            'label'     => lang('tambah')
        ],
        'import'        => [
            'class'     => 'btn-import',
            'icon'      => 'fa-upload',
            'label'     => lang('import')
        ],
        'template_import'   => [
            'class'     => 'btn-template-import',
            'icon'      => 'fa-file-alt',
            'label'     => lang('templat_import')
        ],
        'export'        => [
            'class'     => 'btn-export',
            'icon'      => 'fa-download',
            'label'     => lang('export')
        ]
    ];
    if(is_array($btn_additional)) {
        foreach($btn_additional as $i_ba => $ba) {
            if(is_array($ba) && isset($ba['label']) && isset($ba['icon']) && isset($ba['class'])) {
                $define['add_'.$i_ba]   = $ba;
            }
        }
    }
    $btn                = [];
    if($access['input'])    $btn[]  = 'input';
    if(isset($access['import']) && $access['import']) {
        $btn[]          = 'import';
        $btn[]          = 'template_import';
    }
    if(isset($access['export']) && $access['export']) $btn[]   = 'export';
    if(is_array($btn_additional)) {
        foreach($btn_additional as $i_ba => $ba) {
            if(is_array($ba) && isset($ba['label']) && isset($ba['icon']) && isset($ba['class'])) {
                $btn[]  = 'add_'.$i_ba;
            }
        }
    }

    $data_key       = $action_key ? ' app-action-key="'.$action_key.'"' : '';
    $button_color   = 'btn-app';
    if(setting('header_color_primary') && setting('pos_add_button') != 'table') {
        $button_color   = 'btn-app';
    }
    
    if(count($btn) > 0) {
        $accessButton   .= '<div class="btn-group" role="group">' . "\n";
        $accessButton   .= '<button class="btn '.$button_color.' '.$define[$btn[0]]['class'].'"'.$data_key.' app-link="default"><i class="'.$define[$btn[0]]['icon'].'"></i><span>'.$define[$btn[0]]['label'].'</span></button>' . "\n";
        unset($btn[0]);
        if(count($btn) > 0) {
            $accessButton   .= '<div class="btn-group" role="group">' . "\n";
            $accessButton   .= '<button type="button" class="btn '.$button_color.' dropdown-toggle"'.$data_key.' data-bs-toggle="dropdown" aria-expanded="false"></button>' . "\n";
            $accessButton   .= '<ul class="dropdown-menu dropdown-menu-end">' . "\n";
            foreach($btn as $b) {
                $accessButton   .= '<li><button class="dropdown-item dropdown-item-icon '.$define[$b]['class'].'"'.$data_key.'><i class="'.$define[$b]['icon'].'"></i>'.$define[$b]['label'].'</button></li>' . "\n";
            }
            $accessButton   .= '</ul>' . "\n";
            $accessButton   .= '</div>' . "\n";
        }
        $accessButton   .= '</div>' . "\n";
    }
    return $accessButton;
}

function clear_custom_tag($content='') {
    $content    = preg_replace('/<action-header\b[^>]*>(.*?)<\/action-header>/is', '', $content);
    $content    = preg_replace('/<action-header-additional\b[^>]*>(.*?)<\/action-header-additional>/is', '', $content);
    $html       = preg_replace('/<right-panel\b[^>]*>(.*?)<\/right-panel>/is', '', $content);
    return $html;
}

function clear_css($content='') {
    $content = preg_replace('/<link.*?(.*?)>/is','', $content);
    $html = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $content);
    return $html;
}

function clear_js($content='') {
    $content    = str_replace('.js','.js?v='.APP_VERSION,$content);
    $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $content);
    return $html;
}

function right_panel($content='',$data=[]) {
    $right_panel    = '';
    $title          = '';
    $icon           = 'fa-align-right';
    $html           = '';
    preg_match_all('/<right-panel.*?>(.*?)<\/right-panel>/si', $content, $res);
    if(isset($res[1][0])) {
        $key_title  = 'title="';
        $key_icon   = 'toggle-icon="';
        $x1         = strpos($res[0][0],$key_title);
        if($x1 !== false) {
            $x1     += strlen($key_title);
            $x2     = strpos($res[0][0],'"',$x1);
            $title  = substr($res[0][0],$x1,($x2-$x1));
        }
        $x1         = strpos($res[0][0],$key_icon);
        if($x1 !== false) {
            $x1     += strlen($key_icon);
            $x2     = strpos($res[0][0],'"',$x1);
            $icon   = substr($res[0][0],$x1,($x2-$x1));
        }
        foreach($res[1] as $r) {
            $right_panel    .= define_custom_tag($r,$data);
        }
    }
    if($right_panel) {
        preg_match_all('/<panel-header.*?>(.*?)<\/panel-header>/si', $right_panel, $r_header);
        if(isset($r_header[0][0])) {
            $header         = $r_header[1][0];
            $right_panel    = str_replace($r_header[0][0],'',$right_panel);
        }
        $html   = '<div id="right-panel">';
        if(isset($header) && $header) {
            $html   .= '<div class="panel-header"><div class="title w-100">'.$header.'</div></div>';
        } elseif($title) {
            $html   .= '<div class="panel-header"><div class="title">'.$title.'</div></div>';
        }
        $html   .= '<div class="panel-content">'.$right_panel.'</div></div>';
    }
    return [
        'title' => $title,
        'icon'  => $icon,
        'html'  => $html
    ];
}

function render_css($content='',$str_view='') {
    $return_css  = '';
    $css         = '';
    $inline_css  = '';
    preg_match_all('/<link.*?(.*?)>/si', $content, $res);
    if(isset($res[0])) {
        foreach($res[0] as $r) {
            $return_css .= $r.PHP_EOL;
        }
    }

    preg_match_all('/<style.*?>(.*?)<\/style>/si', $content, $res);
    if(isset($res[1])) {
        if(isset($res[0]) && isset($res[0][0]) && strpos($res[0][0],'data-inline') !== false) {
            foreach($res[1] as $k => $r) {
                if(ENVIRONMENT == 'production') {
                    $inline_css    .= minify_css($r);
                } else {
                    $inline_css    .= $r;
                }
            }
        } else {
            foreach($res[1] as $k => $r) {
                if(ENVIRONMENT == 'production') {
                    $css    .= minify_css($r);
                } else {
                    $css    .= $r;
                }
            }
        }
    }

    if(!is_dir(FCPATH . 'assets/cache')) {
        $oldmask = umask(0);
        mkdir(FCPATH . 'assets/cache',0777);
        umask($oldmask);
    }

    $filename   = 'assets/cache/' . md5($str_view) . '.css';
    if($css) {
        $render = false;
        if(file_exists( $filename )) {
            $str_file   = file_get_contents($filename);
            if($str_file != $css) $render = true;
        } else $render = true;
        if($render) {
            $handle = fopen ($filename, "wb");
            if($handle) {
                fwrite ( $handle, $css );
            }
            fclose($handle);
        }
        $return_css .= file_exists( $filename ) ? '<link rel="stylesheet" type="text/css" href="' . base_url($filename) . '?v='.APP_VERSION.'" />' : '<style type="text/css">' . $css . '</style>';
    }
    if($inline_css) {
        $return_css .= '<style type="text/css">' . $inline_css . '</style>';
    }
    return $return_css;
}

function render_js($content='',$str_view='') {
    $return_js  = '';
    $inline_js  = '';
    $js         = '';
    preg_match_all('/<script.*?>(.*?)<\/script>/si', $content, $res);
    if(isset($res[1])) {
        foreach($res[1] as $k => $r) {
            $_res0 = str_replace($r,'',$res[0][$k]);
            if(strpos($_res0, ' src=') !== false) {
                $return_js .= str_replace('.js','.js?v='.APP_VERSION,$res[0][$k]).PHP_EOL;
            } elseif(strpos($_res0, 'data-inline') !== false) {
                if(ENVIRONMENT !== 'production' && strpos($_res0, 'data-unminify') == false) {
                    $inline_js  .= minify_js($r);
                } else {
                    $inline_js  .= trim($r,"\n");
                }
            } else {
                if(ENVIRONMENT == 'production') {
                    $js     .= minify_js($r);
                } else {
                    $js     .= trim($r,"\n");
                }
            }
        }
    }

    if(!is_dir(FCPATH . 'assets/cache')) {
        $oldmask = umask(0);
        mkdir(FCPATH . 'assets/cache',0777);
        umask($oldmask);
    }

    $filename   = 'assets/cache/' . md5($str_view) . '.js';
    if($js) {
        $render = false;
        if(file_exists( $filename )) {
            $str_file   = file_get_contents($filename);
            if($str_file != $js) $render = true;
        } else $render = true;
        if($render) {
            $handle = fopen ($filename, "wb");
            if($handle) {
                fwrite ( $handle, $js );
            }
            fclose($handle);
        }
        $return_js .= file_exists( $filename ) ? '<script type="text/javascript" src="' . base_url($filename) . '?v='.APP_VERSION.'"></script>' : '<script type="text/javascript">' . $js . '</script>' . PHP_EOL;
    }
    if($inline_js) {
        $return_js .= '<script type="text/javascript">' . $inline_js . '</script>' . PHP_EOL;
    }
    return $return_js;
}

function minify_js($r='') {
    $r = preg_replace([
        '#\s*("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')\s*|\s*\/\*(?!\!|@cc_on)(?>[\s\S]*?\*\/)\s*|\s*(?<![\:\=])\/\/.*(?=[\n\r]|$)|^\s*|\s*$#',
        '#;+\}#',
        '#([\{,])([\'])(\d+|[a-z_][a-z0-9_]*)\2(?=\:)#i',
        '#([a-z0-9_\)\]])\[([\'"])([a-z_][a-z0-9_]*)\2\]#i'
    ],[
        '$1',
        '}',
        '$1$3',
        '$1.$3'    
    ],$r);
    $r  = preg_replace('/(?:(?:\r\n|\r|\n)\s*){1}/s', "\n", $r);
    $_r = explode("\n",$r);
    $result = '';
    foreach($_r as $y => $x) {
        if(trim($x)){
            $result .= trim($x);
            $next   = '';
            if(isset($_r[$y + 1])) {
                $next   = trim($_r[$y + 1]);
            }
            if(in_array(substr($x,-1),[')',']']) && !in_array(substr($next,0,1),[')',']'])) {
                $result .= ';';
            } elseif(!in_array(substr($x,-1),[',',';','{','}','(','['])) {
                $next_first_word    = explode(' ',$next)[0];
                if(in_array($next_first_word,['var','let','const'])) {
                    $result .= ';';
                } else {
                    $new_line   = true;
                    if(in_array(substr($next,0,1),[')','}',']'])) {
                        $new_line   = false;
                    }
                    if($new_line) {
                        $result .= "\n";
                    }
                }
            }
        }
    }
    foreach([',',';','{','}','='] as $c) {
        $result = str_replace([$c.' ',' '.$c],$c, $result);
    }
    return $result;
}

function minify_css($input='') {
    return preg_replace([
            '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')|\/\*(?!\!)(?>.*?\*\/)|^\s*|\s*$#s',
            '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/))|\s*+;\s*+(})\s*+|\s*+([*$~^|]?+=|[{};,>~]|\s(?![0-9\.])|!important\b)\s*+|([[(:])\s++|\s++([])])|\s++(:)\s*+(?!(?>[^{}"\']++|"(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')*+{)|^\s++|\s++\z|(\s)\s+#si',
            '#(?<=[\s:])(0)(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)#si',
            '#:(0\s+0|0\s+0\s+0\s+0)(?=[;\}]|\!important)#i',
            '#(background-position):0(?=[;\}])#si',
            '#(?<=[\s:,\-])0+\.(\d+)#s',
            '#(\/\*(?>.*?\*\/))|(?<!content\:)([\'"])([a-z_][a-z0-9\-_]*?)\2(?=[\s\{\}\];,])#si',
            '#(\/\*(?>.*?\*\/))|(\burl\()([\'"])([^\s]+?)\3(\))#si',
            '#(?<=[\s:,\-]\#)([a-f0-6]+)\1([a-f0-6]+)\2([a-f0-6]+)\3#i',
            '#(?<=[\{;])(border|outline):none(?=[;\}\!])#',
            '#(\/\*(?>.*?\*\/))|(^|[\{\}])(?:[^\s\{\}]+)\{\}#s'
        ],[
            '$1',
            '$1$2$3$4$5$6$7',
            '$1',
            ':0',
            '$1:0 0',
            '.$1',
            '$1$3',
            '$1$2$4$5',
            '$1$2$3',
            '$1:0',
            '$1$2'
        ], $input);
}

function define_custom_tag($html,$data=[]) {
    $CI                 = get_instance();

    // define foreach
    preg_match_all('/each(.*?)endEach/si', $html, $res);
    foreach($res[0] as $key_res => $val_res) {
        $new_str    = '';
        $str        = $res[1][$key_res];
        preg_match_all('/\((.*?)\)/si', $str, $z);
        if(isset($z[1][0])) {
            $loop_content   = trim( str_replace($z[0][0],'',$str) );
            $x_looping_data = explode('=>',$z[1][0]);
            $variable       = trim($x_looping_data[0]);
            $key            = isset($x_looping_data[1]) && trim($x_looping_data[1]) ? trim($x_looping_data[1]) : trim($x_looping_data[0]);
            if(isset($data[$variable]) && is_array($data[$variable])) {
                foreach($data[$variable] as $dv) {
                    preg_match_all('/{'.$key.'(.*?)}/si', $loop_content, $y);
                    $loop_replace   = $loop_content . "\n";
                    foreach($y[0] as $key_y => $val_y) {
                        $key_value  = trim($y[1][$key_y],'.');
                        if(is_string($dv)) {
                            $loop_replace   = str_replace($val_y, $dv, $loop_replace);
                        } elseif(is_array($dv)) {
                            if(isset($dv[$key_value])) $loop_replace   = str_replace($val_y, $dv[$key_value], $loop_replace);
                            else $loop_replace   = str_replace($val_y, '', $loop_replace);
                        } elseif(is_object($dv)) {
                            if(isset($dv->$key_value)) $loop_replace   = str_replace($val_y, $dv->$key_value, $loop_replace);
                            else $loop_replace   = str_replace($val_y, '', $loop_replace);
                        }
                    }
                    $new_str    .= $loop_replace;
                }
            }
        }
        $html = str_replace($val_res,$new_str,$html);
    }

    // define variable
    preg_match_all('/\${(.*?)}/si', $html, $res);
    foreach($res[0] as $key_res => $val_res) {
        if(isset($data[$res[1][$key_res]])) {
            $html   = str_replace($val_res, $data[trim($res[1][$key_res])], $html);
        }
    }

    // define app-card
    preg_match_all('/<app-card.*?>(.*?)<\/app-card>/si', $html, $res);
    foreach($res[0] as $key_res => $val_res) {
        $tag            = str_replace($res[1][$key_res],'',$val_res);
        $attr           = trim(str_replace(['app-card','</','<','>'],'',$tag));
        $list_attr      = explode(' ',$attr);
        $x_attr         = [];
        $i              = 0;
        $open_attr      = false;
        foreach($list_attr as $l) {
            if(!$open_attr) {
                $x_attr[$i]  = $l;
            } else {
                $x_attr[$i]  .= ' '.$l;
            }
            if(!$open_attr && strpos($l,'="') !== false && count(explode('"',$l)) == 2) $open_attr = true;
            else if($open_attr && strpos($l,'"') !== false) $open_attr   = false;
            if(!$open_attr) $i++;
        }

        $new_attr       = '';
        $title          = '';
        $sub_title      = '';
        $collapse       = false;
        $open           = false;
        $class          = 'card';
        foreach($x_attr as $a) {
            if(strpos($a,'subtitle=') !== false) {
                $sub_title      = trim(str_replace(['"','subtitle='],'',$a));
            } elseif(strpos($a,'title=') !== false) {
                $title          = trim(str_replace(['"','title='],'',$a));
            } elseif(strpos($a,'class=') !== false) {
                $class          .= ' '.trim(str_replace(['"','class='],'',$a));
            } elseif(strpos($a,'collapse-mode=') !== false) {
                $collapse       = strtolower(trim(str_replace(['"','collapse-mode='],'',$a))) == "true";
            } elseif(strpos($a,'collapse-default=') !== false) {
                $open           = strtolower(trim(str_replace(['"','collapse-default='],'',$a))) == "open";
            } else {
                $new_attr       .= ' '.$a;
            }
        }
        if($title && $collapse) {
            $class .= ' card-collapse';
            if($open) $class .= ' open';
        }

        $new_attr   .= " class=\"{$class}\"";
        $new_html   = "<div{$new_attr}>" . "\n";
        if($title) {
            $new_html   .= '<div class="card-header">' . "\n";
            $new_html   .= '<div class="card-title fw-semi-bold f-120 mb-1">'.$title.'</div>' . "\n";
            if($sub_title) {
                $new_html   .= '<div class="card-subtitle">'.$sub_title.'</div>' . "\n";
            }
            $new_html   .= '</div>' . "\n";
        }
        $new_html   .= '<div class="card-body">'.$res[1][$key_res].'</div>' . "\n";
        $new_html   .= '</div>' . "\n";
        $html       = str_replace($val_res,$new_html,$html);
    }    

    // define app-modal
    preg_match_all('/<app-modal.*?>(.*?)<\/app-modal>/si', $html, $res);
    foreach($res[0] as $key_res => $val_res) {
        $tag            = str_replace($res[1][$key_res],'',$val_res);
        $attr           = trim(str_replace(['app-modal','</','<','>'],'',$tag));
        $list_attr      = explode(' ',$attr);
        $x_attr         = [];
        $i              = 0;
        $open_attr      = false;
        foreach($list_attr as $l) {
            if(!$open_attr) {
                $x_attr[$i]  = $l;
            } else {
                $x_attr[$i]  .= ' '.$l;
            }
            if(!$open_attr && strpos($l,'="') !== false && count(explode('"',$l)) == 2) $open_attr = true;
            else if($open_attr && strpos($l,'"') !== false) $open_attr   = false;
            if(!$open_attr) $i++;
        }

        $new_attr       = ' aria-hidden="true" tabindex="-1"';
        $title          = 'Modal';
        $sub_title      = '';
        $scrollable     = true;
        $class          = 'modal fade';
        $size           = '';
        $modal_id       = '';
        foreach($x_attr as $a) {
            if(strpos($a,'subtitle=') !== false) {
                $sub_title      = trim(str_replace(['"','subtitle='],'',$a));
            } elseif(strpos($a,'title=') !== false) {
                $title          = trim(str_replace(['"','title='],'',$a));
            } elseif(strpos($a,'class=') !== false) {
                $class          .= ' '.trim(str_replace(['"','class='],'',$a));
            } elseif(substr($a,0,3) == 'id=') {
                $modal_id       = trim(str_replace(['"','id='],'',$a));
            } elseif(strpos($a,'scrollable=') !== false) {
                $_scrollable    = trim(str_replace(['"','scrollable='],'',$a));
                if($_scrollable == 'false') {
                    $scrollable = false;
                }
            } elseif(strpos($a,'size=') !== false) {
                $_size          = trim(str_replace(['"','size=','modal-'],'',$a));
                if(in_array($_size,['fullscreen','xl','lg','md','sm'])) {
                    $size       = ' modal-' . $_size;
                }
            } else {
                $new_attr       .= ' '.$a;
            }
        }
        if(!$modal_id) $new_attr .= ' id="modal-'.rand().'"';
        else $new_attr .= ' id="'.$modal_id.'"';

        if($sub_title) $title .= '<small>'.$sub_title.'</small>';

        $class_dialog   = 'modal-dialog';
        if($scrollable) {
            $class_dialog   .= ' modal-dialog-scrollable';
        }
        if($size) {
            $class_dialog   .= ' modal-' . $size;
        }

        $modal_content  = $res[1][$key_res];
        $modal_footer   = '';
        preg_match_all('/<footer-form(.*?)\/>/si', $modal_content, $res_modal);
        if(isset($res_modal[0][0])) {
            preg_match_all('/<form(.*?)>/si', $modal_content, $res_form);
            if(isset($res_form[1][0])) {
                $attrForm   = explode(' ',$res_form[1][0]);
                $idForm     = '';
                foreach($attrForm as $af) {
                    if(substr($af,0,3) == 'id=') {
                        $idForm = trim(str_replace(['\'','"','id='],'',$af));
                    }
                }
                if($idForm) {
                    $modal_footer   .= '<div class="modal-footer-info">' . "\n";
                    $modal_footer   .= '<i class="fa-info-circle" data-appinity-tooltip="right"></i>' . "\n";
                    $modal_footer   .= '</div>' . "\n";
                    $modal_footer   .= '<button type="button" class="btn btn-theme" data-bs-dismiss="modal">'.lang('batal').'</button>' . "\n";
                    $modal_footer   .= '<button type="submit" class="btn btn-app" form="'.$idForm.'">'.lang('simpan').'</button>' . "\n";
                }
            }
        }
        foreach($res_modal[1] as $k_modal => $v_modal) {
            $modal_content  = str_replace($res_modal[0][$k_modal],'',$modal_content);
        }

        $pushFooter = $modal_footer ? false : true;
        preg_match_all('/<footer.*?>(.*?)<\/footer>/si', $modal_content, $res_modal);
        foreach($res_modal[1] as $k_modal => $v_modal) {
            if($pushFooter) {
                $modal_footer   .= trim($v_modal);
            }
            $modal_content  = str_replace($res_modal[0][$k_modal],'',$modal_content);
        }

        $add_modal_header   = '';
        preg_match_all('/<header.*?>(.*?)<\/header>/si', $modal_content, $res_modal);
        foreach($res_modal[1] as $k_modal => $v_modal) {
            $add_modal_header   .= trim($v_modal);
            $modal_content  = str_replace($res_modal[0][$k_modal],'',$modal_content);
        }

        $new_attr   .= " class=\"{$class}\"";
        $new_html   = "<div{$new_attr}>" . "\n";
        $new_html   .= '<div class="'.$class_dialog.'">' . "\n";
        $new_html   .= '<div class="modal-content">' . "\n";
        $new_html   .= '<div class="modal-header">' . "\n";
        if($add_modal_header) {
            $new_html   .= $add_modal_header . "\n";
        }
        $new_html   .= '<div class="modal-title">'.$title.'</div>';
        $new_html   .= '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>' . "\n";
        $new_html   .= '</div>' . "\n";
        $new_html   .= '<div class="modal-body">'.$modal_content.'</div>' . "\n";
        if($modal_footer) {
            if($modal_footer == 'null') $modal_footer = '';
            $new_html   .= '<div class="modal-footer">'.$modal_footer.'</div>' . "\n";
        }
        $new_html   .= '</div>' . "\n";
        $new_html   .= '</div>' . "\n";
        $new_html   .= '</div>' . "\n";
        $html       = str_replace($val_res,$new_html,$html);
    }

    // form
    preg_match_all('/<form(.*?)>/si', $html, $res_form);
    foreach($res_form[1] as $k_form => $v_form) {
        $x_form             = explode(' ',trim($v_form));
        $tableFormName      = '';
        $tableFormAttr      = '';
        $app_link           = 'default';
        foreach($x_form as $x_f) {
            if(substr($x_f,0,6) == 'table=') {
                $tableFormName  = trim(str_replace(['table=','\'','"'],'',$x_f));
                $tableFormAttr  = $x_f;
            }
        }
        if($tableFormName) {
            $new_attr   = str_replace($tableFormAttr,'data-key="'.encode_string($tableFormName,TABLE_KEY).'"',$res_form[0][$k_form]);
            $html       = str_replace($res_form[0][$k_form],$new_attr,$html);            
        }
    }

    // define app-table
    preg_match_all('/<app-table.*?>(.*?)<\/app-table>/si', $html, $res);
    foreach($res[0] as $key_res => $val_res) {
        $tag            = str_replace($res[1][$key_res],'',$val_res);
        $attr           = trim(str_replace(['app-table','</','<','>'],'',$tag));
        $list_attr      = explode(' ',$attr);
        $x_attr         = [];
        $i              = 0;
        $open_attr      = false;
        foreach($list_attr as $l) {
            if(!$open_attr) {
                $x_attr[$i]  = $l;
            } else {
                $x_attr[$i]  .= ' '.$l;
            }
            if(!$open_attr && strpos($l,'="') !== false && count(explode('"',$l)) == 2) $open_attr = true;
            else if($open_attr && strpos($l,'"') !== false) $open_attr   = false;
            if(!$open_attr) $i++;
        }

        $new_attr       = '';
        $key_table      = '';
        $class_table    = 'table '.str_replace(',',' ',setting('table_style'));
        $app_link       = 'default';
        $add_button     = true;
        foreach($x_attr as $a) {
            if(strpos($a,'source=') !== false) {
                $n              = trim(str_replace(['"','source='],'',$a));
                $source         = strpos($n, base_url()) === false ? base_url($n) : $n;
                $new_attr       .= ' data-source="'.$source.'"';
            } elseif(strpos($a,'table=') !== false) {
                $n              = trim(str_replace(['"','table='],'',$a));
                $key_table      = encode_string($n,TABLE_KEY);
            } elseif(strpos($a,'class=') !== false) {
                $__class        = trim(str_replace(['"','class='],'',$a));
                if($__class == '__appinity__') {
                    $class_table    .= ' table-appinity';
                } else {
                    $class_table    = $__class;
                }
            } elseif(strpos($a,'app-link=') !== false) {
                $app_link       = trim(str_replace(['"','app-link='],'',$a));
            } elseif(strpos($a,'add-button=') !== false) {
                $add_button     = trim(str_replace(['"','add-button='],'',$a)) == "false" ? false : true;
            } else {
                $new_attr       .= ' '.$a;
            }
        }
        $new_attr   .= ' app-link="'.$app_link.'"';

        /* CARA PENULISAN ATRIBUT table
            table="tbl_a"                           =>  tbl_a   : nama table,
            table="tbl_a.id"                        =>  tbl_a   : nama table, 
                                                        id      : nama field primary,
            table="tbl_a[tbl_b(id_b:self.tbl_b)]"   =>  tbl_a   : nama table,
                                                        tbl_b   : nama table join,
                                                        ()      : isi dalam kurung adalah kondisi join
                                                                  - self : maksudnya tbl_join itu sendiri
                                                                  - jika field tidak dikaitkan maka akan dikaitkan
                                                                    dengan table parent nya
                                                                  - contoh diatas akan berisi "tbl_a.id_b = tbl_b.id"
            multi join
            table="tbl_a[tbl_b(kondisi),tbl_c(kondisi)]"    => tbl_b dan tbl_c di join dengan table a
            table="tbl_a[tbl_b(kondisi)[tbl_c(kondisi)]]"   => tbl_b di join tbl_a, dan tbl_c di join dengan tbl_b
            kesimpulannya untuk join bentuknya hirarki seperti ini
            tbl_a [
                tbl_b1 [
                    tbl_c [
                        tbl_d [
                            dst
                        ]
                    ]
                ],
                tbl_b2
            ]
            tbl_a join dengan tbl_b1 & tbl_b2
            tbl_b1 join dengan tbl_c
            dst
        */

        
        $new_attr       .= " class=\"{$class_table}\"";
        if($key_table) {
            $new_attr   .= " data-key=\"{$key_table}\"";
            if(strpos($new_attr,'data-source') === false) {
                $new_attr   .= ' data-source="'.base_url('general/data').'"';
                if(isset($data['__cur_uri'])) {
                    $new_attr   .= ' data-ref="'.$data['__cur_uri'].'"';
                }
            }
        }
        $new_html   = "";
        if(setting('pos_add_button') == "table" && $add_button) {
            $cls_action = setting('pos_action_button') == 'left' ? 'text-start' : 'text-end';
            $new_html   .= '<div class="mb-3 appinityTable-action-button '.$cls_action.'">'.setting('action_header').'</div>';
        }
        $new_html   .= "<table{$new_attr}>{$res[1][$key_res]}</table>\n";
        $html       = str_replace($val_res,$new_html,$html);
    }

    // define language
    preg_match_all('/ln{(.*?)}/si', $html, $res);
    foreach($res[0] as $key_res => $val_res) {
        $html   = str_replace($val_res, lang(trim($res[1][$key_res])), $html);
    }

    // define base_url
    preg_match_all('/base_url{(.*?)}/si', $html, $res);
    foreach($res[0] as $key_res => $val_res) {
        $html   = str_replace($val_res, base_url(trim($res[1][$key_res])), $html);
    }

    // define asset_url
    preg_match_all('/asset_url{(.*?)}/si', $html, $res);
    foreach($res[0] as $key_res => $val_res) {
        $html   = str_replace($val_res, asset_url(trim($res[1][$key_res])), $html);
    }

    // define user variable
    preg_match_all('/user{(.*?)}/si', $html, $res);
    foreach($res[0] as $key_res => $val_res) {
        $html   = str_replace($val_res, user(trim($res[1][$key_res])), $html);
    }

    // define setting variable
    preg_match_all('/setting{(.*?)}/si', $html, $res);
    foreach($res[0] as $key_res => $val_res) {
        $html   = str_replace($val_res, setting(trim($res[1][$key_res])), $html);
    }

    // define app-view
    preg_match_all('/<app-view (.*?)\/>/si', $html, $res);
    foreach($res[0] as $view_key => $view_val) {
        $attr_view      = [];
        $str_attr       = $res[1][$view_key];
        $list_attr      = explode(' ',$str_attr);
        $new_list_attr  = [];
        $i              = 0;
        $open_attr      = false;
        foreach($list_attr as $l) {
            if(!$open_attr) {
                $new_list_attr[$i]  = $l;
            } else {
                $new_list_attr[$i]  .= ' '.$l;
            }
            if(!$open_attr && strpos($l,'="') !== false && count(explode('"',$l)) == 2) $open_attr = true;
            else if($open_attr && strpos($l,'"') !== false) $open_attr   = false;
            if(!$open_attr) $i++;
        }
        foreach($new_list_attr as $l) {
            if(trim($l)) {
                $attr_param = explode('=',$l);
                $attr       = trim($attr_param[0]);
                $val        = isset($attr_param[1]) ? trim(trim($attr_param[1]),'"') : '';
                $attr_view[$attr] = $val;
            }
        }

        $new_html_view  = '';
        if(isset($attr_view['src']) && file_exists(FCPATH . 'application/views/' . $attr_view['src'] . '.php')) {
            $new_html_view  = define_custom_tag($CI->load->view($attr_view['src'],$data,true));
        }
        $html   = str_replace($res[0][$view_key],$new_html_view,$html);
    }

    // define app-input-group
    preg_match_all('/<app-input-group.*?>(.*?)<\/app-input-group>/si', $html, $res);
    foreach($res[0] as $key_res => $val_res) {
        // check default
        $default    = [];
        $temp_html  = substr($html, 0, strpos($html,$val_res));
        preg_match_all('/<app-input-default(.*?)\/>/si', $temp_html, $res2);
        if(count($res2[1]) > 0) {
            $str_attr       = end($res2[1]);
            $list_attr      = explode(' ',$str_attr);
            $new_list_attr  = [];
            $i              = 0;
            $open_attr      = false;
            foreach($list_attr as $l) {
                if(!$open_attr) {
                    $new_list_attr[$i]  = $l;
                } else {
                    $new_list_attr[$i]  .= ' '.$l;
                }
                if(!$open_attr && strpos($l,'="') !== false && count(explode('"',$l)) == 2) $open_attr = true;
                else if($open_attr && strpos($l,'"') !== false) $open_attr   = false;
                if(!$open_attr) $i++;
            }
            foreach($new_list_attr as $l) {
                if(trim($l)) {
                    $attr_param = explode('=',$l);
                    $attr       = trim($attr_param[0]);
                    $val        = isset($attr_param[1]) ? trim(trim($attr_param[1]),'"') : '';
                    $default[$attr] = $val;
                }
            }
        }

        $label          = '';
        $sub_label      = '';
        $size           = isset($default['size'])       ? $default['size']          : '12:12';
        $size_param     = isset($default['size-param']) ? $default['size-param']    : 'md';
        $other_attr     = '';

        $tag            = str_replace($res[1][$key_res],'',$val_res);
        $attr           = trim(str_replace(['app-input-group','</','<','>'],'',$tag));
        $list_attr      = explode(' ',$attr);
        $x_attr         = [];
        $i              = 0;
        $open_attr      = false;
        foreach($list_attr as $l) {
            if(!$open_attr) {
                $x_attr[$i]  = $l;
            } else {
                $x_attr[$i]  .= ' '.$l;
            }
            if(!$open_attr && strpos($l,'="') !== false && count(explode('"',$l)) == 2) $open_attr = true;
            else if($open_attr && strpos($l,'"') !== false) $open_attr   = false;
            if(!$open_attr) $i++;
        }

        foreach($x_attr as $l) {
            if(trim($l)) {
                $attr_param = explode('=',$l);
                $attr       = trim($attr_param[0]);
                $val        = isset($attr_param[1]) ? trim(trim($attr_param[1]),'"') : '';
                if($attr == 'label' && $val)            $label      = $val;
                else if($attr == 'sub-label' && $val)   $sub_label  = '<small class="d-block">'.$val.'</small>';
                else if($attr == 'size' && $val)        $size       = $val;
                else if($attr == 'size-param' && $val)  $size_param = $val;
                else if(!in_array($attr,['label','sub-label','size','size-param'])) {
                    $other_attr    .= " {$l}";
                }
            }
        }

        $x_size         = explode(':',$size);
        $label_size     = 0;
        $input_size     = 12;
        if(count($x_size) == 1 && (int) $x_size[0] > 0 && (int) $x_size[0] <= 12) {
            $label_size = $x_size[0];
            $input_size = $x_size[0];
        } elseif(count($x_size) == 2) {
            if((int) $x_size[0] > 0 && (int) $x_size[0] <= 12) $label_size  = $x_size[0];
            if((int) $x_size[1] > 0 && (int) $x_size[1] <= 12) $input_size  = $x_size[1];
        }
        if(!in_array($size_param,['none','sm','md','lg','xl'])) $size_param    = 'md-';
        else if($size_param == 'none') $size_param = '';
        else $size_param .= '-';

        preg_match_all('/<app-input-default(.*?)\/>/si', $html, $res_input_default);

        $new_content    = '';
        $child          = '';
        preg_match_all('/<app-select.*?>(.*?)<\/app-select>/si', $html, $res_child);
        if(count($res_child[0]) > 0 && $label && $label_size) {
            $child      .= define_app_select($res_child, $res[1][$key_res], true, $res_input_default);
        }
        preg_match_all('/<app-input (.*?)\/>/si', $res[1][$key_res], $res_child);
        if(count($res_child[0]) > 0 && $label && $label_size) {
            $child      .= define_app_input($res_child, $res[1][$key_res], true, $res_input_default);
        }

        // clear custom tag
        preg_match_all('/<app-input (.*?)\/>/si', $res[1][$key_res], $res_child);
        foreach($res_child[0] as $r) {
            $child      = str_replace($r, '', $child);
        }
        preg_match_all('/<app-select.*?>(.*?)<\/app-select>/si', $html, $res_child);
        foreach($res_child[0] as $r) {
            $child      = str_replace($r, '', $child);
        }

        if($child) {
            $class_required = strpos($child,'required') !== false ? ' required' : '';
            $new_content    .= '<div class="row">' . "\n";
            if($label_size && $label) {
                $new_content    .= '<label class="col-'.$size_param.$label_size.$class_required.' form-label">'.$label.$sub_label.'</label>' . "\n";
            }
            $new_content    .= '<div class="col-'.$size_param.$input_size.'">' . "\n";
            $new_content    .= '<div class="row">' . "\n";
            $new_content    .= $child;
            $new_content    .= '</div>' . "\n";
            $new_content    .= '</div>' . "\n";
            $new_content    .= '</div>' . "\n";
        }
        $html   = str_replace($val_res,$new_content,$html);
    }

    // define app-input
    // allowed type : text, textarea, password, date, datetime, daterange, icon, tags, currency, color, fileupload, imageupload, range
    preg_match_all('/<app-input (.*?)\/>/si', $html, $res);
    $html   = define_app_input($res, $html);

    // define app-select
    preg_match_all('/<app-select.*?>(.*?)<\/app-select>/si', $html, $res);
    $html   = define_app_select($res, $html);


    //clear default
    preg_match_all('/<app-input-default(.*?)\/>/si', $html, $res);
    foreach($res[0] as $r) {
        $html   = str_replace($r,'',$html);
    }

    return $html;
}

function define_app_input($res, $html, $grouping = false, $res_input_default = '') {
    $validation_exist   = [];
    $path_exist         = [];
    $autocode_lists     = [];
    foreach($res[0] as $key_res => $val_res) {
        $default    = [];

        // check default
        $temp_html      = substr($html, 0, strpos($html,$val_res));
        preg_match_all('/<app-input-default(.*?)\/>/si', $temp_html, $res2);
        if(count($res2[1]) == 0 && is_array($res_input_default) && isset($res_input_default[1])) {
            $res2[1]    = $res_input_default[1];
        }
        if(count($res2[1]) > 0) {
            $str_attr       = end($res2[1]);
            $list_attr      = explode(' ',$str_attr);
            $new_list_attr  = [];
            $i              = 0;
            $open_attr      = false;
            foreach($list_attr as $l) {
                if(!$open_attr) {
                    $new_list_attr[$i]  = $l;
                } else {
                    $new_list_attr[$i]  .= ' '.$l;
                }
                if(!$open_attr && strpos($l,'="') !== false && count(explode('"',$l)) == 2) $open_attr = true;
                else if($open_attr && strpos($l,'"') !== false) $open_attr   = false;
                if(!$open_attr) $i++;
            }
            foreach($new_list_attr as $l) {
                if(trim($l)) {
                    $attr_param = explode('=',$l);
                    $attr       = trim($attr_param[0]);
                    $val        = isset($attr_param[1]) ? trim(trim($attr_param[1]),'"') : '';
                    $default[$attr] = $val;
                }
            }    
        }
        $validation_ref = isset($default['rules']) ? $default['rules'] : '';
        $_validation    = [];
        $_path          = [];
        if($validation_ref) {
            if(!isset($validation_exist[$validation_ref])) {
                $_validation    = __validation($validation_ref);
                $validation_exist[$validation_ref]  = $_validation;
                $_path          = __rules_path($validation_ref);
                $path_exist[$validation_ref]        = $_path;
            } else {
                $_validation    = $validation_exist[$validation_ref];
                $_path          = $path_exist[$validation_ref];
            }
        }
        $autocode   = false;
        if(count($autocode_lists) == 0 && $validation_ref) {
            $kode       = get_data('kode',['tabel'=>table_prefix($validation_ref),'is_active'=> 1])->result();
            $autocode_lists['false']  = 'false'; // agar tidak di proses lagi loopingan autocode jika query kode tidak ada data
            foreach($kode as $k) {
                $autocode_lists[$k->kolom]  = $k->kolom;
            }
        }

        $allowed_type   = ['text', 'hidden', 'textarea', 'password', 'password-toggle', 'date', 'datetime', 'daterange', 'number', 'icon', 'tags', 'currency', 'color', 'fileupload', 'imageupload', 'range', 'checkbox', 'switch', 'radio'];
        $type           = 'text';
        $label          = '';
        $sub_label      = '';
        $label_type     = isset($default['label-type']) ? $default['label-type'] : '';
        $id             = '';
        $name           = '';
        $validation     = '';
        $prefix         = '';
        $suffix         = '';
        $checked        = '';
        $length         = '';
        $class          = 'form-control ';
        $parent_class   = 'mb-3 ';
        $size           = isset($default['size'])       ? $default['size']          : '12:12';
        $size_param     = isset($default['size-param']) ? $default['size-param']    : 'md';
        $other_attr     = '';
        $value          = '';
        $locPath        = '';
        $dtPlacement    = 'left';
        $str_attr       = trim($res[1][$key_res]);
        preg_match_all('/<em.*?>(.*?)<\/em>/si', $str_attr, $res2);
        foreach($res2[0] as $k_res2 => $v_res2) {
            $str_attr   = str_replace($v_res2,'undefined',$str_attr);
        }
        $list_attr      = explode(' ',$str_attr);
        $new_list_attr  = [];
        $i              = 0;
        $open_attr      = false;
        foreach($list_attr as $l) {
            if(!$open_attr) {
                $new_list_attr[$i]  = $l;
            } else {
                $new_list_attr[$i]  .= ' '.$l;
            }
            if(!$open_attr && strpos($l,'="') !== false && count(explode('"',$l)) == 2) $open_attr = true;
            else if($open_attr && strpos($l,'"') !== false) $open_attr   = false;
            if(!$open_attr) $i++;
        }
        foreach($new_list_attr as $l) {
            if(trim($l)) {
                $attr_param = explode('=',$l,2);
                $attr       = trim($attr_param[0]);
                $val        = isset($attr_param[1]) ? trim(trim($attr_param[1]),'"') : '';
                if($attr == 'type' && $val)             $type           = $val;
                else if($attr == 'label' && $val)       $label          = $val;
                else if($attr == 'sub-label' && $val)   $sub_label      = '<small class="d-block">'.$val.'</small>';
                else if($attr == 'label-type' && $val)  $label_type     = $val;
                else if($attr == 'name' && $val)        $name           = $id = $val;
                else if($attr == 'id' && $val)          $id             = $val;
                else if($attr == 'validation' && $val)  $validation     = $val;
                else if($attr == 'class' && $val)       $class         .= $val;
                else if($attr == 'parent-class' && $val)$parent_class  .= $val;
                else if($attr == 'size' && $val)        $size           = $val;
                else if($attr == 'size-param' && $val)  $size_param     = $val;
                else if($attr == 'value' && $val)       $value          = $val;
                else if($attr == 'length' && $val)      $length          = $val;
                else if($attr == 'checked' && $val)     $checked        = $val;
                else if($attr == 'data-path' && $val && ($type == 'imageupload' || $type == 'fileupload'))      $locPath        = trim($val,'/') . '/';
                else if($attr == 'data-placement' && $val && in_array($type,['date','datetime','daterange']))   $dtPlacement    = $val;
                else if(in_array($attr,['prefix','prepend']) && $val)   $prefix    = $val;
                else if(in_array($attr,['suffix','append']) && $val)    $suffix    = $val;
                else if(!in_array($attr,['type','label','sub-label','label-type','name','id','validation','class','size','parent-class','length',
                                        'size-param','value','checked','append','prepend','suffix','prefix'])) {
                    $other_attr    .= " {$l}";
                }
            }
        }
        if(isset($_path[$name])) {
            $locPath = $_path[$name];
        }
        if($type == 'imageupload' || $type == 'fileupload') {
            $other_attr .= ' data-path="'.$locPath.'"';
        } elseif(in_array($type,['date','datetime','daterange'])) {
            $other_attr .= ' data-placement="'.$dtPlacement.'"';
        }
        if(isset($_validation[$name])) {
            $validation = $_validation[$name];
            preg_match_all('/setting{(.*?)}/si', $validation, $res_val);
            foreach($res_val[0] as $key_res_val => $val_res_val) {
                $validation    = str_replace($val_res_val, setting(trim($res_val[1][$key_res_val])), $validation);
            }
        }
        if(isset($autocode_lists[$name])) {
            if(strpos($other_attr,' disabled') === false) $other_attr .= ' disabled="disabled"';
            if(strpos($other_attr,' placeholder') === false) $other_attr .= ' placeholder="'.lang('otomatis_saat_disimpan').'"';
            else {
                $strPlaceholder         = ' placeholder="';
                $posStartPlaceholder    = strpos($other_attr,$strPlaceholder);
                $posValue               = $posStartPlaceholder + strlen($strPlaceholder);
                $posEndPlaceholder      = strpos($other_attr,'"',$posValue);
                $valuePlaceholder       = substr($other_attr, $posValue, ($posEndPlaceholder - $posValue));
                $other_attr             = str_replace('placeholder="'.$valuePlaceholder.'"','placeholder="'.lang('otomatis_saat_disimpan').'"',$other_attr);
            }
        }
        $new_content        = '';
        $class_container= '';
        if(in_array($type,$allowed_type)) {
            $input_type     = 'text';
            if(in_array($type,['password','color','range','textarea','checkbox','radio','hidden'])) {
                $input_type = $type;
            } elseif($type == 'password-toggle') {
                $input_type         = 'password';
                $class_container    .= ' password-toggle';
            } elseif($type == 'switch') {
                $input_type         = 'checkbox';
            } elseif($type == 'icon') {
                $prefix     = '';
                $suffix     = '&nbsp;';
                $other_attr .= ' data-placement="bottomLeft"';
            } elseif($type == 'number') {
                if($validation) {
                    if(strpos($validation,'numeric') == false) {
                        $validation .= '|numeric';
                    }
                } else {
                    $validation = 'numeric';
                }
            }
            $x_size         = explode(':',$size);
            $label_size     = 0;
            $input_size     = 12;
            if(count($x_size) == 1 && (((int) $x_size[0] > 0 && (int) $x_size[0] <= 12) || $x_size[0] == 'auto')) {
                $label_size = $x_size[0];
                $input_size = $x_size[0];
            } elseif(count($x_size) == 2) {
                if((int) $x_size[0] > 0 && (int) $x_size[0] <= 12) $label_size  = $x_size[0];
                if((int) $x_size[1] > 0 && (int) $x_size[1] <= 12) $input_size  = $x_size[1];
            }
            if(!in_array($size_param,['none','sm','md','lg','xl'])) $size_param    = 'md-';
            else if($size_param == 'none') $size_param = '';
            else $size_param .= '-';
            $label_class    = strpos($validation,'required') !== false ? ' required' : '';

            if($type == 'imageupload') {
                $other_attr .= ' data-type="upload-image" data-value="'.$value.'"';
                $value      = '';
            } elseif($type == 'fileupload') {
                $other_attr .= ' data-type="upload-file" data-value="'.$value.'"';
                $value      = '';
            } elseif($type == 'range') {
                $class      = str_replace('form-control','form-range',$class);
                if(!$value) $value = 0;
            } elseif(in_array($type,['checkbox','switch','radio'])) {
                $class      = str_replace('form-control','form-check-input',$class);
            } elseif(in_array($type,['icon','tags','date','datetime','daterange','currency'])) {
                $class      .= ' input-'.strtolower($type);
            } elseif($type == 'color') {
                $class      .= ' form-control-color mw-100';
            }
            
            $x_class        = explode(' ',$class);
            $a_class        = [];
            foreach($x_class as $xc) {
                $a_class[$xc]   = $xc;
            }
            $class          = implode(" ",$a_class);

            if($length) {
                $other_attr .= ' maxlength="'.$length.'" size="'.$length.'"';
            }

            if(!$grouping) {
                if($label && $label_type == 'floating' && in_array($type,['textarea','text'])) {
                    $new_content    .= '<div class="'.$parent_class.' form-floating">' . "\n";
                    if($input_type != 'textarea') {
                        $new_content    .= '<input type="'.$input_type.'" class="'.$class.'" id="'.$id.'" name="'.$name.'" aria-label="'.strip_tags($label).'" data-validation="'.$validation.'" placeholder="'.strip_tags($label).'" value="'.$value.'"'.$other_attr.'>';
                    } else {
                        $new_content    .= '<textarea class="'.$class.'" id="'.$id.'" name="'.$name.'" aria-label="'.strip_tags($label).'" data-validation="'.$validation.'"'.$other_attr.' placeholder="'.strip_tags($label).'">'.$value.'</textarea>';
                    }
                    $new_content    .= '<label for="'.$id.'" class="'.$label_class.'">'.$label.$sub_label.'</label>' . "\n";
                    $new_content    .= '</div>' . "\n";
                } else {
                    if($input_type != 'hidden') {
                        $new_content    .= '<div class="'.$parent_class.' row">' . "\n";
                        if($label_size && $label) {
                            $new_content    .= '<label for="'.$id.'" class="col-'.$size_param.$label_size.$label_class.' form-label">'.$label.$sub_label.'</label>' . "\n";
                        } else {
                            $input_size = 12;
                        }
                        $new_content    .= '<div class="col-'.$size_param.$input_size.$class_container.'">' . "\n";
                        if($suffix || $prefix) {
                            $new_content    .= '<div class="input-group">' . "\n";
                        }
                        if($prefix) {
                            if(substr($prefix,0,5) == 'help:') {
                                $new_content    .= '<button type="button" class="btn btn-app '.$name.'-help" data-appinity-tooltip="right" aria-label="'.substr($suffix,5).'"><i class="fa-question-circle"></i></button>';
                            } else {
                                if(strpos($prefix,'fa-') !== false) $prefix = '<i class="'.$prefix.'"></i>';
                                $new_content    .= '<div class="input-group-text">'.$prefix.'</div>' . "\n";
                            }
                        }
                    }
                    if($input_type == 'textarea') {
                        $new_content    .= '<textarea class="'.$class.'" id="'.$id.'" name="'.$name.'" aria-label="'.strip_tags($label).'" data-validation="'.$validation.'"'.$other_attr.'>'.$value.'</textarea>' . "\n";
                    } elseif(in_array($type,['checkbox','switch','radio'])) {
                        if($checked == $value) {
                            $other_attr .= ' checked';
                        }
                        $checkbox_class = $type == 'switch' ? ' form-switch' : '';
                        $new_content    .= '<div class="form-check'.$checkbox_class.'">' . "\n";
                        $new_content    .= '<input type="'.$input_type.'" class="'.$class.'" id="'.$id.'" name="'.$name.'" aria-label="'.strip_tags($label).'" value="'.$value.'"'.$other_attr.'>' . "\n";
                        $new_content    .= '</div>' . "\n";
                    } else {
                        $new_content    .= '<input type="'.$input_type.'" class="'.$class.'" id="'.$id.'" name="'.$name.'" aria-label="'.strip_tags($label).'" data-validation="'.$validation.'" value="'.$value.'"'.$other_attr.'>' . "\n";
                        if($type == 'password-toggle') {
                            $new_content.= '<a href="javascript:;"><i class="fa-eye"></i></a>';
                        }
                    }
                    if($input_type != 'hidden') {
                        if($suffix) {
                            if(substr($suffix,0,5) == 'help:') {
                                $new_content    .= '<button type="button" class="btn btn-app '.$name.'-help" data-appinity-tooltip="left" aria-label="'.substr($suffix,5).'"><i class="fa-question-circle"></i></button>';
                            } else {
                                if(strpos($suffix,'fa-') !== false) $suffix = '<i class="'.$suffix.'"></i>';
                                $new_content    .= '<div class="input-group-text">'.$suffix.'</div>' . "\n";
                            }
                        }
                        if($suffix || $prefix) {
                            $new_content    .= '</div>' . "\n";
                        }
                        $new_content    .= '</div>' . "\n";
                        $new_content    .= '</div>' . "\n";
                    }
                }
            } else {
                $new_content    .= '<div class="'.$parent_class.' col-'.$size_param.$input_size.'">' . "\n";
                if($suffix || $prefix) {
                    $new_content    .= '<div class="input-group">' . "\n";
                }
                if($prefix) {
                    if(strpos($prefix,'fa-') !== false) $prefix = '<i class="'.$prefix.'"></i>';
                    $new_content    .= '<div class="input-group-text">'.$prefix.'</div>' . "\n";
                }
                if($input_type == 'textarea') {
                    $new_content    .= '<textarea class="'.$class.'" id="'.$id.'" name="'.$name.'" aria-label="'.strip_tags($label).'" placeholder="'.strip_tags($label).'" data-validation="'.$validation.'"'.$other_attr.'>'.$value.'</textarea>' . "\n";
                } elseif(in_array($type,['checkbox','switch','radio'])) {
                    if($checked == $value) {
                        $other_attr .= ' checked';
                    }
                    $checkbox_class = $type == 'switch' ? ' form-switch' : '';
                    $new_content    .= '<div class="form-check'.$checkbox_class.'">' . "\n";
                    $new_content    .= '<input type="'.$input_type.'" class="'.$class.'" id="'.$id.'" name="'.$name.'" aria-label="'.strip_tags($label).'" value="'.$value.'"'.$other_attr.'>' . "\n";
                    if($label) {
                        $new_content    .= '<label class="form-check-label" for="'.$id.'">'.$label.'</label>' . "\n";
                    }
                    $new_content    .= '</div>' . "\n";
                } else {
                    $new_content    .= '<input type="'.$input_type.'" class="'.$class.'" id="'.$id.'" name="'.$name.'" aria-label="'.strip_tags($label).'" placeholder="'.strip_tags($label).'" data-validation="'.$validation.'" value="'.$value.'"'.$other_attr.'>' . "\n";
                }
                if($suffix) {
                    if(strpos($suffix,'fa-') !== false) $suffix = '<i class="'.$suffix.'"></i>';
                    $new_content    .= '<div class="input-group-text">'.$suffix.'</div>' . "\n";
                }
                if($suffix || $prefix) {
                    $new_content    .= '</div>' . "\n";
                }
                $new_content    .= '</div>' . "\n";
            }
        }

        $html   = str_replace($val_res, $new_content, $html);

    }
    return $html;
}

function define_app_select($res, $html, $grouping = false, $res_input_default = '') {
    $validation_exist   = [];
    foreach($res[0] as $key_res => $val_res) {
        $default    = [];

        // check default
        $temp_html  = substr($html, 0, strpos($html,$val_res));
        preg_match_all('/<app-input-default(.*?)\/>/si', $temp_html, $res2);
        if(count($res2[1]) == 0 && is_array($res_input_default) && isset($res_input_default[1])) {
            $res2[1]    = $res_input_default[1];
        }
        if(count($res2[1]) > 0) {
            $str_attr       = end($res2[1]);
            $list_attr      = explode(' ',$str_attr);
            $new_list_attr  = [];
            $i              = 0;
            $open_attr      = false;
            foreach($list_attr as $l) {
                if(!$open_attr) {
                    $new_list_attr[$i]  = $l;
                } else {
                    $new_list_attr[$i]  .= ' '.$l;
                }
                if(!$open_attr && strpos($l,'="') !== false && count(explode('"',$l)) == 2) $open_attr = true;
                else if($open_attr && strpos($l,'"') !== false) $open_attr   = false;
                if(!$open_attr) $i++;
            }
            foreach($new_list_attr as $l) {
                if(trim($l)) {
                    $attr_param = explode('=',$l);
                    $attr       = trim($attr_param[0]);
                    $val        = isset($attr_param[1]) ? trim(trim($attr_param[1]),'"') : '';
                    $default[$attr] = $val;
                }
            }    
        }

        $validation_ref = isset($default['rules']) ? $default['rules'] : '';
        $_validation    = [];
        if($validation_ref) {
            if(!isset($validation_exist[$validation_ref])) {
                $_validation    = __validation($validation_ref);
                $validation_exist[$validation_ref]  = $_validation;
            } else {
                $_validation    = $validation_exist[$validation_ref];
            }
        }

        $label          = '';
        $sub_label      = '';
        $label_type     = isset($default['label-type']) ? $default['label-type'] : '';
        $id             = '';
        $name           = '';
        $validation     = '';
        $prefix         = '';
        $suffix         = '';
        $class          = 'form-select ';
        $parent_class   = 'mb-3 ';
        $size           = isset($default['size'])       ? $default['size']          : '12:12';
        $size_param     = isset($default['size-param']) ? $default['size-param']    : 'md';
        $other_attr     = '';
        $value          = '';

        $tag            = str_replace($res[1][$key_res],'',$val_res);
        $attr           = trim(str_replace(['app-select','</','<','>'],'',$tag));
        $list_attr      = explode(' ',$attr);
        $x_attr         = [];
        $i              = 0;
        $open_attr      = false;
        foreach($list_attr as $l) {
            if(!$open_attr) {
                $x_attr[$i]  = $l;
            } else {
                $x_attr[$i]  .= ' '.$l;
            }
            if(!$open_attr && strpos($l,'="') !== false && count(explode('"',$l)) == 2) $open_attr = true;
            else if($open_attr && strpos($l,'"') !== false) $open_attr   = false;
            if(!$open_attr) $i++;
        }

        foreach($x_attr as $l) {
            if(trim($l)) {
                $attr_param = explode('=',$l);
                $attr       = trim($attr_param[0]);
                $val        = isset($attr_param[1]) ? trim(trim($attr_param[1]),'"') : '';
                if($attr == 'label' && $val)       $label      = $val;
                else if($attr == 'sub-label' && $val)   $sub_label      = '<small class="d-block">'.$val.'</small>';
                else if($attr == 'label-type' && $val)  $label_type     = $val;
                else if($attr == 'name' && $val)        $name           = $id = $val;
                else if($attr == 'id' && $val)          $id             = $val;
                else if($attr == 'validation' && $val)  $validation     = $val;
                else if($attr == 'class' && $val)       $class         .= $val;
                else if($attr == 'parent-class' && $val)$parent_class  .= $val;
                else if($attr == 'size' && $val)        $size           = $val;
                else if($attr == 'size-param' && $val)  $size_param     = $val;
                else if($attr == 'value' && $val)       $value          = $val;
                else if(in_array($attr,['prefix','prepend']) && $val)   $prefix    = $val;
                else if(in_array($attr,['suffix','append']) && $val)    $suffix    = $val;
                else if(!in_array($attr,['label','sub-label','label-type','name','id','validation','class','size',
                                        'size-param','value','append','prepend','suffix','prefix'])) {
                    $other_attr    .= " {$l}";
                }
            }
        }
        if(isset($_validation[$name])) {
            $validation = $_validation[$name];
            preg_match_all('/setting{(.*?)}/si', $validation, $res_val);
            foreach($res_val[0] as $key_res_val => $val_res_val) {
                $validation    = str_replace($val_res_val, setting(trim($res_val[1][$key_res_val])), $validation);
            }
        }
        $new_content        = '';
        $class_container    = '';

        $x_size         = explode(':',$size);
        $label_size     = 0;
        $input_size     = 12;
        if(count($x_size) == 1 && (int) $x_size[0] > 0 && (int) $x_size[0] <= 12) {
            $label_size = $x_size[0];
            $input_size = $x_size[0];
        } elseif(count($x_size) == 2) {
            if((int) $x_size[0] > 0 && (int) $x_size[0] <= 12) $label_size  = $x_size[0];
            if((int) $x_size[1] > 0 && (int) $x_size[1] <= 12) $input_size  = $x_size[1];
        }
        if(!in_array($size_param,['none','sm','md','lg','xl'])) $size_param    = 'md-';
        else if($size_param == 'none') $size_param = '';
        else $size_param .= '-';
        $label_class    = strpos($validation,'required') !== false ? ' required' : '';
        
        $x_class        = explode(' ',$class);
        $a_class        = [];
        foreach($x_class as $xc) {
            $a_class[$xc]   = $xc;
        }
        $class          = implode(" ",$a_class);

        if(!$grouping) {
            if($label && $label_type == 'floating') {
                $new_content    .= '<div class="'.$parent_class.' form-floating">' . "\n";
                $new_content    .= '<select class="'.$class.'" id="'.$id.'" name="'.$name.'" aria-label="'.strip_tags($label).'" data-validation="'.$validation.'" placeholder="'.strip_tags($label).'" value="'.$value.'"'.$other_attr.'>' . "\n";
                $new_content    .= $res[1][$key_res];
                $new_content    .= '</select>' . "\n";
                $new_content    .= '<label for="'.$id.'" class="'.$label_class.'">'.$label.$sub_label.'</label>' . "\n";
                $new_content    .= '</div>' . "\n";
            } else {
                $new_content    .= '<div class="'.$parent_class.' row">' . "\n";
                if($label_size && $label) {
                    $new_content    .= '<label for="'.$id.'" class="col-'.$size_param.$label_size.$label_class.' form-label">'.$label.$sub_label.'</label>' . "\n";
                } else {
                    $input_size = 12;
                }
                $new_content    .= '<div class="col-'.$size_param.$input_size.$class_container.'">' . "\n";
                if($suffix || $prefix) {
                    $new_content    .= '<div class="input-group">' . "\n";
                }
                if($prefix) {
                    if(strpos($prefix,'fa-') !== false) $prefix = '<i class="'.$prefix.'"></i>';
                    $new_content    .= '<div class="input-group-text">'.$prefix.'</div>' . "\n";
                }
                $new_content    .= '<select class="'.$class.'" id="'.$id.'" name="'.$name.'" aria-label="'.strip_tags($label).'" data-validation="'.$validation.'"'.$other_attr.'>' . "\n";
                $option_content = $res[1][$key_res];
                if(strpos($other_attr,'multiple') !== false) {
                    $option_val = explode(',',$value);
                    foreach($option_val as $ov) {
                        $option_content = str_replace('value="'.$ov.'"','value="'.$ov.'" selected',$option_content);
                    }
                } else {
                    $option_content = str_replace('value="'.$value.'"','value="'.$value.'" selected',$option_content);
                }
                $new_content    .= $option_content;
                $new_content    .= '</select>' . "\n";
                if($suffix) {
                    if(strpos($suffix,'fa-') !== false) $suffix = '<i class="'.$suffix.'"></i>';
                    $new_content    .= '<div class="input-group-text">'.$suffix.'</div>' . "\n";
                }
                if($suffix || $prefix) {
                    $new_content    .= '</div>' . "\n";
                }
                $new_content    .= '</div>' . "\n";
                $new_content    .= '</div>' . "\n";
            }
        } else {
            $new_content    .= '<div class="'.$parent_class.' col-'.$size_param.$input_size.'">' . "\n";
            if($suffix || $prefix) {
                $new_content    .= '<div class="input-group">' . "\n";
            }
            if($prefix) {
                if(strpos($prefix,'fa-') !== false) $prefix = '<i class="'.$prefix.'"></i>';
                $new_content    .= '<div class="input-group-text">'.$prefix.'</div>' . "\n";
            }
            $new_content    .= '<select class="'.$class.'" id="'.$id.'" name="'.$name.'" aria-label="'.strip_tags($label).'" data-validation="'.$validation.'" placeholder="'.strip_tags($label).'"'.$other_attr.'>' . "\n";
            $option_content = $res[1][$key_res];
            if(strpos($other_attr,'multiple') !== false) {
                $option_val = explode(',',$value);
                foreach($option_val as $ov) {
                    $option_content = str_replace('value="'.$ov.'"','value="'.$ov.'" selected',$option_content);
                }
            } else {
                $option_content = str_replace('value="'.$value.'"','value="'.$value.'" selected',$option_content);
            }
            $new_content    .= $option_content;
            $new_content    .= '</select>' . "\n";
            if($suffix) {
                if(strpos($suffix,'fa-') !== false) $suffix = '<i class="'.$suffix.'"></i>';
                $new_content    .= '<div class="input-group-text">'.$suffix.'</div>' . "\n";
            }
            if($suffix || $prefix) {
                $new_content    .= '</div>' . "\n";
            }
            $new_content    .= '</div>' . "\n";
        }

        $html   = str_replace($val_res, $new_content, $html);
    }
    return $html;
}

function generate_token() {
    $rand       = strtotime(date('Y-m-d H:i:s'));
    $key        = $rand + 260992;
    $id_user    = (int) user('id');
    $encode_id  = encode_id([$id_user, $rand],'app-'.$key);
    $encode_key = encode_id($key);
    return [
        base64_encode($encode_key . '?!&$' . $encode_id),
        $encode_id
    ];
}

function token_validation($request_token,$client_token) {
    $CI             = get_instance();
    $encode_token   = base64_decode($request_token);
    $parse_token    = decode_id($encode_token);
    $client_token   = isset(decode_id($client_token)[0]) ? decode_id($client_token)[0] : '';
    $result         = false;
    $id_user        = 0;
    if($CI->session->userdata('id')) {
        $id_user    = $CI->session->userdata('id');
    }

    if(is_array($parse_token) && count($parse_token) == 3) {
        if($id_user == $parse_token[0] && 
            $client_token == $parse_token[2] &&
            ($parse_token[2] - $parse_token[1]) == 260992 && 
            abs($parse_token[1] - strtotime(date('Y-m-d H:i:s'))) <= 7200) {
            $result = true;
        }
    }
    return $result;
}