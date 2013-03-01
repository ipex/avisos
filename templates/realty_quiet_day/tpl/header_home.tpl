<!-- header home tpl -->

<div id="top_bg_home"><div></div></div>

<div id="main_container_home">
	<div id="header_home">
		<div>
			<!-- header -->
			
			<div id="logo">
				<a href="{$rlBase}" title="{$config.site_name}">
					<img alt="" src="{$rlTplBase}img/{if $smarty.const.RL_LANG_DIR == 'rtl'}rtl/{/if}logo.png" />
				</a>
			</div>
		
			<!-- main menu -->
			<div id="main_menu_container">
				<div>{include file='menus'|cat:$smarty.const.RL_DS|cat:'main_menu.tpl'}</div>
			</div>
			<!-- main menu end -->
			
			<!-- print search form -->
			<div class="search">
				<div>
					{if $search_forms}
						<div class="content">
							{if $search_forms|@count > 1}
								<!-- tabs -->
								{if $config.search_fields_position == 2}
									<table class="search">
									<tr class="header">
										<td class="field">{$lang.search}</td>
										<td class="value">
								{/if}
								
								<ul class="search_tabs">
								{foreach from=$search_forms item='search_form' key='sf_key' name='stabsF'}<li class="{if $smarty.foreach.stabsF.first}first active{elseif $smarty.foreach.stabsF.last}last{/if}">{$search_form.name}</li>{/foreach}
								</ul>
								
								{if $config.search_fields_position == 2}
										</td>
									</tr>
									</table>
								{/if}
								<!-- tabs end -->
							{else}
								<div class="search_caption">{$lang.quick_search}</div>
							{/if}
							
							{foreach from=$search_forms item='search_form' key='sf_key' name='sformsF'}	
								{assign var='spage_key' value=$listing_types[$search_form.listing_type].Page_key}
								{assign var='post_form_key' value=$sf_key}
								{if $search_forms|@count > 1}
									<div class="search_tab_area{if !$smarty.foreach.sformsF.first} hide{/if}">
								{/if}
								<form method="{$listing_types[$search_form.listing_type].Submit_method}" action="{$rlBase}{if $config.mod_rewrite}{$pages.$spage_key}/{$search_results_url}.html{else}?page={$pages.$spage_key}&amp;{$search_results_url}{/if}">
									<input type="hidden" name="action" value="search" />
									<input type="hidden" name="post_form_key" value="{$sf_key}" />
									
									{if $search_form.arrange_field}
										<input {if !$smarty.foreach.sformsF.first}disabled="disabled"{/if} class="search_tab_hidden" type="hidden" name="f[{$search_form.arrange_field}]" value="{$search_form.arrange_value}" />
									{/if}
									
									{assign var='items_count' value=$search_form.data|@count}
									{foreach from=$search_form.data item='group' name='qsearchF'}
										{if $smarty.foreach.qsearchF.iteration <= 5}
											{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fields_search.tpl' fields=$group.Fields}
										{/if}
									{/foreach}
									
									<table class="search">
									<tr>
										{if $config.search_fields_position == 2}<td class="field"></td>{/if}
										<td class="value" colspan="2"><label><input type="checkbox" name="f[with_photo]" value="true" /> {$lang.with_photos_only}</label></td>
									</tr>
									</table>
									
									<table class="search" style="margin-top: 12px;">
									<tr>
										{if $config.search_fields_position == 2}<td class="field"></td>{/if}
										<td class="lalign"><input type="submit" name="search" value="{$lang.search}" /></td>
										{if $listing_types[$search_form.listing_type].Advanced_search}
											<td><a class="white_11" title="{$lang.advanced_search}" href="{$rlBase}{if $config.mod_rewrite}{$pages.$spage_key}/{$advanced_search_url}.html{else}?page={$pages.$spage_key}&amp;{$advanced_search_url}{/if}">{$lang.advanced_search}</a></td>
										{/if}
									</tr>
									</table>
									
								</form>
								{if $search_forms|@count > 1}
									</div>
								{/if}
							{/foreach}	
						</div>
						
						{if $search_forms|@count > 1}
							<script type="text/javascript">
								flynax.searchTabs();
								flynax.multiCatsHandler();
							</script>
						{/if}
					{/if}
				</div>
			</div>
			<!-- print search form -->
			
			{include file='blocks'|cat:$smarty.const.RL_DS|cat:'user_navbar.tpl'}
			
			<!-- header end -->
		</div>
	</div>
	
<!-- header home tpl -->