<!-- search tpl -->

<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}jquery/numeric.js"></script>
<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}jquery/jquery.ui.js"></script>
<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}jquery/datePicker/i18n/ui.datepicker-{$smarty.const.RL_LANG_CODE}.js"></script>

<!-- tabs -->
<div class="tabs">
	<ul>
		{if $search_forms|@count > 0}
			{foreach from=$search_forms item='search_form' key='sf_key' name='sformsF'}
				{assign var='tab_phrase' value='listing_types+name+'|cat:$listing_types[$sf_key].Key}
				<li {if $smarty.foreach.sformsF.first}class="first {if $smarty.foreach.sformsF.first && !$keyword_search}active{/if}"{/if} id="tab_{$sf_key|replace:'_':''}">
					<span class="center">{$lang[$tab_phrase]}</span>
				</li>
			{/foreach}
		{/if}
		{assign var='ks_phrase' value='blocks+name+keyword_search'}
		<li class="{if $keyword_search || !$search_forms}active{/if}{if !$search_forms} first{/if}" id="tab_keyword">
			<span class="center">{$lang.$ks_phrase}</span>
		</li>
	</ul>
</div>
<div class="clear"></div>
<!-- tabs end -->

{foreach from=$search_forms item='search_form' key='sf_key' name='sformsF'}
	{assign var='spage_key' value=$listing_types[$sf_key].Page_key}
	
	<div id="area_{$sf_key|replace:'_':''}" class="tab_area{if !$smarty.foreach.sformsF.first || $keyword_search} hide{/if}">
		<div class="highlight">
			<form method="{$listing_types[$sf_key].Submit_method}" action="{$rlBase}{if $config.mod_rewrite}{$pages.$spage_key}/{$search_results_url}.html{else}?page={$pages.$spage_key}&amp;{$search_results_url}{/if}">
				<input type="hidden" name="action" value="search" />
				{assign var='post_form_key' value=$sf_key|cat:'_quick'}
				<input type="hidden" name="post_form_key" value="{$post_form_key}" />
				
				{foreach from=$search_form item='group' name='qsearchF'}
					{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fields_search.tpl' fields=$group.Fields}
				{/foreach}
				
				<table class="search">
				<tr>
					{if $config.search_fields_position == 2}<td class="field button"></td>{/if}
					<td class="value button">
						<input type="submit" name="search" value="{$lang.search}" />
						<label><input style="margin-{$text_dir}: 20px;" type="checkbox" name="f[with_photo]" value="true" /> {$lang.with_photos_only}</label>
					</td>
				</tr>
				{if $listing_types[$sf_key].Advanced_search && $listing_types[$sf_key].Advanced_search_availability}
				<tr>
					{if $config.search_fields_position == 2}<td class="field"></td>{/if}
					<td class="lalign"><a class="default_11" title="{$lang.advanced_search}" href="{$rlBase}{if $config.mod_rewrite}{$pages.$spage_key}/{$advanced_search_url}.html{else}?page={$pages.$spage_key}&amp;{$advanced_search_url}{/if}">{$lang.advanced_search}</a></td>
				</tr>
				{/if}
				</table>
				
			</form>
		</div>
	</div>
{/foreach}

<div id="area_keyword" class="tab_area{if !$keyword_search && $search_forms|@count > 0} hide{/if}">
	<div class="highlight">
		<form method="post" action="{$rlBase}{if $config.mod_rewrite}{$pages.search}.html{else}?page={$pages.search}{/if}">
			<input type="hidden" name="form" value="keyword_search" />
		
			<table style="width: 350px;">
			<tr>
				<td><div class="relative"><input type="text" maxlength="255" name="f[keyword_search]" style="width: 94%;" {if $smarty.post.f.keyword_search}value="{$smarty.post.f.keyword_search}"{/if}/></div></td>
				<td style="width: 50px;"><input class="low" type="submit" name="search" value="{$lang.search}" /></td>
			</tr>
			</table>
			<div class="keyword_search_opt" style="display: block;">
				<ul>
					{assign var='tmp' value=3}
					{section name='keyword_opts' loop=$tmp max=3}
						<li><label><input {if $fVal.keyword_search_type || $keyword_mode}{if $smarty.section.keyword_opts.iteration == $fVal.keyword_search_type || $keyword_mode == $smarty.section.keyword_opts.iteration}checked="checked"{/if}{else}{if $smarty.section.keyword_opts.iteration == 2}checked="checked"{/if}{/if} value="{$smarty.section.keyword_opts.iteration}" type="radio" name="f[keyword_search_type]" /> {assign var='ph' value='keyword_search_opt'|cat:$smarty.section.keyword_opts.iteration}{$lang.$ph}</label></li>
					{/section}
				</ul>
			</div>
		</form>
	</div>

	<div class="listings_area">
	{if !empty($listings)}
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'grid_navbar.tpl'}
						
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'grid.tpl' hl=trye}
		<script type="text/javascript">flynax.highlightSRGrid($('#area_keyword input[name="f\[keyword_search\]"]').val());</script>
		
		<!-- paging block -->
		{if $config.mod_rewrite}
			{paging calc=$pInfo.calc total=$listings|@count current=$pInfo.current per_page=$config.listings_per_page url=$category.Path var='listing'}
		{else}
			{paging calc=$pInfo.calc total=$listings|@count current=$pInfo.current per_page=$config.listings_per_page url=$category.ID var='category'}
		{/if}
		<!-- paging block end -->
	{else}
		{if $keyword_search}
			{if $config.mod_rewrite}
				{assign var='href' value=$rlBase|cat:$pages.add_listing|cat:'.html'}
			{else}
				{assign var='href' value=$rlBase|cat:'index.php?page='|cat:$pages.add_listing}
			{/if}
			
			{assign var='link' value='<a href="'|cat:$href|cat:'">$1</a>'}
			<div class="info">{$lang.no_listings_found|regex_replace:'/\[(.+)\]/':$link}</div>
		{/if}
	{/if}
	</div>
</div>
<script type="text/javascript">
	{literal}
		$(document).ready(function(){
			flynax.multiCatsHandler();
		});
	{/literal}
</script>

<!-- search tpl end -->
