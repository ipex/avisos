<!-- settings tpl -->

<form method="post" action="{$rlBase}index.php?controller={$cInfo.Controller}">
	<input name="group_id" value="{$smarty.get.group}" type="hidden" />
	<table class="sTable" id="settings_anchor">
	<tr>
		<td style="width: 108px; border-right: 1px #667835 solid;" align="right" valign="top">
			{foreach from=$configGroups item='group' name='fGroups'}
				{if empty($group.Plugin_status) || $group.Plugin_status == 'active'}
					<div id="ltab_{$group.ID}" title="{$group.name}" class="ltab {if $smarty.foreach.fGroups.first}tabs_active{else}tabs_inactive{/if}" {if $smarty.foreach.fGroups.first}style="margin: 0;"{/if}><div>{$group.name}</div></div>
					{if $smarty.get.group == $group.ID || $smarty.foreach.fGroups.first}
						<script type="text/javascript">
						var sKey = '{$group.ID}';
						</script>
					{/if}
				{/if}
			{/foreach}
		</td>
		<td valign="top" style="width: 1px; border-right: 1px #667835 solid;"></td>
		<td valign="top">
			{foreach from=$configGroups item='group' name='fGroups'}				
				<div id="larea_{$group.ID}" class="larea hide">
					<div class="ltab_block_header clear"><div>{$lang.configs_caption|replace:'[group]':$group.name}</div></div>
					{assign var='store' value=$group.ID}
					{if !empty($configs.$store)}
						<div style="padding: {if $configs.$store.0.Type != 'divider'}10px{else}0{/if} 10px 0;">
							<input type="hidden" name="a_config" value="update"/>
							<table class="form">
							{foreach from=$configs.$store item='configItem' name='configF'}
							<tr class="{if $smarty.foreach.configF.iteration%2 != 0 && $configItem.Type != 'divider'}highlight{/if}">
								{if $configItem.Type == 'divider'}
									<td class="divider_line" colspan="2">
										<div class="inner">{$configItem.name}</div>
									</td>
								{else}
									<td class="name" style="width: 210px;">{$configItem.name}</td>
									<td class="field">
										<div class="inner_margin">
											{if $configItem.Data_type == 'int'}<input class="text" type="hidden" name="config[{$configItem.Key}][d_type]" value="{$configItem.Data_type}" />{/if}
											<input class="text" type="hidden" name="config[{$configItem.Key}][value]" value="{$configItem.Default}" />
											
											{if $configItem.Type == 'text'}
												<input class="text {if $configItem.Data_type == 'int'}numeric{/if}" type="text" name="config[{$configItem.Key}][value]" value="{$configItem.Default}" />
											{elseif $configItem.Type == 'textarea'}
												<textarea cols="5" rows="5" class="{if $configItem.Data_type == 'int'}numeric{/if}" name="config[{$configItem.Key}][value]">{$configItem.Default|replace:'\r\n':$smarty.const.PHP_EOL}</textarea>
											{elseif $configItem.Type == 'bool'}
												<input {if $configItem.Default == 1}checked="checked"{/if} type="radio" id="{$configItem.Key}_1" name="config[{$configItem.Key}][value]" value="1" /> 
												<label for="{$configItem.Key}_1">{$lang.enabled}</label>
												
												<input {if $configItem.Default == 0}checked="checked"{/if} type="radio" id="{$configItem.Key}_0" name="config[{$configItem.Key}][value]" value="0" /> 
												<label for="{$configItem.Key}_0">{$lang.disabled}</label>
											{elseif $configItem.Type == 'select'}
												<select {if $configItem.Key == 'timezone'}class="w350"{/if} style="width: 204px;" name="config[{$configItem.Key}][value]" 
													{foreach from=$configItem.Values item='sValue' name='sForeach'}
														{if $smarty.foreach.sForeach.first}
															{if $smarty.foreach.sForeach.total <= '1'} class="disabled" disabled="disabled"{/if}
														>
															{if is_array($sValue)}<option value="">{$lang.select}</option>{/if}
														{/if}
														<option value="{if is_array($sValue)}{$sValue.ID}{else}{$sValue}{/if}" {if is_array($sValue)}{if $configItem.Default == $sValue.ID}selected="selected"{/if}{else}{if $sValue == $configItem.Default}selected="selected"{/if}{/if}>{if is_array($sValue)}{$sValue.name}{else}{$sValue}{/if}</option>
													{/foreach}
												</select>
											{elseif $configItem.Type == 'radio'}
												{assign var='displayItem' value=$configItem.Display}
												{foreach from=$configItem.Values item='rValue' name='rForeach' key='rKey'}
													<input id="radio_{$configItem.Key}_{$rKey}" {if $rValue == $configItem.Default}checked="checked"{/if} type="radio" value="{$rValue}" name="config[{$configItem.Key}][value]" /><label for="radio_{$configItem.Key}_{$rKey}">&nbsp;{$displayItem.$rKey}&nbsp;&nbsp;</label>
												{/foreach}
											{else}
												{$configItem.Default}
											{/if}
											{if $configItem.des != ''}
												<span style="{if $configItem.Type == 'textarea'}line-height: 10px;{elseif $configItem.Type == 'bool'}line-height: 14px;margin: 0 10px;{/if}" class="settings_desc">{$configItem.des}</span>
											{/if}
										</div>
									</td>
								{/if}
							</tr>
							{/foreach}
							<tr>
								<td></td>
								<td><input style="margin: 10px 0 0 0;" type="submit" class="button" value="{$lang.save}" /></td>
							</tr>
							</table>
						</div>
					{else}
						<div style="margin: 10px 20px" class="static">{$lang.confog_group_empty}</div>
					{/if}
				</div>
			{/foreach}
		</td>
	</tr>
	</table>
</form>

<script type="text/javascript">
{literal}

$(document).ready(function(){	
	$('#larea_'+sKey).show();
	
	$('.ltab').each(function(){
		if ( $(this).attr('class').split(' ')[1] == 'tabs_active' )
		{
			$(this).removeClass('tabs_active').addClass('tabs_inactive');
		}
	});
	$('#ltab_'+sKey).removeClass('tabs_inactive').addClass('tabs_active');
	
	$('.larea').hide();
	
	$('#larea_'+sKey).show();
	
	$('.ltab').click(function(){
		
		var yScroll;
		if (self.pageYOffset)
			yScroll = self.pageYOffset;
		else if (document.documentElement && document.documentElement.scrollTop) 
			yScroll = document.documentElement.scrollTop;// Explorer 6 Strict
		else if (document.body)
			yScroll = document.body.scrollTop;// all other Explorers
			
		var pos = $('#settings_anchor').position();
		
		$('html, body').stop();

		if ( yScroll > pos.top )
		{
			$('html, body').animate({scrollTop:pos.top-40}, 'slow');
		}
	
		var cid = $(this).attr('id').split('_')[1];
		$('input[name=group_id]').val(cid);

		$('.ltab').each(function(){
			if ( $(this).attr('class').split(' ')[1] == 'tabs_active' )
			{
				$(this).removeClass('tabs_active').addClass('tabs_inactive');
			}
		});
		$('#ltab_'+cid).removeClass('tabs_inactive').addClass('tabs_active');
		
		$('.larea').hide();
		$('#larea_'+cid).show();
	});
});

{/literal}
</script>

<!-- settings tpl end -->