<!-- search forms tpl -->

<!-- navigation bar -->
<div id="nav_bar">
	{rlHook name='apTplSearchFormsNavBar'}
	
	{if $smarty.get.action == 'edit'}<a href="{$rlBaseC}action=build&amp;form={$smarty.get.form}" class="button_bar"><span class="left"></span><span class="center_build">{$lang.build_form}</span><span class="right"></span></a>{/if}
	<a href="{$rlBaseC}action=add" class="button_bar"><span class="left"></span><span class="center_add">{$lang.add_form}</span><span class="right"></span></a>
	<a href="{$rlBase}index.php?controller={$smarty.get.controller}" class="button_bar"><span class="left"></span><span class="center_list">{$lang.forms_list}</span><span class="right"></span></a>
</div>
<!-- navigation bar end -->

{if isset($smarty.get.action)}

	{if $smarty.get.action == 'add' || $smarty.get.action == 'edit'}

		{assign var='sPost' value=$smarty.post}
	
		<!-- add/edit form -->
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_start.tpl'}
			<form action="{$rlBaseC}action={if $smarty.get.action == 'add'}add{elseif $smarty.get.action == 'edit'}edit&amp;form={$smarty.get.form}{/if}" method="post">
				<input type="hidden" name="submit" value="1" />
				{if $smarty.get.action == 'edit'}
					<input type="hidden" name="fromPost" value="1" />
				{/if}
				<table class="form">
				<tr>
					<td class="name"><span class="red">*</span>{$lang.key}</td>
					<td class="value">
						<input {if $smarty.get.action == 'edit'}readonly="readonly"{/if} class="{if $smarty.get.action == 'edit'}disabled{/if}" name="key" type="text" style="width: 150px;" value="{$sPost.key}" maxlength="30" />
					</td>
				</tr>

				<tr>
					<td class="name">
						<span class="red">*</span>{$lang.name}
					</td>
					<td class="value">
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
					<td class="name"><span class="red">*</span>{$lang.listing_type}</td>
					<td class="value">
						{if $smarty.get.action == 'add' || !$sPost.readonly}
							<select name="type">
								<option value="">{$lang.select}</option>
								{foreach from=$listing_types item='l_type'}
									<option value="{$l_type.Key}" {if $sPost.type == $l_type.Key}selected="selected"{/if}>{$l_type.name}</option>
								{/foreach}
							</select>
						{else}
							<input style="width: 150px" class="disabled" type="text" disabled="disabled" value="{$listing_types[$sPost.type].name}" />
							<input type="hidden" name="type" value="{$sPost.type}" />
						{/if}
					</td>
				</tr>
				
				<tr>
					<td class="name"><span class="red">*</span>{$lang.use_groups}</td>
					<td class="field">
						<label><input {if $sPost.groups}checked="checked"{/if}type="radio" name="groups" value="1" /> {$lang.yes}</label>
						<label><input {if !$sPost.groups}checked="checked"{/if} type="radio" name="groups" value="0" /> {$lang.no}</label>
					</td>
				</tr>
				
				{rlHook name='apTplSearchFormsForm'}
				
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
					<td class="value">
						<input type="submit" value="{if $smarty.get.action == 'edit'}{$lang.edit}{else}{$lang.add}{/if}" />
					</td>
				</tr>
				</table>
			</form>
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_end.tpl'}
		<!-- add/edit form end -->

	{elseif $smarty.get.action == 'build'}
	
		{if !$form_info.Groups}
			{assign var='no_groups' value=true}
		{/if}
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'builder'|cat:$smarty.const.RL_DS|cat:'builder.tpl'}
	
	{/if}
	
{else}

	<!-- search forms grid -->
	<div id="grid"></div>
	<script type="text/javascript">//<![CDATA[
	var searchFormsGrid;
	
	{literal}
	$(document).ready(function(){
		
		searchFormsGrid = new gridObj({
			key: 'searchForms',
			id: 'grid',
			ajaxUrl: rlUrlHome + 'controllers/search_forms.inc.php?q=ext',
			defaultSortField: 'name',
			title: lang['ext_search_forms_manager'],
			fields: [
				{name: 'name', mapping: 'name', type: 'string'},
				{name: 'Type', mapping: 'Type'},
				{name: 'Mode', mapping: 'Mode'},
				{name: 'Groups', mapping: 'Groups'},
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
					header: lang['ext_option'],
					dataIndex: 'Mode',
					width: 15,
					id: 'rlExt_item'
				},{
					header: lang['ext_type'],
					dataIndex: 'Type',
					width: 15
				},{
					header: lang['ext_use_groups'],
					dataIndex: 'Groups',
					width: 15,
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
						selectOnFocus: true
					})
				},{
					header: lang['ext_status'],
					dataIndex: 'Status',
					width: 90,
					fixed: true,
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
					width: 100,
					fixed: true,
					dataIndex: 'Key',
					sortable: false,
					renderer: function(data) {
						var out = "<center>";
		
						out += "<a href='"+rlUrlHome+"index.php?controller="+controller+"&action=build&form="+data+"'><img class='build' ext:qtip='"+lang['ext_build']+"' src='"+rlUrlHome+"img/blank.gif' /></a>";
						out += "<a href='"+rlUrlHome+"index.php?controller="+controller+"&action=edit&form="+data+"'><img class='edit' ext:qtip='"+lang['ext_edit']+"' src='"+rlUrlHome+"img/blank.gif' /></a>";
						out += "<img class='remove' ext:qtip='"+lang['ext_delete']+"' src='"+rlUrlHome+"img/blank.gif' onclick='rlConfirm( \""+lang['ext_notice_'+delete_mod]+"\", \"xajax_deleteSearchForm\", \""+Array(data)+"\", \"section_load\" )' />";
						
						out += "</center>";
						
						return out;
					}
				}
			]
		});
		
		{/literal}{rlHook name='apTplSearchFormsGrid'}{literal}
		
		searchFormsGrid.init();
		grid.push(searchFormsGrid.grid);
		
	});
	{/literal}
	//]]>
	</script>
	<!-- search forms grid end -->

	{rlHook name='apTplSearchFormsBottom'}
	
{/if}

<!-- search form tpl end -->