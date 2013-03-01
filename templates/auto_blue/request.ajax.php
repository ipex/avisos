<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: {version}
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: REQUEST.AJAX.PHP
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

$request_mode = $_REQUEST['mode'];
$request_item = $_REQUEST['item'];
$request_lang = $_REQUEST['lang'];

if ( !$request_item || !$request_mode || !in_array($request_mode, array('photo', 'listing')) )
	exit;

/* system config */
require_once( '../../includes/config.inc.php' );

session_start();
	
require_once( RL_CLASSES . 'rlDb.class.php' );
require_once( RL_CLASSES . 'reefless.class.php' );

$rlDb = &new rlDb();
$reefless = &new reefless();

/* load classes */
$reefless -> connect(RL_DBHOST, RL_DBPORT, RL_DBUSER, RL_DBPASS, RL_DBNAME);
$reefless -> loadClass('Debug');
$reefless -> loadClass('Config');

$config = $rlConfig -> allConfig();

$reefless -> loadClass('Cache');
$reefless -> loadClass('Valid');
$reefless -> loadClass('Hook');
$reefless -> loadClass('Lang');
$reefless -> loadClass('ListingTypes', null, false, true);

/* get page paths */
$rlDb -> setTable('pages');
$pages_tmp = $rlDb -> fetch(array( 'Key', 'Path'));
foreach ( $pages_tmp as $page_tmp )
{
	$pages[$page_tmp['Key']] = $page_tmp['Path'];
}
unset($pages_tmp);

$rlValid -> sql($request_item);
$rlValid -> sql($request_lang);

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

switch ($request_mode){
	case 'listing':
		$lang = $rlLang -> getLangBySide('frontEnd', $request_lang);

		$seo_base = RL_URL_HOME;
		if ( $config['lang'] != $request_lang && $config['mod_rewrite'] )
		{
			$seo_base .= $request_lang . '/';
		}
		if ( !$config['mod_rewrite'] )
		{
			$seo_base .= 'index.php';
		}
		
		$request_type = $rlValid -> xSql($_REQUEST['type']);
		$request_field = $rlValid -> xSql($_REQUEST['field']);
		
		$reefless -> loadClass('Common');
		$reefless -> loadClass('Listings');
		$reefless -> loadClass('Search');
		
		$data['keyword_search'] = $request_item;
		$fields['keyword_search'] = array(
			'Type' => 'text'
		);
		
		$rlSearch -> fields = $fields;
		
		$listings = $rlSearch -> search($data, false, false, 20);
		foreach ($listings as $listing)
		{
			$category_path = $seo_base;
			if ( $config['mod_rewrite'] )
			{
				$category_path .= $pages[$rlListingTypes -> types[$listing['Listing_type']]['Page_key']] .'/'. $listing['Path'];
				$listing_path = $category_path;
				$category_path .= $rlListingTypes -> types[$listing['Listing_type']]['Cat_postfix'] ? '.html' : '/';
				$listing_path .= '/'. $rlValid -> str2path($listing['listing_title']) .'-'. $listing['ID'] .'.html';
			}
			else
			{
				$category_path .= '?page='. $pages[$rlListingTypes -> types[$listing['Listing_type']]['Page_key']] .'&category='. $listing['Category_ID'];
				$listing_path .= '?page='. $pages[$rlListingTypes -> types[$listing['Listing_type']]['Page_key']] .'&id='. $listing['ID'];
			}
			
			$out[] = array(
				'listing_title' => $listing['listing_title'],
				'Category_name' => $lang['categories+name+'. $listing['Cat_key']],
				'Category_path' => $category_path,
				'Listing_path' => $listing_path
			);
		}
		unset($listings);
			
		break;
		
	case 'photo':
		$out = RL_FILES_URL . $rlDb -> getOne('Photo', "`Thumbnail` = '{$request_item}'", 'listing_photos');
		
		break;
}

if ( !empty($out) )
{
	$reefless -> loadClass('Json');
	echo $rlJson -> encode($out);
}
else
{
	echo null;
}