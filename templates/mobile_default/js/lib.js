
/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: LIB.JS
 *	
 *	The software is a commercial product delivered under single, non-exclusive,
 *	non-transferable license for one domain or IP address. Therefore distribution,
 *	sale or transfer of the file in whole or in part without permission of Flynax
 *	respective owners is considered to be illegal and breach of Flynax License End
 *	User Agreement.
 *	
 *	You are not allowed to remove this information from the file without permission
 *	of Flynax respective owners.
 *	
 *	Flynax Classifieds Software 2013 | All copyrights reserved.
 *	
 *	http://www.flynax.com/
 ******************************************************************************/

$(document).ready(function(){
	//navigator.geolocation.getCurrentPosition(foundLocation, noLocation);
});

var glCoords = new Array();
var glAddress = new Array();
var glAddressString = '';
var foundLocation = function(position){
	if ( position.coords.latitude && position.coords.longitude )
	{
		glCoords['latitude'] = position.coords.latitude;
		glCoords['longitude'] = position.coords.longitude;
	}
	//alert(glCoords['latitude'] + ',' +glCoords['longitude']);
	if ( position.address )
	{
		glAddress['country'] = position.address.country;
		glAddress['countryCode'] = position.address.countryCode;
		glAddress['region'] = position.address.region;
		glAddress['city'] = position.address.city;
		glAddress['street'] = position.address.street;
		glAddress['streetNumber'] = position.address.streetNumber;
		
		for ( var i in glAddress )
		{
			if ( glAddress[i] )
			{
				glAddressString += glAddress[i]+', ';
			}
		}
		
		glAddressString = glAddressString.substr(0, glAddressString.length-2);
		//alert(glAddressString)
	}
}

var noLocation = function(){
	alert('netu')
}

function createCookie( name, value, days)
{
	if (days)
	{
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires="+date.toGMTString();
	}
	else
	{
		var expires = "";
	}
	document.cookie = name+"="+value+expires+"; path=/";
}

function readCookie(name)
{
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	
	for(var i=0;i < ca.length;i++)
	{
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	}
	return null;
}

function eraseCookie(name)
{
	createCookie(name,"",-1);
}

/**
*
* tabs click handler
*
* @param object obj - tab object referent
* 
**/
$(document).ready(function(){
	$('table.tabs>tbody>tr>td').click(function(){
		tabsSwitcher(this);
	});
});

var tabsSwitcher = function(obj){
	var key = $(obj).attr('abbr');
	
	$('div.tab_area').hide();
	$('div.tabs li.active').removeClass('active');
	
	$('table.tabs>tbody>tr>td').removeClass('active');
	$(obj).addClass('active');
	$('div#area_'+key).show();
	
	$('#system_message>div').fadeOut();
};

$(document).ready(function(){
	$('div.star_icon a').each(function(){
		var id = $(this).attr('id').split('_')[1];
		var ids = readCookie('favorites');
		
		if ( ids && ids.indexOf(id) >= 0 )
		{
			$(this).parent().addClass('remove');
			$(this).attr('title', lang['remove_from_favorites']);
		}
	});
	
	$('div.star_icon a').click(function(){
		var id = $(this).attr('id').split('_')[1];
		var ids = readCookie('favorites');
		
		if ( ids )
		{
			ids = ids.split(',');
			
			if ( ids.indexOf(id) >= 0 )
			{
				ids.splice(ids.indexOf(id), 1);
				
				createCookie('favorites', ids.join(','), 31);
				
				$(this).parent().removeClass('remove');
				$(this).attr('title', lang['add_to_favorites']);
				
				if ( rlPageInfo['key'] == 'favorites' )
				{
					$(this).parent().parent().parent().parent().parent().parent().fadeOut('normal', function(){
						$(this).remove();
						
						if ( $('#listings ul li').length < 2 )
						{
							$('#listings ul').remove();
							
							if ( $('ul.paging').length > 0 )
							{
								var redirect = rlUrlRoot;
								redirect += rlConfig['mod_rewrite'] ? rlPageInfo['path'] +'.html' : 'index.php?page='+ rlPageInfo['path'];
								location.href = redirect;
							}
							else
							{
								var div = '<div class="padding">'+lang['no_favorite']+'</div>';
								$('div.content_container').append(div);
								$('.sorting').parent().remove();
							}
						}
					});
						
					$('#notice_message').html(lang['notice_removed_from_favorites']);
					$('#notice_obj').fadeIn();
				}
				
				return;
			}
			else
			{
				ids.push(id);
			}
		}
		else
		{
			ids = new Array();
			ids.push(id);
		}
		
		createCookie('favorites', ids.join(','), 31);
		
		$(this).parent().addClass('remove');
		$(this).attr('title', lang['remove_from_favorites']);
	});
});

