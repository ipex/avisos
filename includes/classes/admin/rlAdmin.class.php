<?php

/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: RLADMIN.CLASS.PHP
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

class rlAdmin extends reefless 
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
	* @var mMenu_controllers
	**/
	var $mMenu_controllers;

	/**
	* class constructor
	**/
	function rlAdmin()
	{
		global $rlLang, $rlValid;

		$this -> rlLang  = & $rlLang;
		$this -> rlValid = & $rlValid;
	}

	/**
	* check admin panel logining
	*
	* @param mixed $user - admin username
	* @param MD5 $pass - admin user password
	* @param varchar $lang - language inerface
	*
	* @todo put data to session
	* 
	**/
	function LogIn( $userInfo )
	{
		$_SESSION['sessAdmin'] = array(
			'user_id' => $userInfo['ID'],
			'user' => $userInfo['User'],
			'pass' => md5($userInfo['Pass']),
			'mail' => $userInfo['Email'],
			'name' => $userInfo['Name'],
			'rights' => unserialize($userInfo['Rights']),
			'type' => $userInfo['Type']
		);

		/*$_SESSION['user_id'] = $userInfo['ID'];
		$_SESSION['user'] = $userInfo['User'];
		$_SESSION['pass'] = md5($userInfo['Pass']);
		$_SESSION['mail'] = $userInfo['Email'];
		$_SESSION['name'] = $userInfo['Name'];
		$_SESSION['rights'] = unserialize($userInfo['Rights']);
		$_SESSION['type'] = $userInfo['Type'];*/
	}

	/**
	* administrator log out
	*
	* @todo destroy the current session and administrator information
	* 
	**/
	function LogOut()
	{
		unset($_SESSION['sessAdmin']);
		unset($_SESSION['query_string']);
	}

	/**
	* check admin log in
	*
	* @todo check admin login or not
	* 
	* @return bool
	**/
	function isLogin()
	{
		$username = $this -> rlValid -> xSql( $_SESSION['sessAdmin']['user'] );
		$password = $this -> rlValid -> xSql( $_SESSION['sessAdmin']['pass'] );

		$pass = $this -> getOne( 'Pass', "`User` = '{$username}'", 'admins' );

		if ( !empty( $pass ) )
		{
			if ( md5($pass) == $password )
			{
				return true;
			}
		}

		return false;
	}

	/**
	* get main admin menu
	*
	* @return mixed array data
	* 
	**/
	function getMainMenuItems()
	{
		$rights = $_SESSION['rlAdmin']['rights'];

		$this -> setTable( 'admin_controllers' );

		$mMenuItems = $this -> fetch( array('ID', 'Key', 'Controller', 'Vars'), array( 'Parent_ID' => '0' ), 'ORDER BY `Position`' );
		$mMenuItems = $this -> rlLang -> replaceLangKeys( $mMenuItems, 'admin_controllers', array( 'name' ), RL_LANG_CODE, 'admin' );
		foreach ($mMenuItems as $key => $value)
		{
			$mMenuChild = $this -> fetch( array('Key', 'Controller', 'Vars'), array( 'Parent_ID' => $mMenuItems[$key]['ID'] ), 'ORDER BY `Position`' );
			$mMenuChild = $this -> rlLang -> replaceLangKeys( $mMenuChild, 'admin_controllers', array( 'name' ), RL_LANG_CODE, 'admin' );

			foreach ($mMenuChild as $mKey => $mVal)
			{
				$mMenuItems[$key]['Controllers_list'][] = $mVal['Controller'];

				if ( array_key_exists($mVal['Key'], $rights) )
				{
					$this -> mMenu_controllers[$mVal['Key']] = $mVal['Controller'];
				}
			}

			if (!empty($mMenuChild))
			{
				$mMenuItems[$key]['child'] = $mMenuChild;
			}

			if ( $mMenuItems[$key]['Key'] == 'plugins' )
			{
				$plugins = $this -> fetch( array('Name` AS `name', 'Key` AS `Plugin', 'Key', 'Controller'), array('Install' => 1, 'Status' => 'active'), "ORDER BY `ID`", null, 'plugins' );
				$mMenuItems[$key]['child'][0] = $mMenuChild[0];
				foreach ($plugins as $pKey => $pVal)
				{
					$mMenuItems[$key]['Controllers_list'][] = $pVal['Controller'];

					if ( !empty($plugins[$pKey]['Controller']) )
					{
						$mMenuItems[$key]['child'][$pKey+1] = $plugins[$pKey];
					}
				}
			}
		}

		$this -> resetTable();

		return $mMenuItems;
	}

	/**
	* get admin bread crumbs (recursive method)
	*
	* @param string $id    - current part id (controller)
	* @param string $aStep - additional step
	* @param array $path   - path
	*
	* @return mixed array - broad crumbs path
	* 
	**/
	function getBreadCrumbs( $id, $aStep = array(), $path = array(), $plugin = false )
	{
		$this -> rlValid -> sql( $id );

		if ( $plugin )
		{
			$iteration = $this -> fetch( array('Name` AS `name', 'Key` AS `Plugin', 'Controller'), array( 'Key' => $plugin ), null, null, 'plugins', 'row' );
		}
		else
		{
			$iteration = $this -> fetch( '*', array( 'ID' => $id ), null, null, 'admin_controllers', 'row' );
			$iteration = $this -> rlLang -> replaceLangKeys( $iteration, 'admin_controllers', array( 'name' ), RL_LANG_CODE, 'admin' );
		}

		array_push( $path, $iteration );

		if ($iteration['Parent_ID'] > '0')
		{
			return $this -> getBreadCrumbs( $iteration['Parent_ID'], $aStep, $path );
		}
		else 
		{
			$path = array_reverse($path);
			if ($aStep != null)
			{
				if ( is_array($aStep) )
				{
					foreach ($aStep as $key => $value)
					{
						array_push($path, $aStep[$key]);
					}
				}
				else 
				{
					array_push($path, array( 'name' => $aStep ));
				}
			}
			if (!$plugin)
			{
				unset($path[0]);
			}

			return $path;
		}
	}

	/**
	* get controller info by ID
	*
	* @param string $controller - current part (controller)
	*
	* @return mixed array - controller information
	* 
	**/
	function getController( $controller )
	{
		$this -> rlValid -> sql( $controller );

		$info = $this -> fetch( '*', array( 'Controller' => $controller ), null, null, 'admin_controllers', 'row' );
		$info = $this -> rlLang -> replaceLangKeys( $info, 'admin_controllers', array( 'name' ), RL_LANG_CODE, 'admin' );

		if ( empty($info) )
		{
			$info = $this -> fetch( array('Name` AS `name', 'Key` AS `Plugin', 'Key', 'Controller'), array( 'Controller' => $controller ), null, null, 'plugins', 'row' );
		}

		$info['prev'] = $_SESSION['ad_prev_page_key'] ? $_SESSION['ad_prev_page_key'] : false;
		$_SESSION['ad_prev_page_key'] = $info['Key'];

		return $info;
	}

	/**
	* integrate special configuration values
	*
	* @param array $configs - referent to current configurations
	*
	* @return mixed array - configurations array with special values
	* 
	**/
	function mixSpecialConfigs( &$configs )
	{
		global $lang, $rlHook, $l_timezone;

		foreach ($configs as $key => &$value)
		{
			$value['Values'] = explode( ',', $value['Values'] );

			if ( in_array($value['Type'], array('select', 'radio', 'checkbox')) )
			{
				if ( is_array($value['Values']) )
				{
					$select_out = array();
					$found_phrases = 0;

					foreach ($value['Values'] as $select_value)
					{
						$phrase = $lang['config+option+'. $value['Key'] .'_'. $select_value];

						if ( $phrase )
						{
							$select_out[] = array(
								'ID' => $select_value,
								'name' => $phrase
							);
							$found_phrases++;
						}
					}

					if ( $found_phrases == count($value['Values']) )
					{
						$value['Values'] = $select_out;
					}
				}
			}

			// mixing by keys
			switch ($value['Key'])
			{
				case 'template':
					$tpl_dir = RL_ROOT . "templates" . RL_DS;
					$values =  $this -> scanDir( $tpl_dir, true );

					foreach ($values as $tk => $tpl)
					{
						if ( (bool)preg_match('/^mobile_/', $tpl) )
						{
							unset($values[$tk]);
						}
					}

					$value['Values'] = $values;
					break;

				case 'mobile_template':
					$tpl_dir = RL_ROOT . "templates" . RL_DS;
					$values =  $this -> scanDir( $tpl_dir, true );

					foreach ($values as $tk => $tpl)
					{
						if ( !(bool)preg_match('/^mobile_/', $tpl) )
						{
							unset($values[$tk]);
						}
					}

					$value['Values'] = $values;
					break;

				case 'lang':
					$langList = $this -> rlLang -> getLanguagesList( 'all' );
					foreach ( $langList as $lIndex => $lValue)
					{
						$langValues[] = $langList[$lIndex]['Code'];
					}
					$value['Values'] = $langValues;
					break;

				case 'listing_feilds_position':
				case 'search_fields_position':
					$value['Display'] = array( $GLOBALS['lang']['under_listing_title'], $GLOBALS['lang']['right_listing_title'] );
					break;

				case 'alphabetic_field':
					$this -> setTable('account_fields');
					$account_fields = $this -> fetch(array('Key`, `Key` AS `ID'), array('Status' => 'active'));
					$this -> resetTable();

					$value['Values'] = $GLOBALS['rlLang'] -> replaceLangKeys( $account_fields, 'account_fields', array('name') );
					break;

				case 'account_quick_account_type':
					$this -> setTable('account_types');
					$account_types = $this -> fetch(array('Key`, `Key` AS `ID'), array('Status' => 'active'), "AND `Key` <> 'visitor'");
					$this -> resetTable();

					$value['Values'] = $GLOBALS['rlLang'] -> replaceLangKeys( $account_types, 'account_types', array('name') );
					break;

				case 'timezone':
					$values = array();
					foreach ($l_timezone as $tz_key => $tz)
					{
						$values[] = array(
							'name' => $tz[1],
							'Key' => $tz_key,
							'ID' => $tz_key
						);
					}
					$value['Values'] = $values;
					break;

				default: 
			}

			$rlHook -> load('apMixConfigItem', $value);
		}
	}

	/**
	* delete page
	*
	* @package ajax
	*
	* @param string $key - page key
	*
	**/
	function ajaxDeletePage( $key )
	{
		global $_response, $lang;

		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );

			return $_response;
		}

		$lang_keys[] = array(
			'Key' => 'pages+name+' . $key,
		);
		$lang_keys[] = array(
			'Key' => 'pages+title+' . $key,
		);
		$lang_keys[] = array(
			'Key' => 'pages+meta_description+' . $key,
		);
		$lang_keys[] = array(
			'Key' => 'pages+meta_keywords+' . $key,
		);
		$lang_keys[] = array(
			'Key' => 'pages+content+' . $key,
		);

		$page_readonly = $this -> fetch( array('Readonly'), array('Key' => $key), "AND `Status` <> 'trash'", 1, 'pages', 'row' );
		if( !$page_readonly['Readonly'] )
		{
			$GLOBALS['rlActions'] -> delete( array( 'Key' => $key ), array('pages', 'lang_keys'), "Readonly = '0'", 1, $key, $lang_keys );

			$del_mode = $GLOBALS['rlActions'] -> action;

			$_response -> script("
				pagesGrid.reload();
				printMessage('notice', '{$lang['page_' . $del_mode]}');
			");
		}
		else
		{
			$_response -> script("printMessage('alert', '{$lang['page_readonly']}')");
		}

		return $_response;
	}

	/**
	* delete block
	*
	* @package ajax
	*
	* @param string $key - block key
	*
	**/
	function ajaxDeleteBlock( $key )
	{
		global $_response, $lang;

		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}

		$lang_keys[] = array(
			'Key' => 'blocks+name+' . $key,
		);
		$lang_keys[] = array(
			'Key' => 'blocks+content+' . $key,
		);

		$block_info = $this -> fetch( array('Readonly', 'Content', 'Type'), array('Key' => $key), "AND `Status` <> 'trash'", 1, 'blocks', 'row' );

		if( !$block_info['Readonly'] )
		{
			$GLOBALS['rlActions'] -> delete( array( 'Key' => $key ), array('blocks', 'lang_keys'), "Readonly = '0'", 1, $key, $lang_keys );

			$del_mode = $GLOBALS['rlActions'] -> action;

			$_response -> script("
				blocksGrid.reload();
				printMessage('notice', '{$lang['block_' . $del_mode]}')
			");

			// delete file | for banner type
			if ($block_info['Type'] == 'banner' && !$GLOBALS['config']['trash'])
			{
				unlink( RL_FILES . $block_info['Content'] );
			}
		}
		else
		{
			$_response -> script("printMessage('alert', '{$lang['block_readonly']}')");
		}

		return $_response;
	}

	/**
	* delete admin
	*
	* @package ajax
	*
	* @param string $id - admin ID
	*
	**/
	function ajaxDeleteAdmin( $id )
	{
		global $_response, $lang;

		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}

		$GLOBALS['rlActions'] -> delete( array( 'ID' => $id ), array('admins'), null, 1, $id );

		$del_mode = $GLOBALS['rlActions'] -> action;

		$_response -> script("
			adminGrid.reload();
			printMessage('notice', '{$lang['admin_' . $del_mode]}');
		");

		return $_response;
	}

	/**
	* get and build an additional fields
	*
	* @package xAjax
	*
	* @param int $id - account type id
	*
	**/
	function ajaxGetAccountFields( $id )
	{
		global $_response, $rlAccount, $rlCommon, $lang, $rlSmarty;

		$fields = $rlAccount -> getFields($id);
		$fields = $this -> rlLang -> replaceLangKeys( $fields, 'account_fields', array( 'name', 'description' ) );
		$fields = $rlCommon -> fieldValuesAdaptation($fields, 'account_fields');

		if ( empty($fields) )
		{
			$_response -> script( "document.account_reg_form.submit();" );
		}
		else 
		{
			foreach ($fields as $key => $value)
			{
				switch ($value['Type']){
					case 'date':
						if ( $fields[$key]['Default'] == 'single' )
						{
							$_response -> script("$('#date_{$fields[$key]['Key']}').datepicker({showOn: 'button', buttonImage: '". RL_TPL_BASE ."img/calendar.png', buttonText: '{$lang['dp_choose_date']}', buttonImageOnly: true, dateFormat: 'yy-mm-dd'}).datepicker($.datepicker.regional['". RL_LANG_CODE ."'])");
						}
						else
						{
							$_response -> script("$('#date_{$fields[$key]['Key']}_from').datepicker({showOn: 'button', buttonImage: '". RL_TPL_BASE ."img/calendar.png', buttonText: '{$lang['dp_choose_date']}', buttonImageOnly: true, dateFormat: 'yy-mm-dd'}).datepicker($.datepicker.regional['". RL_LANG_CODE ."'])");
							$_response -> script("$('#date_{$fields[$key]['Key']}_to').datepicker({showOn: 'button', buttonImage: '". RL_TPL_BASE ."img/calendar.png', buttonText: '{$lang['dp_choose_date']}', buttonImageOnly: true, dateFormat: 'yy-mm-dd'}).datepicker($.datepicker.regional['". RL_LANG_CODE ."'])");
						}
						break;

					case 'accept':
						unset($fields[$key]);
						break;
				}
			}

			$rlSmarty -> assign_by_ref( 'fields', $fields );

			$tpl = 'blocks' . RL_DS . 'account_field.tpl';
			$_response -> assign( 'additional_fields', 'innerHTML', $rlSmarty -> fetch( $tpl, null, null, false ) );
			$_response -> script( "$('#account_field_area').fadeIn('slow');" );

			$_response -> script("
				$('.qtip').each(function(){
					$(this).qtip({
						content: $(this).attr('title'),
						show: 'mouseover',
						hide: 'mouseout',
						position: {
							corner: {
								target: 'topRight',
								tooltip: 'bottomLeft'
							}
						},
						style: {
							width: 150,
							background: '#8e8e8e',
							color: 'white',
							border: {
								width: 7,
								radius: 5,
								color: '#8e8e8e'
							},
							tip: 'bottomLeft'
						}
					});
				}).attr('title', '');
				
				flynax.tabs();
			");

			$_response -> script( "flynax.slideTo('#account_field_area'); $('#next1').slideUp();" );
			$_response -> script( "
				var run = '';
				$('.eval').each(function(){
					run += $(this).html();
				});
				
				eval(run);
			");
		}

		$_response -> script( "$('#step1_loading').fadeOut('normal');" );

		$GLOBALS['rlHook'] -> load('apPhpSubmitProfileEnd');

		return $_response;
	}

	/**
	* delete account
	*
	* @package ajax
	*
	* @param int $id - account ID
	* @param string $reason - reason text
	* @param bool $direct - direct call mode, no xajax code
	*
	**/
	function ajaxDeleteAccount( $id = false, $reason = false, $direct = false )
	{
		global $_response, $config, $lang, $delete_items, $rlHook;

		if ( !$id )
			return $_response;

		// check admin session expire
		if ( $this -> checkSessionExpire() === false && !$direct )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}

		if ( is_array($id) )
		{
			$replace_id = (int)$id[1];
			$id = (int)$id[0];
		}
		else
		{
			$id = (int)$id;
		}

		/* get account info */
		$account_info = $GLOBALS['rlAccount'] -> getProfile($id);

		/* replace mode */
		if ( $replace_id )
		{
			if ( $replace_id == $id )
			{
				$_response -> script("printMessage('error', '". str_replace('{username}', $account_info['Username'], $lang['replace_account_duplicate']) ."');");
				return $_response;
				exit;
			}
			else
			{
				$update = array(
					'fields' => array(
						'Account_ID' => $replace_id
					),
					'where' => array(
						'Account_ID' => $id
					)
				);
				$GLOBALS['rlActions'] -> updateOne($update, 'listings');
				$GLOBALS['rlActions'] -> updateOne($update, 'tmp_categories');
			}
		}

		/* save listings data */
		$sql = "SELECT `T1`.`ID`, `T1`.`Category_ID`, `T1`.`Crossed`, `T2`.`Type` AS `Listing_type` ";
		$sql .= "FROM `". RL_DBPREFIX ."listings` AS `T1` ";
		$sql .= "LEFT JOIN `". RL_DBPREFIX ."categories` AS `T2` ON `T1`.`Category_ID` = `T2`.`ID` ";
		$sql .= "WHERE `T1`.`Account_ID` = '{$id}'";
		$listings = $this -> getAll($sql);

		/* delete/trash account data */
		$delete_items = array('accounts', 'listings');
		$rlHook -> load('deleteAccountSetItems');

		if ( !$config['trash'] )
		{
			$this -> deleteAccountDetails( $id, $listings );
		}

		/* delete account data */
		$GLOBALS['rlActions'] -> delete( array( 'ID' => $id ), $delete_items, false, false, $id );

		/* send message for account owner */
		$this -> loadClass('Mail');
		$mail_tpl = $GLOBALS['rlMail'] -> getEmailTemplate( 'account_deleted' );
		$find = array(
			'{username}',
			'{reason}'
		);
		$replace = array(
			$account_info['Full_name'],
			$reason ? nl2br($reason) : $lang['no_reason_specified']
		);

		$mail_tpl['body'] = str_replace($find, $replace, $mail_tpl['body'] );
		$GLOBALS['rlMail'] -> send( $mail_tpl, $account_info['Mail'] );

		if ( !$direct )
		{
			/* print notice */
			$del_mode = $GLOBALS['rlActions'] -> action;
			$_response -> script("
				accountsGrid.reload();
				$('#delete_block').fadeOut();
				printMessage('notice', '{$lang['account_' . $del_mode .'_notice']}');
			");

			return $_response;
		}
	}

	/**
	* delete account
	*
	* @param int $id - account ID
	* @param array $listings - saved listings data
	*
	**/
	function deleteAccountDetails( $id = false, $listings = false )
	{
		global $rlListings;

		if ( !$id )
			return false;

		/* delete all account messages */
		$this -> query("DELETE FROM `" . RL_DBPREFIX . "messages` WHERE `From` = '{$id}' OR `To` = '{$id}'");

		/* delete thumbnail */
		$account_info = $this -> fetch( array('Photo', 'Username', 'Mail'), array('ID' => $id), null, 1, 'accounts', 'row' );
		if( !empty($account_info['Photo']) )
		{
			unlink( RL_FILES . $account_info['Photo'] );
		}

		/* delete all account files */
		$sql = "SELECT `Key` FROM `" . RL_DBPREFIX . "account_fields` WHERE `Type` = 'file' OR `Type` = 'image'";
		$file_fields = $this -> getAll( $sql );

		if ( $file_fields )
		{
			$files_sql ="SELECT ";
			foreach($file_fields as $key =>$field)
			{
				$files_sql .= "`".$file_fields[$key]['Key']."`, ";
			}
			$files_sql = substr($files_sql, 0, -2);
			$files_sql .=" FROM `" . RL_DBPREFIX . "accounts` WHERE `ID` ='".$id."'";

			$files = $this -> getRow( $files_sql );
		}

		foreach ($files as $key => $value)
		{
			if( !empty($files[$key]) )
			{
				unlink( RL_FILES . $files[$key] );
			}
		}

		/* delete favorites */
		$this -> query( "DELETE FROM `" . RL_DBPREFIX . "favorites` WHERE `Account_ID` = '{$id}'" );

		/* delete saved search */
		$this -> query( "DELETE FROM `" . RL_DBPREFIX . "saved_search` WHERE `Account_ID` = '{$id}'" );

		/* delete custom categories */
		$this -> query( "DELETE FROM `" . RL_DBPREFIX . "tmp_categories` WHERE `Account_ID` = '{$id}'" );

		/* get account listings */
		if ( !$listings )
		{
			$sql = "SELECT `T1`.`ID`, `T1`.`Category_ID`, `T1`.`Crossed`, `T2`.`Type` AS `Listing_type` ";
			$sql .= "FROM `". RL_DBPREFIX ."listings` AS `T1` ";
			$sql .= "LEFT JOIN `". RL_DBPREFIX ."categories` AS `T2` ON `T1`.`Category_ID` = `T2`.`ID` ";
			$sql .= "WHERE `T1`.`Account_ID` = '{$id}'";
			$listings = $this -> getAll($sql);
		}

		if ( $listings )
		{
			$this -> loadClass('Listings', 'admin');
			foreach ($listings as $listing)
			{	
				$rlListings -> deleteListingData($listing['ID'], $listing['Category_ID'], $listing['Crossed'], $listing['Listing_type']);
			}
		}
	}

	/**
	* delete news
	*
	* @package ajax
	*
	* @param string $id - news ID
	*
	**/
	function ajaxDeleteNews( $id )
	{
		global $_response, $lang;

		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}

		$lang_keys[] = array(
			'Key' => 'news+title+' . $id
		);
		$lang_keys[] = array(
			'Key' => 'news+content+' . $id
		);
		$lang_keys[] = array(
			'Key' => 'news+meta_keywords+' . $id
		);
		$lang_keys[] = array(
			'Key' => 'news+meta_description+' . $id
		);

		$GLOBALS['rlActions'] -> delete( array( 'ID' => $id ), array('news'), null, null, $id, $lang_keys );

		$del_mode = $GLOBALS['rlActions'] -> action;

		$_response -> script("
			newsGrid.reload();
			printMessage('notice', '{$lang['news_' . $del_mode]}');
		");

		return $_response;
	}

	/**
	* delete contact
	*
	* @package ajax
	*
	* @param string $id - contact ID
	*
	**/
	function ajaxDeleteContact( $id )
	{
		global $_response, $lang;

		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}

		$GLOBALS['rlActions'] -> delete( array( 'ID' => $id ), array('contacts'), $id, null, $id, false );

		$del_mode = $GLOBALS['rlActions'] -> action;

		$_response -> script("
			contactsGrid.reload();
			printMessage('notice', '{$lang['contact_' . $del_mode]}');
		");

		return $_response;
	}

	/**
	* delete transaction
	*
	* @package ajax
	*
	* @param string $id - transaction ID
	*
	**/
	function ajaxDeleteTransaction( $id )
	{
		global $_response, $lang;

		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}

		if ( false === (bool)strpos($id, '|') )
		{
			$GLOBALS['rlActions'] -> delete( array( 'ID' => $id ), array('transactions'), $id, null, $id, false );
		}
		else
		{
			$ids = explode('|', $id);
			foreach ($ids as $id)
			{
				$GLOBALS['rlActions'] -> delete( array( 'ID' => $id ), array('transactions'), $id, null, $id, false );
			}
		}

		$del_mode = $GLOBALS['rlActions'] -> action;

		$_response -> script("
			transactionsGrid.reload();
			transactionsGrid.checkboxColumn.clearSelections();
			transactionsGrid.actionsDropDown.setVisible(false);
			transactionsGrid.actionButton.setVisible(false);
			printMessage('notice', '{$lang['transaction_' . $del_mode]}');
		");

		return $_response;
	}

	/**
	* delete plan using
	*
	* @package ajax
	*
	* @param string $id - item ID
	*
	**/
	function ajaxDeletePlanUsing( $id )
	{
		global $_response, $lang;

		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}

		$this -> query("DELETE FROM `". RL_DBPREFIX ."listing_packages` WHERE `ID` = '". (int)$id ."' LIMIT 1");

		$_response -> script("
			plansUsingGrid.reload();
			printMessage('notice', '{$lang['item_deleted']}');
		");

		return $_response;
	}

	/**
	* restore item from Trash Box
	*
	* @param string $id - trash item ID
	*
	**/
	function restoreTrashItem( $id )
	{
		if ( !$id )
			return false;
		
		$trash_item = $this -> fetch( '*', array('ID' => $id), null, 1, 'trash_box', 'row' );
		$criterion = $trash_item['Criterion'];

		if ( empty($trash_item['Criterion']) || empty($trash_item['Key']) )
		{
			$GLOBALS['rlDebug'] -> logger("Can not restore item from Trash Box, CRITERIONS or KEY/ID does not define");

			return false;
		}
		
		$tables = explode(',', $trash_item['Zones']);
		
		if ( empty($tables) )
			return false;

		/* call restore method */
		$className = $trash_item['Class_name'];
		$restoreMethod = $trash_item['Restore_method'];
		
		if ( $className && $restoreMethod )
		{
			$this -> loadClass($className, null, $plugin);
			$className = 'rl'.$className;
			
			if ( !method_exists($className, $restoreMethod) )
			{
				$GLOBALS['rlDebug'] -> logger("There are not such method ({$restoreMethod}) in loaded class ({$className})");
				return false;
			}
			
			global $$className;
			$$className -> $restoreMethod($trash_item['Key']);
		}
		
		/* restore item */	
		foreach ( $tables as $table )
		{
			if ( $tables[0] == 'accounts' && $table != 'accounts' )
			{
				$criterion = str_replace( 'ID', 'Account_ID', $criterion );
			}

			switch ($table){
				case 'contacts':
					$new_status = 'readed';
					break;

				case 'categories':
					$new_status = 'active';
					$this -> loadClass('Categories');
					$cat_id = $this -> getOne('ID', $criterion, $table);

					if ($cat_id)
					{
						$GLOBALS['rlCategories'] -> categoryWalker($cat_id, 'restore');
					}

					break;

				/*case 'listing_type':
					$type_info = $this -> fetch(array('Key', 'ID'), null, "WHERE {$trash_item['Criterion']}", 1, 'listing_types', 'row');
					$rlListingTypes -> restoreListingTypeData($type_info['Key']);*/

				default:
					$new_status = 'approval';
					break;
			}

			$sql = "UPDATE `" . RL_DBPREFIX . $table . "` SET `Status` = '{$new_status}' WHERE " . $criterion;
			$this -> query( $sql );
		}

		/* restore language's phrases */
		if ( !empty($trash_item['Lang_keys']) )
		{
			$lang_keys = unserialize($trash_item['Lang_keys']);
			// set trash status for langusge's keys
			foreach ($lang_keys as $lKey => $lVal)
			{
				$l_update[$lKey]['where'] = $lang_keys[$lKey];
				$l_update[$lKey]['fields'] = array( 'Status' => 'active' );
			}

			$this -> loadClass( 'Actions' );
			$GLOBALS['rlActions'] -> update( $l_update, 'lang_keys' );
		}

		/* delete item from trash box */
		$this -> query( "DELETE FROM `" . RL_DBPREFIX . "trash_box` WHERE `ID` = '{$id}' LIMIT 1" );

		return true;
	}

	/**
	* restore item from Trash Box
	*
	* @package ajax
	*
	* @param string $id - item ID
	*
	**/
	function ajaxRestoreTrashItem( $id )
	{
		global $_response, $lang;

		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}

		if ( $this -> restoreTrashItem($id) )
		{
			$_response -> script("
				trashGrid.reload();
				printMessage('notice', '{$lang['item_restored']}');
			");
		}

		return $_response;
	}

	/**
	* delete item from Trash Box
	*
	* @package ajax
	*
	* @param string $id - item ID
	*
	**/
	function ajaxDeleteTrashItem( $id )
	{
		global $_response, $lang;

		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}

		if ( $this -> deleteTrashItem($id) )
		{
			$_response -> script("
				trashGrid.reload();
				printMessage('notice', '{$lang['item_deleted']}');
			");
		}

		return $_response;
	}

	/**
	* delete item from Trash Box
	*
	* @param string $id - trash item ID
	*
	**/
	function deleteTrashItem( $id = false )
	{
		if ( !$id )
			return false;
		
		$trash_item = $this -> fetch('*', array('ID' => $id), null, 1, 'trash_box', 'row');

		if ( empty($trash_item['Criterion']) || (empty($trash_item['Key']) && $trash_item['Zones'] != 'listings') )
		{
			$GLOBALS['rlDebug'] -> logger("Can not delete item from Trash Box, CRITERIONS or KEY/ID does not define");
			return false;
		}
		
		$tables = $trash_item['Zones'];
		if ( false !== strpos($tables, ',') )
		{
			$tables = explode(',', $tables);
		}

		if ( !is_array( $tables ) )
		{
			$tables = array($tables);
		}
		
		if ( empty($tables) )
			return false;

		/* call delete method */
		$className = $trash_item['Class_name'];
		$deleteMethod = $trash_item['Remove_method'];
		
		if ( $className && $deleteMethod )
		{
			$this -> loadClass($className, null, $plugin);
			$className = 'rl'.$className;
			
			if ( !method_exists($className, $deleteMethod) )
			{
				$GLOBALS['rlDebug'] -> logger("There are not such method ({$deleteMethod}) in loaded class ({$className})");
				return false;
			}
			
			global $$className;
			$$className -> $deleteMethod($trash_item['Key']);
		}
		
		/* delete items */
		foreach ( $tables as $table )
		{
			$criterion = $trash_item['Criterion'];

			switch ($table){
				case 'accounts':
					$this -> deleteAccountDetails($trash_item['Key']);
					break;

				case 'listings':
					$this -> loadClass('Listings');
					$GLOBALS['rlListings'] -> deleteListingData($trash_item['Key']);
					break;

				case 'categories':
					$this -> loadClass('Categories');
					$cat_info = $this -> fetch(array('Key', 'ID'), null, "WHERE {$trash_item['Criterion']}", 1, 'categories', 'row');

					$GLOBALS['rlCategories'] -> categoryWalker($cat_info['ID'], 'delete');
					$GLOBALS['rlCategories'] -> deleteCatRelations($cat_info['ID']);
					break;

				case 'listing_groups':
					$this -> loadClass('Categories');
					$group_key = $this -> fetch(array('Key'), null, "WHERE {$trash_item['Criterion']}", 1, 'listing_groups', 'row');

					$GLOBALS['rlCategories'] -> deleteGroupRelations($group_key['Key']);
					break;

				case 'data_formats':
					$format_id = $this -> getOne('ID', $criterion, $table);

					// get child keys
					$this -> setTable('data_formats');
					$child_keys = $this -> fetch(array('Key'), array('Parent_ID' => $format_id));
					$this -> resetTable();

					// remove items lang keys
					foreach ($child_keys as $cKey => $cVal)
					{
						$this -> query("DELETE FROM `".RL_DBPREFIX."lang_keys` WHERE `Key` = 'data_formats+name+".$child_keys[$cKey]['Key']."'");
					}

					if ($format_id)
					{
						$this -> query("DELETE FROM `" . RL_DBPREFIX . $table . "` WHERE `Parent_ID` = '{$format_id}'");
					}
					break;

				case 'search_forms':
					$form_id = $this -> getOne('ID', "{$trash_item['Criterion']}", 'search_forms');
					$this -> query("DELETE FROM `". RL_DBPREFIX ."search_forms_relations` WHERE `Form_ID` = '{$form_id}'");
					break;

				default:
					if ( $tables[0] == 'accounts' && $table != 'accounts' )
					{
						$criterion = str_replace( 'ID', 'Account_ID', $criterion );
					}
					break;
			}

			$sql = "DELETE FROM `" . RL_DBPREFIX . $table . "` WHERE " . $criterion;
			$this -> query( $sql );
		}

		/* delete language's phrases */
		if ( !empty($trash_item['Lang_keys']) )
		{
			$lang_keys = unserialize($trash_item['Lang_keys']);

			if ( !empty($lang_keys) )
			{
				foreach ($lang_keys as $lKey => $lVal)
				{
					$where .= "`Key` = '{$lang_keys[$lKey]['Key']}' OR ";
				}
				$where = substr($where, 0, -3);
				$this -> query("DELETE FROM `" . RL_DBPREFIX . "lang_keys` WHERE {$where}");
			}
		}

		/* delete item from trash box */
		$this -> query( "DELETE FROM `" . RL_DBPREFIX . "trash_box` WHERE `ID` = '{$id}' LIMIT 1" );

		return true;
	}

	/**
	* clear trash box
	*
	* @package ajax
	*
	**/
	function ajaxClearTrash()
	{
		global $_response, $lang;

		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}

		$this -> setTable('trash_box');

		$trash = $this -> fetch('ID');

		foreach ( $trash as $item )
		{
			$this -> deleteTrashItem( $item['ID'] );
		}

		$_response -> script("
			trashGrid.reload();
			printMessage('notice', '{$lang['trash_cleared']}');
			$('.button_bar span.center_remove').html('{$lang['clear_trash']}');
		");

		return $_response;
	}

	/**
	* mass actions with trash
	*
	* @package xAjax
	*
	* @param string $ids     - listings ids, ex: 250,356,501,...
	* @param string $action  - mass action
	*
	**/
	function ajaxTrashMassActions( $ids, $action )
	{
		global $_response, $lang;

		$ids = explode('|', $ids);

		foreach ($ids as $id)
		{
			if ( $action == 'delete' )
			{
				$this -> deleteTrashItem( $id );
				$notice = $lang['notice_items_deleted'];
			}
			elseif ( $action == 'restore' )
			{
				$this -> restoreTrashItem( $id );
				$notice = $lang['notice_items_restored'];
			}
		}

		$_response -> script("
			trashGrid.reload();
			printMessage('notice', '{$notice}');
		");

		return $_response;
	}

	/**
	* run sql query
	*
	* @package ajax
	*
	* @param string $query - SQL query
	*
	**/
	function ajaxRunSqlQuery( $query )
	{
		global $_response, $lang;

		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}

		$lines = preg_split( '/\r\n|\r|\n/', $query );

		if ( isset($lines[1]) )
		{
			foreach ( $lines as $query )
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

				if (!empty($query_sql))
				{
					$query_sql = str_replace(array('{prefix}', '{sql_prefix}'), RL_DBPREFIX, $query_sql);
				}

				$res = mysql_query( $query_sql );
				if (!$res)
				{
					$errors[] = $lang['can_not_run_sql_query'] . mysql_error();
				}
				unset($query_sql);

				$rows += mysql_affected_rows();
			}

			if ( $errors )
			{
				$out = '<ul>';
				/* print errors */
				foreach ($errors as $error)
				{
					$out .= '<li>'. $error .'</li>';
				}
				$out .= '</ul>';
				$_response -> script("printMessage('error', '{$out}');");
			}
			else
			{
				$_response -> script("printMessage('notice', '". str_replace('{number}', '<b>'.$rows.'</b>', $lang['query_ran']) ."');");
			}
		}
		else
		{
			$query = str_replace(array('{prefix}', '{sql_prefix}', '{db_prefix}'), RL_DBPREFIX, $query);
			$res = @mysql_query( $query );

			if ( !$res )
			{
				$error = mysql_error();
				$_response -> script('printMessage("error", "'. $error .'");');
			}
			else
			{
				$rows = mysql_affected_rows();
				$notice = str_replace('{number}', '<b>'.$rows.'</b>', $lang['query_ran']);

				preg_match("/^(SELECT|SHOW|select|show)(.*)\s*FROM\s*(\`".RL_DBPREFIX."([a-z]|-|_)*\`)/", $query, $matches);

				if ( !empty($matches[3]) )
				{
					// Convert to array
					while ( $row = mysql_fetch_assoc( $res ) )
					{
						// Add to array
						$index = count( $out );
						$out[$index] = $row;
					}

					if ( trim($matches[2]) == '*' )
					{
						$tmp_fields = $this -> getAll("SHOW COLUMNS FROM {$matches[3]}");
						$count = count($out[0]);

						foreach ($tmp_fields as $key => $value)
						{
							if ( $key < $count )
							{
								$fields[] = $value['Field'];
							}
						}
					}
					else
					{
						function bs_trim( &$item, $key )
						{
							$item = str_replace('`', '', $item);
						}

						$fields = explode(',', $matches[2]);
						array_walk($fields, 'bs_trim');
					}

					$GLOBALS['rlSmarty'] -> assign_by_ref('fields', $fields);
					$GLOBALS['rlSmarty'] -> assign_by_ref('out', $out);

					$tpl = 'blocks' . RL_DS . 'batabase_grid.tpl';

					$_response -> assign("grid", 'innerHTML', $GLOBALS['rlSmarty'] -> fetch( $tpl, null, null, false ));
				}

				$_response -> script("printMessage('notice', '{$notice}')");
			}
		}

		$_response -> script("$('#run_button').val('{$lang['go']}')");

		return $_response;
	}

	/**
	* get all pages keys=>paths
	* 
	* @return array - pages keys/paths
	**/
	function getAllPages()
	{
		$this -> setTable( 'pages' );
		$pages = $this -> fetch( array( 'Key', 'Path' ) );
		$this -> resetTable();

		foreach ( $pages as $key => $value )
		{
			$out[$pages[$key]['Key']] = $pages[$key]['Path'];
		}
		unset($pages);

		return $out;
	}

	/**
	* delete account type
	*
	* @package ajax
	*
	* @param string $key - account type Key
	* @param string $reason - remove type reason message
	* @param int $replace_key - new account type key to replace with
	*
	**/
	function ajaxDeleteAccountType( $key = false, $reason = false, $replace_key = false )
	{
		global $_response, $lang, $config, $rlActions;

		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}

		if ( !$key )
			return $_response;

		if ( is_array($key) )
		{
			$replace_key = $key[1];
			$key = $key[0];
		}

		/* replace type id mode */
		if ( $replace_key )
		{
			$update = array(
				'fields' => array(
					'Type' => $replace_key
				),
				'where' => array(
					'Type' => $key
				)
			);
			$GLOBALS['rlActions'] -> updateOne($update, 'accounts');
		}
		else
		{
			/* check exist accounts used requested account type */
			$this -> setTable('accounts');
			$accounts = $this -> fetch(array('ID', 'Username', 'First_name', 'Last_name', 'Mail'), array('Type' => $key));
			$this -> resetTable();

			/* delete accounts data */
			if ( $accounts )
			{
				foreach ($accounts as $account)
				{
					$this -> ajaxDeleteAccount($account['ID'], $reason, true);
				}
			}
		}

		/* delete account type */
		$lang_keys[] = array(
			'Key' => 'account_types+name+' . $key
		);
		$lang_keys[] = array(
			'Key' => 'account_types+desc+' . $key
		);

		if ( !$config['trash'] )
		{
			// remove enum option from listing plans table
			$rlActions -> enumRemove('listing_plans', 'Allow_for', $key);
		}

		/* delete acounts */
		$rlActions -> delete( array( 'Key' => $key ), array('account_types'), null, null, $key, $lang_keys );
		$del_mode = $rlActions -> action;

		$_response -> script("
			accountTypesGrid.reload();
			printMessage('notice', '{$lang['item_' . $del_mode]}');
			$('#delete_block').fadeOut();
		");

		return $_response;
	}

	/**
	* account type pre deleting checking
	*
	* @package ajax
	*
	* @param string $key - account type Key
	*
	**/
	function ajaxPreAccountTypeDelete( $key = false )
	{
		global $_response, $config, $lang, $rlHook, $rlSmarty;

		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}

		/* get account details */
		$account_type = $this -> fetch(array('ID', 'Key'), array('Key' => $key), null, 1, 'account_types', 'row');
		$account_type['name'] = $lang['account_types+name+'. $account_type['Key']];
		$rlSmarty -> assign_by_ref('account_type', $account_type);

		/* check account types count */
		$this -> setTable('account_types');
		$available = $this -> fetch(array('ID'), null, "WHERE `Key` <> 'visitor' AND `Status` <> 'trash'");

		if ( count($available) <= 1 )
		{
			$_response -> script("
				$('#delete_block').stop().fadeOut();
				printMessage('alert', '{$lang['limit_account_types_remove']}');
			");
			return $_response;
		}

		/* check exist accounts use requested account type */
		$this -> setTable('accounts');
		$accounts = $this -> fetch(array('ID', 'Username', 'First_name', 'Last_name', 'Mail'), array('Type' => $key), "AND `Status` <> 'trash'");

		$accounts_total = count($accounts);

		$delete_details[] = array(
			'name' => $lang['accounts'],
			'items' => $accounts_total,
			'link' => RL_URL_HOME . ADMIN . '/index.php?controller=accounts&amp;account_type='. $key
		);
		$delete_total_items += $accounts_total;

		/* check exist listings use requested account types */
		$sql = "SELECT COUNT(`T1`.`ID`) AS `Count` ";
		$sql .= "FROM `". RL_DBPREFIX ."listings` AS `T1` ";
		$sql .= "LEFT JOIN `". RL_DBPREFIX ."accounts` AS `T2` ON `T1`.`Account_ID` = `T2`.`ID` ";
		$sql .= "WHERE `T2`.`Type` = '{$key}' AND `T1`.`Status` <> 'trash'";
		$listings = $this -> getRow($sql);

		$listings_total = $listings['Count'];

		$delete_details[] = array(
			'name' => $lang['listings'],
			'items' => $listings_total,
			'link' => RL_URL_HOME . ADMIN . '/index.php?controller=listings&amp;account_type='. $key
		);
		$delete_total_items += $listings_total;

		$rlHook -> load('deleteAccountTypeDataCollection');

		$rlSmarty -> assign_by_ref('delete_details', $delete_details);

		if ( $delete_total_items )
		{
			$tpl = 'blocks' . RL_DS . 'delete_preparing_account_type.tpl';
			$_response -> assign("delete_container", 'innerHTML', $GLOBALS['rlSmarty'] -> fetch( $tpl, null, null, false ));
			$_response -> script("$('#delete_block').slideDown();");
		}
		else
		{
			$phrase = $config['trash'] ? $lang['trash_confirm'] : $lang['drop_confirm'];
			$_response -> script("
				$('#delete_block').slideUp();
				rlConfirm('{$phrase}', 'xajax_deleteAccountType', '{$key}');
			");
		}

		return $_response;
	}

	/**
	* update account fields kit
	*
	* @package ajax
	*
	* @param int $type_id - account type ID
	* @param int $account_id - account ID
	*
	**/
	function ajaxUpdateAccountFields( $type_id = false, $account_id = false )
	{
		global $_response, $account_info, $rlAccount, $rlActions, $rlCommon, $account_info, $rlSmarty, $lang;

		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}

		/* update type */
		$new_type_key = $this -> getOne('Key', "`ID` = '{$type_id}'", 'account_types');
		$update = array(
			'fields' => array(
				'Type' => $new_type_key
			),
			'where' => array(
				'ID' => $account_id
			)
		);
		$rlActions -> updateOne($update, 'accounts');

		/* update form */
		$account_fields = $rlAccount -> getFields((int)$type_id);
		$account_fields = $this -> rlLang -> replaceLangKeys($account_fields, 'account_fields', array( 'name', 'description' ));
		$account_fields = $rlCommon -> fieldValuesAdaptation($account_fields, 'account_fields');

		if ( !empty($account_fields) )
		{
			foreach ($account_info as $i_index => $i_val)
			{
				$search_fields[$i_index] = $i_index;
			}

			foreach ($account_fields as $key => $value)
			{
				if ( $account_info[$account_fields[$key]['Key']] != '' )
				{
					switch ($account_fields[$key]['Type'])
					{
						case 'mixed':
							$df_item = false;
							$df_item = explode( '|', $account_info[$account_fields[$key]['Key']] );

							$account_fields[$key]['value'] = $df_item[0];
							$account_fields[$key]['df'] = $df_item[1];
						break;

						case 'date':
							if ( $account_fields[$key]['Default'] == 'single' )
							{
								$account_fields[$key]['current'] = $account_info[$search_fields[$account_fields[$key]['Key']]];
							}
							elseif ( $account_fields[$key]['Default'] == 'multi' )
							{
								$account_fields[$key]['from'] = $account_info[$account_fields[$key]['Key']];
								$account_fields[$key]['to'] = $account_info[$account_fields[$key]['Key'].'_multi'];
							}
						break;

						case 'price':
							$price = false;
							$price = explode( '|', $account_info[$account_fields[$key]['Key']] );

							$account_fields[$key]['value'] = $price[0];
							$account_fields[$key]['currency'] = $price[1];
						break;

						case 'unit':
							$unit = false;
							$unit = explode( '|', $account_info[$account_fields[$key]['Key']] );

							$account_fields[$key]['value'] = $unit[0];
							$account_fields[$key]['unit'] = $unit[1];
						break;

						case 'checkbox':
							$ch_items = null;
							$ch_items = explode(',', $account_info[$account_fields[$key]['Key']]);

							$account_fields[$key]['current'] = $ch_items;
							unset($ch_items);
						break;

						case 'accept':
							unset($account_fields[$key]);
							break;

						default:
							$account_fields[$key]['current'] = $account_info[$search_fields[$account_fields[$key]['Key']]];
						break;
					}
				}
			}

			$rlSmarty -> assign_by_ref( 'fields', $account_fields );

			$tpl = 'blocks' . RL_DS . 'account_field.tpl';
			$_response -> assign( 'additional_fields', 'innerHTML', $GLOBALS['rlSmarty'] -> fetch( $tpl, null, null, false ) );
			$_response -> script("$('#account_field_area').fadeIn(); $('#next1').slideUp();");

			foreach ($account_fields as $key => $value)
			{
				if ( $value['Type'] == 'date' )
				{
					if ( $value['Default'] == 'single' )
					{
						$_response -> script("$('#date_{$value['Key']}').datepicker({showOn: 'both', buttonImage: '". RL_TPL_BASE ."img/blank.gif', buttonText: '{$lang['dp_choose_date']}', buttonImageOnly: true, dateFormat: 'yy-mm-dd'}).datepicker($.datepicker.regional['". RL_LANG_CODE ."'])");
					}
					else
					{
						$_response -> script("$('#date_{$value['Key']}_from').datepicker({showOn: 'both', buttonImage: '". RL_TPL_BASE ."img/blank.gif', buttonText: '{$lang['dp_choose_date']}', buttonImageOnly: true, dateFormat: 'yy-mm-dd'}).datepicker($.datepicker.regional['". RL_LANG_CODE ."'])");
						$_response -> script("$('#date_{$value['Key']}_to').datepicker({showOn: 'both', buttonImage: '". RL_TPL_BASE ."img/blank.gif', buttonText: '{$lang['dp_choose_date']}', buttonImageOnly: true, dateFormat: 'yy-mm-dd'}).datepicker($.datepicker.regional['". RL_LANG_CODE ."'])");
					}
				}
			}

			$_response -> script("
				$('.qtip').each(function(){
					$(this).qtip({
						content: $(this).attr('title'),
						show: 'mouseover',
						hide: 'mouseout',
						position: {
							corner: {
								target: 'topRight',
								tooltip: 'bottomLeft'
							}
						},
						style: {
							width: 150,
							background: '#8e8e8e',
							color: 'white',
							border: {
								width: 7,
								radius: 5,
								color: '#8e8e8e'
							},
							tip: 'bottomLeft'
						}
					});
				}).attr('title', '');
			");

			$_response -> script( "flynax.slideTo('#account_field_area');" );
			$_response -> script( "$('.eval').each(function(){
				eval($(this).html());
			});");
		}
		else
		{
			$_response -> script("
				$('#account_field_area').fadeOut();
				$('#next1').slideDown();
				printMessage('alert', '{$lang['account_type_has_not_fields']}');
			");
		}

		$_response -> script("$('#type_change_loading').fadeOut('normal');");

		return $_response;
	}

	/**
	* delete search form
	*
	* @package ajax
	*
	* @param string $key - search form KEY
	*
	**/
	function ajaxDeleteSearchForm( $key )
	{
		global $_response, $lang;

		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}

		$info = $this -> fetch( array('Readonly', 'ID'), array('Key' => $key), "AND `Status` <> 'trash'", 1, 'search_forms', 'row' );

		if ( $info )
		{
			$lang_keys[] = array(
				'Key' => 'search_forms+name+' . $key
			);

			if( !$info['Readonly'] )
			{
				$GLOBALS['rlActions'] -> delete( array( 'Key' => $key ), array('search_forms', 'lang_keys'), "Readonly = '0'", 1, $key, $lang_keys );

				$del_mode = $GLOBALS['rlActions'] -> action;

				$_response -> script("searchFormsGrid.reload();");

				// delete relations if delete
				if ( !$GLOBALS['config']['trash'] )
				{
					$this -> query("DELETE FROM `". RL_DBPREFIX ."search_forms_relations` WHERE `Category_ID` = '{$info['ID']}'");
				}

				$_response -> script( "printMessage('notice', '{$lang['form_' . $del_mode]}');" );
			}
			else
			{
				$_response -> script( "printMessage('alert', '{$lang['form_readonly']}');" );
			}
		}
		else
		{
			trigger_error( "Can not delete search form, exist query resopnse is empty", E_WARNING );
			$GLOBALS['rlDebug'] -> logger("Can not delete search form, exist query resopnse is empty");
		}

		return $_response;
	}

	/**
	* get Flynax RSS blog feed 
	*
	* @package ajax
	*
	**/
	function ajaxGetFlynaxRss()
	{
		global $config, $lang, $rlSmarty, $reefless, $_response;

		$rl_news_feed = $reefless -> getPageContent($config['flynax_news_feed']);

		$reefless -> loadClass( 'Rss' );
		$GLOBALS['rlRss'] -> items_number = $config['flynax_news_number'];
		$GLOBALS['rlRss'] -> items[] = 'date';
		$GLOBALS['rlRss'] -> createParser($rl_news_feed);

		$rss_content = $GLOBALS['rlRss'] -> getRssContent();
		$rlSmarty -> assign_by_ref('rss_content', $rss_content);
		$tpl = 'blocks'. RL_DS .'flynaxNews.blocks.tpl';
		$_response -> assign('flynax_news_container', 'innerHTML', $GLOBALS['rlSmarty'] -> fetch( $tpl, null, null, false ) );
		$_response -> script("$('#flynax_news_container').fadeIn('normal')");

		return $_response;
	}

	/**
	* get Flynax RSS blog feed 
	*
	* @package ajax
	*
	**/
	function ajaxGetPluginsLog()
	{
		global $config, $lang, $rlSmarty, $reefless, $_response;

		/* parse xml */
		$change_log = $reefless -> getPageContent($config['flynax_plugins_log_feed']);

		$reefless -> loadClass( 'Rss' );
		$GLOBALS['rlRss'] -> items_number = 20;
		$GLOBALS['rlRss'] -> items = array('key', 'name', 'version', 'comment', 'date');
		$GLOBALS['rlRss'] -> createParser($change_log);
		$change_log_content = $GLOBALS['rlRss'] -> getRssContent();

		/* check current plugins conditions */
		$this -> setTable('plugins');
		$tmp_plugins = $this -> fetch(array('Key', 'Version'));
		$this -> resetTable();

		foreach ($tmp_plugins as $index => $plugin)
		{
			$plugins[$plugin['Key']] = $plugin['Version'];
		}
		unset($tmp_plugins);

		foreach ($change_log_content as $index => $item)
		{
			$change_log_content[$index]['current'] = $plugins[$item['key']];

			if ( $plugins[$item['key']] )
			{
				$compare = version_compare($item['version'], $plugins[$item['key']]);
				$status = false;

				switch ($compare){
					case 0:
						 $status = 'current';
						break;
					case 1:
						$status = 'update';
						break;
					case -1:
						$status = $plugins[$item['key']] ? 'no' : 'install';
						break;
				}

				$change_log_content[$index]['status'] = $status;
			}
			else
			{
				$change_log_content[$index]['status'] = 'install';
			}
		}

		/* build DOM */
		$rlSmarty -> assign_by_ref('change_log_content', $change_log_content);
		$tpl = 'blocks'. RL_DS .'flynaxPluginsLog.blocks.tpl';
		$_response -> assign('plugins_log_container', 'innerHTML', $GLOBALS['rlSmarty'] -> fetch( $tpl, null, null, false ) );
		$_response -> script("$('#plugins_log_container').fadeIn('normal')");

		$_response -> call('rlPluginRemoteInstall');

		return $_response;
	}

	/**
	* check plugin for update
	*
	* @param string $key - requested plugin key
	*
	* @package ajax
	*
	**/
	function ajaxCheckForUpdate( $key = false )
	{
		global $config, $lang, $rlSmarty, $reefless, $_response;

		if ( !$key )
			return $_response;
			
		/* parse xml */
		$change_log = $reefless -> getPageContent($config['flynax_plugins_browse_feed'] .'?key='. $key);

		$reefless -> loadClass( 'Rss' );
		$GLOBALS['rlRss'] -> items_number = 1;
		$GLOBALS['rlRss'] -> items = array('key', 'name', 'version', 'comment', 'date');
		$GLOBALS['rlRss'] -> createParser($change_log);
		$change_log_content = $GLOBALS['rlRss'] -> getRssContent();

		if ( !$change_log_content )
		{
			$_response -> script("printMessage('error', '{$lang['flynax_connect_fail']}');");
			return $_response;
		}

		/* get requested plugin details */
		$plugin_info = $this -> fetch(array('Name', 'Version'), array('Key' => $key), null, 1, 'plugins', 'row');
		$update_available = false;

		$log = $change_log_content[1];
		
		if ( version_compare($log['version'], $plugin_info['Version']) > 0 && $log['key'] == $key )
		{
			$update_available = true;
			$update_version = $log['version'];
		}

		if ( $update_available )
		{
			$link = '<a target="_blank" href="http://www.flynax.com/plugins/'. strtolower($key) .'.html#changelog">'. $plugin_info['Name'] .'</a>';

			$_response -> script("
				$('#browse_area').slideUp('fast');
				$('#update_area').slideDown();
				flynax.slideTo('#bc_container');
				$('#update_version').html('{$update_version}');
				$('#update_link').html('{$link}');
				$('#plugin_name').html('{$plugin_info['Name']}');
				update_plugin_key = '{$key}';
			");
		}
		else
		{
			$_response -> script("printMessage('alert', '". str_replace('{plugin}', $plugin_info['Name'], $lang['plugin_update_not_found']) ."');");
		}

		return $_response;
	}

	/**
	* save configs
	*
	* @package ajax
	*
	**/
	function ajaxSaveConfig( $data = false )
	{
		global $_response, $rlActions, $lang, $config;

		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}

		foreach ( $data as $item )
		{
			if ( $item['key'] == 'lang' )
			{
				if ( $item['value'] != RL_LANG_CODE )
				{
					$redirect = "language=". $item['value'];
				}
			}
			elseif ( $item['key'] == 'flynax_news_number' )
			{
				$update = array(
					'fields' => array(
						'Default' => $item['value']
					),
					'where' => array(
						'Key' => $item['key']
					)
				);
				$rlActions -> updateOne($update, 'config');

				if ( $item['value'] != $config[$item['key']] )
				{
					$_response -> script("
						if ( $('.block div[lang=flynax_news]').is(':visible') )
						{
							xajax_getFlynaxRss();
						}
					");
				}
			}
			else
			{
				if ( !$item['deny'] )
				{
					$update = array(
						'fields' => array(
							'Default' => $item['value']
						),
						'where' => array(
							'Key' => $item['key']
						)
					);
					$rlActions -> updateOne($update, 'config');
				}
			}
		}

		$_response -> script('$("#save_settings").val("'. $lang['save']. '")');

		if ( $redirect )
		{
			$_response -> redirect( RL_URL_HOME . ADMIN . '/index.php?'. $redirect );
		}

		return $_response;
	}

	/**
	* fetch adapt and admin panel blocks
	*
	* @todo assign blocks array in smarty
	*
	**/
	function assignBlocks()
	{
		global $rlDb, $rlSmarty;

		$rlDb -> setTable('admin_blocks');
		$blocks = $rlDb -> fetch(array('Column', 'Key', 'Ajax', 'Content', 'Fixed', 'Position', 'Status'), array('Status' => 'active'), "ORDER BY `Position`");
		$rlDb -> resetTable();
		$blocks = $this -> rlLang -> replaceLangKeys( $blocks, 'admin_blocks', array( 'name' ), RL_LANG_CODE, 'admin' );

		$cookie_blocks_status_tmp = explode(',', $_COOKIE['ap_blocks_status']);

		foreach ($cookie_blocks_status_tmp as $item)
		{
			$tmp_item = explode('|', $item);
			$cookie_blocks_status[$tmp_item[0]] = $tmp_item[1];
		}
		unset($cookie_blocks_status_tmp);

		$cookie_blocks_fixed_tmp = explode(',', $_COOKIE['ap_blocks_fixed']);
		foreach ($cookie_blocks_fixed_tmp as $item)
		{
			$tmp_item = explode('|', $item);
			$cookie_blocks_fixed[$tmp_item[0]] = $tmp_item[1];
		}
		unset($cookie_blocks_fixed_tmp);

		$rlSmarty -> assign_by_ref('blocks', $blocks);

		foreach ($_COOKIE as $cIndex => $cValue)
		{
			if ( false !== strpos($cIndex, 'ap_arrangement') )
			{
				$column = str_replace('ap_arrangement_', '', $cIndex);
				$cItems = explode(',', $cValue);
				foreach ($cItems as $cItem)
				{
					$cItemExp = explode('|', $cItem);
					$cookie_items[$cItemExp[0]] = $column;
				}
			}
		}

		foreach ($blocks as $key => $value)
		{
			$blocks[$key]['Status'] = isset($cookie_blocks_status[$value['Key']]) ? ($cookie_blocks_status[$value['Key']] == 'true' ? 'active' : 'approval') : $blocks[$key]['Status'];
			$blocks[$key]['Fixed'] = isset($cookie_blocks_fixed[$value['Key']]) ? ($cookie_blocks_fixed[$value['Key']] == 'true' ? 1 : 0) : $blocks[$key]['Fixed'];
			if ( $cookie_items[$value['Key']] )
			{
				$ap_blocks[$cookie_items[$value['Key']]][] = $blocks[$key];
			}
			else
			{
				$ap_blocks[$value['Column']][] = $blocks[$key];
			}
		}

		foreach ($ap_blocks as $key => $value)
		{
			if ( $_COOKIE['ap_arrangement_'. $key] )
			{
				$cookie_column_tmp = explode(',', $_COOKIE['ap_arrangement_'. $key]);

				foreach ( $cookie_column_tmp as $vk => $vi)
				{
					$it = explode('|', $vi);
					$cookie_column[$it[0]] = $it[1];
				}

				$new_value = false;
				foreach ($value as $bk => $bv)
				{
					$position = isset($cookie_column[$bv['Key']]) ? $cookie_column[$bv['Key']] : $bv['Position'];
					$new_value[$position] = $bv;
					$ap_blocks[$key] = $new_value;
				}
				ksort($ap_blocks[$key]);
			}
		}

		$rlSmarty -> assign_by_ref('ap_blocks', $ap_blocks);
	}

	/**
	* get content statistics
	*
	* @todo get and assign content statistic to SMARTY
	**/
	function getStatistics()
	{
		global $lang, $config, $content_stat, $rlSmarty;

		/* get listings */
		$listing_statuses = array('new', 'active', 'pending', 'incomplete', 'expired');
		$sql = "SELECT ";
		foreach ($listing_statuses as $l_status)
		{
			switch ($l_status){
				case 'active':
					$sql .= "SUM( IF(`T1`.`Status` = '{$l_status}' AND ( UNIX_TIMESTAMP(DATE_ADD(`T1`.`Pay_date`, INTERVAL `T2`.`Listing_period` DAY)) > UNIX_TIMESTAMP(NOW()) OR `T2`.`Listing_period` = 0 ), 1, 0) ) AS `{$l_status}`, ";
					break;

				case 'new':
					$sql .= "SUM( IF(( UNIX_TIMESTAMP(DATE_ADD(`T1`.`Pay_date`, INTERVAL `T2`.`Listing_period` DAY)) > UNIX_TIMESTAMP(NOW()) OR `T2`.`Listing_period` = 0 ) AND UNIX_TIMESTAMP(`T1`.`Pay_date`) BETWEEN UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL {$config['new_period']} DAY)) AND UNIX_TIMESTAMP(NOW()), 1, 0) ) AS `{$l_status}`, ";
					break;

				default:
					$sql .= "SUM( IF(`T1`.`Status` = '{$l_status}', 1, 0) ) AS `{$l_status}`, ";
					break;
			}
		}
		$sql = rtrim($sql, ', ');

		$sql .= "FROM `". RL_DBPREFIX ."listings` AS `T1` ";
		$sql .= "LEFT JOIN `". RL_DBPREFIX ."listing_plans` AS `T2` ON `T1`.`Plan_ID` = `T2`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "categories` AS `T3` ON `T1`.`Category_ID` = `T3`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "accounts` AS `T4` ON `T1`.`Account_ID` = `T4`.`ID` ";
		$sql .= "WHERE `T3`.`Status` = 'active' AND `T4`.`Status` <> 'trash' ";

		$listings = $this -> getRow($sql);

		$content_stat['listings'] = array(
			'name' => $lang['listings'],
			'total' => array_sum($listings) - $listings['new'],
			'items' => $listings
		);
		unset($listings, $sql, $listing_statuses);

		/* get accounts */
		$account_statuses = array('new', 'active', 'pending', 'incomplete');
		$sql = "SELECT ";
		foreach ($account_statuses as $a_status)
		{
			switch ($a_status){
				case 'new':
					$sql .= "SUM( IF(UNIX_TIMESTAMP(`T1`.`Date`) BETWEEN UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL {$config['new_period']} DAY)) AND UNIX_TIMESTAMP(NOW()), 1, 0) ) AS `{$a_status}`, ";
					break;

				default:
					$sql .= "SUM( IF(`T1`.`Status` = '{$a_status}', 1, 0) ) AS `{$a_status}`, ";
					break;
			}
		}
		$sql = rtrim($sql, ', ');

		$sql .= "FROM `". RL_DBPREFIX ."accounts` AS `T1` ";
		$sql .= "LEFT JOIN `". RL_DBPREFIX ."account_types` AS `T2` ON `T1`.`Type` = `T2`.`Key` ";
		$sql .= "WHERE `T2`.`Status` = 'active' ";

		$accounts = $this -> getRow($sql);

		$content_stat['accounts'] = array(
			'name' => $lang['accounts'],
			'total' => array_sum($accounts) - $accounts['new'],
			'items' => $accounts
		);
		unset($accounts, $sql, $account_statuses);

		/* custom categories */
		$sql = "SELECT COUNT('ID') AS `Count` FROM `". RL_DBPREFIX ."tmp_categories` WHERE `Status` = 'approval'";

		$custom_categories = $this -> getRow($sql);

		$content_stat['categories'] = array(
			'name' => $lang['admin_controllers+name+custom_categories'],
			'new' => $custom_categories['Count']
		);
		unset($custom_categories, $sql);

		/* contacts */
		$sql = "SELECT COUNT('ID') AS `Count`, SUM(IF(`Status` = 'new', 1, 0)) AS `New` FROM `". RL_DBPREFIX ."contacts`";

		$contacts = $this -> getRow($sql);

		$content_stat['contacts'] = array(
			'name' => $lang['contacts'],
			'total' => $contacts['Count'],
			'new' => $contacts['New']
		);
		unset($contacts, $sql);

		$rlSmarty -> assign_by_ref('content_stat', $content_stat);
	}

	/**
	* add new amenity
	*
	* @param array $names = amenity names
	*
	* @package ajax
	*
	**/
	function ajaxAddAmenity( $names = false )
	{
		global $_response, $rlActions, $lang, $config, $rlValid;

		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}

		/* load the utf8 lib */
		loadUTF8functions('ascii', 'utf8_to_ascii', 'unicode');

		/* check names */
		$languages = $GLOBALS['languages'];
		foreach ($languages as $language)
		{
			if ( empty($names[$language['Code']]) )
			{
				$errors[] = str_replace( '{field}', "'<b>{$lang['value']} ({$language['name']})</b>'", $lang['notice_field_empty']);
			}
			else
			{
				if ( !$key )
				{
					$key = $names[$language['Code']];
					if ( !utf8_is_ascii( $key ) )
					{
						$key = utf8_to_ascii( $key );
					}

					$key = $rlValid -> str2key($key, '-');
					$key = $rlValid -> uniqueKey($key, 'map_amenities');
				}

				$lang_keys[] = array(
					'Code' => $language['Code'],
					'Module' => 'common',
					'Key' => 'map_amenities+name+'.$key,
					'Value' => $names[$language['Code']]
				);
			}
		}

		if ( $errors )
		{
			$out = '<ul>';
			/* print errors */
			foreach ($errors as $error)
			{
				$out .= '<li>'. $error .'</li>';
			}
			$out .= '</ul>';
			$_response -> script("printMessage('error', '{$out}');");
		}
		else
		{
			$position = $this -> getRow( "SELECT MAX(`Position`) AS `max` FROM `" . RL_DBPREFIX . "map_amenities`" );

			$insert = array(
				'Key' => $key,
				'Position' => $position['max'] + 1
			);

			/* insert new item */
			if ( $rlActions -> insertOne($insert, 'map_amenities') )
			{
				$rlActions -> insert($lang_keys, 'lang_keys');

				$_response -> script("printMessage('notice', '{$lang['item_added']}')");

				$_response -> script( "mapAmenitiesGrid.reload();" );
				$_response -> script( "$('#new_item').slideUp('normal')" );
			}
		}

		$_response -> script("
			$('input[name=add_item_submit]').val('{$lang['add']}');
			$('#new_item input[type=text]').val('');
		");

		return $_response;
	}

	/**
	* edit amenity names by key
	*
	* @param string $key = amenity key
	* @param array $names = amenity names
	*
	* @package ajax
	*
	**/
	function ajaxEditAmenity( $key = falsek, $names = false )
	{
		global $_response, $rlActions, $lang, $config, $rlValid;

		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}

		if ( !$key || !$names )
			return $_response;

		/* check names */
		foreach ($GLOBALS['languages'] as $language)
		{
			if ( empty($names[$language['Code']]) )
			{
				$errors[] = str_replace( '{field}', "'<b>{$lang['value']} ({$language['name']})</b>'", $lang['notice_field_empty']);
			}
			else
			{
				if ( $this -> getOne('ID', "`Key` = 'map_amenities+name+{$key}' AND `Code` = '{$language['Code']}' AND 1", 'lang_keys') )
				{
					$lang_keys_update[] = array(
						'fields' => array(
							'Value' => $names[$language['Code']]
						),
						'where' => array(
							'Code' => $language['Code'],
							'Key' => 'map_amenities+name+'.$key
						)
					);
				}
				else
				{
					$lang_keys_insert[] = array(
						'Code' => $language['Code'],
						'Module' => 'common',
						'Key' => 'map_amenities+name+'.$key,
						'Value' => $names[$language['Code']]
					);
				}
			}
		}

		if ( $errors )
		{
			$out = '<ul>';
			/* print errors */
			foreach ($errors as $error)
			{
				$out .= '<li>'. $error .'</li>';
			}
			$out .= '</ul>';
			$_response -> script("printMessage('error', '{$out}');");
		}
		else
		{
			if ( $lang_keys_update )
			{
				$rlActions -> update($lang_keys_update, 'lang_keys');
			}
			if ( $lang_keys_insert )
			{
				$rlActions -> insert($lang_keys_insert, 'lang_keys');
			}

			$_response -> script("printMessage('notice', '{$lang['item_edited']}')");

			$_response -> script( "mapAmenitiesGrid.reload();" );
			$_response -> script( "$('#new_item').slideUp('normal')" );
		}

		$_response -> script( "
			$('input[name=edit_item_submit]').val('{$lang['edit']}');
			$('#edit_item').slideUp();
			$('#edit_item input[type=text]').val('');
		" );

		return $_response;
	}

	/**
	* delete amenity
	*
	* @param string $key - amenity key
	*
	* @package ajax
	*
	**/
	function ajaxDeleteAmenity( $key = false )
	{
		global $_response, $rlActions, $lang, $config, $rlValid;

		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}

		if ( !$key )
			return $_response;

		$this -> query("DELETE FROM `". RL_DBPREFIX ."map_amenities` WHERE `Key` = '{$key}' LIMIT 1");
		$this -> query("DELETE FROM `". RL_DBPREFIX ."lang_keys` WHERE `Key` = 'map_amenities+name+{$key}'");
		$_response -> script("mapAmenitiesGrid.reload();");

		$_response -> script("printMessage('notice', '{$lang['item_deleted']}')");

		return $_response;
	}

	/**
	* check new admin messqages
	*
	**/
	function checkNewMessages()
	{
		global $rlSmarty;

		$id = $_SESSION['sessAdmin']['user_id'];

		$sql = "SELECT COUNT(`ID`) AS `Count` FROM `" . RL_DBPREFIX . "messages` WHERE `To` = '{$id}' AND `Status` = 'new' AND `Admin` = '1'";
		$count = $this -> getRow($sql);

		$rlSmarty -> assign_by_ref('new_messages', $count['Count']);
	}

	/**
	* delete saved search
	*
	* @package ajax
	*
	* @param string $id - saved search ID
	*
	**/
	function ajaxDeleteSavedSearch( $id = false )
	{
		global $_response, $lang;

		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );

			return $_response;
		}

		$GLOBALS['rlActions'] -> delete(array( 'ID' => $id ), array('saved_search'));
		$del_mode = $GLOBALS['rlActions'] -> action;

		$_response -> script("
			savedSearchesGrid.reload();
			printMessage('notice', '{$lang['item_' . $del_mode]}');
		");

		return $_response;
	}

	/**
	* make search by saved search criteria
	*
	* @package ajax
	*
	* @param string $id - saved search ID
	*
	**/
	function ajaxCheckSavedSearch( $id = false )
	{
		global $_response, $lang, $config, $rlListingTypes, $pages, $rlSmarty, $rlActions;

		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );

			return $_response;
		}

		/* get saved search details */
		$saved_search = $this -> fetch('*', array('ID' => $id), null, 1, 'saved_search', 'row');
		$saved_search['Content'] = unserialize($saved_search['Content']);

		$checked_listings = $saved_search['Matches'];
		$exploded_matches = explode(',', $checked_listings);

		$this -> loadClass('Search');
		$this -> loadClass('Mail');

		/* load fields */
		$GLOBALS['rlSearch'] -> getFields( $saved_search['Form_key'], $saved_search['Lising_type'] );

		$GLOBALS['rlSearch'] -> exclude = $saved_search['Matches'];
		$matches = $GLOBALS['rlSearch'] -> search($saved_search['Content'], $saved_search['Listing_type'], 0, 20);
		$GLOBALS['rlSearch'] -> exclude = false;
		
		if ( $matches )
		{
			foreach ( $matches as $listing )
			{
				if ( !in_array($listing['ID'], $exploded_matches) )
				{
					$checked_listings .= empty($checked_listings) ? $listing['ID'] : ','.$listing['ID'];

					$page_path = $pages[$rlListingTypes -> types[$listing['Listing_type']]['Page_key']];

					$link = RL_URL_HOME;
					$link .= $config['mod_rewrite'] ? $page_path .'/'. $listing['Path'] .'/'. $rlSmarty -> str2path($listing['listing_title']) .'-'. $listing['ID'] .'.html' : '?page='. $page_path .'&amp;id='. $listing['ID'];
					$links .= '<a href="'.$link.'">'.$link.'</a><br />';

					$counter += 1;
				}
			}

			/* send notification */
			if ( $counter )
			{
				/* get profile details */
				$this -> loadClass('Account');
				$profile_data = $GLOBALS['rlAccount'] -> getProfile((int)$saved_search['Account_ID']);

				$email_tpl = $GLOBALS['rlMail'] -> getEmailTemplate('cron_saved_search_match');
				$email_tpl['body'] = str_replace(array('{username}', '{count}', '{links}'), array($profile_data['Full_name'], $counter, $links), $email_tpl['body']);

				$GLOBALS['rlMail'] -> send($email_tpl, $profile_data['Mail']);

				$message = str_replace(array('{count}', '{username}'), array($counter, $profile_data['Full_name']), $lang['saved_search_search_results']);
				$_response -> script("printMessage('notice', '{$message}')");
			}
			else
			{
				$_response -> script("printMessage('alert', '{$lang['saved_search_no_listings_found']}')");
			}

			/* update entry */
			$update = array(
				'fields' => array(
					'Date' => 'NOW()',
					'Cron' => 1,
					'Matches' => $checked_listings
				),
				'where' => array(
					'ID' => $id
				)
			);
			$rlActions -> updateOne($update, 'saved_search');
		}
		else
		{
			$_response -> script("printMessage('alert', '{$lang['saved_search_no_listings_found']}')");
		}

		return $_response;
	}
}
