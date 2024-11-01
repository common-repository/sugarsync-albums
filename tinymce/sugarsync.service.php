<?php

$wpconfig = realpath("../../../../wp-config.php");

if (!file_exists($wpconfig)) {
	echo "Could not found wp-config.php. Error in path :\n\n".$wpconfig ;	
	die;	
}// stop when wp-config is not there

require_once($wpconfig);
require_once(ABSPATH . '/wp-admin/admin.php');

// check for rights
if(!current_user_can('edit_posts')) die;

global $wpdb;

//The nonce value
$nonce = wp_create_nonce('sugarsync');

?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php _e("SugarSync", "co-ss");?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<script language="javascript" type="text/javascript" src="<?php echo co_ss_plugin_url('/script/jquery-1.4.2.pack.js'); ?>"></script>
<script language="javascript" type="text/javascript" src="<?php echo co_ss_plugin_url('/script/jquery-tools-1.2.pack.js'); ?>"></script>
<script language="javascript" type="text/javascript" src="<?php echo co_ss_plugin_url('/script/jquery-ui-1.8.custom.pack.js'); ?>"></script>
<script language="javascript" type="text/javascript" src="<?php bloginfo('wpurl');?>/wp-includes/js/tinymce/tiny_mce_popup.js"></script>
<script language="javascript" type="text/javascript" src="<?php bloginfo('wpurl');?>/wp-includes/js/tinymce/utils/mctabs.js"></script>
<script language="javascript" type="text/javascript" src="<?php bloginfo('wpurl');?>/wp-includes/js/tinymce/utils/form_utils.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo co_ss_plugin_url('/tinymce/sugarsync.js'); ?>"></script>
<base target="_self" />
<script type="text/javascript">
$(document).ready(function(){
	tinyMCEPopup.executeOnLoad('initSugarSync();');
	$("#tabs").tabs({selected:1});
	
	getAccounts();
	$("#bar").hide();
	$("#albums").hide();
});
</script>

