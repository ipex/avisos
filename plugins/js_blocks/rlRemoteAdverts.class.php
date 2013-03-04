<?php


/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LISENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: RLREMOTEADVERTS.CLASS.PHP
 *
 *	This script is a commercial software and any kind of using it must be 
 *	coordinate with Flynax Owners Team and be agree to Flynax License Agreement
 *
 *	This block may not be removed from this file or any other files with out 
 *	permission of Flynax respective owners.
 *
 *	Copyrights Flynax Classifieds Software | 2013
 *	http://www.flynax.com/
 *
 ******************************************************************************/

class rlRemoteAdverts extends reefless
{

	/**
	* get listings
	*
	* @param string $category - category ID
	* @param string $order - field name for order
	* @param string $order_type - order type
	* @param int $start - start DB position
	* @param int $limit - listing number per request
	*
	* @return array - listings information
	**/

	function getListings( $limit = false, $type = false, $where = false, $order = false, $order_type = 'DESC', $featured = false )
	{
		global $sorting, $sql;
		
		$start = $start > 1 ? ($start - 1) * $limit : 0;

		$GLOBALS['reefless'] -> loadClass('ListingTypes');
		$GLOBALS['reefless'] -> loadClass('Listings');
		$GLOBALS['reefless'] -> loadClass('Lang');
		$GLOBALS['reefless'] -> loadClass('Cache');

		$sql ="SELECT `Key`, `Path` FROM `".RL_DBPREFIX."pages` WHERE ";
		
		if( $type )
		{
			$sql .="`Key` = 'lt_".$type."' LIMIT 1";
		}else
		{
			$sql .= "`Key` LIKE 'lt_%'";
		}

		$tmp_pages = $this -> getAll( $sql );
		
		foreach( $tmp_pages as $k => $page )
		{
			$lt_pages[ str_replace('lt_', '', $page['Key']) ] = $page['Path'];
		}
		unset( $tmp_pages );

		$sql = "SELECT SQL_CALC_FOUND_ROWS `T1`.*, `T3`.`Path` AS `Path`, `T3`.`Type` AS `Listing_type`, ";

		/*check order field*/
		$fsql = "SHOW FIELDS FROM `".RL_DBPREFIX."listings` ";	
		$struct = $this -> getAll( $fsql );

		foreach( $struct as $sKey => $sVal )
		{
			if( strtolower($struct[$sKey]['Field']) == strtolower($order) )
			{
				$no_error = true;
				break;
			}
		}

		if( !$no_error )
		{
			echo 'Field '.$order.' was not found in structure of listings table, use another field ';
			exit;
		}

		if( strtolower($order) == 'price' )
		{
			$sql .="CAST( SUBSTRING_INDEX(`T1`.`Price`,'|',1) AS SIGNED) AS `price_val`, ";
		}

		$sql .= "IF(TIMESTAMPDIFF(HOUR, `T1`.`Featured_date`, NOW()) <= `T4`.`Listing_period` * 24 OR `T4`.`Listing_period` = 0, '1', '0') `Featured` ";

		$sql .= "FROM `" . RL_DBPREFIX . "listings` AS `T1` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_plans` AS `T2` ON `T1`.`Plan_ID` = `T2`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_plans` AS `T4` ON `T1`.`Featured_ID` = `T4`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "categories` AS `T3` ON `T1`.`Category_ID` = `T3`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "accounts` AS `T7` ON `T1`.`Account_ID` = `T7`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "account_types` AS `T8` ON `T7`.`Type` = `T8`.`Key` ";

		$sql .= "WHERE (";
		$sql .= " TIMESTAMPDIFF(HOUR, `T1`.`Pay_date`, NOW()) <= `T2`.`Listing_period` * 24 "; //round to hour
		$sql .= " OR `T2`.`Listing_period` = 0 ";
		$sql .= ") ";		
		
		$sql .= "AND `T1`.`Status` = 'active' AND `T3`.`Status` = 'active' AND `T7`.`Status` = 'active' ";
		$sql .= "AND `T2`.`Remote_adverts` = '1' AND `T8`.`Remote_adverts` = '1' ";

		foreach( $where as $key => $value )
		{
			if( $key == 'kind_id' || $key == 'category_id' )
			{	
				$sql .= "AND (`T1`.`Category_ID` = '{$value}' OR (FIND_IN_SET('{$value}', `T1`.`Crossed`) > 0 AND `T2`.`Cross` > 0 ) ";
				$sql .= "OR FIND_IN_SET('{$value}', `T3`.`Parent_IDs`) > 0 ) ";
			}
			else
			{
				$sql .="AND `T1`.`{$key}` = '{$GLOBALS['rlValid'] -> xSql($value)}' ";
			}
		}
	 
		$sql .= "AND `T1`.`Status` = 'active' AND `T3`.`Status` = 'active' ";

		$GLOBALS['rlHook'] -> load('listingsModifyWhere');

		if( $type )
		{
			$sql .= "AND `T3`.`Type` = '{$type}' ";
		}

		if( $featured )
		{
			$sql .="AND UNIX_TIMESTAMP(DATE_ADD(`T1`.`Featured_date`, INTERVAL `T2`.`Days` DAY)) > UNIX_TIMESTAMP(NOW()) ";
		}
		
		if( $order )
		{
			if( strtolower($order) == 'price' )
			{
				$sql .= "ORDER BY `price_val` {$order_type}, ";
			}
			else
			{
				$sql .= "ORDER BY `T1`.`{$order}` {$order_type}, ";
			}
		}
		$sql .= "`ID` DESC ";
		$sql .= "LIMIT {$limit} ";

		$listings = $this -> getAll( $sql );


		/* load the utf8 lib */
		loadUTF8functions('ascii', 'utf8_to_ascii', 'unicode');

		$calc = $this -> getRow( "SELECT FOUND_ROWS() AS `calc`" );
		$this -> calc = $calc['calc'];

		if ( !$GLOBALS['config']['cache'] && $category_id )
		{
			$fields = $GLOBALS['rlListings'] -> getFormFields( $category_id, 'short_forms', $listings[0]['Listing_type'] );
		}

		if( $this -> getOne("SELECT `Key` FROM `".RL_DBPREFIX."plugins` WHERE `Status` = 'active' AND `Key` = 'multiField'") )
		{
			$sql = "SELECT * FROM `".RL_DBPREFIX."multi_formats` WHERE 1 ";
			global $multi_formats;
			$mf_tmp = $this -> getAll( $sql );
			foreach( $mf_tmp as $key => $item )
			{
				$multi_formats[ $item['Key'] ] = $item;
			}
		}

		foreach ( $listings as $key => $value )
		{
			if ( $GLOBALS['config']['cache'] || !$category_id)
			{
				$fields = $GLOBALS['rlListings'] -> getFormFields( $value['Category_ID'], 'short_forms', $value['Listing_type'] );
			}

			foreach ( $fields as $fKey => $fValue )
			{
if ( !utf8_is_ascii( $value[$fKey] ) )
				{
					$value[$fKey] = utf8_to_ascii( $value[$fKey] );
				}
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
			
			$listings[$key]['listing_title'] = $GLOBALS['rlListings'] -> getListingTitle( $value['Category_ID'], $value, $value['Listing_type'] );
			$listings[$key]['Page_path'] = $lt_pages[ $value['Listing_type'] ];
		}

		return $listings;
	}


	/**
	* load categories
	*
	* @package AJAX
	*
	* @param string $listing_type - listing type
	* @param int $value - category id
	* @param int $level - level
	*
	* @return array - listings information
	**/

	function ajaxLoadCategories( $listing_type, $value = 0, $level = 0 ) 
	{
		global $_response;

		if( $listing_type )
		{
			$categories = $GLOBALS['rlCategories'] -> getCategories( $value, $listing_type );

			$options = '<option value="0">'.$GLOBALS['lang']['any'].'</option>';
			foreach($categories as $key => $category)
			{
				$options .='<option value="'.$category['ID'].'">'.$category['name'].'</option>';
			}

			$target = 'category_level'.($level+1);

			$_response -> script("$('#{$target}').html('".$options."')");
			$_response -> script("$('#{$target}').removeAttr('disabled');");
		}elseif( count( $GLOBALS['rlListingTypes'] -> types ) > 1 )
		{
			$_response -> script("$('select.multicat').attr('disabled', true).val('0')");
		}

		return $_response;
	}
}
