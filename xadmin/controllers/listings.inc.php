<?php

/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: LISTINGS.INC.PHP
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
	if ( $_GET['action'] == 'update' )
	{
		$reefless -> loadClass('Actions');
		$reefless -> loadClass('Categories');
		$reefless -> loadClass('Listings');
		$reefless -> loadClass('Account');
		
		$type = $rlValid -> xSql( $_GET['type'] );
		$field = $rlValid -> xSql( $_GET['field'] );
		$value = $rlValid -> xSql( nl2br($_GET['value']) );
		$id = $rlValid -> xSql( $_GET['id'] );
		$key = $rlValid -> xSql( $_GET['key'] );

		if ( !$id )
			exit;
		
		/* get listing info before update */
		$sql = "SELECT `T1`.*, UNIX_TIMESTAMP(`T1`.`Pay_date`) AS `Payed`, `T1`.`Crossed`, `T1`.`Status`, ";
		$sql .= "`T1`.`Plan_ID`, `T3`.`Listing_period`, `T3`.`Type` AS `Plan_type`, `T3`.`Featured`, `T3`.`Advanced_mode`, `T4`.`Type` AS `Listing_type`, ";
		$sql .= "`T3`.`Cross` ";
		$sql .= "FROM `".RL_DBPREFIX."listings` AS `T1` ";
		$sql .= "RIGHT JOIN `".RL_DBPREFIX."listing_plans` AS `T3` ON `T1`.`Plan_ID` = `T3`.`ID` ";
		$sql .= "RIGHT JOIN `".RL_DBPREFIX."categories` AS `T4` ON `T1`.`Category_ID` = `T4`.`ID` ";
		$sql .= "WHERE `T1`.`ID` = '{$id}'";
		$listing_info = $rlDb -> getRow($sql);
		
		/* get account info */
		$account_info = $rlAccount -> getProfile((int)$listing_info['Account_ID']);
		
		$updateData = array(
			'fields' => array(
				$field => $value
			),
			'where' => array(
				'ID' => $id
			)
		);
		
		$rlHook -> load('apExtListingsUpdate');
		
		$rlActions -> updateOne( $updateData, 'listings');
		
		switch ($field){
			case 'Status':
				/* inform listing owner about status changing */
				$reefless -> loadClass('Mail');
				$mail_tpl = $rlMail -> getEmailTemplate( $value == 'active' ? 'listing_activated' : 'listing_deactivated' );
	
				$category = $rlCategories -> getCategory( $listing_info['Category_ID'] );

				/* generate link */
				if ( $value == 'active' && $listing_info['Status'] != 'active' )
				{
					$allow_send = true;
					
					/* increase listings counter */
					if (!empty($listing_info['Payed']))
					{
						$rlCategories -> listingsIncrease($listing_info['Category_ID'], $listing_info['Listing_type']);
						
						/* crossed listings count control */
						if ( !empty($listing_info['Crossed']) )
						{
							$crossed_cats = explode(',', trim($listing_info['Crossed'], ','));
							foreach ($crossed_cats as $crossed_cat_id)
							{
								$rlCategories -> listingsIncrease($crossed_cat_id);
							}
						}
					}
					
					$listing_title = $rlListings -> getListingTitle( $listing_info['Category_ID'], $listing_info, $listing_info['Listing_type'] );
					$page_path = $rlDb -> getOne('Path', "`Key` = 'lt_{$listing_info['Listing_type']}'", 'pages');
					
					$link = RL_URL_HOME;
					$link .= $config['mod_rewrite'] ? $page_path .'/'. $category['Path'] .'/'. $rlValid -> str2path($listing_title) .'-'.$id.'.html' : '?page='. $page_path .'&amp;id='.$id ;
					
					/* clear cache */
					$updateData['fields']['Last_step'] = '';
				}
				else if ( $value != 'active' && $listing_info['Status'] == 'active' )
				{
					$allow_send = true;
					
					/* deincrease listings counter */
					if ( !empty($listing_info['Payed']) )
					{
						$rlCategories -> listingsDecrease($listing_info['Category_ID'], $listing_info['Listing_type']);
						
						/* crossed listings count control */
						if ( !empty($listing_info['Crossed']) )
						{
							$crossed_cats = explode(',', trim($listing_info['Crossed'], ','));
							foreach ($crossed_cats as $crossed_cat_id)
							{
								$rlCategories -> listingsDecrease($crossed_cat_id);
							}
						}
					}
				}
				
				if ( $allow_send )
				{
					$mail_tpl['body'] = str_replace( array('{username}', '{link}'), array($account_info['Full_name'], '<a href="'.$link.'">'.$link.'</a>'), $mail_tpl['body'] );
					$rlMail -> send( $mail_tpl, $account_info['Mail'] );
				}
				
				break;

			case 'Pay_date':
				$period = $listing_info['Listing_period'] * 86400;
				if ( (strtotime($value) + $period > time()) && ($listing_info['Pay_date'] + $period <= time()) && $listing_info['Status'] == 'active' )
				{
					$reefless -> loadClass('Categories');
					$rlCategories -> listingsIncrease($listing_info['Category_ID'], $listing_info['Listing_type']);
					
					/* crossed listings count control */
					if ( !empty($listing_info['Crossed']) )
					{
						$crossed_cats = explode(',', trim($listing_info['Crossed'], ','));
						foreach ($crossed_cats as $crossed_cat_id)
						{
							$rlCategories -> listingsIncrease($crossed_cat_id);
						}
					}
				}
				
				/* activate featured plan */
				if ( $listing_info['Featured'] && !$listing_info['Advanced_mode'] )
				{
					$customUpdate = array(
						'fields' => array(
							'Featured_ID' => $listing_info['Plan_ID'],
							'Featured_date' => 'NOW()'
						),
						'where' => array(
							'ID' => $id
						)
					);
					$rlActions -> updateOne( $customUpdate, 'listings');
				}
				
				break;
		
			case 'Plan_ID':
				$sql = "SELECT `Type`, `Cross`, `Featured`, `Advanced_mode` FROM `".RL_DBPREFIX."listing_plans` WHERE `ID` = '{$value}'";
				$new_plan_info = $rlDb -> getRow($sql);
	
				if ( !$new_plan_info['Featured'] )
				{
					$sql = "UPDATE `". RL_DBPREFIX ."listings` SET `Featured_date` = '', `Featured_ID` = '' WHERE `ID` = {$id}";
				}
				elseif ( $new_plan_info['Featured'] && !$new_plan_info['Advanced_mode'] && !$listing_info['Featured'] )
				{
					$sql = "UPDATE `".RL_DBPREFIX."listings` SET `Featured_date` = NOW(), `Featured_ID` = '{$value}' WHERE `ID` = {$id}";
				}
				
				if ( $sql )
				{
					$rlDb -> query($sql);
				}
				
				if ( !$new_plan_info['Cross'] && $listing_info['Cross'] )
				{
					$current_crossed = explode(',', $listing_info['Crossed']);
					foreach ($current_crossed as $incrace_cc)
					{
						$rlCategories -> listingsDecrease( $incrace_cc );
					}
					$sql = "UPDATE `".RL_DBPREFIX."listings` SET `Crossed` = '' WHERE `ID` = '{$id}' LIMIT 1";
					$rlDb -> query($sql);
				}
				else if ( $new_plan_info['Cross'] && !$listing_info['Cross'] )
				{
					$current_crossed = explode(',', $listing_info['Crossed']);
					foreach ($current_crossed as $incrace_cc)
					{
						$rlCategories -> listingsIncrease( $incrace_cc );
					}
				}
				
				break;
		}
		
		exit;
	}
	
	/* data read */
	$limit = (int)$_GET['limit'];
	$start = (int)$_GET['start'];
	$category_id = (int)$_GET['category_id'];
	
	/* run filters */
	$filters = array(
		'f_Type' => true,
		'f_Category_ID' => true,
		'f_Pay_date' => true,
		'f_Plan_ID' => true,
		'f_Status' => true,
		'f_listing_id' => true,
		'f_Account' => true,
		'f_name' => true,
		'f_email' => true,
		'f_account_type' => true
	);

	$rlHook -> load('apExtListingsFilters');

	foreach ($_GET as $filter => $val)
	{
		if ( array_key_exists($filter, $filters) )
		{
			$filter_field = explode('f_', $filter);

			switch ($filter_field[1]){
				case 'Type':
					$where .= "`T3`.`Type` = '{$_GET[$filter]}' AND ";
					
					break;
				
				case 'Pay_date':
					$cond = $_GET[$filter] == 'payed' ? '<>' : '=';
					$where .= "UNIX_TIMESTAMP(`T1`.`".$filter_field[1]."`) " . $cond . " 0 AND ";
					
					break;
				
				case 'Account':
					$where .= "`T2`.`Username` = '".$_GET[$filter]."' AND ";
					
					break;
				
				case 'Category_ID':
					$where .= "(`T1`.`{$filter_field[1]}` = '{$_GET[$filter]}' OR FIND_IN_SET('{$_GET[$filter]}', `T3`.`Parent_IDs`) > 0)  AND ";
					
					break;
					
				case 'email':
					$where .= "`T2`.`Mail` = '{$_GET[$filter]}' AND ";
					
					break;
					
				case 'account_type':
					$where .= "`T2`.`Type` = '{$_GET[$filter]}' AND ";
					
					break;
					
				case 'listing_id':
					$where .= "`T1`.`ID` = '{$_GET[$filter]}' AND ";
					
					break;
					
				case 'name':
					$words = explode(' ', $field);
					$sql .= "AND (CONCAT_WS(' ', `T2`.`First_name`, `T2`.`Last_name`) LIKE '%". implode("%' OR CONCAT_WS(' ', `T2`.`First_name`, `T2`.`Last_name`) LIKE '%", $words) ."%') ";
					
					break;
					
				default:
					if ( $filter_field[1] == 'Status' && $_GET[$filter] == 'new' )
					{
						$new_period = empty($config['new_period']) ? 1 : $config['new_period'];
						$new_period = $new_period * 86400;
						
						$where .= "(UNIX_TIMESTAMP(`T1`.`Date`) + {$new_period}) >= UNIX_TIMESTAMP(NOW()) AND ";
					}
					else
					{
						$where .= isset($filters[$filter]['tb']) ? "`{$filters[$filter]['tb']}`." : "`T1`.";
						$where .= "`".$filter_field[1]."` = '".$_GET[$filter]."' AND ";
					}
					
					break;
			}
		}
	}
	
	$allow_tmp_categories = 0;
	foreach ($rlListingTypes -> types as $ltype)
	{
		if ( $ltype['Cat_custom_adding'] )
		{
			$allow_tmp_categories = 1;
		}
	}
	
	if ( !empty($where) )
	{
		$where = 'AND ' . substr($where, 0, -4);
	}

	$transfer_fields = array('ID', 'Status', 'title', 'Type', 'Cat_title', 'Plan_ID', 'Pay_date', 'Username', 'Account_ID', 'Date', 'Allow_photo', 'Allow_video');
	
	$sql = "SELECT SQL_CALC_FOUND_ROWS ";
	$sql .= "`T1`.*, `T2`.`Username`, `T3`.`Key` AS `Category_key`, `T3`.`Type` AS `Listing_type`, ";
	$sql .= "`T4`.`Key` AS `Plan_key`, `T4`.`Price` AS `Plan_price`, `T4`.`Listing_period`, `T4`.`Image` AS `Plan_image`, ";
	$sql .= "(SELECT COUNT(`ID`) FROM `". RL_DBPREFIX ."listing_video` WHERE `Listing_ID` = `T1`.`ID`) AS `Video_count`, ";
	$sql .= "`T4`.`Image_unlim`, `T4`.`Video` AS `Plan_video`, `T4`.`Video_unlim`, `T5`.`Key` AS `Featured_plan_key`, ";
	$sql .= "IF(UNIX_TIMESTAMP(`T1`.`Pay_date`) = 0, 0, `Pay_date`) AS `Pay_date` ";
	if ( $allow_tmp_categories )
	{
		$sql .= ", `T6`.`Name` AS `Tmp_name`";
	}
	$sql .= "FROM `" . RL_DBPREFIX . "listings` AS `T1` ";
	$sql .= "LEFT JOIN `" . RL_DBPREFIX ."accounts` AS `T2` ON `T1`.`Account_ID` = `T2`.`ID` ";
	$sql .= "LEFT JOIN `" . RL_DBPREFIX ."categories` AS `T3` ON `T1`.`Category_ID` = `T3`.`ID` ";
	$sql .= "LEFT JOIN `" . RL_DBPREFIX ."listing_plans` AS `T4` ON `T1`.`Plan_ID` = `T4`.`ID` ";
	$sql .= "LEFT JOIN `" . RL_DBPREFIX ."listing_plans` AS `T5` ON `T1`.`Featured_ID` = `T5`.`ID` ";
	
	if ( $allow_tmp_categories )
	{
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "tmp_categories` AS `T6` ON `T1`.`ID` = `T6`.`Listing_ID` ";
	}
	//$sql .= "WHERE `T1`.`Status` <> 'trash' {$where} GROUP BY `T1`.`ID` LIMIT {$start}, {$limit}";
	$sql .= "WHERE `T1`.`Status` <> 'trash' {$where} LIMIT {$start}, {$limit}";

	$rlHook -> load('apExtListingsSql');
	
	$data = $rlDb -> getAll( $sql );
	
	$count = $rlDb -> getRow( "SELECT FOUND_ROWS() AS `count`" );

	$reefless -> loadClass('Listings');
	$reefless -> loadClass('Common');
	
	foreach ( $data as $key => $value )
	{
		$listingTitle = $rlListings -> getListingTitle( $data[$key]['Category_ID'], $data[$key], $value['Listing_type'] );
		
		/* collapsible row data */
		$src = empty($data[$key]['Main_photo']) ? RL_URL_HOME . 'templates/' . $config['template'] . '/img/no-picture.jpg' : RL_URL_HOME .'files/'.$data[$key]['Main_photo'];
		$data[$key]['thumbnail'] = '<img style="border: 2px white solid;" alt="'. $listingTitle .'" title="'. $listingTitle .'" src="'. $src .'" />';
		
		$data[$key]['Allow_photo'] = ($value['Plan_image'] > 0 || $value['Image_unlim']) && $rlListingTypes -> types[$value['Listing_type']]['Photo'] ? 1 : 0;
		$data[$key]['Allow_video'] = ($value['Plan_video'] > 0 || $value['Video_unlim']) && $rlListingTypes -> types[$value['Listing_type']]['Video'] ? 1 : 0;
		$crossed = '';
		if ( $_GET['f_Category_ID'] && in_array($_GET['f_Category_ID'], explode(',', $value['Crossed'])) )
		{
			$crossed = ' <b>('.$lang['crossed'].')</b>';
		}
		
		$data[$key]['data'] = $data[$key]['ID'];
		$data[$key]['Status'] = $GLOBALS['lang'][$data[$key]['Status']];
		$data[$key]['title'] = $listingTitle . $crossed;
		$data[$key]['Type'] = $rlListingTypes -> types[$data[$key]['Listing_type']]['name'];
		$data[$key]['Type_key'] = $value['Listing_type'];
		$data[$key]['Cat_title'] = $data[$key]['Tmp_name'] ? $data[$key]['Tmp_name'] .'<b> ('. $lang['pending'] .')</b>' : $GLOBALS['lang']['categories+name+'.$data[$key]['Category_key']];
		$data[$key]['Cat_ID'] = $value['Category_ID'];
		$data[$key]['Cat_custom'] = $value['Tmp_name'] ? 1 : 0;
		$data[$key]['Username'] = empty($data[$key]['Account_ID']) ? $lang['administrator'] : $data[$key]['Username'];

		/* populate fields */
		$fields = $rlListings -> getFormFields( $value['Category_ID'], 'short_forms', $value['Listing_type'] );

		$fields_html = '';
		
		if ( $fields )
		{
			$fields_html = '<div style="margin: 0 0 0 10px"><table>';
			foreach ( $fields as $fKey => $fValue )
			{
				if ( $first )
				{
					$html_value = $GLOBALS['rlCommon'] -> adaptValue( $fValue, $value[$fKey], 'listing', $value['ID'] );
				}
				else
				{
					if ( $field['Condition'] == 'isUrl' || $field['Condition'] == 'isEmail' )
					{
						$html_value = $listings[$key][$item];
					}
					else
					{
						$html_value = $GLOBALS['rlCommon'] -> adaptValue( $fValue, $value[$fKey], 'listing', $value['ID'] );
					}
				}
				
				if ( $html_value != '' )
				{
					$fields_html .= '<tr><td style="padding: 0 5px 4px;">'.$fValue['name'].':</td><td><b>'. $html_value .'</b></td></tr>';
				}
				$first++;
			}
			if ( $data[$key]['Crossed'] )
			{
				$crossed_details = '<ul class="ext_listing_info_list">';
				foreach (explode(',', $data[$key]['Crossed']) as $crossed_category_id )
				{
					$category_info = $rlDb -> fetch(array('Key'), array('ID' => $crossed_category_id), null, 1, 'categories', 'row');
					$crossed_details .= '<li><a target="_blank" href="'. RL_URL_HOME . ADMIN .'/index.php?controller=browse&amp;id='. $crossed_category_id .'">'. $lang['categories+name+'. $category_info['Key']] .'</a></li>';
				}
				$crossed_details .= '</ul>';
				
				$fields_html .= '<tr><td style="padding: 0 5px 4px;">'. $lang['crossed_categories'] .':</td><td>'. $crossed_details .'</td></tr>';
			}
			
			$rlHook -> load('apExtListingsDataMiddle');
			
			if ( $data[$key]['Allow_photo'] )
			{
				$fields_html .= '<tr><td colspan="2" style="padding: 0 0 4px 5px;"><a href="'. RL_URL_HOME . ADMIN .'/index.php?controller=listings&amp;action=photos&amp;id='. $value['ID'] .'">'. $lang['manage_photos'] .'</a> ('. $value['Photos_count'] .')</td></tr>';
			}
			if ( $data[$key]['Allow_video'] )
			{
				$fields_html .= '<tr><td colspan="2" style="padding: 0 0 4px 5px;"><a href="'. RL_URL_HOME . ADMIN .'/index.php?controller=listings&amp;action=video&amp;id='. $value['ID'] .'">'. $lang['manage_video'] .'</a> ('. $value['Video_count'] .')</td></tr>';
			}
			
			$rlHook -> load('apExtListingsDataBottom');
			
			$fields_html .= '</table></div>';
		}

		$data[$key]['fields'] = $fields_html;
		
		/* plan tooltip generation */
		$price = empty($data[$key]['Plan_price']) ? '<span style=color:#3cb524;>'.$lang['free'].'</span>' : $data[$key]['Plan_price'];
		
		if ( !empty($data[$key]['Plan_ID']) )
		{
			$plan_info = "
			<table class='info'>
			<tr><td>{$lang['price']}:</td><td> <b>{$price}</b><br /></td></tr>
			<tr><td>{$lang['days']}:</td><td> <b>{$data[$key]['Listing_period']}</b></td></tr>";
			if ( $value['Image_unlim'] && $rlListingTypes -> types[$value['Listing_type']]['Photo'] )
			{
				$plan_info_photo = $lang['unlimited'];
			}
			else if ( $value['Plan_image'] > 0 && $rlListingTypes -> types[$value['Listing_type']]['Photo'] )
			{
				$plan_info_photo = $value['Plan_image'];
			}
			else
			{
				$plan_info_photo = $lang['not_available'];
			}
			$plan_info .= "<tr><td>{$lang['images']}:</td><td> <b>{$plan_info_photo}</b></td></tr>";
			
			if ( $value['Video_unlim'] && $rlListingTypes -> types[$value['Listing_type']]['Video'] )
			{
				$plan_info_video = $lang['unlimited'];
			}
			else if ( $value['Plan_video'] > 0 && $rlListingTypes -> types[$value['Listing_type']]['Video'] )
			{
				$plan_info_video = $value['Plan_video'];
			}
			else
			{
				$plan_info_video = $lang['not_available'];
			}
			$plan_info .= "<tr><td>{$lang['video']}:</td><td> <b>{$plan_info_video}</b></td></tr>";
			if ( !empty($data[$key]['Featured_ID']) )
			{
				$featured_pay_status = !empty($data[$key]['Featured_date']) ? $lang['payed'] : $lang['not_payed'];
				$plan_info .= "
					<tr><td colspan='2'><span class=delete>{$lang['featured']}</span></td></tr>
					<tr><td>{$lang['plan']}:</td><td> <b>{$lang['listing_plans+name+'.$data[$key]['Featured_plan_key']]}</b></td></tr>
				";
			}
			$plan_info .= "</table>";
			$data[$key]['Plan_name'] = $lang['listing_plans+name+'.$data[$key]['Plan_key']];
			$data[$key]['Plan_info'] = $plan_info;
		}
		else
		{
			$data[$key]['Plan_ID'] = '';
		}
		
		foreach ($value as $tr_field => $tr_value)
		{
			if ( !in_array( $tr_field, $transfer_fields ) )
			{
				unset($data[$key][$tr_field]);
			}
		}
		
		$rlHook -> load('apExtListingsData');
	}
	
	$reefless -> loadClass( 'Json' );
	$output['total'] = $count['count'];
	$output['data'] = $data;
	unset($data);

	echo $rlJson -> encode( $output );
}
/* ext js action end */

