<?php

/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: BLOCKS.INC.PHP
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
		
		$rlHook -> load('apExtBlocksUpdate');
		
		$rlActions -> updateOne( $updateData, 'blocks');
		exit;
	}
	
	/* data read */
	$limit = $rlValid -> xSql( $_GET['limit'] );
	$start = $rlValid -> xSql( $_GET['start'] );
	$sort = $rlValid -> xSql( $_GET['sort'] );
	$sortDir = $rlValid -> xSql( $_GET['dir'] );

	$sql = "SELECT SQL_CALC_FOUND_ROWS DISTINCT `T1`.*, `T2`.`Value` AS `name` ";
	$sql .= "FROM `". RL_DBPREFIX ."blocks` AS `T1` ";
	$sql .= "LEFT JOIN `".RL_DBPREFIX."lang_keys` AS `T2` ON CONCAT('blocks+name+',`T1`.`Key`) = `T2`.`Key` AND `T2`.`Code` = '". RL_LANG_CODE ."' ";
	$sql .= "WHERE `T1`.`Status` <> 'trash' ";
	if ( $sort )
	{
		$sortField = $sort == 'name' ? "`T2`.`Value`" : "`T1`.`{$sort}`";
		$sql .= "ORDER BY {$sortField} {$sortDir} ";
	}
	$sql .= "LIMIT {$start}, {$limit}";
	
	$rlHook -> load('apExtBlocksSql');
	
	$data = $rlDb -> getAll($sql);
	
	foreach ( $data as $key => $value )
	{
		$data[$key]['Status'] = $GLOBALS['lang'][$data[$key]['Status']];
		$data[$key]['Tpl'] = $data[$key]['Tpl'] ? $lang['yes'] : $lang['no'] ;
		$data[$key]['Side'] = $GLOBALS['lang'][$data[$key]['Side']];
		$data[$key]['Type'] = $GLOBALS['lang'][$data[$key]['Type']];
	}
	
	$rlHook -> load('apExtBlocksData');
	
	$count = $rlDb -> getRow( "SELECT FOUND_ROWS() AS `count`" );
	
	$reefless -> loadClass( 'Json' );
	
	$output['total'] = $count['count'];
	$output['data'] = $data;

	echo $rlJson -> encode( $output );
}
/* ext js action end */

