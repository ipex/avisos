{if $isLogin}
	<div class="welcome">{$lang.welcome}, <b>{$isLogin}</b>!</div>
	<ul class="account_menu">
		{foreach from=$account_menu item='mItem'}
			{if $mItem.Key == 'my_messages' && !$config.messages_module}{else}
				<li>
					<a {if $page == $mItem.Path}class="actiev"{/if} title="{$mItem.title}" href="{if $mItem.Page_type == 'external'}{$mItem.Controller}{else}{$rlBase}{if $config.mod_rewrite}{if $mItem.Path != ''}{$mItem.Path}.html{$mItem.Get_vars}{/if}{else}{if $mItem.Path != ''}?page={$mItem.Path}{$mItem.Get_vars|replace:'?':'&'}{/if}{/if}{/if}">{$mItem.name}</a>
					{if $mItem.Key == 'my_messages' and $new_messages}
						<a href="{$rlBase}{if $config.mod_rewrite}{if $mItem.Path != ''}{$mItem.Path}.html{/if}{else}{if $mItem.Path != ''}?page={$mItem.Path}{/if}{/if}">
							<img class="envelop" alt="{$lang.new_message_available|replace:'[count]':$new_messages}" title="{$lang.new_message_available|replace:'[count]':$new_messages}" src="{$rlTplBase}img/blank.gif" />
						</a>
					{/if}
				</li>
			{/if}
		{/foreach}
		
		{rlHook name='afterAccountMenu'}
		
		<li><a title="{$lang.title_logout}" href="{$rlBase}{if $config.mod_rewrite}{if $mItem.Path != ''}{$pages.login}.html?action=logout{/if}{else}{if $mItem.Path != ''}?page={$pages.login}&amp;action=logout{/if}{/if}">{$lang.logout}</a></li>
	</ul>
{else}
	<form class="account_login" action="{$rlBase}{if $config.mod_rewrite}{$pages.login}.html{else}?page={$pages.login}{/if}" method="post">
	    <input type="hidden" name="action" value="login" />
	    
	    <div>{$lang.username}</div>
	    <input type="text" name="username" maxlength="25" value="{$smarty.post.username}" />
	    
	    <div>{$lang.password}</div>
	    <input type="password" name="password" maxlength="25" />
	    
	    <div class="button"><input type="submit" value="{$lang.login}" /></div>
	    <span class="black_12">{$lang.forgot_pass}</span> <a title="{$lang.remind_pass}" class="brown_12" href="{$rlBase}{if $config.mod_rewrite}{$pages.remind}.html{else}?page={$pages.remind}{/if}">{$lang.remind}</a><br />
	    <span class="black_12">{$lang.new_here}</span> <a title="{$lang.create_account}" class="brown_12" href="{$rlBase}{if $config.mod_rewrite}{$pages.registration}.html{else}?page={$pages.registration}{/if}">{$lang.registration}</a>
	</form>
{/if}