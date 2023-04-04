<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class General extends BE_Controller {

    public function index() {
        $source = base64_decode(get('resource'));
        if($source && file_exists($source)) {
            $this->load->helper('download');
            force_download($source, NULL);
        }
    }

}