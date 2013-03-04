<?php
/* Copyright */

/* ext js action */
if ( $_GET['q'] == 'ext' )
{
	// system config
	require_once( '../../../includes/config.inc.php' );
	require_once( RL_ADMIN_CONTROL .'ext_header.inc.php' );
	require_once( RL_LIBS .'system.lib.php' );
	
	/* date update */
	if ($_GET['action'] == 'update' )
	{
		$reefless -> loadClass( 'BankWireTransfer', null, 'bankWireTransfer');
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
		
		$rlActions -> updateOne( $updateData, 'bwt_transactions');

		if($field == 'Status')
		{
			if($value == 'active')
			{
				$sql = "SELECT `T1`.*, `T2`.`Item_ID`, `T2`.`Plan_ID`, `T2`.`Total` ";
				$sql .= "FROM `".RL_DBPREFIX."bwt_transactions` AS `T1` ";
				$sql .= "LEFT JOIN `".RL_DBPREFIX."transactions` AS `T2` ON `T1`.`Txn_ID` = `T2`.`Txn_ID` ";
				$sql .= "WHERE `T1`.`ID` = '{$id}' ";
				$sql .= "LIMIT 1";

				$txn = $rlDb->getRow($sql);
				$rlBankWireTransfer->completeTransaction($txn);	
			}
		}
		exit;
	}

	/* data read */
	$limit = $rlValid -> xSql( $_GET['limit'] );
	$start = $rlValid -> xSql( $_GET['start'] );

	$sql = "SELECT SQL_CALC_FOUND_ROWS `T1`.*, `T2`.`Item_ID`, `T2`.`Account_ID`, `T2`.`Total`, `T2`.`Plan_ID`, `T2`.`Date`, `T2`.`Service`, `T3`.`title`, `T4`.`Username`, `T5`.`Price` AS `pPrice`, `T5`.`Key` AS `pKey` ";
	$sql .= "FROM `" . RL_DBPREFIX . "bwt_transactions` AS `T1` ";
	$sql .= "LEFT JOIN `" . RL_DBPREFIX . "transactions` AS `T2` ON `T1`.`Txn_ID` = `T2`.`Txn_ID` ";
	$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listings` AS `T3` ON `T2`.`Item_ID` = `T3`.`ID` ";
	$sql .= "LEFT JOIN `" . RL_DBPREFIX . "accounts` AS `T4` ON `T2`.`Account_ID` = `T4`.`ID` ";
	$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_plans` AS `T5` ON `T2`.`Plan_ID` = `T5`.`ID` ";

    $sql .= "WHERE `T1`.`Status` <> 'trash' ";
	$sql .= "ORDER BY `T2`.`Date` DESC LIMIT {$start}, {$limit}";

	$data = $rlDb -> getAll( $sql );                                                                                                      
	$count = $rlDb -> getRow( "SELECT FOUND_ROWS() AS `count`" );

	foreach($data as $key => $val)
	{
		if($val['Service'] == 'invoice')
		{
			$sql_inv = "SELECT `ID`,`Txn_ID`,`Subject` FROM `".RL_DBPREFIX."invoices` WHERE `ID` = '{$val['Item_ID']}' LIMIT 1";
			$invoice_info = $rlDb -> getRow( $sql_inv );
			
			$data[$key]['Item'] = $invoice_info['Subject'] . '(#'.$invoice_info['ID'].')'; 
		}
		else
		{
			$plan_name = $lang['listing_plans+name+'.$data[$key]['pKey']];
			$data[$key]['Item'] = $plan_name . " (#{$data[$key]['Item_ID']})|". $plan_name; 
		}
		      
		$data[$key]['Type'] = $GLOBALS['lang'][$data[$key]['Type']];
		$data[$key]['Status'] = $GLOBALS['lang'][$data[$key]['Status']];
	}

	$output['total'] = $count['count'];
	$output['data'] = $data;

	$reefless -> loadClass( 'Json' );
	echo $rlJson -> encode( $output );
	exit();
}

$reefless -> loadClass( 'BankWireTransfer', null, 'bankWireTransfer' );

if ( isset( $_GET['action'] ) )
{
	// get all languages
	$allLangs = $GLOBALS['languages'];
	$rlSmarty -> assign_by_ref( 'allLangs', $allLangs );

	if($_GET['action'] == 'view')
	{
		$bcAStep = $lang['bwt_view_details']; 

		/* get transaction info	*/
		$sql = "SELECT `T1`.*, `T2`.`Item_ID`, `T2`.`Plan_ID`, `T2`.`Total`, `T2`.`Service` ";
		$sql .= "FROM `".RL_DBPREFIX."bwt_transactions` AS `T1` ";
		$sql .= "LEFT JOIN `".RL_DBPREFIX."transactions` AS `T2` ON `T1`.`Txn_ID` = `T2`.`Txn_ID` ";
		$sql .= "WHERE `T1`.`ID` = '{$_GET['txn_id']}' ";
		$sql .= "LIMIT 1";

		$txn_info = $rlDb->getRow( $sql );	
		$rlSmarty -> assign_by_ref( 'txn_info', $txn_info );
		
		/* get listing info */
		if($txn_info['Service'] == 'listing')
		{
			$reefless -> loadClass( 'Listings' );
			$listing = $rlListings -> getShortDetails( $txn_info['Item_ID'], $plan_info = true );
			$rlSmarty -> assign_by_ref( 'listing', $listing );
		}

		if($txn_info['Type'] == 'by_check')
		{
			/* get payments details */
			$sql = "SELECT * FROM `".RL_DBPREFIX."bwt_payment_details` ";
			$payment_details = $rlDb->getAll($sql);
			
			foreach($payment_details as $key => $val)
			{
				$payment_details[$key]['name'] = $lang['payment_details+name+'.$val['Key']];
				$payment_details[$key]['description'] = $lang['payment_details+des+'.$val['Key']];
			}

			$rlSmarty -> assign_by_ref( 'payment_details', $payment_details );  
		}
	}
}

if ( isset( $_GET['module'] ) )
{
	if ( is_file( RL_PLUGINS .'bankWireTransfer'. RL_DS .'admin'. RL_DS . $_GET['module'] .'.inc.php' ) )
	{
		require_once( RL_PLUGINS .'bankWireTransfer'. RL_DS .'admin'. RL_DS . $_GET['module'] .'.inc.php' );
	}
	else
	{
		$sError = true;
	}
}
else
{
	/* register ajax methods */	                                                                                
	$rlXajax -> registerFunction( array( 'deleteTransaction', $rlBankWireTransfer, 'ajaxDeleteTransaction' ) );
} 
