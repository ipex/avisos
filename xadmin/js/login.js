
/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: LOGIN.JS
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

/* make username filed in focus */
$(document).ready(
	function(){
		$('#username').focus();
	}
);

/**
* check admin login form
*
* @param srting user_empty - error message in case when the user filed is empty
* @param string pass_empty - error message in case when the password filed is empty
*
* @return bool
* 
**/
function jsLogin( user_empty, pass_empty )
{
	if ( $("#username").val() != '' )
	{
		if ( $("#password").val() != '' )
		{
			$('#login_button').val(lang['ext_loading']);
			
			var pass_hash = $('#password').val();
			var password = hex_md5(sec_key)+hex_md5(pass_hash);

			xajax_logIn( $('#username').val(), password, $('#interface').val() );
		}
		else
		{
			fail_alert( '#password', pass_empty );
		}
	}
	else
	{
		fail_alert( '#username', user_empty );
	}
	
	return false;
}

/**
*
* alert the message and focus current field
*
* @param srting field - jQuery format field 
* @param string message - alert message text
* 
**/
function fail_alert( field, message )
{
	Ext.MessageBox.alert(lang['alert'], message, function(){
		if ( field != '' )
		{
			$(field).addClass('field_error');
			$(field).focus();
		}
	});
}
