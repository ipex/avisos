<?xml version="1.0" encoding="utf-8"?>
<rss version="2.0">
<channel>
<image>
	<url>{$rlTplBase}img/logo.png</url>
	<title>{$site_name|replace:'&':'&amp;'}</title>
	<link>{$smarty.const.RL_URL_HOME}</link>
</image>

<title>{$rss.title|replace:'&':'&amp;'}</title>
<description>{$rss.description|replace:'&':'&amp;'}</description>
<link>{$rss.back_link}</link>

{if $rss_item == 'account-listings'}
	{foreach from=$listings item='listing'}
		{if $listing.Listing_type}
			{assign var='listing_type' value=$listing_types[$listing.Listing_type]}
		{/if}
		
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'rss_listing.tpl'}
	{/foreach}
{elseif $rss_item == 'category'}
	{foreach from=$listings item='listing'}
		{if $listing.Listing_type}
			{assign var='listing_type' value=$listing_types[$listing.Listing_type]}
		{/if}
		
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'rss_listing.tpl'}
	{/foreach}
{elseif $rss_item == 'news'}
	{foreach from=$news item='news_item'}
		<item>
			<title>{$news_item.title|replace:"&":"&amp;"}</title>
			<pubDate>{$news_item.Date|date_format:'%a, %d %b %Y %H:%M:%S GMT'}</pubDate>
			<link>{$rlBase}{if $config.mod_rewrite}{$pages.news}/{$news_item.path}.html{else}?page={$pages.news}&amp;id={$news_item.ID}{/if}</link>
			<description><![CDATA[{$news_item.content}]]></description>
			{$tplRssNewsItem}
		</item>
	{/foreach}
{else}
	{foreach from=$listings item='listing'}
		{if $listing.Listing_type}
			{assign var='listing_type' value=$listing_types[$listing.Listing_type]}
		{/if}
		
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'rss_listing.tpl'}
	{/foreach}
{/if}

{rlHook name='tplRssFeedBottom'}

</channel>
</rss>