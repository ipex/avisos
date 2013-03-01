<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-gb" xml:lang="en-gb">
<head>

<title>
{$lang.rl_admin_panel}

{foreach from=$breadCrumbs item='bc_title'}
	&nbsp;&raquo;&nbsp;{$bc_title.name|strip_tags}
{/foreach}
</title>

<meta http-equiv="X-UA-Compatible" content="IE=9" />
<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1, maximum-scale=1" />
<meta name="generator" content="Flynax Classified Software" />
<meta http-equiv="Content-Type" content="text/html; charset={$config.encoding}" />

<link href="{$rlTplBase}css/ext/ext-all.css" type="text/css" rel="stylesheet" />
<link href="{$rlTplBase}css/ext/rlExt.css" type="text/css" rel="stylesheet" />
<link href="{$rlTplBase}css/style.css" type="text/css" rel="stylesheet" />
<link href="{$rlTplBase}css/jquery.ui.css" type="text/css" rel="stylesheet" />
<link href="{$rlTplBase}css/common.css" type="text/css" rel="stylesheet" />

<link href="{$smarty.const.RL_LIBS_URL}jquery/colorpicker/css/colorpicker.css" type="text/css" rel="stylesheet" />
{if isset($smarty.get.key) || isset($smarty.get.form)}
	<link href="{$rlTplBase}css/builder.css" type="text/css" rel="stylesheet" />
{/if}
<link type="image/x-icon" rel="shortcut icon" href="{$rlTplBase}img/favicon.ico" />

<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}jquery/jquery.js"></script>
<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}jquery/jquery.ui.js"></script>
<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}jquery/numeric.js"></script>
<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}jquery/cookie.js"></script>
<script type="text/javascript" src="{$rlBase}js/lib.js"></script>

{$ajaxJavascripts}

<script type="text/javascript">//<![CDATA[
	var rlUrlHome = '{$rlBase}';
	var rlPlugins = '{$smarty.const.RL_PLUGINS_URL}';
	var rlDateFormat = '{$smarty.const.RL_DATE_FORMAT}';
	var controller = '{$smarty.get.controller}';
	var rlCurrency = '{$config.system_currency}';
	
	var rlConfig = new Array();
	rlConfig['tpl_base'] = '{$smarty.const.RL_URL_HOME}{$smarty.const.ADMIN}/';
	rlConfig['libs_url'] = '{$smarty.const.RL_LIBS_URL}/';
	rlConfig['lang'] = '{$smarty.const.RL_LANG_CODE}/';
	rlConfig['fckeditor_bar'] = '{$config.fckeditor_bar}';
	rlConfig['messages_length'] = '{$config.messages_length}';
	
	{if $config.trash}
		var delete_mod = 'trash';
	{else}
		var delete_mod = 'delete';
	{/if}
	
	var lang = Array();
	
	{foreach from=$ext_phrases item='phrase' key='key'}
	//lang["{$key}"] = "{$phrase|replace:'/':'&002F;'}";
	lang["{$key}"] = "{$phrase}";
	{/foreach}
	
	lang['photo'] = '{$lang.photo}';
	lang['of'] = '{$lang.of}';
	lang['delete'] = '{$lang.delete}';
	lang['manage'] = '{$lang.manage}';
	lang['done'] = '{$lang.done}';
	lang['loading'] = '{$lang.loading}';
	lang['save'] = '{$lang.save}';
	lang['characters_left'] = '{$lang.characters_left}';
	
	var rights = Array();

	{if $smarty.session.sessAdmin.type == 'super'}
		{foreach from=$extended_sections item='aRight'}
			rights["{$aRight}"] = new Array({foreach from=$extended_modes item='mode' name='modeF'}"{$mode}"{if !$smarty.foreach.modeF.last},{/if}{/foreach});
		{/foreach}
	{else}
		{foreach from=$aRights item='aRight' key='rKey'}
			rights["{$rKey}"] = {if is_array($aRight)}new Array({foreach from=$aRight item='mode' name='modeF'}"{$mode}"{if !$smarty.foreach.modeF.last},{/if}{/foreach}){else}true{/if};
		{/foreach}
	{/if}
	
	var cKey = '{$cKey}';
	var apMenu = new Array();
//]]>
</script>

