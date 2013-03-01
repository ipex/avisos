<!-- account types tpl -->

<!-- navigation bar -->
<div id="nav_bar">
	{rlHook name='apTplAccountTypesNavBar'}

	{if $aRights.$cKey.add && !$smarty.get.action}
		<a href="{$rlBaseC}action=add" class="button_bar"><span class="left"></span><span class="center_add">{$lang.add_type}</span><span class="right"></span></a>
	{/if}
	{if $aRights.$cKey.edit && $smarty.get.action == 'build'}
		{if $smarty.get.form != 'reg_form'}
			<a href="{$rlBase}index.php?controller=account_types&amp;action=build&amp;form=reg_form&amp;key={$category_info.Key}" class="button_bar"><span class="left"></span><span class="center_build">{$lang.registration_form}</span><span class="right"></span></a>
		{/if}
		
		{if $smarty.get.form != 'search_form'}
			<a href="{$rlBase}index.php?controller=account_types&amp;action=build&amp;form=search_form&amp;key={$category_info.Key}" class="button_bar"><span class="left"></span><span class="center_build">{$lang.search_form}</span><span class="right"></span></a>
		{/if}
		{if $smarty.get.form != 'short_form'}
			<a href="{$rlBase}index.php?controller=account_types&amp;action=build&amp;form=short_form&amp;key={$category_info.Key}" class="button_bar"><span class="left"></span><span class="center_build">{$lang.short_form}</span><span class="right"></span></a>
		{/if}
	{/if}
	<a href="{$rlBase}index.php?controller={$smarty.get.controller}" class="button_bar"><span class="left"></span><span class="center_list">{$lang.types_list}</span><span class="right"></span></a>
</div>
<!-- navigation bar end -->

