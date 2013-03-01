<?php

/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: RLLISTINGTYPES.CLASS.PHP
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

class rlListingTypes extends reefless 
{
	/**
	* @var language class object
	**/
	var $rlLang;
	
	/**
	* @var validator class object
	**/
	var $rlValid;
	
	/**
	* @var configuration class object
	**/
	var $rlConfig;
	
	/**
	* @var listing types
	**/
	var $types;
	
	/**
	* class constructor
	*
	* @param $active - use active type only
	*
	**/
	function rlListingTypes( $active = false )
	{
		global $rlLang, $rlValid, $rlConfig;
		
		$this -> rlLang  = & $rlLang;
		$this -> rlValid = & $rlValid;
		$this -> rlConfig = & $rlConfig;
		
		$this -> get($active);
	}
	
	/**
	* get listing types
	*
	* @param $active - use active type only
	*
	* @return array
	**/
	function get( $active = false )
	{
		global $rlSmarty;
		
		$sql = "SELECT `T1`.*, IF(`T2`.`Status` = 'active', 1, 0) AS `Advanced_search_availability` ";
		$sql .= "FROM `". RL_DBPREFIX."listing_types` AS `T1` ";
		$sql .= "LEFT JOIN `". RL_DBPREFIX."search_forms` AS `T2` ON `T1`.`Key` = `T2`.`Type` AND `T2`.`Mode` = 'advanced' ";
		$sql .= $active ? "WHERE `T1`.`Status` = 'active' " : '';
		$sql .= "ORDER BY `Order` ";
		$types = $this -> getAll($sql);

		$types = $this -> rlLang -> replaceLangKeys( $types, 'listing_types', array( 'name' ) );
		
		foreach ($types as $type)
		{
			$type['Type'] = $type['Key'];
			$type['Page_key'] = 'lt_'. $type['Type'];
			$type['My_key'] = 'my_'. $type['Type'];
			$type_out[$type['Key']] = $type;
		}
		unset($types);
		
		$this -> types = $type_out;
		
		if ( is_object($rlSmarty) )
		{
			$rlSmarty -> assign_by_ref('listing_types', $type_out);
		}
	}
	
	/**
	* activate/deactivate components
	*
	* @param $key - listing type key
	* @param $value - new status value
	*
	* @return array
	**/
	function activateComponents( $key = false, $value = 'active' )
	{
		global $rlActions;
		
		// individual page
		$individual_page = array(
			'fields' => array(
				'Status' => $value == 'active' ? $value : 'trash'
			),
			'where' => array(
				'Key' => 'lt_'. $key
			)
		);
		$rlActions -> updateOne( $individual_page, 'pages');
		
		// my listings page
		$my_listings_page = array(
			'fields' => array(
				'Status' => $value == 'active' ? $value : 'trash'
			),
			'where' => array(
				'Key' => 'my_'. $key
			)
		);
		$rlActions -> updateOne( $my_listings_page, 'pages');
		
		// quick search form
		$quick_search = array(
			'fields' => array(
				'Status' => $value == 'active' ? $value : 'trash'
			),
			'where' => array(
				'Key' => $key . '_quick'
			)
		);
		$rlActions -> updateOne( $quick_search, 'search_forms');
		
		// advanced search form
		$advanced_search = array(
			'fields' => array(
				'Status' => $value == 'active' ? $value : 'trash'
			),
			'where' => array(
				'Key' => $key . '_advanced'
			)
		);
		$rlActions -> updateOne( $advanced_search, 'search_forms');
		
		// categories block
		$categories_block = array(
			'fields' => array(
				'Status' => $value == 'active' ? $value : 'trash'
			),
			'where' => array(
				'Key' => 'ltcb_'. $key
			)
		);
		$rlActions -> updateOne( $categories_block, 'blocks');
		
		// featured block
		$featured_block = array(
			'fields' => array(
				'Status' => $value == 'active' ? $value : 'trash'
			),
			'where' => array(
				'Key' => 'ltfb_'. $key
			)
		);
		$rlActions -> updateOne( $featured_block, 'blocks');
		
		/* activate/deactivate listing type related lang phrases */
		// suspend phrases
		$update_phrases[] = array(
			'fields' => array(
				'Status' => $value == 'active' ? $value : 'trash'
			),
			'where' => array(
				'Key' => 'pages+name+lt_'. $key
			)
		);
		
		$update_phrases[] = array(
			'fields' => array(
				'Status' => $value == 'active' ? $value : 'trash'
			),
			'where' => array(
				'Key' => 'pages+title+lt_'. $key
			)
		);
		
		$update_phrases[] = array(
			'fields' => array(
				'Status' => $value == 'active' ? $value : 'trash'
			),
			'where' => array(
				'Key' => 'pages+name+my_'. $key
			)
		);
		
		$update_phrases[] = array(
			'fields' => array(
				'Status' => $value == 'active' ? $value : 'trash'
			),
			'where' => array(
				'Key' => 'pages+title+my_'. $key
			)
		);
		
		$update_phrases[] = array(
			'fields' => array(
				'Status' => $value == 'active' ? $value : 'trash'
			),
			'where' => array(
				'Key' => 'blocks+name+ltcb_'. $key
			)
		);
		
		$update_phrases[] = array(
			'fields' => array(
				'Status' => $value == 'active' ? $value : 'trash'
			),
			'where' => array(
				'Key' => 'blocks+name+ltfb_'. $key
			)
		);
		
		$update_phrases[] = array(
			'fields' => array(
				'Status' => $value == 'active' ? $value : 'trash'
			),
			'where' => array(
				'Key' => 'search_forms+name+'. $key . '%'
			)
		);
		
		$rlActions -> update( $update_phrases, 'lang_keys');
	}
	
	/**
	* activate/deactivate components related to Admin Only option
	*
	* @param $key - listing type key
	* @param $value - new status value
	*
	* @return array
	**/
	function adminOnly( $key = false, $value = 'active' )
	{
		global $rlActions;
		
		// my listings page
		$my_listings_page = array(
			'fields' => array(
				'Status' => $value == 'active' ? $value : 'trash'
			),
			'where' => array(
				'Key' => 'my_'. $key
			)
		);
		$rlActions -> updateOne( $my_listings_page, 'pages');
				
		/* activate/deactivate listing type related lang phrases */
		// suspend phrases
		$update_phrases[] = array(
			'fields' => array(
				'Status' => $value == 'active' ? $value : 'trash'
			),
			'where' => array(
				'Key' => 'pages+name+my_'. $key
			)
		);
		
		$update_phrases[] = array(
			'fields' => array(
				'Status' => $value == 'active' ? $value : 'trash'
			),
			'where' => array(
				'Key' => 'pages+title+my_'. $key
			)
		);
		
		$rlActions -> update( $update_phrases, 'lang_keys');
	}
	
