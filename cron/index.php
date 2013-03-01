<?php

/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: INDEX.PHP
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

/* load configs */
include_once( dirname(__FILE__) . "/../includes/config.inc.php");

/* system controller */
require_once( RL_INC . 'control.inc.php' );

/* system libs */
require_once( RL_LIBS . 'system.lib.php' );

/* load system configurations */
$config = $rlConfig -> allConfig();
$rlSmarty -> assign_by_ref( 'config', $config );
$GLOBALS['config'] = $config;

/* set timezone */
$reefless -> setTimeZone();

/* get system languages */
$lang = $rlLang -> getLangBySide( 'frontEnd', $config['lang'] );

/* get date format */
$date_format = $rlDb -> fetch( array('Date_format'), array('Code' => $config['lang']), null, 1, 'languages', 'row' );
$date_format = str_replace('%', '', $date_format['Date_format']);

define('RL_LANG_CODE', $config['lang']);

$reefless -> loadClass('Categories');

/* prefere email templates */
$reefless -> loadClass('Mail');
$reefless -> loadClass('Account');
$reefless -> loadClass('Listings');
$reefless -> loadClass('Cache');

/* get page paths */
$rlDb -> setTable('pages');
$pages_tmp = $rlDb -> fetch(array( 'Key', 'Path'));
foreach ( $pages_tmp as $page_tmp )
{
	$pages[$page_tmp['Key']] = $page_tmp['Path'];
}
unset($pages_tmp);

$reefless -> loadClass('ListingTypes', null, false, true);

/* LISTINGS CHECKING */
$sql = "SELECT `T1`.*, UNIX_TIMESTAMP(`T1`.`Pay_date`) AS `Pay_date`, UNIX_TIMESTAMP(`Featured_date`) AS `Featured_date`, ";
$sql .= "`T3`.`Listing_period`, `T4`.`Listing_period` AS `Feature_period`, `T5`.`Path` AS `Cat_path`, `T5`.`Type` AS `Listing_type` ";
$sql .= "FROM `".RL_DBPREFIX."listings` AS `T1` ";
$sql .= "LEFT JOIN `".RL_DBPREFIX."listing_plans` AS `T3` ON `T1`.`Plan_ID` = `T3`.`ID` ";
$sql .= "LEFT JOIN `".RL_DBPREFIX."listing_plans` AS `T4` ON `T1`.`Featured_ID` = `T4`.`ID` ";
$sql .= "LEFT JOIN `".RL_DBPREFIX."categories` AS `T5` ON `T1`.`Category_ID` = `T5`.`ID` ";
$sql .= "WHERE `T1`.`Cron` = '0' ";
$sql .= "AND `T1`.`Status` <> 'expired' AND `T1`.`Status` <> 'incomplete' AND `T1`.`Status` <> 'trash' ";
$sql .= "LIMIT {$config['listings_number']}";
$listings = $rlDb -> getAll($sql);

