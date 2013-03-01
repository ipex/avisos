<?php

/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: SYSTEM.LIB.PHP
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

/* fields types list */
$l_types = array(
	'text' => $GLOBALS['lang']['type_text'],
	'textarea' => $GLOBALS['lang']['type_textarea'],
	'number' => $GLOBALS['lang']['type_number'],
	'phone' => $GLOBALS['lang']['type_phone'],
	'date' => $GLOBALS['lang']['type_date'],
	'mixed' => $GLOBALS['lang']['type_mixed'],
	'price' => $GLOBALS['lang']['type_price'],
	'bool' => $GLOBALS['lang']['type_bool'],
	'select' => $GLOBALS['lang']['type_select'],
	'radio' => $GLOBALS['lang']['type_radio'],
	'checkbox' => $GLOBALS['lang']['type_checkbox'],
	'image' => $GLOBALS['lang']['type_image'],
	'file' => $GLOBALS['lang']['type_file_storage'],
	'accept' => $GLOBALS['lang']['type_accept']
);

/* deny files extension regular expresion */
$l_deny_files_regexp = "/\.(php|php3|php4|php5|phtml|pl|py|psp|js|jsp|cgi|util|inc)$/";

/* conditions list */
$l_cond = array(
	'isEmail' => $GLOBALS['lang']['mail'],
	'isUrl' => $GLOBALS['lang']['url'],
	'isDomain' => $GLOBALS['lang']['domain']
);

/* resize types list */
$l_resize = array(
	'W' => $GLOBALS['lang']['by_width'],
	'H' => $GLOBALS['lang']['by_height'],
	'C' => $GLOBALS['lang']['by_width_height']
);

/* file types list */
$l_file_types = array(
	'pdf' => array(
		'name' => $GLOBALS['lang']['adobe_acrobat'],
		'ext' => 'pdf',
	),
	'zip' => array(
		'name' => $GLOBALS['lang']['archive'],
		'ext' => 'zip,rar',
	),
	'doc' => array(
		'name' => $GLOBALS['lang']['ms_word'],
		'ext' => 'doc,rtf',
	),
	'xls' => array(
		'name' => $GLOBALS['lang']['ms_excel'],
		'ext' => 'xls,csv',
	)
);

/* menus types */
$l_menu_types = array(
	'1' => $GLOBALS['lang']['main_menu'],
	'2' => $GLOBALS['lang']['account_menu'],
	'3' => $GLOBALS['lang']['bottom_menu']
);

if ( $tpl_settings['inventory_menu'] === true )
{
	$l_menu_types['4'] = $GLOBALS['lang']['inventory_menu'];
}

/* menus types */
$l_mobile_menu_types = array(
	'5' => $GLOBALS['lang']['main_menu'],
	'6' => $GLOBALS['lang']['user_menu'],
	'7' => $GLOBALS['lang']['bottom_menu']
);

if ( $tpl_settings['inventory_menu'] === true )
{
	$l_menu_types['4'] = $GLOBALS['lang']['inventory_menu'];
}

/* page types */
$l_page_types = array(
	'static' => $GLOBALS['lang']['static'],
	'system' => $GLOBALS['lang']['system'],
	'external' => $GLOBALS['lang']['external']
);

/* blocks sides */
$l_block_sides = array(
	'left' => $GLOBALS['lang']['left'],
	'top' => $GLOBALS['lang']['top'],
	'bottom' => $GLOBALS['lang']['bottom'],
	'middle' => $GLOBALS['lang']['middle'],
	'middle_left' => $GLOBALS['lang']['middle_left'],
	'middle_right' => $GLOBALS['lang']['middle_right']
);

if ( $tpl_settings['right_block'] === true )
{
	$l_block_sides['right'] = $GLOBALS['lang']['right'];
}

/* blocks types */
$l_block_types = array(
	'html' => $GLOBALS['lang']['html'],
	'smarty' => $GLOBALS['lang']['smarty'],
	'php' => $GLOBALS['lang']['php']
);

/* plan types */
$l_plan_types = array(
	'listing' => $GLOBALS['lang']['listing_plan'],
	'package' => $GLOBALS['lang']['package_plan'],
	'featured' => $GLOBALS['lang']['featured_plan']
);

/* player files types */
$l_player_file_types = array(
	'flv' => '',
	'mp4' => '',
	'rtmp' => '',
	'swf' => '',
	'mp3' => ''
);

/* email template's variables */
$l_email_variables = array('{site_name}', '{site_url}', '{site_email}', '{username}');

/* youTube source */
$l_youtube_thumbnail = 'http://img.youtube.com/vi/{key}/1.jpg';
$l_youtube_direct = 'http://www.youtube.com/v/{key}&feature=player_embedded';

