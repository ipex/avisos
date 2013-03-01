<?php

/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: INDEX.PHP
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

/* print system information */
if (isset($_GET['system_info']))
{
	phpinfo();
	exit;
}

/* system config */
require_once( '..'.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'config.inc.php' );

/* system controller */
require_once( RL_ADMIN_CONTROL . 'admin.control.inc.php' );

/* www prefix detecting */
$reefless -> wwwRedirect(true);

$rlHook -> load('apBoot');

/* system configurations load */
$config = $rlConfig -> allConfig();
$rlSmarty -> assign_by_ref( 'config', $config );

/* load cache handler */
$reefless -> loadClass( 'Cache' );

/* load template settings */
$st_path = RL_ROOT . 'templates' . RL_DS . $config['template'] . RL_DS . 'settings.tpl.php';
if ( is_readable( $st_path ) )
{
	require_once( $st_path );
}

/* define site languages */
$rlDb -> setTable( 'languages' );
$languages = $rlLang -> getLanguagesList();
$rlLang -> defineLanguage( $_GET['language'] );
$rlLang -> modifyLanguagesList( $languages );

/* site languages array generation */
$lang = $rlLang -> getLangBySide( 'admin', RL_LANG_CODE );
$GLOBALS['lang'] = $lang;
$rlSmarty -> assign_by_ref( 'lang', $lang );

/* login attempts control */
$reefless -> loginAttempt(true);

/* load system lib */
require_once( RL_LIBS . 'system.lib.php' );

/* set timezone */
$reefless -> setTimeZone();

/* get all pages keys/paths */
$pages = $GLOBALS['pages'] = $rlAdmin -> getAllPages();
$rlSmarty -> assign_by_ref( 'pages', $pages );

/* site Ext JS languages array generation */
$ext_phrases = $rlLang -> getLangBySide( 'ext', RL_LANG_CODE );
$rlSmarty -> assign_by_ref( 'ext_phrases', $ext_phrases );

/* assign base path */
$rlSmarty -> assign( 'rlBase', RL_URL_HOME . ADMIN . '/' );
$rlSmarty -> assign( 'rlBaseC', RL_URL_HOME . ADMIN . '/index.php?controller='.$_GET['controller'].'&amp;' );
$rlSmarty -> assign( 'rlTplBase', RL_URL_HOME . ADMIN . '/' );
define( 'RL_TPL_BASE', RL_URL_HOME . ADMIN . '/' );

/* check admin user authorization */
if ( !$rlAdmin -> isLogin() )
{
	// select all languages
	$rlSmarty -> assign_by_ref( 'languages', $languages );
	$rlSmarty -> assign( 'langCount', count($languages) );
	
	//display login form
	$reefless -> loadClass( 'Admin', 'admin' );
	
	/* ajax process request / get javascripts */
	$rlXajax -> processRequest();

	ob_start();
	$rlXajax -> printJavascript();
	$ajax_javascripts = ob_get_contents();
	ob_end_clean();

	/* assign ajax javascripts */
	$rlSmarty -> assign_by_ref( 'ajaxJavascripts', $ajax_javascripts );
	
	$rlSmarty -> display( 'login.tpl' );
	$_SESSION['query_string'] = $_SERVER['QUERY_STRING'];
	
	$rlHook -> load('apNotLogin');
	
	exit;
}

/* load listing types */
$reefless -> loadClass('ListingTypes');

if ( !$_REQUEST['xjxfun'] )
{
	/* load the main menu */
	$mMenuItems = $rlAdmin -> getMainMenuItems();
	$rlSmarty -> assign_by_ref( 'mMenuItems', $mMenuItems );
	
	$mMenu_controllers = $rlAdmin -> mMenu_controllers;
	$rlSmarty -> assign_by_ref( 'mMenu_controllers', $mMenu_controllers );
	
	$menu_icons = array(
		'common' => -97,
		'listings' => -116,
		'categories' => -135,
		'plugins' => -154,
		'forms' => -173,
		'account' => -192,
		'content' => -211
	);
	$rlSmarty -> assign_by_ref( 'menu_icons', $menu_icons );
	
	/* check admin expire time */
	if (!isset($_POST['xjxfun']))
	{
		$ses_exp = session_cache_expire()-5;
		if ( isset($_SESSION['admin_expire_time']) && $_SERVER['REQUEST_TIME'] - $_SESSION['admin_expire_time'] > $ses_exp * 60 )
		{
			session_destroy();
			
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$reefless -> redirect( null, $redirect_url );
		}
		else
		{
			$_SESSION['admin_expire_time'] = $_SERVER['REQUEST_TIME'];
		}
	}
}

