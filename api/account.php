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

if($action == "new"){

	$account = trim($_POST["account"]);
	
	if($account){
		
		//$wpdb->query("INSERT INTO `" . $wpdb->prefix . "sugarsync_accounts`(`account`) VALUES('".$account."')");
		
		$id = $wpdb->get_var("SELECT `id` FROM `".$wpdb->prefix . "sugarsync_accounts` WHERE `account`='".$account."'");
		
		if($id){
			
			echo json_encode(array("type"=>"message", "data"=>'The account "'.$account.'" is exists!'));
			exit();
		
		} else {
			
			$wpdb->insert($wpdb->prefix . "sugarsync_accounts", array("account" => $account), array("%s"));
			
			wp_cache_delete("co-ss:accounts", "plugin");
			
		}
		
	}
	
} elseif($action == "delete"){
	
	$id = $_POST["id"];
	
	if($id){
		
		$wpdb->query("DELETE FROM `".$wpdb->prefix . "sugarsync_accounts` WHERE 1 AND `id`='".$id."';");
		
		wp_cache_delete("co-ss:accounts", "plugin");
		
	}
	
}

$accounts = co_ss_get_accounts_cache();

$json = array("type"=>"accounts", "data"=>$accounts);

echo json_encode($json);
?>