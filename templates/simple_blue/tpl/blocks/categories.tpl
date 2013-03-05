<!-- categories block tpl -->

{if !empty($categories)}

	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'content_block_header.tpl' name=$lang.categories no_margin=$no_margin style='categories'}

	{rlHook name='browsePreCategories'}
	
	{math assign='pages_number' equation='ceil(count/num)' count=$categories|@count num=$listing_type.Cat_visible_number}
	<div class="categories" id="categories_{$listing_type.Key|replace:'_':''}_{$pages_number}">
		<div>
			<ul>
				<li>
				<table class="fixed">
				<tr>
				{foreach from=$categories item='cat' name='fCats'}
					<td valign="top">
						<div class="item">
							{rlHook name='tplPreCategory'}
							<a class="category" title="{if $lang[$cat.pTitle]}{$lang[$cat.pTitle]}{else}{$cat.name}{/if}" href="{$rlBase}{if $config.mod_rewrite}{$pageInfo.Path}/{$cat.Path}{if $listing_type.Cat_postfix}.html{else}/{/if}{else}?page={$pageInfo.Path}&amp;category={$cat.ID}{/if}">{$cat.name}</a>
							{if $listing_type.Cat_listing_counter}
								<span>(<b>{$cat.Count|number_format}</b>)</span>
							{/if}
							{rlHook name='tplPostCategory'}
							
							{if !empty($cat.sub_categories) && $listing_type.Cat_show_subcats}
							<div class="sub_categories">
								{if $listing_type.Cat_subcat_number}
									{section loop=$cat.sub_categories name='sub_cat' max=$listing_type.Cat_subcat_number}
										{rlHook name='tplPreSubCategory'}
										{assign var='subcat_title' value=$cat.sub_categories[sub_cat].pTitle}
										<a title="{if $lang.$subcat_title}{$lang.$subcat_title}{else}{$cat.sub_categories[sub_cat].name}{/if}" href="{$rlBase}{if $config.mod_rewrite}{$pageInfo.Path}/{$cat.sub_categories[sub_cat].Path}{if $listing_type.Cat_postfix}.html{else}/{/if}{else}?page={$pageInfo.Path}&amp;category={$cat.sub_categories[sub_cat].ID}{/if}">{$cat.sub_categories[sub_cat].name}</a>{if $smarty.section.sub_cat.last}{if $cat.sub_categories|@count > $listing_type.Cat_subcat_number}<span class="more" title="{$lang.show_other_categories}">&nbsp;&raquo;</span>{/if}{else}, {/if}
									{/section}
									
									<div class="hide other_categories">
										{section loop=$cat.sub_categories name='sub_cat' start=$listing_type.Cat_subcat_number}
											{rlHook name='tplPreSubCategory'}
											{assign var='subcat_title' value=$cat.sub_categories[sub_cat].pTitle}
											<a title="{if $lang.$subcat_title}{$lang.$subcat_title}{else}{$cat.sub_categories[sub_cat].name}{/if}" href="{$rlBase}{if $config.mod_rewrite}{$pages[$listing_type.Page_key]}/{$cat.sub_categories[sub_cat].Path}{if $listing_type.Cat_postfix}.html{else}/{/if}{else}?page={$pages[$listing_type.Page_key]}&amp;category={$cat.sub_categories[sub_cat].ID}{/if}">{$cat.sub_categories[sub_cat].name}</a>{if !$smarty.section.sub_cat.last}, {/if}
										{/section}
									</div>
								{else}
									{foreach from=$cat.sub_categories item='sub_cat' name='subCatF'}
										{rlHook name='tplPreSubCategory'}
										<a title="{if $lang[$sub_cat.pTitle]}{$lang[$sub_cat.pTitle]}{else}{$sub_cat.name}{/if}" href="{$rlBase}{if $config.mod_rewrite}{$pageInfo.Path}/{$sub_cat.Path}{if $listing_type.Cat_postfix}.html{else}/{/if}{else}?page={$pageInfo.Path}&amp;category={$sub_cat.ID}{/if}">{$sub_cat.name}</a>{if !$smarty.foreach.subCatF.last}, {/if}
									{/foreach}
								{/if}
							</div>
							{/if}
						</div>
					</td>
					
					{if $smarty.foreach.fCats.iteration%$listing_type.Cat_columns_number == 0 && ($smarty.foreach.fCats.iteration%$listing_type.Cat_visible_number != 0 || !$listing_type.Cat_visible_number) && !$smarty.foreach.fCats.last}
					</tr>
					<tr>
					{/if}
					
					{if $smarty.foreach.fCats.iteration%$listing_type.Cat_visible_number == 0 && $listing_type.Cat_visible_number && !$smarty.foreach.fCats.last}
					</tr>
					</table>
					{if $listing_type.Cat_visible_number}
					</li><li class="hide">
					{/if}
					<table class="sTable" style="table-layout: fixed;">
					<tr>
					{/if}
				{/foreach}
				{if $smarty.foreach.fCats.total%$listing_type.Cat_columns_number != 0}
					{math assign='rest' equation='(ceil(total/cols)*cols) - total' total=$smarty.foreach.fCats.total cols=$listing_type.Cat_columns_number}
					{section name='rest' loop=$rest|ceil}
						<td></td>
					{/section}			
				{/if}
				</tr>
				</table>
				</li>
			</ul>
			<div class="clear"></div>
		</div>
	</div>
	
	{if $smarty.foreach.fCats.total > $listing_type.Cat_visible_number && $listing_type.Cat_visible_number}
		{assign var='pages_number' value=$smarty.foreach.fCats.total/$listing_type.Cat_visible_number}
		<div class="slider_bar">
			<div class="arrow"><div class="prev hide" title="{$lang.show_previous_categories}"></div></div>
			<div class="navigation">
				{section name='slide_page' loop=$pages_number|ceil}
					<a title="{$lang.show_other_categories}" accesskey="{$smarty.section.slide_page.iteration}" {if $smarty.section.slide_page.first}class="active"{/if} href="javascript:void(0)"><span></span></a>
				{/section}
			</div>
			<div class="arrow"><div class="next" title="{$lang.show_next_categories}"></div></div>
		</div>
		
		{if $listing_type.Cat_scrolling}
		<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}jquery/jquery.fancybox.js"></script>
		{/if}
		
		<script type="text/javascript">
		{literal}
		$(document).ready(function(){
			$('div.categories').flCatSlider({
				scroll: {/literal}{if $listing_type.Cat_scrolling}true{else}false{/if}{literal}
			});
		});
		{/literal}
		</script>
	{/if}
	
	<script type="text/javascript">flynax.moreCategories();</script>
	
	{rlHook name='browsePostCategories'}
	
	{include file='blocks'|cat:$smarty.const.RL_DS|cat:'content_block_footer.tpl'}

{else}

	{if !$category.ID}
		<div class="info">{$lang.listing_type_no_categories}</div>
	{/if}

{/if}

<!-- categories block tpl -->