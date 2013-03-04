{if !isset($smarty.get.txn_id)}
	<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}jquery/numeric.js"></script>
	<form action="{$rlBase}{if $config.mod_rewrite}{$pages.bank_wire_transfer}.html{else}?page={$pages.bank_wire_transfer}{/if}" method="post" autocomplete="off">
	<input type="hidden" name="type" value="wire_transfer" />
	<input type="hidden" name="txn_id" value="{$txn_id}" />
	<input type="hidden" name="item" value="{$item}" />
	<input type="hidden" name="form" value="submit" />
	<input type="hidden" name="item_id" value="{$item_id}" />
	<table class="sTable">
		<tr>
			<td width="180">
				{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='payment_info' name=$lang.bwt_account_info style='fg'}
					<table class="sTable">
						<tr>
							<td class="grey_small" style="padding:5px 10px; width:150px;">{$lang.bwt_bank_account_number}: <span class="red">*</span></td>
							<td><input type="text" name="bwt[bank_account_number]" autocomplete="off" value="{$smarty.post.bwt.bank_account_number}" class="text" size="22" style="text-align:left;" /></td>
						</tr>
						<tr>
							<td class="grey_small" style="padding:5px 10px;">{$lang.bwt_account_name}: <span class="red">*</span></td>
							<td><input type="text" name="bwt[account_name]" autocomplete="off" value="{$smarty.post.bwt.account_name}" class="text" style="text-align:left;" /></td>
						</tr>
						<tr>
							<td class="grey_small" style="padding:5px 10px;">{$lang.bwt_company_name}:</td>
							<td><input type="text" name="bwt[company_name]" autocomplete="off" value="{$smarty.post.bwt.company_name}" class="text" style="text-align:left;"/></td>
						</tr>
						<tr>
							<td class="grey_small" style="padding:5px 10px;">{$lang.bwt_counry}:</td>
							<td><select name="bwt[country]" style="width:202px;">
									<option {if !$smarty.post.bwt.country}selected="selected"{/if}value="">{$lang.select}</option>
									{foreach from=$bwt_country item='bwt_countries'}
										<option value="{$bwt_countries.name}" {if $smarty.post.bwt.account_country == $bwt_countries.name}selected="selected"{/if}>{$bwt_countries.name}</option>
									{/foreach}
								</select>
							</td>
						</tr>
						<tr>
							<td class="grey_small" style="padding:5px 10px;">{$lang.bwt_state}:</td>
							<td><input type="text" name="bwt[state]" autocomplete="off" value="{$smarty.post.bwt.state}" class="text" style="text-align:left;"/></td>
						</tr>
						<tr>
							<td class="grey_small" style="padding:5px 10px;">{$lang.bwt_city}:</td>
							<td>
								<input type="text" name="bwt[city]" autocomplete="off" value="{$smarty.post.bwt.city}" class="text" style="text-align:left; width: 100px;" />
								&nbsp;&nbsp;{$lang.bwt_zip}: <input type="text" name="bwt[zip]" autocomplete="off" value="{$smarty.post.bwt.zip}" class="numeric" size="7" maxlength="7" style="text-align:left; width: 30px;"/>
							</td>
						</tr>
						<tr>
							<td class="grey_small" style="padding:5px 10px;">{$lang.bwt_address}:</td>
							<td><input type="text" name="bwt[address]" autocomplete="off" value="{$smarty.post.bwt.address}" class="text" style="text-align:left;"/></td>
						</tr>
					</table>
				{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}
			</td>
		</tr>
		<tr>
			<td>
				{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='bank_info' name=$lang.bwt_bank_info style='fg'}
					<table class="sTable">
						<tr>
							<td class="grey_small" style="padding:5px 10px; width:150px;">{$lang.bwt_bank_name}:</td>
							<td><input type="text" name="bwt[bank_name]" autocomplete="off" value="{$smarty.post.bwt.bank_name}" class="text"style="text-align:left;" /></td>
						</tr>
						<tr>
							<td class="grey_small" style="padding:5px 10px;">{$lang.bwt_counry}:</td>
							<td><select name="bwt[bank_country]" style="width:202px;">
									<option {if !$smarty.post.bwt.bank_country}selected="selected"{/if}value="">{$lang.select}</option>
									{foreach from=$bwt_country item='bwt_countries_bank'}
										<option value="{$bwt_countries_bank.name}" {if $smarty.post.bwt.bank_country == $bwt_countries_bank.name}selected="selected"{/if}>{$bwt_countries_bank.name}</option>
									{/foreach}
								</select>
							</td>
						</tr>
						<tr>
							<td class="grey_small" style="padding:5px 10px;">{$lang.bwt_state}:</td>
							<td><input type="text" name="bwt[bank_state]" autocomplete="off" value="{$smarty.post.bwt.bank_state}" class="text" style="text-align:left;"/></td>
						</tr>
						<tr>
							<td class="grey_small" style="padding:5px 10px;">{$lang.bwt_city}:</td>
							<td>
								<input type="text" name="bwt[bank_city]" autocomplete="off" value="{$smarty.post.bwt.bank_city}" class="text" style="text-align:left; width: 100px;" />
								&nbsp;&nbsp;{$lang.bwt_zip}: <input type="text" name="bwt[bank_zip]" autocomplete="off" value="{$smarty.post.bwt.bank_zip}" class="numeric" size="7" maxlength="7" style="text-align:left; width: 30px;"/>
							</td>
						</tr>
						<tr>
							<td class="grey_small" style="padding:5px 10px;">{$lang.bwt_bank_address}:</td>
							<td><input type="text" name="bwt[bank_address]" value="{$smarty.post.bwt.bank_address}" class="text" style="text-align:left;"/></td>
						</tr>
						<tr>
							<td class="grey_small" style="padding:5px 10px;">{$lang.bwt_bank_phone}:</td>
							<td><input type="text" name="bwt[bank_phone]" autocomplete="off" value="{$smarty.post.bwt.bank_phone}" class="text" style="text-align:left;"/></td>
						</tr>
					</table>
				{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}
			</td>
		</tr>
		<tr>
			<td>
				<div align="center">
					<input type="submit" name="submit" value="{$lang.bwt_pay}"/>
				</div>
			</td>
		</tr>
	</table>
	</form>

	<script type="text/javascript">
	{literal}
	$(document).ready(function(){
		$("input.numeric").numeric();
	});
	{/literal}
	</script>
{else}
	{include file=$smarty.const.RL_PLUGINS|cat:'bankWireTransfer'|cat:$smarty.const.RL_DS|cat:'bank_write_details.tpl' txn_info=$txn_info}
	{if $pageInfo.Controller != 'bwt_print'}
	 <table class="table">
		<tr>
			<td>
				<div align="center">
					<input type="submit" onclick="location.href='{$rlBase}{if $config.mod_rewrite}{$pages.my_listings}.html{else}?page={$pages.my_listings}{/if}'" value="{$lang.bwt_continue}"/>
				</div>
			</td>
		</tr>
	</table>
	{/if}
{/if}