if ( empty($listings) )
{
	$rlDb -> query("UPDATE `".RL_DBPREFIX."listings` SET `Cron` = '0' WHERE `Status` <> 'incomplete'");
}
else
{
	$c_expired_listings = $c_expired_featured = 0;
	$listing_expired_email = $rlMail -> getEmailTemplate('cron_listing_expired');
	$status_expired_email = $rlMail -> getEmailTemplate('cron_featured_status_expired');
	
	$listing_pre_expired_email = $rlMail -> getEmailTemplate('cron_listing_pre_expired');
	$status_pre_expired_email = $rlMail -> getEmailTemplate('cron_featured_status_pre_expired');
	
	foreach ($listings as $key => $listing)
	{
		$listing_type = $rlListingTypes -> types[$listing['Listing_type']];
		$account_info = $rlAccount -> getProfile((int)$listing['Account_ID']);
		$listing_title = $rlListings -> getListingTitle( $listing['Category_ID'], $listing, $listing['Listing_type'] );
		$expire_days = $config['pre_days'];
		
		$details_link = RL_URL_HOME;
		$details_link .= $config['mod_rewrite'] ? $pages[$listing_type['Page_key']] .'/'. $listing['Cat_path'] .'/'. $rlSmarty -> str2path($listing_title) . '-' . $listing['ID'] . '.html' : '?page='. $pages[$listing_type['Page_key']] .'&amp;id=' . $listing['ID'];
		$details_link = '<a href="'. $details_link .'">'. $listing_title .'</a>';
		
		/* check expired listings */
		if ( $listing['Pay_date'] && ($listing['Pay_date'] + ($listing['Listing_period'] * 86400) <= time()) && $listing['Listing_period'] > 0 )
		{
			$update[$key]['fields'] = array(
				'Pay_date' => '',
				'Status' => 'expired',
				'Cron_notified' => '0'
			);
			$c_expired_listings++;
			
			if ( $rlListings -> isActive($listing['ID']) )
			{
				$rlCategories -> listingsDecrease($listing['Category_ID'], $listing['Listing_type']);
			}
			
			/* decrease crossed categories */
			if ( !empty($listing['Crossed']) )
			{
				$crossed_cats = explode(',', trim($listing['Crossed'], ','));
				foreach ($crossed_cats as $crossed_cat_id)
				{
					$rlCategories -> listingsDecrease($crossed_cat_id);
				}
			}
			
			/* send email */
			$update_link = RL_URL_HOME;
			$update_link .= $config['mod_rewrite'] ? $pages['upgrade_listing'] . '.html?id=' . $listing['ID'] : $update_page['Path'] . '?page='.$update_page['Path'].'&amp;id=' . $listing['ID'];
			$update_link = '<a href="'.$update_link.'">'.$update_link.'</a>';
			
			$copy_listing_expired_email = $listing_expired_email;
			$copy_listing_expired_email['body'] = str_replace(array('{username}', '{renew_link}', '{details_link}' ), array($account_info['Full_name'], $update_link, $details_link), $copy_listing_expired_email['body']);
			$copy_listing_expired_email['subject'] = str_replace(array('{username}', '{renew_link}', '{details_link}' ), array($account_info['Full_name'], $update_link, $details_link), $copy_listing_expired_email['subject']);
			$rlMail -> send( $copy_listing_expired_email, $account_info['Mail'] );
		}
		
		/* check expired featured status */
		if ( $listing['Feature_period'] && $listing['Featured_date'] && ($listing['Featured_date'] + ($listing['Feature_period'] * 86400) <= time()) && $listing['Feature_period'] > 0 )
		{
			$update[$key]['fields'] = array(
				'Featured_date' => '',
				'Featured_ID' => '',
				'Cron_featured' => '0'
			);
			$c_expired_featured++;
	
			$update_link = RL_URL_HOME;
			$update_link .= $config['mod_rewrite'] ? $pages['upgrade_listing'] .'/featured.html?id='. $listing['ID'] : '?page='. $pages['upgrade_listing'] .'&amp;id=' . $listing['ID'] . '&amp;featured';
			$update_link = '<a href="'.$update_link.'">'.$update_link.'</a>';

			$copy_status_expired_email = $status_expired_email;
			$copy_status_expired_email['body'] = str_replace(array('{username}', '{renew_link}', '{details_link}' ), array($account_info['Full_name'], $update_link, $details_link), $copy_status_expired_email['body']);
			$copy_status_expired_email['subject'] = str_replace(array('{username}', '{renew_link}', '{details_link}' ), array($account_info['Full_name'], $update_link, $details_link), $copy_status_expired_email['subject']);
			$rlMail -> send( $copy_status_expired_email, $account_info['Mail'] );
		}
		
		/* check pre expire listings */
		if ( $listing['Pay_date'] && ($listing['Cron_notified'] == '0') && ($listing['Pay_date'] + (($listing['Listing_period'] * 86400) - ($expire_days * 86400)) <= time()) && $listing['Listing_period'] > 0 )
		{
			$update[$key]['fields']['Cron_notified'] = '1';
			$expire_date = $listing['Pay_date'] + ($listing['Listing_period'] * 86400);
			$expire_date = date(str_replace('b', 'M', $date_format), $expire_date);
	
			$update_link = RL_URL_HOME;
			$update_link .= $config['mod_rewrite'] ? $pages['upgrade_listing'] . '.html?id=' . $listing['ID'] : $update_page['Path'] . '?page='.$update_page['Path'].'&amp;id=' . $listing['ID'];
			$update_link = '<a href="'.$update_link.'">'.$update_link.'</a>';
	
			$copy_listing_pre_expired = $listing_pre_expired_email;
			$copy_listing_pre_expired['body'] = str_replace(array('{username}', '{days}', '{expire_date}', '{renew_link}', '{details_link}' ), array($account_info['Full_name'], $expire_days, $expire_date, $update_link, $details_link), $copy_listing_pre_expired['body']);
			$copy_listing_pre_expired['subject'] = str_replace(array('{username}', '{days}', '{expire_date}', '{renew_link}', '{details_link}' ), array($account_info['Full_name'], $expire_days, $expire_date, $update_link, $details_link), $copy_listing_pre_expired['subject']);
			$rlMail -> send( $copy_listing_pre_expired, $account_info['Mail'] );
		}
		
		/* check pre expire featured status */
		if ( $listing['Feature_period'] && $listing['Featured_date'] && ($listing['Cron_featured'] == '0') && ($listing['Featured_date'] + (($listing['Feature_period'] * 86400) - ($expire_days * 86400)) <= time()) && $listing['Feature_period'] > 0 )
		{
			$update[$key]['fields']['Cron_featured'] = '1';
			$expire_date = $listing['Featured_date'] + ($listing['Feature_period'] * 86400);
			$expire_date = date(str_replace('b', 'M', $date_format), $expire_date);
	
			$update_link = RL_URL_HOME;
			$update_link .= $config['mod_rewrite'] ? $pages['upgrade_listing'] .'/featured.html?id='. $listing['ID'] : '?page='. $pages['upgrade_listing'] .'&amp;id=' . $listing['ID'] . '&amp;featured';
			$update_link = '<a href="'.$update_link.'">'.$update_link.'</a>';
	
			$copy_status_pre_expired = $status_pre_expired_email;
			$copy_status_pre_expired['body'] = str_replace(array('{username}', '{days}', '{expire_date}', '{renew_link}', '{details_link}' ), array($account_info['Full_name'], $expire_days, $expire_date, $update_link, $details_link), $copy_status_pre_expired['body']);
			$copy_status_pre_expired['subject'] = str_replace(array('{username}', '{days}', '{expire_date}', '{renew_link}', '{details_link}' ), array($account_info['Full_name'], $expire_days, $expire_date, $update_link, $details_link), $copy_status_pre_expired['subject']);
			$rlMail -> send( $copy_status_pre_expired, $account_info['Mail'] );
		}
		
		$rlHook -> load('cronListings');
		
		$update[$key]['fields']['Cron'] = '1';
		$update[$key]['where']['ID'] = $listing['ID'];
		
		unset($copy_listing_expired_email, $copy_status_expired_email, $copy_listing_pre_expired, $copy_status_pre_expired);
	}
	
	if ( !empty($update) )
	{
		$reefless -> loadClass('Actions');
		$rlActions -> update($update, 'listings');
	}
	
	if ( $config['listings_checking_notification'] )
	{
		$admin_email['subject'] = "Cron Job Notification";
		$admin_email['body'] = "
		Cron Job notification at ". date(str_replace('b', 'M', $date_format)) ."<br /><br />
		Listings checked: ".count($listings)."<br />
		Listings expired: ".$c_expired_listings."<br />
		Featured expired: ".$c_expired_featured."
		".$rlHook -> load('cronNotification')."
		";
		
		$rlMail -> send( $admin_email, $config['notifications_email'] );
	}
	
	unset($listings, $update, $account_info, $update_link, $details_link);
}

