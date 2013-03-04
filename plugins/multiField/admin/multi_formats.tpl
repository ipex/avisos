<link href="{$smarty.const.RL_PLUGINS_URL}multiField/static/aStyle.css" type="text/css" rel="stylesheet" />

<!-- navigation bar -->
<div id="nav_bar">
	{if $aRights.$cKey.add}
		{if !$smarty.get.action && !$smarty.get.parent}
			<a href="{$rlBaseC}action=add" class="button_bar"><span class="left"></span><span class="center_add">{$lang.mf_add_item}</span><span class="right"></span></a>
		{elseif $smarty.get.parent}
			<a onclick="$('#search').slideDown();$('#new_item').slideUp('fast');$('#edit_item').slideUp('fast');" href="javascript:void(0)" class="button_bar"><span class="left"></span><span class="center_search">{$lang.search}</span><span class="right"></span></a>
			<a href="javascript:void(0)" onclick="show('new_item');$('#edit_item').slideUp('fast');$('#related_fields').slideUp('fast');$('#search').slideUp('fast')" class="button_bar"><span class="left"></span><span class="center_add">{$lang.add_item}</span><span class="right"></span></a>
			<a href="javascript:void(0)" onclick="show('related_fields');$('#new_item').slideUp('fast');$('#edit_item').slideUp('fast');$('#search').slideUp('fast');{if $geo_filter}$('#load_cont').slideUp('fast');{/if}" class="button_bar"><span class="left"></span><span class="center_list">{$lang.mf_related_fields}</span><span class="right"></span></a>
			<a href="javascript:void(0)" id="load_button" class="button_bar"><span class="left"></span><span class="center_import">{$lang.mf_import_flsource}</span><span class="right"></span></a>
		{elseif $smarty.get.action == 'edit'}
			<a href="{$rlBaseC}parent={$smarty.get.item}" class="button_bar"><span class="left"></span><span class="center_build">{$lang.mf_manage_items}</span><span class="right"></span></a>
		{/if}		
	{/if}

	{if $smarty.get.action == 'add'}
		<a href="{$rlBase}index.php?controller={$smarty.get.controller}" class="button_bar"><span class="left"></span><span class="center_list">{$lang.mf_formats_list}</span><span class="right"></span></a>
	{/if}
</div>
<!-- navigation bar end -->

{include file=$smarty.const.RL_PLUGINS|cat:'multiField'|cat:$smarty.const.RL_DS|cat:'admin'|cat:$smarty.const.RL_DS|cat:'import_interface.tpl'}

<!-- load from server -->
<div id="load_cont" class="hide" style="margin-top:15px">
	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_start.tpl' key="fl_load" loading=1 fixed=0 navigation=false}
		<div class="white block_loading" style="height:57px" id="flsource_container"></div>
	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_end.tpl'}
</div>
<!-- load from server end -->

