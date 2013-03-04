var link_head  = document.createElement('link');
link_head.href  = "{$smarty.const.RL_PLUGINS_URL}js_blocks/static/remote_adverts.css";
link_head.type = "text/css";
link_head.rel = "stylesheet";

document.getElementsByTagName('head').item(0).appendChild(link_head);

var $out ='';
var max_page =0;
var clicked_pages = Array();

$out +='<div id="jListingPaging"></div>';
{if !empty($smarty.get.per_page)}
	{assign var="per_page" value=$smarty.get.per_page}
{else}
	{assign var="per_page" value=5}
{/if}

{foreach from=$listings item='listing' name="fList"}
	{if $smarty.foreach.fList.first}
		$out +='<ul id="page_1" class="jListingPage">';
	{/if}

	{assign var="url" value=$smarty.const.RL_URL_HOME|cat:$listing.Page_path|cat:'/'|cat:$listing.Path|cat:'/'}
	{assign var="url2" value='-l'|cat:$listing.ID|cat:'.html'}
	$out += '<li id="jlisting_{$listing.ID}" class="jListingItem" onmouseover="changeCss(this)" onmouseout="restoreCss(this)" {if $smarty.get.direction == 'horizontal'}style="display:inline-block;margin-right:5px;*display:inline;*zoom:1"{/if} onclick="location.href=\'{$url}{str2path string=$listing.listing_title}{$url2}\'">';
	$out += '<table><tr><td class="jListingTdPicture">';
	$out += '<a href="{$rlBase}{if $config.mod_rewrite}{$listing.Page_path}/{$listing.Path}/{str2path string=$listing.listing_title}-l{$listing.ID}.html{else}?page={$browse_path}&amp;id={$listing.ID}{/if}{if $config.ra_statistics}?r={$tmp_code}{/if}">';
	$out +='<img class="jListingImg" src="{if !empty($listing.Main_photo)}{$smarty.const.RL_FILES_URL}{$listing.Main_photo}{else}{$smarty.const.RL_URL_HOME}templates/{$config.template}/img/no-picture.jpg{/if}"></img></a></td>';
	$out += '<td valign="top" class="jListingTdFields"><div class="jListingFields">';
	$out +='<table>';
	{foreach from=$listing.fields item='item' key='field' name='fListings'}
		{if !empty($item.value) && $item.Details_page}
			$out +='<tr><td>';
			{if !empty($item.name) && $smarty.get.field_names}	
				$out +='<span class="jListingField">{$item.name|escape:quotes}: </span>';
			{/if}

			{if $smarty.foreach.fListings.first}
				$out += '<a href="{$rlBase}{if $config.mod_rewrite}{$listing.Page_path}/{$listing.Path}/{str2path string=$listing.listing_title}-l{$listing.ID}.html{else}?page={$browse_path}&amp;id={$listing.ID}{/if}{if $config.ra_statistics}?r={$tmp_code}{/if}">';
				$out += '<span class="jListingFirst">';
				$out +='{$item.value|escape:quotes|regex_replace:"/[\r\t\n]/":"<br />"}</span></a>';
			{else}
				{if $item.Key == 'description_add'}
					$out += '<span class="jListingValue">{$item.value|strip_tags|truncate:175|escape}</span>';
				{else}
					$out += '<span class="jListingValue">{$item.value|escape:quotes}</span>';
				{/if}
			{/if}
			$out +='</td></tr>';
		{/if}
	{/foreach}
	$out +='</table>';
	$out += '</div></td></tr></table>';
	$out += '</li>';

	{if $smarty.foreach.fList.iteration%$per_page == 0}
		{assign var="page" value=$smarty.foreach.fList.iteration/$per_page+1}
		{if !$smarty.foreach.fList.last}
			$out += '</ul>';
			$out +='<ul id="page_{$page|ceil}" class="jListingPage jListingHide" >';
			max_page = {$page|ceil};
		{/if}
	{/if}
{/foreach}

$out +='</ul>';
{if $smarty.get.direction == 'horizontal'}$out +='<div style="clear:both;"></div>'{/if}
{literal}

