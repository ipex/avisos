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

/* load system config */
require_once( 'includes'.DIRECTORY_SEPARATOR.'config.inc.php' );

/* system controller */
require_once( RL_INC . 'control.inc.php' );

/* www. prefix redirect */
$reefless -> wwwRedirect();

$rlHook -> load('init');

/* load cache control */
$reefless -> loadClass('Cache');

/* load template settings */
$ts_path = RL_ROOT . 'templates' . RL_DS . $config['template'] . RL_DS . 'settings.tpl.php';
if ( is_readable( $ts_path ) )
{
	require_once( $ts_path );
}

/* rewrite GET method vareables */
$reefless -> loadClass( 'Navigator' );
$rlNavigator -> rewriteGet( $_GET['rlVareables'], $_GET['page'], $_GET['language'] );

/* select all languages */
$languages = $rlLang -> getLanguagesList();
$rlSmarty -> assign_by_ref('languages', $languages);

/* define site languages */
$rlLang -> defineLanguage( $rlNavigator -> cLang );
$rlLang -> modifyLanguagesList( $languages );

/* load all fronEnd phrases */
$lang = $rlLang -> getLangBySide( 'frontEnd', RL_LANG_CODE );
$GLOBALS['lang'] = &$lang;
$rlSmarty -> assign_by_ref( 'lang', $lang );

/* login attempts control */
$reefless -> loginAttempt();

/* check user login */
$reefless -> loadClass( 'Account' );

if ( $rlAccount -> isLogin() )
{
	$rlSmarty -> assign( 'isLogin', $_SESSION['account']['Username'] );
	define( 'IS_LOGIN', true );
	
	$account_info = $_SESSION['account'];
	$rlSmarty -> assign_by_ref('account_info', $account_info);
}

/* load system libs */
require_once( RL_LIBS . 'system.lib.php' );

/* set timezone */
$reefless -> setTimeZone();

/* account abilities handler */
$reefless -> loadClass('ListingTypes', null, false, true);
foreach ( $rlListingTypes -> types as $listingType )
{
	if ( !in_array($listingType['Key'], $account_info['Abilities']) )
	{
		$deny_pages[] = 'my_'. $listingType['Key'];
	}
	
	/* count admin only types */
	$admin_only_types += $listingType['Admin_only'] ? 1 : 0;
}
unset($listingType);

$rlSmarty -> assign_by_ref('admin_only_types', $admin_only_types);

if ( empty($account_info['Abilities']) || empty($rlListingTypes -> types) || $admin_only_types == count($rlListingTypes -> types) )
{
	$deny_pages[] = 'add_listing';
	$deny_pages[] = 'payment_history';
	$deny_pages[] = 'my_packages';
}

/* assign base path */
$bPath = RL_URL_HOME;
if ($config['lang'] != RL_LANG_CODE && $config['mod_rewrite'])
{
	$bPath .= RL_LANG_CODE . '/';
}
if (!$config['mod_rewrite'])
{
	$bPath .= 'index.php';
}

$rlHook -> load('seoBase');

define( 'SEO_BASE', $bPath );

$rlSmarty -> assign( 'rlBase',  $bPath);
define('RL_TPL_BASE', RL_URL_HOME . 'templates/' . $config['template'] . '/');
$rlSmarty -> assign( 'rlTplBase', RL_TPL_BASE );

/* get all pages keys/paths */
$pages = $rlNavigator -> getAllPages();
$rlSmarty -> assign_by_ref( 'pages', $pages );

/* define system page */
$page_info = $rlNavigator -> definePage();

/* save previous visited page key */
if ($_SERVER['REDIRECT_REDIRECT_STATUS'] != 404)
{
	$page_info['prev'] = $_SESSION['page_info']['current'] ? $_SESSION['page_info']['current'] : false;
	$page_info['query_string'] = $_SERVER['QUERY_STRING'];
	$_SESSION['page_info']['current'] = $page_info['Key'];
}

$rlHook -> load('pageinfoArea');

/* load mobile class */
$reefless -> loadClass('Mobile');

if ( $rlMobile -> isMobile )
{
	$config['featured_per_page'] = $config['mobile_featured_number'];
	$config['messages_module'] = 1;
	$rlSmarty -> register_function('paging_mobile', array( 'rlMobile', 'paging' ));
}

$rlSmarty -> assign_by_ref( 'pageInfo', $page_info );

/* redirect link handler */
$currentPage = trim($_SERVER['REQUEST_URI'], '/');

$dir = str_replace(RL_DS, '', RL_DIR);
$currentPage = ltrim($currentPage, $dir);
$currentPage = ltrim($currentPage, '/');

if ( $rlNavigator -> cMobile )
{
	$currentPage = ltrim(ltrim($currentPage, $config['mobile_location_name']), '/');
}

if ( $config['lang'] != $rlNavigator -> cLang )
{
	if ( defined('RL_MOBILE') && RL_MOBILE && $config['mobile_location_type'] == 'subdirectory' && isset($_GET['wildcard']) )
	{
		$currentPage = ltrim($currentPage, $config['mobile_location_name']);
	}
	
	$currentPage = substr($currentPage, 3, strlen($currentPage));
	$currentPage = !(bool)preg_match('/\.html$/', $currentPage) && $currentPage ? $currentPage .'/' : $currentPage;
}
elseif ( strlen($currentPage) == 2 )
{
	$currentPage = '';
}
else
{
	$currentPage = !(bool)preg_match('/\.html$/', $currentPage) && $currentPage ? $currentPage .'/' : $currentPage;
}

