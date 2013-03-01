<!-- video grid -->

{if $config.video_display_type == 'preview'}
	{if $config.video_thumbnail_position == 'top'}
		<div class="thumb_horizontal">{include file='blocks'|cat:$smarty.const.RL_DS|cat:'video_thumbnails.tpl'}</div>
	{/if}
	<table class="listing_details">
	<tr>
		{if $config.video_thumbnail_position == 'left'}
		<td valign="top" class="side_bar_video">
			{include file='blocks'|cat:$smarty.const.RL_DS|cat:'video_thumbnails.tpl'}
		</td>
		{/if}
		<td valign="top" class="details">
			<div class="{if !$autoload}highlight {/if}highlight_loading">
				<a class="hide" href="{$smarty.const.RL_FILES_URL}{$videos.0.Video}" style="height:{$config.video_height}px;" id="player"></a>
				<div class="hide" id="video_youTube" style="height:{$config.video_height}px;"></div>
			</div>
		</td>
		{if $config.video_thumbnail_position == 'right'}
		<td valign="top" class="side_bar_video thumbnails_right">
			{include file='blocks'|cat:$smarty.const.RL_DS|cat:'video_thumbnails.tpl'}
		</td>
		{/if}
	</tr>
	</table>
	{if $config.video_thumbnail_position == 'bottom'}
		<div class="thumb_horizontal">{include file='blocks'|cat:$smarty.const.RL_DS|cat:'video_thumbnails.tpl'}</div>
	{/if}
{else}
	<div class="highlight">
	{if $videos|@count == 1}
		{if $videos.0.Type == 'local'}
			{include file='blocks'|cat:$smarty.const.RL_DS|cat:'video.tpl'}
		{elseif $videos.0.Type == 'youtube'}
			<script type="text/javascript">
			flynax.loadVideo('{$videos.0.Preview}', 'div#area_video div.highlight');
			</script>
		{/if}
	{else}
		{assign var='replace' value=`$smarty.ldelim`key`$smarty.rdelim`}
		<ul class="thumbnails inline">
		{foreach from=$videos item='video'}
			<li id="video_{$video.ID}">
				{if $video.Type == 'local'}
					<img class="item pointer" src="{$smarty.const.RL_FILES_URL}{$video.Preview}" alt="" />
					<script type="text/javascript">//<![CDATA[
					{literal}

					$('#video_{/literal}{$video.ID}{literal} img.item').fancybox({
						padding: 10,
						width: {/literal}{$config.video_width}{literal},
						height: {/literal}{$config.video_height}{literal},
						content: '<a href="{/literal}{$smarty.const.RL_FILES_URL}{$video.Video}{literal}" style="display:block;width:{/literal}{$config.video_width}{literal}px;height:{/literal}{$config.video_height}{literal}px;" id="player"></a>',
						afterShow:	function(){
							flowplayer('player', rlConfig['libs_url']+'player/flowplayer-3.2.7.swf', {
								wmode: 'transparent',
								plugins: {
							        pseudo: {
							            url: rlConfig['libs_url']+'player/flowplayer.pseudostreaming-3.2.9.swf'
							        }
								},
								 clip: {
							        provider: 'pseudo',
							        url: '{/literal}{$smarty.const.RL_FILES_URL}{$video.Video}{literal}'
							    }
							});
						},
						afterClose: function(){
							$f().stop();
						},
						helpers: {
							media : {},
							overlay: {
								opacity: 0.5
							}
						}
					});
					
					{/literal}
					</script>
				{else}
					<a class="youtube fancybox.iframe" href="http://www.youtube.com/embed/{$video.Preview}?autoplay=1"><img class="item pointer" src="{$l_youtube_thumbnail|replace:$replace:$video.Preview}" alt="" /></a>
				{/if}
			</li>
		{/foreach}
		</ul>
		<div class="clear"></div>
		
		<script type="text/javascript">//<![CDATA[
		{literal}
		
		/* preview video handler */
		$('ul.thumbnails > li > a.youtube').fancybox({
			padding: 10,
			width: {/literal}{$config.video_width}{literal},
			height: {/literal}{$config.video_height}{literal},
			helpers: {
				media : {},
				overlay: {
					opacity: 0.5
				}
			}
		});
		
		{/literal}
		</script>
		
	{/if}
</div>
{/if}

<!-- video grid end -->