<!-- listing category name tpl -->

{$lang.category}:
{if $listing_type.Page}<a title="{$lang.category}: {$listing.name}" class="cat_caption" href="{$rlBase}{if $config.mod_rewrite}{$pages[$listing_type.Page_key]}/{$listing.Path}{if $listing_type.Cat_postfix}.html{else}/{/if}{else}?page={$pages[$listing_type.Page_key]}&amp;category={$listing.Category_ID}{/if}">{else}<span class="cat_caption">{/if}
	{$listing.name}
{if $listing_type.Page}</a>{else}</span>{/if}

{if $listing.Crossed_listing} <img src="{$rlTplBase}img/blank.gif" alt="{$lang.crossed}" title="{$lang.crossed}" class="crossed" />{/if}

<!-- listing category name tpl -->