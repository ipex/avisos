<!-- advanced search form -->

<div class="highlight">
	<form class="search_form" method="{$listing_type.Submit_method}" action="{$rlBase}{if $config.mod_rewrite}{$pageInfo.Path}/{$advanced_search_url}/{$search_results_url}.html{else}?page={$pageInfo.Path}&amp;{$search_results_url}&amp;{$advanced_search_url}{/if}">
		<input type="hidden" name="action" value="search" />
		{assign var='post_form_key' value=$listing_type.Key|cat:'_advanced'}
		<input type="hidden" name="post_form_key" value="{$post_form_key}" />
		
		{foreach from=$search_form item='group'}
			{if $group.Group_ID}
				{if $group.Fields && $group.Display}
					{assign var='hide' value=false}
				{else}
					{assign var='hide' value=true}
				{/if}
				
				{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id=$group.ID name=$lang[$group.pName]}
				{if $group.Fields}
					{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fields_search.tpl' fields=$group.Fields advanced=true}
				{else}
					<div class="info">{$lang.no_items_in_group}</div>
				{/if}
				{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}
			{else}
				{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fields_search.tpl' fields=$group.Fields advanced=true}
			{/if}
		{/foreach}
		
		<!-- sorting -->
		<table class="search" style="margin-top: 10px;">
		<tr>
			<td class="field">{$lang.sort_listings_by}</td>
			<td class="value">
				<select name="f[sort_by]">
					<option value="0">{$lang.select}</option>
					{foreach from=$fields_list item='field'}
						{if $field.Type != 'checkbox'}
							<option value="{$field.Key}" {if $smarty.request.f.sort_by == $field.Key}selected="selected"{/if}>{$field.name}</option>
						{/if}
					{/foreach}
				</select>
				
				<select name="f[sort_type]">
					<option value="asc">{$lang.ascending}</option>
					<option value="desc" {if $smarty.request.f.sort_type == 'desc'}selected="selected"{/if}>{$lang.descending}</option>
				</select>
			</td>
		</tr>
		<tr>
			<td class="field"></td>
			<td class="value">
				<div style="padding-bottom: 5px;"><input type="submit" name="search" value="{$lang.search}" /> <label><input {if $smarty.post.with_photo}checked="checked"{/if} type="checkbox" name="f[with_photo]" value="true" /> {$lang.with_photos_only}</label></div>
				<a title="{$lang.quick_search}" href="{$rlBase}{if $config.mod_rewrite}{$pageInfo.Path}.html{else}?page={$pageInfo.Path}{/if}" class="default_11">{$lang.quick_search}</a>
			</td>
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
	flynax.multiCatsHandler();
	$("input.numeric").numeric();
});

{/literal}
</script>

<!-- advanced search form end -->