/* SAVED SEARCH CHECKING */
$sql = "SELECT `ID`, `Account_ID`, `Form_key`, `Listing_type`, `Content`, `Matches` ";
$sql .= "FROM `".RL_DBPREFIX."saved_search` ";
$sql .= "WHERE `Cron` = '0' AND `Status` = 'active' LIMIT {$config['searches_per_run']}";
$searches = $rlDb -> getAll($sql);

if ( empty($searches) )
{
	$rlDb -> query("UPDATE `".RL_DBPREFIX."saved_search` SET `Cron` = '0'");
}
else
{
	/* prefere email notification template */
	$saved_search_email = $rlMail -> getEmailTemplate('cron_saved_search_match');
	
	$reefless -> loadClass('Search');
	$reefless -> loadClass('Common');
	
	foreach ($searches as $key => $search)
	{
		$rlSearch -> getFields( $search['Form_key'], $search['Lising_type'] );
		
		$content = unserialize($search['Content']);
		
		$rlSearch -> exclude = $search['Matches'];
		$matches = $rlSearch -> search($content, $search['Listing_type'], 0, 20);
		$rlSearch -> exclude = false;
		
		$checked_listings = $search['Matches'];
		$exploded_matches = explode(',', $checked_listings);
		
		$update[$key]['fields']['Cron'] = '1';
	
		if ( !empty($matches) )
		{
			foreach ($matches as $match)
			{
				if ( !in_array($match['ID'], $exploded_matches) )
				{
					$listing_type = $rlListingTypes -> types[$match['Listing_type']];
					
					$checked_listings .= empty($checked_listings) ? $match['ID'] : ','.$match['ID'];
					$match_count++;
					
					$link = RL_URL_HOME;
					$link .= $config['mod_rewrite'] ? $pages[$listing_type['Page_key']] .'/'. $match['Path'] .'/'. $rlSmarty -> str2path($match['listing_title']) . '-' . $match['ID'] . '.html' : '?page='. $pages[$listing_type['Page_key']] .'&amp;id=' . $match['ID'];
					$links .= '<a href="'. $link .'">'. $match['listing_title'] .'</a><br />';
					
					$allow_send = true;
				}
			}
			
			if ( $allow_send )
			{
				$update[$key]['fields']['Matches'] = $checked_listings;
				$count = $match_count;
				
				$account_info = $rlAccount -> getProfile((int)$search['Account_ID']);
				
				$copy_notify_email = $saved_search_email;
				$copy_notify_email['body'] = str_replace(array('{username}', '{count}', '{links}' ), array($account_info['Full_name'], $count, $links), $copy_notify_email['body']);
				$copy_notify_email['subject'] = str_replace(array('{username}', '{count}'), array($account_info['Full_name'], $count), $copy_notify_email['subject']);
				$rlMail -> send( $copy_notify_email, $account_info['Mail'] );
			}
		}
		
		$update[$key]['fields']['Cron'] = '1';
		$update[$key]['fields']['Date'] = 'NOW()';
		$update[$key]['where']['ID'] = $search['ID'];
		
		unset($copy_notify_email, $content, $allow_send, $links, $match_count);
	}
	
	if ( $update )
	{
		$reefless -> loadClass('Actions');
		$rlActions -> update($update, 'saved_search');
	}
	
	unset($update);
}

