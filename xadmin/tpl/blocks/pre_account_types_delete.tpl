<!-- pre delete DOM -->	

{assign var='type_name_phrase' value='account_types+name+'|cat:$key}
{assign var='replace_name' value='<b>'|cat:$lang.$type_name_phrase|cat:'</b>'}
{assign var='accounts_count' value=$accounts|@count}
{assign var='replace_count' value='<b>'|cat:$accounts_count|cat:'</b>'}
<div>{$lang.pre_account_type_delete_notice|replace:'[type]':$replace_name|replace:'[count]':$replace_count} <a class="static" href="javascript:void(0)" onclick="show('accounts_list')">{$lang.show_list}</a></div>
<div id="accounts_list" class="hide">
	<div style="padding: 5px">
	<table style="width: 70%;border-collapse: separate;border-spacing: 3px;">
	<tr>
	{foreach from=$accounts item='account' name='at_f'}
		<td style="background: #f5f5f5;"><div style="margin: 0 5px;padding: 3px 0;font-size: 12px;"><span class="label" style="font-size: 12px;cursor: default;">{$account.Username}</span>{if $account.First_name || $account.Last_name} ({$account.First_name} {$account.Last_name}){/if}</div></td>
		
		{if $smarty.foreach.at_f.iteration%4 == 0}
		</tr><tr>
		{/if}
	{/foreach}
	</tr>
	</table>
	</div>
</div>

<div style="margin: 3px 0 8px;">{$lang.choose_removal_method}</div>
<div style="margin: 5px 10px">
	<div style="padding: 2px 0;"><input type="radio" value="delete" class="del_action" name="del_action" id="delete_act" /> <label for="delete_act">{if $config.trash}{$lang.full_account_drop}{else}{$lang.full_account_delete}{/if}</label></div>
	<div style="padding: 2px 0;"><input type="radio" value="replace" class="del_action" name="del_action" id="replace_act" /> <label for="replace_act">{$lang.replace_another_account_type}</label></div>
	
	<input type="hidden" id="selected_method" value="0" />
	<div style="margin: 5px 0;">
		<div id="top_buttons">
			<input class="simple" type="button" value="{$lang.go}" onclick="delete_chooser($('.del_action:checked').val())" />
			<input class="simple" type="button" value="{$lang.cancel}" onclick="show('pre_delete_block');" />
		</div>
		
		<div id="alter_types" class="hide">
			<div style="margin: 10px;">
				<div style="padding: 0 0 5px;font-weight: bold;">{$lang.alter_account_types}</div>	
				{foreach from=$alter_account item='alter'}
					<div style="padding: 2px 0;"><input type="radio" name="alter" value="{$alter.Key}" class="alter" id="alter_{$alter.Key}" /> <label for="alter_{$alter.Key}" class="fLable">{$alter.name}</label></div>
				{/foreach}
				
				<div style="margin: 5px 0 0 0;">
					<input class="simple" type="button" value="{$lang.go}" onclick="alter_chose()" />
					<input class="simple" type="button" value="{$lang.cancel}" onclick="show('pre_delete_block');" />
				</div>
			</div>
		</div>
	</div>
</div>

<!-- pre delete DOM end -->