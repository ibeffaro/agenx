<?php
defined('BASEPATH') or exit('No direct script access allowed');

function asset_url($str = '')
{
    return base_url('assets/') . $str;
}

function debug($data = '')
{
    echo '<pre>';
    print_r($data);
    echo '</pre>';
}

function setting($key = '')
{
    $CI     = get_instance();
    return $CI->config->item('setting_' . $key) ? $CI->config->item('setting_' . $key) : '';
}

function user($key = '')
{
    $CI     = get_instance();
    return $CI->config->item('user_' . $key) ? $CI->config->item('user_' . $key) : '';
}

function lang($key = '', $default = '', $replace = [])
{
    $CI     = get_instance();
    if (!is_array($replace)) {
        $replace    = [$replace];
    }
    $default_label = ENVIRONMENT == 'production' ? ucwords(strtolower(str_replace('_', ' ', $key))) : '<em class="text-decoration-line-through">{' . $key . '}</em>';
    if ($default) $default_label = $default;
    $text   = $CI->config->item('lang_' . $key) ? $CI->config->item('lang_' . $key) : $default_label;
    foreach ($replace as $k => $v) {
        $text   = str_replace('{$' . ($k + 1) . '}', $v, $text);
    }
    return $text;
}

function adjustBrightness($hex, $steps = -20)
{
    $steps = max(-255, min(255, $steps));
    $hex = str_replace('#', '', $hex);
    if (strlen($hex) == 3) {
        $hex = str_repeat(substr($hex, 0, 1), 2) . str_repeat(substr($hex, 1, 1), 2) . str_repeat(substr($hex, 2, 1), 2);
    }
    $color_parts = str_split($hex, 2);
    $return = '#';

    foreach ($color_parts as $color) {
        $color   = hexdec($color);
        $color   = max(0, min(255, $color + $steps));
        $return .= str_pad(dechex($color), 2, '0', STR_PAD_LEFT);
    }

    return $return;
}

function getBrightness($hex)
{
    $hex = str_replace('#', '', $hex);
    $c_r = hexdec(substr($hex, 0, 2));
    $c_g = hexdec(substr($hex, 2, 2));
    $c_b = hexdec(substr($hex, 4, 2));
    $calc = (($c_r * 299) + ($c_g * 587) + ($c_b * 114)) / 1000;

    $result = 'dark';
    if ($calc > 175) {
        $result = 'light';
    }
    return $result;
}

function hexToRgb($hex, $alpha = false, $return = 'string')
{
    $hex        = str_replace('#', '', $hex);
    $length     = strlen($hex);
    $rgb['r']   = hexdec($length == 6 ? substr($hex, 0, 2) : ($length == 3 ? str_repeat(substr($hex, 0, 1), 2) : 0));
    $rgb['g']   = hexdec($length == 6 ? substr($hex, 2, 2) : ($length == 3 ? str_repeat(substr($hex, 1, 1), 2) : 0));
    $rgb['b']   = hexdec($length == 6 ? substr($hex, 4, 2) : ($length == 3 ? str_repeat(substr($hex, 2, 1), 2) : 0));
    $string_rgb = 'rgb(' . implode(',', $rgb) . ')';
    if ($alpha) {
        $rgb['a']   = $alpha;
        $string_rgb = 'rgba(' . implode(',', $rgb) . ')';
    }
    if ($return == 'string') {
        return $string_rgb;
    } else {
        return $rgb;
    }
}

function upload_path($dir = '')
{
    $path   = 'assets/uploads/';
    if ($dir)
        return $path . $dir . '/';
    else
        return $path;
}

function delete_dir($directory, $empty = false)
{
    if (substr($directory, -1) == "/") {
        $directory = substr($directory, 0, -1);
    }

    if (!file_exists($directory) || !is_dir($directory)) {
        return false;
    } elseif (!is_readable($directory)) {
        return false;
    } else {
        $directoryHandle = opendir($directory);

        while ($contents = readdir($directoryHandle)) {
            if ($contents != '.' && $contents != '..') {
                $path = $directory . "/" . $contents;

                if (is_dir($path)) {
                    delete_dir($path);
                } else {
                    @unlink($path);
                }
            }
        }

        closedir($directoryHandle);

        if ($empty == false) {
            if (!rmdir($directory)) {
                return false;
            }
        }

        return true;
    }
}

function delete_temp_folder($filename)
{
    $file   = basename($filename);
    $dir    = trim(str_replace($file, '', $filename));
    if ($dir) {
        delete_dir($dir);
    }
}

function file_upload_max_size()
{
    static $max_size = -1;

    if ($max_size < 0) {
        $post_max_size = parse_size(ini_get('post_max_size'));
        if ($post_max_size > 0) {
            $max_size = $post_max_size;
        }

        $upload_max = parse_size(ini_get('upload_max_filesize'));
        if ($upload_max > 0 && $upload_max < $max_size) {
            $max_size = $upload_max;
        }
    }
    return $max_size;
}

function parse_size($size)
{
    $unit = preg_replace('/[^bkmgtpezy]/i', '', $size);
    $size = preg_replace('/[^0-9\.]/', '', $size);
    if ($unit) {
        return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
    } else {
        return round($size);
    }
}

function encode_id($id = 0, $key = 0)
{
    if (!$key) $key = round((strtotime(date('Y-m-d')) / 1000) * 2);
    if (!is_array($id)) {
        $id = [$id, rand()];
    }
    $hashids    = new Hashids\Hashids((string) $key);
    return $hashids->encode($id);
}

function decode_id($encode_id = '', $key = 0)
{
    if (!$key) $key = round((strtotime(date('Y-m-d')) / 1000) * 2);
    $hashids    = new Hashids\Hashids((string) $key);
    return $hashids->decode($encode_id);
}

function encode_string($str = '', $key = 0)
{
    if (!$key) $key = round((strtotime(date('Y-m-d')) / 1000) * 2);
    $new_str    = '';
    $serialize  = [];
    for ($i = 0; $i < strlen($str); $i++) {
        $serialize[]    = ord($str[$i]);
    }
    if (count($serialize) > 0) {
        $new_str    = encode_id($serialize, $key);
    }
    return $new_str;
}

function decode_string($str = '', $key = 0)
{
    if (!$key) $key = round((strtotime(date('Y-m-d')) / 1000) * 2);
    $new_str    = decode_id($str, $key);
    $result     = '';
    if (isset($new_str) && is_array($new_str)) {
        foreach ($new_str as $n) {
            $result .= chr($n);
        }
    }
    return $result;
}

function datetime_now($to_time = false)
{
    if ($to_time) {
        return strtotime(date('Y-m-d H:i:s'));
    } else {
        return date('Y-m-d H:i:s');
    }
}

function date_now($to_time = false)
{
    if ($to_time) {
        return strtotime(date('Y-m-d'));
    } else {
        return date('Y-m-d');
    }
}

