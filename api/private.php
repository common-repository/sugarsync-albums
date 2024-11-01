<?php
$wpconfig = realpath("../../../../wp-config.php");

if (!file_exists($wpconfig)) {
	echo "Could not found wp-config.php. Error in path :\n\n".$wpconfig ;	
	die;	
}// stop when wp-config is not there

require_once($wpconfig);
require_once(ABSPATH.'/wp-admin/admin.php');

// check for rights
if(!current_user_can('edit_posts')) die;

global $wpdb;

$su = $_GET["su"];

if($su == "account"){

	
}elseif($su == "albums"){
	
	$albums = array("type" => "albums", "data" => co_ss_get_albums_and_updatecache($_POST["account"]));
	
	$api = json_encode($albums);
	
} elseif($su == "pictures"){
	
	$pictures = array("type" => "pictures", "data" => co_ss_get_pictures_and_updatecache($_POST["account"], $_POST["album"]));
	
	$api = json_encode($pictures);

}

exit($api);
?>