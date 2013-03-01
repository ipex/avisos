<!-- my packages tpl -->

{if $renew_id}
	<div class="highlight">
	
	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='plan_block' name=$lang.plan_details}
		<table class="plans">
		<tr class="no_hover">
			<td class="label">
				<table class="bg{if $pack_info.Limit > 0 && $pack_info.Using == 0 && $pack_info.Using != ''} o60{/if}">
				<tr>
					<td class="left" {if $pack_info.Color}style="background-color: #{$pack_info.Color};"{/if}></td>
					<td class="center" {if $pack_info.Color}style="background-color: #{$pack_info.Color};"{/if}>
						<div class="price">{if $pack_info.Price > 0}{$config.system_currency}{$pack_info.Price}{else}{$lang.free}{/if}</div>
						<div class="type">{assign var='l_type' value=$pack_info.Type|cat:'_plan_short'}{$lang.$l_type}</div>
					</td>
					<td class="right">
						<div class="relative">
							<div {if $pack_info.Color}style="background-color: #{$pack_info.Color};"{/if}>
								{if $pack_info.Color}<div class="tile" style="background-color: #{$pack_info.Color};"></div>{/if}
								<div class="bg"></div>
							</div>
						</div>
					</td>
				</tr>
				</table>
			</td>
			<td class="info">
				<table class="sTable">
				<tr>
					<td class="caption"><div>{$pack_info.name}</div></td>
					<td>
						<ul class="features">
							<li class="period" title="{$lang.listing_live}">{if $pack_info.Listing_period}{$pack_info.Listing_period} {$lang.days}{else}{$lang.unlimited}{/if}</li>
							{if $pack_info.Image || $pack_info.Image_unlim}<li class="pics" title="{$lang.images_number}">{if $pack_info.Image_unlim}{$lang.unlimited}{else}{$pack_info.Image}{/if}</li>{/if}
							{if $pack_info.Video || $pack_info.Video_unlim}<li class="video" title="{$lang.number_of_videos}">{if $pack_info.Video_unlim}{$lang.unlimited}{else}{$pack_info.Video}{/if}</li>{/if}
						</ul>
					</td>
				</tr>
				</table>
				
				<div class="desc">
					<div class="text">{$pack_info.des|nl2br}</div>
				</div>
			</td>
		</tr>
		</table>
	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}

	<form onsubmit="return flynax.isGatewaySelected();" name="payment" method="post" action="{$rlBase}{if $config.mod_rewrite}{$pages.payment}.html{else}?page={$pages.payment}{/if}">
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
	
	</div>
	
{elseif $purchase}

	{if empty($available_packages)}
		<div class="info">{$lang.no_available_packages}</div>
	{else}
		<div class="highlight">
		
		<form onsubmit="return flynax.isGatewaySelected();" name="payment" method="post" action="{$rlBase}{if $config.mod_rewrite}{$pageInfo.Path}/purchase.html{else}index.php?page={$pageInfo.Path}&amp;purchase{/if}">
			<input type="hidden" name="action" value="submit" />
			
			<!-- select a plan -->
			{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='plans' name=$lang.select_plan tall=true}
			
			<table class="plans">
			{foreach from=$available_packages item='plan' name='plansF'}
			<tr {if $plan.ID == $smarty.post.plan}class="active"{/if}>
				{assign var='item_disabled' value=false}
				{if $plan.Limit > 0 && $plan.Using == 0 && $plan.Using != ''}
				{assign var='item_disabled' value=true}
				{/if}
				<td class="radio"><input {if $plan.ID|in_array:$used_plans_id}disabled="disabled"{/if} id="plan_{$plan.ID}" type="radio" name="plan" value="{$plan.ID}" {if $plan.ID == $smarty.post.plan}checked="checked"{/if} /></td>
				<td class="label">
					<table class="bg{if $plan.Limit > 0 && $plan.Using == 0 && $plan.Using != ''} o60{/if}">
					<tr>
						<td class="left" {if $plan.Color}style="background-color: #{$plan.Color};"{/if}></td>
						<td class="center" {if $plan.Color}style="background-color: #{$plan.Color};"{/if}>
							<div class="price">{if isset($plan.Listings_remains)}&rarr;{else}{if $plan.Price > 0}{$config.system_currency}{$plan.Price}{else}{$lang.free}{/if}{/if}</div>
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
						{if $plan.ID|in_array:$used_plans_id}
						<td class="ralign">
							<div class="status" title="{$lang.package_purchased}">{$lang.already_purchased}</div>
						</td>
						{/if}
					</tr>
					</table>
					
					<div class="desc">
						<div class="text">{$plan.des|nl2br}</div>
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
			{foreach from=$available_packages item='plan'}
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
	{/if}