function date_lang($date, $full = false, $min = false)
{
    $list_month = [
        1   => lang('januari'),
        2   => lang('februari'),
        3   => lang('maret'),
        4   => lang('april'),
        5   => lang('mei'),
        6   => lang('juni'),
        7   => lang('juli'),
        8   => lang('agustus'),
        9   => lang('september'),
        10  => lang('oktober'),
        11  => lang('november'),
        12  => lang('desember'),
    ];
    if (is_array($date) && count($date) == 2) {
        $x_date1    = explode(' ', $date[0]);
        $x_date2    = explode(' ', $date[1]);

        if (strtotime($x_date1[0]) > strtotime($x_date2[0])) {
            $x_date_temp    = $x_date2;
            $x_date2        = $x_date1;
            $x_date1        = $x_date_temp;
        }

        $x_dt1      = explode('-', $x_date1[0]);
        $x_dt2      = explode('-', $x_date2[0]);
        $result     = '';
        if (count($x_dt1) == 3 && count($x_dt2) == 3) {
            $day1   = $x_dt1[2];
            $day2   = $x_dt2[2];
            $month1 = (int) $x_dt1[1];
            $month2 = (int) $x_dt2[1];
            $year1  = $x_dt1[0];
            $year2  = $x_dt2[0];
            if ($month1 > 0 && $month1 <= 12 && $month2 > 0 && $month2 <= 12) {
                $ln_month1  = $list_month[$month1];
                $ln_month2  = $list_month[$month2];
                if ($full) {
                    $ln_month1  = substr($ln_month1, 0, 3);
                    $ln_month2  = substr($ln_month2, 0, 3);
                }

                if ($day1 == $day2 && $month1 == $month2 && $year1 == $year2) {
                    $result = "{$day1} {$ln_month1} {$year1}";
                } elseif ($month1 == $month2 && $year1 == $year2) {
                    $result = "{$day1} - {$day2} {$ln_month1} {$year1}";
                } elseif ($year1 == $year2) {
                    $result = "{$day1} {$ln_month1} - {$day2} {$ln_month2} {$year1}";
                } else {
                    $result = "{$day1} {$ln_month1} {$year1} - {$day2} {$ln_month2} {$year2}";
                }
            }
        }
        return $result;
    } else {
        $x_date     = explode(' ', $date);
        $date       = $x_date[0];
        $list_day   = [
            'Mon'   => lang('senin'),
            'Tue'   => lang('selasa'),
            'Wed'   => lang('rabu'),
            'Thu'   => lang('kamis'),
            'Fri'   => lang('jumat'),
            'Sat'   => lang('sabtu'),
            'Sun'   => lang('minggu')
        ];
        $strdate    = strtotime($date);
        $day        = date('d', $strdate);
        $month      = (int) date('m', $strdate);
        $year       = date('Y', $strdate);
        $day_name   = date('D', $strdate);

        $lang_month = $list_month[$month];
        $lang_day   = $list_day[$day_name];
        if ($min) {
            $lang_month = substr($lang_month, 0, 3);
            $lang_day   = substr($lang_day, 0, 3);
        }

        $new_date   = $day . ' ' . $lang_month . ' ' . $year;
        if (isset($x_date[1])) {
            $new_date   .= ' ' . $x_date[1];
        }
        if ($full) {
            $new_date   = $lang_day . ', ' . $new_date;
        }
        return $new_date;
    }
}

function enc_password($password)
{
    return password_hash($password, PASSWORD_DEFAULT, array('cost' => COST));
}

function get($param = "")
{
    $CI     = get_instance();
    if ($param) return $CI->input->get($param);
    else return $CI->input->get();
}

function postUpper($post = "")
{
    $x  = post($post);
    if (is_array($x)) {
        foreach ($x as $k => $v) {
            if (!is_array($v)) {
                $v  = strtoupper($v);
            }
            $x[$k]  = $v;
        }
    } else {
        $x  = strtoupper($x);
    }
    return $x;
}

function post($post = "", $data_type = "")
{
    $CI     = get_instance();
    $r      = $CI->input->post($post);
    if (!$post) {
        $data   = [];
        $_post  = $CI->input->post();
        foreach ($_post as $field => $r) {
            if (!is_array($r)) {
                if ($field == 'password') {
                    if (trim($r)) {
                        $r  = enc_password(xss_clean($r));
                    } else {
                        $r  = 'unsetPost';
                    }
                } elseif (is_currency($r)) {
                    $r  = return_number($r);
                } elseif (is_date($r) || is_daterange($r)) {
                    $r  = return_date($r);
                }
                if ($r === '__CURRENT_DATE__') {
                    $r  = date('Y-m-d H:i:s');
                }
                if ($r != 'unsetPost') {
                    $data[$field]   = xss_clean($r);
                }
            }
        }
        return $data;
    } else {
        $r      = $CI->input->post($post);
        if (!is_array($r)) {
            if (is_currency($r)) {
                $r  = return_number($r);
            } elseif (is_date($r) || is_daterange($r)) {
                $r  = return_date($r);
            } elseif ($data_type == 'html') {
                $r  = html_escape($r);
            } elseif ($data_type == 'password') {
                if (trim($r)) {
                    $r  = enc_password(xss_clean($r));
                }
            }
            return xss_clean($r);
        } else return $r;
    }
}

