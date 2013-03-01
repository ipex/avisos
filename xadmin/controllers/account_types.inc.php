<?php

/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: ACCOUNT_TYPES.INC.PHP
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
		
		$rlHook -> load('apExtAccountTypesUpdate');
		
		$rlActions -> updateOne( $updateData, 'account_types');
		exit;
	}
	
	/* data read */
	$limit = $rlValid -> xSql( $_GET['limit'] );
	$start = $rlValid -> xSql( $_GET['start'] );

	$sql = "SELECT SQL_CALC_FOUND_ROWS DISTINCT `T1`.*, ";
	$sql .= "(SELECT COUNT(`ID`) FROM `". RL_DBPREFIX ."accounts` WHERE `Type` = `T1`.`Key` AND `Status` <> 'trash') AS `Accounts_count` ";
	$sql .="FROM `". RL_DBPREFIX ."account_types` AS `T1` ";
	$sql .= "WHERE `T1`.`Status` <> 'trash' ORDER BY `T1`.`Position` ";
	$sql .= "LIMIT {$start}, {$limit}";
	
	$rlHook -> load('apExtAccountTypesSql');
	
	$data = $rlDb -> getAll($sql);
	$data = $rlLang -> replaceLangKeys( $data, 'account_types', array( 'name' ), RL_LANG_CODE, 'admin' );

	foreach ( $data as $key => $value )
	{
		$data[$key]['Status'] = $lang[$data[$key]['Status']];
	}
	
	$rlHook -> load('apExtAccountTypesData');
	
	$count = $rlDb -> getRow( "SELECT FOUND_ROWS() AS `count`" );
	$count = $count['count'];
	
	$reefless -> loadClass( 'Json' );
	
	$output['total'] = $count['count'];
	$output['data'] = $data;

	echo $rlJson -> encode( $output );
}
/* ext js action end */

