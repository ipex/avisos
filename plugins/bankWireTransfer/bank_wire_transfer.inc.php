<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LISENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: BANK_WIRE_TRANSFER.INC.PHP
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

$reefless -> loadClass( 'Listings' );
$reefless -> loadClass( 'Actions' );
$reefless -> loadClass( 'Categories' );
$reefless -> loadClass( 'BankWireTransfer', null, 'bankWireTransfer' );

$id = (int)$_REQUEST['item_id'];

$rlSmarty -> assign( 'item_id', $id );

if( $_GET['action'] = 'completed' && !empty($_GET['txn_id']) )
{
	/* get transaction info	*/
	$sql = "SELECT `T1`.*, `T2`.`Item_ID`, `T2`.`Plan_ID`, `T2`.`Total` ";
	$sql .= "FROM `".RL_DBPREFIX."bwt_transactions` AS `T1` ";
	$sql .= "LEFT JOIN `".RL_DBPREFIX."transactions` AS `T2` ON `T1`.`Txn_ID` = `T2`.`Txn_ID` ";
	$sql .= "WHERE `T1`.`ID` = '{$_GET['txn_id']}' ";
	$sql .= "LIMIT 1";

	$txn_info = $rlDb->getRow($sql);
	$rlSmarty -> assign_by_ref( 'txn_info', $txn_info );
	$rlSmarty -> assign( 'txn_id', $txn_info['Txn_ID'] );

	/* get listing info */
	$listing = $rlListings -> getShortDetails( $txn_info['Item_ID'], $plan_info = true );
	$rlSmarty -> assign_by_ref( 'listing', $listing );

	$navIcons[] = '<a title="'. $lang['print_page'] .'" ref="nofollow" class="print" href="'.SEO_BASE.'bwt-print.html?txn_id='.$_GET['txn_id'].'"> <span></span> </a>';
	$rlSmarty -> assign_by_ref( 'navIcons', $navIcons );
}
else
{
	if ( !empty($_POST['type']) )
	{
		$bwt_type = trim( $_POST['type'] );
		$rlSmarty -> assign_by_ref( 'bwt_type', $bwt_type );
	}

	// clear tmp Txn_ID (only for type by check) 
	if( !empty($_SESSION['complete_payment']) )
	{
		unset( $_SESSION['Txn_ID'] );
	}

	if( (empty($_POST['form']) || empty($_POST['txn_id'])) && empty($_SESSION['Txn_ID']) )
	{
		$Txn_ID = $rlBankWireTransfer->generate(!empty($config['bwt_lenght_txn_id']) ? $config['bwt_lenght_txn_id'] : 12);
	}
	else
	{
		if( !empty($_SESSION['Txn_ID']) )
		{
			$Txn_ID = $_SESSION['Txn_ID'];
		}
		else
		{
			$Txn_ID = $_POST['txn_id'];
		}
	}

	$rlSmarty -> assign( 'txn_id', $Txn_ID );

	if ( $bwt_type == 'by_check' )
	{
		/* get transaction info	*/
		$sql = "SELECT `T1`.*, `T2`.`Item_ID`, `T2`.`Plan_ID`, `T2`.`Total`, `T2`.`Service` ";
		$sql .= "FROM `".RL_DBPREFIX."bwt_transactions` AS `T1` ";
		$sql .= "LEFT JOIN `".RL_DBPREFIX."transactions` AS `T2` ON `T1`.`Txn_ID` = `T2`.`Txn_ID` ";
		$sql .= "WHERE `T2`.`Item_ID` = '{$_POST['item_id']}' AND `T1`.`Status` = 'approval' && `T2`.`Service` = '{$_POST['service']}' ";
		$sql .= "LIMIT 1";

		$txn_ready = $rlDb->getRow( $sql );

		if ( !empty($txn_ready) && $txn_ready['order_id'] != session_id() )
		{  
			$errors[] = $lang['bwt_txn_exists'];
	 		$rlSmarty -> assign_by_ref( 'errors', $errors );
		}
		else
	    {
			/* get transaction info	*/
			$sql = "SELECT `T1`.*, `T2`.`Item_ID`, `T2`.`Plan_ID`, `T2`.`Total`, `T2`.`Service` ";
			$sql .= "FROM `".RL_DBPREFIX."bwt_transactions` AS `T1` ";
			$sql .= "LEFT JOIN `".RL_DBPREFIX."transactions` AS `T2` ON `T1`.`Txn_ID` = `T2`.`Txn_ID` ";
			$sql .= "WHERE `T2`.`Item_ID` = '{$_POST['item_id']}' AND `T1`.`order_id` = '".session_id()."' ";
			$sql .= "LIMIT 1";

			$txn_info = $rlDb->getRow( $sql );

			if ( empty($_SESSION['complete_payment']) && empty($txn_info['Txn_ID']) )
			{
				$errors[] = $lang['bwt_session_finished'];
			}

			if ( empty ( $errors ) )
			{
				if (!empty( $txn_info ) )
				{
					$rlSmarty -> assign_by_ref( 'txn_info', $txn_info );	
					$Txn_ID	= $txn_info['Txn_ID'];
				}
				else
				{	
					$sql = "SELECT * FROM `".RL_DBPREFIX."categories` WHERE `ID` = '{$_SESSION['complete_payment']['category_id']}' LIMIT 1";
					$category = $rlDb->getRow( $sql );

					$insert_data = array(
							'IP' => $_SERVER['REMOTE_ADDR'],
							'Txn_ID' => $Txn_ID,
							'Type' => 'by_check',
							'order_id' => session_id(),
							'Item_data' => $_POST['item']
						);

					if ( $rlActions->insertOne( $insert_data, 'bwt_transactions' ) )
					{
						// success
						$rlSmarty -> assign_by_ref( 'txn_data', $insert_data );

						// add common transactions
						$insert_data = array(
								'Date' => 'NOW()',
								'Item_ID' => $_SESSION['complete_payment']['item_id'],
								'Account_ID' => $_SESSION['complete_payment']['account_id'],
								'Plan_ID' => $_SESSION['complete_payment']['plan_info']['ID'],
								'Service' => $category['Type'] == 'listings' ? 'listing' : $category['Type'],
								'Txn_ID' => $Txn_ID,
								'Total' => $_SESSION['complete_payment']['plan_info']['Price'],
								'Gateway' => 'bankWireTransfer',
								'Status' => "active"
							);
							
						if(empty($insert_data['Service']) && $_SESSION['complete_payment']['callback']['plugin'] == 'Invoices' )
						{
							$insert_data['Service'] = 'invoice';
						}

						if ( $rlActions->insertOne( $insert_data, 'transactions' ) )
						{						
							$_SESSION['Txn_ID'] = $Txn_ID;
							unset( $_SESSION['complete_payment'], $insert_data );
						}							
					}
					
					/* get transaction info	*/
					$sql = "SELECT `T1`.*, `T2`.`Item_ID`, `T2`.`Plan_ID`, `T2`.`Total`, `T2`.`Service` ";
					$sql .= "FROM `".RL_DBPREFIX."bwt_transactions` AS `T1` ";
					$sql .= "LEFT JOIN `".RL_DBPREFIX."transactions` AS `T2` ON `T1`.`Txn_ID` = `T2`.`Txn_ID` ";
					$sql .= "WHERE `T1`.`Txn_ID` = '{$Txn_ID}' ";
					$sql .= "LIMIT 1";

					$txn_info = $rlDb->getRow($sql);
					$rlSmarty -> assign_by_ref( 'txn_info', $txn_info );
				}                    

				/* get listing info */
				if($txn_info['Service'] == 'listing')
				{
					$listing = $rlListings -> getShortDetails( $txn_info['Item_ID'], $plan_info = true );
					$rlSmarty -> assign_by_ref( 'listing', $listing );
				}
				                                         
				$rlSmarty -> assign( 'txn_id', $Txn_ID );
				
				$navIcons[] = '<a title="'. $lang['print_page'] .'" class="print" ref="nofollow" href="'.SEO_BASE.'bwt-print.html?txn_id='.$txn_info['ID'].'"> <span></span> </a>';
				$rlSmarty -> assign_by_ref( 'navIcons', $navIcons );
				
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
			else
			{
	 			$rlSmarty -> assign_by_ref( 'errors', $errors );
			}
		}
	}
	else
	{
		if(!empty($id))
		{
			/* get listing info */
			$listing = $rlListings -> getShortDetails( $id, $plan_info = true );
			$rlSmarty -> assign_by_ref( 'listing', $listing );

			$bwt_countries = $rlCategories -> getDF( 'countries' );
			$rlSmarty -> assign_by_ref( 'bwt_country', $bwt_countries );

			/* define listing type */
			$listing_type = $rlListingTypes -> types[$listing['Listing_type']];
			$rlSmarty -> assign_by_ref( 'listing_type', $listing_type );

			$rlSmarty -> assign_by_ref( 'item', $_POST['item'] );

			if ( $_POST['form'] == 'submit' )
			{
				if ( empty( $_POST['bwt']['bank_account_number'] ) )
				{
					$errors[] = str_replace('{field}', "<b>".$lang['bwt_account_number']."</b>", $lang['notice_field_empty']);
				}
				elseif (strlen($_POST['bwt']['bank_account_number']) > 30 || strlen($_POST['bwt']['bank_account_number']) < 8)
				{
					$errors[] = str_replace( '{field}', "<b>".$lang['bwt_bank_account_number']."</b>", $lang['notice_field_incorrect']);
				}
				if (empty($_POST['bwt']['account_name']))
				{
					$errors[] = str_replace( '{field}', "<b>".$lang['bwt_account_name']."</b>", $lang['notice_field_empty']);
				}

				if(empty($errors))
				{
					$data = $_POST['bwt'];
					$data['IP'] = $_SERVER['REMOTE_ADDR'];
					$data['Txn_ID'] = $_POST['txn_id'];
					$data['order_id'] = session_id();
					$data['Type'] = 'write_transfer';
					$data['Item_data'] = $_POST['item'];  

					if( $rlActions->insertOne($data, 'bwt_transactions') )
					{
						$txn_id_tmp = mysql_insert_id();
						// add common transactions
						$insert_data = array(
								'Date' => 'NOW()',
								'Item_ID' => $_SESSION['complete_payment']['item_id'],
								'Account_ID' => $_SESSION['complete_payment']['account_id'],
								'Plan_ID' => $_SESSION['complete_payment']['plan_info']['ID'],
								'Service' => $category['Type'] == 'listings' ? 'listing' : $category['Type'],
								'Txn_ID' => $_POST['txn_id'],
								'Total' => $_SESSION['complete_payment']['plan_info']['Price'],
								'Gateway' => 'bankWireTransfer',
								'Status' => "active"
							);

						if( $rlActions->insertOne($insert_data, 'transactions') )
						{
							unset( $_SESSION['complete_payment'], $insert_data );
						}	                               

						/* redirect */
						$redirect = SEO_BASE;
						$redirect .= $config['mod_rewrite'] ? $pages['bank_wire_transfer'] .'.html?action=completed&txn_id='.$txn_id_tmp : '?page='. $pages['bank_wire_transfer'] . '&action=completed&txn_id='.$txn_id_tmp;
						$reefless -> redirect( null, $redirect );
					}
				}
				else
				{
	 				$rlSmarty -> assign_by_ref( 'errors', $errors );
				}
			}
		}
	}
}
?>