{if $smarty.get.parent}

	<!-- search -->
	<div id="search" class="hide">
	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_start.tpl' block_caption=$lang.search}
	
	<form method="post" onsubmit="return false;" id="search_form" action="">
		<table class="form">
		<tr>
			<td class="name">{$lang.mf_name}</td>
			<td class="field">
				<input type="text" id="search_name" />
			</td>
		</tr>
		<tr>
			<td></td>
			<td class="field">
				<input type="submit" class="button" value="{$lang.search}" id="search_button" />
				<input type="button" class="button" value="{$lang.reset}" id="reset_search_button" />
			
				<a class="cancel" href="javascript:void(0)" onclick="$('#search').slideUp('fast')">{$lang.cancel}</a>
			</td>
		</tr>
		
		</table>
	</form>
	
	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_end.tpl'}
	</div>
	
	<script type="text/javascript">
	var remote_filters = new Array();
	{literal}
	
	var search = new Array();
	var cookie_filters = new Array();

	$(document).ready(function(){
		
		if ( readCookie('mf_sc') || remote_filters.length > 0 )
		{
			$('#search').show();
			cookie_filters = remote_filters.length > 0 ? remote_filters : readCookie('mf_sc').split(',');

			for (var i in cookie_filters)
			{
				if ( typeof(cookie_filters[i]) == 'string' )
				{
					var item = cookie_filters[i].split('||');
					if ( item[0] != 'undefined' && item[0] != '' )
					{													
						$('#search_'+item[0].toLowerCase()).selectOptions(item[1]);					
					}
				}
			}
		}
		
		$('#search_form').submit(function(){
			createCookie('tags_pn', 0, 1);
			search = new Array();
			search.push( new Array('action', 'search') );
			search.push( new Array('Name', $('#search_name').val()) );
			search.push( new Array('Parent', '{/literal}{$smarty.get.parent}{literal}') );

			var save_search = new Array();
			for(var i in search)
			{
				if ( search[i][1] != '' && typeof(search[i][1]) != 'undefined'  )
				{
					save_search.push(search[i][0]+'||'+search[i][1]);
				}
			}
			createCookie('mf_sc', save_search, 1);
			
			itemsGrid.filters = search;
			itemsGrid.reload();
		});
		
		$('#reset_search_button').click(function(){
			eraseCookie('mf_sc');
			itemsGrid.reset();
			
			$("#search select option[value='']").attr('selected', true);
			$("#search input[type=text]").val('');
			$("#search input").each(function(){
				if ( $(this).attr('type') == 'radio' )
				{
					$(this).attr('checked', false);
				}
			});
		});
		
	});
	
	{/literal}
	</script>
	<!-- search end -->
{/if}