function save_data($table = '', $data = '', $force = false, $config = [])
{
    $CI             = get_instance();
    $header         = $CI->input->request_headers();
    $reference      = isset($header['X-Data-Ref']) ? $header['X-Data-Ref'] : '';
    $field_ref      = isset($header['X-Field-Ref']) ? $header['X-Field-Ref'] : '';

    if (!$reference && isset($_SERVER['HTTP_X_DATA_REF']))   $reference  = $_SERVER['HTTP_X_DATA_REF'];
    if (!$field_ref && isset($_SERVER['HTTP_X_FIELD_REF']))  $field_ref  = $_SERVER['HTTP_X_FIELD_REF'];

    if ($field_ref && isset($data['primary_key']) && $data['primary_key']) {
        $field_ref  = $data['primary_key'];
    }
    $access         = get_access($reference);
    $validation     = __validation($table);
    $file_path      = __rules_path($table);
    $valid          = true;
    $message        = '';
    $status         = 'failed';
    $response_data  = [];
    $tableField     = get_fields($table);
    $primary_key    = $field_ref;
    $listFields     = [];
    foreach ($tableField as $f) {
        if ($f->primary_key) {
            $primary_key        = $f->name;
        }
        $listFields[$f->name]   = $f->name;
    }
    if ($primary_key && !$field_ref) {
        $field_ref  = $primary_key;
    }
    if (is_array($data) && count($data) > 1) {
        foreach ($data as $k_data => $v_data) {
            if (isset($validation[$k_data])) {
                $val            = trim($v_data);
                $x_validaiton   = explode('|', $validation[$k_data]);
                foreach ($x_validaiton as $z) {
                    $p = explode(':', $z);
                    $l = isset($p[1]) && $p[1] ? $p[1] : 0;
                    if ($p[0] == 'unique_group' && $val) {
                        $unique_group[$k]   = $field2;
                        $ug_name[$k]        = $v['name'];
                    }
                    if ($message == '') {
                        if ($p[0] == 'required' && !$val) {
                            $message = '{' . $k_data . '} ' . lang('harus_diisi');
                            $valid = false;
                        } elseif ($p[0] == 'length' && strlen($val) != $l && $val) {
                            $message = '{' . $k_data . '} ' . lang('harus_n_karakter', '', [$l]);
                            $valid = false;
                        } elseif ($p[0] == 'max-length' && strlen($val) > $l && $val) {
                            $message = '{' . $k_data . '} ' . lang('maksimal_n_karakter', '', [$l]);
                            $valid = false;
                        } elseif ($p[0] == 'min-length' && strlen($val) < $l && $val) {
                            $message = '{' . $k_data . '} ' . lang('minimal_n_karakter', '', [$l]);
                            $valid = false;
                        } elseif ($p[0] == 'numeric' && !is_numeric(str_replace('.', '', $val)) && $val) {
                            $message = '{' . $k_data . '} ' . lang('harus_diisi_format_angka');
                            $valid = false;
                        } elseif ($p[0] == 'letter' && !preg_match("/^[a-zA-Z_\-\/]+$/", $val) && $val) {
                            $message = '{' . $k_data . '} ' . lang('harus_diisi_format_huruf');
                            $valid = false;
                        } elseif ($p[0] == 'alphanumeric' && !preg_match("/^[a-zA-Z0-9_\-\/]+$/", $val) && $val) {
                            $message = '{' . $k_data . '} ' . lang('harus_diisi_format_huruf_atau_angka');
                            $valid = false;
                        } elseif ($p[0] == 'email' && !filter_var($val, FILTER_VALIDATE_EMAIL) && $val) {
                            $message = '{' . $k_data . '} ' . lang('harus_diisi_format_email') . ' (ex@email.xx)';
                            $valid = false;
                        } elseif ($p[0] == 'equal' && $val != post($l)) {
                            $message = $v['name'] . ' ' . lang('tidak_cocok');
                            $valid = false;
                        } elseif ($p[0] == 'min' && is_numeric($val) && $val < $l) {
                            $message = '{' . $k_data . '} ' . lang('tidak_boleh_kurang_dari_n', '', [$l]);
                            $valid = false;
                        } elseif ($p[0] == 'max' && is_numeric($val) && $val > $l) {
                            $message = '{' . $k_data . '} ' . lang('tidak_boleh_lebih_dari_n', '', [$l]);
                            $valid = false;
                        } elseif ($p[0] == 'unique' && $val) {
                            $arr = array(
                                $k_data => $v_data
                            );
                            if (isset($data[$primary_key]) && $data[$primary_key]) $arr[$primary_key . ' !='] = $data[$primary_key];
                            $check = get_data($table, $arr)->row();
                            if (isset($check->id)) {
                                $message    = '{' . $k_data . '} ' . ' "' . $val . '" ' . lang('sudah_ada');
                                $status     = 'info';
                                $valid      = false;
                            }
                        }
                    }
                }
            }
        }
    }
    foreach ($file_path as $fn => $fp) {
        if (isset($data[$fn])) {
            if ($data[$fn]) {
                $filename   = $data[$fn];
                $ext        = pathinfo($filename, PATHINFO_EXTENSION);
                if (is_dir(FCPATH . upload_path()) && !is_dir(FCPATH . $fp)) {
                    $oldmask = umask(0);
                    mkdir(FCPATH . $fp, 0777);
                    umask($oldmask);
                }
                if ($ext) {
                    $newname    = md5(uniqid() . uniqid() . uniqid()) . '.' . $ext;
                    if (@copy(FCPATH . $filename, FCPATH . $fp . $newname)) {
                        $data[$fn]  = $newname;
                    } else {
                        unset($data[$fn]);
                    }
                    $flname     = basename($filename);
                    $temp_dir   = str_replace($flname, '', $filename);
                    if ($temp_dir) {
                        delete_dir(FCPATH . $temp_dir);
                    }
                }
            } else {
                unset($data[$fn]);
            }
        }
    }

    if (!$message) {
        $message    = lang('data_gagal_disimpan');
    }

    $_act   = '';
    if ($valid) {
        $table      = table_prefix($table);
        $dataMatch  = [];
        $valKey     = '';
        foreach ($listFields as $lf) {
            if (isset($data[$lf])) {
                $dataMatch[$lf] = $data[$lf];
            }
        }
        $autocode = get_data('kode', [
            'is_active' => 1,
            'tabel'     => $table
        ])->result();
        $field_autocode = [];

        $autocode_fix       = [];
        $autocode_ignore    = [];
        if (isset($config['autocode_ignore'])) {
            if (is_array($config['autocode_ignore'])) $autocode_ignore = $config['autocode_ignore'];
            else $autocode_ignore   = [$config['autocode_ignore']];
        }
        if (isset($config['autocode'])) {
            if (is_array($config['autocode'])) $autocode_fix = $config['autocode'];
            else $autocode_fix   = [$config['autocode']];
        }

        foreach ($autocode as $a) {
            if (!in_array($a->kolom, $autocode_ignore)) {
                $autocode_exec  = true;
                if (count($autocode_fix) && !in_array($a->kolom, $autocode_fix)) {
                    $autocode_exec  = false;
                }
                if (isset($dataMatch[$a->kolom]) && $dataMatch[$a->kolom] && $dataMatch[$a->kolom] != '-') $autocode_exec = false;
                if ($autocode_exec) {
                    $dataMatch[$a->kolom]       = generate_code($table, $a->kolom, $data);
                    $field_autocode[$a->kolom]  = $a->kolom;
                }
            }
        }
        if (isset($dataMatch[$field_ref]) && $dataMatch[$field_ref]) {
            if (post('last_' . $field_ref)) {
                $valKey = post('last_' . $field_ref);
            } else {
                $valKey = $dataMatch[$field_ref];
            }
            $last_data  = get_data($table, $field_ref, $valKey)->row_array();
        }

        if (isset($dataMatch[$field_ref]) && $dataMatch[$field_ref] && isset($last_data) && is_array($last_data) && count($last_data)) {
            $_act       = 'edit';
            $editable   = true;
            if (isset($last_data['not_editable']) && $last_data['not_editable']) $editable = false;
            if (($access['edit'] || $force) && $editable) {
                if (isset($listFields['updated_at'])) $dataMatch['updated_at']   = date_now();
                if (isset($listFields['updated_by'])) $dataMatch['updated_by']   = user('nama');
                if (isset($last_data) && is_array($last_data) && count($last_data) > 0) {
                    foreach ($last_data as $keyLastData => $valueLastData) {
                        if (isset($field_autocode[$keyLastData]) && $valueLastData) {
                            unset($dataMatch[$keyLastData]);
                        }
                    }
                }
                $save       = update_data($table, $dataMatch, $field_ref, $valKey);
                if ($save) {
                    foreach ($file_path as $fn => $fp) {
                        if (
                            isset($last_data[$fn]) && $last_data[$fn] &&
                            strpos($last_data[$fn], 'default') == false && isset($data[$fn]) && $data[$fn]
                        ) {
                            @unlink(FCPATH . $fp . $last_data[$fn]);
                        }
                    }
                    $status         = 'success';
                    $message        = lang('data_berhasil_diperbarui');
                    $response_data  = get_data($table, $field_ref, $dataMatch[$field_ref])->row_array();
                    if (!isset($response_data[$field_ref])) {
                        $response_data  = $dataMatch;
                    }
                }
            } else {
                $message    = lang('izin_ditolak');
            }
        } else {
            $_act   = 'input';
            if ($access['input'] || $force) {
                if (isset($listFields['created_at'])) $dataMatch['created_at']   = date_now();
                if (isset($listFields['created_by'])) $dataMatch['created_by']   = user('nama');
                if (isset($listFields['updated_at'])) $dataMatch['updated_at']   = date_now();
                if (isset($listFields['updated_by'])) $dataMatch['updated_by']   = user('nama');
                $save   = insert_data($table, $dataMatch);
                if ($save) {
                    $status         = 'success';
                    $message        = lang('data_berhasil_disimpan');
                    $response_data  = get_data($table, $field_ref, $save)->row_array();
                    if (!isset($response_data[$field_ref])) {
                        $response_data  = $dataMatch;
                    }
                }
            } else {
                $message    = lang('izin_ditolak');
            }
        }
    }

    return [
        'status'    => $status,
        'message'   => $message,
        'data'      => $response_data,
        'menu'      => isset($access['menu']) ? $access['menu'] : '',
        'action'    => $_act
    ];
}

