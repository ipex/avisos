<?php

/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: RLAJAXLANG.CLASS.PHP
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

class rlAjaxLang extends reefless
{
	/**
	* @var language class object
	**/
	var $rlLang;
	
	/**
	* @var validator class object
	**/
	var $rlValid;
	
	/**
	* @var configurations class object
	**/
	var $rlConfig;
	
	/**
	* @var administrator controller class object
	**/
	var $rlAdmin;
	
	/**
	* @var actions class object
	**/
	var $rlActions;

	/**
	* @var notice class object
	**/
	var $rlNotice;
	
	/**
	* class constructor
	**/
	function rlAjaxLang()
	{
		global $rlLang, $rlValid, $rlConfig, $rlAdmin, $rlActions, $rlNotice;
		
		$this -> rlLang   =  & $rlLang;
		$this -> rlValid  =  & $rlValid;
		$this -> rlConfig =  & $rlConfig;
		$this -> rlAdmin =   & $rlAdmin;
		$this -> rlActions = & $rlActions;
		$this -> rlNotice =  & $rlNotice;
	}
	
	/**
	* set language as default
	*
	* @package ajax
	*
	* @param string $object - DOM object id
	* @param string $code - language code
	*
	**/
	function ajaxSetDefault( $object, $code )
	{
		global $_response, $lang;

		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}
		
		if ($this -> rlConfig -> setConfig( 'lang', $code ))
		{
			$_response -> script( "languagesGrid.reload();" );
			$_response -> script( "printMessage('notice', '{$lang['changes_saved']}')" );
		}
		else 
		{
			trigger_error( "Can not set default language, MySQL problems", E_WARNING );
			$GLOBALS['rlDebug'] -> logger("Can not set default language, MySQL problems");
		}

