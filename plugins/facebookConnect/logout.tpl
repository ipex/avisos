<!-- Facebook logout block -->

{if $fbconnect_me}
<script type="text/javascript">
var fcLogoutImg = '{$smarty.const.RL_PLUGINS_URL}facebookConnect/static/fb_logout.gif';
{literal}

	$(document).ready(function() {
		$('a[href$=logout]').each(function() {
			var html = ' <img onclick="fcLogin(1);" style="cursor:pointer;" alt="" title="{/literal}{$lang.title_logout}{literal}" src="'+ fcLogoutImg +'" />';

			var parent = $(this).parent();
			$(this).remove();
			$(parent).append(html);
		});
	});

{/literal}
</script>
{/if}

<!-- Facebook logout block end -->