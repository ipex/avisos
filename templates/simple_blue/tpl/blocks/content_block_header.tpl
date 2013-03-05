{assign var=m_index value='feMenu_'|cat:$block.ID}
{assign var=sCookie value=$smarty.cookies}

<div class="content_block{if $style == 'categories'} categories_box{/if}" {if $no_margin}style="margin: 0;"{/if}>
	<div class="header {if $name} cursor_default{/if}" onclick="action_block('{$block.ID}');">{if $name}{$name}{else}{$block.name}{/if}</div>
	<div {if !$name}id="block_content_{$block.ID}"{/if} class="body{if $sCookie.$m_index == 'hide' && $name} hide{/if}">
		<div class="inner" {if $no_padding}style="padding: 0;"{/if}>