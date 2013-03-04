<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LISENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: MY_CREDITS.INC.PHP
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

$account_info_tmp = $rlDb -> fetch( '*', array( 'ID' =>  $account_info['ID'] ), null, 1, 'accounts', 'row' );
$rlSmarty -> assign_by_ref( 'account_info_tmp', $account_info_tmp );

if ( $_GET['nvar_1'] == 'purchase' )
{
	/* add bread crumbs item */
	$bread_crumbs[] = array(
		'name' => $lang['paygc_purchase_credits']
	);
	
	$page_info['name'] = $lang['paygc_purchase_credits'];
	
	if ( $_POST['submit'] )
	{
		if ( !empty($_POST['credits'] ) )
		{
			$credit_id = (int)$_POST['credits'];
		}
		else
		{
			$errors[] = $lang['paygc_empty_credit'];
		}

		$gateway = $_POST['gateway'];

	    if ( empty( $gateway ) )
		{
			$errors[] = $lang['notice_payment_gateway_does_not_chose'];	
		}

		if ( !empty( $errors ) )
		{
			$rlSmarty -> assign_by_ref( 'errors', $errors );
		}
		else
		{			
			$credit_info = $rlDb -> fetch( '*', array( 'ID' =>  $credit_id ), null, 1, 'credits_manager', 'row' );

			$cancel_url = SEO_BASE;
			$cancel_url .= $config['mod_rewrite'] ? $page_info['Path'] . '.html?canceled' : 'index.php?page=' . $page_info['Path'] . '&amp;canceled';

			$success_url = SEO_BASE;
			$success_url .= $config['mod_rewrite'] ? $page_info['Path'] . '.html?completed' : 'index.php?page=' . $page_info['Path'] . '&amp;completed';

			$complete_payment_info = array(
				'item_name' => $credit_info['Credits'] . ' ' . $lang['paygc_credits'],
				'gateway' => $gateway,
				'service' => "credits",
				'item_id' => $credit_id,
				'plan_info' => $credit_info,
				'account_id' => $account_info['ID'],
				'callback' => array(
					'class' => 'rlPayAsYouGoCredits',
					'method' => 'completeTransaction',
					'cancel_url' => $cancel_url,
					'success_url' => $success_url,
					'plugin' => "payAsYouGoCredits"
				)
			);

			$_SESSION['complete_payment'] = $complete_payment_info;

			/* redirect to checkout */
			$redirect = SEO_BASE;
			$redirect .= $config['mod_rewrite'] ? $pages['payment'] .'.html' : 'index.php?page='. $pages['payment'];
			$reefless -> redirect( null, $redirect );
			exit;
		}		
	}

	/* get credits list	*/
   	$sql = "SELECT * FROM `" . RL_DBPREFIX . "credits_manager` WHERE `Status` = 'active' ORDER BY `Price` ASC";
   	$credits = $rlDb -> getAll( $sql );

	foreach ( $credits as $key => $val )
	{
		$credits[$key]['Price_one'] = round( ( $val['Price'] / $val['Credits'] ), 2 );
	}

	$rlSmarty -> assign_by_ref( 'credits', $credits );
}
else
{                   
	/* get expiration date */
	$days = !empty($GLOBALS['config']['paygc_period']) ? (int)$GLOBALS['config']['paygc_period'] * 30 : 30;
	$sql = "SELECT `ID`, `Total_credits`, `paygc_pay_date`, DATE_ADD(`paygc_pay_date`, INTERVAL ".$days." DAY) AS `Expiration_date` FROM `".RL_DBPREFIX."accounts` WHERE `ID` = '{$account_info['ID']}'";
	$credits_expration_data = $rlDb -> getRow($sql);
	
	$rlSmarty -> assign_by_ref( 'credits_expration_data', $credits_expration_data['Expiration_date'] );
	
    // set notifications
	if ( isset( $_GET['completed'] ) )
	{
		$rlSmarty -> assign_by_ref( 'pNotice', $lang['paygc_payment_completed'] );
	}	
	if ( isset( $_GET['canceled'] ) )
	{          
		$errors[] = $lang['paygc_payment_canceled'];
		$rlSmarty -> assign_by_ref( 'errors', $errors );
	}	
}