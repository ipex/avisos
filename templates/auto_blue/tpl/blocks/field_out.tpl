<!-- field output tpl -->

<tr id="df_field_{$item.Key}">
	<td class="name">{$item.name}:</td>
	<td class="value {if $smarty.foreach.fListings.first}first{/if}">
		{if $item.Type == 'checkbox' && $item.Opt1}
			{if $item.Opt2}
				{assign var='col_num' value=$item.Opt2}
			{else}
				{assign var='col_num' value=3}
			{/if}
			<table class="checkboxes{if $col_num > 2} fixed{/if}">
			<tr>
			{foreach from=$item.Values item='tile' name='checkboxF'}
				<td>
					{if !empty($item.Condition)}
						{assign var="tit_source" value=$tile.Key}
					{else}
						{assign var="tit_source" value=$tile.ID}
					{/if}
					<div title="{$lang[$tile.pName]}" class="checkbox{if $tit_source|in_array:$item.source}_active{/if}">
					{if $tit_source|in_array:$item.source}<img src="{$rlTplBase}img/blank.gif" alt="" />{/if}
					{$lang[$tile.pName]}
					</div>
				</td>
				{if $smarty.foreach.checkboxF.iteration%$col_num == 0 && !$smarty.foreach.checkboxF.last}
				</tr>
				<tr>
				{/if}
			{/foreach}
			</tr>
			</table>
		{else}
			{$item.value}
		{/if}
	</td>
</tr>

<!-- field output tpl end -->