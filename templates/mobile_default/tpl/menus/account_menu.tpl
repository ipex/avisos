{if $isLogin}
	<div class="grey_middle grey_line">{$lang.welcome}, <strong>{$isLogin}</strong>!</div>
	{foreach from=$account_menu item='mItem'}
		{if $mItem.Key == 'my_messages' && !$config.messages_module}
		{else}
			<div class="lbAccount">
				<a class="mAccount {if $page == $mItem.Path}selected{/if}" title="{$mItem.title}" href="{if $mItem.Page_type == 'external'}{$mItem.Controller}{else}{$rlBase}{if $config.mod_rewrite}{if $mItem.Path != ''}{$mItem.Path}.html{$mItem.Get_vars}{/if}{else}{if $mItem.Path != ''}?page={$mItem.Path}{$mItem.Get_vars|replace:'?':'&'}{/if}{/if}{/if}"><font>{$mItem.name}</font></a>
				{if $mItem.Key == 'my_messages' and $new_messages}
					<a href="{$rlBase}{if $config.mod_rewrite}{if $mItem.Path != ''}{$mItem.Path}.html{/if}{else}{if $mItem.Path != ''}?page={$mItem.Path}{/if}{/if}">
						<img alt="{$lang.new_message_available|replace:'[count]':$new_messages}" title="{$lang.new_message_available|replace:'[count]':$new_messages}" src="{$rlTplBase}img/envelope_blink.gif" />
					</a>
				{/if}
			</div>
		{/if}
	{/foreach}
	
	{rlHook name='afterAccountMenu'}
	
	<div class="lbAccount"><a class="mAccount" title="{$lang.title_logout}" href="{$rlBase}{if $config.mod_rewrite}{if $mItem.Path != ''}{$pages.login}.html?action=logout{/if}{else}{if $mItem.Path != ''}?page={$pages.login}&amp;action=logout{/if}{/if}"><font>{$lang.logout}</font></a></div>
{else}
	<form action="{$rlBase}{if $config.mod_rewrite}login.html{else}?page=login{/if}" method="post">
	    <input type="hidden" name="action" value="login" />
	    <div class="grey_small" style="line-height: 14px; margin-left: 0pt;">{$lang.username}</div>
	    <input type="text" name="username" class="text_blink" style="width: 130px;" maxlength="25" value="{$smarty.post.username}" />
	    <div class="grey_small" style="line-height: 14px; margin-left: 0pt;">{$lang.password}</div>
	    <input type="password" name="password" class="text_blink" style="width: 130px;" maxlength="25" />
	    <div><input type="submit" class="button_grey" value="{$lang.login}" /></div>
	    <span class="grey_small">{$lang.forgot_pass}</span> <a title="{$lang.remind_pass}" class="dark" href="{$rlBase}{if $config.mod_rewrite}remind.html{else}?page=remind{/if}">{$lang.remind}</a><br />
	    <span class="grey_small">{$lang.new_here}</span> <a title="{$lang.create_account}" class="dark" href="{$rlBase}{if $config.mod_rewrite}registration.html{else}?page=registration{/if}">{$lang.registration}</a>
	</form>
{/if}