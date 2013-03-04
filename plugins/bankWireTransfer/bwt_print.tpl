<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>

<title>
{$config.site_name}
</title>

<meta name="generator" content="reefLESS Boat Classifieds Software" />
<meta http-equiv="Content-Type" content="text/html; charset={$config.encoding}" />
<link href="{$rlTplBase}css/print.css" type="text/css" rel="stylesheet" />
<link rel="shortcut icon" href="{$smarty.const.RL_URL_HOME}{$smarty.const.ADMIN}/img/favicon.ico" />

<script type="text/javascript">
	var rlUrlHome = '{$rlTplBase}';
	var lang = new Array();
	lang['photo'] = '{$lang.photo}';
	lang['of'] = '{$lang.of}';
</script>

<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}jquery/jquery.js"></script>
<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}jquery/cookie.js"></script>
<script type="text/javascript" src="{$rlTplBase}js/lib.js"></script>

</head>
<body>
<table class="sTable">
	<tr>
		<td><h1>{$lang.bwt_payment_wire_transfer}</h1></td>
		<td align="right"><input title="{$lang.print_page}" onclick="window.print(); $(this).hide();" type="button" value="{$lang.print_page}" /></td>
	</tr>
</table>
<div class="sLine"></div>
<div id="content" style="padding-top: 15px;">  
	{include file=$smarty.const.RL_PLUGINS|cat:'bankWireTransfer'|cat:$smarty.const.RL_DS|cat:'bank_wire_transfer.tpl'}	
</div>
</body>