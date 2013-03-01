<?php

/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: CONTROL.INC.PHP
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

session_start();

require_once( RL_CLASSES . 'rlDb.class.php' );
require_once( RL_CLASSES . 'reefless.class.php' );

$rlDb = new rlDb();
$reefless = new reefless();

/* load classes */
$reefless -> connect(RL_DBHOST, RL_DBPORT, RL_DBUSER, RL_DBPASS, RL_DBNAME);
$reefless -> loadClass( 'Debug' );
$reefless -> loadClass( 'Config' );
$reefless -> loadClass( 'Lang' );
$reefless -> loadClass( 'Valid' );
$reefless -> loadClass( 'Hook' );
$reefless -> loadClass( 'Listings' );
$reefless -> loadClass( 'Categories' );

if ( !isset($_SERVER['SHELL']) )
{
	/* load ajax library */
	require_once( RL_AJAX . 'xajax_core' . RL_DS . 'xajax.inc.php' );
	
	$rlXajax = new xajax();
	$_response = new xajaxResponse();
	$GLOBALS['_response'] = $_response;
	
	$rlXajax -> configure('javascript URI', RL_URL_HOME . 'libs/ajax/' );
	$rlXajax -> configure('debug', RL_AJAX_DEBUG);
	
	$rlXajax -> setCharEncoding( 'UTF-8' );
	/* ajax library end */
}

/* load system configurations */
$config = $rlConfig -> allConfig();
$GLOBALS['config'] = $config;

/* load smarty library */
require_once( RL_LIBS . 'smarty' . RL_DS . 'Smarty.class.php' );
$reefless -> loadClass( 'Smarty' );

/* assign configs to template */
$rlSmarty -> assign_by_ref( 'config', $config );

define('RL_SETUP', 'JGxpY2Vuc2VfZG9tYWluID0gImF2aXNvcy5jb20uYm8iOyRsaWNlbnNlX251bWJlciA9ICJGTDQzSzU2NTNXMkkiOw==');

/* gpc checking */
if (get_magic_quotes_gpc())
{
	if ( isset($_SERVER['CONTENT_TYPE']) )
	{
		$in = array(&$_GET, &$_POST);
		while (list($k,$v) = each($in))
		{
			foreach ($v as $key => $val)
			{
				if (!is_array($val)) {
					$in[$k][$key] = str_replace(array("\'", '\"'), array( "'", '"'), $val);
					continue;
				}
				$in[] = &$in[$k][$key];
			}
		}
		unset($in);
	}
}

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
