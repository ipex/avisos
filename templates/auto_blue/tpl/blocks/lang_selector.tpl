<!-- languages selector -->

{if $languages|@count > 1}
	<div class="languages">
		<div class="bg">
			<div class="inner">
				<img src="{$rlTplBase}img/blank.gif" style="background: url('{$rlTplBase}img/lang_flags/{$smarty.const.RL_LANG_CODE|lower}.gif') 0 0 no-repeat;" alt="" /> {$languages[$smarty.const.RL_LANG_CODE].name}
				<img src="{$rlTplBase}img/blank.gif" class="arrow" alt="" />
			</div>
			
			<div class="list">
				<ul class="hide">
					{foreach from=$languages item='lang_item'}
						{if $lang_item.Code|lower != $smarty.const.RL_LANG_CODE|lower}
							<li>
								<a title="{$lang_item.name}" href="{if $lang_url_home}{$lang_url_home}{else}{$smarty.const.RL_URL_HOME}{/if}{if $config.mod_rewrite}{$lang_item.dCode}{$pageLink|replace:'&':'&amp;'}{else}?language={$lang_item.Code}{/if}"><img src="{$rlTplBase}img/blank.gif" style="background: url('{$rlTplBase}img/lang_flags/{$lang_item.Code|lower}.gif') 0 0 no-repeat;" alt="" /></a>
								<a class="name" title="{$lang_item.name}" href="{if $lang_url_home}{$lang_url_home}{else}{$smarty.const.RL_URL_HOME}{/if}{if $config.mod_rewrite}{$lang_item.dCode}{$pageLink|replace:'&':'&amp;'}{else}?language={$lang_item.Code}{/if}">{$lang_item.name}</a>
							</li>
						{/if}
					{/foreach}
				</ul>
			</div>
		</div>
	</div>
{/if}

<!-- languages selector end -->