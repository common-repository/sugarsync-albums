function initSugarSync(){
	tinyMCEPopup.resizeToInnerSize();
}

function popMsg(m){
	var p = $('<div class="co-ss-popup-msg-window"></div>');
	p.html(m);
	$("body").append(p);
	p.css({
		"top":($(window).height() - p.outerHeight()) * 0.5,
		"left":($(document).width() - p.outerWidth()) * 0.5
	});
	p.effect("highlight", {}, 500, function(){
		setTimeout(function(){
			p.remove();
		}, 2000);
	});
}

function insertSugarSyncPart(){
	var tagtext = "";
	if(cache.on == "pictures"){
		var pictures = new Array();
		for(pic in cache.pictures){
			if(cache.pictures[pic].current){
				pictures.push(cache.pictures[pic].options.name);
			}
		}
		if(pictures.length > 0){
			var size = [$("#co-ss-slider-width-val").val(), $("#co-ss-slider-height-val").val(), $("#co-ss-slider-rotate-val").val()];
			var style = $("#style").val();
			if(pictures.length == cache.pictures.length){
				tagtext = "[SugarSync::T:picture|S:"+size.join(",")+"|L:"+style+"|C:"+$("#accounts").val()+"|A:"+$("#albums").val()+"]";
			} else {
				tagtext = "[SugarSync::T:picture|S:"+size.join(",")+"|L:"+style+"|C:"+$("#accounts").val()+"|A:"+$("#albums").val()+"|P:"+pictures.join(",")+"]";
			}
		} else {
			popMsg(lang["Please select at least one picture"]);
			return;
		}
	} else if(cache.on == "albums"){
		var albums = new Array();
		for(album in cache.albums){
			if(cache.albums[album].current){
				albums.push(cache.albums[album].options.name);
			}
		}
		if(albums.length > 0){
			var size = [$("#co-ss-slider-width-val").val(), $("#co-ss-slider-height-val").val(), $("#co-ss-slider-rotate-val").val()];
			var style = $("#style").val();
			tagtext = "[SugarSync::T:album|S:"+size.join(",")+"|L:"+style+"|C:"+$("#accounts").val()+"|A:"+albums.join(",")+"]";
		}
	} else {
		popMsg(lang["First of all, choose to insert a picture or album!"]);
		return;
	}
	
	tagtext += "<br />";
	
	var add_text = true;
	
	if(add_text) {
		window.tinyMCEPopup.execCommand('mceInsertContent', false, tagtext);
	}
	window.tinyMCEPopup.close();
}

function changeCode(obj, target){
	$(target).val($(obj).val());
}

function requestApi(type){
	
	var api = sugarsync_api;
	var url = api;
	
	var accounts = $("#accounts").val();
	var albums = $("#albums").val();
	
	if(type == "accounts"){
		$("#albums").hide();
		$("#albums").val("");
		albums = "";
	}
	
	var pm = {};
	
	if(accounts != ""){
		pm.account = accounts;
		url = api + "?su=" + "albums";
		
		cache.albums = [];
		
		if(albums != ""){
			pm.account = accounts;
			pm.album = albums;
			url = api + "?su=" + "pictures";
			cache.pictures = [];
		}
		
		$.ajax({
			url: url,
			type: "POST",
			dataType: 'json',
			data: (pm),
			success: requestHandle
		});
		
	} else {
	
		requestHandle({type:"albums", data:{}});
	
	}

}

function requestHandle(data){
	
	//$("#select-bar").hide();
	//$("#insert-bar").hide();
	$("#bar").hide();
	$("#shower").removeClass("pl");
	
	$("#shower").empty();
	
	if(data.type == "albums"){
		
		cache.on = "albums";
		
		var albums = $("#albums");
		
		
		$("#shower").empty();
		
		if(!isEmpty(data.data)){
			
			albums.empty();
			albums.append('<option value="">-</option>');
			for(i in data.data){
				albums.append('<option value="'+data.data[i].album_slug+'">'+data.data[i].album_name+'</option>');
			}
			
			$("#albums").show();
			
			for(i in data.data){
				var img_conf = data.data[i].album_thumb;
				img_conf.title = data.data[i].album_name;
				img_conf.name = data.data[i].album_slug;
				img_conf.bar = ''
					+ '<div>' + data.data[i].album_name + '</div>'
					+ '<a href="#" onClick="enterAlbum(\'' + data.data[i].album_slug + '\'); return false;">' + lang["Enter"] + '</a>';
				$.extend(img_conf, pic_perf.albums || {});
				var img = new Pictures(img_conf);
				cache.albums.push(img);
				$("#shower").append(img.getUI());
			}
			$("#shower").addClass("pl");
			$("#bar").show();
		} else {
			albums.hide();
			popMsg(lang["No album"]);
		}
		
	} else if(data.type == "pictures"){
		
		cache.on = "pictures";
		
		$("#shower").empty();
		
		if(!isEmpty(data.data)){
			for(i in data.data){
				var img_conf = data.data[i];
				$.extend(img_conf, pic_perf.pictures || {});
				var img = new Pictures(img_conf);
				cache.pictures.push(img)
				$("#shower").append(img.getUI());
			}
			$("#shower").addClass("pl");
			$("#bar").show();
			
		} else {
			popMsg(lang["No picture"]);
		}
		
		//$("#shower").empty().append(shower_inner);
	}	
}