{else}

	{if $packages}
		<div class="highlight">
		
		<table class="list">
		<tr class="header">
			<td>{$lang.package_info}</td>
			<td class="divider"></td>
			<td style="width: 180px;">{$lang.features}/{$lang.listings_left}</td>
			<td class="divider"></td>
			<td style="width: 90px;">{$lang.expiration_date}</td>
		</tr>
		{foreach from=$packages item='package' name='packagesF'}
		<tr class="body">
			<td class="first" valign="top">
				<table>
				<tr>
					<td valign="top" class="price"><div>{if $package.Price > 0}{$config.system_currency} {$package.Price}{else}{$lang.free}{/if}</div></td>
					<td valign="top">
						<span class="name">{$package.name}</span>
						
						<ul class="features" style="padding-top: 5px;">
							<li title="{$lang.plan_live_for}" class="period">{$lang.plan_live_for}: {if $package.Plan_period}<b>{$package.Plan_period}</b> {$lang.days}{else}{$lang.unlimited}{/if}</li>
						</ul>
					</td>
				</tr>
				</table>
			</td>
			<td class="divider"></td>
			<td valign="top">
				<ul class="features">
					<li title="{$lang.images_number}" class="pics">{if $package.Image}<b>{$package.Image}</b>{else}<span title="{$lang.unlimited}">-</span>{/if}</li>
					<li title="{$lang.number_of_videos}" class="video">{if $package.Video}<b>{$package.Video}</b>{else}<span title="{$lang.unlimited}">-</span>{/if}</li>
					<li title="{$lang.listing_live_for}" class="period">{if $package.Listing_period}<b>{$package.Listing_period}</b> {$lang.days}{else}<span title="{$lang.unlimited}">-</span>{/if}</li>
				</ul>
				
				<ul class="package_info">
				{if $package.Advanced_mode}
					<li>
						{$lang.standard}:
						<span {if empty($package.Standard_remains) && !empty($package.Standard_listings)}class="overdue"{/if}>
							{if empty($package.Standard_listings)}
								<b>{$lang.unlimited}</b>
							{else}
								{assign var='rRest' value=`$smarty.ldelim`rest`$smarty.rdelim`}
								{assign var='rAmount' value=`$smarty.ldelim`amount`$smarty.rdelim`}
								{$lang.rest_of_amount|replace:$rRest:$package.Standard_remains|replace:$rAmount:$package.Standard_listings}
							{/if}
						</span>
					</li>
					<li>
						{$lang.featured}:
						<span {if empty($package.Featured_remains) && !empty($package.Featured_listings)}class="overdue"{/if}>
							{if empty($package.Featured_listings)}
								<b>{$lang.unlimited}</b>
							{else}
								{assign var='rRest' value=`$smarty.ldelim`rest`$smarty.rdelim`}
								{assign var='rAmount' value=`$smarty.ldelim`amount`$smarty.rdelim`}
								{$lang.rest_of_amount|replace:$rRest:$package.Featured_remains|replace:$rAmount:$package.Featured_listings}
							{/if}
						</span>
					</li>
				{else}
					<li>
						{if $package.Featured}
							{$lang.featured}: 
						{else}
							{$lang.standard}: 
						{/if}
						<span {if empty($package.Listings_remains) && !empty($package.Listing_number)}class="overdue"{/if}>
							{if empty($package.Listing_number)}
								<b>{$lang.unlimited}</b>
							{else}
								{assign var='rRest' value=`$smarty.ldelim`rest`$smarty.rdelim`}
								{assign var='rAmount' value=`$smarty.ldelim`amount`$smarty.rdelim`}
								{$lang.rest_of_amount|replace:$rRest:$package.Listings_remains|replace:$rAmount:$package.Listing_number}
							{/if}
						</span>
					</li>
				{/if}
				</ul>
			</td>
			<td class="divider"></td>
			<td valign="top">
				<span class="{$package.Exp_status}">
					{if $package.Exp_date == 'unlimited'}
						{$lang.unlimited}
					{else}
						{$package.Exp_date|date_format:$smarty.const.RL_DATE_FORMAT}
					{/if}
				</span>
				<div style="text-align: center;padding: 10px 0 0;">
					<a title="{$lang.renew}" href="{$rlBase}{if $config.mod_rewrite}{$pageInfo.Path}.html?renew={$package.ID}{else}index.php?page={$pageInfo.Path}&amp;renew={$package.ID}{/if}" class="renew"><span></span></a>
				</div>
			</td>
		</tr>
		{/foreach}
		</table>
		
		<div style="padding: 15px 0 0 0; text-align: right;">
			<a href="{$rlBase}{if $config.mod_rewrite}{$pageInfo.Path}/purchase.html{else}index.php?page={$pageInfo.Path}&amp;purchase{/if}" class="button">{$lang.purchase_new_package}</a>
		</div>
		
		</div>
		
	{else}
	
		{if $config.mod_rewrite}
			{assign var='link' value=$rlBase|cat:$pageInfo.Path|cat:'/purchase.html'}
		{else}
			{assign var='link' value=$rlBase|cat:'index.php?page='|cat:$pageInfo.Path|cat:'&amp;purchase'}
		{/if}
		{assign var='replace' value='<a href="'|cat:$link|cat:'" class="static">$1</a>'}
		<span class="info">{$lang.no_packages_available|regex_replace:'/\[(.*)\]/':$replace}</span>
		
	{/if}
	
{/if}

<!-- my packages tpl end -->