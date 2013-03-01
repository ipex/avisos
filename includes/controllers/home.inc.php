<?php

/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: HOME.INC.PHP
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
$rlSearch -> getHomePageSearchForm();

/* get random feauted */
if ( $tpl_settings['random_featured_home'] )
{
	foreach ( $rlListingTypes -> types as $home_type )
	{
		if ( $home_type['Random_featured'] )
		{
			$random_featured = $rlListings -> getRandom($home_type['Key'], $home_type['Random_featured_type'], $home_type['Random_featured_number']);
			$rlSmarty -> assign_by_ref('random_featured', $random_featured);
			$rlSmarty -> assign('listing_type', $home_type);
			
			break;
		}
	}
}

/* enable rss */
$rss = array(
	'title' => $page_info['title']
);
$rlSmarty -> assign_by_ref('rss', $rss);

$rlHook -> load('homeBottom');