<?php

/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: PRINT.INC.PHP
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

$languages = $rlLang -> getLanguagesList();
$rlLang -> modifyLanguagesList( $languages );

$reefless -> loadClass( 'Valid' );
$reefless -> loadClass( 'Categories' );
$reefless -> loadClass( 'Listings' );
$reefless -> loadClass( 'Common' );

// register functions in smarty
$rlSmarty -> register_function('rlHook', array( 'rlHook', 'load' ));

$item = $_GET['item'];

switch ($item){
	case 'listing':
		$listing_id = (int)$_GET['id'];
		
		/* get listing info */
		$sql = "SELECT `T1`.*, `T2`.`Path`, `T2`.`Key` AS `Cat_key`, `T3`.`Image`, `T2`.`Type` AS `Listing_type` ";
		$sql .= "FROM `" . RL_DBPREFIX . "listings` AS `T1` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "categories` AS `T2` ON `T1`.`Category_ID` = `T2`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_plans` AS `T3` ON `T1`.`Plan_ID` = `T3`.`ID` ";
		$sql .= "WHERE `T1`.`ID` = '{$listing_id}' LIMIT 1";
		$listing_data = $rlDb -> getRow( $sql );
		
		$listing_type = $rlListingTypes -> types[$listing_data['Listing_type']];
		
		if ( $listing_data['Status'] != 'active' && $listing_data['Account_ID'] != $account_info['ID'] )
		{
			unset($listing_data);
		}
		
		if ( empty($listing_id) || empty($listing_data) )
		{
			die();
		}
		else
		{	
			/* build listing structure */
			$category_id = $listing_data['Category_ID'];
		
			$listing = $rlListings -> getListingDetails( $category_id, $listing_data, $listing_type );
			$rlSmarty -> assign_by_ref( 'listing', $listing );
		
			/* get listing title */
			$listing_title = $rlListings -> getListingTitle( $category_id, $listing_data, $listing_data['Listing_type'] );
			$rlSmarty -> assign_by_ref( 'listing_title', $listing_title );
		
			/* enable print page */
			$print = array(
				'item' => 'listing',
				'id' => $listing_data['ID'],
				'name' => $lang['categories+name+'.$listing_data['Cat_key']]
			);
			$rlSmarty -> assign_by_ref('print', $print);
			
			/* get listing photos */
			$photo = $rlDb -> fetch( '*', array( 'Listing_ID' => $listing_id ), "ORDER BY `Type` DESC, `ID`", 1, 'listing_photos', 'row' );
			$rlSmarty -> assign_by_ref( 'photo', $photo );
		
			/* get seller information */
			$seller_info = $rlAccount -> getProfile( (int)$listing_data['Account_ID'] );
			$rlSmarty -> assign_by_ref( 'seller_info', $seller_info );
		}
		break;
	
	case 'browse':
		$id = (int)$_GET['id'];
	
		/* get category info */
		$category = $rlCategories -> getCategory( $id );
		if ( empty($category) )
		{
			die();
		}
		$rlSmarty -> assign_by_ref('category', $category);
		
		$rss = array(
			'title' => $category['name'],
			'path' => $category['Path'],
			'id' => $category['ID']
		);
		$rlSmarty -> assign_by_ref( 'rss', $rss );
		
		/* get sorting form fields */
		$sorting_fields = $rlListings -> getFormFields( $category['ID'], 'short_forms' );
		$sorting_fields = $sorting_fields[$category['ID']]['fields'];
		$sorting_fields[] = array( 'Key' => 'Date' );
		
		foreach ($sorting_fields as $key => $value)
		{
			$sorting[$sorting_fields[$key]['Key']] = $sorting_fields[$key];
		}
		unset($sorting_fields);
		$rlSmarty -> assign_by_ref( 'sorting', $sorting );
		
		/* get listings */
		$listings = $rlListings -> getListings( $category['ID'], false, 'ASC', $pInfo['current'], $config['listings_per_print_page'] );
		$rlSmarty -> assign_by_ref( 'listings', $listings );
		break;
	
	case 'search':
		$data = $_SESSION['post'];
		$reefless -> loadClass( 'Search' );
	
		/* get current search form */
		$listing_mode = $_SESSION['listing_mode'] ? $_SESSION['listing_mode'] : 'sale_rent';
		
		$rlSearch -> getFields( $_SESSION['form'], $listing_mode );
		$rlSmarty -> assign_by_ref( 'fields_list', $rlSearch -> fields );

		/* get listings */
		$listings = $rlSearch -> search( $data, $listing_mode, false, $config['listings_per_print_page'] );
		$rlSmarty -> assign_by_ref( 'listings', $listings );
		break;
	
	case 'listings':
		$type = empty($_GET['type']) ? 'sale_rent' : $_GET['type'];
		$period = empty($_GET['nvar_1']) ? $_GET['period'] : $_GET['nvar_1'];
		$period = empty($period) ? 'new' : $period;

		$listings = $rlListings -> getListingsByPeriod( false, 'ASC', 0, $config['listings_per_print_page'], $type, $period );
		$rlSmarty -> assign_by_ref( 'listings', $listings );
		break;
	
	default:
		$sError = true;
		break;
}

$rlHook -> load('phpPrintPageBottom');