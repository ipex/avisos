<!-- Facebook connect block -->

<div id="fb-root"></div>
<script type="text/javascript">
//<![CDATA[
var fbStatus = '{$fb_status}';
var autoRegPreventDetected = {if $autoRegPreventDetected}true{else}false{/if};

{literal}

$(document).ready(function() {
	if ( !document.getElementById('fb-nav-bar') ) {
		var fcDOM = '<img style="cursor:pointer;" alt="" title="{/literal}{$lang.fConnect_login_title}{literal}" src="{/literal}{$smarty.const.RL_PLUGINS_URL}{literal}facebookConnect/static/fb_login.png" onclick="fcLogin();" />';
		$('input[value=login]:first').parent().find('input[type=submit]').after(fcDOM);
	}
});

window.fbAsyncInit = function() {
	FB.init({
		appId  : '{/literal}{$config.facebookConnect_appid}{literal}',
		status : true, 
		cookie : true,
		xfbml  : true,
		oauth  : true
	});

	if ( fbStatus != '' & fbStatus != 'active' ) {
		printMessage('warning', '{/literal}{$lang.notice_account_approval|escape:"quotes"|regex_replace:"/[\r\t\n]/":" "}{literal}');

		FB.getLoginStatus(function(response) {
			if ( response.authResponse ) {
				FB.logout();
			}
		});
	}

	if ( autoRegPreventDetected ) {
		FB.getLoginStatus(function(response) {
			if ( response.authResponse ) {
				FB.logout();
			}
		});
	}
};

function fcLogin(mode) {
	FB.getLoginStatus(function(response) {
		if ( response ) {
			if ( response.authResponse ) {
				FB.logout(function(response) {
					if ( mode == undefined ) {
						fcLogin();
					}
					else {
						window.location.href = '{/literal}{$smarty.const.RL_URL_HOME}{if $config.mod_rewrite}{$pages.login}.html?action=logout{else}index.php?page={$pages.login}&action=logout{/if}{literal}';
					}
				});
			}
			else {
				FB.login(function(response) {
					if ( response.authResponse ) {
						window.location.href = '{/literal}{$smarty.const.RL_URL_HOME}{literal}';
					}
				}, {scope:'email'});
			}
		}
		else {
			FB.login();
		}
	});
}

(function(d) {
	var js, id = 'facebook-jssdk'; if (d.getElementById(id)) {return;}
	js = d.createElement('script'); js.id = id; js.async = true;
	js.src = "//connect.facebook.net/en_US/all.js";
	d.getElementsByTagName('head')[0].appendChild(js);
}(document));

{/literal}
//]]>
</script>

{if $fb_email}
<script type="text/javascript">
//<![CDATA[
var fc_phrase_prompt = "{$lang.fConnect_prompt}";
var fc_email = "{$fb_email}";
{literal}

$(document).ready(function() {
	fConnect_force_prompt();
});

var fConnect_force_prompt = function() {
	$('div.error div.close').click();

	var result = prompt( fc_phrase_prompt.replace( '<br />', '\r\n' ).replace( '{email}', fc_email ) );
	if ( result == null ) {
		FB.getLoginStatus(function(response) {
			if ( response.authResponse ) {
				FB.logout();
			}
		});
	}
	else {
		xajax_fConnect(result);
	}
}

{/literal}
//]]>
</script>
{/if}

<!-- Facebook connect block end -->