<?php

/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: CUSTOM_CATEGORIES.INC.PHP
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

/* ext js action */
if ($_GET['q'] == 'ext')
{
	/* system config */
	require_once( '../../includes/config.inc.php' );
	require_once( RL_ADMIN_CONTROL . 'ext_header.inc.php' );
	require_once( RL_LIBS . 'system.lib.php' );

	/* date update */
	if ($_GET['action'] == 'update' )
	{
		$reefless -> loadClass( 'Actions' );
		
		$type = $rlValid -> xSql( $_GET['type'] );
		$field = $rlValid -> xSql( $_GET['field'] );
		$value = $rlValid -> xSql( nl2br($_GET['value']) );
		$id = $rlValid -> xSql( $_GET['id'] );
		$key = $rlValid -> xSql( $_GET['key'] );

		$updateData = array(
			'fields' => array(
				$field => $value
			),
			'where' => array(
				'ID' => $id
			)
		);

		$rlHook -> load('apExtCustomCategoriesUpdate');
		
		$rlActions -> updateOne( $updateData, 'tmp_categories');
		exit;
	}
	
	/* data read */
	$limit = $rlValid -> xSql( $_GET['limit'] );
	$start = $rlValid -> xSql( $_GET['start'] );
	
	$langCode = $rlValid -> xSql( $_GET['lang_code'] );
	$phrase = $rlValid -> xSql( $_GET['phrase'] );

	$sql = "SELECT `T1`.*, `T2`.`Key` AS `Parent_key`,`T2`.`Type`, `T3`.`Username` FROM `".RL_DBPREFIX."tmp_categories` AS `T1` ";
	$sql .= "LEFT JOIN `".RL_DBPREFIX."categories` AS `T2` ON `T1`.`Parent_ID` = `T2`.`ID` ";
	$sql .= "LEFT JOIN `".RL_DBPREFIX."accounts` AS `T3` ON `T1`.`Account_ID` = `T3`.`ID` ";
	$sql .= "WHERE `T1`.`Status` = 'approval' ORDER BY `Key` LIMIT {$start}, {$limit}";

	$rlHook -> load('apExtCustomCategoriesSql');
	
	$data = $rlDb -> getAll($sql);
	$data = $rlLang -> replaceLangKeys( $data, 'tmp_categories', array( 'name' ), RL_LANG_CODE, 'admin' );
	$rlDb -> resetTable();

	// get section
	foreach ( $data as $key => $value )
	{
		$data[$key]['Parent'] = $data[$key]['Parent_key'] ? $GLOBALS['lang']['categories+name+'.$data[$key]['Parent_key']] : $lang['no_parent'];
		$data[$key]['Listing_ID'] = $data[$key]['Listing_ID'] ? $data[$key]['Listing_ID'] : $lang['no_listing_added'];
		$data[$key]['Type'] = $rlListingTypes -> types[$data[$key]['Type']]['name'];
	}
	
	$rlHook -> load('apExtCustomCategoriesData');
	
	$count = $rlDb -> getRow( "SELECT COUNT(`ID`) AS `count` FROM `" . RL_DBPREFIX . "tmp_categories` WHERE `Status` <> 'trash'" );
	
	$reefless -> loadClass( 'Json' );
	
	$output['total'] = $count['count'];
	$output['data'] = $data;
	echo $rlJson -> encode( $output );
}
/* ext js action end */

else
{
	$allow_tmp = 0;
	foreach ($rlListingTypes -> types as $ltype)
	{
		if ( $ltype['Cat_custom_adding'] )
		{
			$allow_tmp = 1;
		}
	}
	$rlSmarty -> assign_by_ref('allow_tmp', $allow_tmp);
	
	if ( $allow_tmp )
	{
		$reefless -> loadClass( 'TmpCategories', 'admin' );
		
		/* register ajax methods */
		$rlXajax -> registerFunction( array( 'deleteTmpCategory', $rlTmpCategories, 'ajaxDeleteTmpCategory' ) );
		$rlXajax -> registerFunction( array( 'activateTmpCategory', $rlTmpCategories, 'ajaxActivateTmpCategory' ) );
	}
	else
	{
		$url = RL_URL_HOME . ADMIN . '/index.php?controller=settings&amp;group=10';
		$link = '<a class="dark_13" href="'. $url .'">'. $lang['admin_controllers+name+config'] .'</a>';
		$alerts[] = str_replace('[link]', $link, $lang['tmp_categories_desabled']);
		$rlSmarty -> assign_by_ref('alerts', $alerts);
	}
	
	$rlHook -> load('apPhpCustomCategoriesBottom');
}