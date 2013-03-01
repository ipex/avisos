<?php

/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: RLCATEGORIES.CLASS.PHP
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

class rlCategories extends reefless 
{
	/**
	* @var language class object
	**/
	var $rlLang;

	/**
	* @var actions class object
	**/
	var $rlActions;
	
	/**
	* @var notice class object
	**/
	var $rlNotice;
	
	/**
	* @var valid class object
	**/
	var $rlValid;
	
	/**
	* @var common class object
	**/
	var $rlCommon;
	
	/**
	* @var category sections
	**/
	var $sections;
	
	/**
	* @var selected fields array
	**/
	var $fields;
	
	/**
	* class constructor
	**/
	function rlCategories()
	{
		global $rlLang, $rlActions, $rlNotice, $rlValid, $rlCommon, $rlSmarty;

		$this -> rlLang  = & $rlLang;
		$this -> rlValid  = & $rlValid;
		$this -> rlActions = & $rlActions;
		$this -> rlNotice = & $rlNotice;
		$this -> rlCommon = & $rlCommon;
	}
	
	/**
	* get data format
	*
	* @param string $key - data format key
	* @param int $id     - data format ID
	*
	* @return array - data list
	**/
	function getDataFormat( $key = null, $id = false )
	{
		echo 'DF';
		$this -> setTable( 'data_formats' );
		if ( $id )
		{
			$where = array( 'Status' => 'active', 'Parent_ID' => $id );
		}
		else
		{
			$id = $this -> fetch( 'ID', array( 'Key' => $key ) );
			$where = array( 'Status' => 'active', 'Parent_ID' => $id[0]['ID'] );
		}

		$data = $this -> fetch( array('ID', 'Key'), $where, 'ORDER BY `ID`, `Key`', null );
		$data = $this -> rlLang -> replaceLangKeys( $data, 'data_formats', array( 'name' ) );
		$this -> resetTable();

		return $data;
	}
	
	/**
	* get all data format
	*
	* @param string $key - format key
	* @param string $order - order type (alphabetic/position)
	*
	* @return array - data formats list
	**/
	function getDF( $key = false, $order = false )
	{
		global $rlCache, $config;
		
		if ( !$key )
			return;
		
		/* get data from cache */
		if ( $config['cache'] )
		{
			$df = $rlCache -> get('cache_data_formats', $key);

			if ( $df )
			{
				$df = $this -> rlLang -> replaceLangKeys( $df, 'data_formats', array( 'name' ) );

				$order = !$order && $GLOBALS['data_formats'] ? $GLOBALS['data_formats'][$key]['Order_type'] : $order;
					
				if ( $order && in_array($order, array('alphabetic', 'position')) )
				{
					$this -> rlArraySort($df, $order == 'alphabetic' ? 'name' : 'Position');
				}
				
				return $df;
			}
			
			return false;
		}
		
		/* get data from DB */
		$this -> setTable( 'data_formats' );

		/* DO NOT SET ANOTHER FIELD FOR ORDER, ID ONLY */
		$format_id = $this -> getOne('ID', "`Key` = '{$key}'");

		$data = $this -> fetch( array('ID', 'Parent_ID', 'Key`, CONCAT("data_formats+name+", `Key`) AS `pName', 'Default'), array('Status' => 'active', 'Parent_ID' => $format_id), 'ORDER BY `ID`, `Key`', null );
		$data = $this -> rlLang -> replaceLangKeys( $data, 'data_formats', array( 'name' ) );
		
		$this -> resetTable();
		
		if ( $order && in_array($order, array('alphabetic', 'position')) )
		{
			$this -> rlArraySort($data, $order == 'alphabetic' ? 'name' : 'Position');
		}

		return $data;
	}
	
