<?php

/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: JSONRESPONSE.PHP
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

/* system config */
require_once( '../../includes/config.inc.php' );
require_once( RL_ADMIN_CONTROL . 'ext_header.inc.php' );

/* load system lib */
require_once( RL_LIBS . 'system.lib.php' );

switch( $_GET['q'] )
{
	case 'phrase':
		$key = $_GET['key'];
		$lang = $_GET['lang'];

		$output = $rlDb -> getOne('Value', "`Key` = '{$key}' AND `Code` = '{$lang}'", 'lang_keys');
		break;

	case 'accounts':
		$str = $_GET['str'];
		$fields = $_GET['add_id'] ? ', `ID`' : '';
		$output = array();

		if ( !empty( $str ) )
		{
			$sql = "SELECT `Username` {$fields} FROM `". RL_DBPREFIX ."accounts` WHERE `Username` REGEXP '^{$str}' AND `Status` ='active'";
			$output = $rlDb -> getAll($sql);
		}
		break;

	default:
		exit;
		break;
}

$rlHook -> load('apPhpJsonResponse');

$reefless -> loadClass( 'Json' );
echo $rlJson -> encode( $output );