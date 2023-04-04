<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Page_builder extends BE_Controller {

    var $dir;
    function __construct() {
        parent::__construct();
        $this->dir          = SCPATH . 'pages';
        if(!is_dir($this->dir)){
            $oldmask = umask(0);
            mkdir($this->dir,0777);
            umask($oldmask);
        }
    }

    public function index() {
        $data['list_page']  = [];
        if(is_dir($this->dir)) {
            $scan   = scandir($this->dir);
            foreach($scan as $s) {
                if(strpos($s,'.page.txt') !== false) $data['list_page'][]   = $s;
            }
        }
        render($data);
    }

    public function add() {
        $filename   = strtolower(preg_replace("/[^a-zA-Z0-9_]+/", "", post('nama'))) . '.page.txt';
        $loc_file   = $this->dir . DIRECTORY_SEPARATOR . $filename;
        $response   = [
            'status'    => 'failed',
            'message'   => lang('nama_halaman_sudah_ada')
        ];
        if(!file_exists($loc_file)) {
            $dt     = [
                'main'  => []
            ];
            $content    = serialize($dt);
            $handle     = fopen($loc_file, "wb");
            if($handle) {
                fwrite ( $handle, $content );
            }
            fclose($handle);
            $oldmask = umask(0);
            chmod($loc_file, 0777);
            umask($oldmask);
            if(file_exists($loc_file)) {
                $response   = [
                    'status'    => 'success',
                    'message'   => lang('data_berhasil_disimpan'),
                    'href'      => base_url('development/page-builder/edit/'.encode_string($filename))
                ];
            }
        }

        render($response,'json');
    }

    function delete() {
        $page       = decode_string(get('key'));
        $filename   = $this->dir . DIRECTORY_SEPARATOR . $page;
        if(file_exists($filename)) {
            @unlink($filename);
        }
        render([
            'status'    => 'success',
            'message'   => lang('data_berhasil_dihapus')
        ],'json');
    }

}