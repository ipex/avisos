<!-- user navigation bar -->

<div id="user_navbar">
	<div class="hookUserNavbar">{rlHook name='tplUserNavbar'}</div>
	
	<div class="user_button{if $isLogin} pointer{/if}">	
		<div class="inner">
			{if $isLogin}
			<div class="inner">
				<a title="{$lang.account_area}" class="account" href="javascript:void(0)">{$isLogin}<span></span></a>
				<img src="{$rlTplBase}img/blank.gif" class="arrow" alt="" />
			</div>
			{else}
			<div class="inner">
				<a title="{$lang.create_account}" class="registration" href="{$rlBase}{if $config.mod_rewrite}{$pages.registration}.html{else}?page={$pages.registration}{/if}">{$lang.registration}</a>
				<span class="divider">/</span>
			</div>
			<div class="inner">
				<a title="{$lang.login}" class="login" href="javascript:void(0)">{$lang.login}</a>
			</div>
			{/if}
		
		<div>
			<div class="bottom_layer">
				<ul class="menu hide">
					{foreach from=$account_menu item='mItem'}
						{if $mItem.Key == 'my_messages' && !$config.messages_module}{else}
							<li>
								<a {if $page == $mItem.Path}class="active"{/if} title="{$mItem.title}" href="{if $mItem.Page_type == 'external'}{$mItem.Controller}{else}{$rlBase}{if $config.mod_rewrite}{if $mItem.Path != ''}{$mItem.Path}.html{$mItem.Get_vars}{/if}{else}{if $mItem.Path != ''}?page={$mItem.Path}{$mItem.Get_vars|replace:'?':'&'}{/if}{/if}{/if}">{$mItem.name}</a>
								{if $mItem.Key == 'my_messages' && $new_messages}
									<a title="{$lang.new_message_available|replace:'[count]':$new_messages}" class="note" href="{$rlBase}{if $config.mod_rewrite}{if $mItem.Path != ''}{$mItem.Path}.html{/if}{else}{if $mItem.Path != ''}?page={$mItem.Path}{/if}{/if}"><img class="envelop" src="{$rlTplBase}img/blank.gif" alt="" /></a>
								{/if}
							</li>
						{/if}
					{/foreach}
					<li><a class="logout" title="{$lang.title_logout}" href="{$rlBase}{if $config.mod_rewrite}{$pages.login}.html?action=logout{else}?page={$pages.login}&amp;action=logout{/if}">{$lang.logout}</a></li>
					
					{rlHook name='tplAfterAccountMenu'}
				</ul>
			</div>
		</div>
	</div>
	
	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'lang_selector.tpl'}
</div>

{if !$isLogin}
	<div id="login_modal_source" class="hide">
		<div class="tmp-dom">
			<div class="caption_padding">{$lang.login}</div>
			
			{if $loginAttemptsLeft > 0 && $config.security_login_attempt_user_module}
				<div class="attention">
					{$loginAttemptsMess}
				</div>
			{elseif $loginAttemptsLeft <= 0 && $config.security_login_attempt_user_module}
				<div class="attention">
					{assign var='periodVar' value=`$smarty.ldelim`period`$smarty.rdelim`}
					{assign var='replace' value='<b>'|cat:$config.security_login_attempt_user_period|cat:'</b>'}
					{assign var='regReplace' value='<span class="red">$1</span>'}
					{$lang.login_attempt_error|replace:$periodVar:$replace|regex_replace:'/\[(.*)\]/':$regReplace}
				</div>
			{/if}
			
			<form {if $loginAttemptsLeft <= 0 && $config.security_login_attempt_user_module}onsubmit="return false;"{/if} action="{$rlBase}{if $config.mod_rewrite}{$pages.login}.html{else}?page={$pages.login}{/if}" method="post">
				<input type="hidden" name="action" value="login" />
				<table class="white">
				<tr>
					<td class="caption">{$lang.username}</td>
					<td class="field"><input {if $loginAttemptsLeft <= 0 && $config.security_login_attempt_user_module}disabled="disabled" class="disabled"{/if} type="text" name="username" style="width: 180px;" maxlength="100" value="{$smarty.post.username}" /></td>
				</tr>
				<tr>
					<td class="caption">{$lang.password}</td>
					<td class="field"><input {if $loginAttemptsLeft <= 0 && $config.security_login_attempt_user_module}disabled="disabled" class="disabled"{/if} type="password" name="password" style="width: 180px;" maxlength="100" /></td>
				</tr>
				<tr>
					<td class="caption"></td>
					<td class="field"><div style="padding-top: 8px"><input {if $loginAttemptsLeft <= 0 && $config.security_login_attempt_user_module}disabled="disabled" class="disabled"{/if} type="submit" value="{$lang.login}" /></div></td>
				</tr>
				<tr>
					<td></td>
					<td class="field">
						<span class="white_12">{$lang.forgot_pass}</span> <a title="{$lang.remind_pass}" class="brown_12" href="{$rlBase}{if $config.mod_rewrite}{$pages.remind}.html{else}?page={$pages.remind}{/if}">{$lang.remind}</a>
					</td>
				</tr>
				</table>
			</form>
		</div>
	</div>
	
	<script type="text/javascript">
	{literal}
	
	$(document).ready(function(){
		$('#user_navbar a.login').flModal({
			width: 340,
			height: 'auto',
			source: '#login_modal_source'
		});
	});
	
	{/literal}
	</script>
{/if}

<!-- user navigation bar end -->