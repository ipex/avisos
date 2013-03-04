<!-- payAsYouGoCredits plugin -->

{if $smarty.get.nvar_1 != 'purchase'}
	<div class="highlight">
		<table class="table">
			<tr>
				<td class="name">
					{$lang.paygc_account_credits}:
				</td>
				<td class="value">
					<b>{$account_info_tmp.Total_credits}</b> {$lang.paygc_credits_count}
				</td>
			</tr>
			{if $account_info_tmp.Total_credits > 0 && $config.paygc_period > 0}
			<tr>
				<td class="name">
					{$lang.paygc_expiration_date}:
				</td>
				<td class="value">
					{$credits_expration_data|date_format:$smarty.const.RL_DATE_FORMAT}
				</td>
			</tr>
			{/if}
			<tr>
				<td></td>
				<td>
					<div style="padding: 10px 0px 5px;">
						<input type="button" class="button" value="{$lang.paygc_buy_credits}" onclick="location.href='{$rlBase}{if $config.mod_rewrite}{$pages.my_credits}/purchase.html{else}?page={$pages.my_credits}&amp;nvar_1=purchase{/if}'" />
					</div>
					<a href="{$rlBase}{if $config.mod_rewrite}{$pages.payment_history}.html?credits{else}?page={$pages.payment_history}&amp;credits{/if}">{$lang.paygc_view_history}</a>
				</td>
			</tr>
		</table>
	</div>
{else}
	<div class="highlight">
		{if !empty($credits)}
			{if $lang.paygc_desc}
				{assign var='replace' value=`$smarty.ldelim`number`$smarty.rdelim`}
				<div class="dark" style="padding-bottom: 15px;">{$lang.paygc_desc|replace:$replace:$config.paygc_period}</div>
			{/if}
			
			<form method="post" action="{$rlBase}{if $config.mod_rewrite}{$pages.my_credits}/purchase.html{else}?page={$pages.my_credits}&amp;nvar_1=purchase{/if}">
				<input type="hidden" name="submit" value="true" />

				{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='credit_list' name=$lang.paygc_give_youself_credits}
	            <div id="credits">
					<table class="table">
						<tr>
							{foreach from=$credits item='item' key='key' name='creditsF'}
								<td align="center">
									<div class="credit_item" id="credit_item_{$item.ID}">
										<div class="dark number">{$item.Credits}</div>
										<div class="credits">{$lang.paygc_credits_count}</div>
										<div class="dark price">
											{if $config.system_currency_position == 'before'}
												{$config.system_currency}
											{/if}
											{$item.Price}
											{if $config.system_currency_position == 'after'}
												{$config.system_currency}
											{/if}
										</div>
										<div class="price_one dark_12">({$config.system_currency}{$item.Price_one}/{$lang.paygc_credits_count|replace:'s':''} )</div>
										<input type="radio" id="credit_item_value_{$item.ID}" accesskey="price_{$item.Price}" name="credits" value="{$item.ID}" />
									</div>
								</td>
								{if $smarty.foreach.creditsF.iteration%4 == 0 && !$smarty.foreach.creditsF.last}
									</tr><tr>
								{else}
									{if !$smarty.foreach.creditsF.last}<td class="divider" width="10"></td>{/if}
								{/if}
							{/foreach}
							{if $smarty.foreach.creditsF.total == 1}
								<td class="divider"></td>
								<td></td>
							{/if}
						</tr>
					</table>
				</div>
				{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}

				<div class="clear" style="height: 15px;"></div>

				<!-- select a payment gateway -->
				{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='gateways' name=$lang.payment_gateways}
					<ul id="payment_gateways">
						{if $config.use_paypal}
						<li>
							<img alt="" src="{$smarty.const.RL_LIBS_URL}payment/paypal/paypal.png" />
							<p><input {if $smarty.post.gateway == 'paypal' || !$smarty.post.gateway}checked="checked"{/if} type="radio" name="gateway" value="paypal" /></p>
						</li>
						{/if}
						{if $config.use_2co}
						<li>
							<img alt="" src="{$smarty.const.RL_LIBS_URL}payment/2co/2co.png" />
							<p><input {if $smarty.post.gateway == '2co'}checked="checked"{/if} type="radio" name="gateway" value="2co" /></p>
						</li>
						{/if}
						
						{rlHook name='paymentGateway'}
					</ul>

					<script type="text/javascript">
						flynax.paymentGateway();
					</script>

				{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}
				<!-- select a payment gateway end -->

				<input type="submit" value="{$lang.checkout}" />
			</form>
		{else}
			<div class="info">{$lang.paygc_no_packages}</div>
		{/if}
	</div>
	
	<script type="text/javascript">
		{literal}
			$(document).ready(function() {
            	$('#credits div.credit_item').click(function()
				{
					var item_id = $(this).attr('id').split('_')[2];
					$( '#credit_item_value_' + item_id ).attr('checked', true);
					
					$('#credits div.credit_item').each(function()
					{     
						var item_id_tmp = $(this).attr('id').split('_')[2];

						if(item_id == item_id_tmp)	
						{
						 	$( $(this) ).addClass('active');	
						}
						else
						{
							$( $(this) ).removeClass('active');	
						}
					});
				})

			});
		{/literal}
	</script>
{/if}

<!-- end payAsYouGoCredits plugin -->