<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: {version}
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: AUTOCOMPLETE.INC.PHP
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

/* system config */
require_once( '../../includes/config.inc.php' );

session_start();
	
require_once( RL_CLASSES . 'rlDb.class.php' );
require_once( RL_CLASSES . 'reefless.class.php' );

$rlDb = &new rlDb();
$reefless = &new reefless();

/* load classes */
$reefless -> connect(RL_DBHOST, RL_DBPORT, RL_DBUSER, RL_DBPASS, RL_DBNAME);
$reefless -> loadClass( 'Debug' );
$reefless -> loadClass( 'Valid' );
$reefless -> loadClass( 'Lang' );
$reefless -> loadClass( 'Cache' );
$reefless -> loadClass('ListingTypes', null, false, true);

$str = $rlValid -> xSql(trim($_GET['str']));
$str = str_replace(' ', '(.*)', $str);
$field = $rlValid -> xSql($_GET['field']);

$sql = "SELECT `T2`.`Key` AS `Field_key`, `T2`.`Type` AS `Field_type` FROM `" . RL_DBPREFIX . "listing_titles` AS `T1` ";
$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_fields` AS `T2` ON `T1`.`Field_ID` = `T2`.`ID` ";
$sql .= "LEFT JOIN `" . RL_DBPREFIX . "categories` AS `T3` ON `T1`.`Category_ID` = `T3`.`ID` ";
$sql .= "WHERE `T2`.`Status` = 'active' AND `T3`.`Status` = 'active' ";
$sql .= "GROUP BY `T1`.`Field_ID`";

$fields = $rlDb->getAll($sql);
if ( !empty($fields) )
{
	foreach ($fields as $key => $value)
	{
		switch ($value['Field_type']){
			case 'text':
			case 'textarea':
			case 'number':
			case 'date':
			case 'mixed':
			case 'price':
			case 'unit':
				$direct_fields[] = $value['Field_key'];
				break;
			default:
				$multi_fields[] = $value;
				break;
		}
	}
}

unset($fields);

$reefless -> loadClass( 'Config' );

$config = $rlConfig -> allConfig();
$GLOBALS['config'] = $config;

$sql = "SELECT DISTINCT `T1`.*, `T3`.`Path` AS `Category_path`, `T3`.`Type` AS `Listing_type`, `T3`.`Key` AS `Category_key`, ";
$sql .= "UNIX_TIMESTAMP(`T1`.`Date`) AS `Listing_date`, `T3`.`ID` AS `Category_ID` ";
$sql .= "FROM `" . RL_DBPREFIX . "listings` AS `T1` ";
$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_plans` AS `T2` ON `T1`.`Plan_ID` = `T2`.`ID` ";
$sql .= "LEFT JOIN `" . RL_DBPREFIX . "categories` AS `T3` ON `T1`.`Category_ID` = `T3`.`ID` ";

$rlLang -> extDefineLanguage();

$lang = $rlLang -> getLangBySide( 'frontEnd', RL_LANG_CODE );

if ( !empty($multi_fields) )
{
	foreach ( $multi_fields as $key => $value )
	{
		$tb_number = $key + 4;
		//$sql .= "LEFT JOIN `" . RL_DBPREFIX . "lang_keys` AS `T{$tb_number}` ON `T{$tb_number}`.`Key` LIKE 'listing_fields+name+{$value['Field_key']}%' AND `T{$tb_number}`.`Code` = '". RL_LANG_CODE ."' AND `T{$tb_number}`.`Status` = 'active' AND `T{$tb_number}`.`Plugin` = '' ";
	}
}
$sql .= "WHERE (UNIX_TIMESTAMP(DATE_ADD(`T1`.`Pay_date`, INTERVAL `T2`.`Listing_period` DAY)) > UNIX_TIMESTAMP(NOW()) OR `T2`.`Listing_period` = 0) ";
$sql .= "AND `T1`.`status` = 'active' AND `T2`.`status` = 'active'  AND `T3`.`status` = 'active' ";
$sql .= "AND CONCAT(`T1`.`". implode("`, `T1`.`", $direct_fields) ."`) REGEXP '{$str}' ";

if ( !empty($multi_fields) )
{
	foreach ( $multi_fields as $key => $value )
	{
		$tb_number = $key + 4;
	//	$sql .= "OR `T{$tb_number}`.`Value` RLIKE '{$str}' ";
	}
}

$output = $rlDb -> getAll($sql);

if ( !empty($output) )
{
	/* utf8 library functions */
	function loadUTF8functions()
	{
		$names = func_get_args();
		
		if ( empty($names) )
		{
			return false;
		}
		
		foreach ( $names as $name )
		{
			if (file_exists( RL_LIBS . 'utf8' . RL_DS . 'utils' . RL_DS . $name . '.php' ))
			{
				require_once( RL_LIBS . 'utf8' . RL_DS . 'utils' . RL_DS . $name . '.php' );
			}
		}
	}
	
	/* assign base path */
	$bPath = RL_URL_HOME;
	if ( $config['lang'] != RL_LANG_CODE && $config['mod_rewrite'] )
	{
		$bPath .= RL_LANG_CODE . '/';
	}
	if ( !$config['mod_rewrite'] )
	{
		$bPath .= 'index.php';
	}
	
	/* get pages paths */
	$rlDb -> setTable('pages');
	$tmp_pages = $rlDb -> fetch( array( 'Key', 'Path' ) );
	$rlDb -> resetTable();

	foreach ( $tmp_pages as $tmp_page )
	{
		$pages[$tmp_page['Key']] = $tmp_page['Path'];
	}
	unset($tmp_pages);
	
	$reefless -> loadClass( 'Hook' );
	$reefless -> loadClass( 'Common' );
	$reefless -> loadClass( 'Categories' );
	$reefless -> loadClass( 'Listings' );
	
	foreach ($output as $key => $value)
	{
		$lt = $rlListingTypes -> types[$value['Listing_type']];
		$cat_postfix = $lt['Cat_postfix'] ? '.html' : '/';
		
		$echo[$key]['listing_title'] = $rlListings -> getListingTitle( $value['Category_ID'], $value, $value['Listing_type'] );
		$l_path = $bPath;
		$l_path .= $config['mod_rewrite'] ? $pages[$lt['Page_key']] .'/'. $value['Category_path'] .'/'. $rlValid -> str2path($echo[$key]['listing_title']) . '-'. $value['ID'] .'.html' : '?page='. $page_path .'&id=' . $value['ID'];
		$echo[$key]['Listing_path'] = $l_path;
		
		$c_path = $bPath;
		$c_path .= $config['mod_rewrite'] ? $pages[$lt['Page_key']] .'/'. $value['Category_path'] . $cat_postfix : '?page='. $pages[$lt['Page_key']] .'&category=' . $value['Category_ID'];
		$echo[$key]['Cat_path'] = $c_path;
		$echo[$key]['Date'] = date(str_replace(array('%', 'b'), array('', 'M'), RL_DATE_FORMAT), $value['Listing_date']);
		$echo[$key]['Category_name'] = str_replace(' ', '&nbsp;', $lang['categories+name+'.$value['Category_key']]);
		$echo[$key]['listing_title'] = str_replace(' ', '&nbsp;', $echo[$key]['listing_title']);
	}
}

unset($output);

$reefless -> loadClass( 'Json' );
echo $rlJson -> encode( $echo );