<?php

/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: ACCOUNTS.INC.PHP
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

	/* load system lib */
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

		$rlHook -> load('apExtAccountsUpdate');

		if ( $field == 'Status' && $id )
		{
			$reefless -> loadClass('Account');
			$reefless -> loadClass('Common');

			/* get account info */
			$account_info = $rlAccount -> getProfile( (int)$id );

			if ( $account_info['Status'] != $value )
			{
				if ( $value == 'active' )
				{
					$updateData['fields']['Password_tmp'] = '';
				}

				/* inform user about status changing of her account */
				$reefless -> loadClass('Mail');
				$mail_tpl = $rlMail -> getEmailTemplate( $value == 'active' ? 'account_activated' : 'account_deactivated' );
				$mail_tpl['body'] = str_replace( '{username}', $account_info['Full_name'], $mail_tpl['body'] );
				$rlMail -> send( $mail_tpl, $account_info['Mail'] );

				/* diactivate account listings */
				$reefless -> loadClass('Listings', 'admin');
				$reefless -> loadClass('Categories');
				$account_listings = $rlListings -> getListingsByAccount($id, true);

				if ( $account_listings )
				{
					foreach ($account_listings as $al_index => $al_listing )
					{
						if ( $value == 'active' )
						{
							$rlCategories -> listingsIncrease($al_listing['Category_ID']);
						}
						else
						{
							$rlCategories -> listingsDecrease($al_listing['Category_ID']);
						}
					}
				}
			}
		}

		$rlActions -> updateOne( $updateData, 'accounts');
		exit;
	}

	/* data read */
	$limit = $rlValid -> xSql( $_GET['limit'] );
	$start = $rlValid -> xSql( $_GET['start'] );
	$search = $rlValid -> xSql( $_GET['search'] );
	$sort = $rlValid -> xSql( $_GET['sort'] );
	$sortDir = $rlValid -> xSql( $_GET['dir'] );

	$date_from = $rlValid -> xSql($_GET['date_from']);
	$date_to = $rlValid -> xSql($_GET['date_to']);

	$sql = "SELECT SQL_CALC_FOUND_ROWS `ID`, CONCAT(`First_name`, ' ', `Last_name`) AS `Name`, `Username`, `Type`, `Status`, `Mail`, `Date`, `Photo`, ";
	$sql .= "(SELECT COUNT(`ID`) FROM `". RL_DBPREFIX ."listings` WHERE `Account_ID` = `T1`.`ID` AND `Status` <> 'trash') AS `Listings_count`, ";
	$sql .= "(SELECT COUNT(`ID`) FROM `". RL_DBPREFIX ."tmp_categories` WHERE `Account_ID` = `T1`.`ID` AND `Status` <> 'trash') AS `Custom_categories_count` ";
	$sql .="FROM `". RL_DBPREFIX ."accounts` AS `T1` ";
	$sql .= "WHERE 1 ";

	if ( $search )
	{
		if ( !empty($_GET['username']) )
		{
			$sql .= " AND `Username` LIKE '%{$_GET['username']}%' ";
		}
		if ( !empty($_GET['first_name']) )
		{
			$sql .= " AND `First_name` LIKE '%{$_GET['first_name']}%' ";
		}
		if ( !empty($_GET['last_name']) )
		{
			$sql .= " AND `Last_name` LIKE '%{$_GET['last_name']}%' ";
		}
		if ( !empty($_GET['email']) )
		{
			$sql .= " AND `Mail` LIKE '%{$_GET['email']}%' ";
		}
		if ( !empty($_GET['account_type']) )
		{
			$sql .= " AND `Type` = '{$_GET['account_type']}' ";
		}
		if ( !empty($_GET['search_status']) )
		{
			$status = $_GET['search_status'];

			if( in_array($status, array('active', 'approval', 'pending', 'incomplete')) )
			{
				$sql .= " AND `Status` = '{$status}' ";
			}
			elseif ($status == 'new')
			{
				$new_period = empty($config['new_period']) ? 1 : $config['new_period'];
				$sql .= " AND `Status` <> 'trash' AND UNIX_TIMESTAMP(DATE_ADD(`Date`, INTERVAL {$new_period} DAY)) > UNIX_TIMESTAMP(NOW()) ";
			}
		}

		if ( !empty($date_from) )
		{
			$sql .= "AND UNIX_TIMESTAMP(DATE(`Date`)) >= UNIX_TIMESTAMP('{$date_from}') ";
		}
		if ( !empty($date_to) )
		{
			$sql .= "AND UNIX_TIMESTAMP(DATE(`Date`)) <= UNIX_TIMESTAMP('{$date_to}') ";
		}
	}

	$sql .= "AND `Status` <> 'trash' ORDER BY `{$sort}` {$sortDir} LIMIT {$start}, {$limit}";

	$rlHook -> load('apExtAccountsSql');

	$data = $rlDb -> getAll($sql);

	$count = $rlDb -> getRow( "SELECT FOUND_ROWS() AS `count`" );
	$count = $count['count'];

	foreach ( $data as $key => $value )
	{
		$data[$key]['Status'] = $lang[$data[$key]['Status']];
		$data[$key]['Type_name'] = $lang['account_types+name+'.$data[$key]['Type']];
		$name = trim($data[$key]['Name']);
		$data[$key]['Name'] = empty($name) ? $lang['not_available'] : $name;

		$src = $value['Photo'] ? RL_FILES_URL . $value['Photo'] : RL_URL_HOME . ADMIN . '/img/no-account.png';
		$data[$key]['thumbnail'] = '<img style="border: 2px white solid;width: 70px" alt="'. $listingTitle .'" title="'. $listingTitle .'" src="'. $src .'" />';

		$fields_html = '<div style="margin: 0 0 0 10px"><table>';

		// add listing count
		$fields_html .= '<tr><td style="padding: 0 5px 4px;">'.$lang['listings'].':</td><td><b><a href="'. RL_URL_HOME . ADMIN .'/index.php?controller=accounts&amp;action=view&amp;userid='. $value['ID'] .'#listings">'. $value['Listings_count'] .'</a></b></td></tr>';

		// add custom categories counter
		if ( $value['Custom_categories_count'] )
		{
			$fields_html .= '<tr><td style="padding: 0 5px 4px;">'.$lang['admin_controllers+name+custom_categories'].':</td><td><b><a href="'. RL_URL_HOME . ADMIN .'/index.php?controller=custom_categories">'. $value['Custom_categories_count'] .'</a></b></td></tr>';
		}

		$fields_html .= '</table></div>';

		$data[$key]['fields'] = $fields_html;
	}
	
	$rlHook -> load('apExtAccountsData');

	$reefless -> loadClass( 'Json' );

	$output['total'] = $count;
	$output['data'] = $data;

	echo $rlJson -> encode( $output );
}
/* ext js action end */