function destroy_data($tabel = '', $field = '', $id = '', $child = [], $force = false)
{
    $CI             = get_instance();
    $header         = $CI->input->request_headers();
    $reference      = isset($header['X-Data-Ref']) ? $header['X-Data-Ref'] : '';
    if (!$reference && isset($_SERVER['HTTP_X_DATA_REF']))   $reference  = $_SERVER['HTTP_X_DATA_REF'];

    $access         = get_access($reference);
    $file_path      = __rules_path($tabel);
    $proccess       = true;
    if (is_array($field)) {
        if (count($field) == 0) {
            $proccess   = false;
        } elseif (count($field) == 1) {
            foreach ($field as $f => $i) {
                $field  = $f;
                $id     = $i;
            }
        }
    }

    $status     = 'failed';
    if (($access['delete'] || $force) && $proccess) {
        $delete = false;
        if (is_array($field)) {
            $delete = delete_data($tabel, $field);
        } else {
            if (is_array($id)) {
                $jml_del = 0;
                foreach ($id as $j) {
                    if ((in_array($tabel, ['user', 'user_group']) && $j == 1) ||
                        ($tabel == 'user' && $j == user('id')) ||
                        ($tabel == 'user_group' && $j == user('id_group'))
                    ) {
                        $message = lang('izin_ditolak');
                    } else {
                        $last_data = get_data($tabel, $field, $j)->row_array();
                        if (isset($last_data['not_deletable']) && $last_data['not_deletable']) {
                            $del = false;
                        } else {
                            $del = delete_data($tabel, $field, $j);
                        }
                        if ($del) {
                            foreach ($file_path as $fa => $fl) {
                                if (isset($last_data[$fa]) && $last_data[$fa] && strpos($last_data[$fa], 'default') == false) {
                                    @unlink(FCPATH . $fl . $last_data[$fa]);
                                }
                            }
                            if (is_array($child) && count($child) > 0) {
                                foreach ($child as $k => $c) {
                                    if (is_array($c)) {
                                        foreach ($c as $c1) {
                                            delete_data($c1, $k, $j);
                                        }
                                    } else {
                                        delete_data($c, $k, $j);
                                    }
                                }
                            }
                        }
                        if ($del) $jml_del++;
                    }
                }
                if ($jml_del == 0) $message = lang('tidak_ada_data_yang_dihapus');
                else {
                    $message    = $jml_del . ' ' . lang('data_berhasil_dihapus');
                    $status     = 'success';
                }
            } else {
                if ((in_array($tabel, ['user', 'user_group']) && $id == 1) ||
                    ($tabel == 'user' && $id == user('id')) ||
                    ($tabel == 'user_group' && $id == user('id_group'))
                ) {
                    $message = lang('izin_ditolak');
                } else {
                    $last_data  = get_data($tabel, $field, $id)->row_array();
                    if (isset($last_data['not_deletable']) && $last_data['not_deletable']) {
                        $delete = false;
                        $message = lang('izin_ditolak');
                    } else {
                        $delete = delete_data($tabel, $field, $id);
                    }
                    if ($delete) {
                        foreach ($file_path as $fa => $fl) {
                            if (isset($last_data[$fa]) && $last_data[$fa] && strpos($last_data[$fa], 'default') == false) {
                                @unlink(FCPATH . $fl . $last_data[$fa]);
                            }
                        }
                        if (is_array($child) && count($child) > 0) {
                            foreach ($child as $k => $c) {
                                if (is_array($c)) {
                                    foreach ($c as $c1) {
                                        delete_data($c1, $k, $id);
                                    }
                                } else {
                                    delete_data($c, $k, $id);
                                }
                            }
                        }
                    }
                }
            }
        }
        if ($delete) {
            $message    = lang('data_berhasil_dihapus');
            $status     = 'success';
        }
    } else {
        $message = lang('izin_ditolak');
    }
    $response = array(
        'status'    => $status,
        'message'   => $message,
        'menu'      => isset($access['menu']) ? $access['menu'] : '',
        'action'    => 'delete'
    );
    return $response;
}

function send_mail($data = [], $layout = true)
{
    if (setting('smtp_active')) {
        $CI                 = get_instance();
        $f_segment          = $CI->uri->segment(1);
        $class              = $CI->router->fetch_class();
        $method             = $CI->router->fetch_method();
        if (!isset($data['content'])) {
            if (isset($data['message'])) {
                $data['content'] = $data['message'];
            } else {
                $view           = $f_segment == $class ? $class . '/' . $method . '.mail.php' : $f_segment . '/' . $class . '/' . $method . '.mail.php';
                $data['content'] = $CI->load->view($view, $data, true);
            }
        }
        if ($layout) {
            $message        = $CI->load->view('layout/mail', $data, true);
        } else {
            $message        = $data['content'];
        }
        if (setting('smtp_server') && setting('smtp_port') && setting('smtp_password')) {
            $config = [
                'protocol'     => 'smtp',
                'smtp_host'    => setting('smtp_server'),
                'smtp_port'    => setting('smtp_port'),
                'smtp_user'    => setting('smtp_email'),
                'smtp_pass'    => setting('smtp_password'),
                'mailtype'     => 'html',
                'charset'      => 'iso-8859-1',
                'wordwrap'     => FALSE
            ];
        } else {
            $config        = [
                'protocol'      => 'mail',
                'mailtype'      => 'html',
                'wordwrap'      => FALSE
            ];
        }
        $email_sender       = setting('smtp_email_alias') ? setting('smtp_email_alias') : setting('smtp_email');
        if (!is_array($data['to']) && $data['to'] == $email_sender) {
            $email_sender   = setting('smtp_email');
        }
        $email_sender_name  = setting('smtp_sender_alias') ? setting('smtp_sender_alias') : setting('title');
        try {
            $CI->load->library('email', $config);
            $CI->email->set_newline("\r\n");
            $CI->email->from($email_sender, $email_sender_name);
            $CI->email->to($data['to']);
            if (isset($data['cc'])) {
                $CI->email->cc($data['cc']);
            }
            if (isset($data['bcc'])) {
                $CI->email->bcc($data['bcc']);
            }
            $CI->email->subject($data['subject']);
            $CI->email->message($message);
            if ($CI->email->send()) {
                $response = [
                    'status'    => 'success',
                    'message'   => lang('surel_berhasil_terkirim')
                ];
            } else {
                $response = [
                    'status'    => 'failed',
                    'message'   => lang('surel_gagal_terkirim')
                ];
            }
        } catch (Exception $e) {
            $response = [
                'status'    => 'failed',
                'message'   => lang('surel_gagal_terkirim')
            ];
        }
        return $response;
    } else {
        return [
            'status'    => 'failed',
            'message'   => lang('smtp_tidak_aktif')
        ];
    }
}

function custom_date($date, $full = true)
{
    if ($date) {
        if (strlen($date) == 10 || !$full) {
            return $date == '0000-00-00' ? '' : date('d/m/Y', strtotime($date));
        } else {
            return $date == '0000-00-00 00:00:00' ? '' : date('d/m/Y H:i', strtotime($date));
        }
    } else return '';
}

function return_date($date, $return_origin = true)
{
    $res    = $return_origin ? $date : '';
    if ($date) {
        if (strlen($date) >= 10 && strlen($date) <= 19) {
            $dt     = explode(' ', $date);
            $c      = explode('/', $dt[0]);
            if (count($c) == 3 && strlen($c[0]) == 2 && strlen($c[1]) == 2 && strlen($c[2]) == 4) {
                $res    = $c[2] . '-' . $c[1] . '-' . $c[0];
                if (isset($dt[1])) $res  .= ' ' . $dt[1];
            }
        } else {
            $dt     = explode(' - ', $date);
            $res    = [];
            foreach ($dt as $date) {
                $c      = explode('/', $date);
                if (count($c) == 3 && strlen($c[0]) == 2 && strlen($c[1]) == 2 && strlen($c[2]) == 4) {
                    $res[]  = $c[2] . '-' . $c[1] . '-' . $c[0];
                }
            }
        }
    }
    return $res;
}

function is_date($datetime)
{
    $res    = false;
    $dt     = explode(' ', $datetime);
    $date   = $dt[0];
    if (strlen($date) == 10) {
        $c  = explode('/', $date);
        if (count($c) == 3 && strlen($c[0]) == 2 && strlen($c[1]) == 2 && strlen($c[2]) == 4) $res = true;
    }
    return $res;
}

function is_daterange($daterange)
{
    $res    = false;
    $dt     = explode(' - ', $daterange);
    if (count($dt) == 2) {
        $res    = true;
        foreach ($dt as $date) {
            $c          = explode('/', $date);
            $is_valid   = false;
            if (count($c) == 3 && strlen($c[0]) == 2 && strlen($c[1]) == 2 && strlen($c[2]) == 4) $is_valid = true;
            if (!$is_valid) {
                $res    = false;
            }
        }
    }
    return $res;
}

