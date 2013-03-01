<!-- search form -->

	<form id="add_listing" method="post" action="{$rlBase}{if $config.mod_rewrite}{$pageInfo.Path}.html{else}?page={$pageInfo.Path}{/if}">
		<input type="hidden" name="form" value="{$key}" />
	
		{assign var='hide' value=false}
		
		{foreach from=$form item='group' name='groupF'}
			{if $group.Group_ID}
				{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id=$group.ID name=$group.name style='fg' first=$smarty.foreach.groupF.first}
				
				<div class="padding">
					{if $group.Fields}
						{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fields_search.tpl' fields=$group.Fields}
					{else}
						{$lang.no_items_in_group}
					{/if}
				</div>
				
				{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}
			{else}
				<div class="padding">{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fields_search.tpl' fields=$group.Fields}</div>
			{/if}
		{/foreach}
		
		<!-- sorting -->
		<div style="margin: 10px 10px 0 10px;">
			
			<div class="field">
				{$lang.sort_listings_by}
			</div>
	
			<select name="f[sort_field]">
				<option value="0">{$lang.select}</option>
				{foreach from=$fields_list item='field'}
					{if $field.Type != 'checkbox'}
						<option value="{$field.Key}" {if $smarty.post.sort_field == $field.Key}selected{/if}>{$field.name}</option>
					{/if}
				{/foreach}
			</select>
			
			<select name="f[sort_type]">
				<option value="asc">{$lang.ascending}</option>
				<option value="desc" {if $smarty.post.sort_type == 'desc'}selected{/if}>{$lang.descending}</option>
			</select>
	
			<div style="padding: 10px 0 5px 0;">
				<input class="button" type="submit" name="search" value="{$lang.search}" /> <input {if $smarty.post.with_photo}checked{/if} id="with_photos" type="checkbox" name="f[with_photo]" value="true" /> <label for="with_photos">{$lang.with_photos_only}</label>
			</div>
		</div>
		<!-- sorting end -->
	</form>

	{assign var='phrase' value=$alternative}
	<div class="padding">
		<a title="{$lang.$phrase}" href="{$rlBase}{if $config.mod_rewrite}{$pageInfo.Path}.html?{$alternative}{else}?page={$pageInfo.Path}&amp;{$alternative}{/if}" class="orange">[{$lang.$phrase}]</a>
	</div>

<!-- search form end -->