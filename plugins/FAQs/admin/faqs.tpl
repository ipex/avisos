<!-- faqs tpl -->

<!-- navigation bar -->
<div id="nav_bar">
	{rlHook name='apTplFAQsNavBar'}
	
	{if $aRights.$cKey.add}
		<a href="{$rlBaseC}action=add" class="button_bar"><span class="left"></span><span class="center_add">{$lang.faq_add_faqs}</span><span class="right"></span></a>
	{/if}
	<a href="{$rlBase}index.php?controller={$smarty.get.controller}" class="button_bar"><span class="left"></span><span class="center_list">{$lang.faq_faqs_list}</span><span class="right"></span></a>
</div>
<!-- navigation bar end -->

{if $smarty.get.action}

	{assign var='sPost' value=$smarty.post}

	<!-- add faq  -->
	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_start.tpl'}
	<form action="{$rlBaseC}action={if $smarty.get.action == 'add'}add{elseif $smarty.get.action == 'edit'}edit&amp;faqs={$smarty.get.faqs}{/if}" method="post">
	<input type="hidden" name="submit" value="1" />
	{if $smarty.get.action == 'edit'}
		<input type="hidden" name="fromPost" value="1" />
	{/if}
	<table class="form">
	<tr>
		<td class="name">
			<span class="red">*</span>{$lang.title}
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
				<input type="text" name="name[{$language.Code}]" value="{$sPost.name[$language.Code]}" maxlength="350" class="w350" />
				{if $allLangs|@count > 1}
						<span class="field_description_noicon">{$lang.name} (<b>{$language.name}</b>)</span>
					</div>
				{/if}
			{/foreach}
		</td>
	</tr>
	<tr>
		<td class="name">
			<span class="red">*</span>{$lang.content}
		</td>
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
				{assign var='dCode' value='content_'|cat:$language.Code}
				{fckEditor name='content_'|cat:$language.Code width='100%' height='140' value=$sPost.$dCode}
				{if $allLangs|@count > 1}</div>{/if}
			{/foreach}
		</td>
	</tr>
	
	<tr>
		<td class="name"><span class="red">*</span>{$lang.faq_page_url}</td>
		<td class="field">
			<table>
			<tr>
				<td><span style="padding: 0 5px 0 0;" class="field_description_noicon">{$smarty.const.RL_URL_HOME}{$pages.faqs}/</span></td>
				<td><input name="path" type="text" value="{$sPost.path}" maxlength="40" /></td>
				<td><span class="field_description_noicon">.html</span></td>
			</tr>
			</table>
		</td>
	</tr>
	
	{if $smarty.get.action == 'edit'}
	<tr>
		<td class="name"><span class="red">*</span>{$lang.date}</td>
		<td class="field">
			<input class="date" name="date" type="text" value="{$sPost.date}" style="width: 120px;" maxlength="40" />
		</td>
	</tr>
	{/if}

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
	<!-- add faq end -->

{else}

	<!-- faqs grid -->
	<div id="grid"></div>
	<script type="text/javascript">//<![CDATA[
	var faqsGrid;
	
	{literal}
	$(document).ready(function(){
		
		faqsGrid = new gridObj({
			key: 'faqs',
			id: 'grid',
			ajaxUrl: rlPlugins + 'FAQs/admin/faqs.inc.php?q=ext',
			defaultSortField: 'Date',
			defaultSortType: 'DESC',
			title: lang['ext_faqs_manager'],
			fields: [
				{name: 'ID', mapping: 'ID'},
				{name: 'title', mapping: 'title'},
				{name: 'Status', mapping: 'Status'},
				{name: 'Date', mapping: 'Date', type: 'date', dateFormat: 'Y-m-d H:i:s'}
			],
			columns: [
				{
					header: lang['ext_title'],
					dataIndex: 'title',
					width: 60,
					id: 'rlExt_item_bold'
				},{
					header: lang['ext_add_date'],
					dataIndex: 'Date',
					width: 15,
					renderer: Ext.util.Format.dateRenderer(rlDateFormat.replace(/%/g, '').replace('b', 'M')),
					editor: new Ext.form.DateField({
						format: 'Y-m-d H:i:s'
					})
				},{
					header: lang['ext_status'],
					dataIndex: 'Status',
					width: 12,
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
					dataIndex: 'ID',
					sortable: false,
					renderer: function(data) {
						var out = "<center>";
						var splitter = false;
						
							out += "<a href='"+rlUrlHome+"index.php?controller="+controller+"&action=edit&faqs="+data+"'><img class='edit' ext:qtip='"+lang['ext_edit']+"' src='"+rlUrlHome+"img/blank.gif' /></a>";
							out += "<img class='remove' ext:qtip='"+lang['ext_delete']+"' src='"+rlUrlHome+"img/blank.gif' onclick='rlConfirm( \""+lang['ext_notice_'+delete_mod]+"\", \"xajax_deleteFAQs\", \""+Array(data)+"\", \"faqs_load\" )' />";
						
						out += "</center>";
						
						return out;
					}
				}
			]
		});
		
		faqsGrid.init();
		grid.push(faqsGrid.grid);
		
	});
	{/literal}
	//]]>
	</script>
	<!-- faqs grid end -->

{/if}

<!-- news tpl end -->