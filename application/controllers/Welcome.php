<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Welcome extends BE_Controller
{

    public function index()
    {
        $this->load->library('user_agent');
        $data['title']            = lang('selamat_datang');
        $data['ip']                = $this->input->ip_address();
        $data['informasi']      = get_data('informasi', [
            'where'             => [
                'tanggal_mulai <='      => date('Y-m-d'),
                'tanggal_selesai >='    => date('Y-m-d'),
                '(id_group = "" OR FIND_IN_SET(' . user('id_group') . ',id_group))'
            ],
            'order_by'          => 'updated_at',
            'order'             => 'DESC'
        ])->result();

        if ($this->agent->is_browser()) {
            $agent = $this->agent->browser() . ' ' . $this->agent->version();
        } elseif ($this->agent->is_robot()) {
            $agent = $this->agent->robot();
        } elseif ($this->agent->is_mobile()) {
            $agent = $this->agent->mobile();
        } else {
            $agent = 'Unidentified User Agent';
        }

        $data['agent']            = $agent;
        render($data, 'view:welcome');
    }
}
