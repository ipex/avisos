<!-- listing types tpl -->

<!-- navigation bar -->
<div id="nav_bar">
	{rlHook name='apTplListingTypesNavBar'}

	{if $aRights.$cKey.add && !$smarty.get.action}
		<a href="{$rlBaseC}action=add" class="button_bar"><span class="left"></span><span class="center_add">{$lang.add_type}</span><span class="right"></span></a>
	{/if}
	<a href="{$rlBase}index.php?controller={$smarty.get.controller}" class="button_bar"><span class="left"></span><span class="center_list">{$lang.types_list}</span><span class="right"></span></a>
</div>
<!-- navigation bar end -->

{if $smarty.get.action}

	{assign var='sPost' value=$smarty.post}

	<!-- add new plans -->
	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_start.tpl'}
	<form action="{$rlBaseC}action={if $smarty.get.action == 'add'}add{elseif $smarty.get.action == 'edit'}edit&amp;key={$smarty.get.key}{/if}" method="post">
	<input type="hidden" name="submit" value="1" />
	{if $smarty.get.action == 'edit'}
		<input type="hidden" name="fromPost" value="1" />
	{/if}
	<table class="form">
	<tr>
		<td class="name"><span class="red">*</span>{$lang.key}</td>
		<td class="field">
			<input {if $smarty.get.action == 'edit'}readonly="readonly"{/if} class="{if $smarty.get.action == 'edit'}disabled{/if}" name="key" type="text" style="width: 150px;" value="{$sPost.key}" maxlength="30" />
		</td>
	</tr>
	
	<tr>
		<td class="name">
			<span class="red">*</span>{$lang.name}
		</td>
		<td class="field">
			{if $allLangs|@count > 1}
				<ul class="tabs">
					{foreach from=$allLangs item='language' name='langF'}
					<li lang="{$language.Code}" {if $smarty.foreach.langF.first}class="active"{/if}>{$language.name}</li>
					{/foreach}
				</ul>
			{/if}
			
			{foreach from=$allLangs item='language' name='langF'}
				{if $allLangs|@count > 1}<div class="tab_area{if !$smarty.foreach.langF.first} hide{/if} {$language.Code}">{/if}
					<input type="text" name="name[{$language.Code}]" value="{$sPost.name[$language.Code]}" class="w250" maxlength="50" /> <span class="field_description_noicon">
				{if $allLangs|@count > 1}{$lang.name} (<b>{$language.name}</b>)</span>
					</div>
				{/if}
			{/foreach}
		</td>
	</tr>
	
	<tr>
		<td class="name">{$lang.individual_page}</td>
		<td class="field">
			{assign var='checkbox_field' value='page'}
			
			{if $sPost.$checkbox_field == '1'}
				{assign var=$checkbox_field|cat:'_yes' value='checked="checked"'}
			{elseif $sPost.$checkbox_field == '0'}
				{assign var=$checkbox_field|cat:'_no' value='checked="checked"'}
			{else}
				{assign var=$checkbox_field|cat:'_yes' value='checked="checked"'}
			{/if}
			
			<table>
			<tr>
				<td>
					<input {$page_yes} type="radio" id="{$checkbox_field}_yes" name="{$checkbox_field}" value="1" /> <label for="{$checkbox_field}_yes">{$lang.yes}</label>
					<input {$page_no} type="radio" id="{$checkbox_field}_no" name="{$checkbox_field}" value="0" /> <label for="{$checkbox_field}_no">{$lang.no}</label>
				</td>
				{if $smarty.get.action == 'edit' && $sPost.$checkbox_field}
					{assign var='replace' value='<a target="_blank" class="static" href="'|cat:$rlBase|cat:'index.php?controller=pages&amp;action=edit&amp;page=lt_'|cat:$smarty.get.key|cat:'">$1</a>'}
					<td><span class="field_description">{$lang.individual_page_hint|regex_replace:'/\[(.*)\]/':$replace}</span></td>
				{/if}
			</tr>
			</table>
		</td>
	</tr>
	
	<tr>
		<td class="name">{$lang.apply_pictures}</td>
		<td class="field">
			{assign var='checkbox_field' value='photo'}
			
			{if $sPost.$checkbox_field == '1'}
				{assign var=$checkbox_field|cat:'_yes' value='checked="checked"'}
			{elseif $sPost.$checkbox_field == '0'}
				{assign var=$checkbox_field|cat:'_no' value='checked="checked"'}
			{else}
				{assign var=$checkbox_field|cat:'_yes' value='checked="checked"'}
			{/if}
			
			<input {$photo_yes} type="radio" id="{$checkbox_field}_yes" name="{$checkbox_field}" value="1" /> <label for="{$checkbox_field}_yes">{$lang.yes}</label>
			<input {$photo_no} type="radio" id="{$checkbox_field}_no" name="{$checkbox_field}" value="0" /> <label for="{$checkbox_field}_no">{$lang.no}</label>
		</td>
	</tr>
	
	<tr>
		<td class="name">{$lang.apply_video}</td>
		<td class="field">
			{assign var='checkbox_field' value='video'}
			
			{if $sPost.$checkbox_field == '1'}
				{assign var=$checkbox_field|cat:'_yes' value='checked="checked"'}
			{elseif $sPost.$checkbox_field == '0'}
				{assign var=$checkbox_field|cat:'_no' value='checked="checked"'}
			{else}
				{assign var=$checkbox_field|cat:'_yes' value='checked="checked"'}
			{/if}
			
			<input {$video_yes} type="radio" id="{$checkbox_field}_yes" name="{$checkbox_field}" value="1" /> <label for="{$checkbox_field}_yes">{$lang.yes}</label>
			<input {$video_no} type="radio" id="{$checkbox_field}_no" name="{$checkbox_field}" value="0" /> <label for="{$checkbox_field}_no">{$lang.no}</label>
		</td>
	</tr>
	
	<tr>
		<td class="name">{$lang.admin_only}</td>
		<td class="field">
			{assign var='checkbox_field' value='admin'}
			
			{if $sPost.$checkbox_field == '1'}
				{assign var=$checkbox_field|cat:'_yes' value='checked="checked"'}
			{elseif $sPost.$checkbox_field == '0'}
				{assign var=$checkbox_field|cat:'_no' value='checked="checked"'}
			{else}
				{assign var=$checkbox_field|cat:'_no' value='checked="checked"'}
			{/if}
			
			<table>
			<tr>
				<td>
					<input {$admin_yes} type="radio" id="{$checkbox_field}_yes" name="{$checkbox_field}" value="1" /> <label for="{$checkbox_field}_yes">{$lang.yes}</label>
					<input {$admin_no} type="radio" id="{$checkbox_field}_no" name="{$checkbox_field}" value="0" /> <label for="{$checkbox_field}_no">{$lang.no}</label>
				</td>
				{if $smarty.get.action == 'edit' && !$sPost.admin}
					{assign var='replace' value='<a target="_blank" class="static" href="'|cat:$rlBase|cat:'index.php?controller=pages&amp;action=edit&amp;page=my_'|cat:$smarty.get.key|cat:'">$1</a>'}
					<td><span class="field_description">{$lang.my_listings_page_hint|regex_replace:'/\[(.*)\]/':$replace}</span></td>
				{/if}
			</tr>
			</table>
		</td>
	</tr>
	
	{rlHook name='apTplListingTypesForm'}
	
	</table>
	
	<div class="individual_page_item">
	<table class="form">
	<tr>
		<td class="name">{$lang.category_settings}</td>
		<td class="field">
			<fieldset class="light">
				<legend id="legend_cat_settings" class="up" onclick="fieldset_action('cat_settings');">{$lang.settings}</legend>
				<div id="cat_settings">
					
					<table class="form wide">
					<tr>
						<td class="divider first" colspan="3"><div class="inner">{$lang.common}</div></td>
					</tr>
					<tr>
						<td class="name">{$lang.general_category}</td>
						<td class="field">
							<select name="general_cat" {if $smarty.get.action == 'add'}disabled="disabled" class="disabled"{/if}>
								<option value="">{if $category_titles|@count == 0}{$lang.no_categories_available}{else}{$lang.select}{/if}</option>
								{foreach from=$category_titles item='c_title'}
									<option value="{$c_title.ID}" {if $c_title.ID == $sPost.general_cat}selected="selected"{/if}>{$lang[$c_title.pName]}</option>
								{/foreach}
							</select>
							<span class="field_description">
								{if $smarty.get.action == 'add'}
									{$lang.general_category_hint}
								{else}
									{assign var='replace' value='<a target="_blank" class="static" href="'|cat:$rlBase|cat:'index.php?controller=categories&amp;type='|cat:$smarty.get.key|cat:'">$1</a>'}
									{assign var='sReplace' value=`$smarty.ldelim`name`$smarty.rdelim`}
									{$lang.general_category_manage_hint|regex_replace:'/\[(.*)\]/':$replace|replace:$sReplace:$sPost.name[$smarty.const.RL_LANG_CODE]}
								{/if}
							</span>
						</td>
					</tr>
					
					<tr>
						<td class="name"><span class="red">*</span>{$lang.position}</td>
						<td class="field">
							<select name="cat_position">
								<option value="">{$lang.select}</option>
								{foreach from=$cat_positions item='cat_pos'}
								<option value="{$cat_pos.key}" {if $cat_pos.key == $sPost.cat_position}selected="selected"{/if}>{$cat_pos.name}</option>
								{/foreach}
							</select>
						</td>
					</tr>
					
					<tr>
						<td class="name"><span class="red">*</span>{$lang.number_of_columns}</td>
						<td class="field">
							<input style="width: 30px;" type="text" class="numeric" name="cat_columns_number" value="{$sPost.cat_columns_number}" />
						</td>
					</tr>
					
					<tr>
						<td class="name">{$lang.display_first}</td>
						<td class="field">
							<input style="width: 30px;" type="text" class="numeric" name="cat_visible_number" value="{$sPost.cat_visible_number}" />
							<span class="field_description_noicon">{$lang.categories}</span>
							<span class="field_description">({$lang.display_first_hint})</span>
						</td>
					</tr>
					
					<tr>
						<td class="name">{$lang.display_counter}</td>
						<td class="field">
							{assign var='checkbox_field' value='display_counter'}
			
							{if $sPost.$checkbox_field == '1'}
								{assign var=$checkbox_field|cat:'_yes' value='checked="checked"'}
							{elseif $sPost.$checkbox_field == '0'}
								{assign var=$checkbox_field|cat:'_no' value='checked="checked"'}
							{else}
								{assign var=$checkbox_field|cat:'_yes' value='checked="checked"'}
							{/if}
							
							<input {$display_counter_yes} type="radio" id="{$checkbox_field}_yes" name="{$checkbox_field}" value="1" /> <label for="{$checkbox_field}_yes">{$lang.yes}</label>
							<input {$display_counter_no} type="radio" id="{$checkbox_field}_no" name="{$checkbox_field}" value="0" /> <label for="{$checkbox_field}_no">{$lang.no}</label>
						</td>
					</tr>
					
					<tr>
						<td class="name">{$lang.display_postfix}</td>
						<td class="field">
							{assign var='checkbox_field' value='html_postfix'}
			
							{if $sPost.$checkbox_field == '1'}
								{assign var=$checkbox_field|cat:'_yes' value='checked="checked"'}
							{elseif $sPost.$checkbox_field == '0'}
								{assign var=$checkbox_field|cat:'_no' value='checked="checked"'}
							{else}
								{assign var=$checkbox_field|cat:'_yes' value='checked="checked"'}
							{/if}
							
							<input {$html_postfix_yes} type="radio" id="{$checkbox_field}_yes" name="{$checkbox_field}" value="1" /> <label for="{$checkbox_field}_yes">{$lang.yes}</label>
							<input {$html_postfix_no} type="radio" id="{$checkbox_field}_no" name="{$checkbox_field}" value="0" /> <label for="{$checkbox_field}_no">{$lang.no}</label>
						</td>
					</tr>
					
					<tr>
						<td class="name">{$lang.category_order}</td>
						<td class="field">
							<select name="category_order">
								{foreach from=$category_order_types item='cat_order_type'}
								<option value="{$cat_order_type.key}" {if $cat_order_type.key == $sPost.category_order}selected="selected"{/if}>{$cat_order_type.name}</option>
								{/foreach}
							</select>
						</td>
					</tr>
					
					<tr>
						<td class="name">{$lang.allow_subcategories}</td>
						<td class="field">
							{assign var='checkbox_field' value='allow_subcategories'}
			
							{if $sPost.$checkbox_field == '1'}
								{assign var=$checkbox_field|cat:'_yes' value='checked="checked"'}
							{elseif $sPost.$checkbox_field == '0'}
								{assign var=$checkbox_field|cat:'_no' value='checked="checked"'}
							{else}
								{assign var=$checkbox_field|cat:'_no' value='checked="checked"'}
							{/if}
							
							<input {$allow_subcategories_yes} type="radio" id="{$checkbox_field}_yes" name="{$checkbox_field}" value="1" /> <label for="{$checkbox_field}_yes">{$lang.yes}</label>
							<input {$allow_subcategories_no} type="radio" id="{$checkbox_field}_no" name="{$checkbox_field}" value="0" /> <label for="{$checkbox_field}_no">{$lang.no}</label>
						</td>
					</tr>
					
					<tr>
						<td class="name">{$lang.display_subcategories}</td>
						<td class="field">
							{assign var='checkbox_field' value='display_subcategories'}
			
							{if $sPost.$checkbox_field == '1'}
								{assign var=$checkbox_field|cat:'_yes' value='checked="checked"'}
							{elseif $sPost.$checkbox_field == '0'}
								{assign var=$checkbox_field|cat:'_no' value='checked="checked"'}
							{else}
								{assign var=$checkbox_field|cat:'_yes' value='checked="checked"'}
							{/if}
							
							<input {$display_subcategories_yes} type="radio" id="{$checkbox_field}_yes" name="{$checkbox_field}" value="1" /> <label for="{$checkbox_field}_yes">{$lang.yes}</label>
							<input {$display_subcategories_no} type="radio" id="{$checkbox_field}_no" name="{$checkbox_field}" value="0" /> <label for="{$checkbox_field}_no">{$lang.no}</label>
						</td>
					</tr>
					
					<tr>
						<td class="name">{$lang.category_scrolling_in_box}</td>
						<td class="field">
							{assign var='checkbox_field' value='scrolling_in_box'}
			
							{if $sPost.$checkbox_field == '1'}
								{assign var=$checkbox_field|cat:'_yes' value='checked="checked"'}
							{elseif $sPost.$checkbox_field == '0'}
								{assign var=$checkbox_field|cat:'_no' value='checked="checked"'}
							{else}
								{assign var=$checkbox_field|cat:'_yes' value='checked="checked"'}
							{/if}
							
							<input {$scrolling_in_box_yes} type="radio" id="{$checkbox_field}_yes" name="{$checkbox_field}" value="1" /> <label for="{$checkbox_field}_yes">{$lang.yes}</label>
							<input {$scrolling_in_box_no} type="radio" id="{$checkbox_field}_no" name="{$checkbox_field}" value="0" /> <label for="{$checkbox_field}_no">{$lang.no}</label>
						</td>
					</tr>
					
					<tr>
						<td class="name">{$lang.subcategories_number}</td>
						<td class="field">
							<input style="width: 30px;" type="text" class="numeric" name="subcategories_number" value="{$sPost.subcategories_number}" />
							<span class="field_description">{$lang.subcategories_number_hint}</span>
						</td>
					</tr>
					
					{rlHook name='apTplListingTypesFormCategory'}
					</table>
					
					<table class="form wide">
					<tr>
						<td class="divider" colspan="3"><div class="inner">{$lang.additional_cat_block}</div></td>
					</tr>
					<tr>
						<td class="name">{$lang.show_on_pages}</td>
						<td class="field">
							{assign var='bPages' value=$sPost.ablock_pages}
							<table class="sTable" id="pagas_checkboxes">
							<tr>
								<td valign="top">
								{foreach from=$pages item='page' name='pagesF'}
								{assign var='pId' value=$page.ID}
								<div style="padding: 2px 8px 2px 0;">
									<input class="checkbox" {if $pId|in_array:$bPages}checked="checked"{/if} id="page_{$page.ID}" type="checkbox" name="ablock_pages[{$page.ID}]" value="{$page.ID}" /> <label class="cLabel" for="page_{$page.ID}">{$page.name}</label>
								</div>
								{assign var='perCol' value=$smarty.foreach.pagesF.total/3|ceil}
			
								{if $smarty.foreach.pagesF.iteration % $perCol == 0}
									</td>
									<td valign="top">
								{/if}
								{/foreach}
								</td>
							</tr>
							</table>
							
							<script type="text/javascript">
							{literal}
							
							$(document).ready(function(){
								$('table#pagas_checkboxes input').click(function(){
									pagesTracker();
								});
								
								pagesTracker();
							});
							
							var pagesTracker = function(){
								$('.position_star').hide();
								
								$('table#pagas_checkboxes input').each(function(){
									if ( $(this).attr('checked') )
									{
										$('.position_star').show();
										return;
									}
								});
							}
							
							{/literal}
							</script>
						</td>
					</tr>
					
					<tr>
						<td class="name"><span class="red hide position_star">*</span>{$lang.position}</td>
						<td class="field">
							<select name="ablock_position">
								<option value="">{$lang.select}</option>
								{foreach from=$l_block_sides item='block_side' name='sides_f' key='sKey'}
								<option value="{$sKey}" {if $sKey == $sPost.ablock_position}selected="selected"{/if}>{$block_side}</option>
								{/foreach}
							</select>
							
							{if $smarty.get.action == 'edit' && !empty($sPost.ablock_pages.0)}
								{assign var='replace' value='<a target="_blank" class="static" href="'|cat:$rlBase|cat:'index.php?controller=blocks&amp;action=edit&amp;block=ltcb_'|cat:$smarty.get.key|cat:'">$1</a>'}
								<span class="field_description">{$lang.show_on_pages_hint|regex_replace:'/\[(.*)\]/':$replace}</span>
							{/if}
						</td>
					</tr>
						
					<tr>
						<td class="name"><span class="red hide position_star">*</span>{$lang.number_of_columns}</td>
						<td class="field">
							<input style="width: 30px;" type="text" class="numeric" name="ablock_columns_number" value="{$sPost.ablock_columns_number}" />
						</td>
					</tr>
					
					<tr>
						<td class="name">{$lang.display_first}</td>
						<td class="field">
							<input style="width: 30px;" type="text" class="numeric" name="ablock_visible_number" value="{$sPost.ablock_visible_number}" />
							<span class="field_description_noicon">{$lang.categories}</span>
							<span class="field_description">({$lang.display_first_hint})</span>
						</td>
					</tr>
					
					<tr>
						<td class="name">{$lang.category_scrolling_in_box}</td>
						<td class="field">
							{assign var='checkbox_field' value='ablock_scrolling_in_box'}
			
							{if $sPost.$checkbox_field == '1'}
								{assign var=$checkbox_field|cat:'_yes' value='checked="checked"'}
							{elseif $sPost.$checkbox_field == '0'}
								{assign var=$checkbox_field|cat:'_no' value='checked="checked"'}
							{else}
								{assign var=$checkbox_field|cat:'_yes' value='checked="checked"'}
							{/if}
							
							<input {$ablock_scrolling_in_box_yes} type="radio" id="{$checkbox_field}_yes" name="{$checkbox_field}" value="1" /> <label for="{$checkbox_field}_yes">{$lang.yes}</label>
							<input {$ablock_scrolling_in_box_no} type="radio" id="{$checkbox_field}_no" name="{$checkbox_field}" value="0" /> <label for="{$checkbox_field}_no">{$lang.no}</label>
						</td>
					</tr>
					
					<tr>
						<td class="name">{$lang.display_subcategories}</td>
						<td class="field">
							{assign var='checkbox_field' value='ablock_display_subcategories'}
			
							{if $sPost.$checkbox_field == '1'}
								{assign var=$checkbox_field|cat:'_yes' value='checked="checked"'}
							{elseif $sPost.$checkbox_field == '0'}
								{assign var=$checkbox_field|cat:'_no' value='checked="checked"'}
							{else}
								{assign var=$checkbox_field|cat:'_yes' value='checked="checked"'}
							{/if}
							
							<input {$ablock_display_subcategories_yes} type="radio" id="{$checkbox_field}_yes" name="{$checkbox_field}" value="1" /> <label for="{$checkbox_field}_yes">{$lang.yes}</label>
							<input {$ablock_display_subcategories_no} type="radio" id="{$checkbox_field}_no" name="{$checkbox_field}" value="0" /> <label for="{$checkbox_field}_no">{$lang.no}</label>
						</td>
					</tr>
					
					<tr>
						<td class="name">{$lang.subcategories_number}</td>
						<td class="field">
							<input style="width: 30px;" type="text" class="numeric" name="ablock_subcategories_number" value="{$sPost.ablock_subcategories_number}" />
							<span class="field_description">{$lang.subcategories_number_hint}</span>
						</td>
					</tr>
					
					{rlHook name='apTplListingTypesFormCategoryAddBlock'}
					</table>
				
				</div>
			</fieldset>
		</td>
	</tr>
	
	<tr>
		<td class="name">{$lang.search_settings}</td>
		<td class="field">
			<fieldset class="light">
				<legend id="legend_search_settings" class="up" onclick="fieldset_action('search_settings');">{$lang.settings}</legend>
				<div id="search_settings">
				
					<table class="form wide">
					<tr>
						<td class="name">{$lang.search_form}</td>
						<td valign="top" class="field">
							{assign var='checkbox_field' value='search_form'}
			
							{if $sPost.$checkbox_field == '1'}
								{assign var=$checkbox_field|cat:'_yes' value='checked="checked"'}
							{elseif $sPost.$checkbox_field == '0'}
								{assign var=$checkbox_field|cat:'_no' value='checked="checked"'}
							{else}
								{assign var=$checkbox_field|cat:'_yes' value='checked="checked"'}
							{/if}
							
							<table>
							<tr>
								<td>
									<input {$search_form_yes} type="radio" id="{$checkbox_field}_yes" name="{$checkbox_field}" value="1" /> <label for="{$checkbox_field}_yes">{$lang.yes}</label>
									<input {$search_form_no} type="radio" id="{$checkbox_field}_no" name="{$checkbox_field}" value="0" /> <label for="{$checkbox_field}_no">{$lang.no}</label>
								</td>
								{if $smarty.get.action == 'edit' && $sPost.search_form}
									{assign var='replace' value='<a target="_blank" class="static" href="'|cat:$rlBase|cat:'index.php?controller=search_forms&amp;action=edit&amp;form='|cat:$smarty.get.key|cat:'_quick">$1</a>'}
									<td><span class="field_description">{$lang.search_form_hint|regex_replace:'/\[(.*)\]/':$replace}</span></td>
								{/if}
							</tr>
							</table>
							
							<div style="padding: 6px 0 4px 1px;"><label><input type="checkbox" {if $sPost.search_home}checked="checked"{/if} name="search_home" value="1" /> {$lang.search_form_on_home}</label></div>
							<div style="padding: 6px 0 4px 1px;"><label><input type="checkbox" {if $sPost.search_page}checked="checked"{/if} name="search_page" value="1" /> {$lang.search_form_on_search}</label></div>
							<div style="padding: 4px 0 4px 1px;"><label><input type="checkbox" {if $sPost.search_multi_categories}checked="checked"{/if} name="search_multi_categories" value="1" /> {$lang.search_multi_categories}</label></div>
						</td>
					</tr>
					</table>
					
					<div id="multi_categories_levels">
					<table class="form wide">
					<tr>
						<td class="name">{$lang.number_of_levels}</td>
						<td class="field">
							<input style="width: 30px;" type="text" class="numeric" name="search_multicat_levels" value="{$sPost.search_multicat_levels}" />
							<span class="field_description">{$lang.search_multicat_levels_hint}</span>
						</td>
					</tr>
					</table>
					</div>
					
					<table class="form wide">
					{*if $smarty.get.action == 'edit'*}
					<tr>
						<td class="name">{$lang.advanced_search}</td>
						<td class="field">
							{assign var='checkbox_field' value='advanced_search'}
			
							{if $sPost.$checkbox_field == '1'}
								{assign var=$checkbox_field|cat:'_yes' value='checked="checked"'}
							{elseif $sPost.$checkbox_field == '0'}
								{assign var=$checkbox_field|cat:'_no' value='checked="checked"'}
							{else}
								{assign var=$checkbox_field|cat:'_yes' value='checked="checked"'}
							{/if}
							
							<table>
							<tr>
								<td>
									<input {$advanced_search_yes} type="radio" id="{$checkbox_field}_yes" name="{$checkbox_field}" value="1" /> <label for="{$checkbox_field}_yes">{$lang.yes}</label>
									<input {$advanced_search_no} type="radio" id="{$checkbox_field}_no" name="{$checkbox_field}" value="0" /> <label for="{$checkbox_field}_no">{$lang.no}</label>
								</td>
								{if $smarty.get.action == 'edit' && $sPost.advanced_search}
									{assign var='replace' value='<a target="_blank" class="static" href="'|cat:$rlBase|cat:'index.php?controller=search_forms&amp;action=edit&amp;form='|cat:$smarty.get.key|cat:'_advanced">$1</a>'}
									<td><span class="field_description">{$lang.search_form_hint|regex_replace:'/\[(.*)\]/':$replace}</span></td>
								{/if}
							</tr>
							</table>
						</td>
					</tr>
					
					<tr>
						<td class="name">{$lang.submit_method}</td>
						<td class="field">
							<select name="refine_search_type">
								{foreach from=$refine_search_types item='refine_search_type'}
								<option value="{$refine_search_type.key}" {if $refine_search_type.key == $sPost.refine_search_type}selected="selected"{/if}>{$refine_search_type.name}</option>
								{/foreach}
							</select>
						</td>
					</tr>
					
					<tr>
						<td class="name">{$lang.display_form_in}</td>
						<td class="field">
							<select name="display_form_in">
								{foreach from=$search_form_types item='search_form_type'}
								<option value="{$search_form_type.key}" {if $search_form_type.key == $sPost.display_form_in}selected="selected"{/if}>{$search_form_type.name}</option>
								{/foreach}
							</select>
						</td>
					</tr>
					
					{rlHook name='apTplListingTypesFormSearch'}
					</table>
					
					<script type="text/javascript">
					{literal}
					
					$(document).ready(function(){
						$('input[name=search_form]').click(function(){
							searchFormTracker();
						});
						
						searchFormTracker();
					});
					
					var searchFormTracker =  function()
					{
						if ( $('input[name=search_form]:checked').val() == '1' )
						{
							$('input[name=advanced_search]').attr('disabled', false);
							$('select[name=display_form_in]').attr('disabled', false);
							$('select[name=display_form_in]').parent().attr('class', 'selector');
							$('select[name=refine_search_type]').attr('disabled', false);
							$('select[name=refine_search_type]').parent().attr('class', 'selector');
							$('input[name=search_home]').attr('checked', true).attr('disabled', false);
							$('input[name=search_page]').attr('checked', true).attr('disabled', false);
						}
						else
						{
							$('input[name=advanced_search]').attr('disabled', true);
							$('select[name=display_form_in]').attr('disabled', true);
							$('select[name=display_form_in]').parent().attr('class', 'selector_disabled');
							$('select[name=refine_search_type]').attr('disabled', true);
							$('select[name=refine_search_type]').parent().attr('class', 'selector_disabled');
							$('input[name=search_home]').attr('checked', false).attr('disabled', true);
							$('input[name=search_page]').attr('checked', false).attr('disabled', true);
						}
					}
					
					{/literal}
					</script>
					
				</div>
			</fieldset>
		</td>
	</tr>
	</table>
	</div>
	
	<table class="form">
	<tr>
		<td class="name">{$lang.featured_settings}</td>
		<td class="field">
			<fieldset class="light">
				<legend id="legend_featured_settings" class="up" onclick="fieldset_action('featured_settings');">{$lang.settings}</legend>
				<div id="featured_settings">
				
					<table class="form wide">
					<tr>
						<td class="name">{$lang.featured_blocks}</td>
						<td class="field">
							{assign var='checkbox_field' value='featured_blocks'}
			
							{if $sPost.$checkbox_field == '1'}
								{assign var=$checkbox_field|cat:'_yes' value='checked="checked"'}
							{elseif $sPost.$checkbox_field == '0'}
								{assign var=$checkbox_field|cat:'_no' value='checked="checked"'}
							{else}
								{assign var=$checkbox_field|cat:'_yes' value='checked="checked"'}
							{/if}
							
							<table>
							<tr>
								<td>
									<input {$featured_blocks_yes} type="radio" id="{$checkbox_field}_yes" name="{$checkbox_field}" value="1" /> <label for="{$checkbox_field}_yes">{$lang.yes}</label>
									<input {$featured_blocks_no} type="radio" id="{$checkbox_field}_no" name="{$checkbox_field}" value="0" /> <label for="{$checkbox_field}_no">{$lang.no}</label>
								</td>
								{if $smarty.get.action == 'edit' && $sPost.$checkbox_field}
									{assign var='replace' value='<a target="_blank" class="static" href="'|cat:$rlBase|cat:'index.php?controller=blocks&amp;action=edit&amp;block=ltfb_'|cat:$smarty.get.key|cat:'">$1</a>'}
									<td><span class="field_description">{$lang.featured_blocks_hint|regex_replace:'/\[(.*)\]/':$replace}</span></td>
								{/if}
							</tr>
							</table>
						</td>
					</tr>
					</table>
					
					<div class="individual_page_item">
						<table class="form wide">
						<tr>
							<td class="name">{$lang.random_featured}</td>
							<td class="field">
								{assign var='checkbox_field' value='random_featured'}
				
								{if $sPost.$checkbox_field == '1'}
									{assign var=$checkbox_field|cat:'_yes' value='checked="checked"'}
								{elseif $sPost.$checkbox_field == '0'}
									{assign var=$checkbox_field|cat:'_no' value='checked="checked"'}
								{else}
									{assign var=$checkbox_field|cat:'_yes' value='checked="checked"'}
								{/if}
								
								<input {$random_featured_yes} type="radio" id="{$checkbox_field}_yes" name="{$checkbox_field}" value="1" /> <label for="{$checkbox_field}_yes">{$lang.yes}</label>
								<input {$random_featured_no} type="radio" id="{$checkbox_field}_no" name="{$checkbox_field}" value="0" /> <label for="{$checkbox_field}_no">{$lang.no}</label>
							</td>
						</tr>
						</table>
						
						<div class="hide" id="random_featured_settings">
							<table class="form wide">
							<tr>
								<td class="name no_divider"></td>
								<td class="field">
									<div class="random_deny" style="padding: 0 0 5px 0">
										<label><input type="radio" {if $sPost.random_featured_type == 'single' || !$sPost.random_featured_type}checked="checked"{/if} name="random_featured_type" value="single" /> {$lang.random_featured_type_single}</label> <span class="field_description">{$lang.random_featured_type_single_hint}</span>
									</div>
									<div class="random_deny" style="padding: 0 0 5px 0">
										<label><input type="radio" {if $sPost.random_featured_type == 'multi'}checked="checked"{/if} name="random_featured_type" value="multi" /> {$lang.random_featured_type_multi}</label> <span class="field_description">{$lang.random_featured_type_multi_hint}</span>
									</div>
									<div style="padding: 0 0 10px 0">
										<label><input type="radio" {if $sPost.random_featured_type == 'list'}checked="checked"{/if} name="random_featured_type" value="list" /> {$lang.random_featured_type_list}</label> <span class="field_description">{$lang.random_featured_type_list_hint}</span>
									</div>
									<div id="random_listings_number">
										<input type="text" name="random_featured_number" value="{$sPost.random_featured_number}" style="width: 25px;text-align: center;" maxlength="2" /> <span class="field_description_noicon">{$lang.listing_number}</span>
									</div>
								</td>
							</tr>
							</table>
						</div>
					</div>
					
					{rlHook name='apTplListingTypesFormFeatured'}
				</div>
			</fieldset>
		</td>
	</tr>
	
	<tr>
		<td class="name">{$lang.arrange_settings}</td>
		<td class="field">
			{if $smarty.get.action == 'add'}
				{$lang.not_available} <span class="field_description">{$lang.general_category_hint}</span>
			{else}
				<fieldset class="light">
					<legend id="legend_arrange_settings" class="up" onclick="fieldset_action('arrange_settings');">{$lang.settings}</legend>
					<div id="arrange_settings">
					
						<table class="form wide">
						<tr>
							<td class="name">{$lang.arrange_by_field}</td>
							<td class="field">
								<select name="arrange_field">
									<option value="0">- {$lang.disabled} -</option>
									{foreach from=$fields item='field'}
									{assign var='type_phrase' value='type_'|cat:$field.Type}
									<option value="{$field.Key}" {if $sPost.arrange_field == $field.Key}selected="selected"{/if}>{$field.name} ({$lang.$type_phrase})</option>
									{foreachelse}
									<option value="0">{$lang.no_fields_available}</option>
									{/foreach}
								</select>
								<span class="field_description">{$lang.arrange_by_field_hint}</span>
							</td>
						</tr>
						</table>
						
						<div id="arrange_area" class="hide">
							<table class="form wide">
							<tr>
								<td class="name">{$lang.apply_to}</td>
								<td class="field">
									<div class="individual_page_item">
										<div style="padding: 6px 0 4px;" id="arrange_search">
											<label><input class="modules" type="checkbox" {if $sPost.is_arrange_search}checked="checked"{/if} name="is_arrange_search" value="1" /> {$lang.arrange_search_form}</label>
											<div class="area hide">
												<div style="padding: 5px 0;">
													
												</div>
											</div>
										</div>
									</div>
									
									<div style="padding: 4px 0;" id="arrange_featured">
										<label><input class="modules" type="checkbox" {if $sPost.is_arrange_featured}checked="checked"{/if} name="is_arrange_featured" value="1" /> {$lang.arrange_featured_block}</label>
										<div class="area hide">
											<div style="padding: 5px 0;">
												
											</div>
										</div>
									</div>
									<div style="padding: 4px 0;" id="arrange_statistics">
										<label><input class="modules" type="checkbox" {if $sPost.is_arrange_statistics}checked="checked"{/if} name="is_arrange_statistics" value="1" /> {$lang.arrange_statistics}</label>
										<div class="area hide">
											<div style="padding: 5px 0;">
												
											</div>
										</div>
									</div>
								</td>
							</tr>
							</table>
						</div>
						
						{rlHook name='apTplListingTypesFormArrange'}
					
					</div>
				</fieldset>
				
				<script type="text/javascript">//<![CDATA[
				var arrange_langs = new Array();
				var langs_list = new Array();
				{assign var='exp_values' value=','|explode:$fields[$sPost.arrange_field].Values}
				{assign var='arrange_key' value=$fields[$sPost.arrange_field].Key}
				{foreach from=$allLangs item='languages' name='lF'}
				langs_list['{$languages.Code}'] = '{$languages.name}';
				
				arrange_langs['{$arrange_key}_{$languages.Code}'] = [
					[{foreach from=$exp_values item='value' name='valueF'}{if $sPost.arrange_search[$arrange_key].$value[$languages.Code]}'{$sPost.arrange_search[$arrange_key].$value[$languages.Code]}'{else}false{/if}{if !$smarty.foreach.valueF.last},{/if}{/foreach}],
					[{foreach from=$exp_values item='value' name='valueF'}{if $sPost.arrange_featured[$arrange_key].$value[$languages.Code]}'{$sPost.arrange_featured[$arrange_key].$value[$languages.Code]}'{else}false{/if}{if !$smarty.foreach.valueF.last},{/if}{/foreach}],
					[{foreach from=$exp_values item='value' name='valueF'}{if $sPost.arrange_statistics[$arrange_key].$value[$languages.Code]}'{$sPost.arrange_statistics[$arrange_key].$value[$languages.Code]}'{else}false{/if}{if !$smarty.foreach.valueF.last},{/if}{/foreach}]
				];
				{/foreach}
				var arrange_modules = ['arrange_search', 'arrange_featured', 'arrange_statistics'];
				var arrange_names = ['{$lang.arrange_tab_name}', '{$lang.arrange_box_name}', '{$lang.arrange_col_name}'];
				
				
				var fields = new Array();
				{foreach from=$fields item='field'}
					{assign var='exp_values' value=','|explode:$field.Values}
					fields['{$field.Key}'] = [
						'{$field.Type}',
						'{$field.Values}',
						[{foreach from=$exp_values item='value' name='valueF'}{assign var='val_phrase' value='listing_fields+name+'|cat:$field.Key|cat:'_'|cat:$value}{if $field.Type == 'bool'}'{if $value}{$lang.yes}{else}{$lang.no}{/if}'{else}'{$lang.$val_phrase}'{/if}{if !$smarty.foreach.valueF.last},{/if}{/foreach}]
					];
				{/foreach}
				
				{literal}
				
				$(document).ready(function(){
					$('select[name=arrange_field]').change(function(){
						arrangeField();
					});
					
					arrangeField();
					
					$('#arrange_area input.modules').click(function(){
						arrangeOpen(this);
					})
					$('#arrange_area input.modules:checked').each(function(){
						arrangeOpen(this);
					});
					
					$('input[name=random_featured]').change(function(){
						randomFeatured();
					});
					
					randomFeatured();
					
					$('input[name=photo]').change(function(){
						photoOpt();
					});
					
					photoOpt();
					
					$('input[name=random_featured_type]').change(function(){
						randomFeaturedType();
					});
					
					randomFeaturedType();
				});
				
				var arrangeOpen = function(obj){
					if ( $(obj).is(':checked') )
					{
						$(obj).parent().next().slideDown();
					}
					else
					{
						$(obj).parent().next().slideUp();
					}
				};
				
				var arrangeField = function(){
					var key = $('select[name=arrange_field]').val();
					
					if ( key != '0' )
					{
						$('#arrange_area').slideDown();
						$('#arrange_area input.tmp').attr('checked', true).removeClass('tmp').parent().next().slideDown();
					}
					else
					{
						$('#arrange_area').slideUp();
						$('#arrange_area input.modules:checked').attr('checked', false).addClass('tmp').parent().next().slideUp();
						return;
					}
					
					arrangeBuild(key);
				};
				
				var arrangeBuild = function(key){
					var tabs = '<ul class="tabs">';
					var first_tab = true;
					for ( var lng in langs_list)
					{
						if ( typeof(langs_list[lng]) != 'function' )
						{
							var active = first_tab ? ' class="active"' : '';
							tabs+= '<li'+active+' lang="'+lng+'">'+langs_list[lng]+'</li> ';
							first_tab = false;
						}
					}
					tabs += '</ul>';
					
					for (var j=0; j<arrange_modules.length;j++)
					{
						var module = arrange_modules[j];
						var values = fields[key][1].split(',');
						var html = tabs;
						
						first_tab = true;
						for ( var lng in langs_list)
						{
							if ( typeof(langs_list[lng]) != 'function' )
							{
								var hide = first_tab ? '' : ' hide';
								html += '<div class="tab_area '+lng+' '+hide+'">';
								html += '<table style="margin-left: 20px;" class="frame"><tr>';
								for ( var i=0; i<values.length; i++ )
								{
									if ( !arrange_langs[key+'_'+lng] )
									{
										var set = fields[key][2][i];
									}
									else
									{
										var set = arrange_langs[key+'_'+lng][j][i] ? arrange_langs[key+'_'+lng][j][i] : fields[key][2][i];
									}
									html += '<td class="name">'+arrange_names[j].replace('{name}', fields[key][2][i])+'</td><td class="field ckeditor"><input type="text" name="'+module+'['+key+']['+values[i]+']['+lng+']" value="'+set+'" /> <span class="field_description_noicon">('+langs_list[lng]+')</span></td></tr><tr>';
								}
								html += '</tr></table></div>';
								first_tab = false;
							}
						}
	
						/* append search tabs fieds */
						$('#'+module+' div.area div').html(html);
						flynax.tabs();
					}
				};			
				{/literal}
				//]]>
				</script>
			{/if}
			
			<script type="text/javascript">
			{literal}
			
			$(document).ready(function(){
				$('input[name=random_featured]').change(function(){
					randomFeatured();
				});
				
				randomFeatured();
				
				$('input[name=photo]').change(function(){
					photoOpt();
				});
				
				photoOpt();
				
				$('input[name=random_featured_type]').change(function(){
					randomFeaturedType();
				});
				
				randomFeaturedType();
				
				$('input[name=page]').change(function(){
					individualPage();
				});
				
				individualPage(true);
			});
			
			var randomFeatured = function(){
				var value = parseInt($('input[name=random_featured]:checked').val());

				if ( value )
				{
					$('#random_featured_settings').slideDown();
				}
				else
				{
					$('#random_featured_settings').slideUp();
				}
			};
			
			var randomFeaturedType = function(){
				var value = $('input[name=random_featured_type]:checked').val();

				if ( value == 'single' )
				{
					$('#random_listings_number').slideUp();
				}
				else
				{
					$('#random_listings_number').slideDown();
				}
			};
			
			var photoOpt = function(){
				var value = parseInt($('input[name=photo]:checked').val());

				if ( value )
				{
					$('.random_deny').show();
				}
				else
				{
					$('.random_deny').hide();
					$('input[name=random_featured_type][value=list]').attr('checked', true);
				}
			};
			
			var individualPage = function(mode){
				var value = parseInt($('input[name=page]:checked').val());
				
				if ( value )
				{
					if (mode)
					{
						$('.individual_page_item').show();
					}
					else
					{
						$('.individual_page_item').slideDown();
					}
				}
				else
				{
					if (mode)
					{
						$('.individual_page_item').hide();
					}
					else
					{
						$('.individual_page_item').slideUp();
					}
				}
			};
			{/literal}
			</script>
			<script type="text/javascript">
			{literal}
				$(document).ready(function(){
					$('input[name=search_multi_categories]').change(function(){
						searchMultiCat();
					});
					searchMultiCat();
				});
				var searchMultiCat = function(){
					var enabled = $('input[name=search_multi_categories]:checked').val() ? 1 : 0;

					if ( enabled != 0 )
					{
						$('#multi_categories_levels').slideDown();
					}
					else
					{
						$('#multi_categories_levels').slideUp();
						return;
					}
				};
			{/literal}
			</script>
		</td>
	</tr>
	
	<tr>
		<td class="name"><span class="red">*</span>{$lang.status}</td>
		<td class="field">
			<select name="status">
				<option value="active" {if $sPost.status == 'active'}selected="selected"{/if}>{$lang.active}</option>
				<option value="approval" {if $sPost.status == 'approval'}selected="selected"{/if}>{$lang.approval}</option>
			</select>
		</td>
	</tr>
	<tr>
		<td></td>
		<td class="field">
			<input class="button" type="submit" value="{if $smarty.get.action == 'edit'}{$lang.edit}{else}{$lang.add}{/if}" />
		</td>
	</tr>
	</table>
	</form>
	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_end.tpl'}
	<!-- add new plan end -->

	{rlHook name='apTplListingTypesAction'}
	
