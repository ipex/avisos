<!-- add/edit new field -->

{assign var='sPost' value=$smarty.post}

{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_start.tpl'}

<form action="{$rlBaseC}action={if $smarty.get.action == 'add'}add{elseif $smarty.get.action == 'edit'}edit&amp;field={$smarty.get.field}{/if}" method="post">
	<input type="hidden" name="submit" value="1" />
	{if $smarty.get.action == 'edit'}
		<input type="hidden" name="fromPost" value="1" />
	{/if}
	<table class="form">
	<tr>
		<td class="name"><span class="red">*</span>{$lang.key}</td>
		<td class="field">
			<input {if $smarty.get.action == 'edit'}readonly="readonly"{/if} class="{if $smarty.get.action == 'edit'}disabled{/if}" name="key" type="text" style="width: 150px;" value="{$sPost.key}" maxlength="30" />
		</td>
	</tr>
	
	<tr>
		<td class="name"><span class="red">*</span>{$lang.name}</td>
		<td class="field">
			{if $allLangs|@count > 1}
				<ul class="tabs">
					{foreach from=$allLangs item='language' name='langF'}
					<li lang="{$language.Code}" {if $smarty.foreach.langF.first}class="active"{/if}>{$language.name}</li>
					{/foreach}
				</ul>
			{/if}
			
			{foreach from=$allLangs item='language' name='langF'}
				{if $allLangs|@count > 1}<div class="tab_area{if !$smarty.foreach.langF.first} hide{/if} {$language.Code}">{/if}
				<input type="text" name="name[{$language.Code}]" value="{$sPost.name[$language.Code]}" maxlength="350" />
				{if $allLangs|@count > 1}
						<span class="field_description_noicon">{$lang.name} (<b>{$language.name}</b>)</span>
					</div>
				{/if}
			{/foreach}
		</td>
	</tr>
	
	<tr>
		<td class="name">{$lang.description}</td>
		<td class="field">
			{if $allLangs|@count > 1}
				<ul class="tabs">
					{foreach from=$allLangs item='language' name='langF'}
					<li lang="{$language.Code}" {if $smarty.foreach.langF.first}class="active"{/if}>{$language.name}</li>
					{/foreach}
				</ul>
			{/if}
			
			{foreach from=$allLangs item='language' name='langF'}
				{if $allLangs|@count > 1}<div class="tab_area{if !$smarty.foreach.langF.first} hide{/if} {$language.Code}">{/if}
				<textarea cols="" rows="" name="description[{$language.Code}]">{$sPost.description[$language.Code]}</textarea>
				{if $allLangs|@count > 1}</div>{/if}
			{/foreach}
		</td>
	</tr>
	
	<tr>
		<td class="name">{$lang.required_field}</td>
		<td>
			{if $sPost.required == '1'}
				{assign var='required_yes' value='checked="checked"'}
			{elseif $sPost.required == '0'}
				{assign var='required_no' value='checked="checked"'}
			{else}
				{assign var='required_no' value='checked="checked"'}
			{/if}
			<label><input {$required_yes} type="radio" name="required" value="1" /> {$lang.yes}</label>
			<label><input {$required_no} type="radio" name="required" value="0" /> {$lang.no}</label>
		</td>
	</tr>
	
	<tr>
		<td class="name">{$lang.google_map}</td>
		<td class="field">
			{if $sPost.map == '1'}
				{assign var='map_yes' value='checked="checked"'}
			{elseif $sPost.map == '0'}
				{assign var='map_no' value='checked="checked"'}
			{else}
				{assign var='map_no' value='checked="checked"'}
			{/if}
			
			<table>
			<tr>
				<td>
					<label><input {$map_yes} type="radio" name="map" value="1" /> {$lang.yes}</label>
					<label><input {$map_no} type="radio" name="map" value="0" /> {$lang.no}</label>
				</td>
				<td>
					<span class="field_description">{$lang.use_for_displaing_map}</span>
				</td>
			</tr>
			</table>
		</td>
	</tr>
	
	<tr>
		<td class="name">{$lang.show_on}</td>
		<td class="field">
			<label><input {if isset($sPost.add_page)}checked="checked"{else}{if empty($sPost)}checked="checked"{/if}{/if} type="checkbox" name="add_page" /> {$lang.add_page}</label>
			<label><input {if isset($sPost.details_page)}checked="checked"{else}{if empty($sPost)}checked="checked"{/if}{/if} type="checkbox" name="details_page" /> {$lang.details_page}</label>
		</td>
	</tr>
	
	{rlHook name='apTplFieldsForm'}
	
	<tr>
		<td class="name">{$lang.status}</td>
		<td class="field">
			<select name="status">
				<option value="active" {if $sPost.status == 'active'}selected="selected"{/if}>{$lang.active}</option>
				<option value="approval" {if $sPost.status == 'approval'}selected="selected"{/if}>{$lang.approval}</option>
			</select>
		</td>
	</tr>
	
	<tr>
		<td class="name"><span class="red">*</span>{$lang.field_type}</td>
		<td class="field">
			<select {if $smarty.get.action == 'edit'}disabled="disabled"{/if} onchange="field_types(this.value);" name="type" class="{if $smarty.get.action == 'edit'}disabled{/if}">
				<option value="">{$lang.select_field_type}</option>
				{foreach from=$l_types item='lType' key='key'}
					<option {if $sPost.type == $key}selected="selected"{/if} value="{$key}">{$lType}</option>
				{/foreach}
			</select>
			{if $smarty.get.action == 'edit'}
				<input type="hidden" name="type" value="{$sPost.type}" />
			{/if}
			
			{if $smarty.get.action == 'edit' && $field_info.Key|in_array:$sys_fields}
			<span class="field_description">{$lang.system_field_notice}</span>
			{/if}
		</td>
	</tr>
	</table>
	
	<!-- additional options -->
	<div id="additional_options">

	<script type="text/javascript">
	var langs_list = Array(
	{foreach from=$allLangs item='languages' name='lF'}
	'{$languages.Code}|{$languages.name}'{if !$smarty.foreach.lF.last},{/if}
	{/foreach}
	);
	</script>
	
	<!-- text field -->
	{assign var='textDefault' value=$sPost.text.default}
	<div id="field_text" class="hide">
		<table class="form">
		<tr>
			<td class="name">{$lang.default_value}</td>
			<td class="field">
				{if $allLangs|@count > 1}
					<ul class="tabs">
						{foreach from=$allLangs item='language' name='langF'}
						<li lang="{$language.Code}" {if $smarty.foreach.langF.first}class="active"{/if}>{$language.name}</li>
						{/foreach}
					</ul>
				{/if}
				
				{foreach from=$allLangs item='language' name='langF'}
					{if $allLangs|@count > 1}<div class="tab_area{if !$smarty.foreach.langF.first} hide{/if} {$language.Code}">{/if}
					<input type="text" name="text[default][{$language.Code}]" value="{$textDefault[$language.Code]}" maxlength="100" />
					{if $allLangs|@count > 1}
							<span class="field_description_noicon">{$lang.name} (<b>{$language.name}</b>)</span>
						</div>
					{/if}
				{/foreach}
			</td>
		</tr>
		
		{assign var='text_cond' value=$sPost.text}
		<tr>
			<td class="name">{$lang.check_condition}</td>
			<td class="field">
				<select name="text[condition]">
					<option value="">- {$lang.condition} -</option>
					{foreach from=$l_cond item='condition' key='cKey'}
						<option {if $text_cond.condition == $cKey}selected="selected"{/if} value="{$cKey}">{$condition}</option>
					{/foreach}
				</select>
			</td>
		</tr>
		</table>
		{if $allLangs|@count > 1}
		<div id="text_multilingual" {if $text_cond.condition}class="hide"{/if}>
			<table class="form">
			<tr>
				<td class="name">{$lang.multilingual}</td>
				<td class="field">
					{if $sPost.text.multilingual == '1'}
						{assign var='text_multilingual_yes' value='checked="checked"'}
					{elseif $sPost.text.multilingual == '0'}
						{assign var='text_multilingual_no' value='checked="checked"'}
					{else}
						{assign var='text_multilingual_no' value='checked="checked"'}
					{/if}
					
					<label><input {$text_multilingual_yes} type="radio" name="text[multilingual]" value="1" /> {$lang.yes}</label>
					<label><input {$text_multilingual_no} type="radio" name="text[multilingual]" value="0" /> {$lang.no}</label>
				</td>
			</tr>
			</table>
		</div>
		{/if}
		<table class="form">
		<tr>
			<td class="name">{$lang.maxlength}</td>
			<td class="field">
				<input class="numeric" name="text[maxlength]" type="text" style="width: 50px; text-align: center;" value="{$sPost.text.maxlength}" maxlength="3" /> <span class="field_description">{$lang.default_text_value_des}</span>
			</td>
		</tr>
		
		{rlHook name='apTplFieldsFormText'}
		
		</table>
		
		<script type="text/javascript">
		{literal}
		
		$(document).ready(function(){
			$('select[name="text[condition]"]').change(function(){
				var val = $(this).val();
				
				if ( val )
				{
					$('#text_multilingual').slideUp();
					$('input[name="text[multilingual]"][value=0]').prop('checked', true);
				}
				else
				{
					$('#text_multilingual').slideDown();
				}
			});
		});
		
		{/literal}
		</script>
	</div>
	<!-- text field end -->
	
	<!-- textarea field -->
	{assign var='textarea' value=$sPost.textarea}
	<div id="field_textarea" class="hide">
		<table class="form">
		<tr>
			<td class="name">{$lang.maxlength}</td>
			<td class="field">
				<input class="numeric" name="textarea[maxlength]" type="text" style="width: 50px; text-align: center;" value="{$textarea.maxlength}" maxlength="4" /> <span class="field_description">{$lang.default_textarea_value_des}</span>
			</td>
		</tr>
		{if $allLangs|@count > 1}
		<tr>
			<td class="name">{$lang.multilingual}</td>
			<td class="field">
				{if $sPost.textarea.multilingual == '1'}
					{assign var='multilingual_yes' value='checked="checked"'}
				{elseif $sPost.textarea.multilingual == '0'}
					{assign var='multilingual_no' value='checked="checked"'}
				{else}
					{assign var='multilingual_no' value='checked="checked"'}
				{/if}
				
				<label><input {$multilingual_yes} type="radio" name="textarea[multilingual]" value="1" /> {$lang.yes}</label>
				<label><input {$multilingual_no} type="radio" name="textarea[multilingual]" value="0" /> {$lang.no}</label>
			</td>
		</tr>
		{/if}
		<tr>
			<td class="name">{$lang.html_editor}</td>
			<td class="field">
				{if $sPost.textarea.html == '1'}
					{assign var='html_yes' value='checked="checked"'}
				{elseif $sPost.textarea.html == '0'}
					{assign var='html_no' value='checked="checked"'}
				{else}
					{assign var='html_no' value='checked="checked"'}
				{/if}
				
				<label><input {$html_yes} type="radio" name="textarea[html]" value="1" /> {$lang.yes}</label>
				<label><input {$html_no} type="radio" name="textarea[html]" value="0" /> {$lang.no}</label>
			</td>
		</tr>
		
		{rlHook name='apTplFieldsFormTextarea'}
		
		</table>
	</div>
	<!-- textarea field end -->
	
	<!-- number field -->
	{assign var='number' value=$sPost.number}
	<div id="field_number" class="hide">
		<table class="form">
		<tr>
			<td class="name">{$lang.maxlength}</td>
			<td class="field">
				<input class="numeric" name="number[max_length]" type="text" style="width: 60px; text-align: center;" value="{$number.max_length}" maxlength="8" />
				<span class="field_description">{$lang.number_field_length_hint}</span>
			</td>
		</tr>
		</tr>
		
		{rlHook name='apTplFieldsNumber'}
		
		</table>
	</div>
	<!-- number field end -->
	
	<!-- phone number field -->
	{assign var='phone' value=$sPost.phone}
	<div id="field_phone" class="hide">
		<table class="form">
		{rlHook name='apTplFieldsPhone'}
		
		<tr>
			<td class="name">{$lang.bind_data_format}</td>
			<td class="field">
				<select id="dd_phone_block" name="phone[condition]" class="data_format">
					<option value="0">{$lang.select}</option>
					{foreach from=$data_formats item='format'}
					<option value="{$format.Key}"{if $format.Key == $phone.condition} selected="selected"{/if}>{$format.name|strip_tags}</option>
					{/foreach}
				</select>
			</td>
		</tr>
		
		<tr>
			<td class="name">{$lang.field_format}</td>
			<td class="field_tall">
				<ul class="clear_list">
					<li><label><input type="checkbox" name="phone[code]" {if $phone.code}checked="checked"{/if} value="1" /> {$lang.phone_code}</label></li>
					<li id="phone_block" {if $phone.condition}class="hide"{/if}><input style="width: 20px;text-align: center;" type="text" name="phone[area_length]" value="{if $phone.area_length}{$phone.area_length}{else}3{/if}" maxlength="1" /> <label>{$lang.phone_area_length}</label></li>
					<li><input style="width: 20px;text-align: center;" type="text" name="phone[phone_length]" value="{if $phone.phone_length}{$phone.phone_length}{else}7{/if}" maxlength="1" /> <label>{$lang.phone_number_length}</label></li>
					<li><label><input type="checkbox" name="phone[ext]" {if $phone.ext}checked="checked"{/if} value="1" /> {$lang.phone_ext}</label></li>
				</ul>
			</td>
		</tr>
		<tr>
			<td class="name">{$lang.field_preview}</td>
			<td class="field">
				<div style="padding: 0 0 10px 0;">
					<span class="phone_code_prev hide">+ <input disabled="disabled" type="text" maxlength="4" style="width: 30px;text-align: center;" /> -</span>
					<input disabled="disabled" id="phone_area_input" type="text" maxlength="5" style="width: 40px;text-align: center;" />
					- <input disabled="disabled" id="phone_number_input" type="text" maxlength="9" style="width: 80px;" /></span>
					<span class="phone_ext hide">/ <input disabled="disabled" type="text" maxlength="4" style="width: 35px;" /></span>
				</div>
				<div>
					<span class="phone_code_prev hide">+ xxx</span>
					<span id="phone_area_preview">(xxx)</span>
					<span id="phone_number_preview">123-4567</span>
					<span class="phone_ext hide">{$lang.phone_ext_out}22</span>
				</div>
			</td>
		</tr>
		
		</table>
		
		<script type="text/javascript">flynax.phoneFieldControls();</script>
	</div>
	<!-- phone number field -->
	
	<!-- date field -->
	{assign var='date' value=$sPost.date}
	<div id="field_date" class="hide">
		<table class="form">
		<tr>
			<td class="name"><span class="red">*</span>{$lang.mode}</td>
			<td class="field">
				<label><input {if $date.mode == 'single'}checked="checked"{/if} type="radio" name="date[mode]" value="single" /> {$lang.single_date}</label>
				<label><input {if $date.mode == 'multi'}checked="checked"{/if} type="radio" name="date[mode]" value="multi" /> {$lang.time_period}</label>
			</td>
		</tr>
		
		{rlHook name='apTplFieldsDate'}
		
		</table>
	</div>
	<!-- date field end -->
	
	<!-- boolean field -->
	{if $sPost.bool.default == '1'}
		{assign var='bool_default_yes' value='checked="checked"'}
	{elseif $sPost.required == '0'}
		{assign var='bool_default_no' value='checked="checked"'}
	{else}
		{assign var='bool_default_no' value='checked="checked"'}
	{/if}
	<div id="field_bool" class="hide">
		<table class="form">
		<tr>
			<td class="name">{$lang.default_value}</td>
			<td class="field">
				<label><input {$bool_default_yes} type="radio" name="bool[default]" value="1" /> {$lang.yes}</label>
				<label><input {$bool_default_no} type="radio" name="bool[default]" value="0" /> {$lang.no}</label>
			</td>
		</tr>
		
		{rlHook name='apTplFieldsBool'}
		
		</table>
	</div>
	<!-- boolean field end -->
	
	<!-- mixed field -->
	<div id="field_mixed" class="hide">
		<script type="text/javascript">
		var mixed_step = 1;
		</script>
		<table class="form">
		
		{rlHook name='apTplFieldsMixed'}
		
		<tr>
			<td class="name">{$lang.bind_data_format}</td>
			<td class="field">
				<select id="dd_mixed_block" name="mixed_data_format" class="data_format">
					<option value="0">{$lang.select}</option>
					{foreach from=$data_formats item='format'}
					<option value="{$format.Key}"{if $format.Key == $sPost.mixed_data_format} selected="selected"{/if}>{$format.name|strip_tags}</option>
					{/foreach}
				</select>
			</td>
		</tr>
		</table>
		
		<div id="mixed_block" {if $sPost.mixed_data_format}class="hide"{/if}>
		<table class="form" style="margin: 10px 0 0;">
		<tr>
			<td class="name">{$lang.field_items}</td>
			<td class="field">
				<table id="mixed">
				{if $sPost.mixed}
					{foreach from=$sPost.mixed item='selectItem' key='selectKey'}
					{if $selectKey != 'default'}
					<tr id="mixed_{$selectKey}">
						<td>
							{foreach from=$allLangs item='languages' name='lang_foreach'}
								{assign var='lCode' value=$languages.Code}
								<div><input type="text" class="margin float" value="{$selectItem.$lCode}" name="mixed[{$selectKey}][{$languages.Code}]" /><span class="field_description">{$lang.item_value} <span class="green_10">(<b>{$languages.name}</b>)</span></span></div>
							{/foreach}
						</td>
						<td style="padding: 3px 10px 0 10px;">
							<input {if $sPost.mixed.default == $selectKey}checked="checked"{/if} id="mixed_def_{$selectKey}" type="radio" name="mixed[default]" value="{$selectKey}" /> <label for="mixed_def_{$selectKey}">{$lang.default}</label>
						</td>
						<td>
							<a class="delete_item" href="javascript:void(0)" onclick="$('#mixed_{$selectKey}').remove('');">{$lang.remove}</a>
							<script type="text/javascript">
							if (mixed_step <= {$selectKey})
								mixed_step = {$selectKey} + 1;
							</script>
						</td>
					</tr>
					{/if}
					{/foreach}
				{/if}
				</table>
				
				<div class="add_item"><a href="javascript:void(0)" onclick="field_build('mixed', langs_list );">{$lang.add_field_item}</a></div>
			</td>
		</tr>
		</table>
		</div>
	</div>
	<!-- mixed field end -->
	
	<!-- dropdown list field -->
	<div id="field_select" class="hide">
		<script type="text/javascript">
		var select_step = 1;
		</script>
		<table class="form">
		
		{rlHook name='apTplFieldsDropdown'}
		
		<tr>
			<td class="name">{$lang.bind_data_format}</td>
			<td class="field">
				<select id="dd_select_block" name="data_format" class="data_format">
					<option value="0">{$lang.select}</option>
					{foreach from=$data_formats item='format'}
					<option value="{$format.Key}"{if $format.Key == $sPost.data_format} selected="selected"{/if}>{$format.name|strip_tags}</option>
					{/foreach}
				</select>
			</td>
		</tr>
		</table>
		
		<div id="select_block" {if $sPost.data_format}class="hide"{/if}>
		<table class="form" style="margin: 10px 0 0;">
		<tr>
			<td class="name">{$lang.field_items}</td>
			<td class="field">
				<table id="select">
				{if $sPost.select}
					{foreach from=$sPost.select item='selectItem' key='selectKey'}
					{if $selectKey != 'default'}
					<tr id="select_{$selectKey}">
						<td>
							{foreach from=$allLangs item='languages' name='lang_foreach'}
								{assign var='lCode' value=$languages.Code}
								<div><input type="text" class="float margin" value="{$selectItem.$lCode}" name="select[{$selectKey}][{$languages.Code}]" /><span class="field_description">{$lang.item_value} <span class="green_10">(<b>{$languages.name}</b>)</span></span></div>
							{/foreach}
						</td>
						<td style="padding: 3px 10px 0 10px;">
							<input {if $sPost.select.default == $selectKey}checked="checked"{/if} id="select_def_{$selectKey}" type="radio" name="select[default]" value="{$selectKey}" /> <label for="select_def_{$selectKey}">{$lang.default}</label>
						</td>
						<td>
							<a class="delete_item" href="javascript:void(0)" onclick="$('#select_{$selectKey}').remove('');">{$lang.remove}</a>
							<script type="text/javascript">
							if (select_step <= {$selectKey})
								select_step = {$selectKey} + 1;
							</script>
						</td>
					</tr>
					{/if}
					{/foreach}
				{/if}
				</table>
				
				<div class="add_item"><a href="javascript:void(0)" onclick="field_build('select', langs_list );">{$lang.add_field_item}</a></div>
			</td>
		</tr>
		</table>
		</div>
	</div>
	<!-- dropdown list field end -->
	
	<!-- radio set field -->
	<div id="field_radio" class="hide">
		<script type="text/javascript">
		var radio_step = 1;
		</script>
		<table class="form">
		
		{rlHook name='apTplFieldsRadio'}
		
		<tr>
			<td class="name">{$lang.bind_data_format}</td>
			<td class="field">
				<select id="dd_radio_block" name="data_format" class="data_format margin">
					<option value="0">{$lang.select}</option>
					{foreach from=$data_formats item='format'}
					<option value="{$format.Key}"{if $format.Key == $sPost.data_format} selected="selected"{/if}>{$format.name|strip_tags}</option>
					{/foreach}
				</select>
			</td>
		</tr>
		</table>
		
		<div id="radio_block" {if $sPost.data_format}class="hide"{/if}>
		<table class="form" style="margin: 10px 0 0;">
		<tr>
			<td class="name">{$lang.field_items}</td>
			<td class="field">
				<table id="radio">
				{if $sPost.radio}
					{foreach from=$sPost.radio item='radioItem' key='radioKey'}
					{if $radioKey != 'default'}
					<tr id="radio_{$radioKey}">
						<td>
							{foreach from=$allLangs item='languages' name='lang_foreach'}
								{assign var='lCode' value=$languages.Code}
								<div><input type="text" class="float margin" value="{$radioItem.$lCode}" name="radio[{$radioKey}][{$languages.Code}]" /><span class="field_description">{$lang.item_value} <span class="green_10">(<b>{$languages.name}</b>)</span></span></div>
							{/foreach}
						</td>
						<td style="padding: 3px 10px 0 10px;">
							<input {if $sPost.radio.default == $radioKey}checked="checked"{/if} id="radio_def_{$radioKey}" type="radio" name="radio[default]" value="{$radioKey}" /> <label for="radio_def_{$radioKey}">{$lang.default}</label>
						</td>
						<td>
							<a class="delete_item" href="javascript:void(0)" onclick="$('#radio_{$radioKey}').remove('');">{$lang.remove}</a>
							<script type="text/javascript">
							if (radio_step <= {$radioKey})
								radio_step = {$radioKey} + 1;
							</script>
						</td>
					</tr>
					{/if}
					{/foreach}
				{/if}
				</table>
				
				<div class="add_item"><a href="javascript:void(0)" onclick="field_build('radio', langs_list );">{$lang.add_field_item}</a></div>
			</td>
		</tr>
		</table>
		</div>
	</div>
	<!-- radio set field end -->
	
	<!-- checkbox set field -->	
	<div id="field_checkbox" class="hide">
		<script type="text/javascript">
		var checkbox_step = 1;
		</script>
		<table class="form">
		
		{rlHook name='apTplFieldsCheckbox'}
		
		<tr>
			<td class="name">{$lang.number_of_columns}</td>
			<td>
				<input type="text" style="text-align: center;width: 40px;" maxlength="2" value="{if $sPost.column_number}{$sPost.column_number}{else}3{/if}" name="column_number" />
			</td>
		</tr>
		
		{if $cInfo.Controller == 'account_fields'}
			<input type="hidden" name="{$checkbox_field}" value="0" />
		{else}
		<tr>
			<td class="name">{$lang.show_all_options}</td>
			<td>
				{assign var='checkbox_field' value='show_tils'}
			
				{if $sPost.$checkbox_field == '1'}
					{assign var=$checkbox_field|cat:'_yes' value='checked="checked"'}
				{elseif $sPost.$checkbox_field == '0'}
					{assign var=$checkbox_field|cat:'_no' value='checked="checked"'}
				{else}
					{assign var=$checkbox_field|cat:'_yes' value='checked="checked"'}
				{/if}
				
				<input {$show_tils_yes} type="radio" id="{$checkbox_field}_yes" name="{$checkbox_field}" value="1" /> <label for="{$checkbox_field}_yes">{$lang.yes}</label>
				<input {$show_tils_no} type="radio" id="{$checkbox_field}_no" name="{$checkbox_field}" value="0" /> <label for="{$checkbox_field}_no">{$lang.no}</label>
				
				<span class="field_description">{$lang.show_all_options_hint}</span>
			</td>
		</tr>
		{/if}
		
		<tr>
			<td class="name">{$lang.bind_data_format}</td>
			<td>
				<select id="dd_checkbox_block" name="data_format" class="data_format">
					<option value="0">{$lang.select}</option>
					{foreach from=$data_formats item='format'}
					<option value="{$format.Key}"{if $format.Key == $sPost.data_format} selected="selected"{/if}>{$format.name|strip_tags}</option>
					{/foreach}
				</select>
			</td>
		</tr>
		</table>
		
		<div id="checkbox_block" {if $sPost.data_format}class="hide"{/if}>
		<table class="form" style="margin: 10px 0 0;">
		<tr>
			<td class="name">{$lang.field_items}</td>
			<td class="field">
				<table id="checkbox">
				{if $sPost.checkbox}
					{foreach from=$sPost.checkbox item='checkboxItem' key='checkboxKey'}
					{assign var='checkbox' value=$sPost.checkbox}
					{assign var='checkboxIter' value=$checkbox.$checkboxKey}
					{if $checkboxKey != 'default'}
					<tr id="checkbox_{$checkboxKey}">
						<td>
							{foreach from=$allLangs item='languages' name='lang_foreach'}
								{assign var='lCode' value=$languages.Code}
								<div><input type="text" class="margin float" value="{$checkboxItem.$lCode}" name="checkbox[{$checkboxKey}][{$languages.Code}]" /><span class="field_description">{$lang.item_value} <span class="green_10">(<b>{$languages.name}</b>)</span></span></div>
							{/foreach}
						</td>
						<td style="padding: 3px 10px 0 10px;">
							<input {if $checkboxIter.default == $checkboxKey}checked="checked"{/if} id="checkbox_def_{$checkboxKey}" type="checkbox" name="checkbox[{$checkboxKey}][default]" value="{$checkboxKey}" /> <label for="checkbox_def_{$checkboxKey}">{$lang.default}</label>
						</td>
						<td>
							<a class="delete_item" href="javascript:void(0)" onclick="$('#checkbox_{$checkboxKey}').remove('');">{$lang.remove}</a>
							<script type="text/javascript">
							if (checkbox_step <= {$checkboxKey})
								checkbox_step = {$checkboxKey} + 1;
							</script>
						</td>
					</tr>
					{/if}
					{/foreach}
				{/if}
				</table>
				
				<div class="add_item"><a href="javascript:void(0)" onclick="field_build('checkbox', langs_list );">{$lang.add_field_item}</a></div>
			</td>
		</tr>
		</table>
		</div>
	</div>
	<!-- checkbox set field end -->
	
	<!-- image field -->
	{assign var='image' value=$sPost.image}
	<div id="field_image" class="hide">
		<table class="form">
		<tr>
			<td class="name">{$lang.resize_type}</td>
			<td class="field">
				<select onchange="resize_action($(this).val());" name="image[resize_type]">
					<option value="">- {$lang.resize_type} -</option>
					{foreach from=$l_resize item='resize' key='resKey'}
						<option value="{$resKey}" {if $resKey == $sPost.image.resize_type}selected="selected"{/if}>{$resize}</option>
					{/foreach}
				</select>
			</td>
		</tr>
		
		<tr>
			<td class="name">{$lang.resolution}</td>
			<td class="field">
				<table>
				<tr>
					<td>{$lang.width}:</td>
					<td>
						<input readonly="readonly" id="resW" class="margin numeric disabled" name="image[width]" type="text" style="width: 40px; text-align: center;" value="{$sPost.image.width}" maxlength="4" />
					</td>
				</tr>
				<tr>
					<td>{$lang.height}:</td>
					<td>
						<input readonly="readonly" id="resH" class="margin numeric disabled" name="image[height]" type="text" style="width: 40px; text-align: center;" value="{$sPost.image.height}" maxlength="4" />
					</td>
				</tr>
				</table>
			</td>
		</tr>
		
		{rlHook name='apTplFieldsImage'}
		
		</table>
	</div>
	<!-- image field end -->
	
	<!-- file storage field -->
	{assign var='image' value=$sPost.image}
	<div id="field_file" class="hide">
		<table class="form">
		<tr>
			<td class="name"><span class="red">*</span>{$lang.file_type}</td>
			<td class="field">
				<select name="file[type]">
					<option value="">- {$lang.file_type} -</option>
					{foreach from=$l_file_types item='fTypes' key='ftKey'}
						<option value="{$ftKey}" {if $ftKey == $sPost.file.type}selected="selected"{/if}>{$fTypes.name} ({$fTypes.ext})</option>
					{/foreach}
				</select>
			</td>
		</tr>
		
		{rlHook name='apTplFieldsFile'}
		
		</table>
	</div>
	<!-- file storage field end -->
	
	<!-- agreement field -->
	<div id="field_accept" class="hide">
		<table class="form">
		
		{rlHook name='apTplFieldsAgreement'}
		
		{foreach from=$allLangs item='languages' name='lang_foreach'}
		{assign var='accept' value=$sPost.accept}
		{assign var='lCode' value=$languages.Code}
		<tr>
			<td class="name">
				<div style="margin-left: 10px;"><span class="red">*</span>{$lang.agreement_text} <span class="green_10">(<b>{$languages.name}</b>)</span></div>
			</td>
			<td class="field">
				<textarea rows="5" cols="" name="accept[{$languages.Code}]">{$accept.$lCode}</textarea>
			</td>
		</tr>
		{/foreach}
		</table>
	</div>
	<!-- agreement field -->
	
	{rlHook name='apTplFieldsFormBottom'}
	
	</div>
	<!-- additional options end -->
	
	{assign var='no_expand' value=false}
	{if $smarty.get.action == 'edit' && $field_info.Key|in_array:$sys_fields}
		{assign var='no_expand' value=true}
	{/if}
	
	<!-- additional JS -->
	{if $sPost.type != false && !$no_expand}
	<script type="text/javascript">
		field_types('{$sPost.type}');
	</script>	
	{/if}
	
	{if $sPost.image.resize_type}
	<script type="text/javascript">
		resize_action('{$sPost.image.resize_type}');
	</script>	
	{/if}
	<!-- additional JS end -->
	
	<table class="form">
	<tr>
		<td class="no_divider"></td>
		<td class="field">
			<input type="submit" value="{if $smarty.get.action == 'edit'}{$lang.edit}{else}{$lang.add}{/if}" />
		</td>
	</tr>
	</table>
</form>

{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_end.tpl'}

<!-- add/edit new field end -->