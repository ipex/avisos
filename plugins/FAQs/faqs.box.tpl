<!-- faqs block tpl -->

{if !empty($all_faqs_block)}
	<ul class="news faqs">
	{foreach from=$all_faqs_block item='faqs'}
		<li>
			<a title="{$faqs.title}" href="{$rlBase}{if $config.mod_rewrite}{$pages.faqs}/{$faqs.Path}.html{else}?page={$pages.faqs}&amp;id={$faqs.ID}{/if}">{$faqs.title}</a>
			<div class="dark">{$faqs.content|strip_tags:false|truncate:$config.faqs_block_content_length:"":false}{if $faqs.content|strlen > $config.faqs_block_content_length}...{/if}</div>
		</li>
	{/foreach}
	</ul>
	<div class="ralign">
		<a title="{$lang.view_all_faqs}" href="{$rlBase}{if $config.mod_rewrite}{$pages.faqs}.html{else}?page={$pages.faqs}{/if}">{$lang.view_all_faqs}</a>
	</div>
{else}
	{$lang.no_faqs}
{/if}

<!-- faqs block tpl end -->