		return $_response;
	}
	
	/**
	* add new language (copy from exist)
	*
	* @package ajax
	*
	* @param array $data - new language data
	*
	**/
	function ajax_addLanguage( $data )
	{
		global $_response;

		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}
		
		loadUTF8functions('ascii', 'utf8_to_ascii', 'unicode');
		$lang_name = $lang_key = $this -> rlValid -> xSql( str_replace( array( '"', "'" ), array( '', '' ), $data[0][1] ) );
		
		if ( empty($lang_name) )
		{
			$error[] = $GLOBALS['lang']['name_field_empty'];
		}
		
		if ( !utf8_is_ascii( $lang_name ) )
		{
			$lang_key = utf8_to_ascii( $lang_name );
		}
		
		$lang_key = strtolower( str_replace( array( '"', "'" ), array( '', '' ), $lang_key ) );
		
		$iso_code = $this -> rlValid -> xSql( $data[1][1] );
		
		if ( !utf8_is_ascii( $iso_code ) )
		{
			$error = $GLOBALS['lang']['iso_code_incorrect_charset'];
		}
		else 
		{
			if ( strlen( $iso_code )!= 2 )
			{
				$error[] = $GLOBALS['lang']['iso_code_incorrect_number'];
			}
			
			//check language exist
			$lang_exist = $this -> fetch( '*', array( 'Code' => $iso_code ), null, null, 'languages' );

			if ( !empty( $lang_exist ) )
			{
				$error[] = $GLOBALS['lang']['iso_code_incorrect_exist'];
			}
		}

		/* check direction */
		$direction = $data[4][1];
		
		if ( !in_array($direction, array('rtl', 'ltr')) )
		{
			$error[] = $GLOBALS['lang']['text_direction_fail'];
		}
		
		/* check date format */
		$date_format = $this -> rlValid -> xSql( $data[2][1] );
		
		if ( empty($date_format) || strlen($date_format) < 5 )
		{
			$error[] = $GLOBALS['lang']['language_incorrect_date_format'];
		}
		
		if ( !empty($error) )
		{
			/* print errors */
			$error_content = '<ul>';
			foreach ($error as $err)
			{
				$error_content .= "<li>{$err}</li>";
			}
			$error_content .= '</ul>';
			$_response -> script( 'printMessage("error", "'. $error_content .'")' );
		}
		else 
		{
			/* get & optimize new language phrases*/
			$source_code = $this -> rlValid -> xSql( $data[3][1] );
			$this -> setTable( 'lang_keys' );
			
			$source_phrases = $this -> fetch( '*', array( 'Code' => $source_code ) );
	
			if ( !empty($source_phrases) )
			{
				$step = 1;
				
				foreach ( $source_phrases as $item => $row )
				{
					$insert_phrases[$item] = $source_phrases[$item];
					$insert_phrases[$item]['Code'] = $iso_code;
				
					unset( $insert_phrases[$item]['ID'] );
						
					if ($step % 500 == 0)
					{
						$this -> rlActions -> insert( $insert_phrases, 'lang_keys' );
						unset($insert_phrases);
						$step = 1;
					}
					else
					{
						$step++;
					}
				}
				
				if ( !empty($insert_phrases) )
				{
					$this -> rlActions -> insert( $insert_phrases, 'lang_keys' );
				}
	
				$additional_row = array(
					'Code' => $iso_code,
					'Module' => 'common',
					'Key'  => 'languages+name+' . $lang_key,
					'Value' => $lang_name,
					'Status' => 'active'
				);
				
				$this -> rlActions -> insertOne( $additional_row, 'lang_keys' );
			}
			else 
			{
				$error[] = $GLOBALS['lang']['language_no_phrases'];
			}
			
			if ( !empty($error) )
			{
				/* print errors */
				$_response -> script("printMessage('error', '{$error}')");
			}
			else
			{
				$insert = array(
					'Code' => $iso_code,
					'Direction' => $direction,
					'Key' => $lang_key,
					'Status' => 'active',
					'Date_format' => $date_format
				);
				$this -> rlActions -> insertOne( $insert, 'languages' );
								
				/* print notice */
				$_response -> script("
					printMessage('notice', '{$GLOBALS['lang']['language_added']}');
					show('lang_add_container');
					languagesGrid.reload();
				");
			}
		}
		
		$_response -> script( "$('#lang_add_load').fadeOut('slow');" );

		return $_response;
	}
	
	/**
	* add new language phrase
	*
	* @package ajax
	*
	* @param array $data - new phrase data
	*
	**/
	function ajax_addPhrase( $data, $values )
	{
		global $_response, $lang;

		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}
		
		loadUTF8functions('ascii', 'utf8_to_ascii', 'unicode');
		
		$key = str_replace(array('"', "'"), array("", ""), $data[0][1]);
		$key = $this -> rlValid -> xSql(trim($key));

		if ( strlen($key) < 2 )
		{
			$error[] = $lang['incorrect_phrase_key'];
		}
		
		if ( !utf8_is_ascii( $key ) )
		{
			$error[] = $lang['key_incorrect_charset'];
		}
		
		$key = $this -> rlValid -> str2key($key);
		
		//check key exists
		$key_exist = $this -> fetch( 'ID', array( 'Key' => $key ), null, null, 'lang_keys', 'row' );
		
		if (!empty($key_exist))
		{
			$error[] = str_replace( '{key}', "'<b>{$key}</b>'", $lang['notice_key_exist'] );
		}
		
		$side = $this -> rlValid -> xSql( $data[1][1] );

		if ( !empty($error) )
		{
			/* print errors */
			$error_content = '<ul>';
			foreach ($error as $err)
			{
				$error_content .= "<li>{$err}</li>";
			}
			$error_content .= '</ul>';
			$_response -> script( 'printMessage("error", "'. $error_content .'")' );
		}
		else 
		{
			foreach ( $values as $index => $field )
			{
				$phrase[] = array( 'Code' => $values[$index][0], 'Value' => $values[$index][1], 'Module' => $side, 'Key' => $key, 'Status' => 'active' );
			}

			if ($this -> rlActions -> insert( $phrase, 'lang_keys' ))
			{
				/* hide add phrase block */
				$_response -> script("
					show('lang_add_phrase');
					$('#lang_add_phrase textarea').val('');
					$('#lang_add_phrase input').val('');
				");
	
				/* print notice */
				$_response -> script( "printMessage('notice', '{$lang['lang_phrase_added']}')" );
			}
		}

		$_response -> script( "$('#add_phrase_submit').val('{$lang['add']}');" );

		return $_response;
	}
	
	/**
	* delete language
	*
	* @package ajax
	*
	* @param int $id - language ID
	*
	**/
	function ajaxDeleteLang( $id )
	{
		global $_response, $config, $lang;

		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}

		$id = (int)$id;
		$code = $this -> getOne('Code', "`ID` = '{$id}'", 'languages');
		
		if ( !$code || !$id )
			return $_response;


		/*handle multilingual fields - remove all tags if only one lang left*/
		if( count($GLOBALS['languages']) == 2 )
		{
			$multilang_fields_listings = $this -> fetch(array('Key'), array('Multilingual' => '1'), null, null, 'listing_fields');
			foreach( $multilang_fields_listings as $ml_key => $ml_field )
			{
				$custom_sql = "UPDATE `". RL_DBPREFIX ."listings` SET `{$ml_field['Key']}` = IF (LOCATE('{|/', `{$ml_field['Key']}`) > 0, IF (LOCATE('{|{$config['lang']}|}', `{$ml_field['Key']}`) > 0, SUBSTRING(`{$ml_field['Key']}` FROM LOCATE('{|{$config['lang']}|}', `{$ml_field['Key']}`)+6 FOR LOCATE('{|/{$config['lang']}|}', `{$ml_field['Key']}`) - LOCATE('{|{$config['lang']}|}', `{$ml_field['Key']}`)-6), SUBSTRING(`{$ml_field['Key']}` FROM 7 FOR LOCATE('{|/', `{$ml_field['Key']}`)-7)), `{$ml_field['Key']}`) WHERE `{$ml_field['Key']}` IS NOT NULL";
				$this -> query($custom_sql);
			}
			$multilang_fields_accounts = $this -> fetch(array('Key'), array('Multilingual' => '1'), null, null, 'account_fields');
			foreach( $multilang_fields_accounts as $ml_key => $ml_field )
			{
				$custom_sql = "UPDATE `". RL_DBPREFIX ."accounts` SET `{$ml_field['Key']}` = IF (LOCATE('{|/', `{$ml_field['Key']}`) > 0, IF (LOCATE('{|{$config['lang']}|}', `{$ml_field['Key']}`) > 0, SUBSTRING(`{$ml_field['Key']}` FROM LOCATE('{|{$config['lang']}|}', `{$ml_field['Key']}`)+6 FOR LOCATE('{|/{$config['lang']}|}', `{$ml_field['Key']}`) - LOCATE('{|{$config['lang']}|}', `{$ml_field['Key']}`)-6), SUBSTRING(`{$ml_field['Key']}` FROM 7 FOR LOCATE('{|/', `{$ml_field['Key']}`)-7)), `{$ml_field['Key']}`) WHERE `{$ml_field['Key']}` IS NOT NULL";
				$this -> query($custom_sql);
			}
		}

		if ( $config['lang'] != $code )
		{
			$fields = array( 'ID' => $this -> rlValid -> xSql($code) );

			$this -> query("DELETE FROM `".RL_DBPREFIX."lang_keys` WHERE `Code` = '{$code}'");
			$this -> query("DELETE FROM `".RL_DBPREFIX."languages` WHERE `Code` = '{$code}'");
			
			$_response -> script("
				printMessage('notice', '{$lang['language_deleted']}');
				languagesGrid.reload();
			");
		}
		else
		{
			trigger_error( "The default language desabled for deleting or droping to trash.", E_USER_WARNING );
			$GLOBALS['rlDebug'] -> logger("The default language desabled for deleting or droping to trash.");
		}
		
		return $_response;
	}
	
	/**
	* copy languages's phrases
	*
	* @package ajax
	*
	* @param int $from - language code 1
	* @param int $to - language code 2
	*
	**/
	function ajaxCopyPhrases( $from = false, $to = false, $name = false )
	{
		global $_response, $lang;

		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}

		$phrases = $_SESSION['source_'.$from];
		$compare_to = $_SESSION['compare_'.$to];
		$lang_code = $_SESSION['lang_'.$to];

		if ( empty($phrases) || empty($lang_code) )
		{
			return $_response;
		}
		
		foreach ($phrases as $key => $value)
		{
			$insert = array();
			
			$insert = array(
				'Code' => $lang_code,
				'Module' => $phrases[$key]['Module'],
				'Key' => $phrases[$key]['Key'],
				'Value' => $phrases[$key]['Value'],
				'Plugin' => $phrases[$key]['Plugin'],
				'Status' => $phrases[$key]['Status']
			);
			
			$GLOBALS['rlActions'] -> insertOne($insert, 'lang_keys');
			
			$compare_to[] = array(
				'ID' => mysql_insert_id(),
				'Code' => $lang_code,
				'Module' => $phrases[$key]['Module'],
				'Key' => $phrases[$key]['Key'],
				'Value' => $phrases[$key]['Value'],
				'Plugin' => $phrases[$key]['Plugin'],
				'Status' => $phrases[$key]['Status']
			);
		}

		if ( !empty($insert) )
		{			
			$_SESSION['compare_'.$to] = $compare_to;
			
			/* print notice */			
			$_response -> script( "printMessage('notice', '{$lang['compare_phrases_copied']}')" );
			
			$_response -> script("$('#copy_button_{$from}').slideUp('slow');");
			$_response -> script( "$('#loading_{$from}').fadeOut('fast');" );
			
			$_response -> script( "compareGrid{$to}.reload();" );
		}
		
		return $_response;
	}
	
	/**
	* mass delete phrases
	*
	* @package xAjax
	*
	* @param string $ids - phrases ids
	* @param string $code - language code
	* @param int $gridNumber - grid number | Compare mode
	*
	**/
	function ajaxMassDelete( $ids, $code = false, $gridNumber = false )
	{
		global $_response, $lang;
		
		//$where = !empty($code) ? "AND `Code` = '{$code}'" : "";
		
		$tmp_phrases = $_SESSION['source_'.$gridNumber];
		
		foreach ($tmp_phrases as $key => $val)
		{
			$phrases[$tmp_phrases[$key]['ID']] = $tmp_phrases[$key];
		}
		unset($tmp_phrases);

		$ids = explode('|', $ids);
		
		foreach ($ids as $id)
		{
			$this -> query( "DELETE FROM `".RL_DBPREFIX."lang_keys` WHERE `ID` = '{$id}' {$where} LIMIT 1" );
			unset($phrases[$id]);
		}
		var_dump($gridNumber);
		$_SESSION['source_'.$gridNumber] = $_SESSION['compare_'.$gridNumber] = $phrases;
		
		if ( empty($phrases) )
		{
			$_response -> script("$('#compare_area_{$gridNumber}').slideUp('slow')");
		}
		
		$_response -> script("compareGrid{$gridNumber}.reload();");
		$_response -> script( "printMessage('notice', '{$lang['notice_items_deleted']}')" );
		
		unset($phrases);
		
		return $_response;
	}
	
	/**
	* export language
	*
	* @package xAjax
	*
	* @param int $id - export language ID
	*
	**/
	function exportLanguage( $id = false )
	{
		global $lang, $config, $rlSmarty;
		
		if ( !$id )
		{
			return false;
		}

		$info = $this -> fetch(array('Code', 'Key', 'Direction', 'Date_format'), array('ID' => $id), null, 1, 'languages', 'row');
		$name = $this -> getOne('Value', "`Key` = 'languages+name+{$info['Key']}'", 'lang_keys');
		$phrases = $this -> fetch(array('Value', 'Module', 'Key', 'Plugin', 'Status'), array('Code' => $info['Code']), null, null, 'lang_keys');
		
		if ( $phrases )
		{
			$insert = "INSERT INTO `{prefix}lang_keys` (`Code`, `Module`, `Key`, `Value`, `Plugin`, `Status`) VALUES ". PHP_EOL;
			$lang_name = strtoupper($name). " (". strtoupper($info['Code']) .")";
			
			$content = "-- Flynax Classifieds Software". PHP_EOL
					  ."-- Direction: UNDEFINED". PHP_EOL
					  ."-- Export date: ". date('Y.m.d') . PHP_EOL
					  ."-- version: {$config['rl_version']}". PHP_EOL
					  ."-- Language SQL Dump: {$lang_name}". PHP_EOL
					  ."-- http://www.flynax.com/license-agreement.html". PHP_EOL . PHP_EOL
					  ."INSERT INTO `{prefix}languages` (`Code`, `Key`, `Status`, `Date_format`, `Direction`) VALUES ('{$info['Code']}', '{$info['Key']}', 'active', '{$info['Date_format']}', '{$info['Direction']}');". PHP_EOL . PHP_EOL;
					  
			$content .= $insert;
			foreach ( $phrases as $key => $value )
			{
				$value['Value'] = str_replace("'", "''", $value['Value']);
				$tmp = <<<VS
('{$info['Code']}', '{$value['Module']}', '{$value['Key']}', '{$value['Value']}', '{$value['Plugin']}', '{$value['Status']}')
VS;

				if ( count($phrases)-1 == $key )
				{
					$content .= $tmp .';';
				}
				else
				{
					if ( $key%500 == 0 && $key != 0 )
					{
						$content .= $tmp .';'. PHP_EOL . $insert;
					}
					else
					{
						$content .= $tmp .','. PHP_EOL;
					}
				}
			}
			
			header('Content-Type: application/download');
    		header('Content-Disposition: attachment; filename='.ucfirst($info['Key']).'('.strtoupper($info['Code']).').sql');
			echo $content;
			exit;
		}
		else
		{
			$alerts[] = $lang['lang_export_empty_alert'];
			$rlSmarty -> assign_by_ref('alerts', $alerts);
			
			return false;
		}
	}
}