<style type="text/css">
.ui-slider{position:relative;text-align:left}
</style>
<style type="text/css">
.tabs li.ui-state-active{background:url(<?php bloginfo("wpurl");?>/wp-includes/js/tinymce/themes/advanced/skins/default/img/tabs.gif) no-repeat 0 -18px; margin-right:2px}
.tabs .ui-state-active span{background:url(<?php bloginfo("wpurl");?>/wp-includes/js/tinymce/themes/advanced/skins/default/img/tabs.gif) no-repeat right -54px}
.panel_wrapper div.panel{display:block;font-size:12px;position:relative}
.panel_wrapper div.ui-tabs-hide{display:none}
.panel_wrapper input, .panel_wrapper select, .panel_wrapper textarea{font-size:12px}
table#account_list{font-size:12px;border-style:solid;border-width:1px 0 0 1px;border-color:#ccc}
table#account_list thead tr th{font-weight:700;background:#333;color:#eee;padding:3px;border-style:solid;border-width:0 1px 1px 0;border-color:#ccc}
table#account_list tbody tr td{font-size:10pt;padding:5px;border-style:solid;border-width:0 1px 1px 0;border-color:#ccc}
.mceActionPanel{clear:both}
.co-ss-toolbox{height:22px;position:relative;margin-bottom:5px}
#account-album-pictures{}
#bar{position:absolute;top:35px;left:0;border-right:1px solid #eee;width:120px;padding-right:10px}
.bar-unit{padding:5px;margin-bottom:5px; background:#f8f8f8; border:1px solid #ddd}
.co-ss-caption{padding-bottom:5px;margin-bottom:5px;border-bottom:1px dashed #ccc;font-weight:700;color:#77AC1E}
.ui-slider-label{}
.ui-slider-wapper{width:100px;padding:5px}
.ui-slider{height:6px;background:#ccc;font-size:0px;line-height:0px}
.ui-slider-handle{position:absolute;background:#888;height:8px;width:8px;display:block;top:-1px;margin-left:-4px;font-size:0px;line-height:0px}
.ui-slider-value{width:34px;font-size:8pt}
.co-ss-shower{clear:both;height:380px;overflow:hidden;overflow-y:auto !important; overflow-y:scroll}
.pl{padding-left:135px}
.co-ss-units{float:left;background:#f8f8f8;border:1px solid #ccc;margin:5px;cursor:default;position:relative;padding:5px;}
.co-ss-units .co-ss-current-ico{position:absolute;width:20px;height:20px;top:3px;right:3px;background:transparent url(<?php echo co_ss_plugin_url('/images/current.png');?>) no-repeat scroll 50% 50%}
.co-ss-units-current{background:#DEF7BD;border-color:#9EC65E}
.co-ss-units-focus{background:#eee}
.co-ss-units-img-wapper{background:transparent url(<?php echo co_ss_plugin_url('/images/loading.gif');?>) no-repeat scroll 50% 50%;}
.co-ss-units-img-wapper img{}
.co-ss-units-bar-wapper{height:20px;padding-top:5px;font-family:Tahoma;text-align:center;font-size:12px}
.co-ss-popup-msg-window{position:absolute;padding:10px 20px;background:#333;border:1px solid #000;font-size:14px;color:#fff}
</style>
</head>

<body>
<form name="SugarSync" onSubmit="return false;" action="#">
	
	<div class="tabs" id="tabs">
		<ul>
			<li id="gallery_tab"><span><a href="#tabs-1"><?php _e("Accounts", "co-ss"); ?></a></span></li>
			<li id="simplegallery_tab"><span><a href="#tabs-2"><?php _e("Albums & Pictures", "co-ss"); ?></a></span></li>
		</ul>
	</div>
	<div class="panel_wrapper" style="height: 425px;">
		<div id="tabs-1" class="panel">
			<div id="toolbox-account" class="co-ss-toolbox">
				<label for="newaccount"><?php _e("New account:", "co-ss");?></label><input id="newaccount" name="newaccount" type="text" />
				<input type="button" value="<?php _e("Add new", "co-ss");?>" onClick="addNewAccount()" />
				<span id="account_message"></span>
			</div>
			<div class="co-ss-shower">
				<table id="account_list" width="100%" border="0" cellspacing="0" cellpadding="0">
					<thead>
						<tr>
							<th><?php _e("Account_name", "co-ss");?></th>
							<th width="80"><?php _e("Operational", "co-ss");?></th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>
		</div>
		<div id="tabs-2" class="panel">
			<div id="toolbox" class="co-ss-toolbox">
				<div id="account-album-pictures">
					<select id="accounts" name="accounts" onChange="requestApi(this.name);">
						<option value="">-</option>
					</select>
					<select id="albums" name="albums" onChange="requestApi(this.name);">
						<option value="">-</option>
					</select>
				</div>
				
			</div>
			<div id="bar">
				<div class="bar-unit">
					<a href="#" onClick="selectAllPictures();return false;"><?php _e("Select All", "co-ss");?></a>
					<a href="#" onClick="unSelectAllPictures();return false;"><?php _e("Select None", "co-ss");?></a>
					<a href="#" onClick="reverseSelectPictures();return false;"><?php _e("Negative Selection", "co-ss");?></a>
				</div>
				<div class="bar-unit">
					<div class="co-ss-caption"><?php _e("Pictures Conf:", "co-ss");?></div>
					<label class="ui-slider-label" for="co-ss-slider-width-val"><?php _e("Width limit:", "co-ss");?></label>
					<input class="ui-slider-value" type="text" id="co-ss-slider-width-val" />
					<div class="ui-slider-wapper"><div id="co-ss-slider-width"></div></div>
					<label class="ui-slider-label" for="co-ss-slider-height-val"><?php _e("Height limit:", "co-ss");?></label>
					<input class="ui-slider-value" type="text" id="co-ss-slider-height-val" />
					<div class="ui-slider-wapper"><div id="co-ss-slider-height"></div></div>
					<label class="ui-slider-label"><?php _e("Rotate:", "co-ss");?></label><span id="co-ss-slider-rotate-msg"></span>
					<input type="hidden" id="co-ss-slider-rotate-val" />
					<div class="ui-slider-wapper"><div id="co-ss-slider-rotate"></div></div>
				</div>
				<div class="bar-unit">
					<div class="co-ss-caption"><?php _e("Album Style:", "co-ss");?></div>
					<input name="style" type="radio" id="style-normal" value="normal" checked="checked" onChange="changeCode(this,'#style')" />
					<label for="style-normal"><?php _e("Normal", "co-ss");?></label>
					<input name="style" type="radio" id="style-column" value="column" onChange="changeCode(this,'#style')" />
					<label for="style-column"><?php _e("Column", "co-ss");?></label>
					<input type="hidden" value="normal" id="style" />
				</div>
			</div>
			<div id="shower" class="co-ss-shower"></div>
		</div>	
	</div>

	<div class="mceActionPanel">
		<div style="float: left">
			<input type="button" id="cancel" name="cancel" value="<?php _e("Cancel", "co-ss"); ?>" onClick="tinyMCEPopup.close();" />
		</div>

		<div style="float: right">
			<input type="submit" id="insert" name="insert" value="<?php _e("Insert", "co-ss"); ?>" onClick="insertSugarSyncPart();" />
		</div>
	</div>
</form>

<script type="text/javascript">

var pic_perf = {
	pictures : {width:104, height:104},
	albums   : {width:128, height:128}
}

var cache = {
	on       : null,
	accounts : [],
	albums   : [],
	pictures : []
};

var sugarsync_api = "<?php echo co_ss_plugin_url("/api/private.php");?>";
var sugarsync_account = "<?php echo co_ss_plugin_url("/api/account.php");?>";

var lang = {
	"Please select at least one picture" : "<?php _e("Please select at least one picture", "co-ss")?>",
	"Insert"  : "<?php _e("Insert", "co-ss");?>",
	"Enter"   : "<?php _e("Enter", "co-ss");?>",
	"No album" : "<?php _e("No album", "co-ss");?>",
	"No picture" : "<?php _e("No picture", "co-ss");?>",
	"Delete" : "<?php _e("Delete", "co-ss");?>",
	"First of all, choose to insert a picture or album!" : "<?php _e("First of all, choose to insert a picture or album!", "co-ss");?>",
	"Rotate right 90 degrees" : "<?php _e("Rotate right 90 degrees", "co-ss");?>",
	"Rotate left 90 degrees" : "<?php _e("Rotate left 90 degrees", "co-ss");?>",
	"Normal" : "<?php _e("Normal", "co-ss");?>",
	"Reverse" : "<?php _e("Reverse", "co-ss");?>"
}

$(function() {
	$("#co-ss-slider-width").slider({
		min: 0,
		max: 1600,
		value: 640,
		slide: function(event, ui) {
			$("#co-ss-slider-width-val").val(ui.value);
		}
	});
	$("#co-ss-slider-width-val").val($("#co-ss-slider-width").slider("value"));
	
	$("#co-ss-slider-height").slider({
		min: 0,
		max: 1600,
		value: 0,
		slide: function(event, ui) {
			$("#co-ss-slider-height-val").val(ui.value);
		}
	});
	$("#co-ss-slider-height-val").val($("#co-ss-slider-height").slider("value"));
	
	$("#co-ss-slider-rotate").slider({
		value:0,
		min: -1,
		max: 2,
		step: 1,
		slide: function(event, ui) {
			var value = ui.value;
			var msg = "";
			if(value == -1){
				value += 4;
				msg = lang["Rotate left 90 degrees"];
			} else if(value == 1) {
				msg = lang["Rotate right 90 degrees"];
			} else if(value == 2) {
				msg = lang["Reverse"];
			} else {
				msg = lang["Normal"];
			}
			$("#co-ss-slider-rotate-msg").html(msg);
			$("#co-ss-slider-rotate-val").val(value);
		}
	});
	$("#co-ss-slider-rotate-msg").html(lang["Normal"]);
	$("#co-ss-slider-rotate-val").val($("#co-ss-slider-rotate").slider("value"));

});
</script>
</body>
</html>
