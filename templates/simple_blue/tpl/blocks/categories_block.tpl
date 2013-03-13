<!-- categories block -->

{assign var='types' value=','|explode:$types}
<!--
{if !$block.Tpl}
	{if $types|@count > 1}
		<div class="caption" style="padding-bottom: 0;">
			{$lang.categories}
		</div>
	{/if}
{else}
	<div style="padding-top: 5px"></div>
{/if}
-->
{foreach from=$types item='type'}
	{if $types|@count > 1}
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' name=$listing_types.$type.name id='addcatblock'|cat:$listing_types.$type.Key class='categories_padding'}
	{/if}
	
	{assign var='listing_type' value=$listing_types.$type}
	
	{if $categories.$type}
		{math assign='pages_number' equation='ceil(count/num)' count=$categories.$type|@count num=$listing_type.Ablock_visible_number}
		{*if $pageInfo.Key == 'home' && $block.Key == 'ltcb_listings'}
			{assign var='pages_number' value=$pages_number+1}
		{/if*}
		<div class="categories" id="categories_{$type|replace:'_':''}_{$pages_number}">
			<div>
				<ul>
					{*if $block.Key == 'ltcb_listings'}
						<li>{include file='blocks'|cat:$smarty.const.RL_DS|cat:'map.tpl'}</li>
						<li class="hide">
					{else*}
						<li>
					{*/if*}
					
					<div class="categoty-column categories_{$type|replace:'_':''}">
						
					{foreach from=$categories.$type item='cat' name='fCats'}
						{rlHook name='tplBetweenCategories'}
						
						<div class="item">
							<p>{$rlHook.name}</p>
							<div class="parent-cateory">
								{rlHook name='tplPreCategory'}
								<a class="category" title="{if $lang[$cat.pTitle]}{$lang[$cat.pTitle]}{else}{$lang[$cat.pName]}{/if}" href="{$rlBase}{if $config.mod_rewrite}{$pages[$listing_type.Page_key]}/{$cat.Path}{if $listing_type.Cat_postfix}.html{else}/{/if}{else}?page={$pages[$listing_type.Page_key]}&amp;category={$cat.ID}{/if}">{$lang[$cat.pName]}</a>
								{if $listing_type.Cat_listing_counter}
									<span>({$cat.Count|number_format})</span>
								{/if}
								{rlHook name='tplPostCategory'}
							</div>
							
							{if !empty($cat.sub_categories) && $listing_type.Ablock_show_subcats}
							<div class="sub_categories">
								{if $listing_type.Ablock_subcat_number}
									{section loop=$cat.sub_categories name='sub_cat' max=$listing_type.Ablock_subcat_number}
										{rlHook name='tplPreSubCategory'}
										{assign var='subcat_title' value=$cat.sub_categories[sub_cat].pTitle}
										<a title="{if $lang.$subcat_title}{$lang.$subcat_title}{else}{$cat.sub_categories[sub_cat].name}{/if}" href="{$rlBase}{if $config.mod_rewrite}{$pages[$listing_type.Page_key]}/{$cat.sub_categories[sub_cat].Path}{if $listing_type.Cat_postfix}.html{else}/{/if}{else}?page={$pages[$listing_type.Page_key]}&amp;category={$cat.sub_categories[sub_cat].ID}{/if}">{$cat.sub_categories[sub_cat].name}</a>{if $smarty.section.sub_cat.last}{if $cat.sub_categories|@count > $listing_type.Ablock_subcat_number}<span class="more" title="{$lang.show_other_categories}">&nbsp;&raquo;</span>{/if}{else}, {/if}
									{/section}
									
									<div class="hide other_categories">
										{section loop=$cat.sub_categories name='sub_cat' start=$listing_type.Ablock_subcat_number}
											{rlHook name='tplPreSubCategory'}
											{assign var='subcat_title' value=$cat.sub_categories[sub_cat].pTitle}
											<a title="{if $lang.$subcat_title}{$lang.$subcat_title}{else}{$cat.sub_categories[sub_cat].name}{/if}" href="{$rlBase}{if $config.mod_rewrite}{$pages[$listing_type.Page_key]}/{$cat.sub_categories[sub_cat].Path}{if $listing_type.Cat_postfix}.html{else}/{/if}{else}?page={$pages[$listing_type.Page_key]}&amp;category={$cat.sub_categories[sub_cat].ID}{/if}">{$cat.sub_categories[sub_cat].name}</a>{if !$smarty.section.sub_cat.last}, {/if}
										{/section}
									</div>
								{else}
									{foreach from=$cat.sub_categories item='sub_cat' name='subCatF'}
										{rlHook name='tplPreSubCategory'}
										<a title="{if $lang[$sub_cat.pTitle]}{$lang[$sub_cat.pTitle]}{else}{$sub_cat.name}{/if}" href="{$rlBase}{if $config.mod_rewrite}{$pages[$listing_type.Page_key]}/{$sub_cat.Path}{if $listing_type.Cat_postfix}.html{else}/{/if}{else}?page={$pages[$listing_type.Page_key]}&amp;category={$sub_cat.ID}{/if}">{$sub_cat.name}</a>{if !$smarty.foreach.subCatF.last}, {/if}
									{/foreach}
								{/if}
							</div>
							{/if}
						</div>
						
						{*if $smarty.foreach.fCats.iteration%$listing_type.Ablock_columns_number == 0 && ($smarty.foreach.fCats.iteration%$listing_type.Ablock_visible_number != 0 || !$listing_type.Ablock_visible_number) && !$smarty.foreach.fCats.last}
						</div>
						<div class="categoty-column">
						{/if*}
						
						{if $smarty.foreach.fCats.iteration%$listing_type.Ablock_visible_number == 0 && $listing_type.Ablock_visible_number && !$smarty.foreach.fCats.last}
							</div>
							{if $listing_type.Ablock_visible_number}
								</li><li class="hide">
							{/if}
							<div class="categoty-column">
						{/if}
					{/foreach}
					
					{if $smarty.foreach.fCats.total%$listing_type.Ablock_columns_number != 0}
						{math assign='rest' equation='(ceil(total/cols)*cols) - total' total=$smarty.foreach.fCats.total cols=$listing_type.Ablock_columns_number}
						{section name='rest' loop=$rest|ceil}
							<div></div>
						{/section}			
					{/if}
					</div>
					</li>
				</ul>
				<div class="clear"></div>
			</div>
		</div>
		
		{if $pages_number > 1}
			<div class="slider_bar{if $types|@count > 1} slider_bar_light{/if}">
				<div class="prev hide" title="{$lang.show_previous_categories}"></div>
				<div class="navigation">
					{section name='slide_page' loop=$pages_number|ceil}
						<a title="{if $block.Key == 'ltcb_listings'}{if $smarty.section.slide_page.first}{$lang.show_map}{else}{$lang.show_states}{/if}{else}{$lang.show_other_categories}{/if}" accesskey="{$smarty.section.slide_page.iteration}" {if $smarty.section.slide_page.first}class="active"{/if} href="javascript:void(0)"><span {if $block.Key == 'ltcb_listings' && $smarty.section.slide_page.first}class="map"{/if}>&nbsp;</span></a>
					{/section}
				</div>
				<div class="next" title="{$lang.show_next_categories}"></div>
			</div>
			
			{if $listing_type.Ablock_scrolling}
			<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}jquery/jquery.fancybox.js"></script>
			{/if}
			
			<script type="text/javascript">
			{literal}
			$(document).ready(function(){
				var catSlider = false;
				
				if ( !catSlider )
				{
					$('div.categories').flCatSlider({
						scroll: {/literal}{if $listing_type.Ablock_scrolling}true{else}false{/if}{literal}
					});
					catSlider = true;
				}
			});
			{/literal}
			</script>
		{/if}
		
		<script type="text/javascript">
			flynax.moreCategories();

			{literal}		
			$(document).ready(function(){
				var columns = {/literal}{if $listing_type.Ablock_columns_number}{$listing_type.Ablock_columns_number}{else}3{/if}{literal};
				var key = '{/literal}{$type|replace:'_':''}{literal}';
				var width = $('div.categories_'+key).width();
				var item_width = Math.floor((width - 40) / columns);
				
				$('div.categories_'+key+' div.item').width(item_width);
				$('div.categories_'+key).masonry({
					itemSelector : '.item',
					isRTL: rlLangDir == 'rtl' ? true : false,
					fraudWidth : 20, //Flynax Setting
					columnWidth : function( containerWidth ) {
						return containerWidth / columns;
					}
				});
			});
			{/literal}
		</script>
		
		<script type="text/javascript" src="{$rlTplBase}js/jquery.masonry.js"></script>
	
	{else}
		<div style="padding: 10px 12px;">{$lang.listing_type_no_categories}</div>
	{/if}
	
	{if $types|@count > 1}
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}
	{/if}
	
{/foreach}

<!-- categories block end -->