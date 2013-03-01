<!-- refine search field -->

{foreach from=$fields item='field'}
	{assign var='fKey' value=$field.Key}
	{assign var='fVal' value=$smarty.post}

	<div class="name">
		<img alt="" class="point" src="{$rlTplBase}img/blank.gif" />
		{$lang[$field.pName]}
	</div>

	<div class="value">
	{if $field.Type == 'text'}
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
			<input {if $fVal.$fKey.zip}value="{$fVal.$fKey.zip}"{/if} class="numeric w50" type="text" name="f[{$field.Key}][zip]" size="{if $field.Values}{$field.Values|count_characters}{else}10{/if}" maxlength="10" />
			{$lang.sbd_zipcode}
		{else}
			<input class="{if $wide_mode}w180{else}w130{/if}" type="text" name="f[{$field.Key}]" maxlength="{if $field.Values != ''}{$field.Values}{else}255{/if}" {if $fVal.$fKey}value="{$fVal.$fKey}"{/if} />
			{if $field.Key == 'keyword_search'}
				<div class="keyword_search_opt">
					<div>
						{assign var='tmp' value=3}
						{section name='keyword_opts' loop=$tmp max=3}
							<label><input {if $fVal.keyword_search_type}{if $smarty.section.keyword_opts.iteration == $fVal.keyword_search_type}checked="checked"{/if}{else}{if $smarty.section.keyword_opts.iteration == 2}checked="checked"{/if}{/if} value="{$smarty.section.keyword_opts.iteration}" type="radio" name="f[keyword_search_type]" /> {assign var='ph' value='keyword_search_opt'|cat:$smarty.section.keyword_opts.iteration}{$lang.$ph}</label>
						{/section}
					</div>
				</div>
				<a id="refine_keyword_opt" class="dotted" href="javascript:void(0)">{$lang.advanced_options}</a>
				<script type="text/javascript">
				{literal}
				
				$(document).ready(function(){
					$('#refine_keyword_opt').click(function(){
						$(this).prev().slideToggle();
					});
				});
				
				{/literal}
				</script>
			{/if}
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
			<input {if $fVal.$fKey.zip}value="{$fVal.$fKey.zip}"{/if} class="numeric w50" type="text" name="f[{$field.Key}][zip]" size="{if $field.Values}{$field.Values|count_characters}{else}10{/if}" maxlength="10" />
			{$lang.sbd_zipcode}
		{elseif $field.Key|strpos:'zip' != false && !$aHooks.search_by_distance}
			<input {if $fVal.$fKey.from}value="{$fVal.$fKey.from}"{/if} class="numeric w50" type="text" name="f[{$field.Key}][from]" size="{if $field.Values}{$field.Values|count_characters}{else}10{/if}" maxlength="{if $field.Values}{$field.Values|count_characters}{else}10{/if}" />
		{else}
			<input value="{if $fVal.$fKey.from}{$fVal.$fKey.from}{else}{$lang.from}{/if}" class="numeric field_from w50" type="text" name="f[{$field.Key}][from]" maxlength="{if $field.Values}{$field.Values}{else}18{/if}" /><img class="between" alt="" src="{$rlTplBase}img/blank.gif" /><input value="{if $fVal.$fKey.to}{$fVal.$fKey.to}{else}{$lang.to}{/if}" class="numeric field_to w50" type="text" name="f[{$field.Key}][to]" maxlength="{if $field.Values}{$field.Values}{else}18{/if}" />
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
			<input class="date w70" type="text" id="date_{$field.Key}_from{if $postfix}_{$postfix}{/if}" name="f[{$field.Key}][from]" maxlength="10" value="{$fVal.$fKey.from}" /><img alt="" src="{$rlTplBase}img/blank.gif" class="between" /><input class="date w70" type="text" id="date_{$field.Key}_to{if $postfix}_{$postfix}{/if}" name="f[{$field.Key}][to]" maxlength="10" value="{$fVal.$fKey.to}" />
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
		<input value="{if $fVal.$fKey.from}{$fVal.$fKey.from}{else}{$lang.from}{/if}" class="numeric field_from w50" type="text" name="f[{$field.Key}][from]" maxlength="15" /><img alt="" src="{$rlTplBase}img/blank.gif" class="between" /><input value="{if $fVal.$fKey.to}{$fVal.$fKey.to}{else}{$lang.to}{/if}" class="numeric field_to w50" type="text" name="f[{$field.Key}][to]" maxlength="15" />
		<div style="padding: 5px 0 0 0;">
			{$lang.unit} <select name="f[{$field.Key}][df]" class="w70">
				<option value="0">{$lang.any}</option>
				{if !empty($field.Condition)}
					{assign var='df_source' value=$field.Condition|df}
				{else}
					{assign var='df_source' value=$field.Values}
				{/if}
				{foreach from=$df_source item='df_item'}
					<option value="{$df_item.Key}" {if ($df_item.Key == $fVal.$fKey.df)}selected="selected"{/if}>{$lang[$df_item.pName]}</option>
				{/foreach}
			</select>
		</div>
	{elseif $field.Type == 'price'}
		<input value="{if $fVal.$fKey.from}{$fVal.$fKey.from}{else}{$lang.from}{/if}" class="numeric field_from w50" type="text" name="f[{$field.Key}][from]" maxlength="15" /><img alt="" src="{$rlTplBase}img/blank.gif" class="between" /><input value="{if $fVal.$fKey.to}{$fVal.$fKey.to}{else}{$lang.to}{/if}" class="numeric field_to w50" type="text" name="f[{$field.Key}][to]" maxlength="15" />
		</div>
		<div class="name"><img alt="" class="point" src="{$rlTplBase}img/blank.gif" />{$lang.currency}</div>
		<div class="value">
			<select name="f[{$field.Key}][currency]" class="w70">
				<option value="0">{$lang.any}</option>
				{foreach from='currency'|df item='currency_item'}
					<option value="{$currency_item.Key}" {if $currency_item.Key == $fVal.$fKey.currency}selected="selected"{/if}>{$lang[$currency_item.pName]}</option>
				{/foreach}
			</select>
	{elseif $field.Type == 'bool'}
		<label><input type="radio" value="on" name="f[{$field.Key}]" {if $fVal.$fKey == 'on'}checked="checked"{/if} /> {$lang.yes}</label>
		<label><input type="radio" value="off" name="f[{$field.Key}]" {if $fVal.$fKey == 'off'}checked="checked"{/if}/> {$lang.no}</label>
	{elseif $field.Type == 'select'}
		{rlHook name='tplSearchFieldSelect'}
		{assign var="multicat_listing_type" value=$group.Listing_type}
		{if $field.Condition == 'years'}
			<select name="f[{$field.Key}][from]" class="w70">
				<option value="0">{$lang.from}</option>
				{foreach from=$field.Values item='option' key='key'}
					{if $field.Condition}
						{assign var='key' value=$option.Key}
					{/if}
					<option {if $fVal.$fKey.from}{if $fVal.$fKey.from == $key}selected="selected"{/if}{/if} value="{if $field.Condition}{$option.Key}{else}{$key}{/if}">{$option.name}</option>
				{/foreach}
			</select><img alt="" src="{$rlTplBase}img/blank.gif" class="between" /><select name="f[{$field.Key}][to]" class="w70">
				<option value="0">{$lang.to}</option>
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
			<select id="{$post_form_key}_{$field.Key}_{$multicat_listing_type}_level0" class="multicat{if $levels_number == 2} w100{/if}">
				<option value="0">{$lang.any}</option>
				{foreach from=$field.Values item='option' key='key'}
					<option {if $fVal.$fKey == $option.ID}selected="selected"{/if} value="{$option.ID}">{$lang[$option.pName]}</option>
				{/foreach}
			</select>

			{section name=multicat start=1 loop=$levels_number step=1}
				<select id="{$post_form_key}_{$field.Key}_{$multicat_listing_type}_level{$smarty.section.multicat.index}" disabled="disabled" class="multicat{if $levels_number == 2} w100{/if}{if $smarty.section.multicat.last} last{/if}">
					<option value="0">{$lang.any}</option>
				</select>
			{/section}
		{else}
			<select name="f[{$field.Key}]" class="{if $wide_mode}w180{else}w130{/if}">
				<option value="0">{$lang.any}</option>
				{foreach from=$field.Values item='option' key='key'}
					{if $field.Condition}
						{assign var='key' value=$option.Key}
					{elseif $field.Key == 'Category_ID'}
						{assign var='key' value=$option.ID}
					{/if}
					<option {if $field.Key == 'Category_ID'}style="padding-{$text_dir}: {$option.margin}px;"{/if} {if $field.Key == 'Category_ID' && $option.Level == '0'}class="highlight_option"{/if} {if isset($fVal.$fKey)}{if $fVal.$fKey == $key}selected="selected"{/if}{elseif $option.Default}selected="selected"{/if} value="{if $field.Key == 'Category_ID'}{$option.ID}{else}{if $field.Condition}{$option.Key}{else}{$key}{/if}{/if}">{$lang[$option.pName]}</option>
				{/foreach}
			</select>
		{/if}
	{elseif $field.Type == 'checkbox'}
		<input type="hidden" name="f[{$field.Key}][0]" value="0" />
		<table class="sTable" style="table-layout: fixed;">
		<tr>
		{foreach from=$field.Values item='option' key='key' name='checkboxF'}
			{if $field.Condition}
				{assign var='key' value=$option.Key}
			{/if}
			<td valign="top">
				<label><input type="checkbox" value="{$key}" name="f[{$field.Key}][{$key}]" {if is_array($fVal.$fKey)}{foreach from=$fVal.$fKey item='chVals'}{if $chVals == $key}checked="checked"{/if}{/foreach}{/if} /> {$lang[$option.pName]}</label>
			</td>
			{if $smarty.foreach.checkboxF.iteration%2 == 0 && !$smarty.foreach.checkboxF.last}
			</tr>
			<tr>
			{/if}
		{/foreach}
		</tr>
		</table>
	{elseif $field.Type == 'radio'}
		<input type="hidden" value="0" name="f[{$field.Key}]" />
		<table class="sTable" style="table-layout: fixed;">
		<tr>
			{foreach from=$field.Values item='option' key='key' name='radioF'}
				{if $field.Condition}
					{assign var='key' value=$option.Key}
				{/if}
				<td valign="top">
					<label><input type="radio" id="{$field.Key}_{$key}{if $postfix}_{$postfix}{/if}" value="{$key}" name="f[{$field.Key}]" {if $fVal.$fKey}{if $fVal.$fKey == $key}checked="checked"{/if}{/if} /> {$lang[$option.pName]}</label>
				</td>
				{if $smarty.foreach.radioF.iteration%2 == 0 && !$smarty.foreach.radioF.last}
				</tr>
				<tr>
				{/if}
			{/foreach}
		</tr>
		</table>
	{/if}
	</div>

{/foreach}
<!-- refine search field end -->
