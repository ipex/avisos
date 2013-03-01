<!-- listing type -->

{rlHook name='browseTop'}

<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}jquery/numeric.js"></script>

<!-- search results -->
{if $search_results}
	
	{if !empty($listings)}
	
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'grid_navbar.tpl'}

		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'grid.tpl' hl=true grid_photo=$listing_type.Photo}
		<script type="text/javascript">flynax.highlightSRGrid($('div.refine input[name="f\[keyword_search\]"]').val());</script>
		
		<!-- paging block -->
		{paging calc=$pInfo.calc total=$listings|@count current=$pInfo.current per_page=$config.listings_per_page url=$search_results_url method=$listing_type.Submit_method}
		<!-- paging block end -->
	
	{else}
		
		<div class="info">
			{if $listing_type.Admin_only}
				{$lang.no_listings_found_deny_posting}
			{else}
				{if $config.mod_rewrite}
					{assign var='href' value=$rlBase|cat:$pages.add_listing|cat:'.html'}
				{else}
					{assign var='href' value=$rlBase|cat:'?page='|cat:$pages.add_listing}
				{/if}
				
				{assign var='link' value='<a href="'|cat:$href|cat:'">$1</a>'}
				{$lang.no_listings_found|regex_replace:'/\[(.+)\]/':$link}
			{/if}
		</div>
		
	{/if}
	
	<script type="text/javascript">
	var save_search_notice = "{$lang.save_search_confirm}";
	flynax.saveSearch();
	flynax.multiCatsHandler();
	</script>

<!-- search results end -->
{else}
<!-- browse/search forms mode -->

	{if $advanced_search}
	
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'advanced_search.tpl'}

	{else}
	
		{if !empty($category.des)}
			<div class="category_description">
				{$category.des}
			</div>
		{/if}
	
		{if $listing_type.Cat_position == 'top'}
			{include file='blocks'|cat:$smarty.const.RL_DS|cat:'categories.tpl' no_margin=false}
		{/if}
		
		{if ($search_form || ($listing_type.Random_featured && $random_featured)) && !$category.ID}
			<table class="type_top_content" {if !$search_form}style="width: auto;"{/if}>
			<tr>
			<!-- print search form -->
			{if $search_form}
				<td valign="top">
					<div class="caption">{$lang.quick_search}</div>
					{if $tabs_search}
						<ul class="search_tabs">
							{foreach from=$tabs_search item='s_tab' name='stabF'}
								{if $search_form.$s_tab}
									{assign var='s_tab_phrase' value='search_forms+name+'|cat:$listing_type.Key|cat:'_tab'|cat:$s_tab}
									<li {if $smarty.foreach.stabF.first}class="first active"{/if}>
										{$lang.$s_tab_phrase}<span></span>
									</li>
								{/if}
							{/foreach}
						</ul>
						<script type="text/javascript">flynax.searchTabs();</script>
					{/if}
					<div class="highlight_dark{if $listing_type.Random_featured && $random_featured} sell_space{/if}{if $tabs_search} in_tabs side_block_search{/if}">	
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
												<label><input style="margin-{$text_dir}: 10px;" type="checkbox" name="f[with_photo]" value="true" /> {$lang.with_photos_only}</label>
											</td>
										</tr>
										{if $listing_type.Advanced_search && $listing_type.Advanced_search_availability}
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
								{if $listing_type.Advanced_search && $listing_type.Advanced_search_availability}
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
						flynax.multiCatsHandler();
						$("input.numeric").numeric();
					});
					
					{/literal}
					</script>
				</td>
			{/if}
			<!-- print search form -->
			
			<!-- print random featured listing(s) -->
			{if $listing_type.Random_featured && $random_featured}
				<td valign="top" class="random_featured">
					{include file='blocks'|cat:$smarty.const.RL_DS|cat:'random_featured.tpl'}
				</td>
			{/if}
			<!-- print random featured listing(s) end -->
			</tr>
			</table>
		{/if}
		
		{if $listing_type.Cat_position == 'bottom'}
			{include file='blocks'|cat:$smarty.const.RL_DS|cat:'categories.tpl' no_margin=true}
		{/if}
		
		{if $listing_type.Cat_position != hide}
		<script type="text/javascript">
		{literal}
		
		$(document).ready(function(){
			$('a.post_ad').append('{/literal}{$lang.add_listing}{literal}');
		});
		
		{/literal}
		</script>
		{/if}
		
		{if $category.ID}
			{if !empty($listings)}
			
				{include file='blocks'|cat:$smarty.const.RL_DS|cat:'grid_navbar.tpl'}

				{include file='blocks'|cat:$smarty.const.RL_DS|cat:'grid.tpl' grid_photo=$listing_type.Photo}
				
				<!-- paging block -->
				{if $config.mod_rewrite}
					{paging calc=$pInfo.calc total=$listings|@count current=$pInfo.current per_page=$config.listings_per_page url=$category.Path var='listing'}
				{else}
					{paging calc=$pInfo.calc total=$listings|@count current=$pInfo.current per_page=$config.listings_per_page url=$category.ID var='category'}
				{/if}	
				<!-- paging block end -->
			
			{else}
			
				{if $category.Lock}
					{assign var='br_count' value=$bread_crumbs|@count}
					{assign var='br_count' value=$br_count-2}
					
					{if $config.mod_rewrite}
						{assign var='lock_link' value=$rlBase|cat:$bread_crumbs.$br_count.path}
						{if $listing_type.Cat_postfix}
							{assign var='lock_link' value=$lock_link|cat:'.html'}
						{else}
							{assign var='lock_link' value=$lock_link|cat:'/'}
						{/if}
					{else}
						{assign var='lock_link' value=$rlBase|cat:'?page='|cat:$bread_crumbs.$br_count.path}
					{/if}
					
					{assign var='replace' value='<a title="'|cat:$lang.back_to_category|replace:'[name]':$bread_crumbs.$br_count.name|cat:'" href="'|cat:$lock_link|cat:'">'|cat:$lang.click_here|cat:'</a>'}
					<div class="info">{$lang.browse_category_locked|regex_replace:'/\[(.+)\]/':$replace}</div>
				{else}
					<div class="info">
						{if $listing_type.Admin_only}
							{$lang.no_listings_here_submit_deny}
						{else}
							{assign var='link' value='<a href="'|cat:$add_listing_href|cat:'">$1</a>'}
							{$lang.no_listings_here|regex_replace:'/\[(.+)\]/':$link}
						{/if}
					</div>
				{/if}
				
			{/if}
		{/if}
		
	{/if}
	
	<script type="text/javascript">$("input.numeric").numeric();</script>
	
{/if}
<!-- browse mode -->

{rlHook name='browseBottom'}

<!-- listing type end -->
