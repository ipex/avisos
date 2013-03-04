<!-- auto reg prevent -->

<!-- navigation bar -->
<div id="nav_bar">
	<a href="javascript:void(0)" onclick="show('add_prevent', '#action_blocks div');" class="button_bar">
		<span class="left"></span><span class="center_add">{$lang.add}</span><span class="right"></span>
	</a>
</div>
<!-- navigation bar end -->

<div id="action_blocks">
	<div id="add_prevent" class="hide">
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_start.tpl' block_caption=$lang.autoRegPrevent_addItem}
		<form onsubmit="addSpamers();return false;" action="" method="post">
		<table class="form">
		<tr>
			<td class="name">{$lang.username}</td>
			<td class="field">
				<input type="text" id="arp_username" style="width: 200px;" maxlength="60" />
			</td>
		</tr>
		<tr>
			<td class="name">{$lang.mail}</td>
			<td class="field">
				<input type="text" id="arp_mail" style="width: 200px;" maxlength="60" />
			</td>
		</tr>
		<tr>
			<td class="name">{$lang.autoRegPrevent_ext_ip}</td>
			<td class="field">
				<input type="text" id="arp_ip" style="width: 200px;" maxlength="60" />
			</td>
		</tr>
		<tr>
			<td></td>
			<td class="field">
				<input type="submit" name="item_submit" value="{$lang.add}" />
				<a onclick="$('#add_prevent').slideUp('normal')" href="javascript:void(0)" class="cancel">{$lang.close}</a>
			</td>
		</tr>
		</table>
		</form>
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_end.tpl'}

		<script type="text/javascript">
		{literal}
			var addSpamers = function() {
				var username = $.trim($('input#arp_username').val());
				var email = $.trim($('input#arp_email').val());
				var ip = $.trim($('input#arp_ip').val());

				if ( username == '' && email == '' && ip == '' ) {
					printMessage('error', lang['autoRegPrevent_fillOutNotice']);
				}
				else {
					var ipPattent = /[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/;
					if ( ip && !ipPattent.test(ip) ) {
						printMessage('error', lang['autoRegPrevent_invalidIp']);
					}
					else {
						$('input[name=item_submit]').val(lang['ext_loading']);
						xajax_addSpamers(username, email, ip);
					}
				}
			}
		{/literal}
		</script>
	</div>
</div>

<div id="grid"></div>
<script type="text/javascript">//<![CDATA[
var autoRegPrevent;

{literal}
$(document).ready(function(){
	autoRegPrevent = new gridObj({
		key: 'autoRegPrevent',
		id: 'grid',
		ajaxUrl: rlPlugins + 'autoRegPrevent/admin/auto_reg_prevent.inc.php?q=ext',
		defaultSortField: 'Date',
		title: lang['autoRegPrevent_ext_manager'],
		fields: [
			{name: 'ID', mapping: 'ID'},
			{name: 'Username', mapping: 'Username'},
			{name: 'Mail', mapping: 'Mail'},
			{name: 'IP', mapping: 'IP'},
			{name: 'Reason', mapping: 'Reason'},
			{name: 'Date', mapping: 'Date', type: 'date', dateFormat: 'Y-m-d H:i:s'},
			{name: 'Status', mapping: 'Status'}
		],
		columns: [
			{
				header: lang['ext_username'],
				dataIndex: 'Username',
				width: 50
			},{
				header: lang['ext_email'],
				dataIndex: 'Mail',
				width: 50
			},{
				header: '{/literal}{$lang.autoRegPrevent_ext_ip}{literal}',
				dataIndex: 'IP',
				width: 30
			},{
				header: lang['autoRegPrevent_ext_reason'],
				dataIndex: 'Reason',
				width: 30,
				id: 'rlExt_item'
			},{
				header: lang['autoRegPrevent_ext_date_reg'],
				dataIndex: 'Date',
				width: 20,
				renderer: Ext.util.Format.dateRenderer(rlDateFormat.replace(/%/g, '').replace('b', 'M'))
			},{
				header: lang['ext_status'],
				dataIndex: 'Status',
				width: 20,
				editor: new Ext.form.ComboBox({
					{/literal}
					store: [
						['block', '{$lang.autoRegPrevent_status_block}'],
						['unblock', '{$lang.autoRegPrevent_status_unblock}']
					],
					{literal}
					mode: 'local',
					typeAhead: true,
					triggerAction: 'all',
					selectOnFocus: true
				}),
				renderer: function(val) {
					return '<div ext:qtip="'+lang['ext_click_to_edit']+'">'+val+'</div>';
				}
			}
		]
	});

	autoRegPrevent.init();
	grid.push(autoRegPrevent.grid);
});
{/literal}
//]]>
</script>

<!-- auto reg prevent end -->