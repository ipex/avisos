<!-- langiages selector block -->

{if $langCount > 1}{/if}
	
<div id="languages">
	<table class="sTable">
	<tr>
		{foreach from=$languages item=lang_code}
		<td class="item{if $lang_code.Code != $smarty.const.RL_LANG_CODE} no_bg{/if}">
			{if $lang_code.Code == $smarty.const.RL_LANG_CODE}
				<img title="{$lang_code.name}" src="{$rlTplBase}img/flags/{$lang_code.Code|lower}.png" alt="{$lang_code.name}" />
			{else}
				<a title="{$lang_code.name}" href="{$rlBaseLang}{if $config.mod_rewrite}{$lang_code.dCode}{$pageLink}{else}index.php?language={$lang_code.Code}{/if}">
					<img src="{$rlTplBase}img/flags/{$lang_code.Code|lower}.png" alt="" />	
				</a>
			{/if}
		</td>
		{/foreach}
	</tr>
	</table>
</div>

<!-- langiages selector block end -->