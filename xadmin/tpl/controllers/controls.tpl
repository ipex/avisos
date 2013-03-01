<!-- controls tpl -->

{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_start.tpl'}
<div style="padding: 10px;">
	<table class="lTable">
		<tr class="body">
			<td class="list_td_light">{$lang.recount_text}</td>
			<td style="width: 5px;" rowspan="100"></td>
			<td class="list_td_light" align="center" style="width: 200px;">
				<input id="listing_recount" type="button" onclick="xajax_recountListings('#listing_recount');$(this).val('{$lang.loading}');" value="{$lang.recount}" style="margin: 0;width: 100px;" />
			</td>
		</tr>
		<tr>
			<td style="height: 5px;" colspan="3"></td>
		</tr>
		
		<tr class="body">
			<td class="list_td">{$lang.rebuild_cat_levels}</td>
			<td align="center" class="list_td">
				<input id="cat_levels" type="button" onclick="xajax_rebuildCatLevels(true, '#cat_levels');$(this).val('{$lang.loading}');" value="{$lang.rebuild}" style="margin: 0;width: 100px;" />
			</td>
		</tr>
		<tr>
			<td style="height: 5px;" colspan="3"></td>
		</tr>
		
		<tr class="body">
			<td class="list_td_light">{$lang.reorder_fields_positions}</td>
			<td class="list_td_light" align="center">
				<input id="reorder_fields" type="button" onclick="xajax_reorderFields(true, '#reorder_fields');$(this).val('{$lang.loading}');" value="{$lang.reorder}" style="margin: 0;width: 100px;" />
			</td>
		</tr>
		<tr>
			<td style="height: 5px;" colspan="3"></td>
		</tr>
		
		{if $config.cache}
		<tr class="body">
			<td class="list_td">{$lang.update_cache}</td>
			<td class="list_td" align="center">
				<input id="update_cache" type="button" onclick="xajax_updateCache(true, '#update_cache');$(this).val('{$lang.loading}');" value="{$lang.update}" style="margin: 0;width: 100px;" />
			</td>
		</tr>
		<tr>
			<td style="height: 5px;" colspan="3"></td>
		</tr>
		{/if}
		
		{rlHook name='apTplControlsForm'}
		
	</table>
</div>
{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_end.tpl'}

<!-- controls tpl end -->