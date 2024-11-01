<?php
/**
 * 获取相册效果
 */
function co_ss_get_effects(){
		
	$path = co_ss_plugin_path() ."effect/";
	
	if(substr($path, -1, 1) != '/')
		$path .= '/';
	
	$handle = opendir($path);
		
	$effects = array();  
	while(($file = readdir($handle)) !== false){
		if($file != "." && $file != ".."){   
			if(is_dir($path . $file)){
				$effects[] = $file;
			}
		}  
	}
	return $effects;
}

/**
 * 获取文件内容, 需要curl支持
 */
function co_ss_readfile($filename){
	//Just to declare the variables
	$data = false;
	$have_curl = false;
	$local_file = false;
	
	if(function_exists(curl_init)) { //do we have curl installed?
		$have_curl = true;
	}
	
	$search = "@([\w]*)://@i"; //is the file to read a local file?
	if (!preg_match_all($search, $filename, $matches)) {
		$local_file = true;
	}

	if($have_curl && !$local_file) { //if we have curl and it isn't a local file, use cUrl
		// Try with curl
		if($ch = curl_init($filename)) {
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			$data=curl_exec($ch);
			curl_close($ch);
		}
	} else {
		//Try with fopen
		if($fop = @fopen($filename, 'r')) {
			$data = null;
			while(!feof($fop))
				$data .= fread($fop, 1024);
			fclose($fop);
		}
	}
	return $data;
}

/**
 * xml文件识别
 */
function co_ss_check_xml(&$content){
	return preg_match("/<[rss|xml]/is", $content);
}

/**
 * 返回并创建账户缓存
 */
function co_ss_get_accounts_cache(){
	global $wpdb;
	$accounts = wp_cache_get("co-ss:accounts", "plugin");
	
	if(!$accounts){
		
		$result = $wpdb->get_results("SELECT `id`, `account` FROM `".$wpdb->prefix . "sugarsync_accounts`");
		
		foreach($result as $value){
			$accounts[] = array(
				"id"       => $value->id,
				"account"  => $value->account
			);
		}
		
		wp_cache_set("co-ss:accounts", $accounts, "plugin");
		
	}
	return $accounts;
}

/**
 * 返回并创建相册缓存
 */
function co_ss_get_albums_cache($account){
	global $wpdb;
	$cache = wp_cache_get("co-ss:".$account.":albums", "plugin");
	if($cache === false){
		/* 获取已经缓存的相册 */
		$cache = $wpdb->get_results("SELECT `album_name`, `album_slug`, `album_thumb` FROM `".$wpdb->prefix."sugarsync_albums` WHERE `account`='".$account."'");
		if(empty($cache)) $cache = array();
		wp_cache_set("co-ss:".$account.":albums", $cache, "plugin");
	}
	return $cache;
}

/**
 * 返回从远端获取的即时相册
 */
function co_ss_get_albums_remote($account){
	
	$albums = array();
	
	$data = co_ss_readfile("http://".$account.".sugarsync.com/feeds/rssalbum.xml");
	
	if(co_ss_check_xml($data)){
		
		$m = co_ss_GCBR("/<item>.*<\/item>/isU", $data);
		$m = $m[0];
		
		foreach($m as $v){
			
			$xmltemp = simplexml_load_string($v);
			
			$title = trim($xmltemp->title);
			$slug = substr( $xmltemp->link, strrpos($xmltemp->link, "/") + 1 );
			$description = $xmltemp->description;
			
			$name = co_ss_GCBR("/src=\"(.*\/)(\d+)_n_[^\.]*\.(.*)\?/isU", $description, 1);
			$name = $name[0];
			
			$path = $name[1];
			
			$albums[] = array(
				"album_name"  => $title,
				"album_slug"  => $slug,
				"album_thumb" => array(
					"path"         => $path,
					"name"         => $name[2],
					"extension"    => $name[3],
				)
			);				
		}
	}
	
	return $albums;
}

/**
 * 返回并创建图片缓存
 */
