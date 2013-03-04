<?php
/* copyright */

if ( $_SERVER['REQUEST_METHOD'] == 'POST' || $_SERVER['REQUEST_METHOD'] == 'post' )
{
	require_once( '../../../includes/config.inc.php' );

	/* system controller */
	require_once( RL_INC . 'control.inc.php' );

	$reefless->loadClass( 'Cache' );

	/* load system configurations */
	$config = $rlConfig->allConfig();
	
	if ( !empty( $_POST['item_number'] ) )
	{                              
		$items = explode( '|', base64_decode( urldecode( $_POST['item_number'] ) ) ); 

		$plan_id = $items[0];
		$item_id = $items[1];
		$account_id = $items[2];
		$crypted_sum = $items[3];
		$callback_class = $items[4];
		$callback_method = $items[5];
		$cancel_url = $items[6];
		$success_url = $items[7];
		$lang_code = $items[8];
		$plugin = $items[9]; // $plugin from v4.0.2
                                                                                                       
		$total = $_POST['total'];   
		$txn_id = $reefless -> generateHash( 10, 'upper' );

		define( 'RL_LANG_CODE', $lang_code );

		$seo_base = RL_URL_HOME;
		$seo_base .= $lang_code == $config['lang'] ? '' : $lang_code . '/';

		$lang = $rlLang -> getLangBySide( 'frontEnd', RL_LANG_CODE );
		$GLOBALS['lang'] = $lang;

        if ( !empty( $GLOBALS['config']['paygc_rate_common'] ) )
		{
			$total = round( ( $total / $GLOBALS['config']['paygc_rate_common'] ), 2 );
		} 
		elseif ( !empty( $GLOBALS['config']['paygc_rate_hide'] ) )
		{
			$total = round( ( $total / $GLOBALS['config']['paygc_rate_hide'] ), 2);
		}

		if ( empty( $item_id ) || empty( $total ) )
		{
			$errors = true;
		}
		
		$account_info = $rlDb -> fetch( array( 'ID', 'Total_credits' ), array( 'ID' => $account_id ), null, 1, 'accounts', 'row' );

		if ( $total > $account_info['Total_credits'] )
		{
			$errors = true;
		}

		if ( !$errors ) 
		{
			$reefless -> loadClass( str_replace( 'rl', '', $callback_class ), null, $plugin );
			$$callback_class -> $callback_method( $item_id, $plan_id, $account_id, $txn_id, 'payAsYouGoCredits', $total );

			/* update account */
			$sql = "UPDATE `" . RL_DBPREFIX . "accounts` SET `Total_credits` = `Total_credits` - {$total} WHERE `ID` = '{$account_id}'";
			$rlDb -> query( $sql );
			
			$reefless -> redirect( false, str_replace( '&amp;', '&', $success_url ) );
		}   
		else
		{
			$reefless -> redirect( false, str_replace( '&amp;', '&', $cancel_url ) );
		}                       
	}
}