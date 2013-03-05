<!-- listing details -->

{if !$errors}

{rlHook name='listingDetailsTopTpl'}

<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}player/flowplayer.js"></script>
<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}jquery/jquery.fancybox.js"></script>
{if $config.gallery_slideshow}
<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}jquery/fancybox/helpers/jquery.fancybox-buttons.js"></script>
{/if}
<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}jquery/numeric.js"></script>
<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}jquery/jquery.textareaCounter.js"></script>
<script type="text/javascript">
var fb_slideshow_close = {if $config.gallery_slideshow}false{else}true{/if};
var fb_slideshow = {if $config.gallery_slideshow}{literal}{}{/literal}{else}false{/if};
var fb_slideshow_delay = {if $config.gallery_slideshow_delay}{$config.gallery_slideshow_delay}*1000{else}5000{/if};
</script>

<!-- tabs -->
<div class="tabs">
	<ul>
		{foreach from=$tabs item='tab' name='tabF'}
		<li {if $smarty.foreach.tabF.first}class="active first"{/if} id="tab_{$tab.key}">
			<span class="center">{$tab.name}</span>
		</li>
		{/foreach}
	</ul>
</div>
<div class="clear"></div>
<!-- tabs end -->

<!-- listing details -->
<div id="area_listing" class="tab_area">
	<table class="listing_details">
	<tr>
		<td valign="top" class="side_bar">
			<!-- listing photos -->
			{if $listing_type.Photo}		
				<div class="photos">
					{if $photos}
						{foreach from=$listing item='group'}
							{foreach from=$group.Fields item='item' }
								{if !empty($item.value) && $item.Details_page && $item.Key == 'price'}
									<span class="price_tag" id="df_field_price">{$item.value}<span></span></span>
									{assign var='price_tag' value=true}
								{/if}
							{/foreach}
						{/foreach}
						
						{if $config.pg_gallery_type == 'LightBox'}
							<ul class="inline">
							{foreach from=$photos item='photo' name='photosF'}
								<li {if $smarty.foreach.photosF.iteration%2 != 0}class="nl"{/if}>
									<a title="{if $photo.Description}{$photo.Description}{else}{$pageInfo.name}{/if}" rel="group" href="{$smarty.const.RL_URL_HOME}files/{$photo.Photo}"><img alt="" class="shadow" src="{$smarty.const.RL_URL_HOME}files/{$photo.Thumbnail}" /></a>
								</li>
							{/foreach}
							</ul>
							<div class="clear"></div>
							
							<script type="text/javascript">
							{literal}
							
							$(document).ready(function(){
								$('div.photos ul.inline a').fancybox({
									padding: 10,
									removeFirst: false,
									customIndex: false,
									mouseWheel: true,
									closeBtn: fb_slideshow_close,
									playSpeed: fb_slideshow_delay,
									helpers: {
							    		title: {
							    			type: 'over'
							    		},
							    		overlay: {
											opacity: 0.5
										},
										buttons: fb_slideshow
							    	}
								});
							});
							
							{/literal}
							</script>
						{elseif $config.pg_gallery_type == 'PreviewBox'}
							{if $config.pg_thumbnails_position == 'bottom'}
								<div class="preview">
									<a rel="group" href="{$smarty.const.RL_URL_HOME}files/{$photos.0.Photo}" title="{if $photos.0.Description}{$photos.0.Description}{else}{$pageInfo.name}{/if}">
										<img alt="{if $photos.0.Description}{$photos.0.Description}{else}{$pageInfo.name}{/if}" class="shadow" src="{$smarty.const.RL_URL_HOME}files/{$photos.0.Photo}" />
									</a>
									<div></div>
								</div>
							{/if}
							
							<script type="text/javascript" src="{$rlTplBase}js/photo_gallery.js"></script>
							{if $photos|@count > 1}
								{math assign='slides' equation='ceil(count/(rows*cols))' cols=$config.pg_previewbox_cols rows=$config.pg_previewbox_rows count=$photos|@count}
								{assign var='per_slide' value=$config.pg_previewbox_cols*$config.pg_previewbox_rows}
								<div class="slider">
									<ul>
										{assign var='pFrom' value=0}
										{section name='pSlide' start='0' loop=$slides}
										<li>
											{section name='index' loop=$photos start=$pFrom max=$per_slide}<div><img title="{if $photos[index].Description}{$photos[index].Description}{else}{$pageInfo.name}{/if}" alt="{if $photos[index].Description}{$photos[index].Description}{else}{$pageInfo.name}{/if}" class="{$photos[index].Photo}" style="{if $smarty.section.index.iteration%$config.pg_previewbox_cols == 0}margin: 0 0 4px 0;{/if}{if $config.gallery_sizes_thum_width}width: {$config.gallery_sizes_thum_width}px;{/if}" src="{$smarty.const.RL_URL_HOME}files/{$photos[index].Thumbnail}" /></div>{/section}
											{assign var='pFrom' value=$pFrom+$per_slide}
										</li>
										{/section}
									</ul>
									{if $photos|@count > $per_slide}
									<div class="nav_bar">
										<div class="arrow"><div class="prev hide"></div></div>
										<div class="navigation"></div>
										<div class="arrow"><div class="next"></div></div>
									</div>
									{/if}
								</div>
							{/if}
							
							{if $config.pg_thumbnails_position == 'top'}
								<div class="preview">
									<a rel="group" href="{$smarty.const.RL_URL_HOME}files/{$photos.0.Photo}" title="{if $photos.0.Description}{$photos.0.Description}{else}{$pageInfo.name}{/if}">
										<img alt="{if $photos.0.Description}{$photos.0.Description}{else}{$pageInfo.name}{/if}" class="shadow" src="{$smarty.const.RL_URL_HOME}files/{$photos.0.Photo}" />
									</a>
									<div></div>
								</div>
							{/if}
							
							<div id="imgSource" class="hide">
							{foreach from=$photos item='photo'}
								<a rel="group" href="{$smarty.const.RL_URL_HOME}files/{$photo.Photo}" title="{if $photo.Description}{$photo.Description}{else}{$pageInfo.name}{/if}"></a>
							{/foreach}
							</div>
						{/if}
					{else}
						{$lang.no_listing_photos}
					{/if}
				</div>
			{/if}
			<!-- listing photos end -->
			
			<!-- listing stat data -->
			<ul class="statistics">
				{rlHook name='listingDetailsBeforeStats'}
				
				<li><span class="name">{$lang.category}:</span> <a title="{$lang[$listing_data.Category_pName]}" href="{$rlBase}{if $config.mod_rewrite}{$pages[$listing_type.Page_key]}/{$listing_data.Path}{if $listing_type.Cat_postfix}.html{else}/{/if}{else}?page={$pages[$listing_type.Page_key]}&amp;category={$listing_data.Category_ID}{/if}">{$lang[$listing_data.Category_pName]}</a></li>
				{if $config.count_listing_visits}<li><span class="name">{$lang.shows}:</span> {$listing_data.Shows}</li>{/if}
				{if $config.display_posted_date}<li><span class="name">{$lang.posted}:</span> {$listing_data.Date|date_format:$smarty.const.RL_DATE_FORMAT}</li>{/if}
				
				{rlHook name='listingDetailsAfterStats'}
				
				{if $listing_data.Account_ID == $account_info.ID}
					<li style="padding: 20px 0 0 0;">
						<a class="button" href="{$rlBase}{if $config.mod_rewrite}{$pages.edit_listing}.html?id={$listing_data.ID}{else}?page={$pages.edit_listing}&amp;id={$listing_data.ID}{/if}">{$lang.edit_listing}</a>
					</li>
				{/if}
			</ul>
			<!-- listing stat data end -->
		</td>
		<td valign="top" class="details">
			<div class="highlight">
				<!-- share tools -->
				<div class="listing_share">
					<table align="{$text_dir_rev}"><tr><td>
					<div class="addthis_toolbox addthis_default_style ">
					<a class="addthis_button_preferred_1"></a>
					<a class="addthis_button_preferred_2"></a>
					<a class="addthis_button_preferred_3"></a>
					<a class="addthis_button_preferred_4"></a>
					<a class="addthis_button_compact"></a>
					<a class="addthis_counter addthis_bubble_style"></a>
					</div>
					</td></tr></table>
					<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=xa-50bf3e91400a72c5"></script>
				</div>	
				<!-- share tools end -->
				
				<!-- listing info -->
				{rlHook name='listingDetailsPreFields'}

				{foreach from=$listing item='group'}
					{if $group.Group_ID}
						{assign var='hide' value=true}
						{if $group.Fields && $group.Display}
							{assign var='hide' value=false}
						{/if}
				
						{assign var='value_counter' value='0'}
						{foreach from=$group.Fields item='group_values' name='groupsF'}
							{if $group_values.value == '' || !$group_values.Details_page}
								{assign var='value_counter' value=$value_counter+1}
							{/if}
						{/foreach}
				
						{if !empty($group.Fields) && ($smarty.foreach.groupsF.total != $value_counter)}
							{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id=$group.ID name=$group.name}
							
							<table class="table">
							{foreach from=$group.Fields item='item' key='field' name='fListings'}
								{if !empty($item.value) && $item.Details_page && ($price_tag && $item.Key != 'price') || !$price_tag}
									{include file='blocks'|cat:$smarty.const.RL_DS|cat:'field_out.tpl'}
								{/if}
							{/foreach}
							</table>
							
							{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}
						{/if}
					{else}
						{if $group.Fields}
							<table class="table">
							{foreach from=$group.Fields item='item' }
								{if !empty($item.value) && $item.Details_page && ($price_tag && $item.Key != 'price' || !$price_tag)}
									{include file='blocks'|cat:$smarty.const.RL_DS|cat:'field_out.tpl'}
								{/if}
							{/foreach}
							</table>
						{/if}
					{/if}
				{/foreach}
				<!-- listing info end -->
				
				{if isset($smarty.get.highlight)}
				<script type="text/javascript">flynax.highlightSRDetails("{$smarty.session.keyword_search_data.keyword_search}");</script>
				{/if}
			</div>
		</td>
	</tr>
	</table>
