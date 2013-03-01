<?php

/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: ADD_LISTING.INC.PHP
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

if ( $config['add_listing_without_reg'] && !defined('IS_LOGIN') )
{
	$page_info['Login'] = 0;
	
	/* visitor account simulation */
	$account_type_details = $rlAccount -> getAccountType('visitor');
	$account_info['Type_ID'] = $account_type_details['ID'];
	$account_info['Type'] = $account_type_details['Key'];
	$account_info['ID'] = 0;
	$account_info['Abilities'] = $account_type_details['Abilities'];
}

if ( empty($account_info['Abilities']) )
{
	$errors[] = $lang['add_listing_deny'];
	$rlSmarty -> assign( 'no_access', true );
}

/* build registration steps */
$rlHook -> load('addListingSteps');

$steps['done'] = array(
	'name' => $lang['reg_done'],
	'path' => 'done'
);

$show_step_caption = false;
$rlSmarty -> assign_by_ref( 'show_step_caption', $show_step_caption);
$rlSmarty -> assign_by_ref( 'steps', $steps);

/* optimize categiry request */
$request = explode('/', $_GET['rlVareables']);
$request_step = array_pop($request);

/* detect step from GET */
$get_step = $request_step ? $request_step : $_GET['step'];

$rlHook -> load('addListingTop');

/* clear saved data */
if ( !isset($_GET['edit']) && !$get_step )
{
	unset($_SESSION['add_listing']);
	unset($_SESSION['complete_payment']);
	unset($_SESSION['done']);
}

