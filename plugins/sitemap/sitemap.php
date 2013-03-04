<?php
/**copyrights**/

require_once( '../../includes/config.inc.php' );
require_once( RL_INC . 'control.inc.php' );
require_once( RL_LIBS . 'system.lib.php' );

/* load system configurations */
$config = $rlConfig -> allConfig();
$GLOBALS['config'] = $config;

$lang = $rlLang -> getLangBySide( 'frontEnd', $config['lang'] );
$GLOBALS['lang'] = &$lang;

define( 'RL_LANG_CODE', $config['lang'] );
                               
$reefless -> loadClass( 'Categories' );

/* prefere email templates */
$reefless -> loadClass( 'Navigator' );
$reefless -> loadClass( 'Listings' );
$reefless -> loadClass( 'Cache' );
$reefless -> loadClass( 'ListingTypes' );

$pages = $rlNavigator -> getAllPages();

$reefless -> loadClass( 'Sitemap', null, 'sitemap' ); 
                                                            
if ( !empty( $_GET['mod'] ) && is_numeric( $_GET['mod'] ) )
{
	$_GET['number'] = (int)$_GET['mod'];
	$_GET['mod'] = false;
}

!isset( $_GET['number'] ) ? $_GET['number'] = false : null;
!isset( $_GET['mod'] ) ? $_GET['mod'] = false : null;

/* send headers */
header( "Content-Type: text/xml; charset=utf-8" );
         
if ( !function_exists('loadUTF8functions' ) )
{
	function loadUTF8functions()
	{
		$names = func_get_args();

		if ( empty( $names ) )
		{
			return false;
		}
		
		foreach ( $names as $name )
		{
			if ( file_exists( RL_LIBS . 'utf8' . RL_DS . 'utils' . RL_DS . $name . '.php' ) )
			{
				require_once( RL_LIBS . 'utf8' . RL_DS . 'utils' . RL_DS . $name . '.php' );
			}
		}
	}
}

print( $rlSitemap -> build( $_GET['search'], $_GET['number'], $_GET['mod'] ) );