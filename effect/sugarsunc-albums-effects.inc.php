<?php
co_ss_effect_init();

function co_ss_effect_init(){
	global $co_ss_effects;
	$co_ss_effects = new co_ss_effect();
}
/**
 * 解析后加载的效果
 */
function co_ss_effect_str($gallery, $title){
	global $_GET, $co_ss_effects;
	
	$options = $co_ss_effects->options();
	
	$str = '';
	if(!empty($_GET["effect"]) && in_array($_GET["effect"], $options["effects"]))
		$str .= $co_ss_effects->{$_GET["effect"]."_bind"}($gallery, $title);
	else
		if($options["effect"])
			$str .= $co_ss_effects->{$options["effect"]."_bind"}($gallery, $title);
	
	return $str;
}

/**
 * 效果选择栏
 */
function co_ss_effect_choices(){
	global $_GET, $co_ss_effects;
	
	$options = $co_ss_effects->options();
	$str = '';
	if($options["customize"]){
		$str .= '<div class="co-ss-customize-bar">';
		$str .= '<table border="0" cellspacing="0" cellpadding="0"><tr>';
		$str .= '<td><span>'.__("Album effects", "co-ss").':</span></td>';
		$str .= '<td'.(!empty($_GET["effect"]) && in_array($_GET["effect"], $options["effects"]) ? '' : ' class="current"').'><a href="'.get_permalink().'">'.__("Syetem default", "co-ss").'</a></td>';
		
		if($options["effects"])
			foreach($options["effects"] as $effect){
				$str .= '<td'.($_GET["effect"] == $effect ? ' class="current"' : '').'><a href="'.get_permalink().'?effect='.$effect.'">'.$effect.'</a></td>';
			}
		$str .= '</tr></table>';
		$str .= '</div>';
	}
	return $str;
}
/**
 * 加载相册效果
 */
function co_ss_append_effects(){
	global $_GET, $co_ss_effects;
	$options = $co_ss_effects->options();
	
	if($options["lazyload"]) $co_ss_effects->lazyload();
	
	if(!empty($_GET["effect"]) && in_array($_GET["effect"], $options["effects"]))
		$co_ss_effects->{$_GET["effect"]}();
	else
		if($options["effect"])
			$co_ss_effects->{$options["effect"]}();
			
	$co_ss_effects->create(); //output
}

/**
 * 相册效果类
 */
class co_ss_effect{
	
	var $effects = '';
	var $options = array();
	
	function co_ss_effect(){
		$this->options = get_option("SugarSync");
	}
	
	function create(){
		echo $this->effects;
	}
	
	function options(){
		return $this->options;
	}
	
	function lazyload(){
		if(is_singular() && co_ss_show_opening()){
			$this->effects .= ''
				.'<script language="javascript" type="text/javascript" src="'.co_ss_plugin_url('/script/jquery-lazyload-1.5.0.pack.js').'"></script>'."\r\n"
				.'<script type="text/javascript">$(function(){$("img").lazyload({effect:"fadeIn",threshold:200});})</script>'."\r\n";
		}
	}

	function lightbox(){
		if(is_singular() && co_ss_show_opening()){
			$this->effects .= ''
				.'<link rel="stylesheet" type="text/css" href="'.co_ss_plugin_url('/effect/lightbox/css/jquery.lightbox-0.5.css').'"/>'."\r\n"
				.'<script language="javascript" type="text/javascript" src="'.co_ss_plugin_url('/effect/lightbox/jquery.lightbox-0.5.pack.js').'"></script>'."\r\n"
				.'<script type="text/javascript">'
				.'$(document).ready(function(){$("a[rel*=lightbox]").lightBox({'
				.'imageLoading:"'.co_ss_plugin_url('/effect/lightbox/images/lightbox-ico-loading.gif').'",'
				.'imageBtnPrev:"'.co_ss_plugin_url('/effect/lightbox/images/lightbox-btn-prev.gif').'",'
				.'imageBtnNext:"'.co_ss_plugin_url('/effect/lightbox/images/lightbox-btn-next.gif').'",'
				.'imageBtnClose:"'.co_ss_plugin_url('/effect/lightbox/images/lightbox-btn-close.gif').'",'
				.'imageBlank:"'.co_ss_plugin_url('/effect/lightbox/images/lightbox-blank.gif').'"'
				.'})})'
				.'</script>'."\r\n";
		}
	}
	
	function lightbox_bind(){
		return ' rel="lightbox"';
	}
	
	function clearbox(){
		if(is_singular() && co_ss_show_opening()){
			$this->effects .= ''
				.'<script language="javascript" type="text/javascript">'
				.'var '
				.'CB_ScriptDir="'.co_ss_plugin_url("/effect/clearbox").'",'
				.'CB_Language="zh_CN"'
				.';'
				.'</script>'."\r\n"
				.'<script language="javascript" type="text/javascript" src="'.co_ss_plugin_url('/effect/clearbox/clearbox-3.0.2-ultra.pack.js').'"></script>'."\r\n";
		}
	}
	
	function clearbox_bind($gallery, $title){
		return ' rel="clearbox[gallery='.$gallery.',,title='.$title.',,type=image]"';
	}
}
?>