<?php

/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: EDIT_LISTING.INC.PHP
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

$reefless -> loadClass( "Listings" );
$reefless -> loadClass( "Categories" );

/* register ajax methods */
$rlXajax -> registerFunction( array( 'deleteListingFile', $rlListings, 'ajaxDeleteListingFile' ) );
$rlXajax -> registerFunction( array( 'changeListingCategory', $rlCategories, 'ajaxChangeListingCategory' ) );
$rlXajax -> registerFunction( array( 'getCatLevel', $rlCategories, 'ajaxGetCatLevel' ) );
$rlXajax -> registerFunction( array( 'openTree', $rlCategories, 'ajaxOpenTree' ) );
$rlXajax -> registerFunction( array( 'removeTmpFile', $reefless, 'ajaxRemoveTmpFile' ) );

if ( $_POST['xjxfun'] )
{
	$rlXajax -> processRequest();
	exit;
}

$listing_id = (int)$_GET['id'];

/* get listing info */
$sql = "SELECT `T1`.*, `T2`.`Cross` AS `Plan_crossed`, `T2`.`Key` AS `Plan_key`, `T3`.`Type` AS `Listing_type` ";
$sql .= "FROM `".RL_DBPREFIX."listings` AS `T1` ";
$sql .= "LEFT JOIN `".RL_DBPREFIX."listing_plans` AS `T2` ON `T1`.`Plan_ID` = `T2`.`ID` ";
$sql .= "LEFT JOIN `".RL_DBPREFIX."categories` AS `T3` ON `T1`.`Category_ID` = `T3`.`ID` ";
$sql .= "WHERE `T1`.`ID` = '{$listing_id}' LIMIT 1";

$listing = $rlDb -> getRow($sql);
$rlSmarty -> assign_by_ref('listing_data', $listing);

