<!-- transactions tpl -->

<!-- navigation bar -->
<div id="nav_bar">
{if $smarty.get.module == 'payment_details'}
	{if $smarty.get.action}
	<a href="{$rlBaseC}module=payment_details" class="button_bar"><span class="left"></span>
		<span class="center_list">{$lang.bwt_payment_details}</span><span class="right"></span>
	</a>
	{else}
	<a href="{$rlBaseC}module=payment_details&amp;action=add" class="button_bar"><span class="left"></span>
		<span class="center_add">{$lang.bwt_add_item}</span><span class="right"></span>
	</a>
	{/if}
	<a href="{$rlBaseC|replace:'&amp;':''}" class="button_bar"><span class="left"></span>
		<span class="center_list">{$lang.bwt_transactions}</span><span class="right"></span>
	</a>
{else}
	<a href="{$rlBaseC}module=payment_details" class="button_bar"><span class="left"></span>
		<span class="center_list">{$lang.bwt_payment_details}</span><span class="right"></span>
	</a>
{/if}
</div>
<!-- navigation bar end -->

{if isset($smarty.get.action) && !isset($smarty.get.module)}
	{if $smarty.get.action == 'view'}
		{include file=$smarty.const.RL_PLUGINS|cat:'bankWireTransfer'|cat:$smarty.const.RL_DS|cat:'admin'|cat:$smarty.const.RL_DS|cat:'txn_details.tpl' txn_info=$txn_info}
	{/if}
{else}
	{assign var='plunginPath' value=$smarty.const.RL_PLUGINS|cat:'bankWireTransfer'|cat:$smarty.const.RL_DS}
	{if isset($smarty.get.module)}
		{include file=$plunginPath|cat:'admin'|cat:$smarty.const.RL_DS|cat:$smarty.get.module|cat:'.tpl'}
	{else}
		<!-- bwt transactions grid -->
		<div id="grid"></div>
		<script type="text/javascript">//<![CDATA[
		var bwtTransactionsGrid;

		{literal}
		$(document).ready(function(){
			
			bwtTransactionsGrid = new gridObj({
				key: 'bwt_transactions',
				id: 'grid',
				ajaxUrl: rlPlugins + 'bankWireTransfer/admin/bank_wire_transfer.inc.php?q=ext',
				defaultSortField: 'Date',
				remoteSortable: true,
				checkbox: true,
				actions: [
					[lang['ext_delete'], 'delete']
				],
				title: lang['ext_transactions_manager'],
				fields: [
					{name: 'Item', mapping: 'Item'},
					{name: 'Username', mapping: 'Username', type: 'string'},
					{name: 'Full_name', mapping: 'Full_name', type: 'string'},
					{name: 'Txn_ID', mapping: 'Txn_ID'},
					{name: 'Total', mapping: 'Total'},
					{name: 'Status', mapping: 'Status'},
					{name: 'Type', mapping: 'Type'},
					{name: 'ID', mapping: 'ID', type: 'int'},
					{name: 'Date', mapping: 'Date', type: 'date', dateFormat: 'Y-m-d H:i:s'}
				],
				columns: [
					{
						header: lang['ext_id'],
						dataIndex: 'ID',
						width: 3,
						id: 'rlExt_black_bold'
					},{
						header: lang['ext_item'],
						dataIndex: 'Item',
						width: 20,
						id: 'rlExt_item_bold',
						renderer: function(val){
							var item = val.split('|');
							return "<span ext:qtip='"+lang['ext_plan']+": <b>"+item[1]+"</b>'>"+item[0]+"</span>";
						}
					},{
						header: lang['ext_username'],
						dataIndex: 'Username',
						width: 15,
						renderer: function(username, obj, row){
							if ( username )
							{
								var full_name = trim(row.data.Full_name) ? ' ('+trim(row.data.Full_name)+')' : '';
								var out = '<a class="green_11_bg" href="'+rlUrlHome+'index.php?controller=accounts&action=view&username='+username+'" ext:qtip="'+lang['ext_click_to_view_details']+'">'+username+'</a>'+full_name;
							}
							else
							{
								var out = '<span class="delete">{/literal}{$lang.account_removed}{literal}</span>';
							}
							return out;
						}
					},{
						header: lang['ext_txn_id'],
						dataIndex: 'Txn_ID',
						width: 15
					},{
						header: lang['ext_total']+' ('+rlCurrency+')',
						dataIndex: 'Total',
						width: 5
					},{
						header: lang['ext_type'],
						dataIndex: 'Type',
						width: 10,
						css: 'font-weight: bold;'
					},{
						header: lang['ext_date'],
						dataIndex: 'Date',
						width: 10,
						renderer: Ext.util.Format.dateRenderer(rlDateFormat.replace(/%/g, '').replace('b', 'M'))
					},{
						header: lang['ext_status'],
						dataIndex: 'Status',
						width: 100,
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
						})
					},{
						header: lang['ext_actions'],
						width: 70,
						fixed: true,
						dataIndex: 'ID',
						sortable: false,
						renderer: function(data) {
							var out = "<center>";
							var splitter = false;
							
							out += "<a href='"+rlUrlHome+"index.php?controller="+controller+"&action=view&txn_id="+data+"'><img class='view' ext:qtip='"+lang['ext_view']+"' src='"+rlUrlHome+"img/blank.gif' /></a>";
							out += "<img class='remove' ext:qtip='"+lang['ext_delete']+"' src='"+rlUrlHome+"img/blank.gif' onClick='rlConfirm( \""+lang['ext_notice_'+delete_mod]+"\", \"xajax_deleteTransaction\", \""+data+"\", \"load\" )' />";
							 
							out += "</center>";

							return out;
						}
					}
				]
			});
			
			bwtTransactionsGrid.init();
			grid.push(bwtTransactionsGrid.grid);
			
			// actions listener
			bwtTransactionsGrid.actionButton.addListener('click', function()
			{
				var sel_obj = bwtTransactionsGrid.checkboxColumn.getSelections();
				var action = bwtTransactionsGrid.actionsDropDown.getValue();

				if (!action)
				{
					return false;
				}
				
				for( var i = 0; i < sel_obj.length; i++ )
				{
					bwtTransactionsGrid.ids += sel_obj[i].id;
					if ( sel_obj.length != i+1 )
					{
						transactionsGrid.ids += '|';
					}
				}
				
				if ( action == 'delete' )
				{
					Ext.MessageBox.confirm('Confirm', lang['ext_notice_'+delete_mod], function(btn){
						if ( btn == 'yes' )
						{
							xajax_deleteTransaction( bwtTransactionsGrid.ids );
						}
					});
				}
			});
			
		});
		{/literal}
		//]]>
		</script>
		<!-- bwt transactions grid end -->
	{/if}
{/if}