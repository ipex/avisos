{if $smarty.const.REALM == 'admin'}
	{assign var="post_prefix" value="f"}
{else}
	{assign var="post_prefix" value="account"}
{/if}

{foreach from=$fields item="field"}
	{if $multi_formats[$field.Condition]}
		{if $mf_form_key}
			var post_form_dom = 'form:has(input[value={$mf_form_key}],name=[post_form_key]) ';
		{else}
			var post_form_dom = '';
		{/if}{literal}

		if( '{/literal}{$field.Key}{literal}'.indexOf('level') > 0 && $(post_form_dom + 'select[name="{/literal}{$post_prefix}[{$field.Key}{literal}]"]').val() == "0" )
		{
			var top_field = trim('{/literal}{$field.Key}{literal}'.split('level')[0], '_');
			var level = '{/literal}{$field.Key}{literal}'.split('level')[1];
			var prev_level = level-1;
			var prev_field = level > 1 ? top_field + '_level' + prev_level : top_field;

			if( !$(post_form_dom + 'select[name="{/literal}{$post_prefix}['+prev_field+']"]').val() || $(post_form_dom + 'select[name="{$post_prefix}{literal}['+prev_field+']"]').val() == "0" )
			{
				$(post_form_dom + 'select[name="{/literal}{$post_prefix}[{$field.Key}{literal}]"]').attr('disabled', 'disabled').addClass('disabled');
			}
		}
		
		$(post_form_dom + 'select[name="{/literal}{$post_prefix}[{$field.Key}{literal}]"]').change(function(){
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
				var prev_val = $(post_form_dom + 'select[name="{/literal}{$post_prefix}{literal}['+ prev_field +']"]').val();

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
			
			if( $(this).val() != "0" && $(this).val() && $(post_form_dom + 'select[name="{/literal}{$post_prefix}{literal}['+top_field+'_level'+(level+1)+']"]').length > 0 )
			{
				xajax_mfGetNext( $(this).val(), $(this).attr('name'), '{/literal}{$mf_form_key}{literal}', $('.{/literal}{$mf_form_prefix}{$field.Key}{literal}_mf').length, 'account' );
			}
		});
		
		if( '{/literal}{$field.Key}{literal}'.indexOf('level') < 0 || '{/literal}{$field.Key}{literal}'.indexOf('level') == false)
		{
			{/literal}	
			xajax_mfBuild( $('#{$mf_form_prefix}{$field.Key}_val').val(), $('#{$mf_form_prefix}{$field.Key}_lastsel').val(), '{$mf_form_key}', $('.{$mf_form_prefix}{$field.Key}_mf').length, 'account' );
			{literal}
		}	
		{/literal}	
	{/if}
{/foreach}
