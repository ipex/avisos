{assign var=m_index value='feMenu_'|cat:$block.ID}
{assign var=sCookie value=$smarty.cookies}

<div class="content_block" {if $no_margin}style="margin: 0;"{/if}>
	<div class="header{if $name} cursor_default{/if}"{if !$name} onclick="action_block('{$block.ID}');"{/if}>
		{if $name}{$name}{else}{$block.name}{/if}
	</div>

	<div {if $no_padding}style="padding: 0;"{/if} {if $name}class="body"{else}id="block_content_{$block.ID}" class="body {if $sCookie.$m_index == 'hide'}hide{/if}"{/if}>
		<div>