	/**
	* arrange type by field values
	*
	* @param $key - field key
	*
	* @return bool
	**/
	function arrange( $key = false )
	{
		global $fields, $type_info, $f_key, $allLangs, $rlActions, $lang;
		
		/* add field mode */
		if ( $key && $fields[$key] && !$type_info['Arrange_field'] )
		{	
			/* arrange search tabs | add */
			if ( $_POST['is_arrange_search'] && !$type_info['Arrange_search'] )
			{
				$this -> arrange_search_add($key);
			}
			
			/* arrange featured box | add */
			if ( $_POST['is_arrange_featured'] && !$type_info['Arrange_featured'] )
			{
				$this -> arrange_featured_add($key);
			}
			
			/* arrange statistics columns | add */
			if ( $_POST['is_arrange_statistics'] && !$type_info['Arrange_stats'] )
			{
				$this -> arrange_statistics_add($key);
			}
		}
		// edit field mode
		if ( $key && $fields[$key] && $type_info['Arrange_field'] && $type_info['Arrange_field'] == $key )
		{
			/* arrange search tabs | add */
			if ( $_POST['is_arrange_search'] && !$type_info['Arrange_search'] )
			{
				$this -> arrange_search_add($key);
			}
			/* arrange search tabs | edit */
			elseif ( $_POST['is_arrange_search'] && $type_info['Arrange_search'] )
			{
				$this -> arrange_search_edit($key);
			}
			/* arrange search tabs | remove */
			elseif ( !$_POST['is_arrange_search'] && $type_info['Arrange_search'] )
			{
				$this -> arrange_search_remove($key);
			}
			
			/* arrange featured boxes | add */
			if ( $_POST['is_arrange_featured'] && !$type_info['Arrange_featured'] )
			{
				$this -> arrange_featured_add($key);
			}
			/* arrange featured boxes | edit */
			elseif ( $_POST['is_arrange_featured'] && $type_info['Arrange_featured'] )
			{
				$this -> arrange_featured_edit($key);
			}
			/* arrange featured boxes | remove */
			elseif ( !$_POST['is_arrange_featured'] && $type_info['Arrange_featured'] )
			{
				$this -> arrange_featured_remove($key);
			}
			
			/* arrange statistics columns | add */
			if ( $_POST['is_arrange_statistics'] && !$type_info['Arrange_stats'] )
			{
				$this -> arrange_statistics_add($key);
			}
			/* arrange statistics columns | edit */
			elseif ( $_POST['is_arrange_statistics'] && $type_info['Arrange_stats'] )
			{
				$this -> arrange_statistics_edit($key);
			}
			/* arrange statistics columns | remove */
			elseif ( !$_POST['is_arrange_statistics'] && $type_info['Arrange_stats'] )
			{
				$this -> arrange_statistics_remove($key);
			}
		}
		// change field mode
		if ( $key && $fields[$key] && $type_info['Arrange_field'] && $type_info['Arrange_field'] != $key )
		{
			if ( $type_info['Arrange_search'] )
			{
				$this -> arrange_search_remove($type_info['Arrange_field']);
				$this -> arrange_search_add($key);
			}
			
			if ( $type_info['Arrange_featured'] )
			{
				$this -> arrange_featured_remove($type_info['Arrange_field']);
				$this -> arrange_featured_add($key);
			}
			
			if ( $type_info['Arrange_stats'] )
			{
				$this -> arrange_statistics_remove($type_info['Arrange_field']);
				$this -> arrange_statistics_add($key);
			}
		}
		// remove field mode
		if ( !$key && $type_info['Arrange_field'] )
		{
			// remove all related modules
			if ( $type_info['Arrange_search'] )
			{
				$this -> arrange_search_remove($type_info['Arrange_field']);
			}
			
			if ( $type_info['Arrange_featured'] )
			{
				$this -> arrange_featured_remove($type_info['Arrange_field']);
			}
			
			if ( $type_info['Arrange_stats'] )
			{
				$this -> arrange_statistics_remove($type_info['Arrange_field']);
			}
		}
	}
	
	/**
	* arrange search tabs | ADD MODE (secondary method)
	*
	* @param $key - field key
	*
	* @return bool
	**/
	function arrange_search_add($key)
	{
		global $fields, $type_info, $f_key, $allLangs, $rlActions, $lang;
		
		$field_info = $fields[$key];
		$field_values = explode(',', $field_info['Values']);
		
		$order = 1;
		foreach ($field_values as $value)
		{
			$search_key = $f_key .'_tab'. $value;
			
			$insert[] = array(
				'Key' => $search_key,
				'Type' => $f_key,
				'In_tab' => 1,
				'Value' => $value,
				'Order' => $order,
				'Mode' => 'quick',
				'Groups' => 0,
				'Readonly' => 1
			);
			$order++;
			
			foreach ($allLangs as $lang_key => $lang_value)
			{
				$phrase = $_POST['arrange_search'][$key][$value][$lang_value['Code']];
				$phrase = $phrase ? $phrase : $lang['search_forms+name+'. $type_info['Key'] .'_tab'. $value];
				
				$lang_keys[] = array(
					'Code' => $lang_value['Code'],
					'Module' => 'common',
					'Status' => 'active',
					'Key' => 'search_forms+name+' . $search_key,
					'Value' => $phrase
				);
			}
		}
		
		if ( $insert && $lang_keys )
		{
			$rlActions -> insert($insert, 'search_forms');
			$rlActions -> insert($lang_keys, 'lang_keys');
		}
	}
	
	/**
	* arrange search tabs | EDIT MODE (secondary method)
	*
	* @param $key - field key
	*
	* @return bool
	**/
	function arrange_search_edit($key)
	{
		global $fields, $type_info, $f_key, $allLangs, $rlActions;

		$field_info = $fields[$key];
		$field_values = explode(',', $field_info['Values']);
		
		foreach ($field_values as $value)
		{
			$search_key = $f_key .'_tab'.$value;
			
			foreach ($allLangs as $lang_key => $lang_value)
			{
				$phrase = $_POST['arrange_search'][$key][$value][$lang_value['Code']];
				if ( $phrase )
				{
					$lang_keys[] = array(
						'fields' => array(
							'Value' => $phrase
						),
						'where' => array(
							'Code' => $lang_value['Code'],
							'Key' => 'search_forms+name+' . $search_key
						)
					);
				}
			}
		}
		
		if ( $lang_keys )
		{
			$rlActions -> update($lang_keys, 'lang_keys');
		}
	}
	
