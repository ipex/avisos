<!-- recently listings area -->
{assign var='partition' value=$listing_types.$lt_key.Page_key}

{if $listings}
	{assign var='cur_date' value=false}
	{assign var='grid_mode' value='list'}
	{assign var='replace_patter' value=`$smarty.ldelim`day`$smarty.rdelim`}

	<!-- listings -->
	<div id="listings">
		<ul>
			{foreach from=$listings item='listing' key='key' name='listingsF'}
				{if $listing.Post_date != $cur_date}
					{if $listing.Date_diff == 1}
						{assign var='divider_name' value=$lang.today}
					{elseif $listing.Date_diff == 2}
						{assign var='divider_name' value=$lang.yesterday}
					{elseif $listing.Date_diff > 2 && $listing.Date_diff < 8}
						{assign var='divider_name' value=$lang.days_ago_pattern|replace:$replace_patter:$listing.Date_diff-1}
					{else}
						{assign var='divider_name' value=$listing.Post_date|date_format:$smarty.const.RL_DATE_FORMAT}
					{/if}
					{include file='blocks'|cat:$smarty.const.RL_DS|cat:'divider.tpl' name=$divider_name}
					{assign var='cur_date' value=$listing.Post_date}
				{/if}
				
				{include file='blocks'|cat:$smarty.const.RL_DS|cat:'listing.tpl'}
			{/foreach}
		</ul>
	</div>
	<!-- listings end -->

	<!-- paging block -->
	{if $config.mod_rewrite}
		{paging calc=$pInfo.calc total=$listings|@count current=$pInfo.current per_page=$config.listings_per_page url=$pages.$partition}
	{else}
		{paging calc=$pInfo.calc total=$listings|@count current=$pInfo.current per_page=$config.listings_per_page url=$pages.$partition}
	{/if}	
	<!-- paging block end -->
{else}
	<div class="padding">{$lang.no_listings_here_submit_deny}</div>
{/if}

<!-- recently listings area end -->