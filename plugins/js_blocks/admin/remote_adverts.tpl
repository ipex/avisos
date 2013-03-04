<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}jquery/colorpicker/js/colorpicker.js"></script>
<link href="{$smarty.const.RL_LIBS_URL}jquery/colorpicker/css/colorpicker.css" type="text/css" rel="stylesheet" />
<script type="text/javascript" src="{$smarty.const.RL_PLUGINS_URL}js_blocks/static/lib.js"></script>

{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_start.tpl'}
	<table class="form">
		<tr>
			<td class="name">
				{$lang.jl_owner}
			</td>
			<td class="field">
				<input type="text" maxlength="255" id="Account" />
			</td>
		</tr>
		{if $listing_types|@count > 1}
		<tr>
			<td class="name">
				{$lang.jl_listing_types}
			</td>
			<td class="field">
				<select name="listing_type" >
					<option value="0">{$lang.all}</option>
					{foreach from=$listing_types key="key" item="listing_type" name="ltLoop"}
						<option value="{$listing_type.Key}" {if $smarty.foreach.ltLoop.first}selected="selected"{/if}>{$listing_type.name}</option>
					{/foreach}
				</select>
			</td>
		</tr>
		{/if}
		<tr>
			<td class="name">
				{$lang.category}
			</td>
			<td class="field">
				{assign var="levels_number" value=2}
				<input type="hidden" id="category_value" name="category_id" value="{$fVal.$fKey}"/>
				<select id="category_level0" {if $levels_number == 2}style="width:120px"{/if} class="multicat">
					<option value="0">{$lang.any}</option>
					{foreach from=$categories item='option' key='key'}
						<option {if $fVal.$fKey == $option.ID}selected="selected"{/if} value="{$option.ID}">{$lang[$option.pName]}</option>
					{/foreach}
				</select>
				{section name=multicat start=1 loop=$levels_number step=1}
					<select id="category_level{$smarty.section.multicat.index}" disabled="disabled" {if $levels_number == 2}style="width:120px"{/if} class="multicat{if $smarty.section.multicat.last} last{/if}">
						<option value="0">{$lang.any}</option>
					</select>
				{/section}
			</td>
		</tr>
		<tr>
			<td class="name">
				{$lang.jl_per_page}
			</td>
			<td class="field" >
				<input type="text" class="w60" name="per_page" maxlength="5" value="5">
			</td>
		</tr>
		<tr>
			<td class="name">
				{$lang.jl_limit}
			</td>
			<td class="field" >
				<input type="text" class="w60" name="limit" maxlength="5" value="10">
			</td>
		</tr>	
		<tr>
			<td class="name">
				{$lang.jl_box_styling}
			</td>
			<td class="field">
				<fieldset class="light">
				<legend id="legend_box_styling" class="down" onclick="fieldset_action('box_styling');">{$lang.jl_box_styling}</legend>
					<div id="box_styling" class="hide">
						<!-- box styling -->
						<table class="form" id="jParams">
						<tr>
							<td class="name">
								{$lang.jl_img_width}
							</td>
							<td class="field">
								<input type="text" class="w60" abbr="img|jListingImg|width" name="conf_img_width" maxlength="5">
								<select style="width:50px">
									<option value="px">px</option>
									<option value="%">%</option>
								</select>
							</td>
						</tr>
						<tr>
							<td class="name">
								{$lang.jl_img_height}
							</td>
							<td class="field">
								<input type="text" class="w60" abbr="img|jListingImg|height" name="conf_img_height" maxlength="5">
								<select style="width:50px">
									<option value="px">px</option>
									<option value="%">%</option>
								</select>
							</td>
						</tr>
						<tr>
							<td class="name">
								{$lang.jl_advert_bg}
							</td>
							<td class="field">
								<input type="hidden" name="conf_advert_bg" abbr="li|jListingItem|background" value="{$sPost.color}" />
								<div id="conf_advert_bg_picker" class="colorSelector"><div style="background-color: #{if $sPost.color}{$sPost.color}{else}fff{/if}"></div></div>
							</td>
						</tr>
						<tr>
							<td class="name">
								{$lang.jl_field_first_color}
							</td>
							<td class="field">
								<input type="hidden" name="conf_field_first_color" abbr="span|jListingFirst|color" value="{$sPost.color}" />
								<div id="conf_field_first_color_picker" class="colorSelector"><div style="background-color: #{if $sPost.color}{$sPost.color}{else}3a5f9c{/if}"></div></div>
							</td>
						</tr>
						<tr>
							<td class="name">
								{$lang.jl_field_color}
							</td>
							<td class="field">
								<input type="hidden" name="conf_field_color" abbr="span|jListingValue|color" value="{$sPost.color}" />
								<div id="conf_field_color_picker" class="colorSelector"><div style="background-color: #{if $sPost.color}{$sPost.color}{else}666666{/if}"></div></div>
							</td>
						</tr>
						<tr>
							<td class="name">
								{$lang.jl_field_names}
							</td>
							<td class="field" id="field_names_switch">
								<label>
									<input type="radio" value="1" name="fn_switch" />
									{$lang.enabled}
								</label>
								<label>
									<input type="radio" value="0" name="fn_switch" checked="checked" />
									{$lang.disabled}
								</label>
							</td>
						</tr>
						<tr id="field_names_color_cont" class="hide">
							<td class="name">
								{$lang.jl_field_names_color}
							</td>
							<td class="param">
								<div style="padding: 0 0 5px 0;">
									<input type="hidden" name="conf_field_names_color" abbr="span|jListingField|color" value="{$sPost.color}" />
									<div id="conf_field_names_color_picker" class="colorSelector"><div style="background-color: #{if $sPost.color}{$sPost.color}{else}444444{/if}"></div></div>
								</div>
							</td>
						</tr>
						</table>
						<!-- box styling end -->
					</div>
				</fieldset>
			</td>
		</tr>
		<tr>
			<td class="name">
				{$lang.jl_box_code}
			</td>
			<td class="field">
				{assign var="custom_id" value="?custom_id="|cat:$box_id}
				<table class="sTable">
				<tr>
					<td style="width:50%" valign="top">
						<fieldset>
						<legend id="legend_box_preview" class="down" onclick="fieldset_action('box_preview');">{$lang.jl_box_preview}</legend>
							<div id="box_preview">{$out|replace:"[aurl]":$custom_id}</div>
						</fieldset>
					</td>
					<td style="width:50%;padding-left:10px" valign="top" >
						<fieldset>
						<legend id="legend_box_code" class="down" onclick="fieldset_action('box_code');">{$lang.jl_box_code}</legend>
							<div id="box_code"><textarea cols="5" rows="5" style="width:90%" id="jCodeOut">{$out|replace:"[aurl]":$custom_id}</textarea></div>
						</fieldset>
					</td>
				</tr>
				</table>
			</td>
		</tr>
	</table>
{include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_end.tpl'}

<script type="text/javascript">
	{literal}
		$('#Account').rlAutoComplete({add_id: true});
	{/literal}

	var bg_color = '{if $sPost.color}{$sPost.color}{else}d8cfc4{/if}';
	var url = '{$smarty.const.RL_PLUGINS_URL}js_blocks/blocks.inc.php?custom_id={$box_id}';
	var acurl = '?custom_id={$box_id}';
	var aurl = '';
	var adurl = new Array();
	var iout = '{$out|replace:"</script>":"<\/script>"}';
</script>
