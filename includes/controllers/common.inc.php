<?php

/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: COMMON.INC.PHP
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

/* send headers */
header("Content-Type: text/html; charset=utf-8");
header("Cache-Control: store, no-cache, max-age=3600, must-revalidate");

$reefless -> loadClass('Common');

/* get page blocks */
$blocks = $rlCommon -> getBlocks();
$rlSmarty -> assign_by_ref( 'blocks', $blocks );
$block_keys = $rlCommon -> block_keys;

/* get listing type key (on listing type pages only) */
if ( false !== strpos($page_info['Key'], 'lt_') )
{
	$listing_type_key = str_replace('lt_', '', $page_info['Key']);
}

/* simulate category blocks */
$rlCommon -> simulateCatBlocks();

/* load common components in non ajax mode */
if ( !$_REQUEST['xjxfun'] )
{
	/* build menus */	
	$rlCommon -> buildMenus();

	/* build featured listing blocks */
	$rlListings -> buildFeaturedBoxes($listing_type_key);
	
	/* get statistics block data */
	if ( $block_keys['statistics'] )
	{
		$rlListingTypes -> statisticsBlock();
	}
	
	/* get bread crumbs */
	$bread_crumbs = $rlCommon -> getBreadCrumbs( $page_info );
	
	/* check messages */
	$message_info = $rlCommon -> checkMessages();
	if ( !empty($message_info) )
	{
		$rlSmarty -> assign_by_ref('new_messages', $message_info);
	}
}

/* call special block hooks */
$rlHook -> load('specialBlock');

if( in_array($page_info['Controller'], array('home', 'listing_type', 'search')) )
{
	$rlXajax -> registerFunction( array( 'multiCatNext', $rlCategories, 'ajaxMultiCatNext' ) );
	$rlXajax -> registerFunction( array( 'multiCatBuild', $rlCategories, 'ajaxMultiCatBuild' ) );
}

/* register blocks in smarty */
function smartyEval($param, $content, &$smarty)
{
	return $content; 
}

function insert_eval($params, &$smarty)
{
	require_once( RL_LIBS . 'smarty' . RL_DS . 'plugins' . RL_DS . 'function.eval.php');

	return smarty_function_eval(array("var" => $params['content']), $smarty);
}

$rlSmarty -> register_block('eval', 'smartyEval', false);

/* register functions in smarty */
$rlSmarty -> register_function('str2path', array( 'rlSmarty', 'str2path' ));
$rlSmarty -> register_function('str2money', array( 'rlSmarty', 'str2money' ));
$rlSmarty -> register_function('paging', array( 'rlSmarty', 'paging' ));
$rlSmarty -> register_function('search', array( 'rlSmarty', 'search' ));
$rlSmarty -> register_function('rlHook', array( 'rlHook', 'load' ));
$rlSmarty -> register_function('getTmpFile', array( 'reefless', 'getTmpFile' ));
$rlSmarty -> register_function('encodeEmail', array( 'rlSmarty', 'encodeEmail' ));
