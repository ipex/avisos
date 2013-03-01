<!-- bread crumbs block -->

<ul id="bread_crumbs">
	<li class="home"><a href="{$rlBase}" title="{$config.site_name}">&nbsp;</a></li>
	{assign var='truncate_value' value=12}
	{foreach from=$bread_crumbs item='breadCrumbs' name='fBreafC'}
		{if !$smarty.foreach.fBreafC.first}
			{if $smarty.foreach.fBreafC.iteration >= 3 && $bread_crumbs|@count > 4}
				{assign var='truncate_value' value=$truncate_value-2}
			{/if}
			
			<li>
			{if $smarty.foreach.fBreafC.last}
				<span title="{$breadCrumbs.title|strip_tags}">
					{if !empty($breadCrumbs.name)}
						{if $bread_crumbs|@count > 3}
							{$breadCrumbs.name|strip_tags|truncate:$truncate_value:"...":true}
						{else}
							{$breadCrumbs.name|strip_tags}
						{/if}
					{else}
						{if $bread_crumbs|@count > 3}
							{$breadCrumbs.title|strip_tags|truncate:$truncate_value:"...":true}
						{else}
							{$breadCrumbs.title|strip_tags}
						{/if}
					{/if}
				</span>
			{else}
				<a href="{$rlBase}{if $config.mod_rewrite}{if $breadCrumbs.path != ''}{$breadCrumbs.path}{if $breadCrumbs.category}{if $config.display_cat_html}.html{else}/{/if}{else}.html{/if}{/if}{if $breadCrumbs.vars}?{$breadCrumbs.vars}{/if}{else}{if $breadCrumbs.path != ''}?page={$breadCrumbs.path}{/if}{if $breadCrumbs.vars}&amp;{$breadCrumbs.vars}{/if}{/if}" title="{$breadCrumbs.title|strip_tags}">
					{if $bread_crumbs|@count > 3}
						{$breadCrumbs.name|strip_tags|truncate:$truncate_value:"...":true}
					{else}
						{$breadCrumbs.name|strip_tags}
					{/if}
				</a>
			{/if}
			</li>
		{/if}
	{/foreach}
</ul>

<!-- bread crumbs block end -->