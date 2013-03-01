<table class="sTable">

	{foreach from=$allLangs item='langItem' name='langsF'}
	
	<tr class="{if $langItem.Status == 'approval'}opacity {/if}{if $langItem.Code == $config.lang}list_default{else}{if $smarty.foreach.langsF.iteration%2 != 0}list{else}list_light{/if}{/if}">
		<td class="l_name_td">
			<div>
				{$langItem.name}
			</div>
		</td>
		<td style="width:250px;">
			{if $langItem.Status == 'approval'}
				({$lang.approval})
			{else}
				{if $langItem.Code == $config.lang}
					<span>| {$lang.default}</span>
				{else}
					|<button onclick="xajax_setDefault( 'langs_container', '{$langItem.Code}' ); $('#lang_load').fadeIn('normal');">{$lang.set_default}</button>
				{/if}
			{/if}
		</td>
		<td align="right">
			{if $aRights.$cKey.edit}<span onclick="location.href='{$rlBase}index.php?controller={$smarty.get.controller}&amp;action=edit&amp;lang={$langItem.Code}'" class="edit">{$lang.edit}</span> |{/if}
		</td>
		<td style="width: 120px;" align="right">
			<div>
				{if $aRights.$cKey.edit}<span class="edit" onclick="langGrid( '{$langItem.Code}', '{$langItem.name}' );">{$lang.manage_phrases}</span>{/if}
			</div>
		</td>
		<td style="width: 50px;">
			{if $langItem.Code != $config.lang}
				<div>
					{if $aRights.$cKey.delete}|&nbsp;<span class="delete" onclick="rlConfirm( '{$lang.delete_confirm}', 'xajax_deleteLang', '{$langItem.Code}', 'lang_load' );">{$lang.delete}</span>{/if}
				</div>
			{/if}
		</td>
	</tr>
	{if !$smarty.foreach.langsF.last}
	<tr>
		<td colspan="5" class="lang_height"></td>
	</tr>
	{/if}
	
	{/foreach}
	
	<script type="text/javascript">
	{literal}
	$(document).ready(function(){
		$('.opacity').css('opacity', 0.5);
	});
	{/literal}
	</script>
	
	<tr>
		<td colspan="5" align="center" class="td_h9">
			<div class="load" id="lang_load"></div>
		</td>
	</tr>
	
</table>