$rlSmarty -> assign_by_ref( 'pageLink', $currentPage );

$linkPage = $rlNavigator -> cPage == 'index' ? '' : $rlNavigator -> cPage;
$rlSmarty -> assign_by_ref( 'page', $linkPage );

/* load page controller */
if ( $page_info['Tpl'] )
{
	require_once( RL_CONTROL . 'common.inc.php' );
	
	/* assign available hooks */
	$rlCommon -> getHooks();
}

/* load page controller */
if ( $page_info['Controller'] != '404' )
{
	if ( $page_info['Plugin'] )
	{
		require_once( RL_PLUGINS . $page_info['Plugin'] . RL_DS . $page_info['Controller'] . '.inc.php' );
	}
	else
	{
		require_once( RL_CONTROL . $page_info['Controller'] . '.inc.php' );
	}
	
	/* load 404 page */
	if ( $sError === true )
	{
		$rlSmarty -> assign_by_ref( 'errors', $GLOBALS['lang']['error_404'] );
		unset($page_info['Controller']);
	}
}
else 
{
	$rlSmarty -> assign_by_ref( 'errors', $GLOBALS['lang']['error_404'] );
}

/* get notice */
if ( isset($_SESSION['notice']) )
{
	$reefless -> loadClass( 'Notice' );
	$pNotice = $_SESSION['notice'];
	
	switch ($_SESSION['notice_type']){
		case 'notice':
			$pType = 'pNotice';
			break;
			
		case 'alert':
			$pType = 'pAlert';
			break;
			
		case 'error':
			$pType = 'errors';
			break;
		
	}
	$rlSmarty -> assign_by_ref( $pType, $pNotice );
	$rlNotice -> resetNotice();
}

/* assign errors */
if ( !empty($errors) && !$pType && !$pNotice )
{
	$rlSmarty -> assign_by_ref( 'errors', $errors );
	$rlSmarty -> assign('error_fields', $error_fields);
}

/* ajax process request / get javascripts */
$rlXajax -> processRequest();

ob_start();
$rlXajax -> printJavascript();
$ajax_javascripts = ob_get_contents();
ob_end_clean();

/* assign ajax javascripts */
$rlSmarty -> assign_by_ref( 'ajaxJavascripts', $ajax_javascripts );

/* load boot hooks */
$rlHook -> load('boot');

/* exit in ajax mode */
if ( $_REQUEST['xjxfun'] )
{
	exit;
}

/* print total mysql queries execution time */
if ( RL_DB_DEBUG )
{
	echo '<br /><br />Total sql queries time: <b>'. $_SESSION['sql_debug_time'] .'</b>.<br />';
}

/* load templates */
if ( $page_info['Tpl'] )
{
	$rlSmarty -> assign_by_ref( 'bread_crumbs', $bread_crumbs );
	$rlCommon -> pageTitle($bread_crumbs);

	$page_info['Login'] = !empty($page_info['Deny']) ? 1 : $page_info['Login'];
	
	$rlSmarty -> display( 'header.tpl' );

	if ( $page_info['Login'] && !defined('IS_LOGIN') )
	{
		$page_info['Controller'] = 'login';
		$page_info['Plugin'] = '';
		$page_info['Page_type'] = 'system';
		$rlSmarty -> assign( 'request_page', $page_info['Path'] );
		if ( !empty($errors) && !$pType && !$pNotice )
		{
			$rlSmarty -> assign( 'warning', $lang['notice_should_login'] );
		}
	}
	elseif ( (isset($account_info['Type']) && in_array($account_info['Type_ID'], explode(',', $page_info['Deny']))) || (isset($account_info['Abilities'][$page_info['Key']]) && $account_info['Abilities'][$page_info['Key']] === false) )
	{
		$page_info['Controller'] = '404';
		$page_info['Page_type'] = 'system';
		$rlSmarty -> assign( 'request_page', $page_info['Path'] );
		if ( !empty($errors) && !$pType && !$pNotice )
		{
			$rlSmarty -> assign( 'errors', $lang['notice_account_access_deny'] );
		}
	}
	
	if ( $page_info['Plugin'] )
	{
		$rlSmarty -> assign( 'content', RL_PLUGINS . $page_info['Plugin'] . RL_DS . $page_info['Controller'] . '.tpl' );
	}
	else
	{
		$rlSmarty -> assign( 'content', 'controllers' . RL_DS . $page_info['Controller'] . '.tpl' );
	}

	$rlSmarty -> display( 'content.tpl' );
	$rlSmarty -> display( 'footer.tpl' );
}
else 
{
	if ( $page_info['Login'] && !defined('IS_LOGIN') )
	{
		$page_info['Controller'] = 'login';
		$page_info['Page_type'] = 'system';
		$rlSmarty -> assign( 'request_page', $page_info['Path'] );
		$rlSmarty -> assign( 'errors', $lang['notice_should_login'] );
	}
	
	if ( $page_info['Page_type'] == 'system' )
	{
		$rlSmarty -> display( 'controllers' . RL_DS . $page_info['Controller'] . '.tpl' );
	}
	else
	{
		require_once( RL_CONTROL . $page_info['Controller'] . '.inc.php' );
		echo $content['Value'];
	}
}