if ( !isset($account_info['Type']) || 
	empty($listing_id) || 
	empty($listing) || 
	$listing['Account_ID'] != $account_info['ID'] )
{
	$sError = true;
}
else
{
	/* get listing form */
	if ( isset($listing_id) )
	{
		/* simulate plan information */
		$plan_info = $rlDb -> fetch( array('ID', 'Key', 'Type', 'Cross', 'Price', 'Listing_number', 'Cross', 'Image', 'Image_unlim', 'Video', 'Video_unlim'), array( 'ID' => $listing['Plan_ID'], 'Status' => 'active' ), null, 1, 'listing_plans', 'row' );
		$plan_info['name'] = $lang['listing_plans+name+'. $plan_info['Key']];
		$rlSmarty -> assign_by_ref('plan_info', $plan_info);
		
		/* crossed categories sections/tree */
		if ( $plan_info['Cross'] )
		{
			$sections = $rlCategories -> getCatTree(0, !$config['crossed_categories_by_type'] && in_array($listing['Listing_type'], $account_info['Abilities']) ? $listing['Listing_type'] : $account_info['Abilities'], true);
			$rlSmarty -> assign_by_ref( 'sections', $sections );
			
			$crossed = $_POST['crossed_categories'] ? $_POST['crossed_categories'] : explode(',', $listing['Crossed']);
			$_SESSION['edit_listing']['crossed'] = $crossed;
			$rlSmarty -> assign_by_ref('crossed', $crossed);
			
			$rlCategories -> parentPoints($crossed);
			
			if ( $_POST['crossed_done'] )
			{
				$_SESSION['edit_listing']['crossed_done'] = 1;
			}
		}
		
		/* get posting type of listing */
		$listing_type = $rlListingTypes -> types[$listing['Listing_type']];
		$rlSmarty -> assign_by_ref('listing_type', $listing_type);
		
		/* get listing title/assign info */
		$listing_title = $rlListings -> getListingTitle( $listing['Category_ID'], $listing, $listing['Listing_type'] );
		$rlSmarty -> assign_by_ref('listing_title', $listing_title);
		$rlSmarty -> assign_by_ref('listing_info', $listing);

		/* get current listing category information */
		$category = $rlCategories -> getCategory( $listing['Category_ID'] );
		$rlSmarty -> assign_by_ref( 'category', $category );

		/* get categories list by current listing type */
		$categories_titles = $rlCategories -> getCatTitles($listing['Listing_type']);
		$rlSmarty -> assign_by_ref( 'categories_list', $categories_titles );
		
		if ( !$category )
		{
			/* system error */
			$errors[] = $lang['notice_listing_category_inaccessible'];
		}
		else 
		{	
			$form = $rlCategories -> buildListingForm( $category['ID'], $listing_type );
			
			if ( empty($form) )
			{
				/* system error */
				$errors[] = $lang['notice_no_fields_related'];
			}
			else
			{
				$rlSmarty -> assign_by_ref( 'form', $form );
				
				/* add bread crumbs item */
				$bc_last = array_pop($bread_crumbs);
				$bread_crumbs[] = array(
					'name' => $lang['pages+name+my_'. $category['Type']],
					'title' => $lang['pages+title+my_'. $category['Type']],
					'path' => $pages['my_' .$category['Type']]
				);
				$bread_crumbs[] = $bc_last;
	
				$listing_fields = $rlCategories -> fields;
				
				if ( !isset($_POST['fromPost']) )
				{	
					/* POST simulation */
					foreach ($listing_fields as $key => $value)
					{
						if ( $listing[$listing_fields[$key]['Key']] != '' )
						{
							switch ($listing_fields[$key]['Type'])
							{
								case 'mixed':
									$df_item = false;	
									$df_item = explode( '|', $listing[$listing_fields[$key]['Key']] );
									
									$_POST['f'][$listing_fields[$key]['Key']]['value'] = $df_item[0];
									$_POST['f'][$listing_fields[$key]['Key']]['df'] = $df_item[1];
									break;
								
								case 'date':
									if ( $listing_fields[$key]['Default'] == 'single' )
									{
										$_POST['f'][$listing_fields[$key]['Key']] = $listing[$listing_fields[$key]['Key']];
									}
									elseif ( $listing_fields[$key]['Default'] == 'multi' )
									{
										$_POST['f'][$listing_fields[$key]['Key']]['from'] = $listing[$listing_fields[$key]['Key']];
										$_POST['f'][$listing_fields[$key]['Key']]['to'] = $listing[$listing_fields[$key]['Key'].'_multi'];
									}
									break;
									
								case 'phone':
									$_POST['f'][$listing_fields[$key]['Key']] = $reefless -> parsePhone($listing[$listing_fields[$key]['Key']]);
									break;
								
								case 'price':
									$price = false;	
									$price = explode( '|', $listing[$listing_fields[$key]['Key']] );
									
									$_POST['f'][$listing_fields[$key]['Key']]['value'] = $price[0];
									$_POST['f'][$listing_fields[$key]['Key']]['currency'] = $price[1];
									break;
								
								case 'checkbox':
									$ch_items = null;
									$ch_items = explode(',', $listing[$listing_fields[$key]['Key']]);
	
									$_POST['f'][$listing_fields[$key]['Key']] = $ch_items;
									unset($ch_items);
									break;
								
								default:
									if ( in_array($value['Type'], array('text', 'textarea')) && $listing_fields[$key]['Multilingual'] && count($GLOBALS['languages']) > 1 )
									{
										$_POST['f'][$listing_fields[$key]['Key']] = $reefless -> parseMultilingual($listing[$listing_fields[$key]['Key']]);
									}
									else
									{
										$_POST['f'][$listing_fields[$key]['Key']] = $listing[$listing_fields[$key]['Key']];
									}
									break;
							}
						}
					}
					
					$rlHook -> load('editListingPostSimulation');
				}
				
				$rlHook -> load('editListingPreAssign');
				
				/* listing editing */
				if ( $_POST['action'] == 'edit' )
				{
					$data = $_POST['f'];
					
					if ( $plan_info['Cross'] > 0 && count($_POST['crossed_categories']) > $plan_info['Cross'] )
					{
						$errors[] = $lang['crossed_count_hack'];
					}
	
					// check form fields
					if ( !empty($data) )
					{
						if ( $back = $rlCommon -> checkDynamicForm( $data, $listing_fields ) )
						{
							foreach ( $back as $error )
							{
								$errors[] = $error;
								$rlSmarty -> assign('fixed_message', true);
							}
							
							if ( $rlCommon -> error_fields )
							{
								$error_fields .= $rlCommon -> error_fields;
								$rlCommon -> error_fields = false;
							}
						}
					}
					
					$rlHook -> load('editListingDataChecking');
					
					if ( empty($errors) )
					{
						$reefless -> loadClass( 'Actions' );
						$reefless -> loadClass( 'Resize' );
		
						$rlHook -> load('editListingAdditionalInfo');
						
						if ( $rlListings -> edit( $listing_id, $plan_info, $data, $listing_fields ) )
						{
							/* crossed categories handler */
							if ( $listing['Crossed'] )
							{
								$current_crossed = explode(',', $listing['Crossed']);
								foreach ($current_crossed as $incrace_cc)
								{
									$rlCategories -> listingsDecrease( $incrace_cc );
								}
							}
							
							if ( $plan_info['Cross'] > 0 && !empty($_POST['crossed_categories']) )
							{
								foreach ($_POST['crossed_categories'] as $incrace_cc)
								{
									$rlCategories -> listingsIncrease( $incrace_cc );
								}
							}
							
							/* get updated listing info */
							$sql = "SELECT `T1`.*, `T2`.`Cross` AS `Plan_crossed` FROM `".RL_DBPREFIX."listings` AS `T1` ";
							$sql .= "LEFT JOIN `".RL_DBPREFIX."listing_plans` AS `T2` ON `T1`.`Plan_ID` = `T2`.`ID` ";
							$sql .= "WHERE `T1`.`ID` = '{$listing_id}' LIMIT 1";
							
							$updated_listing = $rlDb -> getRow($sql);
							
							/* send notification to admin and owner */
							if ( !$config['edit_listing_auto_approval'] && serialize($updated_listing) != serialize($listing) )
							{
								$reefless -> loadClass('Mail');
								
								/* send to admin */
								$mail_tpl = $rlMail -> getEmailTemplate( 'admin_listing_edited' );
								
								$link = SEO_BASE;
								$link .= $config['mod_rewrite'] ? $pages['lt_'. $category['Type']] .'/'. $category['Path'] .'/'. $rlSmarty -> str2path($listing_title) .'-'.$listing_id.'.html' : '?page='.$pages['lt_'. $category['Type']].'&amp;id='.$listing_id ;
								$activation_link = RL_URL_HOME . ADMIN .'/index.php?controller=listings&amp;action=remote_activation&amp;id='. $listing_id . '&amp;hash='. md5($rlDb -> getOne('Date', "`ID` = '{$listing_id}'", 'listings'));
								$activation_link = '<a href="'.$activation_link.'">'.$activation_link.'</a>';
								
								$m_find = array('{username}', '{link}', '{date}', '{status}', '{activation_link}');
								$m_replace = array(
									$account_info['Username'], 
									'<a href="'. RL_URL_HOME . ADMIN .'/index.php?controller=listings&amp;action=view&amp;id='. $listing_id .'">'. $listing_title . '</a>', 
									date(str_replace(array('b', '%'), array('M', ''), RL_DATE_FORMAT)),
									$lang['suspended'],
									$activation_link
								);
								$mail_tpl['body'] = str_replace( $m_find, $m_replace, $mail_tpl['body'] );								
								$rlMail -> send( $mail_tpl, $config['notifications_email'] );
								
								/* send to owner */
								$mail_tpl = $rlMail -> getEmailTemplate('edit_listing_pending');
								$mail_tpl['body'] = preg_replace('/\[(.+)\]/', '<a href="'. $link .'">$1</a>', $mail_tpl['body']);
								$rlMail -> send( $mail_tpl, $account_info['Mail'] );
								
								/* dicrease related category counter */
								$rlCategories -> listingsDecrease( $category['ID'] );
								
								if ( $updated_listing['Crossed'] && $updated_listing['Plan_crossed'] )
								{
									$current_crossed = explode(',', $updated_listing['Crossed']);
									foreach ($current_crossed as $incrace_cc)
									{
										$rlCategories -> listingsDecrease( $incrace_cc );
									}
								}
							}
							
							$rlHook -> load('afterListingEdit');
							
							$reefless -> loadClass( 'Notice' );
							$rlNotice -> saveNotice( $lang['notice_listing_edited'] );
	
							$url = SEO_BASE;
							$url .= $config['mod_rewrite'] ? $pages[$listing_type['My_key']] .'.html' : '?page='. $pages[$listing_type['My_key']];
							$reefless -> redirect(null, $url);
						}
					}
				}
				/* edit listing end */
			}
		}
	}
}