<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}extJs/ext-base.js"></script>
<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}extJs/ext-all.js"></script>
<script type="text/javascript" src="{$rlBase}js/ext/RowExpander.js"></script>
<script type="text/javascript" src="{$rlBase}js/ext/rlGrid.js"></script>
<!-- EXT js load end -->

{rlHook name='apTplHeader'}

</head>
<body>

<table class="sTable">
<tr>
	<td valign="top" rowspan="2" id="sidebar" {if $smarty.cookies.ap_menu_collapsed == 'true'}style="width: 61px"{/if}>
		<div style="min-height: 100%">
			<div class="header_left" {if $smarty.cookies.ap_menu_collapsed == 'true'}style="width: 61px"{/if}>
				<div id="outer_logo" {if $smarty.cookies.ap_menu_collapsed == 'true'}style="padding-left: 12px;"{/if}>
					<div id="logo" {if $smarty.cookies.ap_menu_collapsed == 'true'}style="width: 38px;"{/if}>
						<a href="{$rlBase}index.php" title="{$lang.rl_admin_panel}">&nbsp;</a>
					</div>
				</div>
			</div>
			
			<div class="middle_left" {if $smarty.cookies.ap_menu_collapsed == 'true'}style="width: 61px"{/if}>
				{assign var='sCookie' value=$smarty.cookies}
				
				<!-- menu nav bar -->
				<div id="menu_navbar">
					<div id="mode_switcher"></div>
				</div>
				<!-- menu nav bar end -->
	
				<!-- main menu collapsed -->
				<div id="mmenu_slide"{if $smarty.cookies.ap_menu_collapsed == 'false' || !isset($smarty.cookies.ap_menu_collapsed)} class="hide"{/if}>
				
					{foreach from=$mMenuItems item='mItem' name='menuS'}
						<div lang="{$mItem.Key}" class="scaption {if ( $smarty.get.controller && $smarty.get.controller|in_array:$mItem.Controllers_list ) || ( !$smarty.get.controller && 'home'|in_array:$mItem.Controllers_list )} active{/if}" id="mssection_{$mItem.ID}">
							<div class="outer">
								<div style="background-position: {if ( $smarty.get.controller && $smarty.get.controller|in_array:$mItem.Controllers_list ) || ( !$smarty.get.controller && 'home'|in_array:$mItem.Controllers_list )}-18{else}0{/if}px {$menu_icons[$mItem.Key]}px;"></div>
							</div>
						</div>
					{/foreach}
					
					{rlHook name='apTplMenuCollapsedEnd'}
				
				</div>
				<!-- main menu collapsed end -->
				
				<!-- main menu full -->
				<div id="mmenu_full"{if $smarty.cookies.ap_menu_collapsed == 'true'} class="hide"{/if}>
				
					{foreach from=$mMenuItems item='mItem' name='menuF'}
						<script type="text/javascript">
						apMenu['{$mItem.Key}'] = new Array();
						apMenu['{$mItem.Key}']['section_name'] = '{$mItem.name}';
						</script>
						
						<div id="msection_{$mItem.ID}">
							
							{assign var='ma_status' value='adMenu_'|cat:$mItem.ID}
							<div id="lb_status_{$mItem.ID}" class="caption{if ( $smarty.get.controller && $smarty.get.controller|in_array:$mItem.Controllers_list ) || ( !$smarty.get.controller && 'home'|in_array:$mItem.Controllers_list )}_active{/if}">
								<div class="icon" style="background-position: {if ( $smarty.get.controller && $smarty.get.controller|in_array:$mItem.Controllers_list )  || ( !$smarty.get.controller && 'home'|in_array:$mItem.Controllers_list )}-18px{else}0{/if} {$menu_icons[$mItem.Key]}px;"></div>
								<div class="name">{$mItem.name|replace:' ':'&nbsp;'}</div>
							</div>
			
							{assign var=m_index value='adMenu_'|cat:$mItem.ID}
							
							<div id="lblock_{$mItem.ID}" class="ms_container clear{if $sCookie.$m_index == 'hide' || $smarty.cookies.ap_menu_collapsed == 'true'} hide{if $smarty.cookies.ap_menu_collapsed == 'true' && isset($sCookie.$m_index) && $sCookie.$m_index != 'hide'} tmp_visible{/if}{elseif !isset($sCookie.$m_index) && !$smarty.foreach.menuF.first} hide{/if}">
			
								<!-- section content -->
								<div class="section" id="{$mItem.Key}_section">
								
								{assign var='aRights' value=$smarty.session.sessAdmin.rights}
								{assign var='itemCount' value=0}
								
								{foreach from=$mItem.child item='child' name='admMenu'}
									{assign var='childKey' value=$child.Key}
									
									<script type="text/javascript">
									apMenu['{$mItem.Key}']['{$child.Key}'] = new Array();
									apMenu['{$mItem.Key}']['{$child.Key}']['Name'] = "{$child.name}";
									apMenu['{$mItem.Key}']['{$child.Key}']['Controller'] = "{$child.Controller}";
									apMenu['{$mItem.Key}']['{$child.Key}']['Vars'] = "{$child.Vars}";
									</script>
									
									{if $config.admin_hide_denied_items && !$aRights.$childKey && $smarty.session.sessAdmin.type != 'super'}
										{assign var='itemCount' value=$itemCount+1}
										{if $smarty.foreach.admMenu.last && $smarty.foreach.admMenu.total == $itemCount}
											<script type="text/javascript">
											{literal}
											$(document).ready(function(){
												$('#msection_{/literal}{$mItem.ID}{literal}').slideUp(1);
											});
											{/literal}
											</script>
										{/if}
									{else}
										{assign var='mitem_status' value=''}
										
										{if $child.Controller == $smarty.get.controller && $child.Vars == 'status='|cat:$smarty.get.status}
											{assign var='mitem_status' value=' active'}
										{elseif $child.Controller == $smarty.get.controller && empty($child.Vars) && !isset($smarty.get.status)}
											{assign var='mitem_status' value=' active'}
										{elseif !$smarty.get.controller && $child.Controller == 'home'}
											{assign var='mitem_status' value=' active'}
										{/if}
										
										<script type="text/javascript">
											apMenu['{$mItem.Key}']['{$child.Key}']['Active'] = {if $mitem_status == ' active'}true{else}false{/if};
										</script>
										
										<div class="mitem{$mitem_status}" {if $mItem.Key == 'plugins' && $child.Key != 'plugins'}id="mPlugin_{$child.Plugin}"{/if}>
											<a {if $child.name|strlen > 22}title="{$child.name}"{/if} href="{$rlBase}index.php{if $child.Controller != ''}?controller={$child.Controller}{if $child.Vars}&amp;{$child.Vars}{/if}{/if}">{$child.name|replace:' ':'&nbsp;'}</a>
										</div>
									{/if}
								{/foreach}	
							
								</div>
								<!-- section content end -->
								
							</div>
							
						</div>
					{/foreach}
					
					{rlHook name='apTplMenuFullEnd'}
					
				</div>
				<!-- main menu full end -->
				
			</div>
		</div>
	</td>
	<td id="content" valign="top">
		<div class="header_right">
			<div class="outer">
				<div class="inner">
					{rlHook name='apTplHeaderNavBar'}
					
					<div id="admin_bar">
						<span class="dark_14">{$lang.welcome},</span>
						{if $aRights.admins.edit}
							<a href="{$rlBase}index.php?controller=admins&amp;action=edit&amp;admin={$smarty.session.sessAdmin.user_id}" class="dark_14">{$smarty.session.sessAdmin.name}</a>
						{else}
							<span class="dark_14">{$smarty.session.sessAdmin.name}</span>
						{/if}
						<div class="new_message">
							<a title="{$lang.my_messages}" href="{$rlBase}index.php?controller=messages"><img class="envelope" src="{$rlTplBase}img/blank.gif" alt="" /></a>
							{if $new_messages > 0}
								<a class="new" title="{$lang.new_message}" href="{$rlBase}index.php?controller=messages">{$new_messages}</a>
							{/if}
						</div>
						<a class="logout" href="javascript:void(0)" onclick="xajax_logOut();">{$lang.logout}</a>
					</div>
					<div id="buttons_bar">&nbsp;
						{rlHook name='apTplHeaderButton'}
						
						<a href="{$rlBase}index.php?system_info" target="_blank" class="button_link"><span class="left"></span><span class="center"><span class="info">{$lang.rl_system_info}</span></span><span class="right"></span></a>
						<a href="{$smarty.const.RL_URL_HOME}" target="_blank" class="button_link"><span class="left"></span><span class="center"><span>{$lang.front_end}</span></span><span class="right"></span></a>
					</div>
				</div>
			</div>
		</div>
