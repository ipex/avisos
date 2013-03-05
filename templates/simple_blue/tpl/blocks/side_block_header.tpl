{assign var='m_index' value='feMenu_'|cat:$block.ID}
{assign var='sCookie' value=$smarty.cookies}

<div class="side_block">
	<div class="header" onclick="action_block('{$block.ID}');">{if $name}{$name}{else}{$block.name}{/if}</div>
	<div id="block_content_{$block.ID}" class="body{if $sCookie.$m_index == 'hide'} hide{/if}">
		<div class="inner" {if $no_padding}style="padding: 0;"{/if}>