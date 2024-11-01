<?php
/*
Plugin Name: SugarSync Albums
Plugin URI: http://codante.org/wordpress-plugins-sugarsync-album-api
Description: SugarSync相册接口,使用前请先<a href="https://www.sugarsync.com/referral?rf=d9y3diy5cvha8">注册SugarSync账户</a>,本插件还提供了一个公共接口,您可以根据需要进行自主的扩展.
Version: 2.01
Author: 莳子
Author URI: http://codante.org/
*/


include(co_ss_plugin_path() . "lib/sugarsync-albums-funcs.inc.php");
is_readable(co_ss_plugin_path() . "effect/sugarsunc-albums-effects.inc.php") && include(co_ss_plugin_path() . "effect/sugarsunc-albums-effects.inc.php");

function co_ss_version(){
	//version 2.0
	return "2.01";
}

function co_ss_text_domain() {
	load_textdomain('co-ss', co_ss_plugin_path() . "lang/sugarsync-albums-" . get_locale() . ".mo");
}

function co_ss_menu(){
	add_menu_page( __("SugarSync", "co-ss"), __("SugarSync", "co-ss"), 8, "co-sugarsync-option", "co_ss_option", co_ss_plugin_url('/images/icon.png'));
	add_submenu_page( "co-sugarsync-option", __("Setting", "co-ss"), __("Setting", "co-ss"), 8, "co-sugarsync-option", 'co_ss_option' );
	add_submenu_page( "co-sugarsync-option", __("Flush", "co-ss"), __("Flush", "co-ss"), 8, "co-sugarsync-flush", 'co_ss_option' );
}

function co_ss_plugin_path(){
	return ABSPATH . 'wp-content/plugins/sugarsync-albums/';
}

function co_ss_plugin_url($str = ''){
	$aux = '/wp-content/plugins/sugarsync-albums/'.$str;
	$aux = str_replace('//', '/', $aux);
	$url = get_bloginfo('wpurl');
	return $url.$aux;
}

function co_ss_option_create_effects_form($effects, $current = false){
	$str = '';
	$str .= ''
		.'<div>'
		.'<input type="radio" name="effect" id="none" value="none"'.(!empty($effects) && $current ? '' : ' checked="checked"').' />'
		.'<label for="none">'.__("None", "co-ss").' '.__("Effects Library", "co-ss").'</label>'
		.'</div>';

	if($effects)
		foreach($effects as $effect){
			$str .= ''
				.'<div>'
				.'<input type="radio" name="effect" id="'.$effect.'" value="'.$effect.'"'.($current && $current == $effect ? ' checked="checked"' : '').' />'
				.'<label for="'.$effect.'">'.$effect.' '.__("Effects Library", "co-ss").'</label>'
				.'</div>';
		}
	return $str;
}

