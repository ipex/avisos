<!-- login/logout -->

<div class="highlight">
	{if $isLogin}
		<div class="welcome">{$lang.welcome}, <b>{$isLogin}</b>!</div>
		
		<ul class="account_menu">
			{foreach from=$account_menu item='mItem'}
				{if $mItem.Key == 'my_messages' && !$config.messages_module}{else}
					<li>
						<a title="{$mItem.title}" href="{$rlBase}{if $config.mod_rewrite}{if $mItem.Path != ''}{$mItem.Path}.html{$mItem.Get_vars}{/if}{else}{if $mItem.Path != ''}?page={$mItem.Path}{$mItem.Get_vars|replace:'?':'&amp;'}{/if}{/if}">{$mItem.name}</a>
						{if $mItem.Key == 'my_messages' && $new_messages}
							<div class="message_feed">
								<a title="{$lang.new_message_available|replace:'[count]':$new_messages}" class="note" href="{$rlBase}{if $config.mod_rewrite}{if $mItem.Path != ''}{$mItem.Path}.html{/if}{else}{if $mItem.Path != ''}?page={$mItem.Path}{/if}{/if}"><img class="envelop" src="{$rlTplBase}img/blank.gif" alt="" /></a>
								<a class="new" title="{$lang.new_message}" href="{$rlBase}{if $config.mod_rewrite}{if $mItem.Path != ''}{$mItem.Path}.html{/if}{else}{if $mItem.Path != ''}?page={$mItem.Path}{/if}{/if}">{$new_messages}</a>
							</div>
						{/if}
					</li>
				{/if}
			{/foreach}
			
			{rlHook name='afterAccountMenu'}
		
			<li><a title="{$lang.title_logout}" href="{$rlBase}{if $config.mod_rewrite}{if $mItem.Path != ''}{$pages.login}.html?action=logout{/if}{else}{if $mItem.Path != ''}?page={$pages.login}&amp;action=logout{/if}{/if}">{$lang.logout}</a></li>
		</ul>
		
	{else}
		<form {if $loginAttemptsLeft <= 0 && $config.security_login_attempt_user_module}onsubmit="return false;"{/if} class="account_login" action="{$rlBase}{if $config.mod_rewrite}{$pages.login}.html{else}?page={$pages.login}{/if}" method="post">
			<input type="hidden" name="action" value="login" />
			
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
			
			{if isset($request_page)}
				<input type="hidden" name="regirect" value="{$request_page}" />
			{/if}
			
			<div>{$lang.username}</div>
			<input {if $loginAttemptsLeft <= 0 && $config.security_login_attempt_user_module}disabled="disabled" class="disabled"{/if} name="username" type="text" style="width: 150px;" maxlength="25" value="{$smarty.post.username}" />
			
			<div>{$lang.password}</div>
			<input {if $loginAttemptsLeft <= 0 && $config.security_login_attempt_user_module}disabled="disabled" class="disabled"{/if} name="password" type="password" style="width: 150px;" maxlength="25" />
			
			<div class="button"><input {if $loginAttemptsLeft <= 0 && $config.security_login_attempt_user_module}disabled="disabled" class="disabled"{/if} type="submit" value="{$lang.login}" /></div>
			
			<span class="black_12">{$lang.forgot_pass}</span> <a title="{$lang.remind_pass}" class="brown_12" href="{$rlBase}{if $config.mod_rewrite}remind.html{else}?page=remind{/if}">{$lang.remind_pass}</a><br />
			<span class="black_12">{$lang.new_here}</span> <a title="{$lang.create_account}" class="brown_12" href="{$rlBase}{if $config.mod_rewrite}registration.html{else}?page=registration{/if}">{$lang.create_account}</a>
		
		</form>
	{/if}
</div>

<!-- login/logout end -->