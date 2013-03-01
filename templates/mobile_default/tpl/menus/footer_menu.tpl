<!-- footer menu block -->

<div class="footer_menu">
{foreach name='fMenu' from=$footer_menu item='footerMenu'}
	<a {if $page == $footerMenu.Path}class="active"{/if} {if $footerMenu.No_follow}rel="nofollow" {/if} title="{$footerMenu.title}" href="{if $footerMenu.Page_type == 'external'}{$footerMenu.Controller}{else}{$rlBase}{if $config.mod_rewrite}{if $footerMenu.Path != ''}{$footerMenu.Path}.html{$footerMenu.Get_vars}{/if}{else}{if $footerMenu.Path != ''}?page={$footerMenu.Path}{$footerMenu.Get_vars|replace:'?':'&amp;'}{/if}{/if}{/if}">{$footerMenu.name}</a>
{/foreach}
</div>
	
<!-- footer menu block end -->