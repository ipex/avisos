<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-gb" xml:lang="en-gb">
<head>
<title>{$config.under_constructions_meta_title}</title>
<meta name="generator" content="Avisos Clasificados de Bolivia" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="description" content="{$config.under_constructions_meta_description}" />
<meta name="Keywords" content="{$config.under_constructions_meta_keywords}" />
<link href="{$smarty.const.RL_URL_HOME}plugins/underConstructions/style.css" type="text/css" rel="stylesheet" />
<link rel="shortcut icon" href="{$rlTplBase}img/favicon.ico" />

<script type="text/javascript" src="{$smarty.const.RL_URL_HOME}libs/jquery/jquery.js"></script>

{$ajaxJavascripts}
</head>
<body>

<div id="outer_second">
	<div id="outer_first">
		<div id="header"></div>
	</div>
</div>

<div id="flash"></div>

<div id="box">
	<div class="logo"></div>
	
	<table class="shadow">
	<tr>
		<td class="left"></td>
		<td>
		
			<table class="content">
			<tr class="header">
				<td class="left"></td>
				<td class="center"></td>
				<td class="right"></td>
			</tr>
			</table>
			
			<div class="body">
				<h1>{$lang.under_constructions_h1}</h1>
				<h2>{$lang.under_constructions_h2}</h2>
				
				<div class="date">
					<table align="center">
					<tr class="numbers">
						<td align="center" id="time_day"></td>
						<td align="center" id="time_hour"></td>
						<td align="center" id="time_minute"></td>
						<td align="center" id="time_sec"></td>
					</tr>
					<tr class="items">
						<td align="center">{$lang.under_constructions_days}</td>
						<td align="center">{$lang.under_constructions_hours}</td>
						<td align="center">{$lang.under_constructions_minutes}</td>
						<td align="center">{$lang.under_constructions_seconds}</td>
					</tr>
					</table>
				</div>
				
				{if $aHooks.massmailer_newsletter}
				<div class="subscribe">
					<form method="post" onsubmit="return subscribr();" action="">
						<table align="center">
						<tr>
							<td>
								<div class="input">
									<label class="left"></label>
									<input type="text" id="email" value="{$lang.under_constructions_email}" />
									<label class="right"></label>
								</div>
							</td>
							<td>
								<div class="button">
									<label for="button" class="left"></label>
									<input id="button" type="submit" value="{$lang.under_constructions_subscribe}" />
									<label for="button" class="right"></label>
								</div>
							</td>
						</tr>
						</table>
					</form>
					<div id="error_obj">
						<div id="error_message"></div>
					</div>
					
					<div id="notice_obj">
						<div id="notice_message"></div>
					</div>
				</div>
				{/if}
				
			</div>
			<table class="content">
			<tr class="footer">
				<td class="left"></td>
				<td class="center"><div></div></td>
				<td class="right"></td>
			</tr>
			</table>
		
		</td>
		<td class="right"></td>
	</tr>
	</table>
	
</div>

<script type="text/javascript">
//<![CDATA[
var current_date = new Array();
var redirect_url = '{$smarty.const.RL_URL_HOME}';
current_date['day'] = {$smarty.now|date_format:'%d'};
current_date['month'] = {$smarty.now|date_format:'%m'};
current_date['year'] = {$smarty.now|date_format:'%Y'};
current_date['hours'] = parseInt('{$smarty.now|date_format:'%H'}');
current_date['minutes'] = parseInt('{$smarty.now|date_format:'%M'}');
current_date['seconds'] = parseInt('{$smarty.now|date_format:'%S'}');

var curTime = 0;

{literal}

$(document).ready(function(){
	//set current date
	var cDate = new Date();
	cDate.setDate(current_date['day']);
	cDate.setMonth(current_date['month']);
	cDate.setFullYear(current_date['year']);
	cDate.setHours(current_date['hours']);
	cDate.setMinutes(current_date['minutes']);
	cDate.setSeconds(current_date['seconds']);

	var curSeconds = cDate.getTime();

	{/literal}
	//set target date
	var tDate = new Date();
	tDate.setDate({$date|date_format:'%d'});
	tDate.setMonth({$date|date_format:'%m'});
	tDate.setFullYear({$date|date_format:'%Y'});
	tDate.setHours(parseInt('{$date|date_format:'%H'}'));
	tDate.setMinutes(parseInt('{$date|date_format:'%M'}'));
	tDate.setSeconds(parseInt('{$date|date_format:'%S'}'));
	{literal}
	var targetSeconds = tDate.getTime();
	
	//get different
	curTime = (targetSeconds - curSeconds)/1000;
	
	printDate();
	
	$(window).resize(function(){
		resize();	
	});
	
	resize();
	
	var val = false;
	$('input#email').focus(function(){
		if ( !val )
		{
			val = $(this).val();
			$(this).val('');
		}
	}).blur(function(){
		if ( $(this).val() == '' )
		{
			$(this).val(val);
			val = false;
		}
	});
});

var resize = function(){
	var height = $(window).height();
	height = Math.ceil(height/2);
	
	$('#header').height(height);
}

var subscribr = function(){
	xajax_subscribe('subscribe', 'Guest', $('#email').val());
	return false;
}

var printDate = function(){
	
	var days = Math.floor(curTime/3600/24);
	var hours = Math.floor((curTime-(days*3600*24))/3600);

	var minutes = Math.floor((curTime-(days*3600*24)-(hours*3600))/60);
	var seconds = Math.floor((curTime-(days*3600*24)-(hours*3600))-(minutes*60));
	
	if ( days < 0 )
	{
		location.href = redirect_url;
		return;
	}
	
	days = days < 10 ? '0'+days: days;
	hours = hours < 10 ? '0'+hours: hours;
	minutes = minutes < 10 ? '0'+minutes: minutes;
	seconds = seconds < 10 ? '0'+seconds: seconds;
	
	var outTime = days+':' +hours+':'+minutes+':'+seconds;
	$('#time_obj').html(outTime);
	$('#time_day').html(days);
	$('#time_hour').html(hours);
	$('#time_minute').html(minutes);
	$('#time_sec').html(seconds);

	
	curTime--;
	setTimeout('printDate()', 1000);
}

{/literal}
//]]>
</script>

</body>
</html>
