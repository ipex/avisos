<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LISENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: RLLISTINGSBOX.CLASS.PHP
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

class rlListingsBox extends reefless
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
	* class constructor
	**/
	function rlListingsBox()
	{
		global $rlLang, $rlValid;
		
		$this -> rlLang   = & $rlLang;
		$this -> rlValid  = & $rlValid;
	}
	
	/**
	* get listings
	*
	* @param string $info - array info
	* @param int $field - fields for update in grid
	*
	* @return array - listings information
	**/
	function checkContentBlock( $info = false, $field = false)
	{
		if( is_array($field) )
		{			
			$data = $this -> fetch( array('Type','Box_type', 'Count', 'Unique'), array( 'ID' => $field[2] ), null, null, 'listing_box', 'row' );
			if( $field[0] == 'Type' )
			{
				$type = $field[1];
				$box_type = $data['Box_type'];
				$limit = $data['Count'];
			}
			elseif( $field[0] == 'Box_type' )
			{
				$type = $data['Type'];
				$box_type = $field[1];
				$limit = $data['Count'];
			}
			elseif( $field[0] == 'Count' )
			{
				$type = $data['Type'];
				$box_type = $data['Box_type'];
				$limit = $field[1];
			}
			$unique = $data['unique'];
		}
		else
		{
			$type = $info['type'];
			$box_type = $info['box_type'];
			$limit = $info['count'];
			$unique = $info['unique'];
		}
		
		
		$content = '
				global $reefless;
				global $rlSmarty;

				$reefless -> loadClass("ListingsBox", null, "listings_box");
				global $rlListingsBox;
				$listings_box = $rlListingsBox -> getListings( "' . $type . '", "' . $box_type . '", "' . $limit . '", "' . $unique . '" );
				$rlSmarty -> assign_by_ref( "listings_box", $listings_box );			
				$rlSmarty -> assign( "type", "' . $type . '" );

				
				$rlSmarty -> display( RL_PLUGINS . "listings_box" . RL_DS . "listings_box.block.tpl" );
			';
		
		
		return $content;
	}
	
	
	
	/**
	* get listings
	*
	* @param string $category - category ID
	* @param string $order - field name for order
	* @param string $order_type - order type
	* @param int $start - start DB position
	* @param int $limit - listing number per request
	* @param int $Unique - Unique listings in box
	*
	* @return array - listings information
	**/
	function getListings( $type = false, $order = false , $limit = false, $unique = false )
	{
		global $sql, $config, $rlListings;
		if ( version_compare($config['rl_version'], '4.1.0') < 0 )
		{
			$sql = "SELECT SQL_CALC_FOUND_ROWS DISTINCT {hook} SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT `T6`.`Thumbnail` ORDER BY `T6`.`Type` DESC, `T6`.`ID` ASC), ',', 1) AS `Main_photo`, ";
			$sql .= "`T1`.*, `T1`.`Shows`, `T3`.`Path` AS `Path`, `T3`.`Key` AS `Key`, `T3`.`Type` AS `Listing_type`, ";
			$sql .= $config['grid_photos_count'] ? "COUNT(`T6`.`Thumbnail`) AS `Photos_count`, " : "";
		}
		else
		{
			$sql = "SELECT SQL_CALC_FOUND_ROWS DISTINCT {hook} ";
			$sql .= "`T1`.*, `T3`.`Path` AS `Path`, `T3`.`Key` AS `Key`, `T3`.`Type` AS `Listing_type`, ";
		}
		
		$GLOBALS['rlHook'] -> load('listingsModifyField');
		
		$sql .= "IF(UNIX_TIMESTAMP(DATE_ADD(`T1`.`Featured_date`, INTERVAL `T2`.`Listing_period` DAY)) > UNIX_TIMESTAMP(NOW()) OR `T2`.`Listing_period` = 0, '1', '0') `Featured` ";
		$sql .= "FROM `" . RL_DBPREFIX . "listings` AS `T1` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_plans` AS `T2` ON `T1`.`Plan_ID` = `T2`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_plans` AS `T4` ON `T1`.`Featured_ID` = `T4`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "categories` AS `T3` ON `T1`.`Category_ID` = `T3`.`ID` ";
		if ( version_compare($config['rl_version'], '4.1.0') < 0 )
		{
			$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_photos` AS `T6` ON `T1`.`ID` = `T6`.`Listing_ID` ";
		}
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "accounts` AS `T7` ON `T1`.`Account_ID` = `T7`.`ID` ";
		
		$GLOBALS['rlHook'] -> load('listingsModifyJoin');
		
		$sql .= "WHERE (UNIX_TIMESTAMP(DATE_ADD(`T1`.`Pay_date`, INTERVAL `T2`.`Listing_period` DAY)) > UNIX_TIMESTAMP(NOW()) OR `T2`.`Listing_period` = 0) ";
		
		$sql .= "AND `T1`.`Status` = 'active' AND `T7`.`Status` = 'active' ";
		
		if($type)
		{
			$sql .= "AND (`T3`.`Type` = '{$type}' OR FIND_IN_SET( `T3`.`Type` , '{$type}') > 0 ) ";
		}
		
		if ( $unique && $rlListings -> selectedIDs )
		{
			$sql .= "AND FIND_IN_SET(`T1`.`ID`, '". implode(',', $rlListings -> selectedIDs) ."') = 0 ";
		}
		
		
		$GLOBALS['rlHook'] -> load('listingsModifyWhere');
		$GLOBALS['rlHook'] -> load('listingsModifyGroup');

		$sql .= "GROUP BY `T1`.`ID` ";
		
		switch ($order){
			case 'popular':
				$sql .= "ORDER BY `T1`.`Shows` DESC ";
				break;
			case 'top_rating':
				$sql .= "ORDER BY `T1`.`lr_rating_votes` DESC ";
				break;
			case 'random':
				$sql .= "ORDER BY RAND() ";
				break;
			case 'recently_added':
				$sql .= "ORDER BY `T1`.`Date` DESC ";
				break;
			default:
				$sql .= "ORDER BY `ID` DESC ";
				break;
		}
		
		$sql .= "LIMIT {$limit} ";
		
		$sql = str_replace('{hook}', $hook, $sql);
		
		$listings = $this -> getAll( $sql );
		$listings = $this -> rlLang -> replaceLangKeys( $listings, 'categories', 'name' );
		
		if ( empty($listings) )
		{
			return false;
		}
		
		$calc = $this -> getRow( "SELECT FOUND_ROWS() AS `calc`" );
		$this -> calc = $calc['calc'];
		
		
		foreach ( $listings as $key => $value )
		{
			if ( $unique)
			{
				/* get listing IDs */
				$rlListings -> selectedIDs[] = $IDs[] = $value['ID'];
			}
			/* populate fields */
			$fields = $GLOBALS['rlListings'] -> getFormFields( $value['Category_ID'], 'short_forms', $value['Listing_type'] );

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
			
			$listings[$key]['listing_title'] = $GLOBALS['rlListings'] -> getListingTitle( $value['Category_ID'], $value, $value['Listing_type'] );
		}		
		return $listings;
	}
	
	
	/**
	* delete Rss
	*
	* @package xAjax
	*
	* @param int $id -  id
	*
	**/
	function ajaxDeleteBoxBlock( $id = false )
	{
		global $_response;
		$id = (int)$id;
		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$_response -> redirect( RL_URL_HOME . ADMIN . '/index.php?action=session_expired' );
			return $_response;
		}
		
		if ( !$id )
		{
			return $_response;
		}
		$key = 'listing_box_' . $id;
		// delete rss feed
		$this -> query("DELETE FROM `" . RL_DBPREFIX . "listing_box` WHERE `ID` = '{$id}' LIMIT 1");
		
		$this -> query("DELETE FROM `" . RL_DBPREFIX . "blocks` WHERE `Key` = '{$key}' LIMIT 1");

		$this -> query("DELETE FROM `" . RL_DBPREFIX . "lang_keys` WHERE `Key` = 'blocks+name+{$key}'");		
			
			$_response -> script("
				listingsBox.reload();
				printMessage('notice', '{$GLOBALS['lang']['block_deleted']}')
			");
		
		return $_response;
	}
}
