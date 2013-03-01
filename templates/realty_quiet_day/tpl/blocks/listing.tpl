<!-- listing item -->

{if $listing.Listing_type}
	{assign var='listing_type' value=$listing_types[$listing.Listing_type]}
{/if}

<div class="item{if $listing.Featured} featured{/if}">
	<table class="sTable{if !$listing_type.Photo} featured_label_fix{/if}">
	<tr>
		{if $listing_type.Photo}
		<td rowspan="2" class="photo" valign="top">
			<div>
				{if $listing_type.Page}<a title="{$listing.listing_title}" {if $config.view_details_new_window}target="_blank"{/if} href="{$rlBase}{if $config.mod_rewrite}{$pages[$listing_type.Page_key]}/{$listing.Path}/{str2path string=$listing.listing_title}-{$listing.ID}.html{if $hl}?highlight{/if}{else}?page={$pages[$listing_type.Page_key]}&amp;id={$listing.ID}{if $hl}&amp;highlight{/if}{/if}">{/if}
					<img alt="{$listing.listing_title}" src="{if empty($listing.Main_photo)}{$rlTplBase}img/no-picture.jpg{else}{$smarty.const.RL_URL_HOME}files/{$listing.Main_photo}{/if}" />
				{if $listing_type.Page}</a>{/if}
				{if !empty($listing.Main_photo) && $config.grid_photos_count && $listing_type.Page}
					<div class="counter"><a {if $config.view_details_new_window}target="_blank"{/if} href="{$rlBase}{if $config.mod_rewrite}{$pages[$listing_type.Page_key]}/{$listing.Path}/{str2path string=$listing.listing_title}-{$listing.ID}.html{else}?page={$pages[$listing_type.Page_key]}&amp;id={$listing.ID}{/if}">{$listing.Photos_count}</a></div>
				{/if}
			</div>
		</td>
		{/if}
		<td {if $grid_mode != 'list'}class="fields"{/if} valign="top">
			{assign var='f_first' value=true}
			{if $grid_mode == 'list'}
				<table class="sTable">
				<tr>
					<td class="fields">
						<div>
							{foreach from=$listing.fields item='item' key='field' name='fListings'}
								{if !empty($item.value) && $item.Details_page}
									<span {if $config.sf_display_fields}title="{$item.name}"{/if} id="sf_field_{$listing.ID}_{$item.Key}">
									{if $f_first && $listing_type.Page}
										<a title="{$item.value}" {if $config.view_details_new_window}target="_blank"{/if} href="{$rlBase}{if $config.mod_rewrite}{$pages[$listing_type.Page_key]}/{$listing.Path}/{str2path string=$listing.listing_title}-{$listing.ID}.html{if $hl}?highlight{/if}{else}?page={$pages[$listing_type.Page_key]}&amp;id={$listing.ID}{if $hl}&amp;highlight{/if}{/if}">{$item.value}</a>,
									{else}
										{$item.value}{if !$smarty.foreach.fListings.last},{/if}
									{/if}
									{assign var='f_first' value=false}
									</span>
								{/if}
							{/foreach}
			
							{rlHook name='listingAfterFields'}
						</div>
					</td>
					<td class="ralign" valign="top">
						{if $listing_type.Page}<a title="{$lang.category}: {$listing.name}" class="cat_caption" href="{$rlBase}{if $config.mod_rewrite}{$pages[$listing_type.Page_key]}/{$listing.Path}{if $listing_type.Cat_postfix}.html{else}/{/if}{else}?page={$pages[$listing_type.Page_key]}&amp;category={$listing.Category_ID}{/if}">{else}<span class="cat_caption">{/if}
							{$listing.name}
						{if $listing_type.Page}</a>{else}</span>{/if}
						{if $listing.Crossed_listing} <img src="{$rlTplBase}img/blank.gif" alt="{$lang.crossed}" title="{$lang.crossed}" class="crossed" />{/if}
					</td>
				</tr>
				</table>
			{else}
				<div>
					<table>
						{if $listing.fields}
							{foreach from=$listing.fields item='item' key='field' name='fListings'}
							{if !empty($item.value) && $item.Details_page}
							<tr id="sf_field_{$listing.ID}_{$item.Key}">
								{if $config.sf_display_fields}
								<td class="name">{$item.name}:</td>
								{/if}
								<td class="value {if $f_first}first{/if}">
									{if $f_first && $listing_type.Page}
										<a title="{$item.value}" {if $config.view_details_new_window}target="_blank"{/if} href="{$rlBase}{if $config.mod_rewrite}{$pages[$listing_type.Page_key]}/{$listing.Path}/{str2path string=$listing.listing_title}-{$listing.ID}.html{if $hl}?highlight{/if}{else}?page={$pages[$listing_type.Page_key]}&amp;id={$listing.ID}{if $hl}&amp;highlight{/if}{/if}">{$item.value}</a>
									{else}
										{$item.value}
									{/if}
								</td>
							</tr>
							{assign var='f_first' value=false}
							{/if}
							{/foreach}
							
							{rlHook name='listingAfterFields'}
						{else}
							<tr><td>&nbsp;</td></tr>
						{/if}
					</table>
				</div>
			{/if}
		</td>
	</tr>
	<tr>
		<td class="caption" valign="bottom">
			{if $grid_mode == 'list'}
				<table class="nav">
				<tr>
					<td valign="bottom">
						{rlHook name='listingBeforeStats'}
						
						{if $config.count_listing_visits}<span class="shows icon" title="{$lang.shows}">{$listing.Shows}</span>{/if}
						{if $config.display_posted_date}<span class="date icon" title="{$lang.posted_date}">{$listing.Date|date_format:$smarty.const.RL_DATE_FORMAT}</span>{/if}
						
						{rlHook name='listingAfterStats'}
					</td>
					<td valign="bottom" class="ralign">
						{rlHook name='listingNavIcons'}
					
						<a id="fav_{$listing.ID}" title="{$lang.add_to_favorites}" href="javascript:void(0)" class="icon add_favorite"><span>&nbsp;</span></a>
					</td>
				</tr>
				</table>
			{else}
				{if $listing_type.Page}<a title="{$lang.category}: {$listing.name}" class="cat_caption" href="{$rlBase}{if $config.mod_rewrite}{$pages[$listing_type.Page_key]}/{$listing.Path}{if $listing_type.Cat_postfix}.html{else}/{/if}{else}?page={$pages[$listing_type.Page_key]}&amp;category={$listing.Category_ID}{/if}">{else}<span class="cat_caption">{/if}
					{$listing.name}
				{if $listing_type.Page}</a>{else}</span>{/if}
				{if $listing.Crossed_listing} <img src="{$rlTplBase}img/blank.gif" alt="{$lang.crossed}" title="{$lang.crossed}" class="crossed" />{/if}
			{/if}
		</td>
	</tr>
	</table>
	
	{if $grid_mode != 'list'}
		<table class="nav">
		<tr>
			<td valign="bottom">
				{rlHook name='listingBeforeStats'}
				
				{if $config.count_listing_visits}<span class="shows icon" title="{$lang.shows}">{$listing.Shows}</span>{/if}
				{if $config.display_posted_date}<span class="date icon" title="{$lang.posted_date}">{$listing.Date|date_format:$smarty.const.RL_DATE_FORMAT}</span>{/if}
				
				{rlHook name='listingAfterStats'}
			</td>
			<td valign="bottom" class="ralign">
				{rlHook name='listingNavIcons'}
			
				<a id="fav_{$listing.ID}" title="{$lang.add_to_favorites}" href="javascript:void(0)" class="icon add_favorite"><span>&nbsp;</span></a>
			</td>
		</tr>
		</table>
	{/if}
	
	{if $listing.Featured && $listing_type.Photo}
		<div class="label" {if $smarty.const.RL_LANG_CODE != 'en'}style="background-image: url('{$rlTplBase}img/featured_{$smarty.const.RL_LANG_CODE|lower}.png')"{/if}>{if $listing_type.Page}<a {if $config.view_details_new_window}target="_blank"{/if} href="{$rlBase}{if $config.mod_rewrite}{$pages[$listing_type.Page_key]}/{$listing.Path}/{str2path string=$listing.listing_title}-{$listing.ID}.html{if $hl}?highlight{/if}{else}?page={$pages[$listing_type.Page_key]}&amp;id={$listing.ID}{if $hl}&amp;highlight{/if}{/if}">&nbsp;</a>{/if}</div>
	{/if}
</div>

<!-- listing item end -->