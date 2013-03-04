<?php
/* copyright */

/* get invoice info */
if ( $_SESSION['complete_payment']['callback']['plugin'] == 'Invoices' )
{   
	$sql = "SELECT `ID`,`Txn_ID`,`Account_ID` FROM `".RL_DBPREFIX."invoices` WHERE `Account_ID` = '{$account_info['ID']}' AND `Txn_ID` = '{$_SESSION['complete_payment']['item_id']}' LIMIT 1";
	$invoice_info = $rlDb -> getRow( $sql );

	$_SESSION['complete_payment']['item_id'] = $invoice_info['ID'];
	$item_id = $invoice_info['ID'];
	
	unset($invoice_info);
}

$data = $plan_id .'|'. $item_id .'|'. $account_id .'|'. $price .'|'. $callback_class .'|'. $callback_method .'|'. RL_LANG_CODE .'|'. $callback_plugin; // $callback_plugin from v4.0.2;
$item = base64_encode($data);

if(!empty($_SESSION['bwt_type']))
{
	$bwt_type = $_SESSION['bwt_type'];
	unset($_SESSION['bwt_type']);
}
else
{
	$bwt_type = null;
}
?>
<form action="<?php echo SEO_BASE . $pages['bank_wire_transfer'] . '.html';  ?>" method="POST" name="payment_form">
	<input type="hidden" name="total" value="<?php echo $price; ?>" />
	<input type="hidden" name="form" value="payment" />
	<input type="hidden" name="item_id" value="<?php echo $item_id; ?>" />
	<input type="hidden" name="service" value="<?php echo $service; ?>" />
	<input type="hidden" name="plan_id" value="<?php echo $plan_id; ?>" />
	<input type="hidden" name="type" value="<?php echo $bwt_type; ?>" />
	<input type="hidden" name="item" value="<?php echo $item; ?>" />
</form>