else
{
	$rlHook -> load('apPhpAccountsTop');

	/* remote listing activation */
	if ( $_GET['action'] == 'remote_activation' && $_GET['id'] && $_GET['hash'] )
	{
		$remote_id = (int)$_GET['id'];
		$remote_hash = $_GET['hash'];

		$sql = "SELECT `ID`, `Mail`, `Username` FROM `". RL_DBPREFIX ."accounts` WHERE `ID` = '{$remote_id}' AND MD5(`Date`) = '{$remote_hash}' AND `Status` <> 'active' LIMIT 1";
		$remote_activation_info = $rlDb -> getRow($sql);

		if ( $remote_activation_info['ID'] == $remote_id )
		{
			$activation_update = array(
				'fields' => array('Status' => 'active'),
				'where' => array('ID' => $remote_id)
			);

			if ( $rlActions -> updateOne($activation_update, 'accounts') )
			{
				$reefless -> loadClass('Mail');
				$mail_tpl = $rlMail -> getEmailTemplate( 'account_activated' );

				/* get account info */
				$mail_tpl['body'] = str_replace( '{username}', $remote_activation_info['Username'], $mail_tpl['body'] );
				$rlMail -> send( $mail_tpl, $remote_activation_info['Mail'] );

				$reefless -> loadClass( 'Notice' );
				$rlNotice -> saveNotice( $lang['notice_remote_activation_activated_account'] );
			}
		}
		else
		{
			$reefless -> loadClass( 'Notice' );
			$errors[] = $lang['notice_remote_account_activation_deny'];

			$rlSmarty -> assign_by_ref('errors', $errors);
		}
		unset($_GET['action']);
	}
	else
	{
		/* assing statuses */
		$statuses = array('new', 'active', 'pending', 'incomplete', 'approval');
		$rlSmarty -> assign_by_ref('statuses', $statuses);

		/* assign languages list */
		$allLangs = $GLOBALS['languages'];
		$rlSmarty -> assign_by_ref( 'allLangs', $allLangs );
		
		/* additional bread crumb step */
		if ($_GET['action'])
		{
			switch ($_GET['action']){
				case 'add':
					$bcAStep = $lang['add_account'];
				break;
				case 'edit':
					$bcAStep = $lang['edit_account'];
				break;
				case 'view':
					$bcAStep = $lang['view_account'];
				break;
			}
		}

		/* define RL_TPL_BASE */
		define('RL_TPL_BASE', RL_URL_HOME . ADMIN . '/');

		/* get account types */
		$reefless -> loadClass('Account');
		$account_types = $rlAccount -> getAccountTypes('visitor');
		$rlSmarty -> assign_by_ref('account_types', $account_types);

		if ( $_GET['action'] == 'add' || $_GET['action'] == 'edit' )
		{
			$reefless -> loadClass('Account');
			$reefless -> loadClass('Common');
			$reefless -> loadClass('Categories');
			$reefless -> loadClass('Mail');
			$reefless -> loadClass('Resize');

			/* get domain name */
			$domain = $rlValid -> getDomain(RL_URL_HOME, true);
			$rlSmarty -> assign_by_ref('domain', $domain);

			$account_id = $rlValid -> xSql($_GET['account']);

			// get current account info
			$account_info = $rlAccount -> getProfile( (int)$account_id );
			$rlSmarty -> assign_by_ref('aInfo', $account_info);

			if ( $_GET['action'] == 'edit' && !$_POST['fromPost'] )
			{
				/* get account fields */
				$account_fields = $rlAccount -> getFields($account_info['Account_type_ID']);
				$account_fields = $rlLang -> replaceLangKeys( $account_fields, 'account_fields', array( 'name', 'description' ) );
				$account_fields = $rlCommon -> fieldValuesAdaptation($account_fields, 'account_fields');
				$rlSmarty -> assign_by_ref( 'fields', $account_fields );

				if ( !empty( $account_fields ) )
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

									$_POST['f'][$key]['value'] = $df_item[0];
									$_POST['f'][$key]['df'] = $df_item[1];
									break;

								case 'date':
									if ( $account_fields[$key]['Default'] == 'single' )
									{
										$_POST['f'][$key] = $account_info[$search_fields[$account_fields[$key]['Key']]];
									}
									elseif ( $account_fields[$key]['Default'] == 'multi' )
									{
										$_POST['f'][$key]['from'] = $account_info[$account_fields[$key]['Key']];
										$_POST['f'][$key]['to'] = $account_info[$account_fields[$key]['Key'].'_multi'];
									}
									break;
									
								case 'phone':
									$_POST['f'][$key] = $reefless -> parsePhone($account_info[$account_fields[$key]['Key']]);
									break;

								case 'price':
									$price = false;
									$price = explode( '|', $account_info[$account_fields[$key]['Key']] );

									$_POST['f'][$key]['value'] = $price[0];
									$_POST['f'][$key]['currency'] = $price[1];
									break;

								case 'unit':
									$unit = false;
									$unit = explode( '|', $account_info[$account_fields[$key]['Key']] );

									$_POST['f'][$key]['value'] = $unit[0];
									$_POST['f'][$key]['unit'] = $unit[1];
									break;

								case 'checkbox':
									$ch_items = null;
									$ch_items = explode(',', $account_info[$account_fields[$key]['Key']]);

									$_POST['f'][$key] = $ch_items;
									unset($ch_items);
									break;

								case 'accept':
									unset($account_fields[$key]);
									break;
									
								case 'text':
								case 'textarea':
									if ( $account_fields[$key]['Multilingual'] && count($GLOBALS['languages']) > 1 )
									{
										$_POST['f'][$key] = $reefless -> parseMultilingual($account_info[$account_fields[$key]['Key']]);
									}
									else
									{
										$_POST['f'][$key] = $account_info[$account_fields[$key]['Key']];
									}
									break;

								default:
									$_POST['f'][$key] = $account_info[$search_fields[$account_fields[$key]['Key']]];
									break;
							}
						}
					}

					$rlSmarty -> assign_by_ref( 'fields', $account_fields );
				}

				if ( !$_POST['fromPost'] )
				{
					$_POST['profile']['username'] = $account_info['Username'];
					$_POST['profile']['mail'] = $account_info['Mail'];
					$_POST['profile']['display_email'] = $account_info['Display_email'];
					$_POST['profile']['first_name'] = $account_info['First_name'];
					$_POST['profile']['last_name'] = $account_info['Last_name'];
					$_POST['profile']['type'] = $account_info['Account_type_ID'];
					$_POST['profile']['status'] = $account_info['Status'];
				}

				$rlHook -> load('apPhpAccountsPost');
			}

			if ( isset($_POST['form_submit']) )
			{
				$errors = array();

				/* load the utf8 lib */
				loadUTF8functions('ascii', 'utf8_to_ascii', 'unicode');

				$profile_data = $_POST['profile'];
				$account_data = $_POST['f'];

				/* get selected account type fields */
				$fields = $rlAccount -> getFields($profile_data['type']);
				$fields = $rlLang -> replaceLangKeys( $fields, 'account_fields', array( 'name', 'description' ) );
				$fields = $rlCommon -> fieldValuesAdaptation($fields, 'account_fields');
				$rlSmarty -> assign_by_ref( 'fields', $fields );

				// check username lenght
				if ( strlen($profile_data['username']) < 3 )
				{
					$errors[] = str_replace( '{field}', '<span class="field_error">"'.$lang['username'].'"</span>', $lang['notice_reg_length'] );
					$error_fields[] = 'profile[username]';
				}

				/* check account exist (in add mode only) */
				if ($_GET['action'] == 'add')
				{
					/* check username */
					$account_exist = $rlDb -> fetch( array('Username'), array( 'Username' => $profile_data['username'] ), null, null, 'accounts', 'row' );

					if ( !empty($account_exist) )
					{
						$errors[] = str_replace( '{username}', "<b>\"".$profile_data['username']."\"</b>", $lang['notice_account_exist']);
						$error_fields[] = 'profile[username]';
					}
				}

				/* check password */
				if ( $_GET['action'] == 'add' || ($_GET['action'] == 'edit' && !empty($profile_data['password']) ) )
				{
					if ( empty($profile_data['password']) )
					{
						$errors[] = str_replace( '{field}', "<b>".$lang['password']."</b>", $lang['notice_field_empty']);
						$error_fields[] = 'profile[password]';
					}
					if ( empty($profile_data['password_repeat']) )
					{
						$errors[] = str_replace( '{field}', "<b>".$lang['password_repeat']."</b>", $lang['notice_field_empty']);
						$error_fields[] = 'profile[password_repeat]';
					}

					/* check password's match */
					if ($profile_data['password'] != $profile_data['password_repeat'])
					{
						$errors[] = $lang['notice_pass_bad'];
						$error_fields[] = 'profile[password]';
					}
				}

				/* check email */
				if( !$rlValid -> isEmail( $profile_data['mail'] ) )
				{
					$errors[] = $lang['notice_bad_email'];
					$error_fields[] = 'profile[mail]';
				}

				// check dublicate e-mail
				if ($_GET['action'] == 'add')
				{
					$email_exist = $rlDb -> fetch( 'Mail', array( 'Mail' => $profile_data['mail'] ), null, null, 'accounts', 'row' );
				}
				else
				{
					$email_exist = $rlDb -> fetch( 'Mail', array( 'Mail' => $profile_data['mail'] ), "AND `Mail` <> '{$account_info['Mail']}'", null, 'accounts', 'row' );
				}

				if ( !empty($email_exist) )
				{
					$error_fields[] = 'profile[mail]';
					$errors[] = str_replace( '{email}', '<b>"'. $profile_data['mail'] .'"</b>', $lang['notice_account_email_exist'] );
				}

				/* check type */
				if( empty($profile_data['type']) )
				{
					$errors[] = $lang['notice_choose_account_type'];
					$error_fields[] = 'profile[type]';
				}

				$location = $profile_data['location'];
				if ( $account_types[$profile_data['type']]['Own_location'] )
				{
					/* validate */
					$location = trim($location);
					$wildcard_deny = explode(',', $config['account_wildcard_deny']);
					$rlDb -> setTable('pages');
					$deny_pages_tmp = $rlDb -> fetch(array('Path'), null, "WHERE `Path` <> ''");
					foreach ($deny_pages_tmp as $deny_page)
					{
						$wildcard_deny[] = $deny_page['Path'];
					}
					unset($deny_pages_tmp);

					preg_match('/[\W]+/', str_replace(array('-', '_'), '', $location), $matches);

					if ( $_GET['action'] == 'edit' )
					{
						$add_where = "AND `ID` <> '{$account_id}'";
					}

					if ( empty($location) || !empty($matches) )
					{
						$errors[] = $lang['personal_address_error'];
						$error_fields[] = 'profile[location]';
					}
					/* check for uniqueness */
					else if ( in_array($location, $wildcard_deny) || $rlDb -> getOne('ID', "`Own_address` = '{$location}' {$add_where}", 'accounts') )
					{
						$errors[] = $lang['personal_address_in_use'];
						$error_fields[] = 'profile[location]';
					}
				}
				
				if ( $back_errors = $rlCommon -> checkDynamicForm( $account_data, $fields, 'f', true ) )
				{
					foreach ( $back_errors as $error )
					{
						$errors[] = $error;
					}
					
					if ( $rlCommon -> error_fields )
					{
						$error_fields = $rlCommon -> error_fields;
						$rlCommon -> error_fields = false;
					}
				}

				$rlHook -> load('apPhpAccountsValidate');

				if( !empty($errors) )
				{
					$rlSmarty -> assign_by_ref( 'errors', $errors );
				}
				else 
				{
					/* add/edit action */
					if ( $_GET['action'] == 'add' )
					{
						/* personal address handler */
						$profile_data['location'] = trim($profile_data['location']);

						if ( !$account_types[$profile_data['type']]['Own_location'] )
						{
							/* load the utf8 lib */
							loadUTF8functions('ascii', 'utf8_to_ascii', 'unicode');

							$username = $profile_data['username'];
							if ( !utf8_is_ascii( $username ) )
							{
								$username = utf8_to_ascii( $username );
							}

							$profile_data['location'] = $rlSmarty -> str2path($profile_data['username']);
						}

						$rlHook -> load('apPhpAccountsBeforeAdd');

						// save account details
						if ( $action = $rlAccount -> registration( $profile_data['type'], $profile_data, $account_data, $fields ) )
						{
							$rlHook -> load('apPhpAccountsAfterAdd');

							$message = $lang['notice_reg_complete'];
							$aUrl = array( "controller" => $controller );
						}
						else 
						{
							trigger_error( "Can't add new account (MYSQL problems)", E_WARNING );
							$rlDebug -> logger("Can't add new account (MYSQL problems)");
						}
					}
					elseif ( $_GET['action'] == 'edit' )
					{
						$update_data = array(
							'mail' => $profile_data['mail'],
							'location' => $profile_data['location'],
							'display_email' => $profile_data['display_email'],
							'type' => $rlDb -> getOne('Key', "`ID` = '{$profile_data['type']}'", 'account_types'),
							'status' => $profile_data['status']
						);

						if ( !empty($profile_data['password']) )
						{
							$update_data['password'] = md5($profile_data['password']);
						}

						$rlHook -> load('apPhpAccountsBeforeEdit');

						$action = $rlAccount -> editProfile($update_data, (int)$_GET['account']);
						$rlAccount -> editAccount( $account_data, $fields, (int)$_GET['account'] );

						$rlHook -> load('apPhpAccountsAfterEdit');

						$message = $lang['notice_account_edited'];
						$aUrl = array( 
							'controller' => $controller
						);

						/* inform user about status changing of her account */
						if ( $profile_data['status'] != $account_info['Status'] )
						{
							$reefless -> loadClass('Mail');
							$mail_tpl = $rlMail -> getEmailTemplate( $profile_data['status'] == 'active' ? 'account_activated' : 'account_deactivated' );

							$mail_tpl['body'] = str_replace( '{username}', $account_info['Full_name'], $mail_tpl['body'] );
							$rlMail -> send( $mail_tpl, $account_info['Mail'] );

							/* diactivate account listings */
							$reefless -> loadClass('Listings', 'admin');
							$reefless -> loadClass('Categories');
							$account_listings = $rlListings -> getListingsByAccount( (int)$_GET['account'], true );

							if ( $account_listings )
							{
								foreach ($account_listings as $al_index => $al_listing )
								{
									if ( $profile_data['status'] == 'active' )
									{
										$rlCategories -> listingsIncrease($al_listing['Category_ID']);
									}
									else
									{
										$rlCategories -> listingsDecrease($al_listing['Category_ID']);
									}
								}
							}
						}
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
		elseif( $_GET['action'] == 'view' )
		{
			$account_id = (int)$_GET['userid'];

			$reefless -> loadClass('Listings');
			$reefless -> loadClass('Account');
			$reefless -> loadClass('Message');

			$rlXajax -> registerFunction( array( 'contactOwner', $rlMessage, 'ajaxContactOwnerAP' ) );

			/* populate tabs */
			$tabs = array(
				'seller' => array(
					'key' => 'seller',
					'name' => $lang['account_information']
				),
				'listings' => array(
					'key' => 'listings',
					'name' => $lang['account_listings']
				),
				'map' => array(
					'key' => 'map',
					'name' => $lang['map']
				)
			);

			$rlSmarty -> assign_by_ref('tabs', $tabs);

			/* get seller information */
			$seller_info = $rlAccount -> getProfile((int)$account_id);
			$rlSmarty -> assign_by_ref('seller_info', $seller_info);

			if ( !$seller_info )
			{
				$rlSmarty -> assign('alerts', array('Requested account not found'));
			}
			else
			{
				/* get amenties */
				if ( $config['map_amenities'] )
				{
					$rlDb -> setTable('map_amenities');
					$amenities = $rlDb -> fetch(array('Key', 'Default'), array('Status' => 'active'), "ORDER BY `Position`");
					$amenities = $rlLang -> replaceLangKeys( $amenities, 'map_amenities', array('name') );
					$rlSmarty -> assign_by_ref('amenities', $amenities);
				}

				/* define fields for Google Map */
				$location = $rlAccount -> mapLocation;
				if ( !empty($location) )
				{
					$rlSmarty -> assign_by_ref( 'location', $location );
				}

				if ( !$config['map_module'] || !$location )
				{
					unset($tabs['map']);
				}
			}

			$rlHook -> load('apPhpAccountsAfterView');
		}

		$reefless -> loadClass('Listings');

		/* register ajax methods */
		$rlXajax -> registerFunction( array( 'deleteAccount', $rlAdmin, 'ajaxDeleteAccount' ) );
		$rlXajax -> registerFunction( array( 'prepareDeleting', $rlAccount, 'ajaxPrepareDeleting' ) );
		$rlXajax -> registerFunction( array( 'massActions', $rlAccount, 'ajaxMassActions' ) );
		$rlXajax -> registerFunction( array( 'delAccountFile', $rlAccount, 'ajaxDelAccountFile' ) );
		$rlXajax -> registerFunction( array( 'getAccountFields', $rlAdmin, 'ajaxGetAccountFields' ) );
		$rlXajax -> registerFunction( array( 'updateAccountFields', $rlAdmin, 'ajaxUpdateAccountFields' ) );
	}

	$rlHook -> load('apPhpAccountsBottom');
}