else 
{
	$rlHook -> load('apPhpBlocksTop');
	
	$reefless -> loadClass('Categories');
	
	/* additional bread crumb step */
	if ($_GET['action'])
	{
		$bcAStep = $_GET['action'] == 'add' ? $lang['add_block'] : $lang['edit_block'] ;
	}
	
	if ($_GET['action'] == 'add' || $_GET['action'] == 'edit')
	{
		/* get categories/section */
		$sections = $rlCategories -> getCatTree(0, false, true);
		$rlSmarty -> assign_by_ref( 'sections', $sections );
		
		/* get all languages */
		$allLangs = $GLOBALS['languages'];
		$rlSmarty -> assign_by_ref( 'allLangs', $allLangs );
		
		/* get pages list */
		$pages = $rlDb -> fetch( array('ID', 'Key'), array('Tpl' => 1), "AND `Status` = 'active' ORDER BY `Key`", null, 'pages' );
		$pages = $rlLang -> replaceLangKeys( $pages, 'pages', array( 'name' ), RL_LANG_CODE, 'admin' );
		$rlSmarty -> assign_by_ref( 'pages', $pages );
		
		$b_key = $rlValid -> xSql($_GET['block']);

		// get current block info
		$block_info = $rlDb -> fetch( '*', array( 'Key' => $b_key ), "AND `Status` <> 'trash'", null, 'blocks', 'row' );
		$rlSmarty -> assign_by_ref( 'block', $block_info );
		
		// clear cache
		if ( !$_POST['submit'] && !$_POST['xjxfun'] )
		{
			unset($_SESSION['categories']);
		}
		
		if ($_GET['action'] == 'edit' && !$_POST['fromPost'])
		{
			unset($_SESSION['categories']);
			
			$_POST['key'] = $block_info['Key'];
			$_POST['status'] = $block_info['Status'];
			$_POST['side'] = $block_info['Side'];
			$_POST['tpl'] = $block_info['Tpl'];
			$_POST['type'] = $block_info['Type'];
			
			if ( $block_info['Type'] == 'html' )
			{
				$content = $rlDb -> fetch( array( 'Code', 'Value' ), array( 'Key' => 'blocks+content+'.$block_info['Key'] ), "AND `Status` <> 'trash'", null, 'lang_keys' );

				foreach ($content as $cKey => $cVal)
				{
					$_POST['html_content_'.$content[$cKey]['Code']] = $content[$cKey]['Value'];
				}
			}
			else
			{
				$_POST['content'] = $block_info['Content'];
			}

			$_POST['type'] = $block_info['Type'];
			$_POST['show_on_all'] = $block_info['Sticky'];
			$_POST['cat_sticky'] = $block_info['Cat_sticky'];
			$_POST['subcategories'] = $block_info['Subcategories'];
			$_POST['categories'] = explode(',', $block_info['Category_ID']);
						
			$m_pages = explode(',', $block_info['Page_ID']);
			foreach ($m_pages as $page_id)
			{
				$_POST['pages'][$page_id] = $page_id;
			}
			unset($m_pages);

			// get names
			$names = $rlDb -> fetch( array( 'Code', 'Value' ), array( 'Key' => 'blocks+name+'.$b_key ), "AND `Status` <> 'trash'", null, 'lang_keys' );
			foreach ($names as $nKey => $nVal)
			{
				$_POST['name'][$names[$nKey]['Code']] = $names[$nKey]['Value'];
			}
			
			$rlHook -> load('apPhpBlocksPost');
		}
		
		/* get parent points */
		if ( $_POST['categories'] )
		{
			$rlCategories -> parentPoints($_POST['categories']);
		}
		
		if ( isset($_POST['submit']) )
		{
			$errors = array();
			
			$f_key = $_POST['key'];
			
			/* load the utf8 lib */
			loadUTF8functions('ascii', 'utf8_to_ascii', 'unicode');

			$_SESSION['categories'] = $_POST['categories'];
			
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
					$error_fields[] = 'key';
				}
				
				$exist_key = $rlDb -> fetch( array('Key'), array( 'Key' => $f_key ), null, null, 'blocks' );

				if ( !empty($exist_key) )
				{
					$errors[] = str_replace( '{key}', "<b>\"".$f_key."\"</b>", $lang['notice_block_exist']);
					$error_fields[] = 'key';
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
					$error_fields[] = "name[{$lval['Code']}]";
				}
				
				$f_names[$allLangs[$lkey]['Code']] = $f_name[$allLangs[$lkey]['Code']];
			}
			
			/* check side */
			$f_side = $_POST['side'];
			
			if ( empty($f_side) )
			{
				$errors[] = str_replace( '{field}', "<b>\"".$lang['block_side']."\"</b>", $lang['notice_select_empty']);
				$error_fields[] = 'side';
			}
			
			/* check type */
			$f_type = $_POST['type'];
			
			if ( empty($f_type) )
			{
				$errors[] = str_replace( '{field}', "<b>\"".$lang['block_type']."\"</b>", $lang['notice_select_empty']);
				$error_fields[] = 'type';
			}

			/* check content */
			if ( $f_type == 'html' )
			{
				foreach ($allLangs as $lkey => $lval )
				{
					if ( empty( $_POST['html_content_'.$allLangs[$lkey]['Code']] ) )
					{
						$errors[] = str_replace( '{field}', "<b>".$lang['content']."({$allLangs[$lkey]['name']})</b>", $lang['notice_field_empty']);
						$error_fields[] = 'html_content_'. $lval['Code'];
					}
				}
			}
			else
			{
				$f_content = $_POST['content'];

				if ( empty($f_content) )
				{
					$errors[] = str_replace( '{field}', "<b>\"".$lang['content']."\"</b>", $lang['notice_field_empty']);
					$error_fields[] = 'content';
				}
			}

			if ( $f_type == 'php' || $f_type == 'smarty' )
			{
				$f_content = str_replace( '&', '&amp;', $f_content);
			}
			
			$rlHook -> load('apPhpBlocksValidate');
			
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
					$position = $rlDb -> getRow( "SELECT MAX(`Position`) AS `max` FROM `" . RL_DBPREFIX . "blocks`" );

					// write main, block information
					$data = array(
						'Key' => $f_key,
						'Status' => $_POST['status'],
						'Position' => $position['max']+1,
						'Side' => $f_side,
						'Type' => $f_type,
						'Tpl' => $_POST['tpl'],
						'Page_ID' => implode(',', $_POST['pages']),
						'Category_ID' => implode(',', $_POST['categories']),
						'Subcategories' => empty($_POST['subcategories']) ? 0 : 1,
						'Sticky' => empty($_POST['show_on_all']) ? 0 : 1,
						'Cat_sticky' => empty($_POST['cat_sticky']) ? 0 : 1
					);
					
					if ( $f_type != 'html' )
					{
						$data['Content'] = $f_content;
					}

					$rlHook -> load('apPhpBlocksBeforeAdd');
					
					if ( $action = $rlActions -> insertOne( $data, 'blocks' ) )
					{
						$rlHook -> load('apPhpBlocksAfterAdd');
						
						// write name's phrases
						foreach ($allLangs as $key => $value)
						{
							$lang_keys[] = array(
								'Code' => $allLangs[$key]['Code'],
								'Module' => 'common',
								'Status' => 'active',
								'Key' => 'blocks+name+' . $f_key,
								'Value' => $f_name[$allLangs[$key]['Code']],
							);
							
							// add content for html block
							if ( $f_type == 'html' )
							{
								$lang_keys[] = array(
									'Code' => $allLangs[$key]['Code'],
									'Module' => 'common',
									'Status' => 'active',
									'Key' => 'blocks+content+' . $f_key,
									'Value' => $_POST['html_content_'.$allLangs[$key]['Code']],
								);
							}
						}

						$rlActions -> insert( $lang_keys, 'lang_keys' );
						
						$message = $lang['block_added'];
						$aUrl = array( "controller" => $controller );
					}
					else 
					{
						trigger_error( "Can't add new block (MYSQL problems)", E_WARNING );
						$rlDebug -> logger("Can't add new block (MYSQL problems)");
					}
				}
				elseif ( $_GET['action'] == 'edit' )
				{
					$update_data = array(
						'fields' => array(
							'Status' => $_POST['status'],
							'Side' => $f_side,
							'Tpl' => $_POST['tpl'],
							'Page_ID' => implode(',', $_POST['pages']),
							'Sticky' => empty($_POST['show_on_all']) ? 0 : 1,
							'Category_ID' => $_POST['cats_sticky'] ? '' : implode(',', $_POST['categories']),
							'Subcategories' => empty($_POST['subcategories']) ? 0 : 1,
							'Cat_sticky' => empty($_POST['cat_sticky']) ? 0 : 1
						),
						'where' => array( 'Key' => $f_key )
					);

					if ( empty($block_info['Plugin']) )
					{
						$update_data['fields']['Type'] = $f_type;
						$update_data['fields']['Content'] = $f_content;
					}

					$rlHook -> load('apPhpBlocksBeforeEdit');
					
					$action = $GLOBALS['rlActions'] -> updateOne( $update_data, 'blocks' );
					
					$rlHook -> load('apPhpBlocksAfterEdit');

					foreach ($allLangs as $key => $value)
					{
						if ( $rlDb -> getOne('ID', "`Key` = 'blocks+name+{$f_key}' AND `Code` = '{$allLangs[$key]['Code']}'", 'lang_keys') )
						{
							// edit name's values
							$update_names = array(
								'fields' => array(
									'Value' => $_POST['name'][$allLangs[$key]['Code']]
								),
								'where' => array(
									'Code' => $allLangs[$key]['Code'],
									'Key' => 'blocks+name+' . $f_key
								)
							);
							
							// update
							$rlActions -> updateOne( $update_names, 'lang_keys' );
						}
						else
						{
							// insert names
							$insert_names = array(
								'Code' => $allLangs[$key]['Code'],
								'Module' => 'common',
								'Key' => 'blocks+name+' . $f_key,
								'Value' => $_POST['name'][$allLangs[$key]['Code']]
							);
							
							// insert
							$rlActions -> insertOne( $insert_names, 'lang_keys' );
						}
						
						if ($f_type == 'html')
						{
							if ( $rlDb -> getOne('ID', "`Key` = 'blocks+content+{$f_key}' AND `Code` = '{$allLangs[$key]['Code']}'", 'lang_keys') )
							{
								$lang_keys_content['where'] = array(
									'Code' => $allLangs[$key]['Code'],
									'Key' => 'blocks+content+' . $f_key
								);
								
								$lang_keys_content['fields'] = array(
									'Value' => $_POST['html_content_'.$allLangs[$key]['Code']]
								);
								
								// update
								$GLOBALS['rlActions'] -> updateOne( $lang_keys_content, 'lang_keys' );
							}
							else
							{
								// insert content
								$insert_content = array(
									'Code' => $allLangs[$key]['Code'],
									'Module' => 'common',
									'Key' => 'blocks+content+' . $f_key,
									'Value' => $_POST['html_content_'.$allLangs[$key]['Code']]
								);
								
								// insert
								$rlActions -> insertOne( $insert_content, 'lang_keys' );
							}
						}
					}

					$message = $lang['block_edited'];
					$aUrl = array( "controller" => $controller );
				}
				
				if ( $action )
				{
					unset($_SESSION['categories']);
					
					$reefless -> loadClass( 'Notice' );
					$rlNotice -> saveNotice( $message );
					$reefless -> redirect( $aUrl );
				}
			}
		}
		$rlXajax -> registerFunction( array( 'getCatLevel', $rlCategories, 'ajaxGetCatLevel' ) );
		$rlXajax -> registerFunction( array( 'openTree', $rlCategories, 'ajaxOpenTree' ) );
	}

	$reefless -> loadClass( 'Admin', 'admin' );
	
	/* register ajax methods */
	$rlXajax -> registerFunction( array( 'deleteBlock', $rlAdmin, 'ajaxDeleteBlock' ) );
}