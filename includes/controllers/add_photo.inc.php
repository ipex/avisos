<?php

/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: ADD_PHOTO.INC.PHP
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

$reefless -> loadClass( 'Listings' );
$reefless -> loadClass( 'Actions' );
$reefless -> loadClass( 'Crop' );
$reefless -> loadClass( 'Resize' );

$id = $_GET['id'] ? (int)$_GET['id'] : $_SESSION['add_photo']['listing_id'];
$rlSmarty -> assign_by_ref('listing_id', $id);
$_SESSION['add_photo']['listing_id'] = $id;

/* get listing info */
$listing = $rlListings -> getShortDetails( $id, $plan_info = true );
$rlSmarty -> assign_by_ref( 'listing', $listing );
$photos_allow = $listing['Plan_image'];

/* define listing type */
$listing_type = $rlListingTypes -> types[$listing['Listing_type']];
$rlSmarty -> assign_by_ref('listing_type', $listing_type);

$rlHook -> load('addPhotoTop');

if ( !isset($account_info) || 
	empty($id) || 
	empty($listing) || 
	($listing['Plan_image'] == 0 && !$listing['Image_unlim']) || 
	!$listing_type['Photo'] || 
	$listing['Account_ID'] != $account_info['ID'] )
{
	$sError = true;
}
else
{
	/* add bread crumbs item */
	$bc_last = array_pop($bread_crumbs);
	$bread_crumbs[] = array(
		'name' => $lang['pages+name+'. $listing_type['My_key']],
		'title' => $lang['pages+title+'. $listing_type['My_key']],
		'path' => $pages[$listing_type['My_key']]
	);
	$bread_crumbs[] = $bc_last;
	
	/* simulate plan_info variable */
	$plan_info = array(
		'Image_unlim' => $listing['Image_unlim'],
		'Image' => $listing['Plan_image']
	);
	$rlSmarty -> assign_by_ref('plan_info', $plan_info);
	
	$rlXajax -> registerFunction( array( 'makeMain', $rlListings, 'ajaxMakeMain' ) );
	$rlXajax -> registerFunction( array( 'editDesc', $rlListings, 'ajaxEditDesc' ) );
	$rlXajax -> registerFunction( array( 'reorderPhoto', $rlListings, 'ajaxReorderPhoto' ) );
	$rlXajax -> registerFunction( array( 'crop', $rlCrop, 'ajaxCrop' ) );
	
	$rlSmarty -> assign_by_ref('allowed_photos', $plan_info['Image']);
	
	$max_file_size = str_replace('M', '', ini_get('upload_max_filesize'));
	$rlSmarty -> assign_by_ref( 'max_file_size', $max_file_size );
	
	$rlHook -> load('addPhotoBottom');
}