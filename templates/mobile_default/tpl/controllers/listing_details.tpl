<!-- listing details -->

{rlHook name='mobileListingDetailsTopTpl'}

<div id="width_tracker"></div>

<!-- tabs -->
<div id="tabs">
	<table class="sTable tabs">
	<tr>
		<td class="item active" abbr="listing">
			<table class="sTable">
			<tr>
				<td class="left"></td>
				<td class="center" valign="top"><div>{$lang.listing}</div></td>
				<td class="right"></td>
			</tr>
			</table>
		</td>
		
		<td class="divider"></td>
		<td class="item" abbr="seller">
			<table class="sTable">
			<tr>
				<td class="left"></td>
				<td class="center" valign="top"><div>{$lang.seller_info}</div></td>
				<td class="right"></td>
			</tr>
			</table>
		</td>
		
		{if !empty($video)}
			<td class="divider"></td>
			<td class="item" abbr="video">
				<table class="sTable">
				<tr>
					<td class="left"></td>
					<td class="center" valign="top"><div>{$lang.video}</div></td>
					<td class="right"></td>
				</tr>
				</table>
			</td>
		{/if}
		
		{if $config.map_module && $location}
			<td class="divider"></td>
			<td class="item" abbr="map">
				<table class="sTable">
				<tr>
					<td class="left"></td>
					<td class="center" valign="top"><div>{$lang.map}</div></td>
					<td class="right"></td>
				</tr>
				</table>
			</td>
		{/if}
		
		<td class="divider"></td>
		<td class="item" abbr="tell">
			<table class="sTable">
			<tr>
				<td class="left"></td>
				<td class="center" valign="top"><div>{$lang.tell_friend}</div></td>
				<td class="right"></td>
			</tr>
			</table>
		</td>
	</tr>
	</table>
</div>
<!-- tabs end -->

<!-- listing tab -->
<div id="listing_tab">

	<!-- listing photos -->
	{if $listing_type.Photo}
		<div class="photos">
		{if !empty($photos)}
			<div id="thumbnails">
				<div class="prev"></div>
				<div class="next"></div>
				
				<div id="scroll"><div class="inner"></div></div>
			</div>
			
			<div id="preview">
				<a href="javascript:void(0)" id="peview_photo"></a>
			</div>
			
			<span class="hide" id="tmp_area"></span>
			
			<script type="text/javascript">
			var files_url = '{$smarty.const.RL_URL_HOME}files/';
			var photos = new Array();
			var thumbnail_width = {$config.gallery_sizes_thum_width};
			var thumbnail_height = {$config.gallery_sizes_thum_height};
			var mask = 0;
			
			{foreach from=$photos item='photo' key='pgKey' name='photosF'}
				photos[{$pgKey}] = new Array('{$photo.Thumbnail}', '{$photo.Photo}', '{$photo.Description|replace:"'":'&#39;'|replace:'"':'&quot'}');
			{/foreach}
			</script>
			<script type="text/javascript" src="{$rlTplBase}js/photo_gallery.js"></script>
		{else}
			<div>{$lang.no_listing_photos}</div>
		{/if}
		</div>
		<div class="box_shadow"></div>
	{/if}
	<!-- listing photos end -->

	{rlHook name='listingDetailsPreFields'}
	
	<!-- listing info -->
	{foreach from=$listing item='group'}
		{if $group.Group_ID}
			{assign var='value_counter' value='0'}
			{foreach from=$group.Fields item='group_values' name='groupsF'}
				{if $group_values.value == '' || !$group_values.Details_page}
					{assign var='value_counter' value=$value_counter+1}
				{/if}
			{/foreach}
	
			{if !empty($group.Fields) && ($smarty.foreach.groupsF.total != $value_counter)}<!-- new thing -->
			{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id=$group.ID name=$group.name style='fg'}
			
			<div class="listing_group">
				<table>
				{foreach from=$group.Fields item='item' key='field' name='fListings'}
				{if !empty($item.value) && $item.Details_page}
				<tr id="df_field_{$item.Key}">
					<td valign="top">
						<div class="field">{$item.name}:</div>
					</td>
					<td valign="top">
						<div class="value">{$item.value}</div>	
					</td>
				</tr>
				{/if}
				{/foreach}
				</table>
			</div>
			
			{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}
			{/if}
		{else}
			<div class="listing_group">
				<table class="sTable">
				{assign value=$group.Fields.0 var='item'}
				{if !empty($item.value) && $item.Details_page}
				<tr id="df_field_{$item.Key}">
					<td style="width: 25%;" valign="top">
						<div class="field">{$item.name}:</div>
					</td>
					<td valign="top">
						<div class="value">{$item.value}</div>	
					</td>
				</tr>
				{/if}
				</table>
			</div>
		{/if}
	{/foreach}
	<!-- listing info end -->

	{rlHook name='listingDetailsPostFields'}

