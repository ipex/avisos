<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>

<title>
{$config.site_name}
</title>

<meta name="generator" content="reefLESS Boat Classifieds Software" />
<meta http-equiv="Content-Type" content="text/html; charset={$config.encoding}" />
<link href="{$rlTplBase}css/print.css" type="text/css" rel="stylesheet" />
<link rel="shortcut icon" href="{$smarty.const.RL_URL_HOME}{$smarty.const.ADMIN}/img/favicon.ico" />

<script type="text/javascript">
	var rlUrlHome = '{$rlTplBase}';
	var lang = new Array();
	lang['photo'] = '{$lang.photo}';
	lang['of'] = '{$lang.of}';
</script>

<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}jquery/jquery.js"></script>
<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}jquery/cookie.js"></script>
<script type="text/javascript" src="{$rlTplBase}js/lib.js"></script>

</head>
<body>

{if $smarty.get.item == 'listing'}

	<table class="sTable">
	<tr>
		<td><h1>{$listing_title}</h1></td>
		<td align="right"><input title="{$lang.print_page}" onclick="window.print();$(this).hide();" type="button" value="{$lang.print_page}" /></td>
	</tr>
	</table>
	<div class="sLine"></div>
	
	<table class="sTable">
	<tr>
		<td valign="top" style="width: 50%">
			<div class="box" style="margin-right: 30px;">
				<h3>{$lang.listing_photos}</h3>
				<img style="width: 340px" alt="" src="{$smarty.const.RL_URL_HOME}files/{$photo.Photo}" />
			</div>
		</td>
		<td valign="top">
			<div class="box">
				<h3>{$lang.owner_information}</h3>
				
				<table class="sTable">
				<tr>
					<td valign="top" style="width: 100px;">
						<div>
							{if $seller_info.Own_page}
								<a title="{$lang.visit_owner_page}" href="{$seller_info.Personal_address}">
							{/if}
							<img {if !empty($seller_info.Photo)}class="photo"{/if} title="" alt="{$lang.seller_thumbnail}" src="{if !empty($seller_info.Photo)}{$smarty.const.RL_URL_HOME}files/{$seller_info.Photo}{else}{$rlTplBase}img/no-account.png{/if}" />
							{if $seller_info.Own_page}
								</a>
							{/if}
						</div>
					</td>
					<td valign="top">
						<div style="padding: 0 0 0 10px;">
							<div class="username">{$seller_info.Full_name} ({$seller_info.Username})</div>
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
								<tr id="si_field_personal_address">
									<td class="name">{$lang.mail}:</td>
									<td class="value"><a href="mailto:{$seller_info.Mail}">{$seller_info.Mail}</a></td>
								</tr>
							{/if}
							</table>
						</div>
					</td>
				</tr>
				</table>
				
				{if $seller_info.Fields}
				<table class="table" style="margin-top: 10px;">
				{foreach from=$seller_info.Fields item='item' name='sellerF'}
					{if !empty($item.value)}
					<tr id="si_field_{$item.Key}">
						<td class="name">{$item.name}:</td>
						<td class="value {if $smarty.foreach.sellerF.first}first{/if}">{$item.value}</td>
					</tr>
					{/if}
				{/foreach}
				</table>
				{/if}
			</div>
		</td>
	</tr>
	</table>
	
	<!-- listing info -->
	<div class="listing">
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
						{if !empty($item.value) && $item.Details_page}
						<tr id="df_field_{$item.Key}">
							<td class="name">{$item.name}:</td>
							<td class="value {if $smarty.foreach.fListings.first}first{/if}">{$item.value}</td>
						</tr>
						{/if}
					{/foreach}
					</table>
					
					{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}
				{/if}
			{else}
				{if $group.Fields}
					<table class="table">
					{assign value=$group.Fields.0 var='item'}
					{if !empty($item.value) && $item.Details_page}
					<tr id="df_field_{$item.Key}">
						<td class="name">{$item.name}:</td>
						<td class="value">{$item.value}</td>
					</tr>
					{/if}
					</table>
				{/if}
			{/if}
		{/foreach}
	</div>
	<!-- listing info end -->
	
	<div class="footer">
		<span>&copy; {$smarty.now|date_format:'%Y'}, {$lang.powered_by} </span><a title="{$lang.powered_by} {$lang.copy_rights}" href="{$lang.flynax_url}">{$lang.copy_rights}</a>
	</div>
	
	</body>
	</html>

{elseif $smarty.get.item == 'browse' || $smarty.get.item == 'search' || $smarty.get.item == 'listings' }

	<table class="sTable">
	<tr>
		<td><h1>{if $smarty.get.item == 'browse'}{$lang.listings_of_category|replace:'[category]':$rss.title}{else}{$lang.search_results}{/if}</h1></td>
		<td align="right"><input title="{$lang.print_page}" onclick="window.print();$(this).slideUp('slow');" type="button" value="{$lang.print_page}" /></td>
	</tr>
	</table>
	<div class="sLine"></div>

	<!-- listings -->
	<div id="listings">
		{if !empty($listings)}
			{foreach from=$listings item='listing' key='key'}
			
			<div style="padding: 13px 0;border-bottom: 1px #ccc solid;">
				<table class="sTable">
				<tr>
					<td rowspan="2" style="width: 100px;padding: 0 10px 0 0;" align="center" valign="top">
						<img src="{if empty($listing.Main_photo)}{$rlTplBase}img/no-picture.jpg{else}{$smarty.const.RL_URL_HOME}files/{$listing.Main_photo}{/if}" />
					</td>
					<td class="spliter" rowspan="2"></td>
					<td valign="top" style="height: 65px">
						<table>
						{foreach from=$listing.fields item='item' key='field' name='fListings'}
						{if !empty($item.value)}
						<tr>
							<td valign="top" align="left">
								<div class="field">{$item.name}:</div>
							</td>
							<td style="width: 3px;"></td>
							<td valign="top" align="left">
								<div class="value">
								{if $smarty.foreach.fListings.first}
									<b>{$item.value}</b>
								{else}
									{$item.value}
								{/if}
								</div>
							</td>
						</tr>
						{/if}
						{/foreach}
						<tr>
							<td valign="top" align="left"><div class="field">{$lang.category}:</div></td>
							<td style="width: 3px;"></td>
							<td valign="top" align="left">
								<div class="value">{$listing.name}</div>
							</td>
						</tr>
						</table>
					</td>
				</tr>
				</table>
			</div>
			
			{/foreach}
		{/if}
	</div>
	<!-- listings end -->

{/if}