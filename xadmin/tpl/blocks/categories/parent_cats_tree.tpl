<!-- parent categories -->

{if $categories}
	<fieldset>
		<legend id="legend_section_{$type.Key}" class="up" onclick="fieldset_action('section_{$type.Key}');">{$listing_types[$type].name}</legend>
		<div id="section_{$type.Key}" style="padding: 5px 0 10px 0;">
			{include file='blocks'|cat:$smarty.const.RL_DS|cat:'category_level.tpl'}
		</div>
	</fieldset>
{/if}

<!-- parent categories end -->