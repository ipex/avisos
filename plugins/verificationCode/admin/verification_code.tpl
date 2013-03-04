<!-- verification_code tpl -->

<!-- navigation bar -->
<div id="nav_bar">
	{if !$smarty.get.action} 
		<a href="{$rlBaseC}&amp;action=add" class="button_bar"><span class="left"></span>
			<span class="center_add">{$lang.vc_add_item}</span><span class="right"></span>
		</a>
	{/if}
	<a href="{$rlBaseC|replace:'&amp;':''}" class="button_bar"><span class="left"></span>
		<span class="center_list">{$lang.verification_code_list}</span><span class="right"></span>
	</a>
</div>
<!-- navigation bar end -->

{if isset($smarty.get.action)}

	{assign var='sPost' value=$smarty.post}

	<!-- add/edit verification_code -->
	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_start.tpl'}
	<form action="{$rlBaseC}&amp;action={if $smarty.get.action == 'add'}add{else}edit&amp;item={$smarty.get.item}{/if}" method="post">
		<input type="hidden" name="submit" value="1" />
		{if $smarty.get.action == 'edit'}
			<input type="hidden" name="fromPost" value="1" />
		{/if}
		<table class="form">
			<tr>
				<td class="name"><span class="red">*</span>{$lang.vc_name}</td>
				<td>
					<input type="text" name="name" value="{$sPost.name}" maxlength="255" />
				</td>
			</tr>
			<tr>
				<td class="name"><span class="red">*</span>{$lang.vc_position}</td>
				<td class="field">
					<select name="position" class="login_input_select">
						<option value="">{$lang.select}</option>
						<option value="header" {if $sPost.position == 'header'}selected="selected"{/if}>{$lang.vc_position_header}</option>
						<option value="footer" {if $sPost.position == 'footer'}selected="selected"{/if}>{$lang.vc_position_footer}</option> 
					</select>
				</td>
			</tr>
			<tr>
				<td class="name"><span class="red">*</span>{$lang.vc_content}</td>
				<td class="field">                        	
					<textarea cols="50" rows="5" name="content">{$sPost.content}</textarea>
				</td>
			</tr>
			<tr>
				<td class="name">{$lang.show_on_pages}</td>
				<td class="field" id="pages_obj">
					<fieldset class="light">
						{assign var='pages_phrase' value='admin_controllers+name+pages'}
						<legend id="legend_pages" class="up">{$lang.$pages_phrase}</legend>
						<div id="pages">
							<div id="pages_cont" {if !empty($sPost.show_on_all) || empty($sPost.pages)}style="display: none;"{/if}>
								{assign var='bPages' value=$sPost.pages}
								<table class="sTable" style="margin-bottom: 15px;">
								<tr>
									<td valign="top">
									{foreach from=$pages item='page' name='pagesF'}
									{assign var='pId' value=$page.ID}
									<div style="padding: 2px 8px;">
										<input class="checkbox" {if isset($bPages.$pId)}checked="checked"{/if} id="page_{$page.ID}" type="checkbox" name="pages[{$page.ID}]" value="{$page.ID}" /> <label class="cLabel" for="page_{$page.ID}">{$page.name}</label>
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
							</div>
							
							<div class="grey_area" style="margin: 0 0 5px;">
								<label><input id="show_on_all" {if $sPost.show_on_all || empty($sPost.pages)}checked="checked"{/if} type="checkbox" name="show_on_all" value="true" /> {$lang.sticky}</label>
								<span id="pages_nav" {if $sPost.show_on_all || empty($sPost.pages)}class="hide"{/if}>
									<span onclick="$('#pages_cont input').attr('checked', true);" class="green_10">{$lang.check_all}</span>
									<span class="divider"> | </span>
									<span onclick="$('#pages_cont input').attr('checked', false);" class="green_10">{$lang.uncheck_all}</span>
								</span>
							</div>
						</div>
					</fieldset>
					
					<script type="text/javascript">
					{literal}
					
					$(document).ready(function() {
						$('#legend_pages').click(function() {
							fieldset_action('pages');
						});
						
						$('input#show_on_all').click(function() {
							$('#pages_cont').slideToggle();
							$('#pages_nav').fadeToggle();
						});
						
						$('#pages input').click(function() {
							if ( $('#pages input:checked').length > 0 )
							{
								//$('#show_on_all').prop('checked', false);
							}
						});
					});
					
					{/literal}
					</script>
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
	<!-- add/edit verification_code end -->

