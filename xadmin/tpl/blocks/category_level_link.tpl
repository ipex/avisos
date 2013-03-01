<!-- category level link -->

{if $category.Type}
	{assign var='cat_type' value=$category.Type}
{else}
	{assign var='cat_type' value=$section.Type}
{/if}

{if $categories}
	<ul {if $first}class="first"{/if} id="tree_area_{if $category.ID}{$category.ID}{else}{$section.ID}{/if}">
	{foreach from=$categories item='cat' name='catF'}
		{if !empty($cat.Sub_cat) || ($cat.Add == '1' && $listing_types[$cat_type].Cat_custom_adding)}
			{assign var='sub_leval' value=true}
		{else}
			{assign var='sub_leval' value=false}
		{/if}
			
		<li id="tree_cat_{$cat.ID}" {if $cat.Lock}class="locked"{/if}>
			<img {if !$sub_leval}class="no_child"{/if} src="{$rlTplBase}img/blank.gif" alt="" />
			<a title="{$lang.add_listing_to} {$cat.name}" href="{$rlBase}index.php?controller={$smarty.get.controller}&amp;action=add&amp;category={$cat.ID}">{$cat.name}</a>
			<span class="tree_loader"></span>
		</li>
	{/foreach}
	</ul>
{/if}

<!-- category level link end -->