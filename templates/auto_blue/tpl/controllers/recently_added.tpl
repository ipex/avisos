<!-- listings tpl -->

<!-- tabs -->
{if $listing_types|@count > 1}
	<div class="tabs">
		<div class="left"></div>
		<ul>
			{foreach from=$listing_types item='tab' key='lt_key' name='tabsF'}
				<li class="{if $smarty.foreach.tabsF.first}first{/if}{if $requested_type == $lt_key} active{/if}" lang="{$lt_key}" id="tab_{$lt_key|replace:'_':''}">
					<span class="left">&nbsp;</span>
					<span class="center"><span>{$tab.name}</span></span>
					<span class="right">&nbsp;</span>
				</li>
			{/foreach}
		</ul>
		<div class="right"></div>
	</div>
	<div class="clear"></div>
	
	<script type="text/javascript">
	{literal}
	
	$(document).ready(function(){
		$('div.tabs ul li:not(.active)').click(function(){
			var key = $(this).attr('lang');
			
			if ( $('div#area_'+key).find('div#listings').length <= 0 )
			{
				xajax_loadRecentlyAdded(key);
			}
		});
		
		if ( flynax.getHash() )
		{
			$('div.tabs ul li#tab_'+flynax.getHash().replace(/_/g, '')).trigger('click');
		}
	});
	
	{/literal}
	</script>
{/if}
<!-- tabs end -->

{foreach from=$listing_types item='tab' key='lt_key' name='tabsF'}
	<div class="tab_area{if $requested_type != $lt_key} hide{/if}" id="area_{$lt_key|replace:'_':''}">
		{if $requested_type == $lt_key}
			{include file='blocks'|cat:$smarty.const.RL_DS|cat:'recently.tpl'}
		{elseif $requested_type != $lt_key}
			<span class="info">{$lang.loading}</span>
		{/if}
	</div>
{/foreach}

<!-- listings tpl end -->