<!-- languages selector -->

{if $languages|@count > 1}
	<div class="bg">
		<div class="arrow"></div>
		<img src="{$rlTplBase}img/flags/{$smarty.const.RL_LANG_CODE|lower}.png" alt="" /> <span id="current_lang_name" class="hide">{$languages[$smarty.const.RL_LANG_CODE].name}</span>
		<ul class="hide">
			{foreach from=$languages item='lang_item'}
				{if $lang_item.Code|lower != $smarty.const.RL_LANG_CODE|lower}
					<li>
						<a title="{$lang_item.name}" href="{if $lang_url_home}{$lang_url_home}{else}{$smarty.const.RL_URL_HOME}{/if}{if $config.mod_rewrite}{$lang_item.dCode}{$pageLink|replace:'&':'&amp;'}{else}?language={$lang_item.Code}{/if}"><img src="{$rlTplBase}img/flags/{$lang_item.Code|lower}.png" alt="" /></a>
						<a class="name" title="{$lang_item.name}" href="{if $lang_url_home}{$lang_url_home}{else}{$smarty.const.RL_URL_HOME}{/if}{if $config.mod_rewrite}{$lang_item.dCode}{$pageLink|replace:'&':'&amp;'}{else}?language={$lang_item.Code}{/if}">{$lang_item.name}</a>
					</li>
				{/if}
			{/foreach}
		</ul>
	</div>
{/if}

<!-- languages selector end -->