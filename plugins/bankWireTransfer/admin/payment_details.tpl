<!-- banner plans tpl -->

{if $smarty.get.action}
{assign var='sPost' value=$smarty.post}

<!-- add/edit banner plan -->
{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_start.tpl'}
<form action="{$rlBaseC}module=payment_details&amp;action={if $smarty.get.action == 'add'}add{else}edit&amp;item={$smarty.get.item}{/if}" method="post">
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
		<td class="name"><span class="red">*</span>{$lang.name}</td>
		<td>
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
				<textarea rows="" cols="" name="description[{$language.Code}]">{$sPost.description[$language.Code]}</textarea>
				{if $allLangs|@count > 1}</div>{/if}
			{/foreach}
		</td>
	</tr>
	<tr>
		<td class="no_divider"></td>
		<td class="field">
			<input type="submit" value="{if $smarty.get.action == 'edit'}{$lang.edit}{else}{$lang.add}{/if}" />
		</td>
	</tr>
	</table>
</form>
{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_end.tpl'}
<!-- add/edit banner plan end -->

{else}

<!-- payment details tpl -->
<div id="grid"></div>
<script type="text/javascript">//<![CDATA[
var bwtPaymentDetails;

{literal}
$(document).ready(function(){

	bwtPaymentDetails = new gridObj({
		key: 'paymentDetails',
		id: 'grid',
		ajaxUrl: rlPlugins + 'bankWireTransfer/admin/payment_details.inc.php?q=ext',
		defaultSortField: false,
		title: lang['ext_payment_details_manager'],
		fields: [
			{name: 'ID', mapping: 'ID'},
			{name: 'Key', mapping: 'Key'},
			{name: 'name', mapping: 'name'},
			{name: 'description', mapping: 'description'},
			{name: 'Position', mapping: 'Position', type: 'int'},
		],
		columns: [
			{
				header: lang['ext_name'],
				dataIndex: 'name',
				id: 'rlExt_item_bold',
				width: 20
			},{
				header: lang['ext_description'],
				dataIndex: 'description',
				id: 'rlExt_item_bold',
				width: 60
			},{
				header: lang['ext_position'],
				dataIndex: 'Position',
				width: 6,
				editor: new Ext.form.NumberField({
					allowBlank: false,
					allowDecimals: false
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

					out += "<a href='"+ rlUrlHome +"index.php?controller="+ controller +"&module=payment_details&action=edit&item="+ data +"'><img class='edit' ext:qtip='"+ lang['ext_edit'] +"' src='"+ rlUrlHome +"img/blank.gif' /></a>";
					out += "<img class='remove' ext:qtip='"+ lang['ext_delete'] +"' src='"+ rlUrlHome +"img/blank.gif' onclick='rlConfirm( \""+ lang['ext_notice_delete'] +"\", \"xajax_deleteItem\", \""+ Array(data) +"\", \"section_load\" )' />";
					out += "</center>";

					return out;
				}
			}
		]
	});

	bwtPaymentDetails.init();
	grid.push(bwtPaymentDetails.grid);

});
{/literal}
//]]>
</script>

{/if}

<!-- payment details tpl end -->