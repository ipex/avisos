<!-- fields block ( for search ) -->

<div class="padding">

{foreach from=$fields item='field' name='searchF'}
	{assign var='fKey' value=$field.Key}
	{assign var='post_key' value=$sf_key|cat:'_post'}
	{assign var='fVal' value=$smarty.session.$post_key}
	
	<div class="field" style="margin: 10px 0 0 0;padding: 0 0 0 0;">
		{$lang[$field.pName]}
	</div>

	{if $field.Type == 'text'}
		<input class="text" type="text" name="f[{$field.Key}]" maxlength="{if $field.Values != ''}{$field.Values}{else}255{/if}" {if $fVal.$fKey}value="{$fVal.$fKey}"{/if} />
		{if $field.Key == 'keyword_search'}
			<div class="keyword_search_opt">
				<div>
					{assign var='tmp' value=3}
					{section name='keyword_opts' loop=$tmp max=3}
						<label><input {if $fVal.keyword_search_type}{if $smarty.section.keyword_opts.iteration == $fVal.keyword_search_type}checked="checked"{/if}{else}{if $smarty.section.keyword_opts.iteration == 2}checked="checked"{/if}{/if} value="{$smarty.section.keyword_opts.iteration}" type="radio" name="f[keyword_search_type]" /> {assign var='ph' value='keyword_search_opt'|cat:$smarty.section.keyword_opts.iteration}{$lang.$ph}</label>
					{/section}
				</div>
			</div>
			<div><a id="refine_keyword_opt" class="dotted" href="javascript:void(0)">{$lang.advanced_options}</a></div>
			<script type="text/javascript">
			{literal}
			
			$(document).ready(function(){
				$('#refine_keyword_opt').click(function(){
					$(this).parent().prev().slideToggle();
				});
			});
			
			{/literal}
			</script>
		{/if}
	{elseif $field.Type == 'number'}
		{if $field.Key|strpos:'zip' !== false && $aHooks.search_by_distance}
			<select name="f[{$field.Key}][distance]" class="w50">
				{foreach from=','|explode:$config.sbd_distance_items item='distance'}
					<option {if $fVal.$fKey.distance == $distance}selected="selected"{/if} value="{$distance}">{$distance}</option>
				{/foreach}
			</select>
			{if $config.sbd_default_units == 'miles'}
				{$lang.sbd_mi_short}
			{else}
				{$lang.sbd_km_short}
			{/if}
			{$lang.sbd_within}
				<input {if $fVal.$fKey.zip}value="{$fVal.$fKey.zip}"{/if} class="w50" type="text" name="f[{$field.Key}][zip]" size="{if $field.Values}{$field.Values|count_characters}{else}10{/if}" maxlength="{if $field.Values}{$field.Values|count_characters}{else}10{/if}" />
			{$lang.sbd_zipcode}
		{elseif $field.Key|strpos:'zip' != false && !$aHooks.search_by_distance}
			<input {if $fVal.$fKey.from}value="{$fVal.$fKey.from}"{/if} class="numeric" type="text" name="f[{$field.Key}][from]" size="{if $field.Values}{$field.Values|count_characters}{else}10{/if}" maxlength="{if $field.Values}{$field.Values|count_characters}{else}10{/if}" />
		{else}
			{*<span class="fLable">{$lang.from}</span> *}<input {if $fVal.$fKey.from}value="{$fVal.$fKey.from}"{/if} class="numeric" type="text" name="f[{$field.Key}][from]" size="{if $field.Values}{$field.Values|count_characters}{else}10{/if}" maxlength="{if $field.Values}{$field.Values|count_characters}{else}10{/if}" />
			<span class="fLable">{$lang.to}</span> <input {if $fVal.$fKey.to}value="{$fVal.$fKey.to}"{/if} class="numeric" type="text" name="f[{$field.Key}][to]" size="{if $field.Values}{$field.Values|count_characters}{else}10{/if}" maxlength="{if $field.Values}{$field.Values|count_characters}{else}10{/if}" />
		{/if}
	{elseif $field.Type == 'date'}
		{if $field.Default == 'multi'}
			<input class="text" type="text" id="date_{$field.Key}{if $postfix}_{$postfix}{/if}" name="f[{$field.Key}]" maxlength="10" style="width: 70px;float: {$text_dir};" value="{$fVal.$fKey}" />
			<div class="clear"></div>
			<script type="text/javascript">
			{literal}
			$(document).ready(function(){
				$('#date_{/literal}{$field.Key}{if $postfix}_{$postfix}{/if}{literal}').datepicker({dateFormat: 'yy-mm-dd'}).datepicker($.datepicker.regional['{/literal}{$smarty.const.RL_LANG_CODE}{literal}']);
			});
			{/literal}
			</script>
		{elseif $field.Default == 'single'}
			<table>
			<tr>
			{*<td><label for="date_{$field.Key}_from" class="fLable">{$lang.from}</label></td>*}
				<td style="width: 120px;"><input class="text" type="text" id="date_{$field.Key}_from{if $postfix}_{$postfix}{/if}" name="f[{$field.Key}][from]" maxlength="10" style="width: 70px;float: {$text_dir};" value="{$fVal.$fKey.from}" /></td>
				<td><label for="date_{$field.Key}_to" class="fLable">{$lang.to}</label></td>
				<td style="width: 120px;"><input class="text" type="text" id="date_{$field.Key}_to{if $postfix}_{$postfix}{/if}" name="f[{$field.Key}][to]" maxlength="10" style="width: 70px;float: {$text_dir};" value="{$fVal.$fKey.to}" /></td>
			</tr>
			</table>
			<script type="text/javascript">
			{literal}
			$(document).ready(function(){
				$('#date_{/literal}{$field.Key}_from{if $postfix}_{$postfix}{/if}{literal}').datepicker({dateFormat: 'yy-mm-dd'}).datepicker($.datepicker.regional['{/literal}{$smarty.const.RL_LANG_CODE}{literal}']);
				$('#date_{/literal}{$field.Key}_to{if $postfix}_{$postfix}{/if}{literal}').datepicker({dateFormat: 'yy-mm-dd'}).datepicker($.datepicker.regional['{/literal}{$smarty.const.RL_LANG_CODE}{literal}']);
			});
			{/literal}
			</script>
		{/if}
	{elseif $field.Type == 'mixed'}
		{*<span class="fLable">{$lang.from}</span> *}<input {if $fVal.$fKey.from}value="{$fVal.$fKey.from}"{/if} class="numeric" type="text" name="f[{$field.Key}][from]" style="width: 42px" maxlength="15" />
		<span class="fLable">{$lang.to}</span> <input {if $fVal.$fKey.to}value="{$fVal.$fKey.to}"{/if} class="numeric" type="text" name="f[{$field.Key}][to]" style="width: 42px" maxlength="15" />
		<span class="fLable">{$lang.unit}</span> <select name="f[{$field.Key}][df]" style="width: 70px;">
			<option value="0">{$lang.any}</option>
			{if !empty($field.Condition)}
				{assign var='df_source' value=$field.Condition|df}
			{else}
				{assign var='df_source' value=$field.Values}
			{/if}
			{foreach from=$df_source item='df_item'}
				<option value="{$df_item.Key}" {if ($df_item.Key == $fVal.$fKey.df) || $fd_item.Default}selected="selected"{/if}>{$lang[$df_item.pName]}</option>
			{/foreach}
		</select>
	{elseif $field.Type == 'price'}
		{*<span class="fLable">{$lang.from}</span> *}<input {if $fVal.$fKey.from}value="{$fVal.$fKey.from}"{/if} class="numeric" type="text" name="f[{$field.Key}][from]" style="width: 42px" maxlength="15" />
		<span class="fLable">{$lang.to}</span> <input {if $fVal.$fKey.to}value="{$fVal.$fKey.to}"{/if} class="numeric" type="text" name="f[{$field.Key}][to]" style="width: 42px" maxlength="15" />
		<span class="fLable">{$lang.currency}</span> <select name="f[{$field.Key}][currency]" style="width: 70px;">
			<option value="0">{$lang.any}</option>
			{foreach from='currency'|df item='currency_item'}
				<option value="{$currency_item.Key}" {if ($currency_item.Key == $fVal.$fKey.currency) || $currency_item.Default}selected="selected"{/if}>{$lang[$currency_item.pName]}</option>
			{/foreach}
		</select>
	{elseif $field.Type == 'bool'}
		<input id="{$field.Key}_1{if $postfix}_{$postfix}{/if}" type="radio" value="on" name="f[{$field.Key}]" {if $fVal.$fKey == 'on'}checked="checked"{/if} /> <label for="{$field.Key}_1{if $postfix}_{$postfix}{/if}" class="fLable">{$lang.yes}</label>
		<input id="{$field.Key}_0{if $postfix}_{$postfix}{/if}" type="radio" value="off" name="f[{$field.Key}]" {if $fVal.$fKey == 'off'}checked="checked"{/if}/> <label for="{$field.Key}_0{if $postfix}_{$postfix}{/if}" class="fLable">{$lang.no}</label>
	{elseif $field.Type == 'select'}
		{rlHook name='fieldsTplSelectArea'}
		{if $field.Condition == 'years'}
			{*<span class="fLable">{$lang.from}</span> *}
			<select name="f[{$field.Key}][from]" style="width: auto;">
				<option value="0">{$lang.any}</option>
				{foreach from=$field.Values item='option' key='key'}
					{if $field.Condition}
						{assign var='key' value=$option.Key}
					{/if}
					<option {if $fVal.$fKey.from}{if $fVal.$fKey.from == $key}selected="selected"{/if}{/if} value="{if $field.Condition}{$option.Key}{else}{$key}{/if}">{$option.name}</option>
				{/foreach}
			</select>
			<span class="fLable">{$lang.to}</span> 
			<select name="f[{$field.Key}][to]" style="width: auto;">
				<option value="0">{$lang.any}</option>
				{foreach from=$field.Values item='option' key='key'}
					{if $field.Condition}
						{assign var='key' value=$option.Key}
					{/if}
					<option {if $fVal.$fKey.to}{if $fVal.$fKey.to == $key}selected="selected"{/if}{/if} value="{if $field.Condition}{$option.Key}{else}{$key}{/if}">{$option.name}</option>
				{/foreach}
			</select>
		{else}
			<select name="f[{$field.Key}]" style="width: 200px;">
				<option value="0">{$lang.any}</option>
				{foreach from=$field.Values item='option' key='key'}
					{if $field.Condition}
						{assign var='key' value=$option.Key}
					{/if}
					<option {if $field.Key == 'Category_ID'}style="padding-{$text_dir}: {$option.margin}px;"{/if} {if $field.Key == 'Category_ID' && $option.Level == '0'}class="highlight_option"{/if} {if isset($fVal.$fKey) && $fVal.$fKey == $key}selected="selected"{/if} value="{if $field.Key == 'Category_ID'}{$option.ID}{else}{if $field.Condition}{$option.Key}{else}{$key}{/if}{/if}">{$lang[$option.pName]}</option>
				{/foreach}
			</select>
		{/if}
	{elseif $field.Type == 'checkbox'}
		{assign var='fDefault' value=$field.Default}
		<input type="hidden" name="f[{$field.Key}][0]" value="0" />
		<table>
		<tr>
		{foreach from=$field.Values item='option' key='key' name='checkboxF'}
			{if $field.Condition}
				{assign var='key' value=$option.Key}
			{/if}
			<td {if $smarty.foreach.checkboxF.total > 5}style="width: 33%"{/if}>
				<label><input type="checkbox" value="{$key}" name="f[{$field.Key}][{$key}]" {if is_array($fVal.$fKey)}{foreach from=$fVal.$fKey item='chVals'}{if $chVals == $key}checked="checked"{/if}{/foreach}{/if} /> {$lang[$option.pName]}</label>
			</td>
			{if $smarty.foreach.checkboxF.iteration%3 == 0 && $smarty.foreach.checkboxF.total > $smarty.foreach.checkboxF.iteration}
			</tr>
			<tr>
			{/if}
		{/foreach}
		</tr>
		</table>
	{elseif $field.Type == 'radio'}
		<input type="hidden" value="0" name="f[{$field.Key}]" />
		<table>
		<tr>
		{foreach from=$field.Values item='option' key='key' name='radioF'}
			{if $field.Condition}
				{assign var='key' value=$option.Key}
			{/if}
			<td {if $smarty.foreach.radioF.total > 5}style="width: 33%"{/if}>
				<label><input type="radio" value="{$key}" name="f[{$field.Key}]" {if $fVal.$fKey}{if $fVal.$fKey == $key}checked="checked"{/if}{/if} /> {$lang[$option.pName]}</label>
			</td>
			{if $smarty.foreach.radioF.iteration%3 == 0 && !$smarty.foreach.radioF.last}
			</tr>
			<tr>
			{/if}
		{/foreach}
		</tr>
		</table>
	{/if}

{/foreach}

</div>

<!-- fields block ( for search ) end -->