<?php

/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: MY_PACKAGES.INC.PHP
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

$sql = "SELECT `T1`.`Listings_remains`, `T1`.`Standard_remains`, `T1`.`Featured_remains`, `T1`.`Date`, `T1`.`IP`, `T1`.`ID`, `T1`.`Plan_ID`, ";
$sql .= "`T2`.`Key`, `T2`.`Featured`, `T2`.`Advanced_mode`, `T2`.`Standard_listings`, `T2`.`Featured_listings`, `T2`.`Price`, `T2`.`Type`, ";
$sql .= "`T2`.`Listing_period`, `T2`.`Plan_period`, `T2`.`Image`, `T2`.`Video`, `T2`.`Listing_number`, `T2`.`Status`, ";
$sql .= "IF (`T2`.`Plan_period` = 0, 'unlimited', UNIX_TIMESTAMP(DATE_ADD(`T1`.`Date`, INTERVAL `T2`.`Plan_period` DAY))) AS `Exp_date`, ";
$sql .= "IF (`T2`.`Plan_period` > 0 AND UNIX_TIMESTAMP(DATE_ADD(`T1`.`Date`, INTERVAL `T2`.`Plan_period` DAY)) < UNIX_TIMESTAMP(NOW()), 'expired', 'active') AS `Exp_status` ";
$sql .= "FROM `". RL_DBPREFIX ."listing_packages` AS `T1` ";
$sql .= "LEFT JOIN `". RL_DBPREFIX ."listing_plans` AS `T2` ON `T1`.`Plan_ID` = `T2`.`ID` ";
$sql .= "WHERE `T1`.`Account_ID` = '{$account_info['ID']}' AND `T1`.`Type` = 'package' ";

$rlHook -> load('myPackagesSql', $sql);

$sql .= "ORDER BY `T1`.`ID` DESC";

$packages = $rlDb -> getAll($sql);
$packages = $rlLang -> replaceLangKeys( $packages, 'listing_plans', array('name', 'des') );

foreach ($packages as $key => $value)
{
	$used_plans_id[] = $value['Plan_ID'];
	$packages_tmp[$value['ID']] = $value;
}
$packages = $packages_tmp;
unset($packages_tmp);

$rlSmarty -> assign_by_ref('packages', $packages);
$rlSmarty -> assign_by_ref('used_plans_id', $used_plans_id);
      
$reefless -> loadClass( 'Notice' );

$rlHook -> load('phpMyPackagesTop');

if ( isset($_GET['completed']) )
{                                               
	$rlNotice -> saveNotice($lang['notice_package_payment_completed']);
}
elseif ( isset($_GET['canceled']) )
{
	$rlNotice -> saveNotice($lang['notice_package_payment_canceled'], 'alert');
}

