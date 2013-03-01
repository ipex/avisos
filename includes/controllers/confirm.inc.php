<?php

/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: CONFIRM.INC.PHP
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

/* get account key from GET */ 
$key = $rlValid -> xSql( $_GET['key'] );

/* get account with requested key */
$sql = "SELECT `T1`.`ID`, `T1`.`Status`, `T1`.`Username`, `T1`.`Password`, `T1`.`Password_tmp`, `T1`.`First_name`, `T1`.`Last_name`, `T1`.`Mail`, ";
$sql .= "`T2`.`Email_confirmation`, `T2`.`Admin_confirmation`, `T2`.`Auto_login` ";
$sql .= "FROM `". RL_DBPREFIX ."accounts` AS `T1` ";
$sql .= "LEFT JOIN `". RL_DBPREFIX ."account_types` AS `T2` ON `T1`.`Type` = `T2`.`Key` ";
$sql .= "WHERE `T1`.`Confirm_code` = '{$key}' LIMIT 1";

$rlHook -> load('confirmSql', $sql);

$account = $rlDb -> getRow($sql);

if ( empty($account) || empty($key) )
{
	$sError = true;
}
else
{
	$login_link = $config['mod_rewrite'] ? SEO_BASE . $pages['login'] .'.html' : RL_URL_HOME . '?page='. $pages['login'];

	if ( $account['Status'] == 'incomplete' )
	{
		$reefless -> loadClass('Account');
		$reefless -> loadClass('Mail');
		
		$rlHook -> load('confirmPreConfirm');
		
		$rlAccount -> confirmAccount( $account['ID'], $account );
		
		if ( $account['Auto_login'] && !$account['Admin_confirmation'] )
		{
			$rlAccount -> login( $account['Username'], $account['Password_tmp'] );
			
			$reefless -> loadClass('Notice');
			$rlNotice -> saveNotice($lang['account_confirmed_auto_login']);
			
			$url = SEO_BASE;
			$url .= $config['mod_rewrite'] ? $pages['login'].'.html' : '?page=' .$pages['login'];
			$reefless -> redirect( null, $url );
		}
		
		if ( $account['Admin_confirmation'] && $account['Status'] != 'active' )
		{
			$message = $lang['account_confirmed_pending'];
		}
		else
		{
			$message = str_replace( array( '{link}' ), array( "<a href=\"{$login_link}\">{$lang['here']}</a>" ), $lang['account_confirmed'] );
		}
	}
	else
	{
		if ( $account['Admin_confirmation'] )
		{
			$message = $lang['account_already_confirmed_pending'];
		}
		else
		{
			$message = str_replace( array( '{link}' ), array( "<a href=\"{$login_link}\">{$lang['here']}</a>" ), $lang['account_already_confirmed'] );
		}
	}
	
	$rlSmarty -> assign_by_ref( 'message', $message );
}