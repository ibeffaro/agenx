<?php

use PhpOffice\PhpSpreadsheet\Helper\Sample;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class Simpleexcel {

    public function read($filename, $fieldLists = []) {
        $saveTimeZone = date_default_timezone_get();
        date_default_timezone_set('UTC');
        $dataReturn     = [];
        $reader         = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($filename);
        $spreadsheet    = $reader->load($filename);
        $worksheet      = $spreadsheet->getActiveSheet();
        $countRows      = $worksheet->getHighestRow();
        $countCols      = $worksheet->getHighestColumn(); // hasilnya A,B,C,dst sesuai index

        if(!is_array($fieldLists) || (is_array($fieldLists) && count($fieldLists) == 0)) {
            $fieldLists = [];
            $idxField   = 0;
            for($i='A';$i<=$countCols;$i++) {
                $fieldLists[]   = $idxField;
                $idxField++;
            }
        }

        if(is_array($fieldLists) && count($fieldLists) > 0) {
            $defineCol      = [];
            $idxCol         = 0;
            for($i='A';$i!==($countCols++);$i++) {
                if($idxCol == count($fieldLists)) break; // diambil sesuai jumlah data yg dijabarkan di fieldList
                $defineCol[$idxCol] = $i;
                $idxCol++;
            }

            for($i=1;$i<=$countRows;$i++) {
                foreach($defineCol as $idx => $col) {
                    $value      = $worksheet->getCell($col.$i)->getValue();
                    $typeVal    = gettype($value);
                    if($typeVal != 'string' && strpos((string) $value,'E+') != false) {
                        $value      = $worksheet->getCell($col.$i)->getFormattedValue();
                    } elseif(is_numeric($value) && (int) $value > 0) { 
                        if((int) $value <= 7288012799) { // 7288012799 adalah 2200-12-12 23:59:59
                            $time               = @\PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($value);
                            $toleransi_tahun    = abs(date('Y',$time) - date('Y'));
                            if($time > 0 && $toleransi_tahun < 10) {
                                $value  = date('Y-m-d H:i:s',$time);
                            }
                        }
                    } elseif(is_object($value)) {
                        $value  = $value->getPlainText();
                    }
                    if(strlen($value) == 10) {
                        $x  = explode('/',$value);
                        if(count($x) != 3) {
                            $x  = explode('-',$value);
                        }
                        if(is_array($x) && count($x) == 3) {
                            $year       = $x[2];
                            $month      = $x[1];
                            $day        = $x[0];
                            $check_date = $year . '-' . $month . '-' . $day;
                            if(strlen($year) == 4 && strlen($month) == 2 && 
                                strlen($day) == 2 && date('Y-m-d',strtotime($check_date)) == $check_date) {
                                $value  = $check_date;
                            }
                        }
                    }
                    $dataReturn[($i-1)][$fieldLists[$idx]]  = $value;
                }
            }
        }
        date_default_timezone_set($saveTimeZone);
        return $dataReturn;
    }

    public function write($data=[],$filename='',$config=[]) {
        $saveTimeZone = date_default_timezone_get();
        date_default_timezone_set('UTC');
        if($filename) {
            $filename   = preg_replace("/[^A-Za-z0-9_\/]/", '', $filename);
        }
        if(!$filename) {
            $filename   = 'export_'.date('Ymdhis');
        }
        $background     = isset($config['background']) ? $config['background'] : 'EEEEEE';
        $color          = isset($config['color']) ? $config['color'] : '000000';
        $borderColor    = isset($config['border_color']) ? $config['border_color'] : '000000';

        $styleHeader    = [
            'font' => [
                'bold'  => true,
                'color' => ['argb' => 'FF'.$color]
            ],
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF'.$borderColor],
                ],
            ],
        ];
        $styleBody      = [
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ]
        ];
        $heading1       = [
            'font' => [
                'bold'  => true,
                'size'  => 16
            ]
        ];
        $heading2       = [
            'font' => [
                'bold'  => true,
                'size'  => 14
            ]
        ];

        $styleBold  = [
            'font' => [
                'bold'  => true,
            ],
        ];

        $styleMark  = [
            'font' => [
                'color' => ['argb' => 'FF'.$color]
            ],
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF'.$borderColor],
                ],
            ],
        ];

        $generateList   = [];
        if(is_array($data)) {
            if(isset($data['data']) && (is_array($data['data']) || is_object($data['data']))) {
                $generateList[] = $data;
            } else {
                if( isset($data[0])
                    && is_array($data[0]) 
                    && isset($data[0]['data']) 
                    && (is_array($data[0]['data']) || is_object($data[0]['data']))
                ) {
                    foreach($data as $d) {
                        $generateList[] = $d;
                    }
                } else {
                    $generateList[] = [
                        'data'      => $data
                    ];
                }
            }
        }

        $keywordValue   = ['[center]','[bold]','[mark]'];

        $spreadsheet    = new Spreadsheet();

        foreach($generateList as $keyGenerate => $attrGenerate) {
            $data       = $attrGenerate['data'];
            $headerGroup= isset($attrGenerate['header_group'])  ? $attrGenerate['header_group'] : false;
            $header     = isset($attrGenerate['header'])        ? $attrGenerate['header']       : false;
            $headerInfo = isset($attrGenerate['header_info'])   ? $attrGenerate['header_info']  : false;
            $sheetName  = isset($attrGenerate['title'])         ? $attrGenerate['title']        : false;

            if($header === false && isset($data[0])) {
                if(is_object($data[0])) {
                    $data[0]    = (array) $data[0];
                }
                foreach($data[0] as $fieldname => $fieldvalue) {
                    $header[$fieldname] = strtoupper(str_replace('_',' ',$fieldname));
                }
            }

            if(is_array($header)) {
                $new_header = [];
                foreach($header as $key_header => $val_header) {
                    if(is_numeric($key_header)) {
                        $new_header[$val_header]    = $val_header;
                    } else {
                        $new_header[$key_header]    = $val_header;
                    }
                }
                $header = $new_header;
            }

            $defineCol      = [];
            $idxCol         = 0;
            $countField     = 0;
            if(is_array($header)) {
                $countField = count($header);
            }
            for($i='A';$i<='ZZZ';$i++) {
                if($idxCol == $countField) break; // diambil sesuai jumlah data yg dijabarkan di fieldList
                $defineCol[$idxCol] = $i;
                $idxCol++;
            }

            // membuat sheet untuk list data ke 2 dst
            if($keyGenerate > 0) {
                $spreadsheet->createSheet();
            }

            $sheet          = $spreadsheet->getSheet($keyGenerate);
            if($sheetName) {
                $sheet->setTitle($sheetName);
            }
            $rowNumber      = 1;
            if(is_array($headerInfo)) {
                foreach($headerInfo as $info) {
                    if(is_string($info)) {
                        $stringInfo = str_replace(['h1:','H1:','h2:','H2:'],'',$info);
                        $cells      = 'A'.$rowNumber;
                        $sheet->setCellValue($cells,$stringInfo);
                        $sheet->mergeCells($cells.':'.end($defineCol).$rowNumber);
                        if(substr(strtolower($info),0,3) == 'h1:') {
                            $sheet->getStyle($cells)->applyFromArray($heading1);
                        } elseif(substr(strtolower($info),0,3) == 'h2:') {
                            $sheet->getStyle($cells)->applyFromArray($heading2);
                        }
                        $rowNumber++;
                    }
                }
                if($rowNumber > 1) $rowNumber++; // biar ada space antara info dan tabel
            } elseif(is_string($headerInfo) && strlen($headerInfo) > 0) {
                $stringInfo = str_replace(['h1:','H1:','h2:','H2:'],'',$headerInfo);
                $cells      = 'A'.$rowNumber;
                $sheet->setCellValue($cells,$stringInfo);
                $sheet->mergeCells($cells.':'.end($defineCol).$rowNumber);
                if(substr(strtolower($headerInfo),0,3) == 'h1:') {
                    $sheet->getStyle($cells)->applyFromArray($heading1);
                } elseif(substr(strtolower($headerInfo),0,3) == 'h2:') {
                    $sheet->getStyle($cells)->applyFromArray($heading2);
                }
                $rowNumber += 2;
            }
            if(is_array($headerGroup)) {
                $groupingFix    = [];
                foreach($headerGroup as $titleHG => $listHG) {
                    $listFix    = [];
                    foreach($listHG as $hg) {
                        $index  = 0;
                        foreach($header as $fieldname => $fieldvalue) {
                            if($fieldname == $hg) {
                                $listFix[$index]    = $hg;
                            }
                            $index++;
                        }
                    }
                    ksort($listFix);
                    $idx        = -1;
                    $dtGroup    = [];
                    foreach($listFix as $kF => $vF) {
                        if($kF - 1 != $idx) {
                            if(count($dtGroup) > 0) {
                                $first  = -1;
                                $last   = -1;
                                foreach($dtGroup as $kC => $vC) {
                                    if($first == -1) $first = $kC;
                                    $last = $kC;
                                }
                                $groupingFix[]  = [
                                    'title'     => $titleHG,
                                    'start'     => $first,
                                    'end'       => $last
                                ];
                            }
                            $dtGroup        = [];
                            $dtGroup[$kF]   = $vF;
                        } else {
                            $dtGroup[$kF]       = $vF;
                        }
                        $idx = $kF;
                    }
                    if(count($dtGroup) > 0) {
                        $first  = -1;
                        $last   = -1;
                        foreach($dtGroup as $kC => $vC) {
                            if($first == -1) $first = $kC;
                            $last = $kC;
                        }
                        $groupingFix[]  = [
                            'title'     => $titleHG,
                            'start'     => $first,
                            'end'       => $last
                        ];
                    }
                }
                if(count($groupingFix) > 0) {
                    $alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
                    foreach($groupingFix as $gF) {
                        $cells  = $defineCol[$gF['start']].$rowNumber;
                        $sheet->setCellValue($cells,$gF['title']);
                        $sheet->getStyle($cells)->applyFromArray($styleHeader);
                        $sheet->getStyle($cells)->getFill()
                            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setARGB('FF'.$background);
                        $sheet->getStyle($cells)->getAlignment()->setHorizontal($alignment_center);
                        if($gF['start'] != $gF['end']) {
                            $cells2 = $defineCol[$gF['end']].$rowNumber;
                            $sheet->mergeCells($cells.':'.$cells2);
                        }
                    }
                    $rowNumber++;
                }
            }

            if(is_array($header)) {
                $index  = 0;
                foreach($header as $fieldname => $fieldvalue) {
                    $headerLabel    = str_replace(['-d','-t','-c','-p'],'',$fieldvalue);
                    $cells          = $defineCol[$index].$rowNumber;
                    $sheet->setCellValue($cells,$headerLabel);
                    $sheet->getStyle($cells)->applyFromArray($styleHeader);
                    $sheet->getStyle($cells)->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setARGB('FF'.$background);
                    $index++;
                }
                $rowNumber++;
            }
            foreach($data as $keyData => $valData) {
                if(is_object($valData)) {
                    $valData    = (array) $valData;
                }
                $index          = 0;
                foreach($header as $fieldname => $fieldvalue) {
                    $cells          = $defineCol[$index].($keyData + $rowNumber);
                    $cellValue      = $cellValOrig  = trim(str_replace($keywordValue,'',$valData[$fieldname]));

                    if(substr($fieldvalue,0,2) == '-d') {
                        $str    = trim(str_replace(['-',':',' ','0'],'',$cellValue));
                        if(is_numeric($str)) {
                            $cellValue  = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel( strtotime($cellValue) ); 
                        } else {
                            $cellValue  = '';
                        }
                    }

                    if(substr($fieldvalue,0,2) == '-t' && is_numeric($cellValue)) {
                        $sheet->setCellValueExplicit($cells,$cellValue,\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    } else {
                        $sheet->setCellValue($cells,$cellValue);
                    }


                    // jadi format date
                    if(substr($fieldvalue,0,2) == '-d') {
                        $str    = trim(str_replace(['-',':',' ','0'],'',$cellValOrig));
                        if(is_numeric($str)) {
                            if(strlen($cellValOrig) == 10) {
                                $sheet->getStyle($cells)->getNumberFormat()->setFormatCode(
                                    \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_DMYSLASH
                                );
                            } else {
                                $sheet->getStyle($cells)->getNumberFormat()->setFormatCode(
                                    \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_DATETIME
                                );
                            }
                        }
                    }
                    // jadi format currency
                    elseif(substr($fieldvalue,0,2) == '-c') {
                        if(is_numeric($cellValue)) {
                            $sheet->getStyle($cells)->getNumberFormat()->setFormatCode(
                                \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1
                            );
                        }
                    }
                    // jadi format percentage
                    elseif(substr($fieldvalue,0,2) == '-p') {
                        if(is_numeric($cellValue)) {
                            $sheet->getStyle($cells)->getNumberFormat()->setFormatCode(
                                \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00
                            );
                        }
                    }
                    // jadi format text
                    elseif(substr($fieldvalue,0,2) == '-t') {
                        if(is_numeric($cellValue)) {
                            $sheet->getStyle($cells)->getNumberFormat()->setFormatCode(
                                \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT
                            );
                        }
                    }
                    $sheet->getStyle($cells)->applyFromArray($styleBody);

                    // jika dalam value ada keyword [center] maka hasilnya harus rata tengah
                    if(strpos($valData[$fieldname],'[center]') !== false) {
                        $sheet->getStyle($cells)->getAlignment()->setHorizontal('center');
                    }

                    // jika dalam value ada keyword [bold] maka hasilnya harus rata tengah
                    if(strpos($valData[$fieldname],'[bold]') !== false) {
                        $sheet->getStyle($cells)->applyFromArray($styleBold);
                    }

                    // jika dalam value ada keyword [mark] maka hasilnya harus rata tengah
                    if(strpos($valData[$fieldname],'[mark]') !== false) {
                        $sheet->getStyle($cells)->applyFromArray($styleMark);
                        $sheet->getStyle($cells)->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setARGB('FF'.$background);
                    }

                    $index++;
                }
            }
            foreach($defineCol as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
        }
        // di set ke sheet pertama, agar pas dibuka pertama kali sheet pertama
        $spreadsheet->setActiveSheetIndex(0);
        date_default_timezone_set('Asia/Jakarta');

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");

        date_default_timezone_set($saveTimeZone);

        $flname     = explode('/',$filename);

        if(count($flname) > 1) {
            if(!file_exists('output')) {
                $oldmask = umask(0);
                mkdir('output',0777);
                umask($oldmask);
            }
            $path       = 'output';
            foreach($flname as $pt) {
                if(trim($pt)) {
                    if(trim($pt) != trim(end($flname))) {
                        $path       .= '/' . trim($pt);
                        if(!file_exists($path)) {
                            $oldmask = umask(0);
                            mkdir($path,0777);
                            umask($oldmask);
                        }
                    } else {
                        $savedname  = $path . '/' . trim($pt) . '.xlsx';
                    }
                }
            }
            if(isset($savedname)) {
                $writer->save($savedname);
                header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
                header("Cache-Control: no-store, no-cache, must-revalidate");
                header("Cache-Control: post-check=0, pre-check=0", false);
                header("Pragma: no-cache");
                header("Content-Type: application/vnd.ms-excel");
                header('Content-Disposition: attachment;filename="'. basename($savedname) .'"');
                echo file_get_contents($savedname);
            }
        } else {
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Cache-Control: no-store, no-cache, must-revalidate");
            header("Cache-Control: post-check=0, pre-check=0", false);
            header("Pragma: no-cache");
            header("Content-Type: application/vnd.ms-excel");
            header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"');
            $writer->save("php://output");
        }
        exit;
    }

}