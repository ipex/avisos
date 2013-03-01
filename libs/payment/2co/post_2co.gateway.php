<?php

/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: POST_2CO.GATEWAY.PHP
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

if ($_SERVER['REQUEST_METHOD'] == 'POST' || $_SERVER['REQUEST_METHOD'] == 'post')
{
	require_once('../../../includes/config.inc.php');
	
	/* system controller */
	require_once( RL_INC . 'control.inc.php' );
	
	/* load system configurations */
	$config = $rlConfig -> allConfig();
	$errors = false;

	if (!empty($_POST['item_number']))
	{
		$items = explode('|', base64_decode(urldecode($_POST['item_number'])));
		$plan_id = $items[0];
		$item_id = $items[1];
		$account_id = $items[2];
		$crypted_sum = $items[3];
		$callback_class = $items[4];
		$callback_method = $items[5];
		$cancel_url = $items[6];
		$success_url = $items[7];
		$lang_code = $items[8];
		$callback_plugin = $items[9] ? $items[9] : false; // from v4.0.2
		
		define( 'RL_LANG_CODE', $lang_code );
		define( 'RL_DATE_FORMAT', $rlDb -> getOne('Date_format', "`Code` = '{$config['lang']}'", 'languages') );
		
		$seo_base = RL_URL_HOME;
		$seo_base .= $lang_code == $config['lang'] ? '' : $lang_code;
		
		$lang = $rlLang -> getLangBySide( 'frontEnd', RL_LANG_CODE );
		$GLOBALS['lang'] = $lang;

		$total = $_POST['total'];

		// Check crypted sum
		if ( strcmp($crypted_sum, crypt(sprintf("%.2f", $total), str_replace('http://', '', RL_URL_HOME))) != 0 )
		{
			// Exit since crypted sum is invalid
			$errors = true;
		}
		
		if ( empty($item_id) || empty($plan_id) || empty($total) )
		{
			$errors = true;
		}

		if ( !$errors )
		{
			// If IPN processing script gets to this point it means
			// everything went smoothly and we can update listing status
			$txn_id = $_POST['cart_id'];
	
			if ( $callback_plugin ) // from v4.0.2
			{
				$reefless -> loadClass(str_replace('rl', '', $callback_class), null, $callback_plugin);
			}
			else
			{
				$reefless -> loadClass(str_replace('rl', '', $callback_class));
			}
			$$callback_class -> $callback_method( $item_id, $plan_id, $account_id, $txn_id, 'paypal', $total );
			
			$reefless -> redirect(null, $success_url);
		}
		else
		{
			$reefless -> redirect(null, $cancel_url);
		}
	}
}