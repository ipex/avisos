<!-- news tpl -->

<div class="padding">
	{if empty($news)}
		{if !empty($all_news)}
			{foreach from=$all_news item='news'}
				<div style="padding: 0 0 20px">
					<div class="black_small">{$news.Date|date_format:$smarty.const.RL_DATE_FORMAT}</div>
					<a title="{$news.title}" href="{$rlBase}{if $config.mod_rewrite}{$pages.news}/{$news.Path}.html{else}?page={$pages.news}&amp;id={$news.ID}{/if}">{$news.title}</a>
					<div class="text">{$news.content|strip_tags:false|truncate:$config.news_page_content_length:"":false}{if $news.content|strlen > $config.news_page_content_length}...{/if}</div>
					
					{rlHook name='mobileTplNewsPostContent'}
				</div>
			{/foreach}
			
			<!-- paging block -->
			{paging calc=$pInfo.calc total=$all_news current=$pInfo.current per_page=$config.news_at_page}
			<!-- paging block end -->
			
		{else}
			{$lang.no_news}
		{/if}
	{else}
		<div>{$news.Date|date_format:$smarty.const.RL_DATE_FORMAT}</div>
		<div class="text">{$news.content}</div>
		
		{rlHook name='mobileTplNewsPostContent'}
		
		<div style="text-align: right;">
			<a title="{$lang.back_to_news}" href="{$rlBase}{if $config.mod_rewrite}{$pages.news}.html{else}?page={$pages.news}{/if}">{$lang.back_to_news}</a>
		</div>
	{/if}
	
	{rlHook name='newsBottomTpl'}
</div>

{*<div class="padding">
	{if empty($news)}
		{if !empty($all_news)}
			{foreach from=$all_news item='news'}
				<div style="padding: 0 0 20px">
					<div class="black_small">{$news.Date|date_format:$smarty.const.RL_DATE_FORMAT}</div>
					<a title="{$news.title}" href="{$rlBase}{if $config.mod_rewrite}{$pages.news}/{str2path string=$news.title}-n{$news.ID}.html{else}?page={$pages.news}&amp;id={$news.ID}{/if}">{$news.title}</a>
					<div class="text">{$news.content|strip_tags:false|truncate:$config.news_page_content_length:"":false}{if $news.content|strlen > $config.news_page_content_length}...{/if}</div>
					
					{rlHook name='newsPostContent'}
					
				</div>
			{/foreach}
			
			<!-- paging block -->
			{paging calc=$pInfo.calc total=$all_news current=$pInfo.current per_page=$config.news_at_page}
			<!-- paging block end -->
			
		{else}
			<div class="grey_middle">{$lang.no_news}</div>
		{/if}
	{else}
		<div>{$news.Date|date_format:$smarty.const.RL_DATE_FORMAT}</div>
		<div class="static">{$news.title}</div>
		<div class="text">{$news.content}</div>
		
		{rlHook name='newsPostContent'}
		
		<div style="text-align: right;">
			&larr; <a title="{$lang.back_to_news}" class="static" href="{$rlBase}{if $config.mod_rewrite}{$pages.news}.html{else}?page={$pages.news}{/if}">{$lang.back_to_news}</a>
		</div>
	{/if}
</div>*}

<!-- news tpl end -->