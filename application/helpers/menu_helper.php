<?php
defined('BASEPATH') OR exit('No direct script access allowed');

function info_data_menu() {
    $data           = [];
    /*
    if(user('id')) {
        // Hitung jumlah data yang butuh approval COG
        $where_ac       = [
            'status_cog'    => 1
        ];
        if(user('id_cabang')) $where_ac['id_cabang']    = user('id_cabang');
        $approval_cog   = get_data('client_demand',[
            'select'    => 'COUNT(id) AS jml',
            'where'     => $where_ac
        ])->row();
        $data['oti/approval-cog']   = (int) $approval_cog->jml;

        
        // Hitung jumlah data yang butuh input COG
        $where_ic       = [
            'status_cog' => 0
        ];
        if(user('id_cabang')) $where_ac['id_cabang']    = user('id_cabang');
        $input_cog   = get_data('client_demand',[
            'select'    => 'COUNT(id) AS jml',
            'where'     => $where_ic
        ])->row();
        $data['oti/input-cog']   = (int) $input_cog->jml;
    }
    */
    return $data;
}