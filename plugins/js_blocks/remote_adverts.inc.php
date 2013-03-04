<?php

$listing_type = $account_info['Abilities'][0];

$categories = $rlCategories -> getCategories(0, $listing_type);
$rlSmarty -> assign( 'categories', $categories );

foreach( $account_info['Abilities'] as $key => $lt )
{
	if( $rlListingTypes -> types[$lt] )
	{
		$listing_types[] = $rlListingTypes -> types[$lt];
	}
}

if( !$account_info['Abilities'] )
{
	$listing_types = $rlListingTypes -> types;
}

$rlSmarty -> assign('listing_types', $listing_types);

$reefless -> loadClass('RemoteAdverts', null, 'js_blocks');
$rlXajax -> registerFunction( array( 'loadCategories', $rlRemoteAdverts, 'ajaxLoadCategories' ) );

$box_id = "ra".mt_rand();

$out = '<div id="'.$box_id.'"> </div>';
$out .='<script type="text/javascript" src="'.RL_PLUGINS_URL.'js_blocks/blocks.inc.php[aurl]"></script>';

$rlSmarty -> assign('out', $out);
$rlSmarty -> assign('box_id', $box_id);

