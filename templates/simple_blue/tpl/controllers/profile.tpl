<!-- my profile -->

<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}jquery/jquery.qtip.js"></script>
<script type="text/javascript">flynax.qtip();</script>
<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}jquery/jquery.textareaCounter.js"></script>
<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}ckeditor/ckeditor.js"></script>

<!-- tabs -->
<div class="tabs">
	<div class="left"></div>
	<ul>
		{foreach from=$tabs item='tab' name='tabF'}
		<li class="{if $smarty.foreach.tabF.first}first{/if}{if $tab.active} active{/if}" id="tab_{$tab.key}">
			<span class="left">&nbsp;</span>
			<span class="center"><span>{$tab.name}</span></span>
			<span class="right">&nbsp;</span>
		</li>
		{/foreach}
	</ul>
	<div class="right"></div>
</div>
<div class="clear"></div>
<!-- tabs end -->

<!-- profile tab -->
<div id="area_profile" class="tab_area {if $smarty.request.info == 'account'}hide{/if}">
	<div class="highlight">
		
		<form method="post" action="{$rlBase}{if $config.mod_rewrite}{$pageInfo.Path}.html{else}?page={$pageInfo.Path}{/if}" enctype="multipart/form-data">
			<input type="hidden" name="info" value="profile" />
			<input type="hidden" name="fromPost_profile" value="1" />

			<table class="sTable">
			<tr>
				<td valign="top" class="thumbnail">
					<div class="canvas {if $profile_info.Photo}active{/if}">
						<img class="preview {if $profile_info.Photo} thumbnail{/if}" src="{if $profile_info.Photo}{$smarty.const.RL_FILES_URL}{$profile_info.Photo}{else}{$rlTplBase}img/no-account.png{/if}" alt="" />
						<img src="{$rlTplBase}img/blank.gif" class="delete {if $profile_info.Photo}ajax{/if}" alt="{$lang.delete}" title="{$lang.delete}" />
					</div>
					
					<label class="upload">
						<span class="link">{$lang.manage}</span>
						<input type="file" name="thumbnail" />
					</label>
					
					<script type="text/javascript">//<![CDATA[
					var profile_thumbnail = '{$profile_info.Photo}';
					{literal}
					
					$(document).ready(function(){
						$('input[name=thumbnail]').flUpload({
							sampleFrame: '.canvas',
							fixedSize: true,
							sampleMaxWidth: 100,
							sampleMaxHeight: 100
						});
						
						$('div.canvas img.ajax').flModal({
							caption: lang['warning'],
							content: '{/literal}{$lang.delete_confirm}{literal}',
							prompt: 'xajax_delProfileThumbnail',
							width: 'auto',
							height: 'auto'
						});
					});
					
					{/literal}
					//]]>
					</script>
				</td>
				<td valign="top">
					<table class="submit">
					<tr>
						<td class="name_top">{$lang.username}:</td>
						<td class="field"><b>{$profile_info.Username}</b>{if $profile_info.Full_name} ({$profile_info.Full_name}){/if}</td>
					</tr>
					<tr>
						<td class="name_top">{$lang.account_type}:</td>
						<td class="field">{$profile_info.Type_name}</td>
					</tr>
					{if $profile_info.Type_description}
					<tr>
						<td class="name"></td>
						<td class="field"><div class="description">{$profile_info.Type_description}</div></td>
					</tr>
					{/if}
					<tr>
						<td class="name">{$lang.mail}:</td>
						<td class="field">
							<input type="text" name="profile[mail]" maxlength="150" {if $smarty.post.profile.mail}value="{$smarty.post.profile.mail}"{/if} />
							{if $config.account_edit_email_confirmation}
								<div id="email_change_notice" class="notice_message {if !$aInfo.Mail_tmp}hide{/if}">
									{if $aInfo.Mail_tmp}
										{$lang.account_edit_email_confirmation_info|replace:'[e-mail]':$aInfo.Mail_tmp}
									{else}
										<b>{$lang.notice}</b>: {$lang.account_edit_email_confirmation_notice}
										<script type="text/javascript">
										{literal}
										
										$(document).ready(function(){
											$('input[name="profile[mail]"]').focus(function(){
												$('#email_change_notice').fadeIn('slow');
											});
										});
										
										{/literal}
										</script>
									{/if}
								</div>
							{/if}
						</td>
					</tr>
					
					<tr>
						<td class="name"></td>
						<td class="field"><label><input value="1" type="checkbox" {if $smarty.post.profile.display_email}checked="checked"{/if} name="profile[display_email]" /> {$lang.display_email}</label></td>
					</tr>
					
					{if $account_info.Own_location}
					<tr>
						<td class="name_top">{$lang.personal_address}:</td>
						<td class="field">
							{if $profile_info.Own_address}
								<a target="_blank" href="{$profile_info.Personal_address}">
									{*http://{if $config.account_wildcard}{$profile_info.Own_address}.{$domain}{else}{$domain}/{$profile_info.Own_address}{/if}*}
									{$profile_info.Personal_address}
								</a>
							{else}
								{if $config.account_wildcard}
									http://<input type="text" style="width: 90px;" maxlength="30" name="profile[location]" {if $smarty.post.profile.location}value="{$smarty.post.profile.location}"{/if} />.{$domain}
								{else}
									http://{$domain}/<input type="text" style="width: 90px;" maxlength="30" name="profile[location]" {if $smarty.post.profile.location}value="{$smarty.post.profile.location}"{/if} />
								{/if}
								<div class="notice_message">{$lang.latin_characters_only}</div>
							{/if}
						</td>
					</tr>
					{/if}
					<tr>
						<td class="name"></td>
						<td class="field button">
							<input type="submit" value="{$lang.save}" id="profile_submit" />
						</td>
					</tr>
					</table>
				</td>
			</tr>
			</table>
		</form>
		
	</div>
</div>
<!-- profile tab end -->

{if !empty($profile_info.Fields)}
<!-- account tab -->
<div id="area_account" class="tab_area {if $smarty.request.info != 'account'}hide{/if}">
	<div class="highlight">
	
		<form method="post" action="{$rlBase}{if $config.mod_rewrite}{$pageInfo.Path}.html{else}?page={$pageInfo.Path}{/if}" enctype="multipart/form-data">
			<input type="hidden" name="info" value="account" />
			<input type="hidden" name="fromPost_account" value="1" />
			
			<table class="submit">
			{include file='blocks'|cat:$smarty.const.RL_DS|cat:'profile_account.tpl'}
			<tr>
				<td class="name"></td>
				<td class="field button"><input type="submit" name="finish" value="{$lang.edit}" /></td>
			</tr>
			</table>
		</form>
	
	</div>
</div>
<!-- account tab end -->
{/if}

<!-- manage password tab -->
<div id="area_password" class="tab_area hide">
	<div class="highlight">
	
		<table class="submit">
		<tr>
			<td class="name" style="width: 140px;">{$lang.current_password}:</td>
			<td class="field"><input type="password" id="current_password" maxlength="30"  /></td>
		</tr>
		<tr>
			<td class="name">{$lang.new_password}:</td>
			<td class="field">
				<input class="fleft" name="profile[password]" type="password" id="new_password" maxlength="30" />
				{if $config.account_password_strength}
					<input type="hidden" id="password_strength" value="" />
					<div class="password_strength">
						<div class="scale">
							<div class="color"></div>
							<div class="shine"></div>
						</div>
						<div class="dark_11" id="pass_strength"></div>
					</div>
					
					<script type="text/javascript">
					{literal}
					
					$(document).ready(function(){
						flynax.passwordStrength();
						
						$('#new_password').blur(function(){
							if ( rlConfig['account_password_strength'] )
							{
								if ( $('#password_strength').val() < 3 )
								{
									printMessage('warning', lang['password_weak_warning'])
								}
								else
								{
									$('div.warning div.close').trigger('click');
								}
							}
						});
					});
						
					{/literal}
					</script>
				{/if}
				
				<script type="text/javascript">
				{literal}
				
				$(document).ready(function(){
					$('#change_password').click(function(){
						xajax_changePass( $('#current_password').val(), $('#new_password').val(), $('#password_repeat').val() );
						$(this).val('{/literal}{$lang.loading}{literal}');
					});
				});
				
				{/literal}
				</script>
			</td>
		</tr>
		<tr>
			<td class="name">{$lang.new_password_repeat}:</td>
			<td class="field"><input type="password" id="password_repeat" maxlength="30" /></td>
		</tr>
		<tr>
			<td class="name"></td>
			<td class="field button"><input id="change_password" type="button" value="{$lang.change}" /></td>
		</tr>
		</table>
		
	</div>
</div>
<!-- manage password tab -->

<script type="text/javascript">
{literal}

var accountClicked = false;
$(document).ready(function(){
	$('div.tabs li#tab_account').click(function(){
		if ( !accountClicked )
		{
			flynax.mlTabs();
			accountClicked = true;
		}
	});
});

{/literal}

{if $smarty.request.info == 'account'}
	accountClicked = true;
	
	{literal}
	
	$(document).ready(function(){
		flynax.mlTabs();
	});
	
	{/literal}
	
{/if};

</script>

{rlHook name='profileBlock'}

<!-- my profile end -->
