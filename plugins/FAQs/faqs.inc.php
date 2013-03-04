<?php


/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LISENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: FAQS.INC.PHP
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

if( $config['mod_rewrite'] )
{
	$path = $rlValid -> xSql( $_GET['nvar_1'] );
	$faqs_id = $rlDb -> getOne('ID', "`Path` = '{$path}'", 'faqs');
}
else
{
	$faqs_id = (int)$_GET['id'];
}

$pInfo['current'] = (int)$_GET['pg'];

$reefless -> loadClass( 'FAQs', null, 'FAQs' );

if ( empty($faqs_id) )
{
	if ( $pInfo['current'] > 1 )
	{
		$bc_page = str_replace('{page}', $pInfo['current'], $lang['title_page_part']);
		
		/* add bread crumbs item */
		$bread_crumbs[1]['title'] .= $bc_page;
	}
	
	$all_faqs = $rlFAQs -> get( false, true, $pInfo['current'] );
	$rlSmarty -> assign_by_ref( 'all_faqs', $all_faqs );
	
	$pInfo['calc'] = $rlFAQs -> calc_faqs;
	$rlSmarty -> assign_by_ref( 'pInfo', $pInfo );
	
	$rlHook -> load('faqsList');
	
	/* build rss */
	$rss = array(
		'item' => 'faqs',
		'title' => $lang['pages+name+'.$pages['faqs']]
	);
	$rlSmarty -> assign_by_ref( 'rss', $rss );
}
else
{
	$faqs = $rlFAQs -> get( $faqs_id, true );
	$rlSmarty -> assign( 'faqs', $faqs );
	
	$bread_crumbs[] = array(
		'title' => $faqs['title']
	);
	$page_info['name'] = $faqs['title'];
	
	$rlHook -> load('faqsItem');
}
