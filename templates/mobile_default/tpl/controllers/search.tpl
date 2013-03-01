<!-- search tpl -->

<!-- print search form -->
{if $search_forms}

	<!-- tabs -->
	<div id="tabs">
		<table class="sTable tabs">
		<tr>
			{if $search_forms|@count > 0}
				{foreach from=$search_forms item='search_form' key='sf_key' name='sformsF'}
				{assign var='tab_phrase' value='listing_types+name+'|cat:$listing_types[$sf_key].Key}
				<td class="item{if $smarty.foreach.sformsF.first && !$keyword_search} active{/if}" abbr="{$sf_key|replace:'_':''}">
					<table class="sTable">
					<tr>
						<td class="left"></td>
						<td class="center" valign="top"><div>{$lang[$tab_phrase]}</div></td>
						<td class="right"></td>
					</tr>
					</table>
				</td>
				<td class="divider"></td>
				{/foreach}
			{/if}
			
			{assign var='ks_phrase' value='blocks+name+keyword_search'}
			<td class="item{if $keyword_search} active{/if}" abbr="keyword">
				<table class="sTable">
				<tr>
					<td class="left"></td>
					<td class="center" valign="top"><div>{$lang.$ks_phrase}</div></td>
					<td class="right"></td>
				</tr>
				</table>
			</td>
		</tr>
		</table>
	</div>
	<!-- tabs end -->
	
	{foreach from=$search_forms item='search_form' key='sf_key' name='sformsF'}
		{assign var='spage_key' value=$listing_types[$sf_key].Page_key}
		
		{if $smarty.foreach.sformsF.first}<script type="text/javascript">var active_tab = "{$sf_key|replace:'_':''}";</script>{/if}
		
		<div id="{$sf_key|replace:'_':''}_tab" class="{if !$smarty.foreach.sformsF.first || $keyword_search}hide{/if}">
			<form method="{$listing_types[$sf_key].Submit_method}" action="{$rlBase}{if $config.mod_rewrite}{$pages.$spage_key}/{$search_results_url}.html{else}?page={$pages.$spage_key}&amp;{$search_results_url}{/if}">
				<input type="hidden" name="action" value="search" />
				{assign var='post_form_key' value=$sf_key|cat:'_quick'}
				<input type="hidden" name="post_form_key" value="{$post_form_key}" />
				
				{foreach from=$search_form item='group' name='qsearchF'}
					{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fields_search.tpl' fields=$group.Fields}
				{/foreach}
				
				<div class="padding" style="padding-top: 15px;">
					<div class="field">
						{$lang.sort_listings_by}
					</div>
			
					<select name="f[sort_by]">
						<option value="0">{$lang.select}</option>
						{foreach from=$search_form item='item'}
							{assign var='field' value=$item.Fields.0}
							{if $field.Type != 'checkbox'}
								<option value="{$field.Key}" {if $smarty.post.sort_field == $field.Key}selected{/if}>{$lang[$field.pName]}</option>
							{/if}
						{/foreach}
					</select>
					
					<select name="f[sort_type]">
						<option value="asc">{$lang.ascending}</option>
						<option value="desc" {if $smarty.post.sort_type == 'desc'}selected{/if}>{$lang.descending}</option>
					</select>
				</div>
				
				<div class="padding" style="padding-top: 10px;">
					<input class="button" type="submit" name="search" value="{$lang.search}" />
					<label><input style="margin-{$text_dir}: 20px;" type="checkbox" name="f[with_photo]" value="true" /> {$lang.with_photos_only}</label>
					
					{if $listing_types[$sf_key].Advanced_search}
						<div style="padding-top: 8px;"><a class="static" title="{$lang.advanced_search}" href="{$rlBase}{if $config.mod_rewrite}{$pages.$spage_key}/{$advanced_search_url}.html{else}?page={$pages.$spage_key}&amp;{$advanced_search_url}{/if}">{$lang.advanced_search}</a></div>
					{/if}
				</div>
			</form>
		</div>
	{/foreach}
	
{/if}
<!-- print search form -->

<!-- keyword search tab -->
<div id="keyword_tab" class="{if !$keyword_search}hide{/if}">
	{if $keyword_search}<script type="text/javascript">active_tab = 'keyword';</script>{/if}
	<form method="post" action="{$rlBase}{if $config.mod_rewrite}{$pages.search}.html{else}?page={$pages.search}{/if}">
		<input type="hidden" name="form" value="keyword_search" />
	
		<div id="qucik_search">
			<table class="sTable">
			<tr>
				<td class="left"></td>
				<td class="center"><input type="text" name="f[keyword_search]"  maxlength="255" {if $smarty.post.f.keyword_search}value="{$smarty.post.f.keyword_search}"{/if} /></td>
				<td class="right"><input type="submit" name="search" value="" /></td>
			</tr>
			</table>
		</div>
	
		<div class="keyword_search_opt" style="display: block;">
			<div>
				{assign var='tmp' value=3}
				{section name='keyword_opts' loop=$tmp max=3}
					<label><input {if $fVal.keyword_search_type || $keyword_mode}{if $smarty.section.keyword_opts.iteration == $fVal.keyword_search_type || $keyword_mode == $smarty.section.keyword_opts.iteration}checked="checked"{/if}{else}{if $smarty.section.keyword_opts.iteration == 2}checked="checked"{/if}{/if} value="{$smarty.section.keyword_opts.iteration}" type="radio" name="f[keyword_search_type]" /> {assign var='ph' value='keyword_search_opt'|cat:$smarty.section.keyword_opts.iteration}{$lang.$ph}</label>
				{/section}
			</div>
		</div>
	</form>

	{if !empty($listings)}
		
		{if $sorting}
			<div class="sorting">
				<span class="caption">{if $mode == 'account'}{$lang.sort_accounts_by}{else}{$lang.sort_listings_by}{/if}:</span>
				{foreach from=$sorting item='field_item' key='sort_key' name='fSorting'}
					<a {if $sort_by == $sort_key}class="active {if $sort_type == 'asc' || empty($sort_type)}asc{else}desc{/if}"{/if} title="{$lang.sort_listings_by} {$field_item.name}" href="{if $config.mod_rewrite}?{else}{$smarty.const.RL_URL_HOME}index.php?page={$pages.browse}&amp;category={$smarty.get.category}&amp;{/if}sort_by={$sort_key}{if $sort_by == $sort_key}&amp;sort_type={if $sort_type == 'asc' || !isset($sort_type)}desc{elseif !empty($sort_key) && empty($sort_type)}desc{else}asc{/if}{/if}">{$field_item.name}</a>
					{if !$smarty.foreach.fSorting.last}<span class="divider">|</span>{/if}
				{/foreach}
				
				{rlHook name='mobileKeywordsAfterSorting'}
			</div>
		{/if}
	
		<!-- listings -->
		<div id="listings">
			{if !empty($listings)}
				<ul>
					{foreach from=$listings item='listing' key='key' name='listingsF'}
						{include file='blocks'|cat:$smarty.const.RL_DS|cat:'listing.tpl'}
					{/foreach}
				</ul>
			{/if}
		</div>
		<!-- listings end -->
		
		<!-- paging block -->
		{if $config.mod_rewrite}
			{paging calc=$pInfo.calc total=$listings|@count current=$pInfo.current per_page=$config.listings_per_page url=$category.Path var='listing'}
		{else}
			{paging calc=$pInfo.calc total=$listings|@count current=$pInfo.current per_page=$config.listings_per_page url=$category.ID var='category'}
		{/if}
		<!-- paging block end -->
	
	{else}
	
		{if $keyword_search}
			<div class="padding">{$lang.no_listings_found_deny_posting}</div>
		{/if}
		
	{/if}
</div>

<!-- keyword search tab end -->

<script type="text/javascript">//<![CDATA[
{literal}

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
	
	if ( flynax.getHash() )
	{
		$('table.tabs td[abbr='+flynax.getHash()+']').trigger('click');
	}
});

{/literal}

//]]>
</script>

<!-- search tpl end -->