	/**
	* arrange search tabs | REMOVE MODE (secondary method)
	*
	* @param $key - field key
	*
	* @return bool
	**/
	function arrange_search_remove($key)
	{
		global $fields, $type_info, $f_key, $allLangs, $rlActions;

		$field_info = $fields[$key];
		$field_values = explode(',', $field_info['Values']);
		
		foreach ($field_values as $value)
		{
			$search_key = $f_key .'_tab'.$value;
			$form_id = $this -> getOne('ID', "`Key` = '{$search_key}' AND `Type` = '{$f_key}'", 'search_forms');
			
			$this -> query("DELETE FROM `". RL_DBPREFIX ."search_forms` WHERE `Key` = '{$search_key}' AND `Type` = '{$f_key}' LIMIT 1");
			$this -> query("DELETE FROM `". RL_DBPREFIX ."lang_keys` WHERE `Key` = 'search_forms+name+{$search_key}'");
			$this -> query("DELETE FROM `". RL_DBPREFIX ."search_forms_relations` WHERE `Category_ID` = '{$form_id}'");
		}
	}
	
	/**
	* arrange featured boxes | ADD MODE (secondary method)
	*
	* @param $key - field key
	*
	* @return bool
	**/
	function arrange_featured_add($key)
	{
		global $fields, $type_info, $f_key, $allLangs, $rlActions, $lang;
		
		$field_info = $fields[$key];
		$field_values = explode(',', $field_info['Values']);
		
		$parent_box = $this -> fetch(array('Page_ID', 'Category_ID', 'Subcategories', 'Sticky', 'Cat_sticky', 'Position', 'Side'), array('Key' => 'ltfb_'. $f_key), null, 1, 'blocks', 'row');
		$order = $parent_box['Position'];
		
		foreach ($field_values as $value)
		{
			$box_key = 'ltfb_'. $f_key .'_box'. $value;
			$order++;
			
			$insert[] = array(
				'Page_ID' => $parent_box['Page_ID'],
				'Sticky' => $parent_box['Sticky'],
				'Key' => $box_key,
				'Position' => $order,
				'Side' => $parent_box['Side'],
				'Type' => 'smarty',
				'Content' => '{include file=\'blocks\'|cat:$smarty.const.RL_DS|cat:\'featured.tpl\' listings=$featured_'. $f_key .'_'. $value .' type=\''. $f_key .'\' field=\''. $key .'\' value=\''. $value .'\'}',
				'Tpl' => 1,
				'Status' => 'active',
				'Readonly' => 1
			);
			
			foreach ($allLangs as $lang_key => $lang_value)
			{
				$phrase = $_POST['arrange_featured'][$key][$value][$lang_value['Code']];
				$phrase = $phrase ? $phrase : str_replace('{type}', $lang['listing_types+name+'. $f_key], $lang['featured_block_pattern']) ."({$value})";
				
				$lang_keys[] = array(
					'Code' => $lang_value['Code'],
					'Module' => 'common',
					'Status' => 'active',
					'Key' => 'blocks+name+' . $box_key,
					'Value' => $phrase
				);
			}
		}
		
		/* move current general featured box to trash */
		$this -> query("UPDATE `". RL_DBPREFIX ."blocks` SET `Status` = 'trash' WHERE `Key` = 'ltfb_{$f_key}' LIMIT 1");
		$this -> query("UPDATE `". RL_DBPREFIX ."lang_keys` SET `Status` = 'trash' WHERE `Key` = 'blocks+name+ltfb_{$f_key}'");
		
		if ( $insert && $lang_keys )
		{
			$rlActions -> insert($insert, 'blocks');
			$rlActions -> insert($lang_keys, 'lang_keys');
		}
	}
	
	/**
	* arrange featured boxes | EDIT MODE (secondary method)
	*
	* @param $key - field key
	*
	* @return bool
	**/
	function arrange_featured_edit($key)
	{
		global $fields, $type_info, $f_key, $allLangs, $rlActions;

		$field_info = $fields[$key];
		$field_values = explode(',', $field_info['Values']);
		
		foreach ($field_values as $value)
		{
			$box_key = 'ltfb_'. $f_key .'_box'. $value;
			
			foreach ($allLangs as $lang_key => $lang_value)
			{
				$phrase = $_POST['arrange_featured'][$key][$value][$lang_value['Code']];
				if ( $phrase )
				{
					$lang_keys[] = array(
						'fields' => array(
							'Value' => $phrase
						),
						'where' => array(
							'Code' => $lang_value['Code'],
							'Key' => 'blocks+name+' . $box_key
						)
					);
				}
			}
		}
		
		if ( $lang_keys )
		{
			$rlActions -> update($lang_keys, 'lang_keys');
		}
	}
	
	/**
	* arrange featured boxes | REMOVE MODE (secondary method)
	*
	* @param $key - field key
	*
	* @return bool
	**/
	function arrange_featured_remove($key)
	{
		global $fields, $type_info, $f_key, $allLangs, $rlActions;

		$field_info = $fields[$key];
		$field_values = explode(',', $field_info['Values']);
		
		foreach ($field_values as $value)
		{
			$box_key = 'ltfb_'. $f_key .'_box'. $value;
			$this -> query("DELETE FROM `". RL_DBPREFIX ."blocks` WHERE `Key` = '{$box_key}' LIMIT 1");
			$this -> query("DELETE FROM `". RL_DBPREFIX ."lang_keys` WHERE `Key` = 'blocks+name+{$box_key}'");
		}
		
		/* move current general featured box to active */
		if ( $type_info['Featured_blocks'] )
		{
			$this -> query("UPDATE `". RL_DBPREFIX ."blocks` SET `Status` = 'active' WHERE `Key` = 'ltfb_{$f_key}' LIMIT 1");
			$this -> query("UPDATE `". RL_DBPREFIX ."lang_keys` SET `Status` = 'active' WHERE `Key` = 'blocks+name+ltfb_{$f_key}'");
		}
	}
	