/**
*
* prompt alert
*
* @param string message - prompt message text
* @param srting method  - javascript method (function)
* @param Array  params  - method (function) params
* @param string load_object  - load object ID
* 
**/
function rlConfirm( message, method, params, load_object )
{
	if (confirm(message))
	{
		var func = method+'('+params+')';
		
		eval(func);
		
		if ( load_object != '')
		{
			$('#'+load_object).fadeIn('normal');
		}
	}
}

/**
* notices/errors handler
*
* @param string type - message type: error, notice, warning
* @param string/array message - message text
* @param string/array fields - error fields names, array or through comma
*
**/
var printMessageTimer = false;
var printMessage = function(type, message, fields, direct){
	
	var types = new Array('error', 'notice', 'warning');
	var height = 0;
	
	if ( types.indexOf(type) < 0 )
		return;
		
	if ( typeof(message) == 'object' )
	{
		var tmp = '<ul>';
		for( var i=0; i<message.length; i++ )
		{
			tmp += '<li>'+message[i]+'</li>';
		}
		tmp += '</ul>';
		message = tmp;
	}
	
	$('input,select,textarea,table.error').removeClass('error');
	
	/* highlight error fields */
	if ( fields )
	{
		if ( typeof(fields) != 'object' )
		{
			fields = fields.split(',');
		}

		for ( var i = 0; i<fields.length; i++ )
		{
			if ( !fields[i] )
				continue;

			if ( trim(fields[i]) != '' )
			{
				if ( fields[i].charAt(0) == '#' )
				{
					$(fields[i]).addClass('error');
				}
				else
				{
					var selector = 'input[name^="'+fields[i]+'"]:last,select[name="'+fields[i]+'"],textarea[name="'+fields[i]+'"]';
					if ( $(selector).length > 0 && $(selector).attr('type') != 'radio' && $(selector).attr('type') != 'checkbox' )
					{
						$(selector).addClass('error');
					}
					else
					{
						if ( $(selector).attr('type') == 'radio' || $(selector).attr('type') == 'checkbox' )
						{
							$(selector).closest('table').addClass('error');
						}
						else
						{
							$('input[name="'+fields[i]+'[1]"],select[name="'+fields[i]+'[1]"],textarea[name="'+fields[i]+'][1]"').parent().parent().parent().parent().addClass('error');
						}
					}
				}	
			}
		}
	}
	
	/* print error in direct mode */
	if ( direct )
	{
		var html = ' \
			<div class="'+type+' hide"> \
				<div class="inner"> \
					<div class="icon"></div> \
					<div class="message">'+message+'</div> \
				</div> \
			</div> \
		';
		
		$('#system_message').html(html);
		$('#system_message div.'+type).fadeIn();
		
		$('input.error,select.error,textarea.error').focus(function(){
			$(this).removeClass('error');
		});
		$('table.error').click(function(){
			$(this).removeClass('error');
		});
		
		return;
	}
	
	/* print errors */
	if ( $('body>div.'+type).length > 0 )
	{
		$('body>div.'+type+' div.message').fadeOut(function(){
			$(this).html(message).fadeIn();
			height = $('body>div.'+type).height() * -1;
			
			clearTimeout(printMessageTimer);
			printMessageTimer = setTimeout('close()', 30000);
		});
	}
	else
	{
		$('body>div.error, body>div.notice, body>div.warning, #system_message>div.error, #system_message>div.notice, #system_message>div.warning').fadeOut('fast', function(){
			$(this).remove();
		});
		
		clearTimeout(printMessageTimer);
		
		var html = ' \
			<div class="'+type+'"> \
				<div class="inner"> \
					<div class="icon"></div> \
					<div class="message">'+message+'</div> \
					<div class="close" title="'+lang['close']+'"></div> \
				</div> \
			</div> \
		';
		
		$('body').prepend(html);
			height = $('body>div.'+type).height() * -1;
		$('body>div.'+type).css('margin-top', height).show().animate({marginTop: 0}, function(){
			printMessageTimer = setTimeout('close()', 30000);
		});
	}
	
	$('body>div.'+type).unbind('mouseenter').unbind('mouseleave').mouseenter(function(){
		clearTimeout(printMessageTimer);
	}).mouseleave(function(){
		printMessageTimer = setTimeout('close()', 30000);
	});
	
	/* close */
	$('body>div.'+type+' div.close').unbind('click').click(function(){
		close();
	});
	
	$('input.error,select.error,textarea.error').focus(function(){
		$(this).removeClass('error');
	});
	$('table.error').click(function(){
		$(this).removeClass('error');
	});
	
	this.close = function(){
		$('body>div.'+type).animate({marginTop: height}, 'fast', function(){
			$('body>div.'+type).remove();
		});
		clearTimeout(printMessageTimer);
	};
};

