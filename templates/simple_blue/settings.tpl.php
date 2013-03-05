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
	'name' => 'general_simple',
	'inventory_menu' => false,
	'right_block' => true,
	'long_top_block' => true,
	'featured_price_tag' => true
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
		array('frontEnd', 'nothing_found_for_char', 'Nothing found for <b>&quot;{char}&quot;</b>'),
		array('frontEnd', 'list_view', 'List View'),
		array('frontEnd', 'grid_in_category', 'in {category}',),
		array('frontEnd', 'gallery_view', 'Gallery View'),
		array('frontEnd', 'subscribe_rss', 'Subscribe to RSS-feed'),
		array('frontEnd', 'blocks+name+home_search_form', 'Search'),
		array('admin', 'long_top', 'Long Top')
	);

	/* insert template phrases */
	if ( version_compare($config['rl_version'], '4.1.0') <= 0 )
	{
		$sql = "INSERT INTO `". RL_DBPREFIX ."lang_keys` (`Code`, `Module`, `Key`, `Value`) VALUES ";
		foreach ($languages as $language)
		{
			foreach ($tpl_phrases as $tpl_phrase)
			{
				$sql .= "('{$language['Code']}', '{$tpl_phrase[0]}', '{$tpl_phrase[1]}', '{$tpl_phrase[2]}'), ";
			}
		}
		$sql = rtrim($sql, ', ');
		$rlDb -> query($sql);
	}
	
	/* add search box */
	$sql = "INSERT INTO `". RL_DBPREFIX ."blocks` (`Page_ID`, `Sticky`, `Key`, `Position`, `Side`, `Type`, `Content`, `Tpl`, `Status`) VALUES ";
	$sql .= "('1', 0, 'home_search_form', 1, 'middle_right', 'smarty', '{include file=''blocks''|cat:\$smarty.const.RL_DS|cat:''search_form.tpl''}', '1', 'active')";
	$rlDb -> query($sql);
	
	/* insert indicate config */
	$sql = "INSERT INTO `". RL_DBPREFIX ."config` (`Group_ID`, `Position`, `Key`, `Default`, `Values`, `Type`, `Data_type`) ";
	$sql .= "VALUES ('0', '0', '{$tpl_settings['name']}', '1', '', 'text', '')";
	$rlDb -> query($sql);
	
	/* insert template hook */
	$sql = "INSERT INTO `". RL_DBPREFIX ."hooks` (`Name`, `Plugin`, `Code`, `Status`) VALUES ";
	$sql .= "('apPhpBlocksTop', 'template', 'global \$tpl_settings, \$l_block_sides, \$lang; if ( \$tpl_settings[''long_top_block''] ) { \$l_block_sides[''long_top''] = \$lang[''long_top'']; }', 'active'),";
	$sql .= "('pageinfoArea', 'template', 'global \$tpl_settings, \$l_block_sides, \$lang; if ( \$tpl_settings[''long_top_block''] ) { \$l_block_sides[''long_top''] = \$lang[''long_top'']; }', 'active')";
	$rlDb -> query($sql);
	
	/* insert new block side */
	$sql = "ALTER TABLE `". RL_DBPREFIX ."blocks` CHANGE `Side` `Side` SET( 'left', 'right', 'top', 'bottom', 'middle', 'middle_left', 'middle_right', 'long_top' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";
	$rlDb -> query($sql);
}