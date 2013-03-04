<?php
/* copyright */
  
global $page_info, $rlDb, $rlLang, $transactions, $rlSmarty, $lang;

if( $page_info['Controller'] == 'payment_history' )
{ 
	$txn_ids = array();
	$tmp_transactions = array();

	/* get payments details */
	$sql = "SELECT * FROM `".RL_DBPREFIX."bwt_payment_details` ";
	$payment_details = $rlDb->getAll($sql);
	
	foreach($payment_details as $key => $val)
	{
		$payment_details[$key]['name'] = $lang['payment_details+name+'.$val['Key']];
		$payment_details[$key]['description'] = $lang['payment_details+des+'.$val['Key']];
	}

	$rlSmarty -> assign_by_ref( 'payment_details', $payment_details );

	foreach( $transactions as $key => $val )
	{
		$txn_ids[] = $val['ID'];
	}

	/* get bwt transaction info	*/
	$sql = "SELECT `T1`.*, `T2`.`Item_ID`, `T2`.`Plan_ID`, `T2`.`Total`, `T2`.`Gateway` ";
	$sql .= "FROM `".RL_DBPREFIX."bwt_transactions` AS `T1` ";
	$sql .= "LEFT JOIN `".RL_DBPREFIX."transactions` AS `T2` ON `T1`.`Txn_ID` = `T2`.`Txn_ID` ";
	$sql .= "WHERE `T2`.`Gateway` = 'bankWireTransfer' AND (`T2`.`ID` = '" . implode("' OR `T2`.`ID` = '", $txn_ids) . "')";
	
	$tmp_transactions = $rlDb->getAll($sql);

	foreach( $tmp_transactions as $key => $val )
	{
		$bwt_transactions[$val['Txn_ID']] = $val;
	}
	
	unset( $tmp_transactions, $sql, $txn_ids );

	$rlSmarty -> assign_by_ref( 'bwt_transactions', $bwt_transactions );
}
?>
