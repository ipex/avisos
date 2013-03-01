<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>{foreach from=$title item='title_item' name='titleF'}{if $smarty.foreach.titleF.first}{$title_item}{else} &#171; {$title_item}{/if}{/foreach}</title>

<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1, maximum-scale=1" />
<meta name="generator" content="Flynax Classifieds Software - Mobile Version" />
<meta http-equiv="Content-Type" content="text/html; charset={$config.encoding}" />
<meta name="description" content="{$pageInfo.meta_description}" />
<meta name="Keywords" content="{$pageInfo.meta_keywords}" />
<link href="{$rlTplBase}css/mobile.css" type="text/css" rel="stylesheet" />
<link rel="apple-touch-icon-precomposed" href="{$rlTplBase}img/favicon.png" />
<link href="{$rlTplBase}css/jquery.ui.css" type="text/css" rel="stylesheet" />
<!--link href="{$rlTplBase}css/jquery.ui.css" type="text/css" rel="stylesheet" /-->
<link rel="shortcut icon" href="{$rlTplBase}img/favicon.ico" />
         
{if $smarty.const.RL_LANG_DIR == 'rtl'}
	<link href="{$rlTplBase}css/rtl.css" type="text/css" rel="stylesheet" />
	{assign var='text_dir' value='right'}
	{assign var='text_dir_rev' value='left'}
{else}
	{assign var='text_dir' value='left'}
	{assign var='text_dir_rev' value='right'}
{/if}

{if $rss}
	<link rel="alternate" type="application/rss+xml" title="{$rss.title}" href="{$rlBase}{if $config.mod_rewrite}{$pages.rss_feed}/{if $rss.item}?{$rss.item}{else}{if $rss.id}?id={$rss.id}{if ($rss.type || $rss.period) && $rss.id}&amp;{elseif ($rss.type || $rss.period) && $rss.id}?{/if}{/if}{if $rss.type}type={$rss.type}{/if}{if $rss.period}&amp;period={$rss.period}{/if}{/if}{else}?page={$pages.rss_feed}{if $rss.item}&amp;{$rss.item}{else}&amp;{if $rss.id}id={$rss.id}{if $rss.type || $rss.period}&amp;{/if}{/if}{if $rss.type}type={$rss.type}{/if}{if $rss.period}&amp;period={$rss.period}{/if}{/if}{/if}" />
{/if}

<script type="text/javascript">
	var rlUrlHome = '{$rlTplBase}';
	var rlUrlRoot = '{$rlBase}';
	var rlLangDir = '{$smarty.const.RL_LANG_DIR}';
	var rlPageInfo = new Array();
	rlPageInfo['key'] = '{$pageInfo.Key}';
	rlPageInfo['path'] = '{if $pageInfo.Path_real}{$pageInfo.Path_real}{else}{$pageInfo.Path}{/if}';
	var rlConfig = new Array();
	rlConfig['mod_rewrite'] = {$config.mod_rewrite};
	var lang = new Array();
	lang['photo'] = '{$lang.photo}';
	lang['of'] = '{$lang.of}';
	lang['remove_from_favorites'] = '{$lang.remove_from_favorites}';
	lang['add_to_favorites'] = '{$lang.add_to_favorites}';
	lang['notice_removed_from_favorites'] = '{$lang.notice_listing_removed_from_favorites}';
	lang['no_favorite'] = '{$lang.no_favorite}';
	lang['loading'] = '{$lang.loading}';
	
	var rlConfig = new Array();
	rlConfig['seo_url'] = '{$rlBase}';
	rlConfig['tpl_base'] = '{$rlTplBase}';
	rlConfig['files_url'] = '{$smarty.const.RL_FILES_URL}';
	rlConfig['libs_url'] = '{$smarty.const.RL_LIBS_URL}';
	rlConfig['mod_rewrite'] = {$config.mod_rewrite};
	rlConfig['sf_display_fields'] = {$config.sf_display_fields};
	rlConfig['account_password_strength'] = {$config.account_password_strength};
	rlConfig['messages_length'] = {$config.messages_length};
</script>

<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}javascript/flynax.lib.js"></script>
<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}jquery/jquery.js"></script>
<script type="text/javascript" src="{$rlTplBase}js/lib.js"></script>

{rlHook name='tplHeader'}

{$ajaxJavascripts}

</head>
<body>
<div class="main_container">

	<!-- header block -->
	<div class="{if $pageInfo.Controller == 'home'}hearde_block_home{else}hearde_block{/if}">
		<div id="logo">
			<a href="{$rlBase}">
				<img alt="{$config.site_name}" title="{$config.site_name}" src="{$rlTplBase}img/logo.png" />
			</a>
		</div>
		
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'lang_selector.tpl'}
	</div>
	
	{include file='menus'|cat:$smarty.const.RL_DS|cat:'main_menu.tpl'}
	<!-- header block end -->