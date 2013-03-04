<?php
	
/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LISENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: RLBANKWIRETRANSFER.CLASS.PHP
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

	class rlBankWireTransfer extends reefless 
	{
		function rlBankWireTransfer()
		{
		}

		function completeTransaction( $txn )
		{
			global $rlDb;

			if ( !empty( $txn ) )
			{
				$items = explode('|', base64_decode(urldecode($txn['Item_data'])));

				$plan_id = $items[0];
				$item_id = $items[1];
				$account_id = $items[2];
				$callback_class = $items[4];
				$callback_method = $items[5];
				$plugin = $items[7];

				// remove tmp transaction
				$sql = "DELETE FROM `".RL_DBPREFIX."transactions` WHERE `Txn_ID` = '{$txn['Txn_ID']}' LIMIT 1";
				$txn_info = $rlDb->query( $sql );

				$this -> loadClass(str_replace('rl', '', $callback_class), null, $plugin);
				$GLOBALS[$callback_class] -> $callback_method( $item_id, $plan_id, $account_id, $txn['Txn_ID'], 'bankWireTransfer', $txn['Total'] );
			}
		}    

		function generate( $number = 8 ) 
		{
			//$laters = range('a', 'z');
			$laters = range('A', 'Z');

			for ( $i = 0; $i < $number; $i++ ) 
			{
				$step = rand(1, 2);

				if( $step == 1 ) 
				{
					$out .= rand(0, 9);
				}
				elseif ( $step == 2 ) 
				{
					$index = rand( 0, count( $laters ) - 1 );
					$out .= $laters[$index];
				}
			}

			return $out;
		}

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
				$GLOBALS['rlActions'] -> delete( array( 'ID' => $id ), array( 'bwt_transactions' ), $id, null, $id, false );
			}
			else
			{
				$ids = explode('|', $id );
				foreach ( $ids as $id )
				{
					$GLOBALS['rlActions'] -> delete( array( 'ID' => $id ), array( 'bwt_transactions' ), $id, null, $id, false );
				}
			}

			$del_mode = $GLOBALS['rlActions'] -> action;
			
			$_response -> script("
				bwtTransactionsGrid.reload();
				bwtTransactionsGrid.checkboxColumn.clearSelections();
				bwtTransactionsGrid.actionsDropDown.setVisible(false);
				bwtTransactionsGrid.actionButton.setVisible(false);
				printMessage('notice', '{$lang['transaction_' . $del_mode]}');
			");

			return $_response;
		}

		function ajaxDeleteItem( $id = false )
		{
			global $_response, $lang;

			$delete = "DELETE FROM `". RL_DBPREFIX ."payment_details` WHERE `ID` = '{$id}' LIMIT 1";
			$this -> query($delete);

			// print message, update grid
			$_response -> script("
				bwtPaymentDetails.reload();
				printMessage('notice', '{$lang['item_deleted']}');
				$('#delete_block').slideUp();
			");

			return $_response;
		}
	} 
?>