</div>
<!-- listing tab -->

<!-- seller info tab -->
<div id="seller_tab" class="hide">
	<div class="photos">
		<table class="sTable">
		<tr>
			<td rowspan="2" valign="top" style="width: 110px">
				<div class="img_border" style="margin: 0;">
					{if $seller_info.Own_page}<a title="{$lang.visit_owner_page}" href="{$seller_info.Personal_address}">{/if}
					<img class="img" title="{$seller_info.Full_name}" alt="{$seller_info.Full_name}" {if empty($seller_info.Photo)}style="width: 110px;"{/if} src="{if !empty($seller_info.Photo)}{$smarty.const.RL_URL_HOME}files/{$seller_info.Photo}{else}{$rlTplBase}img/account.gif{/if}" />
					{if $seller_info.Own_page}</a>{/if}
				</div>
				<div class="clear"></div>
			</td>
			<td valign="top">
				<div class="caption">
					{$seller_info.Full_name}
				</div>
			</td>
		</tr>
		<tr>
			<td valign="top">
				<ul class="item_stats">
					{if $seller_info.Own_page}
						{if $seller_info.Listings_count > 1}<li><a title="{$lang.other_owner_listings}" href="{$seller_info.Personal_address}#listings">{$lang.other_owner_listings}</a> <span class="counter">({$seller_info.Listings_count})</span></li>{/if}
					{/if}
					{rlHook name='mobileSellerinfoAfterStat'}
				</ul>

				<div style="padding: 0 10px;">
					<a id="contactOwnerBtn" class="button" href="javascript:void(0)">
						<span class="left">&nbsp;</span>
						<span class="center">{$lang.contact_owner}</span>
						<span class="right">&nbsp;</span>
					</a>
				</div>
			</td>
		</tr>
		</table>
		
		<div class="hide form" id="contact_owner">
			<div class="form_caption">{$lang.contact_owner}</div>
			<form onsubmit="xajax_contactOwner($('#contact_name').val(), $('#contact_email').val(), $('#contact_phone').val(), $('#contact_message').val(), $('#contact_code_security_code').val(), '{$listing_data.ID}');$(this).find('input[type=submit]').val('{$lang.loading}');return false;" name="contact_owner">
			<table class="sTable">
			<tr>
				<td><span class="field">{$lang.name}</span> <span class="red">*</span></td>
				<td><input type="text" class="text" id="contact_name" value="{$account_info.First_name} {$account_info.Last_name}" /></td>
			</tr>
			<tr>
				<td><span class="field">{$lang.mail}</span> <span class="red">*</span></td>
				<td><input type="email" class="text" id="contact_email" value="{$account_info.Mail}" /></td>
			</tr>
			<tr>
				<td><span class="field">{$lang.contact_phone}</span></td>
				<td><input type="text" class="text" id="contact_phone" /></td>
			</tr>
			<tr>
				<td colspan="2">
					<div class="field">{$lang.message} <span class="red">*</span></div>
					<textarea class="text" id="contact_message" rows="6" cols=""></textarea>
				</td>	
			</tr>
			<tr>
				<td>
					<span class="field">{$lang.security_code} <span class="red">*</span></span>
				</td>
				<td>
					{include file='captcha.tpl' no_caption=true captcha_id='contact_code' }
				</td>
			</tr>
			<tr>
				<td></td>
				<td>
					<input class="button" type="submit" name="finish" value="{$lang.send}" />
					<a class="cancel" onclick="$('#contact_owner').hide();" href="javascript:void(0);">{$lang.cancel}</a>
				</td>
			</tr>
			</table>
			</form>
		</div>
		
		<script type="text/javascript">
		{literal}
		
		$(document).ready(function(){
			$('#contactOwnerBtn').click(function(){
				$('#contact_owner').show();
				var poss = $('#contact_owner').position();
				$('html,body').scrollTop(poss.top+20);
			});
		});
		
		{/literal}
		</script>
		
	</div>
	<div class="box_shadow" style="margin-bottom: 15px;"></div>

	<div class="padding">
	
		<table class="listing_group">
		<tr>
			<td valign="top"><div class="field">{$lang.name}:</div></td>
			<td valign="top">
				<div class="value">
					{$seller_info.Full_name}
				</div>
			</td>
		</tr>
		
		{if $seller_info.Display_email}
		<tr>
			<td valign="top"><div class="field">{$lang.mail}:</div></td>
			<td valign="top"><div class="value">{encodeEmail email=$seller_info.Mail}</div></td>
		</tr>
		{/if}
		
		{foreach from=$seller_info.Fields item='field'}
		{if !empty($field.name) && !empty($field.value)}
		<tr id="si_field_{$field.Key}">
			<td valign="top"><div class="field">{$field.name}:</div></td>
			<td valign="top"><div class="value">{$field.value}</div></td>
		</tr>
		{/if}
		{/foreach}
		</table>
	</div>