</div>

<script type="text/javascript">
{literal}
$(document).ready(function(){
	if ( $('div#area_listing td.side_bar>*').length == 0 )
	{
		$('div#area_listing td.side_bar').remove();
	}
});
{/literal}
</script>
<!-- listing details end -->

<!-- seller info -->
<div id="area_seller" class="tab_area hide">
	<table class="seller_info">
	<tr>
		<td valign="top" class="side_bar">
			<div>
				{if $seller_info.Own_page}
					<a title="{$lang.visit_owner_page}" href="{$seller_info.Personal_address}">
				{/if}
				<img {if !empty($seller_info.Photo)}class="photo"{/if} title="" alt="{$lang.seller_thumbnail}" src="{if !empty($seller_info.Photo)}{$smarty.const.RL_URL_HOME}files/{$seller_info.Photo}{else}{$rlTplBase}img/no-account.png{/if}" />
				{if $seller_info.Own_page}
					</a>
				{/if}
			</div>
			<ul class="info">
				{if $config.messages_module && ($isLogin || (!$isLogin && $config.messages_allow_free))}<li><input id="contact_owner" type="button" value="{$lang.contact_owner}" /></li>{/if}
				{if $seller_info.Own_page}
					<li><a title="{$lang.visit_owner_page}" href="{$seller_info.Personal_address}">{$lang.visit_owner_page}</a></li>
					{if $seller_info.Listings_count > 1}<li><a title="{$lang.other_owner_listings}" href="{$seller_info.Personal_address}#listings">{$lang.other_owner_listings}</a> <span class="counter">({$seller_info.Listings_count})</span></li>{/if}
				{/if}
			</ul>
		</td>
		<td valign="top" class="details">
			<div class="highlight">
				<div class="username">{$seller_info.Full_name}</div>
				<table class="table" style="margin: 0 0 15px;">
				<tr id="si_field_join_date">
					<td class="name">{$lang.join_date}:</td>
					<td class="value first">{$seller_info.Date|date_format:$smarty.const.RL_DATE_FORMAT}</td>
				</tr>
				{if $seller_info.Own_page && $seller_info.Personal_address}
				<tr id="si_field_personal_address">
					<td class="name">{$lang.personal_address}:</td>
					<td class="value"><a title="{$lang.visit_owner_page}" href="{$seller_info.Personal_address}">{$seller_info.Personal_address}</a></td>
				</tr>
				{/if}
				{if $seller_info.Display_email}
					<tr id="si_field_mail">
						<td class="name">{$lang.mail}:</td>
						<td class="value">{encodeEmail email=$seller_info.Mail}</td>
					</tr>
				{/if}
				</table>
				
				{if $seller_info.Fields}
				<table class="table">
				{foreach from=$seller_info.Fields item='item' name='fListings'}
					{if !empty($item.value) && $item.Details_page}
						{include file='blocks'|cat:$smarty.const.RL_DS|cat:'field_out.tpl'}
					{/if}
				{/foreach}
				</table>
				{/if}
			</div>
		</td>
	</tr>
	</table>
	
	<!-- contact owner form template -->
	<div class="hide" id="contact_owner_form">
		<div class="caption">{$lang.contact_owner}</div>
		
		<form name="contact_owner" onsubmit="flynax.contactOwnerSubmit($(this).find('input[type=submit]'), {$listing_data.ID});return false;" method="post" action="">
			<table class="submit_modal">
			{if $isLogin}
				<tr>
					<td colspan="2">
						<div>{$lang.message} <span class="red">*</span></div>
						<textarea id="contact_owner_message" rows="6" cols="" style="width: 97%;"></textarea>
					</td>
				</tr>
			{else}
				<tr>
					<td class="name">{$lang.name} <span class="red">*</span></td>
					<td class="field"><input type="text" id="contact_name" value="{$account_info.First_name} {$account_info.Last_name}" /></td>
				</tr>
				<tr>
					<td class="name">{$lang.mail} <span class="red">*</span></td>
					<td class="field"><input type="text" id="contact_email" value="{$account_info.Mail}" /></td>
				</tr>
				<tr>
					<td class="name">{$lang.contact_phone}</td>
					<td class="field"><input type="text" id="contact_phone" /></td>
				</tr>
				<tr>
					<td class="name" colspan="2">
						<div>{$lang.message} <span class="red">*</span></div>
						<textarea id="contact_owner_message" rows="6" cols="" style="width: 97%;"></textarea>
					</td>	
				</tr>
				<tr>
					<td colspan="2">{include file='captcha.tpl' captcha_id='contact_code' no_wordwrap=true}</td>
				</tr>
			{/if}
			<tr>
				<td colspan="2" {if !$isLogin}class="button"{/if}>
					<input type="submit" name="finish" value="{$lang.send}" />
					<input type="button" name="close" value="{$lang.cancel}" />
				</td>
			</tr>
			</table>
		</form>
	</div>
	<!-- contact owner form template end -->
	
	<script type="text/javascript">
	{literal}
	
	$(document).ready(function(){
		$('#contact_owner').flModal({
			source: '#contact_owner_form',
			width: 500,
			height: 'auto',
			ready: function(){
				$('#contact_owner_message').textareaCount({
					'maxCharacterSize': rlConfig['messages_length'],
					'warningNumber': 20
				})
			}
		});
	});
	
	{/literal}
	</script>
