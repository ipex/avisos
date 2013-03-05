<!-- grid navigation bar -->

<table class="grid_navbar">
<tr>
	<td class="switcher">
		<div class="table"><div {if $smarty.cookies.grid_mode == 'table' || !isset($smarty.cookies.grid_mode)}class="active"{/if}></div></div>
		<div class="list"><div {if $smarty.cookies.grid_mode == 'list'}class="active"{/if}></div></div>
	</td>
	{if $sorting}
	<td class="sorting">
		<span class="caption">{if $mode == 'account'}{$lang.sort_accounts_by}{else}{$lang.sort_listings_by}{/if}:</span>
		{foreach from=$sorting item='field_item' key='sort_key' name='fSorting'}
			<a rel="nofollow" {if $sort_by == $sort_key}class="active {if $sort_type == 'asc' || empty($sort_type)}asc{else}desc{/if}"{/if} title="{$lang.sort_listings_by} {$field_item.name}" href="{if $config.mod_rewrite}?{else}index.php?{$pageInfo.query_string}&amp;{/if}sort_by={$sort_key}{if $sort_by == $sort_key}&amp;sort_type={if $sort_type == 'asc' || !isset($sort_type)}desc{elseif !empty($sort_key) && empty($sort_type)}desc{else}asc{/if}{/if}">{$field_item.name}</a>
			{if !$smarty.foreach.fSorting.last}<span class="divider">|</span>{/if}
		{/foreach}
		
		{rlHook name='browseAfterSorting'}
	</td>
	{/if}
	<td class="custom">{rlHook name='browseGridNavBar'}</td>
</tr>
</table>

<!-- grid navigation bar end -->