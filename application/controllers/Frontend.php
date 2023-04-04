<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Frontend extends FE_Controller
{

    public function order()
    {
        $data['title'] = 'Order';
        render($data);
    }

    public function do_order()
    {
        $data = post();
        $data['nomor_id'] = date('YmdHis');

        if (!$data['id']) {
            $data['created_at'] = datetime_now();
            $data['created_by'] = $data['nama'];
            $save   = insert_data('order', $data);
        } else {
            $data['updated_at'] = datetime_now();
            $data['updated_by'] = user('nama');
            $save   = update_data('order', $data, 'id', $data['id']);
        }

        if ($save) {
            $response['result']   = get_data('order', 'id', $save)->row_array();
        }
        render($response);
    }
}
