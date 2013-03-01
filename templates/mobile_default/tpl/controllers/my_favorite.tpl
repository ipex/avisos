<!-- mobile favorites -->

{if !empty($listings)}

	<!-- sorting -->
	<form method="get" action="">
		<div class="sorting">
			<select name="sort_by" class="default">
				<option value="">{$lang.select}</option>
				{foreach from=$sorting item='field_item' key='sort_key' name='fSorting'}
					<option value="{$field_item.field}" {if $sort_by == $field_item.field}selected="selected"{/if}>{$field_item.phrase}</option>
				{/foreach}
			</select>
			<select name="sort_type" class="default">
				<option value="asc">{$lang.ascending}</option>
				<option value="desc" {if $smarty.get.sort_type == 'desc'}selected="selected"{/if}>{$lang.descending}</option>
			</select>
			<input class="default" type="submit" name="submit" value="{$lang.sort}" />
		</div>
	</form>
	<!-- sorting end -->
	
	{rlHook name='favouriteBeforeListings'}
	
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
	{paging calc=$pInfo.calc total=$listings current=$pInfo.current per_page=$config.listings_per_page controller=$pages.my_favorites}
	<!-- paging block end -->

{else}
	<div class="padding">{$lang.no_favorite}</div>
{/if}

<!-- mobile favorites end -->