{if $smarty.get.action == 'add' || $smarty.get.action == 'edit'}

	{assign var='sPost' value=$smarty.post}

	<!-- add/edit new type -->
	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_start.tpl'}
	<form action="{$rlBaseC}action={if $smarty.get.action == 'add'}add{elseif $smarty.get.action == 'edit'}edit&amp;type={$smarty.get.type}{/if}" method="post">
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
				<input type="text" name="name[{$language.Code}]" value="{$sPost.name[$language.Code]}" maxlength="350" />
				{if $allLangs|@count > 1}
						<span class="field_description_noicon">{$lang.name} (<b>{$language.name}</b>)</span>
					</div>
				{/if}
			{/foreach}
		</td>
	</tr>
	
	<tr>
		<td class="name">{$lang.description}</td>
		<td class="field ckeditor">
			{if $allLangs|@count > 1}
				<ul class="tabs">
					{foreach from=$allLangs item='language' name='langF'}
					<li lang="{$language.Code}" {if $smarty.foreach.langF.first}class="active"{/if}>{$language.name}</li>
					{/foreach}
				</ul>
			{/if}
			
			{foreach from=$allLangs item='language' name='langF'}
				{if $allLangs|@count > 1}<div class="tab_area{if !$smarty.foreach.langF.first} hide{/if} {$language.Code}">{/if}
				{assign var='dCode' value='description_'|cat:$language.Code}
				{fckEditor name='description_'|cat:$language.Code width='100%' height='140' value=$sPost.$dCode}
				{if $allLangs|@count > 1}</div>{/if}
			{/foreach}
		</td>
	</tr>
	
	<tr>
		<td class="name">{$lang.abilities}</td>
		<td class="field">
			<fieldset class="light" style="margin-top: 5px;">
				<legend id="legend_account_abb" class="up" onclick="fieldset_action('account_abb');">{$lang.abilities}</legend>
				<div id="account_abb">
					{foreach from=$listing_types item='l_type'}
						<div style="padding: 2px 0 4px 10px;">
							{assign var='replace' value=`$smarty.ldelim`type`$smarty.rdelim`}
							<label><input {if $l_type.Key|in_array:$sPost.abilities}checked="checked"{/if} type="checkbox" name="abilities[]" value="{$l_type.Key}" /> {$lang.ability_to_add|replace:$replace:$l_type.name}</label>
						</div>
					{/foreach}
					
					{if $smarty.get.type != 'visitor'}
						<table cellpadding="0" cellspacing="0">
						<tr>
							<td>
								<div style="padding: 2px 0 4px 10px;">
									<label><input {if $sPost.page}checked="checked"{/if} type="checkbox" name="page" value="1" /> {$lang.account_type_custom_page}</label>
								</div>
							</td>
							{if $smarty.get.action == 'edit' && $sPost.page}
								{assign var='replace' value='<a target="_blank" class="static" href="'|cat:$rlBase|cat:'index.php?controller=pages&amp;action=edit&amp;page=at_'|cat:$smarty.get.type|cat:'">$1</a>'}
								<td><span class="field_description">{$lang.individual_page_hint|regex_replace:'/\[(.*)\]/':$replace}</span></td>
							{/if}
						</tr>
						</table>
						
						<div style="padding: 2px 0 4px 10px;">
							{if $config.account_wildcard}
								{assign var='replace' value=$lang.sub_domain}
							{else}
								{assign var='replace' value=$lang.sub_directory}
							{/if}
							{assign var='s_type' value=`$smarty.ldelim`type`$smarty.rdelim`}
							<label><input {if $sPost.own_location}checked="checked"{/if} type="checkbox" name="own_location" value="1" /> {$lang.account_type_own_location|replace:$s_type:$replace}</label>
						</div>
					{/if}
				</div>
			</fieldset>
			
			<div class="grey_area" style="margin-bottom: 10px;">
				<span onclick="$('#account_abb input').attr('checked', true);" class="green_10">{$lang.check_all}</span>
				<span class="divider"> | </span>
				<span onclick="$('#account_abb input').attr('checked', false);" class="green_10">{$lang.uncheck_all}</span>
			</div>
		</td>
	</tr>
	
	{if $smarty.get.type != 'visitor'}
	<tr>
		<td class="name">{$lang.settings}</td>
		<td class="field">
			<fieldset class="light" style="margin-top: 5px;">
				<legend id="legend_account_settings" class="up" onclick="fieldset_action('account_settings');">{$lang.settings}</legend>
				<div id="account_settings">
					{foreach from=$account_settings item='account_setting'}
						<div style="padding: 2px 0 4px 10px;">
							<label><input {if $sPost[$account_setting.key]}checked="checked"{/if} type="checkbox" name="{$account_setting.key}" value="1" /> {$account_setting.name}</label>
						</div>
					{/foreach}
				</div>
			</fieldset>
			
			<script type="text/javascript">
			{literal}
			
			$(document).ready(function(){
				$('#account_settings input').click(function(){
					ATsettingsControl();
				});
				
				ATsettingsControl();
			});
			
			var ATsettingsControl = function(){
				if ( $('#account_settings input[name=admin_confirmation]:checked').length > 0 )
				{
					$('#account_settings input[name=auto_login]').attr('checked', false).attr('disabled', true).parent().addClass('disabled');
				}
				else
				{
					$('#account_settings input[name=auto_login]').attr('disabled', false).parent().removeClass('disabled');
				}
			}
			
			{/literal}
			</script>
		</td>
	</tr>
	{/if}
	
	{rlHook name='apTplAccountTypesForm'}
	
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
			<input type="submit" value="{if $smarty.get.action == 'edit'}{$lang.edit}{else}{$lang.add}{/if}" />
		</td>
	</tr>
	</table>
	</form>
	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_end.tpl'}
	<!-- add/edit type end -->

{elseif $smarty.get.action == 'build'}

	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'builder'|cat:$smarty.const.RL_DS|cat:'builder.tpl' no_groups=true}
	
