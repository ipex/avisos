{if !empty($txn_info)}
 <table class="table">
	<tr>
		<td>
			{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='payment_info' name=$lang.bwt_account_info style='fg'}
				<table class="sTable">
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
				<table class="sTable">
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