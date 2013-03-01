<?php

/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: EXT_HEADER.INC.PHP
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

/* define interface */
define( 'REALM', 'admin' );
	
require_once( RL_CLASSES . 'rlDb.class.php' );
require_once( RL_CLASSES . 'reefless.class.php' );

$rlDb = new rlDb();
$reefless = new reefless();

session_start();

/* check session status */
if ( !$reefless -> checkSessionExpire() )
{
	echo 'session_expired';
	exit;
}

/* load classes */
$reefless -> connect(RL_DBHOST, RL_DBPORT, RL_DBUSER, RL_DBPASS, RL_DBNAME);
$reefless -> loadClass('Debug');
$reefless -> loadClass('Valid');
$reefless -> loadClass('Lang');
$reefless -> loadClass('Config');
$reefless -> loadClass('Hook');

$reefless -> loadClass( 'Admin', 'admin' );

/* check is login */
if (!$rlAdmin -> isLogin())
{
	exit;
}

/* get configs */
$config = $rlConfig -> allConfig();
$rlLang -> extDefineLanguage();

/* site languages array generation */
$lang = $rlLang -> getLangBySide( 'admin', RL_LANG_CODE, 'all' );

require_once( RL_LIBS . 'system.lib.php' );

/* set timezone */
$reefless -> setTimeZone();

$reefless -> loadClass('Cache');
$reefless -> loadClass('ListingTypes');

/* utf8 library functions */
function loadUTF8functions()
{
	$names = func_get_args();
	
	if ( empty($names) )
	{
		return false;
	}
	
	foreach ( $names as $name )
	{
		if (file_exists( RL_LIBS . 'utf8' . RL_DS . 'utils' . RL_DS . $name . '.php' ))
		{
			require_once( RL_LIBS . 'utf8' . RL_DS . 'utils' . RL_DS . $name . '.php' );
		}
	}
}

$rlHook -> load('apExtHeader');
