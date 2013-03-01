<?php

/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: RLCACHE.CLASS.PHP
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

/**
* cache class
* 
* available cache recourses:
* | cache_submit_forms 			- submit forms
* | cache_categories_by_type 	- categories by listing type, full list
* | cache_categories_by_parent 	- categories by listing type, by parent includes subcategories
* | cache_categories_by_id	 	- categories by id, full list
* | cache_search_forms 			- search forms by form key
* | cache_search_fields			- search fields list by form key
* | cache_featured_form_fields 	- featured form fields by category id
* | cache_listing_titles_fields - listing titles form fields by category id
* | cache_short_forms_fields 	- short form fields by category id
* | cache_data_formats		 	- data formats by key
* | cache_listing_statistics	- listing statistics by listing type
*
**/
class rlCache extends reefless 
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
	* @var valid class object
	**/
	var $rlValid;
	
	/**
	* @var common class object
	**/
	var $rlCommon;
	
	/**
	* class constructor
	**/
	function rlCache()
	{
		$this -> loadClass('Categories');
		$this -> loadClass('Common');
		
		global $rlLang, $rlActions, $rlValid, $rlCommon;

		$this -> rlLang  = & $rlLang;
		$this -> rlValid  = & $rlValid;
		$this -> rlActions = & $rlActions;
		$this -> rlCommon = & $rlCommon;
	}
	
	/**
	* get cache item
	*
	* @param string $key - cache item key
	* @param id $id - item id
	* @param string $type - listing type
	*
	* @return array
	*
	**/
	function get( $key = false, $id = false, $type = false )
	{
		global $config;
		
		$file = RL_CACHE . $config[$key];
		
		if ( !$key || !$config['cache'] )
		{
			return false;
		}
		
		if ( empty($config[$key]) || !is_readable($file) )
		{
			return false;
		}
		
		if ( $GLOBALS[$key] )
		{
			$content = $GLOBALS[$key];//probably bad idea, have to check 
		}
		else
		{
			$fh = fopen($file, 'r');
			$content = fread($fh, filesize($file));
			$GLOBALS[$key] = $content;
			fclose($fh);
		}
		
		$content = unserialize($content);
		
		if ( $id === false )
		{
			return $content;
		}
		
		$out = $content[$type['Key']] ? $content[$type['Key']][$id] : $content[$id];

		if ( $type && !$out && in_array($key, array('cache_featured_form_fields', 'cache_listing_titles_fields', 'cache_short_forms_fields', 'cache_submit_forms')) )
		{
			$categories_by_type = $this -> get('cache_categories_by_type', false, $type);
			$categories_by_type = $categories_by_type[$type['Key']];
			
			$out = $this -> matchParent($id, 'Parent_ID', $categories_by_type, $content);
			if ( !$out )
			{
				return $content[$type['Cat_general_cat']];
			}
			
			return $out;
		}
		unset($content);
		
		return $out;
	}
	
	/**
	* match parent
	*
	* @param string $key - cache srouce 
	* @param string $field - parent field name
	* @param array $search - search resurce
	* @param array $content - main content from cache
	*
	**/
	function matchParent( &$id, $field = false, &$search, &$content )
	{
		if ( !$id || !$field || !$search || !$content )
		{
			return false;
		}
		
		if ( $search[$id][$field] )
		{
			if ( !empty($content[$search[$id][$field]]) )
			{
				return $content[$search[$id][$field]];
			}
			else
			{
				return $this -> matchParent($search[$id][$field], $field, $search, $content);
			}
		}
		
		return false;
	}
	
	/**
	* cache files handler
	*
	* @param string $key - cache srouce 
	*
	**/
	function file( $key )
	{
		global $config, $rlDebug, $reefless, $rlConfig;
		
		if ( !$key )
		{
			return false;
		}
		
		if ( !isset($config[$key]) )
		{
			$rlDebug -> logger("Can't handle cache file, '{$key}' key doesn't exist in configurations.");
			return false;
		}
		
		/* create cache file */
		if ( empty($config[$key]) || !file_exists(RL_CACHE . $config[$key]) || !is_writable(RL_CACHE . $config[$key]) )
		{
			$hash = $reefless -> generateHash();
			if ( !$hash )
			{
				$rlDebug -> logger("Can't create cache file, generateHash() doesn't generate anything.");
			}
			else
			{
				$file_name = $key .'_'. $hash;
				$file_dir = RL_CACHE . $file_name;
				
				$fh = fopen($file_dir, 'w') or $rlDebug -> logger("Can't create new file, fopen() fail.");
				fclose($fh);
				
				chmod($file_dir, 0644);
				
				if ( !is_writable($file_dir) )
				{
					chmod($file_dir, 0777);
				}
				
				/* save file name */
				$rlConfig -> setConfig($key, $file_name);
				$config[$key] = $file_name;
			}
		}
	}
	
	/**
	*
	* update submit forms
	* | cache_submit_forms
	*
	**/
	function updateSubmitForms()
	{
		global $config;
		
		if ( !$config['cache'] )
		{
			return false;
		}
		
		/* submit forms cache */
		$sql = "SELECT `T1`.`Group_ID`, `T1`.`ID`, `T2`.`ID` AS `Category_ID`, `T3`.`Key` AS `Key`, `T3`.`Display` AS `Display`, ";
		$sql .= "`T1`.`Fields`, CONCAT('listing_groups+name+', `T3`.`Key`) AS `pName`, `T2`.`Type` AS `Listing_type` ";
		$sql .= "FROM `". RL_DBPREFIX ."listing_relations` AS `T1` ";
		$sql .= "LEFT JOIN `". RL_DBPREFIX ."categories` AS `T2` ON `T1`.`Category_ID` = `T2`.`ID` ";
		$sql .= "LEFT JOIN `". RL_DBPREFIX ."listing_groups` AS `T3` ON `T1`.`Group_ID` = `T3`.`ID` ";
		$sql .= "WHERE `T1`.`Group_ID` = '' OR `T3`.`Status` = 'active' ";
		$sql .= "ORDER BY `T1`.`Position`";
		
		$rows = $this -> getAll($sql);
		
		if ( !$rows )
		{
			return false;
		}
		
		foreach ($rows as $key => $value)
		{
			if ( !empty($value['Fields']) )
			{	
				$sql = "SELECT *, FIND_IN_SET(`ID`, '{$value['Fields']}') AS `Order`, ";
				$sql .= "CONCAT('listing_fields+name+', `Key`) AS `pName`, CONCAT('listing_fields+description+', `Key`) AS `pDescription`, ";
				$sql .= "CONCAT('listing_fields+default+', `Key`) AS `pDefault`, `Multilingual` ";
				$sql .= "FROM `". RL_DBPREFIX ."listing_fields` ";
				$sql .= "WHERE FIND_IN_SET(`ID`, '{$value['Fields']}' ) > 0 AND `Status` = 'active' ";
				$sql .= "ORDER BY `Order`";
				$tmp_fields = $this -> getAll($sql);

				if ( empty($tmp_fields) )
				{
					unset($rows[$key]);
				}
				else
				{
					foreach ($tmp_fields as $field)
					{
						$fields[$field['Key']] = $field;
					}
					unset($tmp_fields);
					
					$rows[$key]['Fields'] = $this -> rlCommon -> fieldValuesAdaptation($fields, 'listing_fields', $value['Listing_type']);
				}
			}
			else
			{
				$rows[$key]['Fields'] = false;
			}
			
			unset($field_ids, $fields, $field_info);
			
			// reassign to form, collect by category ID
			$set = count($form[$value['Category_ID']])+1;
			$index = $value['Key'] ? $value['Key'] : 'nogroup_'. $set;
			$form[$value['Category_ID']][$index] = $rows[$key];
		}
		
		unset($rows);
		
		// write cache to file
		if ( $form )
		{
			$this -> file('cache_submit_forms');
			$file = RL_CACHE . $config['cache_submit_forms'];
			
			$fh = fopen($file, 'w');
			fwrite($fh, serialize($form)); 
			fclose($fh);
			
			unset($form);
		}
	}
	
	/**
	*
	* update categories by listing type
	* | cache_categories_by_type
	*
	**/
	function updateCategoriesByType()
	{
		global $config, $rlListingTypes;
		
		if ( !$config['cache'] )
		{
			return false;
		}
		
		if ( $rlListingTypes -> types )
		{
			foreach ($rlListingTypes -> types as $key => $value)
			{
				$sql = "SELECT *, CONCAT('categories+name+', `Key`) AS `pName`, CONCAT('categories+title+', `Key`) AS `pTitle` ";
				$sql .= "FROM `". RL_DBPREFIX ."categories` ";
				$sql .= "WHERE `Type` = '{$value['Key']}' AND `Status` = 'active' ";
				
				if ( $tmp_categories = $this -> getAll($sql) )
				{
					foreach ($tmp_categories as $cKey => $cValue )
					{
						$categories[$cValue['ID']] = $cValue;
					}
					unset($tmp_categories);
					
					$out[$value['Key']] = $categories;
					unset($categories);
				}
			}
			
			if ( $out )
			{
				$this -> file('cache_categories_by_type');
				$file = RL_CACHE . $config['cache_categories_by_type'];
				
				$fh = fopen($file, 'w');
				fwrite($fh, serialize($out)); 
				fclose($fh);
				
				unset($out);
			}
		}
	}
	
	/**
	*
	* update categories by listing type, organized by parent
	* | cache_categories_by_parent
	*
	**/
	function updateCategoriesByParent()
	{
		global $config, $rlListingTypes;
		
		if ( !$config['cache'] )
		{
			return false;
		}
		
		if ( $rlListingTypes -> types )
		{
			foreach ($rlListingTypes -> types as $key => $value)
			{
				$out[$value['Key']] = $this -> getChildCat(array(0), $value);
			}
			
			if ( $out )
			{
				$this -> file('cache_categories_by_parent');
				$file = RL_CACHE . $config['cache_categories_by_parent'];
				
				$fh = fopen($file, 'w');
				fwrite($fh, serialize($out)); 
				fclose($fh);
				
				unset($out);
			}
		}
	}
	
	/**
	*
	* update categories by id, full list
	* | cache_categories_by_id
	*
	**/
	function updateCategoriesByID()
	{
		global $config, $rlListingTypes;
		
		if ( !$config['cache'] )
		{
			return false;
		}
		
		$sql = "SELECT *, `Modified`, CONCAT('categories+name+', `Key`) AS `pName`, CONCAT('categories+title+', `Key`) AS `pTitle` ";
		$sql .= "FROM `". RL_DBPREFIX ."categories` ";
		$sql .= "WHERE `Status` = 'active'";
		
		if ( $tmp_categories = $this -> getAll($sql) )
		{
			foreach ($tmp_categories as $category )
			{
				$out[$category['ID']] = $category;
			}
			unset($tmp_categories);
		}
		
		if ( $out )
		{
			$this -> file('cache_categories_by_id');
			$file = RL_CACHE . $config['cache_categories_by_id'];
			
			$fh = fopen($file, 'w');
			fwrite($fh, serialize($out)); 
			fclose($fh);
			
			unset($out);
		}
	}
	
	/**
	* call all methods relatead to categories
	**/
	function updateCategories()
	{
		$this -> updateCategoriesByType();
		$this -> updateCategoriesByParent();
		$this -> updateCategoriesByID();
	}
	
	/**
	* get children categories by parent | recursive method
	*
	* @param array $parent - parent category ids
	* @param array $type - listing type info
	*
	**/
	function getChildCat( $parent = array(0), $type = false, $data = false )
	{
		foreach ( $parent as $parent_id )
		{
			$sql = "SELECT *, `Modified` ";
			$sql .= "FROM `". RL_DBPREFIX ."categories` ";
			$sql .= "WHERE `Type` = '{$type['Key']}' AND `Status` = 'active' AND `Parent_ID` = '{$parent_id}'";
			$sql .= "ORDER BY `Position`";
			
			if ( $tmp_categories = $this -> getAll($sql) )
			{
				foreach ($tmp_categories as $cKey => $cValue )
				{
					$ids[] = $cValue['ID'];
					
					$categories[$cValue['ID']] = $cValue;
					$categories[$cValue['ID']]['pName'] = 'categories+name+'. $cValue['Key'];
					$categories[$cValue['ID']]['pTitle'] = 'categories+title+'. $cValue['Key'];
					
					/* get subcategories */
					if ( $type['Cat_show_subcats'] )
					{
						$this -> calcRows = true;
						$subCategories = $this -> fetch( array('ID', 'Path`, CONCAT("categories+name+", `Key`) AS `pName`, CONCAT("categories+title+", `Key`) AS `pTitle', 'Key'), array('Status' => 'active', 'Parent_ID' => $cValue['ID']), "ORDER BY `Position`", null, 'categories' );
						$this -> calcRows = false;
						
						if (!empty($subCategories))
						{
							$categories[$cValue['ID']]['sub_categories'] = $subCategories;
							$categories[$cValue['ID']]['sub_categories_calc'] = $this -> calcRows;	
						}
						
						unset($subCategories);
					}
				}
				unset($tmp_categories);
				
				$data[$parent_id] = $categories;
				
				unset($categories);
			}
			else
			{
				continue;
			}
		}
		
		if ( $parent )
		{
			return $this -> getChildCat($ids, $type, $data);
		}
		else
		{
			return $data;
		}
	}
	
	/**
	*
	* update search forms by form key
	* | cache_search_forms
	*
	**/
	function updateSearchForms()
	{
		global $config, $rlListingTypes;
		
		if ( !$config['cache'] )
		{
			return false;
		}
		
		$sql = "SELECT `T1`.`Category_ID`, `T1`.`Group_ID`, `T1`.`Fields`, ";
		$sql .= "`T2`.`Key` AS `Group_key`, `T2`.`Display`, ";
		$sql .= "`T3`.`Type` AS `Listing_type`, `T3`.`Key` AS `Form_key` ";
		$sql .= "FROM `". RL_DBPREFIX ."search_forms_relations` AS `T1` ";
		$sql .= "LEFT JOIN `". RL_DBPREFIX ."listing_groups` AS `T2` ON `T1`.`Group_ID` = `T2`.`ID` AND `T2`.`Status` = 'active' ";
		$sql .= "LEFT JOIN `". RL_DBPREFIX ."search_forms` AS `T3` ON `T1`.`Category_ID` = `T3`.`ID` ";
		$sql .= "WHERE `T3`.`Status` = 'active' ";
		$sql .= "ORDER BY `Position` ";
		$relations = $this -> getAll($sql);
		
		if ( !$relations )
		{
			return false;
		}
		
		$this -> loadClass('Categories');
		
		/* populate field information */
		foreach ($relations as $key => $value)
		{
			if ( !$value )
				continue;

			$sql = "SELECT `ID`, `Key`, `Type`, `Default`, `Values`, `Condition`, CONCAT('listing_fields+name+', `Key`) AS `pName`, ";
			$sql .= "`Multilingual`, `Opt1`, `Opt2`, FIND_IN_SET(`ID`, '{$value['Fields']}') AS `Order` ";
			$sql .= "FROM `". RL_DBPREFIX ."listing_fields` ";
			$sql .= "WHERE FIND_IN_SET(`ID`, '{$value['Fields']}' ) > 0 AND `Status` = 'active' ";
			$sql .= "ORDER BY `Order`";
			$fields = $this -> getAll($sql);
			
			if ( $value['Group_key'] )
			{
				$relations[$key]['pName'] = 'listing_groups+name+'. $value['Group_key'];
			}
			$relations[$key]['Fields'] = empty($fields) ? false : $this -> rlCommon -> fieldValuesAdaptation($fields, 'listing_fields', $value['Listing_type']);
			
			$out[$value['Form_key']][] = $relations[$key];
		}
		
		unset($relations);
		
		if ( $out )
		{
			$this -> file('cache_search_forms');
			$file = RL_CACHE . $config['cache_search_forms'];
			
			$fh = fopen($file, 'w');
			fwrite($fh, serialize($out)); 
			fclose($fh);
			
			unset($out);
		}
	}
	
	/**
	*
	* update search fields list by form key
	* | cache_search_fields
	*
	**/
	function updateSearchFields()
	{
		global $config, $rlListingTypes;
		
		if ( !$config['cache'] )
		{
			return false;
		}
		
		$sql = "SELECT `T1`.`Category_ID`, `T1`.`ID`, `T1`.`Fields`, `T2`.`Key` AS `Form_key` ";
		$sql .= "FROM `". RL_DBPREFIX ."search_forms_relations` AS `T1` ";
		$sql .= "LEFT JOIN `". RL_DBPREFIX ."search_forms` AS `T2` ON `T1`.`Category_ID` = `T2`.`ID` ";
		$sql .= "WHERE `T2`.`Status` = 'active' ";
		$sql .= "ORDER BY `Position` ";
		$relations = $this -> getAll($sql);
		
		if ( !$relations )
		{
			return false;
		}
		
		foreach ($relations as $key => $value)
		{
			$sql = "SELECT `ID`, `Key`, `Type`, `Default`, `Values`, `Condition`, `Details_page`, `Opt1`, `Opt2`, ";
			$sql .= "`Multilingual`, FIND_IN_SET(`ID`, '{$value['Fields']}') AS `Order` ";
			$sql .= "FROM `". RL_DBPREFIX ."listing_fields` ";
			$sql .= "WHERE FIND_IN_SET(`ID`, '{$value['Fields']}' ) > 0 AND `Status` = 'active' ";
			$sql .= "ORDER BY `Order`";
			$fields = $this -> getAll($sql);
			
			foreach ( $fields as $fKey => $fValue )
			{
				$out[$value['Form_key']][$fValue['Key']] = $fValue;
			}
			
			unset($fields);
		}
		
		unset($relations);
		
		if ( $out )
		{
			$this -> file('cache_search_fields');
			$file = RL_CACHE . $config['cache_search_fields'];
			
			$fh = fopen($file, 'w');
			fwrite($fh, serialize($out)); 
			fclose($fh);
			
			unset($out);
		}
	}
	
	/**
	*
	* update featured form fields by category id
	* | cache_featured_form_fields
	*
	**/
	function updateFeaturedFormFields()
	{
		global $config, $rlListingTypes;
		
		if ( !$config['cache'] )
		{
			return false;
		}
		
		$this -> setTable('categories');
		$categoris = $this -> fetch(array('ID', 'Key'));
		
		foreach ($categoris as $key => $value)
		{
			$sql = "SELECT `T2`.`Key`, `T2`.`Type`, `T2`.`Default`, `T2`.`Condition`, `T2`.`Details_page`, `T2`.`Multilingual`, `T2`.`Opt1`, `T2`.`Opt2` ";
			$sql .= "FROM `". RL_DBPREFIX ."featured_form` AS `T1` ";
			$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_fields` AS `T2` ON `T1`.`Field_ID` = `T2`.`ID` ";
			$sql .= "WHERE `T1`.`Category_ID` = '{$value['ID']}' ORDER BY `T1`.`Position`";
			
			if ( $fields = $this -> getAll( $sql ) )
			{
				foreach ($fields as $field)
				{
					$tmp_fields[$field['Key']] = $field;
				}
				
				$fields = $tmp_fields;
				unset($tmp_fields);
		
				$out[$value['ID']] = $fields;
				unset($fields);
			}
		}
		unset($categoris);
		
		if ( $out )
		{
			$this -> file('cache_featured_form_fields');
			$file = RL_CACHE . $config['cache_featured_form_fields'];
			
			$fh = fopen($file, 'w');
			fwrite($fh, serialize($out)); 
			fclose($fh);
			
			unset($out);
		}
	}
	
	/**
	*
	* update listing title form fields by category id
	* | cache_listing_titles_fields
	*
	**/
	function updateTitlesFormFields()
	{
		global $config, $rlListingTypes;
		
		if ( !$config['cache'] )
		{
			return false;
		}
		
		$tables = array('', '', 'short_forms');
		$this -> setTable('categories');
		$categoris = $this -> fetch(array('ID', 'Key'));
		
		foreach ($categoris as $key => $value)
		{
			$sql = "SELECT `T2`.`Key`, `T2`.`Type`, `T2`.`Default`, `T2`.`Condition`, `T2`.`Details_page`, `T2`.`Multilingual`, `T2`.`Opt1`, `T2`.`Opt2` ";
			$sql .= "FROM `". RL_DBPREFIX ."listing_titles` AS `T1` ";
			$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_fields` AS `T2` ON `T1`.`Field_ID` = `T2`.`ID` ";
			$sql .= "WHERE `T1`.`Category_ID` = '{$value['ID']}' ORDER BY `T1`.`Position`";
			
			if ( $fields = $this -> getAll( $sql ) )
			{
				foreach ($fields as $field)
				{
					$tmp_fields[$field['Key']] = $field;
				}
				
				$fields = $tmp_fields;
				unset($tmp_fields);
		
				$out[$value['ID']] = $fields;
				unset($fields);
			}
		}
		unset($categoris);
		
		if ( $out )
		{
			$this -> file('cache_listing_titles_fields');
			$file = RL_CACHE . $config['cache_listing_titles_fields'];
			
			$fh = fopen($file, 'w');
			fwrite($fh, serialize($out)); 
			fclose($fh);
			
			unset($out);
		}
	}
	
	/**
	*
	* update listing title form fields by category id
	* | cache_short_forms_fields
	*
	**/
	function updateShortFormFields()
	{
		global $config, $rlListingTypes;
		
		if ( !$config['cache'] )
		{
			return false;
		}
		
		$this -> setTable('categories');
		$categoris = $this -> fetch(array('ID', 'Key'));
		
		foreach ($categoris as $key => $value)
		{
			$sql = "SELECT `T2`.`Key`, `T2`.`Type`, `T2`.`Default`, `T2`.`Condition`, `T2`.`Details_page`, `T2`.`Multilingual`, `T2`.`Opt1`, `T2`.`Opt2` ";
			$sql .="FROM `". RL_DBPREFIX ."short_forms` AS `T1` ";
			$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_fields` AS `T2` ON `T1`.`Field_ID` = `T2`.`ID` ";
			$sql .= "WHERE `T1`.`Category_ID` = '{$value['ID']}' ORDER BY `T1`.`Position`";
			
			if ( $fields = $this -> getAll( $sql ) )
			{
				foreach ($fields as $field)
				{
					$tmp_fields[$field['Key']] = $field;
				}
				
				$fields = $tmp_fields;
				unset($tmp_fields);
		
				$out[$value['ID']] = $fields;
				unset($fields);
			}
		}
		unset($categoris);
		
		if ( $out )
		{
			$this -> file('cache_short_forms_fields');
			$file = RL_CACHE . $config['cache_short_forms_fields'];
			
			$fh = fopen($file, 'w');
			fwrite($fh, serialize($out)); 
			fclose($fh);
			
			unset($out);
		}
	}
	
	/**
	* call all methods related to forms
	**/
	function updateForms()
	{
		$this -> updateSubmitForms();
		$this -> updateSearchForms();
		$this -> updateSearchFields();
		$this -> updateFeaturedFormFields();
		$this -> updateTitlesFormFields();
		$this -> updateShortFormFields();
	}
	
	/**
	*
	* update data formats by key
	* | cache_data_formats
	*
	**/
	function updateDataFormats()
	{
		global $config, $rlListingTypes;
		
		if ( !$config['cache'] )
		{
			return false;
		}
		
		$this -> setTable( 'data_formats' );

		/* DO NOT SET ANOTHER FIELD FOR ORDER, ID ONLY */
		$data = $this -> fetch( array('ID', 'Parent_ID', 'Key`, CONCAT("data_formats+name+", `Key`) AS `pName', 'Position', 'Default'), array('Status' => 'active'), 'ORDER BY `ID`, `Key`' );

		foreach ($data as $key => $value)
		{
			if ( !$value['Key'] )
				continue;
				
			if ( !array_key_exists($data[$key]['Key'], $out) && empty($data[$key]['Parent_ID']) )
			{
				$out[$data[$key]['Key']] = array();
				$df_info[$data[$key]['ID']] = $data[$key]['Key'];
			}
			else
			{
				if ( !$df_info[$data[$key]['Parent_ID']] )
					continue;
					
				$out[$df_info[$data[$key]['Parent_ID']]][] = $data[$key];
			}
		}
		
		unset($data, $df_info);
		
		if ( $out )
		{
			$this -> file('cache_data_formats');
			$file = RL_CACHE . $config['cache_data_formats'];
			
			$fh = fopen($file, 'w');
			fwrite($fh, serialize($out)); 
			fclose($fh);
			
			unset($out);
		}
	}
	
	/**
	*
	* update listing statistics
	* | cache_listing_statistics
	*
	* @param string $listing_type - listing type key
	* 
	**/
	function updateListingStatistics( $listing_type = false )
	{
		global $config, $rlListingTypes;
		
		if ( !$config['cache'] )
		{
			return false;
		}
		
		$types = $listing_type && $rlListingTypes -> types[$listing_type] ? array($rlListingTypes -> types[$listing_type]) : $rlListingTypes -> types;
		
		foreach ($types as $type)
		{
			if ( $type['Status'] == 'approval' )
				continue;

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
					$total[$value] = $data['Count'];
					$total['total'] += $data['Count'];
					
					/* get today */
					$t_sql = $c_sql ."AND UNIX_TIMESTAMP(`T1`.`Pay_date`) BETWEEN UNIX_TIMESTAMP(CURDATE()) AND UNIX_TIMESTAMP(NOW()) ";
					$data = $this -> getRow($t_sql);
					$today[$value] = $data['Count'];
					$today['total'] += $data['Count'];
				}
				
				$out[$type['Key']]['total'] = $total;
				$out[$type['Key']]['today'] = $today;
			}
			else
			{
				/* get total */
				$data = $this -> getRow($sql);
				$out[$type['Key']]['total'] = $data['Count'];
				
				/* today new */
				$t_sql = $sql ."AND UNIX_TIMESTAMP(`T1`.`Pay_date`) BETWEEN UNIX_TIMESTAMP(CURDATE()) AND UNIX_TIMESTAMP(NOW()) ";
				$data = $this -> getRow($t_sql);
				$out[$type['Key']]['today'] = $data['Count'];
			}
		}
		
		unset($data, $sql, $c_sql, $n_sql, $t_sql);
		
		if ( $out )
		{
			$this -> file('cache_listing_statistics');
			$file = RL_CACHE . $config['cache_listing_statistics'];

			/* update single type statistics only */
			if ( $listing_type && $rlListingTypes -> types[$listing_type] )
			{
				$tmp = $this -> get('cache_listing_statistics');
				$tmp[$listing_type] = $out[$listing_type];
				$fh = fopen($file, 'w');
				fwrite($fh, serialize($tmp)); 
				unset($tmp);
			}
			/* update all */
			else
			{
				$fh = fopen($file, 'w');
				fwrite($fh, serialize($out)); 
			}
			fclose($fh);
			
			unset($out);
		}
	}
	
	/**
	*
	* update all system cache
	*
	**/
	function update()
	{
		$this -> updateDataFormats();
		
		$this -> updateSubmitForms();
		$this -> updateCategoriesByType();
		$this -> updateCategoriesByParent();
		$this -> updateCategoriesByID();
		$this -> updateSearchForms();
		$this -> updateSearchFields();
		
		$this -> updateFeaturedFormFields();
		$this -> updateTitlesFormFields();
		$this -> updateShortFormFields();
		
		$this -> updateListingStatistics();
	}
}
