<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LISENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: FAQS.INC.PHP
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
if ($_GET['q'] == 'ext')
{
	/* system config */
	require_once( '../../../includes/config.inc.php' );
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
		
		$rlActions -> updateOne( $updateData, 'faqs');
		exit;
	}
	
	/* data read */
	$limit = $rlValid -> xSql( $_GET['limit'] );
	$start = $rlValid -> xSql( $_GET['start'] );
	$sort = $rlValid -> xSql( $_GET['sort'] );
	$sortDir = $rlValid -> xSql( $_GET['dir'] );

	$sql = "SELECT SQL_CALC_FOUND_ROWS DISTINCT `T1`.*, `T1`.`ID` AS `Key`, `T2`.`Value` AS `title` ";
	$sql .= "FROM `". RL_DBPREFIX ."faqs` AS `T1` ";
	$sql .= "LEFT JOIN `".RL_DBPREFIX."lang_keys` AS `T2` ON CONCAT('faqs+title+',`T1`.`ID`) = `T2`.`Key` AND `T2`.`Code` = '". RL_LANG_CODE ."' ";
	$sql .= "WHERE `T1`.`Status` <> 'trash' ";
	if ( $sort )
	{
		$sortField = $sort == 'title' ? "`T2`.`Value`" : "`T1`.`{$sort}`";
		$sql .= "ORDER BY {$sortField} {$sortDir} ";
	}
	$sql .= "LIMIT {$start}, {$limit}";
	
	$data = $rlDb -> getAll($sql);

	foreach ( $data as $key => $value )
	{
		$data[$key]['Status'] = $GLOBALS['lang'][$data[$key]['Status']];
	}
	
	$count = $rlDb -> getRow( "SELECT FOUND_ROWS() AS `count`" );
	
	$reefless -> loadClass( 'Json' );
	
	$output['total'] = $count['count'];
	$output['data'] = $data;
	
	echo $rlJson -> encode( $output );
}
/* ext js action end */

