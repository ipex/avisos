<!-- my listings -->

<div id="listings">
	{if !empty($listings)}
	
	<!-- sorting -->
	<div class="sorting">
		<div>
			<span class="caption">{$lang.sort_listings_by}:</span>
			{foreach from=$sorting item='sort_item' key='sort_key' name='fSorting'}
				{if $sort_by == $sort_key}
					<a style="text-decoration: none;" title="{$lang.sort_listings_by} {$sort_item.phrase}" class="static" href="{if $config.mod_rewrite}?{else}index.php?page={$pageInfo.Path}&amp;{/if}sort_by={$sort_key}&amp;sort_type={if $sort_type == 'desc' || !isset($sort_type)}asc{else}desc{/if}">
						<b>{$sort_item.phrase}</b>
						<img alt="" src="{$rlTplBase}img/arrow_{if $sort_type == 'asc' || empty($sort_type)}asc{else}desc{/if}.gif" />
					</a>
				{else}
				<a title="{$lang.sort_listings_by} {$sort_item.phrase}" class="static" href="{if $config.mod_rewrite}?{else}index.php?page={$pageInfo.Path}&amp;{/if}sort_by={$sort_key}">{$sort_item.phrase}</a>{/if}
				{if !$smarty.foreach.fSorting.last}<span class="grey_small">|</span>{/if}
			{/foreach}
			
			{rlHook name='myListingsSorting'}
			
		</div>	
	</div>
	<!-- sorting end -->
	
	{rlHook name='myListingsBeforeListings'}
	
	{foreach from=$listings item='listing' key='key'}
		
		<fieldset class="item {$listing.Status}" id="listing_{$listing.ID}">
		{if $listing.Featured_expire}
		<legend class="blue_bright" align="{$text_dir_rev}">{$lang.featured}</legend>
		{/if}

			<table class="sTable {$listing.Status}">
			<tr>
				{if $pageInfo.Key != 'my_ads'}
					<td rowspan="2" style="width: 100px;" align="center" valign="top">
					<a {if $config.view_details_new_window}target="_blank"{/if} href="{$rlBase}{if $config.mod_rewrite}{$pages.browse}/{$listing.Path}/{str2path string=$listing.listing_title}-l{$listing.ID}.html{else}?page={$pages.browse}&amp;id={$listing.ID}{/if}">
						<img alt="{$listing.fields.0.value}" title="{$listing.fields.0.value}" src="{if empty($listing.Main_photo)}{$rlTplBase}img/no_photo.gif{else}{$smarty.const.RL_URL_HOME}files/{$listing.Main_photo}{/if}" />
					</a>
					</td>
				{/if}
				<td {if $pageInfo.Key != 'my_ads'}class="spliter"{/if} rowspan="2"></td>
				<td valign="top" style="height: 65px">
					<table>
					{foreach from=$listing.fields item='item' key='field' name='fListings'}
					{if !empty($item.value)}
					<tr>
						{if $config.sf_display_fields}
						<td valign="top">
							<div class="field">{$item.name}:</div>
						</td>
						<td style="width: 3px;"></td>
						{/if}
						<td valign="top">
							{if $smarty.foreach.fListings.first && $pageInfo.Key != 'my_ads'}
								<a class="static" {if $config.view_details_new_window}target="_blank"{/if} href="{$rlBase}{if $config.mod_rewrite}{$pages.browse}/{$listing.Path}/{str2path string=$listing.listing_title}-l{$listing.ID}.html{else}?page={$pages.browse}&amp;id={$listing.ID}{/if}">
									<b>{$item.value}</b>
								</a>
							{else}
								<div class="value">
									{$item.value}
								</div>
							{/if}
						</td>
					</tr>
					{/if}
					{/foreach}
					</table>
				</td>
				<td rowspan="2" align="{$text_dir_rev}" valign="top">
					<table>
					{if $listing.Type && $config.listing_for}
					<tr>
						<td align="{$text_dir_rev}"><span class="field">{assign var='type_f_name' value='listing_fields+name+Type'}<small>{$lang.$type_f_name}:</small></span></td>
						<td align="{$text_dir}" style="width: 90px;">
							{assign var='l_type' value='listing_fields+name+Type_'|cat:$listing.Type}
							<span class="value"><small><b>{$lang.$l_type}</b></small></span>
						</td>
					</tr>
					{/if}
					<tr>
						<td align="{$text_dir_rev}"><span class="field"><small>{$lang.category}:</small></span></td>
						<td align="{$text_dir}">
							{if $listing.Tmp_name}
								<span class="value"><small><b>{$listing.Tmp_name} ({$lang.pending})</b></small></span>
							{else}
								{if $listing.Category_type == 'advertising'}<span class="value"><small><b>{$listing.name}</b></small></span>{else}<a title="{$listing.name}" class="static_small" href="{$rlBase}{if $config.mod_rewrite}{$pages.browse}/{$listing.Path}{if $config.display_cat_html}.html{else}/{/if}{else}?page={$pages.browse}&amp;category={$listing.Kind_ID}{/if}">{$listing.name}</a>{/if}
							{/if}
						</td>
					</tr>
					<tr>
						<td align="{$text_dir_rev}"><span class="field"><small>{$lang.added}:</small></span></td>
						<td align="{$text_dir}"><span class="value"><small>{$listing.Date|date_format:$smarty.const.RL_DATE_FORMAT}</small></span></td>
					</tr>
					<tr>
						<td align="{$text_dir_rev}"><span class="field"><small>{$lang.status}:</small></span></td>
						<td align="{$text_dir}">
							{assign var='l_status' value=$listing.Status}
							<span class="value {$listing.Status}"><small><b>{$lang.$l_status}</b></small></span>
						</td>
					</tr>
					{if $pageInfo.Key != 'my_ads'}
					<tr>
						<td align="{$text_dir_rev}"><span class="field"><small>{$lang.featured}:</small></span></td>
						<td align="{$text_dir}">
							<input class="reduced" {if $listing.Featured_expire}checked="checked" onclick="return false;"{else} title="{$lang.make_featured}" onclick="location.href='{$rlBase}{if $config.mod_rewrite}{$pages.upgrade_listing}.html?id={$listing.ID}&amp;features{else}?page={$pages.upgrade_listing}&amp;id={$listing.ID}&amp;features{/if}';return false;"{/if} type="checkbox" name="featured" />
							{if $listing.Featured_expire}
								<span class="value" title="{$lang.expire_date}"><small>{$listing.Featured_expire|date_format:$smarty.const.RL_DATE_FORMAT}</small></span>
							{/if}
						</td>
					</tr>
					{/if}
					<tr>
						<td align="{$text_dir_rev}"><span class="field"><small>{if $listing.Free == 'free'}{$lang.free}{else}{$lang.payed}{/if}:</small></span></td>
						<td align="{$text_dir}">
							<input class="reduced" {if $listing.Plan_expire}checked="checked" onclick="return false;"{else} title="{$lang.make_payment}" onclick="location.href='{$rlBase}{if $config.mod_rewrite}{$pages.upgrade_listing}.html?id={$listing.ID}{else}?page={$pages.upgrade_listing}&amp;id={$listing.ID}{/if}';return false;"{/if} type="checkbox" name="featured" />
							{if $listing.Plan_expire}
								<span class="value" title="{$lang.expire_date}"><small>{$listing.Plan_expire|date_format:$smarty.const.RL_DATE_FORMAT}</small></span>
							{/if}
						</td>
					</tr>
					{if !empty($listing.Plan)}
					<tr>
						<td align="{$text_dir_rev}"><span class="field"><small>{$lang.plan}:</small></span></td>
						<td align="{$text_dir}"><span class="value" title="{$lang.expire_date}"><small>{$listing.Plan}</small></span></td>
					</tr>
					{/if}
					
					{rlHook name='myListingsafterStatFields'}
					
					</table>
				</td>
			</tr>
			<tr>
				<td valign="bottom" class="icon">
				{if $pageInfo.Key != 'my_ads'}
					<a {if $config.view_details_new_window}target="_blank"{/if} href="{$rlBase}{if $config.mod_rewrite}{$pages.browse}/{$listing.Path}/{str2path string=$listing.listing_title}-l{$listing.ID}.html{else}?page={$pages.browse}&amp;id={$listing.ID}{/if}">
						<img title="{$lang.view_details}" alt="{$lang.view_details}" src="{$rlTplBase}img/view_details_icon.gif" /></a>
					<a href="{$rlBase}{if $config.mod_rewrite}{$pages.add_photo}.html?id={$listing.ID}{else}?page={$pages.add_photo}&amp;id={$listing.ID}{/if}">
						<img title="{$lang.add_photo}" alt="{$lang.add_photo}" src="{$rlTplBase}img/add_photo_icon.gif" /></a>
					<a href="{$rlBase}{if $config.mod_rewrite}{$pages.add_video}.html?id={$listing.ID}{else}?page={$pages.add_video}&amp;id={$listing.ID}{/if}">
						<img title="{$lang.add_video}" alt="{$lang.add_video}" src="{$rlTplBase}img/add_video_icon.gif" /></a>
				{/if}
					<a href="{$rlBase}{if $config.mod_rewrite}{$pages.edit_listing}.html?id={$listing.ID}{else}?page={$pages.edit_listing}&amp;id={$listing.ID}{/if}">
						<img title="{$lang.edit_listing}" alt="{$lang.edit_listing}" src="{$rlTplBase}img/edit_icon.gif" /></a>
					<a href="{$rlBase}{if $config.mod_rewrite}{$pages.upgrade_listing}.html?id={$listing.ID}{else}?page={$pages.upgrade_listing}&amp;id={$listing.ID}{/if}">
						<img title="{$lang.upgrade_plan}" alt="{$lang.upgrade_plan}" src="{$rlTplBase}img/upgrade_icon.gif" /></a>
					<a href="javascript:void(0);" onclick="rlConfirm( '{$lang.notice_delete_listing|escape:quotes}', 'xajax_deleteListing', Array('{$listing.ID}'), 'listing_loading' );">
						<img title="{$lang.delete}" alt="{$lang.delete}" src="{$rlTplBase}img/delete_icon.gif" /></a>
						
					{rlHook name='myListingsIcon'}

				</td>
				<td></td>
			</tr>
			</table>
		</fieldset>
	{/foreach}

	<!-- paging block -->
	{paging calc=$pInfo.calc total=$listings current=$pInfo.current per_page=$config.listings_per_page}
	<!-- paging block end -->
	
	{else}
		{assign var='replace' value='<a class="navigator" href="'|cat:$add_listing_href|cat:'">'|cat:$lang.click_here|cat:'</a>'}
		<div style="margin: 10px;" class="grey_middle">{$lang.no_listings_here|replace:'[click]':$replace}</div>
	{/if}
</div>

{rlHook name='myListingsBottom'}

<!-- my listings end -->
