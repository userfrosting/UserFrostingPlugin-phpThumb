<?php

namespace phpThumb;

		
if(!class_exists('\phpThumb\ThumbController')){
	
	function PasswordStrength($password) {
		$strength = 0;
		$strength += strlen(preg_replace('#[^a-z]#',       '', $password)) * 0.5; // lowercase characters are weak
		$strength += strlen(preg_replace('#[^A-Z]#',       '', $password)) * 0.8; // uppercase characters are somewhat better
		$strength += strlen(preg_replace('#[^0-9]#',       '', $password)) * 1.0; // numbers are somewhat better
		$strength += strlen(preg_replace('#[a-zA-Z0-9]#',  '', $password)) * 2.0; // other non-alphanumeric characters are best
		return $strength;
	}

class ThumbController extends \phpThumb {
	
	

    public function __construct($app,$config){
        $this->_app = $app;
        $this->_config = $config;
		$this->phpThumb = new parent;
		$this->phpThumb->config_high_security_password = $this->_config['high_security_password'];
    
	}
/*     public function set_props($props){
		$this->_config = [];
		foreach($props as $p => $v){
			$this->_config[$p] = $v;
		}
    } */
	
	public function makethumbnail($imageType,$filename,$subdirs="",$hash,$tparameters){
		$basefilename = hash("md5", $filename, false);
	
		
		if(strlen($subdirs)>0){$subdirs = "/".$subdirs;}
		
		if(!is_dir($this->_config['imageFolder'].$subdirs."/cache")){
			mkdir($this->_config['imageFolder'].$subdirs."/cache");
		}
		$basefolder = $this->_config['imageFolder'].$subdirs."/cache/$basefilename";
		ksort($tparameters);
		
		$hashparameters = "";
		
		$last = key( array_slice( $tparameters, -1, 1, TRUE ) );
		$output_filename = $basefolder."/";
		foreach($tparameters as $p => $v){
				error_log('$param:'.$p);
			$output_filename .= preg_replace('/[^A-Za-z0-9_\-]/', '_', ($p."-".$v));
			$hashparameters .= $p."=".$v;
			if($p!=$last){
				$output_filename .= "&";
				$hashparameters .= "&";
			}
		}
		$output_filename .= ".jpg";
			$phpThumb = $this->phpThumb;
			$errorpage = new \UserFrosting\BaseController($this->_app);
			if (!empty($phpThumb->config_high_security_enabled)) {
				if (strlen($hash)!==32) {
					$phpThumb->config_disable_debug = false; // otherwise error message won't print
					return $errorpage->page404();
					$phpThumb->ErrorImage('ERROR: missing hash');
				}
				if (PasswordStrength($phpThumb->config_high_security_password) < 20) {
					error_log('Password not strong enough');
					$phpThumb->config_disable_debug = false; // otherwise error message won't print
					$phpThumb->ErrorImage('ERROR: $PHPTHUMB_CONFIG[high_security_password] is not complex enough');
					return $errorpage->page404();
				}
				$tohash = $imageType.$subdirs."/".$filename.$hashparameters.$this->_config['high_security_password'];
				if ($hash != md5($tohash)) {
					error_log('incorrect hash');
					header('HTTP/1.0 403 Forbidden');
					sleep(10); // deliberate delay to discourage password-guessing
					
					return $errorpage->page404();
					$phpThumb->ErrorImage('ERROR: invalid hash');
				}
			}
		
		if(!is_dir($basefolder)){mkdir($basefolder);}
		if(file_exists($output_filename))
		{
			$this->_app->response->headers->set("Content-Type", "image/jpeg");
			echo file_get_contents($output_filename);
		}else{
			
			$phpThumb->setSourceData(file_get_contents($this->_config['imageFolder'].$subdirs."/".$filename));
			$imgInfo = getimagesize($this->_config['imageFolder'].$subdirs."/".$filename);
			
			
			foreach($tparameters as $param => $pval){
				//if(strpos($param,"fltr")!==false){
				//if(strpos($param,"fltr")===0){
				if(strpos($param,"fltr")>-1){
					$phpThumb->setParameter("fltr", $pval);
				}else{
					$phpThumb->setParameter($param, $pval);
					}
			}
			if ($phpThumb->GenerateThumbnail()) {
					$this->_app->response->headers->set("Content-Type", "image/jpeg");
					$phpThumb->RenderOutput();
					file_put_contents($output_filename, $phpThumb->outputImageData);
					echo $phpThumb->outputImageData;
			} else {
				// do something with debug/error messages
				echo 'Failed:<pre>'.$phpThumb->fatalerror."\n\n".implode("\n\n", $phpThumb->debugmessages).'</pre>';
			}
		}
		
		// add cache cleanup here by checking each file's fileatime($file)
	}


}
		}