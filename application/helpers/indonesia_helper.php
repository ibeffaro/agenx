<?php

function date_id($date,$full=false,$min=false) {
    $list_month = [
        1   => 'Januari',
        2   => 'Februari',
        3   => 'Maret',
        4   => 'April',
        5   => 'Mei',
        6   => 'Juni',
        7   => 'Juli',
        8   => 'Agustus',
        9   => 'September',
        10  => 'Oktober',
        11  => 'November',
        12  => 'Desember',
    ];
    if(is_array($date) && count($date) == 2) {
        $x_date1    = explode(' ',$date[0]);
        $x_date2    = explode(' ',$date[1]);

        if(strtotime($x_date1[0]) > strtotime($x_date2[0])) {
            $x_date_temp    = $x_date2;
            $x_date2        = $x_date1;
            $x_date1        = $x_date_temp;
        }

        $x_dt1      = explode('-',$x_date1[0]);
        $x_dt2      = explode('-',$x_date2[0]);
        $result     = '';
        if(count($x_dt1) == 3 && count($x_dt2) == 3) {
            $day1   = $x_dt1[2];
            $day2   = $x_dt2[2];
            $month1 = (int) $x_dt1[1];
            $month2 = (int) $x_dt2[1];
            $year1  = $x_dt1[0];
            $year2  = $x_dt2[0];
            if($month1 > 0 && $month1 <= 12 && $month2 > 0 && $month2 <= 12) {
                $ln_month1  = $list_month[$month1];
                $ln_month2  = $list_month[$month2];
                if($full) {
                    $ln_month1  = substr($ln_month1,0,3);
                    $ln_month2  = substr($ln_month2,0,3);
                }

                if($day1 == $day2 && $month1 == $month2 && $year1 == $year2) {
                    $result = "{$day1} {$ln_month1} {$year1}";
                } elseif($month1 == $month2 && $year1 == $year2) {
                    $result = "{$day1} - {$day2} {$ln_month1} {$year1}";
                } elseif($year1 == $year2) {
                    $result = "{$day1} {$ln_month1} - {$day2} {$ln_month2} {$year1}";
                } else {
                    $result = "{$day1} {$ln_month1} {$year1} - {$day2} {$ln_month2} {$year2}";
                }
            }
        }
        return $result;
    } else {
        $x_date     = explode(' ',$date);
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
        $day        = date('d',$strdate);
        $month      = (int) date('m',$strdate);
        $year       = date('Y',$strdate);
        $day_name   = date('D',$strdate);

        $lang_month = $list_month[$month];
        $lang_day   = $list_day[$day_name];
        if($min) {
            $lang_month = substr($lang_month, 0, 3);
            $lang_day   = substr($lang_day, 0, 3);
        }

        $new_date   = $day . ' ' . $lang_month . ' ' . $year;
        if(isset($x_date[1])) {
            $new_date   .= ' '.$x_date[1];
        }
        if($full) {
            $new_date   = $lang_day . ', ' . $new_date;
        }
        return $new_date;
    }
}
function telp_id($no_telp='') {
    if($no_telp) {
        $no_telp    = str_replace(['(',')',' ','-'],'',$no_telp);
        if(substr($no_telp,0,3) == '+62')       $no_telp    = substr($no_telp,1);
        else if(substr($no_telp,0,1) == '0')    $no_telp    = '62' . substr($no_telp,1);
        else if(substr($no_telp,0,2) != '62')   $no_telp    = '62' . $no_telp;
    }
    return $no_telp;
}
function terbilang($angka="0") {
    $angka = (float)$angka;
    $bilangan = array('','Satu','Dua','Tiga','Empat','Lima','Enam','Tujuh','Delapan','Sembilan','Sepuluh','Sebelas');
    if ($angka < 12) {
        return $bilangan[$angka];
    } else if ($angka < 20) {
        return $bilangan[$angka - 10] . ' Belas';
    } else if ($angka < 100) {
        $hasil_bagi = (int)($angka / 10);
        $hasil_mod = $angka % 10;
        return trim(sprintf('%s Puluh %s', $bilangan[$hasil_bagi], $bilangan[$hasil_mod]));
    } else if ($angka < 200) { return sprintf('Seratus %s', terbilang($angka - 100));
    } else if ($angka < 1000) { $hasil_bagi = (int)($angka / 100); $hasil_mod = $angka % 100; return trim(sprintf('%s Ratus %s', $bilangan[$hasil_bagi], terbilang($hasil_mod)));
    } else if ($angka < 2000) { return trim(sprintf('Seribu %s', terbilang($angka - 1000)));
    } else if ($angka < 1000000) { $hasil_bagi = (int)($angka / 1000); $hasil_mod = $angka % 1000; return sprintf('%s Ribu %s', terbilang($hasil_bagi), terbilang($hasil_mod));
    } else if ($angka < 1000000000) { $hasil_bagi = (int)($angka / 1000000); $hasil_mod = $angka % 1000000; return trim(sprintf('%s Juta %s', terbilang($hasil_bagi), terbilang($hasil_mod)));
    } else if ($angka < 1000000000000) { $hasil_bagi = (int)($angka / 1000000000); $hasil_mod = fmod($angka, 1000000000); return trim(sprintf('%s Milyar %s', terbilang($hasil_bagi), terbilang($hasil_mod)));
    } else if ($angka < 1000000000000000) { $hasil_bagi = $angka / 1000000000000; $hasil_mod = fmod($angka, 1000000000000); return trim(sprintf('%s Triliun %s', terbilang($hasil_bagi), terbilang($hasil_mod)));
    } else {
        return 'Data Salah';
    }
}
function bulan($date) {
    $list_month = [
        1   => 'Januari',
        2   => 'Februari',
        3   => 'Maret',
        4   => 'April',
        5   => 'Mei',
        6   => 'Juni',
        7   => 'Juli',
        8   => 'Agustus',
        9   => 'September',
        10  => 'Oktober',
        11  => 'November',
        12  => 'Desember',
    ];
    if(strlen($date) > 2) {
        $strdate    = strtotime($date);
        $month      = (int) date('m',$strdate);
    } else $month = $date;
    $lang_month = $list_month[$month];
    return $lang_month;    
}