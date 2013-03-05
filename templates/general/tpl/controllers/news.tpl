<!-- news tpl -->

<div class="highlight">
	{if empty($news)}
		{if !empty($all_news)}
			<ul class="news">
			{foreach from=$all_news item='news'}
				<li class="page">
					<table>
					<tr>
						<td>
							<a title="{$news.title}" href="{$rlBase}{if $config.mod_rewrite}{$pages.news}/{$news.Path}.html{else}?page={$pages.news}&amp;id={$news.ID}{/if}">{$news.title}</a>
							{rlHook name='newsPostCaption'}
						</td>
						<td class="date">
							<div>
								{$news.Date|date_format:$smarty.const.RL_DATE_FORMAT}
							</div>
						</td>
					</tr>
					</table>
					<div class="dark">
						{$news.content|strip_tags:false|truncate:$config.news_page_content_length:"":false}{if $news.content|strlen > $config.news_page_content_length}...{/if}
						{rlHook name='newsPostContent'}
					</div>
				</li>
			{/foreach}
			</ul>
			
			<!-- paging block -->
			{paging calc=$pInfo.calc total=$all_news current=$pInfo.current per_page=$config.news_at_page}
			<!-- paging block end -->
			
		{else}
			<div class="info">{$lang.no_news}</div>
		{/if}
	{else}
		<div class="news">
			<table class="sTable">
			<tr>
				<td>
					{rlHook name='newsPostCaption'}
				</td>
				<td class="date">
					<div>
						{$news.Date|date_format:$smarty.const.RL_DATE_FORMAT}
					</div>
				</td>
			</tr>
			</table>
			<div class="dark">
				{$news.content}
				
				{rlHook name='newsPostContent'}
			</div>
		</div>
		
		<div class="ralign">
			<a title="{$lang.back_to_news}" href="{$rlBase}{if $config.mod_rewrite}{$pages.news}.html{else}?page={$pages.news}{/if}">{$lang.back_to_news}</a>
		</div>
	{/if}
	
	{rlHook name='newsBottomTpl'}
</div>

<!-- news tpl end -->