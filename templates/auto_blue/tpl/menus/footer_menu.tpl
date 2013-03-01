<!-- footer menu block -->

{math assign='footer_menu_count' equation='ceil(count/3)' count=$footer_menu|@count}
<table>
<tr>
	<td>
		<ul>
		{foreach name='fMenu' from=$footer_menu item='footerMenu'}
			<li><a {if $page == $footerMenu.Path}class="active"{/if} {if $footerMenu.No_follow || $footerMenu.Login}rel="nofollow"{/if} title="{$footerMenu.title}" href="{if $footerMenu.Page_type != 'external'}{$rlBase}{/if}{if $pageInfo.Controller != 'add_listing' && $mainMenu.Controller == 'add_listing' && !empty($category.Path) && !$category.Lock}{if $config.mod_rewrite}{$mainMenu.Path}/{$category.Path}/{$steps.plan.path}.html{else}?page={$mainMenu.Path}&amp;step={$steps.plan.path}&amp;id={$category.ID}{/if}{else}{if $footerMenu.Page_type == 'external'}{$footerMenu.Controller}{else}{if $config.mod_rewrite}{if $footerMenu.Path != ''}{$footerMenu.Path}.html{$footerMenu.Get_vars}{/if}{else}{if $footerMenu.Path != ''}?page={$footerMenu.Path}{$footerMenu.Get_vars|replace:'?':'&amp;'}{/if}{/if}{/if}{/if}">{$footerMenu.name}</a></li>
			{if $smarty.foreach.fMenu.iteration%$footer_menu_count == 0 && !$smarty.foreach.fMenu.last}
			</ul></td><td><ul>
			{/if}
		{/foreach}
		</ul>
	</td>
</tr>
</table>

<!-- footer menu block end -->