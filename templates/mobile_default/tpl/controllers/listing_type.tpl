<!-- listing type -->

{rlHook name='mobileTplBrowseTop'}

<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}jquery/numeric.js"></script>

<!-- search results -->
{if $search_results}

	<!-- search options -->
	<div style="padding: 0 10px 8px 10px;text-align: {$text_dir_rev};">
		{if $smarty.const.RL_LANG_DIR == 'rtl'}&rarr;{else}&larr;{/if} <a title="{$lang.modify_search_criterion}" href="{$rlBase}{if $config.mod_rewrite}{$pages.search}.html?modify#{$listing_type.Key}{else}?page={$pages.search}&amp;modify#{$listing_type.Key}{/if}">{$lang.modify_search_criterion}</a>
	</div>
	<!-- search options end -->

	{if !empty($listings)}
	
		<!-- listings -->
		<div id="listings">
			<ul>
				{foreach from=$listings item='listing' key='key' name='listingsF'}
					{include file='blocks'|cat:$smarty.const.RL_DS|cat:'listing.tpl'}
				{/foreach}
			</ul>
		</div>
		<!-- listings end -->
		
		<!-- paging block -->
		{paging calc=$pInfo.calc total=$listings|@count current=$pInfo.current per_page=$config.listings_per_page url=$search_results_url method=$listing_type.Submit_method}
		<!-- paging block end -->

	{else}
		
		<div class="padding">
			{$lang.no_listings_here_submit_deny}
		</div>
		
	{/if}

<!-- search results end -->
{else}
<!-- browse/search forms mode -->

	{if $advanced_search}
	
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'advanced_search.tpl'}

	{else}
	
		{*if !empty($category.des)}
			<div class="highlight" style="margin: 0 0 15px;">
				{$category.des}
			</div>
		{/if*}
	
		{if $listing_type.Cat_position == 'top'}
			{include file='blocks'|cat:$smarty.const.RL_DS|cat:'categories.tpl' no_margin=false}
		{/if}
		
		<!-- print search form -->
		{*if $search_form && !$category.ID}
			<div class="caption">{$lang.quick_search}</div>
			{if $tabs_search}
				<ul class="search_tabs">
					{assign var='z_index' value=20}
					{foreach from=$tabs_search item='s_tab' name='stabF'}
						{assign var='s_tab_phrase' value='search_forms+name+'|cat:$listing_type.Key|cat:'_tab'|cat:$s_tab}
						<li {if $smarty.foreach.stabF.first}class="first active"{/if} style="z-index: {$z_index}">
							<span class="left"></span>
							<span class="center">{$lang.$s_tab_phrase}</span>
							<span class="right"></span>
						</li>
						{assign var='z_index' value=$z_index-1}
					{/foreach}
				</ul>
				<script type="text/javascript">flynax.searchTabs();</script>
			{/if}
			<div class="highlight_dark{if $listing_type.Random_featured && $random_featured} sell_space{/if}{if $tabs_search} in_tabs{/if}">	
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
									<td class="value button">
										<input type="submit" name="search" value="{$lang.search}" />
										<label><input style="margin-{$text_dir}: 20px;" type="checkbox" name="f[with_photo]" value="true" /> {$lang.with_photos_only}</label>
									</td>
								</tr>
								{if $listing_type.Advanced_search}
								<tr>
									{if $config.search_fields_position == 2}<td class="field"></td>{/if}
									<td class="lalign"><a class="white_11" title="{$lang.advanced_search}" href="{$rlBase}{if $config.mod_rewrite}{$pageInfo.Path}/{$advanced_search_url}.html{else}?page={$pageInfo.Path}&amp;{$advanced_search_url}{/if}">{$lang.advanced_search}</a></td>
								</tr>
								{/if}
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
							<td class="value button">
								<input type="submit" name="search" value="{$lang.search}" />
								<label><input style="margin-{$text_dir}: 20px;" type="checkbox" name="f[with_photo]" value="true" /> {$lang.with_photos_only}</label>
							</td>
						</tr>
						{if $listing_type.Advanced_search}
						<tr>
							{if $config.search_fields_position == 2}<td class="field"></td>{/if}
							<td class="lalign"><a class="white_11" title="{$lang.advanced_search}" href="{$rlBase}{if $config.mod_rewrite}{$pageInfo.Path}/{$advanced_search_url}.html{else}?page={$pageInfo.Path}&amp;{$advanced_search_url}{/if}">{$lang.advanced_search}</a></td>
						</tr>
						{/if}
						</table>
					</form>
				{/if}
			</div>
			
			<script type="text/javascript">
			var phrase_from = "{$lang.from}";
			var phrase_to = "{$lang.to}";
			{literal}
			
			$(document).ready(function(){
				flynax.fromTo(phrase_from, phrase_to);
				$("input.numeric").numeric();
			});
			
			{/literal}
			</script>
		{/if*}
		<!-- print search form end -->
		
		{if $listing_type.Cat_position == 'bottom'}
			{include file='blocks'|cat:$smarty.const.RL_DS|cat:'categories.tpl' no_margin=true}
		{/if}
		
		{if $category.ID}
			{if !empty($listings)}
				
				<!-- sorting -->
				<form method="get" action="{if $config.mod_rewrite}?{else}?page={$pages.browse}&amp;category={$smarty.get.category}&amp;{/if}">
					<div class="sorting">
						<select name="sort_by" class="default w110">
							<option value="">{$lang.select}</option>
							{foreach from=$sorting item='field_item' key='sort_key' name='fSorting'}
								{if $field_item.Key != 'state'} 
									<option value="{$field_item.Key}" {if $sort_by == $field_item.Key}selected="selected"{/if}>{$field_item.name}</option>
								{/if}
							{/foreach}
						</select>
						<select name="sort_type" class="default w120">
							<option value="asc">{$lang.ascending}</option>
							<option value="desc" {if $smarty.get.sort_type == 'desc'}selected="selected"{/if}>{$lang.descending}</option>
						</select>
						<input class="default" type="submit" name="submit" value="{$lang.sort}" />
					</div>
				</form>
				<!-- sorting end -->
			               
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
			
				{if $category.ID}
					<div class="padding">
						{$lang.no_listings_here_submit_deny}
					</div>
				{/if}
				
			{/if}
		{/if}
		
	{/if}
	
	<script type="text/javascript">$("input.numeric").numeric();</script>
	
{/if}
<!-- browse mode -->

{rlHook name='mobileTplBrowseBottom'}

<!-- listing type end -->