function is_currency($string)
{
    $_str       = explode(',', $string, 2);
    $str        = str_replace('.', '', $_str[0]);
    $parsing    = explode('.', $_str[0]);
    $res        = false;
    if (strlen($string) > 3) {
        if (is_numeric($str)) {
            $res    = true;
            foreach ($parsing as $k => $p) {
                if (($k == 0 && strlen($p) > 3) || ($k > 0 && strlen($p) != 3)) {
                    $res    = false;
                }
            }
        }
        if ($res && isset($_str[1]) && !is_numeric($_str[1])) $res = false;
        if (!$res) {
            $test = explode(',', trim($string, ','));
            $testResult = true;
            foreach ($test as $t) {
                if (!is_numeric($t) || (int) $t != $t) $testResult = false;
            }
            $res = $testResult;
        }
    }
    return $res;
}

function return_number($currency, $return_origin = true)
{
    if (is_currency($currency)) {
        return str_replace(['.', ','], ['', '.'], $currency);
    } else return $return_origin ? $currency : '';
}

function return_currency($number = '', $decimal = 0)
{
    if (is_numeric($number)) {
        if ($decimal == 'auto') {
            $dec    = 0;
            $number += 0;
            $ex     = explode('.', $number);
            if (isset($ex[1])) {
                $dec    = strlen($ex[1]);
                if ($dec == 1 && $ex[1] == 0) $dec = 0;
            }
            $decimal    = $dec;
        }
        return number_format($number, $decimal, ',', '.');
    } else return $number;
}

