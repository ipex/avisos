<!-- fields handler -->

<script type="text/javascript">
//<![CDATA[
var realty_cat_name = '{$category.Key}';
{literal}

$(document).ready(function(){
	if ( rlPageInfo['key'] == 'edit_listing' )
	{
		realty_cat_name = $('#sf_field_states select option:selected').val();
	}

	$('#sf_field_states select option').each(function(){
		if ( $(this).val() == realty_cat_name )
		{
			var field_name = $('#sf_field_states select').attr('name');
			var field_value = $(this).val();

			$(this).attr('selected', true);
			$('#sf_field_states select').attr('disabled', true).addClass('disabled').attr('name', 'disabled_'+field_name);
			
			$('#sf_field_states select').after('<input type="hidden" name="'+field_name+'" value="'+field_value+'" />');
		}
	});
	
	$('#sale_rent_table input').change(function(){
		realtyPropType();
	});
	
	realtyPropType();
});

var realtyPropType = function(obj)
{
	var val = parseInt($('#sale_rent_table input:checked').val());
	if ( val == 2 )
	{
		$('#sf_field_time_frame').parent().fadeIn();
	}
	else
	{
		$('#sf_field_time_frame').parent().fadeOut();
	}
}

{/literal}
//]]>
</script>

<!-- fields handler end -->