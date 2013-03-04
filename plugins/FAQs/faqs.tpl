<!-- faqs tpl -->

<div class="highlight">
	{if empty($faqs)}
		{if !empty($all_faqs)}
			<ul class="news faqs">
			{foreach from=$all_faqs item='faqs'}
				<li class="page">
					<table>
					<tr>
						<td>
							<a style="font-size: 18px;" title="{$faqs.title}" href="{$rlBase}{if $config.mod_rewrite}{$pages.faqs}/{$faqs.Path}.html{else}?page={$pages.faqs}&amp;id={$faqs.ID}{/if}">{$faqs.title}</a>
							{rlHook name='faqsPostCaption'}
						</td>
						<td class="date">
							<div>
								{$faqs.Date|date_format:$smarty.const.RL_DATE_FORMAT}
							</div>
						</td>
					</tr>
					</table>
					<div class="dark">
						{$faqs.content|strip_tags:false|truncate:$config.faqs_page_content_length:"":false}{if $faqs.content|strlen > $config.faqs_page_content_length}...{/if}
						{rlHook name='faqsPostContent'}
					</div>
				</li>
			{/foreach}
			</ul>
			
			<!-- paging block -->
			{paging calc=$pInfo.calc total=$all_faqs current=$pInfo.current per_page=$config.faqs_at_page}
			<!-- paging block end -->
			
		{else}
			<div class="info">{$lang.no_faqs}</div>
		{/if}
	{else}
		<div class="news faqs">
			<div class="dark">
				{$faqs.content}
			</div>
			
			<table class="sTable">
			<tr>
				<td class="date">
					<div style="margin: 0;">
						{$faqs.Date|date_format:$smarty.const.RL_DATE_FORMAT}
					</div>
				</td>
			</tr>
			</table>
		</div>
		
		<div class="ralign">
			<a title="{$lang.back_to_faqs}" href="{$rlBase}{if $config.mod_rewrite}{$pages.faqs}.html{else}?page={$pages.faqs}{/if}">{$lang.back_to_faqs}</a>
		</div>
	{/if}
</div>

<!-- faqs tpl end -->