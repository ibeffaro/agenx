<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Checkin extends BE_Controller
{

    public function index()
    {
        $data['user_group'] = get_data('user_group', 'is_active = 1')->result();
        render($data);
    }

    public function data()
    {
        $config = [
            'where'     => ['is_active' => 1],
            'order_by'  => 'id',
            'order'     => 'desc'
        ];
        $data   = generate_data($config);
        render($data, 'json');
    }

    public function save()
    {
        $data       = post();
        $cekOrder   = get_data('order', 'nomor_id', $data['nomor_id'])->row();
        if (isset($cekOrder->nomor_id) && $cekOrder->nomor_id && $cekOrder->is_active == 0) {
            $update = update_data('order', ['is_active' => 1], 'nomor_id', $cekOrder->nomor_id);
            if ($update) {
                $response = [
                    'status'    => 'failed',
                    'message'   => 'Nomor ID berhasil di Gunakan'
                ];
            } else {
                $response = [
                    'status'    => 'failed',
                    'message'   => 'Nomor ID gagal di Gunakan'
                ];
            }
        } elseif (isset($cekOrder->nomor_id) && $cekOrder->nomor_id && $cekOrder->is_active == 1) {
            $response = [
                'status'    => 'failed',
                'message'   => 'Nomor ID sudah pernah di Gunakan'
            ];
        } else {
            $response = [
                'status'    => 'failed',
                'message'   => 'Nomor ID tidak terdaftar'
            ];
        }
        render($response, 'json');
    }

    public function delete()
    {
        $response   = destroy_data('order', get());
        render($response, 'json');
    }
}
