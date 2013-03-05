<!-- content -->

<!-- long top blocks area -->
{if $blocks.long_top}	
	<div class="ling_top_block">
		{foreach from=$blocks item='block'}
		{if $block.Side == 'long_top'}
			{include file='blocks'|cat:$smarty.const.RL_DS|cat:'blocks_manager.tpl' block=$block}
		{/if}
		{/foreach}
	</div>
{/if}
<!-- long top blocks area -->

<div id="content">
	<table class="content">
	<tr>
		<!-- left blocks area -->
		{if $blocks.left}	
			<td class="left{if !$blocks.right} wide{/if}">
				{foreach from=$blocks item='block'}
				{if $block.Side == 'left'}
					{include file='blocks'|cat:$smarty.const.RL_DS|cat:'blocks_manager.tpl' block=$block}
				{/if}
				{/foreach}
			</td>
		{/if}
		<!-- left blocks area end -->
		
		<td class="content">

			{if $pageInfo.Key != 'home'}
				{if $print || $navIcons}
					<div class="fleft">
						<h1>{$pageInfo.name}</h1>
					</div>
					
					<div class="fright" id="content_nav_icons">	
						{if !empty($navIcons)}
							{foreach from=$navIcons item='icon'}
								{$icon}
							{/foreach}
						{/if}
						
						{rlHook name='pageNavIcons'}
						{if $print}
							<a class="print" title="{$lang.print_page}" target="_blank" href="{$rlBase}{if $config.mod_rewrite}{$pages.print}.html?item={$print.item}{if $print.id}&amp;id={$print.id}{/if}{if $print.type}&amp;type={$print.type}{/if}{if $print.period}&amp;period={$print.period}{/if}{else}?page={$pages.print}&amp;item={$print.item}{if $print.id}&amp;id={$print.id}{/if}{if $print.type}&amp;type={$print.type}{/if}{if $print.period}&amp;period={$print.period}{/if}{/if}"><span></span></a>
						{/if}
						{*if $rss}
							<a class="rss" title="{$lang.rss_feed}" target="_blank" href="{$rlBase}{if $config.mod_rewrite}{$pages.rss_feed}/{if $rss.item}{$rss.item}/{/if}{if $rss.id}{$rss.id}/{/if}{else}?page={$pages.rss_feed}{if $rss.item}&amp;item={$rss.item}{/if}{if $rss.id}&amp;id={$rss.id}{/if}{/if}"><span></span></a>
						{/if*}
					</div>
					<div class="clear"></div>
				{else}
					<h1>{$pageInfo.name}</h1>
				{/if}
			{/if}
			
			<div id="system_message">
				{if isset($errors)}
					<script type="text/javascript">//<![CDATA[
					var fixed_message = {if $fixed_message}false{else}true{/if};
					var error_fields = {if $error_fields}'{$error_fields}'{else}false{/if};

					var message_text = '<ul>';
					{foreach from=$errors item='error'}message_text += '<li>{$error|regex_replace:"/[\r\t\n]/":"<br />"}</li>';{/foreach}
					message_text += '</ul>';
					{literal}
					
					$(document).ready(function(){
						printMessage('error', message_text, error_fields, fixed_message);
					});
					
					{/literal}
					//]]>
					</script>
				{/if}
				{if isset($pNotice)}
					<script type="text/javascript">
					var message_text = '{$pNotice}';
					{literal}
					
					$(document).ready(function(){
						printMessage('notice', message_text, false, true);
					});
					
					{/literal}
					</script>
				{/if}
				{if isset($pAlert)}
					<script type="text/javascript">
					var message_text = '{$pAlert}';
					{literal}
					
					$(document).ready(function(){
						printMessage('warning', message_text, false, true);
					});
					
					{/literal}
					</script>
				{/if}
				
				<!-- no javascript mode -->
				<noscript>
				<div class="warning" style="margin-top: 3px;">
					<div class="inner">
						<div class="icon"></div>
						<div class="message">{$lang.no_javascript_warning}</div>
					</div>
				</div>
				</noscript>
				<!-- no javascript mode end -->
			</div>
	
			{if $blocks.top}
			<!-- top blocks area -->
				{foreach from=$blocks item='block'}
				{if $block.Side == 'top'}
					{include file='blocks'|cat:$smarty.const.RL_DS|cat:'blocks_manager.tpl' block=$block}
				{/if}
				{/foreach}
			<!-- top blocks area end -->
			{/if}
			
			<div id="controller_area">
				{if $pageInfo.Page_type == 'system'}
					{include file=$content}
				{else}
					<div class="static-content highlight">{$staticContent}</div>
				{/if}
			</div>
			
			<!-- middle blocks area -->
			{if $blocks.middle}
				{foreach from=$blocks item='block'}
					{if $block.Side == 'middle'}
						{include file='blocks'|cat:$smarty.const.RL_DS|cat:'blocks_manager.tpl' block=$block}
					{/if}
				{/foreach}
			{/if}
			<!-- middle blocks area end -->
			
			{if $blocks.middle_left || $blocks.middle_right}
			<!-- middle blocks area -->
			<table class="fixed">
			<tr>
				<td valign="top">
				{foreach from=$blocks item='block'}
				{if $block.Side == 'middle_left'}
					{include file='blocks'|cat:$smarty.const.RL_DS|cat:'blocks_manager.tpl' block=$block}
				{/if}
				{/foreach}
				</td>
				<td style="width: 10px"></td>
				<td valign="top">
				{foreach from=$blocks item='block'}
				{if $block.Side == 'middle_right'}
					{include file='blocks'|cat:$smarty.const.RL_DS|cat:'blocks_manager.tpl' block=$block}
				{/if}
				{/foreach}
				</td>
			</tr>
			</table>
			<!-- middle blocks area end -->
			{/if}
			
			{if $blocks.bottom}
			<!-- bottom blocks area -->
			<div {if !$blocks.middle_left && !$blocks.middle_left}style="margin-top: 7px;"{/if}>
				{foreach from=$blocks item='block'}
				{if $block.Side == 'bottom'}
					{include file='blocks'|cat:$smarty.const.RL_DS|cat:'blocks_manager.tpl' block=$block}
				{/if}
				{/foreach}
			</div>
			<!-- bottom blocks area end -->
			{/if}
		
		</td>
		
		<!-- right blocks area -->
		{if $blocks.right}	
			<td class="right{if !$blocks.left} wide{/if}">
				{foreach from=$blocks item='block'}
				{if $block.Side == 'right'}
					{include file='blocks'|cat:$smarty.const.RL_DS|cat:'blocks_manager.tpl' block=$block}
				{/if}
				{/foreach}
			</td>
		{/if}
		<!-- right blocks area end -->
	</tr>
	</table>
</div>

<!-- content end -->