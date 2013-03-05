<!-- payment history tpl -->

{if $transactions}
	<div class="highlight">		
		<table class="list" id="saved_search">
		<tr class="header">
			<td align="center" class="no_padding" style="width: 15px;">#</td>
			<td class="divider"></td>
			<td>{$lang.item}</td>
			<td class="divider"></td>
			<td style="width: 50px;"><div title="{$lang.amount}" class="text-overflow">{$lang.amount}</div></td>
			<td class="divider"></td>
			<td style="width: 80px;"><div title="{$lang.payment_gateway}" class="text-overflow">{$lang.payment_gateway}</div></td>
			<td class="divider"></td>
			<td style="width: 90px;"><div title="{$lang.txn_id}" class="text-overflow">{$lang.txn_id}</div></td>
			<td class="divider"></td>
			<td style="width: 70px;">{$lang.date}</td>
		</tr>
		{foreach from=$transactions item='item' name='transactionF'}
		<tr class="body" id="item_{$item.ID}">
			<td class="no_padding" align="center"><span class="text">{$smarty.foreach.transactionF.iteration}</span></td>
			<td class="divider"></td>
			<td>
				<span class="text">{$item.plan_info}</span>
				<div>
					{if $item.item_info}
						<a href="{$item.link}">{$item.item_info}</a>
					{else}
						<span class="red">{$lang.item_not_available}</span>
					{/if}
				</div>
			</td>
			<td class="divider"></td>
			<td>{if $config.system_currency_position == 'before'}{$config.system_currency}{/if} {$item.Total} {if $config.system_currency_position == 'after'}{$config.system_currency}{/if}</td>
			<td class="divider"></td>
			<td><span class="text">{$item.Gateway}</span></td>
			<td class="divider"></td>
			<td><span class="text">{$item.Txn_ID}</span></td>
			<td class="divider"></td>
			<td><span class="text">{$item.Date|date_format:$smarty.const.RL_DATE_FORMAT}</span></td>
		</tr>
		{/foreach}
		</table>
		
		{paging calc=$pInfo.calc total=$transactions|@count current=$pInfo.current per_page=$config.transactions_per_page}
	</div>
{else}
	<div class="info">{$lang.no_account_transactions}</div>
{/if}
<!-- payment history tpl end -->