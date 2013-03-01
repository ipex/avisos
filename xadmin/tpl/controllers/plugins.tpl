<!-- plugins tpl -->

<!-- navigation bar -->
<div id="nav_bar">
	<a href="javascript:void(0)" class="button_bar"><span class="left"></span><span class="center_search">{$lang.browse}</span><span class="right"></span></a>
</div>
<!-- navigation bar end -->

<div id="action_blocks">
	<div id="browse_area" class="hide">
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_start.tpl' block_caption=$lang.available_plugins}
			<div id="browse_content"></div>
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_end.tpl'}
	</div>
	
	<div id="update_area" class="hide">
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_start.tpl'}
		<div id="update_info">
			{assign var='replace_ver' value=`$smarty.ldelim`version`$smarty.rdelim`}
			{assign var='replace_name' value=`$smarty.ldelim`name`$smarty.rdelim`}
			<div>{$lang.plugin_update_request_hint|replace:$replace_ver:'<b><span id="update_version"></span></b>'|replace:$replace_name:'<span id="update_link"></span>'}</div>
			<input id="start_update" type="button" value="{$lang.update}" />
			<a onclick="$('#update_area').slideUp();" class="cancel" href="javascript:void(0);">{$lang.cancel}</a>
		</div>
		<div id="update_progress" class="hide">
			<div class="dark_12"><b id="plugin_name"></b> {$lang.plugin_is_updating}</div>
			<div class="progress static" style="padding: 5px 0 0;">{$lang.remote_progress_backingup}</div>
		</div>
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_end.tpl'}
	</div>
</div>

<script type="text/javascript">//<![CDATA[
var actions_locked = false;
var plugin_is_installing_phrase = '{$lang.plugin_is_installing}';
var plugin_updating_phrase = "{$lang.remote_progress_updating}";
var plugin_update_notice = "{$lang.remote_plugin_update_notice}";
var plugin_connect_phrase = "{$lang.remote_progress_connect}";
var plugin_install_notice = "{$lang.remote_plugin_install_notice}";
var plugin_installing_phrase = "{$lang.remote_progress_installing}";
var update_plugin_key;
var plugins_loaded = false;
{literal}

$(document).ready(function(){
	$('#start_update').click(function(){
		rlConfirm(plugin_update_notice, 'startUpdate');
	});
	
	$('#nav_bar a.button_bar').click(function(){
		if ( plugins_loaded )
		{
			$('#browse_area').slideToggle();
		}
		else
		{
			xajax_browsePlugins();
			$(this).find('span.center_search').html(lang['loading']);
		}
	});
});

var rlPluginRemoteInstall = function(){
	// install links handler
	$('a.remote_install').click(function(){
		if ( !actions_locked )
		{
			plugin_obj = this;
			rlConfirm(plugin_install_notice, 'startInstallation');
		}
	});
};

var startInstallation = function(){
	actions_locked = true;
	
	hideNotices();
	
	var key = $(plugin_obj).attr('name');
	var area = $(plugin_obj).closest('div.changelog_item');
	var name = $(area).find('a:first').html();
	var id = $(area).attr('id');
	var height = $(area).height()-16-2;
	height = height < 55 ? 'auto' : height;
	var width = $(area).width();
	
	/* set fixed height for main container */
	$(area).parent().height($(area).height());
	
	/* prepare HTML DOM */
	var html = ' \
	<div style="margin: 0 0 16px 0;height: '+ height +'px;width: '+ width +'px;position: absolute;padding: 0;" class="hide grey_area" id="'+ id +'_tmp"> \
		<div style="padding: 8px 10px 10px;"> \
			<div class="dark_13"><b>'+ name +'</b> '+ plugin_is_installing_phrase +'</div> \
			<div class="progress static" style="padding: 5px 0 0;"></div> \
		</div> \
	</div>';
	
	/* show progress bar */
	$(area).after(html);
	$(area).css({width: $(area).width(), position: 'absolute'}).fadeOut();
	$(area).next().fadeIn('normal', function(){
		$(area).css('position', 'relative');
		$(this).css({position: 'relative', width: 'auto'});
		$(this).find('.progress').html(plugin_connect_phrase);
		xajax_remoteInstall(key);
	});
};

