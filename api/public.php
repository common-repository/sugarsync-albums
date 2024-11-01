<?php
$wpconfig = realpath("../../../../wp-config.php");

if (!file_exists($wpconfig)) {
	echo "Could not found wp-config.php. Error in path :\n\n".$wpconfig ;	
	die;	
}// stop when wp-config is not there

require_once($wpconfig);

$su = $_GET["su"];

if($su == "albums"){
	
	$account = $_POST["account"];
	

	
	$albums = array();
	if($account){	
		$albums = co_ss_get_albums_remote($account);
	}

	$albums = array("type" => "albums", "data" => $albums);
	
	$api = json_encode($albums);
	
} elseif($su == "pictures"){

	$account = $_POST["account"];
	$album = $_POST["album"];
	
	$pictures = array();
	
	if($account && $album){
		$pictures = co_ss_get_pictures_remote($account, $album);
	}
	
	$pictures = array("type" => "pictures", "data" => $pictures);
	
	$api = json_encode($pictures);

}

exit($api);
?>