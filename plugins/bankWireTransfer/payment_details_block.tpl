{if !empty($payment_details)}
 	{if $pageInfo.Controller == 'payment_history'}
		<div class="name"><b>{$lang.bwt_payment_details}:</b></div>
		<div class="sLine"></div>
	{/if}

	{foreach from=$payment_details item='pd'}
		<div class="name">{$pd.name}</div>
		<div class="value">{$pd.description}</div>

		<div class="clear" style="height: 10px;"></div>
	{/foreach}
{else}
	<div class="static-content">{$lang.bwt_missing_payment_details}</div>
{/if}
