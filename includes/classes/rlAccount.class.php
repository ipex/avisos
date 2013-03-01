<?php

/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: RLACCOUNT.CLASS.PHP
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

class rlAccount extends reefless
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
	* @var Actions class object
	**/
	var $rlActions;

	/**
	* @var common class object
	**/
	var $rlCommon;

	/**
	* @var Google Map fields list
	**/
	var $mapLocation = array();

	/**
	* @var calculate items
	**/
	var $calc;

	/**
	* @var calculate items in alphabet mode
	**/
	var $calc_alphabet;

	/**
	* @var login return message type
	**/
	var $messageType = 'error';

	/**
	* class constructor
	**/
	function rlAccount()
	{
		global $rlLang, $rlValid, $rlConfig, $rlActions, $rlCommon;

		$this -> rlLang   = & $rlLang;
		$this -> rlValid  = & $rlValid;
		$this -> rlConfig = & $rlConfig;
		$this -> rlActions = & $rlActions;
		$this -> rlCommon = & $rlCommon;
	}

	/**
	* get accounts types information
	*
	* @param array $except - except keys
	*
	* @return array - lsit of account types
	**/
	function getAccountTypes( $except = false )
	{
		global $fields;

		if ( $except )
		{
			if ( is_array($except) )
			{
				$additional .= " AND `Key` <> '". implode("' AND `Key` <> '", $except) ."' ";
			}
			else
			{
				$additional .= " AND `Key` <> '{$except}' ";
			}
		}

		$fields = array('ID', 'Key', 'Abilities', 'Page', 'Own_location', 'Email_confirmation', 'Admin_confirmation', 'Auto_login');
		
		$GLOBALS['rlHook'] -> load('rlAccountGetAccountTypesFields', $fields); // from v4.1.0
		
		$types = $this -> fetch($fields, array('Status' => 'active'), "{$additional} ORDER BY `Position`", null, 'account_types' );
		$types = $this -> rlLang -> replaceLangKeys( $types, 'account_types', array( 'name', 'desc' ) );

		foreach ($types as $type)
		{
			$tmp_types[$type['ID']] = $type;
		}
		$types = $tmp_types;
		unset($tmp_types);

		return $types;
	}

	/**
	* get account type details
	*
	* @param mixed $key - account key or ID
	*
	* @return array - requested account details
	**/
	function getAccountType( $key = false )
	{
		global $fields;

		$where = is_int($key) ? array('ID' => $key) : array('Key' => $key);
		$where['Status'] = 'active';

		$fields = array('ID', 'Key', 'Abilities', 'Page', 'Own_location');
		$type = $this -> fetch($fields, $where, null, 1, 'account_types', 'row');

		if ( !$type )
			return false;

		$type = $this -> rlLang -> replaceLangKeys( $type, 'account_types', array( 'name', 'desc' ) );
		$type['Abilities'] = explode(',', $type['Abilities']);

		unset($fields);

		return $type;
	}

	/**
	* account registration
	*
	* @param int $type_id - account type id
	* @param array $step1 - step 1 data
	* @param array $account - step 2 data
	* @param array $fields - current account type fields
	*
	**/
	function registration( $type_id, $profile, $account, $fields )
	{
		global $pages, $config, $rlMail, $lang, $rlCommon, $account_types;

		// get account type key
		$account_type_key = $this -> getOne( 'Key', "`ID` = '{$type_id}'", 'account_types');

		if ( empty($account_type_key) )
		{
			return false;
		}

		// step 1
		$data = array(
			'Type' => $account_type_key,
			'Username' => $profile['username'],
			'Own_address' => $profile['location'],
			'Password' => md5($profile['password']),
			'Password_tmp' => $account_types[$type_id]['Email_confirmation'] ? $profile['password'] : '',
			'Lang' => strtolower(RL_LANG_CODE),
			'Mail' => $profile['mail'],
			'Date' => 'NOW()'
		);

		if ( $profile['display_email'] )
		{
			$data['Display_email'] = 1;
		}

		// check administrator confirmation
		$data['Status'] = $account_types[$type_id]['Admin_confirmation'] && !defined('REALM') ? 'pending' : 'active';

		if ( $account_types[$type_id]['Email_confirmation'] && !defined('REALM'))
		{
			$confirm_code = md5(mt_rand());
			$data['Confirm_code'] = $confirm_code;
			$data['Status'] = 'incomplete';
		}

		$GLOBALS['rlHook'] -> load('phpRegistrationBeforeInsert'); // from v4.0.2

		// insert data
		if ( $this -> rlActions -> insertOne($data, 'accounts', array('Username', 'Password', 'Password_tmp')) )
		{
			$account_id = mysql_insert_id();
			$name = $account['First_name'] || $account['Last_name'] ? trim($account['First_name'] .' '. $account['Last_name']) : $profile['username'];

			// prepare email confirmation
			if ( $account_types[$type_id]['Email_confirmation'] && !defined('REALM') )
			{
				// create activation link
				$activation_link = SEO_BASE;
				$activation_link .= $config['mod_rewrite'] ? "{$pages['confirm']}.html?key=" : "?page={$pages['confirm']}&amp;key=" ;
				$activation_link .= $confirm_code;
				$activation_link = '<a href="' . $activation_link . '">' . $activation_link . '</a>';

				$mail_tpl = $rlMail -> getEmailTemplate('account_created_incomplete');
				$find = array(
					'{activation_link}',
					'{username}',
				);
				$replace = array(
					$activation_link,
					$name
				);
				$mail_tpl['body'] = str_replace( $find, $replace, $mail_tpl['body'] );
			}
			else
			{
				if ( defined('REALM') )
				{
					$mail_tpl_key = 'account_created_active';
				}
				else
				{
					$mail_tpl_key = $account_types[$type_id]['Admin_confirmation'] ? 'account_created_pending' : 'account_created_active';
				}
				$mail_tpl = $rlMail -> getEmailTemplate($mail_tpl_key);

				$account_area_link = SEO_BASE;
				$account_area_link .= $config['mod_rewrite'] ? $pages['login'] .'.html' : '?page='. $pages['login'];
				$account_area_link = '<a href="'. $account_area_link .'">'. $lang['blocks+name+account_area'] .'</a>';

				$find = array(
					'{username}',
					'{password}',
					'{name}',
					'{account_area}'
				);
				$replace = array(
					$profile['username'],
					$profile['password'],
					$name,
					$account_area_link
				);
				$mail_tpl['body'] = str_replace( $find, $replace, $mail_tpl['body'] );
			}

			/* send e-mail to new user */
			$rlMail -> send( $mail_tpl, $profile['mail'] );

			/* prepare admin notification e-mail */
			$mail_tpl = $rlMail -> getEmailTemplate( 'account_created_admin' );

			$details_link = RL_URL_HOME . ADMIN .'/index.php?controller=accounts&amp;action=view&amp;userid='. $account_id;
			$details_link = '<a href="'. $details_link .'">'. $details_link .'</a>';

			$find = array('{first_name}', '{last_name}', '{username}', '{join_date}', '{status}', '{details_link}');
			$replace = array(
				empty($account['First_name']) ? 'Not specified' : $account['First_name'],
				empty($account['Last_name']) ? 'Not specified' : $account['Last_name'],
				$profile['username'],
				date(str_replace(array('b', '%'), array('M', ''), RL_DATE_FORMAT)),
				$lang[$data['Status']],
				$details_link
			);
			$mail_tpl['body'] = str_replace( $find, $replace, $mail_tpl['body'] );

			if ( $account_types[$type_id]['Admin_confirmation'] )
			{
				$activation_link = RL_URL_HOME . ADMIN .'/index.php?controller=accounts&amp;action=remote_activation&amp;id='. $account_id . '&amp;hash='. md5($this -> getOne('Date', "`ID` = '{$account_id}'", 'accounts'));
				$activation_link = '<a href="'. $activation_link .'">'. $activation_link .'</a>';
				$mail_tpl['body'] = preg_replace('/(\{if activation is enabled\})(.*)(\{activation_link\})(.*)(\{\/if\})/', '$2 '. $activation_link .' $4', $mail_tpl['body']);
			}
			else
			{
				$mail_tpl['body'] = preg_replace('/\{if activation is enabled\}(.*)\{\/if\}/', '', $mail_tpl['body']);
			}

			/* send e-mail to admin */
			$rlMail -> send( $mail_tpl, $config['site_main_email'] );

			// step 2
			if (empty($account))
			{
				return true;
			}

			foreach ( $fields as $fIndex => $fRow )
			{
				$sFields[$fIndex] = $fields[$fIndex]['Key'];
			}

			foreach ( $account as $key => $value )
			{
				$poss = array_search( $key, $sFields );

				if ( $fields[$poss]['Map'] && $value[$key] )
				{
					$location[] = $rlCommon -> adaptValue($fields[$poss], $value);
				}

				switch ($fields[$poss]['Type']){

					case 'text':
						if ( $fields[$poss]['Multilingual'] && count($GLOBALS['languages']) > 1 )
						{
							$out = '';
							foreach ($GLOBALS['languages'] as $language)
							{
								$val = $account[$key][$language['Code']];
								if ( $val )
								{
									$out .= "{|{$language['Code']}|}". $val ."{|/{$language['Code']}|}";
								}
							}

							$data2['fields'][$key] = $out;
						}
						else
						{
							$data2['fields'][$key] = $account[$key];
						}
						
						break;

					case 'textarea':
						if ( $fields[$poss]['Condition'] == 'html' )
						{
							$html_fields[] = $fields[$poss]['Key'];
						}
						
						if ( $fields[$poss]['Multilingual'] && count($GLOBALS['languages']) > 1 )
						{
							$limit = (int)$fields[$poss]['Values'];
							
							$out = '';
							foreach ($GLOBALS['languages'] as $language)
							{
								$val = $account[$key][$language['Code']];
								if ( $limit && $fields[$poss]['Condition'] != 'html' )
								{
									$limit = (int)$fields[$poss]['Values'];
									if ( function_exists('mb_substr') && function_exists('mb_internal_encoding') )
									{
										mb_internal_encoding('UTF-8');
										$val = mb_substr($val, 0, $limit);
									}
									else
									{
										$val = substr($val, 0, $limit);
									}
								}
								
								if ( $val )
								{
									$out .= "{|{$language['Code']}|}". $val ."{|/{$language['Code']}|}";
								}
							}
							$data2['fields'][$key] = $out;
						}
						else
						{
							if ( $fields[$poss]['Values'] )
							{
								$limit = (int)$fields[$poss]['Values'];
								
								if ( $limit && $fields[$poss]['Condition'] != 'html' )
								{
									if ( function_exists('mb_substr') && function_exists('mb_internal_encoding') )
									{
										mb_internal_encoding('UTF-8');
										$data2['fields'][$key] = mb_substr($account[$key], 0, $limit);
									}
									else
									{
										$data2['fields'][$key] = substr($account[$key], 0, $limit);
									}
								}
							}
							$data2['fields'][$key] = $data2['fields'][$key];
						}
					
						break;
					
					case 'select':
					case 'bool':
					case 'radio':
						$data2['fields'][$key] = $account[$key];

						break;
						
					case 'number':
						$data2['fields'][$key] = preg_replace('/[^\d]/', '', $account[$key]);
						
						break;
						
					case 'phone':
						$out = '';
					
						/* code */
						if ( $fields[$poss]['Opt1'] )
						{
							$code = (int)substr($account[$key]['code'], 0, $fields[$poss]['Default']);
							$out = 'c:'. $code .'|';
						}
						
						/* area */
						$area = (int)$account[$key]['area'];
						$out .= 'a:'. $area . '|';
						
						/* number */
						$number = (int)substr($account[$key]['number'], 0, $fields[$poss]['Values']);
						$out .= 'n:'. $number;
						
						/* extension */
						if ( $fields[$poss]['Opt2'] )
						{
							$ext = (int)$account[$key]['ext'];
							$out .= '|e:'. $ext;
						}
						
						$data2['fields'][$key] = $out;
						break;

					case 'mixed':
						$data2['fields'][$key] = $account[$key]['value'].'|'.$account[$key]['df'];
						break;

					case 'unit':
						$data2['fields'][$key] = $account[$key]['value'].'|'.$account[$key]['unit'];
						break;

					case 'date':
						if ( $fields[$poss]['Default'] == 'single' )
						{
							$data2['fields'][$key] = $account[$key];
						}
						elseif ($fields[$poss]['Default'] == 'multi')
						{
							$data2['fields'][$key] = $account[$key]['from'];
							$data2['fields'][$key.'_multi'] = $account[$key]['to'];
						}

						break;

					case 'checkbox';

						unset($account[$key][0]);
						foreach ($account[$key] as $chRow)
						{
							$chValues .= $chRow.",";
						}
						$chValues = substr( $chValues, 0, -1);

						$data2['fields'][$key] = $chValues;

						break;

					case 'image':

						$file_name = 'account_' .  $account_id . '_' . $key . '_' . time();
						$resize_type = $fields[$poss]['Default'];
						$resolution = strtoupper($resize_type) == 'C' ? explode('|', $fields[$poss]['Values']) : $fields[$poss]['Values'];

						$file_name = $this -> rlActions -> upload( $key, $file_name, $resize_type, $resolution, false, false );
						$data2['fields'][$key] = $file_name;

						break;

					case 'file':

						$file_name = 'account_' .  $account_id . '_' . $key . '_' . time();
						$file_name = $this -> rlActions -> upload( $key, $file_name, false, false, false, false );
						$data2['fields'][$key] = $file_name;

						break;
				}
			}

			if ( !empty($data2) )
			{
				/* get coordinates by address request */
				if ( $location )
				{
					$address = implode(', ', $location);
					$address = urlencode($address);
					$content = $this -> getPageContent("http://maps.googleapis.com/maps/api/geocode/json?address={$address}&sensor=false");

					$this -> loadClass('Json');
					$content = $GLOBALS['rlJson'] -> decode($content);
					if ( strtolower($content -> status) == 'ok' )
					{
						$data2['fields']['Loc_address'] = $content -> results[0] -> formatted_address;
						$data2['fields']['Loc_latitude'] = $content -> results[0] -> geometry -> location -> lat;
						$data2['fields']['Loc_longitude'] = $content -> results[0] -> geometry -> location -> lng;
					}
				}

				$data2['where'] = array('ID' => $account_id);

				$GLOBALS['rlHook'] -> load('phpRegistrationBeforeUpdate'); // from v4.0.2

				$this -> rlActions -> updateOne( $data2, 'accounts' );
			}

			return true;
		}
	}

	/**
	* quick user registration
	*
	* @param string $name - user name
	* @param string $email - user e-mail
	*
	**/
	function quickRegistration( $name = false, $email = false )
	{
		global $rlActions, $rlSmarty, $config;

		if ( !$name || !$email )
			return;

		/* preapare data */
		loadUTF8functions('ascii', 'utf8_to_ascii', 'unicode');

		$password = $this -> generateHash(10, 'password', true);
		$username = !utf8_is_ascii($name) ? utf8_to_ascii($name) : $name;
		$this -> rlValid -> sql( $username );

		$user_exist = $this -> getRow("SELECT COUNT(`ID`) AS `Count` FROM `". RL_DBPREFIX ."accounts` WHERE `Username` REGEXP '{$username}[0-9]?'");

		$username = $user_exist['Count'] ? $username . ($user_exist['Count'] + 1) : $username;
		$first_name = $name;

		if ( $config['account_quick_account_type'] && $this -> getOne('ID', "`Key` = '{$config['account_quick_account_type']}' AND `Status` = 'active'", 'account_types') )
		{
			$type = $config['account_quick_account_type'];
		}
		else
		{
			$type = $this -> getOne('Key', "`ID` <> 1 ORDER BY `ID` ASC", 'account_types');
		}

		/* insert new account */
		if ( is_array($exp_name = explode(' ', $name)) )
		{
			$first_name = $exp_name[0];
			array_shift($exp_name);
			$last_name = implode(' ', $exp_name);
		}

		$own_address = $rlSmarty -> str2path($username);

		$inser = array(
			'Quick' => 1,
			'Type' => $type,
			'Username' => $username,
			'Own_address' => $own_address,
			'Password' => md5($password),
			'Lang' => strtolower(RL_LANG_CODE),
			'First_name' => $first_name,
			'Last_name' => $last_name,
			'Mail' => $email,
			'Date' => 'NOW()',
			'Status' => 'active'
		);

		$GLOBALS['rlHook'] -> load('phpQuickRegistrationBeforeInsert'); // from v4.0.2

		$rlActions -> insertOne($inser, 'accounts');
		$account_id = mysql_insert_id();

		unset($inser, $user_exist, $first_name, $last_name);

		return array($username, $password, $account_id);
	}

	/**
	* get fields by account type ID
	*
	* @param int $id - account type ID
	*
	**/
	function getFields( $id = false )
	{
		$sql = "SELECT `T1`.`Key`, `T1`.`Type`, `T1`.`Default`, `T1`.`Values`, `T1`.`Condition`, `T1`.`Required`, `T1`.`Map`, `T1`.`Add_page`, ";
		$sql .= "`T1`.`Details_page`, `T1`.`Multilingual`, `T1`.`Opt1`, `T1`.`Opt2` ";
		$sql .= "FROM `". RL_DBPREFIX ."account_fields` AS `T1` ";
		$sql .= "LEFT JOIN `". RL_DBPREFIX ."account_submit_form` AS `T2` ON `T1`.`ID` = `Field_ID` ";
		$sql .= "WHERE `T1`.`Status` = 'active' AND `T2`.`Category_ID` = '{$id}' ";
		$sql .= "ORDER BY `T2`.`Position`";

		$tmp_fields = $this -> getAll($sql);

		foreach ($tmp_fields as $field)
		{
			$fields[$field['Key']] = $field;
		}
		unset($tmp_fields);

		return $fields;
	}

	/**
	* get profile details
	*
	* @param mixed $id - account id or location address
	*
	**/
	function getProfile( $id = false, $edit_mode = false )
	{
		global $lang, $config, $rlValid, $pages, $rlMobile;

		if ( !$id )
		{
			return false;
		}

		$sql = "SELECT `T1`.*, `T2`.`ID` AS `Account_type_ID`, `T2`.`Own_location`, `T2`.`Page` AS `Own_page`, ";
		$sql .= "(";
		$sql .= "SELECT COUNT(`T3`.`ID`) ";
		$sql .= "FROM `". RL_DBPREFIX ."listings` AS `T3` ";
		$sql .= "LEFT JOIN `". RL_DBPREFIX ."listing_plans` AS `T4` ON `T3`.`Plan_ID` = `T4`.`ID` ";
		if ( defined('REALM') )
		{
			$sql .= "WHERE `T3`.`Account_ID` = `T1`.`ID` AND `T3`.`Status` <> 'trach'";
		}
		else
		{
			$sql .= "WHERE `T3`.`Account_ID` = `T1`.`ID` AND (UNIX_TIMESTAMP(DATE_ADD(`T3`.`Pay_date`, INTERVAL `T4`.`Listing_period` DAY)) > UNIX_TIMESTAMP(NOW()) OR `T4`.`Listing_period` = 0) AND `T3`.`Status` = 'active'";
		}
		$sql .= " ) ";
		$sql .= "AS `Listings_count` ";
		$sql .= "FROM `". RL_DBPREFIX ."accounts` AS `T1` ";
		$sql .= "LEFT JOIN `". RL_DBPREFIX ."account_types` AS `T2` ON `T1`.`Type` = `T2`.`Key` ";
		if ( is_int($id) )
		{
			$sql .= "WHERE `T1`.`ID` = '{$id}' LIMIT 1";
		}
		else
		{
			$this -> rlValid -> sql( $id );
			$sql .= "WHERE `T1`.`Own_address` = '{$id}' LIMIT 1";
		}

		$data = $this -> getRow($sql);

		if ( !$data )
			return false;

		unset($data['Password']);

		$data['Full_name'] = $data['First_name'] || $data['Last_name'] ? $data['First_name'] .' '. $data['Last_name'] : $data['Username'];
		$data['Type_name'] = $lang['account_types+name+'. $data['Type']];
		$data['Type_description'] = $lang['account_types+desc+'. $data['Type']];

		/* generate personal address */
		if ( $data['Own_page'] )
		{
			if ( $data['Own_location'] && $data['Own_address'] && $config['mod_rewrite'] )
			{
				if ( $config['account_wildcard'] )
				{
					$tmp_seo_base = defined('REALM') && REALM == 'admin' ? RL_URL_HOME : SEO_BASE;
					$tmp_seo_base = str_replace('www.', '', $tmp_seo_base);
					$tmp_seo_base = $rlMobile -> isMobile ? str_replace(RL_URL_HOME, rtrim(RL_MOBILE_URL, 'index.php'), $tmp_seo_base) : $tmp_seo_base;
					$data['Personal_address'] = 'http://'. $data['Own_address'] .'.'. str_replace(array('http://', 'https://'), '', $tmp_seo_base);
				}
				else
				{
					$data['Personal_address'] = defined('REALM') && REALM == 'admin' ? RL_URL_HOME : SEO_BASE;
					$data['Personal_address'] .= $data['Own_address'] .'/';
					
					if ( $rlMobile -> isMobile )
					{
						$data['Personal_address'] = str_replace(RL_URL_HOME, rtrim(RL_MOBILE_URL, 'index.php'), $data['Personal_address']);
					}
				}
			}
			else
			{
				$page_key = 'at_'. $data['Type'];
				$data['Personal_address'] = SEO_BASE;
				$data['Personal_address'] .= $config['mod_rewrite'] ? $pages[$page_key] .'/'. $data['Own_address'] .'.html': '?page='. $pages[$page_key] .'&amp;id='. $data['ID'];
				
				if ( $rlMobile -> isMobile )
				{
					$data['Personal_address'] = str_replace(RL_URL_HOME, rtrim(RL_MOBILE_URL, 'index.php'), $data['Personal_address']);
				}
			}
		}

		$fields = $this -> getFields($data['Account_type_ID']);
		$fields = $this -> rlLang -> replaceLangKeys( $fields, 'account_fields', array( 'name', 'description' ) );

		if ( $edit_mode )
		{
			$fields = $GLOBALS['rlCommon'] -> fieldValuesAdaptation($fields, 'account_fields');
		}

		foreach ($fields as $key => $field)
		{
			if ( (empty($data[$key]) && !$edit_mode) || ($field['Type'] == 'accept' && $edit_mode) )
				continue;

			$data['Fields'][$key] = $field;
			$data['Fields'][$key]['value'] = $GLOBALS['rlCommon'] -> adaptValue( $field, $data[$key], 'account', $id, null, null, $edit_mode );

			if ( $field['Map'] )
			{
				$mValue = str_replace( "'", "\'", $data['Fields'][$key]['value'] );
				$this -> mapLocation['search'] .= $mValue .', ';
				$this -> mapLocation['show'] .= $field['name'].': <b>'. $mValue .'<\/b><br />';
			}
		}

		if ( $this -> mapLocation )
		{
			$this -> mapLocation['search'] = substr($this -> mapLocation['search'], 0, -2);
			$this -> mapLocation['show'] = substr($this -> mapLocation['show'], 0, -11);
		}

		unset($fields);

		return $data;
	}

	/**
	* if user exist
	*
	* @package xAjax
	*
	* @param string $username - requested username
	*
	**/
	function ajaxUserExist( $username = false )
	{
		global $_response, $lang;

		if ( !$username )
		{
			return $_response;
		}

		$username = trim( $username );
		$this -> rlValid -> sql( $username );
		$exist = (bool)$this -> getOne('ID', "`Username` = '{$username}'", 'accounts');
		$message = str_replace('{username}', $username, $lang['notice_account_exist']);

		$GLOBALS['rlHook'] -> load('phpAjaxUserExist', $username, $message, $exist); // from v4.0.2

		if ( $exist || empty( $username ) )
		{
			$_response -> script("
				if ( !$('input[name=\"profile[username]\"]').hasClass('error') )
				{
					printMessage('error', '{$message}');
					$('input[name=\"profile[username]\"]').addClass('error').next().remove();
					$('input[name=\"profile[username]\"]').after('<span class=\"fail_field\">&nbsp;</span>');
				}
			");
		}
		else
		{
			$_response -> script("
				$('div.error div.close').trigger('click');
				$('input[name=\"profile[username]\"]').removeClass('error').next().remove();
				$('input[name=\"profile[username]\"]').after('<span class=\"success_field\">&nbsp;</span>');
			");
		}

		return $_response;
	}

	/**
	* if email exist
	*
	* @package xAjax
	*
	* @param string $email - requested e-mail
	*
	**/
	function ajaxEmailExist( $email = false )
	{
		global $_response, $lang;

		if ( !$email )
			return $_response;

		if ( !$this -> rlValid -> isEmail($email) )
		{
			$_response -> script("
				if ( !$('input[name=\"profile[mail]\"]').hasClass('error') )
				{
					printMessage('error', '". str_replace('{email}', $email, $lang['notice_bad_email']) ."');
					$('input[name=\"profile[mail]\"]').addClass('error').next().remove();
					$('input[name=\"profile[mail]\"]').after('<span class=\"fail_field\">&nbsp;</span>');
				}
			");
			return $_response;
		}

		$this -> rlValid -> sql( $email );
		$exist = (bool)$this -> getOne('ID', "`Mail` = '{$email}'", 'accounts');
		$message = str_replace('{email}', $email, $lang['notice_account_email_exist']);

		$GLOBALS['rlHook'] -> load('phpAjaxEmailExist', $email, $message); // from v4.0.2

		if ( $exist )
		{
			$_response -> script("
				if ( !$('input[name=\"profile[mail]\"]').hasClass('error') )
				{
					printMessage('error', '{$message}');
					$('input[name=\"profile[mail]\"]').addClass('error').next().remove();
					$('input[name=\"profile[mail]\"]').after('<span class=\"fail_field\">&nbsp;</span>');
				}
			");
		}
		else
		{
			$_response -> script("
				$('div.error div.close').trigger('click');
				$('input[name=\"profile[mail]\"]').removeClass('error').next().remove();
				$('input[name=\"profile[mail]\"]').after('<span class=\"success_field\">&nbsp;</span>');
			");
		}

		return $_response;
	}

	/**
	* check personal address field value
	*
	* @package xAjax
	*
	* @param int $location - personal address value
	*
	**/
	function ajaxCheckLocation( $location = false )
	{
		global $_response, $lang, $config;

		if ( !$location )
		{
			return $_response;
		}

		/* validate */
		$location = trim($location);
		$wildcard_deny = explode(',', $config['account_wildcard_deny']);
		$this -> setTable('pages');
		$deny_pages_tmp = $this -> fetch(array('Path'), null, "WHERE `Path` <> ''");
		foreach ($deny_pages_tmp as $deny_page)
		{
			$wildcard_deny[] = $deny_page['Path'];
		}
		unset($deny_pages_tmp);

		preg_match('/[\W]+/', $location, $matches);

		$this -> rlValid -> sql( $location );
		if ( empty($location) || !empty($matches) )
		{
			$error = $lang['personal_address_error'];
		}
		/* check for uniqueness */
		else if ( in_array($location, $wildcard_deny) || $this -> getOne('ID', "`Own_address` = '{$location}'", 'accounts') )
		{
			$error = $lang['personal_address_in_use'];
		}

		$GLOBALS['rlHook'] -> load('phpAjaxCheckLocation', $location, $wildcard_deny, $error);

		if ( $error )
		{
			$_response -> script("
				if ( !$('input[name=\"profile[location]\"]').hasClass('error') )
				{
					printMessage('error', '". $error ."');
					$('input[name=\"profile[location]\"]').addClass('error').parent().next().remove();
					$('input[name=\"profile[location]\"]').parent().after('<span class=\"fail_field\">&nbsp;</span>');
				}
			");
		}
		else
		{
			$_response -> script("
				$('div.error div.close').trigger('click');
				$('input[name=\"profile[location]\"]').removeClass('error').parent().next().remove();
				$('input[name=\"profile[location]\"]').parent().after('<span class=\"success_field\">&nbsp;</span>');
			");
		}

		return $_response;
	}

	/**
	* validate "on the fly" fields
	*
	* @package xAjax
	*
	* @param string $username - requested username
	* @param string $email - requested e-mail
	* @param int $location - personal address value
	*
	**/
	function ajaxValidateProfile( $username = false, $email = false, $location = false, $check_location = false )
	{
		global $_response, $lang, $config;

		if ( $username )
		{
			$this -> rlValid -> sql( $username );
			$exist = (bool)$this -> getOne('ID', "`Username` = '{$username}'", 'accounts');
			$message = str_replace('{username}', $username, $lang['notice_account_exist']);

			$GLOBALS['rlHook'] -> load('phpAjaxValidateProfileUsername', $username, $message, $exist); // from v4.0.2

			if ( $exist )
			{
				$errors .= "<li>{$message}</li>";
				$response .= "
					if ( !$('input[name=\"profile[username]\"]').hasClass('error') )
					{
						$('input[name=\"profile[username]\"]').addClass('error').next().remove();
						$('input[name=\"profile[username]\"]').after('<span class=\"fail_field\">&nbsp;</span>');
					}
				";
			}
			else
			{
				$response .="
					$('input[name=\"profile[username]\"]').removeClass('error').next().remove();
					$('input[name=\"profile[username]\"]').after('<span class=\"success_field\">&nbsp;</span>');
				";
			}
		}

		if ( $email )
		{
			$this -> rlValid -> sql( $email );
			$exist = (bool)$this -> getOne('ID', "`Mail` = '{$email}'", 'accounts');
			$message = str_replace('{email}', $email, $lang['notice_account_email_exist']);

			$GLOBALS['rlHook'] -> load('phpAjaxValidateProfileEmail', $email, $message, $exist); // from v4.0.2

			if ( $exist )
			{
				$errors .= "<li>{$message}</li>";
				$response .= "
					if ( !$('input[name=\"profile[mail]\"]').hasClass('error') )
					{
						$('input[name=\"profile[mail]\"]').addClass('error').next().remove();
						$('input[name=\"profile[mail]\"]').after('<span class=\"fail_field\">&nbsp;</span>');
					}
				";
			}
			else
			{
				$response .= "
					$('input[name=\"profile[mail]\"]').removeClass('error').next().remove();
					$('input[name=\"profile[mail]\"]').after('<span class=\"success_field\">&nbsp;</span>');
				";
			}
		}

		if ( $location && $check_location)
		{
			/* validate */
			$location = trim($location);
			$wildcard_deny = explode(',', $config['account_wildcard_deny']);
			$this -> setTable('pages');
			$deny_pages_tmp = $this -> fetch(array('Path'), null, "WHERE `Path` <> ''");
			foreach ($deny_pages_tmp as $deny_page)
			{
				$wildcard_deny[] = $deny_page['Path'];
			}
			unset($deny_pages_tmp);

			$this -> rlValid -> sql( $location );
			preg_match('/[\W]+/', str_replace(array('-', '_'), '', $location), $matches);

			if ( empty($location) || !empty($matches) )
			{
				$errors .= '<li>'. $lang['personal_address_error'] .'</li>';
				$error = true;
			}
			/* check for uniqueness */
			else if ( in_array($location, $wildcard_deny) || $this -> getOne('ID', "`Own_address` = '{$location}'", 'accounts') )
			{
				$errors .= '<li>'. $lang['personal_address_in_use'] .'</li>';
				$error = true;
			}

			$GLOBALS['rlHook'] -> load('phpAjaxValidateProfileLocation', $location, $wildcard_deny, $error); // from v4.0.2

			if ( $error )
			{
				$response .= "
					if ( !$('input[name=\"profile[location]\"]').hasClass('error') )
					{
						$('input[name=\"profile[location]\"]').addClass('error').parent().next().remove();
						$('input[name=\"profile[location]\"]').parent().after('<span class=\"fail_field\">&nbsp;</span>');
					}
				";
			}
			else
			{
				$response .= "
					$('input[name=\"profile[location]\"]').removeClass('error').parent().next().remove();
					$('input[name=\"profile[location]\"]').parent().after('<span class=\"success_field\">&nbsp;</span>');
				";
			}
		}

		$GLOBALS['rlHook'] -> load('phpAjaxValidateProfile'); // from v4.0.2

		if ( $errors )
		{
			$errors = '<ul>'. $errors .'</ul>';
			$_response -> script("printMessage('error', '". $errors ."');");
			$_response -> script($response);
		}

		return $_response;
	}

	/**
	* check type fields exist
	*
	* @package xAjax
	*
	* @param int $type_id - requested account type id
	*
	**/
	function ajaxCheckTypeFields( $type_id = false )
	{
		global $_response, $lang;

		if ( !$type_id )
		{
			return $_response;
		}

		if ( $this -> getFields($type_id) )
		{
			$_response -> script("$('#step_account').fadeIn();");
		}
		else
		{
			$_response -> script("$('#step_account').fadeOut();");
		}

		return $_response;
	}

	/**
	* submit profile data
	*
	* @package xAjax
	*
	* @param string $username - username
	* @param string $password - password
	* @param string $email - email address
	* @param int $display_email - display email
	* @param string $type - account type id
	* @param string $location - personal address/location
	*
	**/
	function ajaxSubmitProfile( $username = false, $password = false, $email = false, $display_email = false, $type = false, $location = false, $security_code = false, $fields_loaded = false )
	{
		global $_response, $lang, $rlValid, $config, $rlCommon, $rlSmarty, $account_types;

		/* check username */
		$username = trim( $username );
		if ( $this -> rlCommon -> strLen( $username, '<', 3 ) )
		{
			$errors .= '<li>'.str_replace( '{field}', '<span class="field_error">"'.$lang['username'].'"</span>', $lang['notice_reg_length'] ).'</li>';
			$fields = 'profile[username],';
		}
		else
		{
			$rlValid -> sql( $username );
			if ( $this -> getOne('ID', "`Username` = '{$username}'", 'accounts') )
			{
				$errors .= '<li>'.str_replace('{username}', $username, $lang['notice_account_exist']).'</li>';
				$fields = 'profile[username]';
			}
		}

		/* check password */
		if ( $this -> rlCommon -> strLen( $password, '<', 3 ) )
		{
			$errors .= '<li>'.str_replace( '{field}', '<span class="field_error">"'.$lang['password'].'"</span>', $lang['notice_reg_length'] ).'</li>';
			$fields .= 'profile[password],';
		}
		$rlValid -> sql( $password );

		/* check email */
		if ( !$rlValid -> isEmail( $email ) )
		{
			$errors .= '<li>'.$lang['notice_bad_email'].'</li>';
			$fields .= 'profile[mail],';
		}
		$rlValid -> sql( $email );

		if ( $this -> getOne('ID', "`Mail` = '{$email}'", 'accounts') )
		{
			$errors .= '<li>'. str_replace('{email}', $email, $lang['notice_account_email_exist']) .'</li>';
			$fields .= 'profile[mail],';
		}

		/* check type */
		if ( !$type )
		{
			$errors .= '<li>'.str_replace( '{field}', '<span class="field_error">"'.$lang['password'].'"</span>', $lang['notice_choose_account_type'] ).'</li>';
			$fields .= 'profile[type],';
		}
		$rlValid -> sql( $type );

		/* check personal address */
		if ( $account_types[$type]['Own_location'] )
		{
			/* validate */
			$location = trim($location);

			preg_match('/[\W]+/', str_replace(array('-', '_'), '', $location), $matches);

			if ( empty($location) || !empty($matches) )
			{
				$errors .= '<li>'. $lang['personal_address_error'] .'</li>';
				$fields .= 'profile[location],';
			}

			/* check for uniqueness */
			$wildcard_deny = explode(',', $config['account_wildcard_deny']);
			$this -> setTable('pages');
			$deny_pages_tmp = $this -> fetch(array('Path'), null, "WHERE `Path` <> ''");
			foreach ($deny_pages_tmp as $deny_page)
			{
				$wildcard_deny[] = $deny_page['Path'];
			}
			unset($deny_pages_tmp);

			$rlValid -> sql( $location );
			if ( in_array($location, $wildcard_deny) || $this -> getOne('ID', "`Own_address` = '{$location}'", 'accounts') )
			{
				$errors .= '<li>'. $lang['personal_address_in_use'] .'</li>';
				$fields .= 'profile[location],';
			}
		}

		/* check security image code */
		if ( $config['security_img_registration'] )
		{
			if ( $security_code != $_SESSION['ses_security_code'] || empty($_SESSION['ses_security_code']) )
			{
				$errors .= '<li>'. $lang['security_code_incorrect'] .'</li>';
				$fields .= '#security_code';
			}
		}

		$GLOBALS['rlHook'] -> load('phpSubmitProfileValidate', $username, $password, $type);

		if ( $errors )
		{
			$error_mes = '<ul>'.$errors.'</ul>';
			$_response -> script("printMessage('error', '{$error_mes}', '{$fields}')");
		}
		else
		{
			/* get fields by account type */
			$fields = $this -> getFields($type);

			if ( $fields )
			{
				if ( !(int)$fields_loaded )
				{
					$fields = $this -> getFields($type);
					$fields = $this -> rlLang -> replaceLangKeys( $fields, 'account_fields', array( 'name', 'default', 'description' ) );
					$fields = $rlCommon -> fieldValuesAdaptation($fields, 'account_fields');

					$rlSmarty -> assign_by_ref( 'fields', $fields );
					$tpl = 'blocks' . RL_DS . 'reg_account.tpl';

					$_response -> script("$('tr.tmp').remove();");
					$_response -> assign('account_table', 'innerHTML', $rlSmarty -> fetch( $tpl, null, null, false ));

					$_response -> script("
					 	flynax.qtip(true);
					 	flynax.phoneField();
					");
				}

				$_response -> script("
					$('div.error div.close').trigger('click');
					reg_account_submit = true;
					flynax.switchStep('account');
					reg_account_type = '{$type}';
					flynax.mlTabs();
				");
				
				if ( !(int)$fields_loaded )
				{
					$_response -> script("
						var run = '';
						$('.eval').each(function(){
							run += $(this).html();
						});
						
						eval(run);
					");
				}
				
				$_SESSION['registration_captcha_passed'] = true;
			}
			else
			{
				$_response -> script("
					reg_account_submit = true;
					$('form[name=account_reg_form]').submit();
				");
			}
		}

		$GLOBALS['rlHook'] -> load('phpSubmitProfileEnd');

		$_response -> script("$('#profile_submit').val('{$lang['next_step']}')");

		return $_response;
	}

	/**
	* confirm account by account ID
	*
	* @param int $id - account id
	* @param array $account - account information
	*
	**/
	function confirmAccount( $id = false, $account = false )
	{
		global $config, $lang, $rlMail, $pages;

		/* send confirmation email to account owner */
		$mail_tpl_key = $account['Admin_confirmation'] ? 'account_confirmed_pending' : 'account_confirmed_active';
		$mail_tpl = $rlMail -> getEmailTemplate($mail_tpl_key);

		$account_area_link = SEO_BASE;
		$account_area_link .= $config['mod_rewrite'] ? $pages['login'] .'.html' : '?page='. $pages['login'];
		$account_area_link = '<a href="'. $account_area_link .'">'. $lang['blocks+name+account_area'] .'</a>';

		$name = $account['First_name'] || $account['Last_name'] ? $account['First_name'] .' '. $account['Last_name'] : $account['Username'];

		$find = array(
			'{account_area}',
			'{username}',
			'{password}',
			'{name}'
		);

		$replace = array(
			$account_area_link,
			$account['Username'],
			$account['Password_tmp'],
			$name
		);
		$mail_tpl['body'] = str_replace( $find, $replace, $mail_tpl['body'] );
		$rlMail -> send( $mail_tpl, $account['Mail'] );

		$id = (int)$id;
		$status = $account['Admin_confirmation'] ? 'pending' : 'active';
		$sql = "UPDATE `" . RL_DBPREFIX . "accounts` SET `Status` = '{$status}', `Confirm_code` = '', `Password_tmp` = '' WHERE `ID` = '{$id}'";
		return  $this -> query( $sql );
	}

	/**
	* edit profile data
	*
	* @param array $profile - profile data
	*
	**/
	function editProfile( $profile = false, $id = false )
	{
		global $account_info;

		$account_id = $id ? (int)$id : (int)$account_info['ID'];

		$data = array(
			'fields' => array(
				'Mail' => $profile['mail'],
				'Display_email' => $profile['display_email'] ? 1 : 0,
			),
			'where' => array(
				'ID' => $account_id
			)
		);

		if ( defined('REALM') )
		{
			$data['fields']['Type'] = $profile['type'];
			$data['fields']['Status'] = $profile['status'];

			if ( $profile['password'] )
			{
				$data['fields']['Password'] = $profile['password'];
			}
		}

		if ( $profile['location'] )
		{
			$data['fields']['Own_address'] = trim($profile['location']);
			/* update session data */
			$account_info['Own_address'] = trim($profile['location']);
		}

		/* upload thumbnail */
		if ( $_FILES['thumbnail']['name'] && is_readable($_FILES['thumbnail']['tmp_name']) )
		{
			$thumbnail_name = 'account-thumbnail-'. $account_id .'-'. mt_rand();
			if ( $thumbnail_name = $this -> rlActions -> upload('thumbnail', $thumbnail_name, 'C', array(100, 100), false, false) )
			{
				$data['fields']['Photo'] = $thumbnail_name;

				$current_thumbnail = $this -> getOne('Photo', "`ID` = '{$account_id}'", 'accounts');
				unlink(RL_FILES . $current_thumbnail);
			}
		}

		$GLOBALS['rlHook'] -> load('phpEditProfileBeforeUpdate', $data); // from v4.0.2

		// update data
		$result = $this -> rlActions -> updateOne( $data, 'accounts' );

		return $result;
	}

	/**
	* edit account
	*
	* @param array $account - account data
	* @param array $fields - current account type fields
	* @param array $id - account id
	*
	**/
	function editAccount( $account_data = false, $fields = false, $id = false )
	{
		global $account_info, $rlCommon;

		if ( !$account_data || !$fields )
		{
			return true;
		}

		$account_id = $id ?(int) $id : (int)$account_info['ID'];

		foreach ( $fields as $fIndex => $fRow )
		{
			$sFields[$fIndex] = $fields[$fIndex]['Key'];
		}

		$update['where'] = array(
			'ID' => $account_id
		);

		foreach ( $account_data as $key => $value )
		{
			$poss = array_search( $key, $sFields );

			if ( $fields[$poss]['Map'] && $value[$key] )
			{
				$location[] = $rlCommon -> adaptValue($fields[$poss], $value);
			}

			switch ($fields[$poss]['Type']){

				case 'text':
					if ( $fields[$poss]['Multilingual'] && count($GLOBALS['languages']) > 1 )
					{
						$out = '';
						foreach ($GLOBALS['languages'] as $language)
						{
							$val = $account_data[$key][$language['Code']];
							if ( $val )
							{
								$out .= "{|{$language['Code']}|}". $val ."{|/{$language['Code']}|}";
							}
						}

						$update['fields'][$key] = $out;
					}
					else
					{
						$update['fields'][$key] = $account_data[$key];
					}
					
					break;
					
				case 'textarea':
					if ( $fields[$poss]['Condition'] == 'html' )
					{
						$html_fields[] = $fields[$poss]['Key'];
					}
					
					if ( $fields[$poss]['Multilingual'] && count($GLOBALS['languages']) > 1 )
					{
						$limit = (int)$fields[$poss]['Values'];
						
						$out = '';
						foreach ($GLOBALS['languages'] as $language)
						{
							$val = $account_data[$key][$language['Code']];
							if ( $limit && $fields[$poss]['Condition'] != 'html' )
							{
								$limit = (int)$fields[$poss]['Values'];
								if ( function_exists('mb_substr') && function_exists('mb_internal_encoding') )
								{
									mb_internal_encoding('UTF-8');
									$val = mb_substr($val, 0, $limit);
								}
								else
								{
									$val = substr($val, 0, $limit);
								}
							}
							
							if ( $val )
							{
								$out .= "{|{$language['Code']}|}". $val ."{|/{$language['Code']}|}";
							}
						}
						$update['fields'][$key] = $out;
					}
					else
					{
						if ( $fields[$poss]['Values'] )
						{
							$limit = (int)$fields[$poss]['Values'];
							
							if ( $limit && $fields[$poss]['Condition'] != 'html' )
							{
								if ( function_exists('mb_substr') && function_exists('mb_internal_encoding') )
								{
									mb_internal_encoding('UTF-8');
									$account_data[$key] = mb_substr($account_data[$key], 0, $limit);
								}
								else
								{
									$account_data[$key] = substr($account_data[$key], 0, $limit);
								}
							}
						}
						$update['fields'][$key] = $account_data[$key];
					}
				
					break;
				
				case 'select':
				case 'bool':
				case 'radio':
					$update['fields'][$key] = $account_data[$key];

					break;
					
				case 'number':
					$update['fields'][$key] = preg_replace('/[^\d]/', '', $account_data[$key]);
					
					break;
					
				case 'phone':
					$out = '';
					
					/* code */
					if ( $fields[$poss]['Opt1'] )
					{
						$code = (int)substr($account_data[$key]['code'], 0, $fields[$poss]['Default']);
						$out = 'c:'. $code .'|';
					}
					
					/* area */
					$area = (int)$account_data[$key]['area'];
					$out .= 'a:'. $area . '|';
					
					/* number */
					$number = (int)substr($account_data[$key]['number'], 0, $fields[$poss]['Values']);
					$out .= 'n:'. $number;
					
					/* extension */
					if ( $fields[$poss]['Opt2'] )
					{
						$ext = (int)$account_data[$key]['ext'];
						$out .= '|e:'. $ext;
					}
					
					$update['fields'][$key] = $out;
				
					break;

				case 'date':
					if ( $fields[$poss]['Default'] == 'single' )
					{
						$update['fields'][$key] = $account_data[$key];
					}
					elseif ($fields[$poss]['Default'] == 'multi')
					{
						$update['fields'][$key] = $account_data[$key]['from'];

						/* save multi data (to date in this case) */
						$multi['where'] = array(
							'ID' => $id
						);

						$multi['fields'] = array( $key.'_multi' => $account_data[$key]['to'] );

						$GLOBALS['rlHook'] -> load('phpEditAccountBeforeUpdateDateMulti', $multi, $account_data); // from v4.0.2

						$this -> rlActions -> updateOne( $multi, 'accounts' );
					}

					break;

				case 'mixed':
						$update['fields'][$key] = $account_data[$key]['value'].'|'.$account_data[$key]['df'];
					break;

				case 'checkbox';

					unset($account_data[$key][0]);

					$chValues = null;

					foreach ($account_data[$key] as $chRow)
					{
						$chValues .= $chRow.",";
					}
					$chValues = substr( $chValues, 0, -1);

					$update['fields'][$key] = $chValues;

					break;

				case 'image':

					$file_name = 'account_' .  $id . '_' . $key . '_' . time();
					$resize_type = $fields[$poss]['Default'];
					$resolution = strtoupper($resize_type) == 'C' ? explode('|', $fields[$poss]['Values']) : $fields[$poss]['Values'];

					$file_name = $this -> rlActions -> upload( $key, $file_name, $resize_type, $resolution, false, false );
					if ( $file_name )
					{
						$update['fields'][$key] = $file_name;

						/* unlink old image */
						$image_name = $this -> getOne($fields[$poss]['Key'], "`ID` = '{$account_id}'", 'accounts');
						unlink(RL_FILES . $image_name);
					}

					break;

				case 'file':

					$file_name = 'account_' .  $id . '_' . $key . '_' . time();
					$resize_type = $fields[$poss]['Default'];
					$resolution = strtoupper($resize_type) == 'C' ? explode('|', $fields[$poss]['Values']) : $fields[$poss]['Values'];

					$file_name = $this -> rlActions -> upload( $key, $file_name, false, false, false, false );
					if ( $file_name )
					{
						$update['fields'][$key] = $file_name;

						/* unlink old image */
						$image_name = $this -> getOne($fields[$poss]['Key'], "`ID` = '{$account_id}'", 'accounts');
						unlink(RL_FILES . $image_name);
					}

					break;
			}
		}

		/* get coordinates by address request */
		if ( $location )
		{
			$address = implode(', ', $location);
			$address = urlencode($address);
			$content = $this -> getPageContent("http://maps.googleapis.com/maps/api/geocode/json?address={$address}&sensor=false");

			$this -> loadClass('Json');
			$content = $GLOBALS['rlJson'] -> decode($content);
			if ( strtolower($content -> status) == 'ok' )
			{
				$update['fields']['Loc_address'] = $content -> results[0] -> formatted_address;
				$update['fields']['Loc_latitude'] = $content -> results[0] -> geometry -> location -> lat;
				$update['fields']['Loc_longitude'] = $content -> results[0] -> geometry -> location -> lng;
			}
		}

		$GLOBALS['rlHook'] -> load('phpEditAccountBeforeUpdate', $update, $content); // from v4.0.2

		/* save new data */
		$result = $this -> rlActions -> updateOne( $update, 'accounts' );
		
		if ( !define('REALM') && REALM != 'admin' )
		{
			/* update session data */
			$sql = "SELECT `T1`.*, `T2`.`Abilities`, `T2`.`ID` AS `Type_ID`, `T2`.`Own_location` ";
			$sql .= "FROM `". RL_DBPREFIX ."accounts` AS `T1` ";
			$sql .= "LEFT JOIN `". RL_DBPREFIX ."account_types` AS `T2` ON `T1`.`Type` = `T2`.`Key` ";
			$sql .= "WHERE `T1`.`ID` = '{$account_id}' AND `T1`.`Status` <> 'trash'";
			$account = $this -> getRow($sql);
			
			unset($account['Confirm_code']);
			$account['Password'] = md5($account['Password']);
			$account['Full_name'] = $account['First_name'] || $account['Last_name'] ? $account['First_name'] .' '. $account['Last_name'] : $account['Username'];
			
			$_SESSION['account'] = $account;
		}

		return $result;
	}

	/**
	* account Login
	*
	* @param string $username - account username
	* @param string $password - account password
	* @param bool $direct 	  - allow login by MD5 password
	*
	* @return bool - true or errors (array)
	**/
	function login( $username = false, $password = false, $direct = false )
	{
		global $sql, $config, $reefless, $lang; // $sql depricated on v4.1.0

		/* empty data return */
		if ( empty($username) || empty($password) )
		{
			$errors[] = $GLOBALS['lang']['notice_incorrect_auth'];
			return $errors;
		}
		
		/* login attempts control - error and exit */
		if ( $reefless -> attemptsLeft <= 0 && $config['security_login_attempt_user_module'] )
		{
			$errors[] = str_replace('{period}', '<b>'.$config['security_login_attempt_user_period'].'</b>', $lang['login_attempt_error']);
			return $errors;
		}
		
		$this -> rlValid -> sql( $username );
		$this -> rlValid -> sql( $password );

		$errors = array();

		// get account information
		$sql = "SELECT `T1`.*, `T2`.`Abilities`, `T2`.`ID` AS `Type_ID`, `T2`.`Own_location` ";
		$sql .= "FROM `". RL_DBPREFIX ."accounts` AS `T1` ";
		$sql .= "LEFT JOIN `". RL_DBPREFIX ."account_types` AS `T2` ON `T1`.`Type` = `T2`.`Key` ";
		$sql .= "WHERE `T1`.`Username` = '{$username}' AND `T1`.`Status` <> 'trash'";

		$GLOBALS['rlHook'] -> load('loginSql', $sql);

		$account = $this -> getRow($sql);

		/* login attempts control - save attempts */
		if ( $config['security_login_attempt_user_module'] )
		{
			$insert = array(
				'IP' => $_SERVER['REMOTE_ADDR'],
				'Date' => 'NOW()',
				'Status' => $account ? 'success' : 'fail',
				'Interface' => 'user',
				'Username' => $username
			);
			
			$this -> loadClass('Actions');
			$GLOBALS['rlActions'] -> insertOne($insert, 'login_attempts');
		}
		
		/* check login request */
		if ( !empty( $account ) )
		{
			$request_password = $direct ? $password : md5( $password );

			$GLOBALS['rlHook'] -> load('phpLoginValidation', $request_password, $account, $password, $direct); // from v4.0.2

			if ( $account['Password'] == $request_password && $account['Status'] == 'active' )
			{
				//no errors, good!
			}
			elseif ( $account['Password'] == $request_password && $account['Status'] != 'active' )
			{
				$errors[] = $GLOBALS['lang']['notice_account_approval'];
				$this -> messageType = 'alert';
			}
			else 
			{
				$errors[] = $GLOBALS['lang']['notice_incorrect_auth'];
			}
		}
		else 
		{
			$errors[] = $GLOBALS['lang']['notice_incorrect_auth'];
		}

		if ( empty($errors) )
		{
			/* check abilities */
			$abilities = explode(',', $account['Abilities']);
			$abilities = empty($abilities[0]) ? false : $abilities;

			/* do not use this data in future please */
			$_SESSION['id'] = $account['ID'];
			$_SESSION['username'] = $account['Username'];
			$_SESSION['password'] = md5( $account['Password'] );
			$_SESSION['type'] = $account['Type'];
			$_SESSION['type_id'] = $account['Type_ID'];
			$_SESSION['abilities'] = $abilities;

			unset($account['Confirm_code']);
			$account['Password'] = md5($account['Password']);
			$account['Full_name'] = $account['First_name'] || $account['Last_name'] ? $account['First_name'] .' '. $account['Last_name'] : $account['Username'];

			/* use this only */
			$account['Abilities'] = $abilities;
			$_SESSION['account'] = $account;

			$GLOBALS['rlHook'] -> load('phpLoginSaveSessionData', $username, $password); // from v4.0.2

			return true;
		}
		else 
		{
			return $errors;
		}
	}

	/**
	* is account Loged in
	*
	* @return bool
	**/
	function isLogin()
	{
		$username = $this -> rlValid -> xSql( $_SESSION['account']['Username'] );
		$password = $this -> rlValid -> xSql( $_SESSION['account']['Password'] );
		
		if ( !$username || !$password )
			return false;

		// get account information
		$account_password = $this -> getOne('Password', "`Username` = '{$username}' AND `Status` = 'active'", 'accounts');
		$success = (bool)($account_password && md5($account_password) == $password);

		$GLOBALS['rlHook'] -> load('phpIsLogin', $account_password);

		if ( $success )
		{
			return true;
		}

		if ( $_SESSION['account'] )
		{
			unset($_SESSION['account']);
		}

		return false;
	}

	/**
	* account logout
	*
	* @todo session destroy and redirect
	**/
	function logOut()
	{
		global $pages;

		session_destroy();
		session_regenerate_id();

		$GLOBALS['rlHook'] -> load('phpLogOut');

		$location = $this -> rlConfig -> getConfig('mod_rewrite') ? SEO_BASE . $pages['login'] . '.html?logout' : SEO_BASE . '?page=' . $pages['login'] . '&logout' ;
		$this -> redirect( null, $location );
	}

	/**
	* delete profile thumbnail
	*
	* @package xAjax
	*
	* @param int $id - account id
	*
	**/
	function ajaxDelProfileThumbnail( $id = false )
	{
		global $_response, $account_info;

		if ( defined( 'IS_LOGIN' ) )
		{
			$account_id = defined('REALM') && REALM == 'admin' ? $id : $account_info['ID'];
			if ( !$account_id )
				return $_response;

			$thumbnail = $this -> getOne('Photo', "`ID` = '{$account_id}'", 'accounts');

			$update = array(
				'fields' => array('Photo' => ''),
				'where' => array('ID' => $account_id)
			);

			$GLOBALS['rlHook'] -> load('phpAjaxDelProfileThumbnailBeforeUpdate', $update); // from v4.0.2

			$this -> rlActions -> updateOne($update, 'accounts');
			unlink(RL_FILES . $thumbnail);

			$_response -> script("
				$('div.canvas').find('img.preview').removeClass('thumbnail').attr('src', '". RL_TPL_BASE ."img/no-account.png').show();
				$('div.canvas').find('canvas').remove();
				$('div.canvas').removeClass('active');
				$('div.canvas').find('img.delete').removeClass('ajax');
			");
		}


		return $_response;
	}

	/**
	* delete file
	*
	* @package xAjax
	*
	* @param string $key - field name
	* @param string $account_id - account ID
	* @param string $dom - dom element
	*
	**/
	function ajaxDelAccountFile( $key = false, $account_id = false, $dom = false )
	{
		global $_response, $account_info;

		$account_id = defined('REALM') && REALM == 'admin' ? $account_id : $account_info['ID'];

		if ( !$account_id || !$key )
			return;

		if ( $file = $this -> getOne($key, "`ID` = '{$account_id}'", 'accounts') )
		{
			$GLOBALS['rlHook'] -> load('phpAjaxDelAccountFileBeforeUpdate'); // from v4.0.2

			$this -> query("UPDATE `". RL_DBPREFIX ."accounts` SET `{$key}` = '' WHERE `ID` = '{$account_id}' LIMIT 1");
			unlink(RL_FILES . $file);

			$_response -> script("
				$('#{$dom}').slideUp();
				$('#{$dom}').next().fadeIn();
			");
		}

		return $_response;
	}

	/**
	* change account password
	*
	* @package xAjax
	*
	* @param string $current - current password
	* @param string $new     - new password
	* @param string $repeat  - new password repeat
	*
	**/
	function ajaxChangePass( $current, $new, $repeat )
	{
		global $_response, $new_password, $lang, $account_info;

		$new_password = $new;
		$account_id = $_SESSION['id'];

		$errors = array();

		if ( defined( 'IS_LOGIN' ) )
		{
			$current = $this -> rlValid -> xSql( $current );
			$new = $this -> rlValid -> xSql( $new );
			$repeat = $this -> rlValid -> xSql( $repeat );

			// check current password
			$check_current = $this -> fetch( array('Password'), array( 'ID' => $account_id, 'Status' => 'active' ), null, null, 'accounts', 'row' );

			if ( $check_current['Password'] != md5($current) )
			{
				$errors[] = $GLOBALS['lang']['notice_incorrect_current_pass'];
			}

			// check passwords length
			if ( strlen($new) < 3 )
			{
				$errors[] = str_replace( '{field}', '<span class="field_error">"'.$GLOBALS['lang']['new_password'].'"</span>', $GLOBALS['lang']['notice_reg_length'] );
			}

			if ( strlen($repeat) < 3 )
			{
				$errors[] = str_replace( '{field}', '<span class="field_error">"'.$GLOBALS['lang']['password_repeat'].'"</span>', $GLOBALS['lang']['notice_reg_length'] );
			}

			// check passwords mutch
			if ( $repeat != $new )
			{
				$errors[] = $GLOBALS['lang']['notice_pass_bad'];
			}

			$GLOBALS['rlHook'] -> load('phpAjaxChangePassCheckErrors', $new, $errors); // from v4.0.2

			if ( empty($errors) )
			{
				$_response -> script( "$('#change_password').val('{$lang['change']}')" );

				$GLOBALS['rlHook'] -> load('phpAjaxChangePassBeforeUpdate', $new); // from v4.0.2

				$update = $this -> query( "UPDATE `" . RL_DBPREFIX . "accounts` SET `Password` = MD5('{$new}') WHERE `ID` = '{$account_id}' AND `Password` = MD5('{$current}') LIMIT 1" );
				if ( $update )
				{
					$account_info['Password'] = $_SESSION['password'] = md5(md5($new));

					$GLOBALS['rlHook'] -> load('accountChangePassword');

					$_response -> script( "$('#current_password, #new_password, #password_repeat').attr('value', '');" );
					$_response -> script( "printMessage('notice', '{$GLOBALS['lang']['changes_saved']}');" );
				}
			}
			else 
			{
				$error_content = '<ul>';
				foreach ($errors as $error)
				{
					$error_content .= "<li>" . $error . "</li>";
				}
				$error_content .= '</ul>';

				$_response -> script( "printMessage('error', '{$error_content}');" );
			}
		}
		$_response -> script( "$('#change_password').val('{$lang['change']}')" );

		return $_response;
	}

	/**
	* get dealers by char
	*
	* @param string $char - start character
	* @param int $per_page - items per page
	* @param int $page - current page
	* @param array $type_info - account type info
	*
	* @return array of dealers accounts
	**/
	function getDealersByChar($char = false, $per_page = false, $page = false, $type_info = false)
	{
		global $config, $alphabet, $rlMobile, $pages;

		$start = $page > 1 ? ($page - 1) * $per_page : 0;

		/* check custom alphabetic field exist */
		if ( $config['alphabetic_field'] && !$this -> getOne('ID', "`Key` = '{$config['alphabetic_field']}' AND `Status` = 'active'", 'account_fields') )
		{
			$config['alphabetic_field'] = false;
		}

		$char = $char == '0-9' ? '[0-9]' : $char;
		$char = 0 === array_search($char, $alphabet) ? false : $char;
		$this -> rlValid -> sql($char);

		$sql = "SELECT SQL_CALC_FOUND_ROWS `T1`.*, ";
		$sql .= "(";
		$sql .= "SELECT COUNT(`T3`.`ID`) ";
		$sql .= "FROM `". RL_DBPREFIX ."listings` AS `T3` ";
		$sql .= "LEFT JOIN `". RL_DBPREFIX ."listing_plans` AS `T4` ON `T3`.`Plan_ID` = `T4`.`ID` ";
		$sql .= "WHERE `T3`.`Account_ID` = `T1`.`ID` AND (UNIX_TIMESTAMP(DATE_ADD(`T3`.`Pay_date`, INTERVAL `T4`.`Listing_period` DAY)) > UNIX_TIMESTAMP(NOW()) OR `T4`.`Listing_period` = 0) AND `T3`.`Status` = 'active'";
		$sql .= " ) ";
		$sql .= "AS `Listings_count` ";
		$sql .= "FROM `". RL_DBPREFIX ."accounts` AS `T1` ";
		$sql .= "LEFT JOIN `". RL_DBPREFIX ."account_types` AS `T2` ON `T1`.`Type` = `T2`.`Key` ";
		$sql .= "WHERE ";
		if ( $char )
		{
			$sql .= $config['alphabetic_field'] ? "`T1`.`{$config['alphabetic_field']}` REGEXP '^{$char}+' " : "(`T1`.`First_name` REGEXP '^{$char}+' OR `T1`.`Last_name` REGEXP '^{$char}+') ";
		}
		else
		{
			$sql .= "1 ";
		}
		$sql .= "AND `T2`.`Key` = '{$type_info['Key']}' AND `T1`.`Status` = 'active' AND `T2`.`Status` = 'active' ";
		$sql .= "LIMIT {$start}, {$per_page}";
		$dealers = $this -> getAll($sql);

		$calc = $this -> getRow( "SELECT FOUND_ROWS() AS `calc`" );
		$this -> calc_alphabet = $calc['calc'];

		/* get domain name */
		$domain = $this -> rlValid -> getDomain(RL_URL_HOME, true);

		if ( RL_LANG_CODE != $config['lang'] )
		{
			$domain = $domain . '/' . RL_LANG_CODE;
		}

		/* get short form fields */
		$fields = $this -> getFormFields( $type_info['ID'] );

		foreach ( $dealers as $key => $value )
		{
			foreach ( $fields as $fKey => $fValue )
			{
				if ( $field['Condition'] == 'isUrl' || $field['Condition'] == 'isEmail' )
				{
					$fields[$fKey]['value'] = $dealers[$key][$item];
				}
				else
				{
					$fields[$fKey]['value'] = $GLOBALS['rlCommon'] -> adaptValue( $fValue, $value[$fKey], 'account', $value['ID'] );
				}
			}

			$dealers[$key]['Full_name'] = $value['First_name'] || $value['Last_name'] ? trim($value['First_name'] .' '. $value['Last_name']) : $value['Username'];
			$dealers[$key]['fields'] = $fields;

			/* generate personal address */
			if ( $type_info['Page'] )
			{
				if ( $type_info['Own_location'] && $value['Own_address'] && $config['mod_rewrite'] )
				{
					if ( $config['account_wildcard'] )
					{
						$tmp_seo_base = $rlMobile -> isMobile ? str_replace(RL_URL_HOME, rtrim(RL_MOBILE_URL, 'index.php'), SEO_BASE) : str_replace('www.', '', SEO_BASE);
						$dealers[$key]['Personal_address'] = 'http://'. $value['Own_address'] .'.'. str_replace(array('http://', 'https://'), '', $tmp_seo_base);
					}
					else
					{
						$dealers[$key]['Personal_address'] = SEO_BASE . $value['Own_address'] .'/';
						
						if ( $rlMobile -> isMobile )
						{
							$dealers[$key]['Personal_address'] = str_replace(RL_URL_HOME, rtrim(RL_MOBILE_URL, 'index.php'), $dealers[$key]['Personal_address']);
						}
					}
				}
				else
				{
					$page_key = 'at_'. $type_info['Key'];

					$dealers[$key]['Personal_address'] = SEO_BASE;
					$dealers[$key]['Personal_address'] .= $config['mod_rewrite'] ? $pages[$page_key] .'/'. $value['Own_address'] .'.html': '?page='. $pages[$page_key] .'&amp;id='. $value['ID'];
					
					if ( $rlMobile -> isMobile )
					{
						$dealers[$key]['Personal_address'] = str_replace(RL_URL_HOME, rtrim(RL_MOBILE_URL, 'index.php'), $dealers[$key]['Personal_address']);
					}
				}
			}
		}

		return $dealers;
	}

	/**
	* search dealers
	*
	* @param array $data - search data
	* @param array $form - search form
	* @param int $per_page - items per page
	* @param int $page - current page
	* @param array $type_info - account type info
	*
	* @return array of dealers accounts
	**/
	function searchDealers( $data = false, $form = false, $per_page = 10, $page = false, $type_info = false )
	{
		global $config, $pages, $rlMobile;

		if ( !$data || !$form || !$type_info )
			return false;

		$start = $page > 1 ? ($page - 1) * $per_page : 0;

		$sql = "SELECT SQL_CALC_FOUND_ROWS `T1`.*, ";
		$sql .= "(";
		$sql .= "SELECT COUNT(`T3`.`ID`) ";
		$sql .= "FROM `". RL_DBPREFIX ."listings` AS `T3` ";
		$sql .= "LEFT JOIN `". RL_DBPREFIX ."listing_plans` AS `T4` ON `T3`.`Plan_ID` = `T4`.`ID` ";
		$sql .= "WHERE `T3`.`Account_ID` = `T1`.`ID` AND (UNIX_TIMESTAMP(DATE_ADD(`T3`.`Pay_date`, INTERVAL `T4`.`Listing_period` DAY)) > UNIX_TIMESTAMP(NOW()) OR `T4`.`Listing_period` = 0) AND `T3`.`Status` = 'active'";
		$sql .= " ) ";
		$sql .= "AS `Listings_count` ";
		$sql .= "FROM `". RL_DBPREFIX ."accounts` AS `T1` ";
		$sql .= "LEFT JOIN `". RL_DBPREFIX ."account_types` AS `T2` ON `T1`.`Type` = `T2`.`Key` ";
		$sql .= "WHERE `T2`.`Key` = '{$type_info['Key']}' AND `T1`.`Status` = 'active' AND `T2`.`Status` = 'active' ";

		foreach ($form as $key => $field)
		{
			$fKey = $field['Key'];
			$f = $this -> rlValid -> xSql($data[$fKey]);

			if( !empty($f) )
			{
				switch ($field['Type']){
					case 'mixed':
						if ( $f['df'] )
						{
							$sql .= "AND LOCATE('{$f['df']}', `T1`.`". $fKey ."`) > 0 ";
						}
					case 'price':
						if ( $f['currency'] )
						{
							$sql .= "AND LOCATE('{$f['currency']}', `T1`.`". $fKey ."`) > 0 ";
						}
					case 'unit':
						if ( $f['unit'] )
						{
							$sql .= "AND LOCATE('{$f['unit']}', `T1`.`". $fKey."`) > 0 ";
						}
					case 'number':
						if ( false !== strpos($field['Key'], 'zip') && $GLOBALS['aHooks']['search_by_distance'] )
						{
							if ( $f['distance'] && $f['zip'] )
							{
								$zip_code = $f['zip'];
								$miles = $f['distance'];

								$zip_info = $this ->getRow("SELECT `Latitude`, `Longitude` FROM `" . RL_DBPREFIX . "zip_code` WHERE `Zip` = '{$zip_code}'");

								if ($zip_info['Latitude'] && $zip_info['Longitude'])
								{
									$sql .= "AND (3956 * 2 * ASIN(SQRT( POWER(SIN(({$zip_info['Latitude']} - `ZIP`.`Latitude`) * 0.0174532925 / 2), 2) + 	COS({$zip_info['Latitude']} * 0.0174532925) * COS(`ZIP`.`Latitude` * 0.0174532925) * POWER(SIN(({$zip_info['Longitude']} - `ZIP`.`Longitude`) * 0.0174532925 / 2), 2))) <= {$miles}) ";
								}
							}
						}
						else
						{
							if( (int)$f['from'] )
							{
								$sql .= "AND ROUND(`T1`.`{$fKey}`) >= '".(int)$f['from']."' ";
							}
							if( (int)$f['to'] )
							{
								$sql .= "AND ROUND(`T1`.`{$fKey}`) <= '".(int)$f['to']."' ";
							}
						}
						break;

					case 'text':
						if ( is_numeric($f) )
						{
							$sql .= "AND `T1`.`{$fKey}` LIKE '%". $f ."%' ";
						}
						else
						{
							$sql .= "AND (MATCH (`T1`.`{$fKey}`) AGAINST('". $f ."' IN BOOLEAN MODE)) ";
						}
						break;

					case 'date':
						if ( $field['Default'] == 'single')
						{
							if( $f['from'] )
							{
								$sql .= "AND UNIX_TIMESTAMP(`T1`.`{$fKey}`) >= UNIX_TIMESTAMP('". $f['from'] ."') ";
							}
							if( $f['to'] )
							{
								$sql .= "AND UNIX_TIMESTAMP(`T1`.`{$fKey}`) <= UNIX_TIMESTAMP('". $f['to'] ."') ";
							}
						}
						elseif ( $field['Default'] == 'multi')
						{
							$sql .= "AND UNIX_TIMESTAMP(`T1`.`{$fKey}`) <= UNIX_TIMESTAMP('". $f ."') ";
							$sql .= "AND UNIX_TIMESTAMP(`T1`.`{$fKey}_multi`) >= UNIX_TIMESTAMP('". $f ."') ";
						}
						break;

					case 'select':
						if ( $field['Condition'] == 'years' )
						{
							if( $f['from'] )
							{
								$sql .= "AND `T1`.`{$fKey}` >= '".(int)$f['from']."' ";
							}
							if( $f['to'] )
							{
								$sql .= "AND `T1`.`{$fKey}` <= '".(int)$f['to']."' ";
							}
						}
						else
						{
							$sql .= "AND `T1`.`{$fKey}` = '". $f ."' ";
						}
						break;

					case 'bool':
						if ( $f == 'on' )
						{
							$sql .= "AND `T1`.`{$fKey}` = '1' ";
						}
						else
						{
							$sql .= "AND `T1`.`{$fKey}` = '0' ";
						}
						break;

					case 'radio':
						$sql .= "AND `T1`.`{$fKey}` = '".$f."' ";
						break;

					case 'checkbox':
						unset($f[0]);
						if ( !empty($f) )
						{
							$sql .= "AND (";
							foreach ( $f as $fI => $fV )
							{
								$sql .= "FIND_IN_SET('". $f[$fI] ."', `T1`.`{$fKey}`) > 0 OR ";
							}
							$sql = substr( $sql, 0, -3 );
							$sql .= ") ";
						}
						break;
				}
			}
		}

		$sql .= "ORDER BY ";
		if ( $data['sort_by'] && $form[$data['sort_by']] )
		{
			switch ($form[$data['sort_by']]['Type']){
				case 'price':
				case 'unit':
				case 'mixed':
					$sql .= "ROUND(`T1`.`{$form[$data['sort_by']]['Key']}`) " . strtoupper($data['sort_type']) . " ";
					break;

				case 'select':
					if ( $form[$data['sort_by']]['Key'] == 'Category_ID' )
					{
						$sql .= "`T3`.`Key` " . strtoupper($data['sort_type']) . " ";
					}
					elseif ( $form[$data['sort_by']]['Key'] == 'Listing_type' )
					{
						$sql .= "`T3`.`Type` " . strtoupper($data['sort_type']) . " ";
					}
					else
					{
						$sql .= "`T1`.`{$form[$data['sort_by']]['Key']}` " . strtoupper($data['sort_type']) . " ";
					}
					break;

				default:
					$sql .= "`T1`.`{$form[$data['sort_by']]['Key']}` " . strtoupper($data['sort_type']) . " ";
					break;
			}
		}
		else
		{
			$sql .= "`Date` " . strtoupper($data['sort_type']) . " ";
		}

		$sql .= "LIMIT {$start}, {$per_page}";

		$dealers = $this -> getAll($sql);

		$calc = $this -> getRow( "SELECT FOUND_ROWS() AS `calc`" );
		$this -> calc = $calc['calc'];

		/* get domain name */
		$domain = $this -> rlValid -> getDomain(RL_URL_HOME, true);

		if ( RL_LANG_CODE != $config['lang'] )
		{
			$domain = $domain . '/' . RL_LANG_CODE;
		}

		/* get short form fields */
		$fields = $this -> getFormFields( $type_info['ID'] );

		foreach ( $dealers as $key => $value )
		{
			foreach ( $fields as $fKey => $fValue )
			{
				if ( $field['Condition'] == 'isUrl' || $field['Condition'] == 'isEmail' )
				{
					$fields[$fKey]['value'] = $dealers[$key][$item];
				}
				else
				{
					$fields[$fKey]['value'] = $GLOBALS['rlCommon'] -> adaptValue( $fValue, $value[$fKey], 'account', $value['ID'] );
				}
			}

			$dealers[$key]['Full_name'] = $value['First_name'] || $value['Last_name'] ? trim($value['First_name'] .' '. $value['Last_name']) : $value['Username'];
			$dealers[$key]['fields'] = $fields;

			/* generate personal address */
			if ( $type_info['Page'] )
			{
				if ( $type_info['Own_location'] && $value['Own_address'] && $config['mod_rewrite'] )
				{
					if ( $config['account_wildcard'] )
					{
						$tmp_seo_base = $rlMobile -> isMobile ? str_replace(RL_URL_HOME, rtrim(RL_MOBILE_URL, 'index.php'), SEO_BASE) : str_replace('www.', '', SEO_BASE);
						$dealers[$key]['Personal_address'] = 'http://'. $value['Own_address'] .'.'. str_replace(array('http://', 'https://'), '', $tmp_seo_base);
					}
					else
					{
						$dealers[$key]['Personal_address'] = SEO_BASE . $value['Own_address'] .'/';
						
						if ( $rlMobile -> isMobile )
						{
							$dealers[$key]['Personal_address'] = str_replace(RL_URL_HOME, rtrim(RL_MOBILE_URL, 'index.php'), $dealers[$key]['Personal_address']);
						}
					}
				}
				else
				{
					$page_key = 'at_'. $type_info['Key'];
					$dealers[$key]['Personal_address'] = SEO_BASE;
					$dealers[$key]['Personal_address'] .= $config['mod_rewrite'] ? $pages[$page_key] .'/'. $value['Own_address'] .'.html': '?page='. $pages[$page_key] .'&amp;id='. $value['ID'];
					
					if ( $rlMobile -> isMobile )
					{
						$dealers[$key]['Personal_address'] = str_replace(RL_URL_HOME, rtrim(RL_MOBILE_URL, 'index.php'), $dealers[$key]['Personal_address']);
					}
				}
			}
		}

		return $dealers;
	}

	/**
	* get account form fields
	*
	* @param int $id - account type ID
	* @param string $table - table
	*
	* @return categories fields list
	**/
	function getFormFields( $id = false, $table = 'account_short_form' )
	{
		$sql = "SELECT `T2`.`Key`, `T2`.`Type`, `T2`.`Default`, `T2`.`Condition`, CONCAT('account_fields+name+', `T2`.`Key`) AS `pName`, ";
		$sql .= "`T2`.`Details_page`, `T2`.`Multilingual`, `T2`.`Opt1`, `T2`.`Opt2` ";
		$sql .= "FROM `" . RL_DBPREFIX . $table . "` AS `T1` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "account_fields` AS `T2` ON `T1`.`Field_ID` = `T2`.`ID` ";
		$sql .= "WHERE `T1`.`Category_ID` = '{$id}' ORDER BY `T1`.`Position`";

		$fields = $this -> getAll( $sql );

		if ( $fields )
		{
			foreach ($fields as $value)
			{
				$tmp_fields[$value['Key']] = $value;
			}

			$fields = $tmp_fields;
			unset($tmp_fields);

			return $fields;
		}
		else
		{
			return false;
		}
	}

	/**
	* build search form
	*
	* @param string $id - account type ID
	*
	* @return array - form information
	**/
	function buildSearch( $id = false )
	{
		global $rlCommon, $rlLang;

		if ( !$id )
			return;

		$sql = "SELECT `T1`.`ID`, `T1`.`Key`, `T1`.`Type`, `T1`.`Default`, `T1`.`Values`, `Condition`, `Required`, `Map`, ";
		$sql .= "CONCAT('account_fields+name+', `T1`.`Key`) AS `pName` ";
		$sql .= "FROM `". RL_DBPREFIX ."account_fields` AS `T1` ";
		$sql .= "LEFT JOIN `". RL_DBPREFIX ."account_search_relations` AS `T2` ON `T1`.`ID` = `T2`.`Field_ID` ";
		$sql .= "WHERE `T2`.`Category_ID` = {$id} AND `T1`.`Status` = 'active' ORDER BY `T2`.`Position`";
		$fields = $this -> getAll($sql);

		$fields = $rlCommon -> fieldValuesAdaptation($fields, 'account_fields');
		$fields = $rlLang -> replaceLangKeys( $fields, 'account_fields', array( 'name', 'default' ) );

		foreach ($fields as $field)
		{
			$tmp[$field['Key']] = $field;
		}
		$fields = $tmp;
		unset($tmp);

		return $fields;
	}

	/**
	* get account type details
	*
	* @param string $key - account type key
	*
	* @return array - form information
	**/
	function getTypeDetails( $key = false )
	{
		global $rlLang;

		if ( !$key )
			return;

		$type = $this -> fetch(array('ID', 'Key', 'Abilities', 'Page', 'Own_location'), array('Key' => $key, 'Status' => 'active'), null, 1, 'account_types', 'row');
		$type = $rlLang -> replaceLangKeys( $type, 'account_types', array( 'name', 'description' ) );

		return $type;
	}

	/**
	* send "edit e-mail account" confirmation
	*
	* @return array - form information
	**/
	function sendEditEmailNotification( $account_id = false, $new_email = false )
	{
		global $pages, $rlDb, $reefless, $account_info, $config;

		if ( !$account_id )
		{
			return false;
		}

		$confirm_code = md5(mt_rand());

		/* save code */
		$save_code_sql = "UPDATE `". RL_DBPREFIX ."accounts` SET `Confirm_code` = '{$confirm_code}' WHERE `ID` = '{$account_info['ID']}' LIMIT 1";
		$rlDb -> query($save_code_sql);

		// create activation link
		$activation_link = SEO_BASE;
		$activation_link .= $config['mod_rewrite'] ? "{$pages['my_profile']}.html?key=" : "?page={$pages['my_profile']}&amp;key=" ;
		$activation_link .= $confirm_code;
		$activation_link = '<a href="'. $activation_link .'">'. $activation_link .'</a>';

		$reefless -> loadClass('Mail');

		$mail_tpl = $GLOBALS['rlMail'] -> getEmailTemplate( 'account_edit_email' );
		$mail_tpl['body'] = str_replace( array('{activation_link}', '{username}'), array($activation_link, $account_info['Full_name']), $mail_tpl['body'] );
		
		$GLOBALS['rlMail'] -> send( $mail_tpl, $new_email );
	}

	/**
	* delete account preparation
	*
	* @package ajax
	*
	* @param int $id - account id
	*
	**/
	function ajaxPrepareDeleting( $id = false )
	{
		global $_response, $rlSmarty, $rlHook, $delete_details, $lang, $delete_total_items, $config;

		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}

		/* get account details */
		$account_details = $this -> getProfile((int)$id);
		$rlSmarty -> assign_by_ref('account_details', $account_details);

		/* check listings */
		$listings = $this -> getRow("SELECT COUNT(`ID`) AS `Count` FROM `". RL_DBPREFIX ."listings` WHERE `Account_ID` = '{$id}' AND `Status` <> 'trash'");

		$delete_details[] = array(
			'name' => $lang['listings'],
			'items' => $listings['Count'],
			'link' => RL_URL_HOME . ADMIN . '/index.php?controller=listings&amp;username='. $account_details['Username']
		);
		$delete_total_items += $listings['Count'];

		/* check custom categories */
		$custom_categories = $this -> getRow("SELECT COUNT(`ID`) AS `Count` FROM `". RL_DBPREFIX ."tmp_categories` WHERE `Account_ID` = '{$id}'");
		$delete_details[] = array(
			'name' => $lang['admin_controllers+name+custom_categories'],
			'items' => $custom_categories['Count'],
			'link' => RL_URL_HOME . ADMIN . '/index.php?controller=custom_categories'
		);
		$delete_total_items += $custom_categories['Count'];

		$rlHook -> load('deleteAccountDataCollection');

		$rlSmarty -> assign_by_ref('delete_details', $delete_details);

		if ( $delete_total_items )
		{
			$tpl = 'blocks' . RL_DS . 'delete_preparing_account.tpl';
			$_response -> assign("delete_container", 'innerHTML', $GLOBALS['rlSmarty'] -> fetch( $tpl, null, null, false ));
			$_response -> script("
				$('input[name=new_account]').rlAutoComplete({add_id: true});
				$('#delete_block').slideDown();
			");
		}
		else
		{
			$phrase = $config['trash'] ? str_replace('{username}', $account_details['Username'], $lang['notice_drop_empty_account']) : str_replace('{username}', $account_details['Username'], $lang['notice_delete_empty_account']);
			$_response -> script("
				$('#delete_block').slideUp();
				rlPrompt('{$phrase}', 'xajax_deleteAccount', {$account_details['ID']});
			");
		}

		return $_response;
	}

	/**
	* mass delete accounts
	*
	* @package ajax
	*
	* @param int $id - account IDs
	* @param string $action - activate/approve
	*
	**/
	function ajaxMassActions( $ids = false, $action = false )
	{
		global $_response, $rlActions, $lang, $config, $pages;

		$ids = explode('|', $ids);

		if ( !$ids || !in_array($action, array('activate', 'approve', 'resend_link')) )
			return $_response;

		$this -> loadClass('Mail');
		$set_status = $action == 'activate' ? 'active' : 'approval';

		$tmp_mail_tpl_resend = $GLOBALS['rlMail'] -> getEmailTemplate('account_created_incomplete');
		$tmp_mail_tpl_status = $GLOBALS['rlMail'] -> getEmailTemplate( $set_status == 'active' ? 'account_activated' : 'account_deactivated' );

		foreach ( $ids as $id )
		{
			$id = (int)$id;
			$account_info = $this -> getProfile($id);

			/* update status */
			if ( in_array($action, array('activate', 'approve')) )
			{
				if ( $account_info['Status'] == $set_status )
					continue;

				/* set email notification */
				$mail_tpl_status = $tmp_mail_tpl_status;
				$mail_tpl_status['body'] = str_replace( '{username}', $account_info['Full_name'], $mail_tpl_status['body'] );
				$GLOBALS['rlMail'] -> send( $mail_tpl_status, $account_info['Mail'] );

				/* update entry */
				$update = array(
					'fields' => array(
						'Status' => $set_status
					),
					'where' => array(
						'ID' => $id
					)
				);
				$success = $rlActions -> updateOne($update, 'accounts');
			}
			elseif ( $action == 'resend_link' )
			{
				/* resend activation link */
				if ( $account_info['Status'] == 'incomplete' )
				{
					$activation_link = RL_URL_HOME;
					$activation_link .= $config['mod_rewrite'] ? "{$pages['confirm']}.html?key=" : "?page={$pages['confirm']}&amp;key=" ;
					$activation_link .= $account_info['Confirm_code'];
					$activation_link = '<a href="' . $activation_link . '">' . $activation_link . '</a>';

					$mail_tpl_resend = $tmp_mail_tpl_resend;

					$find = array(
						'{activation_link}',
						'{username}',
					);
					$replace = array(
						$activation_link,
						$account_info['Full_name']
					);
					$mail_tpl_resend['body'] = str_replace( $find, $replace, $mail_tpl_resend['body'] );
					//$GLOBALS['rlMail'] -> send( $mail_tpl_resend, $account_info['Mail'] );

					$resend_links++;
				}
			}
		}

		if ( in_array($action, array('activate', 'approve')) )
		{
			if ( $success )
			{
				$_response -> script("printMessage('notice', '{$lang['mass_action_completed']}')");
			}
			else
			{
				trigger_error("Can not run mass action with accounts (MySQL Fail). Action: {$action}", E_USER_ERROR);
				$GLOBALS['rlDebug'] -> logger("Can not run mass action with accounts (MySQL Fail). Action: {$action}");
			}
		}
		elseif ( $action == 'resend_link' )
		{
			if ( $resend_links )
			{
				$mess = str_replace('{count}', $resend_links, $lang['resend_activation_link_success']);
				$_response -> script("printMessage('notice', '{$mess}')");
			}
			else
			{
				$mess = $lang['resend_activation_link_fail'];
				$_response -> script("printMessage('alert', '{$mess}')");
			}
		}

		return $_response;
	}
}