	/**
	* delete listing field's group
	*
	* @package ajax
	*
	* @param string $key - group key
	*
	**/
	function ajaxDeleteFGroup( $key = false )
	{
		global $_response, $lang;

		if ( !$key )
			return false;
		
		$lang_keys[] = array(
			'Key' => 'listing_groups+name+' . $key,
		);

		// delete group field relations
		if ( !$GLOBALS['config']['trash'] )
		{
			$this -> deleteGroupRelations( $key );
		}
		
		$this -> rlActions -> delete( array( 'Key' => $key ), array('listing_groups', 'lang_keys'), null, 1, $key, $lang_keys );
		$del_mode = $this -> rlActions -> action;
		
		$_response -> script("
			listingGroupsGrid.reload();
			printMessage('notice', '{$lang['group_' . $del_mode]}');
		");
		
		return $_response;
	}
	
	/**
	* delete group relations
	*
	* @param string $key - group key
	*
	**/
	function deleteGroupRelations( $key = false )
	{
		if ( !$key )
			return false;
		
		// get category id
		$group_id = $this -> getOne('ID', "`Key` = '{$key}'", 'listing_groups');
		
		if ( $group_id )
		{
			// delete field relations from main form
			$sql = "DELETE FROM `" . RL_DBPREFIX . "listing_relations` WHERE `Group_ID` = '{$group_id}'";
			$this -> query( $sql );
	
			// delete field relations from search form
			$sql = "DELETE FROM `" . RL_DBPREFIX . "search_forms_relations` WHERE `Group_ID` = '{$group_id}'";
			$this -> query( $sql );
		}
	}
	
	/**
	* get all categories
	*
	* @param int $id - category ID
	* @param mixed $type - listing type key
	* @param bool $include_sections - include sections
	*
	* @return array - listing types list
	**/
	function getCategories( $parent = 0, $type = false, $include_sections = false )
	{
		global $select, $where, $rlListingTypes, $config, $rlCache;
	
		$types = $type ? array($rlListingTypes -> types[$type]) : $rlListingTypes -> types;

		/* get categories from cache */
		if ( $config['cache'] )
		{
			foreach ( $types as $type )
			{
				$categories = $rlCache -> get('cache_categories_by_parent', $parent, $type);
				$categories = $this -> rlLang -> replaceLangKeys( $categories, 'categories', array( 'name' ) );

				$GLOBALS['rlHook'] -> load('phpCategoriesGetCategoriesCache', $categories);

				if ( $type['Cat_order_type'] == 'alphabetic' )
				{
					$this -> rlArraySort($categories, 'name');
				}
				
				if ( $type['Cat_show_subcats'] )
				{
					foreach ($categories as $key => &$value)
					{
						if ( $value['sub_categories'] )
						{
							$value['sub_categories'] = $this -> rlLang -> replaceLangKeys( $value['sub_categories'], 'categories', array( 'name' ) );

							if ( $type['Cat_order_type'] == 'alphabetic' )
							{
								$this -> rlArraySort($value['sub_categories'], 'name');
								$categories[$key]['sub_categories'] = $value['sub_categories'];
							}
						}
					}
				}
				
				if ( $include_sections )
				{
					$sections[$type['Key']] = array(
						'ID' => $type['ID'],
						'name' => $type['name'],
						'Key' => $type['Key'],
						'Categories' => $categories
					);
				}
			}
			
			if ( $include_sections )
			{
				$categories = $sections;
			}
			
			return $categories;
		}
		
		$sections = array();
		foreach ( $types as $type )
		{
			$where = array(
				'Status' => 'active',
				'Parent_ID' => $parent
			);
			if ( $type )
			{
				$where['Type'] = $type['Key'];
			}

			$select = array( 'ID', 'Path', 'Count', "Key`, CONCAT('categories+name+', `Key`) AS `pName`, CONCAT('categories+title+', `Key`) AS `pTitle", 'Type');

			/* load hook for front-end only */
			if ( !defined('REALM') )
			{
				$GLOBALS['rlHook'] -> load('getCategoriesModifySelect');
			}

			$categories = $this -> fetch( $select, $where, "ORDER BY `Position`", null, 'categories' );
			//$categories = $this -> rlLang -> replaceLangKeys( $categories, 'categories', array( 'name' ) );

			$GLOBALS['rlHook'] -> load('phpCategoriesGetCategories');

			if ( $type['Cat_order_type'] == 'alphabetic' )
			{
				$this -> rlArraySort($categories, 'name');
			}

			/* get subcategories */
			if ( $type['Cat_show_subcats'] )
			{
				foreach ($categories as $key => $value)
				{
					$sub_limit = !empty($type['Cat_subcat_number']) ? $type['Cat_subcat_number'] : null;
					$this -> calcRows = true;
					$subCategories = $this -> fetch( array( 'ID', 'Path', 'Key'), array('Status' => 'active', 'Parent_ID' => $categories[$key]['ID']), "ORDER BY `Position`", $sub_limit, 'categories' );
					$this -> calcRows = false;
					$subCategories = $this -> rlLang -> replaceLangKeys( $subCategories, 'categories', array( 'name' ) );

					if ( $type['Cat_order_type'] == 'alphabetic' )
					{
						$this -> rlArraySort($subCategories, 'name');
					}

					if (!empty($subCategories))
					{
						$categories[$key]['sub_categories'] = $subCategories;
						$categories[$key]['sub_categories_calc'] = $this -> calcRows;	
					}

					unset($subCategories);
				}
			}
			
			if ( $include_sections )
			{
				if ( !empty( $categories ) )
				{
					$sections[$type['Key']] = array(
						'ID' => $type['ID'],
						'name' => $type['name'],
						'Key' => $type['Key'],
						'Categories' => $categories,
					);
				}
			}
		}
		
		if ( $include_sections )
		{
			$categories = $sections;
		}

		/* "with sections" mode */
		if ( $sections && $parent == 0 )
		{
			if ( !$this -> sections )
			{
				return $categories;
			}
			
			$cat_sections = $this -> sections;
			
			foreach ( $categories as $cKey => $cVal )
			{
				$cat_sections[$cVal['Type']]['Categories'][] = $cVal;
			}
			unset($categories);
			
			$categories = $cat_sections;
		}

		return $categories;
	}

	/**
	* get categories tree
	*
	* @param int $parent_id - category parent_id
	* @param string/array $type - listing type
	* @param bool $group_by_sections - group categories by sections mode
	*
	* @return array - category array
	**/
	function getCatTree( $parent_id = 0, $type = false, $group_by_sections = false )
	{
		global $sql, $rlListingTypes, $account_info;

		$sql  = "SELECT `T1`.`ID`, `T1`.`Path`, `T1`.`Level`, `T1`.`Type`, `T1`.`Key`, `T1`.`Lock`, `T1`.`Add`, ";
		
		$GLOBALS['rlHook'] -> load('getCatTreeFields');
		
		$sql .= "IF(`T2`.`ID` AND `T2`.`Status` = 'active', `T2`.`ID`, IF( `T3`.`ID`, 1, 0 )) `Sub_cat`";
		$sql .= " FROM `".RL_DBPREFIX."categories` AS `T1` ";
		$sql .= "LEFT JOIN `".RL_DBPREFIX."categories` AS `T2` ON `T1`.`ID` = `T2`.`Parent_ID` AND `T2`.`Status` = 'active' ";
		$sql .= "LEFT JOIN `".RL_DBPREFIX."tmp_categories` AS `T3` ON `T1`.`ID` = `T3`.`Parent_ID` AND `T3`.`Account_ID` = '{$account_info['ID']}' ";
		$sql .= "WHERE `T1`.`Status` = 'active' ";
		
		if ( $type && $parent_id == 0 )
		{
			$type = is_array($type) ? $type : explode(',', $type);
			$sql .= "AND (`T1`.`Type` = '". implode("' OR `T1`.`Type` = '", $type) ."') ";
		}
		
		$sql .= "AND `T1`.`Parent_ID` = '{$parent_id}' ";
		$sql .= "GROUP BY `T1`.`Key` ";
		$sql .= "ORDER BY `T1`.`Position` ";
		
		$categories = $this -> getAll( $sql );
		$categories = $this -> rlLang -> replaceLangKeys( $categories, 'categories', array( 'name' ) );
		
		/* group by sections mode */
		if ( $group_by_sections && $parent_id == 0 )
		{
			$categories_grouped = $rlListingTypes -> types;
			
			if ( $type )
			{
				foreach ( $categories_grouped as $key => $value )
				{
					if ( !in_array($value['Key'], $type) )
					{
						unset($categories_grouped[$key]);
					}
				}
			}
			
			foreach ($categories as $key => $value)
			{
				if ( $categories_grouped[$value['Type']] )
				{
					$categories_grouped[$value['Type']]['Categories'][] = $value;
				}
			}
			
			/* ordering */
			foreach ( $categories_grouped as $key => $value )
			{
				if ( $value['Cat_order_type'] == 'alphabetic' )
				{
					$this -> rlArraySort($value['Categories'], 'name');
					$categories_grouped[$key]['Categories'] = $value['Categories'];
				}
			}
			
			$categories = $categories_grouped;
			unset($categories_grouped);
		}
		else
		{
			$type = (!$type || $type == "false") && $parent_id ? $this -> getOne("Type", "`ID` = ".$parent_id, "categories") : false;
			if ( $type )
			{
				if ( $rlListingTypes -> types[$type]['Cat_order_type'] == 'alphabetic' )
				{
					$this -> rlArraySort($categories, 'name');
				}
			}
		}
		
		return $categories;
	}
	
	/**
	* get category level
	*
	* @package xajax
	*
	* @param int $category_id - category ID
	* @param string $type - listing type
	* @param string $tpl - tpl postfix name, example: category_level_{$tpl}.tpl
	* @param string $function - js callback method
	* @param string $postfix - postfix
	*
	* @return array - sub categories array
	**/
	function ajaxGetCatLevel( $category_id, $type = false, $tpl = false, $function = false, $postfix = false )
	{
		global $_response, $rlSmarty, $rlListingTypes, $account_info;
		
		/* get category infor */
		$category = $this -> getCategory($category_id);
		$rlSmarty -> assign_by_ref( 'category', $category );
		
		/* get related categories */
		$categories = $this -> getCatTree($category_id, $type);

		/* custom category for current user detecting */
		if ( $rlListingTypes -> types[$category['Type']]['Cat_custom_adding'] )
		{
			$custom_cat_in = $this -> fetch(array('ID', 'Name'), array('Account_ID' => $account_info['ID'], 'Parent_ID' => $category_id), "ORDER BY `Date`", null, 'tmp_categories');
			if ( !empty($custom_cat_in) )
			{
				foreach ($custom_cat_in as $key => $value)
				{
					$categories[] = array(
						'ID' => $custom_cat_in[$key]['ID'],
						'name' => $custom_cat_in[$key]['Name'],
						'Tmp' => true
					);
				}
			}
		}

		if ( $categories || ($rlListingTypes -> types[$category['Type']]['Cat_custom_adding'] && $category['Add']) )
		{
			$rlSmarty -> assign_by_ref('categories', $categories);
			
			$file = 'blocks' . RL_DS;
			$file .= $tpl ? "category_level_{$tpl}.tpl" : 'category_level.tpl';
			
			$postfix = $postfix ? '_'. $postfix : '';
			$_response -> script("xajaxFix = $('#tree_cat_{$category_id}{$postfix}').find('input').attr('checked');");
			$_response -> append("tree_cat_{$category_id}{$postfix}", 'innerHTML', $rlSmarty -> fetch( $file, null, null, false ));
			$_response -> script("
				$('#tree_cat_{$category_id}{$postfix}>ul').fadeIn('normal');
				$('#tree_cat_{$category_id}{$postfix}>img').addClass('opened');
				$('#tree_cat_{$category_id}{$postfix}>span.tree_loader').fadeOut(function(){
					$(this).hide();
				});
				
				if ( xajaxFix == 'checked' )
				{
					$('#tree_cat_{$category_id}{$postfix}>label>input').attr('checked', true);
				}

				flynax.treeLoadLevel('{$tpl}', '{$function}');
			");
			
			if ( $function )
			{
				$_response -> call($function);
			}
		}
		
		return $_response;
	}
	
	/**
	* detect parent item with enabled including mode | recursive method
	*
	* @param int $id - category id
	*
	* @return bool
	**/
	function detectParentIncludes( $id )
	{
		if ( $id == 0 )
		{
			return false;
		}
		
		/* get parent */
		$parent = $this -> fetch(array('Parent_ID', 'Add_sub', 'Add'), array('ID' => $id), null, 1, 'categories', 'row');
		
		if (!empty($parent))
		{
			/* check relations */
			if ( $parent['Add_sub'] == '1' )
			{
				return $parent['Add'];
			}
			return $this -> detectParentIncludes($parent['Parent_ID']);
		}
		else
		{
			return false;
		}
	}
	
	/**
	* get parent points
	*
	* @param array $ids - categories ids
	*
	* @assign array - parent points IDs
	**/
	function parentPoints( $ids = false )
	{
		global $rlListingTypes, $rlSmarty;
		
		if ( empty($ids) || empty($ids[0]) )
			return false;
		
		$sql = "SELECT `ID`, `Parent_ID`, `Type` FROM `". RL_DBPREFIX ."categories` WHERE (`ID` = ". implode(" OR `ID` = ", $ids) .")";
		$parents = $this -> getAll($sql);
		
		$checked = array();
		
		foreach ($parents as $cat)
		{
			if ( !$cat['Parent_ID'] || in_array($cat['Parent_ID'], $checked) )
				continue;
			
			$bc = $this -> getBreadCrumbs( $cat['Parent_ID'], false, $rlListingTypes -> types[$cat['Type']]);
			$checked[] = $cat['Parent_ID'];
			
			$bc = array_reverse($bc);
			foreach ($bc as $bc_item)
			{
				if ( false === array_search($bc_item['ID'], $out) )
				{
					$out[] = $bc_item['ID'];
				}
			}
		}
		
		$rlSmarty -> assign_by_ref('parentPoints', $out);
		
		return $out;
	}

	/**
	* get category bread crumbs | recursive method
	*
	* @param int $parent_id - category parent_id
	* @param array $path - path array
	* @param array $type - listing type info
	*
	* @return array - path array
	**/
	function getBreadCrumbs( $parent_id = false, $path = false, $type = false )
	{
		global $rlCache, $config;
		
		if ( !$parent_id )
		{
			return false;
		}
		
		if ( $config['cache'] )
		{
			$cat_info = $rlCache -> get('cache_categories_by_type', $parent_id, $type);
	
			if ( !empty($cat_info) )
			{
				$cat_info = $this -> rlLang -> replaceLangKeys( $cat_info, 'categories', array( 'name' ) );
				$path[]  = $cat_info;
			}
			else
			{
				$path = false;
			}
	
			if ( !empty($cat_info['Parent_ID']) )
			{
				return $this -> getBreadCrumbs( $cat_info['Parent_ID'], $path, $type );
			}
			else
			{
				return $path;
			}
		}
		else
		{
			$cat_info = $this -> fetch( array('ID', 'Key', 'Parent_ID', 'Path'), array('ID' => $parent_id, 'Type' => $type['Key']), null, null, 'categories', 'row' );
			
			if ( !empty($cat_info) )
			{
				$cat_info = $this -> rlLang -> replaceLangKeys( $cat_info, 'categories', array( 'name' ) );
				$path[]  = $cat_info;
			}
			else
			{
				$path = false;
			}

			if ( !empty($cat_info['Parent_ID']) )
			{
				return $this -> getBreadCrumbs( $cat_info['Parent_ID'], $path, $type );
			}
			else
			{
				return $path;
			}
		}
	}
	
	/**
	* category walker check/update/delete categories or listings inside subcategories | recursive method
	*
	* @param int $category_id - start category id
	* @param string $mode - action mode: detect|delete|trash|restore|replace
	* @param array $data - recurcive variable
	* @param int $new_id - new category ID | in replace mode only
	* @param int $initial_category - initial category ID
	*
	* @return array - mixed data
	**/
	function categoryWalker( $category_id = false, $mode = false, $data = array(), $new_id = false, $initial_category = false )
	{
		if ( !$mode )
		{
			trigger_error('categoryWalker() error, no mode selected', E_WARNING);
			$GLOBALS['rlDebug'] -> logger("categoryWalker() error, no mode selected");
			
			return false;
		}
		
		if ( !$category_id )
			return;
		
		$this -> setTable('categories');
		
		switch ($mode){
			
			case 'detect':
				/* get child categories */
				$child = $this -> fetch( array('ID', 'Parent_ID'), array('Parent_ID' => $category_id), "AND `Status` <> 'trash'" );
				$listings = $this -> getRow("SELECT COUNT(`ID`) AS `count` FROM `". RL_DBPREFIX ."listings` WHERE (`Category_ID` = '{$category_id}' OR FIND_IN_SET('{$category_id}', `Crossed`) > 0) AND `Status` <> 'trash'");

				if ( $listings['count'] )
				{
					$data['listings'] += $listings['count'];
				}

				if ( !empty($child) )
				{
					foreach ($child as $key => $value)
					{
						$data['categories']++;
						
						$data = $this -> categoryWalker($child[$key]['ID'], 'detect', $data);
					}
				}

				return $data;
				break;
				
			case 'delete':
				/* get child categories */
				$child = $this -> fetch( array('ID', 'Parent_ID', 'Key'), array('Parent_ID' => $category_id) );
				$listings = $this -> fetch( array('ID'), array('Category_ID' => $category_id), null, null, 'listings');
				
				/* delete all listings */
				if (!empty($listings))
				{
					$this -> loadClass('Listings');
					foreach ($listings as $key => $value)
					{
						$GLOBALS['rlListings'] -> deleteListingData($listings[$key]['ID']);
					}
					
					$this -> query("DELETE FROM `".RL_DBPREFIX."listings` WHERE `Category_ID` = '{$category_id}'");
				}

				if ( !empty($child) )
				{
					foreach ($child as $key => $value)
					{
						/* delete categories */
						$this -> query("DELETE FROM `".RL_DBPREFIX."categories` WHERE `ID` = '{$child[$key]['ID']}' LIMIT 1");
						
						/* delete lang keys */
						$this -> query("DELETE FROM `".RL_DBPREFIX."lang_keys` WHERE `Key` = 'categories+title+{$child[$key]['Key']}' OR `Key` = 'categories+name+{$child[$key]['Key']}' OR `Key` = 'categories+des+{$child[$key]['Key']}' OR `Key` = 'categories+meta_description+{$child[$key]['Key']}' OR `Key` = 'categories+meta_keywords+{$child[$key]['Key']}'");
						
						/* delete category field relations */
						$this -> deleteCatRelations($child[$key]['ID']);
						
						$this -> categoryWalker($child[$key]['ID'], 'delete');
					}
				}
				break;
				
			case 'trash':
				/* get child categories */
				$child = $this -> fetch( array('ID', 'Parent_ID', 'Key'), array('Parent_ID' => $category_id) );
				$listings = $this -> fetch( array('ID`, UNIX_TIMESTAMP(`Pay_date`) AS `Pay_date', 'Category_ID'), array('Category_ID' => $category_id), null, null, 'listings');
				
				/* trash all listings */
				if (!empty($listings))
				{
					foreach ($listings as $key => $value)
					{
						if ($listings[$key]['Pay_date'])
						{
							$this -> listingsDecrease($listings[$key]['Category_ID']);
						}
					}
					$this -> query("UPDATE `".RL_DBPREFIX."listings` SET `Status` = 'trash' WHERE `Category_ID` = '{$category_id}'");
				}

				if ( !empty($child) )
				{
					foreach ($child as $key => $value)
					{
						/* trash categories */
						$this -> query("UPDATE `".RL_DBPREFIX."categories` SET `Status` = 'trash' WHERE `ID` = '{$child[$key]['ID']}' LIMIT 1");
						
						$data = $this -> categoryWalker($child[$key]['ID'], 'detect', $data);
					}
				}
				break;
				
			case 'restore':
				/* get child categories */
				$child = $this -> fetch( array('ID', 'Parent_ID', 'Key'), array('Parent_ID' => $category_id) );
				$listings = $this -> fetch( array('ID`, UNIX_TIMESTAMP(`Pay_date`) AS `Pay_date', 'Category_ID'), array('Category_ID' => $category_id), null, null, 'listings');
				
				/* restore all listings */
				if (!empty($listings))
				{
					foreach ($listings as $key => $value)
					{
						if ($listings[$key]['Pay_date'])
						{
							$this -> listingsIncrease($listings[$key]['Category_ID']);
						}
					}
					$this -> query("UPDATE `".RL_DBPREFIX."listings` SET `Status` = 'active' WHERE `Category_ID` = '{$category_id}'");
				}

				if ( !empty($child) )
				{
					foreach ($child as $key => $value)
					{
						/* restore categories */
						$this -> query("UPDATE `".RL_DBPREFIX."categories` SET `Status` = 'active' WHERE `ID` = '{$child[$key]['ID']}' LIMIT 1");
						
						$data = $this -> categoryWalker($child[$key]['ID'], 'detect', $data);
					}
				}
				break;
				
			case 'replace':
				$new_id = (int)$new_id;

				if ( $new_id )
				{
					$find_id = $initial_category ? $initial_category : $category_id;
					$initial_category_data = $this -> fetch(array('Path', 'Tree'), array('ID' => $find_id), null, 1, 'categories', 'row');
					$replace_category_data = $this -> fetch(array('Path', 'Tree'), array('ID' => $new_id), null, 1, 'categories', 'row');
					
					/* update sub-categories */
					$this -> query("UPDATE `". RL_DBPREFIX ."categories` SET `Parent_ID` = '{$new_id}', `Path` = REPLACE(`Path`, '{$initial_category_data['Path']}', '{$replace_category_data['Path']}'), `Tree` = REPLACE(`Tree`, '{$initial_category_data['Tree']}', '{$replace_category_data['Tree']}') WHERE `Parent_ID` = '{$category_id}'");
					
					/* update listings */
					$this -> query("UPDATE `". RL_DBPREFIX ."listings` SET `Category_ID` = '{$new_id}' WHERE `Category_ID` = '{$category_id}'");
				}
				
				if ( $child = $this -> fetch(array('ID', 'Parent_ID', 'Key'), array('Parent_ID' => $category_id)) )
				{
					foreach ($child as $child_category)
					{
						$this -> categoryWalker($child_category['ID'], 'replace', false, $new_id, $category_id);
					}
				}
				
				break;
		}
	}
	
	/**
	* get category relate paths
	*
	* @param int $parent_id - category parent_id
	* @param string $path - path
	*
	* @return string - path
	**/
	function getCatPath( $parent_id = false, $path = false )
	{
		$cat_info = $this -> fetch( array('Parent_ID', 'Path'), array('ID' => $parent_id), null, 1, 'categories', 'row' );

		if ( !empty($cat_info['Path']) )
		{
			$path = $cat_info['Path'] .'/'. $path;
		}
		
		return $path;
	}
	
	/**
	* get category
	*
	* @param int $id - category ID
	* @param int $path - category path
	*
	* @return array - category info
	**/
	function getCategory( $id = false, $path = false )
	{
		if ( !$id && !$path )
		{
			return false;
		}
		
		$this -> rlValid -> sql( $path );
		
		if (defined('REALM') && REALM == 'admin')
		{
			$status = "AND `Status` <> 'trash'";
		}
		else
		{
			$status = "AND `Status` = 'active'";
		}

		if( $id )
		{
			$where['ID'] = (int)$id;
		}
		elseif ( $path )
		{
			$where['Path'] = $path;
		}
		
		$category = $this -> fetch( array( 'ID', 'Parent_ID', 'Path', 'Key', 'Count', 'Lock', 'Type', 'Level', 'Add'), $where, $status, null, 'categories', 'row' );
		if ( empty($category) )
		{
			return false;
		}
		$category = $this -> rlLang -> replaceLangKeys( $category, 'categories', array( 'name', 'title', 'des', 'meta_description', 'meta_keywords' ) );

		return $category;
	}
	
	/**
	* get all categories
	*
	* @param int $types - category types
	*
	* @return array - listing types list
	**/
	function getCatTitles( $types = false )
	{
		global $config, $rlListingTypes, $counter, $rlCache, $rlListingTypes;
		
		if ( $types )
		{
			$main_type = is_array($types) ? $types[0] : $types;
		}
		else
		{
			$main_type = current($rlListingTypes -> types);
			$main_type = $main_type['Key'];
		}
		
		$tmp_categories = array();
		
		/* get categories from the cache */
		if ( $config['cache'] )
		{
			if ( is_array($types) )
			{
				foreach ($types as $type)
				{
					if ( $categories_stack = $rlCache -> get('cache_categories_by_type', $type) )
					{
						$tmp_categories = array_merge($tmp_categories, $categories_stack);
						unset($categories_stack);
					}
				}
			}
			else
			{
				if ( $types )
				{
					$tmp_categories = $rlCache -> get('cache_categories_by_type', $types);
				}
				else
				{
					foreach ($rlListingTypes -> types as $type)
					{
						if ( $categories_stack = $rlCache -> get('cache_categories_by_type', $type['Key']) )
						{
							$tmp_categories = array_merge($tmp_categories, $categories_stack);
							unset($categories_stack);
						}
					}
				}
			}
		}
		/* get categories from the DB */
		else
		{
			if ( !empty($types) )
			{
				$where = is_array($types) ? "AND (`Type` = '". implode("' OR `Type` = '", $types) ."')" : "AND `Type` = '{$types}' ";
			}
			$tmp_categories = $this -> fetch( array( 'ID', 'Parent_ID', 'Tree', 'Key', 'Level' ), array( 'Status' => 'active' ), "{$where} ORDER BY `Tree` ASC", null, 'categories' );
		}
		
		if ( empty($tmp_categories) )
			return;
		
		// alphabetic order
		if ( $rlListingTypes -> types[$main_type]['Cat_order_type'] == 'alphabetic' )
		{
			$tmp_categories = $this -> rlLang -> replaceLangKeys( $tmp_categories, 'categories', array( 'name' ) );
			return $this -> getCatTitleBuild($this -> getCatTitleSort($tmp_categories));
		}
		// position order
		else
		{
			foreach ($tmp_categories as $index => $category)
			{
				$categories[$category['Tree']] = $category;
				$categories[$category['Tree']]['pName'] = 'categories+name+'. $category['Key'];
				$categories[$category['Tree']]['margin'] = $category['Level'] * 10 + 5;
			}
			unset($tmp_categories);
			
			ksort($categories);
		}
		
		return $categories;
	}
	
	/**
	* sort multi-livel categories array by category name | RECURSIVE METHOD
	*
	* @param array $categories - single level categories array
	* @param int $level - start level, 0 is recomended value to start recursion
	* @param int $parent - parent category to start ordering, 0 is recomended value to start recursion
	* 
	* all other parameters are for internal method use only
	*
	* @return array - sordered categories
	**/
	function getCatTitleSort( &$categories, $level = 0, $parent = 0, $out = false, $log = false, $bread_crumbs = false )
	{
		foreach ($categories as $index => $category)
		{
			if ( $category['Parent_ID'] == $parent )
			{
				$bread_crumbs[$category['ID']] = $category['Parent_ID'];
				$tmp[$category['ID']] = $category;
				$tmp[$category['ID']]['pName'] = 'categories+name+'. $category['Key'];
				$tmp[$category['ID']]['margin'] = $category['Level'] * 10 + 5;
				$log[$category['Level']][$category['ID']] = $category['ID'];
				unset($categories[$index]);
			}
		}
		
		$this -> rlArraySort($tmp, 'name');
		
		foreach ( $tmp as $t_item )
		{
			$ttmp[$t_item['ID']] = $t_item;
		}
		$tmp = $ttmp;
		unset($ttmp);
		
		$next_parent = array_shift($log[$level]);

		if ( empty($out) )
		{
			$out = $tmp;
		}
		else
		{
			if ( $tmp )
			{
				$relations = $this -> getCatTitleBC($bread_crumbs, $parent);
				eval('$out'. $relations .' = $tmp;');
			}
		}
		unset($tmp, $relations);
		
		if ( empty($log[$level]) )
		{
			unset($log[$level]);
			$level++;
		}
		
		//if ( !empty($log) && !empty($categories) )
		if ( $next_parent && !empty($categories) )
		{
			return $this -> getCatTitleSort($categories, $level, $next_parent, $out, $log, $bread_crumbs);
		}
		else
		{
			return $out;
		}
	}

	/**
	* get category relations | RECURSIVE METHOD
	*
	* @param array $bread_crumbs - data to work with
	* @param int $parent - parent category to start ordering, 0 is recomended value to start recursion
	* 
	* all other parameters are for internal use only
	*
	* @return string - relations string available for eval use
	**/
	function getCatTitleBC( &$bread_crumbs, $parent = false, $relations = false )
	{
		$relations = "[{$parent}]['Sub_cats']". $relations;
		if ( $bread_crumbs[$parent] )
		{
			return $this -> getCatTitleBC($bread_crumbs, $bread_crumbs[$parent], $relations);
		}
		else
		{
			return $relations;
		}
	}
	
	/**
	* convert multi-level categories array to single array | RECURSIVE METHOD
	*
	* @param array $categories - categories array
	*
	* @return array - single level arrat
	**/
	function getCatTitleBuild( &$categories )
	{
		foreach ($categories as $index => $category)
		{
			unset($category['Sub_cats']);
			$out[] = $category;
			if ( !empty($categories[$index]['Sub_cats']) )
			{
				$out = array_merge($out, $this -> getCatTitleBuild($categories[$index]['Sub_cats']));
			}
		}
		
		return $out;
	}
	
	/**
	* get parent category form relations
	*
	* @param int $id - category id
	*
	* @return array - fields form
	**/
	function getParentCatRelations( $id = false, $noRecursive = false )
	{
		$sql = "SELECT `T1`.`Group_ID`, `T1`.`ID`, `T1`.`Category_ID`, `T2`.`Key`, `T1`.`Fields`, `T2`.`Display`, ";
		$sql .= "CONCAT('listing_groups+name+', `T2`.`Key`) AS `pName`, `T2`.`ID` AS `Group` ";
		$sql .= "FROM `". RL_DBPREFIX ."listing_relations` AS `T1` ";
		$sql .= "LEFT JOIN `". RL_DBPREFIX ."listing_groups` AS `T2` ON `T1`.`Group_ID` = `T2`.`ID` ";
		$sql .= "WHERE `T1`.`Category_ID` = '{$id}' AND (`T1`.`Group_ID` = '' OR `T2`.`Status` = 'active') ";
		$sql .= "ORDER BY `T1`.`Position`";
		
		$form = $this -> getAll($sql);

		$count = 1;
		if ( $noRecursive || !empty($form) )
		{
			foreach ($form as $item)
			{
				$index = $item['Key'] ? $item['Key'] : 'nogroup_'. $count;
				$tmp_form[$index] = $item;
				$count++;
			}
			$form = $tmp_form;
			unset($tmp_form);
			
			return $form;
		}
		
		if ( empty($form) )
		{
			/* get parent */
			if ( $parent = $this -> getOne('Parent_ID', "`ID` = '{$id}'", 'categories') )
			{
				/* check relations */
				return $this -> getParentCatRelations($parent);
			}
		}
	}
	
	/**
	* build the listing form by listing category id
	*
	* @param int $id - category id
	* @param array $listing_type - listing type details
	*
	* @return array - listing form
	**/
	function buildListingForm( $id = false, $listing_type = false )
	{
		global $rlCache, $config, $sql, $rlHook, $lang;
		
		$id = (int)$id;
		
		/* get form from cache */
		if ( $config['cache'] && $form = $rlCache -> get('cache_submit_forms', $id, $listing_type) )
		{
			foreach ($form as $tmp_group_key => $tmp_group)
			{
				foreach ($tmp_group['Fields'] as $tmp_field_key => $tmp_field)
				{
					/* remove 'address on map' if option disabled */
					if ( $tmp_field['Key'] == 'account_address_on_map' && !$config['address_on_map'] )
					{
						unset($form[$tmp_group_key]['Fields'][$tmp_field_key]);
					}
					
					/* save field */
					$tmp_field['name'] = $lang['listing_fields+name+'.$tmp_field['Key']];
					$this -> fields[] = $tmp_field;
				}
			}
			
			return $form;
		}
		
		/* get form from DB */
		$form = $this -> getParentCatRelations($id);
		
		if ( empty($form) )
		{
			/* general category mode */
			if ( $listing_type['Cat_general_cat'] )
			{
				$form = $this -> getParentCatRelations($listing_type['Cat_general_cat'], false);
			}
			/* form is empty in any way */
			else
			{
				return false;
			}
		}

		foreach ($form as $key => $value )
		{
			if ( $value['Fields'] )
			{
				$sql = "SELECT *, FIND_IN_SET(`ID`, '{$value['Fields']}') AS `Order`, ";
				$sql .= "CONCAT('listing_fields+name+', `Key`) AS `pName`, CONCAT('listing_fields+description+', `Key`) AS `pDescription`, CONCAT('listing_fields+default+', `Key`) AS `pDefault` ";
				$sql .= "FROM `". RL_DBPREFIX ."listing_fields` ";
				$sql .= "WHERE FIND_IN_SET(`ID`, '{$value['Fields']}' ) > 0 AND `Status` = 'active' ";
				$sql .= "ORDER BY `Order`";
				$tmp_fields = $this -> getAll($sql);
				
				$rlHook -> load('buildListingFormSql');
				
				foreach ($tmp_fields as $tmp_field)
				{
					$fields[$tmp_field['Key']] = $tmp_field;
					
					/* save field */
					$tmp_field['name'] = $lang['listing_fields+name+'.$tmp_field['Key']];
					$this -> fields[] = $tmp_field;
				}
				unset($tmp_fields);

				if ( empty($fields) )
				{
					unset($form[$key]);
				}
				else
				{
					$form[$key]['Fields'] = $this -> rlCommon -> fieldValuesAdaptation($fields, 'listing_fields', $listing_type);
				}
				
				unset($fields);
			}
			else 
			{
				$form[$key]['Fields'] = false;
			}
		}

		/* remove 'account_address_on_map' field from the form */
		if ( !$config['address_on_map'] )
		{
			foreach ($form as $tmp_group_key => $tmp_group)
			{
				foreach ($tmp_group['Fields'] as $tmp_field_key => $tmp_field)
				{
					if ( $tmp_field['Key'] == 'account_address_on_map' )
					{
						unset($form[$tmp_group_key]['Fields'][$tmp_field_key]);
					}
				}
			}
		}
		
		return $form;
	}
	
	/**
	* preparation category deleting
	*
	* @package ajax
	*
	* @param int $id - category id
	*
	**/
	function ajaxPrepareDeleting( $id = false )
	{
		global $_response, $rlSmarty, $lang, $config;

		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}
		
		$delete_info = $this -> categoryWalker($id, 'detect');

		$category = $this -> getCategory($id);
		$rlSmarty -> assign_by_ref('category', $category);
		
		if ( $delete_info )
		{
			/* get the first level categoris */
			$sections = $this -> getCatTree(0, false, true);
			$rlSmarty -> assign_by_ref('sections', $sections);
			
			$rlSmarty -> assign_by_ref('delete_info', $delete_info);
			$tpl = 'blocks' . RL_DS . 'delete_preparing_category.tpl';
			$_response -> assign("delete_container", 'innerHTML', $GLOBALS['rlSmarty'] -> fetch( $tpl, null, null, false ));
			$_response -> script("
				flynax.treeLoadLevel('', '', 'div#replace_content');flynax.slideTo('#bc_container');
				$('#delete_block').slideDown();
			");
		}
		else
		{
			$phrase = $config['trash'] ? $lang['trash_confirm'] : $lang['drop_confirm'];
			$_response -> script("
				$('#delete_block').slideUp();
				rlConfirm('{$phrase}', 'xajax_deleteCategory', '{$category['Key']}');
			");
		}
		
		return $_response;
	}
	
	/**
	* delete category
	*
	* @package ajax
	*
	* @param string $key - category key
	* @param int $replace - replace category id
	* @param bool $direct - direct method call
	*
	**/
	function ajaxDeleteCategory( $key = false, $replace = false, $direct = false )
	{
		global $_response, $rlCache, $config, $lang, $controller;
		
		// check admin session expire
		if ( $this -> checkSessionExpire() === false && !$direct )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}
		
		$category_info = $this -> fetch(array('ID', 'Count'), array('Key' => $key), null, 1, 'categories', 'row');
		$id = $category_info['ID'];
		
		if ( !$id || !$key )
		{
			$_response -> script("printMessage('error', 'Error detected, no category key or ID specified.');");
			return $_response;
		}
		
		if ( $replace && (int)$replace == (int)$id )
		{
			if ( !$direct )
			{
				$message = str_replace('{category}', $lang['categories+name+'. $key], $lang['replace_category_duplicate']);
				$_response -> script("printMessage('error', '{$message}');");
				return $_response;
			}
			exit;
		}

		if ( $replace )
		{
			$this -> query("UPDATE `". RL_DBPREFIX ."categories` SET `Count` = `Count` + {$category_info['Count']} WHERE `ID` = '{$replace}' LIMIT 1");
		}
		
		// delete category field relations
		if ( $config['trash'] )
		{
			if ($replace)
			{
				$this -> categoryWalker($id, 'replace', '', $replace);
			}
			else
			{
				$this -> categoryWalker($id, 'trash');
			}
		}
		else
		{
			if ($replace)
			{
				$this -> categoryWalker($id, 'replace', '', $replace);
			}
			else
			{
				$this -> categoryWalker($id, 'delete');
			}
			
			$this -> deleteCatRelations( $id );
		}
		
		/* delete/trash category info */
		$lang_keys = array(
			array('Key' => 'categories+name+' . $key),
			array('Key' => 'categories+title+' . $key),
			array('Key' => 'categories+des+' . $key),
			array('Key' => 'categories+meta_description+' . $key),
			array('Key' => 'categories+meta_keywords+' . $key)
		);

		$this -> rlActions -> delete( array( 'Key' => $key ), array('categories'), null, 1, $key, $lang_keys );
		$del_mode = $this -> rlActions -> action;
		
		$rlCache -> updateCategories();
		$rlCache -> updateListingStatistics();
		
		/* return if direct mode */
		if ( $direct )
		{
			return true;
		}
		
		if ( $controller == 'browse' )
		{
			$_response -> redirect(RL_URL_HOME . ADMIN . "/index.php?controller=browse&id=".$new_id);
		}
		else
		{
			$_response -> script("
				categoriesGrid.reload();
				$('#replace_content').slideUp();
				$('#delete_block').fadeOut();
			");
		}
		
		$_response -> script("printMessage('notice', '{$lang['category_' . $del_mode]}')");
		
		return $_response;
	}
	
	/**
	* delete category field relations
	*
	* @param int $id - category ID
	*
	**/
	function deleteCatRelations( $id = false )
	{
		if ( !$id )
			return false;
			
		// delete field relations from short form
		$sql = "DELETE FROM `" . RL_DBPREFIX . "short_forms` WHERE `Category_ID` = '{$id}'";
		$this -> query( $sql );
		
		// delete field relations from listing title form
		$sql = "DELETE FROM `" . RL_DBPREFIX . "listing_titles` WHERE `Category_ID` = '{$id}'";
		$this -> query( $sql );
		
		// delete field relations from featured form
		$sql = "DELETE FROM `" . RL_DBPREFIX . "featured_form` WHERE `Category_ID` = '{$id}'";
		$this -> query( $sql );
		
		// delete field relations from main form
		$sql = "DELETE FROM `" . RL_DBPREFIX . "listing_relations` WHERE `Category_ID` = '{$id}'";
		$this -> query( $sql );
		
		// detete from crossed
		$sql = "UPDATE `". RL_DBPREFIX ."listings` SET `Crossed` = TRIM(BOTH ',' FROM REPLACE(CONCAT(',',`Crossed`,','), ',{$id},', ',')) WHERE FIND_IN_SET({$id}, `Crossed`) > 0";
		$this -> query( $sql );
	}
	
	/**
	* increase category listings
	*
	* @param int $id - category id
	* @param string $type - listing type key
	*
	**/
	function listingsIncrease( $id, $type = false )
	{
		global $rlCache;
		
		if ( empty($id) )
		{
			return false;
		}
		
		$sql = "UPDATE `" . RL_DBPREFIX . "categories` SET `Count` = `Count`+1, `Modified` = NOW() WHERE `ID` = '{$id}'";
		$this -> query($sql);

		/* get category parent */
		$parent = $this -> getOne('Parent_ID', "`ID` = {$id}", 'categories');

		if ( $parent > 0 )
		{
			$this -> listingsIncrease($parent, $type);
		}
		else
		{
			$rlCache -> updateCategories();

			$type = $type ? $type : $this -> getOne('Type', "`ID` = '{$id}'", 'categories');
			$rlCache -> updateListingStatistics($type);
		}
	}
	
	/**
	* decrease category listings
	*
	* @param int $id - category id
	* @param string $type - listing type key
	*
	**/
	function listingsDecrease( $id = false, $type = false )
	{
		global $rlCache;
		
		if ( empty($id) )
		{
			return false;
		}
		
		$sql = "UPDATE `" . RL_DBPREFIX . "categories` SET `Count` = `Count`-1, `Modified` = NOW() WHERE `ID` = '{$id}'";
		$this -> query($sql);

		/* get category parent */
		$parent = $this -> getOne('Parent_ID', "`ID` = {$id}", 'categories');
		
		if ( $parent > 0 )
		{
			$this -> listingsDecrease($parent, $type);
		}
		else
		{
			$rlCache -> updateCategories();
			
			$type = $type ? $type : $this -> getOne('Type', "`ID` = '{$id}'", 'categories');
			$rlCache -> updateListingStatistics($type);
		}
	}
	
	/**
	* lock/unlock category
	*
	* @package xajax
	*
	* @param int $id - category id
	* @param string $mode - mode: lock | unclock
	**/
	function ajaxLockCategory( $id, $mode = false )
	{
		global $_response, $lang;
		
		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}
		
		if ( !$mode || !in_array($mode, array('lock', 'unlock')) )
		{
			return $_response;
		}

		$status = $mode == 'lock' ? 1 : 0;
		$id = (int)$id;
		
		/* update lock status */
		$update = array(
			'fields' => array(
				'Lock' => $status
			),
			'where' => array(
				'ID' => $id
			)
		);
		$GLOBALS['rlActions'] -> updateOne($update, 'categories');

		$lang_key = $mode == 'lock' ? 'message_category_locked' : 'message_category_unlocked';
		$new_phrase = $mode == 'lock' ? 'unlock_category' : 'lock_category';
		$new_action = $mode == 'lock' ? 'unlock' : 'lock';
		
		$_response -> script("
			$('#locked_button_phrase').html('{$GLOBALS['lang'][$new_phrase]}').attr('class', 'center_{$new_action}');
			$('#locked_button').attr('onClick', \"xajax_lockCategory('{$id}', '{$new_action}')\");
			printMessage('notice', '{$lang[$lang_key]}');
		");
		
		return $_response;
	}
	
	/**
	* add custom category
	*
	* @package xajax
	*
	* @param string $name - category ID
	* @param int $parent_id - parent ID
	*
	* @todo - add new category
	**/
	function ajaxAddTmpCategory( $name = false, $parent_id = false )
	{
		global $_response, $lang, $pages, $page_info, $account_info, $config, $steps;

		$name = $this -> rlValid -> xSql(trim(trim($name, '"'), "'"));
		
		if ( empty($name) || empty($parent_id) )
		{
			$_response -> script("$('#tree_cat_{$parent_id} ul>li.tmp span.tree_loader').fadeOut();");
			return $_response;
		}
		
		/* check category exist */
		$sql = "SELECT `T1`.`ID` FROM `".RL_DBPREFIX."categories` AS `T1` ";
		$sql .= "LEFT JOIN `".RL_DBPREFIX."lang_keys` AS `T2` ON CONCAT('categories+name+', `T1`.`Key`) = `T2`.`key` ";
		$sql .= "WHERE LCASE(`T2`.`Value`) = '".strtolower($name)."' AND `Parent_ID` = '{$parent_id}' LIMIT 1";
		$cat_exist = $this -> getRow($sql);
		
		$tmp_cat_exist = $this -> getOne('ID', "LCASE(`Name`) = '".strtolower($name)."'", 'tmp_categories');

		if ( !empty($cat_exist) || $tmp_cat_exist )
		{
			$error_mess = str_replace('[category]', $name, $lang['tmp_category_exists']);			
			$_response -> script( "printMessage('error', '{$error_mess}')" );
		}
		else
		{
			/* save temporary category */
			$insert = array(
				'Name' => $name,
				'Parent_ID' => $parent_id,
				'Account_ID' => $account_info['ID'] ? $account_info['ID'] : 0,
				'Date' => 'NOW()'
			);
			
			$this -> loadClass('Actions');
			
			if ( $GLOBALS['rlActions'] -> insertOne($insert, 'tmp_categories') )
			{
				/* append new dom item to the tree */	
				$tmp_id = mysql_insert_id();
				$tmp_link = $GLOBALS['config']['mod_rewrite'] ? $page_info['Path'] . '/tmp-category/'. $steps['plan']['path'] .'.html?tmp_id='. $tmp_id : '?page=' . $page_info['Path'] .'&amp;step='. $steps['plan']['path'] .'&amp;tmp_id='. $tmp_id;
				$tmp_link = SEO_BASE . $tmp_link;
				$tmp_field = '<li class="hide"><img class="no_child" src="'. RL_TPL_BASE .'img/blank.gif" alt="" /> <a title="'.$lang['add_listing_to'].' '.$name.'" href="'.$tmp_link.'">'.$name.'<\/a></li>';
				
				if ( !$account_info['ID'] )
				{
					$_SESSION['add_listing']['tmp_category_id'] = $tmp_id;
				}

				$_response -> script("$('#tree_area_{$parent_id}').append('{$tmp_field}');");
				$_response -> script("$('#tree_cat_{$parent_id} ul>li.tmp').fadeOut(function(){ $(this).next().fadeIn(); $(this).remove(); });");
				
				/* inform category owner */
				$this -> loadClass('Mail');
				$mail_tpl = $GLOBALS['rlMail'] -> getEmailTemplate( 'custom_category_added_user' );
				$mail_tpl['body'] = str_replace( array('{category_name}'), array($name), $mail_tpl['body'] );
				
				$GLOBALS['rlMail'] -> send( $mail_tpl, $account_info['Mail'] );
				
				/* inform administrator */
				$mail_tpl = $GLOBALS['rlMail'] -> getEmailTemplate( 'custom_category_added_admin' );
				$mail_tpl['body'] = str_replace( array('{category_name}'), array($name), $mail_tpl['body'] );
			
				$GLOBALS['rlMail'] -> send( $mail_tpl, $config['notifications_email'] );
			}
			else
			{
				trigger_error("Can not add temporary category, rlActions -> insertOne function returns false", E_NOTICE);
			}
		}
		
		$_response -> script("$('#tree_cat_{$parent_id} ul>li.tmp span.tree_loader').fadeOut();");
		return $_response;
	}

	/**
	* copy fields relations
	*
	* @param int $target_cat - target category id
	*
	* @param int $source_cat - source category id
	*
	* @param string $mode - edit or add
	**/

	function copyFieldsRelations( $target_cat, $source_cat, $mode = 'add' )
	{
		$this -> loadClass('Actions');

		// copy main form fields
		$main_relations = $this -> fetch('*', array('Category_ID' => $source_cat), null, null, 'listing_relations');
		if (!empty($main_relations))
		{
			foreach ($main_relations as $key => $value)
			{
				unset($main_relations[$key]['ID']);
				$main_relations[$key]['Category_ID'] = $target_cat;
			}
			if( $mode == 'edit' )
			{
				//delete existing relations
				$this -> query("DELETE FROM `".RL_DBPREFIX."listing_relations` WHERE `Category_ID` = ".$target_cat);
			}
			$GLOBALS['rlActions'] -> insert($main_relations, 'listing_relations');
		}
			
		// copy listing title fields
		$title_relations = $this -> fetch('*', array('Category_ID' => $source_cat), null, null, 'listing_titles');
		if (!empty($title_relations))
		{
			foreach ($title_relations as $key => $value)
			{
				unset($title_relations[$key]['ID']);
				$title_relations[$key]['Category_ID'] = $target_cat;
			}
			
			if( $mode == 'edit' )
			{
				//delete existing relations
				$this -> query("DELETE FROM `".RL_DBPREFIX."listing_titles` WHERE `Category_ID` = ".$target_cat);
			}
			$GLOBALS['rlActions'] -> insert($title_relations, 'listing_titles');
		}
			
		// copy short form fields
		$short_relations = $this -> fetch('*', array('Category_ID' => $source_cat), null, null, 'short_forms');
		if (!empty($short_relations))
		{
			foreach ($short_relations as $key => $value)
			{
				unset($short_relations[$key]['ID']);
				$short_relations[$key]['Category_ID'] = $target_cat;
			}
			
			if( $mode == 'edit' )
			{
				//delete existing relations
				$this -> query("DELETE FROM `".RL_DBPREFIX."short_forms` WHERE `Category_ID` = ".$target_cat);
			}
			$GLOBALS['rlActions'] -> insert($short_relations, 'short_forms');
		}
			
		// copy featured form fields
		$featured_relations = $this -> fetch('*', array('Category_ID' => $source_cat), null, null, 'featured_form');
		if (!empty($featured_relations))
		{
			foreach ($featured_relations as $key => $value)
			{
				unset($featured_relations[$key]['ID']);
				$featured_relations[$key]['Category_ID'] = $target_cat;
			}
			
			if( $mode == 'edit' )
			{
				//delete existing relations
				$this -> query("DELETE FROM `".RL_DBPREFIX."featured_form` WHERE `Category_ID` = ".$target_cat);
			}
			$GLOBALS['rlActions'] -> insert($featured_relations, 'featured_form');
		}
	}
	
	/**
	* load listing type categories to form on Add Category page
	*
	* @package xajax
	*
	* @param string $type - listing type
	*
	* @todo - add new category
	**/
	function ajaxLoadType( $type = false )
	{
		global $_response, $lang, $rlSmarty, $rlListingTypes, $pages;
		
		/* fetch type details */
		$rlSmarty -> assign_by_ref('type', $type);
		
		/* fetch categories */
		$categories = $GLOBALS['rlCategories'] -> getCatTree(0, $type);
		$rlSmarty -> assign_by_ref('categories', $categories);
		
		/* collect paths */
		$paths_cat = $this -> getCatTitles($type);
		
		$js .= "cats = new Array(); ";
		foreach ($paths_cat as $key => $value)
		{
			$js .= "cats['{$value['ID']}'] = '{$value['Path']}'; ";
		}
		
		$tpl = 'blocks' . RL_DS . 'categories' . RL_DS . 'parent_cats_tree.tpl';
		$_response -> assign("parent_categories", 'innerHTML', $GLOBALS['rlSmarty'] -> fetch( $tpl, null, null, false ));
		
		$postfix = $rlListingTypes -> types[$type]['Cat_postfix'] ? '.html' : '/';
		
		$_response -> script("
			$('div#parent_category').slideDown();
			$('span#listing_type_loading').fadeOut();
			flynax.treeLoadLevel();
			$('#cat_postfix_el').html('{$postfix}');
		");

		/* set visible path */
		$_response -> script("$('#ab').html('". $pages['lt_'. $type] ."/'); {$js}");
				
		return $_response;
	}

	/**
	* load next level categories in multi categories mode
	*
	* @package xajax
	* 
	* @param string $value - selected category
	* @param string $type - listing type
	* @param string $level - selected category level
	*
	* @todo - add new category
	**/
	function ajaxMultiCatNext( $value = false, $type = false, $form_key = false, $level = false )
	{
		global $_response;

		$categories = $this -> getCategories( $value, $type );

		$options = '<option value="0">'.$GLOBALS['lang']['any'].'</option>';
		foreach($categories as $key => $category)
		{
			$options .='<option value="'.$category['ID'].'">'.$category['name'].'</option>';
		}

		$target = $form_key.'_Category_ID_'.$type.'_level'.($level+1);

//		$_response -> assign($target, 'innerHTML', utf8_encode($options));
		$_response -> script("$('#{$target}').html('".$options."')");
		$_response -> script("$('#{$target}').removeAttr('disabled');");

		return $_response;
	}

	/**
	* load previous levels of categories in multi categories mode
	*
	* @package xajax
	* 
	* @param string $value - selected category
	* @param string $dom_id - post value container input id
	*
	* @todo - add new category
	**/
	function ajaxMultiCatBuild( $value = false, $dom_id = false )
	{
		global $_response;

		$tmp = explode('Category_ID_', $dom_id);
		$form_key = substr($tmp[0], 0, -1);
		$type = str_replace('_value', '', $tmp[1]);

		$levels = $GLOBALS['rlListingTypes'] -> types[ $type ]['Search_multicat_levels'];

		$cat_level = $this -> getOne( "Level", "`ID` = ".(int)$value, 'categories' );

		if ( $cat_level < ($levels - 1) )
		{
			$categories = $this -> getCategories( $value, $type );
		
			$options = '<option value="0">'.$GLOBALS['lang']['any'].'</option>';
			foreach( $categories as $key => $category )
			{
				$options .='<option value="'. $category['ID'] .'">'. $category['name'] .'</option>';
			}
			$target = $form_key.'_Category_ID_'.$type.'_level'.($cat_level+1);
			$_response -> script("$('#{$target}').html('".$options."').removeAttr('disabled')");
		}

		$id = $value;
		for( $i = $levels-1; $i >= 0; $i-- )
		{
			$cat_info = $this -> fetch( array("Level", "Parent_ID"), array('ID' => $id), null, null, 'categories', 'row' );
			if( $cat_info['Level'] < $i )
			{
				continue;
			}

			$parent = $cat_info['Parent_ID'];
			$target = $form_key.'_Category_ID_'.$type.'_level'.$i;

			if ( $parent == 0 )
			{
				$_response -> script("$('#{$target}').val(".$id.")");
				return $_response;
			}
			else
			{
				$categories = $this -> getCategories( $parent, $type );
			
				$options = '<option value="0">'.$GLOBALS['lang']['any'].'</option>';
				foreach( $categories as $key => $category )
				{
					$selected = $id == $category['ID'] ? 'selected="selected"' : '';
					$options .='<option '. $selected .' value="'. $category['ID'] .'">'. $category['name'] .'</option>';
				}

				$target = $form_key.'_Category_ID_'.$type.'_level'.$i;
				$_response -> script("$('#{$target}').html('".$options."')");
				$_response -> script( "$('#{$target}').removeAttr('disabled');" );
				$id = $parent;
			}
		}

		return $_response;
	}

	/**
	* change listing category
	*
	* @package xajax
	* 
	* @param int $listing_id - requested listing ID
	* @param int $category_id - new category ID
	*
	* @todo - change category ID for requested listing
	**/
	function ajaxChangeListingCategory($listing_id = false, $category_id = false)
	{
		global $_response, $lang, $account_info;
		
		$listing_id = (int)$listing_id;
		$category_id = (int)$category_id;
		
		/* get listing details */
		$sql = "SELECT `Category_ID` ";
		$sql .= "FROM `".RL_DBPREFIX."listings` ";
		$sql .= "WHERE `ID` = '{$listing_id}' AND `Account_ID` = '{$account_info['ID']}' AND `Category_ID` <> '{$category_id}' LIMIT 1";
		$listing = $this -> getRow($sql);
		
		if ( !$listing_id || !$category_id || !$listing )
		{
			$_response -> script("
				$('#change_category_options').fadeOut('fast', function(){
					$('#change_category_origin').fadeIn();
				});
				$('#apply_category_changes').val('{$lang['change']}');
			");
			return $_response;
		}
		
		/* update category ID */
		$this -> loadClass('Actions');
		$update = array(
			'fields' => array(
				'Category_ID' => $category_id
			),
			'where' => array(
				'ID' => $listing_id
			)
		);
		
		$GLOBALS['rlActions'] -> updateOne($update, 'listings');
		
		$category_key = $this -> getOne('Key', "`ID` = '{$category_id}'", 'categories');
		
		$_response -> script("
			$('#change_category_options').fadeOut('fast', function(){
				$('#change_category_origin').fadeIn();
			});
			
			$('#change_category_origin > span').html('{$lang['categories+name+'. $category_key]}');
			$('#apply_category_changes').val('{$lang['change']}');
			printMessage('notice', '{$lang['notice_listing_category_changed']}');
		");
		
		return $_response;
	}

	/**
	* get parent categories IDs | RECURSIVE method
	* 
	* @param int $id - category ID
	* @param array $ids - found IDs
	*
	* @return array - parent categories IDs
	**/
	function getParentIDs( $id = false, $ids = false )
	{
		if ( !$id )
			return false;
			
		if ( $parent_id = $this -> getOne('Parent_ID', "`ID` = '{$id}'", 'categories') )
		{
			$ids[] = $parent_id;
			return $this -> getParentIDs($parent_id, $ids);
		}
		else
		{
			return $ids;
		}
	}
}