<!-- bread crumbs block -->

<ul id="bread_crumbs">
	{foreach from=$bread_crumbs item='breadCrumbs' name='fBreadC'}
		<li>
		{if $smarty.foreach.fBreadC.last}
			<span title="{$breadCrumbs.title|strip_tags}">{if !empty($breadCrumbs.name)}{$breadCrumbs.name|strip_tags}{else}{$breadCrumbs.title|strip_tags}{/if}</span>
		{else}
			<a href="{$rlBase}{if $config.mod_rewrite}{if $breadCrumbs.path != ''}{$breadCrumbs.path}{if $breadCrumbs.category}{if $type_info.Cat_postfix}.html{else}/{/if}{else}.html{/if}{/if}{if $breadCrumbs.vars}?{$breadCrumbs.vars}{/if}{else}{if $breadCrumbs.path != ''}?page={$breadCrumbs.path}{/if}{if $breadCrumbs.vars}&amp;{$breadCrumbs.vars}{/if}{/if}" title="{$breadCrumbs.title|strip_tags}">{$breadCrumbs.name|strip_tags}</a>
		{/if}
		</li>
	{/foreach}
</ul>

<!-- bread crumbs block end -->