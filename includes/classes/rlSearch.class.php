<?php

/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: RLSEARCH.CLASS.PHP
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

class rlSearch extends reefless
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
	* @var common class object
	**/
	var $rlCommon;
	
	/**
	* @var current form fields
	**/
	var $fields;
	
	/**
	* @var exclude listing ID in search
	**/
	var $exclude;
	
	/**
	* @var keywords statistics
	**/
	var $keyword_stat;
	
	/**
	* @var keywords map
	**/
	var $keyword_map;
	
	/**
	* class constructor
	**/
	function rlSearch()
	{
		global $rlValid, $rlCommon, $rlLang;
		
		$this -> rlValid = $rlValid;
		$this -> rlCommon = $rlCommon;
		$this -> rlLang = $rlLang;
	}

	/**
	* build search form
	*
	* @param string $key - search form key
	* @param string $type - listing type
	*
	* @return array - form information
	**/
	function buildSearch( $key = false, $type = false )
	{
		global $rlCache, $config;
		
		if ( !$key || !$type )
		{
			return false;
		}
		
		/* get form from cache */
		if ( $config['cache'] )
		{
			return $rlCache -> get('cache_search_forms', $key);
		}
		
		$sql = "SELECT `T1`.`Category_ID`, `T1`.`Group_ID`, `T1`.`Fields`, ";
		$sql .= "`T2`.`Key` AS `Group_key`, `T2`.`Display`, ";
		$sql .= "`T3`.`Type` AS `Listing_type`, `T3`.`Key` AS `Form_key` ";
		$sql .= "FROM `". RL_DBPREFIX ."search_forms_relations` AS `T1` ";
		$sql .= "LEFT JOIN `". RL_DBPREFIX ."listing_groups` AS `T2` ON `T1`.`Group_ID` = `T2`.`ID` ";
		$sql .= "LEFT JOIN `". RL_DBPREFIX ."search_forms` AS `T3` ON `T1`.`Category_ID` = `T3`.`ID` ";
		$sql .= "WHERE `T3`.`Key` = '{$key}' AND `T3`.`Status` = 'active' ";
		$sql .= "ORDER BY `Position` ";
		$relations = $this -> getAll($sql);
		
		if ( !$relations )
		{
			return false;
		}
		
		/* populate field information */
		foreach ($relations as $key => $value)
		{
			$sql = "SELECT `ID`, `Key`, `Type`, `Default`, `Values`, `Condition`, CONCAT('listing_fields+name+', `Key`) AS `pName`, ";
			$sql .= "FIELD(`ID`, '{$value['Fields']}') AS `Order` ";
			$sql .= "FROM `". RL_DBPREFIX ."listing_fields` ";
			$sql .= "WHERE FIND_IN_SET(`ID`, '{$value['Fields']}' ) > 0 AND `Status` = 'active' ";
			$sql .= "ORDER BY `Order`";
			$fields = $this -> getAll($sql);

			$relations[$key]['pName'] = 'listing_groups+name+'. $value['Group_key'];
			$relations[$key]['Fields'] = empty($fields) ? false : $this -> rlCommon -> fieldValuesAdaptation($fields, 'listing_fields', $value['Listing_type']);
		}

		return $relations;
	}
	
	/**
	* get general data of search form
	*
	* @param string $key  - search form key
	* @param string $listing_type_key - listing type key
	* @param bool $tab_form - is form splitted by tabs
	*
	* @todo array - form fields list
	**/
	function getFields( $key = false, $listing_type_key = false, $tab_form = false )
	{
		global $rlCache, $config, $rlListingTypes;
		
		if ( !$key )
		{
			return false;
		}
		
		$arrange_field = $rlListingTypes -> types[$listing_type_key]['Arrange_field'];
		
		/* get form from cache */
		if ( $config['cache'] )
		{
			$fields = $rlCache -> get('cache_search_fields', $key);
			$this -> fields = $this -> rlLang -> replaceLangKeys( $fields, 'listing_fields', array( 'name', 'default' ) );
			
			/* add additional field */
			if ( $tab_form && $arrange_field )
			{
				$a_field = $this -> fetch(array('ID', 'Key', 'Type'), array('Key' => $arrange_field), null, 1, 'listing_fields', 'row');
				if ( $a_field )
				{
					$this -> fields[$arrange_field] = $a_field;
				}
			}

			return true;
		}
		
		$sql = "SELECT `T1`.`Category_ID`, `T1`.`ID`, `T1`.`Fields`, `T2`.`Key` AS `Form_key` ";
		$sql .= "FROM `". RL_DBPREFIX ."search_forms_relations` AS `T1` ";
		$sql .= "LEFT JOIN `". RL_DBPREFIX ."search_forms` AS `T2` ON `T1`.`Category_ID` = `T2`.`ID` ";
		$sql .= "WHERE `T2`.`Key` = '{$key}' AND `T2`.`Status` = 'active' ";
		$sql .= "ORDER BY `Position` ";
		$relations = $this -> getAll($sql);
		
		if ( !$relations )
		{
			return false;
		}
				
		foreach ($relations as $key => $value)
		{
			$sql = "SELECT `ID`, `Key`, `Type`, `Default`, `Values`, `Condition`, `Details_page`, ";
			$sql .= "FIELD(`ID`, '{$value['Fields']}') AS `Order` ";
			$sql .= "FROM `". RL_DBPREFIX ."listing_fields` ";
			$sql .= "WHERE FIND_IN_SET(`ID`, '{$value['Fields']}' ) > 0 AND `Status` = 'active' ";
			$sql .= "ORDER BY `Order`";
			$fields = $this -> getAll($sql);
			
			$fields = $this -> rlLang -> replaceLangKeys( $fields, 'listing_fields', array( 'name', 'default' ) );
			
			foreach ( $fields as $fKey => $fValue )
			{
				$out[$fValue['Key']] = $fValue;
			}
			
			unset($fields);
		}
		
		$this -> fields = $out;
		
		/* add additional field */
		if ( $tab_form && $arrange_field )
		{
			$a_field = $this -> fetch(array('ID', 'Key', 'Type'), array('Key' => $arrange_field), null, 1, 'listing_fields', 'row');
			if ( $a_field )
			{
				$this -> fields[$arrange_field] = $a_field;
			}
		}
	}
	
	/**
	* search listings
	*
	* @param array $data - form data
	* @param string $type - listing type
	* @param int $start - start DB position
	* @param int $limit - listing number per request
	*
	* @return array - listings information
	**/
	function search( $data = false, $type = false, $start = 0, $limit = false )
	{
		global $sql, $custom_order, $config, $rlListings;
		
		$form = $this -> fields;
		
		if ( !$form )
			return false;
		
		$start = $start > 1 ? ($start - 1) * $limit : 0;
		$hook = '';
		
		$this -> loadClass('Listings');
		$this -> loadClass('Common');

		$sql = "SELECT SQL_CALC_FOUND_ROWS {hook} ";
		$sql .= "`T1`.*, `T3`.`Path`, `T3`.`Parent_ID`, `T3`.`Key` AS `Cat_key`, `T3`.`Key` AS `Key`, `T3`.`Type` AS `Listing_type`, ";
		
		$GLOBALS['rlHook'] -> load('listingsModifyFieldSearch', $sql);
		
		//$sql .= "DATE_ADD(`T1`.`Featured_date`, INTERVAL `T2`.`Listing_period` DAY) AS `Featured_expire`, ";
		$sql .= "IF(TIMESTAMPDIFF(HOUR, `T1`.`Featured_date`, NOW()) <= `T4`.`Listing_period` * 24 OR `T4`.`Listing_period` = 0, '1', '0') `Featured` ";
		$sql .= "FROM `" . RL_DBPREFIX . "listings` AS `T1` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_plans` AS `T2` ON `T1`.`Plan_ID` = `T2`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_plans` AS `T4` ON `T1`.`Featured_ID` = `T4`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "categories` AS `T3` ON `T1`.`Category_ID` = `T3`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "accounts` AS `T7` ON `T1`.`Account_ID` = `T7`.`ID` ";
		
		$GLOBALS['rlHook'] -> load('listingsModifyJoinSearch', $sql);

		$sql .= "WHERE (";
		$sql .= " TIMESTAMPDIFF(HOUR, `T1`.`Pay_date`, NOW()) <= `T2`.`Listing_period` * 24 "; //round to hour
		$sql .= " OR `T2`.`Listing_period` = 0 ";
		$sql .= ") ";
		
		foreach ($form as $fKey => $fVal)
		{
			$f = $this -> rlValid -> xSql($data[$fKey]);

			if( !empty($f) )
			{
				switch ($form[$fKey]['Type']){
					case 'mixed':
						if ( $f['df'] )
						{
							$sql .= "AND LOCATE('{$f['df']}', `T1`.`". $fKey ."`) > 0 ";
						}
					case 'price':
						if ( $f['currency'] )
						{
							$sql .= "AND LOCATE('{$f['currency']}', `T1`.`". $fKey ."`) > 0 ";
						}
					case 'unit':
						if ( $f['unit'] )
						{
							$sql .= "AND LOCATE('{$f['unit']}', `T1`.`". $fKey."`) > 0 ";
						}
					case 'number':
						if( (int)$f['from'] )
						{
							$sql .= "AND ROUND(`T1`.`{$fKey}`) >= '".(int)$f['from']."' ";
						}
						if( (int)$f['to'] )
						{
							$sql .= "AND ROUND(`T1`.`{$fKey}`) <= '".(int)$f['to']."' ";
						}
						if ( (int)$f['from'] || (int)$f['to'] )
						{
							$sql .= "AND `T1`.`{$fKey}` <> '' ";
						}
						break;
					
					case 'text':
						if ( $fKey == 'keyword_search' )
						{
							if ( !$this -> keywordSearch($f, $data['keyword_search_type'], $type) )
							{
								return false;
							}
						}
						else
						{
							if ( is_array($f) )
							{
								// plugin handler
							}
							elseif ( is_numeric($f) )
							{
								$sql .= "AND `T1`.`{$fKey}` LIKE '%". $f ."%' ";
							}
							else
							{
								$sql .= "AND (MATCH (`T1`.`{$fKey}`) AGAINST('". $f ."' IN BOOLEAN MODE)) ";
							}
						}
						break;
					
					case 'date':
						if ( $form[$fKey]['Default'] == 'single')
						{
							if( $f['from'] )
							{
								$sql .= "AND UNIX_TIMESTAMP(`T1`.`{$fKey}`) >= UNIX_TIMESTAMP('". $f['from'] ."') ";
							}
							if( $f['to'] )
							{
								$sql .= "AND UNIX_TIMESTAMP(`T1`.`{$fKey}`) <= UNIX_TIMESTAMP('". $f['to'] ."') ";
							}
						}
						elseif ( $form[$fKey]['Default'] == 'multi')
						{
							$sql .= "AND UNIX_TIMESTAMP(`T1`.`{$fKey}`) <= UNIX_TIMESTAMP('". $f ."') ";
							$sql .= "AND UNIX_TIMESTAMP(`T1`.`{$fKey}_multi`) >= UNIX_TIMESTAMP('". $f ."') ";
						}
						break;
					
					case 'select':
						if ( $form[$fKey]['Condition'] == 'years' )
						{
							if( $f['from'] )
							{
								$sql .= "AND `T1`.`{$fKey}` >= '".(int)$f['from']."' ";
							}
							if( $f['to'] )
							{
								$sql .= "AND `T1`.`{$fKey}` <= '".(int)$f['to']."' ";
							}
						}
						elseif ( $form[$fKey]['Key'] == 'Category_ID' )
						{
							$sql .= "AND ((`T1`.`{$fKey}` = '{$f}' OR FIND_IN_SET('{$f}', `T3`.`Parent_IDs`) > 0) OR (FIND_IN_SET('{$f}', `T1`.`Crossed`) > 0)) ";
							$hook = "IF (FIND_IN_SET('{$f}', `T1`.`Crossed`) > 0, 1, 0) AS `Crossed_listing`, ";
						}
						elseif ( $form[$fKey]['Key'] == 'posted_by' )
						{
							$sql .= "AND `T7`.`Type` = '". $f ."' ";
						}
						else
						{
							$sql .= "AND `T1`.`{$fKey}` = '". $f ."' ";
						}
						break;
					
					case 'bool':
						if ( $f == 'on' )
						{
							$sql .= "AND `T1`.`{$fKey}` = '1' ";
						}
						else
						{
							$sql .= "AND `T1`.`{$fKey}` = '0' ";
						}
						break;

					case 'radio':
						$sql .= "AND `T1`.`{$fKey}` = '".$f."' ";
						break;
					
					case 'checkbox':
						unset($f[0]);
						if ( !empty($f) )
						{
							$sql .= "AND (";
							foreach ( $f as $fI => $fV )
							{
								$sql .= "FIND_IN_SET('". $f[$fI] ."', `T1`.`{$fKey}`) > 0 OR ";
							}
							$sql = substr( $sql, 0, -3 );
							$sql .= ") ";
						}
						break;
				}
				
				$GLOBALS['rlHook'] -> load('searchSelectArea', $sql, $f, $fVal);
			}
		}

		if ( $this -> exclude )
		{
			$sql .= "AND FIND_IN_SET(`T1`.`ID`, '{$this -> exclude}') < 0 ";
		}
		
		$sql .= "AND `T1`.`Status` = 'active' AND `T3`.`Status` = 'active' AND `T7`.`Status` = 'active' ";
		if ( $type )
		{
			$sql .= "AND `T3`.`Type` = '{$type}' ";
		}

		if( $data['with_photo'] )
		{
			$sql .= "AND `T1`.`Photos_count` > 0 ";
		}
		
		$GLOBALS['rlHook'] -> load('listingsModifyWhereSearch', $sql);
		$GLOBALS['rlHook'] -> load('listingsModifyGroupSearch', $sql);
		
		if ( false === strpos($sql, 'GROUP BY') )
		{
			//$sql .= " GROUP BY `T1`.`ID` ";
		}
		
		$sql .= "ORDER BY `Featured` DESC ";

		$data['sort_type'] = in_array( $data['sort_type'], array('asc', 'desc' )) ?  $data['sort_type'] : 'asc' ;

		if ( $custom_order )
		{
			$sql .= ", `{$custom_order}` ". strtoupper($data['sort_type']) . " ";
		}
		elseif ( $form[$data['sort_by']] )
		{
			switch ($form[$data['sort_by']]['Type']){
				case 'price':
				case 'unit':
				case 'mixed':
					$sql .= ", ROUND(`T1`.`{$form[$data['sort_by']]['Key']}`) " . strtoupper($data['sort_type']) . " ";
					break;
				
				case 'select':
					if ( $form[$data['sort_by']]['Key'] == 'Category_ID' )
					{
						$sql .= ", `T3`.`Key` " . strtoupper($data['sort_type']) . " ";
					}
					elseif ( $form[$data['sort_by']]['Key'] == 'Listing_type' )
					{
						$sql .= ", `T3`.`Type` " . strtoupper($data['sort_type']) . " ";
					}
					else
					{
						$sql .= ", `T1`.`{$form[$data['sort_by']]['Key']}` " . strtoupper($data['sort_type']) . " ";
					}
					break;
				
				default:
					$sql .= ", `T1`.`{$form[$data['sort_by']]['Key']}` " . strtoupper($data['sort_type']) . " ";
					break;
			}
		}
		else
		{
			$sql .= ", `T1`.`Date` DESC ";
		}
		
		$sql .= "LIMIT {$start}, {$limit} ";
		
		/* replace hook */
		$sql = str_replace('{hook}', $hook, $sql);

		$listings = $this -> getAll( $sql );
		$listings = $this -> rlLang -> replaceLangKeys( $listings, 'categories', 'name' );

		$calc = $this -> getRow( "SELECT FOUND_ROWS() AS `calc`" );
		$this -> calc = $calc['calc'];

		foreach ( $listings as $key => $value )
		{
			$fields = $rlListings -> getFormFields($value['Category_ID'], 'short_forms', $value['Listing_type']);
			
			foreach ( $fields as $fKey => $fValue )
			{
				if ( $first )
				{
					$fields[$fKey]['value'] = $GLOBALS['rlCommon'] -> adaptValue( $fValue, $value[$fKey], 'listing', $value['ID'] );
				}
				else
				{
					if ( $field['Condition'] == 'isUrl' || $field['Condition'] == 'isEmail' )
					{
						$fields[$fKey]['value'] = $listings[$key][$item];
					}
					else
					{
						$fields[$fKey]['value'] = $GLOBALS['rlCommon'] -> adaptValue( $fValue, $value[$fKey], 'listing', $value['ID'] );
					}
				}
				$first++;
			}
			
			$listings[$key]['fields'] = $fields;
			$listings[$key]['listing_title'] = $rlListings -> getListingTitle( $value['Category_ID'], $value, $value['Listing_type'] );
		}

		return $listings;
	}

	/**
	* build keyword search mysql request by requested kewywords string
	*
	* @param string $request - listing type
	* @param int $mode - search mode, 1 - All words, any order; 2 - Any words, any order; 3-  Exact words, exact order; 4 - Exact words, any order
	* @param string $type - listing type key
	*
	* @return array - listings information
	**/
	function keywordSearch( $query = false, $mode = 2, $type = false )
	{
		global $sql, $rlCommon;
		
		$mode = !$mode ? 2 : $mode;
		$query = trim($query);
		$query = preg_replace('/(\\s)\\1+/', ' ', $query);
		$query = str_replace('%', '', $query);
		$query_exploded = explode(' ', $query);
		$query_count = count($query_exploded);
		
		/* remove short words from the query */
		foreach ( $query_exploded as $wi => $word )
		{
			if ( $this -> rlCommon -> strLen( $word, '<', 3 ) )
			{
				unset( $query_exploded[$wi] );
			}
		}
		
		if ( !$query || empty($query_exploded) )
			return;
			
		/* save the query to highlight it on the listing details */
		$_SESSION['keyword_search_data']['keyword_search'] = $query;
			
		/* get system fields */
		$this -> setTable('listing_fields');
		$fields = $this -> fetch(array('Key', 'Type', 'Condition'), array('Status' => 'active'), "AND FIND_IN_SET(`Type`, 'bool,file,accept,image') = 0 AND `Key` != 'keyword_search' AND `Key` <> 'search_account' "); //let's set limit to 20 fields
		
		if ( !$fields )
			return;
			
		foreach ($fields as $field)
		{
			if ( in_array($field['Type'], array('text', 'textarea', 'number'/*, 'date'*/)) || $field['Key'] == 'years' )
			{
				if ( in_array($field['Type'], array('text', 'textarea')) )
				{
					$direct_fields[] = $field['Key'];
				}
			}
			else
			{
				$indirect_fields[$field['Key']] = $field;
			}
		}
		
		/* add keywords to statistics stack */
		if ( $query_count > 1 )
		{
			$this -> keyword_stat = array_flip($query_exploded);
			foreach ($this -> keyword_stat as $kk => $kv)
			{
				$this -> keyword_stat[$kk] = 0;
				$this -> keyword_map[$kk] = '';
			}
		}
		
		/* search by direct fields */
		if ( $direct_fields )
		{
			switch ($mode){
				case 1:
					$direct_sub_sql = "(CONCAT_WS(' ', `T1`.`". implode("`, `T1`.`", $direct_fields) ."`) RLIKE '". implode("?|", $query_exploded) ."?') ";
					if ( $query_count > 1 )
					{
						foreach ($query_exploded as $query_exploded_item)
						{
							$direct_sub_select_sql .= ", SUM(IF(CONCAT_WS(' ', `T1`.`". implode("`, `T1`.`", $direct_fields) ."`) RLIKE '{$query_exploded_item}?', 1, 0)) AS `{$query_exploded_item}` ";
						}
					}
					$direct_concat_sign = 'AND';
					break;
				
				case 2:
					$direct_sub_sql = "(CONCAT_WS(' ', `T1`.`". implode("`, `T1`.`", $direct_fields) ."`) RLIKE '". implode("?|", $query_exploded) ."?') ";
					$direct_concat_sign = 'OR';
					break;
					
				case 3;
					$direct_sub_sql = "CONCAT_WS(' ', `T1`.`". implode("`, `T1`.`", $direct_fields) ."`) LIKE '%{$query}%' ";
					$direct_concat_sign = 'AND';
					break;
			}
			
			if ( $direct_sub_sql && $this -> searchTest($direct_sub_sql, $type, $direct_sub_select_sql) )
			{
				$sub_sql .= "(";
				$sub_sql .= $direct_sub_sql;
				$sub_sql .= ")";
			}
			else
			{
				unset($direct_concat_sign);
			}
		}
		
		/* search by indirect fields */
		if ( $indirect_fields )
		{
			$ind_sql = "SELECT DISTINCT `Key`, IF(`Key` LIKE 'listing_fields+name+%', 0, 1) AS `Df` ";
			if ( $mode == 1 && $query_count > 1 )
			{
				foreach ($query_exploded as $query_exploded_item)
				{
					$ind_sql .= ", SUM(IF(`Value` RLIKE '{$query_exploded_item}?', 1, 0)) AS `{$query_exploded_item}` ";
				}
			}
			$ind_sql .= "FROM `". RL_DBPREFIX ."lang_keys` ";
			$ind_sql .= "WHERE (`Key` RLIKE 'listing_fields\\\\+name\\\\+.*\\\\_[0-9]+' OR `Key` LIKE 'data_formats+name+%') AND ( ";
			switch ($mode){
				case 1:
					$ind_sql .= "`Value` RLIKE '". implode("?' OR `Value` RLIKE '", $query_exploded) ."?'";
					$indirect_concat_sign = 'AND';
					break;
				
				case 2:
					$ind_sql .= "`Value` RLIKE '". implode("?' OR `Value` RLIKE '", $query_exploded) ."?'";
					$indirect_concat_sign = 'OR';
					break;
					
				case 3;
					$ind_sql .= "`Value` LIKE '%{$query}%'";
					$indirect_concat_sign = 'AND';
					break;
			}
			
			$ind_sql .= ") AND `Status` = 'active' AND `Code` = '". RL_LANG_CODE ."' ";
			//$ind_sql .= "LIMIT 20";
			
			$indirect_keys = $this -> getAll($ind_sql);
			
			/* check matches */
			if ( $query_count > 1 && $indirect_keys )
			{
				foreach ($indirect_keys as $ind_match)
				{
					foreach ($query_exploded as $kv)
					{
						if ( $ind_match[$kv] )
						{
							$this -> keyword_map[$kv] = $ind_match['Key'];
						}
					}
				}
			}

			if ( $indirect_keys )
			{
				/* get system listing table fields */
				$tmp_s_fields = $this -> getAll("SHOW FIELDS FROM `". RL_DBPREFIX ."listings`");
				foreach ( $tmp_s_fields as $s_field )
				{
					$s_fields[] = $s_field['Field'];
				}
				unset($tmp_s_fields);

				foreach ($indirect_keys as $ind_key)
				{
					$item = str_replace(array('listing_fields+name+', 'data_formats+name+'), '', $ind_key);
					if ( $ind_key['Df'] )
					{
						// get fields one time only and just in case inderect field relates to Data Formats
						if ( !$df_fields )
						{
							$df_fields_tmp = $this -> fetch(array('Key'), array('Status' => 'active'), "AND `Type` IN ('select', 'radio', 'mixed', 'checkbox') AND `Condition` <> ''", null, 'listing_fields');
							foreach ($df_fields_tmp as $df_field)
							{
								if ( in_array($df_field['Key'], $s_fields) )
								{
									$df_fields[] = $df_field['Key'];
								}
							}
							unset($df_fields_tmp);
						}
						$value = $item['Key'];
						$indirect_sub_sql .= '`T1`.`'. implode("` = '{$value}' OR `T1`.`", $df_fields) ."` = '{$value}' OR ";
						if ( $mode == 1 && $query_count > 1 )
						{
							$indirect_sub_select_sql .= ", SUM(IF(`T1`.`". implode("` = '{$value}' OR `T1`.`", $df_fields) ."` = '{$value}', 1, 0)) AS `{$ind_key['Key']}` ";
						}
					}
					else
					{
						$item = explode('_', $item['Key']);
						$value = array_pop($item);
						$t_field = implode('_', $item);
						
						if ( !in_array($t_field, $s_fields) )
						{
							continue;
						}

						$indirect_sub_sql .= "(`T1`.`{$t_field}` <> '' AND FIND_IN_SET('{$value}', `T1`.`{$t_field}`) > 0) OR ";
						if ( $mode == 1 && $query_count > 1 )
						{
							$indirect_sub_select_sql .= ", SUM(IF(`T1`.`{$t_field}` <> '' AND FIND_IN_SET('{$value}', `T1`.`{$t_field}`) > 0, 1, 0)) AS `{$ind_key['Key']}` ";
						}
					}
				}
				
				$indirect_sub_sql = preg_replace('/(AND|OR)\s$/', '', $indirect_sub_sql);
				if ( $indirect_sub_sql && $this -> searchTest($indirect_sub_sql, $type, $indirect_sub_select_sql) )
				{
					$sub_sql .= $direct_concat_sign ? " {$direct_concat_sign} (" : "(";
					$sub_sql .= $indirect_sub_sql;
					$sub_sql .= ")";
				}
				else
				{
					unset($indirect_concat_sign);
				}
			}
			else
			{
				unset($indirect_concat_sign);
			}
		}
		
		/* search by category */
		$cat_sql = "SELECT `T2`.`ID` ";
		if ( $mode == 1 && $query_count > 1 )
		{
			foreach ($query_exploded as $query_exploded_item)
			{
				$cat_sql .= ", SUM(IF(`Value` RLIKE '{$query_exploded_item}?', 1, 0)) AS `{$query_exploded_item}` ";
			}
		}
		$cat_sql .= "FROM `". RL_DBPREFIX ."lang_keys` AS `T1` ";
		$cat_sql .= "LEFT JOIN `". RL_DBPREFIX ."categories` AS `T2` ON `T1`.`Key` = CONCAT('categories+name+', `T2`.`Key`) ";
		$cat_sql .= "WHERE `T1`.`Key` LIKE 'categories+name+%' AND (";
		switch ($mode){
			case 1:
				$cat_sql .= "`T1`.`Value` RLIKE '". implode("?|", $query_exploded) ."?'";
				break;
			
			case 2:
				$cat_sql .= "`T1`.`Value` RLIKE '". implode("?|", $query_exploded) ."?'";
				break;
				
			case 3;
				$cat_sql .= "`T1`.`Value` LIKE '%{$query}%'";
				break;
		}
		$cat_sql .= ") AND `T1`.`Status` = 'active' AND `T2`.`Status` = 'active' AND `T1`.`Code` = '". RL_LANG_CODE ."' ";
		
		if ( $type )
		{
			$cat_sql .= "AND `T2`.`Type` = '{$type}'";
		}
		
		$cat_ids = $this -> getAll($cat_sql);
		
		/* check matches */
		if ( $query_count > 1 && $indirect_keys )
		{
			foreach ($cat_ids as $cat_match)
			{
				foreach ($query_exploded as $kv)
				{
					if ( $cat_match[$kv] )
					{
						$this -> keyword_map[$kv] = $cat_match['ID'];
					}
				}
			}
		}

		if ( $cat_ids )
		{
			foreach ($cat_ids as $cat_id)
			{
				$cat_sub_sql .= "(`T1`.`Category_ID` = '{$cat_id['ID']}' OR FIND_IN_SET('{$cat_id['ID']}', `T3`.`Parent_IDs`) > 0) OR ";
				if ( $mode == 1 && $query_count > 1 )
				{
					$cat_sub_select_sql .= ", SUM(IF(`T1`.`Category_ID` = '{$cat_id['ID']}' OR FIND_IN_SET('{$cat_id['ID']}', `T3`.`Parent_IDs`) > 0, 1, 0)) AS `cat_{$cat_id['ID']}` ";
				}
			}
			$cat_sub_sql = rtrim($cat_sub_sql, 'OR ');
			
			if ( $this -> searchTest($cat_sub_sql, $type, $cat_sub_select_sql)  )
			{
				/* sign handler */
				$indirect_concat_sign = $indirect_concat_sign ? $indirect_concat_sign : $direct_concat_sign;
				if ( $query_count == 1 && $mode == 1 && $indirect_concat_sign )
				{
					$indirect_concat_sign = 'OR';
				}
				
				$sub_sql .= $indirect_concat_sign ? " {$indirect_concat_sign} (" : "(";
				$sub_sql .= $cat_sub_sql;
				$sub_sql .= ")";
			}
		}

		$allow_search = true;
		
		if ( $mode == 1 && $query_count > 1 )
		{
			foreach ($this -> keyword_stat as $count)
			{
				if ( !$count )
				{
					$allow_search = false;
					break;
				}
			}
		}
		
		/* build final sub query */
		if ( $sub_sql && $allow_search )
		{
			$sql .= "AND (";
			$sql .= $sub_sql;
			$sql .= ") ";
		}
		else
		{
			return false;
		}
		
		return true;
	}
	
	/**
	* search listings
	*
	* @param array $data - form data
	* @param string $type - listing type
	* @param int $start - start DB position
	* @param int $limit - listing number per request
	*
	* @return array - listings information
	**/
	function searchTest( $where = false, $type = false, $select = false )
	{
		global $config;
		
		if ( !$where )
			return false;
		
		$sql = "SELECT COUNT(`T1`.`ID`) AS `Count` ";
				
		$GLOBALS['rlHook'] -> load('listingsModifyFieldSearch', $sql);

		if ( $select )
		{
			$sql .= $select;
		}

		$sql .= "FROM `" . RL_DBPREFIX . "listings` AS `T1` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_plans` AS `T2` ON `T1`.`Plan_ID` = `T2`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "categories` AS `T3` ON `T1`.`Category_ID` = `T3`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "accounts` AS `T7` ON `T1`.`Account_ID` = `T7`.`ID` ";
		
		$GLOBALS['rlHook'] -> load('listingsModifyJoinSearch', $sql);

		$sql .= "WHERE (UNIX_TIMESTAMP(DATE_ADD(`T1`.`Pay_date`, INTERVAL `T2`.`Listing_period` DAY)) > UNIX_TIMESTAMP(NOW()) OR `T2`.`Listing_period` = 0) ";
		
		$sql .= "AND `T1`.`Status` = 'active' AND `T3`.`Status` = 'active' AND `T7`.`Status` = 'active' ";
		
		if ( $type )
		{
			$sql .= "AND `T3`.`Type` = '{$type}' ";
		}
		
		$sql .= "AND ". $where;
		
		$GLOBALS['rlHook'] -> load('listingsModifyWhereSearch', $sql);
		$GLOBALS['rlHook'] -> load('listingsModifyGroupSearch', $sql);
		
		$results = $this -> getRow($sql);
		
		if ( $select && $results )
		{
			foreach ($this -> keyword_stat as $kk => $lv)
			{
				if ( $results[$kk] || $results[$this -> keyword_map[$kk]] || $results['cat_'. $this -> keyword_map[$kk]] )
				{
					$this -> keyword_stat[$kk]++;
				}
			}
		}
		
		return $results['Count'];
	}
	
	/**
	* save search
	*
	* @package xAjax
	* @param string $type - listing type
	*
	**/
	function ajaxSaveSearch( $type = false )
	{
		global $_response, $account_info, $lang, $post_form_key;
		
		if ( !$type )
		{
			return $_response;
		}

		if ( defined( 'IS_LOGIN' ) )
		{
			$content = $_SESSION[$type .'_post'];

			unset($content['sort_type']);
			unset($content['sort_by']);

			foreach ($content as $key => $value)
			{
				if ( $content[$key]['from'] == $lang['from'] )
				{
					unset($content[$key]['from']);
				}
				if ( $content[$key]['to'] == $lang['to'] )
				{
					unset($content[$key]['to']);
				}
				if ( empty($content[$key]) )
				{
					unset($content[$key]);
				}
				if ( isset($content[$key]['from']) && (empty($content[$key]['from']) && empty($content[$key]['to'])) )
				{
					unset($content[$key]);
				}

				if ( isset($content[$key][0]) && is_array($content[$key]) )
				{
					unset($content[$key][0]);
					if ( empty($content[$key]) )
					{
						unset($content[$key]);
					}
				}
			}

			if ( !empty($content) )
			{
				//$content = flStripslashes($content);
				$content = serialize($this -> rlValid -> xSql($content));
				$form_key = $_POST['form'];

				$exist = $this -> fetch( array('ID'), array( 'Content' => $content, 'Account_ID' => $account_info['ID'] ), null, 1, 'saved_search', 'row' );

				if ( empty( $exist ) )
				{
					$insert = array(
						'Account_ID' => $account_info['ID'],
						'Form_key' => $post_form_key,
						'Listing_type' => $type,
						'Content' => $content,
						'Date' => 'NOW()'
					);
					
					$this -> loadClass('Actions');
					
					$GLOBALS['rlActions'] -> rlAllowHTML = true;
					$GLOBALS['rlActions'] -> insertOne($insert, 'saved_search');
					
					$_response -> script("printMessage('notice', '{$lang['search_saved']}')");
				}
				else
				{
					$_response -> script("printMessage('error', '{$lang['search_already_saved']}')");
				}
			}
			else
			{
				$_response -> script("printMessage('error', '{$lang['empty_search_disallow']}')");
			}
		}
		else
		{
			$_response -> script("printMessage('error', '{$lang['notice_operation_inhibit']}')");
		}
		
		unset($content, $exist);
		
		return $_response;
	}
	
	/**
	* delete saved search
	*
	* @package xAjax
	*
	* @param string $id  - search id
	*
	**/
	function ajaxDeleteSavedSearch( $id )
	{
		global $_response, $account_info, $lang;

		if ( defined( 'IS_LOGIN' ) )
		{
			$id = (int)$id;
			$info = $this -> fetch( array('ID', 'Account_ID'), array( 'ID' => $id ), null, 1, 'saved_search', 'row' );

			if ( $info['Account_ID'] == $account_info['ID'] )
			{
				$this -> query( "DELETE FROM `". RL_DBPREFIX ."saved_search` WHERE `ID` = '{$info['ID']}' LIMIT 1" );

				$this -> setTable('saved_search');
				$fav = $this -> fetch( array('ID'), array('Account_ID' => $account_info['ID']) );
				
				if ( empty($fav) )
				{
					$empty_mess = '<div class="info">'.$lang['no_saved_search'].'</div>';
					$_response -> assign( 'saved_search_obj', 'innerHTML', $empty_mess );
				}
				
				$_response -> script( "$('#item_{$id}').fadeOut('slow');" );
				$_response -> script( "printMessage('notice', '{$lang['notice_saved_search_deleted']}');" );
			}
		}
		
		return $_response;
	}
	
	/**
	* activate/deactivate/delete saved search
	*
	* @package xAjax
	*
	* @param string $id  - search id
	*
	**/
	function ajaxMassSavedSearch( $id = false, $action = 'activate' )
	{
		global $_response, $account_info, $lang;

		$items = explode( '|', $id );

		if ( defined( 'IS_LOGIN' ) )
		{
			$status = $action == 'activate' ? 'active' : 'approval';
			
			foreach ( $items as $item )
			{
				if ( !empty($item) )
				{
					if ( $action == 'delete' )
					{
						$sql = "DELETE FROM `". RL_DBPREFIX ."saved_search` WHERE `ID` = '{$item}' AND `Account_ID` = '{$account_info['ID']}' LIMIT 1";
						$this -> query( $sql );
						
						$_response -> script( "$('#item_{$item}').fadeOut('slow');" );
					}
					else
					{
						$sql = "UPDATE `". RL_DBPREFIX ."saved_search` SET `Status` = '{$status}' WHERE `ID` = '{$item}' AND `Account_ID` = '{$account_info['ID']}' LIMIT 1";
						$this -> query( $sql );
						
						$html = '<span class="'. $status .'">'. $lang[$status] .'</span>';
						$_response -> assign( 'status_'.$item, 'innerHTML', $html );
					}
				}
			}
			
			if ( !empty($sql) )
			{
				if ( $action == 'delete' )
				{
					$mess = $lang['notice_items_deleted'];
				}
				else
				{
					$mess = $action == 'activate' ? $lang['notice_items_activated'] : $lang['notice_items_deactivated'];
				}
				$_response -> script("printMessage('notice', '{$mess}')");
			}
		}
		
		return $_response;
	}
	
	/**
	* check saved search
	*
	* @package xAjax
	*
	* @param string $id  - search id
	*
	**/
	function ajaxCheckSavedSearch( $id = false )
	{
		global $_response, $pages, $page_info, $account_info, $config, $rlListingTypes, $search_results_url, $rlActions;

		$id = (int)$id;

		if ( defined( 'IS_LOGIN' ) )
		{
			$search = $this -> fetch( array('ID', 'Form_key', 'Content', 'Listing_type'), array('ID' => $id, 'Account_ID' => $account_info['ID']), null, 1, 'saved_search', 'row' );
			$listing_type = $search['Listing_type'];
			
			$update = array(
				'fields' => array('Date' => 'NOW()'),
				'where' => array('ID' => $search['ID'])
			);
			$rlActions -> updateOne($update, 'saved_search');
			
			$data = unserialize( $search['Content'] );

			$_SESSION['post_form_key'] = $search['Form_key'];
			$_SESSION[$listing_type .'_post'] = $data;
			
			$url = $config['mod_rewrite'] ? SEO_BASE . $pages[$rlListingTypes -> types[$listing_type]['Page_key']] .'/'. $search_results_url .'.html' : SEO_BASE .'?page='. $pages[$rlListingTypes -> types[$listing_type]['Page_key']] .'&'. $search_results_url;
			$_response -> redirect($url);
		}
		
		return $_response;
	}
	
	/**
	* build search forms, depends of the forms count, listing types relations and arrange settings
	*
	* @todo - build forms and assign them to SMARTY
	*
	**/
	function getHomePageSearchForm()
	{
		global $rlListingTypes, $rlSmarty, $rlHook, $home_search_forms, $lang;
		
		/* get search forms */
		foreach ($rlListingTypes -> types as $type_key => $listing_type)
		{
			if ( $listing_type['Search_home'] )
			{
				$type_form_number++;
				$active_form_key = $type_key;
				$active_type = $rlListingTypes -> types[$active_form_key];
			}
		}
		
		if ( !$type_form_number )
			return false;
		
		/* get forms by listing types */
		if ( $type_form_number > 1 )
		{
			foreach ($rlListingTypes -> types as $type_key => $listing_type)
			{
				if ( $listing_type['Search_home'] )
				{
					if ( $search_form = $this -> buildSearch( $type_key .'_quick', $type_key ) )
					{
						$form_key = $type_key .'_quick';
						$home_search_forms[$form_key]['data'] = $search_form;
						$home_search_forms[$form_key]['name'] = $lang['search_forms+name+'. $form_key];
						$home_search_forms[$form_key]['listing_type'] = $type_key;
					}
				}
			}
		}
		/* get arranged (optional) search forms by signle type */
		elseif ( $type_form_number == 1 )
		{
			if ( $active_type['Arrange_field'] && $active_type['Arrange_search'] )
			{
				$arrange_values = explode(',', $active_type['Arrange_values']);
				
				foreach ($arrange_values as $arrange_value)
				{
					$form_key = $active_form_key .'_tab'. $arrange_value;
					if ( $search_form = $this -> buildSearch($form_key, $active_form_key) )
					{
						$home_search_forms[$form_key]['data'] = $search_form;
						$home_search_forms[$form_key]['name'] = $lang['search_forms+name+'. $form_key];
						$home_search_forms[$form_key]['listing_type'] = $active_form_key;
						$home_search_forms[$form_key]['arrange_field'] = $active_type['Arrange_field'];
						$home_search_forms[$form_key]['arrange_value'] = $arrange_value;
					}
				}
			}
			else
			{
				if ( $search_form = $this -> buildSearch( $active_form_key .'_quick', $active_form_key ) )
				{
					$form_key = $active_form_key .'_quick';
					$home_search_forms[$form_key]['data'] = $search_form;
					$home_search_forms[$form_key]['name'] = $lang['search_forms+name+'. $form_key];
					$home_search_forms[$form_key]['listing_type'] = $active_form_key;
				}
			}
		}
		
		$rlHook -> load('phpHomeSearchForms');

		$rlSmarty -> assign_by_ref( 'search_forms', $home_search_forms );
	}
}
