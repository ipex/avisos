{if !empty($fields)}
	{foreach from=$fields item='field'}
		{if !$field.hidden}
			{assign var='f_type' value=$field.Type}
			<div class="field_obj{if $field.Status == 'approval'} field_inactive{/if}" style="{if $field.hidden}display: none;{/if}" id="field_{$field.ID}">
		
				<div class="field_title" title="{$field.name}{if $field.Status == 'approval'} ({$lang.approval}){/if}">
					<div class="title">{$field.name}</div>
					<span class="b_field_type">{$lang.field_type}: (<span>{$l_types.$f_type|truncate:25:"...":true}</span>)</span>
				</div>
				
				{*if !empty($groups)}
				<div class="b_append">
					<div>
					<select class="b_select deny" id="fg_selector_{$field.ID}">
						<option value="0">{$lang.append_to_form}</option>
						<optgroup label="{$lang.append_to_group} &#172;" style="font-weight: normal;">
						{foreach from=$groups item='group'}
						<option value="{$group.ID}">- {$group.name}</option>
						{/foreach}
						</optgroup>
					</select>
					<input onclick="xajax_append2form('{$kind_info.ID}', $('#fg_selector_{$field.ID}').val(), '{$field.ID}');" type="button" value="{$lang.go}" class="b_button deny" />
					</div>
				</div>
				{else}
					<div class="b_append" onclick="xajax_append2form('{$kind_info.ID}', '0', '{$field.ID}');"><div>{$lang.append_to_form}</div></div>
				{/if*}
		
			</div>
		{/if}
	{/foreach}
{else}
	<div class="form_default">
		<center>{$lang.no_fields}</center>
	</div>
{/if}