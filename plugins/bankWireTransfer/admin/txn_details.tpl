<!-- Listing Information -->

{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_start.tpl'}

	{if $listing.fields}
		<fieldset class="light">
			<legend id="legend_search_settings" class="up" onclick="fieldset_action('search_settings');">{$lang.bwt_order_details}</legend>
			<table class="form">
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
		</fieldset>
	{/if}

	<fieldset class="light">
		<legend id="legend_search_settings" class="up" onclick="fieldset_action('search_settings');">{$lang.bwt_order_information}</legend>
		<table class="form">
			<tr>
				<td class="name" width="180">{$lang.bwt_txn_id}</td>
				<td class="value">{$txn_info.Txn_ID}</td>
			</tr>
			{if !empty($txn_info.Total)}
			<tr>
				<td class="name">{$lang.bwt_total}</td>
				<td class="value">{$txn_info.Total} {$config.bwt_currency_code}</td>
			</tr>
			{/if}
			<tr>
				<td class="name" width="180">{$lang.bwt_type}</td>
				<td class="value">{$lang[$txn_info.Type]}</td>
			</tr>
			<tr>
				<td class="name" width="180">{$lang.bwt_ip}</td>
				<td class="value">{$txn_info.IP}</td>
			</tr>
			<tr>
				<td class="name" width="180">{$lang.status}</td>
				<td class="value">{$lang[$txn_info.Status]}</td>
			</tr>
		</table>
	</fieldset>

	<fieldset class="light">
		<legend id="legend_search_settings" class="up" onclick="fieldset_action('search_settings');">{$lang.bwt_payment_details}</legend>
		<!-- Payment Information -->
		{if $txn_info.Type == 'by_check'}
			{if !empty($payment_details)}
				{if $pageInfo.Controller == 'payment_history'}
					<div class="name"><b>{$lang.bwt_payment_details}:</b></div>
				{/if}
				<div class="sLine"></div>
				{foreach from=$payment_details item='pd'}
					<div class="name"><b>{$pd.name}</b></div>
					<div class="value">{$pd.description}</div>

					<div class="clear" style="height: 10px;"></div>
				{/foreach}
			{else}
				<div class="static-content">{$lang.bwt_missing_payment_details}</div>
			{/if}
		{else}
			{if !empty($txn_info)}
				 <table class="sTable">
					<tr>
						<td>
							{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='payment_info' name=$lang.bwt_account_info style='fg'}
								<table class="form">
									<tr>
										<td class="name" width="180">{$lang.bwt_bank_account_number}</td>
										<td class="value">{$txn_info.Bank_account_number}</td>
									</tr>
									<tr>
										<td class="name">{$lang.bwt_account_name}</td>
										<td class="value">{$txn_info.Account_name}</td>
									</tr>
									{if !empty($txn_info.Company_name)}
										<tr><td class="name">{$lang.bwt_company_name}</td><td class="value">{$txn_info.Company_name}</td></tr>
									{/if}
									<tr>
										<td class="name">{$lang.bwt_counry}</td>
										<td class="value">{$txn_info.Country}</td>
									</tr>
									{if !empty($txn_info.State)}
										<tr><td class="name">{$lang.bwt_state}</td><td class="value">{$txn_info.State}</td></tr>
									{/if}
									{if !empty($txn_info.City)}
										<tr><td class="name">{$lang.bwt_city}</td><td class="value">{$txn_info.City}</td></tr>
									{/if}
									{if !empty($txn_info.Zip)}
										<tr><td class="name">{$lang.bwt_zip}</td><td class="value">{$txn_info.Zip}</td></tr>
									{/if}
									{if !empty($txn_info.Address)}
										<tr><td class="name">{$lang.bwt_address}</td><td class="value">{$txn_info.Address}</td></tr>
									{/if}
								</table>
							{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}
						</td>
					</tr>
					<tr>
						<td>
							{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='bank_info' name=$lang.bwt_bank_info style='fg'}
								<table class="form">
									<tr>
										<td class="name" width="180">{$lang.bwt_bank_name}</td>
										<td class="value">{$txn_info.Bank_name}</td>
									</tr>
									<tr>
										<td class="name">{$lang.bwt_counry}</td>
										<td class="value">{$txn_info.Bank_country}</td>
									</tr>
									{if !empty($txn_info.Bank_state)}
										<tr><td class="name">{$lang.bwt_state}</td><td class="value">{$txn_info.Bank_state}</td></tr>
									{/if}
									{if !empty($txn_info.Bank_city)}
										<tr><td class="name">{$lang.bwt_city}</td><td class="value">{$txn_info.Bank_city}	</td></tr>
									{/if}
									{if !empty($txn_info.Bank_zip)}
										<tr><td class="name">{$lang.bwt_zip}:</td><td class="value">{$txn_info.Bank_zip}</td></tr>
									{/if}
									{if !empty($txn_info.Bank_address)}
										<tr><td class="name">{$lang.bwt_bank_address}</td><td class="value">{$txn_info.Bank_address}</td></tr>
									{/if}
									{if !empty($txn_info.Bank_phone)}
										<tr><td class="name">{$lang.bwt_bank_phone}</td><td class="value">{$txn_info.Bank_phone}</td></tr>
									{/if}
								</table>
							{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}
						</td>
					</tr>
				</table>
			{/if}
		{/if}
		<!-- end Payment Information -->
	</fieldset>
{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_end.tpl'} 