<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LISENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: CRONADDITIONAL.PHP
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

global $rlDb;

if ( $GLOBALS['config']['paygc_period'] > 0 )
{
	$days = (int)$GLOBALS['config']['paygc_period'] * 30 * 24;
	$sql = "SELECT `ID`, `Total_credits`, IF(TIMESTAMPDIFF(HOUR, `paygc_pay_date`, NOW()) > ".$days.", '1', '0') `expired` FROM `".RL_DBPREFIX."accounts` WHERE `Status` = 'active' ";
	$accounts = $GLOBALS['rlDb'] -> getAll( $sql );

	foreach( $accounts as $key => $account )
	{
		if ( $account['expired'] )
		{
			$sql_update = "UPDATE `" . RL_DBPREFIX . "accounts` SET `Total_credits` = '0', `paygc_pay_date` = '0000-00-00 00:00:00' WHERE `{$account['ID']}`";
			$GLOBALS['rlDb'] -> query( $sql_update );
		}
	}
}