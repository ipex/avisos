<!-- listing preview tpl -->

{if $cur_step == 'preview'}
	<div class="area_preview step_area">
		<h1>{$listing_title}</h1>
		{include file='controllers'|cat:$smarty.const.RL_DS|cat:'listing_details.tpl'}
		
		<div style="padding: 20px 0 0 0;" class="clear">
			<form method="post" action="{$rlBase}{if $config.mod_rewrite}{$pageInfo.Path}/{$category.Path}/{$steps.$cur_step.path}.html{else}?page={$pageInfo.Path}&amp;id={$category.ID}&amp;step={$steps.$cur_step.path}{/if}">
				<input type="hidden" name="step" value="preview" />
				
				<table class="submit">
				<tr>
					<td class="name button"><a href="{$rlBase}{if $config.mod_rewrite}{$pageInfo.Path}/{$category.Path}/{$prev_step.path}.html{else}?page={$pageInfo.Path}&amp;id={$category.ID}&amp;step={$prev_step.path}{/if}" class="dark_12">{if $smarty.const.RL_LANG_DIR == 'ltr'}&larr;{else}&rarr;{/if} {$lang.edit_listing}</a></td>
					<td class="field button"><span class="arrow"><input type="submit" value="{$lang.listingPreview_confirm}" id="video_submit" /><label for="video_submit" class="right">&nbsp;</label></span></td>
				</tr>
				</table>
			</form>
		</div>
	</div>
	
	<script type="text/javascript">
	{literal}
		$(document).ready(function(){
			$('div#controller_area > div.highlight').hide();
			$('ul.statistics li a.button').parent().hide();
		});
	{/literal}
	</script>
{/if}

<!-- listing preview tpl end -->