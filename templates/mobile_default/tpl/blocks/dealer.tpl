<!-- dealer block -->

<li>
	<table class="sTable">
	<tr>
		<td style="width: 110px;" valign="top" class="image">			
			<div class="img">
				<a title="{$dealer.Full_name}" href="{$dealer.Personal_address}">
					<img style="width: 104px;" alt="" src="{if $dealer.Photo}{$smarty.const.RL_URL_HOME}files/{$dealer.Photo}{else}{$rlTplBase}img/account.gif{/if}" />
				</a>
			</div>
			{if !empty($dealer.Listings_count)}
			<table class="fixed" style="width: 110px;">
			<tr>
				<td style="width: 20px;">
					<div class="photos_icon icon" style="margin: 3px 0 0;">
						<a title="{$lang.listings}" href="{$dealer.Personal_address}#listings">{$dealer.Listings_count}</a>
					</div>
				</td>
				<td style="padding: 2px 0 0 5px;">
					<div class="overflow">
						<a class="dark_gray_11" title="{$lang.listings}" href="{$dealer.Personal_address}#listings">{$lang.account_listings}</a>
					</div>
				</td>
			</tr>
			</table>
			{/if}
		</td>
		<td valign="top">
			<div class="fields">
				<a title="{$dealer.First_name} {$dealer.Last_name}" href="{$dealer.Personal_address}">
					<b>{if !empty($dealer.First_name) || !empty($dealer.Last_name)}{$dealer.First_name} {$dealer.Last_name}{else}{$dealer.Username}{/if}</b>
				</a>
			
				{*$lang.join_date}: {$dealer.Date|date_format:$smarty.const.RL_DATE_FORMAT*}
				
				<table style="margin: 5px 0 0 0;">
				{foreach from=$dealer.fields item='item' key='field' name='fDealers'}
				{if !empty($item.value)}
					<tr>
						<td><span class="field">{$lang[$item.pName]}:</span></td>
						<td><span class="value">{$item.value}</span></td>
					</tr>
				{/if}
				{/foreach}
				</table>
				
			</div>
		</td>
	</tr>
	</table>
</li>

{if $smarty.foreach.dealersF.last}
	<li class="last"></li>
{/if}

<!-- dealer block end -->