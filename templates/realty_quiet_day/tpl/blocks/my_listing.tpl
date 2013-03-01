<!-- my listing item -->

{if $listing.Listing_type}
	{assign var='listing_type' value=$listing_types[$listing.Listing_type]}
{/if}

<div class="item{if $listing.Featured_expire} featured{/if}" id="listing_{$listing.ID}">
	<table class="sTable{if !$listing_type.Photo} featured_label_fix{/if}">
	<tr>
		{if $listing_type.Photo}
		<td class="photo" valign="top">
			<div>
				{if $listing_type.Page}<a title="{$listing.listing_title}" {if $config.view_details_new_window}target="_blank"{/if} href="{$rlBase}{if $config.mod_rewrite}{$pages[$listing_type.Page_key]}/{$listing.Path}/{str2path string=$listing.listing_title}-{$listing.ID}.html{else}?page={$pages[$listing_type.Page_key]}&amp;id={$listing.ID}{/if}">{/if}
					<img alt="{$listing.listing_title}" src="{if empty($listing.Main_photo)}{$rlTplBase}img/no-picture.jpg{else}{$smarty.const.RL_URL_HOME}files/{$listing.Main_photo}{/if}" />
				{if $listing_type.Page}</a>{/if}
				{if !empty($listing.Main_photo) && $config.grid_photos_count}
					<div class="counter">{if $listing_type.Page}<a {if $config.view_details_new_window}target="_blank"{/if} href="{$rlBase}{if $config.mod_rewrite}{$pages[$listing_type.Page_key]}/{$listing.Path}/{str2path string=$listing.listing_title}-{$listing.ID}.html{else}?page={$pages[$listing_type.Page_key]}&amp;id={$listing.ID}{/if}">{/if}{$listing.Photos_count}{if $listing_type.Page}</a>{/if}</div>
				{/if}
			</div>
		</td>
		{/if}
		<td class="fields" valign="top">
			{assign var='f_first' value=true}
			<table>
				{foreach from=$listing.fields item='item' key='field' name='fListings'}
				{if !empty($item.value) && $item.Details_page}
				<tr id="ml_field_{$listing.ID}_{$item.Key}">
					{if $config.sf_display_fields}
					<td class="name">{$item.name}:</td>
					{/if}
					<td class="value {if $f_first}first{/if}">
						{if $f_first && $listing_type.Page}
							<a {if $config.view_details_new_window}target="_blank"{/if} href="{$rlBase}{if $config.mod_rewrite}{$pages[$listing_type.Page_key]}/{$listing.Path}/{str2path string=$listing.listing_title}-{$listing.ID}.html{else}?page={$pages[$listing_type.Page_key]}&amp;id={$listing.ID}{/if}">{$item.value}</a>
						{else}
							{$item.value}
						{/if}
					</td>
				</tr>
				{assign var='f_first' value=false}
				{/if}
				{/foreach}
				
				{rlHook name='listingAfterFields'}
			</table>
		</td>
		<td class="details" valign="top" rowspan="2">
			<table class="info">
			<tr>
				<td class="name">{$lang.category}:</td>
				<td class="value">
					{if $listing.Tmp_name}
						{$listing.Tmp_name} ({$lang.pending})
					{else}
						{if $listing_type.Page}
							<a class="brown_12" target="_blank" title="{$lang[$listing.Cat_key]}" href="{$rlBase}{if $config.mod_rewrite}{$pages.$page_key}/{$listing.Path}{if $listings_type.Cat_postfix}.html{else}/{/if}{else}?page={$pages.$page_key}&amp;category={$listing.Kind_ID}{/if}">{$lang[$listing.Cat_key]}</a>
						{else}
							{$lang[$listing.Cat_key]}
						{/if}
					{/if}
				</td>
			</tr>
			<tr>
				<td class="name">{$lang.added}:</td>
				<td class="value">{$listing.Date|date_format:$smarty.const.RL_DATE_FORMAT}</td>
			</tr>
			<tr>
				<td class="name">{$lang.status}:</td>
				<td class="value">
					{if $listing.Status == 'incomplete'}
						<a href="{$rlBase}{if $config.mod_rewrite}{$pageInfo.Path}.html?incomplete={$listing.ID}&amp;step={$listing.Last_step}{else}?page={$pageInfo.Path}&amp;incomplete={$listing.ID}&amp;step={$listing.Last_step}{/if}" class="{$listing.Status}">{$lang[$listing.Status]}</a>
					{elseif $listing.Status == 'expired' || $listing.Status == 'approval'}
						<a href="{$rlBase}{if $config.mod_rewrite}{$pages.upgrade_listing}.html?id={$listing.ID}{else}?page={$pages.upgrade_listing}&amp;id={$listing.ID}{/if}" class="{$listing.Status}">{$lang[$listing.Status]}</a>
					{else}
						<span {if $listing.Status == 'pending'}title="{$lang.waiting_approval}"{/if} class="{$listing.Status}">{$lang[$listing.Status]}</span>
					{/if}
				</td>
			</tr>
			{if $listing.Plan_expire}
			<tr>
				<td class="name">{$lang.active_till}:</td>
				<td class="value">{$listing.Plan_expire|date_format:$smarty.const.RL_DATE_FORMAT}</td>
			</tr>
			{/if}
			{if $listing.Featured_expire}
			<tr>
				<td class="name">{$lang.featured_till}:</td>
				<td class="value">{$listing.Featured_expire|date_format:$smarty.const.RL_DATE_FORMAT}</td>
			</tr>
			{/if}
			{if $listing.Plan_key}
			<tr>
				<td class="name">{$lang.plan}:</td>
				<td class="value">{$lang[$listing.Plan_key]}</td>
			</tr>
			{/if}
			
			{rlHook name='myListingsafterStatFields'}
			</table>
		</td>
	</tr>
	<tr>
		<td colspan="2" class="nav_icons">
			{if $listings_type.Photo && ($listing.Plan_image > 0 || $listing.Image_unlim)}
				<a title="{$lang.add_photo}" class="nav_icon" href="{$rlBase}{if $config.mod_rewrite}{$pages.add_photo}.html?id={$listing.ID}{else}?page={$pages.add_photo}&amp;id={$listing.ID}{/if}">
					<span class="left">&nbsp;</span><span class="center">
					<img class="add_photo" src="{$rlTplBase}img/blank.gif" alt="" />
					</span><span class="right">&nbsp;</span>
				</a>
			{/if}
			{if $listings_type.Video && ($listing.Plan_video > 0 || $listing.Video_unlim)}
				<a title="{$lang.add_video}" class="nav_icon" href="{$rlBase}{if $config.mod_rewrite}{$pages.add_video}.html?id={$listing.ID}{else}?page={$pages.add_video}&amp;id={$listing.ID}{/if}">
					<span class="left">&nbsp;</span><span class="center">
					<img class="add_video" src="{$rlTplBase}img/blank.gif" alt="" />
					</span><span class="right">&nbsp;</span>
				</a>
			{/if}
			<a title="{$lang.edit_listing}" class="nav_icon" href="{$rlBase}{if $config.mod_rewrite}{$pages.edit_listing}.html?id={$listing.ID}{else}?page={$pages.edit_listing}&amp;id={$listing.ID}{/if}">
				<span class="left">&nbsp;</span><span class="center">
				<img class="edit_listing" src="{$rlTplBase}img/blank.gif" alt="" />
				</span><span class="right">&nbsp;</span>
			</a>
			<a title="{$lang.upgrade_plan}" class="nav_icon" href="{$rlBase}{if $config.mod_rewrite}{$pages.upgrade_listing}.html?id={$listing.ID}{else}?page={$pages.upgrade_listing}&amp;id={$listing.ID}{/if}">
				<span class="left">&nbsp;</span><span class="center">
				<img class="upgrade_listing" src="{$rlTplBase}img/blank.gif" alt="" />
				</span><span class="right">&nbsp;</span>
			</a>
			{if !$listing.Featured_expire && $listing.Status == 'active' && $available_plans}
				<a title="{$lang.make_featured}" class="nav_icon text_button" href="{$rlBase}{if $config.mod_rewrite}{$pages.upgrade_listing}/featured.html?id={$listing.ID}{else}?page={$pages.upgrade_listing}&amp;id={$listing.ID}&amp;featured{/if}">
					<span class="left">&nbsp;</span><span class="center">{$lang.make_featured}</span><span class="right">&nbsp;</span>
				</a>
			{/if}
			
			{rlHook name='myListingsIcon'}
		</td>
	</tr>
	</table>
	
	<img class="delete_highlight" id="delete_listing_{$listing.ID}" src="{$rlTplBase}img/blank.gif" alt="" title="{$lang.delete}" />
	
	{if $listing.Featured_expire}
		<div class="label" {if $smarty.const.RL_LANG_CODE != 'en'}style="background-image: url('{$rlTplBase}img/featured_{$smarty.const.RL_LANG_CODE|lower}.png')"{/if}>{if $listing_type.Page}<a {if $config.view_details_new_window}target="_blank"{/if} href="{$rlBase}{if $config.mod_rewrite}{$pages[$listing_type.Page_key]}/{$listing.Path}/{str2path string=$listing.listing_title}-{$listing.ID}.html{if $hl}?highlight{/if}{else}?page={$pages[$listing_type.Page_key]}&amp;id={$listing.ID}{if $hl}&amp;highlight{/if}{/if}">&nbsp;</a>{/if}</div>
	{/if}
</div>

<!-- my listing item end -->