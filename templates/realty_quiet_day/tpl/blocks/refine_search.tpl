<!-- refine search block tpl -->

<div class="refine">
	<form method="{$listing_type.Submit_method}" action="{$rlBase}{if $config.mod_rewrite}{$pageInfo.Path}{if $advanced_search}/{$advanced_search_url}{/if}/{$search_results_url}.html{else}?page={$pageInfo.Path}&amp;{$search_results_url}{if $advanced_search}&amp;{$advanced_search_url}{/if}{/if}">
		<input type="hidden" name="action" value="search" />
		
		{if $listing_type.Advanced_search}
			{assign var='post_form_key' value=$listing_type.Key|cat:'_advanced'}
		{else}
			{assign var='post_form_key' value=$listing_type.Key|cat:'_quick'}
		{/if}
		<input type="hidden" name="post_form_key" value="{$post_form_key}" />
		
		{foreach from=$refine_search_form item='group'}
			{if $group.Group_ID}
				{if $group.Fields && $group.Display}
					{assign var='hide' value=false}
				{else}
					{assign var='hide' value=true}
				{/if}
				
				{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id=$group.Group_ID name=$lang[$group.pName]}
					{if $group.Fields}
						{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fields_refine.tpl' fields=$group.Fields}
					{else}
						{$lang.no_items_in_group}
					{/if}
				{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}
				
			{else}
				{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fields_refine.tpl' fields=$group.Fields}
			{/if}
		{/foreach}
		
		<!-- sorting -->
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='sorting' name=$lang.sort_listings_by}
		<div style="padding: 4px 0 12px 0;">
			<select name="f[sort_by]" class="w80">
				<option value="0">{$lang.select}</option>
				{foreach from=$fields_list item='field'}
					{if $field.Type != 'checkbox'}
						<option value="{$field.Key}" {if $smarty.request.f.sort_by == $field.Key}selected="selected"{/if}>{$field.name}</option>
					{/if}
				{/foreach}
			</select>
			
			<select name="f[sort_type]" style="width: 90px;">
				<option value="asc">{$lang.ascending}</option>
				<option value="desc" {if $smarty.request.f.sort_type == 'desc'}selected="selected"{/if}>{$lang.descending}</option>
			</select>
		</div>
		
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}
		
		<table>
		<tr>
			<td style="padding-{$text_dir_rev}: 10px;"><input class="search_field_item button" type="submit" name="search" value="{$lang.search}" /></td>
			<td><label><input {if $smarty.post.with_photo}checked="checked"{/if} type="checkbox" name="f[with_photo]" value="true" /> {$lang.with_photos_only}</label></td>
		</tr>
		</table>
		<!-- sorting end -->
	</form>
</div>

<script type="text/javascript">
var phrase_from = "{$lang.from}";
var phrase_to = "{$lang.to}";
{literal}

$(document).ready(function(){
	flynax.fromTo(phrase_from, phrase_to);
	$("input.numeric").numeric();
});

{/literal}
</script>

<!-- refine search block tpl end -->