/* MESSAGES CHECKING */
if ( $config['cron_messages_remove'] )
{
	$rlDb -> query("DELETE FROM `". RL_DBPREFIX ."messages` WHERE `Remove` = 'from,to'");
}

/* INCOMPLETE LISTINGS CHECKING */
$sql = "SELECT `T1`.*, UNIX_TIMESTAMP(`T1`.`Date`) AS `Date`, `T2`.`Path` AS `Cat_path`, `T2`.`Type` AS `Listing_type` ";
$sql .= "FROM `".RL_DBPREFIX."listings` AS `T1` ";
$sql .= "LEFT JOIN `".RL_DBPREFIX."categories` AS `T2` ON `T1`.`Category_ID` = `T2`.`ID` ";
$sql .= "WHERE `T1`.`Cron` = '0' AND UNIX_TIMESTAMP(DATE_ADD(`T1`.`Date`, INTERVAL 1 DAY)) < UNIX_TIMESTAMP(NOW()) ";
$sql .= "AND `T1`.`Status` = 'incomplete' ";
$sql .= "LIMIT {$config['listings_number']}";
$listings = $rlDb -> getAll($sql);

if ( $listings )
{
	$incomplete_listing_email = $rlMail -> getEmailTemplate('cron_incomplete_listing');
	$incomplete_listing_removed = 0;
	$in_days = $config['cron_incomplete_listing_days'];
	
	foreach ( $listings as $key => $listing )
	{
		/* remove listing */
		if ( $listing['Cron_notified'] )
		{
			if ( $listing['Date'] + ($in_days * 86400) <= time() )
			{
				$rlListings -> deleteListingData($listing['ID']);
				$rlDb -> query("DELETE FROM `". RL_DBPREFIX ."listings` WHERE `ID` = '{$listing['ID']}' LIMIT 1");

				$incomplete_listing_removed++;
			}
		}
		/* notify owner about the listing */
		else
		{
			$listing_type = $rlListingTypes -> types[$listing['Listing_type']];
			$account_info = $rlAccount -> getProfile((int)$listing['Account_ID']);
			$listing_title = $rlListings -> getListingTitle( $listing['Category_ID'], $listing, $listing['Listing_type'] );
			$hash = $reefless -> generateHash();
			
			$complete_link = RL_URL_HOME;
			$complete_link .= $config['mod_rewrite'] ? $pages[$listing_type['My_key']] .'.html?incomplete='. $listing['ID'] .'&step='. $listing['Last_step']: '?page='. $pages[$listing_type['My_key']] .'&amp;incomplete=' . $listing['ID'] . '&step='. $listing['Last_step'];
			$complete_link = '<a href="'.$complete_link.'">'.$complete_link.'</a>';
			
			$delete_link = RL_URL_HOME;
			$delete_link .= $config['mod_rewrite'] ? $pages['listing_remove'] .'.html?id='. $listing['ID'] .'&hash='. $hash : '?page='. $pages['listing_remove'] .'&amp;id=' . $listing['ID'] . '&hash='. $hash;
			$delete_link = '<a href="'.$delete_link.'">'.$delete_link.'</a>';
			
			$copy_incomplete_listing = $incomplete_listing_email;
			$copy_incomplete_listing['body'] = str_replace(array('{username}', '{listing_title}', '{number_days}', '{complete_link}', '{delete_link}' ), array($account_info['Full_name'], $listing_title, $in_days, $complete_link, $delete_link), $copy_incomplete_listing['body']);
			$rlMail -> send( $copy_incomplete_listing, $account_info['Mail'] );			
			
			$update[$key]['fields']['Cron'] = '1';
			$update[$key]['fields']['Cron_notified'] = '1';
			$update[$key]['fields']['Loc_address'] = md5($hash); //I save the code to this field to avoid unnecessary fileds creation in listings tabse
			$update[$key]['where']['ID'] = $listing['ID'];
			
			unset($complete_link, $delete_link, $copy_incomplete_listing, $account_info, $hash);
		}
	}
	
	if ( !empty($update) )
	{
		$reefless -> loadClass('Actions');
		$rlActions -> update($update, 'listings');
	}
	
	if ( $config['listings_checking_notification'] )
	{
		$admin_email['subject'] = "Cron Job Notification | Incomplete listings";
		$admin_email['body'] = "
		Cron Job notification at ". date(str_replace('b', 'M', $date_format)) ."<br /><br />
		Incomplete listings checked: ".count($listings)."<br />
		Send notification for: ". count($update) ."<br />
		Removed incomplete listings: ". $incomplete_listing_removed;
		
		$rlMail -> send( $admin_email, $config['notifications_email'] );
	}
	
	unset($update, $admin_email, $listings);
}
else
{
	$rlDb -> query("UPDATE `".RL_DBPREFIX."listings` SET `Cron` = '0' WHERE `Status` = 'incomplete';");
}

/* LOGIN ATTEMPTS CLEAR UP */
if ( $config['security_login_attempt_admin_module'] )
{
	$sql = "DELETE FROM `".RL_DBPREFIX."login_attempts` WHERE `Interface` = 'admin' AND TIMESTAMPDIFF(MONTH, `Date`, NOW()) > 2";
	$rlDb -> query($sql);
}
if ( $config['security_login_attempt_user_module'] )
{
	$sql = "DELETE FROM `".RL_DBPREFIX."login_attempts` WHERE `Interface` = 'user' AND TIMESTAMPDIFF(MONTH, `Date`, NOW()) > 2";
	$rlDb -> query($sql);
}

/* run cron hooks  */
$rlHook -> load('cronAdditional');