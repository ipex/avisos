<!-- Listing Information -->
{if empty($errors)}
	{if $listing.fields}
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='listing_information' name=$lang.bwt_order_details tall=true}

		<table class="table">
			{foreach from=$listing.fields item='field' name='detailsF'}
			<tr>
				<td class="name" width="180">{$field.name}</td>
				<td class="value">
					{if $smarty.foreach.detailsF.first}
						<a target="_blank" href="{$rlBase}{if $config.mod_rewrite}{$pages[$listing_type.Page_key]}/{$listing.Category_path}/{str2path string=$listing.listing_title}-{$listing.ID}.html{else}?page={$pages[$listing_type.Page_key]}&amp;id={$listing.ID}{/if}">{$field.value}</a>
					{else}
						{$field.value}
					{/if}
				</td>
			</tr>
			{/foreach}
		</table>	
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}
	{/if}

	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='order_information' name=$lang.bwt_order_information tall=true}

	<table class="table">
		<tr>
			<td class="name" width="180">{$lang.bwt_txn_id}</td>
			<td class="value">{$txn_id}</td>
		</tr>
		{if !empty($txn_info.Total)}
		<tr>
			<td class="name">{$lang.bwt_total}</td>
			<td class="value">{$txn_info.Total} {$config.bwt_currency_code}</td>
		</tr>
		{/if}
	</table>
	
	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}

	<!-- Payment Information -->
	{if $config.bwt_type == 'by_check' || $bwt_type == 'by_check'}
		{include file=$smarty.const.RL_PLUGINS|cat:'bankWireTransfer'|cat:$smarty.const.RL_DS|cat:'type_by_check.tpl'}	
	{else}
		{include file=$smarty.const.RL_PLUGINS|cat:'bankWireTransfer'|cat:$smarty.const.RL_DS|cat:'type_wire_transfer.tpl'}	
	{/if}
{else}
	<div class="highlight"><b>{$lang.bwt_session_finished}</b></div>
{/if}  