function co_ss_option(){
	global $_POST, $_GET;
	$page = $_GET["page"];
?>
<div class="wrap">
<h2><?php _e("SugarSync", "co-ss");?> <font size="1">v2.0</font></h2>
<?php
	if($page == "co-sugarsync-option"):
?>

<?php
	if(isset($_POST["action"])):
	
		$_DATA = array();
	
		if ($_POST['action'] == 'save'):
			if(co_ss_option_save()):
				echo '<div class="updated" style="padding:10px;">'.__('Save succeed', 'co-ss').'</div>';
			endif;
		endif;
	endif;
	
	$_DATA = array();
	$_DATA = get_option("SugarSync");
	
	$_effects = co_ss_get_effects();
?>
<style type="text/css">
.widefat{line-height:146%}.widefat tbody th{vertical-align:middle}.widefat tbody th, .widefat tbody td{padding:5px 10px}.widefat label{padding-left:6px}
</style>
<form method="post">
<div class="widget">
	<table class="widefat" width="100%" border="0" cellspacing="10" cellpadding="0">
		<tbody>
			<tr>
				<th width="200" style="text-align:right" valign="top"><?php _e("Original image links", "co-ss");?></th>
				<td align="left" valign="top">
					<input type="checkbox" name="original" id="original" value="original"<?php echo ($_DATA["original"] ? ' checked="checked"' : '')?> />
					<label for="original"><?php _e("In the pictures, add a link to the original image", "co-ss");?></label>
				</td>
			</tr>
			<tr class="alternate">
				<th width="200" style="text-align:right" valign="top"><?php _e("Images load", "co-ss");?></th>
				<td align="left" valign="top">
					<input type="checkbox" name="lazyload" id="lazyload" value="lazyload"<?php echo ($_DATA["lazyload"] ? ' checked="checked"' : '')?> />
					<label for="lazyload">lazyload <?php _e("Module", "co-ss");?> <?php _e("Gradually add pictures on the page", "co-ss");?></label>
				</td>
			</tr>
			<tr>
				<th width="200" style="text-align:right" valign="top"><?php _e("Album effects", "co-ss");?></th>
				<td align="left" valign="top">
					<?php echo co_ss_option_create_effects_form($_effects, $_DATA["effect"]);?>
				</td>
			</tr>
			<tr class="alternate">
				<th width="200" style="text-align:right" valign="top"><?php _e("Customize the effect", "co-ss");?></th>
				<td align="left" valign="top">
					<input type="checkbox" name="customize" id="customize" value="customize"<?php echo ($_DATA["customize"] ? ' checked="checked"' : '')?> />
					<label for="customize"><?php _e("Adding all the images in the top of the effect choices, you can customize the effect of the album.", "co-ss");?></label>
				</td>
			</tr>
			<tr>
				<th width="200" style="text-align:right" valign="top"></th>
				<td align="left" valign="top">
					<input type="hidden" name="effects" value="<?php echo join(',', $_effects);?>" />
					<input type="hidden" name="action" value="save" />
					<input class="button-secondary" type="submit" value="  <?php _e("Save", "co-ss");?>  " />
				</td>
			</tr>
		</tbody>
	</table>
</div>
</form>
<div class="widget">
	<div style="margin:12px; line-height: 1.5em;">
		<span><?php _e("About the plugin: ", "co-ss");?></span><a href="http://codante.org/wordpress-plugins-sugarsync-album-api" target="_blank">http://codante.org/wordpress-plugins-sugarsync-album-api</a>
	</div>
</div>
<?php

elseif($page == "co-sugarsync-flush"):

	$accounts = co_ss_get_accounts_cache();
?>
<style type="text/css">
.widefat td ul {font-size:12px}
.widefat td ul.account{padding-top:10px;margin-bottom:10px}
.widefat td li{padding-top:5px;}
.widefat td ul.account li.albums{padding:0 0 0 20px;}
.widefat td ul.account li.albums ul{list-style:inside circle none}
.widefat td ul.account li small{font-size:10px;color:#f60;font-weight:700;position:relative;padding:3px 23px 0 0}
.widefat td ul.account li small span{}
.widefat td ul.account li a{display:block;height:20px;text-indent:-300px;width:20px;position:absolute;top:0;right:0}
.widefat td .reflush_pictures,.widefat td .reflush_albums{background:#fff url(<?php echo co_ss_plugin_url('/images/update.gif');?>) no-repeat scroll 0 0}
.widefat td .co-ss-updating{background:#fff url(<?php echo co_ss_plugin_url('/images/updating.gif');?>) no-repeat scroll 0 0}
.widefat td .co-ss-updated{background:#fff url(<?php echo co_ss_plugin_url('/images/updated.gif');?>) no-repeat scroll 0 0}
</style>
<div class="widget">
<?php if(!empty($accounts)):?>
<table class="widefat" width="100%" border="0" cellspacing="10" cellpadding="0">
	<tbody>		
	<?php foreach($accounts as $account){
		$albums = co_ss_get_albums_cache($account["account"]);
	?>
	<tr><td><ul class="account" id="<?php echo $account["account"];?>">
		<li>
			<?php echo $account["account"];?>
			<small>
				<span class="album_num">(<?php echo count($albums);?>)</span>
				<a class="reflush_albums" href="javascript:void(0)" onclick="reflush_data('<?php echo $account["account"];?>')"><?php _e("reflush", "co-ss");?></a>
			</small>
		</li>
	<?php
		
		if( $albums ){?>
		<li class="albums">
			<ul>
			<?php foreach($albums as $album){?>
				<li id="<?php echo $album->album_slug;?>">
					<?php echo $album->album_name;?>
					<small>
						<span class="picture_num">(<?php echo count(co_ss_get_pictures_cache($account["account"], $album->album_slug));?>)</span>
						<a class="reflush_pictures" href="javascript:void(0)" onclick="reflush_data('<?php echo $account["account"];?>','<?php echo $album->album_slug?>')"><?php _e("reflush", "co-ss");?></a>
					</small>
				</li>
			<?php }?>
			</ul>
		</li>
		<?php }?>
	</ul></td></tr>
	<?php }?>
	</tbody>
</table>
<?php else:?>
	<?php _e("No Accounts.", "co-ss");?>
<?php endif;?>
</div>
<script type="text/javascript">
var reflush_data = function(account, album){
	var url = "<?php echo co_ss_plugin_url('/api/flush.php');?>";
	var pm = {};
	if(account && album){	
		url += "?action=pictures";
		pm.account = account;
		pm.album = album;
		var t = jQuery("#" + album).find(".reflush_pictures");
	} else if(account) {
		url += "?action=albums";
		pm.account = account;
		var t = jQuery("#" + account).find(".reflush_albums");
	}
	t.removeClass("co-ss-updated");
	t.addClass("co-ss-updating");
	jQuery.ajax({
		url: url,
		type: "POST",
		dataType: 'json',
		data: (pm),
		success: reflush_data_handle
	});
}

var reflush_data_handle = function(data){
	if(data.type == "albums"){
		var t = jQuery("#" + data.account);
		t.find(".album_num").html("("+data.data.length+")");
		t.find(".reflush_albums").removeClass("co-ss-updating");
		t.find(".reflush_albums").addClass("co-ss-updated");
	} else if(data.type == "pictures"){
		var t = jQuery("#" + data.album);
		t.find(".picture_num").html("("+data.data+")");
		t.find(".reflush_pictures").removeClass("co-ss-updating");
		t.find(".reflush_pictures").addClass("co-ss-updated");
	
	}
}
</script>
<?php
endif;
?>
</div>
<?php
}

function co_ss_option_save(){
	global $_POST;
	$_DATA["lazyload"] = !empty($_POST['lazyload']) ? true : false;
	$_DATA["customize"] = !empty($_POST['customize']) ? true : false;
	$_DATA["original"] = !empty($_POST['original']) ? true : false;
	$_DATA["effect"] = (!empty($_POST['effect']) && $_POST['effect'] != "none") ? $_POST['effect'] : false;
	$_DATA["effects"] = !empty($_POST['effects']) ? explode(',', $_POST['effects']) : array();
	update_option("SugarSync", $_DATA);
	return true;
}

function co_ss_add_buttons() {
	if ( !current_user_can('edit_posts') && !current_user_can('edit_pages') ) return;
	if ( get_user_option('rich_editing') == 'true') {
		add_filter('mce_external_plugins', 'co_ss_add_script');
		add_filter('mce_buttons', 'co_ss_add_button');
	}
}

function co_ss_add_button($buttons) {
	array_push($buttons, 'SugarSync');
	return $buttons;
}

function co_ss_add_script($plugins) {
	$pluginURL = co_ss_plugin_url('/tinymce/editor_plugin.js');
	$plugins['SugarSync'] = $pluginURL;
	return $plugins;
}

/**
 * Get content by regex
 */
function co_ss_GCBR($regex, &$content, $rank = 0){
	$ranks = array(PREG_PATTERN_ORDER, PREG_SET_ORDER);
	$regex && preg_match_all($regex, $content, $matche, $ranks[$rank]);
	return $matche;
}

/**
 * 解析文章中的指定标签
 */
function co_ss_parse_content($content){
	global $wpdb;
	
	$ss = co_ss_GCBR("/\[SugarSync::([^\]]*)\]/", $content, 1);
	
	if(!empty($ss)){
	
		$ss_tp = array();
		foreach($ss as $_s){
			$s_source = $_s[0]; //被替换的
			$s_command = explode('|', $_s[1]);
			$s_env = array();
			foreach($s_command as $_c){
				list($_k, $_v) = explode(':', $_c);
				$s_env[$_k] = $_v;
			}
			
			$s_replace = ''; #Replace string
			
			//$s_replace .= co_ss_effect_choices();
			
			if($s_env["T"] == "album"){
				
				$albums = array();
				
				$albums_cache = co_ss_get_albums_cache($s_env["C"]);
				if($albums_cache){
					$chk_albums = explode(",", $s_env["A"]);
					foreach($albums_cache as $value){
						if(in_array($value->album_slug, $chk_albums)){
							$albums[] = array(
								"name" => $value->album_name,
								"slug" => $value->album_slug
							);
						}
					}
				}
				if($albums){
					foreach($albums as $album){
						$s_env["A"] = $album["slug"];
						$s_env["An"] = $album["name"];
						
						$pictures = co_ss_get_pictures_remote($s_env["C"], $s_env["A"]);
						if($pictures){		
							$s_replace .= co_ss_create_pictures_str($s_env, $pictures);
						}	
						
					}
				}
			
			} elseif($s_env["T"] == "picture"){
				
				$album_name = __("Gallery", "co-ss");
				$albums_cache = co_ss_get_albums_cache($s_env["C"]);
				if($albums_cache){
					foreach($albums_cache as $value){
						if($value->album_slug == $s_env["A"]){
							$s_env["An"] = $value->album_name;
							break;
						}
					}
				}
				
				$pictures_cache = co_ss_get_pictures_cache($s_env["C"], $s_env["A"]);
				$pictures = array();
				if($pictures_cache){
					foreach($pictures_cache as $v){
						$pictures[$v->name] = array(
							"title"        => $v->title,
							"path"         => $v->path,
							"name"         => $v->name,
							"extension"    => $v->extension,
						);
					}
				}
				
				if(!empty($s_env["P"])){
					$chk_pics = explode(",", $s_env["P"]);
					foreach($pictures as $key => &$value){
						if(!in_array($key, $chk_pics)){
							$value = NULL;
							unset($pictures[$key]);
						}
					}
				}
				
				if($pictures){
					$s_replace .= co_ss_create_pictures_str($s_env, $pictures);
				}
				
			} else {
				
			}
			
			$content = str_replace($s_source, $s_replace, $content);
		}
	}
	//$ss = $ss_tp;
	
	return $content;
}

/**
 * 根据标签解析出相册的html代码
 */
function co_ss_create_pictures_str($s_env, $pictures){

	if($s_env["L"] == "column")
		$style = ' co-ss-column';
	else
		$style = '';
		
	$s_replace = '';
	
	list($w, $h, $r) = explode(",", $s_env["S"]);
	$s_replace .= '<div id="SugarSync-'.$s_env["A"].'" class="SugarSync">'."\r\n";
	function_exists("co_ss_effect_choices") && $s_replace .= co_ss_effect_choices();
	$s_replace .= '<div class="co-ss-pictures">';
	foreach($pictures as $k => $v){
		$src = array(
			"insert"  => $v["path"].$v["name"].'_n_'.$r.'_'.$w.'x'.$h.'.'.$v["extension"],
			"source"  => $v["path"].$v["name"].'_n_'.$r.'_-1x-1'.'.'.$v["extension"],
			"effect"  => $v["path"].$v["name"].'_n_'.$r.'_800x600'.'.'.$v["extension"],
		);
		
		$s_replace .= ''
			.'<div class="co-ss-picture-frame'.$style.'">'
			.'<table border="0" cellspacing="0" cellpadding="0">'
			.'<tbody>'
			.'<tr><td class="co-ss-picture" align="center" valign="middle"';
		if($s_env["L"] == "column"){
			$s_replace .= ' height="'.($h + 10).'" width="'.($w + 10).'" style="height:'.($h + 10).'px;width:'.($w + 10).'px"';
		}
		$s_replace .= '>';
		
		$s_replace .= '<a target="_blank" href="'.$src["effect"].'"';
		
		function_exists("co_ss_effect_str") && $s_replace .= co_ss_effect_str($s_env["An"], $v["title"]);
		
		$s_replace .= '>'
			.'<img src="'.$src["insert"].'" title="'.$v["title"].'" alt="'.$v["title"].'" />'
			.'</a>';
		
		if($_DATA["original"])
			$s_replace .= '<a target="_blank" href="'.$src["source"].'">'.__("View original size", "co-ss").'</a>';
		
		$s_replace .= ''
			.'</td></tr>'
			.'</tbody>'
			.'</table>'
			.'</div>'."\r\n";
	}
	$s_replace .= '</div>';
	$s_replace .= '</div>';
	
	
	return $s_replace;
}


/**
 * 相册样式
 */
function co_ss_theme(){
	if(co_ss_show_opening()){
		echo "<!-- START of effects generated by SugarSync Albums -->\r\n";
		echo '<link rel="stylesheet" type="text/css" href="'.co_ss_plugin_url('/style/sugarsync-albums.css').'"/>'."\r\n";
		function_exists("co_ss_append_effects") && co_ss_append_effects();
		echo "<!-- END of effects generated by SugarSync Albums -->\r\n";
	}
}

/**
 * 解析文章中的标签
 */
function co_ss_show_opening(){
	global $post, $co_ss_show_opening;
	if(!isset($co_ss_show_opening))
		$co_ss_show_opening = !!preg_match("/\[SugarSync::/i", $post->post_content);
	return $co_ss_show_opening;
}

/**
 * 对摘要中的标签不进行转义
 */
function co_ss_excerpt_clean($excerpt){
	return preg_replace("/\[sugarsync::[^\]]*\]/isU", '', $excerpt);
}

/**
 * 激活时执行
 */
function co_ss_activation(){
	co_ss_createTables();
	co_ss_feedback();
}

/**
 * 删除表
 */
function co_ss_dropTables(){
	global $wpdb;
	@mysql_query("DROP TABLE " . $wpdb->prefix . "sugarsync_accounts, " . $wpdb->prefix . "sugarsync_albums, " . $wpdb->prefix . "sugarsync_pictures;");
}
/**
 * 创建必要数据表
 */
function co_ss_createTables(){
	global $wpdb;
	$table_name = $wpdb->prefix . "sugarsync_accounts";
	$rs = @mysql_query("SHOW TABLES LIKE '$table_name'");
	$exists = @mysql_fetch_row($rs);
	if(!$exists){
		$SQL = "CREATE TABLE IF NOT EXISTS " . $table_name . " (
				`id` int(10) NOT NULL auto_increment,
				`account` varchar(64) NOT NULL,
				PRIMARY KEY  (`id`),
				KEY `account` (`account`)
				)";
		@mysql_query($SQL);
	}
	$table_name = $wpdb->prefix . "sugarsync_albums";
	$rs = @mysql_query("SHOW TABLES LIKE '$table_name'");
	$exists = @mysql_fetch_row($rs);
	if(!$exists){
		$SQL = "CREATE TABLE IF NOT EXISTS " . $table_name . " (
				`id` int(10) NOT NULL auto_increment,
				`account` varchar(64) NOT NULL,
				`album_name` varchar(64) NOT NULL,
				`album_slug` varchar(64) NOT NULL,
				`album_thumb` text NOT NULL,
				PRIMARY KEY  (`id`),
				KEY `account` (`account`)
				)";
		@mysql_query($SQL);
	}	
	$table_name = $wpdb->prefix . "sugarsync_pictures";
	$rs = @mysql_query("SHOW TABLES LIKE '$table_name'");
	$exists = @mysql_fetch_row($rs);
	if(!$exists){
		$SQL = "CREATE TABLE IF NOT EXISTS " . $table_name . " (
				`id` int(10) NOT NULL auto_increment,
				`account` varchar(64) NOT NULL,
				`album` varchar(64) NOT NULL,
				`title` varchar(64) NOT NULL,
				`path` char(255) NOT NULL,
				`name` bigint(11) NOT NULL,
				`extension` char(4) NOT NULL,
				PRIMARY KEY  (`id`),
				KEY `aa` (`account`,`album`)
				)";
		@mysql_query($SQL);
	}
}

/**
 * Get some feedback for me. 获取一些您的基本信息
 * Help us better serve you. 以便于我们更好的问您服务
 */
function co_ss_feedback(){
	if(function_exists( wp_remote_post )){
		wp_remote_post("http://codante.org/stat.php", array('body'=>array("slug"=>"sugarsync-albums","site"=>get_bloginfo("url"), "version"=>get_bloginfo("version"),"email"=>get_bloginfo("admin_email"), "check"=>"ea3bnWExGn8nMPfgPIbE/aZUKk0zXOO3I+VPhS7yoC7nRktWV3DtWCTAUvLj", "remarks"=>"plugin_version:" . co_ss_version())));
	}
}

register_activation_hook( __FILE__, 'co_ss_activation' );   #You can mask this line of code.

add_action('init', 'co_ss_text_domain');
add_action('init', 'co_ss_add_buttons');
add_action('admin_menu','co_ss_menu');
add_action('wp_head', 'co_ss_theme');
add_filter('the_excerpt', 'co_ss_excerpt_clean', 30);
add_filter('the_excerpt_rss', 'co_ss_excerpt_clean', 30);
add_filter('the_content', 'co_ss_parse_content', 30);

?>