else 
{
	$reefless -> loadClass( 'FAQs', null, 'FAQs');
	
	/* additional bread crumb step */
	if ($_GET['action'])
	{
		$bcAStep = $_GET['action'] == 'add' ? $lang['faq_add_faqs'] : $lang['faq_edit_faqs'] ;
	}
	
	if ($_GET['action'] == 'add' || $_GET['action'] == 'edit')
	{
		/* get all languages */
		$allLangs = $GLOBALS['languages'];
		$rlSmarty -> assign_by_ref( 'allLangs', $allLangs );
		
		if ( $_GET['action'] == 'edit' )
		{
			$id = (int)$_GET['faqs'];
		}
		
		if ($_GET['action'] == 'edit' && !$_POST['fromPost'])
		{
			// get faqs info
			$faqs_info = $rlDb -> fetch( '*', array( 'ID' => $id ), "AND `Status` <> 'trash'", 1, 'faqs', 'row' ) ;

			$_POST['status'] = $faqs_info['Status'];
			$_POST['path'] = $faqs_info['Path'];
			$_POST['date'] = $faqs_info['Date'];

			// get titles
			$e_titles = $rlDb -> fetch( array( 'Code', 'Value' ), array( 'Key' => 'faqs+title+'.$id ), "AND `Status` <> 'trash'", null, 'lang_keys' );
			foreach ($e_titles as $nKey => $nVal)
			{
				$_POST['name'][$e_titles[$nKey]['Code']] = $e_titles[$nKey]['Value'];
			}
			
			// get content
			$e_content = $rlDb -> fetch( array( 'Code', 'Value' ), array( 'Key' => 'faqs+content+'.$id ), "AND `Status` <> 'trash'", null, 'lang_keys' );
			foreach ($e_content as $nKey => $nVal)
			{
				$_POST['content_' . $e_content[$nKey]['Code']] = $e_content[$nKey]['Value'];
			}
		}
		
		if ( isset($_POST['submit']) )
		{
			$errors = array();

			/* load the utf8 lib */
			loadUTF8functions('ascii', 'utf8_to_ascii', 'unicode');
			
			/* check titles */
			$f_title = $_POST['name'];
			
			if ( empty( $f_title[$config['lang']] ) )
			{
				$errors[] = str_replace( '{field}', "<b>".$lang['title']."</b>", $lang['notice_field_empty']);
				$error_fields[] = "name[{$config['lang']}]";
			}

			/* check content */
			foreach ($allLangs as $lkey => $lval )
			{
				$f_content[$allLangs[$lkey]['Code']] = $_POST['content_' . $allLangs[$lkey]['Code']];
			}
			if ( empty( $_POST['content_' . $config['lang']] ) )
			{
				$errors[] = str_replace( '{field}', "<b>".$lang['content']."</b>", $lang['notice_field_empty']);
			}
			
			
			/* check path */
			$f_path = $_POST['path'];
			
			if ( !utf8_is_ascii( $f_path ) )
			{
				$f_path = utf8_to_ascii( $f_path );
			}
			
			$f_path = $rlValid -> str2path( $f_path );
			
			if ( strlen( $f_path ) < 3 )
			{
				$errors[] = $lang['incorrect_page_address'];
				$error_fields[] = 'path';
			}
			
			$exist_path = $rlDb -> getOne('ID', "`Path` = '{$f_path}' AND `ID` <> '{$id}'", 'faqs');

			if ( $exist_path )
			{
				$errors[] = str_replace( '{path}', "<b>{$f_path}</b>", $lang['notice_page_path_exist']);
			}
			
			if( !empty($errors) )
			{
				$rlSmarty -> assign_by_ref( 'errors', $errors );
			}
			else 
			{	
				/* add/edit action */
				if ( $_GET['action'] == 'add' )
				{
					// write main section information
					$data = array(
						'Status' => $_POST['status'],
						'Path' => $f_path,
						'Date' => 'NOW()'
					);
					
					if ( $action = $rlActions -> insertOne( $data, 'faqs' ) )
					{
						$faqs_id = mysql_insert_id();
						
						// save faqs content
						foreach ($allLangs as $key => $value)
						{
							$lang_keys[] = array(
								'Code' => $allLangs[$key]['Code'],
								'Module' => 'common',
								'Status' => 'active',
								'Plugin' => 'FAQs',
								'Key' => 'faqs+title+' . $faqs_id,
								'Value' => !empty($f_title[$allLangs[$key]['Code']]) ? $f_title[$allLangs[$key]['Code']] : $f_title[$config['lang']]
							);
							
							$lang_keys[] = array(
								'Code' => $allLangs[$key]['Code'],
								'Module' => 'common',
								'Status' => 'active',
								'Plugin' => 'FAQs',
								'Key' => 'faqs+content+' . $faqs_id,
								'Value' => !empty($f_content[$allLangs[$key]['Code']]) ? $f_content[$allLangs[$key]['Code']] : $f_content[$config['lang']]
							);
						}

						$rlActions -> insert( $lang_keys, 'lang_keys' );
						
						$message = $lang['faq_faqs_added'];
						$aUrl = array( "controller" => $controller );
					}
					else 
					{
						trigger_error( "Can't add faq article (MYSQL problems)", E_WARNING );
						$rlDebug -> logger("Can't add faq article (MYSQL problems)");
					}
				}
				elseif ( $_GET['action'] == 'edit' )
				{
					$update_date = array(
						'fields' => array(
							'Status' => $_POST['status'],
							'Date' => $_POST['date'],
							'Path' => $f_path
						),
						'where' => array( 'ID' => $id )
					);
					
					$action = $GLOBALS['rlActions'] -> updateOne( $update_date, 'faqs' );

					foreach ($allLangs as $key => $value)
					{
						// edit titles
						if ( $rlDb -> getOne('ID', "`Key` = 'faqs+title+{$id}' AND `Code` = '{$allLangs[$key]['Code']}'", 'lang_keys') )
						{
							$lang_phrase[] = array(
								'where' => array(
									'Code' => $allLangs[$key]['Code'],
									'Key' => 'faqs+title+' . $id
								),
								'fields' => array(
									'Value' => !empty($f_title[$allLangs[$key]['Code']]) ? $f_title[$allLangs[$key]['Code']] : $f_title[$config['lang']]
								)
							);
						}
						else
						{
							// insert titles
							$insert_title = array(
								'Code' => $allLangs[$key]['Code'],
								'Module' => 'common',
								'Plugin' => 'FAQs',
								'Key' => 'faqs+title+' . $id,
								'Value' => !empty($f_title[$allLangs[$key]['Code']]) ? $f_title[$allLangs[$key]['Code']] : $f_title[$config['lang']]
							);
							
							// insert
							$rlActions -> insertOne( $insert_title, 'lang_keys' );
						}
						
						if ( $rlDb -> getOne('ID', "`Key` = 'faqs+content+{$id}' AND `Code` = '{$allLangs[$key]['Code']}'", 'lang_keys') )
						{
							// edit content
							$lang_phrase[] = array(
								'where' => array(
									'Code' => $allLangs[$key]['Code'],
									'Key' => 'faqs+content+' . $id
								),
								'fields' => array(
									'Value' => !empty($f_content[$allLangs[$key]['Code']]) ? $f_content[$allLangs[$key]['Code']] : $f_content[$config['lang']]
								)
							);
						}
						else
						{
							// insert contents
							$insert_contents = array(
								'Code' => $allLangs[$key]['Code'],
								'Module' => 'common',
								'Plugin' => 'FAQs',
								'Key' => 'faqs+content+' . $id,
								'Value' => !empty($f_content[$allLangs[$key]['Code']]) ? $f_content[$allLangs[$key]['Code']] : $f_content[$config['lang']]
							);
							
							// insert
							$rlActions -> insertOne( $insert_contents, 'lang_keys' );
						}
					}
					
					// update
					$rlActions -> update( $lang_phrase, 'lang_keys' );

					$message = $lang['faq_faqs_edited'];
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

	$reefless -> loadClass( 'Categories' );
	
	/* register ajax methods */
	$rlXajax -> registerFunction( array( 'deleteFAQs', $rlFAQs, 'ajaxDeleteFAQs' ) );
}