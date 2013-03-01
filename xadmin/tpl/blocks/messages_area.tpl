<!-- messages area DOM -->
	
	<table class="table">
	{foreach from=$messages item='message' name='messagesF'}
	<tr class="body{if $message.Hide} removed{/if}{if $message.To == $account_info.ID} hlight{/if}">
		<td style="width: 60px;padding-left: 0;" valign="top" align="center">
			<span title="{if $message.To == $account_info.ID}{$contact.Full_name}{else}{$lang.me}{/if}">
			{if $message.To != $account_info.ID}
				{if $account_info.Photo}
					<img alt="" style="width: 36px;height: auto;" class="thumbnail" src="{$smarty.const.RL_URL_HOME}files/{$account_info.Photo}" />
				{else}
					<img alt="" class="avatar40" src="{$rlTplBase}img/blank.gif" />
				{/if}
			{else}
				{if $contact.Photo}
					<img alt="" style="width: 36px;height: auto;" class="thumbnail" src="{$smarty.const.RL_URL_HOME}files/{$contact.Photo}" />
				{else}
					<img alt="" class="avatar40" src="{$rlTplBase}img/blank.gif" />
				{/if}
			{/if}
			</span>
			
			<div>
				{if $message.To != $account_info.ID}
					{$lang.me}
				{/if}
			</div>
		</td>
		<td class="divider"></td>
		<td valign="top" class="last">
			<table class="sTable">
			<tr>
				<td class="message_cell">
					<div class="message_content_lim">{$message.Message|nl2br|replace:'\n':'<br />'}</div>
					<div class="message_date">
						{$message.Date|date_format:$smarty.const.RL_DATE_FORMAT}
						{rlHook name='apTplMessagesAfterMessage'}
						
						{$message.Date|date_format:'%H:%M'}
						{if $message.Hide}
							<span style="padding: 0 10px;" class="red" title="{$lang.removed_by|replace:'[name]':$contact.Full_name}">{$lang.removed_by|replace:'[name]':$contact.Full_name}</span>
						{/if}
					</div>
				</td>
				<td class="ralign">
					<input {if $message.ID|array_search:$checked_ids !== false}checked="checked"{/if} type="checkbox" name="del_mess" class="del_mess" id="message_{$message.ID}" />
				</td>
			</tr>
			</table>	
		</td>
	</tr>
	{/foreach}
	</table>
	
<!-- messages area DOM end -->