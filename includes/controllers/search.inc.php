<?php

/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: SEARCH.INC.PHP
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

$reefless -> loadClass('Search');

/* get search forms */
foreach ($rlListingTypes -> types as $type_key => $listing_type)
{
	if ( $listing_type['Search_page'] )
	{
		if ( $search_form = $rlSearch -> buildSearch( $type_key .'_quick', $type_key ) )
		{
			$search_forms[$type_key] = $search_form;
		}
	}
}

$rlSmarty -> assign_by_ref( 'search_forms', $search_forms );

/* keyword search mode */
if ( $_POST['form'] == 'keyword_search' || $_SESSION['keyword_search_data'] )
{
	$_SESSION['keyword_search_data'] = $data = $_REQUEST['f'] ? $_REQUEST['f'] : $_SESSION['keyword_search_data'];
	$query = trim($data['keyword_search']);
	$query = preg_replace('/(\\s)\\1+/', ' ', $query);
	$query = str_replace('%', '', $query);
	
	$rlSmarty -> assign('keyword_search', true);
	
	if ( !empty($query) )
	{
		$pInfo['current'] = (int)$_GET['pg'];
		$rlSmarty -> assign('keyword_mode', $data['keyword_search_type']);
		
		if ( $pInfo['current'] > 1 )
		{
			$_SESSION['keyword_search_pageNum'] = $pInfo['current'];
		}
		else
		{
			unset($_SESSION['keyword_search_pageNum']);
		}
		
		if ( !$_POST )
		{
			$_POST['f'] = $_SESSION['keyword_search_data'];
		}
	
		$rlSearch -> fields['keyword_search'] = array(
			'Key' => 'keyword_search',
			'Type' => 'text'
		);
		
		$sorting = array(
			'type' => array(
				'name' => $lang['listing_type'],
				'field' => 'Listing_type',
				'Key' => 'Listing_type',
				'Type' => 'select'
			),
			'category' => array(
				'name' => $lang['category'],
				'field' => 'Category_ID',
				'Key' => 'Category_ID',
				'Type' => 'select'
			),
			'post_date' => array(
				'name' => $lang['join_date'],
				'field' => 'Date',
				'Key' => 'Date'
			)
		);
		$rlSmarty -> assign_by_ref( 'sorting', $sorting );
		
		/* define sort field */
		$sort_by = $_SESSION['keyword_search_sort_by'] = empty($_REQUEST['sort_by']) ? $_SESSION['keyword_search_sort_by'] : $_REQUEST['sort_by'];
		
		if ( !empty($sorting[$sort_by]) )
		{
			$data['sort_by'] = $sort_by;
			$rlSmarty -> assign_by_ref('sort_by', $sort_by);
		}
		
		/* define sort type */
		$sort_type = $_SESSION['keyword_search_sort_type'] = empty($_REQUEST['sort_type']) ? $_SESSION['keyword_search_sort_type'] : $_REQUEST['sort_type'];
		if ( $sort_type )
		{
			$data['sort_type'] = $sort_type = in_array( $sort_type, array('asc', 'desc') ) ? $sort_type : false ;
			$rlSmarty -> assign_by_ref( 'sort_type', $sort_type );
		}
		
		$rlSearch -> fields = array_merge($rlSearch -> fields, $sorting);
		
		$rlHook -> load('keywordSearchData');
		
		/* get listings */
		$listings = $rlSearch -> search( $data, false, $pInfo['current'], $config['listings_per_page'] );
		$rlSmarty -> assign_by_ref( 'listings', $listings );
		
		$pInfo['calc'] = $rlSearch -> calc;
		$rlSmarty -> assign_by_ref( 'pInfo', $pInfo );
		
		if ( $listings )
		{
			$page_info['name'] = str_replace(array('{number}', '{type}'), array($pInfo['calc'], $lang['listings']), $lang['listings_found']);
		}
		
		/* add bread crumbs item */
		$bread_crumbs[] = array(
			'name' => $lang['blocks+name+keyword_search']
		);
	}
}

$rlHook -> load('searchBottom');