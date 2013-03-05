{if $block.Side == 'left' || $block.Side == 'right'}
	{assign var='style' value='side'}
{else}
	{assign var='style' value='content'}
{/if}

{if $block.Key|strpos:'ltcb_' !== false || $block.Key|strpos:'ltsb_' !== false}
	{assign var='no_padding' value=true}
{else}
	{assign var='no_padding' value=false}
{/if}

{if $block.Tpl}
	{include file='blocks'|cat:$smarty.const.RL_DS|cat:$style|cat:'_block_header.tpl' title=$block.name no_padding=$no_padding}
{/if}

{if !$block.Tpl}
<div class="no_design">
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
	{include file='blocks'|cat:$smarty.const.RL_DS|cat:$style|cat:'_block_footer.tpl'}
{/if}