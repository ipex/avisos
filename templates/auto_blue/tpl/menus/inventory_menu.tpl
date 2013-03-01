<!-- inventory menu block -->

{if $inventory_menu}
	<ul id="inventory_menu">
		{foreach from=$inventory_menu item='invMenu'}<li {if $page == $invMenu.Path}class="active"{/if}><span class="square"></span><a {if $invMenu.No_follow || $invMenu.Login}rel="nofollow" {/if}title="{$invMenu.title}" href="{if $invMenu.Page_type != 'external'}{$rlBase}{/if}{if $pageInfo.Controller != 'add_listing' && $invMenu.Controller == 'add_listing' && !empty($category.Path) && !$category.Lock}{if $config.mod_rewrite}{$invMenu.Path}/{$category.Path}/{$steps.plan.path}.html{else}?page={$invMenu.Path}&amp;step={$steps.plan.path}&amp;id={$category.ID}{/if}{else}{if $invMenu.Page_type == 'external'}{$invMenu.Controller}{else}{if $config.mod_rewrite}{if $invMenu.Path != ''}{$invMenu.Path}.html{$invMenu.Get_vars}{/if}{else}{if $invMenu.Path != ''}?page={$invMenu.Path}{$invMenu.Get_vars|replace:'?':'&amp;'}{/if}{/if}{/if}{/if}">{$invMenu.name}</a></li>{/foreach}
	</ul>
{/if}

<!-- inventory menu block end -->