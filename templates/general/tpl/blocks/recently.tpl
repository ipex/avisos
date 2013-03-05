<!-- recently listings area -->
{assign var='partition' value=$listing_types.$lt_key.Page_key}

{if $listings}
	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'grid.tpl' periods=true}

	<!-- paging block -->
	{if $config.mod_rewrite}
		{paging calc=$pInfo.calc total=$listings|@count current=$pInfo.current per_page=$config.listings_per_page url=$pages.$partition}
	{else}
		{paging calc=$pInfo.calc total=$listings|@count current=$pInfo.current per_page=$config.listings_per_page url=$pages.$partition}
	{/if}	
	<!-- paging block end -->
{else}
	<div class="info">
		{if $config.mod_rewrite}
			{assign var='href' value=$rlBase|cat:$pages.add_listing|cat:'.html'}
		{else}
			{assign var='href' value=$rlBase|cat:'?page='|cat:$pages.add_listing}
		{/if}
		{assign var='link' value='<a href="'|cat:$href|cat:'">$1</a>'}
		{$lang.no_listings_here|regex_replace:'/\[(.+)\]/':$link}
	</div>
{/if}

<!-- recently listings area end -->
