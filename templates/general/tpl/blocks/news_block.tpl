<!-- news block tpl -->

{if !empty($all_news)}
	<ul class="news">
	{foreach from=$all_news item='news'}
		<li>
			<table>
			<tr>
				<td><a title="{$news.title}" href="{$rlBase}{if $config.mod_rewrite}{$pages.news}/{$news.Path}.html{else}?page={$pages.news}&amp;id={$news.ID}{/if}">{$news.title}</a></td>
				<td class="date">
					<div>
						{$news.Date|date_format:'%d'}<br />
						{$news.Date|date_format:'%b'}
					</div>
				</td>
			</tr>
			</table>
			<div class="dark">{$news.content|strip_tags:false|truncate:$config.news_block_content_length:"":false}{if $news.content|strlen > $config.news_block_content_length}...{/if}</div>
		</li>
	{/foreach}
	</ul>
	<div class="ralign">
		<a title="{$lang.all_news}" href="{$rlBase}{if $config.mod_rewrite}{$pages.news}.html{else}?page={$pages.news}{/if}">{$lang.all_news}</a>
	</div>
{else}
	{$lang.no_news}
{/if}

<!-- news block tpl end -->