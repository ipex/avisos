<?php


/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LISENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: REMOTE_ADVERTS.INC.PHP
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

$listing_type = current($rlListingTypes -> types);
$listing_type = $listing_type['Key'];

$rlSmarty -> assign('listing_types', $rlListingTypes -> types);
$rlSmarty -> assign('listing_type', $listing_type);

$categories = $rlCategories -> getCategories(0, $listing_type);
$rlSmarty -> assign( 'categories', $categories );

$reefless -> loadClass('RemoteAdverts', null, 'js_blocks');
$rlXajax -> registerFunction( array( 'loadCategories', $rlRemoteAdverts, 'ajaxLoadCategories' ) );

$box_id = "ra".mt_rand();

$out = '<div id="'.$box_id.'"> </div>';
$out .='<script type="text/javascript" src="'.RL_PLUGINS_URL.'js_blocks/blocks.inc.php[aurl]"></script>';

$rlSmarty -> assign('out', $out);
$rlSmarty -> assign('box_id', $box_id);
