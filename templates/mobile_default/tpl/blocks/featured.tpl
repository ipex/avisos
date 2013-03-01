<!-- featured boats block -->

<div class="featured">
	{if !empty($featured)}

		{foreach from=$featured item='listing' key='key'}
			<div class="item">
					<div class="contener">
					<a {if $config.featured_new_window}target="_blank"{/if} href="{$rlBase}{if $config.mod_rewrite}{$pages.browse}/{$listing.Path}/{str2path string=$listing.listing_title}-l{$listing.ID}.html{else}?page={$pages.browse}&amp;id={$listing.ID}{/if}">
						<img alt="{$listing.fields.0.value}" title="{$listing.fields.0.value}" src="{if empty($listing.Main_photo)}{$rlTplBase}img/no_photo.gif{else}{$smarty.const.RL_URL_HOME}files/{$listing.Main_photo}{/if}" />
					</a>
					
					{foreach from=$listing.fields item='item' key='field' name='fListings'}
					{if !empty($item.value) && $item.Details_page}
						<div class="field" style="width: {$config.pg_upload_thumbnail_width}px">
							{$item.value}
						</div>
					{/if}
					{/foreach}
				</div>
			</div>
		{/foreach}

	{else}
		{assign var='replace' value='<a class="navigator"  title="'|cat:$lang.click_here|cat:'" href="'|cat:$add_listing_href|cat:'">'|cat:$lang.click_here|cat:'</a>'}
		<div style="margin: 10px;" class="grey_middle">{$lang.no_listings_here|replace:'[click]':$replace}</div>
	{/if}
</div>

<!-- featured boats block end -->