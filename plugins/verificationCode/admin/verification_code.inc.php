<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LISENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: VERIFICATION_CODE.INC.PHP
 *
 *	This script is a commercial software and any kind of using it must be 
 *	coordinate with Flynax Owners Team and be agree to Flynax License Agreement
 *
 *	This block may not be removed from this file or any other files with out 
 *	permission of Flynax respective owners.
 *
 *	Copyrights Flynax Classifieds Software | 2013
 *	http://www.flynax.com/
 *
 ******************************************************************************/

/* ext js action */
if ( $_GET['q'] == 'ext' )
{
	// system config
	require_once( '../../../includes/config.inc.php' );
	require_once( RL_ADMIN_CONTROL .'ext_header.inc.php' );
	require_once( RL_LIBS .'system.lib.php' );
	
	/* date update */
	if ($_GET['action'] == 'update' )
	{
		$reefless -> loadClass( 'Actions' );
		$reefless -> loadClass( 'VerificationCode', null, 'verificationCode' );

		$field = $rlValid -> xSql( $_GET['field'] );
		$value = $rlValid -> xSql( nl2br($_GET['value']) );
		$id = $rlValid -> xSql( $_GET['id'] );
           
		$updateData = array(
			'fields' => array(
				$field => $value
			),
			'where' => array(
				'ID' => $id
			)
		);

		if($rlActions -> updateOne( $updateData, 'verification_code' ))
		{
			$rlVerificationCode -> updateCodesHook();
		}

		exit;
	}

	/* data read */
	$limit = $rlValid -> xSql( $_GET['limit'] );
	$start = $rlValid -> xSql( $_GET['start'] );

	$sql = "SELECT SQL_CALC_FOUND_ROWS `T1`.* ";
	$sql .= "FROM `" . RL_DBPREFIX . "verification_code` AS `T1` ";
    $sql .= "WHERE `T1`.`Status` <> 'trash' ";
	$sql .= "ORDER BY `T1`.`Date` DESC LIMIT {$start}, {$limit}";

	$data = $rlDb -> getAll( $sql );                                                                                                      
	$count = $rlDb -> getRow( "SELECT FOUND_ROWS() AS `count`" );

	foreach($data as $key => $val)
	{
		$data[$key]['Status'] = $GLOBALS['lang'][$data[$key]['Status']];
		$data[$key]['Position'] = $GLOBALS['lang']['vc_position_'.$data[$key]['Position']];
	}

	$output['total'] = $count['count'];
	$output['data'] = $data;

	$reefless -> loadClass( 'Json' );
	echo $rlJson -> encode( $output );

	exit;
}

$reefless -> loadClass( 'VerificationCode', null, 'verificationCode' );

if ( isset( $_GET['action'] ) )
{
	$reefless -> loadClass( 'Valid' );

	// get all languages
	$allLangs = $GLOBALS['languages'];
	$rlSmarty -> assign_by_ref( 'allLangs', $allLangs );

	$bcAStep[] = array( 'name' => $_GET['action'] == 'add' ? $lang['vc_add_item'] : $lang['vc_edit_item'] );

	if ( $_GET['action'] == 'add' || $_GET['action'] == 'edit' )
	{
		// get account types
		$reefless -> loadClass( 'Account' );
		
		/* get pages list */
		$pages = $rlDb -> fetch( array( 'ID', 'Key' ), array( 'Tpl' => 1 ), "AND `Status` = 'active' ORDER BY `Key`", null, 'pages' );
		$pages = $rlLang -> replaceLangKeys( $pages, 'pages', array( 'name' ), RL_LANG_CODE, 'admin' );
		$rlSmarty -> assign_by_ref( 'pages', $pages );

		$id = (int)$_GET['item'];

		// get current plan info
		if ( isset( $_GET['item'] ) && !$_POST['fromPost'] )
		{
			$verification_code = $rlDb -> fetch( '*', array( 'ID' => $id ), null, null, 'verification_code', 'row' );

			foreach($verification_code as $key => $val)
			{
				if($key == 'Pages')
				{
					$_POST[strtolower($key)] = explode(",", $val);
				}
				elseif($key == 'Pages_sticky')
				{
					$_POST['show_on_all'] = $val;
				}
				else
				{
					$_POST[strtolower($key)] = $val;
				}
			}
		}

		if ( isset( $_POST['submit'] ) )
		{
			$errors = $error_fields = array();

			if ( empty( $_POST['name'] ) )
			{
				array_push( $errors, str_replace('{field}', "<b>{$lang['vc_name']}</b>", $lang['notice_field_empty'] ) );
				array_push( $error_fields, "name" );
			}

			if ( empty( $_POST['content'] ) )
			{
				array_push( $errors, str_replace( '{field}', "<b>{$lang['vc_content']}</b>", $lang['notice_field_empty'] ) );
				array_push( $error_fields, "content" );
			}

			if ( empty( $_POST['position'] ) )
			{
				array_push( $errors, str_replace( '{field}', "<b>{$lang['vc_position']}</b>", $lang['notice_field_empty'] ) );
				array_push( $error_fields, "position" );
			}

			if ( !empty( $errors ) )
			{
				$rlSmarty -> assign_by_ref( 'errors', $errors );
			}
			else 
			{
				if ( $_GET['action'] == 'add' )
				{
					// write main plan information
					$data = array(
						'Name' => $_POST['name'],
						'Pages' => !empty($_POST['pages']) ? implode(",", $_POST['pages']) : '',
						'Pages_sticky' => !empty($_POST['show_on_all']) ? 1 : 0,
						'Date' => 'NOW()',
						'Position' => $_POST['position']
					);

					if ( $action = $rlActions -> insertOne( $data, 'verification_code', array('Content') ) )
					{
						$verification_code_id = mysql_insert_id();
						
						$sql = "UPDATE `" . RL_DBPREFIX . "verification_code` SET `Content` = '".$rlValid->xSql($_POST['content'])."' WHERE `ID` = '{$verification_code_id}'";
						if($rlDb -> query( $sql ))
						{
							$rlVerificationCode -> updateCodesHook();
						}

						$message = $lang['vc_item_added'];
						$aUrl = array( "controller" => $controller );
					}
					else 
					{
						trigger_error( "Can't add new banner plan (MYSQL problems)", E_WARNING );
						$rlDebug -> logger( "Can't add new banner plan (MYSQL problems)" );
					}
				}
				elseif ( $_GET['action'] == 'edit' )
				{                          
					$sql = "UPDATE `" . RL_DBPREFIX . "verification_code` SET  ";
					$sql .= "`Name` = '".$rlValid->xSql($_POST['name'])."', `Content` = '".$rlValid->xSql($_POST['content'])."', `Pages` = '".(!empty($_POST['pages']) ? implode(",", $_POST['pages']) : '')."', `Position` = '{$_POST['position']}', `Pages_sticky` = '".(!empty($_POST['show_on_all']) ? 1 : 0)."' ";
					$sql .= "WHERE `ID` = '{$id}'";

					$action = $rlDb -> query( $sql );
					$rlVerificationCode -> updateCodesHook();

					$message = $lang['vc_item_edited'];
					$aUrl = array( "controller" => $controller );
				}

				if ( $action )
				{
					$reefless -> loadClass( 'Notice' );
					$rlNotice -> saveNotice( $message );
					$reefless -> redirect( $aUrl );
				}
			}
		}
	}
}

/* register ajax methods */
$rlXajax -> registerFunction( array( 'deleteItem', $rlVerificationCode, 'ajaxDeleteItem' ) );