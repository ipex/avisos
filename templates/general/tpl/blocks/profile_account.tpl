<!-- my profile account fields -->

{foreach from=$profile_info.Fields item='field'}
	{assign var='fKey' value=$field.Key}
	{assign var='fVal' value=$smarty.post.account}
	
	<tr>
		<td class="name">
			{if $field.Required}
				<span class="red">*</span>
			{/if}
			
			{$field.name}
		</td>
		<td class="field" id="sf_field_{$field.Key}">
			{if $field.Type == 'text'}
				{if $field.Multilingual && $languages|@count > 1}
					<div class="ml_tabs">
						<ul>
							{foreach from=$languages item='language' name='langF'}
								<li lang="{$language.Code}" {if $smarty.foreach.langF.first}class="active"{/if}>{$language.name}</li>
							{/foreach}
						</ul>
						<div class="nav left"></div>
						<div class="nav right"></div>
					</div>
					<div class="ml_tabs_content">
						{foreach from=$languages item='language' name='langF'}
						<div lang="{$language.Code}" {if !$smarty.foreach.langF.first}class="hide"{/if}>
							<input class="w350" type="text" name="account[{$field.Key}][{$language.Code}]" maxlength="{if $field.Values != ''}{$field.Values}{else}255{/if}" {if $fVal.$fKey[$language.Code]}value="{$fVal.$fKey[$language.Code]}"{elseif $field.Default}value="{$lang[$field.pDefault]}"{/if} /> <span>{$language.name}</span>
						</div>
						{/foreach}
					</div>
				{else}
					<input class="w350" type="text" name="account[{$field.Key}]" maxlength="{if $field.Values != ''}{$field.Values}{else}255{/if}" {if $fVal.$fKey}value="{$fVal.$fKey}"{elseif $field.Default}value="{$lang[$field.pDefault]}"{/if} />
				{/if}
			{elseif $field.Type == 'textarea'}
				<script type="text/javascript">var textarea_fields = new Array();</script>
				{if $field.Multilingual && $languages|@count > 1}
					<div class="ml_tabs">
						<ul>
							{foreach from=$languages item='language' name='langF'}
								<li lang="{$language.Code}" {if $smarty.foreach.langF.first}class="active"{/if}>{$language.name}</li>
							{/foreach}
						</ul>
					</div>
					<div class="ml_tabs_content">
						{foreach from=$languages item='language' name='langF'}
						<div lang="{$language.Code}" {if !$smarty.foreach.langF.first}class="hide"{/if}>
							{if $field.Condition == 'html'}<div class="hide">{if $fVal.$fKey[$language.Code]}{$fVal.$fKey[$language.Code]}{elseif $field.Default}{$lang[$field.pDefault]}{/if}</div>{/if}
							<textarea rows="5" cols="" name="account[{$field.Key}][{$language.Code}]" id="textarea_{$field.Key}_{$language.Code}">{if $field.Condition != 'html'}{if $fVal.$fKey[$language.Code]}{$fVal.$fKey[$language.Code]}{elseif $field.Default}{$lang[$field.pDefault]}{/if}{/if}</textarea>
							<script type="text/javascript">
							textarea_fields.push('textarea_{$field.Key}_{$language.Code}');
							{if $field.Condition != 'html'}
							{literal}
							
							$(document).ready(function(){
								$('#textarea_{/literal}{$field.Key}_{$language.Code}{literal}').textareaCount({
									'maxCharacterSize': {/literal}{$field.Values}{literal},
									'warningNumber': 20
								})
							});
							
							{/literal}
							{/if}
							</script>
						</div>
						{/foreach}
					</div>
				{else}
					{if $field.Condition == 'html'}<div class="hide">{if $fVal.$fKey}{$fVal.$fKey}{elseif $field.Default}{$lang[$field.pDefault]}{/if}</div>{/if}
					<textarea rows="5" cols="" name="account[{$field.Key}]" id="textarea_{$field.Key}">{if $field.Condition != 'html'}{if $fVal.$fKey}{$fVal.$fKey}{elseif $field.Default}{$lang[$field.pDefault]}{/if}{/if}</textarea>
					<script type="text/javascript">
					textarea_fields.push('textarea_{$field.Key}');
					{if $field.Condition != 'html'}
					{literal}
					
					$(document).ready(function(){
						$('#textarea_{/literal}{$field.Key}{literal}').textareaCount({
							'maxCharacterSize': {/literal}{$field.Values}{literal},
							'warningNumber': 20
						})
					});
					
					{/literal}
					{/if}
					</script>
				{/if}
				
				{if $field.Condition == 'html'}
					<script type="text/javascript">
					{literal}
					
					flynax.htmlEditor(textarea_fields);
					
					{/literal}
					</script>
				{/if}
			{elseif $field.Type == 'number'}
				<input class="numeric wauto" type="text" name="account[{$field.Key}]" size="{if $field.Values}{$field.Values}{else}18{/if}" maxlength="{if $field.Values}{$field.Values}{else}18{/if}" {if $fVal.$fKey}value="{$fVal.$fKey}"{/if} />
			{elseif $field.Type == 'phone'}
				<span class="phone-field">
					{if $field.Opt1}
						+ <input type="text" name="account[{$field.Key}][code]" {if $fVal.$fKey.code}value="{$fVal.$fKey.code}"{/if} maxlength="4" size="3" class="wauto ta-center numeric" /> -
					{/if}
					{if $field.Condition}
						{assign var='df_source' value=$field.Condition|df}
						<select name="account[{$field.Key}][area]" class="w50">
							{foreach from=$df_source item='df_item' key='df_key'}
								<option value="{$lang[$df_item.pName]}" {if $fVal.$fKey.area}{if $lang[$df_item.pName] == $fVal.$fKey.area}selected="selected"{/if}{else}{if $df_item.Default}selected="selected"{/if}{/if}>{$lang[$df_item.pName]}</option>
							{/foreach}
						</select>
					{else}
						<input type="text" name="account[{$field.Key}][area]" {if $fVal.$fKey.area}value="{$fVal.$fKey.area}"{/if} maxlength="{$field.Default}" size="{$field.Default}" class="wauto ta-center numeric" />
					{/if}
					-
					<input type="text" name="account[{$field.Key}][number]" {if $fVal.$fKey.number}value="{$fVal.$fKey.number}"{/if} maxlength="{$field.Values}" size="{$field.Values+2}" class="wauto ta-center numeric" />
					{if $field.Opt2}
						{$lang.phone_ext_out} <input type="text" name="account[{$field.Key}][ext]" {if $fVal.$fKey.ext}value="{$fVal.$fKey.ext}"{/if} maxlength="4" size="3" class="wauto ta-center" />
					{/if}
				</span>
			{elseif $field.Type == 'date'}
				{if $field.Default == 'single'}
					<input type="text" id="date_{$field.Key}" name="account[{$field.Key}]" maxlength="10" style="width: 70px;float: {$text_dir};" value="{$fVal.$fKey}" />
					<div class="clear"></div>
					<script type="text/javascript">
					{literal}
					$(document).ready(function(){
						$('#date_{/literal}{$field.Key}{literal}').datepicker({showOn: 'both', buttonImage: '{/literal}{$rlTplBase}{literal}img/blank.gif', buttonImageOnly: true, dateFormat: 'yy-mm-dd'}).datepicker($.datepicker.regional['{/literal}{$smarty.const.RL_LANG_CODE}{literal}']);
					});
					{/literal}
					</script>
				{elseif $field.Default == 'multi'}
					<table>
					<tr>
						<td><label for="date_{$field.Key}_from">{$lang.from}</label></td>
						<td style="width: 120px;"><input class="text" type="text" id="date_{$field.Key}_from" name="account[{$field.Key}][from]" maxlength="10" style="width: 70px;float: {$text_dir};" value="{$fVal.$fKey.from}" /></td>
						<td><label for="date_{$field.Key}_to">{$lang.to}</label></td>
						<td style="width: 120px;"><input class="text" type="text" id="date_{$field.Key}_to" name="account[{$field.Key}][to]" maxlength="10" style="width: 70px;float: {$text_dir};" value="{$fVal.$fKey.to}" /></td>
					</tr>
					</table> 
					<script type="text/javascript">
					{literal}
					$(document).ready(function(){
						$('#date_{/literal}{$field.Key}{literal}_from').datepicker({showOn: 'both', buttonImage: '{/literal}{$rlTplBase}{literal}img/blank.gif', buttonImageOnly: true, dateFormat: 'yy-mm-dd'}).datepicker($.datepicker.regional['{/literal}{$smarty.const.RL_LANG_CODE}{literal}']);
						$('#date_{/literal}{$field.Key}{literal}_to').datepicker({showOn: 'both', buttonImage: '{/literal}{$rlTplBase}{literal}img/blank.gif', buttonImageOnly: true, dateFormat: 'yy-mm-dd'}).datepicker($.datepicker.regional['{/literal}{$smarty.const.RL_LANG_CODE}{literal}']);
					});
					{/literal}
					</script>
				{/if}
			{elseif $field.Type == 'mixed'}
				<input class="numeric" type="text" name="account[{$field.Key}][value]" size="8" maxlength="15" {if $fVal.$fKey.value}value="{$fVal.$fKey.value}"{/if} />
				{if !empty($field.Condition)}
					{assign var='df_condition' value=$field.Condition}
					{assign var='df_source' value=$df.$df_condition}
				{else}
					{assign var='df_source' value=$field.Values}
				{/if}
		
				{if $df_source|@count > 1}
					<select name="account[{$field.Key}][df]">
						{foreach from=$df_source item='df_item' key='df_key'}
							<option value="{$df_item.Key}" {if $fVal.$fKey.df}{if $df_item.Key == $fVal.$fKey.df}selected="selected"{/if}{else}{if $df_key == $field.Default}selected="selected"{/if}{/if}>{$field.Default}{$df_item.name}</option>
						{/foreach}
					</select>
				{else}
					<input type="hidden" name="account[{$field.Key}][df]" value="{foreach from=$df_source item='df_item'}{$df_item.Key}{/foreach}" />
					{foreach from=$df_source item='df_item'}{$df_item.name}{/foreach}
				{/if}
			{elseif $field.Type == 'unit'}
				<input class="numeric w80" type="text" name="account[{$field.Key}][value]" size="8" maxlength="15" {if $fVal.$fKey.value}value="{$fVal.$fKey.value}"{/if} />
				{if $df.unit|@count > 1}
				<select name="account[{$field.Key}][unit]" style="width: 60px;">
					{foreach from=$df.unit item='unit_item'}
						<option value="{$unit_item.Key}" {if $unit_item.Key == $fVal.$fKey.unit}selected="selected"{/if}>{$unit_item.name}</option>
					{/foreach}
				</select>
				{else}
					<input type="hidden" name="account[{$field.Key}][unit]" value="{$df.unit.0.Key}" />
					{$df.unit.0.name}
				{/if}
			{elseif $field.Type == 'bool'}
				<label><input type="radio" value="on" name="account[{$field.Key}]" {if $fVal.$fKey == 'on'}checked="checked"{elseif $field.Default}checked="checked"{/if} /> {$lang.yes}</label>
				<label><input type="radio" value="off" name="account[{$field.Key}]" {if $fVal.$fKey == 'off'}checked="checked"{elseif !$field.Default}checked="checked"{/if} /> {$lang.no}</label>
			{elseif $field.Type == 'select'}
				{rlHook name='tplProfileFieldSelect'}
				<select name="account[{$field.Key}]">
					<option value="0">{$lang.select}</option>
		
					{foreach from=$field.Values item='option' key='key'}
						{if $field.Condition}
							{assign var='key' value=$option.Key}
						{/if}
						<option value="{if $field.Condition}{$option.Key}{else}{$key}{/if}" {if $fVal.$fKey}{if $fVal.$fKey == $key}selected="selected"{/if}{else}{if ($field.Default == $key) || $option.Default }selected="selected"{/if}{/if}>{$lang[$option.pName]}</option>
					{/foreach}
				</select>
			{elseif $field.Type == 'checkbox'}
				{if $field.Opt2}
					{assign var='col_num' value=$field.Opt2}
				{else}
					{assign var='col_num' value=3}
				{/if}
				{assign var='fDefault' value=$field.Default}
				<input type="hidden" name="account[{$field.Key}][0]" value="0" />
				<table {if $col_num > 2} class="fixed"{/if}>
				<tr>
				{foreach from=$field.Values item='option' key='key' name='checkboxF'}
					{if !empty($field.Condition)}
						{assign var="key" value=$option.Key}
					{/if}
					<td valign="top" style="padding: 2px 0;">
						<label><input type="checkbox" value="{$key}" {if is_array($fVal.$fKey)}{foreach from=$fVal.$fKey item='chVals'}{if $chVals == $key}checked="checked"{/if}{/foreach}{else}{foreach from=$field.Default item='chDef'}{if $chDef == $key && !empty($chDef)}checked="checked"{/if}{/foreach}{/if} name="account[{$field.Key}][{$key}]" /> {$lang[$option.pName]}</label>
					</td>
					{if $smarty.foreach.checkboxF.iteration%$col_num == 0 && !$smarty.foreach.checkboxF.last}
					</tr>
					<tr>
					{/if}
				{/foreach}
				</tr>
				</table>
				<div class="checkbox_bar"><a href="javascript:void(0)" onclick="$(this).parent().prev().find('input[type=checkbox]').attr('checked', true)">{$lang.check_all}</a> / <a onclick="$(this).parent().prev().find('input[type=checkbox]').attr('checked', false)" href="javascript:void(0)">{$lang.uncheck_all}</a></div>
			{elseif $field.Type == 'radio'}
				<input type="hidden" value="0" name="account[{$field.Key}]" />
				<table>
				<tr>
				{foreach from=$field.Values item='option' key='key' name='radioF'}
					<td {if $smarty.foreach.radioF.total > 5}style="width: 33%"{/if}>
						<label><input type="radio" value="{$key}" name="account[{$field.Key}]" {if $fVal.$fKey}{if $fVal.$fKey == $key}checked="checked"{/if}{else}{if $field.Default == $key}checked="checked"{/if}{/if} /> {$lang[$option.pName]}</label>
					</td>
					{if $smarty.foreach.radioF.iteration%3 == 0}
					</tr>
					<tr>
					{/if}
				{/foreach}
				</tr>
				</table>
			{elseif $field.Type == 'file' || $field.Type == 'image'}
				{assign var='field_type' value=$field.Default}

				{if $fVal.$fKey}
					<div id="{$field.Key}_file">
						{if $field.Type == 'file'}
							<a href="{$smarty.const.RL_URL_HOME}files/{$fVal.$fKey}">{$lang.download}</a>
							|
							<a id="delete_{$field.Key}" href="javascript:void(0)">{$lang.delete}</a>
						{else}
							<div class="relative fleft">
								<img style="height: auto;" class="thumbnail" title="{$field.name}" alt="{$field.name}" src="{$smarty.const.RL_URL_HOME}files/{$fVal.$fKey}" />
								<img id="delete_{$field.Key}" class="delete" style="display: block;" src="{$rlTplBase}img/blank.gif" alt="" title="{$lang.delete}" />
							</div>
							<div class="clear"></div>
						{/if}
						
						<script type="text/javascript">//<![CDATA[
						{literal}
						
						$(document).ready(function(){
							$('#delete_{/literal}{$field.Key}{literal}').flModal({
								{/literal}
								caption: lang['warning'],
								content: '{$lang.delete_confirm}',
								prompt: "xajax_delAccountFile('{$field.Key}', '{$fVal.$fKey}', '{$field.Key}_file')",
								width: 'auto',
								height: 'auto'
								{literal}
							});
						});
						
						{/literal}
						//]]>
						</script>
					</div>
				{/if}
				
				<input type="hidden" name="account[{$field.Key}]" value="{$fVal.$fKey}" />
				<input class="file" type="file" name="{$field.Key}" />{if $field.Type == 'file' && !empty($field.Default)}<span class="grey_small"> <em>{$l_file_types.$field_type.name} (.{$l_file_types.$field_type.ext|replace:',':', .'})</em></span>{/if}
			{elseif $field.Type == 'accept'}
				<textarea style="width: 80%;" rows="6" readonly class="text" name="{$field.Key}">{$field.default}</textarea><br />
				<input type="hidden" name="account[{$field.Key}]" value="no" />
				<label><input type="checkbox" name="account[{$field.Key}]" class="policy" value="yes" /> {$lang.accept}</label>
				{if $field.Required}
					<span class="red">*</span>
				{/if}
			{/if}
			
			{if !empty($field.description)}
				<img class="qtip" alt="" title="{$field.description}" id="fd_{$field.Key}" src="{$rlTplBase}img/blank.gif" />
			{/if}
		</td>
	</tr>

{/foreach}

<!-- my profile fields end -->