/**
*
* trim string
*
* @param string str - string for trim
* @param string chars - chars to be trimmed
*
* @return trimmed string
* 
**/
function trim(str, chars)
{
	return ltrim(rtrim(str, chars), chars);
}

/**
*
* left trim string
*
* @param string str - string for trim
* @param string chars - chars to be trimmed
*
* @return trimmed string
* 
**/
function ltrim(str, chars)
{
	if ( !str )
		return;
		
	chars = chars || "\\s";
	return str.replace(new RegExp("^[" + chars + "]+", "g"), "");
}

/**
*
* right trim string
*
* @param string str - string for trim
* @param string chars - chars to be trimmed
*
* @return trimmed string
* 
**/
function rtrim(str, chars)
{
	if ( !str )
		return;
		
	chars = chars || "\\s";
	return str.replace(new RegExp("[" + chars + "]+$", "g"), "");
}

/**
*
* hide or show the object (via jQuery effect) by ID, and hide all objects by html path
*
* @param srting id - field id
* @param srting path - html path
* 
**/
function show( id, path )
{
	if (path != undefined)
	{
		$(path).slideUp('fast');
	}

	if ( $( '#'+id ).css('display') == 'block' )
	{
		$( '#'+id ).slideUp('normal');
	}
	else
	{
		$( '#'+id ).slideDown('slow');
	}
}

/* adaptation for IE6 */
if(!Array.indexOf)
{
    Array.prototype.indexOf = function(obj){
        for(var i=0; i<this.length; i++){
            if(this[i]==obj){
                return i;
            }
        }
        return -1;
    }
}

/**
*
* escape or replace quotes
*
* @param string str - string for replacing
* @param bool to - replace if true and escape if false
* 
**/
function quote( str, to )
{
	if (!to)
	{
		return str.replace(/'/g, "").replace(/"/g, "");
	}
	else
	{
		var to_single = '&rsquo;';
		var to_double = '&quot;';
		
		return str.replace(/'/g, to_single).replace(/"/g, to_double).replace(/\n/g, '<br />' );
	}
}
