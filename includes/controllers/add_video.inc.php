<?php

/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: ADD_VIDEO.INC.PHP
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

$listing_id = (int)$_GET['id'];

/* get listing info */
$listing = $rlListings -> getShortDetails( $listing_id, $plan_info = true );
$video_allow = $listing['Video_unlim'] ? 'unlim' : $listing['Plan_video'];

/* simulate plan_info variable */
$plan_info = array(
	'Plan_video' => $listing['Plan_video'],
	'name' => $lang['listing_plans+name+'.$listing['Plan_key']]
);
$rlSmarty -> assign_by_ref('plan_info', $plan_info);

/* define listing type */
$listing_type = $rlListingTypes -> types[$listing['Listing_type']];
$rlSmarty -> assign_by_ref('listing_type', $listing_type);

$rlHook -> load('addVideoTop');

if ( !isset($account_info) || 
	empty($listing_id) || 
	empty($listing) || 
	($listing['Plan_video'] == 0 && !$listing['Video_unlim']) || 
	!$listing_type['Video'] || 
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
	
	$rlSmarty -> assign_by_ref( 'listing', $listing );
	
	/* get listing video */
	$rlDb -> setTable('listing_video');
	$videos = $rlDb -> fetch( array('ID', 'Video', 'Preview', 'Type'), array( 'Listing_ID' => $listing_id ), "ORDER BY `Position`" );
	$rlSmarty -> assign_by_ref( 'videos', $videos );
	
	if ( !$listing['Video_unlim'] )
	{
		$video_allow -= count($videos);
	}
	$rlSmarty -> assign_by_ref( 'video_allow', $video_allow );
	
	$max_file_size = ini_get('upload_max_filesize');
	$rlSmarty -> assign_by_ref( 'max_file_size', $max_file_size );
	
	$rlHook -> load('addVideoPreUpload');
	
	if ( $_POST['step'] == 'video' && ($video_allow || $listing['Video_unlim']) )
	{
		$reefless -> loadClass('Resize');
		$reefless -> loadClass('Crop');
		
		if ( $rlListings -> uploadVideo( $_POST['type'], $_POST['type'] == 'youtube' ? $_POST['youtube_embed'] : $_FILES, $listing_id ) )
		{
			$reefless -> loadClass( 'Notice' );
			$rlNotice -> saveNotice( $lang['notice_files_uploded'] );
			$reefless -> refresh();
		}
	}
	
	$rlXajax -> registerFunction( array( 'deleteVideo', $rlListings, 'ajaxDelVideoFile' ) );
	$rlXajax -> registerFunction( array( 'reorderVideo', $rlListings, 'ajaxReorderVideo' ) );
}