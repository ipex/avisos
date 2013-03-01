<!-- category deleting -->

{assign var='replace' value=`$smarty.ldelim`category`$smarty.rdelim`}
<div>{$lang.delete_category_conditions|replace:$replace:$category.name}</div>

<table class="list" style="margin: 0 0 15px 10px;">
{if !empty($delete_info.categories)}
	<tr>
		<td class="name" style="width: 80px">{$lang.subcategories}:</td>
		<td class="value"><b>{$delete_info.categories}</b></td>
	</tr>
{/if}
{if !empty($delete_info.listings)}
	<tr>
		<td class="name" style="width: 80px">{$lang.listings}:</td>
		<td class="value"><b>{$delete_info.listings}</b></td>
	</tr>
{/if}
</table>

{$lang.choose_removal_method}
<div style="margin: 5px 10px">
	<div style="padding: 2px 0;"><label><input type="radio" value="delete" name="del_method" onclick="$('div#replace_content:visible').slideUp();$('#top_buttons').slideDown();$('#bottom_buttons').slideUp();" /> {if $config.trash}{$lang.full_category_drop}{else}{$lang.full_category_delete}{/if}</label></div>
	<div style="padding: 2px 0;"><label><input type="radio" value="replace" name="del_method" /> {$lang.replace_parent_category}</label></div>
	
	<div style="margin: 5px 0;">
		<div id="top_buttons">
			<input class="simple" type="button" value="{$lang.go}" onclick="delete_chooser($('input[name=del_method]:checked').val(), '{$category.Key}', '{$category.name}')" />
			<a class="cancel" href="javascript:void(0)" onclick="$('#delete_block').fadeOut()">{$lang.cancel}</a>
		</div>
		
		<div id="replace_content" style="margin: 10px 0;" class="hide">
			{foreach from=$sections item='section'}
				<fieldset class="light">
					<legend id="legend_section_{$section.ID}" class="up" onclick="fieldset_action('section_{$section.ID}');">{$section.name}</legend>
					<div id="section_{$section.ID}" class="tree">
						{if !empty($section.Categories)}
							{include file='blocks'|cat:$smarty.const.RL_DS|cat:'category_level.tpl' categories=$section.Categories first=true}
						{else}
							<div style="margin-left: 10px;" class="blue_middle">{$lang.no_items_in_sections}</div>
						{/if}
					</div>
				</fieldset>
			{/foreach}
			
			<input type="hidden" value="" id="replace_category" />
		</div>
		
		<div id="bottom_buttons" class="hide">
			{assign var='replace' value=`$smarty.ldelim`category`$smarty.rdelim`}
			
			{if $config.trash}
				{assign var='notice_phrase' value=$lang.notice_drop_empty_category|replace:$replace:$category.name}
			{else}
				{assign var='notice_phrase' value=$lang.notice_delete_empty_category|replace:$replace:$category.name}
			{/if}
		
			<input class="simple" type="button" value="{$lang.go}" onclick="rlConfirm('{$notice_phrase}', 'replaceCategory', '{$category.Key}');" />
			<a class="cancel" href="javascript:void(0)" onclick="$('#delete_block').fadeOut()">{$lang.cancel}</a>
		</div>
	</div>
</div>

<!-- category deleting end -->