	/**
	* arrange statistics columns | ADD MODE (secondary method)
	*
	* @param $key - field key
	*
	* @return bool
	**/
	function arrange_statistics_add($key)
	{
		global $fields, $type_info, $f_key, $allLangs, $rlActions, $lang;
		
		$field_info = $fields[$key];
		$field_values = explode(',', $field_info['Values']);
		
		foreach ($field_values as $value)
		{
			$column_key = $f_key .'_column'. $value;
			
			foreach ($allLangs as $lang_key => $lang_value)
			{
				$phrase = $_POST['arrange_statistics'][$key][$value][$lang_value['Code']];
				$phrase = $phrase ? $phrase : $value;
				
				$lang_keys[] = array(
					'Code' => $lang_value['Code'],
					'Module' => 'common',
					'Status' => 'active',
					'Key' => 'stats+name+' . $column_key,
					'Value' => $phrase
				);
			}
		}
		
		if ( $lang_keys )
		{
			$rlActions -> insert($lang_keys, 'lang_keys');
		}
	}
	
	/**
	* arrange statistics columns | EDIT MODE (secondary method)
	*
	* @param $key - field key
	*
	* @return bool
	**/
	function arrange_statistics_edit($key)
	{
		global $fields, $type_info, $f_key, $allLangs, $rlActions;

		$field_info = $fields[$key];
		$field_values = explode(',', $field_info['Values']);
		
		foreach ($field_values as $value)
		{
			$column_key = $f_key .'_column'. $value;
			
			foreach ($allLangs as $lang_key => $lang_value)
			{
				$phrase = $_POST['arrange_statistics'][$key][$value][$lang_value['Code']];
				if ( $phrase )
				{
					$lang_keys[] = array(
						'fields' => array(
							'Value' => $phrase
						),
						'where' => array(
							'Code' => $lang_value['Code'],
							'Key' => 'stats+name+' . $column_key
						)
					);
				}
			}
		}
		
		if ( $lang_keys )
		{
			$rlActions -> update($lang_keys, 'lang_keys');
		}
	}
	
	/**
	* arrange statistics columns | REMOVE MODE (secondary method)
	*
	* @param $key - field key
	*
	* @return bool
	**/
	function arrange_statistics_remove($key)
	{
		global $fields, $type_info, $f_key, $allLangs, $rlActions;

		$field_info = $fields[$key];
		$field_values = explode(',', $field_info['Values']);
		
		foreach ($field_values as $value)
		{
			$column_key = $f_key .'_column'. $value;
			$this -> query("DELETE FROM `". RL_DBPREFIX ."lang_keys` WHERE `Key` = 'stats+name+{$column_key}'");
		}
	}
	
	/**
	* simulate arrange post data
	*
	* @param $key - field key
	*
	* @return bool
	**/
	function simulate( $key = false )
	{
		global $type_info, $fields, $allLangs;
		
		// arrange search
		$values = explode(',', $fields[$key]['Values']);
		if ( $type_info['Arrange_search'] )
		{
			foreach ($values as $value)
			{
				foreach ($allLangs as $lang_key => $lang_value)
				{
					$_POST['arrange_search'][$key][$value][$lang_value['Code']] = $this -> getOne('Value', "`Key` = 'search_forms+name+{$type_info['Key']}_tab{$value}' AND `Code` = '{$lang_value['Code']}'", 'lang_keys');
				}
			}
		}
		
		// arrange featured
		if ( $type_info['Arrange_featured'] )
		{
			foreach ($values as $value)
			{
				foreach ($allLangs as $lang_key => $lang_value)
				{
					$_POST['arrange_featured'][$key][$value][$lang_value['Code']] = $this -> getOne('Value', "`Key` = 'blocks+name+ltfb_{$type_info['Key']}_box{$value}' AND `Code` = '{$lang_value['Code']}'", 'lang_keys');
				}
			}
		}
		
		// arrange statistics
		if ( $type_info['Arrange_stats'] )
		{
			foreach ($values as $value)
			{
				foreach ($allLangs as $lang_key => $lang_value)
				{
					$_POST['arrange_statistics'][$key][$value][$lang_value['Code']] = $this -> getOne('Value', "`Key` = 'stats+name+{$type_info['Key']}_column{$value}' AND `Code` = '{$lang_value['Code']}'", 'lang_keys');
				}
			}
		}
	}
	