{else}

	<!-- build reqest -->
	{if $smarty.get.request == 'build'}
		<script type="text/javascript">
		var request_type_key = '{$smarty.get.key}';
		var request_account_notice = "{$lang.suggest_account_type_building}";
		{literal}
		
		$(document).ready(function(){
			rlConfirm(request_account_notice, 'requestRedirect');
		});
		
		var requestRedirect = function(){
			location.href = rlUrlHome+'index.php?controller='+controller+'&action=build&form=reg_form&key='+request_type_key;
		};
		
		{/literal}
		</script>
	{/if}
	<!-- build reqest end -->

	<!-- pre delete block -->
	<div id="delete_block" class="hide">
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_start.tpl' block_caption=$lang.delete_account_type}
			<div id="delete_container">{$lang.detecting}</div>
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_end.tpl'}
	
		<script type="text/javascript">//<![CDATA[
		{if $config.trash}
			var delete_conform_phrase = "{$lang.notice_drop_empty_account_type}";
		{else}
			var delete_conform_phrase = "{$lang.notice_delete_empty_account_type}";
		{/if}
		
		{literal}
		function delete_chooser(method, id, type)
		{
			if (method == 'delete')
			{
				rlPrompt(delete_conform_phrase.replace('{type}', type), 'xajax_deleteAccountType', id);
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
	<!-- pre delete block end -->

	<!-- account types grid -->
	<div id="grid"></div>
	<script type="text/javascript">//<![CDATA[
	var accountTypesGrid;
	{literal}
	
	var list = [
    	{
    		text: lang['ext_build_reg_form'],
    		href: rlUrlHome+"index.php?controller="+controller+"&amp;action=build&amp;form=reg_form&amp;key={key}"
    	},
    	{
    		text: lang['ext_build_short_form'],
    		href: rlUrlHome+"index.php?controller="+controller+"&amp;action=build&amp;form=short_form&amp;key={key}"
    	},
		{
    		text: lang['ext_search_form'],
    		href: rlUrlHome+"index.php?controller="+controller+"&amp;action=build&amp;form=search_form&amp;key={key}"
    	}
	];
	
	$(document).ready(function(){
		
		accountTypesGrid = new gridObj({
			key: 'accountTypes',
			id: 'grid',
			ajaxUrl: rlUrlHome + 'controllers/account_types.inc.php?q=ext',
			defaultSortField: 'name',
			remoteSortable: false,
			title: lang['ext_account_types_manager'],
			fields: [
				{name: 'ID', mapping: 'ID', type: 'int'},
				{name: 'name', mapping: 'name', type: 'string'},
				{name: 'Position', mapping: 'Position', type: 'int'},
				{name: 'Accounts_count', mapping: 'Accounts_count', type: 'int'},
				{name: 'Status', mapping: 'Status'},
				{name: 'Key', mapping: 'Key'}
			],
			columns: [
				{
					header: lang['ext_id'],
					dataIndex: 'ID',
					fixed: true,
					width: 25
				},
				{
					header: lang['ext_name'],
					dataIndex: 'name',
					width: 60,
					id: 'rlExt_item_bold'
				},{
					header: lang['ext_accounts'],
					dataIndex: 'Accounts_count',
					width: 8,
					renderer: function(val, ext, row){
						//return '<span ext:qtip="'+lang['ext_click_to_edit']+'">'+val+'</span>';
						if ( val )
						{
							var out = '<a ext:qtip="'+lang['ext_click_to_view_details']+'" target="_blank" href="'+rlUrlHome+'index.php?controller=accounts&account_type='+row.data.Key+'"><b>'+val+'</b> ('+lang['ext_view']+')</a>';
						}
						else
						{
							var out = val;
						}
						
						return out;
					}
				},{
					header: lang['ext_position'],
					dataIndex: 'Position',
					width: 8,
					editor: new Ext.form.NumberField({
						allowBlank: false,
						allowDecimals: false
					}),
					renderer: function(val){
						return '<span ext:qtip="'+lang['ext_click_to_edit']+'">'+val+'</span>';
					}
				},{
					header: lang['ext_status'],
					dataIndex: 'Status',
					width: 10,
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
					width: 90,
					fixed: true,
					dataIndex: 'Key',
					sortable: false,
					renderer: function(data, ext, row) {
						var out = "<center>";
						var splitter = false;
						
						if ( rights[cKey].indexOf('edit') >= 0 )
						{
							if ( row.data.Key != 'visitor' )
							{
								out += '<img onclick="flynax.extModal(this, \''+data+'\');" class="build" ext:qtip="'+lang['ext_build']+'" src="'+rlUrlHome+'img/blank.gif" />';
							}
							out += "<a href='"+rlUrlHome+"index.php?controller="+controller+"&action=edit&type="+data+"'><img class='edit' ext:qtip='"+lang['ext_edit']+"' src='"+rlUrlHome+"img/blank.gif' /></a>";
						}
						if ( rights[cKey].indexOf('delete') >= 0 && row.data.Key != 'visitor' )
						{
							out += "<img class='remove' ext:qtip='"+lang['ext_delete']+"' src='"+rlUrlHome+"img/blank.gif' onclick='xajax_preAccountTypeDelete(\""+row.data.Key+"\")' />";
						}
						out += "</center>";
						
						return out;
					}
				}
			]
		});
		
		{/literal}{rlHook name='apTplAccountTypesGrid'}{literal}
		
		accountTypesGrid.init();
		grid.push(accountTypesGrid.grid);
		
	});
	{/literal}
	//]]>
	</script>
	<!-- account types grid end -->

{/if}

<!-- account types end tpl -->