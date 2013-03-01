<?php

/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: PAYMENT.INC.PHP
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

$payment = $_SESSION['complete_payment'];

$gateway = empty($_POST['gateway']) ? $payment['gateway'] : $_POST['gateway'];
$price = $payment['plan_info']['Price'];
$plan_id = $payment['plan_info']['ID'];
$item_id = $payment['item_id'];
$account_id = $payment['account_id'];
$item_name = $payment['item_name'];
$callback_class = $payment['callback']['class'];
$callback_method = $payment['callback']['method'];
$callback_plugin = $payment['callback']['plugin'];
$cancel_url = $payment['callback']['cancel_url'];
$success_url = $payment['callback']['success_url'];

$rlHook -> load('paymentControllerValidate');

/* return error if payment gateway does not chose */
if ( !$gateway )
{
	$errors[] = $lang['notice_payment_gateway_does_not_chose'];
}

if ( $errors )
{
	$rlSmarty -> assign_by_ref('errors', $errors);
}
else
{
	$rlHook -> load('paymentController');
	
	/* detect gateway type */
	if ( $aHooks[$gateway] )
	{
		$pre_gateway = RL_PLUGINS . $gateway . RL_DS .'controllers'. RL_DS .'pre.gateway.php';
		$post_gateway = RL_PLUGINS . $gateway . RL_DS .'controllers'. RL_DS .'post.gateway.php';
	}
	else
	{
		$pre_gateway = RL_LIBS . 'payment' . RL_DS . $gateway . RL_DS . 'pre_' . $gateway . '.gateway.php';
		$post_gateway = RL_LIBS . 'payment' . RL_DS . $gateway . RL_DS . 'post_' . $gateway . '.gateway.php';
	}

	if ( file_exists( $pre_gateway ) && file_exists( $post_gateway ) )
	{
		echo '
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
		<html>
		<head></head>
		<body>
		';
		
		require_once( $pre_gateway );
	
		/* submit payment form */
		echo "
		<script type='text/javascript'>
			document.forms['payment_form'].submit();
		</script>
		</body>
		</html>
		";
	}
	else
	{
		$errors = $lang['error_payment_gateway_fail'];
		$rlSmarty -> assign_by_ref('errors', $errors);
	}
}
