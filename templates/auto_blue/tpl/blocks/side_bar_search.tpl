<!-- side bar search form -->

{if $search_forms && $pageInfo.Key == 'home'}

	<div class="side_block_search">
		{if $search_forms|@count > 1}
			<!-- tabs -->
			<ul class="search_tabs">
				{foreach from=$search_forms item='search_form' key='sf_key' name='stabsF'}{assign var='zindex' value=20}<li class="{if $smarty.foreach.stabsF.first}first active{elseif $smarty.foreach.stabsF.last}last{/if}">{$search_form.name}<span style="z-index: {$zindex};"></span></li>{assign var='zindex' value=$zindex-1}{/foreach}
			</ul>
			<!-- tabs end -->
		{/if}
		
		{php}global $config; $config['search_fields_position'] = 1;{/php}
		{assign var='items_count' value=10}
	
		<div class="content">
			{foreach from=$search_forms item='search_form' key='sf_key' name='sformsF'}	
				{assign var='spage_key' value=$listing_types[$search_form.listing_type].Page_key}
				{assign var='post_form_key' value=$sf_key}
				{if $search_forms|@count > 1}
					<div class="search_tab_area{if !$smarty.foreach.sformsF.first} hide{/if}">
				{/if}
				<form method="{$listing_types[$search_form.listing_type].Submit_method}" action="{$rlBase}{if $config.mod_rewrite}{$pages.$spage_key}/{$search_results_url}.html{else}?page={$pages.$spage_key}&amp;{$search_results_url}{/if}">
					<input type="hidden" name="action" value="search" />
					<input type="hidden" name="post_form_key" value="{$sf_key}" />
					
					{if $search_form.arrange_field}
						<input {if !$smarty.foreach.sformsF.first}disabled="disabled"{/if} class="search_tab_hidden" type="hidden" name="f[{$search_form.arrange_field}]" value="{$search_form.arrange_value}" />
					{/if}
					
					{foreach from=$search_form.data item='group' name='qsearchF'}
						{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fields_search.tpl' fields=$group.Fields}
					{/foreach}
					
					<div class="search vmargin"><label><input type="checkbox" name="f[with_photo]" value="true" /> {$lang.with_photos_only}</label></div>
					
					<input type="submit" name="search" value="{$lang.search}" />
					{if $listing_types[$search_form.listing_type].Advanced_search && $listing_types[$search_form.listing_type].Advanced_search_availability}
						<a class="white_11" title="{$lang.advanced_search}" href="{$rlBase}{if $config.mod_rewrite}{$pages.$spage_key}/{$advanced_search_url}.html{else}?page={$pages.$spage_key}&amp;{$advanced_search_url}{/if}">{$lang.advanced_search}</a>
					{/if}
					
				</form>
				{if $search_forms|@count > 1}
					</div>
				{/if}
			{/foreach}	
		</div>
	</div>
		
	<script type="text/javascript">
		{if $search_forms|@count > 1}
			flynax.searchTabs();
		{/if}
		flynax.multiCatsHandler();
	</script>
	
{*elseif ($search_form || ($listing_type.Random_featured && $random_featured)) && (!$category.ID || $single_category_mode) && $pageInfo.Controller == 'listing_type'}

	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'side_block_header.tpl' name=$lang.quick_search}
	
	{if $tabs_search}
		<!-- tabs -->
		{if $config.search_fields_position == 2}
			<table class="search">
			<tr class="header">
				<td class="field">{$lang.search}</td>
				<td class="value">
		{/if}
		
		<ul class="search_tabs">
		{foreach from=$tabs_search item='tab_search' key='sf_key' name='stabsF'}{assign var='s_tab_phrase' value='search_forms+name+'|cat:$listing_type.Key|cat:'_tab'|cat:$tab_search}<li class="{if $smarty.foreach.stabsF.first}first active{elseif $smarty.foreach.stabsF.last}last{/if}">{$lang.$s_tab_phrase}</li>{/foreach}
		</ul>
		
		{if $config.search_fields_position == 2}
				</td>
			</tr>
			</table>
		{/if}
		<!-- tabs end -->
	{/if}
	
	{if $tabs_search}
		{foreach from=$search_form item='s_form' name='intabF' key='ts_index'}
			<div class="search_tab_area{if !$smarty.foreach.intabF.first} hide{/if}">
				<form class="search_form" method="{$listing_type.Submit_method}" action="{$rlBase}{if $config.mod_rewrite}{$pageInfo.Path}/{$search_results_url}.html{else}?page={$pageInfo.Path}&amp;{$search_results_url}{/if}">
					<input type="hidden" name="action" value="search" />
					{assign var='post_form_key' value=$listing_type.Key|cat:'_tab'|cat:$ts_index}
					<input type="hidden" name="post_form_key" value="{$post_form_key}" />
					
					<input {if !$smarty.foreach.intabF.first}disabled="disabled"{/if} class="search_tab_hidden" type="hidden" name="f[{$listing_type.Arrange_field}]" value="{$s_form.0.Tab_value}" />
					{foreach from=$s_form item='group'}
						{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fields_search.tpl' fields=$group.Fields}
					{/foreach}
					
					<table class="search">
					<tr>
						{if $config.search_fields_position == 2}<td class="field"></td>{/if}
						<td class="value"><label><input type="checkbox" name="f[with_photo]" value="true" /> {$lang.with_photos_only}</label>
						</td>
					</tr>
					<tr>
						{if $config.search_fields_position == 2}<td class="field"></td>{/if}
						<td class="lalign value"><input type="submit" name="search" value="{$lang.search}" /></td>
						{if $listing_type.Advanced_search && $listing_type.Advanced_search_availability}
							<td><a class="white_11" title="{$lang.advanced_search}" href="{$rlBase}{if $config.mod_rewrite}{$pageInfo.Path}/{$advanced_search_url}.html{else}?page={$pageInfo.Path}&amp;{$advanced_search_url}{/if}">{$lang.advanced_search}</a></td>
						{/if}
					</tr>
					</table>
				</form>
			</div>
		{/foreach}
	{else}
		<form class="search_form" method="{$listing_type.Submit_method}" action="{$rlBase}{if $config.mod_rewrite}{$pageInfo.Path}/{$search_results_url}.html{else}?page={$pageInfo.Path}&amp;{$search_results_url}{/if}">
			<input type="hidden" name="action" value="search" />
			{assign var='post_form_key' value=$listing_type.Key|cat:'_quick'}
			<input type="hidden" name="post_form_key" value="{$post_form_key}" />
			
			{foreach from=$search_form item='group'}
				{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fields_search.tpl' fields=$group.Fields}
			{/foreach}
			
			<table class="search">
			<tr>
				{if $config.search_fields_position == 2}<td class="field"></td>{/if}
				<td class="value" colspan="2"><label><input type="checkbox" name="f[with_photo]" value="true" /> {$lang.with_photos_only}</label></td>
			</tr>
			<tr>
				{if $config.search_fields_position == 2}<td class="field"></td>{/if}
				<td class="lalign value"><input type="submit" name="search" value="{$lang.search}" /></td>
				{if $listing_type.Advanced_search && $listing_type.Advanced_search_availability}
					<td><a class="white_11" title="{$lang.advanced_search}" href="{$rlBase}{if $config.mod_rewrite}{$pageInfo.Path}/{$advanced_search_url}.html{else}?page={$pageInfo.Path}&amp;{$advanced_search_url}{/if}">{$lang.advanced_search}</a></td>
				{/if}
			</tr>
			</table>
		</form>
	{/if}
	
	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'side_block_footer.tpl'}
	
	<script type="text/javascript">
	{if $tabs_search|@count > 1}
		flynax.searchTabs();
	{/if}
	
	var phrase_from = "{$lang.from}";
	var phrase_to = "{$lang.to}";
	{literal}
	
	$(document).ready(function(){
		flynax.fromTo(phrase_from, phrase_to);
		flynax.multiCatsHandler();
		$("input.numeric").numeric();
	});
	
	{/literal}
	</script>
	*}
{/if}

<!-- side bar search form end -->