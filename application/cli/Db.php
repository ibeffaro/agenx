<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Db extends CLI_Controller {

    var $dir;
    var $dirSeed;

    function __construct() {
        parent::__construct();
        $this->dir      = SCPATH . 'migrations';
        $this->dirSeed  = SCPATH . 'seeds';

        if(!is_dir($this->dir)){
            $oldmask = umask(0);
            mkdir($this->dir,0777);
            umask($oldmask);
        }

        if(!is_dir($this->dirSeed)){
            $oldmask = umask(0);
            mkdir($this->dirSeed,0777);
            umask($oldmask);
        }

        echo "\n"; // biar nggak pusing baca terminalnya jadi dikasih space
    }

    function create($tableName='') {
        if($this->migration_exist($tableName)) {
            echo "Migrasi ditolak karena sudah pernah di buat pada file ".$this->textError($this->migration_exist($tableName));
            $this->reset();
            die;
        }

        if($tableName && !$this->db->table_exists($tableName) && preg_match("/^[a-zA-Z0-9_\/]+$/", $tableName)) {
            $structure  = [
                'table'     => $tableName,
                'up'        => [
                    'create'    => [
                        'fields'    => [
                            'id'    => [
                                'type'              => 'bigint',
                                'unsigned'          => true,
                                'auto_increment'    => true
                            ],
                            'timestamp' => true
                        ],
                        'engine'        => 'MyISAM',
                        'primary_key'   => 'id'
                    ]
                ],
                'down'  => 'drop'
            ];
            $json       = json_encode($structure,JSON_PRETTY_PRINT);
            $filename   = date('YmdHis').'c_'.$tableName.'.json';
            $pathFile   = $this->dir.DIRECTORY_SEPARATOR.$filename;
            $handle     = fopen($pathFile, "wb");
            if($handle) {
                fwrite ( $handle, $json );
            }
            fclose($handle);
            $oldmask = umask(0);
            chmod($pathFile, 0777);
            umask($oldmask);
            if(file_exists($pathFile)) {
                echo "Struktur migrasi ".$this->textSuccess("berhasil")." dibuat";
            } else echo "Struktur migrasi ".$this->textError("gagal")." dibuat";
        } else echo $this->textError("Nama tabel tidak valid");
        $this->reset();
    }

    function modify($tableName='') {
        if($tableName && $this->db->table_exists($tableName) && preg_match("/^[a-zA-Z0-9_\/]+$/", $tableName)) {
            if(!is_dir($this->dir)){
                $oldmask = umask(0);
                mkdir($this->dir,0777);
                umask($oldmask);
            }
            $structure  = [
                'table'     => $tableName,
                'up'        => [
                    'rename_table'  => '',
                    'add_column'    => new stdClass(),
                    'modify_column' => new stdClass(),
                    'drop_column'   => []
                ],
                'down'  => [
                    'rename_table'  => 'up',
                    'drop_column'   => [],
                    'modify_column' => new stdClass(),
                    'add_column'    => new stdClass()
                ]
            ];
            $json       = json_encode($structure,JSON_PRETTY_PRINT);
            $filename   = date('YmdHis').'m_'.$tableName.'.json';
            $pathFile   = $this->dir.DIRECTORY_SEPARATOR.$filename;
            $handle     = fopen($pathFile, "wb");
            if($handle) {
                fwrite ( $handle, $json );
            }
            fclose($handle);
            $oldmask = umask(0);
            chmod($pathFile, 0777);
            umask($oldmask);
            if(file_exists($pathFile)) {
                echo "Struktur migrasi ".$this->textSuccess("berhasil")." dibuat";
            } else echo "Struktur migrasi ".$this->textError("gagal")." dibuat";
        } else echo $this->textError("Nama tabel tidak valid");
        $this->reset();
    }

    function migrate($migrationFile='') {
        $this->migrationTable();
        $migrationProc  = [];

        if($migrationFile) {
            if(file_exists($this->dir.DIRECTORY_SEPARATOR.str_replace('.json','',$migrationFile).'.json')) {
                $checkMigration = get_data('_migration','migration',str_replace('.json','',$migrationFile))->row();
                if(!isset($checkMigration->migration)) {
                    $migrationProc[]    = str_replace('.json','',$migrationFile).'.json';
                } else  {
                    echo $this->textError($migrationFile)." sudah dimigrasi"; die;
                }
            } else {
                echo "Data migrasi ".$this->textError($migrationFile)." tidak ditemukan"; die;
            }
        } else {
            $migrationLists = scandir($this->dir);
            $tblMigration   = get_data('_migration',['order_by'=>'exec_time','order'=>'DESC'])->result();
            foreach($migrationLists as $l) {
                if(str_replace(['.',' '],'',$l)) {
                    $pass   = true;
                    foreach($tblMigration as $t) {
                        if(str_replace('.json','',$l) == $t->migration) $pass = false;
                    }
                    if($pass) $migrationProc[]  = $l;
                }
            }
        }

        $this->migration('up',$migrationProc);
    }

    function rollback($migrationFile = '', $force='') {
        $this->migrationTable();
        $migrationProc  = [];

        $delFile        = false;
        if($migrationFile == '--del' || $force == '--del') {
            $delFile    = true;
        }

        if($migrationFile == '--del') $migrationFile = '';

        if($migrationFile) {
            if(file_exists($this->dir.DIRECTORY_SEPARATOR.str_replace('.json','',$migrationFile).'.json')) {
                $checkMigration = get_data('_migration','migration',str_replace('.json','',$migrationFile))->row();
                if(isset($checkMigration->migration)) {
                    $migrationProc[]    = str_replace('.json','',$migrationFile).'.json';
                } else  {
                    echo $this->textError($migrationFile)." belum dimigrasi"; die;
                }
            } else {
                echo "Data migrasi ".$this->textError($migrationFile)." tidak ditemukan"; die;
            }
        } else {
            $migrationLists = scandir($this->dir);
            $lastMigration  = get_data('_migration',['order_by'=>'exec_time','order'=>'DESC'])->row();
            if(isset($lastMigration->exec_time)) {
                $tblMigration   = get_data('_migration',[
                    'where'     => ['exec_time' => $lastMigration->exec_time],
                    'order_by'  => 'migration',
                    'order'     => 'DESC'
                ])->result();
                foreach($tblMigration as $t) {
                    $pass   = false;
                    foreach($migrationLists as $l) {
                        if(str_replace(['.',' '],'',$l) && str_replace('.json','',$l) == $t->migration) $pass = true;
                    }
                    if($pass) $migrationProc[]  = str_replace('.json','',$t->migration).'.json';
                }
            }
        }

        $this->migration('down',$migrationProc,$delFile);
    }


    private function migration($type='up',$migrationProc='',$delFile=false) {
        $this->load->dbforge();

        $deskripsi  = $type == 'up' ? 'migrasi' : 'rollback';
        $exec_time  = date('Y-m-d H:i:s');

        if(is_array($migrationProc) && count($migrationProc) > 0) {
            echo "Daftar ${deskripsi} :\n";
            foreach($migrationProc as $mp) {
                echo "- ".$this->textSuccess(str_replace('.json','',$mp))."\n";
            }
            echo "\nApakah anda yakin melanjutkan proses ${deskripsi}? (y/n) :";
            $handle = fopen ("php://stdin","r");
            $line = fgets($handle);
            if(strtolower(trim($line)) != 'y'){
                echo $this->markError("Proses ${deskripsi} dibatalkan.");
                $this->reset();
                exit;
            }
            fclose($handle);

            foreach($migrationProc as $mp) {
                $file       = $this->dir.DIRECTORY_SEPARATOR.$mp;
                $content    = file_get_contents($file);
                $content    = str_replace('"length"','"constraint"',$content);
                $data       = json_decode($content,true);
                $process    = false;
                if(is_array($data) && isset($data[$type]) && isset($data['table']) && trim($data['table'])) {
                    if(is_array($data[$type])) {
                        foreach($data[$type] as $key => $param) {
                            if($key == 'create') {

                                if(isset($param['fields']) && is_array($param['fields']) && !$this->db->table_exists($data['table'])) {
                                    $fields         = [];
                                    $primary_key    = '';
                                    foreach($param['fields'] as $k => $v) {
                                        if(isset($param['primary_key']) && $param['primary_key'] == $k) {
                                            $primary_key    = $k;
                                        }
                                        if($k == 'timestamp') {
                                            $fields['created_by']   = [
                                                'type'              => 'varchar',
                                                'constraint'        => '100'
                                            ];
                                            $fields['created_at']   = [
                                                'type'              => 'datetime'
                                            ];
                                            $fields['updated_by']   = [
                                                'type'              => 'varchar',
                                                'constraint'        => '100'
                                            ];
                                            $fields['updated_at']   = [
                                                'type'              => 'datetime'
                                            ];
                                        } else $fields[$k]   = $v;

                                        $paramTable = [];
                                        if(isset($param['engine'])) {
                                            $paramTable['ENGINE']   = $param['engine'];
                                        }                
                                    }
                                    $this->dbforge->add_field($fields);
                                    if($primary_key) {
                                        $this->dbforge->add_key($primary_key, TRUE);
                                    }
                                    $this->dbforge->create_table($data['table'],TRUE,$paramTable);
                                    $process    = true;
                                }

                            } elseif($key == 'add_column') {

                                if(count($param) && $this->db->table_exists($data['table'])) {
                                    $this->dbforge->add_column($data['table'],$param);
                                    $process    = true;
                                }

                            } elseif($key == 'modify_column') {

                                if(count($param) && $this->db->table_exists($data['table'])) {
                                    $this->dbforge->modify_column($data['table'],$param);
                                    $process    = true;
                                }

                            } elseif($key == 'drop_column') {

                                if(count($param) && $this->db->table_exists($data['table'])) {
                                    foreach($param as $p) {
                                        $this->dbforge->drop_column($data['table'],$p);
                                    }
                                    $process    = true;
                                }

                            } elseif($key == 'rename_table' && is_string($param)) {

                                if(isset($data['up']['rename_table']) && is_string($data['up']['rename_table']) && trim($data['up']['rename_table'])) {
                                    if($type == 'up') {
                                        $this->dbforge->rename_table($data['table'],$data['up']['rename_table']);
                                    } else {
                                        $this->dbforge->rename_table($data['up']['rename_table'],$data['table']);
                                    }
                                    $process = true;
                                }

                            }
                        }
                    } elseif(is_string($data[$type]) && $data[$type] == 'drop' && $this->db->table_exists($data['table'])) {
                        $this->dbforge->drop_table($data['table']);
                        $process    = true;
                    }
                }

                if($process) {
                    if($type == 'up') {
                        insert_data('_migration',[
                            'migration' => str_replace('.json','',$mp),
                            'exec_time' => $exec_time
                        ]);
                    } else {
                        delete_data('_migration','migration',str_replace('.json','',$mp));
                        if($delFile) {
                            foreach($migrationProc as $mp) {
                                $file       = $this->dir.DIRECTORY_SEPARATOR.$mp;
                                @unlink($file);
                            }
                        }
                    }
                    echo $this->markSuccess(str_replace('.json','',$mp).' berhasil di '.$deskripsi)."\n";
                } else echo $this->markError(str_replace('.json','',$mp).' gagal di '.$deskripsi)."\n";
            }

        } else echo $type == 'up' ? "Tidak ada migrasi baru" : "Belum ada data migrasi";
        
        $this->reset();
    }

    private function migration_exist($table) {
        $result = false;
        if(is_dir($this->dir) && $table) {
            $lists  = scandir($this->dir);
            foreach($lists as $l) {
                if(strpos($l, 'c_'.$table) != false) $result = $l;
            }
        }
        return $result;
    }

    private function migrationTable() {
        $this->load->dbforge();

        if(!$this->db->table_exists('_migration')) {
            $fields = [
                'migration' => [
                    'type'          => 'varchar',
                    'constraint'    => 150
                ],
                'exec_time' => [
                    'type'          => 'datetime'
                ]
            ];
            $this->dbforge->add_field($fields);
            $this->dbforge->add_key('migration', TRUE);
            $this->dbforge->create_table('_migration',TRUE,['ENGINE'=>'MyISAM']);
        }
    }

    /* SEEDS DATA */
    function pull() {
        $params = explode('/',$this->uri->uri_string());
        unset($params[0],$params[1]);
        
        if(count($params) > 0) {
            echo "Seeder yang sudah ada jika dilanjutkan akan menimpa data sebelumnya. Lanjutkan? (y/n) :";
            $handle = fopen ("php://stdin","r");
            $line = fgets($handle);
            if(strtolower(trim($line)) != 'y'){
                echo $this->markError("Proses pull dibatalkan.");
                $this->reset();
                exit;
            }
            fclose($handle);

            foreach($params as $par) {
                $table = $par;
                if($table && table_exists($table)) {
                    $query  = get_data($table)->result_array();
                    $data   = [
                        'table'     => $table,
                        'fields'    => [],
                        'data'      => []
                    ];
                    foreach($query as $q) {
                        if(count($data['fields']) == 0) {
                            foreach($q as $k => $v) {
                                $data['fields'][]   = $k;
                            }
                        }
                        $dt = [];
                        foreach($q as $k => $v) {
                            $dt[]   = $v;
                        }
                        $data['data'][] = $dt;
                    }

                    $json       = json_encode($data,JSON_PRETTY_PRINT);
                    $filename   = $table.'.json';
                    $pathFile   = $this->dirSeed.DIRECTORY_SEPARATOR.$filename;
                    $handle     = fopen($pathFile, "wb");
                    if($handle) {
                        fwrite ( $handle, $json );
                    }
                    fclose($handle);
                    $oldmask = umask(0);
                    chmod($pathFile, 0777);
                    umask($oldmask);

                    if(file_exists($pathFile)) {
                        echo "Seeder ${table} ".$this->textSuccess('berhasil')." dibuat\n";
                    } else echo "Seeder ${table} ".$this->textError('gagal')." dibuat\n";

                } else echo $this->textError($table)." tidak ditemukan\n";
            }
        } else echo $this->markError("Nama tabel yang dijadikan seeder harus di definisikan");

        $this->reset();
    }

    function push($table='') {
        if(!$table) {
            $mask = "%-20s %-30s ";
            echo sprintf($mask,$this->textBold("all"),"Untuk push semua seed yang ada") . "\n";
            echo sprintf($mask,$this->textBold("nama_tabel"),"Untuk push spesifik tabel pada seed yang sudah ada") . "\n\n";
            echo $this->textUnderline("Daftar seeds yang ada : ")."\n";
            foreach(scandir($this->dirSeed) as $d) {
                if(str_replace(['.',' '],'',$d)) {
                    echo '- ' . str_replace(".json","",$d)."\n";
                }
            }
        } else {
            $params = explode('/',$this->uri->uri_string());
            unset($params[0],$params[1]);
    
            $files  = [];
            if(count($params) == 1 && $table == "all") {
                $listFiles   = scandir($this->dirSeed);
                foreach($listFiles as $file) {
                    if(str_replace(['.',' '],'',$file)) {
                        $files[]    = $file;
                    }
                }
            } else {
                foreach($params as $par) {
                    $file   = $par.'.json';
                    if(file_exists($this->dirSeed.DIRECTORY_SEPARATOR.$file)) {
                        $files[]    = $file;
                    }
                }
            }

            if(count($files) > 0) {
                echo "Table pada database yang di push akan di hapus dan di ganti dengan data yang ada di seed. Lanjutkan? (y/n) :";
                $handle = fopen ("php://stdin","r");
                $line = fgets($handle);
                if(strtolower(trim($line)) != 'y'){
                    echo $this->markError("Proses push dibatalkan.");
                    $this->reset();
                    exit;
                }
                fclose($handle);

                foreach($files as $f) {
                    $fileLocation   = $this->dirSeed . "/" . $f;
                    $content        = file_get_contents($fileLocation);
                    $data           = json_decode($content,true);
                    $dt             = [];
                    if(isset($data['table']) && isset($data['fields']) && isset($data['data'])) {
                        if(is_array($data['fields']) && is_array($data['data'])) {
                            foreach($data['data'] as $d) {
                                $new_data   = [];
                                if(is_array($d) && count($data['fields']) == count($d)) {
                                    foreach($d as $x => $y) {
                                        $new_data[$data['fields'][$x]]  = $y;
                                    }
                                }
                                if(count($new_data) > 0) $dt[]  = $new_data;
                            }
                        }
                    }

                    if(count($dt) > 0 && table_exists($data['table'])) {
                        delete_data($data['table']);
                        insert_batch($data['table'],$dt);
                        echo $this->markSuccess(str_replace(".json","",$f).' berhasil di push');
                    } else echo $this->markError(str_replace(".json","",$f).' gagal di push');
                    echo "\n";
                }
            } else echo "Data Seed tidak ditemukan";
        }

        $this->reset();
    }

}