function enterAlbum(slug){
	$("#albums").val(slug);
	requestApi();
}

var Pictures = Class.create({
							
	initialize: function(options){
		this.options = {
			title      : "",
			path       : null,
			name       : null,
			bar        : "",
			extension  : null,
			height     : 128,
			width      : 128,
			rotation   : 0
		};
		$.extend(this.options, options || {});
		
		this.main = null;
		this._create();
	},
	
	_create: function(){
		this.main = null;
		
		if( this.options.path && this.options.name && this.options.extension ){
			var img_path = this.options.path + this.options.name + "_n_" + this.options.rotation + "_" + this.options.width + "x" + this.options.height + "." + this.options.extension;
			//this.main = $('<div class="co-ss-img-unit" style="height:'+this.options.height+'px;width:'+this.options.width+'px"></div>');
			
			this.main = $('<div class="co-ss-units" title="' + this.options.title + '"></div>');
			
			this.table = $('<table border="0" cellspacing="0" cellpadding="0"></table>');
			this.tbody = $('<tbody></tbody>');
			
			this.tr_img = $('<tr></tr>');
			this.td_img = $('<td class="co-ss-units-img-wapper" style="width:'+this.options.width+'px;height:'+this.options.height+'px;" width="'+this.options.width+'" height="'+this.options.height+'" align="center" valign="middle"></td>');
			this.tr_img.append(this.td_img);

			this.td_bar = $('<td class="co-ss-units-bar-wapper" height="20"></td>');
			this.td_bar.html(this.options.bar);
			this.tr_bar = $('<tr></tr>');
			this.tr_bar.append(this.td_bar);
			
			this.tbody.append(this.tr_img);
			if(this.options.bar) this.tbody.append(this.tr_bar);
			this.table.append(this.tbody);
			
			this.img = $('<img src="' + img_path + '" />');
			this.td_img.append(this.img);
			
			this.main.append(this.table);
			
			this.currentIco = $('<div class="co-ss-current-ico"></div>');
			this.currentIco.hide();
			this.main.append(this.currentIco);
			
			var self = this;
			
			this.main.hover(function(){
				self.focus();
			},function(){
				self.blur();
			});
			
			this.main.click(function(){
				self.changeCurrent();
			});
			
		}
	},
	
	changeCurrent: function(){
		if(this.current)
			this.lostCurrent();
		else
			this.getCurrent();
	},
	
	focus: function(){
		this.main.addClass("co-ss-units-focus");
	},
	
	blur: function(){
		this.main.removeClass("co-ss-units-focus");
	},
	
	getCurrent: function(){
		this.current = true;
		this.currentIco.show();
		this.main.addClass("co-ss-units-current");
	},
	
	lostCurrent: function(){
		this.current = false;
		this.currentIco.hide();
		this.main.removeClass("co-ss-units-current");
	},
	
	resize: function(options){
		$.extend(this.options, options || {});
		this._create();
	},
	
	getUI: function(){
		return this.main;
	},
	
	golden: function(){}
});

function selectAllPictures(){
	for(pic in cache.pictures)
		cache.pictures[pic].getCurrent();
}
function unSelectAllPictures(){
	for(pic in cache.pictures)
		cache.pictures[pic].lostCurrent();
}
function reverseSelectPictures(){
	for(pic in cache.pictures)
		cache.pictures[pic].changeCurrent();
}



function getAccounts(){
	
	var service = sugarsync_account;
	
	$.ajax({
		url: service,
		type: "GET",
		dataType: 'json',
		success: accountHandle
	});
}

function addNewAccount(){
	
	var service = sugarsync_account + "?action=new";
	
	var account = $("#newaccount").val();
	
	if(account){
		var pm = {account:account};
		
		$.ajax({
			url: service,
			type: "POST",
			dataType: 'json',
			data: (pm),
			success: accountHandle
		});
	}
}

function accountDelete(id){
	var service = sugarsync_account + "?action=delete";
	
	var pm = {id:id};
	
	$.ajax({
		url: service,
		type: "POST",
		dataType: 'json',
		data: (pm),
		success: accountHandle
	});
}

function accountHandle(data){
	if(data.type == "accounts"){
		
		cache.on = "accounts";
		cache.accounts = data.data;
		
		$("#account_list > tbody").empty();
		for(i in cache.accounts){
			$("#account_list > tbody").append('<tr><td>' + cache.accounts[i].account + '</td><td align="center"><a href="javascript:accountDelete(' + cache.accounts[i].id + ')">'+lang["Delete"]+'</a></td></tr>');
		}
		
		var accounts = $("#accounts");
		accounts.empty();
		accounts.append('<option value="">-</option>');
		
		for(i in cache.accounts){
			accounts.append('<option value="'+cache.accounts[i].account+'">'+cache.accounts[i].account+'</option>');
		}
	
	} else if(data.type == "message"){
		$("#account_message").html(data.data);
		$("#account_message").effect("highlight", {}, 500, function(){
			setTimeout(function(){
				$("#account_message").empty();
			}, 1000);
		});
	}
}

function isEmpty(o){
	var a = [];
	for(i in o){
		a.push(o[i]);
		break;
	}
	if(a.length>0)
		return false;
	else 
		return true;
}