/* timezone list */
$l_timezone = array(
	'Kwajalein' => array('-12:00', '(GMT -12:00) International Date Line West'),
	'Pacific/Midway' => array('-11:00', '(GMT -11:00) Midway Island, Samoa'),
	'Pacific/Honolulu' => array('-10:00', '(GMT -10:00) Hawaii'),
	'America/Anchorage' => array('-9:00', '(GMT -9:00) Alaska'),
	'America/Los_Angeles' => array('-8:00', '(GMT -8:00) Pacific Time (US &amp; Canada)'),
	'America/Denver' => array('-7:00', '(GMT -7:00) Mountain Time (US &amp; Canada)'),
	'America/Tegucigalpa' => array('-6:00', '(GMT -6:00) Central Time (US &amp; Canada), Mexico City'),
	'America/New_York' => array('-5:00', '(GMT -5:00) Eastern Time (US &amp; Canada), New York, Bogota, Lima'),
	'America/Caracas' => array('-4:30', '(GMT -4:30) Atlantic Time (Canada), Caracas'),
	'America/Halifax' => array('-4:00', '(GMT -4:00) Atlantic Time (Canada), La Paz, Halifax'),
	'Canada/Newfoundland' => array('-3:30', '(GMT -3:30) Newfoundland'),
	'America/Argentina/Buenos_Aires' => array('-3:00', '(GMT -3:00) Brazil, Buenos Aires, Georgetown'),
	'Atlantic/South_Georgia' => array('-2:00', '(GMT -2:00) Mid-Atlantic, Stanley'),
	'Atlantic/Azores' => array('-1:00', '(GMT -1:00) Azores, Cape Verde Islands'),
	'Europe/Dublin' => array('+0:00', '(GMT 0:00) Western Europe Time, London, Lisbon, Casablanca'),
	'Europe/Belgrade' => array('+1:00', '(GMT +1:00) Brussels, Copenhagen, Madrid, Paris'),
	'Europe/Minsk' => array('+2:00', '(GMT +2:00) Kaliningrad, South Africa'),
	'Asia/Kuwait' => array('+3:00', '(GMT +3:00) Baghdad, Riyadh, Moscow, St. Petersburg'),
	'Asia/Tehran' => array('+3:30', '(GMT +3:30) Tehran'),
	'Asia/Muscat' => array('+4:00', '(GMT +4:00) Abu Dhabi, Muscat, Baku, Tbilisi'),
	'Asia/Kabul' => array('+4:30', '(GMT +4:30) Kabul'),
	'Asia/Yekaterinburg' => array('+5:00', '(GMT +5:00) Ekaterinburg, Islamabad, Karachi, Tashkent'),
	'Asia/Kolkata' => array('+5:30', '(GMT +5:30) Bombay, Calcutta, Madras, New Delhi'),
	'Asia/Katmandu' => array('+5:45', '(GMT +5:45) Kathmandu'),
	'Asia/Dhaka' => array('+6:00', '(GMT +6:00) Almaty, Dhaka, Novosibirsk'),
	'Asia/Krasnoyarsk' => array('+7:00', '(GMT +7:00) Bangkok, Hanoi, Jakarta, Krasnoyarsk'),
	'Asia/Brunei' => array('+8:00', '(GMT +8:00) Beijing, Perth, Singapore, Hong Kong'),
	'Asia/Seoul' => array('+9:00', '(GMT +9:00) Tokyo, Seoul, Osaka, Sapporo, Yakutsk'),
	'Australia/Darwin' => array('+9:30', '(GMT +9:30) Adelaide, Darwin'),
	'Australia/Canberra' => array('+10:00', '(GMT +10:00) Eastern Australia, Guam, Vladivostok'),
	'Asia/Magadan' => array('+11:00', '(GMT +11:00) Magadan, Solomon Islands, New Caledonia'),
	'Pacific/Fiji' => array('+12:00', '(GMT +12:00) Auckland, Wellington, Fiji, Kamchatka')
);

/* sub-level paths */
$search_results_url = 'search-results';
$advanced_search_url = 'advanced-search';

/* add listing process steps */
$steps = array(
	'category' => array(
		'name' => $lang['select_category'],
		'caption' => true,
	),
	'plan' => array(
		'name' => $lang['select_plan'],
		'caption' => true,
		'path' => 'select-a-plan'
	),
	'form' => array(
		'name' => $lang['fill_out_form'],
		'caption' => true,
		'path' => 'fill-out-a-form'
	),
	'photo' => array(
		'name' => $lang['add_photo'],
		'caption' => true,
		'path' => 'add-pictures'
	),
	'video' => array(
		'name' => $lang['add_video'],
		'caption' => true,
		'path' => 'add-video'
	),
	'checkout' => array(
		'name' => $lang['checkout'],
		'caption' => true,
		'path' => 'checkout'
	)
);

if (is_object($rlSmarty))
{
	$rlSmarty -> assign_by_ref( 'l_types', $l_types );
	$rlSmarty -> assign_by_ref( 'l_cond', $l_cond );
	$rlSmarty -> assign_by_ref( 'l_resize', $l_resize );
	$rlSmarty -> assign_by_ref( 'l_file_types', $l_file_types );
	$rlSmarty -> assign_by_ref( 'l_listing_types', $l_listing_types );
	$rlSmarty -> assign_by_ref( 'l_menu_types', $l_menu_types );
	$rlSmarty -> assign_by_ref( 'l_mobile_menu_types', $l_mobile_menu_types );
	$rlSmarty -> assign_by_ref( 'l_page_types', $l_page_types );
	$rlSmarty -> assign_by_ref( 'l_block_sides', $l_block_sides );
	$rlSmarty -> assign_by_ref( 'l_block_types', $l_block_types );
	$rlSmarty -> assign_by_ref( 'l_plan_types', $l_plan_types );
	$rlSmarty -> assign_by_ref( 'l_email_variables', $l_email_variables );
	$rlSmarty -> assign_by_ref( 'l_player_file_types', $l_player_file_types );
	$rlSmarty -> assign_by_ref( 'l_youtube_thumbnail', $l_youtube_thumbnail );
	$rlSmarty -> assign_by_ref( 'l_youtube_direct', $l_youtube_direct );
	$rlSmarty -> assign_by_ref( 'search_results_url', $search_results_url );
	$rlSmarty -> assign_by_ref( 'advanced_search_url', $advanced_search_url );
	$rlSmarty -> assign_by_ref( 'steps', $steps );
}
