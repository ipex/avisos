<!-- listings tpl -->

{if !$deny}

	<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}jquery/jquery.qtip.js"></script>
	<script type="text/javascript">flynax.qtip(); flynax.phoneField();</script>
	
	<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}jquery/jquery.caret.js"></script>
	<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}ckeditor/ckeditor.js"></script>
	
	<!-- navigation bar -->
	<div id="nav_bar">
		{rlHook name='apTplListingsNavBar'}
	
		{if $smarty.get.action == 'photos'}
			<a href="{$rlBase}index.php?controller={$smarty.get.controller}&amp;action=video&amp;id={$smarty.get.id}" class="button_bar"><span class="left"></span><span class="center_video">{$lang.manage_video}</span><span class="right"></span></a>
		{/if}
		
		{if !isset($smarty.get.action)}
			<a href="javascript:void(0)" onclick="show('filters', '#action_blocks div');" class="button_bar"><span class="left"></span><span class="center_search">{$lang.filters}</span><span class="right"></span></a>
		{/if}
		
		{if $smarty.get.action == 'video'}
			<a href="{$rlBase}index.php?controller={$smarty.get.controller}&amp;action=photos&amp;id={$smarty.get.id}" class="button_bar"><span class="left"></span><span class="center_photo">{$lang.manage_photos}</span><span class="right"></span></a>
		{/if}
		
		{if $aRights.$cKey.add && !isset($smarty.get.action)}
			<a href="javascript:void(0)" onclick="show('new_listing', '#action_blocks div');" class="button_bar"><span class="left"></span><span class="center_add">{$lang.add_listing}</span><span class="right"></span></a>
		{/if}
		
		{if $smarty.get.action == 'view'}
			<a href="{$rlBase}index.php?controller={$smarty.get.controller}&amp;action=edit&amp;id={$listing_data.ID}" class="button_bar"><span class="left"></span><span class="center_edit">{$lang.edit_listing}</span><span class="right"></span></a>
		{/if}
		
		<a href="{$rlBase}index.php?controller={$smarty.get.controller}" class="button_bar"><span class="left"></span><span class="center_list">{$lang.listings_list}</span><span class="right"></span></a>
	</div>
	<!-- navigation bar end -->
	
	<div id="action_blocks">
	
		{if !isset($smarty.get.action)}
			<!-- filters -->
			<div id="filters" class="hide">
				{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_start.tpl' block_caption=$lang.filter_by}
				
				<table>
				<tr>
					<td valign="top">
						<table class="form">
						{foreach from=$filters item='filter' key='field'}
						<tr>
							<td class="name w130">{$filter.phrase}</td>
							<td class="field">
								<select class="filters w200" id="{$field}">
								<option value="">- {$lang.all} -</option>
								{foreach from=$filter.items item='item' key='value'}
									<option {if $item.type}id="option_{$item.type}_{$value}"{/if} {if $item|is_array && $item.margin}{if $item.margin == 5}class="highlight_opt"{/if} style="margin-left: {$item.margin}px;"{/if}value="{$value}" {if isset($status) && $field == 'Status' && $value == $status}selected="selected"{/if}>{if $item|is_array}{if $item.name}{$item.name}{else}{$lang[$item.pName]}{/if}{else}{$item}{/if}</option>
								{/foreach}
								</select>
							</td>
						</tr>
						{/foreach}
											
						<tr>
							<td></td>
							<td class="field nowrap">
								<input type="button" class="button" value="{$lang.filter}" id="filter_button" />
								<input type="button" class="button" value="{$lang.reset}" id="reset_filter_button" />
								<a class="cancel" href="javascript:void(0)" onclick="show('filters')">{$lang.cancel}</a>
							</td>
						</tr>
						</table>
					</td>
					<td style="width: 50px;"></td>
					<td valign="top">
						<table class="form">
						<tr>
							<td class="name w130">{$lang.listing_id}</td>
							<td class="field">
								<input class="filters" type="text" id="listing_id" maxlength="60" />
							</td>
						</tr>
						<tr>
							<td class="name w130">{$lang.username}</td>
							<td class="field">
								<input class="filters" type="text" maxlength="255" id="Account" />
							</td>
						</tr>
						<tr>
						<td class="name w130">{$lang.name}</td>
							<td class="field">
								<input class="filters" type="text" id="name" maxlength="60" />
							</td>
						</tr>
						<tr>
							<td class="name w130">{$lang.mail}</td>
							<td class="field">
								<input class="filters" type="text" id="email" maxlength="60" />
							</td>
						</tr>
						<tr>
							<td class="name w130">{$lang.account_type}</td>
							<td class="field">
								<select class="filters w200" id="account_type">
									<option value="">{$lang.select}</option>
									{foreach from=$account_types item='type'}
										<option value="{$type.Key}" {if $sPost.profile.type == $type.Key}selected="selected"{/if}>{$type.name}</option>
									{/foreach}
								</select>
							</td>
						</tr>
						
						{rlHook name='apTplListingsSearch2'}
						
						</table>
					</td>
				</tr>
				</table>
				
				{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_end.tpl'}
			</div>
			
			<script type="text/javascript">
			{literal}
			var filters = new Array();
			var step = 0;
	
			$(document).ready(function(){
				
				if ( readCookie('listings_sc') )
				{
					$('#filters').show();
					var cookie_filters = readCookie('listings_sc').split(',');
					
					for (var i in cookie_filters)
					{
						if ( typeof(cookie_filters[i]) == 'string' )
						{
							var item = cookie_filters[i].split('||');
							$('#'+item[0]).selectOptions(item[1]);
						}
					}
				}
				
				$('select#Type').change(function(){
					var key = $(this).val();
					
					if ( key )
					{
						$('select#Category_ID option').hide();
						$('select#Category_ID option[id^=option_'+key+']').show();
					}
					else
					{
						$('select#Category_ID option').show();
					}
				});
				
				$('#filter_button').click(function(){
					filters = new Array();
					write_filters = new Array();
					
					createCookie('listings_pn', 0, 1);
					
					$('.filters').each(function(){
						if ($(this).attr('value') != 0)
						{
							filters.push(new Array($(this).attr('id'), $(this).attr('value')));
							write_filters.push($(this).attr('id')+'||'+$(this).attr('value'));
						}
					});
	
					{/literal}{rlHook name='apTplListingsSearchJS'}{literal}
					
					// save search criteria
					createCookie('listings_sc', write_filters, 1);
					
					// reload grid
					listingsGrid.filters = filters;
					listingsGrid.reload();
				});
				
				$('#reset_filter_button').click(function(){
					eraseCookie('listings_sc');
					listingsGrid.reset();
					
					$("#filters select option[value='']").attr('selected', true);
					$("#filters input[type=text]").val('');
					$("#Category_ID option").show();
				});
	
				/* autocomplete js */
				$('#Account').rlAutoComplete();
			});
			{/literal}
			</script>
			<!-- filters end -->
		{/if}
		
		<!-- categories list -->
		{if $aRights.$cKey.add && !$smarty.get.action}
		<div id="new_listing" class="hide">
			{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_start.tpl' block_caption=$lang.choose_category}
			
				<div id="categories">
				{foreach from=$sections item='section'}
					<fieldset class="light">
					<legend id="legend_section_{$section.ID}" class="up" onclick="fieldset_action('section_{$section.ID}');">{$section.name}</legend>
					<div id="section_{$section.ID}">
						<div class="tree">
							{if !empty($section.Categories)}
								{include file='blocks'|cat:$smarty.const.RL_DS|cat:'category_level_link.tpl' categories=$section.Categories first=true}
							{else}
								<div style="padding: 0 0 8px 10px;">{$lang.no_items_in_sections}</div>
							{/if}
						</div>
					</div>
					</fieldset>
				{/foreach}
				</div>
		
			{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_end.tpl'}
		</div>
		<script type="text/javascript">
		{literal}
		
		$(document).ready(function(){
			flynax.treeLoadLevel('link', 'crossedTree', 'div#new_listing');
		});
		
		{/literal}
		</script>
		{/if}
		<!-- categories list end -->

	</div>
	
	{assign var='sPost' value=$smarty.post}
	
	{if $smarty.get.action == 'add'}
	
		<!-- add new listing -->
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_start.tpl' block_caption=$lang.add_listing}
		
		<!-- listing fieldset -->
		<div style="margin: 5px 10px 10px;">
			<form onsubmit="return submitHandler();" id="add_listing" method="post" action="{$rlBase}index.php?controller={$smarty.get.controller}&amp;action=add&amp;category={$smarty.get.category}" enctype="multipart/form-data">
				<input type="hidden" name="action" value="add" />
			
				<!-- display plans -->
				{if !empty($plans)}
				<fieldset class="light">
					<legend id="legend_plans" class="up" onclick="fieldset_action('plans');">{$lang.plans}</legend>
					<div id="plans">
						{foreach from=$plans item='plan' name='fPlan'}
							<div class="plan_item">
								<table class="sTable">
								<tr>
									<td align="center" style="width: 30px"><input accesskey="{$plan.Cross}" style="margin: 0 10px 0 0;" id="plan_{$plan.ID}" type="radio" name="f[l_plan]" value="{$plan.ID}" {if $plan.ID == $smarty.post.f.l_plan}checked="checked"{else}{if $smarty.foreach.fPlan.first}checked="checked"{/if}{/if} /></td>
									<td>
										<label for="plan_{$plan.ID}" class="blue_11_normal">
											{assign var='l_type' value=$plan.Type|cat:'_plan'}
											{$plan.name} - <b>{if $plan.Price > 0}{$config.system_currency}{$plan.Price}{else}{$lang.free}{/if}</b>
										</label>
										<div>{$plan.des}</div>
									</td>
								</tr>
								</table>
							</div>
						{/foreach}
					</div>
				</fieldset>
				{/if}
				<!-- display plans end -->
				
				<!-- crossed categories -->
				<div id="crossed_area" class="hide">
					<input type="hidden" name="crossed_done" value="{if $smarty.session.add_listing.crossed_done}1{/if}" />
				
					<fieldset class="light">
						<legend id="legend_crossed" class="up" onclick="fieldset_action('crossed');">{$lang.crossed_categories}</legend>
						<div id="crossed">
							<div class="auth">
								<div style="padding: 0 0 10px 0;">
									<div class="dark" id="cc_text">{$lang.crossed_top_text|replace:'[number]':'<b id="cc_number"></b>'}</div>
									<div class="dark hide" id="cc_text_denied">{$lang.crossed_top_text_denied}</div>
								</div>
								
								<!-- print sections/categories tree -->
								<div id="crossed_tree" class="tree{if $smarty.post.crossed_done} hide{/if}">
								{foreach from=$sections item='section'}
									<fieldset class="light">
										<legend id="legend_crossed_{$section.ID}" class="up" onclick="fieldset_action('crossed_{$section.ID}');">{$section.name}</legend>
										<div id="crossed_{$section.ID}" class="tree">
											{assign var='type_page_key' value='lt_'|cat:$section.Key}
											{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id=$section.ID name=$section.name}
											
											{if !empty($section.Categories)}
												{include file='blocks'|cat:$smarty.const.RL_DS|cat:'category_level_crossed.tpl' categories=$section.Categories first=true}
											{else}
												<div class="dark">{$lang.no_items_in_sections}</div>
											{/if}
										
											{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}
										</div>
									</fieldset>
								{/foreach}
								</div>
								<!-- print sections/categories tree end -->
								
								<ul class="hide" id="crossed_selected"><li class="first dark"><b>{$lang.selected_crossed_categories}</b></li></ul>
								<input id="crossed_button" type="button" value="{if $smarty.post.crossed_done}{$lang.manage}{else}{$lang.done}{/if}" />
							</div>
						</div>
					</fieldset>
				</div>
				
				<script type="text/javascript">
				var plans = Array();
				var selected_plan_id = {if $smarty.post.f.l_plan}{$smarty.post.f.l_plan}{else}0{/if};
				var ca_post = {if $crossed}[{foreach from=$crossed item='crossed_cat' name='crossedF'}['{$crossed_cat}']{if !$smarty.foreach.crossedF.last},{/if}{/foreach}]{else}false{/if};
				var cc_parentPoints = {if $parentPoints}[{foreach from=$parentPoints item='parent_point' name='parentF'}['{$parent_point}']{if !$smarty.foreach.parentF.last},{/if}{/foreach}]{else}false{/if};
	
				{foreach from=$plans item='plan'}
				plans[{$plan.ID}] = new Array();
				plans[{$plan.ID}]['Key'] = '{$plan.Key}';
				plans[{$plan.ID}]['Cross'] = {$plan.Cross};
				{/foreach}
				
				{literal}
				
				$(document).ready(function(){
					flynax.treeLoadLevel('crossed', 'crossedTree');
					
					if ( plans[selected_plan_id] && plans[selected_plan_id]['Cross'] )
					{
						crossCount = plans[selected_plan_id]['Cross'];
						$('#crossed_area').show();
						crossedTree();
					}
					
					/* plans click handler */
					$('input[name="f[l_plan]"]').click(function(){
						selected_plan_id = $(this).attr('id').split('_')[1];
						crossCount = plans[selected_plan_id]['Cross'];
	
						if ( crossCount > 0 )
						{
							$('#crossed_area').slideDown();
							crossedTree();
						}
						else
						{
							$('#crossed_area').slideUp();
						}
					});
				});
				
				{/literal}
				</script>
				<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}javascript/crossed.js"></script>
				<!-- crossed categories end -->
		
				<table class="form" style="margin: 0 16px 15px;">
				<tr>
					<td class="name">{$lang.set_owner} <span class="red">*</span></td>
					<td class="field">
						<input type="text" name="account_id" id="account_id" value="{foreach from=$accounts item='account'}{if $sPost.account_id == $account.ID}{$account.Username}{/if}{/foreach}" />
						
						<script type="text/javascript">
						var post_account_id = {if $sPost.account_id}{$sPost.account_id}{else}false{/if};
						{literal}
							$('#account_id').rlAutoComplete({add_id: true, id: post_account_id});
						{/literal}
						</script>
					</td>
				</tr>
				<tr>
					<td class="name">{$lang.status} <span class="red">*</span></td>
					<td class="field">
						<select name="status" class="login_input_select">
							<option value="active" {if $sPost.status == 'active'}selected="selected"{/if}>{$lang.active}</option>
							<option value="approval" {if $sPost.status == 'approval'}selected="selected"{/if}>{$lang.approval}</option>
						</select>
					</td>
				</tr>
				
				{rlHook name='apTplListingsFormAdd'}
				
				</table>
				
				{foreach from=$form item='group'}
				{if $group.Group_ID}
					{if $group.Fields || !$group.Display}
						{assign var='hide' value='false'}
					{else}
						{assign var='hide' value='true'}
					{/if}
		
					<fieldset>
						<legend id="legend_group_{$group.Key}" class="up" onclick="fieldset_action('group_{$group.Key}');">{$lang[$group.pName]}</legend>
						<div id="group_{$group.Key}">
						{if $group.Fields}
							{include file='blocks'|cat:$smarty.const.RL_DS|cat:'field.tpl' fields=$group.Fields}
						{else}
							<span class="blue_middle">{$lang.no_items_in_group}</span>
						{/if}
						</div>
					</fieldset>
				{else}
					<div style="padding: 0 0 0 16px;">
						{include file='blocks'|cat:$smarty.const.RL_DS|cat:'field.tpl' fields=$group.Fields}
					</div>
				{/if}
				{/foreach}
		
				<table class="form" style="margin: 0 16px;">
				<tr>
					<td class="no_divider"></td>
					<td class="field"><input type="submit" name="finish" value="{$lang.add_listing}" /></td>
				</tr>
				</table>
			</form>
		</div>
		
		<!-- listing fieldset end -->
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_end.tpl'}
		<!-- add new listing end -->
	
	{elseif $smarty.get.action == 'edit'}
	
		<!-- listing fieldset -->
		{if !empty($form)}
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_start.tpl'}
		
		<form onsubmit="return submitHandler();" id="edit_listing" method="post" action="{$rlBase}index.php?controller={$smarty.get.controller}&amp;action=edit&amp;id={$smarty.get.id}{if $smarty.get.ui}&amp;ui={$smarty.get.ui}{/if}{if $smarty.get.cat_id}&amp;cat_id={$smarty.get.cat_id}{/if}" enctype="multipart/form-data">
			<input type="hidden" name="action" value="edit" />
			<input type="hidden" name="fromPost" value="1" />
			
			<!-- display plans -->
			{if !empty($plans)}
			<fieldset class="light">
			
				<legend id="legend_plans" class="up" onclick="fieldset_action('plans');">{$lang.plans}</legend>
				<div id="plans">
					{foreach from=$plans item='plan' name='fPlan'}
						<div class="plan_item{if $plan.ID != $smarty.post.f.l_plan} hide{/if}">
							<table class="sTable">
							<tr>
								<td align="center" style="width: 30px"><input accesskey="{$plan.Cross}" style="margin: 0 10px 0 0;" id="plan_{$plan.ID}" type="radio" name="f[l_plan]" value="{$plan.ID}" {if $plan.ID == $smarty.post.f.l_plan}checked="checked"{else}{if $smarty.foreach.fPlan.first}checked="checked"{/if}{/if} /></td>
								<td>
									<label for="plan_{$plan.ID}" class="blue_11_normal">
										{assign var='l_type' value=$plan.Type|cat:'_plan'}
										{$plan.name} - <b>{if $plan.Price > 0}{$config.system_currency}{$plan.Price}{else}{$lang.free}{/if}</b>
									</label>
									<div>{$plan.des}</div>
								</td>
							</tr>
							</table>
						</div>
					{/foreach}
					
					{if $plans|@count > 1 || !$smarty.post.f.l_plan}
						<input id="manage_plans" type="button" value="{$lang.manage}" />
					{/if}
				</div>
			</fieldset>
			
			<script type="text/javascript">
			{literal}
			var plans_expand = false;
			$(document).ready(function(){
				$('#manage_plans').click(function(){
					if ( plans_expand )
					{
						plans_expand = false;
						$('div#plans div.hide').fadeOut();
						$(this).val('{/literal}{$lang.manage}{literal}');
					}
					else
					{
						plans_expand = true;
						$(this).val('{/literal}{$lang.apply}{literal}');
						$('div#plans div.hide').fadeIn();
					}
				});
				
				$('div#plans div.plan_item').click(function(){
					$('div#plans div.plan_item').addClass('hide').css('display', 'block');
					$(this).removeClass('hide');
				});
			});
			
			{/literal}
			</script>
			{/if}
			<!-- display plans end -->
		
			<!-- crossed categories -->
			<div id="crossed_area" {if !$plan_info.Cross}class="hide"{/if}>
				<input type="hidden" name="crossed_done" value="{if $smarty.session.add_listing.crossed_done}1{/if}" />
				
				<fieldset class="light">
					<legend id="legend_crossed" class="up" onclick="fieldset_action('crossed');">{$lang.crossed_categories}</legend>
					<div id="crossed">
		
						{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='crossed' name=$lang.crossed_categories}
						<div class="auth">
							<div style="padding: 0 0 10px 0;">
								<div class="dark" id="cc_text">{$lang.crossed_top_text|replace:'[number]':'<b id="cc_number"></b>'}</div>
								<div class="dark hide" id="cc_text_denied">{$lang.crossed_top_text_denied}</div>
							</div>
							
							<!-- print sections/categories tree -->
							<div id="crossed_tree" class="tree{if $smarty.post.crossed_done} hide{/if}">
							{foreach from=$sections item='section'}
								<fieldset class="light">
									<legend id="legend_crossed_{$section.ID}" class="up" onclick="fieldset_action('crossed_{$section.ID}');">{$section.name}</legend>
									<div id="crossed_{$section.ID}" class="tree">
										{assign var='type_page_key' value='lt_'|cat:$section.Key}
										{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id=$section.ID name=$section.name}
										
										{if !empty($section.Categories)}
											{include file='blocks'|cat:$smarty.const.RL_DS|cat:'category_level_crossed.tpl' categories=$section.Categories first=true}
										{else}
											<div class="dark">{$lang.no_items_in_sections}</div>
										{/if}
									
										{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}
									</div>
								</fieldset>
							{/foreach}
							</div>
							<!-- print sections/categories tree end -->
							
							<ul class="hide" id="crossed_selected"><li class="first dark"><b>{$lang.selected_crossed_categories}</b></li></ul>
							<input id="crossed_button" type="button" value="{if $smarty.post.crossed_done}{$lang.manage}{else}{$lang.done}{/if}" />
						</div>
						{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}
						
						<script type="text/javascript">
						var plans = Array();
						var selected_plan_id = {if $smarty.post.f.l_plan}{$smarty.post.f.l_plan}{else}0{/if};
						var ca_post = {if $crossed}[{foreach from=$crossed item='crossed_cat' name='crossedF'}['{$crossed_cat}']{if !$smarty.foreach.crossedF.last},{/if}{/foreach}]{else}false{/if};
						var cc_parentPoints = {if $parentPoints}[{foreach from=$parentPoints item='parent_point' name='parentF'}['{$parent_point}']{if !$smarty.foreach.parentF.last},{/if}{/foreach}]{else}false{/if};
			
						{foreach from=$plans item='plan'}
						plans[{$plan.ID}] = new Array();
						plans[{$plan.ID}]['Key'] = '{$plan.Key}';
						plans[{$plan.ID}]['Cross'] = {$plan.Cross};
						{/foreach}
						
						{literal}
						
						$(document).ready(function(){
							flynax.treeLoadLevel('crossed', 'crossedTree');
							
							if ( plans[selected_plan_id] && plans[selected_plan_id]['Cross'] )
							{
								crossCount = plans[selected_plan_id]['Cross'];
								$('#crossed_area').show();
								crossedTree();
							}
							
							/* plans click handler */
							$('input[name="f[l_plan]"]').click(function(){
								selected_plan_id = $(this).attr('id').split('_')[1];
								crossCount = plans[selected_plan_id]['Cross'];
			
								if ( crossCount > 0 )
								{
									$('#crossed_area').slideDown();
									crossedTree();
								}
								else
								{
									$('#crossed_area').slideUp();
								}
							});
						});
						
						{/literal}
						</script>
						<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}javascript/crossed.js"></script>
					</div>
				</fieldset>
			</div>
			<!-- crossed categories end -->
		
			<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}ckeditor/ckeditor.js"></script>
			
			<table class="form" style="margin: 0 16px 15px;">
			<tr>
				<td class="name">
					{$lang.set_owner}
				</td>
				<td class="field">
					<input type="text" name="account_id" id="account_id" value="{foreach from=$accounts item='account'}{if $sPost.account_id == $account.ID}{$account.Username}{/if}{/foreach}" />
					
					<script type="text/javascript">
					var account_id = {if $sPost.account_id}{$sPost.account_id}{else}false{/if};
					{literal}
						$('#account_id').rlAutoComplete({add_id: true, id: account_id});
					{/literal}
					</script>
				</td>
			</tr>
			<tr>
				<td class="name">{$lang.status} <span class="red">*</span></td>
				<td class="field">
					<select name="status" class="login_input_select">
						<option value="active" {if $sPost.status == 'active'}selected="selected"{/if}>{$lang.active}</option>
						<option value="approval" {if $sPost.status == 'approval'}selected="selected"{/if}>{$lang.approval}</option>
					</select>
				</td>
			</tr>
			
			{rlHook name='apTplListingsFormEdit'}
			
			</table>
			
			{foreach from=$form item='group'}
			{if $group.Group_ID}
				{if $group.Fields || !$group.Display}
					{assign var='hide' value='false'}
				{else}
					{assign var='hide' value='true'}
				{/if}
				
				<fieldset>
				<legend id="legend_group_{$group.Key}" class="up" onclick="fieldset_action('group_{$group.Key}');">{$lang[$group.pName]}</legend>
					<div id="group_{$group.Key}">
					{if $group.Fields}
						{include file='blocks'|cat:$smarty.const.RL_DS|cat:'field.tpl' fields=$group.Fields}
					{else}
						<span>{$lang.no_items_in_group}</span>
					{/if}
					</div>
				</fieldset>
			{else}
				<div style="padding: 0 16px;">
					{include file='blocks'|cat:$smarty.const.RL_DS|cat:'field.tpl' fields=$group.Fields}
				</div>
			{/if}
			{/foreach}
			
			<table class="form" style="margin: 0 16px;">
			<tr>
				<td class="no_divider"></td>
				<td class="field"><input type="submit" name="finish" value="{$lang.edit_listing}" /></td>
			</tr>
			</table>
		</form>
		
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_end.tpl'}
		{/if}
		
		<!-- listing fieldset end -->
	{elseif $smarty.get.action == 'photos'}
		<!-- manage listing photo -->
		
		<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}jquery/jquery.jcrop.js"></script>
		<script src="{$rlTplBase}js/crop.js" type="text/javascript"></script>
		
		<style type="text/css">
		@import url("{$smarty.const.RL_LIBS_URL}jquery/jcrop/jquery.Jcrop.css");
		</style>
	
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_start.tpl'}
		
		<!-- listing info -->
		<fieldset style="margin: 0 0 10px 0;">
			<legend id="legend_details" class="up" onclick="fieldset_action('details');">{$lang.listing_details}</legend>
			<div id="details">
				<table class="list" style="margin: 0 10px 5px 10px;">
				{foreach from=$listing.fields item='item' key='field' name='fListings'}
				{if !empty($item.value)}
				<tr>
					<td class="name">{$item.name}:</td>
					<td class="value">{$item.value}</td>
				</tr>
				{/if}
				{/foreach}
				</table>
			</div>
		</fieldset>
		<!-- listing info end -->
		
		<!-- photos list -->
		<fieldset style="margin: 10px 0;">
			<legend id="legend_photos_list" class="up" onclick="fieldset_action('photos_list');">{$lang.pictures_manager}</legend>
			<div id="photos_list">
				<div style="padding: 0 10px;" id="photos_dom">
					{include file='blocks'|cat:$smarty.const.RL_DS|cat:'photo_manager.tpl'}
				</div>
			</div>
		</fieldset>
		<!-- photos list end -->
		
		{rlHook name='apTplListingsPhotos'}
		
		<!-- file crop -->
		<div id="width_detect"></div>
		<div id="crop_block" class="hide">
			<fieldset style="margin: 10px 0;">
				<legend id="legend_crop_area" class="up" onclick="fieldset_action('crop_area');">{$lang.pictures_manager}</legend>
				<div id="crop_area">
		
					<div class="dark">{$lang.crop_notice}</div>
					<div id="crop_obj" style="padding: 10px 0;"></div>
				
					<input type="button" class="button" value="{$lang.rl_accept}" id="crop_accept" /> 
					<input type="button" class="button" value="{$lang.cancel}" id="crop_cancel" />
		
				</div>
			</fieldset>
		</div>
		<!-- file crop end -->
		
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_end.tpl'}
		
		<!-- manage listing photo end -->
	{elseif $smarty.get.action == 'video'}
		<!-- add listing video -->
		
		{if $listing.Plan_video || $listing.Video_unlim}
		
			{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_start.tpl'}
		
			<!-- listing info -->
			<fieldset style="margin: 0 0 10px 0;">
				<legend id="legend_details" class="up" onclick="fieldset_action('details');">{$lang.listing_details}</legend>
				<div id="details">
					<table class="list" style="margin: 0 10px 5px 10px;">
					{foreach from=$listing.fields item='item' key='field' name='fListings'}
					{if !empty($item.value)}
					<tr>
						<td class="name">{$item.name}:</td>
						<td class="value">{$item.value}</td>
					</tr>
					{/if}
					{/foreach}
					</table>
				</div>
			</fieldset>
			<!-- listing info end -->
			
			<!-- file uploader -->
			<fieldset style="margin: 10px 0;">
				{if $video_allow && !$listing.Video_unlim}
					{assign var='replace' value=`$smarty.ldelim`number`$smarty.rdelim`}
					{assign var='video_left' value=$lang.upload_video_left|replace:$replace:$video_allow}
				{else}
					{assign var='video_left' value=$lang.upload_video}
				{/if}
			
				<legend id="legend_upload_area" class="up" onclick="fieldset_action('upload_area');"><span id="video_left">{$video_left}</span></legend>
				<div id="upload_area">
					
					{if !$video_allow && !$listing.Video_unlim}
						{assign var='replace_count' value=`$smarty.ldelim`count`$smarty.rdelim`}
						{assign var='replace_plan' value=`$smarty.ldelim`plan`$smarty.rdelim`}
						{assign var='plan_key' value='listing_plans+name+'|cat:$listing.Plan_key}
						<div class="grey_middle" style="padding: 0 0 5px 10px">{$lang.no_more_videos|replace:$replace_count:$listing.Plan_video|replace:$replace_plan:$lang.$plan_key}</div>
					{/if}
					
					<div id="protect" {if !$video_allow && !$listing.Video_unlim}class="hide"{/if}>
					<form method="post" action="{$rlBase}index.php?controller={$smarty.get.controller}&amp;action=video&amp;id={$smarty.get.id}" enctype="multipart/form-data">
						<input name="upload" value="true" type="hidden" />
						<div style="margin: 0 0 5px 10px;">
							<table class="form" id="upload_fields">
							<tr>
								<td class="name w130">{$lang.video_type}:</td>
								<td class="field">
									<select id="type_selector" name="type" >
										<option value="">{$lang.select}</option>
										<option {if $smarty.post.type == 'local'}selected="selected"{/if} value="local">{$lang.local}</option>
										<option {if $smarty.post.type == 'youtube'}selected="selected"{/if} value="youtube">{$lang.youtube}</option>
									</select>
								</td>
							</tr>
							</table>
							
							<div id="local_video" class="upload{if $smarty.post.type != 'local'} hide{/if}">
								<table class="form">
								<tr>
									<td class="name w130">{$lang.file}:</td>
									<td class="field">
										<input class="file" type="file" name="video" />
										<table>
										<tr>
											<td>{$lang.max_file_size}:</td>
											<td><b><em>{$max_file_size}</em></b></td>
										</tr>
										<tr>
											<td>{$lang.available_file_type}:</td>
											<td>
												{foreach from=$l_player_file_types item=item key='f_type' name='file_typesF'}
												<b><em>{$f_type}</em></b>{if !$smarty.foreach.file_typesF.last},{/if}
												{/foreach}
											</td>
										</tr>
										</table>
									</td>
								</tr>
								<tr>
									<td class="name w130">{$lang.preview_image}:</td>
									<td class="field">
										<input class="file" type="file" name="preview" />
									</td>
								</tr>
								<tr>
									<td></td>
									<td>
										<input class="button" type="submit" name="finish" value="{$lang.upload}" />
									</td>
								</tr>
								</table>
							</div>
							
							<div id="youtube_video" class="upload{if $smarty.post.type != 'youtube'} hide{/if}">
								<table class="form">
								<tr>
									<td class="name w130">{$lang.embed}:</td>
									<td class="field">
										<textarea style="width: 500px; height: 80px;" cols="" rows="" name="youtube_embed">{$smarty.post.youtube_embed}</textarea>
									</td>
								</tr>
								<tr>
									<td></td>
									<td>
										<input class="button" type="submit" name="finish" value="{$lang.upload}" />
									</td>
								</tr>
								</table>
							</div>
						</div>
					</form>
					</div>
				</div>
			</fieldset>
			<!-- file uploader end -->
	
			<style type="text/css">
			@import url("{$smarty.const.RL_LIBS_URL}jquery/fancybox/jquery.fancybox.css");
			</style>
			
			<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}player/flowplayer.js"></script>
			<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}jquery/jquery.fancybox.js"></script>
			
			<!-- video list -->
			<fieldset style="margin: 10px 0;">
				<legend id="legend_video_area" class="up" onclick="fieldset_action('video_area');">{$lang.listing_video}</legend>
				<div id="video_area" style="padding: 0 0 4px 10px;">
					{if empty($videos)}
						<div class="grey_middle">{$lang.no_video_uploaded}</div>
					{else}
						{assign var='replace' value=`$smarty.ldelim`key`$smarty.rdelim`}
						<ul class="items" id="uploaded_video">
						{foreach from=$videos item='video'}		
							<li id="video_{$video.ID}">
								{if $video.Type == 'local'}
									<img class="peview_item" src="{$smarty.const.RL_FILES_URL}{$video.Preview}" alt="" />
								{else}
									<img class="peview_item" src="{$l_youtube_thumbnail|replace:$replace:$video.Preview}" alt="" />
								{/if}
								<img title="{$lang.remove}" src="{$rlTplBase}img/blank.gif" id="remove_{$video.ID}" class="remove_item" alt="" />
								
								<script type="text/javascript">//<![CDATA[
								/* load video handler */
								{if $video.Type == 'local'}
								{literal}
								
								$('#video_{/literal}{$video.ID}{literal} img.peview_item').click(function(){
									$.fancybox({
										padding			: 0,
										autoScale		: false,
										transitionIn	: 'none',
										transitionOut	: 'none',
										width			: {/literal}{$config.video_width}{literal},
										height			: {/literal}{$config.video_height}{literal},
										content			: '<a href="{/literal}{$smarty.const.RL_FILES_URL}{$video.Video}{literal}" style="display:block;width:{/literal}{$config.video_width}{literal}px;height:{/literal}{$config.video_height}{literal}px;" id="player"></a>',
										onComplete:		function(){
											flowplayer('player', {src: '{/literal}{$smarty.const.RL_LIBS_URL}{literal}player/flowplayer-3.2.7.swf', wmode: 'transparent'});
										},
										onClosed:		function(){
											$f().stop();
										}
									});
								});
								
								{/literal}
								{else}
								{literal}
								
								$('#video_{/literal}{$video.ID}{literal} img.peview_item').click(function(){
									$.fancybox({
										padding			: 0,
										autoScale		: false,
										transitionIn	: 'none',
										transitionOut	: 'none',
										width			: {/literal}{$config.video_width}{literal},
										height			: {/literal}{$config.video_height}{literal},
										href			: '{/literal}{$l_youtube_direct|replace:$replace:$video.Preview}{literal}',
										type			: 'swf',
										swf				: {
											wmode		: 'transparent',
											allowfullscreen	: true
										}
									});
								});
								
								{/literal}
								{/if}
								//]]>
								</script>
							</li>
						{/foreach}
						</ul>
					{/if}
				</div>
			</fieldset>
			<!-- video list end -->
			
			{rlHook name='apTplListingsVideo'}
			
			<script type="text/javascript">//<![CDATA[
			var video_listing_id = {$listing.ID};
			var sort_save = false;
			{literal}
					
			$('div#video_area ul.items').sortable({
				placeholder: 'hover',
				stop: function(event, obj){
					/* save sorting */
					var sort = '';
					var count = 0;
					$('div#video_area ul.items li').each(function(){
						var id = $(this).attr('id').split('_')[1];
						count++;
						var pos = $('div#video_area ul.items li').index($(this))+1;
						sort += id+','+pos+';';
					});
					
					if ( sort.length > 0 && count > 1 && sort_save != sort )
					{
						sort_save = sort;
						sort = rtrim(sort, ';');
						xajax_reorderVideo(video_listing_id, sort);
					}
				}
			});
					
			$(document).ready(function(){
				$('#type_selector').change(function(){
					var id = $(this).val().split('_')[0];
					$('div.upload').slideUp();
					$('div#'+id+'_video').slideDown('slow');
				});
				
				$('img.remove_item').click(function(){
					rlConfirm("{/literal}{$lang.delete_confirm}{literal}", 'xajax_deleteVideo', $(this).attr('id').split('_')[1]);
				});
			});
			
			{/literal}
			//]]>
			</script>
			
			{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_end.tpl'}
			
		{/if}
	
		<!-- add listing video end -->
		
	{elseif $smarty.get.action == 'view'}
		<style type="text/css">
		@import url("{$smarty.const.RL_LIBS_URL}jquery/fancybox/jquery.fancybox.css");
		</style>
			
		<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}player/flowplayer.js"></script>
		<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}jquery/jquery.fancybox.js"></script>
	
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_start.tpl'}
	
		<ul class="tabs">
			{foreach from=$tabs item='tab' name='tabsF'}
			<li lang="{$tab.key}" {if $smarty.foreach.tabsF.first}class="active"{/if}>{$tab.name}</li>
			{/foreach}
		</ul>
		
		<div class="tab_area listing listing_details">
			<table class="sTable">
			<tr>
				<td class="sidebar">
					{if $photos}
						<ul class="media">
						{foreach from=$photos item='photo' name='photosF'}
							<li {if $smarty.foreach.photosF.iteration%2 != 0}class="nl"{/if}>
								<a title="{$photo.Description}" rel="group" href="{$smarty.const.RL_URL_HOME}files/{$photo.Photo}"><img alt="" class="shadow" src="{$smarty.const.RL_URL_HOME}files/{$photo.Thumbnail}" /></a>
							</li>
						{/foreach}
						</ul>
					{/if}
					
					<ul class="statistics">
						{rlHook name='apTplListingBeforeStats'}
					
						<li><span class="name">{$lang.category}:</span> <a href="{$rlBase}index.php?controller=browse&amp;id={$listing_data.Category_ID}" target="_blank">{$listing_data.category_name}</a></li>
						{if $config.count_listing_visits}<li><span class="name">{$lang.shows}:</span> {$listing_data.Shows}</li>{/if}
						{if $config.display_posted_date}<li><span class="name">{$lang.posted}:</span> {$listing_data.Date|date_format:$smarty.const.RL_DATE_FORMAT}</li>{/if}
						
						{rlHook name='apTplListingAfterStats'}
					</ul>
				</td>
				<td valign="top">
					<!-- listing info -->
					{rlHook name='apListingDetailsPreFields'}
					
					{foreach from=$listing item='group'}
						{if $group.Group_ID}
							{assign var='hide' value=true}
							{if $group.Fields && $group.Display}
								{assign var='hide' value=false}
							{/if}
					
							{assign var='value_counter' value='0'}
							{foreach from=$group.Fields item='group_values' name='groupsF'}
								{if $group_values.value == '' || !$group_values.Details_page}
									{assign var='value_counter' value=$value_counter+1}
								{/if}
							{/foreach}
					
							{if !empty($group.Fields) && ($smarty.foreach.groupsF.total != $value_counter)}
								<fieldset class="light">
									<legend id="legend_group_{$group.ID}" class="up" onclick="fieldset_action('group_{$group.ID}');">{$group.name}</legend>
									<div id="group_{$group.ID}" class="tree">
								
										<table class="list">
										{foreach from=$group.Fields item='item' key='field' name='fListings'}
											{if !empty($item.value) && $item.Details_page}
												{include file='blocks'|cat:$smarty.const.RL_DS|cat:'field_out.tpl'}
											{/if}
										{/foreach}
										</table>
								
									</div>
								</fieldset>
							{/if}
						{else}
							{if $group.Fields}
								<table class="list">
								{assign value=$group.Fields.0 var='item'}
								{if !empty($item.value) && $item.Details_page}
									{include file='blocks'|cat:$smarty.const.RL_DS|cat:'field_out.tpl'}
								{/if}
								</table>
							{/if}
						{/if}
					{/foreach}
					<!-- listing info end -->
				</td>
			</tr>
			</table>
			
			<script type="text/javascript">
			{literal}
			
			$(document).ready(function(){
				$('ul.media a').fancybox({
					titlePosition: 'over',
					centerOnScroll: true,
					scrolling: 'yes'
				});
			});
			
			{/literal}
			</script>
		</div>
		
		<div class="tab_area seller listing_details hide">
			<table class="sTatic">
			<tr>
				<td valign="top" style="width: 170px;text-align: right;padding-right: 20px;">
					<a title="{$lang.visit_owner_page}" href="{$rlBase}index.php?controller=accounts&amp;action=view&amp;userid={$seller_info.ID}">
						<img style="display: inline;width: auto;" {if !empty($seller_info.Photo)}class="thumbnail"{/if} alt="{$lang.seller_thumbnail}" src="{if !empty($seller_info.Photo)}{$smarty.const.RL_URL_HOME}files/{$seller_info.Photo}{else}{$rlTplBase}img/no-account.png{/if}" />
					</a>
	
					<ul class="info">
						{if $config.messages_module}<li><input id="contact_owner" type="button" value="{$lang.contact_owner}" /></li>{/if}
						{if $seller_info.Own_page}
							<li><a target="_blank" title="{$lang.visit_owner_page}" href="{$seller_info.Personal_address}">{$lang.visit_owner_page}</a></li>
							<li><a title="{$lang.other_owner_listings}" href="{$rlBase}index.php?controller=accounts&amp;action=view&amp;userid={$seller_info.ID}#listings">{$lang.other_owner_listings}</a> <span class="counter">({$seller_info.Listings_count})</span></li>
						{/if}
					</ul>
				</td>
				<td valign="top">
					<div class="username">{$seller_info.Full_name}</div>
					{if $seller_info.Fields}
						<table class="list" style="margin-bottom: 20px;">
							<tr id="si_field_username">
								<td class="name">{$lang.username}:</td>
								<td class="value first">{$seller_info.Username}</td>
							</tr>
							<tr id="si_field_date">
								<td class="name">{$lang.join_date}:</td>
								<td class="value">{$seller_info.Date|date_format:$smarty.const.RL_DATE_FORMAT}</td>
							</tr>
							<tr id="si_field_email">
								<td class="name">{$lang.mail}:</td>
								<td class="value"><a href="mailto:{$seller_info.Mail}">{$seller_info.Mail}</a></td>
							</tr>
							<tr id="si_field_personal_address">
								<td class="name">{$lang.personal_address}:</td>
								<td class="value"><a target="_blank" href="{$seller_info.Personal_address}">{$seller_info.Personal_address}</a></td>
							</tr>
							
							{rlHook name='apTplListingsUserInfo'}
						</table>
						
						<table class="list">
						{foreach from=$seller_info.Fields item='item' name='sellerF'}
							{if !empty($item.value)}
							<tr id="si_field_{$item.Key}">
								<td class="name">{$item.name}:</td>
								<td class="value">{$item.value}</td>
							</tr>
							{/if}
						{/foreach}
						</table>
					{/if}
				</td>
			</tr>
			</table>
			
			<script type="text/javascript">
			var owner_id = {if $seller_info.ID}{$seller_info.ID}{else}false{/if}
			{literal}
			
			$(document).ready(function(){
				$('#contact_owner').click(function(){
					rlPrompt('{/literal}{$lang.contact_owner}{literal}', 'xajax_contactOwner', owner_id, true);
				});
			});
			
			{/literal}
			</script>
		</div>
		
		{if $videos}
		<div class="tab_area video listing_details hide">
			{assign var='replace' value=`$smarty.ldelim`key`$smarty.rdelim`}
			<ul class="media">
			{foreach from=$videos item='video'}
				<li id="video_{$video.ID}">
					{if $video.Type == 'local'}
						<img src="{$smarty.const.RL_FILES_URL}{$video.Embed}" alt="" />
					{else}
						<img src="{$l_youtube_thumbnail|replace:$replace:$video.Preview}" alt="" />
					{/if}
					
					<script type="text/javascript">//<![CDATA[
					{if $video.Type == 'local'}
					{literal}
					
					$('#video_{/literal}{$video.ID}{literal} img').click(function(){
						$.fancybox({
							padding			: 0,
							autoScale		: false,
							transitionIn	: 'none',
							transitionOut	: 'none',
							width			: {/literal}{$config.video_width}{literal},
							height			: {/literal}{$config.video_height}{literal},
							content			: '<a href="{/literal}{$smarty.const.RL_FILES_URL}{$video.Video}{literal}" style="display:block;width:{/literal}{$config.video_width}{literal}px;height:{/literal}{$config.video_height}{literal}px;" id="player"></a>',
							onComplete:		function(){
								flowplayer('player', {src: '{/literal}{$smarty.const.RL_LIBS_URL}{literal}player/flowplayer-3.2.7.swf', wmode: 'transparent'});
							},
							onClosed:		function(){
								$f().stop();
							}
						});
					});
					
					{/literal}
					{else}
					{literal}
					
					$('#video_{/literal}{$video.ID}{literal} img').click(function(){
						$.fancybox({
							padding			: 0,
							autoScale		: false,
							transitionIn	: 'none',
							transitionOut	: 'none',
							width			: {/literal}{$config.video_width}{literal},
							height			: {/literal}{$config.video_height}{literal},
							href			: '{/literal}{$l_youtube_direct|replace:$replace:$video.Preview}{literal}',
							type			: 'swf',
							swf				: {
								wmode		: 'transparent',
								allowfullscreen	: true
							}
						});
					});
					
					{/literal}
					{/if}
					//]]>
					</script>
				</li>
			{/foreach}
			</ul>
		</div>
		{/if}
		
		<div class="tab_area map listing_details hide">
			<div id="map" style="width: {if empty($config.map_width)}100%{else}{$config.map_width}px{/if}; height: {if empty($config.map_height)}300px{else}{$config.map_height}px{/if}"></div>
				
			<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=false{if $smarty.const.RL_LANG_CODE != '' && $smarty.const.RL_LANG_CODE != 'en'}&amp;language={$smarty.const.RL_LANG_CODE}{/if}"></script>
			<script type="text/javascript" src="http://www.google.com/uds/api?file=uds.js&amp;v=1.0&amp;key={$config.google_map_key}"></script>
			<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}jquery/jquery.flmap.js"></script>
			<script type="text/javascript">//<![CDATA[
			{literal}
			
			var map_exist = false;
			$(document).ready(function(){
				$('ul.tabs li[lang=map]').click(function(){
					if ( !map_exist )
					{
						$('#map').flMap({
							addresses: [
								[{/literal}'{if $location.direct}{$location.direct}{else}{$location.search}{/if}', '{$location.show}', '{if $location.direct}direct{else}geocoder{/if}'{literal}]
								//['Tamiami, FL', 'Tamiami!', 'geocoder']
								//['-25.363882,131.044922', 'Direct']
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
		
		{rlHook name='apTplListingsTabsArea'}
	
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_end.tpl'}
	{else}
	
		<script type="text/javascript">//<![CDATA[
		// collect plans
		var listing_plans = [
			{foreach from=$plans item='plan' name='plans_f'}
				['{$plan.ID}', '{$plan.name}']{if !$smarty.foreach.plans_f.last},{/if}
			{/foreach}
		];
		
		var ui = typeof( rl_ui ) != 'undefined' ? '&ui='+rl_ui : '';
		var ui_cat_id = typeof( cur_cat_id ) != 'undefined' ? '&cat_id='+cur_cat_id : '';
		
		/* read cookies filters */
		var cookies_filters = false;
		
		if ( readCookie('listings_sc') )
			cookies_filters = readCookie('listings_sc').split(',');
		
		{if isset($status)}
			cookies_filters = new Array();
			cookies_filters[0] = new Array('Status', '{$status}');
		{/if}
		
		{if $smarty.get.username}
			cookies_filters = new Array();
			cookies_filters[0] = new Array('Account', '{$smarty.get.username}');
		{/if}
		
		{if $smarty.get.account_type}
			cookies_filters = new Array();
			cookies_filters[0] = new Array('account_type', '{$smarty.get.account_type}');
		{/if}
		
		{if $smarty.get.listing_type}
			cookies_filters = new Array();
			cookies_filters[0] = new Array('Type', '{$smarty.get.listing_type}');
		{/if}
		
		{if $smarty.get.plan_id}
			cookies_filters = new Array();
			cookies_filters[0] = new Array('Plan_ID', '{$smarty.get.plan_id}');
		{/if}
		
		{rlHook name='apTplListingsRemoteFilter'}
		
		//]]>
		</script>
		
		<!-- listings grid create -->
		<div id="grid"></div>
		<script type="text/javascript">//<![CDATA[
		var mass_actions = [
			[lang['ext_activate'], 'activate'],
			[lang['ext_suspend'], 'approve'],
			{if 'delete'|in_array:$aRights.listings}[lang['ext_delete'], 'delete'],{/if}
			[lang['ext_move'], 'move'],
			[lang['ext_make_featured'], 'featured'],
			[lang['ext_annul_featured'], 'annul_featured']
		];
		
		{literal}
		
		var listingsGrid;
		$(document).ready(function(){
	
			listingsGrid = new gridObj({
				key: 'listings',
				id: 'grid',
				ajaxUrl: rlUrlHome + 'controllers/listings.inc.php?q=ext',
				defaultSortField: 'Date',
				defaultSortType: 'DESC',
				remoteSortable: false,
				checkbox: true,
				actions: mass_actions,
				filters: cookies_filters,
				filtersPrefix: true,
				title: lang['ext_listings_manager'],
				expander: true,
				expanderTpl: '<div style="margin: 0 5px 5px 83px"> \
					<table> \
					<tr> \
					<td>{thumbnail}</td> \
					<td>{fields}</td> \
					</tr> \
					</table> \
					<div> \
				',
				affectedObjects: '#make_featured,#move_area',
				fields: [
					{name: 'ID', mapping: 'ID', type: 'int'},
					{name: 'title', mapping: 'title', type: 'string'},
					{name: 'Username', mapping: 'Username', type: 'string'},
					{name: 'Account_ID', mapping: 'Account_ID', type: 'int'},
					{name: 'Type', mapping: 'Type'},
					{name: 'Type_key', mapping: 'Type_key'},
					{name: 'Plan_name', mapping: 'Plan_name'},
					{name: 'Plan_ID', mapping: 'Plan_name'},
					{name: 'Plan_info', mapping: 'Plan_info'},
					{name: 'Cat_title', mapping: 'Cat_title', type: 'string'},
					{name: 'Cat_ID', mapping: 'Cat_ID', type: 'int'},
					{name: 'Cat_custom', mapping: 'Cat_custom', type: 'int'},
					{name: 'Status', mapping: 'Status'},
					{name: 'Date', mapping: 'Date', type: 'date', dateFormat: 'Y-m-d H:i:s'},
					{name: 'Pay_date', mapping: 'Pay_date', type: 'date', dateFormat: 'Y-m-d H:i:s'},
					{name: 'thumbnail', mapping: 'thumbnail', type: 'string'},
					{name: 'fields', mapping: 'fields', type: 'string'},
					{name: 'data', mapping: 'data', type: 'string'},
					{name: 'Allow_photo', mapping: 'Allow_photo', type: 'int'},
					{name: 'Allow_video', mapping: 'Allow_video', type: 'int'}
				],
				columns: [
					{
						header: lang['ext_id'],
						dataIndex: 'ID',
						width: 40,
						fixed: true,
						id: 'rlExt_black_bold'
					},{
						header: lang['ext_title'],
						dataIndex: 'title',
						width: 23,
						renderer: function(val, ext, row){
							var out = '<a href="'+rlUrlHome+'index.php?controller=listings&action=view&id='+row.data.ID+'">'+val+'</a>';
							return out;
						}
					},{
						header: lang['ext_owner'],
						dataIndex: 'Username',
						width: 8,
						id: 'rlExt_item_bold',
						renderer: function(username, ext, row){
							return "<a target='_blank' ext:qtip='"+lang['ext_click_to_view_details']+"' href='"+rlUrlHome+"index.php?controller=accounts&action=view&userid="+row.data.Account_ID+"'>"+username+"</a>"
						}
					},{
						header: lang['ext_type'],
						dataIndex: 'Type',
						width: 8/*,
						renderer: function(val, obj, row){
							var out = '<a target="_blank" ext:qtip="'+lang['ext_click_to_view_details']+'" href="'+rlUrlHome+'index.php?controller=listing_types&action=edit&key='+row.data.Type_key+'">'+val+'</a>';
							return out;
						}*/
					},{
						header: lang['ext_category'],
						dataIndex: 'Cat_title',
						width: 9/*,
						renderer: function(val, obj, row){
							var link = row.data.Cat_custom ? rlUrlHome+'index.php?controller=custom_categories' : rlUrlHome+'index.php?controller=browse&id='+row.data.Cat_ID;
							var out = '<a target="_blank" ext:qtip="'+lang['ext_click_to_view_details']+'" href="'+link+'">'+val+'</a>';
							return out;
						}*/
					},{
						header: lang['ext_add_date'],
						dataIndex: 'Date',
						width: 10,
						hidden: true,
						renderer: Ext.util.Format.dateRenderer(rlDateFormat.replace(/%/g, '').replace('b', 'M'))
					},{
						header: lang['ext_payed'],
						dataIndex: 'Pay_date',
						width: 8,
						renderer: function(val){
							if (!val)
							{
								var date = '<span class="delete" ext:qtip="'+lang['ext_click_to_set_pay']+'">'+lang['ext_not_payed']+'</span>';
							}
							else
							{
								var date = Ext.util.Format.dateRenderer(rlDateFormat.replace(/%/g, '').replace('b', 'M'))(val);
								date = '<span class="build" ext:qtip="'+lang['ext_click_to_edit']+'">'+date+'</span>';
							}
							return date;
						},
						editor: new Ext.form.DateField({
							format: 'Y-m-d H:i:s'
						})
					},{
						header: lang['ext_plan'],
						dataIndex: 'Plan_ID',
						width: 11,
						editor: new Ext.form.ComboBox({
							store: listing_plans,
							mode: 'local',
							triggerAction: 'all'
						}),
						renderer: function (val, obj, row){
							if (val != '')
							{
								return '<img class="info" ext:qtip="'+row.data.Plan_info+'" alt="" src="'+rlUrlHome+'img/blank.gif" />&nbsp;&nbsp;<span ext:qtip="'+lang['ext_click_to_edit']+'">'+val+'</span>';
							}
							else
							{
								return '<span class="delete" ext:qtip="'+lang['ext_click_to_edit']+'" style="margin-left: 21px;">'+lang['ext_no_plan_set']+'</span>';
							}
						}
					},{
						header: lang['ext_status'],
						dataIndex: 'Status',
						width: 5,
						editor: new Ext.form.ComboBox({
							store: [
								['active', lang['ext_active']],
								['approval', lang['ext_approval']]
							],
							mode: 'local',
							typeAhead: true,
							triggerAction: 'all',
							selectOnFocus: true
						}),
						renderer: function(val) {
							return '<div ext:qtip="'+lang['ext_click_to_edit']+'">'+val+'</div>';
						}
					},{
						header: lang['ext_actions'],
						width: 120,
						fixed: true,
						dataIndex: 'data',
						sortable: false,
						resizeable: false,
						renderer: function(id, obj, row){
							var out = "<div style='text-align: right'>";
							var splitter = false;
			
							if ( cKey == 'browse' )
							{
								cKey = 'listings';
							}
							
							if ( rights[cKey].indexOf('edit') >= 0 )
							{
								if ( row.data.Allow_photo )
								{
									out += "<a href='"+rlUrlHome+"index.php?controller=listings&action=photos&id="+id+"'><img class='photo' ext:qtip='"+lang['ext_manage_photo']+"' src='"+rlUrlHome+"img/blank.gif' /></a>";
								}
								if ( row.data.Allow_video )
								{
									out += "<a href='"+rlUrlHome+"index.php?controller=listings&action=video&id="+id+"'><img class='video' ext:qtip='"+lang['ext_manage_video']+"' src='"+rlUrlHome+"img/blank.gif' /></a>";
								}
							}
							out += "<a href='"+rlUrlHome+"index.php?controller=listings&action=view&id="+id+"'><img class='view' ext:qtip='"+lang['ext_view_details']+"' src='"+rlUrlHome+"img/blank.gif' /></a>";
							if ( rights[cKey].indexOf('edit') >= 0 )
							{
								out += "<a href=\""+rlUrlHome+"index.php?controller=listings&action=edit&id="+id+ui+ui_cat_id+"\"><img class='edit' ext:qtip='"+lang['ext_edit']+"' src='"+rlUrlHome+"img/blank.gif' /></a>";
							}
							if ( rights[cKey].indexOf('delete') >= 0 )
							{
								out += "<img class='remove' ext:qtip='"+lang['ext_delete']+"' src='"+rlUrlHome+"img/blank.gif' onClick='rlPrompt( \""+lang['ext_notice_'+delete_mod]+"\",  \"xajax_deleteListing\", \""+id+"\" )' />";
							}
							out += "</div>";
							
							return out;
						}
					}
				]
			});
			
			{/literal}{rlHook name='apTplListingsGrid'}{literal}
			
			listingsGrid.init();
			grid.push(listingsGrid.grid);
			
			// actions listener
			listingsGrid.actionButton.addListener('click', function()
			{
				var sel_obj = listingsGrid.checkboxColumn.getSelections();
				var action = listingsGrid.actionsDropDown.getValue();
	
				if (!action)
				{
					return false;
				}
				
				for( var i = 0; i < sel_obj.length; i++ )
				{
					listingsGrid.ids += sel_obj[i].id;
					if ( sel_obj.length != i+1 )
					{
						listingsGrid.ids += '|';
					}
				}
				
				switch (action){
					case 'delete':
						Ext.MessageBox.confirm('Confirm', lang['ext_notice_'+delete_mod], function(btn){
							if ( btn == 'yes' )
							{
								xajax_massActions( listingsGrid.ids, action );
								listingsGrid.store.reload();
							}
						});
						
						break;
					
					case 'featured':
						$('#make_featured').fadeIn('slow');
						return false;
						
						break;
					
					case 'annul_featured':
						$('#mass_areas div.scroll').fadeOut('fast');
						Ext.MessageBox.confirm('Confirm', lang['ext_annul_featued_notice'], function(btn){
							if ( btn == 'yes' )
							{
								xajax_annulFeatured( listingsGrid.ids );
							}
						});
						return false;
						
					 	break;
					 	
					case 'move':
						$('#mass_areas div.scroll').fadeOut('fast');
						$('#move_area').fadeIn('slow');
						return false;
						
						break;
					
					default:
						$('#make_featured,#move_area').fadeOut('fast');
						xajax_massActions( listingsGrid.ids, action );
						listingsGrid.store.reload();
					
						break;
				}
	
				listingsGrid.checkboxColumn.clearSelections();
				listingsGrid.actionsDropDown.setVisible(false);
				listingsGrid.actionButton.setVisible(false);
			});
			
			listingsGrid.grid.addListener('afteredit', function(editEvent)
			{
				if ( editEvent.field == 'Plan_ID' )
				{
					listingsGrid.reload();
				}
			});
			
		});
		{/literal}
		//]]>
		</script>
		
		{rlHook name='apTplListingsMiddle'}
		
		<div id="mass_areas">
		
			<!-- make featured -->
			<div id="make_featured" style="margin-top: 10px;" class="hide scroll">
				
				{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_start.tpl' block_caption=$lang.make_featured}
				<table class="form">
				<tr>
					<td class="name w130"><span class="red">*</span>{$lang.plan}</td>
					<td class="field">
						<select id="featured_plan">
							<option value="0">{$lang.select}</option>
							{foreach from=$featured_plans item='featured_plan'}
								<option value="{$featured_plan.ID}">{$featured_plan.name}</option>
							{/foreach}
						</select>
					</td>
				</tr>
				<tr>
					<td class="w130"></td>
					<td class="field">
						<input type="button" onclick="xajax_makeFeatured(listingsGrid.ids, $('#featured_plan').val());" value="{$lang.save}" />
						<a class="cancel" href="javascript:void(0)" onclick="$('#make_featured').fadeOut();">{$lang.cancel}</a>
					</td>
				</tr>
				</table>
				{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_end.tpl'}
				
			</div>
			<!-- make featured end -->
			
			<!-- move listing block -->
				<div id="move_area" style="margin-top: 10px;" class="hide scroll">
				
				{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_start.tpl' block_caption=$lang.move_listings}
				<table class="form">
				<tr>
					<td class="name w130"><span class="red">*</span>{$lang.category}</td>
					<td class="field">
						{foreach from=$sections item='section'}
							<fieldset class="light">
								<legend id="legend_move_{$section.ID}" class="up" onclick="fieldset_action('move_{$section.ID}');">{$section.name}</legend>
								<div id="move_{$section.ID}" class="tree">
									{if !empty($section.Categories)}
										{include file='blocks'|cat:$smarty.const.RL_DS|cat:'category_level.tpl' categories=$section.Categories first=true postfix='move'}
									{else}
										<div style="padding: 0 0 8px 10px;" class="blue_middle">{$lang.no_items_in_sections}</div>
									{/if}
								</div>
							</fieldset>
						{/foreach}
					</td>
				</tr>
				<tr>
					<td class="w130"></td>
					<td class="field">
						<input type="button" onclick="xajax_moveListing(listingsGrid.ids, $('#move_cat_id').val());" value="{$lang.move}" />
						<a class="cancel" href="javascript:void(0)" onclick="$('#move_area').fadeOut();">{$lang.cancel}</a>
					</td>
				</tr>
				</table>
				{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_end.tpl'}
				
				<script type="text/javascript">
				{literal}
				
				$(document).ready(function(){
					flynax.treeLoadLevel('', '', 'div#move_area');
				});
				
				function cat_chooser(id)
				{
					$('#move_cat_id').val(id);
				}
				
				{/literal}
				</script>
				
				<input type="hidden" id="move_cat_id" value="" />
			</div>
			<!-- move listing block end -->
			
		</div>
		
		{rlHook name='apTplListingsBottom'}
	
	{/if}

{/if}

<!-- listings tpl end -->