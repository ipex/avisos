<!-- content block -->

{if $pageInfo.Controller != 'home'}
	<!-- bread crumbs -->
	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'bread_crumbs.tpl'}
	<!-- bread crumbs end -->
	
	{if $pageInfo.Controller == 'listing_details'}
	<table class="sTable">
	<tr>
		<td>
	{/if}
	<h1>{$pageInfo.name}</h1>
	{if $pageInfo.Controller == 'listing_details'}
		</td>
		<td style="width: 29px;">
			<div class="star_icon icon" style="margin-{$text_dir_rev}:10px">
				<a id="fav_{$print.id}" title="{$lang.add_to_favorites}" href="javascript:void(0)">&nbsp;</a>
			</div>
		</td>
	</tr>
	</table>
	{/if}
{/if}

<div id="system_message">
	{if isset($errors)}
		<script type="text/javascript">//<![CDATA[
		var fixed_message = {if $fixed_message}false{else}true{/if};
		var error_fields = {if $error_fields}'{$error_fields}'{else}false{/if};

		var message_text = '<ul>';
		{foreach from=$errors item='error'}message_text += '<li>{$error}</li>';{/foreach}
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

{if $pageInfo.Controller != 'home'}<div class="content_container">{/if}
	{if $pageInfo.Page_type == 'system'}
		{include file=$content}
	{else}
		<div class="padding" style="line-height: 20px;">{$staticContent}</div>
	{/if}
{if $pageInfo.Controller != 'home'}</div>{/if}

<!-- content block end -->