else
{
	$reefless -> loadClass('Account');
	$reefless -> loadClass('Listings');
	
	$rlHook -> load('apPhpAccountTypesTop');
	
	/* additional bread crumb step */
	if ( $_GET['action'] )
	{
		switch ($_GET['action']){
			case 'add':
				$bcAStep = $lang['add_type'];
				break;
			
			case 'edit':
				$bcAStep = $lang['edit_type'];
				break;
				
			case 'build':
				$bcAStep = $lang['build_register_form'];
				break;
		}
	}
	else
	{
		$rlXajax -> registerFunction( array( 'preAccountTypeDelete', $rlAdmin, 'ajaxPreAccountTypeDelete' ) );
		$rlXajax -> registerFunction( array( 'deleteAccountType', $rlAdmin, 'ajaxDeleteAccountType' ) );
		
		/* get accounts types */
		$available_account_types = $rlAccount -> getAccountTypes('visitor');
		$rlSmarty -> assign_by_ref('available_account_types', $available_account_types);
	}
	
	if ($_GET['action'] == 'add' || $_GET['action'] == 'edit')
	{
		/* type settings */
		$account_settings = array(
			array(
				'key' => 'email_confirmation',
				'name' => $lang['account_type_email_confirmation']
			),
			array(
				'key' => 'admin_confirmation',
				'name' => $lang['account_type_admin_confirmation']
			),
			array(
				'key' => 'auto_login',
				'name' => $lang['account_type_auto_login']
			)
		);
		$rlSmarty -> assign_by_ref( 'account_settings', $account_settings );
		
		/* get all languages */
		$allLangs = $GLOBALS['languages'];
		$rlSmarty -> assign_by_ref( 'allLangs', $allLangs );
		
		if ( $_GET['action'] == 'edit' )
		{
			$i_key = $rlValid -> xSql($_GET['type']);
	
			// get current account type info
			$item_info = $rlDb -> fetch( '*', array( 'Key' => $i_key ), "AND `Status` <> 'trash'", null, 'account_types', 'row' ) ;
		}
		
		if ( $_GET['action'] == 'edit' && !$_POST['fromPost'] )
		{
			$_POST['key'] = $item_info['Key'];
			$_POST['page'] = $item_info['Page'];
			$_POST['own_location'] = $item_info['Own_location'];
			$_POST['email_confirmation'] = $item_info['Email_confirmation'];
			$_POST['admin_confirmation'] = $item_info['Admin_confirmation'];
			$_POST['auto_login'] = $item_info['Auto_login'];
			$_POST['status'] = $item_info['Status'];
			$_POST['abilities'] = explode(',', $item_info['Abilities']);

			// get names
			$i_names = $rlDb -> fetch( array( 'Code', 'Value' ), array( 'Key' => 'account_types+name+'.$i_key ), "AND `Status` <> 'trash'", null, 'lang_keys' );
			foreach ($i_names as $nKey => $nVal)
			{
				$_POST['name'][$nVal['Code']] = $nVal['Value'];
			}
			
			// get desc
			$i_desc = $rlDb -> fetch( array( 'Code', 'Value' ), array( 'Key' => 'account_types+desc+'.$i_key ), "AND `Status` <> 'trash'", null, 'lang_keys' );
			foreach ($i_desc as $nKey => $nVal)
			{
				$_POST['description_'.$nVal['Code']] = $nVal['Value'];
			}
			
			$rlHook -> load('apPhpAccountTypesPost');
		}
		
		if ( isset($_POST['submit']) )
		{
			$errors = array();
			
			/* load the utf8 lib */
			loadUTF8functions('ascii', 'utf8_to_ascii', 'unicode');
			
			$f_key = $_POST['key'];
			
			/* check key exist (in add mode only) */
			if ($_GET['action'] == 'add')
			{
				/* check key */
				if ( !utf8_is_ascii( $f_key ) )
				{
					$f_key = utf8_to_ascii( $f_key );
				}
				
				if ( strlen( $f_key ) < 2 )
				{
					$errors[] = $lang['incorrect_phrase_key'];
				}
				
				$exist_key = $rlDb -> fetch( array('Key'), array( 'Key' => $f_key ), null, null, 'account_types' );

				if ( !empty($exist_key) )
				{
					$errors[] = str_replace( '{key}', "<b>\"".$f_key."\"</b>", $lang['notice_account_type_key_exist']);
				}
			}

			$f_key = $rlValid -> str2key( $f_key );
			
			/* check name */
			$f_name = $_POST['name'];
			
			foreach ($allLangs as $lkey => $lval )
			{
				if ( empty( $f_name[$allLangs[$lkey]['Code']] ) )
				{
					$errors[] = str_replace( '{field}', "<b>".$lang['name']."({$allLangs[$lkey]['name']})</b>", $lang['notice_field_empty']);
				}
				
				$f_names[$allLangs[$lkey]['Code']] = $f_name[$allLangs[$lkey]['Code']];
			}
			
			$rlHook -> load('apPhpAccountTypesValidate');

			if( !empty($errors) )
			{
				$rlSmarty -> assign_by_ref( 'errors', $errors );
			}
			else 
			{	
				/* add/edit action */
				if ( $_GET['action'] == 'add' )
				{
					// get max position
					$position = $rlDb -> getRow( "SELECT MAX(`Position`) AS `max` FROM `" . RL_DBPREFIX . "account_types`" );
					
					// write main type information
					$data = array(
						'Key' => $f_key,
						'Status' => $_POST['status'],
						'Abilities' => implode(',', $_POST['abilities']),
						'Page' => (int)$_POST['page'],
						'Own_location' => (int)$_POST['own_location'],
						'Email_confirmation' => (int)$_POST['email_confirmation'],
						'Admin_confirmation' => (int)$_POST['admin_confirmation'],
						'Auto_login' => (int)$_POST['auto_login'],
						'Position' => $position['max']+1
					);
					
					$rlHook -> load('apPhpAccountTypesBeforeAdd');
					
					if ( $action = $rlActions -> insertOne( $data, 'account_types' ) )
					{
						$rlHook -> load('apPhpAccountTypesAfterAdd');
						
						// add enum option to listing plans table
						$rlActions -> enumAdd('listing_plans', 'Allow_for', $f_key);
						
						foreach ($allLangs as $key => $value)
						{
							// write name's phrases
							$lang_keys[] = array(
								'Code' => $allLangs[$key]['Code'],
								'Module' => 'common',
								'Status' => 'active',
								'Key' => 'account_types+name+' . $f_key,
								'Value' => $f_name[$allLangs[$key]['Code']],
							);
							
							// save description
							$lang_keys[] = array(
								'Code' => $allLangs[$key]['Code'],
								'Module' => 'common',
								'Status' => 'active',
								'Key' => 'account_types+desc+' . $f_key,
								'Value' => $_POST['description_'.$allLangs[$key]['Code']],
							);
							
							if ( $_POST['page'] )
							{
								// individual page names
								$lang_keys[] = array(
									'Code' => $allLangs[$key]['Code'],
									'Module' => 'common',
									'Status' => 'active',
									'Key' => 'pages+name+at_'. $f_key,
									'Value' => $f_name[$allLangs[$key]['Code']] .' '. $lang['accounts']
								);
								
								// individual page titles
								$lang_keys[] = array(
									'Code' => $allLangs[$key]['Code'],
									'Module' => 'common',
									'Status' => 'active',
									'Key' => 'pages+title+at_'. $f_key,
									'Value' => $f_name[$allLangs[$key]['Code']] .' '. $lang['accounts']
								);
							}
						}
						
						// creat individual page
						if ( $_POST['page'] )
						{
							$page_position = $rlDb -> getRow( "SELECT MAX(`Position`) AS `max` FROM `" . RL_DBPREFIX . "pages`" );
							
							$individual_page = array(
								'Parent_ID' => 0,
								'Page_type' => 'system',
								'Login' => 0,
								'Key' => 'at_'. $f_key,
								'Position' => $page_position['max'] + 1,
								'Path' => $rlValid -> str2path($f_key) . '-accounts',
								'Controller' => 'account_type',
								'Tpl' => 1,
								'Menus' => 1,
								'Modified' => 'NOW()',
								'Status' => 'active',
								'Readonly' => 1
							);
							$rlActions -> insertOne( $individual_page, 'pages' );
						}
						
						$rlActions -> insert( $lang_keys, 'lang_keys' );
						
						$message = $lang['account_type_added'];
						$aUrl = array( 'controller' => $controller, 'request' => 'build', 'key' => $f_key );
					}
					else 
					{
						trigger_error( "Can't add new account type (MYSQL problems)", E_WARNING );
						$rlDebug -> logger("Can't add new account type (MYSQL problems)");
					}
				}
				elseif ( $_GET['action'] == 'edit' )
				{
					$update_date = array(
						'fields' => array( 
							'Status' => $_POST['status'],
							'Abilities' => implode(',', $_POST['abilities']),
							'Page' => (int)$_POST['page'],
							'Own_location' => (int)$_POST['own_location'],
							'Email_confirmation' => (int)$_POST['email_confirmation'],
							'Admin_confirmation' => (int)$_POST['admin_confirmation'],
							'Auto_login' => (int)$_POST['auto_login']
						),
						'where' => array( 'Key' => $f_key )
					);

					$rlHook -> load('apPhpAccountTypesBeforeEdit');
					
					$action = $GLOBALS['rlActions'] -> updateOne( $update_date, 'account_types' );
					
					$rlHook -> load('apPhpAccountTypesAfterEdit');

					foreach ($allLangs as $key => $value)
					{
						if ( $rlDb -> getOne('ID', "`Key` = 'account_types+name+{$f_key}' AND `Code` = '{$allLangs[$key]['Code']}'", 'lang_keys') )
						{
							// edit names
							$update_phrases = array(
								'fields' => array(
									'Value' => $_POST['name'][$allLangs[$key]['Code']]
								),
								'where' => array(
									'Code' => $allLangs[$key]['Code'],
									'Key' => 'account_types+name+' . $f_key
								)
							);
							
							// update
							$rlActions -> updateOne( $update_phrases, 'lang_keys' );
						}
						else
						{
							// insert names
							$insert_phrases = array(
								'Code' => $allLangs[$key]['Code'],
								'Module' => 'common',
								'Key' => 'account_types+name+' . $f_key,
								'Value' => $_POST['name'][$allLangs[$key]['Code']]
							);
							
							// insert
							$rlActions -> insertOne( $insert_phrases, 'lang_keys' );
						}
						
						if ( $rlDb -> getOne('ID', "`Key` = 'account_types+desc+{$f_key}' AND `Code` = '{$allLangs[$key]['Code']}'", 'lang_keys') )
						{
							// edit descriptions
							$update_phrases = array(
								'fields' => array(
									'Value' => $_POST['description_'.$allLangs[$key]['Code']]
								),
								'where' => array(
									'Code' => $allLangs[$key]['Code'],
									'Key' => 'account_types+desc+' . $f_key
								)
							);
							
							// update
							$rlActions -> updateOne( $update_phrases, 'lang_keys' );
						}
						else
						{
							// insert description
							$insert_phrases = array(
								'Code' => $allLangs[$key]['Code'],
								'Module' => 'common',
								'Key' => 'account_types+desc+' . $f_key,
								'Value' => $_POST['description_'.$allLangs[$key]['Code']]
							);
							
							// insert
							$rlActions -> insertOne( $insert_phrases, 'lang_keys' );
						}
					}
					
					/* individual page tracking */
					if ( $item_info['Page'] && !(int)$_POST['page'] )
					{
						// suspend page
						$suspend_page = array(
							'fields' => array(
								'Status' => 'trash'
							),
							'where' => array(
								'Key' => 'at_'. $f_key
							)
						);
						$rlActions -> updateOne($suspend_page, 'pages');
						
						// suspend phrases
						$suspend_phrases[] = array(
							'fields' => array(
								'Status' => 'trash'
							),
							'where' => array(
								'Key' => 'pages+name+at_'. $f_key
							)
						);
						
						$suspend_phrases[] = array(
							'fields' => array(
								'Status' => 'trash'
							),
							'where' => array(
								'Key' => 'pages+title+at_'. $f_key
							)
						);
						$rlActions -> update($suspend_phrases, 'lang_keys');
					}
					else if ( !$item_info['Page'] && (int)$_POST['page'] )
					{
						if ( !$rlDb -> getOne('ID', "`Key` = 'at_{$f_key}'", 'pages') )
						{
							// create page
							$page_position = $rlDb -> getRow( "SELECT MAX(`Position`) AS `max` FROM `" . RL_DBPREFIX . "pages`" );
								
							$individual_page = array(
								'Parent_ID' => 0,
								'Page_type' => 'system',
								'Login' => 0,
								'Key' => 'at_'. $f_key,
								'Position' => $page_position['max'] + 1,
								'Path' => $rlValid -> str2path($f_key) . '-accounts',
								'Controller' => 'account_type',
								'Tpl' => 1,
								'Menus' => 1,
								'Modified' => 'NOW()',
								'Status' => 'active',
								'Readonly' => 1
							);
							$rlActions -> insertOne( $individual_page, 'pages' );
							
							// add phrases
							foreach ($allLangs as $key => $value)
							{
								// individual page names
								$lang_keys[] = array(
									'Code' => $allLangs[$key]['Code'],
									'Module' => 'common',
									'Status' => 'active',
									'Key' => 'pages+name+at_'. $f_key,
									'Value' => $f_name[$allLangs[$key]['Code']] .' '. $lang['accounts']
								);
								
								// individual page titles
								$lang_keys[] = array(
									'Code' => $allLangs[$key]['Code'],
									'Module' => 'common',
									'Status' => 'active',
									'Key' => 'pages+title+at_'. $f_key,
									'Value' => $f_name[$allLangs[$key]['Code']] .' '. $lang['accounts']
								);
							}
							$rlActions -> insert( $lang_keys, 'lang_keys' );
						}
						// activate page
						else
						{
							$activate_page = array(
								'fields' => array(
									'Status' => 'active'
								),
								'where' => array(
									'Key' => 'at_'. $f_key
								)
							);
							$rlActions -> updateOne($activate_page, 'pages');
							
							// activate phrases
							$activate_phrases[] = array(
								'fields' => array(
									'Status' => 'active'
								),
								'where' => array(
									'Key' => 'pages+name+at_'. $f_key
								)
							);
							
							$activate_phrases[] = array(
								'fields' => array(
									'Status' => 'active'
								),
								'where' => array(
									'Key' => 'pages+title+at_'. $f_key
								)
							);
							$rlActions -> update($activate_phrases, 'lang_keys');
						}
					}
					/* individual page tracking end */

					$message = $lang['account_type_edited'];
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
	elseif ( $_GET['action'] == 'build' )
	{
		$reefless -> loadClass( 'Builder', 'admin' );
		$rlXajax -> registerFunction( array( 'buildForm', $rlBuilder, 'ajaxBuildForm' ) );
		
		$type_key = $rlValid -> xSql($_GET['key']);
		$form_type = $_GET['form'];

		if ( !$type_key || !$form_type )
		{
			$errors[] = 'FORM BUILDER ERROR: Bad request, please contact software support.';
		}
		else
		{
			/* get current account type info */
			$type_info = $rlDb -> fetch( array('ID', 'Key'), array( 'Key' => $type_key ), "AND `Status` <> 'trash'", null, 'account_types', 'row' );
			$type_info = $rlLang -> replaceLangKeys( $type_info, 'account_types', array( 'name' ), RL_LANG_CODE, 'admin' );
			$rlSmarty -> assign_by_ref('category_info', $type_info);
	
			$rlSmarty -> assign('cpTitle', $type_info['name']);
			
			switch ($form_type){
				case 'reg_form':
					$rlBuilder -> rlBuildTable = 'account_submit_form';
					$rlBuilder -> rlBuildField = 'Field_ID';
				
					/* additional bread crumb step */
					$bcAStep = $lang['build_register_form'];
					break;
					
				case 'short_form':
					$rlBuilder -> rlBuildTable = 'account_short_form';
					$rlBuilder -> rlBuildField = 'Field_ID';
				
					/* additional bread crumb step */
					$bcAStep = $lang['account_short_form_builder'];
					break;
					
				case 'search_form':
					$rlBuilder -> rlBuildTable = 'account_search_relations';
					$rlBuilder -> rlBuildField = 'Field_ID';
					
					/* additional bread crumb step */
					$bcAStep = $lang['search_form_builder'];
					break;
			}
			
			$rlHook -> load('apPhpAccountTypesBuildSwitch');
			
			/* get available fields for current type */
			$avail_fields = $rlDb -> fetch( array('Group_ID', 'Field_ID'), array( 'Category_ID' => $type_info['ID'] ), null, null, 'account_search_relations' );
			
			foreach ($avail_fields as $aKey => $aVal)
			{			
				if ($avail_fields[$aKey]['Group_ID'])
				{
					$tmp_fields = explode( ',', $avail_fields[$aKey]['Fields'] );
					foreach ( $tmp_fields as $tmpKey => $tmpVal )
					{
						if (!empty($tmpVal))
						{
							$a_fields .= "`ID` = '{$tmpVal}' OR ";
						}
					}
				}
				else
				{
					$f = (int)$avail_fields[$aKey]['Fields'];
					$a_fields .= "`ID` = '{$f}' OR ";
				}
				
			}
			$a_fields = substr( $a_fields, 0, -4 );
	
			/* get form fields for current type */
			$relations = $rlBuilder -> getFormRelations( $type_info['ID'], 'account_fields' );
			$rlSmarty -> assign_by_ref( 'relations', $relations );
	
			foreach ( $relations as $rKey => $rValue )
			{
				$no_groups[] = $relations[$rKey]['Key'];
				
				$f_fields = $relations[$rKey]['Fields'];
				
				if ( $relations[$rKey]['Group_ID'] )
				{
					foreach ( $f_fields as $fKey => $fValue )
					{
						$no_fields[] = $f_fields[$fKey]['Key'];
					}
				}
				else
				{
					$no_fields[] = $relations[$rKey]['Fields']['Key'];
				}
			}
	
			$fields = $rlDb -> fetch( array('ID', 'Key', 'Type', 'Status'), null, "WHERE `Status` <> 'trash' {$add_cond}", null, 'account_fields' );
			$fields = $rlLang -> replaceLangKeys( $fields, 'account_fields', array( 'name' ), RL_LANG_CODE, 'admin' );
	
			// hide already using fields
			if ( !empty( $no_fields ) )
			{
				foreach ($fields as $fKey => $fVal)
				{
					if ( false !== array_search( $fields[$fKey]['Key'], $no_fields ) )
					{
						$fields[$fKey]['hidden'] = true;
					}
				}
			}
		
			$rlSmarty -> assign_by_ref( 'fields', $fields );
		}
	}
	
	$rlHook -> load('apPhpAccountTypesBottom');
}