{if $smarty.get.action}

	{assign var='sPost' value=$smarty.post}
	<!-- add/edit -->
	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_start.tpl'}
	<form action="{$rlBaseC}action={if $smarty.get.action == 'add'}add{elseif $smarty.get.action == 'edit'}edit&amp;item={$smarty.get.item}{/if}" method="post">
		<input type="hidden" name="submit" value="1" />
		{if $smarty.get.action == 'edit'}
			<input type="hidden" name="fromPost" value="1" />
		{/if}

		{if $smarty.get.action != 'edit'}
			<table class="form">
			<tr>
				<td class="name"><span class="red">*</span>{$lang.mf_type}</td>
				<td class="field">
					{if $sPost.type == 'new'}
						{assign var='type_new' value='checked="checked"'}
					{elseif $sPost.type == 'ex'}
						{assign var='type_ex' value='checked="checked"'}
					{else}
						{assign var='type_new' value='checked="checked"'}
					{/if}
					<label><input {$type_new} class="lang_add" type="radio" name="type" value="new" /> {$lang.mf_type_new}</label>
					<label><input {$type_ex} class="lang_add" type="radio" name="type" value="ex" /> {$lang.mf_type_ex}</label>
				</td>
			</tr>
			</table>
		{/if}

		<div id="new_format_cont">
			<table class="form">
			<tr>
				<td class="name"><span class="red">*</span>{$lang.key}</td>
				<td class="field">
					<input {if $smarty.get.action == 'edit'}readonly="readonly"{/if} class="{if $smarty.get.action == 'edit'}disabled{/if}" name="key" type="text" style="width: 150px;" value="{$sPost.key}" maxlength="30" />
				</td>
			</tr>
			<tr>
				<td class="name"><span class="red">*</span>{$lang.mf_name}</td>
				<td class="field">
					{if $allLangs|@count > 1}
						<ul class="tabs">
							{foreach from=$allLangs item='language' name='langF'}
							<li lang="{$language.Code}" {if $smarty.foreach.langF.first}class="active"{/if}>{$language.name}</li>
							{/foreach}
						</ul>
					{/if}
					
					{foreach from=$allLangs item='language' name='langF'}
						{if $allLangs|@count > 1}
							<div class="tab_area{if !$smarty.foreach.langF.first} hide{/if} {$language.Code}">
						{/if}
						<input type="text" name="name[{$language.Code}]" value="{$sPost.name[$language.Code]}" style="width: 250px;" maxlength="50" />
						{if $allLangs|@count > 1}
							<span class="field_description_noicon">{$lang.name} (<b>{$language.name}</b>)</span></div>
						{/if}
					{/foreach}
				</td>
			</tr>
			<tr>
				<td class="name">{$lang.mf_order_type}</td>
				<td class="field">
					<select name="order_type">
						<option value="alphabetic" {if $sPost.order_type == 'alphabetic'}selected="selected"{/if}>{$lang.alphabetic_order}</option>
						<option value="position" {if $sPost.order_type == 'position'}selected="selected"{/if}>{$lang.position_order}</option>
					</select>
				</td>
			</tr>
			</table>
		</div>

		<div id="data_entries_cont">
			<table class="form">	
			<tr>
				<td class="name"><span class="red">*</span>{$lang.mf_data_entry}</td>
				<td class="field">
					<select name="data_entry" {if $smarty.get.action == 'edit'}disabled="disabled"{/if}>
						<option value="0">{$lang.select}</option>
						{foreach from=$data_entries item="item"}
							<option {if $smarty.post.data_format == $item.Key}selected="selected"{/if} value="{$item.Key}">{$item.name}</option>
						{/foreach}
					</select>
				</td>
			</tr>
			</table>
		</div>

		<table class="form">
		<tr>
			<td class="name"><span class="red">*</span>{$lang.mf_geofilter}</td>
			<td class="field">
				{if $sPost.geo_filter == '1'}
					{assign var='geofilter_yes' value='checked="checked"'}
				{elseif $sPost.geo_filter == '0'}
					{assign var='geofilter_no' value='checked="checked"'}
				{else}
					{assign var='geofilter_no' value='checked="checked"'}
				{/if}

				<label><input {$geofilter_yes} {if $geo_disabled}disabled="disabled"{/if} class="lang_add" type="radio" name="geo_filter" value="1" /> {$lang.enabled}</label>
				<label><input {$geofilter_no} {if $geo_disabled}disabled="disabled"{/if} class="lang_add" type="radio" name="geo_filter" value="0" /> {$lang.disabled}</label>
				
			</td>
		</tr>
		<tr>
			<td class="name">{$lang.status}</td>
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
	<!-- add end -->
	<script type="text/javascript">
		{literal}
		$(document).ready(function(){
			$('input[name=type]').change(function(){
				typesChangeHandler();
			});

			{/literal}

			{*if $smarty.get.action == 'edit'}
				$('#new_format_cont').slideDown();
			{else*}
				typesChangeHandler();
			{*/if*}
			{literal}
		});
		var typesChangeHandler = function(){
			if( $('input[name=type]:checked').val() == 'ex')
			{
				$('#new_format_cont').slideUp();
				$('#data_entries_cont').slideDown();
			}else
			{
				$('#new_format_cont').slideDown();
				$('#data_entries_cont').slideUp();
			}
		};
		{/literal}
	</script>
{else}
	<!-- add new item -->
	<div id="new_item" class="hide">
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_start.tpl' block_caption=$lang.add_item}
		<form onsubmit="addItem();$('input[name=item_submit]').val('{$lang.loading}');return false;" action="" method="post">
		<table class="form">
		<tr>
			<td class="name"><span class="red">*</span>{$lang.key}</td>
			<td class="field"><span class="field_description_noicon" style="padding:0">{$smarty.get.parent}_</span>
				<input type="text" id="ni_key" style="width: 200px;" maxlength="60" />
			</td>
		</tr>

		{if $geo_filter}
			<tr>
				<td class="name">{$lang.mf_path}</td>
	
				<td class="field"><span class="field_description_noicon" style="padding:0">{$smarty.const.RL_URL_HOME}{if $parent_path}{$parent_path}/{/if}</span>
					<input type="text" id="ni_path" style="width: 200px;" maxlength="60" />
				</td>
			</tr>
		{/if}

		<tr>
			<td class="name"><span class="red">*</span>{$lang.value}</td>
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
					<input id="ni_{$language.Code}" type="text" style="width: 250px;" />
					{if $allLangs|@count > 1}
						<span class="field_description_noicon">{$lang.name} (<b>{$language.name}</b>)</span>
					</div>
					{/if}
				{/foreach}
			</td>
		</tr>
		
		<tr>
			<td class="name">{$lang.status}</td>
			<td class="field">
				<select id="ni_status">
					<option value="active" {if $sPost.status == 'active'}selected="selected"{/if}>{$lang.active}</option>
					<option value="approval" {if $sPost.status == 'approval'}selected="selected"{/if}>{$lang.approval}</option>
				</select>
			</td>
		</tr>

		<tr>
			<td></td>
			<td class="field">
				<input type="submit" name="item_submit" value="{$lang.add}" />
				<a onclick="$('#new_item').slideUp('normal')" href="javascript:void(0)" class="cancel">{$lang.close}</a>
			</td>
		</tr>
		</table>
		</form>
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_end.tpl'}
	</div>

	<!-- related fields list -->
	<div id="related_fields" class="hide">
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_start.tpl' block_caption=$lang.mf_related_fields}
		<table class="form">
		<tr>
			<td class="name" style="width:215px">{$lang.mf_related_listing_fields}</td>
			<td class="field">
				{foreach from=$related_listing_fields item="field"}
					<div>
						{$lang.name}: <b>{$field.name} / </b>
						{$lang.key}: <b>{$field.Key} / </b>
						<a href="index.php?controller=listing_fields&action=edit&field={$field.Key}" target="_blank">{$lang.edit|strtolower}</a>
					</div>
				{foreachelse}
					<span class="field_description_noicon">{$lang.mf_no_related_fields}</span>
				{/foreach}
			</td>
		</tr>
		<tr>
			<td class="name">{$lang.mf_related_account_fields}</td>
			<td class="field">
				{foreach from=$related_account_fields item="field"}
					<div>
						{$lang.name}: <b>{$field.name} / </b>
						{$lang.key}: <b>{$field.Key} / </b>
						<a href="index.php?controller=account_fields&action=edit&field={$field.Key}" target="_blank" style="margin-left:5px">{$lang.edit|strtolower}</a>
					</div>
				{foreachelse}
					<span class="field_description_noicon">{$lang.mf_no_related_fields}</span>
				{/foreach}
			</td>
		</tr>
		</table>
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_end.tpl'}
	</div>

	<!-- edit item -->
	<div id="edit_item" class="hide">
	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_start.tpl' block_caption=$lang.edit_item}
		<div id="prepare_edit_area">
			<div id="ei_loading" class="open_load" style="margin: 6px 0 0 10px;">{$lang.preparing}</div>
		</div>
	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_end.tpl'}
	</div>
	<!-- edit item end -->

	{literal}
	<script type="text/javascript">
	var addItem = function(){
	{/literal}
		var names = new Array();

		{foreach from=$allLangs item='languages'}
		names['{$languages.Code}'] = $('#ni_{$languages.Code}').val();
		{/foreach}

		xajax_addItem( $('#ni_key').val(), names, $('#ni_status').val(), '{$smarty.get.parent}'{if $geo_filter}, $('#ni_path').val(){/if});
	{literal}
	}
	</script>
	{/literal}
	
	{literal}
	<script type="text/javascript">
	var editItem = function(key){
	{/literal}
		var names = new Array();
	
		{foreach from=$allLangs item='languages'}
		names['{$languages.Code}'] = $('#ei_{$languages.Code}').val();
		{/foreach}

		xajax_editItem(key, names, $('#ei_status').val(), '{$smarty.get.parent}'{if $geo_filter}, $('#ei_path').val(){/if});
	{literal}
	}
	</script>
	{/literal}
	<!-- add new item end -->

	<!-- multi formats grid -->
	<div id="grid"></div>
	<script type="text/javascript">//<![CDATA[
	{if $smarty.get.parent}
		var itemsGrid;
		var parent = '{$smarty.get.parent}';
		
		{literal}
		$(document).ready(function(){			
			{/literal}{if !$level}{literal}
				Ext.grid.defaultColumn = function(config){
				    Ext.apply(this, config);
				    if(!this.id){
					this.id = Ext.id();
				    }
				    this.renderer = this.renderer.createDelegate(this);
				};

				Ext.grid.defaultColumn.prototype = {
				    init : function(grid){
					this.grid = grid;
					this.grid.on('render', function(){
					    var view = this.grid.getView();
					    view.mainBody.on('mousedown', this.onMouseDown, this);
					}, this);
				    },
				    onMouseDown : function(e, t){
					if( t.className && t.className.indexOf('x-grid3-cc-'+this.id) != -1 )
					{
					    e.stopEvent();
					    var index = this.grid.getView().findRowIndex(t);
					    var record = this.grid.store.getAt(index);
					    record.set(this.dataIndex, !record.data[this.dataIndex]);
							Ext.Ajax.request({
								waitMsg: 'Saving changes...',
								url: rlPlugins + 'multiField/admin/multi_formats.inc.php?q=ext',
								method: 'GET',
								params:
								{
									action: 'update',
									id: record.id,
									field: this.dataIndex,
									value: record.data[this.dataIndex]
								},
								failure: function()
								{
									Ext.MessageBox.alert('Error saving changes...');
								},
								success: function()
								{
									itemsGrid.store.commitChanges();
									itemsGrid.reload();
								}
							});
					}
				    },
				    renderer : function(v, p, record){
					p.css += ' x-grid3-check-col-td'; 	
						return '<div ext:qtip="'+lang['ext_set_default']+'" class="x-grid3-check-col'+(v?'-on':'')+' x-grid3-cc-'+this.id+'">&#160;</div>';
				    }
				};
				var defaultColumn = new Ext.grid.defaultColumn({
					header: lang['ext_default'],
					dataIndex: 'Default',
					width: 60,
					fixed: true
				});
			{/literal}{/if}{literal}
			itemsGrid = new gridObj({
				key: 'data_items',
				id: 'grid',
				ajaxUrl: rlPlugins + 'multiField/admin/multi_formats.inc.php?q=ext&parent='+parent,
				defaultSortField: 'name',
				remoteSortable: true,
				title: lang['ext_multi_formats_manager'],
				fields: [
					{name: 'ID', mapping: 'ID', type: 'int'},
					{name: 'name', mapping: 'name', type: 'string'},
					{name: 'Position', mapping: 'Position', type: 'int'},
					{name: 'Status', mapping: 'Status'},
					{name: 'Key', mapping: 'Key'},
					{name: 'Icons', mapping: 'Icons'},
					{/literal}{if !$level}{literal}
					{name: 'Default', mapping: 'Default'}
					{/literal}{/if}{literal}
				],
				columns: [
					{
						header: lang['ext_name'],
						dataIndex: 'name',
						width: 40,
						id: 'rlExt_item_bold'
					},
					{/literal}{if $order_type == 'position'}{literal}{
						header: lang['ext_position'],
						dataIndex: 'Position',
						width: 70,
						fixed: true,
						editor: new Ext.form.NumberField({
							allowBlank: false,
							allowDecimals: false
						}),
						renderer: function(val){
							return '<span ext:qtip="'+lang['ext_click_to_edit']+'">'+val+'</span>';
						}
					},{/literal}{/if}{literal}
					{/literal}{if !$level}{literal}
						defaultColumn,
					{/literal}{/if}{literal}
					{
						header: lang['ext_status'],
						dataIndex: 'Status',
						width: 80,
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
						width: 70,
						fixed: true,
						dataIndex: 'Key',
						sortable: false,
						renderer: function(val, obj, row){
							var out = "<center>";
							var splitter = false;
							var format_key = row.data.Key;

							var manage_href = rlUrlHome+"index.php?controller="+controller+"&amp;parent="+format_key;	
							out += "<a href="+manage_href+" ><img class='manage' ext:qtip='"+lang['ext_manage']+"' src='"+rlUrlHome+"img/blank.gif' /></a>";
							out += "<img class='edit' ext:qtip='"+lang['ext_edit']+"' src='"+rlUrlHome+"img/blank.gif' onClick='xajax_prepareEdit(\""+val+"\", \""+format_key+"\");$(\"#edit_item\").slideDown(\"normal\");$(\"#new_item\").slideUp(\"fast\");$(\"#ei_loading\").fadeIn(\"fast\")' />";
							out += "<img class='remove' ext:qtip='"+lang['ext_delete']+"' src='"+rlUrlHome+"img/blank.gif' onClick='rlConfirm( \""+lang['ext_notice_delete_item']+"\", \"xajax_deleteItem\", \""+val+"\", \"section_load\" )' />";
							out += "</center>";
							
							return out;
						}
					}
				]
			});

			{/literal}{if !$level}{literal}
				itemsGrid.plugins.push(defaultColumn);
			{/literal}{/if}{literal}

			itemsGrid.init();
			grid.push(itemsGrid.grid);
		});
		{/literal}
	{else}
		{literal}
		
		var multiFieldGrid;
		
		$(document).ready(function(){
			
			multiFieldGrid = new gridObj({
				key: 'multi_formats',
				id: 'grid',
				ajaxUrl: rlPlugins + 'multiField/admin/multi_formats.inc.php?q=ext',
				defaultSortField: 'name',
				title: lang['ext_multi_formats_manager'],
				fields: [
					{name: 'ID', mapping: 'ID', type: 'int'},
					{name: 'name', mapping: 'name', type: 'string'},
					{name: 'Position', mapping: 'Position', type: 'int'},
					{name: 'Status', mapping: 'Status'},
					{name: 'Key', mapping: 'Key'}
				],
				columns: [{
						header: lang['ext_name'],
						dataIndex: 'name',
						id: 'rlExt_item_bold',
						width: 40
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
						dataIndex: 'ID',
						sortable: false,
						renderer: function(val, obj, row){
							var out = "<center>";
							var splitter = false;
							var format_key = row.data.Key;

							var manage_href = rlUrlHome+"index.php?controller="+controller+"&amp;parent="+format_key;	
							out += "<a href="+manage_href+" ><img class='manage' ext:qtip='"+lang['ext_manage']+"' src='"+rlUrlHome+"img/blank.gif' /></a>";
							out += "<a href="+rlUrlHome+"index.php?controller="+controller+"&action=edit&amp;item="+format_key+"><img class='edit' ext:qtip='"+lang['ext_edit']+"' src='"+rlUrlHome+"img/blank.gif' /></a>";
							out += "<img class='remove' ext:qtip='"+lang['ext_delete']+"' src='"+rlUrlHome+"img/blank.gif' onClick='rlConfirm( \""+lang['ext_notice_delete_format']+"\", \"xajax_deleteFormat\", \""+format_key+"\", \"section_load\" )' />";
							out += "</center>";
							
							return out;
						}
					}
				]
			});
			
			multiFieldGrid.init();
			grid.push(multiFieldGrid.grid);
			
		});
		{/literal}
	{/if}

	{if $smarty.get.parent}
	{literal}
		function handleSourceActs()
		{
			$('#import_button').click(function(){
				$(this).val('{/literal}{$lang.loading}{literal}');

				var values = '';
				$('div.td_div input:checked').each(function(){
					values += $(this).val()+",";
				});
				xajax_importSource( values, $('input[name=table]').val(), $('input#ignore_one:checked').val() );
			});

			$('div.td_div input').click(function(){
				if( $('div.td_div input:checked').length  == 1 )
				{
					$('#checked_one_hint').fadeIn();
				}else
				{
					$('#checked_one_hint').fadeOut();
				}
			});
		}
	{/literal}
	{/if}
	//]]>
	</script>
{/if}

<script type="text/javascript">
{literal}
	$('#load_button').click(function(){
		if( $('#load_cont').css('display') == 'none' )
		{
			xajax_listSources('{$smarty.get.parent}');
		}
		show('load_cont');
		$('#edit_item').slideUp('fast');
		$('#new_item').slideUp('fast');
		$('#related_fields').slideUp('fast');
	});
{/literal}
</script>

<script type="text/javascript" src="{$smarty.const.RL_PLUGINS_URL}multiField/static/lib.js"></script>

<!-- importing -->
<script type="text/javascript">//<![CDATA[[
	importExport.phrases['completed'] = "{$lang.mf_import_completed}";
	importExport.config['per_run'] = {$config.mf_import_per_run};
//]]>
</script>

