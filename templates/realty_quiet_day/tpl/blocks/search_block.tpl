<!-- search form block -->

<form id="search_{$form_key}" method="post" action="{$rlBase}{if $config.mod_rewrite}{$pages.search}.html{else}?page={$pages.search}{/if}">
<input type="hidden" name="form" value="{$form_key}" />

{foreach from=$hidden_fields item='hField_val' key='hField_key'}
<input type="hidden" name="f[{$hField_key}]" value="{$hField_val}" />	
{/foreach}

{foreach from=$form item='group'}
	{if $group.Group_ID}
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id=$group.ID name=$group.name style='fg'}
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fields_search.tpl' fields=$group.Fields}
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}
	{else}
		<div style="margin: 0 0 5px 13px;">
			{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fields_search.tpl' fields=$group.Fields postfix=$block.Key}
		</div>
	{/if}
{/foreach}

<input style="margin: 5px 0 5px 13px;" class="button" type="submit" name="search" value="{$lang.search}" />

{if $use_photos}<input id="photos_{$form_key}" type="checkbox" name="f[with_photo]" value="true" /> <label for="photos_{$form_key}" class="fLable">{$lang.with_photos_only}</label>{/if}

</form>

<!-- search form block -->
