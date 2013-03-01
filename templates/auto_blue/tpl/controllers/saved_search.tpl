<!-- saved search tpl -->

<div id="saved_search_obj">
	{if !empty($saved_search)}
		<div class="highlight">
	
			<table class="list" id="saved_search">
			<tr class="header">
				<td align="center" class="no_padding" style="width: 15px;"><input value="" type="checkbox" class="all" /></td>
				<td class="divider"></td>
				<td align="center" class="no_padding" style="width: 15px;">#</td>
				<td class="divider"></td>
				<td>{$lang.criteria}</td>
				<td class="divider"></td>
				<td style="width: 80px;">{$lang.last_check}</td>
				<td class="divider"></td>
				<td style="width: 70px;">{$lang.status}</td>
				<td class="divider"></td>
				<td style="width: 65px;">{$lang.actions}</td>
			</tr>
			{foreach from=$saved_search item='item' name='searchF'}
			{assign var='status_key' value=$item.Status}
			<tr class="body" id="item_{$item.ID}">
				<td class="no_padding" align="center"><input value="{$item.ID}" type="checkbox" name="item" /></td>
				<td class="divider"></td>
				<td class="no_padding" align="center"><span class="text">{$smarty.foreach.searchF.iteration}</span></td>
				<td class="divider"></td>
				<td>
					<table class="table">
					{foreach from=$item.fields item='field'}
						{include file='blocks'|cat:$smarty.const.RL_DS|cat:'saved_search_field.tpl'}
					{/foreach}
					</table>
				</td>
				<td class="divider"></td>
				<td><span class="text">{$item.Date|date_format:$smarty.const.RL_DATE_FORMAT}</span></td>
				<td class="divider"></td>
				<td id="status_{$item.ID}"><span class="{$status_key}">{$lang.$status_key}</span></td>
				<td class="divider"></td>
				<td>
					<img class="search" id="search_{$item.ID}" alt="{$lang.check_search}" title="{$lang.check_search}" src="{$rlTplBase}img/blank.gif" />
					<img class="del" id="delete_{$item.ID}" alt="{$lang.delete}" title="{$lang.delete}" src="{$rlTplBase}img/blank.gif" />
				</td>
			</tr>
			{/foreach}
			</table>
			
			<div id="mass_actions" class="hide mass_actions">
				{$lang.mass_actions} <a id="activate" href="javascript:void(0);" title="{$lang.activate}">{$lang.activate}</a> | <a id="deactivate" href="javascript:void(0);" title="{$lang.deactivate}">{$lang.deactivate}</a> | <a id="delete" href="javascript:void(0);" title="{$lang.delete}">{$lang.delete}</a>
			</div>
		
			<script type="text/javascript">
			{literal}
			
			$(document).ready(function(){
				$('img.search').click(function(){
					var id = $(this).attr('id').split('_')[1];
					xajax_checkSavedSearch(id);
				});
				
				$('img.del').each(function(){				
					$(this).flModal({
						caption: '{/literal}{$lang.warning}{literal}',
						content: '{/literal}{$lang.notice_remove_search}{literal}',
						prompt: 'xajax_deleteSavedSearch('+ $(this).attr('id').split('_')[1] +')',
						width: 'auto',
						height: 'auto'
					});
				});
				
				$('#saved_search input.all').click(function(){
					var status = $(this).is(':checked') ? true : false;
			
					$('#saved_search input').each(function(){
						$(this).attr('checked', status );
					});
				});
				
				$('#saved_search input').click(function(){
					var tab = false;
					
					$('#saved_search input').each(function(){
						if ( $(this).is(':checked') && $(this).attr('class') != 'all' )
						{
							tab = true;
						}
					});
			
					if ( tab == true )
					{
						$('#mass_actions').fadeIn('normal');
					}
					else
					{
						$('#saved_search input.all').attr('checked', false);
						$('#mass_actions').fadeOut('normal');
					}
				});
				
				$('#mass_actions a').click(function(){
					var items = '';
			
					$('#saved_search input').each(function(){
						if ( $(this).is(':checked') )
						{
							items += $(this).val()+"|";
						}
					});
			
					var action = $(this).attr('id');
			
					xajax_massSavedSearch(items, action);
				});
			});
			
			{/literal}
			</script>
		
		</div>
		
	{else}
		<div class="info">{$lang.no_saved_search}</div>
	{/if}
</div>

<!-- saved search tpl end -->