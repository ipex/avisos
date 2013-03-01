<!-- listings tpl -->

<!-- tabs -->
{if $listing_types|@count > 1}
	<div id="tabs">
		<table class="sTable tabs">
		<tr>
			{foreach from=$listing_types item='tab' key='lt_key' name='tabsF'}
			<td class="item{if $requested_type == $lt_key} active{/if}" lang="{$lt_key}" abbr="{$lt_key|replace:'_':''}">
				{if $smarty.foreach.tabsF.first}<script type="text/javascript">var active_tab = "{$lt_key|replace:'_':''}";</script>{/if}
				<table class="sTable">
				<tr>
					<td class="left"></td>
					<td class="center" valign="top"><div>{$tab.name}</div></td>
					<td class="right"></td>
				</tr>
				</table>
			</td>
			{if !$smarty.foreach.tabsF.last}<td class="divider"></td>{/if}
			{/foreach}
		</tr>
		</table>
	</div>
	
	<script type="text/javascript">//<![CDATA[
	{if $requested_type}
	active_tab = '{$requested_type}';
	{/if}
	{literal}
	
	var map_showed = true;
	var name = '';
	
	$(document).ready(function(){
		$('table.tabs td.item').click(function(){
			name = $(this).attr('abbr');
			key = $(this).attr('lang');
	
			if ( $('div#area_'+name).find('div#listings').length <= 0 )
			{
				xajax_loadRecentlyAdded(key);
			}
			
			$('table.tabs td[abbr='+active_tab+']').removeClass('active');
			$(this).addClass('active');
			
			$('#area_'+active_tab).hide();
			$('#area_'+name).show();
			
			active_tab = name;
		});
		
		if ( flynax.getHash() )
		{
			$('table.tabs td[abbr='+flynax.getHash()+']').trigger('click');
		}
	});
	
	{/literal}
	
	//]]>
	</script>
{/if}
<!-- tabs end -->

{foreach from=$listing_types item='tab' key='lt_key' name='tabsF'}
	<div class="{if $requested_type != $lt_key} hide{/if}" id="area_{$lt_key|replace:'_':''}">
		{if $requested_type == $lt_key}
			{include file='blocks'|cat:$smarty.const.RL_DS|cat:'recently.tpl'}
		{elseif $requested_type != $lt_key}
			<div class="padding">{$lang.loading}</div>
		{/if}
	</div>
{/foreach}

<!-- listings tpl end -->