</div>
<!-- seller info end -->

<!-- map -->
{if $config.map_module && $location}
<div id="area_map" class="tab_area hide">
	<div class="highlight">
		<div id="map" style="width: {if empty($config.map_width)}100%{else}{$config.map_width}px{/if}; height: {if empty($config.map_height)}300px{else}{$config.map_height}px{/if}"></div>
	</div>
		
	<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=false{if $smarty.const.RL_LANG_CODE != '' && $smarty.const.RL_LANG_CODE != 'en'}&amp;language={$smarty.const.RL_LANG_CODE}{/if}"></script>
	<script type="text/javascript" src="http://www.google.com/uds/api?file=uds.js&amp;v=1.0&amp;key={$config.google_map_key}"></script>
	<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}jquery/jquery.flmap.js"></script>
	<script type="text/javascript">//<![CDATA[
	{literal}
	
	var map_exist = false;
	$(document).ready(function(){
		$('div.tabs li').click(function(){
			if ( !map_exist && $(this).attr('id') == 'tab_map' )
			{
				$('#map').flMap({
					addresses: [
						[{/literal}'{if $location.direct}{$location.direct}{else}{$location.search}{/if}', '{$location.show}', '{if $location.direct}direct{else}geocoder{/if}'{literal}]
						//['Tamiami, FL', 'Tamiami!', 'geocoder']
						//['-25.363882,131.044922', 'Direct']
					],
					phrases: {
						hide: '{/literal}{$lang.hide}{literal}',
						show: '{/literal}{$lang.show}{literal}',
						notFound: '{/literal}{$lang.location_not_found}{literal}'
					},
					zoom: {/literal}{$config.map_default_zoom}{if $config.map_amenities && $amenities},{literal}
					localSearch: {
						caption: '{/literal}{$lang.local_amenity}{literal}',
						services: [{/literal}
							{foreach from=$amenities item='amenity' name='amenityF'}
							['{$amenity.Key}', '{$amenity.name}', {if $amenity.Default}'checked'{else}false{/if}]{if !$smarty.foreach.amenityF.last},{/if}
							{/foreach}
						{literal}]
					}
					{/literal}{/if}{literal}
				});
				map_exist = true;
			}
		});
	});
	
	{/literal}
	//]]>
	</script>
