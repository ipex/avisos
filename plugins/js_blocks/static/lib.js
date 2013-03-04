
/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LISENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: LIB.JS
 *
 *	This script is a commercial software and any kind of using it must be 
 *	coordinate with Flynax Owners Team and be agree to Flynax License Agreement
 *
 *	This block may not be removed from this file or any other files with out 
 *	permission of Flynax respective owners.
 *
 *	Copyrights Flynax Classifieds Software | 2013
 *	http://www.flynax.com/
 *
 ******************************************************************************/

$(document).ready(function(){
	$("#jCodeOut").focus(function() {
		$(this).select();

		$(this).mouseup(function() {
		       $(this).unbind("mouseup");
		return false;
		});
	});

	$('#Account').change(function(){
		setTimeout(function(){
			adurl['account_id'] = $('#ac_hidden').val();
			buildBox( adurl );
		},1);
	});

	$('#field_names_switch input[type=radio]').change(function(){
		adurl['field_names'] = $(this).val();
		if( $(this).val() == 1 )
		{
			$('#field_names_color_cont').fadeIn();
		}else
		{
			$('#field_names_color_cont').fadeOut();
		}
		buildBox( adurl );
	});
	$('.colorSelector').ColorPicker({
		color: '#'+bg_color,
		onShow: function (colpkr) {
			$(colpkr).fadeIn(500);
				return false;
			},
		onHide: function (colpkr) {
			$(colpkr).fadeOut(500);
			refreshBox();
		return false;
		},
		onChange: function (hsb, hex, rgb) {
			var cur_id = $(this).attr('id')
			$('div.colorSelector').each(function(){
				if( $(this).data('colorpickerId') == cur_id )
				{
					$(this).children('div').css('backgroundColor', '#' + hex);
					$(this).prev().val(hex);
				}
			});
		}
	});

	$('select[name=listing_type]').change(function(){
		xajax_loadCategories( $(this).val(), 0, -1 );

		if( $(this).val() )
		{
			adurl['listing_type'] = $(this).val();
		}
		buildBox( adurl );
	});

	$('input[name=per_page]').change(function(){
		if( $(this).val() )
		{
			adurl['per_page'] = $(this).val();
		}
		buildBox( adurl );
	});

	$('input[name=limit]').change(function(){
		if( $(this).val() )
		{
			adurl['limit'] = $(this).val();
		}
		buildBox( adurl );
	});

	$('.multicat').change(function(){
		var level = $(this).attr('id').split('category_level')[1];
		var category_id = '';

		if( $(this).val() && $(this).val() != 0 )
		{
			category_id = $(this).val();
		}else if( $('#category_level' + (level - 1) ).val() )
		{				
			category_id = $('#category_level' + (level - 1) ).val();
		}	

		if( !$(this).hasClass('last') )
		{
			xajax_loadCategories( $('select[name=listing_type]').val(), category_id, level );
		}
		
		adurl['category_id'] = category_id;

		buildBox( adurl );
	});

	$('table#jParams input[type=text]').change(function(){
		refreshBox();
	});
	$('table#jParams select').change(function(){
		refreshBox();
	});
});

var buildBox = function( adurl )
{
	if( !adurl['listing_type'] && $('select[name=listing_type]').val() )
	{
		adurl['listing_type'] = $('select[name=listing_type]').val();
	}

	aurl = '';
	for( var x in adurl )
	{
		if( adurl[x] && typeof(adurl[x]) != 'function' )
		{
			aurl += '&' + x + "=" + adurl[x];
		}
	}

	$.getScript(url + aurl, function(data, textStatus, jqxhr) {
		refreshBox();
	});
}

var refreshBox = function()
{
	var params = new Array();
	var jconf = new Array();
	var value = false;
	var colorPkrFields = new Array('conf_advert_bg', 'conf_field_first_color', 'conf_field_color', 'conf_field_names_color');
	var sizeFields = new Array('conf_img_width', 'conf_img_height');

	$('#jParams input').each(function(){
		if( ($(this).attr('type') == 'text' ||  $(this).attr('type') == 'hidden') && $(this).val() && $(this).attr('abbr').typeOf != "undefined")
		{
			if( $.inArray( $(this).attr('name'), colorPkrFields ) >= 0 )
			{
				value = '#' + $(this).val();
			}else if( $.inArray( $(this).attr('name'), sizeFields ) >= 0 )
			{
				value = $(this).val() + $(this).next('select').val();
			}
			else
			{
				value = $(this).val();
			}

			params = $(this).attr('abbr').split("|");
			setStyleByClass(params[0], params[1], params[2], value);

			jconf[$(this).attr('name')] = value;
		}
	});

	refreshCode( jconf );
}

var refreshCode = function( jconf )
{
	var jconf_out = '';
	var out = '';

	if( jconf )
	{
		for( var x in jconf )
		{
			if( typeof(jconf[x]) != 'function' )
			{
				jconf_out += "\t" + x + " = '" + jconf[x] + "';\r\n";
			}
		}
	}

	if( jconf_out )
	{
		out = '<script type="text/javascript">\r\n';
		out += jconf_out;
		out += "<\/script>\r\n";
	}

	var jParams = '';

	out += '';
	out += iout.replace('[aurl]', acurl + aurl);

	$('#jCodeOut').val( out );
}