var startUpdate = function(){
	$('#update_info').fadeOut(function(){
		$('#update_progress').fadeIn();
		xajax_remoteUpdate(update_plugin_key, true);
	});
};

var continueUpdating = function(key){
	$('div#progress div.progress').html(plugin_updating_phrase);
	xajax_update(key);
};

var continueInstallation = function(key){
	var area = $('div.changelog_item a[name='+ key +']').closest('div.changelog_item');
	$(area).next().find('div.progress').html(plugin_installing_phrase);
	
	xajax_install(key, 'true');
};

var hideProgressBar = function(){
	$('#update_progress').fadeOut();
};

{/literal}
//]]>
</script>

<!-- plugins grid -->
<div id="grid"></div>
<script type="text/javascript">//<![CDATA[
var listingPlansGrid;

{literal}
$(document).ready(function(){
	
	pluginsGrid = new gridObj({
		key: 'plugins',
		id: 'grid',
		ajaxUrl: rlUrlHome + 'controllers/plugins.inc.php?q=ext',
		defaultSortField: 'Name',
		title: lang['ext_plugins_manager'],
		fields: [
			{name: 'ID', mapping: 'ID'},
			{name: 'Name', mapping: 'Name', type: 'string'},
			{name: 'Key', mapping: 'Key'},
			{name: 'Description', mapping: 'Description', type: 'string'},
			{name: 'Version', mapping: 'Version'},
			{name: 'Status', mapping: 'Status'}
		],
		columns: [
			{
				header: lang['ext_name'],
				dataIndex: 'Name',
				width: 30,
				id: 'rlExt_item_bold'
			},{
				header: lang['ext_description'],
				dataIndex: 'Description',
				width: 60
			},{
				header: lang['ext_version'],
				dataIndex: 'Version',
				width: 12
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
				})
			},{
				header: lang['ext_actions'],
				width: 70,
				fixed: true,
				dataIndex: 'Key',
				sortable: false,
				renderer: function(value) {
					var complete = value.split('|');
					var out = "<center>";

					if (complete[1] == 'not_installed')
					{
						out += "<img class='install' title='"+lang['ext_install']+"' src='"+rlUrlHome+"img/blank.gif' onclick='xajax_install(\""+complete[0]+"\");$(this).animate({opacity: 0.5}, \"slow\").attr(\"onclick\", \"\");' />";
					}
					else
					{
						out += "<img class='update' title='"+lang['ext_check_for_update']+"' src='"+rlUrlHome+"img/blank.gif' onclick='xajax_checkForUpdate(\""+complete[0]+"\");' />";
						out += "<img class='uninstall' title='"+lang['ext_uninstall']+"' src='"+rlUrlHome+"img/blank.gif' onclick='rlConfirm( \""+lang['ext_plugin_uninstall']+"\", \"xajax_unInstall\", \""+Array(value)+"\" )' />";
					}
					out += "</center>";
					
					return out;
				}
			}
		]
	});
	
	{/literal}{rlHook name='apTplPluginsGrid'}{literal}
	
	pluginsGrid.init();
	grid.push(pluginsGrid.grid);
	
	pluginsGrid.grid.addListener('beforeedit', function(editEvent)
	{
		if( editEvent.value == 'not_installed' )
		{
			pluginsGrid.store.rejectChanges();
			Ext.MessageBox.alert(lang['ext_notice'], lang['ext_need_install']);
		}
	});
	
});
{/literal}
//]]>
</script>
<!-- plugins grid end -->

{rlHook name='apTplPluginsBottom'}

<!-- plugins tpl end -->