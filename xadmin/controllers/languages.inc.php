<?php

/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: LANGUAGES.INC.PHP
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
if ($_GET['q'] == 'ext_list')
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
		
		$rlHook -> load('apExtLanguagesUpdate');
		
		$rlActions -> updateOne( $updateData, 'languages');
		exit;
	}
	
	/* data read */
	$limit = $rlValid -> xSql( $_GET['limit'] );
	$start = $rlValid -> xSql( $_GET['start'] );

	$sql = "SELECT SQL_CALC_FOUND_ROWS DISTINCT COUNT(`T2`.`ID`) AS `Number`, `T1`.* FROM `". RL_DBPREFIX ."languages` AS `T1` ";
	$sql .= "LEFT JOIN `". RL_DBPREFIX ."lang_keys` AS `T2` ON `T1`.`Code` = `T2`.`Code` ";
	$sql .= "WHERE `T1`.`Status` <> 'trash' AND `T2`.`Module` <> 'email_tpl' AND `T2`.`Key` NOT LIKE 'data_formats+name+%' GROUP BY `T2`.`Code` ORDER BY `ID` ";
	$sql .= "LIMIT {$start}, {$limit}";
	
	$rlHook -> load('apExtLanguagesSql');
	
	$data = $rlDb -> getAll($sql);

	foreach ( $data as $key => $value )
	{
		$data[$key]['Status'] = $GLOBALS['lang'][$data[$key]['Status']];
		$is_current = $config['lang'] == $value['Code'] ? 'true' : 'false';
		$data[$key]['Data'] = $value['ID'] .'|'. $is_current;
		$data[$key]['Direction'] = $GLOBALS['lang'][$data[$key]['Direction'] .'_direction_title'];
		$data[$key]['name'] = $rlLang -> replaceLangKeys( 'languages+name+'. $data[$key]['Key'], null, null, $data[$key]['Code'] );
		if ( $value['Code'] == $config['lang'] )
		{
			$data[$key]['name'] .= ' <b>('.$lang['default'].')</b>';
		}
		else
		{
			$data[$key]['name'] .= ' | <a class="green_11_bg" href="javascript:void(0)" onclick="xajax_setDefault( \'langs_container\', \''. $value['Code'] .'\' );"><b>'. $lang['set_default'] .'</b></a>';
		}
	}
	
	$rlHook -> load('apExtAccountFieldsData');
	
	$count = $rlDb -> getRow( "SELECT FOUND_ROWS() AS `count`" );
	
	$reefless -> loadClass( 'Json' );
	
	$output['total'] = $count['count'];
	$output['data'] = $data;

	echo $rlJson -> encode( $output );
}
elseif ($_GET['q'] == 'ext')
{
	/* system config */
	require_once( '../../includes/config.inc.php' );
	require_once( RL_ADMIN_CONTROL . 'ext_header.inc.php' );
	
	/* date update */
	if ($_GET['action'] == 'update' )
	{
		$reefless -> loadClass( 'Actions' );
		$type = $rlValid -> xSql( $_GET['type'] );
		$field = $rlValid -> xSql( $_GET['field'] );
		
		/* trim NL */
		$value = $_GET['value'];
		$value = trim($value, PHP_EOL);
		
		//preg_match('/[\n*]?(.*)[\n*]?/', $value, $matches);
		//print_r($matches);
		
		$id = $rlValid -> xSql( $_GET['id'] );
		$lang_code = $rlValid -> xSql( $_GET['lang_code'] );
		
		$updateData = array(
			'fields' => array(
				$field => $value
			),
			'where' => array(
				'ID' => $id
			)
		);
		
		$rlHook -> load('apExtPhrasesUpdate');
		
		$rlActions -> updateOne( $updateData, 'lang_keys');
		exit;
	}
	
	/* data read */
	$limit = $rlValid -> xSql( $_GET['limit'] );
	$start = $rlValid -> xSql( $_GET['start'] );
	$sort = $rlValid -> xSql( $_GET['sort'] );
	$sort = $sort ? $sort : 'Value';
	$sortDir = $rlValid -> xSql( $_GET['dir'] );
	$sortDir = $sortDir ? $sortDir : 'ASC';
	
	$langCode = $_GET['lang_id'] ? $rlDb -> getOne('Code', "`ID` = '{$_GET['lang_id']}'", 'languages') : $rlValid -> xSql( $_GET['lang_code'] );
	$phrase = str_replace( ' ', '%' , $rlValid -> xSql( $_GET['phrase'] ) );
	
	if (isset($_GET['action']) && $_GET['action'] == 'search')
	{
		$criteria = $_GET['criteria'];

		$where = '1';
		
		if ($langCode != 'all')
		{
			$where = "`Code` = '{$langCode}'";
		}

		if ( $criteria == 'in_value' )
		{
			$sql = "SELECT SQL_CALC_FOUND_ROWS `ID`, `Code`, `Module`, CONCAT('<span style=\"color: #596C27;\"><b>',`Code`,'</b></span> | ', `Key`) AS `Key`, `Value` FROM `" . RL_DBPREFIX . "lang_keys` WHERE {$where} AND `Status` = 'active' AND `Module` <> 'email_tpl' AND `Key` NOT LIKE 'data_formats+name+%' AND `Value` LIKE '%{$phrase}%' ORDER BY `{$sort}` {$sortDir} LIMIT {$start}, {$limit}";
		}
		else
		{
			$sql = "SELECT SQL_CALC_FOUND_ROWS `ID`, `Code`, `Module`, CONCAT('<span style=\"color: #596C27;\"><b>',`Code`,'</b></span> | ', `Key`) AS `Key`, `Value` FROM `" . RL_DBPREFIX . "lang_keys` WHERE {$where} AND `Status` = 'active' AND `Module` <> 'email_tpl' AND `Key` NOT LIKE 'data_formats+name+%' AND `Key` LIKE '%{$phrase}%' ORDER BY `{$sort}` {$sortDir} LIMIT {$start}, {$limit}";
		}
		
		$rlHook -> load('apExtPhrasesSearch');
		
		$lang_data = $rlDb -> getAll( $sql );
		$count_rows = $rlDb -> getRow( "SELECT FOUND_ROWS() AS `calc`" );
		$lang_count['count'] = $count_rows['calc'];
	}
	else 
	{
		$rlHook -> load('apExtPhrasesSql');
		
		$rlDb -> setTable( 'lang_keys' );
		$lang_data = $rlDb -> fetch( array('ID', 'Module', 'Key', 'Value'), array( 'Code' => $langCode, 'Status' => 'active' ), "AND `Module` <> 'email_tpl' AND `Key` NOT LIKE 'data_formats+name+%' ORDER BY `{$sort}` {$sortDir}", array( $start, $limit ) );
		$rlDb -> resetTable();
		
		$lang_count = $rlDb -> getRow( "SELECT COUNT(`ID`) AS `count` FROM `" . RL_DBPREFIX . "lang_keys` WHERE `Code` = '{$langCode}' AND `Status` = 'active' AND `Module` <> 'email_tpl' AND `Key` NOT LIKE 'data_formats+name+%'" );
	}
	
	foreach ($lang_data as $index => $item)
	{
		$lang_data[$index]['Module'] = $lang['module_'. $item['Module']];
		
		$rlHook -> load('apExtPhrasesData');
	}
	
	$reefless -> loadClass( 'Json' );
	
	$output['total'] = $lang_count['count'];
	$output['data'] = $lang_data;

	echo $rlJson -> encode( $output );
}
/* ext js action end */
elseif ($_GET['q'] == 'compare')
{
	/* data read */
	$limit = (int)$_GET['limit'];
	$start = (int)$_GET['start'];
	
	/* system config */
	require_once( '../../includes/config.inc.php' );
	require_once( RL_ADMIN_CONTROL . 'ext_header.inc.php' );
	
	$lang_1 = $_SESSION['lang_1'];
	$lang_2 = $_SESSION['lang_2'];
	
	if ($_GET['action'] == 'update' )
	{
		$reefless -> loadClass( 'Actions' );
		
		$type = $rlValid -> xSql( $_GET['type'] );
		$field = $rlValid -> xSql( $_GET['field'] );
		$value = $rlValid -> xSql( nl2br($_GET['value']) );
		$id = $rlValid -> xSql( $_GET['id'] );
		$lang_code = $rlValid -> xSql( $_GET['lang_code'] );

		$updateData = array(
			'fields' => array(
				$field => $value
			),
			'where' => array(
				'ID' => $id
			)
		);
		
		$rlHook -> load('apExtPhrasesCompareUpdate');
		
		$rlActions -> updateOne( $updateData, 'lang_keys');

		if ( $_GET['compare_mode'] == "phrases" )
		{
			set_time_limit(0);
					
			$rlDb -> setTable('lang_keys');
			$phrases_1_tmp = $rlDb -> fetch( '*', array('Code' => $lang_1), "AND `Status` <> 'trash' ORDER BY `Key`" );
			foreach ($phrases_1_tmp as $pK => $pV)
			{
				$phrases_1[$phrases_1_tmp[$pK]['Key']] = $phrases_1_tmp[$pK];
			}
			unset($phrases_1_tmp);
			
			$phrases_2_tmp = $rlDb -> fetch( '*', array('Code' => $lang_2), "AND `Status` <> 'trash' ORDER BY `Key`" );
			foreach ($phrases_2_tmp as $pK => $pV)
			{
				$phrases_2[$phrases_2_tmp[$pK]['Key']] = $phrases_2_tmp[$pK];
			}
			unset($phrases_2_tmp);
			
			$compare_1 = array_diff_key($phrases_1, $phrases_2);
			foreach ($compare_1 as $cK => $cV)
			{
				$adapted_compare_1[] = $compare_1[$cK];
			}
			unset($compare_1);
			
			$_SESSION['compare_1'] = $_SESSION['source_1'] = $adapted_compare_1;
			
			$compare_2 = array_diff_key($phrases_2, $phrases_1);
			foreach ($compare_2 as $cK => $cV)
			{
				$adapted_compare_2[] = $compare_2[$cK];
			}
			unset($compare_2);
			
			$_SESSION['compare_2'] = $_SESSION['source_2'] = $adapted_compare_2;
		}
		else
		{
			$phrases_1_tmp = $rlDb -> fetch( '*', array('Code' => $lang_1), "AND `Status` <> 'trash' ORDER BY `Key`" );
			foreach ($phrases_1_tmp as $pK => $pV)
			{
				$phrases_1[$phrases_1_tmp[$pK]['Key']] = $phrases_1_tmp[$pK]['Value'];
				$phrases_1_orig[$phrases_1_tmp[$pK]['Key']] = $phrases_1_tmp[$pK];
			}
			unset($phrases_1_tmp);

			$phrases_2_tmp = $rlDb -> fetch( '*', array('Code' => $lang_2), "AND `Status` <> 'trash' ORDER BY `Key`" );
			
			foreach ($phrases_2_tmp as $pK => $pV)
			{
				$phrases_2[$phrases_2_tmp[$pK]['Key']] = $phrases_2_tmp[$pK]['Value'];
				$phrases_2_orig[$phrases_2_tmp[$pK]['Key']] = $phrases_2_tmp[$pK];
			}
			unset($phrases_2_tmp);

			$compare_1 = array_intersect_assoc($phrases_1, $phrases_2);
			foreach ($compare_1 as $cK => $cV)
			{
				$adapted_compare_1[] = $phrases_1_orig[$cK];
			}
			unset($compare_1);
			
			$_SESSION['compare_1'] = $adapted_compare_1;

			$compare_2 = array_intersect_assoc($phrases_2, $phrases_1);
			foreach ($compare_2 as $cK => $cV)
			{
				$adapted_compare_2[] = $phrases_2_orig[$cK];
			}
			unset($compare_2);

			$_SESSION['compare_2'] = $adapted_compare_2;
		}
	}
	
	$rlHook -> load('apExtPhrasesCompareSql');
	
	$grid = (int)$_GET['grid'];
	$data = $_SESSION['compare_'.$grid];

	foreach ($data as $index => $item)
	{
		$data[$index]['Module'] = $lang['module_'. $item['Module']];
	}
	
	$reefless -> loadClass( 'Json' );

	$output['total'] = (string)count($data);
	$output['data'] = array_slice($data, $start, $limit);

	echo $rlJson -> encode( $output );
}
elseif ( $_GET['action'] == 'export' )
{
	$reefless -> loadClass( 'AjaxLang', 'admin' );
	$rlAjaxLang -> exportLanguage((int)$_GET['lang']);
}
else
{
	if (!function_exists('array_diff_key'))
	{
	    function array_diff_key()
	    {
	        $arrs = func_get_args();
	        $result = array_shift($arrs);
	        foreach ($arrs as $array) {
	            foreach ($result as $key => $v) {
	                if (array_key_exists($key, $array)) {
	                    unset($result[$key]);
	                }
	            }
	        }
	        return $result;
	   }
	}
	
	/* clear cache */
	if ( !$_REQUEST['compare'] && !$_POST['xjxfun'] )
	{
		unset($_SESSION['compare_mode']);
		
		unset($_SESSION['compare_1']);
		unset($_SESSION['compare_2']);
		
		unset($_SESSION['source_1']);
		unset($_SESSION['source_2']);

		unset($_SESSION['lang_1']);
		unset($_SESSION['lang_2']);
	}
	
	/* get all system languages */
	$allLangs = $rlLang -> getLanguagesList( 'all' );
	$rlSmarty -> assign_by_ref( 'allLangs', $allLangs );
	$rlSmarty -> assign( 'langCount', count($allLangs) );
	
	/* get lang for edit */
	if ($_GET['action'] == 'edit')
	{
		$bcAStep[] = array(
			'name' => $lang['edit']
		);
		
		$edit_id = (int)$_GET['lang'];
		
		// get current language info
		$language = $rlDb -> fetch( '*', array( 'ID' => $edit_id ), null, 1, 'languages', 'row' );
				
		if ($_GET['action'] == 'edit' && !$_POST['fromPost'])
		{
			$_POST['code'] = $language['Code'];
			$_POST['direction'] = $language['Direction'];
			$_POST['date_format'] = $language['Date_format'];
			$_POST['status'] = $language['Status'];

			// get names
			$l_name = $rlDb -> fetch( array( 'Code', 'Value' ), array( 'Key' => 'languages+name+'.$language['Key'] ), "AND `Status` <> 'trash'", 1, 'lang_keys', 'row' );
			$_POST['name'] = $l_name['Value'];
		}
	}
	
	if ($_POST['submit'])
	{
		/* check data */
		
		if ( empty( $_POST['name'] ) )
		{
			$errors[] = str_replace( '{field}', "<b>\"{$lang['name']}\"</b>", $lang['notice_field_empty']);
		}
		
		if ( empty( $_POST['date_format'] ) )
		{
			$errors[] = str_replace( '{field}', "<b>\"{$lang['date_format']}\"</b>", $lang['notice_field_empty']);
		}
		
		if( !empty($errors) )
		{
			$rlSmarty -> assign_by_ref( 'errors', $errors );
		}
		else
		{
			$result = false;
			
			/* update general information */
			$updateLang['fields'] = array(
				'Date_format' => $_POST['date_format'],
				'Status' => $_POST['status'],
				'Direction' => $_POST['direction']
			);
			
			$updateLang['where'] = array(
				'Code' => $_POST['code'],
			);
			
			$result = $rlActions -> updateOne ( $updateLang, 'languages' );

			if ( $rlDb -> getOne('ID', "`Key` = 'languages+name+{$language['Key']}'", 'lang_keys') )
			{
				/* update phrase */
				$updatePhrase = array(
					'fields' => array(
						'Value' => $_POST['name']
					),
					'where' => array(
						'Key' => 'languages+name+' . $language['Key']
					)
				);
				
				$result = $rlActions -> updateOne ( $updatePhrase, 'lang_keys' );
			}
			else
			{
				/* insert phrase */
				$insertPhrase = array(
					'Key' => 'languages+name+' . $language['Key'],
					'Value' => $_POST['name'],
					'Module' => 'common',
					'Code' => $language['Code']
				);
				
				$result = $rlActions -> insertOne ( $insertPhrase, 'lang_keys' );
			}

			if ( $result )
			{
				$message = $lang['language_edited'];
				$aUrl = array( "controller" => $controller );
				
				$reefless -> loadClass( 'Notice' );
				$rlNotice -> saveNotice( $message );
				$reefless -> redirect( $aUrl );
			}
			else 
			{
				trigger_error( "Can't edit language (MYSQL problems)", E_WARNING );
				$rlDebug -> logger("Can't edit language (MYSQL problems)");
			}
		}
	}
	
	if ($_POST['import'])
	{
		$dump_sours = $_FILES['dump']['tmp_name'];
		$dump_file = $_FILES['dump']['name'];
		
		preg_match( "/\(([A-Z]{2})\)(\.sql)/", $dump_file, $matches );

		if (!empty($matches[1]) && strtolower($matches[2]) == '.sql')
		{
			if ( is_readable($dump_sours) )
			{
				$dump_content = fopen($dump_sours, "r");
				$rlDb -> query("SET NAMES `utf8`");
				
				if ($dump_content)
				{
					/* check exist language */
					if  ( $exist_lang_key = $rlDb -> getOne('Key', "LOWER(`Code`) = '". strtolower($matches[1]) ."'", 'languages') )
					{
						$exist_lang_name = $rlDb -> getOne('Value', "`Key` = 'languages+name+". $exist_lang_key ."'", 'lang_keys');
						$errors[] = str_replace(array('{language}', '{code}'), array($exist_lang_name, $matches[1]), $lang['import_language_already_exist']);
					}
					else
					{
						while ( $query = fgets ( $dump_content, 10240) )
						{
							$query = trim($query);
							if ( $query[0] == '#' ) continue;
							if ( $query[0] == '-' ) continue;
	
							if ( $query[strlen($query)-1] == ';' )
							{
								$query_sql .= $query;
							}
							else
							{
								$query_sql .= $query;
								continue;
							}
					
							if (!empty($query_sql) && empty($errors))
							{						
								$query_sql = str_replace('{prefix}', RL_DBPREFIX, $query_sql);
							}
	
							$res = $rlDb -> query( $query_sql );
							if (!$res && count($errors) < 5)
							{
								$errors[] = $lang['can_not_run_sql_query'] . mysql_error();
							}
							unset($query_sql);
						}
			
						fclose($sql_dump);
						
						if (empty($errors))
						{
							$rlNotice -> saveNotice( $lang['new_language_imported'] );
							$aUrl = array( "controller" => $controller );
							
							$reefless -> redirect( $aUrl );
						}
						else
						{
							$errors[] = $lang['dump_query_corrupt'];
						}
					}
				}
				else
				{
					$errors[] = $lang['dump_has_not_content'];
				}
			}
			else
			{
				$errors[] = $lang['can_not_read_file'];
				trigger_error("Can not to read uploaded file | Language Import", E_WARNING);
				$rlDebug -> logger("Can not to read uploaded file | Language Import");
			}
		}
		else
		{
			$errors[] = $lang['incorrect_lang_dump'];
		}
		
		if (!empty($errors))
		{
			$rlSmarty -> assign_by_ref( 'errors', $errors );
		}
	}
	elseif ( isset($_POST['compare']) )
	{
		/* additional bread crumb step */
		$bcAStep = $lang['languages_compare'];
		
		$lang_1 = $_POST['lang_1'];
		$lang_2 = $_POST['lang_2'];

		foreach ($allLangs as $lK => $lV)
		{
			$langs_info[$allLangs[$lK]['Code']] = $allLangs[$lK];
		}
		
		/* checking errors */
		if ( empty($lang_1) || empty($lang_2) )
		{
			$errors[] = $lang['compare_empty_langs'];
		}
		
		if ( $lang_1 == $lang_2 && !$errors )
		{
			$errors[] = $lang['compare_languages_same'];
		}
		
		if ( (!array_key_exists( $lang_1, $langs_info ) || !array_key_exists( $lang_2, $langs_info )) && !$errors )
		{
			$errors[] = $lang['system_error'];
			//trigger_error("Can not compare the languages, gets undefine language code", E_USER_NOTICE);
			$rlDebug -> logger("Can not compare the languages, gets undefine language code");
		}
		
		if ( !empty($errors) )
		{
			$rlSmarty -> assign_by_ref('errors', $errors);
		}
		else
		{
			set_time_limit(0);
			
			$rlDb -> setTable('lang_keys');
			
			$_SESSION['compare_mode'] = $_POST['compare_mode'];
			if ( $_POST['compare_mode'] == "phrases" )
			{
				$phrases_1_tmp = $rlDb -> fetch( '*', array('Code' => $lang_1), "AND `Status` <> 'trash' ORDER BY `Key`" );
				foreach ($phrases_1_tmp as $pK => $pV)
				{
					$phrases_1[$phrases_1_tmp[$pK]['Key']] = $phrases_1_tmp[$pK];
				}
				unset($phrases_1_tmp);
				
				$phrases_2_tmp = $rlDb -> fetch( '*', array('Code' => $lang_2), "AND `Status` <> 'trash' ORDER BY `Key`" );
				foreach ($phrases_2_tmp as $pK => $pV)
				{
					$phrases_2[$phrases_2_tmp[$pK]['Key']] = $phrases_2_tmp[$pK];
				}
				unset($phrases_2_tmp);
				
				$compare_1 = array_diff_key($phrases_1, $phrases_2);
				foreach ($compare_1 as $cK => $cV)
				{
					$adapted_compare_1[] = $compare_1[$cK];
				}
				unset($compare_1);

				$compare_2 = array_diff_key($phrases_2, $phrases_1);
				foreach ($compare_2 as $cK => $cV)
				{
					$adapted_compare_2[] = $compare_2[$cK];
				}
				unset($compare_2);
				
				if ( empty($adapted_compare_1) && empty($adapted_compare_2) )
				{
					$reefless -> loadClass( 'Notice' );
					$rlNotice -> saveNotice( $lang['compare_no_diff_found'] );
	
					$aUrl = array( "controller" => $controller );
					$reefless -> redirect( $aUrl );
				}
				else
				{
					$_SESSION['compare_1'] = $_SESSION['source_1'] = $adapted_compare_1;
					$_SESSION['lang_1'] = $lang_1;
					
					$_SESSION['compare_2'] = $_SESSION['source_2'] = $adapted_compare_2;
					$_SESSION['lang_2'] = $lang_2;
		
					$compare_lang1 = array( 'diff' => count($adapted_compare_1), 'Code' => $lang_1 );
					$compare_lang2 = array( 'diff' => count($adapted_compare_2), 'Code' => $lang_2 );
					
					$rlSmarty -> assign_by_ref('compare_lang1', $compare_lang1);
					$rlSmarty -> assign_by_ref('compare_lang2', $compare_lang2);
					$rlSmarty -> assign_by_ref('langs_info', $langs_info);
				}
			}
			else
			{
				$phrases_1_tmp = $rlDb -> fetch( '*', array('Code' => $lang_1), "AND `Status` <> 'trash' ORDER BY `Key`" );
				foreach ($phrases_1_tmp as $pK => $pV)
				{
					$phrases_1[$phrases_1_tmp[$pK]['Key']] = $phrases_1_tmp[$pK]['Value'];
					$phrases_1_orig[$phrases_1_tmp[$pK]['Key']] = $phrases_1_tmp[$pK];
				}
				unset($phrases_1_tmp);
	
				$phrases_2_tmp = $rlDb -> fetch( '*', array('Code' => $lang_2), "AND `Status` <> 'trash' ORDER BY `Key`" );
				
				foreach ($phrases_2_tmp as $pK => $pV)
				{
					$phrases_2[$phrases_2_tmp[$pK]['Key']] = $phrases_2_tmp[$pK]['Value'];
					$phrases_2_orig[$phrases_2_tmp[$pK]['Key']] = $phrases_2_tmp[$pK];
				}
				unset($phrases_2_tmp);
	
				$compare_1 = array_intersect_assoc($phrases_1, $phrases_2);
				foreach ($compare_1 as $cK => $cV)
				{
					$adapted_compare_1[] = $phrases_1_orig[$cK];
				}
				unset($compare_1);

				$compare_2 = array_intersect_assoc($phrases_2, $phrases_1);
				foreach ($compare_2 as $cK => $cV)
				{
					$adapted_compare_2[] = $phrases_2_orig[$cK];
				}
				unset($compare_2);
				
				if ( empty($adapted_compare_1) && empty($adapted_compare_2) )
				{
					$reefless -> loadClass( 'Notice' );
					$rlNotice -> saveNotice( $lang['compare_no_diff_found'] );
	
					$aUrl = array( "controller" => $controller );
					$reefless -> redirect( $aUrl );
				}
				else
				{
					$_SESSION['compare_1'] = $adapted_compare_1;
					$_SESSION['lang_1'] = $lang_1;

					$_SESSION['compare_2'] = $adapted_compare_2;
					$_SESSION['lang_2'] = $lang_2;
		
					$compare_lang1 = array( 'diff' => count($adapted_compare_1), 'Code' => $lang_1 );
					$compare_lang2 = array( 'diff' => count($adapted_compare_2), 'Code' => $lang_2 );
					
					$rlSmarty -> assign_by_ref('compare_lang1', $compare_lang1);
					$rlSmarty -> assign_by_ref('compare_lang2', $compare_lang2);
					$rlSmarty -> assign_by_ref('langs_info', $langs_info);
				}
			}
		}
	}
	
	$rlHook -> load('apPhpLanguagesBottom');

	/* load admin class */
	$reefless -> loadClass( 'AjaxLang', 'admin' );
	
	/* register ajax methods */
	$rlXajax -> registerFunction( array( 'setDefault', $rlAjaxLang, 'ajaxSetDefault' ) );
	$rlXajax -> registerFunction( array( 'deleteLang', $rlAjaxLang, 'ajaxDeleteLang' ) );
	$rlXajax -> registerFunction( array( 'addLanguage', $rlAjaxLang, 'ajax_addLanguage' ) );
	$rlXajax -> registerFunction( array( 'addPhrase', $rlAjaxLang, 'ajax_addPhrase' ) );
	$rlXajax -> registerFunction( array( 'copyPhrases', $rlAjaxLang, 'ajaxCopyPhrases' ) );
	$rlXajax -> registerFunction( array( 'massDelete', $rlAjaxLang, 'ajaxMassDelete' ) );
	$rlXajax -> registerFunction( array( 'exportLanguage', $rlAjaxLang, 'ajaxExportLanguage' ) );
}