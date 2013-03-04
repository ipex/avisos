<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LISENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: APPHPACCOUNTSAFTEREDIT.PHP
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

global $rlDb, $profile_data;

$id = (int)$_GET['account'];

if ( isset( $_POST['Total_credits'] ) )
{
	$balance = (float)$_POST['Total_credits'];

	$sql_update = "UPDATE `" . RL_DBPREFIX . "accounts` SET `Total_credits` = '{$balance}', `paygc_pay_date` = NOW() WHERE `ID` = '{$id}'";
	$rlDb->query($sql_update );
}