<!-- fieldset block -->

<div class="fieldset" {if $id}id="fs_{$id}"{/if}>
	{if $class}<div class="{$class}">{/if}
	<table class="fieldset_header">
	<tr>
		<td class="caption">{$name}</td>
		<td class="line">&nbsp;</td>
		<td class="arrow"></td>
	</tr>
	</table>
	{if $class}</div>{/if}
	
	<div class="body{if $tall} tall{/if}">
		<div>