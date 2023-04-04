<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Order extends BE_Controller
{

    public function index()
    {
        $data['user_group'] = get_data('user_group', 'is_active = 1')->result();
        render($data);
    }

    public function data()
    {
        $config = [
            'order_by'  => 'id',
            'order'     => 'desc'
        ];
        $data   = generate_data($config);
        render($data, 'json');
    }

    public function get_data()
    {
        $data       = get_data('order', get())->row_array();
        render($data, 'json');
    }

    public function save()
    {
        $data       = post();
        $response   = save_data('order', $data);
        render($response, 'json');
    }

    public function delete()
    {
        $response   = destroy_data('order', get());
        render($response, 'json');
    }
}