function generate_data($config = [])
{
    $CI             = get_instance();
    $header         = $CI->input->request_headers();
    $key            = isset($header['X-Data-Key']) ? $header['X-Data-Key'] : '';
    $reference      = isset($header['X-Data-Ref']) ? $header['X-Data-Ref'] : '';

    if (!$key && isset($_SERVER['HTTP_X_DATA_KEY']))         $key        = $_SERVER['HTTP_X_DATA_KEY'];
    if (!$reference && isset($_SERVER['HTTP_X_DATA_REF']))   $reference  = $_SERVER['HTTP_X_DATA_REF'];

    $decode_key     = decode_string($key, TABLE_KEY);

    preg_match_all('/\[(.*|(?R))\]/si', $decode_key, $res);
    $primary_data   = isset($res[0][0]) ? trim(str_replace($res[0][0], '', $decode_key)) : $decode_key;
    $x_primary_data = explode('.', $primary_data);
    $table_orig     = isset($x_primary_data[0]) ? $x_primary_data[0] : '';
    $table          = isset($x_primary_data[0]) ? $x_primary_data[0] : '';
    $primary_field  = isset($x_primary_data[1]) ? $x_primary_data[1] : '';
    if (!table_exists(table_prefix($table_orig))) {
        $table_orig     = $decode_key;
        $table          = isset($x_primary_data[1]) ? $x_primary_data[1] : $table_orig;
        $primary_field  = '';
    }
    $primary_field  = isset($header['X-Primary-Field']) ? $header['X-Primary-Field'] : '';
    if (!$primary_field && isset($_SERVER['HTTP_PRIMARY_FIELD']))   $primary_field  = $_SERVER['HTTP_X_PRIMARY_FIELD'];

    $join_table     = isset($res[1][0]) ? __recursive_parse($res[1][0], $table) : [];
    if (!$table) {
        $join_table = [];
    }

    $limit          = get('limit') ?: 10;
    $page           = (int) get('page') ? get('page') - 1 : 0;
    $offset         = $page * $limit;

    $select         = [];
    $where          = [];
    $filter         = [];
    $join           = [];
    $order_by       = get('order_by');
    $order          = get('order');
    $buttons        = [];
    $paths          = [];

    if (isset($config['paths'])) $paths = $config['paths'];

    if (isset($config['buttons'])) {
        if (is_array($config['buttons'])) {
            if (isset($config['buttons']['class'])) {
                $buttons[]  = $config['buttons'];
            } else {
                foreach ($config['buttons'] as $cb) {
                    if (is_array($cb)) {
                        $buttons[]  = $cb;
                    }
                }
            }
        }
    }

    foreach ($join_table as $k_jt => $v_jt) {
        $_join          = $k_jt . ' ON ' . $v_jt . ' TYPE LEFT ';
        $join[$_join]   = $_join; // meminimalisir duplikat
    }


    // === KONDISI JIKA BUKAN DARI SERVERSIDE ===
    if (!$table && isset($config['table']) && $config['table']) {
        $table      = $config['table'];
        if (isset($config['primary_field'])) {
            $table  = $config['primary_field'];
        }
    }

    // definisi join dari field
    if ($table && is_array(get('select'))) {
        foreach (get('select') as $gx) {
            $x_as       = explode(' as ', $gx);
            for ($n = count($x_as) - 1; $n > 0; $n--) {
                $g          = $x_as[$n - 1] . ' as ' . $x_as[$n];
                $g          = explode('__at__', str_replace('__colon__', '::', str_replace('_single_colon_', ':', $g)), 2);
                if (isset($g[1])) {
                    $p      = explode(' as ', $g[1]);
                    if (count($p) == 2) {
                        $fields         = trim($p[1], ':');
                        $field1         = 'id';
                        $field2         = $fields;
                        $x_field        = explode(':', $fields);
                        if (count($x_field) == 2) {
                            $field1     = $x_field[0];
                            $field2     = $x_field[1];
                        }
                        if (strpos($p[1], '__at__') === false) {
                            if (strpos($p[0], '::') === false) {
                                $_join  = table_prefix($p[0]) . ' ON ' . table_prefix($p[0]) . '.' . $field1 . ' = ' . table_prefix($table) . '.' . $field2 . ' TYPE LEFT';
                            } else {
                                $_p     = explode('::', $p[0]);
                                $_join  = table_prefix($_p[0]) . ' ' . $_p[1] . ' ON ' . $_p[1] . '.' . $field1 . ' = ' . table_prefix($table) . '.' . $field2 . ' TYPE LEFT';
                            }
                        } else {
                            $e_join = explode('__at__', $p[1]);
                            $f_join = $e_join[0];
                            $t_join = $e_join[1];
                            $a_join = $e_join[1];
                            if (strpos($t_join, '::') !== false) {
                                $xt_join    = explode('::', $t_join);
                                $t_join     = table_prefix($xt_join[0]);
                                $a_join     = $xt_join[1];
                            } else {
                                $t_join     = $a_join   = table_prefix($t_join);
                            }

                            if (strpos($p[0], '::') === false) {
                                $_join  = table_prefix($p[0]) . ' ON ' . table_prefix($p[0]) . '.' . $field1 . ' = ' . $a_join . '.' . $f_join . ' TYPE LEFT';
                            } else {
                                $_p     = explode('::', $p[0]);
                                $_join  = table_prefix($_p[0]) . ' ' . $_p[1] . ' ON ' . $_p[1] . '.' . $field1 . ' = ' . $a_join . '.' . $f_join . ' TYPE LEFT';
                            }
                        }
                        $join[$_join]   = $_join;
                    }
                }
            }
        }
    }

    // ==== MEMASUKAN JOIN DARI LAIN
    if (isset($config['join'])) {
        if (is_array($config['join'])) {
            foreach ($config['join'] as $_join) {
                $join[$_join]   = $_join;
            }
        } else {
            $join[$config['join']]  = $config['join'];
        }
    }

    // === LIST TABLE YG AKAN DI QUERY BAIK ITU TABLE GET MAUPUN TABLE JOIN
    $list_path      = [];
    $table_lists    = [];
    if ($table) $table_lists[]   = table_prefix($table);
    $list_path[$table]          = __rules_path($table);
    foreach ($join as $j) {
        $_j                             = explode(' ', $j);
        if (strtoupper($_j[1]) == 'ON') {
            if (trim($_j[0])) $table_lists[] = table_prefix(trim($_j[0]));
        } else {
            $table_lists[] = table_prefix(trim($_j[0])) . '::' . trim($_j[1]);
        }
        $list_path[trim($_j[0])]        = __rules_path(trim($_j[0]));
    }

    $field_lists    = [];
    if (is_array(get('select'))) {
        foreach (get('select') as $g) {
            $x_g        = explode(' ', $g);
            $g          = str_replace(['__at__', '__colon__'], ['@', '::'], $x_g[0]);
            $field_lists[$g]    = '`' . table_prefix($table) . '`.`' . $g . '`';
            if (isset($list_path[$table][$g]) && !isset($paths[$g])) {
                $paths[$g]      = base_url($list_path[$table][$g]);
            }
            $c_g        = explode('@', $g, 2);
            if (isset($c_g[1])) {
                foreach ($table_lists as $t) {
                    if (strtolower(substr($t, -1 * strlen($c_g[1]))) == strtolower($c_g[1])) {
                        $x_t                = explode('::', $t);
                        $tjoin              = isset($x_t[1]) ? $x_t[1] : $t;
                        $torig              = isset($x_t[0]) ? $x_t[0] : $t;
                        $field_lists[$g]    = '`' . $tjoin . '`.`' . $c_g[0] . '`';
                        if (isset($list_path[$table][$g])) {
                            $paths[$g]      = base_url($list_path[$torig][$c_g[0]]);
                        }
                    }
                }
            }
        }
    }
    foreach ($buttons as $btn) {
        if (isset($btn['condition']) && trim($btn['condition'])) {
            preg_match_all('/{(.*?)}/si', $btn['condition'], $res);
            foreach ($res[1] as $rs) {
                if (substr($rs, 0, 1) != '^') {
                    $field_lists[$rs]   = '`' . table_prefix($table) . '`.`' . $rs . '`';
                    $c_g                = explode('@', $rs, 2);
                    if (isset($c_g[1])) {
                        foreach ($table_lists as $t) {
                            if (strtolower(substr($t, -1 * strlen($c_g[1]))) == strtolower($c_g[1])) {
                                $field_lists[$g]   = '`' . $t . '`.`' . $c_g[0] . '`';
                            }
                        }
                    }
                }
            }
        }
    }

    // === INISIASI RESPONSE ===
    $access     = get_access($reference);
    $response   = [
        'status'            => 'failed',
        'message'           => lang('permintaan_tidak_valid'),
        'data'              => [],
        'num_rows'          => 0,
        'total_data'        => 0,
        'total_filter'      => null,
        'primary_field'     => $primary_field,
        'edit'              => $access['edit'],
        'delete'            => $access['delete'],
        'uri'               => $access['target'],
        'default_image'     => asset_url('images/image.svg')
    ];
    if (isset($config['edit']))      $response['edit']   = $config['edit']   ? true : false;
    if (isset($config['delete']))    $response['delete'] = $config['delete'] ? true : false;

    if (table_exists(table_prefix($table_orig)) && ($access['view'] || (!$access['view'] && !$access['menu']))) {
        if (!$primary_field) {
            $fields = get_fields($table_orig);
            foreach ($fields as $k_field => $v_field) {
                if (isset($v_field->primary_key) && $v_field->primary_key) {
                    $primary_field  = $v_field->name;
                }
            }
            if (count($fields) > 0 && !$primary_field) {
                $primary_field  = $fields[0]->name;
            }
            $response['primary_field']  = $primary_field;
        }

        // === ORDER BY ===
        if (!$order && !$order_by) {
            $order_by   = '';
        }
        // memasukan config order sebagai ORDER BY secara default
        if (!$order_by && isset($config['order_by'])) {
            $order_by   = $config['order_by'];
            if (isset($config['order'])) $order  = $config['order'];
        }

        if ($order_by) {
            $order_by   = str_replace(['__at__', '__colon__'], ['@', '::'], $order_by);
            if (isset($field_lists[$order_by])) $order_by   = $field_lists[$order_by];
        }

        // === WHERE ===
        // memasukan config where
        if (isset($config['where'])) {
            if (is_array($config['where'])) {
                $where      = $config['where'];
            } else {
                $where[]    = $config['where'];
            }
        }
        $where_query    = $where;

        // memasukan filter dari client
        if (get('filter') && is_array(get('filter'))) {
            foreach (get('filter') as $keyFilter => $valFilter) {
                $keyFilter   = str_replace(['__at__', '__colon__'], ['@', '::'], $keyFilter);
                if (isset($field_lists[$keyFilter])) $keyFilter  = $field_lists[$keyFilter];

                if (is_daterange($valFilter)) {
                    $__v  = return_date($valFilter, false);
                    $where_query['DATE(' . $keyFilter . ') >='] = $__v[0];
                    $where_query['DATE(' . $keyFilter . ') <='] = $__v[1];
                } elseif (is_date($valFilter)) {
                    $where_query['DATE(' . $keyFilter . ')']    = return_date($valFilter, false);
                } elseif (is_currency($valFilter)) {
                    $filter[$keyFilter] = return_number($valFilter, false);
                } elseif (substr(strtolower($keyFilter), 0, 5) == 'opt::') {
                    $where_key  = substr($keyFilter, 5);
                    if (strpos($where_key, '.') === false) {
                        $where_key  = table_prefix($table) . '.' . $where_key;
                    }
                    $_valFilter = $valFilter;
                    if (substr($valFilter, 0, 1) == '[' && substr($valFilter, -1) == ']') {
                        $xValFilter = explode(',', str_replace(['[', ']'], '', $valFilter));
                        $_valFilter = [];
                        foreach ($xValFilter as $xVal) {
                            if (trim($xVal) != '') {
                                $_valFilter[]   = trim($xVal);
                            }
                        }
                    }
                    $where_query[$where_key] = $_valFilter;
                } else {
                    $filter[$keyFilter] = $valFilter;
                }
            }
        }

        // === HAPUS IGNORE FILTER === //
        if (isset($config['where_ignore'])) {
            if (is_array($config['where_ignore'])) {
                $where_ignore  = $config['where_ignore'];
            } else {
                foreach (explode(',', str_replace(' ', '', $config['where_ignore'])) as $si) {
                    $where_ignore[]    = $si;
                }
            }
            foreach ($where_ignore as $si) {
                if (isset($filter[$si])) unset($filter[$si]);
                if (isset($where_query[$si])) unset($where_query[$si]);
                if (isset($filter[table_prefix($table) . '.' . $si])) unset($filter[table_prefix($table) . '.' . $si]);
                if (isset($where_query[table_prefix($table) . '.' . $si])) unset($where_query[table_prefix($table) . '.' . $si]);
            }
        }

        // === HAPUS IGNORE SELECT === //
        if (isset($config['select_ignore'])) {
            if (is_array($config['select_ignore'])) {
                $select_ignore  = $config['select_ignore'];
            } else {
                foreach (explode(',', str_replace(' ', '', $config['select_ignore'])) as $si) {
                    $select_ignore[]    = $si;
                }
            }
            foreach ($select_ignore as $si) {
                if (isset($field_lists[$si])) unset($field_lists[$si]);
            }
        }

        // === DEFINISI SELECT ===
        $select[$primary_field] = table_prefix($table) . '.' . $primary_field . ' AS ' . $primary_field;
        foreach ($field_lists as $alias => $field_select) {
            $select[$alias]     = $field_select . ' AS `' . $alias . '`';
        }

        $str_select = is_array($select) ? implode(',', $select) : $select;

        // === DEFINISI CONFIG SELECT ===
        if (isset($config['select'])) {
            $add_select = is_array($config['select']) ? implode(',', $config['select']) : $config['select'];
            if (trim($add_select)) {
                $str_select .= ',' . $add_select;
            }
        }

        $data       = get_data($table_orig, [
            'select'    => $str_select,
            'where'     => $where_query,
            'join'      => $join,
            'like'      => $filter,
            'limit'     => $limit,
            'offset'    => $offset,
            'order_by'  => $order_by,
            'order'     => $order
        ])->result_array();

        if (count($buttons) > 0) {
            foreach ($data as $k_data => $v_data) {
                $data[$k_data]['buttons']   = [];
                foreach ($buttons as $btn) {
                    $condition      = str_replace('^', '', $btn['condition']);
                    unset($btn['condition']);
                    $btn['active']  = true;
                    if (trim($condition)) {
                        preg_match_all('/{(.*?)}/si', $condition, $res);
                        foreach ($res[0] as $k_res => $v_res) {
                            $condition  = str_replace($v_res, '$v_data["' . $res[1][$k_res] . '"]', $condition);
                        }
                        if (eval('return ' . trim($condition, ';') . ';')) {
                            $btn['active']  = true;
                        } else {
                            $btn['active']  = false;
                        }
                    }
                    $btn_id = $v_data[$primary_field];
                    if (is_numeric($v_data[$primary_field])) $btn_id = encode_id($v_data[$primary_field]);
                    if ($btn['link'] != '#') $btn['link']    .= $btn_id;
                    if ($btn['onclick']) $btn['onclick']     = str_replace('()', '(\'' . $btn_id . '\')', $btn['onclick']);
                    $data[$k_data]['buttons'][] = $btn;
                }
            }
        }

        $nums       = get_data($table_orig, [
            'select'    => $primary_field ? 'COUNT(`' . table_prefix($table) . '`.`' . $primary_field . '`) AS jml' : 'COUNT(*) AS jml',
            'join'      => $join,
            'where'     => $where
        ])->row();

        if (count($filter) > 0 || count($where) != count($where_query)) {
            $filter_nums    = get_data($table_orig, [
                'select'    => $primary_field ? 'COUNT(`' . table_prefix($table) . '`.`' . $primary_field . '`) AS jml' : 'COUNT(*) AS jml',
                'join'      => $join,
                'where'     => $where_query,
                'like'      => $filter
            ])->row();
            $response['total_filter']   = (int) $filter_nums->jml;
        }

        $response['status']     = 'success';
        $response['data']       = $data;
        $response['num_rows']   = count($data);
        $response['total_data'] = (int) $nums->jml;
        if (count($paths)) $response['paths']    = $paths;
    } else {
        $response['message']    = lang('izin_ditolak');
    }
    return $response;
}

