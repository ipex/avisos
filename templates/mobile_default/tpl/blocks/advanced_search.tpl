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
		<div class="padding" style="padding-top: 15px;">
			<div class="field">
				{$lang.sort_listings_by}
			</div>
	
			<select name="f[sort_by]">
				<option value="0">{$lang.select}</option>
				{foreach from=$search_form item='item'}
					{assign var='field' value=$item.Fields.0}
					{if $field.Type != 'checkbox'}
						<option value="{$field.Key}" {if $smarty.request.f.sort_by == $field.Key}selected="selected"{/if}>{$lang[$field.pName]}</option>
					{/if}
				{/foreach}
			</select>
			
			<select name="f[sort_type]">
				<option value="asc">{$lang.ascending}</option>
				<option value="desc" {if $smarty.request.f.sort_type == 'desc'}selected="selected"{/if}>{$lang.descending}</option>
			</select>
		</div>
		
		<div class="padding" style="padding-top: 10px;">
			<input class="button" type="submit" name="search" value="{$lang.search}" />
			<label><input style="margin-{$text_dir}: 20px;" type="checkbox" name="f[with_photo]" value="true" /> {$lang.with_photos_only}</label>
			
			{if $listing_types[$sf_key].Advanced_search}
				<div style="padding-top: 8px;"><a class="default_11" title="{$lang.quick_search}" href="{$rlBase}{if $config.mod_rewrite}{$pageInfo.Path}.html{else}?page={$pageInfo.Path}{/if}">{$lang.quick_search}</a></div>
			{/if}
		</div>
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
