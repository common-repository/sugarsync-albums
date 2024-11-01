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

$action = $_GET["action"];

$json = "";

if($action == "albums"){
	
	$json = array("type"=>"albums", "account"=>$_POST["account"], "data"=>co_ss_get_albums_and_updatecache($_POST["account"]));
	
} elseif($action == "pictures"){
	
	$json = array("type"=>"pictures", "album"=>$_POST["album"], "data"=>count(co_ss_get_pictures_and_updatecache($_POST["account"], $_POST["album"])));
}

echo json_encode($json);
?>