function button_data($btn_type = '', $action = '', $lbl = [], $condition = '')
{
    if (strpos($btn_type, 'btn-') == false) $btn_type = 'btn-' . $btn_type;
    $class      = 'btn ' . $btn_type;
    $link       = '#';
    $onclick    = '';
    $label      = '';
    $title      = '';

    if (strpos($action, 'http://') !== false || strpos($action, 'https://') !== false) {
        $link       = $action;
    } elseif (strpos($action, '/') !== false) {
        $link       = base_url($action);
    } elseif (substr($action, -2) == '()') {
        $onclick    = $action;
    } else {
        $class      .= ' ' . $action;
    }
    if (is_array($lbl)) {
        if (isset($lbl[0])) $label   = $lbl[0];
        if (isset($lbl[1])) $title   = $lbl[1];
    } else $label   = $lbl;
    if ($label) {
        return [
            'class'     => $class,
            'link'      => $link,
            'onclick'   => $onclick,
            'label'     => $label,
            'title'     => $title,
            'condition' => $condition
        ];
    } else return false;
}

function __recursive_parse($string_join = '', $table = '', $data = [])
{
    $str_join       = explode(',', $string_join);
    foreach ($str_join as $string) {
        $temp_string    = $string;
        preg_match_all('/\[(.*|(?R))\]/si', $string, $join);
        if (isset($join[0][0])) {
            $temp_string    = trim(str_replace($join[0][0], '', $string));
        }
        preg_match_all('/\((.*|(?R))\)/si', $temp_string, $condition);
        if (isset($condition[0][0])) {
            $join_table = trim(str_replace($condition[0][0], '', $temp_string));
        }

        if (isset($condition[1][0])) {
            $cond           = str_replace(['self', '\''], [$join_table, '"'], $condition[1][0]);
            $clean_cond     = str_replace(['(', ')'], '', $cond);
            $x_cond         = explode(' ', $clean_cond);
            foreach ($x_cond as $x) {
                $new_x  = $x;
                $__x    = explode(':', $x);
                if (count($__x) == 2) {
                    $new_x      = '';
                    foreach ($__x as $key__x => $_x) {
                        $new__x = $_x;
                        if (strpos($_x, '.') === false && strpos($_x, '"') === false && !is_numeric($_x)) {
                            $new__x  = str_replace($_x, $table . '.' . $_x, $new__x);
                        }
                        if ($key__x == 0) {
                            $new_x  .= $new__x . '=';
                        } else {
                            $new_x  .= $new__x;
                        }
                    }
                }
                $cond   = str_replace($x, $new_x, $cond);
            }
            $data[$join_table]   = $cond;
        }
        if (isset($join_table) && isset($join[1][0])) {
            $data   = __recursive_parse($join[1][0], $join_table, $data);
        }
    }
    return $data;
}

function __rules_path($tbl)
{
    $file   = SCPATH . 'rules' . DIRECTORY_SEPARATOR . $tbl . '.txt';
    $file2  = SCPATH . 'rules' . DIRECTORY_SEPARATOR . table_prefix($tbl) . '.txt';
    $return = [];
    if (file_exists($file)) {
        $rules  = @unserialize(file_get_contents($file));
    } elseif (file_exists($file2)) {
        $rules  = @unserialize(file_get_contents($file2));
    }
    if (isset($rules) && is_array($rules)) {
        foreach ($rules as $field => $r) {
            if (is_array($r) && isset($r['path'])) {
                $return[$field] = upload_path($r['path']);
            }
        }
    }
    return $return;
}

function __validation($tbl)
{
    $file   = SCPATH . 'rules' . DIRECTORY_SEPARATOR . $tbl . '.txt';
    $file2  = SCPATH . 'rules' . DIRECTORY_SEPARATOR . table_prefix($tbl) . '.txt';
    $return = [];
    if (file_exists($file)) {
        $rules  = @unserialize(file_get_contents($file));
    } elseif (file_exists($file2)) {
        $rules  = @unserialize(file_get_contents($file2));
    }
    if (isset($rules) && is_array($rules)) {
        foreach ($rules as $field => $r) {
            if (is_string($r)) {
                $return[$field] = $r;
            } elseif (is_array($r) && isset($r['validation'])) {
                $return[$field] = $r['validation'];
            }
        }
    }
    return $return;
}

function read_excel($filename, $field = [])
{
    $CI = get_instance();
    $CI->load->library('simpleexcel');
    return $CI->simpleexcel->read($filename, $field);
}

function render_option($data, $value = '', $label = '')
{
    if (!$label) $label = $value;
    $html   = '<option value=""></option>';
    foreach ($data as $d) {
        if (is_object($d)) $d = (array) $d;
        if ($value) {
            $html   .= '<option value="' . $d[$value] . '">' . $d[$label] . '</option>';
        } else {
            $html   .= '<option value="' . $d . '">' . $d . '</option>';
        }
    }
    return $html;
}

