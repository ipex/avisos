{if $multi_formats[$field.Condition]}
	{if $post_form_key}
		{assign var="mf_form_key" value=$post_form_key}
		{assign var="mf_form_prefix" value=$post_form_key|cat:"_"}
	{/if}

	{if $field.Key|strpos:'level' === false}
		{assign var="last_field_key" value=$field.Key}
		{assign var="last_post_value" value=$fVal.$fKey}

		{counter start=1 assign="mf_index"}
		{foreach from=$fVal item="pval"}
			{assign var="field_key" value=$field.Key|cat:"_level"|cat:$mf_index}
			{if $fVal.$field_key}
				{counter}
				{assign var="last_field_key" value=$field_key}
				{assign var="last_post_value" value=$fVal.$field_key}
			{/if}
		{/foreach}

		<input type="hidden" value="{$last_post_value}" id="{$mf_form_prefix}{$field.Key}_val" />
		<input type="hidden" value="{$last_field_key}" id="{$mf_form_prefix}{$field.Key}_lastsel" />
	{/if}

	{assign var="tmp" value="level"|explode:$field.Key}		
	<input type="hidden" value="{$field.Key}" class="{$mf_form_prefix}{$tmp.0|trim:"_"}_mf" />

	<script type="text/javascript">
	{literal}
		$(document).ready(function(){
			{/literal}{if $mf_form_key}
				var post_form_dom = 'form:has(input[value={$mf_form_key}][name=post_form_key]) ';
			{else}
				var post_form_dom = '';
			{/if}{literal}

			if( '{/literal}{$field.Key}{literal}'.indexOf('level') > 0 && $(post_form_dom + 'select[name="f[{/literal}{$field.Key}{literal}]"]').val() == "0" )
			{
				var top_field = trim('{/literal}{$field.Key}{literal}'.split('level')[0], '_');
				var level = '{/literal}{$field.Key}{literal}'.split('level')[1];
				var prev_level = level-1;
				var prev_field = level > 1 ? top_field + '_level' + prev_level : top_field;

				if( !$(post_form_dom + 'select[name="f['+prev_field+']"]').val() || $(post_form_dom + 'select[name="f['+prev_field+']"]').val() == "0" )
				{
					$(post_form_dom + 'select[name="f[{/literal}{$field.Key}{literal}]"]').attr('disabled', 'disabled').addClass('disabled');
				}
			}

			$(post_form_dom + 'select[name="f[{/literal}{$field.Key}{literal}]"]').change(function(){
				{/literal}
				var top_field = trim('{$field.Key}'.split('level')[0], '_');
				var level = parseInt('{$field.Key}'.split('level')[1]) ? parseInt('{$field.Key}'.split('level')[1]) : 0;

				{literal}			
				if( $(this).val() == "0" )
				{
					if( level > 1 )
					{
						var prev_field = top_field +'_level'+(level-1);
					}
					else
					{
						var prev_field = top_field;
					}
					var prev_val = $(post_form_dom + 'select[name="f['+ prev_field +']"]').val();

					{/literal}
					$('#{$mf_form_prefix}'+top_field+'_lastsel').val( prev_field );
					$('#{$mf_form_prefix}'+top_field+'_val').val( prev_val );
					{literal}
				}else
				{
					{/literal}
					$('#{$mf_form_prefix}'+top_field+'_lastsel').val('{$field.Key}');
					$('#{$mf_form_prefix}'+top_field+'_val').val( $(this).val() );
					{literal}
				}
				
				if( $(this).val() != "0" && $(this).val() && $(post_form_dom + 'select[name="f['+top_field+'_level'+(level+1)+']"]').length > 0 )
				{
					$(post_form_dom + 'select[name="f['+top_field+'_level'+(level+1)+']"] option:first-child').text('{/literal}{$lang.loading}{literal}');
					xajax_mfGetNext( $(this).val(), $(this).attr('name'), '{/literal}{$mf_form_key}{literal}', $('.{/literal}{$mf_form_prefix}{literal}'+top_field+'_mf').length, 'listing', {/literal}'{$multi_formats[$field.Condition].Order_type}'{literal} );
				}
			});

			if( ( $('#{/literal}{$mf_form_prefix}{$field.Key}_val').val() || post_form_dom == '') && ( '{$field.Key}{literal}'.indexOf('level') < 0 || '{/literal}{$field.Key}{literal}'.indexOf('level') == false) )
			{
				{/literal}
				xajax_mfBuild( $('#{$mf_form_prefix}{$field.Key}_val').val(), $('#{$mf_form_prefix}{$field.Key}_lastsel').val(), '{$mf_form_key}', $('.{$mf_form_prefix}{$field.Key}_mf').length, 'listing', '{$multi_formats[$field.Condition].Order_type}' );
				{literal}
			}
		});
	{/literal}
	</script>
{/if}
