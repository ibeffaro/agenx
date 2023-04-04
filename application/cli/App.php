<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class App extends CLI_Controller {

    var $dirRelease;
    var $appName;

    private $frames = [];
    private $length;
    private $current;
    private $allChar;
    private $specialChar;

    function __construct() {
        parent::__construct();
        $this->dirRelease   = FCPATH . 'release';
        $this->appName      = basename(FCPATH);

        if(is_dir($this->dirRelease)){
            delete_dir($this->dirRelease);
        }

        mkdir($this->dirRelease,0777);

        $this->frames   = ['⠋', '⠙', '⠹', '⠸', '⠼', '⠴', '⠦', '⠧', '⠇', '⠏'];
        $this->length   = count($this->frames);
        $this->current  = 0;

        $this->allChar      = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
        $this->specialChar  = ['∀','∂','∃','∅','∇','∈','∉','∋','∏','∑','Α','Β','Γ','Δ','Ε','Ζ','©','®','€','™','←','↑','→','↓','♠','♣','♥','♦'];
    }

    public function release($version = '') {
        if(!$version) $version = date('ymdHi');

        $this->recursive_copy(FCPATH, $this->dirRelease);
        $this->remove_development_file($this->dirRelease);

        if(is_dir($this->dirRelease . DIRECTORY_SEPARATOR . 'writable')) {
            rename($this->dirRelease . DIRECTORY_SEPARATOR . 'writable', $this->dirRelease . DIRECTORY_SEPARATOR . 'resources');
        }

        $phpFile = $this->dirRelease . DIRECTORY_SEPARATOR . 'index.php';
        if(file_exists($phpFile)) {
            $indexPHP   = file_get_contents($phpFile);
            $indexPHP   = str_replace(['writable','\'development\')'],['resources','\'production\')'],$indexPHP);

            $last_app_version = $this->get_string_between($indexPHP, 'APP_VERSION\',', ');');
            if($last_app_version) {
                $indexPHP   = str_replace($last_app_version,' \''.$version.'\'',$indexPHP);
            }

            $handle     = fopen($phpFile, "wb");
            if($handle) {
                fwrite ( $handle, $indexPHP );
            }
            fclose($handle);
        }

        // enkripsi helper
        $helper_dir = $this->dirRelease . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR;
        $dir        = scandir($helper_dir);
        foreach($dir as $d) {
            if(strpos($d,'helper') !== false) {
                $file_helper = $helper_dir . $d;
                if($d == 'custom_helper.php') $this->enc_file_base64($file_helper);
                else $this->enc_file_hashids($file_helper);
            }
        }


        $this->zip_file($this->dirRelease, $this->dirRelease);
        $dirR   = scandir($this->dirRelease);
        foreach($dirR as $d) {
            $this->tick();
            if(!in_array($d,['.','..'])) {
                $fileR  = $this->dirRelease . DIRECTORY_SEPARATOR . $d;
                if(strpos($d,'.') !== false && strpos($d,'.zip') === false) {
                    @unlink($fileR);
                } else {
                    delete_dir($fileR);
                }
            }
        }

        echo chr(27).'[0G';
        echo 'Aplikasi versi rilis berhasil di generate';
    }

    private function recursive_copy($src,$dst) {
        $dir = opendir($src);
        if(!is_dir($dst)){
            @mkdir($dst);
        }
        while(( $file = readdir($dir)) ) {
            $this->tick();
            if (!in_array($file,['.','..','.git','release'])) {
                if ( is_dir($src . '/' . $file) ) {
                    $this->recursive_copy($src .'/'. $file, $dst .'/'. $file);
                }
                else {
                    copy($src .'/'. $file,$dst .'/'. $file);
                }
            }
        }
        closedir($dir);
    }

    private function remove_development_file() {
        $delete_list    = [
            '.editorconfig',
            '.gitignore',
            'appinity',
            'composer.json',
            'composer.lock',
            'contributing.md',
            'license.txt',
            'readme.rst',
            'application/cli',
            'application/controllers/development',
            'application/views/development',
            'writable/migrations',
            'writable/seeds'
        ];

        if(is_dir($this->dirRelease . '/assets/fonts')) {
            $fonts  = scandir($this->dirRelease . '/assets/fonts');
            foreach($fonts as $kf => $f) {
                if(!in_array($f,['.','..','fa-brand','fa-regular','roboto']) && $f != setting('font-type')) {
                    $delete_list[]  = 'assets/fonts/' . $f;
                }
            }
        }
        foreach($delete_list as $d) {
            $this->tick();
            $file   = $this->dirRelease . DIRECTORY_SEPARATOR . $d;
            if((strpos($d,'.') !== false || $d == 'appinity') && file_exists($file)) {
                @unlink($file);
            } elseif(is_dir($file)) {
                delete_dir($file);
            }
        }

    }

    private function tick(string $message = 'Mohon Tunggu') {
        $next = $this->next();

        echo chr(27).'[0G';
        echo sprintf('%s %s', $this->frames[$next], $message);
    }

    private function next(): int {
        $prev          = $this->current;
        $this->current = $prev + 1;

        if ($this->current >= $this->length - 1) {
            $this->current = 0;
        }

        return $prev;
    }

    private function get_string_between($string, $start, $end){
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }

    private function recurse_zip($src, &$zip, $path) {
		$dir = opendir($src);
		while (false !== ($file = readdir($dir))) {
            $this->tick();
			if (($file != '.') && ($file != '..')) {
				if (is_dir($src . '/' . $file)) {
					$this->recurse_zip($src . '/' . $file, $zip, $path);
				} else {
					$zip->addFile($src . '/' . $file, substr($src . '/' . $file, $path));
				}
			}
		}
		closedir($dir);
	}

	private function zip_file($src, $dst = '') {
		if (substr($src, -1) === '/') {
			$src 	= substr($src, 0, -1);
		}
		if (substr($dst, -1) === '/') {
			$dst 	= substr($dst, 0, -1);
		}
		$path  		= strlen($src . '/');
		$filename 	= $this->appName ? $this->appName . '.zip' : substr($src, strrpos($src, '/') + 1) . '.zip';
		$dst  		= empty($dst) ? $filename : $dst . '/' . $filename;
		@unlink($dst);
		$zip 		= new ZipArchive;
		$res 		= $zip->open($dst, ZipArchive::CREATE);
		if ($res !== TRUE) {
			echo 'Error: Unable to create zip file';
			exit;
		}
		if (is_file($src)) {
			$zip->addFile($src, substr($src, $path));
		} else {
			if (!is_dir($src)) {
				$zip->close();
				@unlink($dst);
				echo 'Error: File not found';
				exit;
			}
			$this->recurse_zip($src, $zip, $path);
		}
		$zip->close();
		return $dst;
	}

    private function enc_file_base64($file='') {
        if(file_exists($file)) {
            $content            = file_get_contents($file);
            $twentyFirstText    = substr($content,0,20);
            $repalceFirstText   = str_replace(['<?php','<?'],'',$twentyFirstText);
            $content            = str_replace($twentyFirstText,$repalceFirstText,$content);

            $key        = $this->get_rand_variable();
            $content    = htmlspecialchars($content);
            $content    = str_replace($key['char'],$key['special'],$content);
            $content    = base64_encode(gzdeflate($content));

            $rand       = $key['str_char'].$key['str_special'].'$decode=gzinflate(base64_decode($appinity));$content=str_replace($s,$c,$decode);eval(html_entity_decode($content));';
            $key_rand   = base64_encode($rand);

            $result     = '<?php' . PHP_EOL;
            $result     .= '$appinity=\''.$content.'\';$key=\''.$key_rand.'\';eval(base64_decode($key));';
            $result     .= '$'.($this->allChar[rand(0,50)]).'=\''.base64_encode(md5(uniqid())).'\';';
            
            $handle     = fopen($file, "wb");
            if($handle) {
                fwrite ( $handle, $result );
            }
            fclose($handle);
        }
    }

    private function enc_file_hashids($file='') {
        if(file_exists($file)) {
            $content            = file_get_contents($file);
            $twentyFirstText    = substr($content,0,20);
            $repalceFirstText   = str_replace(['<?php','<?'],'',$twentyFirstText);
            $content            = str_replace($twentyFirstText,$repalceFirstText,$content);

            $key        = $this->get_rand_variable(7);
            $content    = htmlspecialchars($content);
            $content    = str_replace($key['char'],$key['special'],$content);
            $content    = base64_encode(gzdeflate($content));

            $rand       = 'eval(decode_string(\''.encode_string($key['str_char'],BUILDER_KEY).'\',BUILDER_KEY));'.$key['str_special'].'$decode=gzinflate(base64_decode($appinity));$content=str_replace($s,$c,$decode);eval(html_entity_decode($content));';
            $key_rand   = base64_encode($rand);

            $result     = '<?php' . PHP_EOL;
            $result     .= '$appinity=\''.$content.'\';$key=\''.$key_rand.'\';eval(base64_decode($key));';
            $result     .= '$'.($this->allChar[rand(0,50)]).'=\''.base64_encode(md5(uniqid())).'\';';
            
            $handle     = fopen($file, "wb");
            if($handle) {
                fwrite ( $handle, $result );
            }
            fclose($handle);
        }
    }

    private function get_random_char($num=0) {
        $char   = [];
        for($i=0;$i<$num;$i++) {
            $char[] = $this->get_unique($char);
        }
        return $char;
    }

    private function get_unique($arr=[]) {
        $min    = 0;
        $max    = strlen($this->allChar) - 1;
        $rand   = rand($min,$max);
        $res    = $this->allChar[$rand];
        if(in_array($res,$arr)) {
            $res    = $this->get_unique($arr);
        }
        return $res;
    }

    private function get_rand_variable($length_array=0) {
        $special    = $this->specialChar;
        shuffle($special);
        $char       = $this->get_random_char(count($special));

        if($length_array && $length_array <= count($special)) {
            $sp     = $special;
            $ch     = $char;

            $special    = [];
            for($i=0;$i<$length_array;$i++) $special[]  = $sp[$i];

            $char    = [];
            for($i=0;$i<$length_array;$i++) $char[]  = $ch[$i];
        }

        // Generate jadi string

        $str_special    = '';
        foreach($special as $s) {
            if($str_special) $str_special .= ',';
            $str_special    .= '\''.$s.'\'';
        }

        $str_char    = '';
        foreach($char as $s) {
            if($str_char) $str_char .= ',';
            $str_char    .= '\''.$s.'\'';
        }

        return [
            'special'       => $special,
            'char'          => $char,
            'str_special'   => '$s=['.$str_special.'];',
            'str_char'      => '$c=['.$str_char.'];'
        ];
    }

}