<!-- show listing field values -->	

	{if $field.Type == 'text'}
		{$field.value}
	{elseif $field.Type == 'textarea'}
		{$field.value}
	{elseif $field.Type == 'number'}
		{$field.value}
	{elseif $field.Type == 'price'}
		{$field.value.1}{$field.value.0}
	{elseif $field.Type == 'bool'}
		<input id="{$field.Key}_1" type="radio" value="1" name="f[{$field.Key}]" {if $fVal.$fKey == 'on'}checked{elseif $field.Default}checked{/if} /> <label for="{$field.Key}_1" class="fLable">{$lang.yes}</label>
		<input id="{$field.Key}_0" type="radio" value="0" name="f[{$field.Key}]" {if $fVal.$fKey == 'off'}checked{elseif !$field.Default}checked{/if} /> <label for="{$field.Key}_0" class="fLable">{$lang.no}</label>
	{elseif $field.Type == 'select'}
		<select name="f[{$field.Key}]" {if $field.Condition == 'years'}style="width: 110px;"{/if}>
			<option value="0">{$lang.select}</option>

			{foreach from=$field.Values item='option' key='key'}
				{if $field.Condition}
					{assign var='key' value=$option.Key}
				{/if}
				<option value="{if $field.Condition}{$option.Key}{else}{$key}{/if}" {if $fVal.$fKey}{if $fVal.$fKey == $key}selected{/if}{else}{if $field.Default == $key}selected{/if}{/if}>{$option.name}</option>
			{/foreach}
		</select>
	{elseif $field.Type == 'checkbox'}
		{assign var='fDefault' value=$field.Default}
		<input type="hidden" name="f[{$field.Key}][0]" value="0" />
		{foreach from=$field.Values item='option' key='key'}
		<input type="checkbox" id="{$field.Key}_{$key}" value="{$key}" {if is_array($fVal.$fKey)}{foreach from=$fVal.$fKey item='chVals'}{if $chVals == $key}checked{/if}{/foreach}{else}{foreach from=$field.Default item='chDef'}{if $chDef == $key}checked{/if}{/foreach}{/if} name="f[{$field.Key}][{$key}]" /> <label for="{$field.Key}_{$key}" class="fLable">{$option.name}</label>
		{/foreach}
	{elseif $field.Type == 'radio'}
		<input type="hidden" value="0" name="f[{$field.Key}]" />
		{foreach from=$field.Values item='option' key='key'}
			<input type="radio" id="{$field.Key}_{$key}" value="{$key}" name="f[{$field.Key}]" {if $fVal.$fKey}{if $fVal.$fKey == $key}checked{/if}{else}{if $field.Default == $key}checked{/if}{/if} /> <label for="{$field.Key}_{$key}" class="fLable">{$option.name}</label>
		{/foreach}
	{elseif $field.Type == 'file' || $field.Type == 'image'}
		{assign var='field_type' value=$field.Default}
		<input type="hidden" name="f[{$field.Key}]" value="simulation" />
		<input class="file" type="file" name="{$field.Key}" />{if $field.Type == 'file' && !empty($field.Default)}<span class="grey_small"> <em>{$l_file_types.$field_type.name} (.{$l_file_types.$field_type.ext|replace:',':', .'})</em></span>{/if}
	{elseif $field.Type == 'accept'}
		<textarea style="width: 80%;" rows="6" readonly class="text" name="{$field.Key}">{$field.default}</textarea><br />
		<input type="hidden" name="f[{$field.Key}]" value="no" />
		<input type="checkbox" id="{$field.Key}" name="f[{$field.Key}]" value="yes" /> <label for="{$field.Key}" class="fLable">{$lang.accept}</label>
		{if $field.Required}
			<span class="red">*</span>
		{/if}
	{/if}
	
<!-- show listing field values end -->