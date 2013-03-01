{if $pageInfo.Controller == 'home'}
	<div class="main_menu_home">
		<table class="sTable">
		<tr>
			<td class="left">&nbsp;</td>
			{foreach from=$main_menu item='mainMenu' name='mMenu'}
				{if $mainMenu.Key != 'home'}
					<td class="item">
						<a {if $mainMenu.No_follow}rel="nofollow" {/if}title="{$mainMenu.title}" href="{if $mainMenu.Page_type == 'external'}{$mainMenu.Controller}{else}{$rlBase}{if $config.mod_rewrite}{if $mainMenu.Path != ''}{$mainMenu.Path}.html{$mainMenu.Get_vars}{/if}{else}{if $mainMenu.Path != ''}?page={$mainMenu.Path}{$mainMenu.Get_vars|replace:'?':'&amp;'}{/if}{/if}{/if}">{$mainMenu.name}</a>
					</td>
					{if !$smarty.foreach.mMenu.last}<td class="divider"><div></div></td>{/if}
				{/if}
			{/foreach}
			<td class="right">&nbsp;</td>
		</tr>   
		</table>
	</div>
{else}
	<div class="main_menu">
		<div class="inner">
			<table>
			<tr>
				{foreach from=$main_menu item='mainMenu' name='mMenu'}
					<td class="item">
						<a {if $page == $mainMenu.Path}class="active"{/if} {if $mainMenu.No_follow}rel="nofollow" {/if}title="{$mainMenu.title}" href="{if $mainMenu.Page_type == 'external'}{$mainMenu.Controller}{else}{$rlBase}{if $config.mod_rewrite}{if $mainMenu.Path != ''}{$mainMenu.Path}.html{$mainMenu.Get_vars}{/if}{else}{if $mainMenu.Path != ''}?page={$mainMenu.Path}{$mainMenu.Get_vars|replace:'?':'&amp;'}{/if}{/if}{/if}">{$mainMenu.name}</a>
					</td>
					{if !$smarty.foreach.mMenu.last}<td class="divider"><div></div></td>{/if}
				{/foreach}
			</tr>   
			</table>
		</div>
	</div>
{/if}