function generate_type_code($str = "", $data_field = [])
{
    preg_match_all('/{(.*?)}/', $str, $res);
    $i  = $res[1];

    $m_romawi   = ['01' => 'I', '02' => 'II', '03' => 'III', '04' => 'IV', '05' => 'V', '06' => 'VI', '07' => 'VII', '08' => 'VIII', '09' => 'IX', '10' => 'X', '11' => 'XI', '12' => 'XII'];
    $m_id       = ['01' => 'JAN', '02' => 'PEB', '03' => 'MAR', '04' => 'APR', '05' => 'MEI', '06' => 'JUN', '07' => 'JUL', '08' => 'AGU', '09' => 'SEP', '10' => 'OKT', '11' => 'NOP', '12' => 'DES'];
    $m_idfull   = ['01' => 'JANUARI', '02' => 'PEBRUARI', '03' => 'MARET', '04' => 'APRIL', '05' => 'MEI', '06' => 'JUNI', '07' => 'JULI', '08' => 'AGUSTUS', '09' => 'SEPTEMBER', '10' => 'OKTOBER', '11' => 'NOPEMBER', '12' => 'DESEMBER'];
    $m_alphabet = ['01' => 'A', '02' => 'B', '03' => 'C', '04' => 'D', '05' => 'E', '06' => 'F', '07' => 'G', '08' => 'H', '09' => 'I', '10' => 'J', '11' => 'K', '12' => 'L'];
    $string     = $str;
    $result     = '';
    if (count($i) == 0) {
        $result = $str;
    } else {
        foreach ($i as $j => $k) {
            if ($k == 'Y')                           $rs[$j] = date('Y');
            else if ($k == 'y')                      $rs[$j] = date('y');
            else if ($k == 'm')                      $rs[$j] = date('m');
            else if (strtolower($k) == 'r')          $rs[$j] = $m_romawi[date('m')];
            else if (strtolower($k) == 'a')          $rs[$j] = $m_alphabet[date('m')];
            else if ($k == 'M')                      $rs[$j] = strtoupper(date('M'));
            else if (strtolower($k) == 'month')      $rs[$j] = strtoupper(date('F'));
            else if (strtolower($k) == 'bln')        $rs[$j] = $m_id[date('m')];
            else if (strtolower($k) == 'bulan')      $rs[$j] = $m_idfull[date('m')];
            else if (strtolower($k) == 'd')          $rs[$j] = date('d');
            else {
                if (isset($data_field[$k])) $rs[$j] = $data_field[$k];
                else $rs[$j] = '{' . $k . '}';
            }

            $m          = explode('{' . $k . '}', $string, 2);
            $result    .= $m[0] . $rs[$j];
            if (isset($m[1]) && $m[1] && strpos($m[1], '{') === false) {
                $result .= $m[1];
            }
            $string     = $m[1];
        }
    }
    return $result;
}

function generate_code($table = "", $column = "", $data_field = array())
{
    $data           = get_data('kode', ['tabel' => $table, 'kolom' => $column])->row();
    if (isset($data->id)) {
        $jumlah_digit   = $data->panjang;
        $prefix         = generate_type_code($data->awalan, $data_field);
        $suffix         = generate_type_code($data->akhiran, $data_field);

        if ($jumlah_digit) {
            $result   = get_code($table, $prefix, $suffix, $jumlah_digit, $column)->row();
            $code_max   = $result->k;
            $code       = (int) $code_max;
            $new_code   = $code + 1;
            if ($jumlah_digit == 1)
                return $prefix . $new_code . $suffix;
            else
                return $prefix . sprintf("%0" . $jumlah_digit . "s", $new_code) . $suffix;
        } else
            return $prefix . $suffix;
    } else
        return 'undefined';
}
function img_to_base64($path)
{
    if (file_exists($path)) {
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        return $base64;
    } else return '';
}
function timeago($date, $timeago_only = false)
{
    $timestamp = strtotime($date);

    $strTime = array(lang('detik'), lang('menit'), lang('jam'), lang('hari'), lang('bulan'), lang('tahun'));
    $length = array("60", "60", "24", "30", "12", "10");

    $currentTime = time();
    if ($currentTime >= $timestamp) {
        if (($timestamp + (60 * 60 * 24 * 6)) > $currentTime || $timeago_only) {
            $diff     = time() - $timestamp;
            for ($i = 0; $diff >= $length[$i] && $i < count($length) - 1; $i++) {
                $diff = $diff / $length[$i];
            }

            $diff = round($diff);
            return $diff . " " . strtolower($strTime[$i] . " " . lang('yang_lalu'));
        } else {
            return date_lang($date);
        }
    }
}
function file_to_svg($file, $class = '')
{
    $temp_file  = str_replace(base_url(), FCPATH, $file);
    if (file_exists($temp_file)) {
        $svg_file = file_get_contents($temp_file);
        $find_string   = '<svg';
        $position = strpos($svg_file, $find_string);
        $svg_file_new = substr($svg_file, $position);

        return '<div class="img-svg ' . $class . '">' . $svg_file_new . '</div>';
    } else {
        return '<img class="' . $class . '" src="' . $file . '" alt="" />';
    }
}
function linkify($string = '')
{
    return preg_replace(
        "~[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]~",
        "<a href=\"\\0\" target=\"_blank\">\\0</a>",
        $string
    );
}
function achColor($i = 0, $tipe = 'RGBA')
{
    $pc = floor($i);

    if ($pc > 100) $pc = 100;

    if ($pc == 50) {
        $col = 'ffff00';
    } elseif ($pc > 50) {
        $pc = $pc - 50;
        $red = str_pad(dechex(255 - (ceil((255 / 50) * $pc))), 2, '0', STR_PAD_LEFT);
        $col = $red . 'ff00';
    } else {
        $green = str_pad(dechex(ceil(((255 / 50) * $pc))), 2, '0', STR_PAD_LEFT);
        $col =  'ff' . $green . '00';
    }

    $hex  = '#' . $col;
    if ($tipe == 'HEX') {
        return $hex;
    } else {
        list($r, $g, $b) = sscanf($hex, "#%02x%02x%02x");
        return "rgba(${r},${g},${b},.85)";
    }
}
function time_ago($date)
{
    $time_difference = time() - strtotime($date);

    if ($time_difference < 1) {
        return 'baru saja';
    }
    $condition = array(
        12 * 30 * 24 * 60 * 60 =>  'tahun',
        30 * 24 * 60 * 60       =>  'bulan',
        24 * 60 * 60            =>  'hari',
        60 * 60                 =>  'jam',
        60                      =>  'menit',
        1                       =>  'detik'
    );

    foreach ($condition as $secs => $str) {
        $d = $time_difference / $secs;

        if ($d >= 1) {
            $t = round($d);
            return $t . ' ' . $str . ($t > 1 ? 's' : '') . ' yang lalu';
        }
    }
}
function push_log($response = [], $action = '')
{
    if (is_array($response)) {
        if ($response['status'] == 'success' && $response['action']) {
            if ($response['action'] == 'input') $action = 'Menambah';
            else if ($response['action'] == 'edit') $action = 'Mengubah';
            else if ($response['action'] == 'delete') $action = 'Menghapus';
            insert_data('user_log', [
                'id_user'       => user('id'),
                'tanggal'       => date_now(),
                'keterangan'    => $action . ' data ' . $response['menu']
            ]);
        }
    } else {
        insert_data('user_log', [
            'id_user'       => user('id'),
            'tanggal'       => date_now(),
            'keterangan'    => $response
        ]);
    }
}
function toUpper($dt)
{
    if (is_array($dt)) {
        $res = [];
        foreach ($dt as $k => $v) {
            $res[$k]    = strtoupper($v);
        }
        return $res;
    } else return strtoupper($dt);
}
function autocodeField($table = '')
{
    $dt = get_data('kode', ['tabel' => table_prefix($table), 'is_active' => 1])->result();
    $field = [];
    foreach ($dt as $d) $field[$d->kolom] = $d->kolom;
    return $field;
}
function autocodeValidate($field, $data)
{
    if (is_array($field)) {
        foreach ($data as $k => $v) {
            if (isset($field[$k]) && !$v) unset($data[$k]);
        }
    }
    return $data;
}
function autocodeLabel($field, $header)
{
    if (is_array($field)) {
        foreach ($header as $k => $v) {
            if (isset($field[$k])) {
                $header[$k] = ($v ?: $k) . ' (Otomatis Jika Dikosongkan)';
            } else {
                $header[$k] = $v ?: $k;
            }
        }
    }
    return $header;
}
