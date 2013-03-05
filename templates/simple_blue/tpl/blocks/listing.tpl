<!-- listing item -->

{if $listing.Listing_type}
	{assign var='listing_type' value=$listing_types[$listing.Listing_type]}
{/if}

<div class="item{if $listing.Featured} featured{/if}{if $grid_photo == '0'} no-photo-item{/if}" {if $grid_mode == 'table' && $listing_type.Photo}style="height: {$config.pg_upload_thumbnail_height+60}px;"{/if}>
	<div class="bottom-layer">
		<div class="top-layer">
			<table class="sTable">
			<tr>
				{if $listing_type.Photo}
				<td rowspan="2" class="photo" valign="top" {if $grid_mode == 'table'}style="width: {$config.pg_upload_thumbnail_width+4}px;height: {$config.pg_upload_thumbnail_height+4+15}px;"{/if}>
					<div>
						{if $listing.Featured && $listing_type.Photo}
							<div class="label">{if $listing_type.Page}<a {if $config.view_details_new_window}target="_blank"{/if} title="{$lang.featured}" href="{$rlBase}{if $config.mod_rewrite}{$pages[$listing_type.Page_key]}/{$listing.Path}/{str2path string=$listing.listing_title}-{$listing.ID}.html{if $hl}?highlight{/if}{else}?page={$pages[$listing_type.Page_key]}&amp;id={$listing.ID}{if $hl}&amp;highlight{/if}{/if}">{$lang.featured[0]}</a>{/if}</div>
						{/if}
						{if $listing_type.Page}<a title="{$listing.listing_title}" {if $config.view_details_new_window}target="_blank"{/if} href="{$rlBase}{if $config.mod_rewrite}{$pages[$listing_type.Page_key]}/{$listing.Path}/{str2path string=$listing.listing_title}-{$listing.ID}.html{if $hl}?highlight{/if}{else}?page={$pages[$listing_type.Page_key]}&amp;id={$listing.ID}{if $hl}&amp;highlight{/if}{/if}">{/if}
							<img alt="{$listing.listing_title}" src="{if empty($listing.Main_photo)}{$rlTplBase}img/no-picture.jpg{else}{$smarty.const.RL_URL_HOME}files/{$listing.Main_photo}{/if}" />
						{if $listing_type.Page}</a>{/if}
						{if !empty($listing.Main_photo) && $config.grid_photos_count && $listing_type.Page}
							<div class="counter"><a {if $config.view_details_new_window}target="_blank"{/if} href="{$rlBase}{if $config.mod_rewrite}{$pages[$listing_type.Page_key]}/{$listing.Path}/{str2path string=$listing.listing_title}-{$listing.ID}.html{else}?page={$pages[$listing_type.Page_key]}&amp;id={$listing.ID}{/if}">{$listing.Photos_count}</a></div>
						{/if}
						
						{if $listing.fields.price.value && $grid_mode == 'table'}<span class="price"><a {if $config.view_details_new_window}target="_blank"{/if} href="{$rlBase}{if $config.mod_rewrite}{$pages[$listing_type.Page_key]}/{$listing.Path}/{str2path string=$listing.listing_title}-{$listing.ID}.html{if $hl}?highlight{/if}{else}?page={$pages[$listing_type.Page_key]}&amp;id={$listing.ID}{if $hl}&amp;highlight{/if}{/if}">{$listing.fields.price.value}<span class="corner"></span></a></span>{/if}
					</div>
				</td>
				{/if}
				<td valign="top">
					{assign var='f_first' value=true}
					{if $grid_mode == 'list'}
						<table class="sTable">
						<tr>
							<td class="fields">
								<div>
									{foreach from=$listing.fields item='item' key='field' name='fListings'}
										{if !empty($item.value) && $item.Details_page && $item.Key != 'price'}
											<span {if $config.sf_display_fields}title="{$item.name}"{/if} id="sf_field_{$listing.ID}_{$item.Key}">
											{if $f_first && $listing_type.Page}
												{if !$f_first}, {/if}<a title="{$item.value}" {if $config.view_details_new_window}target="_blank"{/if} href="{$rlBase}{if $config.mod_rewrite}{$pages[$listing_type.Page_key]}/{$listing.Path}/{str2path string=$listing.listing_title}-{$listing.ID}.html{if $hl}?highlight{/if}{else}?page={$pages[$listing_type.Page_key]}&amp;id={$listing.ID}{if $hl}&amp;highlight{/if}{/if}">{$item.value}</a>
											{else}
												{if !$f_first}, {/if}{$item.value}
											{/if}
											{assign var='f_first' value=false}
											</span>
										{/if}
									{/foreach}
					
									{rlHook name='listingAfterFields'}
								</div>
								<div class="category-name">
									{include file='blocks'|cat:$smarty.const.RL_DS|cat:'listing_category.tpl'}
								</div>
							</td>
							<td class="ralign nav_icons" valign="top">
								<span class="nav hide">{rlHook name='listingNavIcons'}</span>
								
								<a id="fav_{$listing.ID}" title="{$lang.add_to_favorites}" href="javascript:void(0)" class="icon add_favorite"><span>&nbsp;</span></a>
							</td>
						</tr>
						</table>
					{else}
						<table class="sTable">
						<tr>
							<td class="lalign" valign="top">
								<a id="fav_{$listing.ID}" title="{$lang.add_to_favorites}" href="javascript:void(0)" class="icon add_favorite"><span>&nbsp;</span></a>
						
								<div>
									{rlHook name='listingBeforeStats'}
								</div>
							</td>
							<td class="ralign" valign="top">
								<span class="nav hide">
									{rlHook name='listingNavIcons'}
								</span>
							</td>
						</tr>
						</table>
					{/if}
				</td>
			</tr>
			{if $grid_mode == 'list'}
			<tr>
				<td class="caption" valign="bottom">
					<table class="nav">
					<tr>
						<td valign="bottom">
							{if $listing.fields.price.value}<span class="price">{$listing.fields.price.value}</span>{/if}
							{rlHook name='listingBeforeStats'}
						</td>
						<td valign="bottom" class="ralign">
							{if $config.count_listing_visits}<span class="shows icon" title="{$lang.shows}">{$listing.Shows}</span>{/if}
							{if $config.display_posted_date}<span class="date icon" title="{$lang.posted_date}">{$listing.Date|date_format:$smarty.const.RL_DATE_FORMAT}</span>{/if}
							
							{rlHook name='listingAfterStats'}
						</td>
					</tr>
					</table>
				</td>
			</tr>
			{/if}
			</table>
			
			{if $grid_mode == 'table'}
				<div class="fields">
					<table>
						{if $listing.fields}
							{foreach from=$listing.fields item='item' key='field' name='fListings'}
							{if !empty($item.value) && $item.Details_page && $item.Key != 'price'}
							<tr id="sf_field_{$listing.ID}_{$item.Key}" {if !$f_first}class="hide"{/if}>
								{if $config.sf_display_fields}
								<td class="name">{$item.name}:</td>
								{/if}
								<td class="value {if $f_first}first{/if}">
									{if $f_first}
										<div class="text-overflow">
											{if $f_first && $listing_type.Page}
												<a title="{$item.value}" {if $config.view_details_new_window}target="_blank"{/if} href="{$rlBase}{if $config.mod_rewrite}{$pages[$listing_type.Page_key]}/{$listing.Path}/{str2path string=$listing.listing_title}-{$listing.ID}.html{if $hl}?highlight{/if}{else}?page={$pages[$listing_type.Page_key]}&amp;id={$listing.ID}{if $hl}&amp;highlight{/if}{/if}">{$item.value}</a>
											{else}
												{$item.value}
											{/if}
										</div>
										<div class="category-name text-overflow">
											{include file='blocks'|cat:$smarty.const.RL_DS|cat:'listing_category.tpl'}
										</div>
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
			
				<div class="nav hide">
					{if $config.count_listing_visits}<span class="shows icon" title="{$lang.shows}">{$listing.Shows}</span>{/if}
					{if $config.display_posted_date}<span class="date icon" title="{$lang.posted_date}">{$listing.Date|date_format:$smarty.const.RL_DATE_FORMAT}</span>{/if}
					
					{rlHook name='listingAfterStats'}
				</div>
			{/if}
		</div>
	</div>
</div>

<!-- listing item end -->