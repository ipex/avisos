<!-- accounts tpl -->

{if $account_type}
	
	<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}jquery/numeric.js"></script>
	<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}jquery/jquery.ui.js"></script>
	<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}jquery/datePicker/i18n/ui.datepicker-{$smarty.const.RL_LANG_CODE}.js"></script>
	<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}jquery/jquery.textareaCounter.js"></script>
	
	<!-- account details -->
	{if $account}
		<!-- tabs -->
		<div class="tabs">
			<div class="left"></div>
			<ul>
				{foreach from=$tabs item='tab' name='tabF'}
				<li {if $smarty.foreach.tabF.first}class="active first"{/if} id="tab_{$tab.key}">
					<span class="left">&nbsp;</span>
					<span class="center"><span>{$tab.name}</span></span>
					<span class="right">&nbsp;</span>
				</li>
				{/foreach}
			</ul>
			<div class="right"></div>
		</div>
		<div class="clear"></div>
		<!-- tabs end -->
		
		<!-- account details -->
		<div id="area_details" class="tab_area">
			<table class="seller_info">
			<tr>
				<td valign="top" class="side_bar">
					<div>
						<a title="{$lang.visit_owner_page}" href="{$account.Personal_address}"><img {if !empty($account.Photo)}class="photo"{/if} title="" alt="{$lang.seller_thumbnail}" src="{if !empty($account.Photo)}{$smarty.const.RL_URL_HOME}files/{$account.Photo}{else}{$rlTplBase}img/no-account.png{/if}" /></a>
					</div>
					<ul class="info">
						{if $config.messages_module && ($isLogin || (!$isLogin && $config.messages_allow_free))}<li><input id="contact_owner" type="button" value="{$lang.contact_owner}" /></li>{/if}
						{if $account.Own_page}
							<li><a onclick="tabsSwitcher('#tab_listings')" title="{$lang.other_owner_listings}" href="javascript:void(0)">{$lang.account_listings}</a> <span class="counter">({$account.Listings_count})</span></li>
						{/if}
					</ul>
				</td>
				<td valign="top" class="details">
					<div class="highlight">
						<div class="username">{$account.Full_name}</div>
							<table class="table" style="margin: 0 0 15px;">
							<tr id="si_field_join_date">
								<td class="name">{$lang.join_date}:</td>
								<td class="value first">{$account.Date|date_format:$smarty.const.RL_DATE_FORMAT}</td>
							</tr>
							{if $account.Own_page && $account.Personal_address}
							<tr id="si_field_personal_address">
								<td class="name">{$lang.personal_address}:</td>
								<td class="value"><a title="{$lang.visit_owner_page}" href="{$account.Personal_address}">{$account.Personal_address}</a></td>
							</tr>
							{/if}
							{if $account.Display_email}
								<tr id="si_field_mail">
									<td class="name">{$lang.mail}:</td>
									<td class="value">{encodeEmail email=$account.Mail}</td>
								</tr>
							{/if}
							</table>
						
							{if $account.Fields}
								<table class="table">
								{foreach from=$account.Fields item='item' name='fListings'}
									{if !empty($item.value) && $item.Details_page}
										{include file='blocks'|cat:$smarty.const.RL_DS|cat:'field_out.tpl'}
									{/if}
								{/foreach}
								</table>
							{/if}
					</div>
				</td>
			</tr>
			</table>
			
			<!-- contact owner form template -->
			<div class="hide" id="contact_owner_form">
				<div class="caption">{$lang.contact_owner}</div>
				
				<form name="contact_owner" onsubmit="flynax.contactOwnerSubmit($(this).find('input[type=submit]'));return false;" method="post" action="">
					<table class="submit_modal">
					{if $isLogin}
						<tr>
							<td colspan="2">
								<div>{$lang.message} <span class="red">*</span></div>
								<textarea id="contact_owner_message" rows="6" cols="" style="width: 97%;"></textarea>
							</td>
						</tr>
					{else}
						<tr>
							<td class="name">{$lang.name} <span class="red">*</span></td>
							<td class="field"><input type="text" id="contact_name" value="{$account_info.First_name} {$account_info.Last_name}" /></td>
						</tr>
						<tr>
							<td class="name">{$lang.mail} <span class="red">*</span></td>
							<td class="field"><input type="text" id="contact_email" value="{$account_info.Mail}" /></td>
						</tr>
						<tr>
							<td class="name">{$lang.contact_phone}</td>
							<td class="field"><input type="text" id="contact_phone" /></td>
						</tr>
						<tr>
							<td class="name" colspan="2">
								<div>{$lang.message} <span class="red">*</span></div>
								<textarea id="contact_owner_message" rows="6" cols="" style="width: 97%;"></textarea>
							</td>	
						</tr>
						<tr>
							<td colspan="2">{include file='captcha.tpl' captcha_id='contact_code' no_wordwrap=true}</td>
						</tr>
					{/if}
					<tr>
						<td colspan="2" {if !$isLogin}class="button"{/if}>
							<input type="submit" name="finish" value="{$lang.send}" />
							<input type="button" name="close" value="{$lang.cancel}" />
						</td>
					</tr>
					</table>
				</form>
			</div>
			<!-- contact owner form template end -->
			
			<script type="text/javascript">
			var current_page = {if $pInfo.current}{$pInfo.current}{else}false{/if};
			var sorting_mode = {if $sorting_mode}true{else}false{/if};
			{literal}
			
			$(document).ready(function(){
				$('#contact_owner').flModal({
					source: '#contact_owner_form',
					width: 500,
					height: 'auto',
					ready: function(){
						$('#contact_owner_message').textareaCount({
							'maxCharacterSize': rlConfig['messages_length'],
							'warningNumber': 20
						})
					}
				});
				
				/* switch to listings tab */
				if ( flynax.getHash() == 'listings' || current_page > 0 || sorting_mode )
				{
					tabsSwitcher('#tab_listings')
				}
			});
			
			{/literal}
			</script>
		</div>
		<!-- account details end -->
		
		<!-- account listings -->
		<div id="area_listings" class="tab_area hide">
			{if !empty($listings)}
			
				{include file='blocks'|cat:$smarty.const.RL_DS|cat:'grid_navbar.tpl'}
								
				{include file='blocks'|cat:$smarty.const.RL_DS|cat:'grid.tpl'}

				<!-- paging block -->
				{paging calc=$pInfo.calc total=$listings|@count current=$pInfo.current per_page=$config.listings_per_page custom=$account.Own_address full=true}
				<!-- paging block end -->
			
			{else}
				<div class="info">{$lang.no_dealer_listings}</div>
			{/if}
		</div>
		<!-- account listings end -->
		
		<!-- map -->
		<div id="area_map" class="tab_area hide">
			<div class="highlight">
				<div id="map" style="width: {if empty($config.map_width)}100%{else}{$config.map_width}px{/if}; height: {if empty($config.map_height)}300px{else}{$config.map_height}px{/if}"></div>
			</div>
				
			<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=false{if $smarty.const.RL_LANG_CODE != '' && $smarty.const.RL_LANG_CODE != 'en'}&amp;language={$smarty.const.RL_LANG_CODE}{/if}"></script>
			<script type="text/javascript" src="http://www.google.com/uds/api?file=uds.js&amp;v=1.0&amp;key={$config.google_map_key}"></script>
			<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}jquery/jquery.flmap.js"></script>
			<script type="text/javascript">//<![CDATA[
			{literal}
			
			var map_exist = false;
			$(document).ready(function(){
				$('div.tabs li').click(function(){
					if ( !map_exist && $(this).attr('id') == 'tab_map' )
					{
						$('#map').flMap({
							addresses: [
								[{/literal}'{if $location.direct}{$location.direct}{else}{$location.search}{/if}', '{$location.show}', '{if $location.direct}direct{else}geocoder{/if}'{literal}]
							],
							phrases: {
								hide: '{/literal}{$lang.hide}{literal}',
								show: '{/literal}{$lang.show}{literal}',
								notFound: '{/literal}{$lang.location_not_found}{literal}'
							},
							zoom: {/literal}{$config.map_default_zoom}{if $config.map_amenities && $amenities},{literal}
							localSearch: {
								caption: '{/literal}{$lang.local_amenity}{literal}',
								services: [{/literal}
									{foreach from=$amenities item='amenity' name='amenityF'}
									['{$amenity.Key}', '{$amenity.name}', {if $amenity.Default}'checked'{else}false{/if}]{if !$smarty.foreach.amenityF.last},{/if}
									{/foreach}
								{literal}]
							}
							{/literal}{/if}{literal}
						});
						map_exist = true;
					}
				});
			});
			
			{/literal}
			//]]>
			</script>
		</div>
		<!-- map -->
	
	<!-- accounts search -->
	{else}
		<!-- tabs -->
		<div class="tabs">
			<div class="left"></div>
			<ul>
				{foreach from=$tabs item='tab' name='tabF'}
				<li {if $smarty.foreach.tabF.first}class="active first"{/if} id="tab_{$tab.key}">
					<span class="left">&nbsp;</span>
					<span class="center"><span>{$tab.name}</span></span>
					<span class="right">&nbsp;</span>
				</li>
				{/foreach}
			</ul>
			<div class="right"></div>
		</div>
		<div class="clear"></div>
		<!-- tabs end -->
	
		<script type="text/javascript">rlConfig['sf_display_fields'] = 0;</script><!-- John's solution (temporarry) -->
		
		<!-- characters tab -->
		<div id="area_characters" class="tab_area">
			
			<table class="grid_navbar">
			<tr>
				<td class="switcher">
					<div class="table"><div {if $smarty.cookies.grid_mode == 'table' || !isset($smarty.cookies.grid_mode)}class="active"{/if}></div></div>
					<div class="list"><div {if $smarty.cookies.grid_mode == 'list'}class="active"{/if}></div></div>
				</td>
				<td class="sorting">
					<span class="caption">{$lang.search_accounts_by}:</span>
					<span class="alphabet">
						{foreach from=$alphabet item='character'}
							<a href="{$rlBase}{if $config.mod_rewrite}{$pageInfo.Path}/{$character}.html{else}?page={$pageInfo.Path}&amp;character={$character}{/if}" class="{if $character == $char}active{/if}">{$character}</a>
						{/foreach}
					</span>
				</td>
			</tr>
			</table>
			
			<!-- dealers list -->
			{if !empty($alphabet_dealers)}
				{assign var='grid_mode' value=$smarty.cookies.grid_mode}

				<div id="listings" class="accounts_grid">
					{if $grid_mode == 'list'}
						<div class="list">
							{foreach from=$alphabet_dealers item='dealer' key='key' name='dealersF'}
								{include file='blocks'|cat:$smarty.const.RL_DS|cat:'dealer.tpl'}
							{/foreach}
						</div>
					{else}
						<table class="table">
						<tr>
							{foreach from=$alphabet_dealers item='dealer' key='key' name='dealersF'}
							<td valign="top">{include file='blocks'|cat:$smarty.const.RL_DS|cat:'dealer.tpl'}</td>
							{if $smarty.foreach.dealersF.iteration%2 == 0 && !$smarty.foreach.dealersF.last}
							</tr><tr>
							{else}
								{if !$smarty.foreach.dealersF.last}<td class="divider"></td>{/if}
							{/if}
							{/foreach}
							{if $smarty.foreach.dealersF.total == 1}
							<td class="divider"></td>
							<td valign="top"></td>
							{/if}
						</tr>
						</table>
					{/if}
				</div>
				
				<!-- paging block -->
				{paging calc=$pInfo.calc_alphabet total=$alphabet_dealers|@count current=$pInfo.current per_page=$config.dealers_per_page url=$char var='character'}
				<!-- paging block end -->
			{else}
				<div class="info">{if $search_results != 'search'}{$lang.no_dealers}{/if}</div>
			{/if}
			<!-- dealers list end -->
		</div>
		<!-- characters tab end -->
		
		<!-- advanced search tab -->
		<div id="area_search" class="tab_area hide">
			{if $search_results == 'search'}
				{if $dealers}
					{include file='blocks'|cat:$smarty.const.RL_DS|cat:'grid_navbar.tpl' mode='account'}
					
					{assign var='grid_mode' value=$smarty.cookies.grid_mode}

					<div id="listings" class="accounts_grid">
						{if $grid_mode == 'list'}
							<div class="list">
								{foreach from=$dealers item='dealer' key='key' name='dealersF'}
									{include file='blocks'|cat:$smarty.const.RL_DS|cat:'dealer.tpl'}
								{/foreach}
							</div>
						{else}
							<table class="table">
							<tr>
								{foreach from=$dealers item='dealer' key='key' name='dealersF'}
								<td valign="top">{include file='blocks'|cat:$smarty.const.RL_DS|cat:'dealer.tpl'}</td>
								{if $smarty.foreach.dealersF.iteration%2 == 0 && !$smarty.foreach.dealersF.last}
								</tr><tr>
								{else}
									{if !$smarty.foreach.dealersF.last}<td class="divider"></td>{/if}
								{/if}
								{/foreach}
								{if $smarty.foreach.dealersF.total == 1}
								<td class="divider"></td>
								<td valign="top"></td>
								{/if}
							</tr>
							</table>
						{/if}
					</div>
					
					<!-- paging block -->
					{paging calc=$pInfo.calc total=$dealers|@count current=$pInfo.current per_page=$config.dealers_per_page url=$search_results_url}
					<!-- paging block end -->
				{else}
					<div class="info">{$lang.no_dealers_found}</div>
				{/if}
			{else}
				<div class="highlight">
					<form method="post" action="{$rlBase}{if $config.mod_rewrite}{$pageInfo.Path}/{$search_results_url}.html{else}?page={$pageInfo.Path}&amp;{$search_results_url}{/if}">
						<input type="hidden" name="search" value="true" />
						{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fields_search.tpl'}
					
						<table class="search">
						<tr>
							{if $config.search_fields_position == 2}<td class="field button"></td>{/if}
							<td class="value button"><input type="submit" name="search" value="{$lang.search}" /></td>
						</tr>
						</table>
					</form>
					
					<script type="text/javascript">
					var phrase_from = "{$lang.from}";
					var phrase_to = "{$lang.to}";
					{literal}
					
					$(document).ready(function(){
						flynax.fromTo(phrase_from, phrase_to);
						$("input.numeric").numeric();
					});
					
					{/literal}
					</script>
				</div>
			{/if}
		</div>
		<!-- advanced search tab end -->
		
		{if $alphabet_mode}
			<script type="text/javascript">
				tabsSwitcher('#tab_characters');
			</script>
		{elseif $search_results}
			<script type="text/javascript">
				tabsSwitcher('#tab_search');
			</script>
		{/if}
		
		<script type="text/javascript">
		{literal}
		if ( flynax.getHash('modify') )
		{
			tabsSwitcher('#tab_search');
		}
		{/literal}
		</script>
	{/if}

{/if}

<!-- accounts tpl end -->