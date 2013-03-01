<!-- statistics block -->

{if $statistics_block}
	{foreach from=$statistics_block item='stat_item' key='lt_key'}
		<table class="stats">
		<tr>
			<td class="stat-caption">{$listing_types.$lt_key.name}</td>
			{if $listing_types.$lt_key.Arrange_field}
				{foreach from=','|explode:$listing_types.$lt_key.Arrange_values item='s_column' name='scolsF'}
				{assign var='column' value='stats+name+'|cat:$lt_key|cat:'_column'|cat:$s_column}
				<td class="column">{$lang.$column}</td>
				{/foreach}
			{/if}
		</tr>
		<tr>
			<td class="dotted"><a class="block_bg" href="{$rlBase}{if $config.mod_rewrite}{$pages.listings}.html#{$lt_key}{else}?page={$pages.listings}#{$lt_key}{/if}">{$lang.total}</a></td>
			{if $listing_types.$lt_key.Arrange_field}
				{foreach from=','|explode:$listing_types.$lt_key.Arrange_values item='s_column' name='scolsF'}
					<td class="counter">{$stat_item.total.$s_column}</td>
				{/foreach}
			{else}
				<td class="counter">{$stat_item.total}</td>
			{/if}
		</tr>
		{if ($stat_item.today && !$listing_types.$lt_key.Arrange_field) || ($listing_types.$lt_key.Arrange_field && $stat_item.today.total)}
		<tr>
			<td class="dotted"><a class="block_bg" href="{$rlBase}{if $config.mod_rewrite}{$pages.listings}.html#{$lt_key}{else}?page={$pages.listings}#{$lt_key}{/if}">{$lang.today}</a></td>
			{if $listing_types.$lt_key.Arrange_field}
				{foreach from=','|explode:$listing_types.$lt_key.Arrange_values item='s_column' name='scolsF'}
					<td class="counter">{$stat_item.today.$s_column}</td>
				{/foreach}
			{else}
				<td class="counter">{$stat_item.today}</td>
			{/if}
		</tr>
		{/if}
		</table>
	{/foreach}
{else}
	{$lang.statistics_isnot_available}
{/if}

<!-- statistics block -->