{else}

	<!-- delete listing type block -->
	<div id="delete_block" class="hide">
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_start.tpl' block_caption=$lang.delete_account}
			<div id="delete_container">
				{$lang.detecting}
			</div>
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_end.tpl'}
		
		<script type="text/javascript">//<![CDATA[
		{if $config.trash}
			var delete_conform_phrase = "{$lang.notice_drop_empty_listing_type}";
		{else}
			var delete_conform_phrase = "{$lang.notice_delete_empty_listing_type}";
		{/if}
		
		{literal}
		
		function delete_chooser(method, key, name)
		{
			if (method == 'delete')
			{
				rlPrompt(delete_conform_phrase.replace('{type}', name), 'xajax_deleteListingType', key);
			}
			else if (method == 'replace')
			{
				$('#top_buttons').slideUp('slow');
				$('#bottom_buttons').slideDown('slow');
				$('#replace_content').slideDown('slow');
			}
		}
		
		{/literal}
		//]]>
		</script>
	</div>
	<!-- delete listing type block end -->

	<!-- listing plans grid -->
	<div id="grid"></div>
	<script type="text/javascript">//<![CDATA[
	var listingTypesGrid;
	
	{literal}
	$(document).ready(function(){
		
		listingTypesGrid = new gridObj({
			key: 'listingTypes',
			id: 'grid',
			ajaxUrl: rlUrlHome + 'controllers/listing_types.inc.php?q=ext',
			defaultSortField: 'name',
			title: lang['ext_listing_types_manager'],
			fields: [
				{name: 'name', mapping: 'name', type: 'string'},
				{name: 'Admin_only', mapping: 'Admin_only'},
				{name: 'Order', mapping: 'Order', type: 'int'},
				{name: 'Status', mapping: 'Status'},
				{name: 'Key', mapping: 'Key'}
			],
			columns: [
				{
					header: lang['ext_name'],
					dataIndex: 'name',
					width: 50,
					id: 'rlExt_item_bold'
				},{
					header: lang['ext_position'],
					dataIndex: 'Order',
					width: 10,
					editor: new Ext.form.NumberField({
						allowBlank: false,
						allowDecimals: false
					}),
					renderer: function(val){
						return '<span ext:qtip="'+lang['ext_click_to_edit']+'">'+val+'</span>';
					}
				},{
					header: lang['ext_admin_only'],
					dataIndex: 'Admin_only',
					width: 10,
					editor: new Ext.form.ComboBox({
						store: [
							['1', lang['ext_yes']],
							['0', lang['ext_no']]
						],
						displayField: 'value',
						valueField: 'key',
						emptyText: lang['ext_not_available'],
						typeAhead: true,
						mode: 'local',
						triggerAction: 'all',
						selectOnFocus:true
					}),
					renderer: function(val){
						return '<span ext:qtip="'+lang['ext_click_to_edit']+'">'+val+'</span>';
					}
				},{
					header: lang['ext_status'],
					dataIndex: 'Status',
					fixed: true,
					width: 100,
					editor: new Ext.form.ComboBox({
						store: [
							['active', lang['ext_active']],
							['approval', lang['ext_approval']]
						],
						displayField: 'value',
						valueField: 'key',
						typeAhead: true,
						mode: 'local',
						triggerAction: 'all',
						selectOnFocus:true
					}),
					renderer: function(val){
						return '<span ext:qtip="'+lang['ext_click_to_edit']+'">'+val+'</span>';
					}
				},{
					header: lang['ext_actions'],
					width: 70,
					fixed: true,
					dataIndex: 'Key',
					sortable: false,
					renderer: function(data, ext, row) {
						var out = "<center>";
						
						if ( rights[cKey].indexOf('edit') >= 0 )
						{
							out += "<a href='"+rlUrlHome+"index.php?controller="+controller+"&action=edit&key="+data+"'><img class='edit' ext:qtip='"+lang['ext_edit']+"' src='"+rlUrlHome+"img/blank.gif' /></a>";
						}
						
						if ( rights[cKey].indexOf('delete') >= 0 )
						{
							out += "<img class='remove' ext:qtip='"+lang['ext_delete']+"' src='"+rlUrlHome+"img/blank.gif' onclick='xajax_prepareDeleting(\""+row.data.Key+"\")' />";
						}
						out += "</center>";
						
						return out;
					}
				}
			]
		});
		
		{/literal}{rlHook name='apTplListingTypesGrid'}{literal}
		
		listingTypesGrid.init();
		grid.push(listingTypesGrid.grid);
		
	});
	{/literal}
	//]]>
	</script>
	<!-- listing plans grid end -->

	{rlHook name='apTplListingTypesBottom'}
	
{/if}

<!-- listing types tpl end -->
