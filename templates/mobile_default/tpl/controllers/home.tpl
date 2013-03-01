<!-- home tpl -->

<script type="text/javascript" src="{$rlTplBase}js/jquery.mousewheel.js"></script>

<!-- quick search -->
<div id="qucik_search">
	<form method="post" action="{$rlBase}{if $config.mod_rewrite}{$pages.search}.html{else}?page={$pages.search}{/if}">
		<input type="hidden" name="form" value="keyword_search" />
		<table class="sTable">
		<tr>
			<td class="left"></td>
			<td class="center"><input type="text" name="f[keyword_search]" title="{$lang.keyword_search}" maxlength="255" {if $smarty.post.f.keyword_search}value="{$smarty.post.f.keyword_search}"{/if} /></td>
			<td class="right"><input type="submit" value="" /></td>
		</tr>
		</table>
	</form>
</div>
<!-- quick search end -->

{if $mobile_featured}
<!-- featured carousel -->
<div id="width_tracker"></div>
<div id="carousel">
	<div class="left_nav"></div>
	<div class="visible">
		<ul>
		{foreach from=$mobile_featured item='listing' key='key'}
			{if $listing.Listing_type}
				{assign var='featured_listing_type' value=$listing_types[$listing.Listing_type]}
			{/if}
			<li class="item" style="width: {if $config.pg_upload_thumbnail_width > 100}106{else}{math equation='x + y' x=$config.pg_upload_thumbnail_width y=6}{/if}px;">	
				<div class="img_border">
					<a href="{$rlBase}{if $config.mod_rewrite}{$pages[$featured_listing_type.Page_key]}/{$listing.Path}/{str2path string=$listing.listing_title}-{$listing.ID}.html{if $hl}?highlight{/if}{else}?page={$pages[$featured_listing_type.Page_key]}&amp;id={$listing.ID}{if $hl}&amp;highlight{/if}{/if}">
						<img style="width: {if $config.pg_upload_thumbnail_width > 100}100{else}{$config.pg_upload_thumbnail_width}{/if}px;" alt="{$listing.fields.0.value}" title="{$listing.fields.0.value}" src="{if empty($listing.Main_photo)}{$rlTplBase}img/no_photo.gif{else}{$smarty.const.RL_URL_HOME}files/{$listing.Main_photo}{/if}" />
					</a>
				</div>
				<div class="clear"></div>
				
				{foreach from=$listing.fields item='item' key='field' name='fListings'}
					{if !empty($item.value) && $item.Details_page}
					<div class="fField" style="width: {if $config.pg_upload_thumbnail_width > 100}106{else}{math equation='x + y' x=$config.pg_upload_thumbnail_width y=6}{/if}px;">
						{if $smarty.foreach.fListings.first}
							<b>{$item.value}</b>
						{else}
							{$item.value}
						{/if}
					</div>
					{/if}
				{/foreach}
			</li>
		{/foreach}
		</ul>
	</div>
	<div class="right_nav"></div>
</div>
<div class="box_shadow"></div>

<script type="text/javascript">
var item_float = rlLangDir == 'rtl' ? 'right' : 'left';
var item_float_rev = rlLangDir == 'rtl' ? 'left' : 'right';

{literal}

$(document).ready(function(){
	$(window).resize(function(){
		carouselWidthHandler();
	});
	
	carouselWidthHandler();
	scrollClick();
});

window.onorientationchange = function()
{
	carouselWidthHandler();
	scrollClick();
}

var areaWidth = 0;
var carouselWidthHandler = function(){
	areaWidth = parseInt($('#width_tracker').width());
	
	areaWidth = areaWidth > 0 ? areaWidth: 320;
	areaWidth -= 80;
	
	$('div#carousel div.visible').width(areaWidth);
};

var scrollClick = function(){
	var obj = '#carousel div.visible';
	var count = $(obj).find('ul li').length;
	var itemWidth = $(obj).find('ul li:first').width();
	var visibleWidth = $(obj).width();
	var margin = 5;
	var poss = 0;
	var activeItem = 0;
	var visible = Math.floor(visibleWidth/itemWidth);
	var perSlide = visible;
	
	var diff = Math.ceil(visibleWidth - (visible*itemWidth));
	
	var newMargin = Math.ceil(diff / (visible > 1 ? visible - 1 : visible));
	if ( newMargin > margin )
	{
		$(obj).find('ul li:not(:last)').css('margin-'+item_float_rev, newMargin+'px');
		margin = newMargin;	
	}
	
	/* back to 0 */
	if ( rlLangDir == 'rtl' )
	{
		$(obj).find('ul').css({
			marginRight: 0
		});
	}
	else
	{
		$(obj).find('ul').css({
			marginLeft: 0
		});
	}
	
	$('.right_nav').unbind('click').click(function(){
		if ( (activeItem + visible) >= count )
		{
			return;
		}
		
		poss -= (itemWidth + margin) * perSlide;
		activeItem += perSlide;
		
		if ( rlLangDir == 'rtl' )
		{
			$(obj).find('ul').css({
				marginRight: poss
			});
		}
		else
		{
			$(obj).find('ul').css({
				marginLeft: poss
			});
		}
	});
	$('.left_nav').unbind('click').click(function(){
		if ( activeItem <= 0 )
		{
			return;
		}
		
		poss += (itemWidth + margin) * perSlide;
		activeItem -= perSlide;
		
		if ( rlLangDir == 'rtl' )
		{
			$(obj).find('ul').css({
				marginRight: poss
			});
		}
		else
		{
			$(obj).find('ul').css({
				marginLeft: poss
			});
		}
	});
};

{/literal}
</script>
<!-- featured carousel -->
{/if}

<!-- user menu -->
<div id="user_menu">
	{foreach from=$user_menu item=muser}
		<table class="sTable">
		<tr>
			<td class="left">&nbsp;</td>
			<td class="center">
				<a href="{$rlBase}{if $config.mod_rewrite}{$pages[$muser.Key]}.html{else}?page={$pages[$muser.Key]}{/if}">
					{$muser.name}
				</a>
			</td>
			<td class="right">&nbsp;</td>
		</tr>   
		</table>
	{/foreach}
</div>
<!-- user menu end -->

<!-- home tpl end -->