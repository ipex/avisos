<!-- fields block ( for search ) -->

{if $config.search_fields_position == 2}
	<table class="search">
{else}
	<div class="search">
{/if}

{foreach from=$fields item='field'}
	{assign var='fKey' value=$field.Key}
	{assign var='fVal' value=$smarty.post}
	
	{if $config.search_fields_position == 1}
	
		<div class="field">
			{$lang[$field.pName]}
		</div>
	
	{elseif $config.search_fields_position == 2}
		<tr>
			<td class="field{if $field.Key == 'keyword_search'} top{/if}">
				<div title="{$lang[$field.pName]} {if $field.Condition == 'years' || $field.Type == 'price'}{$lang.from}{/if}">{$lang[$field.pName]} {if $field.Condition == 'years' || $field.Type == 'price'}{$lang.from}{/if}</div>
			</td>
			<td class="value">
	{/if}

	{if $field.Type == 'text'}
		{if $field.Key|strpos:'zip' !== false && $aHooks.search_by_distance}
			<select name="f[{$field.Key}][distance]" class="w50 rmargin">
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
			<input {if $fVal.$fKey.zip}value="{$fVal.$fKey.zip}"{/if} class="numeric w50" type="text" name="f[{$field.Key}][zip]" size="{if $field.Values}{$field.Values|count_characters}{else}10{/if}" maxlength="10" />
			{$lang.sbd_zipcode}
		{else}
			<input {if !$wide_mode}class="w150"{elseif $advanced}class="w240"{/if} type="text" name="f[{$field.Key}]" maxlength="{if $field.Values != ''}{$field.Values}{else}255{/if}" {if $fVal.$fKey}value="{$fVal.$fKey}"{/if} />
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
		{/if}
	{elseif $field.Type == 'number'}
		{if $field.Key|strpos:'zip' !== false && $aHooks.search_by_distance}
			<select name="f[{$field.Key}][distance]" class="w50 rmargin">
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
			<input {if $fVal.$fKey.zip}value="{$fVal.$fKey.zip}"{/if} class="numeric w50" type="text" name="f[{$field.Key}][zip]" size="{if $field.Values}{$field.Values|count_characters}{else}10{/if}" maxlength="10" />
			{$lang.sbd_zipcode}
		{elseif $field.Key|strpos:'zip' != false && !$aHooks.search_by_distance}
			<input {if $fVal.$fKey.from}value="{$fVal.$fKey.from}"{/if} class="numeric w60" type="text" name="f[{$field.Key}][from]" size="{if $field.Values}{$field.Values|count_characters}{else}10{/if}" maxlength="{if $field.Values}{$field.Values|count_characters}{else}10{/if}" />
		{else}
			<input value="{if $fVal.$fKey.from}{$fVal.$fKey.from}{else}{$lang.from}{/if}" class="numeric w60 field_from" type="text" name="f[{$field.Key}][from]" maxlength="{if $field.Values}{$field.Values}{else}18{/if}" /><img alt="" src="{$rlTplBase}img/blank.gif" class="between" /><input value="{if $fVal.$fKey.to}{$fVal.$fKey.to}{else}{$lang.to}{/if}" class="numeric w60 field_to" type="text" name="f[{$field.Key}][to]" maxlength="{if $field.Values}{$field.Values}{else}18{/if}" />
		{/if}
	{elseif $field.Type == 'date'}
		{if $field.Default == 'multi'}
			<input class="date" type="text" id="date_{$field.Key}{if $postfix}_{$postfix}{/if}" name="f[{$field.Key}]" maxlength="10" value="{$fVal.$fKey}" />
			<div class="clear"></div>
			<script type="text/javascript">
			{literal}
			$(document).ready(function(){
				$('#date_{/literal}{$field.Key}{if $postfix}_{$postfix}{/if}{literal}').datepicker({showOn: 'both', buttonImage: '{/literal}{$rlTplBase}{literal}img/blank.gif', buttonImageOnly: true, dateFormat: 'yy-mm-dd'}).datepicker($.datepicker.regional['{/literal}{$smarty.const.RL_LANG_CODE}{literal}']);
			});
			{/literal}
			</script>
		{elseif $field.Default == 'single'}
			{$postfix}
			<input class="date" type="text" id="date_{$field.Key}_from{if $postfix}_{$postfix}{/if}" name="f[{$field.Key}][from]" maxlength="10" value="{$fVal.$fKey.from}" /><img alt="" src="{$rlTplBase}img/blank.gif" class="between" /><input class="date" type="text" id="date_{$field.Key}_to{if $postfix}_{$postfix}{/if}" name="f[{$field.Key}][to]" maxlength="10" value="{$fVal.$fKey.to}" />
			<script type="text/javascript">
			{literal}
			$(document).ready(function(){
				$('#date_{/literal}{$field.Key}_from{if $postfix}_{$postfix}{/if}{literal}').datepicker({showOn: 'both', buttonImage: '{/literal}{$rlTplBase}{literal}img/blank.gif', buttonImageOnly: true, dateFormat: 'yy-mm-dd'}).datepicker($.datepicker.regional['{/literal}{$smarty.const.RL_LANG_CODE}{literal}']);
				$('#date_{/literal}{$field.Key}_to{if $postfix}_{$postfix}{/if}{literal}').datepicker({showOn: 'both', buttonImage: '{/literal}{$rlTplBase}{literal}img/blank.gif', buttonImageOnly: true, dateFormat: 'yy-mm-dd'}).datepicker($.datepicker.regional['{/literal}{$smarty.const.RL_LANG_CODE}{literal}']);
			});
			{/literal}
			</script>
		{/if}
	{elseif $field.Type == 'mixed'}
		<input value="{if $fVal.$fKey.from}{$fVal.$fKey.from}{else}{$lang.from}{/if}" class="numeric w60 field_from" type="text" name="f[{$field.Key}][from]" maxlength="15" /><img alt="" src="{$rlTplBase}img/blank.gif" class="between" /><input value="{if $fVal.$fKey.to}{$fVal.$fKey.to}{else}{$lang.to}{/if}" class="numeric w60 field_to" type="text" name="f[{$field.Key}][to]" maxlength="15" />
		
		{$lang.unit} <select name="f[{$field.Key}][df]" class="w80">
			<option value="0">{$lang.any}</option>
			{if !empty($field.Condition)}
				{assign var='df_source' value=$field.Condition|df}
			{else}
				{assign var='df_source' value=$field.Values}
			{/if}
			{foreach from=$df_source item='df_item'}
				<option value="{$df_item.Key}" {if $df_item.Key == $fVal.$fKey.df}selected="selected"{/if}>{$lang[$df_item.pName]}</option>
			{/foreach}
		</select>
	{elseif $field.Type == 'price'}
		<input {if $fVal.$fKey.from}value="{$fVal.$fKey.from}"{/if} class="numeric w60 field_from" type="text" name="f[{$field.Key}][from]" maxlength="15" />
		{$lang.to}
		<input {if $fVal.$fKey.to}value="{$fVal.$fKey.to}"{/if} class="numeric field_to w60" type="text" name="f[{$field.Key}][to]" maxlength="15" />
		
		{if $items_count < 5}
			{if $config.search_fields_position == 2}
				</td>
			</tr>
			<tr>
				<td class="field">
					<div>{$lang.currency}</div>
				</td>
				<td class="value">
			{else}
				{$lang.currency}
			{/if}
			<select name="f[{$field.Key}][currency]" class="w80{if $config.search_fields_position == 1} lmargin{/if}">
				<option value="0">{$lang.any}</option>
				{foreach from='currency'|df item='currency_item'}
					<option value="{$currency_item.Key}" {if $currency_item.Key == $fVal.$fKey.currency}selected="selected"{/if}>{$lang[$currency_item.pName]}</option>
				{/foreach}
			</select>
		{else}
			<select title="{$lang.currency}" name="f[{$field.Key}][currency]" class="{if $config.search_fields_position == 1}lmargin{/if}" style="width: 70px;">
				<option value="0">{$lang.any}</option>
				{foreach from='currency'|df item='currency_item'}
					<option value="{$currency_item.Key}" {if $currency_item.Key == $fVal.$fKey.currency}selected="selected"{/if}>{$lang[$currency_item.pName]}</option>
				{/foreach}
			</select>
		{/if}
	{elseif $field.Type == 'bool'}
		<input id="{$field.Key}_1{if $postfix}_{$postfix}{/if}" type="radio" value="on" name="f[{$field.Key}]" {if $fVal.$fKey == 'on'}checked="checked"{/if} /> <label for="{$field.Key}_1{if $postfix}_{$postfix}{/if}">{$lang.yes}</label>
		<input id="{$field.Key}_0{if $postfix}_{$postfix}{/if}" type="radio" value="off" name="f[{$field.Key}]" {if $fVal.$fKey == 'off'}checked="checked"{/if}/> <label for="{$field.Key}_0{if $postfix}_{$postfix}{/if}">{$lang.no}</label>
	{elseif $field.Type == 'select'}
		{rlHook name='tplSearchFieldSelect'}
		{assign var="multicat_listing_type" value=$group.Listing_type}
		{if $field.Condition == 'years'}
			<select name="f[{$field.Key}][from]" class="w80">
				<option value="0">{$lang.any}</option>
				{foreach from=$field.Values item='option' key='key'}
					{if $field.Condition}
						{assign var='key' value=$option.Key}
					{/if}
					<option {if $fVal.$fKey.from}{if $fVal.$fKey.from == $key}selected="selected"{/if}{/if} value="{if $field.Condition}{$option.Key}{else}{$key}{/if}">{$option.name}</option>
				{/foreach}
			</select>
			{$lang.to}
			<select name="f[{$field.Key}][to]" class="w80">
				<option value="0">{$lang.any}</option>
				{foreach from=$field.Values item='option' key='key'}
					{if $field.Condition}
						{assign var='key' value=$option.Key}
					{/if}
					<option {if $fVal.$fKey.to}{if $fVal.$fKey.to == $key}selected="selected"{/if}{/if} value="{if $field.Condition}{$option.Key}{else}{$key}{/if}">{$option.name}</option>
				{/foreach}
			</select>
		{elseif $field.Key == 'Category_ID' && $listing_types[$multicat_listing_type].Search_multi_categories}
			{assign var="levels_number" value=$listing_types[$multicat_listing_type].Search_multicat_levels}			

			<input type="hidden" id="{$post_form_key}_{$field.Key}_{$multicat_listing_type}_value" name="f[Category_ID]" value="{$fVal.$fKey}"/>
			<select id="{$post_form_key}_{$field.Key}_{$multicat_listing_type}_level0" {if $levels_number == 2}style="width:120px"{/if} class="multicat">
				<option value="0">{$lang.any}</option>
				{foreach from=$field.Values item='option' key='key'}
					<option {if $fVal.$fKey == $option.ID}selected="selected"{/if} value="{$option.ID}">{$lang[$option.pName]}</option>
				{/foreach}
			</select>

			{section name=multicat start=1 loop=$levels_number step=1}
				<select id="{$post_form_key}_{$field.Key}_{$multicat_listing_type}_level{$smarty.section.multicat.index}" disabled="disabled" {if $levels_number == 2}style="width:120px"{/if} class="multicat{if $smarty.section.multicat.last} last{/if}">
					<option value="0">{$lang.any}</option>
				</select>
			{/section}
		{else}
			<select name="f[{$field.Key}]">
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
		{if $item.Opt2}
			{assign var='col_num' value=$item.Opt2}
		{else}
			{assign var='col_num' value=3}
		{/if}		
		{assign var='fDefault' value=$field.Default}
		<input type="hidden" name="f[{$field.Key}][0]" value="0" />
		<table {if $advanced && $field.Values|@count > 2}class="fixed" style="width: 300px;"{/if}>
		<tr>
		{foreach from=$field.Values item='option' key='key' name='checkboxF'}
			{if $field.Condition}
				{assign var='key' value=$option.Key}
			{/if}
			<td valign="top">
				<input type="checkbox" id="{$field.Key}_{$key}{if $postfix}_{$postfix}{/if}" value="{$key}" name="f[{$field.Key}][{$key}]" {if is_array($fVal.$fKey)}{foreach from=$fVal.$fKey item='chVals'}{if $chVals == $key}checked="checked"{/if}{/foreach}{/if} /> <label for="{$field.Key}_{$key}{if $postfix}_{$postfix}{/if}">{$lang[$option.pName]}</label>
			</td>
			{if $smarty.foreach.checkboxF.iteration%$col_num == 0 && $smarty.foreach.checkboxF.total > $smarty.foreach.checkboxF.iteration}
			</tr>
			<tr>
			{/if}
		{/foreach}
		</tr>
		</table>
	{elseif $field.Type == 'radio'}
		<input type="hidden" value="0" name="f[{$field.Key}]" />
		<table {if $advanced && $field.Values|@count > 2}class="fixed" style="width: 300px;"{/if}>
		<tr>
			{foreach from=$field.Values item='option' key='key' name='radioF'}
				{if $field.Condition}
					{assign var='key' value=$option.Key}
				{/if}
				<td valign="top">
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
	
	{if $config.search_fields_position == 2}
		</td>
	</tr>	
	{/if}

{/foreach}

{if $config.search_fields_position == 2}
</table>
{else}
</div>
{/if}

<!-- fields block ( for search ) end -->
