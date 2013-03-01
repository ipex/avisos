<?php

/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: BROWSE.INC.PHP
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

$category_id = $_GET['id'] ? $_GET['id'] : 0 ;
$reefless -> loadClass( 'Categories' );
$reefless -> loadClass( 'Plan' );
$reefless -> loadClass( 'Common' );

$rlHook -> load('apPhpBrowseTop');

/* get category info */
$category = $rlCategories -> getCategory($category_id);
$category['ID'] = empty($category) ? 0 : $category['ID'];
$rlSmarty -> assign_by_ref('category', $category);

if ( !empty($category['ID']) )
{
	$cat_bread_crumbs = $rlCategories -> getBreadCrumbs($category['ID'], false, $rlListingTypes -> types[$category['Type']]);
	$cat_bread_crumbs = array_reverse($cat_bread_crumbs);

	if (!empty($cat_bread_crumbs))
	{
		foreach ($cat_bread_crumbs as $bKey => $bVal)
		{
			$cat_bread_crumbs[$bKey]['title'] = $cat_bread_crumbs[$bKey]['name'];
			$cat_bread_crumbs[$bKey]['Controller'] = 'browse';
			$cat_bread_crumbs[$bKey]['Vars'] = 'id='.$cat_bread_crumbs[$bKey]['ID'];
		}
		$bcAStep = $cat_bread_crumbs;
	}
}

/* get current category children */
$categories = $rlCategories -> getCategories($category['ID'], $category['ID'] ? $category['Type'] : false, $category['ID'] ? false : true);
$rlSmarty -> assign_by_ref('categories', $categories);

/* get navigation bar data */
if ( $category_id )
{
	/* get category titles for filters */
	$tmp_categories = $rlCategories -> getCatTitles();
	foreach ($tmp_categories as $key => $val)
	{
		$filter_categories[$val['ID']] = array('name' => $lang[$val['pName']], 'margin' => $val['margin'], 'type' => $val['Type']);
	}
	unset($tmp_categories);
	
	/* get plans */
	$plans = $rlPlan -> getPlans( array('listing', 'package', 'featured_direct') );
	$rlSmarty -> assign_by_ref('plans', $plans);

	/* get featured plans */
	$featured_plans = $rlPlan -> getPlans('featured');
	$rlSmarty -> assign_by_ref('featured_plans', $featured_plans);
	
	/* get account types */
	$reefless -> loadClass('Account');
	$account_types = $rlAccount -> getAccountTypes('visitor');
	$rlSmarty -> assign_by_ref('account_types', $account_types);
	
	$filters = array(
		'Type' => array('phrase' => $lang['listing_type'], 'items' => $rlListingTypes -> types),
		'Category_ID' => array('phrase' => $lang['category'], 'items' => $filter_categories),
		'Plan_ID' => array('phrase' => $lang['plan'], 'items' => $plans),
		'Status' => array('phrase' => $lang['status'], 'items' => array(
				'new' => $lang['new'],
				'active' => $lang['active'],
				'approval' => $lang['approval'],
				'pending' => $lang['pending'],
				'incomplete' => $lang['incomplete'],
				'expired' => $lang['expired']
			)
		),
		'Pay_date' => array('phrase' => $lang['pay_status'], 'items' => array(
				'payed' => $lang['payed'],
				'not_payed' => $lang['not_payed']
			)
		)
	);
	$rlSmarty -> assign_by_ref('filters', $filters);
	
	/* get categories */
	$sections = $rlCategories -> getCatTree(0, false, true);
	$rlSmarty -> assign_by_ref( 'sections', $sections );
}

$rlHook -> load('apPhpBrowseMiddle');

$reefless -> loadClass('Listings', 'admin');

/* register ajax methods */
$rlXajax -> registerFunction( array( 'massActions', $rlListings, 'ajaxMassActions' ) );
$rlXajax -> registerFunction( array( 'deleteListing', $rlListings, 'ajaxDeleteListing' ) );
$rlXajax -> registerFunction( array( 'makeFeatured', $rlListings, 'ajaxMakeFeatured' ) );
$rlXajax -> registerFunction( array( 'annulFeatured', $rlListings, 'ajaxAnnulFeatured' ) );
$rlXajax -> registerFunction( array( 'moveListing', $rlListings, 'ajaxMoveListing' ) );
$rlXajax -> registerFunction( array( 'deleteCategory', $rlCategories, 'ajaxDeleteCategory' ) );
$rlXajax -> registerFunction( array( 'prepareDeleting', $rlCategories, 'ajaxPrepareDeleting' ) );
$rlXajax -> registerFunction( array( 'getCatLevel', $rlCategories, 'ajaxGetCatLevel' ) );
$rlXajax -> registerFunction( array( 'lockCategory', $rlCategories, 'ajaxLockCategory' ) );