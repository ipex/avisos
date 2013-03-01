<?php

/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: NEWS.INC.PHP
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

if( $config['mod_rewrite'] )
{
	$path = $rlValid -> xSql( $_GET['nvar_1'] );
	$news_id = $rlDb -> getOne('ID', "`Path` = '{$path}'", 'news');
}
else
{
	$news_id = (int)$_GET['id'];
}

$pInfo['current'] = (int)$_GET['pg'];

$reefless -> loadClass( 'News' );

if ( empty($news_id) )
{
	if ( $pInfo['current'] > 1 )
	{
		$bc_page = str_replace('{page}', $pInfo['current'], $lang['title_page_part']);
		
		/* add bread crumbs item */
		$bread_crumbs[1]['title'] .= $bc_page;
	}
	
	$all_news = $rlNews -> get( false, true, $pInfo['current'] );
	$rlSmarty -> assign_by_ref( 'all_news', $all_news );
	
	$pInfo['calc'] = $rlNews -> calc_news;
	$rlSmarty -> assign_by_ref( 'pInfo', $pInfo );
	
	$rlHook -> load('newsList');
	
	/* build rss */
	$rss = array(
		'item' => 'news',
		'title' => $lang['pages+name+'.$pages['news']]
	);
	$rlSmarty -> assign_by_ref( 'rss', $rss );
}
else
{
	$news = $rlNews -> get( $news_id, true );
	$rlSmarty -> assign( 'news', $news );
	
	$page_info['meta_description'] = $news['meta_description'];
	$page_info['meta_keywords'] = $news['meta_keywords'];
	
	$bread_crumbs[] = array(
		'title' => $news['title']
	);
	$page_info['name'] = $news['title'];
	
	$rlHook -> load('newsItem');
}
