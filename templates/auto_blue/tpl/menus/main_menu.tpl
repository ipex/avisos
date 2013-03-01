<!-- main menu block -->

<ul id="main_menu">
	{foreach name='mMenu' from=$main_menu item='mainMenu'}<li {if $page == $mainMenu.Path}class="active"{/if}><a {if $mainMenu.No_follow || $mainMenu.Login}rel="nofollow" {/if}title="{$mainMenu.title}" href="{if $mainMenu.Page_type != 'external'}{$rlBase}{/if}{if $pageInfo.Controller != 'add_listing' && $mainMenu.Controller == 'add_listing' && !empty($category.Path) && !$category.Lock}{if $config.mod_rewrite}{$mainMenu.Path}/{$category.Path}/{$steps.plan.path}.html{else}?page={$mainMenu.Path}&amp;step={$steps.plan.path}&amp;id={$category.ID}{/if}{else}{if $mainMenu.Page_type == 'external'}{$mainMenu.Controller}{else}{if $config.mod_rewrite}{if $mainMenu.Path != ''}{$mainMenu.Path}.html{$mainMenu.Get_vars}{/if}{else}{if $mainMenu.Path != ''}?page={$mainMenu.Path}{$mainMenu.Get_vars|replace:'?':'&amp;'}{/if}{/if}{/if}{/if}">{$mainMenu.name}</a><span></span></li>{/foreach}<li class="more" style="display: none;"><a href="javascript:void(0)" rel="nofollow">{$lang.menu_more}</a><img class="arrow" alt="" src="{$rlTplBase}img/blank.gif" /></li>
</ul>

<ul id="main_menu_more"><li class="hide"></li></ul>

<!-- main menu block end -->