<ul class="child{if !$expand} hide{/if}">
	{if $subchilds}
		{assign var="mfgLoopName" value="geoChilds"|cat:$level|cat:"Loop"}
		{foreach from=$childs item="levelItem" name=$mfgLoopName}
		<li {if $level1Item.childs}class="expander"{/if}>
			{if $geo_filter_data.geo_url|cat:"/" == $levelItem.Path}
				<span class="list_item_selected">{$levelItem.name}</span>
			{else}
				<a href="{$geo_filter_data.clean_url|replace:"[geo_url]":$levelItem.Path}">{$levelItem.name}</a>
			{/if}

			{if $levelItem.childs}<span class="arrow"></span>{/if}
	
			{if $levelItem.childs}
				{assign var="in_url" value=$geo_filter_data.geo_url|strpos:$levelItem.Path}
				{if $in_url|is_numeric}{assign var="expand" value=true}{else}{assign var="expand" value=false}{/if}
				{include file=$smarty.const.RL_PLUGINS|cat:"multiField"|cat:$smarty.const.RL_DS|cat:"list_level.tpl" childs=$levelItem.childs item_id=$levelItem.ID level=2 subchilds=$levelItem.subchilds expand=$expand}
			{/if}
		</li>
		{/foreach}
	{else}
		<li>
			<table class="gf-table">
			<tr>
			{assign var='mfgLoopName' value="geoChilds"|cat:$level|cat:"Loop"}
			{foreach from=$childs item="levelItem" name=$mfgLoopName}
				<td>
					{if $geo_filter_data.geo_url|cat:"/" == $levelItem.Path}
						<span class="list_item_selected">{$levelItem.name}</span>
					{else}
						<a href="{$geo_filter_data.clean_url|replace:"[geo_url]":$levelItem.Path}">{$levelItem.name}</a>
					{/if}
				</td>
				{if $smarty.foreach.$mfgLoopName.iteration%$config.mf_geo_columns == 0 && !$smarty.foreach.$mfgLoopName.last}
					</tr>
					<tr>
				{/if}
			{/foreach}
			</tr>
			</table>
		</li>
	{/if}
</ul>