</div>
{/if}
<!-- map end -->

<!-- video tab -->
{if !empty($videos)}
<div id="area_video" class="tab_area hide">
	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'video_grid.tpl'}
</div>
{/if}
<!-- video tab end -->

<!-- tell a friend tab -->
<div id="area_tell" class="tab_area hide">
	<div class="highlight">
		<table class="submit">
		<tr>
			<td class="name">{$lang.friend_name} <span class="red">*</span></td>
			<td class="field"><input type="text" id="friend_name" maxlength="50" value="{$smarty.post.friend_name}" /></td>
		</tr>
		<tr>
			<td class="name">{$lang.friend_email} <span class="red">*</span></td>
			<td class="field"><input type="text" id="friend_email" maxlength="50" value="{$smarty.post.friend_email}" /></td>
		</tr>
		<tr>
			<td class="name">{$lang.your_name}</td>
			<td class="field"><input type="text" id="your_name" maxlength="100" value="{$account_info.Full_name}" /></td>
		</tr>
		<tr>
			<td class="name">{$lang.your_email}</td>
			<td class="field"><input class="text" type="text" id="your_email" maxlength="30" value="{$account_info.Mail}" /></td>
		</tr>
		<tr>
			<td class="name">{$lang.message}</td>
			<td class="field"><textarea id="message" rows="6" cols="50">{$smarty.post.message}</textarea></td>
		</tr>
		{if $config.security_img_tell_friend}
		<tr>
			<td class="name">{$lang.security_code} <span class="red">*</span></td>
			<td class="field">{include file='captcha.tpl' no_caption=true}</td>
		</tr>
		{/if}
		<tr>
			<td class="name"></td>
			<td class="field">
				<input onclick="xajax_tellFriend($('#friend_name').val(), $('#friend_email').val(), $('#your_name').val(), $('#your_email').val(), $('#message').val(), $('#security_code').val(), '{$print.id}');$('#tf_loading').fadeIn('normal');" type="button" name="finish" value="{$lang.send}" />
				<span class="loading_highlight" id="tf_loading">&nbsp;</span>
			</td>
		</tr>
		</table>
	</div>
</div>
<!-- tell a friend tab end -->

{rlHook name='listingDetailsBottomTpl'}

{/if}

<!-- listing details end -->