/* check new messages */
$rlAdmin -> checkNewMessages();

/* define controller */
$controller = $_GET['controller'];

if ( empty($controller) )
{
	$controller = 'home';
}

$cInfo = $rlAdmin -> getController( $controller );

if ( $_SESSION['sessAdmin']['type'] == 'super' )
{
	$_SESSION['sessAdmin']['rights'][$cInfo['Key']] = array(
		'add' => 'add',
		'edit' => 'edit',
		'delete' => 'delete'
	);
	$_SESSION['sessAdmin']['rights']['listings'] = array(
		'add' => 'add',
		'edit' => 'edit',
		'delete' => 'delete'
	);
	$_SESSION['sessAdmin']['rights']['categories'] = array(
		'add' => 'add',
		'edit' => 'edit',
		'delete' => 'delete'
	);
}

/* define controller */
if ( ($_SESSION['sessAdmin']['rights'][$cInfo['Key']] || $_SESSION['sessAdmin']['type'] == 'super') || $controller == 'home' )
{
	$controlFile = $cInfo['Plugin'] ? RL_PLUGINS . $cInfo['Plugin'] . RL_DS . 'admin' . RL_DS . $controller . ".inc.php" : RL_ADMIN_CONTROL . $controller . ".inc.php";
		
	if ( file_exists( $controlFile ) )
	{
		require_once( $controlFile );
		
		if ($sError === true)
		{
			$cInfo['Controller'] = '404';
			$rlSmarty -> assign( 'errors', array($GLOBALS['lang']['error_404']) );
		}
	}
	else 
	{
		$cInfo['Controller'] = '404';
		$rlSmarty -> assign( 'errors', array($GLOBALS['lang']['error_404']) );
	}
	
	$rlSmarty -> assign_by_ref('errors', $errors);
}
else
{
	$cInfo['Controller'] = '404';
	$rlSmarty -> assign( 'errors', array(str_replace('{manager}', '<b>'.$cInfo['name'].'</b>', $lang['admin_access_denied'])) );
}

if ( !$_REQUEST['xjxfun'] )
{
	$extended_sections = array('admins', 'languages', 'data_formats', 'listings', 'listing_fields', 'listing_types', 
	'listing_sections', 'listing_groups', 'listing_plans', 'plans_using', 'categories', 'all_accounts', 'account_types', 
	'map_amenities', 'account_fields', 'pages', 'news', 'blocks', 'saved_searches');
	$rlSmarty -> assign_by_ref( 'extended_sections', $extended_sections );
	
	$extended_modes = array('add', 'edit', 'delete');
	$rlSmarty -> assign_by_ref( 'extended_modes', $extended_modes );
	
	$rlSmarty -> assign_by_ref( 'cInfo', $cInfo );
	$rlSmarty -> assign_by_ref( 'aRights', $_SESSION['sessAdmin']['rights'] );
	$rlSmarty -> assign_by_ref( 'cKey', $cInfo['Key'] );
	
	/* load the bread crumbs */
	$breadCrumbs = $rlAdmin -> getBreadCrumbs( $cInfo['ID'], $bcAStep, array(), $cInfo['Plugin'] );
	$rlSmarty -> assign_by_ref( 'breadCrumbs', $breadCrumbs );
	
	/* assign error fields */
	$rlSmarty -> assign_by_ref('error_fields', $error_fields);
	
	/* get notice */
	if (isset( $_SESSION['admin_notice'] ))
	{
		$pNotice = $_SESSION['admin_notice'];
		$rlSmarty -> assign_by_ref( 'pNotice', $pNotice );
		$rlNotice -> resetNotice();
	}
}

/* print total mysql queries execution time */
if ( RL_DB_DEBUG )
{
	echo '<br /><br />Total sql queries time: <b>'. $_SESSION['sql_debug_time'] .'</b>.<br />';
}

$rlHook -> load('apPhpIndexBottom');

/* ajax process request / get javascripts */
$rlXajax -> processRequest();

ob_start();
$rlXajax -> printJavascript();
$ajax_javascripts = ob_get_contents();
ob_end_clean();

/* assign ajax javascripts */
$rlSmarty -> assign_by_ref( 'ajaxJavascripts', $ajax_javascripts );

if ( !$_REQUEST['xjxfun'] )
{
	$rlSmarty -> display( 'index.tpl' );
}