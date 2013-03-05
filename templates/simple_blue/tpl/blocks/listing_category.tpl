<!-- listing category name tpl -->

{assign var='cat_pattern' value=`$smarty.ldelim`category`$smarty.rdelim`}
{if $listing_type.Page}
	{if $config.mod_rewrite}
		{assign var='cat_link' value=$pages[$listing_type.Page_key]|cat:'/'|cat:$listing.Path}
		{if $listing_type.Cat_postfix}
			{assign var='cat_link' value=$cat_link|cat:'.html'}
		{else}
			{assign var='cat_link' value=$cat_link|cat:'/'}
		{/if}
	{else}
		{assign var='cat_link' value='?page='|cat:$pages[$listing_type.Page_key]|cat:'&amp;category='|cat:$listing.Category_ID}
	{/if}
	{assign var='cat_replace' value='<a title="'|cat:$lang.category|cat:': '|cat:$listing.name|cat:'" href="'|cat:$rlBase|cat:$cat_link|cat:'">'|cat:$listing.name|cat:'</a>'}
{else}
	{assign var='cat_replace' value=$listing.name}
{/if}
{$lang.grid_in_category|replace:$cat_pattern:$cat_replace}

{if $listing.Crossed_listing} <img src="{$rlTplBase}img/blank.gif" alt="{$lang.crossed}" title="{$lang.crossed}" class="crossed" />{/if}

<!-- listing category name tpl -->