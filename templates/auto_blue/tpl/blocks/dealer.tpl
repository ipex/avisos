<!-- account item -->

<div class="item">
	<table class="sTable">
	<tr>
		<td rowspan="2" class="photo" valign="top">
			<div>
				<a title="{$dealer.Full_name}" href="{$dealer.Personal_address}">
					<img {if empty($dealer.Photo)}class="no_style"{/if} alt="" src="{if empty($dealer.Photo)}{$rlTplBase}img/no-account.png{else}{$smarty.const.RL_URL_HOME}files/{$dealer.Photo}{/if}" />
				</a>
				{if !empty($dealer.Listings_count)}
					<div class="counter"><a title="{$lang.listings}" href="{$dealer.Personal_address}#listings">{$dealer.Listings_count}</a></div>
				{/if}
			</div>
		</td>
		<td class="fields" valign="top">
			<div>
				<table>
					<tr>
						<td {*colspan="2"*} class="value first"><a title="{$lang.visit_owner_page}" href="{$dealer.Personal_address}">{$dealer.Full_name}</a></td>
					</tr>
				{foreach from=$dealer.fields item='item' key='field' name='fListings'}
					{if !empty($item.value) && $item.Details_page}
					<tr id="al_field_{$dealer.ID}_{$item.Key}">
						{*<td class="name">{$lang[$item.pName]}:</td>*}
						<td class="value" title="{$lang[$item.pName]}">
							{$item.value}
						</td>
					</tr>
					{/if}
				{/foreach}
					
				{rlHook name='accountAfterFields'}
				</table>
			</div>
		</td>
	</tr>
	<tr>
		<td class="caption" valign="bottom">
			<span title="{$lang.join_date}" class="cat_caption">{$dealer.Date|date_format:$smarty.const.RL_DATE_FORMAT}</span>
		</td>
	</tr>
	</table>
	
	<table class="nav">
	<tr>
		<td valign="bottom">
			{rlHook name='accountAfterStats'}
		</td>
		<td valign="bottom" class="ralign">
			{rlHook name='accountNavIcons'}
		</td>
	</tr>
	</table>
</div>

<!-- account item end -->