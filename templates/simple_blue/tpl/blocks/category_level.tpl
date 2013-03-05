<!-- category level -->

{if $category.Type}
	{assign var='cat_type' value=$category.Type}
{else}
	{assign var='cat_type' value=$section.Type}
{/if}

{assign var='replace' value=`$smarty.ldelim`category`$smarty.rdelim`}

{*if $categories*}
	<ul {if $first}class="first"{/if} {if $category.ID}id="tree_area_{$category.ID}"{/if}>
	{foreach from=$categories item='cat' name='catF'}
		{if !empty($cat.Sub_cat) || ($cat.Add == '1' && $listing_types[$cat_type].Cat_custom_adding)}
			{assign var='sub_leval' value=true}
		{else}
			{assign var='sub_leval' value=false}
		{/if}
			
		<li id="tree_cat_{$cat.ID}" {if $cat.Lock}class="locked"{/if}>
			<img {if !$sub_leval}class="no_child"{/if} src="{$rlTplBase}img/blank.gif" alt="" />
			<a title="{$lang.add_listing_to|replace:$replace:$cat.name}" {if $cat.Lock}class="cursor_default"{/if} href="{if $cat.Lock}javascript:void(0);{else}{$rlBase}{if $config.mod_rewrite}{$pageInfo.Path}/{if $cat.Tmp}tmp-category{else}{$cat.Path}{/if}/{$steps.plan.path}.html{if $cat.Tmp}?tmp_id={$cat.ID}{/if}{else}?page={$pageInfo.Path}&amp;step={$steps.plan.path}&amp;{if $cat.Tmp}tmp_id{else}id{/if}={$cat.ID}{/if}{/if}">{$cat.name}</a>
			<span class="tree_loader"></span>
		</li>
	{/foreach}
		{if $category && $listing_types[$cat_type].Cat_custom_adding && $category.Add == 1}
		<li class="tmp dark">
			<img class="no_child" src="{$rlTplBase}img/blank.gif" alt="" />
			{assign var='tmp_link' value='<a href="javascript:void(0);" class="add">$1</a>'}
			{assign var='cat_name' value='<b>'|cat:$category.name|cat:'</b>'}
			{assign var='replace' value=`$smarty.ldelim`category`$smarty.rdelim`}
			<span class="tmp_info">{$lang.tmp_category_info|regex_replace:'/\[(.*)\]/':$tmp_link|replace:$replace:$cat_name}</span>
			<span class="tmp_input hide"><span><input type="text" /><input title="{$lang.add}" onclick="xajax_addTmpCategory($(this).prev().val(), '{$category.ID}');$(this).parent().parent().next().fadeIn('slow');" class="accept" type="button" /></span><img class="remove" title="{$lang.cancel}" src="{$rlTplBase}img/blank.gif" alt="" /></span>
			<span class="tree_loader"></span>
		</li>
		{/if}
	</ul>
{*/if*}

<!-- category level end -->