<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: {version}
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: SETTINGS.TPL.PHP
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
 *	Flynax Classifieds Software 2013 |  All copyrights reserved. 
 *
 *	http://www.flynax.com/
 *
 ******************************************************************************/

/* template settings */
$tpl_settings = array(
	'name' => 'auto_main_blue',
	'inventory_menu' => true,
	'right_block' => true,
	'long_top_block' => true,
	'featured_price_tag' => false,
	'ffb_list' => true
);

if (is_object($rlSmarty))
{
	$rlSmarty -> assign_by_ref( 'tpl_settings', $tpl_settings );
}

if ( !$config[$tpl_settings['name']] )
{
	$languages = $rlLang -> getLanguagesList();
	$tpl_phrases = array(
		array('common', 'menu_more', 'More'),
		array('frontEnd', 'list_view', 'List View'),
		array('frontEnd', 'gallery_view', 'Gallery View'),
		array('frontEnd', 'subscribe_rss', 'Subscribe to RSS-feed')
	);

	/* insert template phrases */
	if ( version_compare($config['rl_version'], '4.1.0') <= 0 )
	{
		$allow_insert = false;
		$sql = "INSERT INTO `". RL_DBPREFIX ."lang_keys` (`Code`, `Module`, `Key`, `Value`) VALUES ";
		foreach ($languages as $language)
		{
			foreach ($tpl_phrases as $tpl_phrase)
			{
				if ( !$rlDb -> getOne('ID', "`Code` = '{$language['Code']}' AND `Key` = '{$tpl_phrase[1]}'", 'lang_keys') )
				{
					$allow_insert = true;
					$sql .= "('{$language['Code']}', '{$tpl_phrase[0]}', '{$tpl_phrase[1]}', '{$tpl_phrase[2]}'), ";
				}
			}
		}
		
		if ( $allow_insert )
		{
			$sql = rtrim($sql, ', ');
			$rlDb -> query($sql);
		}
	}
	
	/* insert indicate config */
	$sql = "INSERT INTO `". RL_DBPREFIX ."config` (`Group_ID`, `Position`, `Key`, `Default`, `Values`, `Type`, `Data_type`) ";
	$sql .= "VALUES ('0', '0', '{$tpl_settings['name']}', '1', '', 'text', '')";
	$rlDb -> query($sql);
}