// the next steps from 2 >
if ( $get_step || $_GET['id'] || $_REQUEST['tmp_id'] )
{
	/* categoey simulation in tmp mode */
	if ( $_REQUEST['tmp_id'] || $_SESSION['add_listing']['tmp_id'] )
	{
		$tmp_id = $_REQUEST['tmp_id'] ? (int)$_REQUEST['tmp_id']: $_SESSION['add_listing']['tmp_id'] ;
		$tmp_cat = $rlDb -> fetch(array('Name', 'Parent_ID'), array('ID' => $tmp_id), null, 1, 'tmp_categories', 'row');

		$_SESSION['add_listing']['tmp_id'] = $tmp_id;
		
		$category = $rlCategories -> getCategory( $tmp_cat['Parent_ID'] );
		$category['name'] = $tmp_cat['Name'];
		$category['Level'] = $category['Level'] + 1;
		$category['Lock'] = 0;
		$category['Path'] = 'tmp-category';
		
		$rlSmarty -> assign( 'cat_id', $tmp_id );
	}
	/* get current category information */
	else
	{
		if ( $config['mod_rewrite'] )
		{
			$category = $rlCategories -> getCategory( false, implode('/', $request) );
		}
		else
		{
			$category = $rlCategories -> getCategory( $_GET['id'] );
		}
		
		unset($_SESSION['add_listing']['tmp_id']);
	}
	
	/* check category */
	if ( !$category )
	{
		$errors[] = $lang['add_listing_category_fail'];
		$rlSmarty -> assign( 'no_access', true );
	}
	
	/* get posting type of listing */
	$listing_type = $rlListingTypes -> types[$category['Type']];
	$rlSmarty -> assign_by_ref('listing_type', $listing_type);
	
	/* check account type permissions */
	if ( !in_array($category['Type'], $account_info['Abilities']) || $listing_type['Admin_only'] )
	{
		$errors[] = str_replace('{category_type}', $lang['listing_types+name+'. $category['Type']], $lang['add_listing_type_deny']);
		$rlSmarty -> assign( 'no_access', true );
	}
	
	if ( !$errors )
	{
		$rlSmarty -> assign_by_ref('category', $category);
		$rlSmarty -> assign('category_id', $category['ID']);
		
		$reefless -> loadClass('Plan');
		$reefless -> loadClass('Actions');
		$reefless -> loadClass('Mail');
		
		if ( $get_step )
		{
			$cur_step = $rlPlan -> stepByPath($get_step, $steps);
			$rlSmarty -> assign_by_ref('cur_step', $cur_step);
	
			/* return user to the first step */
			if ( $_SESSION['done'] && $cur_step && $cur_step != 'done' )
			{
				$url = SEO_BASE;
				$url .= $config['mod_rewrite'] ? $page_info['Path'] .'.html' : '?page='. $page_info['Path'];
				$reefless -> redirect(null, $url);
			}
			
			$plan_id = $_POST['plan'] ? (int)$_POST['plan'] : $_SESSION['add_listing']['plan_id'];
			$listing_id = $_SESSION['add_listing']['listing_id'] ? (int)$_SESSION['add_listing']['listing_id'] : false;
			
			/* get saved listing data */
			if ( $listing_id )
			{
				$listing_data = $rlDb -> fetch('*', array('ID' => $listing_id), null, 1, 'listings', 'row');
				
				/* show login page if requested listing owner isn't logged in */
				if ( $listing_data['Account_ID'] != $account_info['ID'] )
				{
					$page_info['Controller'] = 'login';
					
					/* print first message if there aren't ligin attempts */
					if ( empty($_SESSION['notice']) )
					{
						$rlSmarty -> assign('pAlert', $lang['edit_listing_not_ligged_in_deny']);
					}
					
					/* remove current step */
					$cur_step = false;
				}
			}
			
			if ( !$plan_id && !in_array($cur_step, array('category', 'plan')) )
			{
				$url = SEO_BASE;
				$url .= $config['mod_rewrite'] ? $page_info['Path'] .'/'. $category['Path'] .'/'. $steps['plan']['path'] .'.html' : '?page='. $page_info['Path'] .'&amp;step=' .$steps['plan']['path'];
				$reefless -> redirect(null, $url);
			}
			
			/* get plans, outside the switch because we need plan info through almost of steps */
			$plans = $rlPlan -> getPlanByCategory( $category['ID'], $account_info['Type'] );		
			foreach ( $plans as $pKey => $pValue )
			{
				$tmp_plans[$pValue['ID']] = $pValue;
			}
			$plans = $tmp_plans;
			unset($tmp_plans);
			$rlSmarty -> assign_by_ref('plans', $plans);
	
			/* get plan info */
			if ( $plan_id )
			{
				$plan_info = $plans[$plan_id];
				$plan_info['Plan_video'] = $plan_info['Video'];
				$rlSmarty -> assign_by_ref('plan_info', $plan_info);
			}
			
			$errors = array();
			
			if ( $cur_step )
			{
				/* add step to bread crumbs */
				$bread_crumbs[] = array(
					'name' => $steps[$cur_step]['name']
				);
				
				/* save step for current listing */
				if ( !in_array($cur_step, array('category', 'plan', 'done')) )
				{
					$update_step = array(
						'fields' => array(
							'Last_step' => $cur_step
						),
						'where' => array(
							'ID' => $listing_id
						)
					);
					$rlActions -> updateOne($update_step, 'listings');
				}
			}
			
			/* remove steps depending of plan and listing type */
			if ( $plan_info )
			{
				if ( (!$plan_info['Image'] && !$plan_info['Image_unlim']) || !$listing_type['Photo'] )
				{
					unset($steps['photo']);
				}
				if ( (!$plan_info['Video'] && !$plan_info['Video_unlim']) || !$listing_type['Video'] )
				{
					unset($steps['video']);
				}
				if ( ($plan_info['Type'] == 'package' && ($plan_info['Package_ID'] || $plan_info['Price'] <= 0) ) || ($plan_info['Type'] == 'listing' && $plan_info['Price'] <= 0) )
				{
					unset($steps['checkout']);
				}
			}
	
			/* get prev/next step */
			$tmp_steps = $steps;
			foreach ($tmp_steps as $t_key => $t_step)
			{
				if ( $t_key != $cur_step )
				{
					next($steps);
				}
				else
				{
					break;
				}
			}
			unset($tmp_steps);
			
			$next_step = next($steps);prev($steps);
			$prev_step = prev($steps);
			
			$rlSmarty -> assign('next_step', $next_step);
			$rlSmarty -> assign('prev_step', $prev_step);
			
			// steps handler
			switch ( $cur_step ){
				case 'plan':// step 2
					/* simulate selected plan in POST */
					if ( !$_POST['plan'] && $_SESSION['add_listing']['plan_id'] )
					{
						$_POST['plan'] = $_SESSION['add_listing']['plan_id'];
					}
					
					/* simulate selected type of ad */
					$_POST['listing_type'] = $_SESSION['add_listing']['listing_type'] ? $_SESSION['add_listing']['listing_type'] : $_POST['listing_type'];
					
					/* check for available plans */
					if ( empty($plans) )
					{
						$errors[] = $lang['notice_no_plans_related'];
						$rlSmarty -> assign( 'no_access', true );
						
						$GLOBALS['rlDebug'] -> logger("There are not plans related to '{$category['name']}' category.");
					}
					
					$form = $rlCategories -> buildListingForm( $category['ID'], $listing_type );
					if ( empty($form) )
					{
						// system error, there are not field related to current category
						$errors[] = $lang['notice_no_fields_related'];
						$rlSmarty -> assign( 'no_access', true );
						
						$GLOBALS['rlDebug'] -> logger("There are not fields related to '{$category['name']}' category.");
					}
					
					/* check plan */
					if ( $_POST['step'] == 'plan' )
					{
						$data = $_POST;
						
						if ( !$plan_id )
						{
							$errors[] = $lang['notice_listing_plan_does_not_chose'];
						}
						
						if ( empty($plan_info) )
						{
							$errors[] = $lang['notice_listing_plan_unavailable'];
						}
						
						/* check for allowed plan images count and remove unnecessary */
						if ( !$plan_info['Image_unlim'] && $listing_id )
						{
							$rlDb -> setTable('listing_photos');
							$tmp_photos = $rlDb -> fetch(array('ID', 'Photo', 'Thumbnail', 'Original'), array('Listing_ID' => $listing_id), "ORDER BY `Position` ASC");
							$rlDb -> resetTable();
							
							if ( count($tmp_photos) > $plan_info['Image'] )
							{
								foreach ($tmp_photos as $tmp_photo_index => $tmp_photo)
								{
									if ( $tmp_photo_index > count($plan_info['Image']) )
									{
										unlink(RL_FILES.$tmp_photo['Photo']);
										unlink(RL_FILES.$tmp_photo['Thumbnail']);
										unlink(RL_FILES.$tmp_photo['Original']);
										$rlDb -> query("DELETE FROM `". RL_DBPREFIX ."listing_photos` WHERE `ID` = '{$tmp_photo['ID']}' LIMIT 1");
									}
								}
								unset($tmp_photos);
							}
						}
						
						/* check for allowed plan videos count and remove unnecessary */
						if ( !$plan_info['Video_unlim'] && $listing_id )
						{
							$rlDb -> setTable('listing_video');
							$tmp_videos = $rlDb -> fetch(array('ID', 'Type', 'Video', 'Preview'), array('Listing_ID' => $listing_id), "ORDER BY `Position` ASC");
							$rlDb -> resetTable();
							
							if ( count($tmp_videos) > $plan_info['Video'] )
							{
								foreach ($tmp_videos as $tmp_video_index => $tmp_video)
								{
									if ( $tmp_video_index > count($plan_info['Video']) )
									{
										if ( $tmp_video['Type'] == 'local' )
										{
											unlink(RL_FILES.$tmp_video['Video']);
											unlink(RL_FILES.$tmp_video['Preview']);
										}
										$rlDb -> query("DELETE FROM `". RL_DBPREFIX ."listing_video` WHERE `ID` = '{$tmp_video['ID']}' LIMIT 1");
									}
								}
								unset($tmp_videos);
							}
						}
						
						/* check advanced featured mode */
						if ( $plan_info['Featured'] && $plan_info['Advanced_mode']  )
						{
							$rest_option = $data['listing_type'] == 'standard' ? 'Featured' : 'Standard';
	
							if ( !$_POST['listing_type'] )
							{
								$errors[] = $lang['feature_mode_caption_error'];
							}
							elseif ( $plan_info['Package_ID'] && ($plan_info[ucfirst($data['listing_type']) .'_remains'] <= 0 && $plan_info[ucfirst($data['listing_type']) .'_listings'] > 0) && ($plan_info[$rest_option .'_remains'] > 0 || $plan_info[$rest_option .'_listings'] == 0) )
							{
								$errors[] = $lang['feature_mode_access_hack'];
							}
						}
						
						$_SESSION['add_listing']['listing_type'] = $data['listing_type'];
						
						if ( !$errors )
						{
							$_SESSION['add_listing']['plan_id'] = $plan_id;
							
							$url = SEO_BASE;
							$url .= $config['mod_rewrite'] ? $page_info['Path'] .'/'. $category['Path'] .'/'. $steps['form']['path'] .'.html' : '?page='. $page_info['Path'] .'&id='. $category['ID'] .'&step=' .$steps['form']['path'];
							$reefless -> redirect(null, $url);
						}
					}
					
					break;
				
				case 'form'://step 3
					/* crossed categories sections/tree */
					if ( $plan_info['Cross'] )
					{
						$sections = $rlCategories -> getCatTree(0, !$config['crossed_categories_by_type'] && in_array($category['Type'], $account_info['Abilities']) ? $category['Type'] : $account_info['Abilities'], true);
						$rlSmarty -> assign_by_ref( 'sections', $sections );
						
						$crossed = $_POST['crossed_categories'] ? $_POST['crossed_categories'] : $_SESSION['add_listing']['crossed'];
						$_SESSION['add_listing']['crossed'] = $crossed;
						
						$rlSmarty -> assign_by_ref('crossed', $crossed);
						
						$rlCategories -> parentPoints($crossed);
						
						if ( $_POST['crossed_done'] )
						{
							$_SESSION['add_listing']['crossed_done'] = 1;
						}
					}
					
					/* get form */
					$form = $rlCategories -> buildListingForm( $category['ID'], $listing_type);
					$rlSmarty -> assign_by_ref( 'form', $form );
					
					if ( $listing_id )
					{
						/* load category fields */
						$category_fields = $rlCategories -> fields;
					
						/* get current listing details */
						$cur_listing_sql = "SELECT `T1`.*, `T2`.`Cross` AS `Plan_crossed` ";
						$cur_listing_sql .= "FROM `".RL_DBPREFIX."listings` AS `T1` ";
						$cur_listing_sql .= "LEFT JOIN `".RL_DBPREFIX."listing_plans` AS `T2` ON `T1`.`Plan_ID` = `T2`.`ID` ";
						$cur_listing_sql .= "WHERE `T1`.`ID` = '{$listing_id}' ";
						$cur_listing_sql .= "LIMIT 1";
						
						$listing = $rlDb -> getRow($cur_listing_sql);
							
						/* simulate form POST */
						if ( !isset($_POST['fromPost']) )
						{
							/* set crossed categoris to post */
							if ( strpos($listing['Crossed'], ',') !== false && !empty($listing['Crossed']) )
							{
								$_POST['crossed_categories'] = explode(',', $listing['Crossed']);
							}
							elseif ( strpos($listing['Crossed'], ',') === false && !empty($listing['Crossed']) )
							{
								$_POST['crossed_categories'] = array($listing['Crossed']);
							}
							else
							{
								$_POST['crossed_categories'] = 0;
							}
							
							/* POST simulation */
							foreach ($category_fields as $key => $value)
							{
								if ( $listing[$category_fields[$key]['Key']] != '' )
								{
									switch ($category_fields[$key]['Type'])
									{
										case 'mixed':
											$df_item = false;	
											$df_item = explode( '|', $listing[$category_fields[$key]['Key']] );
											
											$_POST['f'][$category_fields[$key]['Key']]['value'] = $df_item[0];
											$_POST['f'][$category_fields[$key]['Key']]['df'] = $df_item[1];
											break;
										
										case 'date':
											if ( $category_fields[$key]['Default'] == 'single' )
											{
												$_POST['f'][$category_fields[$key]['Key']] = $listing[$category_fields[$key]['Key']];
											}
											elseif ( $category_fields[$key]['Default'] == 'multi' )
											{
												$_POST['f'][$category_fields[$key]['Key']]['from'] = $listing[$category_fields[$key]['Key']];
												$_POST['f'][$category_fields[$key]['Key']]['to'] = $listing[$category_fields[$key]['Key'].'_multi'];
											}
											break;
											
										case 'phone':
											$_POST['f'][$category_fields[$key]['Key']] = $reefless -> parsePhone($listing[$category_fields[$key]['Key']]);
											break;
										
										case 'price':
											$price = false;	
											$price = explode( '|', $listing[$category_fields[$key]['Key']] );
											
											$_POST['f'][$category_fields[$key]['Key']]['value'] = $price[0];
											$_POST['f'][$category_fields[$key]['Key']]['currency'] = $price[1];
											break;
										
										case 'unit':
											$unit = false;	
											$unit = explode( '|', $listing[$category_fields[$key]['Key']] );
											
											$_POST['f'][$category_fields[$key]['Key']]['value'] = $unit[0];
											$_POST['f'][$category_fields[$key]['Key']]['unit'] = $unit[1];
											break;
										
										case 'checkbox':
											$ch_items = null;
											$ch_items = explode(',', $listing[$category_fields[$key]['Key']]);
			
											$_POST['f'][$category_fields[$key]['Key']] = $ch_items;
											unset($ch_items);
											break;
										
										default:
											if ( in_array($value['Type'], array('text', 'textarea')) && $category_fields[$key]['Multilingual'] && count($GLOBALS['languages']) > 1 )
											{
												$_POST['f'][$category_fields[$key]['Key']] = $reefless -> parseMultilingual($listing[$category_fields[$key]['Key']]);
											}
											else
											{
												$_POST['f'][$category_fields[$key]['Key']] = $listing[$category_fields[$key]['Key']];
											}
											break;
									}
								}
							}
							
							$rlHook -> load('addListingPostSimulation');
						}
					}
					
					if ( $_POST['step'] == 'form' )
					{
						$data = $_POST['f'];
						
						/* load fields list */
						if ( !$category_fields )
						{
							$category_fields = $rlCategories -> fields;
						}
						
						if ($data)
						{
							/* authorization */
							if ( $config['add_listing_without_reg'] && !defined('IS_LOGIN') )
							{
								$auth_try = false;
								
								$login_data = $_POST['login'];
								$register_data = $_POST['register'];
								
								/* login */
								if ( $login_data['username'] && $login_data['password'] )
								{
									$auth_try = true;
									
									if ( true === $res = $rlAccount -> login($login_data['username'], $login_data['password']) )
									{
										$rlSmarty -> assign( 'isLogin', $_SESSION['username'] );
										define( 'IS_LOGIN', true );
										
										$account_info = $_SESSION['account'];
										$rlSmarty -> assign_by_ref('account_info', $account_info);
									}
									else
									{
										$errors = array_merge($errors, $res);
										$error_fields .= 'login[username],login[password],';
									}
								}
								/* register */
								elseif ( $register_data['name'] && $register_data['email'] )
								{
									$auth_try = true;
									
									if ( $test =  $rlDb -> getOne('ID', "`Mail` = '{$register_data['email']}' AND `Status` <> 'trash'", 'accounts') )
									{
										$errors[] = str_replace('{email}', '<span class="field_error">'. $register_data['email'] .'</span>', $lang['notice_account_email_exist']);
										$error_fields .= 'register[email],';
									}
									if ( !$rlValid -> isEmail( $register_data['email'] ) )
									{
										$errors[] = $lang['notice_bad_email'];
										$error_fields .= 'register[email],';
									}
									
									if ( !$errors )
									{
										if ( $new_account = $rlAccount -> quickRegistration($register_data['name'], $register_data['email']) )
										{
											$rlAccount -> login($new_account[0], $new_account[1]);
											
											$_SESSION['add_listing']['account'] = $new_account;
											
											$rlSmarty -> assign( 'isLogin', $_SESSION['username'] );
											define( 'IS_LOGIN', true );
											
											$account_info = $_SESSION['account'];
											$rlSmarty -> assign_by_ref('account_info', $account_info);
											
											/* send login details to user */
											$mail_tpl = $rlMail -> getEmailTemplate('quick_account_created');
											$find = array('{username}', '{login}', '{password}');
											$replace = array($register_data['name'], $new_account[0], $new_account[1]);
											
											$mail_tpl['body'] = str_replace($find, $replace, $mail_tpl['body']);
											$rlMail -> send($mail_tpl, $register_data['email']);
										}
									}
								}
								
								if ( !$auth_try )
								{
									$errors[] = $lang['quick_signup_fail'];
								}
							}
							
							/* check security image code */
							if ( $config['security_img_add_listing'] )
							{
								$ses_code = $_POST['security_code'];
								
								if ( $ses_code != $_SESSION['ses_security_code'] )
								{
									$errors[] = $lang['security_code_incorrect'];
								}
							}
			
							/* save crossed cats ids */
							$rlSmarty -> assign_by_ref('exp_cats', implode(',', $_POST['crossed_categories']));
							$_SESSION['exp_categories'] = $_POST['crossed_categories'];
							
							/* check form fields */
							if ( $back_errors = $rlCommon -> checkDynamicForm( $data, $category_fields ) )
							{
								foreach ( $back_errors as $error )
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
	
							if ( !$errors )
							{
								$reefless -> loadClass( 'Actions' );
								$reefless -> loadClass( 'Listings' );
								$reefless -> loadClass( 'Resize' );
								
								$rlHook -> load('addListingAdditionalInfo');
								
								/* edit tmp listing data */
								if ( $listing_id )
								{
									$rlListings -> edit( $listing_id, $plan_info, $data, $category_fields );
									$rlHook -> load('afterListingEdit');
								}
								/* add tmp listing */
								else
								{
									if ( $rlListings -> create( $plan_info, $data, $category_fields ) )
									{
										$listing_id = $rlListings -> id;
										$_SESSION['add_listing']['listing_id'] = $listing_id;
					
										$rlHook -> load('afterListingCreate');
										
										/* tmp category controller */
										if ( $_SESSION['add_listing']['tmp_id'] == $_SESSION['add_listing']['tmp_category_id'] )
										{
											$rlDb -> query("UPDATE `". RL_DBPREFIX ."tmp_categories` SET `Account_ID` = '{$account_info['ID']}' WHERE `ID` = '{$_SESSION['add_listing']['tmp_category_id']}' LIMIT 1");
										}
										else
										{
											$rlDb -> query("DELETE FROM `". RL_DBPREFIX ."tmp_categories` WHERE `ID` = '{$_SESSION['add_listing']['tmp_category_id']}' LIMIT 1");
										}
									}
								}
								
								/* redirect to related controller */
								$redirect = SEO_BASE;
								$redirect .= $config['mod_rewrite'] ? $page_info['Path'] .'/'. $category['Path'] .'/'. $next_step['path'] .'.html' : '?page='. $page_info['Path'] .'&id='. $category['ID'] .'&step=' .$next_step['path'];
								$reefless -> redirect( null, $redirect );
							}
						}
					}
					
					break;
					
				case 'photo'://step 4
					if ( $_POST['step'] == 'photo' )
					{
						/* redirect to related controller */
						$redirect = SEO_BASE;
						$redirect .= $config['mod_rewrite'] ? $page_info['Path'] .'/'. $category['Path'] .'/'. $next_step['path'] .'.html' : '?page='. $page_info['Path'] .'&id='. $category['ID'] .'&step=' .$next_step['path'];
						$reefless -> redirect( null, $redirect );
					}
					else
					{
						$config['img_crop_interface'] = false;
						
						$rlXajax -> registerFunction( array( 'makeMain', $rlListings, 'ajaxMakeMain' ) );
						$rlXajax -> registerFunction( array( 'editDesc', $rlListings, 'ajaxEditDesc' ) );
						$rlXajax -> registerFunction( array( 'reorderPhoto', $rlListings, 'ajaxReorderPhoto' ) );
						
						$rlSmarty -> assign_by_ref('allowed_photos', $plan_info['Image']);
						
						$max_file_size = str_replace('M', '', ini_get('upload_max_filesize'));
						$rlSmarty -> assign_by_ref( 'max_file_size', $max_file_size );
					}
					
					break;
					
				case 'video'://step 5
					$video_allow = $plan_info['Video_unlim'] ? 'unlim' : $plan_info['Video'];
					
					$max_file_size = ini_get('upload_max_filesize');
					$rlSmarty -> assign_by_ref('max_file_size', $max_file_size);
					
					/* get listing video */
					$rlDb -> setTable('listing_video');
					$videos = $rlDb -> fetch( array('ID', 'Video', 'Preview', 'Type'), array( 'Listing_ID' => $listing_id ), "ORDER BY `Position`" );
					$rlSmarty -> assign_by_ref( 'videos', $videos );
					
					$video_allow -= count($videos);
					$rlSmarty -> assign_by_ref('video_allow', $video_allow);
					
					if ( $_POST['step'] == 'video' )
					{
						if ( $video_allow || $plan_info['Video_unlim'] )
						{
							$reefless -> loadClass('Resize');
							$reefless -> loadClass('Crop');
							
							if ( $rlListings -> uploadVideo( $_POST['type'], $_POST['type'] == 'youtube' ? $_POST['youtube_embed'] : $_FILES, $listing_id ) )
							{
								$reefless -> refresh();
							}
						}
						
						/* redirect to related controller */
						if ( $_POST['redirect'] )
						{
							$redirect = SEO_BASE;
							$redirect .= $config['mod_rewrite'] ? $page_info['Path'] .'/'. $category['Path'] .'/'. $next_step['path'] .'.html' : '?page='. $page_info['Path'] .'&id='. $category['ID'] .'&step=' .$next_step['path'];
							$reefless -> redirect( null, $redirect );
						}
					}
					
					$rlXajax -> registerFunction( array( 'deleteVideo', $rlListings, 'ajaxDelVideoFile' ) );
					$rlXajax -> registerFunction( array( 'reorderVideo', $rlListings, 'ajaxReorderVideo' ) );
					
					break;
					
				case 'checkout'://step 6
					if ( $_POST['step'] == 'checkout' )
					{
						$gateway = $_POST['gateway'];
						if ( !$gateway )
						{
							$errors[] = $lang['notice_payment_gateway_does_not_chose'];
						}
						else
						{
							/* get listing title */
							$listing_title = $rlListings -> getListingTitle( $category['ID'], $listing_data, $listing_type['Key'] );
							
							/* save payment details */
							$item_name = $lang[$plan_info['Type'] . '_plan'];
							
							$cancel_url = SEO_BASE;
							$cancel_url .= $config['mod_rewrite'] ? $page_info['Path'] .'/'. $category['Path'] .'/'. $get_step .'.html?canceled' : '?page='. $page_info['Path'] .'&id='. $category['ID'] .'&step=' .$get_step .'&canceled';
							
							$success_url = SEO_BASE;
							$success_url .= $config['mod_rewrite'] ? $page_info['Path'] .'/'. $category['Path'] .'/'. $next_step['path'] .'.html' : '?page='. $page_info['Path'] .'&id='. $category['ID'] .'&step=' .$next_step['path'];
							
							$complete_payment_info = array(
								'item_name' => $item_name . ' - '. $lang['listing'] .' #' . $listing_id . ' ('. $listing_title .')',
								'category_id' => $category['ID'],
								'plan_info' => $plan_info,
								'item_id' => $listing_id,
								'account_id' => $account_info['ID'],
								'gateway' => $gateway,
								'callback' => array(
									'class' => 'rlListings',
									'method' => 'upgradeListing',
									'cancel_url' => $cancel_url,
									'success_url' => $success_url
								)
							);
							$_SESSION['complete_payment'] = $complete_payment_info;
							
							$rlHook -> load('addListingCheckoutPreRedirect');
							
							/* redirect */
							$redirect = SEO_BASE;
							$redirect .= $config['mod_rewrite'] ? $pages['payment'] .'.html' : '?page='. $pages['payment'];
							$reefless -> redirect(null, $redirect);
						}
					}
					
					break;
					
				case 'done':// the last step
					$return_link = SEO_BASE;
					$return_link .= $config['mod_rewrite'] ? $page_info['Path'] .'.html' : '?page='. $page_info['Path'];
					$rlSmarty -> assign_by_ref('return_link', $return_link);
					
					/* go out from this step */
					if ( $_SESSION['done'] )
							continue;
				
					if ( $plan_info['Featured'] && (!$plan_info['Advanced_mode'] || ($plan_info['Advanced_mode'] && $_SESSION['add_listing']['listing_type'] == 'featured')) )
					{
						$featured = true;
					}
					
					/* get listing title */
					$listing_title = $rlListings -> getListingTitle( $category['ID'], $listing_data, $listing_type['Key'] );
					
					// change listing status
					$update_status = array(
						'fields' => array(
							'Status' => $config['listing_auto_approval'] ? 'active' : 'pending',
							'Pay_date' => 'NOW()',
							'Featured_ID' => $featured ? $plan_info['ID'] : 0,
							'Featured_date' => $featured ? 'NOW()' : ''
						),
						'where' => array(
							'ID' => $listing_id
						)
					);
					$rlActions -> updateOne($update_status, 'listings');
					
					$rlHook -> load('afterListingDone');

					// free listing or exist/free package mode
					if ( ($plan_info['Type'] == 'package' && ($plan_info['Package_ID'] || $plan_info['Price'] <= 0) ) || ($plan_info['Type'] == 'listing' && $plan_info['Price'] <= 0) )
					{
						// available package mode
						if ( $plan_info['Type'] == 'package' && $plan_info['Package_ID'] && !$_SESSION['complete_payment'] )
						{
							if ( $plan_info['Listings_remains'] != 0 )
							{
								$update_entry = array(
									'fields' => array(
										'Listings_remains' => $plan_info['Listings_remains'] - 1
									),
									'where' => array(
										'ID' => $plan_info['Package_ID']
									)
								);
								
								if ( $plan_info[ucfirst($_SESSION['add_listing']['listing_type']) .'_listings'] != 0 )
								{
									$update_entry['fields'][ucfirst($_SESSION['add_listing']['listing_type']) .'_remains'] = $plan_info[ucfirst($_SESSION['add_listing']['listing_type']) .'_remains'] - 1;
								}
								
								$rlActions -> updateOne($update_entry, 'listing_packages');
							}
							
							/* set paid status */
							$paid_status = $lang['purchased_packages'];
						}
						// free package mode
						elseif ( $plan_info['Type'] == 'package' && !$plan_info['Package_ID'] && $plan_info['Price'] <= 0 )
						{
							$insert_entry = array(
								'Account_ID' => $account_info['ID'],
								'Plan_ID' => $plan_info['ID'],
								'Listings_remains' => $plan_info['Listing_number'] - 1,
								'Standard_remains' => $plan_info['Standard_listings'],
								'Featured_remains' => $plan_info['Featured_listings'],
								'Type' => 'package',
								'Date' => 'NOW()',
								'IP' => $_SERVER['REMOTE_ADDR']
							);
							
							if ( $plan_info['Featured'] && $plan_info['Advanced_mode'] && $_SESSION['add_listing']['listing_type'] == 'standard' )
							{
								$insert_entry['Standard_remains']--;
							}
							
							if ( $plan_info['Featured'] && $plan_info['Advanced_mode'] && $_SESSION['add_listing']['listing_type'] == 'featured' )
							{
								$insert_entry['Featured_remains']--;
							}
							
							$rlActions -> insertOne($insert_entry, 'listing_packages');
							
							/* set paid status */
							$paid_status = $lang['package_plan'] .'('. $lang['free'] .')';
						}
						// limited listing mode
						elseif ($plan_info['Type'] == 'listing' && $plan_info['Limit'] > 0 )
						{
							/* update/insert limited plan using entry */
							if ( empty($plan_info['Using']) )
							{
								$plan_using_insert = array(
									'Account_ID' => $account_info['ID'],
									'Plan_ID' => $plan_info['ID'],
									'Listings_remains' => $plan_info['Limit']-1,
									'Type' => 'limited',
									'Date' => 'NOW()',
									'IP' => $_SERVER['REMOTE_ADDR']
								);
								
								$rlActions -> insertOne($plan_using_insert, 'listing_packages');
							}
							else
							{
								$plan_using_update = array(
									'fields' => array(
										'Account_ID' => $account_info['ID'],
										'Plan_ID' => $plan_info['ID'],
										'Listings_remains' => $plan_info['Using']-1,
										'Type' => 'limited',
										'Date' => 'NOW()',
										'IP' => $_SERVER['REMOTE_ADDR']
									),
									'where' => array(
										'ID' => $plan_info['Plan_using_ID']
									)
								);
								
								$rlActions -> updateOne($plan_using_update, 'listing_packages');
							}
							
							/* set paid status */
							$paid_status = $plan_info['Price'] ? $lang['not_paid'] : $lang['free'];
						}
	
						/* recount category listings count */
						if ( $config['listing_auto_approval'] )
						{
							$rlCategories -> listingsIncrease( $category['ID'] );
							
							/* crossed categories handler */
							if ( $plan_info['Cross'] > 0 && !empty($_POST['crossed_categories']) )
							{
								foreach ($_POST['crossed_categories'] as $incrace_cc)
								{
									$rlCategories -> listingsIncrease( $incrace_cc );
								}
							}
						}
						
						/* send message to listing owner */
						$mail_tpl = $rlMail -> getEmailTemplate( $config['listing_auto_approval'] ? 'free_active_listing_created' : 'free_approval_listing_created' );
						
						$link = SEO_BASE;
						if ( $config['listing_auto_approval'] )
						{
							$link .= $config['mod_rewrite'] ? $pages['lt_'. $listing_type['Key']] .'/'. $category['Path'] .'/'. $rlSmarty -> str2path($listing_title) .'-'. $listing_id .'.html' : '?page='.$pages['lt_'. $listing_type['Key']].'&amp;id='. $listing_id ;
						}
						else
						{
							$link .= $config['mod_rewrite'] ? $pages['my_'. $listing_type['Key']] .'.html' : '?page='.$pages['my_'. $listing_type['Key']];
						}
	
						$mail_tpl['body'] = str_replace( array('{username}', '{link}'), array($account_info['Username'], '<a href="'.$link.'">'.$link.'</a>'), $mail_tpl['body'] );
						$rlMail -> send( $mail_tpl, $account_info['Mail'] );
					}
					
					/* send admin notification */
					$mail_tpl = $rlMail -> getEmailTemplate( 'admin_listing_added' );
					
					$m_find = array('{username}', '{link}', '{date}', '{status}', '{paid}');
					$m_replace = array(
						$account_info['Username'], 
						'<a href="'. RL_URL_HOME . ADMIN .'/index.php?controller=listings&amp;action=view&amp;id='. $listing_id .'">'. $listing_title . '</a>', 
						date(str_replace(array('b', '%'), array('M', ''), RL_DATE_FORMAT)),
						$lang[$config['listing_auto_approval'] ? 'active' : 'pending'],
						$paid_status
					);
					$mail_tpl['body'] = str_replace( $m_find, $m_replace, $mail_tpl['body'] );
					
					if ( $config['listing_auto_approval'] )
					{
						$mail_tpl['body'] = preg_replace('/\{if activation is enabled\}(.*)\{\/if\}/', '', $mail_tpl['body']);
					}
					else
					{
						$activation_link = RL_URL_HOME . ADMIN .'/index.php?controller=listings&amp;action=remote_activation&amp;id='. $listing_id . '&amp;hash='. md5($rlDb -> getOne('Date', "`ID` = '{$listing_id}'", 'listings'));
						$activation_link = '<a href="'.$activation_link.'">'.$activation_link.'</a>';
						$mail_tpl['body'] = preg_replace('/(\{if activation is enabled\})(.*)(\{activation_link\})(.*)(\{\/if\})/', '$2 '. $activation_link .' $4', $mail_tpl['body']);
					}
					
					$rlMail -> send( $mail_tpl, $config['notifications_email'] );
					
					/* clear saved step for current listing */
					$update_step = array(
						'fields' => array(
							'Last_step' => '',
							'Cron' => '0',
							'Cron_notified' => '0',
							'Cron_featured' => '0'
						),
						'where' => array(
							'ID' => $listing_id
						)
					);
					$rlActions -> updateOne($update_step, 'listings');
					
					/* save done status */
					$_SESSION['done'] = true;
				
					break;
			}
			// end steps handler (switch)
		}
	}
}
// step 1 (select a category)
else
{
	if ( !$_REQUEST['xjxfun'] )
	{
		$rlHook -> load('addListingGetCats');
		$sections = $rlCategories -> getCatTree(0, $account_info['Abilities'], true);
		$rlSmarty -> assign_by_ref( 'sections', $sections );
	}
	
	$rlXajax -> registerFunction( array( 'addTmpCategory', $rlCategories, 'ajaxAddTmpCategory' ) );
}

$rlXajax -> registerFunction( array( 'getCatLevel', $rlCategories, 'ajaxGetCatLevel' ) );
$rlXajax -> registerFunction( array( 'openTree', $rlCategories, 'ajaxOpenTree' ) );
$rlXajax -> registerFunction( array( 'removeTmpFile', $reefless, 'ajaxRemoveTmpFile' ) );

$rlHook -> load('addListingBottom');

//$callback_class = 'rlListings';
//$callback_method = 'upgradeListing';
//$$callback_class -> $callback_method( 214, 25, 157, 'NEWTRANSACTION', 'paypal', 9.95 );