	/**
	* get listings statistics
	*
	* @todo get statistics from the cache and assign array to smarty
	**/
	function statisticsBlock()
	{
		global $rlCache, $config, $rlSmarty, $rlListingTypes;
		
		/* get data from cache */
		if ( $config['cache'] )
		{
			$statistics = $rlCache -> get('cache_listing_statistics');
		}
		/* get data from DB */
		else
		{
			foreach ($rlListingTypes -> types as $type)
			{
				$new_period = $config['new_period'];
				$field = $type['Arrange_field'] ? ", `T1`.`{$type['Arrange_field']}` " : '';
				
				$sql = "SELECT COUNT(*) AS `Count` FROM `". RL_DBPREFIX ."listings` AS `T1` ";
				$sql .= "LEFT JOIN `". RL_DBPREFIX ."categories` AS `T2` ON `T1`.`Category_ID` = `T2`.`ID` ";
				$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_plans` AS `T3` ON `T1`.`Plan_ID` = `T3`.`ID` ";
				$sql .= "LEFT JOIN `" . RL_DBPREFIX . "accounts` AS `T4` ON `T1`.`Account_ID` = `T4`.`ID` ";
				$sql .= "WHERE ( UNIX_TIMESTAMP(DATE_ADD(`T1`.`Pay_date`, INTERVAL `T3`.`Listing_period` DAY)) > UNIX_TIMESTAMP(NOW()) OR `T3`.`Listing_period` = 0 ) ";
				$sql .= "AND `T2`.`Type` = '{$type['Key']}' AND `T1`.`Status` = 'active' AND `T2`.`Status` = 'active' AND `T4`.`Status` = 'active' ";
				
				if ( $type['Arrange_field'] )
				{
					$values = explode(',', $type['Arrange_values']);
					foreach ($values as $value)
					{
						$c_sql = $sql . "AND `T1`.`{$type['Arrange_field']}` = '{$value}' ";
						
						/* get total */
						$data = $this -> getRow($c_sql);
						$total[$value] = $data['Count'] ? $data['Count'] : 0;
						
						/* get new */
						$n_sql = $c_sql ."AND UNIX_TIMESTAMP(`T1`.`Pay_date`) BETWEEN UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL {$new_period} DAY)) AND UNIX_TIMESTAMP(NOW()) ";
						$data = $this -> getRow($n_sql);
						$new[$value] = $data['Count'] ? $data['Count'] : 0;
						
						/* get today */
						$t_sql = $c_sql ."AND UNIX_TIMESTAMP(`T1`.`Pay_date`) BETWEEN UNIX_TIMESTAMP(DATE_FORMAT(NOW(), '%Y-%m-%d')) AND UNIX_TIMESTAMP(NOW()) ";
						$data = $this -> getRow($t_sql);
						$today[$value] = $data['Count'] ? $data['Count'] : 0;
					}
					
					$statistics[$type['Key']]['total'] = $total;
					$statistics[$type['Key']]['new'] = $new;
					$statistics[$type['Key']]['today'] = $today;
				}
				else
				{
					/* get total */
					$data = $this -> getRow($sql);
					$total = $data['Count'] ? $data['Count'] : 0;
					$statistics[$type['Key']]['total'] = $total;
					
					/* get new */
					$n_sql = $sql ."AND UNIX_TIMESTAMP(`T1`.`Pay_date`) BETWEEN UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL {$new_period} DAY)) AND UNIX_TIMESTAMP(NOW()) ";
					$data = $this -> getRow($n_sql);
					$new = $data['Count'] ? $data['Count'] : 0;
					$statistics[$type['Key']]['new'] = $new;
					
					/* today new */
					$t_sql = $sql ."AND UNIX_TIMESTAMP(`T1`.`Pay_date`) BETWEEN UNIX_TIMESTAMP(DATE_FORMAT(NOW(), '%Y-%m-%d')) AND UNIX_TIMESTAMP(NOW()) ";
					$data = $this -> getRow($t_sql);
					$today = $data['Count'] ? $data['Count'] : 0;
					$statistics[$type['Key']]['today'] = $today;
				}
			}
			
			unset($data, $sql, $c_sql, $n_sql, $t_sql);
		}
		
		$rlSmarty -> assign_by_ref('statistics_block', $statistics);
	}
	
	/**
	* delete listing type preparation
	*
	* @package ajax
	*
	* @param int $key - listing type key
	*
	**/
	function ajaxPrepareDeleting( $key = false )
	{
		global $_response, $rlSmarty, $rlHook, $delete_details, $lang, $delete_total_items, $config, $rlListingTypes;

		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}
		
		if ( count($rlListingTypes -> types) <= 1 )
		{
			$_response -> script("printMessage('alert', '{$lang['limit_listing_types_remove']}')");
			return $_response;
		}
		
		/* get listing type details */
		$type_info = $rlListingTypes -> types[$key];
		$rlSmarty -> assign_by_ref('type_info', $type_info);
		
		/* check listings */
		$sql = "SELECT COUNT(`T1`.`ID`) AS `Count` FROM `". RL_DBPREFIX ."listings` AS `T1` ";
		$sql .= "LEFT JOIN `". RL_DBPREFIX ."categories` AS `T2` ON `T1`.`Category_ID` = `T2`.`ID` ";
		$sql .= "WHERE `T2`.`Type` = '{$key}' AND `T1`.`Status` <> 'trash' AND `T2`.`Status` <> 'trash' ";
		$listings = $this -> getRow($sql);
		
		$delete_details[] = array(
			'name' => $lang['listings'],
			'items' => $listings['Count'],
			'link' => RL_URL_HOME . ADMIN . '/index.php?controller=listings&amp;listing_type='. $key
		);
		$delete_total_items += $listings['Count'];
		
		/* check categories */
		$categories = $this -> getRow("SELECT COUNT(`ID`) AS `Count` FROM `". RL_DBPREFIX ."categories` WHERE `Type` = '{$key}' AND `Status` <> 'trash'");
		$delete_details[] = array(
			'name' => $lang['categories'],
			'items' => $categories['Count'],
			'link' => RL_URL_HOME . ADMIN . '/index.php?controller=categories&amp;listing_type='. $key
		);
		$delete_total_items += $categories['Count'];
		
		/* check custom categories */
		$sql = "SELECT COUNT(`T1`.`ID`) AS `Count` FROM `". RL_DBPREFIX ."tmp_categories` AS `T1` ";
		$sql .= "LEFT JOIN `". RL_DBPREFIX ."categories` AS `T2` ON `T1`.`Parent_ID` = `T2`.`ID` ";
		$sql .= "WHERE `T2`.`Type` = '{$key}' AND `T2`.`Status` <> 'trash' ";
		$custom_categories = $this -> getRow($sql);
		$delete_details[] = array(
			'name' => $lang['admin_controllers+name+custom_categories'],
			'items' => $custom_categories['Count'],
			'link' => RL_URL_HOME . ADMIN . '/index.php?controller=custom_categories'
		);
		$delete_total_items += $custom_categories['Count'];
		
		$rlHook -> load('deleteListingTypeDataCollection');
		
		$rlSmarty -> assign_by_ref('delete_details', $delete_details);

		if ( $delete_total_items )
		{
			$tpl = 'blocks' . RL_DS . 'delete_preparing_listing_type.tpl';
			$_response -> assign("delete_container", 'innerHTML', $GLOBALS['rlSmarty'] -> fetch( $tpl, null, null, false ));
			$_response -> script("
				$('#delete_block').slideDown();
			");
		}
		else
		{
			$phrase = $config['trash'] ? str_replace('{type}', $type_info['name'], $lang['notice_drop_empty_listing_type']) : str_replace('{type}', $type_info['name'], $lang['notice_delete_empty_listing_type']);
			$_response -> script("
				$('#delete_block').slideUp();
				rlPrompt('{$phrase}', 'xajax_deleteListingType', '{$type_info['Key']}');
			");
		}
		
		return $_response;
	}
	
	/**
	* delete listing type
	*
	* @package ajax
	*
	* @param string $key - listing type Key
	* @param string $reason - remove type reason message
	* @param string $replace_key - new listing type key to replace with
	*
	**/
	function ajaxDeletingType( $key = false, $reason = false, $replace_key = false )
	{
		global $_response, $lang, $config, $rlActions, $rlListingTypes, $rlCache, $rlCategories;

		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}
		
		if ( !$key )
			return $_response;

		if ( is_array($key) )
		{
			$replace_key = $key[1];
			$key = $key[0];
		}

		/* delete/replace categories */
		if ( $replace_key )
		{
			$this -> query("UPDATE `". RL_DBPREFIX ."categories` SET `Type` = '{$replace_key}' WHERE `Type` = '{$key}'");
		}
		else
		{
			$this -> setTable('categories');
			if ( $categories = $this -> fetch(array('Key'), array('Type' => $key)) )
			{
				foreach ($categories as $category)
				{
					$rlCategories -> ajaxDeleteCategory($category['Key'], false, true);
				}
			}
		}
		
		/* get listing type details */
		$type_info = $rlListingTypes -> types[$key];
		
		/* delete account type */
		$lang_keys = array(
			array('Key' => 'listing_types+name+' . $key),
			array('Key' => 'pages+name+lt_'. $key),
			array('Key' => 'pages+title+lt_'. $key),
			array('Key' => 'pages+name+my_'. $key),
			array('Key' => 'pages+title+my_'. $key),
			array('Key' => 'blocks+name+ltcb_'. $key),
			array('Key' => 'blocks+name+ltfb_'. $key),
			array('Key' => 'search_forms+name+'. $key .'_quick'),
			array('Key' => 'search_forms+name+'. $key .'_advanced'),
			array('Key' => 'blocks+name+ltsb_'. $key)
		);
		
		if ( $type_info['Arrange_field'] )
		{
			$arrange_values = explode(',', $type_info['Arrange_values']);
			foreach ($arrange_values as $arrange_value)
			{
				$lang_keys[] = array(
					'Key' => 'search_forms+name+'. $key .'_tab'. $arrange_value
				);
				$lang_keys[] = array(
					'Key' => 'blocks+name+ltfb_'. $key .'_box'. $arrange_value
				);
				$lang_keys[] = array(
					'Key' => 'stats+name+'. $key .'_column'. $arrange_value
				);
			}
		}
		
		/* trash all related data */
		if ( $config['trash'] )
		{
			$this -> trashListingTypeData($key);
		}

		/* delete listing type */
		$rlActions -> delete( array( 'Key' => $key ), array('listing_types'), null, null, $key, $lang_keys, 'ListingTypes', 'deleteListingTypeData', 'restoreListingTypeData' );
		$del_mode = $rlActions -> action;
		
		/* unset requested type from globals */
		unset($this -> types[$key]);
		
		/* update cache */
		$rlCache -> update();
		
		/* print message, update grid */
		$_response -> script("
			listingTypesGrid.reload();
			printMessage('notice', '{$lang['item_' . $del_mode]}');
			$('#delete_block').slideUp();
		");
		
		return $_response;
	}
	
	/**
	* delete listing type data
	*
	* @package ajax
	*
	* @param string $key - listing type Key
	*
	**/
	function deleteListingTypeData($key = false)
	{
		global $rlActions;
		
		if ( !$key )
			return false;
			
		// remove enum option from listing plans table
		$rlActions -> enumRemove('search_forms', 'Type', $key);
		$rlActions -> enumRemove('categories', 'Type', $key);
		$rlActions -> enumRemove('account_types', 'Abilities', $key);
		$rlActions -> enumRemove('saved_search', 'Listing_type', $key);
		
		// delete custom categories page
		$sql = "DELETE `T1` FROM `". RL_DBPREFIX ."tmp_categories` AS `T1` ";
		$sql .= "LEFT JOIN `". RL_DBPREFIX ."categories` AS `T2` ON `T1`.`Parent_ID` = `T2`.`ID` ";
		$sql .= "WHERE `T2`.`Type` = '{$key}' ";
		$this -> query($sql);
		
		// delete individual page
		$this -> query("DELETE FROM `". RL_DBPREFIX ."pages` WHERE `Key` = 'lt_{$key}' LIMIT 1");
		
		// delete my listings page
		$this -> query("DELETE FROM `". RL_DBPREFIX ."pages` WHERE `Key` = 'my_{$key}' LIMIT 1");
		
		// delete quick search form
		$search_form_id = $this -> getOne('ID', "`Key` = '{$key}_quick'", 'search_forms');
		if ( $search_form_id )
		{
			$this -> query("DELETE FROM `". RL_DBPREFIX ."search_forms` WHERE `Key` = '{$key}_quick' LIMIT 1");
			$this -> query("DELETE FROM `". RL_DBPREFIX ."search_forms_relations` WHERE `Category_ID` = '{$search_form_id}'");
		}
		
		// delete advanced search form
		$adv_search_form_id = $this -> getOne('ID', "`Key` = '{$key}_advanced'", 'search_forms');
		if ( $adv_search_form_id )
		{
			$this -> query("DELETE FROM `". RL_DBPREFIX ."search_forms` WHERE `Key` = '{$key}_advanced' LIMIT 1");
			$this -> query("DELETE FROM `". RL_DBPREFIX ."search_forms_relations` WHERE `Category_ID` = '{$adv_search_form_id}'");
		}
		
		// delete arranged search form
		if ( $type_info['Arrange_field'] )
		{
			$arranged_search_forms = $this -> getOne('ID', "`Key` LIKE '{$key}_tab%'", 'search_forms');
			if ( $arranged_search_forms )
			{
				foreach ($arranged_search_forms as $arranged_search_form_id)
				{
					$this -> query("DELETE FROM `". RL_DBPREFIX ."search_forms` WHERE `ID` = '{$arranged_search_form_id}' LIMIT 1");
					$this -> query("DELETE FROM `". RL_DBPREFIX ."search_forms_relations` WHERE `Category_ID` = '{$arranged_search_form_id}'");
				}
			}
		}
		
		// delete categories block
		$this -> query("DELETE FROM `". RL_DBPREFIX ."blocks` WHERE `Key` = 'ltcb_{$key}' LIMIT 1");
		
		// delete featured block
		$this -> query("DELETE FROM `". RL_DBPREFIX ."blocks` WHERE `Key` = 'ltfb_{$key}' LIMIT 1");
		
		// delete search block
		$this -> query("DELETE FROM `". RL_DBPREFIX ."blocks` WHERE `Key` = 'ltsb_{$key}' LIMIT 1");
		
		// delete arranged featured blocks
		if ( $type_info['Arrange_field'] )
		{
			$arranged_blocks = $this -> getOne('ID', "`Key` LIKE 'ltfb_{$key}_box%'", 'blocks');
			if ( $arranged_blocks )
			{
				foreach ($arranged_blocks as $arranged_block_id)
				{
					$this -> query("DELETE FROM `". RL_DBPREFIX ."blocks` WHERE `ID` = '{$arranged_block_id}' LIMIT 1");
				}
			}
		}
	}
	
	/**
	* trash listing type data
	*
	* @package ajax
	*
	* @param string $key - listing type Key
	*
	**/
	function trashListingTypeData( $key = false )
	{
		if ( !$key )
			return false;
		
		// trash custom categories page
		$sql = "UPDATE `". RL_DBPREFIX ."tmp_categories` AS `T1` ";
		$sql .= "LEFT JOIN `". RL_DBPREFIX ."categories` AS `T2` ON `T1`.`Parent_ID` = `T2`.`ID` ";
		$sql .= "SET `T1`.`Status` = 'trash' ";
		$sql .= "WHERE `T2`.`Type` = '{$key}' ";
		$this -> query($sql);
		
		// trash individual page
		$this -> query("UPDATE `". RL_DBPREFIX ."pages` SET `Status` = 'trash' WHERE `Key` = 'lt_{$key}' LIMIT 1");
		
		// update my listings page
		$this -> query("UPDATE `". RL_DBPREFIX ."pages` SET `Status` = 'trash' WHERE `Key` = 'my_{$key}' LIMIT 1");
		
		// trash quick search form
		$search_form_id = $this -> getOne('ID', "`Key` = '{$key}_quick'", 'search_forms');
		if ( $search_form_id )
		{
			$this -> query("UPDATE `". RL_DBPREFIX ."search_forms` SET `Status` = 'trash' WHERE `Key` = '{$key}_quick' LIMIT 1");
		}
		
		// trash advanced search form
		$adv_search_form_id = $this -> getOne('ID', "`Key` = '{$key}_advanced'", 'search_forms');
		if ( $adv_search_form_id )
		{
			$this -> query("UPDATE `". RL_DBPREFIX ."search_forms` SET `Status` = 'trash' WHERE `Key` = '{$key}_advanced' LIMIT 1");
		}
		
		// trash arranged search form
		if ( $type_info['Arrange_field'] )
		{
			$arranged_search_forms = $this -> getOne('ID', "`Key` = '{$key}_tab%'", 'search_forms');
			if ( $arranged_search_forms )
			{
				foreach ($arranged_search_forms as $arranged_search_form_id)
				{
					$this -> query("UPDATE `". RL_DBPREFIX ."search_forms` SET `Status` = 'trash' WHERE `ID` = '{$arranged_search_form_id}' LIMIT 1");
				}
			}
		}
		
		// trash categories block
		$this -> query("UPDATE `". RL_DBPREFIX ."blocks` SET `Status` = 'trash' WHERE `Key` = 'ltcb_{$key}' LIMIT 1");
		
		// trash featured block
		$this -> query("UPDATE `". RL_DBPREFIX ."blocks` SET `Status` = 'trash' WHERE `Key` = 'ltfb_{$key}' LIMIT 1");
		
		// trash search block
		$this -> query("UPDATE `". RL_DBPREFIX ."blocks` SET `Status` = 'trash' WHERE `Key` = 'ltsb_{$key}' LIMIT 1");
		
		// delete arranged featured blocks
		if ( $type_info['Arrange_field'] )
		{
			$arranged_blocks = $this -> getOne('ID', "`Key` = 'ltfb_{$key}_box%'", 'blocks');
			if ( $arranged_blocks )
			{
				foreach ($arranged_blocks as $arranged_block_id)
				{
					$this -> query("UPDATE `". RL_DBPREFIX ."blocks` SET `Status` = 'trash' WHERE `ID` = '{$arranged_block_id}' LIMIT 1");
				}
			}
		}
	}
	
	/**
	* restore listing type data
	*
	* @package ajax
	*
	* @param string $key - listing type Key
	*
	**/
	function restoreListingTypeData( $key = false )
	{
		if ( !$key )
			return false;
			
		// restore custom categories page
		$sql = "UPDATE `". RL_DBPREFIX ."tmp_categories` AS `T1` ";
		$sql .= "LEFT JOIN `". RL_DBPREFIX ."categories` AS `T2` ON `T1`.`Parent_ID` = `T2`.`ID` ";
		$sql .= "SET `T1`.`Status` = 'approval' ";
		$sql .= "WHERE `T2`.`Type` = '{$key}' ";
		$this -> query($sql);
			
		// restore individual page
		$this -> query("UPDATE `". RL_DBPREFIX ."pages` SET `Status` = 'active' WHERE `Key` = 'lt_{$key}' LIMIT 1");
		
		// restore my listings page
		$this -> query("UPDATE `". RL_DBPREFIX ."pages` SET `Status` = 'active' WHERE `Key` = 'my_{$key}' LIMIT 1");
		
		// restore quick search form
		$search_form_id = $this -> getOne('ID', "`Key` = '{$key}_quick'", 'search_forms');
		if ( $search_form_id )
		{
			$this -> query("UPDATE `". RL_DBPREFIX ."search_forms` SET `Status` = 'active' WHERE `Key` = '{$key}_quick' LIMIT 1");
		}
		
		// restore advanced search form
		$adv_search_form_id = $this -> getOne('ID', "`Key` = '{$key}_advanced'", 'search_forms');
		if ( $adv_search_form_id )
		{
			$this -> query("UPDATE `". RL_DBPREFIX ."search_forms` SET `Status` = 'active' WHERE `Key` = '{$key}_advanced' LIMIT 1");
		}
		
		// restore arranged search form
		if ( $type_info['Arrange_field'] )
		{
			$arranged_search_forms = $this -> getOne('ID', "`Key` = '{$key}_tab%'", 'search_forms');
			if ( $arranged_search_forms )
			{
				foreach ($arranged_search_forms as $arranged_search_form_id)
				{
					$this -> query("UPDATE `". RL_DBPREFIX ."search_forms` SET `Status` = 'active' WHERE `ID` = '{$arranged_search_form_id}' LIMIT 1");
				}
			}
		}
		
		// restore categories block
		$this -> query("UPDATE `". RL_DBPREFIX ."blocks` SET `Status` = 'active' WHERE `Key` = 'ltcb_{$key}' LIMIT 1");
		
		// restore featured block
		$this -> query("UPDATE `". RL_DBPREFIX ."blocks` SET `Status` = 'active' WHERE `Key` = 'ltfb_{$key}' LIMIT 1");
		
		// restore search block
		$this -> query("UPDATE `". RL_DBPREFIX ."blocks` SET `Status` = 'active' WHERE `Key` = 'ltsb_{$key}' LIMIT 1");
		
		// restore arranged featured blocks
		if ( $type_info['Arrange_field'] )
		{
			$arranged_blocks = $this -> getOne('ID', "`Key` = 'ltfb_{$key}_box%'", 'blocks');
			if ( $arranged_blocks )
			{
				foreach ($arranged_blocks as $arranged_block_id)
				{
					$this -> query("UPDATE `". RL_DBPREFIX ."blocks` SET `Status` = 'active' WHERE `ID` = '{$arranged_block_id}' LIMIT 1");
				}
			}
		}
	}
	
	/**
	* update arranged field relations
	*
	* @param string $key - field key
	* @param string $type - field type
	* @param string $values - new arrange calues
	*
	**/
	function editArrangeField($key = false, $type = false, $values = false)
	{
		global $allLangs, $rlActions, $rlCache, $rlListingTypes, $lang;
		
		if ( !$key || !$values )
		{
			$GLOBALS['rlDebug'] -> logger("Returned from editArrangeField(), no key or valies specified");
			return false;
		}
			
		$arrange_data = $this -> fetch(array('Arrange_values', 'Key'), array('Arrange_field' => $key), null, null, 'listing_types');
		foreach ($arrange_data as $arrange_info)
		{
			if ( $arrange_info && strcmp($values, $arrange_info['Arrange_values']) !== 0 )
			{
				$arr1 = explode(',', $arrange_info['Arrange_values']);
				$arr2 = explode(',', $values);
				
				/* update ararnge data in listng type */
				$update = array(
					'fields' => array('Arrange_values' => $values),
					'where' => array('Key' => $arrange_info['Key'])
				);
				$rlActions -> updateOne($update, 'listing_types');
				
				/* remove mode */
				foreach ($arr1 as $item1)
				{
					if ( false === array_search($item1, $arr2) )
					{
						/* remove search forms */
						$search_key = $arrange_info['Key'] .'_tab'. $item1;
						$form_id = $this -> getOne('ID', "`Key` = '{$search_key}' AND `Type` = '{$arrange_info['Key']}'", 'search_forms');
						
						$this -> query("DELETE FROM `". RL_DBPREFIX ."search_forms` WHERE `Key` = '{$search_key}' AND `Type` = '{$arrange_info['Key']}' LIMIT 1");
						$this -> query("DELETE FROM `". RL_DBPREFIX ."lang_keys` WHERE `Key` = 'search_forms+name+{$search_key}'");
						$this -> query("DELETE FROM `". RL_DBPREFIX ."search_forms_relations` WHERE `Category_ID` = '{$form_id}'");
						
						/* remove features boxes */
						$box_key = 'ltfb_'. $arrange_info['Key'] .'_box'. $item1;
						$this -> query("DELETE FROM `". RL_DBPREFIX ."blocks` WHERE `Key` = '{$box_key}' LIMIT 1");
						$this -> query("DELETE FROM `". RL_DBPREFIX ."lang_keys` WHERE `Key` = 'blocks+name+{$box_key}'");
						
						/* remove statistic entry */
						$column_key = $arrange_info['Key'] .'_column'. $item1;
						$this -> query("DELETE FROM `". RL_DBPREFIX ."lang_keys` WHERE `Key` = 'stats+name+{$column_key}'");
					}
				}
				
				/* create mode */
				foreach ($arr2 as $item2)
				{
					if ( false === array_search($item2, $arr1) )
					{
						/* create search forms */
						$search_key = $arrange_info['Key'] .'_tab'. $item2;
						
						$insert = array(
							'Key' => $search_key,
							'Type' => $arrange_info['Key'],
							'In_tab' => 1,
							'Value' => $item2,
							'Order' => $item2,
							'Mode' => 'quick',
							'Groups' => 0,
							'Readonly' => 1
						);
						$rlActions -> insertOne($insert, 'search_forms');
						
						foreach ($allLangs as $lang_value)
						{
							$phrase = $_POST[$type][$item2][$lang_value['Code']];
							$phrase = $phrase ? $phrase : $lang['search_forms+name+'. $arrange_info['Key'] .'_tab'. $item2];
							
							$lang_keys[] = array(
								'Code' => $lang_value['Code'],
								'Module' => 'common',
								'Status' => 'active',
								'Key' => 'search_forms+name+' . $search_key,
								'Value' => $phrase
							);
						}
						
						/* create featured boxes */
						$parent_box = $this -> fetch(array('Page_ID', 'Category_ID', 'Subcategories', 'Sticky', 'Cat_sticky', 'Position', 'Side'), array('Key' => 'ltfb_'. $arrange_info['Key']), null, 1, 'blocks', 'row');
						$order = $parent_box['Position'] + $item2;
						$box_key = 'ltfb_'. $arrange_info['Key'] .'_box'. $item2;
						
						$insert = array(
							'Page_ID' => $parent_box['Page_ID'],
							'Sticky' => $parent_box['Sticky'],
							'Key' => $box_key,
							'Position' => $order,
							'Side' => $parent_box['Side'],
							'Type' => 'smarty',
							'Content' => '{include file=\'blocks\'|cat:$smarty.const.RL_DS|cat:\'featured.tpl\' listings=$featured_'. $arrange_info['Key'] .'_'. $item2 .' type=\''. $arrange_info['Key'] .'\' field=\''. $key .'\' value=\''. $item2 .'\'}',
							'Tpl' => 1,
							'Status' => 'active',
							'Readonly' => 1
						);
						$rlActions -> insertOne($insert, 'blocks');
						
						foreach ($allLangs as $lang_value)
						{
							$phrase = $_POST[$type][$item2][$lang_value['Code']];
							$phrase = $phrase ? $phrase : str_replace('{type}', $lang['listing_types+name+'. $arrange_info['Key']], $lang['featured_block_pattern']) ."({$item2})";
							
							$lang_keys[] = array(
								'Code' => $lang_value['Code'],
								'Module' => 'common',
								'Status' => 'active',
								'Key' => 'blocks+name+' . $box_key,
								'Value' => $phrase
							);
						}
						
						/* create statistics entry */
						$column_key = $arrange_info['Key'] .'_column'. $item2;
				
						foreach ($allLangs as $lang_value)
						{
							$phrase = $_POST[$type][$item2][$lang_value['Code']];
							$phrase = $phrase ? $phrase : $value;
							
							$lang_keys[] = array(
								'Code' => $lang_value['Code'],
								'Module' => 'common',
								'Status' => 'active',
								'Key' => 'stats+name+' . $column_key,
								'Value' => $phrase
							);
						}
	
						if ( $lang_keys )
						{
							$rlActions -> insert($lang_keys, 'lang_keys');
						}
					}
				}
				
				$rlListingTypes -> get();
				
				/* update cache */
				$rlCache -> updateListingStatistics($arrange_info['Key']);
				$rlCache -> updateCategories();
				$rlCache -> updateSearchForms();
				$rlCache -> updateSearchFields();
			}
		}
	}
}