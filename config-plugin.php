<?php


require_once('vendor/phpThumb/phpthumb.class.php');
require_once('controllers/ThumbController.php');


//config
	$config = [
	'imageFolder' => "images/imgdb", //default or parent directory for images
	'userImage_onlyThisUser' => true, //if true, the current user can only see their own images
	'usersHaveSubdirectories' => true, //if true, a "public" folder is created for any user/guest to view
	'high_security_password' => '.^1!@%)cCMq2(A$R09bp]-=4ƒ g', //must be a very very secure password
	'default_parameters' => [	"w" => "200",
								"h" => "200",
								"zc" => 1
							] // for more info on parameters, see http://phpthumb.sourceforge.net/
	];
	

//install
	
	//this creates your imagesFolder if it doesnt exist.
	if(!is_dir($config['imageFolder'])){
		mkdir($config['imageFolder']);
	}
		
	//this creates the public folder if necessary
	if($config['usersHaveSubdirectories'] && !is_dir($config['imageFolder']."/public")){
		mkdir($config['imageFolder']."/public");
	}
	
	//this creates a folder for the active user if needed
	if($app->user->id>0){
		if(		$config['usersHaveSubdirectories']
			&&  !is_dir($config['imageFolder']."/".$app->user->id)
		  )
		  {
			  mkdir($config['imageFolder']."/".$app->user->id);
			  }
	}
// setup some UF stuff and define necessary variables
	$twig = $app->view()->getEnvironment();
	$loader = $twig->getLoader();
	$loader->addPath($app->config('plugins.path') . "/uf-phpThumb/templates");


//twig functions

	//pthumb() - generate a secure url for a thumbnail
	$function = new Twig_SimpleFunction('pthumb', function ($imageType,$folder="",$filename,$parameters) use($app,$config) {
		$parameters = explode("&",$parameters);
		//if(strlen($folder>0)){$folder = "/".$folder;}
		$tparameters = $config['default_parameters'];
		if(count($parameters)>0){
			foreach($parameters as $p)
			{
				$param = explode("=",$p);
				if(count($param)==2){
					$tparameters[($param[0])] = ($param[1]);
					$tparameters[($param[0])] = ($param[1]);
				}
			}
		}
		
		ksort($tparameters);
		
		$hashparameters = "";
		
		$last = key( array_slice( $tparameters, -1, 1, TRUE ) );
		foreach($tparameters as $p => $v){
			$hashparameters .= $p."=".$v;
			if($p!=$last){
				$hashparameters .= "&";
			}
		}
		if($imageType=="public-image" && $config['usersHaveSubdirectories'] === true)
			{$hashfolder = "public";}else{$hashfolder = $folder;}
		if($imageType=="my-image"){$hashfolder = $app->user->id;}
		if(strlen($folder)>0){$folder .="/";}
		if(strlen($hashfolder)>0){$hashfolder .="/";}
		$imgHash =  md5($imageType."/".$hashfolder.$filename.$hashparameters.$config['high_security_password']);
		$toreturn = "/".$imageType."/".$folder.$filename."/".$hashparameters."/".$imgHash;
		return $toreturn;
	});
	$twig->addFunction($function);

//routing

	//route for testpage
	$app->get('/phpthumbtest/', function() use ($app,$config) {
		if(!file_exists($config['imageFolder'].($config['usersHaveSubdirectories'] ? "/public":"")."/stuck.jpg"))
			{copy('images/stuck.jpg',$config['imageFolder'].($config['usersHaveSubdirectories'] ? "/public":"")."/stuck.jpg");}
        if($config['usersHaveSubdirectories']){
			if(!is_dir($config['imageFolder']."/".$app->user->id)){mkdir($config['imageFolder']."/".$app->user->id);}
			if(!file_exists($config['imageFolder']."/".$app->user->id."/stuck.jpg"))
				{copy('images/stuck.jpg',$config['imageFolder']."/".$app->user->id."/stuck.jpg");}
			if(!is_dir($config['imageFolder']."/0")){mkdir($config['imageFolder']."/0");}
			if(!file_exists($config['imageFolder']."/0/stuck.jpg"))
				{copy('images/stuck.jpg',$config['imageFolder']."/0/stuck.jpg");}
		}
		unset($config['high_security_password']);
        $app->render('testphpthumb.twig', ['config' => $config]);
	});

	//route for image within a specific user's image folder
	$app->get('/user-image/:uid/:filename(/:parameters)/:hash', function ($uid, $filename, $parameters="", $hash="") use ($app,$config) {
		$parameters = explode("&",$parameters);
		$tparameters = $config['default_parameters'];
		if(count($parameters)>0){
			foreach($parameters as $p)
			{
				$param = explode("=",$p);
				if(count($param)===2){
					$tparameters[($param[0])] = ($param[1]);
				}
			}
		}
		$thumb = new \phpThumb\ThumbController($app,$config);
		error_log($app->user->id);
		if(
				($config['userImage_onlyThisUser'] && $uid === $app->user->id)
			||	(!$config['userImage_onlyThisUser'])
			||	($config['usersHaveSubdirectories'] && $uid === "public")
		  ) 
		    {
				echo $thumb->makethumbnail("user-image",$filename,$uid,$hash,$tparameters);
			}else{ 
				$errorpage = new \UserFrosting\BaseController($app);
				return $errorpage->page404();
			}
	});

	
	//route for public image if 'usersHaveSubdirectories' == true
	$app->get('/public-image/:filename(/:parameters)/:hash', function ($filename, $parameters="", $hash="") use ($app,$config) {
		if(strlen($hash)==0){$hash = $parameters; $parameters = "";}
		if($config['usersHaveSubdirectories'] == true)
		{$imgdir = "public";}else{$imgdir = "";}
			$parameters = explode("&",$parameters);
			$tparameters = $config['default_parameters'];
			if(count($parameters)>0){
				foreach($parameters as $p)
				{
					$param = explode("=",$p);
					if(count($param)==2){
						$tparameters[($param[0])] = ($param[1]);
					}
				}
			}
			$thumb = new \phpThumb\ThumbController($app,$config);
			echo $thumb->makethumbnail("public-image",$filename,$imgdir,$hash,$tparameters);
	/*
		if(){ 	// anything to catch and prevent?
		}else{ 
			$errorpage = new \UserFrosting\BaseController($app);
			return $errorpage->page404();
		} */
	});
	
	
	//route for image within this user's image folder
	$app->get('/my-image/:filename(/:parameters)/:hash', function ( $filename, $parameters="", $hash="") use ($app,$config) {
		if(strlen($hash)==0){$hash = $parameters; $parameters = "";}
		$parameters = explode("&",$parameters);
		$tparameters = $config['default_parameters'];
		if(count($parameters)>0){
			foreach($parameters as $p)
			{
				$param = explode("=",$p);
				if(count($param)==2){
					$tparameters[($param[0])] = ($param[1]);
				}
			}
		}
		$thumb = new \phpThumb\ThumbController($app,$config);
		if(($config['userImage_onlyThisUser'])||(!$config['userImage_onlyThisUser']))
		{
			echo $thumb->makethumbnail("my-image",$filename,$app->user->id,$hash,$tparameters);
		}else{ 
			echo "only images from user ".$app->user->id." may be seen by you";
		}
	});