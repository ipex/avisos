<!-- my listings -->

{if !empty($listings)}

	<table class="grid_navbar my_listings">
	<tr>
		<td class="sorting">
			<span class="caption">{$lang.sort_listings_by}:</span>
			{foreach from=$sorting item='field_item' key='sort_key' name='fSorting'}
				<a {if $sort_by == $sort_key}class="active {if $sort_type == 'asc' || empty($sort_type)}asc{else}desc{/if}"{/if} title="{$lang.sort_listings_by} {$field_item.name}" href="{if $config.mod_rewrite}?{else}{$smarty.const.RL_URL_HOME}index.php?page={$pages.browse}&amp;category={$smarty.get.category}&amp;{/if}sort_by={$sort_key}{if $sort_by == $sort_key}&amp;sort_type={if $sort_type == 'asc' || !isset($sort_type)}desc{elseif !empty($sort_key) && empty($sort_type)}desc{else}asc{/if}{/if}">{$field_item.name}</a>
				{if !$smarty.foreach.fSorting.last}<span class="divider">|</span>{/if}
			{/foreach}
			
			{rlHook name='browseAfterSorting'}
		</td>
		<td class="custom">{rlHook name='myListingsGridNavBar'}</td>
	</tr>
	</table>
	
	{rlHook name='myListingsBeforeListings'}
	
	<div id="listings" class="my_listings">
	{foreach from=$listings item='listing' key='key'}
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'my_listing.tpl'}
	{/foreach}
	</div>
	
	<script type="text/javascript">
	{literal}
	
	$(document).ready(function(){
		$('div#listings img.delete_highlight').each(function(){
			$(this).flModal({
				caption: '{/literal}{$lang.warning}{literal}',
				content: '{/literal}{$lang.notice_delete_listing}{literal}',
				prompt: 'xajax_deleteListing('+ $(this).attr('id').split('_')[2] +')',
				width: 'auto',
				height: 'auto'
			});
		});
	});
	
	{/literal}
	</script>

	<!-- paging block -->
	{paging calc=$pInfo.calc total=$listings current=$pInfo.current per_page=$config.listings_per_page}
	<!-- paging block end -->

{else}
	<div class="info">
		{assign var='link' value='<a href="'|cat:$add_listing_href|cat:'">$1</a>'}
		{$lang.no_listings_here|regex_replace:'/\[(.+)\]/':$link}
	</div>
{/if}

{rlHook name='myListingsBottom'}

<!-- my listings end -->
