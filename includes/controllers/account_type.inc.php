<?php

/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: ACCOUNT_TYPE.INC.PHP
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

/* define account type */
$account_type_key = str_replace('at_', '', $page_info['Key']);
$account_type = $rlAccount -> getTypeDetails($account_type_key);

$rlHook -> load('accountTypeTop');

if ( $account_type && $account_type['Page'] )
{
	$rlSmarty -> assign_by_ref('account_type', $account_type);
	
	/* register ajax methods */
	$reefless -> loadClass('Message');
	$rlXajax -> registerFunction( array( 'contactOwner', $rlMessage, 'ajaxContactOwner' ) );
	
	/* request account details */
	$account_id = (int)$_GET['id'] ? (int)$_GET['id'] : $_GET['nvar_1'];
	$account = $rlAccount -> getProfile($account_id);
	
	if ( $account )
	{
		if ( !empty($account) )
		{
			/* assign host details */
			if ( $config['account_wildcard'] && isset($_GET['wildcard']) && $config['mod_rewrite'] )
			{
				if ( defined('RL_MOBILE') && RL_MOBILE )
				{
					$lang_url_home = str_replace($rlValid -> getDomain(RL_URL_HOME), $_SERVER['HTTP_HOST'], $rlSmarty -> get_template_vars('rlBaseLang'));
					$rlSmarty -> assign( 'rlBaseLang',  $lang_url_home);
				}
				else
				{
					$lang_url_home = str_replace($rlValid -> getDomain(RL_URL_HOME), $_SERVER['HTTP_HOST'], RL_URL_HOME);
					$rlSmarty -> assign('lang_url_home', $lang_url_home);
				}
			}
			
			/* assign account details */
			$rlSmarty -> assign_by_ref('account', $account);
		
			/* populate tabs */
			$tabs = array(
				'details' => array(
					'key' => 'details',
					'name' => $lang['account_info']
				),
				'listings' => array(
					'key' => 'listings',
					'name' => $lang['account_listings']
				),
				'map' => array(
					'key' => 'map',
					'name' => $lang['map']
				),
			);
			$rlSmarty -> assign_by_ref('tabs', $tabs);
			
			/* add bread crumbs step */
			$bread_crumbs[] = array(
				'name' => $lang['personal_page']
			);
			$page_info['name'] = $account['Full_name'];
			
			/* fields for sorting */
			$sorting = array(
				'join_date' => array(
					'name' => $lang['date'],
					'field' => 'Date'
				),
				'category' => array(
					'name' => $lang['category'],
					'field' => 'Category_ID'
				)
			);
			$rlSmarty -> assign_by_ref( 'sorting', $sorting );

			/* define sort field */
			$sort_by = $_SESSION['account_sort_by'] = $_REQUEST['sort_by'] ? $_REQUEST['sort_by'] : $_SESSION['account_sort_by'];
			if ( $_REQUEST['sort_by'] )
			{
				$rlSmarty -> assign('sorting_mode', true);
			}
			$sort_by = $sort_by ? $sort_by : 'join_date';
			if ( !empty($sorting[$sort_by]) )
			{
				$order_field = $sorting[$sort_by]['Key'];
				$data['sort_by'] = $sort_by;
				$rlSmarty -> assign_by_ref( 'sort_by', $sort_by );
			}
			
			/* define sort type */
			$sort_type = $_SESSION['account_sort_type'] = $_REQUEST['sort_type'] ? $_REQUEST['sort_type'] : $_SESSION['account_sort_type'];
			$sort_type = $sort_type ? $sort_type : 'desc';
			if ( $sort_type )
			{
				$data['sort_type'] = $sort_type = in_array( $sort_type, array('asc', 'desc') ) ? $sort_type : false ;
				$rlSmarty -> assign_by_ref( 'sort_type', $sort_type );
			}
			
			$pInfo['current'] = (int)$_GET['pg'];
			
			if ( !is_int($account_id) )
			{
				$account_id = $account['ID'];
			}
			
			/* get account listings */
			$reefless -> loadClass('Listings');
			$listings = $rlListings -> getListingsByAccount($account['ID'], $sort_by, $sort_type, $pInfo['current'], $config['listings_per_page']);
			$rlSmarty -> assign_by_ref('listings', $listings);
			
			$pInfo['calc'] = $rlListings -> calc;
			$rlSmarty -> assign_by_ref( 'pInfo', $pInfo );

			/* get amenties */
			if ( $config['map_amenities'] )
			{
				$rlDb -> setTable('map_amenities');
				$amenities = $rlDb -> fetch(array('Key', 'Default'), array('Status' => 'active'), "ORDER BY `Position`");
				$amenities = $rlLang -> replaceLangKeys( $amenities, 'map_amenities', array('name') );
				$rlSmarty -> assign_by_ref('amenities', $amenities);
			}
			
			/* enable rss/xml listings feed for account */
			if ( $listings )
			{
				/* build rss */
				$rss = array(
					'item' => 'account-listings',
					'id' => $account['Own_address'],
					'title' => str_replace('{account_name}', $account['Full_name'], $lang['account_rss_feed_caption'])
				);
				$rlSmarty -> assign_by_ref( 'rss', $rss );
			}
			
			/* define fields for Google Map */
			$location = $rlAccount -> mapLocation;
			
			if ( $account['Loc_latitude'] && $account['Loc_longitude'] )
			{
				$location['direct'] = $account['Loc_latitude'] .','. $account['Loc_longitude'];
			}
			
			if ( !empty($location) && $config['map_module'] )
			{
				$rlSmarty -> assign_by_ref( 'location', $location );
			}
			else
			{
				unset($tabs['map']);
			}
			
			$rlHook -> load('accountTypeAccount');
		}
		else
		{
			$errors[] = $lang['account_request_fail'];
		}
	}
	/* account search */
	else
	{
		/* clear saved data */
		if ( !$_GET['nvar_1'] && !isset($_GET[$search_results_url]) )
		{
			if($_SESSION['at_data_'. $account_type_key])
			{
				$_POST = $_SESSION['at_data_'. $account_type_key];
				unset($_SESSION['at_data_'. $account_type_key]);
			}
		}
		
		/* populate tabs */
		$tabs = array(
			'characters' => array(
				'key' => 'characters',
				'name' => $lang['alphabetic_search']
			),
			'search' => array(
				'key' => 'search',
				'name' => $lang['advanced_search']
			)
		);
		$rlSmarty -> assign_by_ref('tabs', $tabs);

		/* advanced search */
		$fields = $rlAccount -> buildSearch($account_type['ID']);
		$rlSmarty -> assign_by_ref('fields', $fields);
		
		/* alphabet bar */
		$alphabet = explode(',', $lang['alphabet_characters']);
		$rlSmarty -> assign_by_ref('alphabet', $alphabet);
		
		/* advanced search results */
		if ( $_GET['nvar_1'] == $search_results_url || isset($_GET[$search_results_url]) )
		{
			/* add link to nav bar */
			$return_link = SEO_BASE;
			$return_link .= $config['mod_rewrite'] ? $page_info['Path'] .'.html#modify' : '?page='. $page_info['Path'] .'#modify';
			$navIcons[] = '<a title="'. $lang['modify_search_criterion'] .'" href="'. $return_link .'">&larr; '. $lang['modify_search_criterion'] .'</a>';
			
			$rlSmarty -> assign_by_ref('navIcons', $navIcons);
			
			/* add bread crumbs step */
			$bread_crumbs[] = array(
				'name' => $lang['search_results']
			);
			
			$rlSmarty -> assign('search_results', 'search');
			
			/* build sorting fields */
			$sorting = array_reverse($fields);
			$sorting['join_date'] = array(
				'Key' => 'Date',
				'name' => $lang['join_date']
			);
			$sorting = array_reverse($sorting);
			$rlSmarty -> assign_by_ref('sorting', $sorting);
			
			/* get accounts */
			$data = $_SESSION['at_data_'. $account_type_key] = $_POST['f'] ? $_POST['f'] : $_SESSION['at_data_'. $account_type_key];
			$pInfo['current'] = (int)$_GET['pg'];

			/* define sort field */
			$sort_by = $_SESSION[$account_type_key .'_sort_by'] = $_REQUEST['sort_by'] ? $_REQUEST['sort_by'] : $_SESSION[$account_type_key .'_sort_by'];
			$sort_by = $sort_by ? $sort_by : 'join_date';
			if ( !empty($sorting[$sort_by]) )
			{
				$order_field = $sorting[$sort_by]['Key'];
				$data['sort_by'] = $sort_by;
				$rlSmarty -> assign_by_ref( 'sort_by', $sort_by );
			}
			
			/* define sort type */
			$sort_type = $_SESSION[$account_type_key .'_sort_type'] = $_REQUEST['sort_type'] ? $_REQUEST['sort_type'] : $_SESSION[$account_type_key .'_sort_type'];
			$sort_type = $sort_type ? $sort_type : 'desc';
			if ( $sort_type )
			{
				$data['sort_type'] = $sort_type = in_array( $sort_type, array('asc', 'desc') ) ? $sort_type : false ;
				$rlSmarty -> assign_by_ref( 'sort_type', $sort_type );
			}
			
			$dealers = $rlAccount -> searchDealers( $data, $fields, $config['dealers_per_page'], $pInfo['current'], $account_type );
			$rlSmarty -> assign_by_ref('dealers', $dealers);
			
			$pInfo['calc'] = $rlAccount -> calc;
			$rlSmarty -> assign_by_ref( 'pInfo', $pInfo );
			
			/* change page name, add found accounts count */
			$page_info['name'] = str_replace(array('{number}'), array($pInfo['calc']), $lang['accounts_found']);
		}
		else
		{
			/* define requested char */
			$_GET['nvar_1'] = $_GET['nvar_1'] === '0' ? '0-9' : $_GET['nvar_1'];
			$char = in_array($_GET['nvar_1'], $alphabet) ? $_GET['nvar_1'] : $_REQUEST['character'];
			$request_char = $char ? true : false;
			$rlSmarty -> assign('alphabet_mode', $request_char);
			
			$char = $char ? $char : $alphabet[0];
			$rlSmarty -> assign_by_ref('char', $char);
			
			if ( $request_char )
			{
				$pInfo['current'] = (int)$_GET['pg'];
			}
			
			$alphabet_dealers = $rlAccount -> getDealersByChar( $char, $config['dealers_per_page'], $pInfo['current'], $account_type );
			$rlSmarty -> assign_by_ref('alphabet_dealers', $alphabet_dealers);
			
			$pInfo['calc_alphabet'] = $rlAccount -> calc_alphabet;
			$rlSmarty -> assign_by_ref( 'pInfo', $pInfo );
			
			if ( $request_char )
			{
				/* add bread crumbs item */
				if ( $pInfo['current'] > 1 )
				{
					$bc_page = str_replace('{page}', $pInfo['current'], $lang['title_page_part']);
				}
				
				$alp_title = str_replace('{char}', $char, $lang['search_by']);
				$bread_crumbs[] = array(
					'title' =>  $alp_title . $bc_page,
					'name' => $lang['alphabetic_search']
				);
				
				$page_info['name'] = $alp_title;
			}
		}
		
		$rlHook -> load('accountTypeAccountsList');
	}
}
else
{
	$errors[] = $lang['account_type_page_access_restricted'];
}