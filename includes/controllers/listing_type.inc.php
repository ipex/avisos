<?php

/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: LISTING_TYPE.INC.PHP
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

$listing_type_key = str_replace('lt_', '', $page_info['Key']);

$listing_type = $rlListingTypes -> types[$listing_type_key];
$rlSmarty -> assign_by_ref('listing_type', $listing_type);

/* re-define listing type variables */
if ( !$config['mod_rewrite'] && $listing_type['Submit_method'] == 'get' )
{
	$listing_type['Submit_method'] = 'post';
}
$refine_block_controller = 'blocks'. RL_DS .'refine_search.tpl';
$rlSmarty -> assign_by_ref('refine_block_controller', $refine_block_controller);

/* get listing ID */
$listing_id = $config['mod_rewrite'] ? (int)$_GET['listing_id'] : (int)$_GET['id'];

if ( !empty($listing_id) )
{
	require_once( RL_INC . 'controllers' . RL_DS . 'listing_details.inc.php' );
	$page_info['Controller'] = 'listing_details';
}
else
{
	$post_form_key = $_SESSION['post_form_key'] = $_POST['post_form_key'] ? $_POST['post_form_key'] : $_SESSION['post_form_key'];
	$post_form_key = $post_form_key ? $post_form_key : $listing_type['Key'] .'_quick';
	
	/* is requested form splited by tabs */
	$tab_form = 0 === strpos($post_form_key, $listing_type['Key'] .'_tab') ? true : false;

	$form_key = $listing_type['Advanced_search'] ? $listing_type['Key'] .'_advanced' : $post_form_key;
	
	$reefless -> loadClass( 'Listings' );
	$reefless -> loadClass( 'Search' );

	/* emulation */
	if ( $_SESSION[$listing_type_key .'_post'] && $_REQUEST['action'] != 'search' )
	{
		$_POST = $_SESSION[$listing_type_key .'_post'];
	}
	
	/* search results mode */
	if ( $_GET['nvar_1'] == $search_results_url || $_GET['nvar_2'] == $search_results_url || isset($_GET[$search_results_url]) )
	{
		/* assign "save search" icon to the nav bar */
		$navIcons[] = '<span class="'. $listing_type_key .'" id="save_search"><a class="save_search" title="'. $lang['save_search'] .'" href="javascript:void(0)"> <span></span> </a> <a href="javascript:void(0)">'. $lang['save_search'] .'</a></span>';
		$rlSmarty -> assign_by_ref('navIcons', $navIcons);
		
		$rlSmarty -> assign('search_results', true);
		$data = $_SESSION[$listing_type_key .'_post'] = $_REQUEST['f'] ? $_REQUEST['f'] : $_SESSION[$listing_type_key .'_post'];
		
		/* re-assign POST for refine search block */
		if ( $_POST['f'] )
		{
			$_POST = $_POST['f'];
			unset($_SESSION['keyword_search'], $_SESSION['keyword_search_data'], $_SESSION['keyword_search_sort_by'], $_SESSION['keyword_search_sort_type']);
		}
		
		/* sorting */
		if ( $_REQUEST['sort_by'] || $_REQUEST['sort_type'] )
		{
			$data['sort_by'] = $_SESSION[$listing_type_key .'_sort_by'] = $_REQUEST['sort_by'];
			$data['sort_type'] = $_SESSION[$listing_type_key .'_sort_type'] = $_REQUEST['sort_type'];
		}
		elseif ( $_SESSION[$listing_type_key .'_sort_by'] )
		{
			$data['sort_by'] = $_REQUEST['sort_by'] = $_SESSION[$listing_type_key .'_sort_by'];
			$data['sort_type'] = $_REQUEST['sort_type'] = $_SESSION[$listing_type_key .'_sort_type'];
		}
	
		$pInfo['current'] = (int)$_GET['pg'];
	
		/* advanced search results emulation */
		if ( ($_GET['nvar_1'] == $advanced_search_url || isset($_GET[$advanced_search_url])) && $listing_type['Advanced_search'] )
		{
			$form_key = $listing_type['Key'] .'_advanced';
			
			/* add bread crumbs item */
			$bread_crumbs[] = array(
				'name' => $lang['advanced_search'],
				'title' => $lang['back_to_advanced_search'],
				'path' => $config['mod_rewrite'] ? $page_info['Path'] .'/'. $advanced_search_url : 'index.php?'. $advanced_search_url
			);
			
			/* emulation */
			$rlSmarty -> assign('advanced_mode', true);
			$concat = $config['mod_rewrite'] ? '/' : '&amp;';
			$search_results_url = $advanced_search_url . $concat . $search_results_url;
			
			$_SESSION[$listing_type['Key'] .'_advanced'] = true;
		}
		
		// get sorting fields
		$rlSearch -> getFields( $tab_form ? $post_form_key : $listing_type_key .'_quick', $listing_type_key );
		$sorting_fields = $rlSearch -> fields;
		
		foreach ($sorting_fields as $key => $value)
		{
			if ( $sorting_fields[$key]['Details_page'] )
			{
				$sorting[$sorting_fields[$key]['Key']] = $sorting_fields[$key];
			}
		}
		$rlSmarty -> assign_by_ref( 'sorting', $sorting );
		
		/* define sort field */
		$sort_by = empty($_REQUEST['sort_by']) ? $_SESSION['search_sort_by'] : $_REQUEST['sort_by'];
		
		if ( !empty($sorting[$sort_by]) )
		{
			$order_field = $sorting[$sort_by]['Key'];
			$data['sort_by'] = $sort_by;
			$rlSmarty -> assign_by_ref( 'sort_by', $sort_by );
		}
		
		/* define sort type */
		$sort_type = empty($_REQUEST['sort_type']) ? $_SESSION['search_sort_type'] : $_REQUEST['sort_type'];
		if ( $sort_type )
		{
			$data['sort_type'] = $sort_type = in_array( $sort_type, array('asc', 'desc') ) ? $sort_type : false ;
			$rlSmarty -> assign_by_ref( 'sort_type', $sort_type );
		}
		
		/* get current search form */
		$rlSearch -> getFields( $post_form_key, $listing_type_key, $tab_form );
		$rlSmarty -> assign( 'fields_list', $rlSearch -> fields );
		
		//$rlSearch -> fields = array_merge($rlSearch -> fields, $sorting_fields);
		//unset($sorting_fields);
		
		/* get listings */
		$listings = $rlSearch -> search( $data, $listing_type_key, $pInfo['current'], $config['listings_per_page'] );
		$rlSmarty -> assign_by_ref( 'listings', $listings );
	
		$pInfo['calc'] = $rlSearch -> calc;
		$rlSmarty -> assign_by_ref( 'pInfo', $pInfo );
		
		if ( $listings )
		{
			$page_info['name'] = str_replace(array('{number}', '{type}'), array($pInfo['calc'], $listing_type['name']), $lang['listings_found']);
		}
		
		$rlHook -> load('searchMiddle');

		/* add bread crumbs item */
		$meta_title = $data['sort_by'] ? str_replace('{field}', $sorting[$data['sort_by']]['name'], $lang['search_results_sorting_mode']) : $lang['search_results'];
		
		if ( $pInfo['current'] )
		{
			$meta_title = $meta_title . str_replace('{page}', $pInfo['current'], $lang['title_page_part']);
		}
		
		$bread_crumbs[] = array(
			'title' => $meta_title,
			'name' => $lang['search_results']
		);
		
		/* get refine search form */
		if ( !$form = $rlSearch -> buildSearch($form_key, $listing_type_key) )
		{
			/* get quick search form if advanced is empty */
			$form_key = str_replace('_advanced', '_quick', $form_key);
			$form = $rlSearch -> buildSearch($form_key, $listing_type_key);
		}
		$rlSmarty -> assign_by_ref('refine_search_form', $form);
		
		/* save current page number */
		if ( $_GET['pg'] )
		{
			$_SESSION[$listing_type['Key'] .'_pageNum'] = (int)$_GET['pg'];
		}
		else
		{
			unset($_SESSION[$listing_type['Key'] .'_pageNum']);
		}
	}
	/* advanced search form build mode */
	elseif ( ($_GET['nvar_1'] == $advanced_search_url || isset($_GET[$advanced_search_url])) && $listing_type['Advanced_search'] )
	{
		/* order field values imulation */
		if ( $_SESSION[$listing_type_key .'_sort_by'] )
		{
			$_REQUEST['sort_by'] = $_SESSION[$listing_type_key .'_sort_by'];
			$_REQUEST['sort_type'] = $_SESSION[$listing_type_key .'_sort_type'];
		}
		
		$rlSmarty -> assign('advanced_search', true);
		$form_key = $listing_type['Key'] .'_advanced';
		
		/* get search forms */
		$form = $rlSearch -> buildSearch($form_key, $listing_type_key);
		$rlSmarty -> assign_by_ref('search_form', $form);
		
		/* get current search form */
		$rlSearch -> getFields( $form_key, $listing_type_key );
		$rlSmarty -> assign_by_ref( 'fields_list', $rlSearch -> fields );
		
		/* add bread crumbs item */
		$bread_crumbs[] = array(
			'name' => $lang['advanced_search']
		);
		
		/* re-define listing type variables */
		if ( $listing_type['Search_display'] == 'content_and_block' )
		{
			unset($blocks['ltsb_'. $listing_type_key]);
			$rlCommon -> defineBlocksExist($blocks);
		}
	}
	/* browse/quick search mode */
	else
	{
		/* get search form */
		if ( $listing_type['Search'] )
		{
			/* split to tabs, in "content_and_block" mode only */
			if ( $listing_type['Search_display'] == 'content_and_block' && $listing_type['Arrange_field'] && $listing_type['Arrange_search'] )
			{
				$field_values = explode(',', $listing_type['Arrange_values']);
				foreach ($field_values as $field_value)
				{
					$form_key = $listing_type_key .'_tab'. $field_value;
					
					if ( $search_form = $rlSearch -> buildSearch($form_key, $listing_type_key) )
					{
						$forms[$field_value] = $search_form;
						$forms[$field_value][0]['Tab_value'] = $field_value;
					}
				}
				
				unset($search_form);
				
				$rlSmarty -> assign_by_ref('search_form', $forms);
				$rlSmarty -> assign_by_ref('tabs_search', $field_values);
			}
			else
			{
				$form_key = $listing_type['Key'] .'_quick';
				
				/* get search forms */
				$form = $rlSearch -> buildSearch($form_key, $listing_type_key);
				$rlSmarty -> assign_by_ref( $listing_type['Search_display'] == 'content_and_block' ? 'search_form' : 'refine_search_form', $form);
			}
			
			/* disable advanced search */
			unset($_SESSION[$listing_type['Key'] .'_advanced']);
			unset($_SESSION[$listing_type['Key'] .'_pageNum']);
		}
		
		/* re-define listing type variables */
		if ( $listing_type['Search_display'] == 'content_and_block' )
		{
			unset($blocks['ltsb_'. $listing_type_key]);
			$rlCommon -> defineBlocksExist($blocks);
		}
		
		/* get category info */
		$reefless -> loadClass('Categories');
		
		if ($config['mod_rewrite'])
		{
			$category_path = $_GET['rlVareables'];
			
/*			if ( $config['lang'] != RL_LANG_CODE )
			{
				$category_path = trim(str_replace($page_info['Path'], '', $category_path), '/');
			}*/
			$category = $rlCategories -> getCategory( false, $category_path);
		}
		else
		{
			$category = $rlCategories -> getCategory( $_GET['category'] );
		}
		$category['ID'] = empty($category) ? 0 : $category['ID'];
		$rlSmarty -> assign_by_ref('category', $category);

		/* meta data rewite */
		$page_info['meta_description'] = empty($category['meta_description']) ? $page_info['meta_description'] : $category['meta_description'];
		$page_info['meta_keywords'] = empty($category['meta_keywords']) ? $page_info['meta_keywords'] : $category['meta_keywords'];
		
		if ( $listing_type['Cat_position'] != 'hide' )
		{
			/* get current category children */
			$categories = $rlCategories -> getCategories( $category['ID'], $listing_type_key);
			$rlSmarty -> assign_by_ref('categories', $categories);
		}
		
		if ( $category['ID'] )
		{
			/* re-define categories block position to TOP */
			$listing_type['Cat_position'] = 'top';

			/* clear search cache */
			unset($_SESSION[$listing_type['Key'] .'_post'], $_SESSION['keyword_search_data']);
			
			/* get sorting form fields */
			$sorting_fields = $rlListings -> getFormFields( $category['ID'], 'short_forms', $listing_type_key );
		
			foreach ($sorting_fields as $key => $value)
			{
				if ( $sorting_fields[$key]['Details_page'] )
				{
					$sorting[$sorting_fields[$key]['Key']] = $sorting_fields[$key];
				}
			}
			unset($sorting_fields);
			$rlSmarty -> assign_by_ref( 'sorting', $sorting );
			
			/* define sort field */
			$sort_by = empty($_GET['sort_by']) ? $_SESSION['browse_sort_by'] : $_GET['sort_by'];
			if ( !empty($sorting[$sort_by]) )
			{
				$order_field = $sorting[$sort_by]['Key'];
			}
		
			$_SESSION['browse_sort_by'] = $sort_by;
			$rlSmarty -> assign_by_ref( 'sort_by', $sort_by );
			
			/* define sort type */
			$sort_type = empty($_GET['sort_type']) ? $_SESSION['browse_sort_type'] : $_GET['sort_type'] ;
			$_SESSION['browse_sort_type'] = $sort_type = in_array( $sort_type, array('asc', 'desc') ) ? $sort_type : false ;
			$rlSmarty -> assign_by_ref( 'sort_type', $sort_type );

			$pInfo['current'] = (int)$_GET['pg'];
		
			/* get listings */
			$listings = $rlListings -> getListings( $category['ID'], $order_field, $sort_type, $pInfo['current'], $config['listings_per_page'] );
			$rlSmarty -> assign_by_ref( 'listings', $listings );
			
			$pInfo['calc'] = $rlListings -> calc;
			$rlSmarty -> assign_by_ref( 'pInfo', $pInfo );
			
			$rlHook -> load('browseMiddle');
			
			if ( !empty($listings) )
			{
				/* build rss */
				$rss = array(
					'item' => 'category',
					'id' => $category['ID'],
					'title' => $category['title'] ? $category['title'] : $category['name']
				);
				$rlSmarty -> assign_by_ref( 'rss', $rss );
				
				/* enable print page */
				$print = array(
					'item' => 'browse',
					'id' => $category['ID']
				);
				$rlSmarty -> assign_by_ref('print', $print);
			}
			
			/* add navigation icon */
			if ( !$category['Lock'] && !$listing_type['Admin_only'] )
			{
				$add_listing_link = SEO_BASE;
				$add_listing_link .= $config['mod_rewrite'] ? $pages['add_listing'].'/'.$category['Path'].'/'. $steps['plan']['path'] .'.html' : '?page='.$pages['add_listing'].'&amp;step='. $steps['plan']['path'] .'&amp;id='.$category['ID'];
				$navIcons[] = '<a class="post_ad" title="'. str_replace('{category}', $category['name'], $lang['add_listing_to']) .'" href="'.$add_listing_link.'"> <span></span> </a>';
				$rlSmarty -> assign_by_ref('navIcons', $navIcons);
			}
			
			$cat_bread_crumbs = $rlCategories -> getBreadCrumbs($category['Parent_ID'], null, $listing_type);
			$cat_bread_crumbs = array_reverse($cat_bread_crumbs);
	
			if (!empty($cat_bread_crumbs))
			{
				foreach ($cat_bread_crumbs as $bKey => $bVal)
				{
					$cat_bread_crumbs[$bKey]['path'] = $config['mod_rewrite'] ? $page_info['Path'] .'/'. $bVal['Path'] : $page_info['Path'] .'&amp;category='. $bVal['ID'];
					
					$cat_bread_crumbs[$bKey]['title'] = $bVal['Key'];
					$cat_bread_crumbs[$bKey]['category'] = true;
					$bread_crumbs[] = $cat_bread_crumbs[$bKey];
				}
			}
			
			$page_info['name'] = $category['name'];
			
			if ( $pInfo['current'] > 1 )
			{
				$bc_page = str_replace('{page}', $pInfo['current'], $lang['title_page_part']);
			}
			
			/* add bread crumbs item */
			$bread_crumbs[] = array(
				'title' => $category['title'] ? $category['title'] . $bc_page : $category['name'] . $bc_page,
				'name' => $category['name'],
				'category' => true
			);
			$rlHook -> load('browseBCArea');
			
			$add_listing_href = $config['mod_rewrite'] ? SEO_BASE . $pages['add_listing'] . '/' . $category['Path'] . '/'. $steps['plan']['path'] .'.html' : RL_URL_HOME . 'index.php?page=' . $pages['add_listing'] . '&amp;step='. $steps['plan']['path'] .'&amp;id=' . $category['ID'];
			$rlSmarty -> assign_by_ref( 'add_listing_href', $add_listing_href );
		}
	}
	
	/* register ajax methods */
	$rlXajax -> registerFunction( array( 'saveSearch', $rlSearch, 'ajaxSaveSearch' ) );
}