function co_ss_get_pictures_cache($account, $album){
	global $wpdb;
	$cache = wp_cache_get("co-ss:".$account.":".$album.":pictures", "plugin");
	if($cache === false){
		/* 获取已经缓存的相册 */
		$cache = $wpdb->get_results("SELECT `title`, `path`, `name`, `extension` FROM `".$wpdb->prefix."sugarsync_pictures` WHERE 1 AND `account`='".$account."' AND `album`='".$album."' ORDER BY `id` ASC");
		if(empty($cache)) $cache = array();
		wp_cache_set("co-ss:".$account.":".$album.":pictures", $cache, "plugin");
	}
	return $cache;
}

/**
 * 返回从远端获取的即时图片
 */
function co_ss_get_pictures_remote($account, $album){
	
	$pictures = array();
	
	$data = co_ss_readfile("http://".$account.".sugarsync.com/feeds/rss.xml?collection=".$album);
	
	if(co_ss_check_xml($data)){
		
		$data = preg_replace("/<(\/?\w+):([^>]*)>/is", "<$1_$2>", $data);
		
		$xml = simplexml_load_string($data);
		
		foreach($xml->channel->item as $v){
			$title = trim((string)$v->title);
			$path = $v->enclosure["url"];
			
			list($path,) = explode("?", $path);
			
			$path = str_replace("https://", "http://", $path);
			
			$name = co_ss_GCBR("/(.*\/)(\d+)_n_[^\.]*\.(.*)/is", $path, 1);
			
			$name = $name[0];
			
			$pictures[$name[2]] = array(
				"title"        => $title,
				"path"         => $name[1],
				"name"         => $name[2],
				"extension"    => $name[3],
			);
		}
	}
	return $pictures;
}

/**
 * 更新相册与账号并创建缓存，返回相册列表
 */
function co_ss_get_albums_and_updatecache($account){
	global $wpdb;
	
	if($account){

		$cache = co_ss_get_albums_cache($account);
		$albums_cache = array();
		if($cache){
			foreach($cache as $v){
				$albums_cache[$v->album_slug] = array(
					"album_name"  => $v->album_name,
					"album_slug"  => $v->album_slug,
					"album_thumb" => unserialize($v->album_thumb)
				);
			}
		}
		
		$albums = co_ss_get_albums_remote($account);
		
		if($albums_cache != $albums){
			$wpdb->query("DELETE FROM `".$wpdb->prefix."sugarsync_albums` WHERE `account`='".$account."'");
			foreach($albums as $album)
				$SQL_values[] = "('".$account."', '".$album["album_name"]."', '".$album["album_slug"]."', '".serialize($album["album_thumb"])."')";
			$wpdb->query("INSERT INTO `".$wpdb->prefix."sugarsync_albums`(`account`, `album_name`, `album_slug`, `album_thumb`) VALUES".join(',', $SQL_values));
			wp_cache_delete("co-ss:".$account.":albums", "plugin");
		}
		
		return $albums;
	}
	return array();
}

/**
 * 更新图片并创建缓存，返回图片列表
 */
function co_ss_get_pictures_and_updatecache($account, $album){
	global $wpdb;
	
	if($account && $album){
		
		$cache = co_ss_get_pictures_cache($account, $album);
		
		$pictures_cache = array();
		if($cache){
			foreach($cache as $v){
				$pictures_cache[$v->name] = array(
					"title"        => $v->title,
					"path"         => $v->path,
					"name"         => $v->name,
					"extension"    => $v->extension,
				);
			}
		}
		
		$pictures = co_ss_get_pictures_remote($account, $album);
		
		if($pictures_cache != $pictures){
			$wpdb->query("DELETE FROM `".$wpdb->prefix."sugarsync_pictures` WHERE 1 AND `account`='".$account."' AND `album`='".$album."'");
			foreach($pictures as $picture)
				$SQL_values[] = "('".$account."', '".$album."', '".$picture["title"]."', '".$picture["path"]."', '".$picture["name"]."', '".$picture["extension"]."')";
				
			$wpdb->query("INSERT INTO `".$wpdb->prefix."sugarsync_pictures`(`account`, `album`, `title`, `path`, `name`, `extension`) VALUES".join(',', $SQL_values));
			wp_cache_delete("co-ss:".$account.":".$album.":pictures", "plugin");
		}
		
		return $pictures;
	}
	return array();
}
?>