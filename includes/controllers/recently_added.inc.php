<?php

/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: RECENTLY_ADDED.INC.PHP
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

$rlXajax -> registerFunction( array( 'loadRecentlyAdded', $rlListings, 'ajaxloadRecentlyAdded' ) );

/* get requested type */
foreach ($rlListingTypes -> types as $type)
{
	$default_type = !$default_type ? $type['Key'] : $default_type;
	if ( isset($_GET[$type['Key']]) )
	{
		$requested_type = $type['Key'];
		break;
	}
}

$default = $_SESSION['recently_added_type'] ? $_SESSION['recently_added_type'] : $default_type;
$requested_type = $requested_type ? $requested_type : $default;
$_SESSION['recently_added_type'] = $requested_type;
$rlSmarty -> assign_by_ref('requested_type', $requested_type);

$pInfo['current'] = (int)$_GET['pg'];

if ( $pInfo['current'] > 1 )
{
	$bc_page = str_replace('{page}', $pInfo['current'], $lang['title_page_part']);
	
	/* add bread crumbs item */
	$bread_crumbs[] = array(
		'title' => $page_info['name'] . $bc_page,
		'name' => $lang['listing_types+name+'.$requested_type] . $bc_page
	);
}

/* get listings */
$listings = $rlListings -> getRecentlyAdded($pInfo['current'], $config['listings_per_page'], $requested_type);
$rlSmarty -> assign_by_ref('listings', $listings);

$pInfo['calc'] = $rlListings -> calc;
$rlSmarty -> assign_by_ref( 'pInfo', $pInfo );

/* build rss */
$rss = array(
	'title' => $lang['pages+name+listings']
);
$rlSmarty -> assign_by_ref( 'rss', $rss );

$rlHook -> load('listingsBottom');