if ( $_GET['renew'] )
{
	unset($_SESSION['complete_payment']);
	
	$renew_id = (int)$_GET['renew'];
	$pack_info = $packages[$renew_id];
	$my_pack_info = $packages[$renew_id];
	
	if ( !$pack_info )
	{
		$errors[] = $lang['renew_package_not_owner'];
	}
	
	$rlHook -> load('phpMyPackagesRenewValidate');
	
	/* free package mode */
	if ( $pack_info['Price'] <= 0 && !$errors )
	{
		/* renew free package */
		$rlListings -> upgradePackage($renew_id, $pack_info['ID'], $account_info['ID'], 'free', 'free', 0, true);
		
		/* save notice */
		$reefless -> loadClass( 'Notice' );
		$rlNotice -> saveNotice($lang['package_renewed']);
		
		/* redirect */
		$url = SEO_BASE;
		$url .= $config['mod_rewrite'] ? $page_info['Path'] .'.html' : 'index.php?page='. $page_info['Path'];
		$reefless -> redirect(null, $url);
	}
		
	if ( !$errors )
	{
		$rlHook -> load('phpMyPackagesRenewPreAction');
		
		$rlSmarty -> assign_by_ref('renew_id', $renew_id);
		$rlSmarty -> assign_by_ref('pack_info', $pack_info);

		/* add bread crumbs item */
		$bread_crumbs[] = array(
			'name' => $lang['renew'] .' '. $pack_info['name']
		);
		
		$cancel_url = SEO_BASE;
		$cancel_url .= $config['mod_rewrite'] ? $page_info['Path'] . '.html?canceled' : '?page='. $page_info['Path']  . '&canceled';
		
		$success_url = SEO_BASE;
		$success_url .= $config['mod_rewrite'] ? $page_info['Path'] . '.html?completed' : '?page='. $page_info['Path'] .'&completed';
		
		$complete_payment_info = array(
			'item_name' => $pack_info['name'] ." ({$lang['package_plan']})",
			'item_id' => $renew_id,
			'plan_info' => $pack_info,
			'account_id' => $account_info['ID'],
			'callback' => array(
				'class' => 'rlListings',
				'method' => 'upgradePackage',
				'cancel_url' => $cancel_url,
				'success_url' => $success_url
			)
		);
		$_SESSION['complete_payment'] = $complete_payment_info;
	}
}
elseif ( $_GET['nvar_1'] == 'purchase' )
{
	unset($_SESSION['complete_payment']);
	
	/* add bread crumbs item */
	$bread_crumbs[] = array(
		'name' => $lang['purchase_new_package']
	);
	
	$rlSmarty -> assign('purchase', true);
	
	$reefless -> loadClass('Plan');
	
	/* get available plans */
	$available_packages = $rlPlan -> getPlans('package', true);
	foreach ( $available_packages as $key => $value )
	{
		$available_packages_tmp[$value['ID']] = $value;
	}
	$available_packages = $available_packages_tmp;
	unset($available_packages_tmp);
	$rlSmarty -> assign_by_ref('available_packages', $available_packages);
	
	if ( $_POST['action'] == 'submit' )
	{
		$gateway = $_POST['gateway'];
		if ( !$gateway )
		{
			$errors[] = $lang['notice_payment_gateway_does_not_chose'];
		}
		
		$plan_id = $_POST['plan'];
		if ( !$plan_id )
		{
			$errors[] = $lang['no_plan_chose'];
		}
		
		if ( in_array($plan_id, $used_plans_id) )
		{
			$errors[] = $lang['duplicate_package_purchase_error'];
		}
		
		$rlHook -> load('phpMyPackagesPurchaseValidate');
		
		if ( !$errors )
		{
			$plan_info = $available_packages[$plan_id];
			
			$rlHook -> load('phpMyPackagesPurchasePreAction');
			
			// paid plan way
			if ( $plan_info['Price'] > 0 )
			{
				$cancel_url = SEO_BASE;
				$cancel_url .= $config['mod_rewrite'] ? $pages['my_packages'] .'.html?canceled' : 'index.php?page='. $pages['my_packages'] .'&amp;canceled';
				
				$success_url = SEO_BASE;
				$success_url .= $config['mod_rewrite'] ? $pages['my_packages'] .'.html?completed' : 'index.php?page='. $pages['my_packages'] .'&amp;completed';
				
				$complete_payment_info = array(
					'item_name' => $plan_info['name'] ." ({$lang['package_plan']})",
					'item_id' => $plan_id,
					'plan_info' => $plan_info,
					'account_id' => $account_info['ID'],
					'gateway' => $gateway,
					'callback' => array(
						'class' => 'rlListings',
						'method' => 'purchasePackage',
						'cancel_url' => $cancel_url,
						'success_url' => $success_url
					)
				);
				$_SESSION['complete_payment'] = $complete_payment_info;
				
				$url = SEO_BASE;
				$url .= $config['mod_rewrite'] ? $pages['payment'] .'.html' : 'index.php?page='. $pages['payment'];
				$reefless -> redirect(null, $url);
			}
			// free plan way
			else
			{
				/* purchace free package */
				$rlListings -> purchasePackage($plan_id, $plan_id, $account_info['ID'], 'free', 'free', 0, true);
				
				/* save notice */
				$reefless -> loadClass( 'Notice' );
				$rlNotice -> saveNotice($lang['free_package_purchase_notice']);
				
				/* redirect */
				$url = SEO_BASE;
				$url .= $config['mod_rewrite'] ? $page_info['Path'] .'.html' : 'index.php?page='. $page_info['Path'];
				$reefless -> redirect(null, $url);
			}
		}
	}
}

$rlHook -> load('phpMyPackagesBottom');