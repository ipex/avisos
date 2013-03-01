<!-- categories block tpl -->

{if !empty($categories)}

	{rlHook name='browsePreCategories'}
	
	{assign var='cat_count' value=$categories|@count}
	{assign var='cat_count' value=$cat_count/2}
	{assign var='cat_count' value=$cat_count|ceil}
	
	<div class="categories">
		<table class="sTable">
		<tr>
			<td valign="top">
				<ul>
				{foreach from=$categories item='cat' name='fCats'}
			
				<li>
					{rlHook name='tplPreCategory'}
					<a class="category" title="{$cat.name}" href="{$rlBase}{if $config.mod_rewrite}{$pageInfo.Path}/{$cat.Path}{if $config.display_cat_html}.html{else}/{/if}{else}?page={$pageInfo.Path}&amp;category={$cat.ID}{/if}">{$cat.name}</a>
					{if $config.listings_counter}
						<span class="counter">({$cat.Count})</span>
					{/if}
				</li>
			
		{if $smarty.foreach.fCats.iteration == $cat_count}
		</ul></td><td valign="top"><ul>
		{/if}
		{/foreach}
			</ul>
			</td>
		</tr>
		</table>
	</div>
	
	{rlHook name='browsePostCategories'}
	
{else}

	{if !$category.ID}
		<div class="padding">{$lang.listing_type_no_categories}</div>
	{/if}

{/if}

<!-- categories block tpl -->