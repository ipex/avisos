<?php

/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: PRE_PAYPAL.GATEWAY.PHP
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

$crypted_price = crypt(sprintf("%.2f",$price), $config['paypal_secret_word']);

$data = $plan_id .'|'. $item_id .'|'. $account_id .'|'. $crypted_price .'|'. $callback_class .'|'. $callback_method .'|'. RL_LANG_CODE .'|'. $callback_plugin; // $callback_plugin from v4.0.2
$item = base64_encode($data);

$notify_url = RL_LIBS_URL . 'payment/paypal/post_paypal.gateway.php';
$host = $config['paypal_sandbox'] ? 'sandbox.paypal.com' : 'www.paypal.com';

/* generate payment form */
?>
<form name="payment_form" action="https://<?php echo $host; ?>/cgi-bin/webscr" method="post">
	<input type="hidden" name="item_number" value="<?php echo str_replace(' ', '+', $item); ?>" />
	<input type="hidden" name="currency_code" value="<?php echo $config['paypal_currency_code']; ?>" />
	<input type="hidden" name="business" value="<?php echo $config['paypal_account_email']; ?>" />
	<input type="hidden" name="cmd" value="_xclick" />
	<input type="hidden" name="no_shipping" value="1" />
	<input type="hidden" name="item_name" value="<?php echo $item_name; ?>" />
	<input type="hidden" name="amount" value="<?php echo $price; ?>" />
	<input type="hidden" name="return" value="<?php echo $success_url;?>" />
	<input type="hidden" name="cancel_return" value="<?php echo $cancel_url;?>" />
	<input type="hidden" name="notify_url" value="<?php echo $notify_url; ?>" />
	<input type="hidden" name="rm" value="2" />
	<input type="hidden" name="charset" value="utf-8">
	<input type="hidden" name="image_url" value="<?php echo RL_TPL_BASE ?>img/logo.png">
</form>