else
{
	$rlHook -> load('apPhpListingsTop');
	
	/* remote listing activation */
	if ( $_GET['action'] == 'remote_activation' && $_GET['id'] && $_GET['hash'] )
	{
		$remote_listing_id = (int)$_GET['id'];
		$remote_hash = $_GET['hash'];
		
		$sql = "SELECT `ID` FROM `". RL_DBPREFIX ."listings` WHERE `ID` = '{$remote_listing_id}' AND MD5(`Date`) = '{$remote_hash}' AND `Status` <> 'active' LIMIT 1";
		$remote_activation_info = $rlDb -> getRow($sql);
		
		if ( $remote_activation_info['ID'] == $remote_listing_id )
		{
			$activation_update = array(
				'fields' => array('Status' => 'active'),
				'where' => array('ID' => $remote_listing_id)				
			);
			
			$rlHook -> load('apPhpListingsBeforeActivate');
			
			if ( $rlActions -> updateOne($activation_update, 'listings') )
			{
				$reefless -> loadClass('Mail');
				$reefless -> loadClass('Listings');
				$reefless -> loadClass('Account');
				$reefless -> loadClass('Common');
				
				$rlHook -> load('apPhpListingsAfterActivate');
				
				$mail_tpl = $rlMail -> getEmailTemplate( 'listing_activated' );
				
				/* get listing info */
				$sql = "SELECT `T1`.*, UNIX_TIMESTAMP(`T1`.`Pay_date`) AS `Payed`, `T1`.`Crossed`, `T1`.`Status`, ";
				$sql .= "`T1`.`Plan_ID`, `T3`.`Listing_period`, `T3`.`Type` AS `Plan_type`, `T3`.`Featured`, `T3`.`Advanced_mode`, `T4`.`Type` AS `Listing_type`, ";
				$sql .= "`T3`.`Cross` ";
				$sql .= "FROM `".RL_DBPREFIX."listings` AS `T1` ";
				$sql .= "RIGHT JOIN `".RL_DBPREFIX."listing_plans` AS `T3` ON `T1`.`Plan_ID` = `T3`.`ID` ";
				$sql .= "RIGHT JOIN `".RL_DBPREFIX."categories` AS `T4` ON `T1`.`Category_ID` = `T4`.`ID` ";
				$sql .= "WHERE `T1`.`ID` = '{$remote_listing_id}'";
				$listing_info = $rlDb -> getRow($sql);
				
				/* get account info */
				$account_info = $rlAccount -> getProfile((int)$listing_info['Account_ID']);
				
				$reefless -> loadClass('Categories');
				$category = $rlCategories -> getCategory( $listing_info['Category_ID'] );
				
				/* increase listings counter */
				if ( !empty($listing_info['Payed']) )
				{
					$rlCategories -> listingsIncrease($listing_info['Category_ID']);
					
					/* crossed listings count control */
					if ( !empty($listing_info['Crossed']) )
					{
						$crossed_cats = explode(',', trim($listing_info['Crossed'], ','));
						foreach ($crossed_cats as $crossed_cat_id)
						{
							$rlCategories -> listingsIncrease($crossed_cat_id);
						}
					}
				}

				$listing_title = $rlListings -> getListingTitle( $listing_info['Category_ID'], $listing_info, $listing_info['Listing_type'] );
				
				$link = RL_URL_HOME;
				$link .= $config['mod_rewrite'] ? $pages['lt_'. $listing_info['Listing_type']] .'/'. $category['Path'] .'/'. $rlValid -> str2path($listing_title) .'-'. $listing_info['ID'] .'.html' : '?page='. $page_path .'&amp;id='. $listing_info['ID'];
				
				$mail_tpl['body'] = str_replace( array('{username}', '{link}'), array($account_info['Full_name'], '<a href="'.$link.'">'.$link.'</a>'), $mail_tpl['body'] );
				$rlMail -> send( $mail_tpl, $account_info['Mail'] );
				
				$reefless -> loadClass( 'Notice' );
				$rlNotice -> saveNotice( $lang['notice_remote_activation_activated'] );
			}
		}
		else
		{
			$reefless -> loadClass( 'Notice' );
			$errors[] = $lang['notice_remote_activation_deny'];
			
			$rlSmarty -> assign_by_ref('errors', $errors);
		}
	}
	else
	{
		/* assign languages list */
		$allLangs = $GLOBALS['languages'];
		$rlSmarty -> assign_by_ref( 'allLangs', $allLangs );
		
		/* track referent controller */
		if ( $cInfo['prev'] == 'browse' )
		{
			$_SESSION['listings_redirect_mode'] = 'browse';
			$_SESSION['listings_redirect_ID'] = $_GET['category'];
		}
		elseif ( !in_array($cInfo['prev'], array('browse', 'listings')) )
		{
			unset($_SESSION['listings_redirect_mode'], $_SESSION['listings_redirect_ID']);
		}
		
		if ( !in_array($_GET['action'], array('photos', 'video', 'view')) )
		{
			$reefless -> loadClass('Listings', 'admin');
		}
		
		$reefless -> loadClass( 'Categories' );
		$reefless -> loadClass( 'Plan' );
		$reefless -> loadClass( 'Common' );
	
		/* get categories */
		$sections = $rlCategories -> getCatTree(0, false, true);
		$rlSmarty -> assign_by_ref( 'sections', $sections );
		
		/* add new listing */
		$category_id = (int)$_GET['category'];
		
		/* get accounts */
		$accounts_list = $rlDb -> fetch(array('ID', 'Username'), array('Status' => 'active'), null, null, 'accounts');
		$rlSmarty -> assign_by_ref('accounts', $accounts_list);
	
		if ( $_GET['action'] == 'add' && !empty($category_id) )
		{
			/* get current category information */
			$category = $rlCategories -> getCategory( $category_id );
			$rlSmarty -> assign_by_ref( 'category', $category );
			$rlSmarty -> assign_by_ref( 'category_id', $category_id );
		
			/* get posting type of listing */
			$listing_type = $rlListingTypes -> types[$category['Type']];
			$rlSmarty -> assign_by_ref('listing_type', $listing_type);
			
			/* change page title */
			$bcAStep[] = array('name' => $lang['add_listing']);
			$rlSmarty -> assign('cpTitle', $category['name']);
	
			if ( $category === false )
			{
				/* system error */
				trigger_error("Admin Panel | Can't load add listing page, category information missed", E_WARNING);
				$rlDebug -> logger("Admin Panel | Can't load add listing page, category information missed");
				$sError = true;
			}
			else 
			{
				$form = $rlCategories -> buildListingForm( $category['ID'], $listing_type );
				$rlSmarty -> assign_by_ref( 'form', $form );
		
				if ( empty($form) )
				{
					// system error
					trigger_error("Admin Panel | Can't load add listing page, form information missed", E_WARNING);
					$rlDebug -> logger("Admin Panel | Can't load add listing page, form information missed");
					
					$link = RL_URL_HOME . ADMIN .'/index.php?controller=categories&amp;action=build&amp;key='. $category['Key'];
					
					$message = str_replace('{category}', $category['name'], $lang['submit_form_empty']);
					$message = preg_replace('/(\[(\w*)\])/', '<a href="'. $link .'">$2</a>', $message);
					$rlSmarty -> assign('alerts', $message);
					$rlSmarty -> assign('deny', true);
				}
				else
				{
					/* get listing plans for current user type */
					$plans = $rlPlan -> getPlanByCategory($category_id);
					$rlSmarty -> assign_by_ref( 'plans', $plans );
					
					/* listing adding */
					if ( $_POST['action'] == 'add' )
					{
						/* load fields list */
						if ( !$category_fields )
						{
							$category_fields = $rlCategories -> fields;
						}
			
						if (!empty($category_fields))
						{
							$data = $_POST['f'];
						}
						
						/* check listing plans for current user type */
						$plan_id = (int)$data['l_plan'];
						$plan_info = $plans[$plan_id];
		
						if ( $_POST['crossed_categories'] )
						{
							$crossed = $_POST['crossed_categories'];
							
							$rlSmarty -> assign_by_ref('crossed', $_POST['crossed_categories']);
							$rlCategories -> parentPoints($crossed);
						}
						
						/* check owner */
						$account_id = (int)$_POST['account_id'];
						if ( !$account_id )
						{
							$errors[] = $lang['listing_owner_does_not_set'];
							$error_fields[] = 'account_id';
						}
						
						// check form fields
						if ($data)
						{
							if ( $back = $rlCommon -> checkDynamicForm( $data, $category_fields, 'f', true ) )
							{
								foreach ( $back as $error )
								{
									$errors[] = $error;
								}
							}
						}
			
						$rlHook -> load('apPhpListingsValidate');
						
						if ( $errors )
						{
							$rlSmarty -> assign_by_ref( 'errors', $errors );
						}
						else 
						{
							$reefless -> loadClass( 'Actions' );
							$reefless -> loadClass( 'Resize' );
			
							$status = $_POST['status'];
							
							$info['Category_ID'] = $category['ID'];
							$info['account_id'] = $account_id;
							$info['price'] = $plan_info['Price'];
							$info['days'] = $plan_info['Listing_period'];
							$info['status'] = $status;
							
							if ( $plan_info['Cross'] )
							{
								$info['crossed'] = implode(',', $_POST['crossed_categories']);
							}
							
							$rlHook -> load('apPhpListingsBeforeAdd');
							
							if ( $rlListings -> create( $info, $data, $category_fields ) )
							{
								$reefless -> loadClass( 'Notice' );
								$listing_id = mysql_insert_id();
								
								$rlHook -> load('apPhpListingsAfterAdd');
								
								/* increase listings counter */
								if ( $status == 'active' )
								{
									$rlCategories -> listingsIncrease( $category['ID'], $listing_type['Key'] );
									
									/* crossed categories handler */
									if ( $plan_info['Cross'] > 0 && !empty($_POST['crossed_categories']) )
									{
										foreach ($_POST['crossed_categories'] as $incrace_cc)
										{
											$rlCategories -> listingsIncrease( $incrace_cc, $listing_type['Key'] );
										}
									}
								}
		
								$reefless -> loadClass('Mail');
	
								/* get account info */
								$account_info = $rlDb -> fetch(array('Mail', 'Username'), array('ID' => $account_id), null, 1, 'accounts', 'row');
	
								/* send message for listing owner */
								$mail_tpl = $rlMail -> getEmailTemplate( $status == 'active' ? 'free_active_listing_created' : 'free_approval_listing_created' );
								$mail_tpl['body'] = str_replace( array('{username}', '{link}'), array($account_info['Username'], '<a href="'.$link.'">'.$link.'</a>'), $mail_tpl['body'] );
								
								$rlMail -> send( $mail_tpl, $account_info['Mail'] );
								
								$rlNotice -> saveNotice( $lang['notice_listing_added'] );
								if ( $_SESSION['listings_redirect_mode'] )
								{
									$aUrl = array( "controller" => "browse", "id" => $_SESSION['listings_redirect_ID'] );
								}
								else
								{
									$aUrl = array("controller" => $controller);
								}
	
								$reefless -> redirect( $aUrl );
							}
						}
					}
				}
				/* add listing end */
			}
		}
		elseif ($_GET['action'] == 'edit')
		{	
			$listing_id = (int)$_GET['id'];
			
			/* get listing info */
			$sql = "SELECT `T1`.*, `T2`.`Cross` AS `Plan_crossed`, `T3`.`Type` AS `Listing_type` ";
			$sql .= "FROM `".RL_DBPREFIX."listings` AS `T1` ";
			$sql .= "LEFT JOIN `".RL_DBPREFIX."listing_plans` AS `T2` ON `T1`.`Plan_ID` = `T2`.`ID` ";
			$sql .= "LEFT JOIN `".RL_DBPREFIX."categories` AS `T3` ON `T1`.`Category_ID` = `T3`.`ID` ";
			$sql .= "WHERE `T1`.`ID` = '{$listing_id}' LIMIT 1";
			$listing = $rlDb -> getRow($sql);
	
			$rlSmarty -> assign_by_ref('listing_info', $listing);
			
			/* get listing form */
			if ( isset($listing_id) )
			{
				/* change page title */
				$listing_title = $rlListings -> getListingTitle( $listing['Category_ID'], $listing, $listing['Listing_type'] );
				$bcAStep[] = array('name' => $lang['edit_listing']);
				$rlSmarty -> assign('cpTitle', $listing_title);
				
				$df = $rlCategories -> getDF();
				$rlSmarty -> assign_by_ref( 'df', $df);
				
				/* get current listing category information */
				$category = $rlCategories -> getCategory( $listing['Category_ID'] );
				$rlSmarty -> assign_by_ref( 'category', $category );
				$rlSmarty -> assign_by_ref( 'category_id', $listing['Category_ID'] );
				
				/* get listing plans for current user type */
				$plans = $rlPlan -> getPlanByCategory($category['ID']);
				$rlSmarty -> assign_by_ref( 'plans', $plans );
				
				if ( $listing['Plan_ID'] )
				{
					$plan_info = $plans[$listing['Plan_ID']];
					$rlSmarty -> assign_by_ref('plan_info', $plan_info);
				}
		
				if ( $category === false )
				{
					/* system error */
					trigger_error("Admin Panel | Can't load edit listing page, category information missed", E_WARNING);
					$rlDebug -> logger("Admin Panel | Can't load edit listing page, category information missed");
					$sError = true;
				}
				else 
				{
					$form = $rlCategories -> buildListingForm( $category['ID'], $rlListingTypes -> types[$listing['Listing_type']] );
					$rlSmarty -> assign_by_ref( 'form', $form );
	
					if ( empty($form) )
					{
						/* system error */
						trigger_error("Admin Panel | Can't load edit listing page, form information missed", E_WARNING);
						$rlDebug -> logger("Admin Panel | Can't load edit listing page, form information missed");
						
						$errors[] = $lang['edit_listing_no_form_fields_error'];
					}
					else
					{
						$listing_fields = $rlCategories -> fields;
						
						$reefless -> loadClass( "Categories" );
			
						if ( $listing['Plan_crossed'] )
						{
							$crossed = !empty($_POST['crossed_categories']) ? implode(',', $_POST['crossed_categories']) : $listing['Crossed'];
							$rlSmarty -> assign_by_ref('exp_cats', $crossed);
							
							$rlSmarty -> assign('pCats', explode(',', $crossed) );
				
							$rlXajax -> registerFunction( array( 'getCatLevel', $rlCategories, 'ajaxGetCatLevel' ) );
							$rlXajax -> registerFunction( array( 'openTree', $rlCategories, 'ajaxOpenTree' ) );
						}
						
						if ( !isset($_POST['fromPost']) )
						{
							/* set crossed categoris to post */
							if ( strpos($listing['Crossed'], ',') !== false && !empty($listing['Crossed']) )
							{
								$_POST['crossed_categories'] = explode(',', $listing['Crossed']);
							}
							elseif ( strpos($listing['Crossed'], ',') === false && !empty($listing['Crossed']) )
							{
								$_POST['crossed_categories'] = array($listing['Crossed']);
							}
							else
							{
								$_POST['crossed_categories'] = 0;
							}
							
							/* POST simulation */
							$_POST['f']['l_plan'] = $listing['Plan_ID'];
	
							foreach ($listing_fields as $key => $value)
							{
								if ( $listing[$listing_fields[$key]['Key']] != '' )
								{
									switch ($listing_fields[$key]['Type'])
									{
										case 'mixed':
											$df_item = false;	
											$df_item = explode( '|', $listing[$listing_fields[$key]['Key']] );
											
											$_POST['f'][$listing_fields[$key]['Key']]['value'] = $df_item[0];
											$_POST['f'][$listing_fields[$key]['Key']]['df'] = $df_item[1];
										break;
										
										case 'date':
											if ( $listing_fields[$key]['Default'] == 'single' )
											{
												$_POST['f'][$listing_fields[$key]['Key']] = $listing[$listing_fields[$key]['Key']];
											}
											elseif ( $listing_fields[$key]['Default'] == 'multi' )
											{
												$_POST['f'][$listing_fields[$key]['Key']]['from'] = $listing[$listing_fields[$key]['Key']];
												$_POST['f'][$listing_fields[$key]['Key']]['to'] = $listing[$listing_fields[$key]['Key'].'_multi'];
											}
										break;
										
										case 'phone':
											$_POST['f'][$listing_fields[$key]['Key']] = $reefless -> parsePhone($listing[$listing_fields[$key]['Key']]);
											break;
										
										case 'price':
											$price = false;	
											$price = explode( '|', $listing[$listing_fields[$key]['Key']] );
											
											$_POST['f'][$listing_fields[$key]['Key']]['value'] = $price[0];
											$_POST['f'][$listing_fields[$key]['Key']]['currency'] = $price[1];
										break;
										
										case 'unit':
											$unit = false;	
											$unit = explode( '|', $listing[$listing_fields[$key]['Key']] );
											
											$_POST['f'][$listing_fields[$key]['Key']]['value'] = $unit[0];
											$_POST['f'][$listing_fields[$key]['Key']]['unit'] = $unit[1];
										break;
										
										case 'checkbox':
											$ch_items = null;
											$ch_items = explode(',', $listing[$listing_fields[$key]['Key']]);
			
											$_POST['f'][$listing_fields[$key]['Key']] = $ch_items;
											unset($ch_items);
										break;
										
										default:
											if ( in_array($value['Type'], array('text', 'textarea')) && $listing_fields[$key]['Multilingual'] && count( $GLOBALS['languages'] ) > 1 )
											{
												$_POST['f'][$listing_fields[$key]['Key']] = $reefless -> parseMultilingual($listing[$listing_fields[$key]['Key']]);
											}
											else
											{
												$_POST['f'][$listing_fields[$key]['Key']] = $listing[$listing_fields[$key]['Key']];
											}
										break;
									}
								}
							}
							$_POST['status'] = $listing['Status'];
							$_POST['account_id'] = $listing['Account_ID'];
							$_POST['f']['l_plan'] = $listing['Plan_ID'];
							
							$rlHook -> load('apPhpListingsPost');
						}
						
						if ( $_POST['crossed_categories'] )
						{
							$crossed = $_POST['crossed_categories'];
							
							$rlSmarty -> assign_by_ref('crossed', $_POST['crossed_categories']);
							$rlCategories -> parentPoints($crossed);
						}
						
						/* listing editing */
						if ($_POST['action'] == 'edit')
						{
							$data = $_POST['f'];
	
							// get plan info
							$plan_id = (int)$data['l_plan'];
							$plan_info = $plans[$plan_id];
							
							/* check owner */
							$account_id = (int)$_POST['account_id'];
							if ( !$account_id )
							{
								$errors[] = $lang['listing_owner_does_not_set'];
								$error_fields[] = 'account_id';
							}
							
							// check form fields
							if ( !empty($data) )
							{
								if ( $back = $rlCommon -> checkDynamicForm( $data, $listing_fields, 'f', true ) )
								{
									foreach ( $back as $error )
									{
										$errors[] = $error;
									}
								}
							}
							
							$rlHook -> load('apPhpListingsValidate');
							
							if ( !empty($errors) )
							{
								$rlSmarty -> assign_by_ref( 'errors', $errors );
							}
							else 
							{
								$reefless -> loadClass( 'Actions' );
								$reefless -> loadClass( 'Resize' );
				
								$info['id'] = $listing_id;
								$info['Status'] = $_POST['status'];
								$info['Account_ID'] = $_POST['account_id'];
								$info['Plan_ID'] = $_POST['f']['l_plan'];
								
								if ( $plan_info['Cross'] )
								{
									$info['crossed'] = implode(',', $_POST['crossed_categories']);
								}
	
								$rlHook -> load('apPhpListingsBeforeEdit');
								
								if ( $rlListings -> edit( $info, $data, $listing_fields ) )
								{
									$rlHook -> load('apPhpListingsAfterEdit');
									
									if ( $_POST['status'] == 'active' && $listing['Status'] != 'active' )
									{
										$rlCategories -> listingsIncrease( $listing['Category_ID'], $listing_type['Key'] );
										$send_confirmation = true;
									}
									else if ( $_POST['status'] != 'active' && $listing['Status'] == 'active' )
									{
										$rlCategories -> listingsDecrease( $listing['Category_ID'], $listing_type['Key'] );
										$send_confirmation = true;
									}
									
									/* crossed categories handler */
									if ( $listing['Crossed'] )
									{
										$current_crossed = explode(',', $listing['Crossed']);
										foreach ($current_crossed as $incrace_cc)
										{
											$rlCategories -> listingsDecrease( $incrace_cc, $listing_type['Key'] );
										}
									}
									
									if ( $plan_info['Cross'] > 0 && !empty($_POST['crossed_categories']) )
									{
										foreach ($_POST['crossed_categories'] as $incrace_cc)
										{
											$rlCategories -> listingsIncrease( $incrace_cc, $listing_type['Key'] );
										}
									}
									
									/* send notification to listing owner */
									if ( $send_confirmation )
									{
										/* get account info */
										$reefless -> loadClass('Account');
										$account_info = $rlAccount -> getProfile((int)$listing['Account_ID']);
										
										$reefless -> loadClass('Mail');
										$mail_tpl = $rlMail -> getEmailTemplate( $_POST['status'] == 'active' ? 'listing_activated' : 'listing_deactivated' );
										
										$link = RL_URL_HOME;
										$link .= $config['mod_rewrite'] ? $pages['lt_'. $listing['Listing_type']] .'/'. $category['Path'] .'/'. $rlValid -> str2path($listing_title) .'-'. $listing_id .'.html' : '?page='. $page_path .'&amp;id='. $listing_id;
										
										$mail_tpl['body'] = str_replace( array('{username}', '{link}'), array($account_info['Full_name'], '<a href="'.$link.'">'.$link.'</a>'), $mail_tpl['body'] );
										$rlMail -> send( $mail_tpl, $account_info['Mail'] );
									}
									
//									/* save listing category type */
//									$_SESSION['category_type'] = $category['Type'];
									
									$reefless -> loadClass( 'Notice' );
									$rlNotice -> saveNotice( $lang['notice_listing_edited'] );
									
									if ( $_SESSION['listings_redirect_mode'] )
									{
										$aUrl = array( "controller" => "browse", "id" => $_SESSION['listings_redirect_ID'] );
									}
									else
									{
										$aUrl = array( "controller" => $controller );
									}
									$reefless -> redirect( $aUrl );
								}
							}
						}
						/* edit listing end */
					}
				}
			}
			else
			{
				/* system error */
				trigger_error("Admin Panel | Can't load edit listing page, listing information missed", E_WARNING);
				$rlDebug -> logger("Admin Panel | Can't load edit listing page, listing information missed");
				$sError = true;
			}
		}
		elseif ($_GET['action'] == 'photos')
		{
			$reefless -> loadClass( 'Listings' );
			$reefless -> loadClass( 'Crop' );
			$reefless -> loadClass( 'Resize' );
	
			$id = $_SESSION['admin_transfer']['listing_id'] = (int)$_GET['id'];
			
			$bcAStep[] = array(
				'name' => $lang['manage_photos']
			);
			
			/* get listing info */
			$listing = $rlListings -> getShortDetails( $id, $plan_info = true );
			$rlSmarty -> assign_by_ref( 'listing', $listing );
			$photos_allow = $listing['Plan_image'];
			
			/* define listing type */
			$listing_type = $rlListingTypes -> types[$listing['Listing_type']];
			$rlSmarty -> assign_by_ref('listing_type', $listing_type);
			
			/* simulate plan_info variable */
			$plan_info = array(
				'Image_unlim' => $listing['Image_unlim'],
				'Image' => $listing['Plan_image']
			);
			$rlSmarty -> assign_by_ref('plan_info', $plan_info);
			
			$rlSmarty -> assign_by_ref('allowed_photos', $plan_info['Image']);
			
			$rlXajax -> registerFunction( array( 'makeMain', $rlListings, 'ajaxMakeMain' ) );
			$rlXajax -> registerFunction( array( 'editDesc', $rlListings, 'ajaxEditDesc' ) );
			$rlXajax -> registerFunction( array( 'reorderPhoto', $rlListings, 'ajaxReorderPhoto' ) );
			$rlXajax -> registerFunction( array( 'crop', $rlCrop, 'ajaxCrop' ) );
			
			$max_file_size = str_replace('M', '', ini_get('upload_max_filesize'));
			$rlSmarty -> assign_by_ref( 'max_file_size', $max_file_size );
			
			$rlHook -> load('apPhpListingsPhotos');
		}
		elseif ($_GET['action'] == 'video')
		{
			$reefless -> loadClass( 'Listings' );
			$reefless -> loadClass( 'Crop' );
			$reefless -> loadClass( 'Resize' );
	
			$id = (int)$_GET['id'];
			
			$bcAStep[] = array(
				'name' => $lang['manage_video']
			);
			
			/* get listing info */
			$listing = $rlListings -> getShortDetails( $id, $plan_info = true );

			if ( empty($id) || 	empty($listing) )
			{
				$sError = true;
			}
			elseif ( !$listing['Plan_video'] && !$listing['Video_unlim'] )
			{
				$alerts[] = $lang['no_video_allowed'];
				$rlSmarty -> assign_by_ref('alerts', $alerts);
			}
			else
			{
				$rlSmarty -> assign_by_ref( 'listing', $listing );
				
				/* get listing video */
				$rlDb -> setTable('listing_video');
				$videos = $rlDb -> fetch( array('ID', 'Video', 'Preview', 'Type'), array( 'Listing_ID' => $id ), "ORDER BY `Position`");
				$rlSmarty -> assign_by_ref( 'videos', $videos );
				
				$video_allow = $listing['Plan_video'] - count($videos);
				$rlSmarty -> assign_by_ref( 'video_allow', $video_allow );
				
				$max_file_size = ini_get('upload_max_filesize');
				$rlSmarty -> assign_by_ref( 'max_file_size', $max_file_size );
				
				if ( $_POST['upload'] )
				{
					if ( $rlListings -> uploadVideo( $_POST['type'], $_POST['type'] == 'youtube' ? $_POST['youtube_embed'] : $_FILES, $id ) )
					{
						$reefless -> loadClass( 'Notice' );
						$rlNotice -> saveNotice( $lang['notice_files_uploded'] );
						$aUrl = array( 
							'controller' => $controller,
							'action' => 'video',
							'id' => $_GET['id']
						);
						
						$reefless -> redirect( $aUrl );
					}
					else
					{
						$rlSmarty -> assign_by_ref('errors', $errors);
					}
				}
				
				$rlXajax -> registerFunction( array( 'deleteVideo', $rlListings, 'ajaxDelVideoFileAP' ) );
				$rlXajax -> registerFunction( array( 'reorderVideo', $rlListings, 'ajaxReorderVideo' ) );
			}
			
			$rlHook -> load('apPhpListingsVideo');
		}
		elseif ($_GET['action'] == 'view')
		{
			$reefless -> loadClass('Listings');
			$reefless -> loadClass('Account');
			$reefless -> loadClass('Message');
			
			$rlXajax -> registerFunction( array( 'contactOwner', $rlMessage, 'ajaxContactOwnerAP' ) );
			
			/* populate tabs */
			$tabs = array(
				'listing' => array(
					'key' => 'listing',
					'name' => $lang['listing']
				),
				'seller' => array(
					'key' => 'seller',
					'name' => $lang['seller_info']
				),
				'video' => array(
					'key' => 'video',
					'name' => $lang['video']
				),
				'map' => array(
					'key' => 'map',
					'name' => $lang['map']
				)
			);
			
			$rlSmarty -> assign_by_ref('tabs', $tabs);
			
			$listing_id = (int)$_GET['id'];
			
			/* get listing info */
			$sql = "SELECT `T1`.*, `T2`.`Path`, `T2`.`Type` AS `Listing_type`, `T2`.`Key` AS `Category_key`, ";
			$sql .= "`T3`.`Image`, `T3`.`Image_unlim`, `T3`.`Video`, `T3`.`Video_unlim` ";
			$sql .= "FROM `" . RL_DBPREFIX . "listings` AS `T1` ";
			$sql .= "LEFT JOIN `" . RL_DBPREFIX . "categories` AS `T2` ON `T1`.`Category_ID` = `T2`.`ID` ";
			$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_plans` AS `T3` ON `T1`.`Plan_ID` = `T3`.`ID` ";
			$sql .= "LEFT JOIN `" . RL_DBPREFIX . "accounts` AS `T5` ON `T1`.`Account_ID` = `T5`.`ID` ";
			$sql .= "WHERE `T1`.`ID` = '{$listing_id}' AND `T5`.`Status` = 'active' LIMIT 1";
			
			$listing_data = $rlDb -> getRow( $sql );
			$listing_data['category_name'] = $lang['categories+name+'. $listing_data['Category_key']];
			
			$rlSmarty -> assign_by_ref('listing_data', $listing_data);
			
			/* define listing type */
			$listing_type = $rlListingTypes -> types[$listing_data['Listing_type']];
			$rlSmarty -> assign_by_ref('listing_type', $listing_type);
			
			$bcAStep[] = array('name' => $lang['view_details']);
						
			/* build listing structure */
			$category_id = $listing_data['Category_ID'];
			$listing = $rlListings -> getListingDetails( $category_id, $listing_data, $listing_type );	
			$rlSmarty -> assign( 'listing', $listing );
			
			/* build location fields */
			$fields_list = $rlListings -> fieldsList;
		
			$location = false;
			foreach ( $fields_list as $key => $value )
			{
				if ( $fields_list[$key]['Map'] && !empty($listing_data[$fields_list[$key]['Key']]) )
				{
					$location['search'] .= $value['value'] . ', ';
					$location['show'] .= $lang[$value['pName']].': <b>'.$value['value'].'<\/b><br />';
				}
			}
			if ( !empty($location) )
			{
				$location['search'] = substr($location['search'], 0, -2);
			}
			
			if ( $listing_data['Loc_latitude'] && $listing_data['Loc_longitude'] )
			{
				$location['direct'] = $listing_data['Loc_latitude'] .','. $listing_data['Loc_longitude'];
			}
			$rlSmarty -> assign_by_ref( 'location', $location );
		
			/* get listing title */
			$listing_title = $rlListings -> getListingTitle( $category_id, $listing_data, $listing_type['Key'] );
			$rlSmarty -> assign('cpTitle', $listing_title);
			
			/* get listing photos */
			$photos = $rlDb -> fetch( '*', array( 'Listing_ID' => $listing_id, 'Status' => 'active' ), "AND `Thumbnail` <> '' AND `Photo` <> '' ORDER BY `Position`", $listing_data['Image'], 'listing_photos' );
			$rlSmarty -> assign_by_ref( 'photos', $photos );
			
			/* get listing video */
			$rlDb -> setTable('listing_video');
			$videos = $rlDb -> fetch(array('ID', 'Type', 'Video', 'Preview'), array( 'Listing_ID' => $listing_id ), "ORDER BY `Position`");
			$rlSmarty -> assign_by_ref( 'videos', $videos );
				
			/* get seller information */
			$seller_info = $rlAccount -> getProfile((int)$listing_data['Account_ID']);
			$rlSmarty -> assign_by_ref('seller_info', $seller_info);
			
			/* get amenties */
			if ( $config['map_amenities'] )
			{
				$rlDb -> setTable('map_amenities');
				$amenities = $rlDb -> fetch(array('Key', 'Default'), array('Status' => 'active'), "ORDER BY `Position`");
				$amenities = $rlLang -> replaceLangKeys( $amenities, 'map_amenities', array('name') );
				$rlSmarty -> assign_by_ref('amenities', $amenities);
			}
			
			if ( empty($videos) || !$listing_type['Video'] || ($listing_data['Video'] == 0 && !$listing_data['Video_unlim']) )
			{
				unset($tabs['video']);
			}
			if ( !$config['map_module'] || !$location )
			{
				unset($tabs['map']);
			}
			
			$rlHook -> load('apPhpListingsView');
		}
		else
		{
			/* get category titles for filters */
			$tmp_categories = $rlCategories -> getCatTitles();
			foreach ($tmp_categories as $key => $val)
			{
				$categories[$val['ID']] = array('name' => $lang[$val['pName']], 'margin' => $val['margin'], 'type' => $val['Type']);
			}
			unset($tmp_categories);
			
			/* get plans */
			$plans = $rlPlan -> getPlans( array('listing', 'package', 'featured_direct') );
			$rlSmarty -> assign_by_ref('plans', $plans);
	
			/* get featured plans */
			$featured_plans = $rlPlan -> getPlans('featured');
			$rlSmarty -> assign_by_ref('featured_plans', $featured_plans);
			
			/* get account types */
			$reefless -> loadClass('Account');
			$account_types = $rlAccount -> getAccountTypes('visitor');
			$rlSmarty -> assign_by_ref('account_types', $account_types);
			
			$filters = array(
				'Type' => array('phrase' => $lang['listing_type'], 'items' => $rlListingTypes -> types),
				'Category_ID' => array('phrase' => $lang['category'], 'items' => $categories),
				'Plan_ID' => array('phrase' => $lang['plan'], 'items' => $plans),
				'Status' => array('phrase' => $lang['status'], 'items' => array(
						'new' => $lang['new'],
						'active' => $lang['active'],
						'approval' => $lang['approval'],
						'pending' => $lang['pending'],
						'incomplete' => $lang['incomplete'],
						'expired' => $lang['expired']
					)
				),
				'Pay_date' => array('phrase' => $lang['pay_status'], 'items' => array(
						'payed' => $lang['payed'],
						'not_payed' => $lang['not_payed']
					)
				)
			);
			$rlSmarty -> assign_by_ref('filters', $filters);
			
			/* define remote status request */
			if ( in_array($_GET['status'], array('new', 'approval', 'active', 'pending', 'incomplete', 'expired')) )
			{
				$rlSmarty -> assign_by_ref('status', $_GET['status']);
			}
		}
		
		/* register ajax methods */
		$rlXajax -> registerFunction( array( 'getCatLevel', $rlCategories, 'ajaxGetCatLevel' ) );
		$rlXajax -> registerFunction( array( 'openTree', $rlCategories, 'ajaxOpenTree' ) );
		$rlXajax -> registerFunction( array( 'massActions', $rlListings, 'ajaxMassActions' ) );
		$rlXajax -> registerFunction( array( 'deleteListing', $rlListings, 'ajaxDeleteListing' ) );
		$rlXajax -> registerFunction( array( 'makeFeatured', $rlListings, 'ajaxMakeFeatured' ) );
		$rlXajax -> registerFunction( array( 'annulFeatured', $rlListings, 'ajaxAnnulFeatured' ) );
		$rlXajax -> registerFunction( array( 'moveListing', $rlListings, 'ajaxMoveListing' ) );
		$rlXajax -> registerFunction( array( 'deleteListingFile', $rlListings, 'ajaxDeleteListingFile' ) );
		
		$rlHook -> load('apPhpListingsBottom');
	}
}
