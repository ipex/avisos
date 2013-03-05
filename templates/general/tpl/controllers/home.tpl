<!-- home tpl -->

{rlHook name='homeTop'}

{if ($search_forms || ($listing_type.Random_featured && $random_featured))}
	<table class="type_top_content" {if !$search_forms}style="width: auto;"{/if}>
	<tr>
	<!-- print search form -->
	{if $search_forms}
		<td valign="top">
			<div class="caption">{$lang.quick_search}</div>
			
			{if $search_forms|@count > 1}
				<ul class="search_tabs">
					{assign var='z_index' value=20}
					{foreach from=$search_forms item='s_tab' name='stabF'}
						<li {if $smarty.foreach.stabF.first}class="first active"{/if} style="z-index: {$z_index}">
							<span class="left"></span>
							<span class="center">{$s_tab.name}</span>
							<span class="right"></span>
						</li>
						{assign var='z_index' value=$z_index-1}
					{/foreach}
				</ul>
				<script type="text/javascript">flynax.searchTabs();</script>
			{/if}
			
			<div class="highlight_dark{if $listing_type.Random_featured && $random_featured} sell_space{/if}{if $search_forms|@count > 1} in_tabs{/if}">
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
						
						{assign var='items_count' value=$search_form.data|@count}
						{foreach from=$search_form.data item='group' name='qsearchF'}
							{if $smarty.foreach.qsearchF.iteration <= 5}
								{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fields_search.tpl' fields=$group.Fields}
							{/if}
						{/foreach}
						
						<table class="search">
						<tr>
							{if $config.search_fields_position == 2}<td class="field"></td>{/if}
							<td class="value" colspan="2"><label><input type="checkbox" name="f[with_photo]" value="true" /> {$lang.with_photos_only}</label></td>
						</tr>
						<tr>
							{if $config.search_fields_position == 2}<td class="field"></td>{/if}
							<td class="lalign"><input type="submit" name="search" value="{$lang.search}" /></td>
							{if $listing_types[$search_form.listing_type].Advanced_search}
								<td><a class="white_11" title="{$lang.advanced_search}" href="{$rlBase}{if $config.mod_rewrite}{$pages.$spage_key}/{$advanced_search_url}.html{else}?page={$pages.$spage_key}&amp;{$advanced_search_url}{/if}">{$lang.advanced_search}</a></td>
							{/if}
						</tr>
						</table>
						
					</form>
					{if $search_forms|@count > 1}
						</div>
					{/if}
				{/foreach}
			</div>
			
			<script type="text/javascript">
				flynax.multiCatsHandler();
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

{rlHook name='homeBottomTpl'}
	
<!-- home tpl end -->