function build()
{
	document.getElementById('{/literal}{if $smarty.get.custom_id}{$smarty.get.custom_id}{else}jl{/if}{literal}').innerHTML = $out;

	var $paging = '';
	
	for(var i=1; i<=max_page; i++)
	{
		var page = document.getElementById('page_' + i);
		$paging +='<span class="jListingPageItem" onmouseover="changeCss(this)" onmouseout="restoreCss(this)" id="pg_' + i + '" onclick=pageClick('+ i +');>' + i + '</span>';
	}
	document.getElementById('jListingPaging').innerHTML = $paging;
	
	if(typeof(conf_img_width) != 'undefined'){
		setStyleByClass("img", 'jListingImg', "width", conf_img_width);}
	if(typeof(conf_img_height)!= 'undefined'){
		setStyleByClass("img", 'jListingImg', "height", conf_img_height);}

	if(typeof(conf_advert_bg)!= 'undefined'){
		setStyleByClass("div", 'jListingItem', "background", conf_advert_bg);}
	if(typeof(conf_advert_border)!= 'undefined'){
		setStyleByClass("div", 'jListingItem', "border", conf_advert_border);}

	if(typeof(conf_field_first_color)!= 'undefined'){
		setStyleByClass("span", 'jListingFirst', "color", conf_field_first_color);}
	if(typeof(conf_field_color)!= 'undefined'){
		setStyleByClass("span", 'jListingValue', "color", conf_field_color);}
	if(typeof(conf_field_names_color)!= 'undefined'){
		setStyleByClass("span", 'jListingField', "color", conf_field_names_color);}

	if(typeof(conf_paging_bg)!= 'undefined'){
		setStyleByClass("span", 'jListingPageItem', "background", conf_paging_bg);}
	if(typeof(conf_paging_border)!= 'undefined'){
		setStyleByClass("span", 'jListingPageItem', "border", conf_paging_border);}
}

function changeCss( obj )
{
	if( obj.className == 'jListingItem' )
	{
		if(typeof(conf_advert_bg_hover) != 'undefined'){
			obj.style.background = conf_advert_bg_hover;}
		if(typeof(conf_advert_border_hover) != 'undefined'){
			obj.style.border = conf_advert_border_hover;}
	}else if( obj.className == 'jListingPageItem' )
	{
		if(typeof(conf_paging_bg_hover) != 'undefined'){
			obj.style.background = conf_paging_bg_hover;}
		if(typeof(conf_paging_border_hover) != 'undefined'){
			obj.style.border = conf_paging_border_hover;}
	}
}

function restoreCss( obj )
{
	if( obj.className == 'jListingItem' )
	{
		if(typeof(conf_advert_bg) != 'undefined'){
			obj.style.background = conf_advert_bg;}
		if(typeof(conf_advert_border) != 'undefined'){
			obj.style.border = conf_advert_border;}
	}else if( obj.className == 'jListingPageItem' )
	{
		if(typeof(conf_paging_bg) != 'undefined'){
			obj.style.background = conf_paging_bg;}
		if(typeof(conf_paging_border) != 'undefined'){
			obj.style.border = conf_paging_border;}
	}
}

function pageClick(n)
{
	{/literal}{if $config.ra_statistics}{literal}
	/* send data for statistics */
	if(n != 1 && !(clicked_pages[n]))
	{
		clicked_pages.push(n);
		clicked_pages[n] = 1;
		var listings = document.getElementById('page_'+n).getElementsByClassName("jListingItem");
		var ids ='';
		for(var i=0; i < listings.length; i++)
		{
			ids +=listings[i]['id'].split('_')[1] +',';
		}

		var ping  = document.createElement('script');
		{/literal}
		ping.src  = "{$smarty.const.RL_PLUGINS_URL}js_blocks/blocks.inc.php?action=ping&ids="+ids;
		{literal}
		ping.type = "text/javascript";
		
		document.getElementsByTagName('head').item(0).appendChild(ping);
	}
	{/literal}{/if}{literal}
	for(var k=1; k<=max_page; k++)
	{
		if(k==n)
		{	
			document.getElementById('page_' + k).className = 'jListingPage';
			document.getElementById('pg_' + k).className = 'active';

			if(typeof(conf_paging_bg_hover) != 'undefined' && typeof(conf_paging_border_hover) != 'undefined'){
				document.getElementById('pg_' + k).style.background = conf_paging_bg_hover;
				document.getElementById('pg_' + k).style.border = conf_paging_border_hover;
			}
		}
		else
		{
			document.getElementById('page_' + k).className = 'jListingHide';
			document.getElementById('pg_' + k).className = 'jListingPageItem';

			if(typeof(conf_paging_bg) != 'undefined' && typeof(conf_paging_border) != 'undefined'){
				document.getElementById('pg_' + k).style.background = conf_paging_bg;
				document.getElementById('pg_' + k).style.border = conf_paging_border;
			}		
		}
	}	
}

function setStyleByClass(t,c,p,v){
	var elements;
	if(t == '*') {
		elements = (ie) ? document.all : document.getElementsByTagName('*');
	} else {
		elements = document.getElementsByTagName(t);
	}
	for(var i = 0; i < elements.length; i++){
		var node = elements.item(i);
		for(var j = 0; j < node.attributes.length; j++) {
			if(node.attributes.item(j).nodeName == 'class') {
				if(node.attributes.item(j).nodeValue == c) {
					eval('node.style.' + p + " = '" +v + "'");
				}
			}
		}
	}
}

document.onready = build();

{/literal}




