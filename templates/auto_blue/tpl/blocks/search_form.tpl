<!-- home page search form tpl -->

{if $search_forms}
	<div class="content home_search">
		{if $search_forms|@count > 1}
			<!-- tabs -->
			{if $config.search_fields_position == 2}
				<table class="search">
				<tr class="header">
					<td class="field">{$lang.search}</td>
					<td class="value">
			{/if}
			
			<ul class="search_tabs {if $config.search_fields_position == 1} search_tabs_margin{/if}">
			{foreach from=$search_forms item='search_form' key='sf_key' name='stabsF'}<li class="{if $smarty.foreach.stabsF.first}first active{elseif $smarty.foreach.stabsF.last}last{/if}"><span class="center">{$search_form.name}</span></li>{/foreach}
			</ul>
			
			{if $config.search_fields_position == 2}
					</td>
				</tr>
				</table>
			{/if}
			<!-- tabs end -->
		{/if}
		
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
				
				{assign var='items_count' value=1}<!-- force currency dropdown move to next line -->
				{foreach from=$search_form.data item='group' name='qsearchF'}
					{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fields_search.tpl' fields=$group.Fields}
				{/foreach}
				
				<table class="search">
				<tr>
					{if $config.search_fields_position == 2}<td class="field"></td>{/if}
					<td class="value" colspan="2"><label><input type="checkbox" name="f[with_photo]" value="true" /> {$lang.with_photos_only}</label></td>
				</tr>
				</table>
				
				<table class="search" style="margin-top: 8px;">
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
	
	{if $search_forms|@count > 1}
		<script type="text/javascript">
			flynax.searchTabs();
		</script>
	{/if}
	<script type="text/javascript">
		flynax.multiCatsHandler();
	</script>
{/if}

<!-- home page search form tpl end -->