</div>
<!-- seller info tab end -->

<!-- video tab -->
{if !empty($video)}
	<div id="video_tab" class="hide" style="padding: 0px 10px;">
		{if $video.Type == 'local'}
			{include file='blocks'|cat:$smarty.const.RL_DS|cat:'video.tpl'}
		{elseif $video.Type == 'youtube'}
			{$video.Embed}
		{/if}
	</div>
{/if}
<!-- video tab end -->

<!-- map tab -->
{if $config.map_module && $location}
<div id="map_tab" class="hide" style="padding: 0px 10px;">

	<div id="map" style="width: {if empty($config.map_width)}100%{else}{$config.map_width}px{/if}; height: {if empty($config.map_height)}300px{else}{$config.map_height}px{/if}"></div>
		
	<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=false{if $smarty.const.RL_LANG_CODE != '' && $smarty.const.RL_LANG_CODE != 'en'}&amp;language={$smarty.const.RL_LANG_CODE}{/if}"></script>
	<script type="text/javascript" src="http://www.google.com/uds/api?file=uds.js&amp;v=1.0&amp;key={$config.google_map_key}"></script>
	<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}jquery/jquery.flmap.js"></script>
	<script type="text/javascript">//<![CDATA[
	{literal}
	
	var map_exist = false;
	$(document).ready(function(){
		$('table.tabs td.item').click(function(){
			if ( !map_exist && $(this).attr('abbr') == 'map' )
			{
				$('#map').flMap({
					addresses: [
						[{/literal}'{if $location.direct}{$location.direct}{else}{$location.search}{/if}', '{$location.show}', '{if $location.direct}direct{else}geocoder{/if}'{literal}]
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
<!-- map tab end -->

<!-- tell a friend tab -->
<div id="tell_tab" class="hide" style="padding: 0px 10px;">
	<table class="sTable">
	<tr>
		<td style="width: 110px;">
			<span class="field">{$lang.friend_name} <span class="red">*</span></span>
		</td>
		<td>
			<input class="text" type="text" id="friend_name" maxlength="50" value="{$smarty.post.friend_name}" />
		</td>
	</tr>
	<tr>
		<td>
			<span class="field">{$lang.friend_email} <span class="red">*</span></span>
		</td>
		<td>
			<input class="text" type="email" id="friend_email" maxlength="100" value="{$smarty.post.friend_email}" />
		</td>
	</tr>
	<tr>
		<td>
			<span class="field">{$lang.your_name}</span>
		</td>
		<td>
			<input class="text" type="text" id="your_name" maxlength="30" value="{$account_info.First_name} {$account_info.Last_name}" />
		</td>
	</tr>
	<tr>
		<td>
			<span class="field">{$lang.your_email}</span>
		</td>
		<td>
			<input class="text" type="email" id="your_email" maxlength="30" value="{$account_info.Mail}" />
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<div class="field">{$lang.message} <span class="red">*</span></div>
			<textarea class="text" id="message" rows="6" cols="30">{$smarty.post.message}</textarea>
		</td>
	</tr>
	{if $config.security_img_tell_friend}
	<tr>
		<td>
			<span class="field">{$lang.security_code} <span class="red">*</span></span>
		</td>
		<td>
			{include file='captcha.tpl' no_caption=true}
		</td>
	</tr>
	{/if}
	<tr>
		<td></td>
		<td>
			<input onclick="xajax_tellFriend($('#friend_name').val(), $('#friend_email').val(), $('#your_name').val(), $('#your_email').val(), $('#message').val(), $('#security_code').val(), '{$listing_data.ID}');$('#tf_loading').fadeIn('normal');" style="margin: 0; width: 100px;" class="button" type="button" name="finish" value="{$lang.send}" />
			<span class="loading" id="tf_loading">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
		</td>
	</tr>
	</table>
</div>
<!-- tell a friend tab end -->

<script type="text/javascript">//<![CDATA[
{literal}

var active_tab = 'listing';
var map_showed = true;
var name = '';

$(document).ready(function(){
	$('table.tabs td.item').click(function(){
		name = $(this).attr('abbr');

		$('table.tabs td[abbr='+active_tab+']').removeClass('active');
		$(this).addClass('active');
		
		$('#'+active_tab+'_tab').hide();
		$('#'+name+'_tab').show();
		
		active_tab = name;
	});
});

{/literal}

//]]>
</script>

{rlHook name='mobileListingDetailsBottomTpl'}

<!-- listing details end -->