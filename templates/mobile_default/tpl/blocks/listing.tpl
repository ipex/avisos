<!-- listing block -->

{if $listing.Listing_type}
	{assign var='listing_type' value=$listing_types[$listing.Listing_type]}
{/if}

<li class="{if $listing.Featured}featured{/if}" id="listing_{$listing.ID}">
	<table class="sTable">
	<tr>
		<td valign="top" width="28">
			<div class="star_icon icon">
				<a id="fav_{$listing.ID}" title="{$lang.add_to_favorites}" href="javascript:void(0)">&nbsp;</a>
			</div>
			
			<div class="info_icon icon">
				<a {if $config.view_details_new_window}target="_blank"{/if} href="{$rlBase}{if $config.mod_rewrite}{$pages[$listing_type.Page_key]}/{$listing.Path}/{str2path string=$listing.listing_title}-{$listing.ID}.html{if $hl}?highlight{/if}{else}?page={$pages[$listing_type.Page_key]}&amp;id={$listing.ID}{if $hl}&amp;highlight{/if}{/if}">&nbsp;</a>
			</div>
			
			{if !empty($listing.Main_photo) && $config.grid_photos_count && $listing_type.Photo}
			<div class="photos_icon icon">
				<a {if $config.view_details_new_window}target="_blank"{/if} href="{$rlBase}{if $config.mod_rewrite}{$pages[$listing_type.Page_key]}/{$listing.Path}/{str2path string=$listing.listing_title}-{$listing.ID}.html{if $hl}?highlight{/if}{else}?page={$pages[$listing_type.Page_key]}&amp;id={$listing.ID}{if $hl}&amp;highlight{/if}{/if}">{$listing.Photos_count}</a>
			</div>
			{/if}
		</td>
		{if $listing_type.Photo}
		<td class="image" rowspan="2" style="width: 100px;" valign="top">
			<div class="img">
				{if $listing.Featured} 
					<div class="featured_line"{if $smarty.const.RL_LANG_CODE|lower != 'en'} style="background: url('{$rlTplBase}img/featured_{$smarty.const.RL_LANG_CODE}.png') 0 0 no-repeat;"{/if}>
						<a {if $config.view_details_new_window}target="_blank"{/if} href="{$rlBase}{if $config.mod_rewrite}{$pages[$listing_type.Page_key]}/{$listing.Path}/{str2path string=$listing.listing_title}-{$listing.ID}.html{if $hl}?highlight{/if}{else}?page={$pages[$listing_type.Page_key]}&amp;id={$listing.ID}{if $hl}&amp;highlight{/if}{/if}">&nbsp;</a>
					</div>
				{/if}
				<a {if $config.view_details_new_window}target="_blank"{/if} href="{$rlBase}{if $config.mod_rewrite}{$pages[$listing_type.Page_key]}/{$listing.Path}/{str2path string=$listing.listing_title}-{$listing.ID}.html{if $hl}?highlight{/if}{else}?page={$pages[$listing_type.Page_key]}&amp;id={$listing.ID}{if $hl}&amp;highlight{/if}{/if}">
					<img style="width: 100px;" alt="{$listing.listing_title}" title="{$listing.listing_title}" src="{if empty($listing.Main_photo)}{$rlTplBase}img/no_photo.gif{else}{$smarty.const.RL_URL_HOME}files/{$listing.Main_photo}{/if}" />
				</a>
			</div>
		</td>
		<td rowspan="2"></td>
		{/if}
		<td align="{$text_dir}" valign="top">
			<div {if $listing_type.Photo}class="fields"{/if}>
				<table>
				{assign var='f_first' value=true}
				{foreach from=$listing.fields item='item' key='field' name='fListings'}
				{if !empty($item.value) && $item.Details_page}
				<tr id="sf_field_{$listing.ID}_{$item.Key}">
					{if $config.sf_display_fields}
						<td valign="top">
							<div class="field">{$item.name}:</div>
						</td>
					{/if}
					<td valign="top">
						{if $f_first}
							<a {if $config.view_details_new_window}target="_blank"{/if} href="{$rlBase}{if $config.mod_rewrite}{$pages[$listing_type.Page_key]}/{$listing.Path}/{str2path string=$listing.listing_title}-{$listing.ID}.html{if $hl}?highlight{/if}{else}?page={$pages[$listing_type.Page_key]}&amp;id={$listing.ID}{if $hl}&amp;highlight{/if}{/if}">
								<b>{$item.value}</b>
							</a>
						{else}
							<div class="value">
								{$item.value}
							</div>
						{/if}
					</td>
				</tr>
				{assign var='f_first' value=false}
				{/if}
				{/foreach}
				</table>
			</div>
		</td>
	</tr>
	</table>
</li>

{if $smarty.foreach.listingsF.last}
	<li class="last"></li>
{/if}

<!-- listing block end -->