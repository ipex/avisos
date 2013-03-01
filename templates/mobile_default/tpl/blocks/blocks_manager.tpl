{if $block.Side == 'left' || $block.Side == 'right'}
	{assign var='style' value='left'}
{else}
	{assign var='style' value='middle'}
{/if}

{if $block.Tpl}
	{if $block.Key == 'account_area'}
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'account_'|cat:$style|cat:'_block_header.tpl' title=$block.name}
	{else}
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:$style|cat:'_block_header.tpl' title=$block.name}
	{/if}
{/if}

{if !$block.Tpl}
<div class="lblock_no_design">
{/if}

{if $block.Type == 'html'}
	{$block.Content}
{elseif $block.Type == 'smarty'}
	{insert name="eval" content=$block.Content}
{elseif $block.Type == 'php'}
	{php}
		eval($this->_tpl_vars['block']['Content']);
	{/php}
{/if}

{if !$block.Tpl}
</div>
{/if}

{if $block.Tpl}
	{if $block.Key == 'account_area'}
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:'account_'|cat:$style|cat:'_block_footer.tpl'}
	{else}
		{include file='blocks'|cat:$smarty.const.RL_DS|cat:$style|cat:'_block_footer.tpl'}
	{/if}
{/if}