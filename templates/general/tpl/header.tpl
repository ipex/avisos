<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>

<title>{foreach from=$title item='title_item' name='titleF'}{if $smarty.foreach.titleF.first}{$title_item}{else} &#171; {$title_item}{/if}{/foreach}</title>

<meta http-equiv="X-UA-Compatible" content="IE=9" />
<meta name="generator" content="Flynax Classifieds Software" />
<meta http-equiv="Content-Type" content="text/html; charset={$config.encoding}" />
<meta name="description" content="{$pageInfo.meta_description|strip_tags}" />
<meta name="Keywords" content="{$pageInfo.meta_keywords|strip_tags}" />
<link href="{$rlTplBase}css/style.css" type="text/css" rel="stylesheet" />
<link href="{$rlTplBase}css/common.css" type="text/css" rel="stylesheet" />
<link href="{$smarty.const.RL_LIBS_URL}jquery/fancybox/jquery.fancybox.css" type="text/css" rel="stylesheet" />
{if $config.gallery_slideshow}
<link href="{$smarty.const.RL_LIBS_URL}jquery/fancybox/helpers/jquery.fancybox-buttons.css" type="text/css" rel="stylesheet" />
{/if}
<link href="{$rlTplBase}css/jquery.ui.css" type="text/css" rel="stylesheet" />
<link type="image/x-icon" rel="shortcut icon" href="{$rlTplBase}img/favicon.ico" />

{if $smarty.const.RL_LANG_DIR == 'rtl'}
	<link href="{$rlTplBase}css/rtl.css" type="text/css" rel="stylesheet" />
	{assign var='text_dir' value='right'}
	{assign var='text_dir_rev' value='left'}
{else}
	{assign var='text_dir' value='left'}
	{assign var='text_dir_rev' value='right'}
{/if}

{if $rss}
	<link rel="alternate" type="application/rss+xml" title="{$rss.title}" href="{$rlBase}{if $config.mod_rewrite}{$pages.rss_feed}/{if $rss.item}{$rss.item}/{/if}{if $rss.id}{$rss.id}/{/if}{else}?page={$pages.rss_feed}{if $rss.item}&amp;item={$rss.item}{/if}{if $rss.id}&amp;id={$rss.id}{/if}{/if}" />
{/if}

<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}jquery/jquery.js"></script>
<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}jquery/jquery.color.js"></script>
<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}jquery/cookie.js"></script>
<script type="text/javascript" src="{$rlTplBase}js/lib.js"></script>
<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}javascript/flynax.lib.js"></script>
<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}jquery/jquery.ui.js"></script>
<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}jquery/datePicker/i18n/ui.datepicker-{$smarty.const.RL_LANG_CODE|lower}.js"></script>

{include file='js_config.tpl'}

{rlHook name='tplHeader'}

{$ajaxJavascripts}

</head>
<body>
	<div id="content_height">
		<!-- header -->
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'user_navbar.tpl'}
		
		<div id="header">
			<div id="logo">
				<a href="{$rlBase}" title="{$config.site_name}">
					<img alt="" src="{$rlTplBase}img/{if $smarty.const.RL_LANG_DIR == 'rtl'}rtl/{/if}logo.png" />
				</a>
			</div>
			
			<div id="search_block">
				<form method="post" action="{$rlBase}{if $config.mod_rewrite}{$pages.search}.html{else}?page={$pages.search}{/if}">
					<input type="hidden" name="form" value="keyword_search" />
					
					<table>
					<tr>
						<td>
							<div class="container">
								<div class="caption">{$lang.search}</div>
								<input id="autocomplete" type="text" maxlength="255" name="f[keyword_search]" {if $smarty.post.f.keyword_search}value="{$smarty.post.f.keyword_search}"{/if}/>
								<div class="advanced"><a title="{$lang.advanced_search}" href="{$rlBase}{if $config.mod_rewrite}{$pages.search}.html{else}?page={$pages.search}{/if}">{$lang.advanced_search}</a></div>
							</div>
						</td>
						<td class="search_submit">
							<table>
							<tr>
								<td><div class="left"></div></td>
								<td>
									<div class="center">
										<input type="submit" value="{$lang.search}" />
									</div>
								</td>
								<td><div class="right"></div></td>
							</tr>
							</table>
						</td>
					</tr>
					</table>
				</form>
				
				<script type="text/javascript">
				var ac_php = '{$rlTplBase}autocomplete.inc.php';
				var view_details = '{$lang.view_details}';
				var join_date = '{$lang.join_date}';
				var category_phrase = '{$lang.category}';
				var pre_search = '{if $lang.preliminary_results}{$lang.preliminary_results}{else}Preliminary Results{/if}';
				
				{literal}
				$(document).ready(function(){
					$('input#autocomplete').vsAutoComplete();
				});
				{/literal}
				</script>
				<script type="text/javascript" src="{$rlTplBase}js/jquery.autocomplete.js"></script>
			</div>
		</div>
		<!-- header end -->
		
		<div id="main_container">