{else}
	<!-- ext grid -->
	<div id="grid"></div>
	<script type="text/javascript"> //<![CDATA[
	var verificationCodeGrid;

	{literal}
	$(document).ready(function() {
		
		verificationCodeGrid = new gridObj({
			key: 'verificationCode',
			id: 'grid',
			ajaxUrl: rlPlugins + 'verificationCode/admin/verification_code.inc.php?q=ext',
			defaultSortField: 'Date',
			remoteSortable: true,
			checkbox: true,
			actions: [
				[lang['ext_delete'], 'delete']
			],
			title: lang['ext_vc_manager'],
			fields: [
				{name: 'Name', mapping: 'Name'},
				{name: 'Position', mapping: 'Position'},
				{name: 'Content', mapping: 'Content', type: 'string'},
				{name: 'Status', mapping: 'Status'},
				{name: 'ID', mapping: 'ID', type: 'int'},
				{name: 'Date', mapping: 'Date', type: 'date', dateFormat: 'Y-m-d H:i:s'}
			],
			columns: [
				{
					header: lang['ext_id'],
					dataIndex: 'ID',
					width: 35,
					fixed: true,
					id: 'rlExt_black_bold'
				},{
					header: lang['ext_vc_name'],
					dataIndex: 'Name',
					width: 40
				},{
					header: lang['ext_vc_position'],
					dataIndex: 'Position',
					width: 100,
					fixed: true
				},{
					header: lang['ext_date'],
					dataIndex: 'Date',
					width: 80,
					fixed: true,
					renderer: Ext.util.Format.dateRenderer(rlDateFormat.replace(/%/g, '').replace('b', 'M'))
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
					width: 80,
					fixed: true,
					dataIndex: 'ID',
					sortable: false,
					renderer: function(data) {
						var out = "<center>";
						var splitter = false;
						
						out += "<a href='"+rlUrlHome+"index.php?controller="+controller+"&action=edit&item="+data+"'><img class='edit' ext:qtip='"+lang['ext_view']+"' src='"+rlUrlHome+"img/blank.gif' /></a>";
						out += "<img class='remove' ext:qtip='"+lang['ext_delete']+"' src='"+rlUrlHome+"img/blank.gif' onClick='rlConfirm( \""+lang['ext_notice_'+delete_mod]+"\", \"xajax_deleteItem\", \""+data+"\", \"load\" )' />";
						 
						out += "</center>";

						return out;
					}
				}
			]
		});
		
		verificationCodeGrid.init();
		grid.push(verificationCodeGrid.grid);
		
		// actions listener
		verificationCodeGrid.actionButton.addListener('click', function()
		{
			var sel_obj = verificationCodeGrid.checkboxColumn.getSelections();
			var action = verificationCodeGrid.actionsDropDown.getValue();

			if ( !action )
			{
				return false;
			}
			
			for( var i = 0; i < sel_obj.length; i++ )
			{
				verificationCodeGrid.ids += sel_obj[i].id;
				if ( sel_obj.length != i+1 )
				{
					verificationCodeGrid.ids += '|';
				}
			}
			
			if ( action == 'delete' )
			{
				Ext.MessageBox.confirm('Confirm', lang['ext_notice_'+delete_mod], function(btn) {
					if ( btn == 'yes' )
					{
						xajax_deleteItem( verificationCodeGrid.ids );
					}
				});
			}
		});
		
	});
	{/literal}
	//]]>
	</script>
	<!-- ext grid end -->
{/if}

<!-- verification_code tpl -->