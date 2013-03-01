<!-- upgrade listing plan -->

{if isset($smarty.get.completed)}

	<span class="info">
		{assign var='replace' value='<a href="'|cat:$link|cat:'">$1</a>'}
		{$lang.notice_payment_listing_completed|regex_replace:'/\[(.*)\]/':$replace}
	</span>

{else}
	
	{rlHook name='upgradeListingTop'}
	
	<div class="highlight">
		<form method="post" action="{$rlBase}{if $config.mod_rewrite}{$pageInfo.Path}{if $featured}/featured{/if}.html?id={$smarty.get.id}{else}?page={$pageInfo.Path}&amp;id={$smarty.get.id}{if $featured}&amp;featured{/if}{/if}">
			<input type="hidden" name="upgrade" value="true" />
			<input type="hidden" name="from_post" value="1" />
			
			<!-- select a plan -->
			{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='plans' name=$lang.select_plan tall=true}
			
			<table class="plans">
			{foreach from=$plans item='plan' name='plansF'}
			<tr {if $plan.ID == $smarty.post.plan}class="active"{/if}>
				{assign var='item_disabled' value=false}
				{if $plan.Limit > 0 && $plan.Using == 0 && $plan.Using != ''}
				{assign var='item_disabled' value=true}
				{/if}
			
				<td class="radio"><input {if $item_disabled}disabled="disabled"{/if} id="plan_{$plan.ID}" type="radio" name="plan" value="{$plan.ID}" {if $plan.ID == $smarty.post.plan}checked="checked"{/if} /></td>
				<td class="label">
					<table class="bg{if $plan.Limit > 0 && $plan.Using == 0 && $plan.Using != ''} o60{/if}">
					<tr>
						<td class="left" {if $plan.Color}style="background-color: #{$plan.Color};"{/if}></td>
						<td class="center" {if $plan.Color}style="background-color: #{$plan.Color};"{/if}>
							<div class="price">{if isset($plan.Listings_remains)}&rarr;{else}{if $plan.Price > 0}{if $config.system_currency_position == 'before'}{$config.system_currency}{/if}{$plan.Price}{if $config.system_currency_position == 'after'}{$config.system_currency}{/if}{else}{$lang.free}{/if}{/if}</div>
							<div class="type">{assign var='l_type' value=$plan.Type|cat:'_plan_short'}{$lang.$l_type}</div>
						</td>
						<td class="right">
							<div class="relative">
								<div {if $plan.Color}style="background-color: #{$plan.Color};"{/if}>
									{if $plan.Color}<div class="tile" style="background-color: #{$plan.Color};"></div>{/if}
									<div class="bg"></div>
								</div>
							</div>
						</td>
					</tr>
					</table>
				</td>
				<td class="info">
					<table class="sTable{if $plan.Limit > 0 && $plan.Using == 0 && $plan.Using != ''} o60{/if}">
					<tr>
						<td class="caption"><div>{$plan.name}</div></td>
						<td>
							<ul class="features">
								<li class="period" title="{$lang.listing_live}">{if $plan.Listing_period}{$plan.Listing_period} {$lang.days}{else}{$lang.unlimited}{/if}</li>
								{if $plan.Image || $plan.Image_unlim}<li class="pics" title="{$lang.images_number}">{if $plan.Image_unlim}{$lang.unlimited}{else}{$plan.Image}{/if}</li>{/if}
								{if $plan.Video || $plan.Video_unlim}<li class="video" title="{$lang.number_of_videos}">{if $plan.Video_unlim}{$lang.unlimited}{else}{$plan.Video}{/if}</li>{/if}
							</ul>
						</td>
						{if isset($plan.Listings_remains)}	
						<td class="ralign">
							<div class="status" title="{$lang.package_purchased}">{$lang.available}</div>
						</td>
						{/if}
					</tr>
					</table>
					
					<div class="desc">
						{if $plan.Limit > 0 && $plan.Using == 0 && $plan.Using != ''}
							<b>{$lang.plan_limit_using_deny}</b>
						{else}
							<div class="text">{$plan.des|nl2br}</div>
							
							{*if $plan.Package_ID*}
								{if $plan.Advanced_mode}
									<div id="featured_option_{$plan.ID}" class="featured_option hide">
										<div>{$lang.feature_mode_caption}</div>
										<label>
											<input class="{if $smarty.post.listing_type == 'standard' || !$smarty.post.listing_type}checked{/if}{if $plan.Package_ID && empty($plan.Standard_remains) && $plan.Standard_listings != 0} disabled{/if}" type="radio" name="listing_type" value="standard" />
											{$lang.standard_listing} (<b>{if $plan.Standard_listings == 0}{$lang.unlimited}{else}{if isset($plan.Listings_remains)}{if empty($plan.Standard_remains)}{$lang.used_up}{else}{$plan.Standard_remains}{/if}{else}{$plan.Standard_listings}{/if}{/if}</b>)
										</label>
										<label>
											<input class="{if $smarty.post.listing_type == 'featured'}checked{/if}{if $plan.Package_ID && empty($plan.Featured_remains) && $plan.Featured_listings != 0} disabled{/if}" type="radio" name="listing_type" value="featured" /> 
											{$lang.featured_listing} (<b>{if $plan.Featured_listings == 0}{$lang.unlimited}{else}{if isset($plan.Listings_remains)}{if empty($plan.Featured_remains)}{$lang.used_up}{else}{$plan.Featured_remains}{/if}{else}{$plan.Featured_listings}{/if}{/if}</b>)
										</label>
									</div>
								{else}
									<div id="featured_option_{$plan.ID}" class="featured_option hide">
										{$lang.listing_number} (<b>{if $plan.Listing_number == 0}{$lang.unlimited}{else}{if empty($plan.Listings_remains)}{$lang.used_up}{else}{$plan.Listings_remains}{/if}{/if}</b>)
									</div>
								{/if}
							{*/if*}
						{/if}
					</div>
				</td>
			</tr>
			{/foreach}
			</table>
			
			{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}
			
			<script type="text/javascript">
			var plans = Array();
			var selected_plan_id = 0;
			var last_plan_id = 0;
			{foreach from=$plans item='plan'}
			plans[{$plan.ID}] = new Array();
			plans[{$plan.ID}]['Key'] = '{$plan.Key}';
			plans[{$plan.ID}]['Price'] = {$plan.Price};
			plans[{$plan.ID}]['Featured'] = {$plan.Featured};
			plans[{$plan.ID}]['Advanced_mode'] = {$plan.Advanced_mode};
			plans[{$plan.ID}]['Package_ID'] = {if $plan.Package_ID}{$plan.Package_ID}{else}false{/if};
			plans[{$plan.ID}]['Standard_listings'] = {$plan.Standard_listings};
			plans[{$plan.ID}]['Featured_listings'] = {$plan.Featured_listings};
			plans[{$plan.ID}]['Standard_remains'] = {if $plan.Standard_remains}{$plan.Standard_remains}{else}false{/if};
			plans[{$plan.ID}]['Featured_remains'] = {if $plan.Featured_remains}{$plan.Featured_remains}{else}false{/if};
			plans[{$plan.ID}]['Listings_remains'] = {if $plan.Listings_remains}{$plan.Listings_remains}{else}false{/if};
			{/foreach}
		
			{literal}
		
			$(document).ready(function(){
				$('table.plans > tbody > tr').mouseenter(function(){
					$(this).find('ul.features').show();
					$('table.plans > tbody > tr input[name=plan]:checked').closest('tr').removeClass('active');
				}).mouseleave(function(){
					$(this).find('ul.features').hide();
					$('table.plans > tbody > tr input[name=plan]:checked').closest('tr').addClass('active');
				}).click(function(){
					if ( $(this).find('input[name=plan]').is(':not(:disabled)') )
					{
						$('table.plans > tbody > tr').removeClass('active');
						$(this).addClass('active');
						flynax.planClick($(this).find('input[name=plan]'));
						$(this).find('input[name=plan]').attr('checked', true);
					}
				});
				
				$('table.plans > tbody > tr:first > td.info').width($('table.plans > tbody > tr:first > td.info').width()-10);
					
				if ( $('table.plans input[name=plan]:checked').length == 0 )
				{
					$('table.plans input[name=plan]:not(:disabled):first').attr('checked', true);
				}
				
				$('#fs_gateways').hide();
				
				flynax.planClick($('ul.plans input[name=plan]:checked'));
				$('table.plans input[name=plan]:checked').closest('tr').addClass('active');
			});
			
			{/literal}
			</script>
			<!-- select a plan end -->
	
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
			
			<input type="submit" value="{$lang.upgrade}" />
			
		</form>
	</div>
		
	{rlHook name='upgradeListingBottom'}
	
{/if}

<!-- upgrade listing plan end -->