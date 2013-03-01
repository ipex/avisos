<!-- search form block -->

<form id="search_{$form_key}" method="post" action="{$rlBase}{if $config.mod_rewrite}{$pages.search}.html{else}?page={$pages.search}{/if}">
	<input type="hidden" name="form" value="{$form_key}" />
	
	{foreach from=$hidden_fields item='hField_val' key='hField_key'}
	<input type="hidden" name="f[{$hField_key}]" value="{$hField_val}" />	
	{/foreach}
	
	{foreach from=$form item='group'}
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fields_refine.tpl' fields=$group.Fields postfix=$block.Key}
	{/foreach}
	
	<input style="margin: 10px 0 0 0;" class="button" type="submit" name="search" value="{$lang.search}" />
	
	{if $use_photos}<input id="photos_{$form_key}" type="checkbox" name="f[with_photo]" value="true" /> <label for="photos_{$form_key}" class="fLable">{$lang.with_photos_only}</label>{/if}
</form>

<!-- search form block -->
