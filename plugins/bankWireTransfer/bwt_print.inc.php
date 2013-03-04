<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LISENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: BWT_PRINT.INC.PHP
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

$languages = $rlLang -> getLanguagesList();
$rlLang -> modifyLanguagesList( $languages );

$reefless -> loadClass( 'Common' );
$reefless -> loadClass( 'Listings' );

if(!empty($_GET['txn_id']))
{
	/* get transaction info	*/
	$sql = "SELECT `T1`.*, `T2`.`Item_ID`, `T2`.`Plan_ID`, `T2`.`Total`, `T2`.`Account_ID` ";
	$sql .= "FROM `".RL_DBPREFIX."bwt_transactions` AS `T1` ";
	$sql .= "LEFT JOIN `".RL_DBPREFIX."transactions` AS `T2` ON `T1`.`Txn_ID` = `T2`.`Txn_ID` ";
	$sql .= "WHERE `T1`.`ID` = '{$_GET['txn_id']}' ";
	$sql .= "LIMIT 1";

	$txn_info = $rlDb->getRow($sql);

	if($txn_info['Account_ID'] == $_SESSION['id'])
   	{                                   
		$rlSmarty -> assign_by_ref( 'txn_info', $txn_info );
		$rlSmarty -> assign( 'txn_id', $txn_info['Txn_ID'] );
		$rlSmarty -> assign( 'bwt_type', $txn_info['Type'] );

		/* get listing info */
		$listing = $rlListings -> getShortDetails( $txn_info['Item_ID'], $plan_info = true );
		$rlSmarty -> assign_by_ref( 'listing', $listing );                              
		           
		/* get payments details */
		if($txn_info['Type'] == 'by_check')
		{
			$sql = "SELECT * FROM `".RL_DBPREFIX."bwt_payment_details` ";
			$payment_details = $rlDb->getAll($sql);
			
			foreach($payment_details as $key => $val)
			{
				$payment_details[$key]['name'] = $lang['payment_details+name+'.$val['Key']];
				$payment_details[$key]['description'] = $lang['payment_details+des+'.$val['Key']];
			}

			$rlSmarty -> assign_by_ref( 'payment_details', $payment_details );
		}
		$rlSmarty->display(RL_PLUGINS . 'bankWireTransfer' . RL_DS . 'bwt_print.tpl');
	}
}

exit();
?>
