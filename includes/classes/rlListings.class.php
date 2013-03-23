<?php

/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: RLLISTINGS.CLASS.PHP
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

class rlListings extends reefless 
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
	* @var common class object
	**/
	var $rlCommon;
	
	/**
	* @var notice class object
	**/
	var $rlNotice;
	
	/**
	* @var actions class object
	**/
	var $rlActions;
	
	/**
	* @var calculate items
	**/
	var $calc;
	
	/**
	* @var listing fields list (view listing details mode)
	**/
	var $fieldsList;
	
	/**
	* @var created listing id
	**/
	var $id;
	
	/**
	* @var selected listing IDs
	**/
	var $selectedIDs;
	
	/**
	* class constructor
	**/
	function rlListings()
	{
		global $rlLang, $rlValid, $rlConfig, $rlCommon, $rlNotice, $rlActions;
		
		$this -> rlLang  = & $rlLang;
		$this -> rlValid = & $rlValid;
		$this -> rlConfig = & $rlConfig;
		$this -> rlCommon = & $rlCommon;
		$this -> rlNotice = & $rlNotice;
		$this -> rlActions = & $rlActions;
	}
	
	/**
	* create listing
	*
	* @param array $plan_info - plan information
	* @param array $data   - listing data
	* @param array $fields - current listing kind fields
	*
	**/
	function create( $plan_info = false, $data = false, $fields = false )
	{
		global $category, $rlCommon, $account_info, $location, $rlHook;
		
		/* collect listing data */
		$listing = array(
			'Category_ID' => $category['ID'],
			'Account_ID' => $account_info['ID'],
			'Plan_ID' => $plan_info['ID'],
			'Date' => 'NOW()',
			'Crossed' => implode(',', $_POST['crossed_categories']),
			'Last_step' => 'form',
			'Last_type' => $_SESSION['add_listing']['listing_type'],
			'Status' => 'incomplete'//the final status will be set on the final step, depends of other parameters
		);
		
		if ( ($plan_info['Featured'] && $plan_info['Advanced_mode'] && $_SESSION['add_listing']['listing_type'] == 'featured') || ($plan_info['Featured'] && !$plan_info['Advanced_mode']) )
		{
			$listing['Featured_ID'] = $plan_info['ID'];
		}

		/* activation/periods handler */
		if ( empty($plan_info['Price']) || ( $plan_info['Type'] == 'package' && !empty($plan_info['Listings_remains']) ) )
		{
			$listing['Pay_date'] = "NOW()";
			
			if ( ($plan_info['Featured'] && $plan_info['Advanced_mode'] && $_SESSION['add_listing']['listing_type'] == 'featured') || ($plan_info['Featured'] && !$plan_info['Advanced_mode']) )
			{
				$listing['Featured_date'] = 'NOW()';
			}
		}

		// generate additional fields information
		if ( !empty($fields) && !empty($data) )
		{
			foreach ($fields as $key => $value)
			{
				// define field key
				$fk = $fields[$key]['Key'];
				
				if ( isset($data[$fields[$key]['Key']]) )
				{
					/* collect location fields/data */
					if ( !$data['account_address_on_map'] && $value['Map'] && $data[$fk] )
					{
						$location[] = $rlCommon -> adaptValue($value, $data[$fk]);
					}
					
					switch ($fields[$key]['Type']){
				
						case 'text':
							if ( $value['Multilingual'] && count($GLOBALS['languages']) > 1 )
							{
								$out = '';
								foreach ($GLOBALS['languages'] as $language)
								{
									$val = $data[$fk][$language['Code']];
									if ( $val )
									{
										$out .= "{|{$language['Code']}|}". $val ."{|/{$language['Code']}|}";
									}
								}

								$listing[$fk] = $out;
							}
							else
							{
								$listing[$fk] = $data[$fk];
							}

							break;
						
						case 'phone':
							$out = '';
						
							/* code */
							if ( $value['Opt1'] )
							{
								$code = (int)substr($data[$fk]['code'], 0, $value['Default']);
								$out = 'c:'. $code .'|';
							}
							
							/* area */
							$area = (int)$data[$fk]['area'];
							$out .= 'a:'. $area . '|';
							
							/* number */
							$number = (int)substr($data[$fk]['number'], 0, $value['Values']);
							$out .= 'n:'. $number;
							
							/* extension */
							if ( $value['Opt2'] )
							{
								$ext = (int)$data[$fk]['ext'];
								$out .= '|e:'. $ext;
							}
							
							$listing[$fk] = $out;
						
							break;
						
						case 'select':
						case 'bool':
						case 'radio':
							$listing[$fk] = $data[$fk];
						
							break;
							
						case 'number':
							$listing[$fk] = preg_replace('/[^\d]/', '', $data[$fk]);
						
							break;
						
						case 'date':
							
							if ( $fields[$key]['Default'] == 'single' )
							{
								$listing[$fk] = $data[$fk];
							}
							elseif ($fields[$key]['Default'] == 'multi')
							{
								$listing[$fk] = $data[$fk]['from'];
								$listing[$fk.'_multi'] = $data[$fk]['to'];
							}
						
							break;

						case 'textarea':
							if ( $value['Condition'] == 'html' )
							{
								$html_fields[] = $value['Key'];
							}
							
							if ( $value['Multilingual'] && count($GLOBALS['languages']) > 1 )
							{
								$limit = (int)$value['Values'];
								
								$out = '';
								foreach ($GLOBALS['languages'] as $language)
								{
									$val = $data[$fk][$language['Code']];
									if ( $limit && $value['Condition'] != 'html' )
									{
										$limit = (int)$value['Values'];
										if ( function_exists('mb_substr') && function_exists('mb_internal_encoding') )
										{
											mb_internal_encoding('UTF-8');
											$val = mb_substr($val, 0, $limit);
										}
										else
										{
											$val = substr($val, 0, $limit);
										}
									}
									
									if ( $val )
									{
										$out .= "{|{$language['Code']}|}". $val ."{|/{$language['Code']}|}";
									}
								}
								$listing[$fk] = $out;
							}
							else
							{
								if ( $value['Values'] )
								{
									$limit = (int)$value['Values'];
									
									if ( $limit && $value['Condition'] != 'html' )
									{
										if ( function_exists('mb_substr') && function_exists('mb_internal_encoding') )
										{
											mb_internal_encoding('UTF-8');
											$data[$fk] = mb_substr($data[$fk], 0, $limit);
										}
										else
										{
											$data[$fk] = substr($data[$fk], 0, $limit);
										}
									}
								}
								$listing[$fk] = $data[$fk];
							}
						
							break;
						
						case 'mixed':

							if ( !empty($data[$fk]['value']) )
							{
								$df = $data[$fk]['value'] . '|' . $data[$fk]['df'];
								$listing[$fk] = $df;
							}

							break;
						
						case 'price':

							if ( !empty($data[$fk]['value']) )
							{
								$data[$fk]['value'] = str_replace(array(',', "'"), '', $data[$fk]['value']);
								
								$price = $data[$fk]['value'] . '|' . $data[$fk]['currency'];
								$listing[$fk] = $price;
							}

							break;
						
						case 'unit':

							if ( !empty($data[$fk]['value']) )
							{
								$unit = $data[$fk]['value'] . '|' . $data[$fk]['unit'];
								$listing[$fk] = $unit;
							}

							break;
						
						case 'checkbox':
						
							unset($chValues);
							
							unset($data[$fk][0]);
							foreach ($data[$fk] as $chRow)
							{
								$chValues .= $chRow.",";
							}
							$chValues = substr( $chValues, 0, -1);
		
							$listing[$fk] = $chValues;
						
							break;

						case 'image':
	if( !empty($_FILES[$fields[$key]['Key']]['name']) )
		{					$file_name = 'listing_' . $fk . '_' . time() . mt_rand();
							$resize_type = $fields[$key]['Default'];
							$resolution = strtoupper($resize_type) == 'C' ? explode('|', $fields[$key]['Values']) : $fields[$key]['Values'];
							
							$file_name = $this -> rlActions -> upload( $fk, $file_name, $resize_type, $resolution, false, false );
							$listing[$fk] = $file_name;
		}					
							break;
						
						case 'file':
	
							$file_name = 'listing_' . $fk . '_' . time() . mt_rand();
							$file_name = $this -> rlActions -> upload( $fk, $file_name, false, false, false, false );
							$listing[$fk] = $file_name;
						
							break;
					}
				}
			}

			$rlHook -> load('listingCreateBeforeInsert');
			
			/* get coordinates by address request */
			if ( !$data['account_address_on_map'] && $location )
			{
				$address = implode(', ', $location);
				$address = urlencode($address);
				$content = $this -> getPageContent("http://maps.googleapis.com/maps/api/geocode/json?address={$address}&sensor=false");
				
				$this -> loadClass('Json');
				$content = $GLOBALS['rlJson'] -> decode($content);
				if ( strtolower($content -> status) == 'ok' )
				{
					$listing['Loc_address'] = $content -> results[0] -> formatted_address;
					$listing['Loc_latitude'] = $content -> results[0] -> geometry -> location -> lat;
					$listing['Loc_longitude'] = $content -> results[0] -> geometry -> location -> lng;
				}
			}
			elseif ( $data['account_address_on_map'] )
			{
				$listing['Loc_address'] = $account_info['Loc_address'];
				$listing['Loc_latitude'] = $account_info['Loc_latitude'];
				$listing['Loc_longitude'] = $account_info['Loc_longitude'];
			}
			
			$res = $GLOBALS['rlActions'] -> insertOne( $listing, 'listings', $html_fields );
			
			$this -> id = mysql_insert_id();
			
			if ( $res && $_SESSION['add_listing']['tmp_id'] )
			{
				$listing_id = mysql_insert_id();
				$tmp_update = array(
					'fields' => array(
						'Listing_ID' => $listing_id
					),
					'where' => array(
						'ID' => $_SESSION['add_listing']['tmp_id']
					)
				);
				$GLOBALS['rlActions'] -> updateOne($tmp_update, 'tmp_categories');
			}
			
			return $res;
		}
		else
		{
			trigger_error( "Can not add new listing, no listing data or fields found.", E_WARNING );
			$GLOBALS['rlDebug'] -> logger("Can not add new listing, no listing data or fields found.");
		}
	}
	
	/**
	* edit listing
	*
	* @param int $id - listing ID
	* @param array $plan_info - plan information
	* @param array $data   - listing data
	* @param array $fields - current listing kind fields
	*
	**/
	function edit( $id = false, $plan_info = false, $data = false, $fields = false )
	{
		global $config, $rlCommon, $rlHook, $location, $account_info;
		
		if ( !$id || !$plan_info || !$data || !$fields )
			return false;
		
		$listing['where'] = array(
			'ID' => $id,
		);
		
		$listing['fields']['Crossed'] = implode(',', $_POST['crossed_categories']);
		$listing['fields']['Plan_ID'] = $plan_info['ID'];
		
		if ( !$config['edit_listing_auto_approval'] )
		{
			$listing['fields']['Status'] = 'pending';
		}
		
		// generate additional fields information
		if ( !empty($fields) && !empty($data) )
		{
			foreach ($fields as $key => $value)
			{
				// define field key
				$fk = $fields[$key]['Key'];
				
				if ( isset($data[$fields[$key]['Key']]) )
				{
					if ( !$data['account_address_on_map'] && $value['Map'] && $data[$fk] )
					{
						$location[] = $rlCommon -> adaptValue($value, $data[$fk]);
					}
					
					switch ($fields[$key]['Type']){
						case 'text':
							if ( $value['Multilingual'] && count($GLOBALS['languages'] ) > 1)
							{
								$out = '';
								foreach ($GLOBALS['languages'] as $language)
								{
									$val = $data[$fk][$language['Code']];
									if ( $val )
									{
										$out .= "{|{$language['Code']}|}". $val ."{|/{$language['Code']}|}";
									}
								}

								$listing['fields'][$fk] = $out;
							}
							else
							{
								$listing['fields'][$fk] = $data[$fk];
							}
							break;
							
						case 'phone':
							$out = '';
							
							/* code */
							if ( $value['Opt1'] )
							{
								$code = (int)substr($data[$fk]['code'], 0, $value['Default']);
								$out = 'c:'. $code .'|';
							}
							
							/* area */
							$area = (int)$data[$fk]['area'];
							$out .= 'a:'. $area . '|';
							
							/* number */
							$number = (int)substr($data[$fk]['number'], 0, $value['Values']);
							$out .= 'n:'. $number;
							
							/* extension */
							if ( $value['Opt2'] )
							{
								$ext = (int)$data[$fk]['ext'];
								$out .= '|e:'. $ext;
							}
							
							$listing['fields'][$fk] = $out;
						
							break;
							
						case 'number':
							$listing['fields'][$fk] = preg_replace('/[^\d]/', '', $data[$fk]);
							
							break;
							
						case 'select':
						case 'bool':
						case 'radio':
							$listing['fields'][$fk] = $data[$fk];
							
							break;
							
						case 'date':
							if ( $fields[$key]['Default'] == 'single' )
							{
								$listing['fields'][$fk] = $data[$fk];
							}
							elseif ($fields[$key]['Default'] == 'multi')
							{
								$listing['fields'][$fk] = $data[$fk]['from'];
								$listing['fields'][$fk.'_multi'] = $data[$fk]['to'];
							}
						
							break;

						case 'textarea':
							if ( $value['Condition'] == 'html' )
							{
								$html_fields[] = $value['Key'];
							}
							
							if ( $value['Multilingual'] && count($GLOBALS['languages']) > 1 )
							{
								$limit = (int)$value['Values'];
								
								$out = '';
								foreach ($GLOBALS['languages'] as $language)
								{
									$val = $data[$fk][$language['Code']];
									
									if ( $limit && $value['Condition'] != 'html' )
									{
										if ( function_exists('mb_substr') && function_exists('mb_internal_encoding') )
										{
											mb_internal_encoding('UTF-8');
											$val = mb_substr($val, 0, $limit);
										}
										else
										{
											$val = substr($val, 0, $limit);
										}
									}
									
									if ( $val )
									{
										$out .= "{|{$language['Code']}|}". $val ."{|/{$language['Code']}|}";
									}
								}
								$listing['fields'][$fk] = $out;
							}
							else
							{
								if ( $value['Values'] )
								{
									$limit = (int)$value['Values'];
									
									if ( $limit && $value['Condition'] != 'html' )
									{
										if ( function_exists('mb_substr') && function_exists('mb_internal_encoding') )
										{
											mb_internal_encoding('UTF-8');
											$data[$fk] = mb_substr($data[$fk], 0, $limit);
										}
										else
										{
											$data[$fk] = substr($data[$fk], 0, $limit);
										}
									}
								}
								$listing['fields'][$fk] = $data[$fk];
							}
							
							break;
						
						case 'mixed';
							if ( empty($data[$fk]['value']) )
							{
								$listing['fields'][$fk] = '';
							}
							else
							{
								$df = $data[$fk]['value'] . '|' . $data[$fk]['df'];
								$listing['fields'][$fk] = $df;
							}
	
							break;
						
						case 'price';
							if ( empty($data[$fk]['value']) )
							{
								$listing['fields'][$fk] = '';
							}
							else
							{
								$data[$fk]['value'] = str_replace(array(',', "'"), '', $data[$fk]['value']);

								$price = $data[$fk]['value'] . '|' . $data[$fk]['currency'];
								$listing['fields'][$fk] = $price;
							}
	
							break;
						
						case 'checkbox';
							unset($chValues);
							
							unset($data[$fk][0]);
							foreach ($data[$fk] as $chRow)
							{
								$chValues .= $chRow.",";
							}
							$chValues = substr( $chValues, 0, -1);
		
							$listing['fields'][$fk] = $chValues;
							
							break;
						
						case 'image':
							$file_name = 'listing_' . $fk . '_' . time() . mt_rand();
							$resize_type = $fields[$key]['Default'];
							$resolution = strtoupper($resize_type) == 'C' ? explode('|', $fields[$key]['Values']) : $fields[$key]['Values'];
							
							$file_name = $this -> rlActions -> upload( $fk, $file_name, $resize_type, $resolution, false, false );
							$listing['fields'][$fk] = $file_name;
							
							break;
						
						case 'file':
							$file_name = 'listing_' . $fk . '_' . time() . mt_rand();
							
							$file_name = $this -> rlActions -> upload( $fk, $file_name, false, false, false, false );
							$listing['fields'][$fk] = $file_name;
							
							break;
					}
				}
			}

			$rlHook -> load('listingCreateBeforeInsert');
			
			/* get coordinates by address request */
			if ( !$data['account_address_on_map'] && $location )
			{
				$address = implode(', ', $location);
				$address = urlencode($address);
				$content = $this -> getPageContent("http://maps.googleapis.com/maps/api/geocode/json?address={$address}&sensor=false");
				
				$this -> loadClass('Json');
				$content = $GLOBALS['rlJson'] -> decode($content);

				if ( strtolower($content -> status) == 'ok' )
				{
					$listing['fields']['Loc_address'] = $content -> results[0] -> formatted_address;
					$listing['fields']['Loc_latitude'] = $content -> results[0] -> geometry -> location -> lat;
					$listing['fields']['Loc_longitude'] = $content -> results[0] -> geometry -> location -> lng;
				}
			}
			elseif ( $data['account_address_on_map'] )
			{
				$listing['fields']['Loc_address'] = $account_info['Loc_address'];
				$listing['fields']['Loc_latitude'] = $account_info['Loc_latitude'];
				$listing['fields']['Loc_longitude'] = $account_info['Loc_longitude'];
			}
			
			return $GLOBALS['rlActions'] -> updateOne( $listing, 'listings', $html_fields );
		}
		else
		{
			trigger_error( "Can not edit listing, no listing data or fields found.", E_WARNING );
			$GLOBALS['rlDebug'] -> logger("Can not edit listing, no listing data or fields found.");
		}
	}
	
	/**
	* is listing active and visible on the front end
	*
	* @param string $listing_id - listing ID
	*
	* @return bool - active or not
	**/
	function isActive( $id = false )
	{
		global $rlHook;
		
		if ( !$id )
			return;
		
		$sql = "SELECT `T1`.`ID` FROM `". RL_DBPREFIX ."listings` AS `T1` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_plans` AS `T2` ON `T1`.`Plan_ID` = `T2`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "categories` AS `T3` ON `T1`.`Category_ID` = `T3`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "accounts` AS `T4` ON `T1`.`Account_ID` = `T4`.`ID` ";
		
		$rlHook -> load('phpIsActiveJoin', $sql);
		
		$sql .= "WHERE ( UNIX_TIMESTAMP(DATE_ADD(`T1`.`Pay_date`, INTERVAL `T2`.`Listing_period` DAY)) > UNIX_TIMESTAMP(NOW()) OR `T2`.`Listing_period` = 0 ) ";
		$sql .= "AND `T1`.`Status` = 'active' AND `T3`.`Status` = 'active' AND `T4`.`Status` = 'active' ";
		
		$rlHook -> load('phpIsActiveWhere', $sql);
		
		$sql .= "AND `T1`.`ID` = '{$id}' LIMIT 1";
		
		return $this -> getRow($sql);
	}
	
	/**
	* get listings by category
	*
	* @param string $category_id - category ID
	* @param string $order - field name for order
	* @param string $order_type - order type
	* @param int $start - start DB position
	* @param int $limit - listing number per request
	*
	* @return array - listings information
	**/
	function getListings( $category_id = false, $order = false, $order_type = 'ASC', $start = 0, $limit = 10 )
	{
		global $sorting, $sql, $custom_order, $config;

		if ( !$category_id )
			return false;
		
		$start = $start > 1 ? ($start - 1) * $limit : 0;
		$hook = '';

		$sql = "SELECT SQL_CALC_FOUND_ROWS DISTINCT {hook} ";
		$sql .= "`T1`.*, `T3`.`Path` AS `Path`, `T3`.`Key` AS `Key`, `T3`.`Type` AS `Listing_type`, ";
		
		$GLOBALS['rlHook'] -> load('listingsModifyField');
		
		$sql .= "IF(TIMESTAMPDIFF(HOUR, `T1`.`Featured_date`, NOW()) <= `T4`.`Listing_period` * 24 OR `T4`.`Listing_period` = 0, '1', '0') `Featured` ";
		$sql .= "FROM `" . RL_DBPREFIX . "listings` AS `T1` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_plans` AS `T2` ON `T1`.`Plan_ID` = `T2`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_plans` AS `T4` ON `T1`.`Featured_ID` = `T4`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "categories` AS `T3` ON `T1`.`Category_ID` = `T3`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "accounts` AS `T7` ON `T1`.`Account_ID` = `T7`.`ID` ";

		$GLOBALS['rlHook'] -> load('listingsModifyJoin');
		
		$sql .= "WHERE (";
		$sql .= " TIMESTAMPDIFF(HOUR, `T1`.`Pay_date`, NOW()) <= `T2`.`Listing_period` * 24 "; //round to hour
		$sql .= " OR `T2`.`Listing_period` = 0 ";
		$sql .= ") ";
		
		$sql .= $config['lisitng_get_children'] ? "AND (" : "AND ";
		
		$sql .= "`T1`.`Category_ID` = '{$category_id}' OR (FIND_IN_SET('{$category_id}', `T1`.`Crossed`) > 0 AND `T2`.`Cross` > 0 ) ";
		$hook = "IF (FIND_IN_SET('{$category_id}', `T1`.`Crossed`) > 0, 1, 0) AS `Crossed_listing`, ";
		
		if ( $config['lisitng_get_children'] )
		{
			$sql .= "OR FIND_IN_SET('{$category_id}', `T3`.`Parent_IDs`) > 0 )";
		}
		
		$sql .= "AND `T1`.`Status` = 'active' AND `T3`.`Status` = 'active' AND `T7`.`Status` = 'active' ";

		$GLOBALS['rlHook'] -> load('listingsModifyWhere');
		$GLOBALS['rlHook'] -> load('listingsModifyGroup');
		
		if ( false === strpos($sql, 'GROUP BY') )
		{
			$sql .= " GROUP BY `T1`.`ID` ";
		}
		
		$sql .= "ORDER BY `Featured` DESC ";
		if ( $custom_order )
		{
			$sql .= ", `{$custom_order}` ". strtoupper($order_type) . " ";
		}
		elseif ( $order )
		{
			switch ($sorting[$order]['Type']){
				case 'price':
				case 'unit':
				case 'mixed':
					$sql .= ", ROUND(`T1`.`{$order}`) " . strtoupper($order_type) . " ";
					break;
				
				case 'select':
					if ( $sorting[$order]['Key'] == 'Category_ID' )
					{
						$sql .= ", `T3`.`Key` " . strtoupper($order_type) . " ";
					}
					else
					{
						$sql .= ", `T1`.`{$order}` " . strtoupper($order_type) . " ";
					}
					break;
				
				default:
					$sql .= ", `T1`.`{$order}` " . strtoupper($order_type) . " ";
					break;
			}
		}
		
		$sql .= ", `ID` DESC ";
		$sql .= "LIMIT {$start}, {$limit} ";

		/* replace hook */
		$sql = str_replace('{hook}', $hook, $sql);

		$listings = $this -> getAll($sql);
		$listings = $this -> rlLang -> replaceLangKeys( $listings, 'categories', 'name' );
		
		if ( empty($listings) )
		{
			return false;
		}

		$calc = $this -> getRow( "SELECT FOUND_ROWS() AS `calc`" );
		$this -> calc = $calc['calc'];
		
		if ( !$config['cache'] )
		{
			$fields = $this -> getFormFields( $category_id, 'short_forms', $listings[0]['Listing_type'] );
		}
		
		foreach ( $listings as $key => $value )
		{
			/* populate fields */
			if ( $config['cache'] )
			{
				$fields = $this -> getFormFields( $value['Category_ID'], 'short_forms', $value['Listing_type'] );
			}
			
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
			
			$listings[$key]['listing_title'] = $this -> getListingTitle( $value['Category_ID'], $value, $value['Listing_type'] );
		}

		return $listings;
	}
	
	/**
	* get listings by account ID
	*
	* @param string $account_id - account ID
	* @param string $order - field name for order
	* @param string $order_type - order type
	* @param int $start - start DB position
	* @param int $limit - listing number per request
	*
	* @return array - listings information
	**/
	function getListingsByAccount( $account_id = false, $order = false, $order_type = 'ASC', $start = 0, $limit = false )
	{
		global $sorting, $sql, $config;

		$start = $start > 1 ? ($start - 1) * $limit : 0;

		$sql = "SELECT SQL_CALC_FOUND_ROWS DISTINCT ";
		$sql .= "`T1`.*, `T1`.`Shows`, `T3`.`Path` AS `Path`, `T3`.`Key` AS `Key`, `T3`.`Type` AS `Listing_type`, ";
		
		$GLOBALS['rlHook'] ->  load('listingsModifyFieldByAccount');
	
		$sql .= "IF(TIMESTAMPDIFF(HOUR, `T1`.`Featured_date`, NOW()) <= `T4`.`Listing_period` * 24 OR `T4`.`Listing_period` = 0, '1', '0') `Featured` ";
		$sql .= "FROM `" . RL_DBPREFIX . "listings` AS `T1` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_plans` AS `T2` ON `T1`.`Plan_ID` = `T2`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_plans` AS `T4` ON `T1`.`Featured_ID` = `T4`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "categories` AS `T3` ON `T1`.`Category_ID` = `T3`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "accounts` AS `T7` ON `T1`.`Account_ID` = `T7`.`ID` ";
		
		$GLOBALS['rlHook'] -> load('listingsModifyJoinByAccount');
		
		$sql .= "WHERE (";
		$sql .= " TIMESTAMPDIFF(HOUR, `T1`.`Pay_date`, NOW()) <= `T2`.`Listing_period` * 24 "; //round to hour
		$sql .= " OR `T2`.`Listing_period` = 0 ";
		$sql .= ") ";
		
		if ( $account_id )
		{
			$sql .= "AND `T1`.`Account_ID` = '{$account_id}' ";
		}
		$sql .= "AND `T1`.`Status` = 'active' AND `T3`.`Status` = 'active' ";
		
		$GLOBALS['rlHook'] ->  load('listingsModifyWhereByAccount');
		$GLOBALS['rlHook'] ->  load('listingsModifyGroupByAccount');
		
		if ( false === strpos($sql, 'GROUP BY') )
		{
			$sql .= " GROUP BY `T1`.`ID` ";
		}

		$sql .= "ORDER BY ";
		if ( $order )
		{
			switch ($sorting[$order]['Type']){
				case 'price':
				case 'unit':
				case 'mixed':
					$sql .= " ROUND(`T1`.`{$sorting[$order]['field']}`) " . strtoupper($order_type) . " ";
				break;
				
				case 'select':
					if ( $sorting[$order]['Key'] == 'Category_ID' )
					{
						$sql .= " `T3`.`Key` " . strtoupper($order_type) . " ";
					}
					else
					{
						$sql .= " `T1`.`{$sorting[$order]['field']}` " . strtoupper($order_type) . " ";
					}
				break;
				
				default:
					$sql .= " `T1`.`{$sorting[$order]['field']}` " . strtoupper($order_type) . " ";
				break;
			}
		}
		else
		{
			$sql .= "`Date` DESC ";
		}
		$sql .= "LIMIT {$start}, {$limit} ";

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
			/* populate fields */
			$fields = $this -> getFormFields( $value['Category_ID'], 'short_forms', $value['Listing_type'] );
			
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
			
			$listings[$key]['listing_title'] = $this -> getListingTitle( $value['Category_ID'], $value, $value['Listing_type'] );
		}

		return $listings;
	}
	
	/**
	* get listings by type and time period
	*
	* @param string $order - field name for order
	* @param string $order_type - order type
	* @param int $start - start DB position
	* @param int $limit - listing number per request
	* @param string $listings_type - listings type
	* @param string $period - time period
	*
	* @return array - listings information
	**/
	function getRecentlyAdded( $start = 0, $limit = false, $listings_type = false )
	{
		global $sql, $config;
		
		$this -> rlValid -> sql($listings_type);
		
		/* define start position */
		$start = $start > 1 ? ($start - 1) * $limit : 0;
		
		$sql = "SELECT SQL_CALC_FOUND_ROWS ";
		$sql .= "TIMESTAMPDIFF(DAY, DATE(`T1`.`Date`), DATE_ADD(CURDATE(), INTERVAL 1 DAY)) AS `Date_diff`, ";
		$sql .= "`T1`.*, `T4`.`Path`, `T4`.`Type` AS `Listing_type`, DATE(`T1`.`Date`) AS `Post_date`, ";
		
		$GLOBALS['rlHook'] -> load('listingsModifyFieldByPeriod');

		$sql .= "IF(TIMESTAMPDIFF(HOUR, `T1`.`Featured_date`, NOW()) <= `T5`.`Listing_period` * 24 OR `T5`.`Listing_period` = 0, '1', '0') `Featured`, ";
		$sql .= "`T4`.`Parent_ID`, `T4`.`Key` AS `Cat_key`, `T4`.`Key` ";
		$sql .= "FROM `" . RL_DBPREFIX . "listings` AS `T1` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_plans` AS `T2` ON `T1`.`Plan_ID` = `T2`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_plans` AS `T5` ON `T1`.`Featured_ID` = `T5`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "categories` AS `T4` ON `T1`.`Category_ID` = `T4`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "accounts` AS `T7` ON `T1`.`Account_ID` = `T7`.`ID` ";
		
		$GLOBALS['rlHook'] -> load('listingsModifyJoinByPeriod');
		
		$sql .= "WHERE (";
		$sql .= " TIMESTAMPDIFF(HOUR, `T1`.`Pay_date`, NOW()) <= `T2`.`Listing_period` * 24 "; //round to hour
		$sql .= " OR `T2`.`Listing_period` = 0 ";
		$sql .= ") ";
		
		$sql .= "AND `T1`.`Status` = 'active' AND `T4`.`Status` = 'active' AND `T7`.`Status` = 'active' ";
		if ( $listings_type )
		{
			$sql .= "AND `T4`.`Type` = '{$listings_type}' ";
		}
		
		$GLOBALS['rlHook'] -> load('listingsModifyWhereByPeriod');
		$GLOBALS['rlHook'] -> load('listingsModifyGroupByPeriod');
		
		/*if ( false === strpos($sql, 'GROUP BY') )
		{
			$sql .= " GROUP BY `T1`.`ID` ";
		}*/
		
		$sql .= "ORDER BY `T1`.`Date` DESC ";
		$sql .= "LIMIT {$start}, {$limit}";

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
			/* populate fields */
			$fields = $this -> getFormFields( $value['Category_ID'], 'short_forms', $value['Listing_type'] );
			
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
			
			$listings[$key]['listing_title'] = $this -> getListingTitle( $value['Category_ID'], $value, $value['Listing_type'] );
		}

		return $listings;
	}
	
	/**
	* load recently added listings by listing type to related area
	*
	* @package AJAX
	*
	* @param string $key - listing type key
	*
	**/
	function ajaxloadRecentlyAdded( $key = false )
	{
		global $_response, $config, $pInfo, $rlSmarty, $rlHook, $lra_listings, $requested_key;
		
		if ( !$key )
			return $_response;
		
		$requested_key = $key;
			
		/* get listings */
		$lra_listings = $this -> getRecentlyAdded(0, $config['listings_per_page'], $key);
		$rlSmarty -> assign_by_ref('listings', $lra_listings);
		
		$pInfo['calc'] = $this -> calc;
		
		$_SESSION['recently_added_type'] = $key;
		$rlSmarty -> assign_by_ref('requested_type', $key);
		$rlSmarty -> assign_by_ref('lt_key', $key);
		
		$pInfo['current'] = 1;
		$rlSmarty -> assign_by_ref('pInfo', $pInfo);
		
		$rlHook -> load('ajaxRecentlyAddedLoadPre');
		
		$tpl = 'blocks' . RL_DS . 'recently.tpl';
		$_response -> assign( 'area_'. str_replace('_', '', $key), 'innerHTML', $rlSmarty -> fetch( $tpl, null, null, false ) );
		
		$_response -> script('flFavoritesHandler()');
		$rlHook -> load('ajaxRecentlyAddedLoadPost');
		
		return $_response;
	}
	
	/**
	* get all my (accout) listings
	*
	* @param string $type - listing type
	* @param string $order - field name for order
	* @param string $order_type - order type
	* @param int $start - start DB position
	* @param int $limit - listing number per request
	*
	* @return array - listings information
	**/
	function getMyListings( $type = false, $order = 'ID', $order_type = 'asc', $start = 0, $limit = false )
	{
		global $sql, $rlListingTypes, $account_info;
		
		$allow_tmp_categories = 0;
		foreach ($rlListingTypes -> types as $ltype)
		{
			if ( $ltype['Cat_custom_adding'] )
			{
				$allow_tmp_categories = 1;
			}
		}
		
		/* define start position */
		$start = $start > 1 ? ($start - 1) * $limit : 0;
		
		$sql = "SELECT SQL_CALC_FOUND_ROWS DISTINCT ";
		$sql .= "`T1`.*, IF( `T2`.`Price` = 0, 'free', '' ) `Free`, `T4`.`Path`, `T4`.`Parent_ID`, CONCAT('categories+name+', `T4`.`Key`) AS `Cat_key`, ";
		$sql .= "DATE_ADD(`T1`.`Pay_date`, INTERVAL `T2`.`Listing_period` DAY) AS `Plan_expire`, CONCAT('listing_plans+name+', `T2`.`Key`) AS `Plan_key`, ";
		$sql .= "DATE_ADD(`T1`.`Featured_date`, INTERVAL `T3`.`Listing_period` DAY) AS `Featured_expire`, `T4`.`Type` AS `Category_type`, `T2`.`Image` AS `Plan_image` ";
		
		if ( $allow_tmp_categories )
		{
			$sql .= ", `T5`.`Name` AS `Tmp_name` ";
		}

		$GLOBALS['rlHook'] -> load('myListingsSqlFields');

		$sql .= ", `T2`.`Image_unlim`, `T2`.`Video` AS `Plan_video`, `T2`.`Video_unlim`, `T4`.`Type` AS `Listing_type`, `T1`.`Last_step` ";

		$sql .= "FROM `" . RL_DBPREFIX . "listings` AS `T1` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_plans` AS `T2` ON `T1`.`Plan_ID` = `T2`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_plans` AS `T3` ON `T1`.`Featured_ID` = `T3`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "categories` AS `T4` ON `T1`.`Category_ID` = `T4`.`ID` ";
		if ( $allow_tmp_categories )
		{
			$sql .= "LEFT JOIN `" . RL_DBPREFIX . "tmp_categories` AS `T5` ON `T1`.`ID` = `T5`.`Listing_ID` ";
		}
		
		$sql .= "WHERE `T1`.`Account_ID` = '{$account_info['ID']}' ";
		$sql .= "AND `T1`.`Status` <> 'trash' ";
		$sql .= "AND `T4`.`Type` = '{$type}' GROUP BY `T1`.`ID` ";
		if ( $order )
		{
			if ( $order == 'Plan_expire' )
			{
				$sql .= "ORDER BY `{$order}` ".strtoupper($order_type)." ";
			}
			elseif ( $order == 'category' )
			{
				$sql .= "ORDER BY `T4`.`Path` ".strtoupper($order_type)." ";
			}
			else
			{
				$sql .= "ORDER BY `T1`.`{$order}` ".strtoupper($order_type)." ";
			}
		}
		else
		{
			$sql .= "ORDER BY `T1`.`ID` DESC ";
		}
		$sql .= "LIMIT {$start}, {$limit}";

		$listings = $this -> getAll( $sql );
		
		if ( empty($listings) )
		{
			return false;
		}

		$calc = $this -> getRow( "SELECT FOUND_ROWS() AS `calc`" );
		$this -> calc = $calc['calc'];
		
		foreach ( $listings as $key => &$value )
		{
			/* populate fields */
			$fields = $this -> getFormFields( $value['Category_ID'], 'short_forms', $value['Listing_type'] );

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
			
			$listings[$key]['listing_title'] = $this -> getListingTitle( $value['Category_ID'], $value, $value['Listing_type'] );

			$GLOBALS['rlHook'] -> load('phpListingsGetMyListings', $value);
		}
		unset($fields);

		return $listings;
	}
	
	/**
	* get my favorite listings
	*
	* @param string $order - field name for order
	* @param string $order_type - order type
	* @param int $start - start DB position
	* @param int $limit - listing number per request
	*
	* @return array - listings information
	**/
	function getMyFavorite( $order = 'ID', $order_type = 'asc', $start = 0, $limit = false )
	{
		global $sql, $rlListings;
		
		$cookies = explode( ',', $this -> rlValid -> xSql( $_COOKIE['favorites'] ) );
		
		if ( !$cookies[0] )
		{
			return false;
		}
		
		/* define start position */
		$start = $start > 1 ? ($start - 1) * $limit : 0;

		$GLOBALS['rlHook'] -> load('myFavoriteSysFields');
		
		$sql = "SELECT SQL_CALC_FOUND_ROWS ";
		$sql .= "`T1`.*, `T4`.`Path`, `T4`.`Type` AS `Listing_type`, `T4`.`Key` AS `Key`, `T4`.`Parent_ID`, `T4`.`Key` AS `Cat_key`, ";
		
		$GLOBALS['rlHook'] -> load('listingsModifyFieldMyFavorite');

		$sql .= "IF(TIMESTAMPDIFF(HOUR, `T1`.`Featured_date`, NOW()) <= `T3`.`Listing_period` * 24 OR `T3`.`Listing_period` = 0, '1', '0') `Featured` ";
		$sql .= "FROM `" . RL_DBPREFIX . "listings` AS `T1` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_plans` AS `T2` ON `T1`.`Plan_ID` = `T2`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_plans` AS `T3` ON `T1`.`Featured_ID` = `T3`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "categories` AS `T4` ON `T1`.`Category_ID` = `T4`.`ID` ";
		
		$GLOBALS['rlHook'] -> load('listingsModifyJoinMyFavorite');
		
		$sql .= "WHERE (";
		$sql .= " TIMESTAMPDIFF(HOUR, `T1`.`Pay_date`, NOW()) <= `T2`.`Listing_period` * 24 "; //round to hour
		$sql .= " OR `T2`.`Listing_period` = 0 ";
		$sql .= ") ";
		
		$sql .= "AND `T1`.`Status` = 'active' AND `T4`.`Status` = 'active' ";
		$sql .= "AND (`T1`.`ID` = '". implode("' OR `T1`.`ID` ='", $cookies) ."') ";
		
		$GLOBALS['rlHook'] -> load('listingsModifyWhereMyFavorite');
		$GLOBALS['rlHook'] -> load('listingsModifyGroupMyFavorite');
		
		if ( false === strpos($sql, 'GROUP BY') )
		{
			$sql .= " GROUP BY `T1`.`ID` ";
		}
		
		if ( $order )
		{
			if ( $order == 'Category_ID' )
			{
				$sql .= "ORDER BY `T4`.`Path` ".strtoupper($order_type)." ";
			}
			elseif ( $order == 'Featured' )
			{
				$sql .= "ORDER BY `Featured` ".$order_type." ";
			}
			else
			{
				$sql .= "ORDER BY `T1`.`{$order}`, `Featured` ".strtoupper($order_type)." ";
			}
		}
		$sql .= "LIMIT {$start}, {$limit}";

		$listings = $this -> getAll( $sql );
		$listings = $this -> rlLang -> replaceLangKeys( $listings, 'categories', 'name' );
		
		if ( empty($listings) )
		{
			return false;
		}

		$calc = $this -> getRow( "SELECT FOUND_ROWS() AS `calc`" );
		$this ->calc = $calc['calc'];

		foreach ( $listings as $key => $value )
		{
			/* populate fields */
			$fields = $this -> getFormFields( $value['Category_ID'], 'short_forms', $value['Listing_type'] );
			
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
			
			$listings[$key]['listing_title'] = $this -> getListingTitle( $value['Category_ID'], $value, $value['Listing_type'] );
		}

		return $listings;
	}
	
	/**
	* get listing short details by ID
	*
	* @param int $id - listing id
	* @param bool $plan_info - include plan information
	*
	* @return array - listing information
	**/
	function getShortDetails( $id, $plan_info = false )
	{
		if ( !$id )
			return false;
		
		$sql = "SELECT `T1`.*, `T3`.`Type` AS `Listing_type`, `T3`.`Path` AS `Category_path` ";
		if ( $plan_info )
		{
			$sql .= ", `T2`.`Image` AS `Plan_image`, `T2`.`Image_unlim`, `T2`.`Video` AS `Plan_video`, `T2`.`Video_unlim`, ";
			$sql .= "`T2`.`Key` AS `Plan_key` ";
		}
		$sql .= "FROM `" . RL_DBPREFIX . "listings` AS `T1` ";
		if ( $plan_info )
		{
			$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_plans` AS `T2` ON `T1`.`Plan_ID` = `T2`.`ID` ";
		}
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "categories` AS `T3` ON `T1`.`Category_ID` = `T3`.`ID` ";
		$sql .= "WHERE `T1`.`ID` = '{$id}' LIMIT 1";
		
		$listing = $this -> getRow( $sql );
		
		$fields = $this -> getFormFields( $listing['Category_ID'], 'short_forms', $listing['Listing_type'] );
		
		foreach ( $fields as $fKey => $fValue )
		{
			if ( $listing[$fKey] == '' )
			{
				unset($fields[$fKey]);
			}
			else
			{	
				if ( $first )
				{
					$fields[$fKey]['value'] = $GLOBALS['rlCommon'] -> adaptValue( $fValue, $listing[$fKey], 'listing', $listing['ID'] );
				}
				else
				{
					if ( $field['Condition'] == 'isUrl' || $field['Condition'] == 'isEmail' )
					{
						$fields[$fKey]['value'] = $listing[$item];
					}
					else
					{
						$fields[$fKey]['value'] = $GLOBALS['rlCommon'] -> adaptValue( $fValue, $listing[$fKey], 'listing', $listing['ID'] );
					}
				}
				$first++;
			}
		}
		
		$listing['fields'] = $fields;
		$listing['listing_title'] = $this -> getListingTitle( $listing['Category_ID'], $listing, $listing['Listing_type'] );

		return $listing;
	}
	
	/**
	* get listing details by id (using field groups relations)
	*
	* @param int $id - category id
	* @param array $listing - listing fields values
	* @param array $listing_type - listing type details
	*
	* @return array - listing details
	**/
	function getListingDetails( $id, &$listing, $listing_type = false )
	{
		global $rlCache, $config, $rlCategories;
		
		if ( !$id || !$listing || !$listing_type )
		{
			return false;
		}
		
		$id = (int)$id;

		/* get form from cache */
		if ( $config['cache'] )
		{
			$form = $rlCache -> get('cache_submit_forms', $id, $listing_type);
		}
		/* get form from DB */
		else
		{
			$rows = $rlCategories -> getParentCatRelations($id);
			
			if ( empty($rows) )
			{
				/* general category mode */
				if ( $listing_type['Cat_general_cat'] )
				{
					$rows = $rlCategories -> getParentCatRelations($listing_type['Cat_general_cat'], false);
				}
				/* form is empty in any way */
				else
				{
					return false;
				}
			}
			
			if ( !$rows )
			{
				return false;
			}
			
			foreach ( $rows as $key => $value )
			{
				if ( !empty($value['Fields']) )
				{	
					$sql = "SELECT *, FIND_IN_SET(`ID`, '{$value['Fields']}' ) AS `Order`, ";
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
				$form[$index] = $rows[$key];
			}
			unset($rows);
		}

		if ( !$form )
		{
			return false;
		}

		foreach ($form as $gKey => $group)
		{
			if ( $group['Fields'] )
			{
				foreach ( $group['Fields'] as $fKey => $value )
				{
					if ( !empty( $value ) && !empty($listing[$value['Key']]) )
					{
						$form[$gKey]['Fields'][$fKey]['source'] = explode(',', $listing[$value['Key']]);
						$form[$gKey]['Fields'][$fKey]['value'] = $GLOBALS['rlCommon'] -> adaptValue( $value, $listing[$value['Key']], 'listing', $listing['ID'] );
						$this -> fieldsList[] = $form[$gKey]['Fields'][$fKey];
					}
					else
					{
						unset($form[$gKey]['Fields'][$fKey]);
					}
				}
				$form[$gKey]['Fields'] = $this -> rlLang -> replaceLangKeys( $form[$gKey]['Fields'], 'listing_fields', array( 'name' ) );
			}
		}
			
		$form = $this -> rlLang -> replaceLangKeys( $form, 'listing_groups', array( 'name' ) );

		return $form;
	}
	
	/**
	* get parent category fields
	*
	* @param int $id - category id
	* @param string $table - table
	*
	* @return categories fields list
	**/
	function getParentCatFields( $id, $table )
	{
		$sql = "SELECT `T2`.`Key`, `T2`.`Type`, `T2`.`Default`, `T2`.`Condition`, `T2`.`Details_page`, `T2`.`Multilingual`, `T2`.`Opt1`, `T2`.`Opt2` ";
		$sql .= "FROM `" . RL_DBPREFIX . $table . "` AS `T1` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_fields` AS `T2` ON `T1`.`Field_ID` = `T2`.`ID` ";
		$sql .= "WHERE `T1`.`Category_ID` = '{$id}' ORDER BY `T1`.`Position`";
		
		$fields = $this -> getAll( $sql );

		if ( empty($fields) )
		{
			$parent = $this -> getOne('Parent_ID', "`ID` = '{$id}' AND `Parent_ID` != '{$id}'", 'categories');
			
			if ( !empty($parent) )
			{
				return $this -> getParentCatFields($parent, $table);
			}
		}
		else
		{
			foreach ($fields as $value)
			{
				$tmp_fields[$value['Key']] = $value;
			}
			
			$fields = $tmp_fields;
			unset($tmp_fields);
		
			return $fields;
		}
	}
	
	/**
	* get listing form fields
	*
	* @param int $id - category id
	* @param string $table - table
	* @param string $type - listing type
	*
	* @return categories fields list
	**/
	function getFormFields( $id = false, $table = 'short_forms', $type = false )
	{
		global $rlListingTypes, $config, $rlCache;

		if ( !$id || !$type )
		{
			return false;
		}
		
		/* get data from cache */
		if ( $config['cache'] )
		{
			$fields = $rlCache -> get('cache_'. $table .'_fields', $id, $rlListingTypes -> types[$type]);
			$fields = $GLOBALS['rlLang'] -> replaceLangKeys( $fields, 'listing_fields', array( 'name', 'default' ) );
			
			return $fields;
		}
		
		/* get data from db */
		$fields = $this -> getParentCatFields($id, $table);
		
		if ( empty($fields) && $rlListingTypes -> types[$type]['Cat_general_cat'] )
		{
			$fields = $this -> getParentCatFields($rlListingTypes -> types[$type]['Cat_general_cat'], $table);
		}
		
		$fields = $GLOBALS['rlLang'] -> replaceLangKeys( $fields, 'listing_fields', array( 'name', 'default' ) );

		return $fields;
	}
	
	/**
	* make photos main
	*
	* @package AJAX
	*
	* @param int $listing_id - listing id
	* @param int $id - photo item id
	*
	**/
	function ajaxMakeMain( $listing_id = false, $id = false )
	{
		global $_response, $account_info, $lang;
		
		if ( !$listing_id || !$id )
			return $_response;
		
		$_response -> setCharacterEncoding('UTF-8');
		
		/* get listing info */
		$listing = $this -> getShortDetails( $listing_id );
		
		if ( $listing['Account_ID'] != $account_info['ID'] && !defined('REALM') )
		{
			return $_response;
		}
		
		/* update photos table */
		$update = array(
			array(
				'fields' => array( 'Type' => 'photo' ),
				'where' => array( 'Listing_ID' => $listing_id )
			),
			array(
				'fields' => array( 'Type' => 'main' ),
				'where' => array( 'Listing_ID' => $listing_id, 'ID' => $id )
			)
		);
		
		$GLOBALS['rlActions'] -> update($update, 'listing_photos');
		
		/* update listing data */
		$this -> updatePhotoData($listing_id);
		
		$_response -> script("
			printMessage('notice', '{$lang['set_primary_notice']}');
			$('div#fileupload span.item div.photo_navbar span.primary span').hide();
			$('div#fileupload span.item div.photo_navbar a').show();
			
			$('div.#navbar_{$id}').find('a').hide();
			$('div.#navbar_{$id}').find('span.primary span').show();
		");
		
		return $_response;
	}
	
	/**
	* reorder photos
	*
	* @package AJAX
	*
	* @param int $listing_id - listing id
	* @param string $data - sorting data
	*
	**/
	function ajaxReorderPhoto( $listing_id = false, $data = false )
	{
		global $_response, $account_info, $lang;
		
		if ( !$listing_id || !$data )
			return $_response;
		
		$listing_id = (int)$listing_id;
			
		$_response -> setCharacterEncoding('UTF-8');
		
		/* get listing info */
		$listing = $this -> getShortDetails( $listing_id );

		if ( $listing['Account_ID'] != $account_info['ID'] && !defined('REALM') )
		{
			return $_response;
		}
		
		$sort  = explode(';', $data);
		foreach ($sort as $value)
		{
			$item = explode(',', $value);
			$update[] = array(
				'fields' => array( 'Position' => $item[1] ),
				'where' => array( 'ID' => $item[0] )
			);
		}
		
		$GLOBALS['rlActions'] -> update( $update, 'listing_photos' );
		
		/* update listing data */
		$this -> updatePhotoData($listing_id);
		
		return $_response;
	}
	
	/**
	* reorder video
	*
	* @package AJAX
	*
	* @param int $listing_id - listing id
	* @param string $data - sorting data
	*
	**/
	function ajaxReorderVideo( $listing_id = false, $data = false )
	{
		global $_response, $account_info, $lang;
		
		if ( !$listing_id || !$data )
			return $_response;
		
		$listing_id = (int)$listing_id;
			
		$_response -> setCharacterEncoding('UTF-8');
		
		/* get listing info */
		$listing = $this -> getShortDetails( $listing_id );

		if ( $listing['Account_ID'] != $account_info['ID'] && !defined('REALM') )
		{
			return $_response;
		}
		
		$sort  = explode(';', $data);
		foreach ($sort as $value)
		{
			$item = explode(',', $value);
			$update[] = array(
				'fields' => array( 'Position' => $item[1] ),
				'where' => array( 'ID' => $item[0] )
			);
		}
		
		$GLOBALS['rlActions'] -> update( $update, 'listing_video' );
		
		return $_response;
	}
	
	/**
	* edit photo description
	*
	* @package AJAX
	*
	* @param int $id - photo item id
	* @param string $desc  - new description
	*
	**/
	function ajaxEditDesc( $id = false, $desc = false )
	{
		global $_response, $account_info, $lang;
		
		if ( !$id )
			return $_response;
		
		$_response -> setCharacterEncoding('UTF-8');
		
		/* get photo details */
		$photo = $this -> fetch( array('Listing_ID', 'Description'), array('ID' => $id), null, 1, 'listing_photos', 'row' );

		/* get listing info */
		$listing = $this -> getShortDetails( $photo['Listing_ID'] );
		
		if ( $listing['Account_ID'] != $account_info['ID'] && !defined('REALM') )
		{
			return $_response;
		}

		if ( $photo['Description'] != $desc )
		{
			$update = array(
				'fields' => array('Description' => $desc),
				'where' => array( 'ID' => $id )
			);
			$GLOBALS['rlActions'] -> updateOne( $update, 'listing_photos' );
			
			$_response -> script("
				printMessage('notice', '{$lang['notice_description_saved']}');
			");
		}
		
		$_response -> script("
			$('div.#navbar_{$id} input').hide();
			$('div.#navbar_{$id}').find('img.edit,img.crop,span.primary').show();
		");
		
		return $_response;
	}
	
	/**
	* get listing title by kind ID
	*
	* @param int $id - category id
	* @param string $listing - listing information by referent
	* @param string $type - listing type key
	*
	**/
	function getListingTitle( $id = false, &$listing, $type = false )
	{
		global $lang;
		
		$fields = $this -> getFormFields( $id, 'listing_titles', $type );
		
		foreach ( $fields as $key => $value )
		{
			if ( empty($fields[$key]['Details_page']) )
			{
				unset($fields[$key]);
			}
			else
			{
				if ( array_key_exists( $fields[$key]['Key'], $listing ) )
				{
					if ( !empty($listing[$fields[$key]['Key']]) )
					{
						$item = $GLOBALS['rlCommon'] -> adaptValue( $value, $listing[$value['Key']], 'listing', $listing['ID'], false, true );
						$title .= $item ? $item .', ' : '';
					}
				}
			}
		}
		
		$title = substr( $title, 0, -2 );
		$title = empty($title) ? 'listing' : $title;
		
		return $title;
	}

	/**
	* delete listing photo
	*
	* @package AJAX
	*
	* @param int $listing_id - listing id
	* @param int $photo_id - photo id
	*
	**/
	function ajaxDeletePhoto( $listing_id, $photo_id )
	{
		global $_response;
		
		$_response -> setCharacterEncoding('UTF-8');
		
		/* get listing info */
		$listing = $this -> getShortDetails( $listing_id );
		
		if ( $listing['Account_ID'] != $_SESSION['id'] && !defined('REALM') )
		{
			return $_response;
		}
		
		/* get listing photos */
		$photo = $this -> fetch( array('Photo', 'Thumbnail', 'Original'), array( 'ID' => $photo_id ), null, null, 'listing_photos', 'row' );
		$sql = "DELETE FROM `" . RL_DBPREFIX . "listing_photos` WHERE `ID` = '{$photo_id}' LIMIT 1";

		if ( $this -> query( $sql ) )
		{
			if ( !empty($photo) )
			{
				unlink( RL_FILES . $photo['Photo'] );
				unlink( RL_FILES . $photo['Thumbnail'] );
				unlink( RL_FILES . $photo['Original'] );
			}
			
			$photos = $this -> fetch( '*', array( 'Listing_ID' => $listing_id ), "ORDER BY `ID`", null, 'listing_photos' );
			
			// rebuild photos block
			$GLOBALS['rlSmarty'] -> assign_by_ref( 'photos', $photos );
			$tpl = 'blocks' . RL_DS . 'photo_block.tpl';
			$_response -> assign( 'photos_dom', 'innerHTML', $GLOBALS['rlSmarty'] -> fetch( $tpl, null, null, false ) );
			
			// rebuild upload section
			// get listing info
			$listing = $this -> getShortDetails( $listing_id, $plan_info = true );
			$GLOBALS['rlSmarty'] -> assign_by_ref( 'listing', $listing );
			
			/* get current listing photos count */
			$photos_count = $this -> getRow( "SELECT COUNT(`ID`) AS `count` FROM `" . RL_DBPREFIX . "listing_photos` WHERE `Listing_ID` = '{$listing_id}'" );
			$GLOBALS['rlSmarty'] -> assign_by_ref( 'photos_count', $photos_count['count'] );
			
			$tpl = 'blocks' . RL_DS . 'photos_upload.tpl';
			$_response -> assign( 'upload_section_dom', 'innerHTML', $GLOBALS['rlSmarty'] -> fetch( $tpl, null, null, false ) );
			
			$listing = $this -> getShortDetails( $listing_id, $plan_info = true );
			
			if ( !$listing['Image_unlim'] )
			{
				$photos_allow = $listing['Plan_image'];
				
				$photos_leave = (int)$photos_allow - (int)$photos_count['count'];
				$photos_leave = str_replace( '{count}', $photos_leave, $GLOBALS['lang']['upload_photo'] . ' (' . $GLOBALS['lang']['photos_leave'] . ')' );
				$_response -> assign( 'fstitle_upload', 'innerHTML', $photos_leave );
			}
			
			$mess = $GLOBALS['lang']['item_deleted'];
			$_response -> script( "$('#notice_obj').fadeOut('fast', function(){ $('#notice_message').html('{$mess}'); $('#notice_obj').fadeIn('slow'); $('#error_obj').fadeOut('fast');});" );
			
			$_response -> script( "$('#gallery a.gallery_item:not(.disabled)').lightBox(); current_field = 2;" );
			if ( $GLOBALS['config']['img_crop_interface'] )
			{
				$_response -> includeScript( RL_TPL_BASE . "js/crop.js" );
			}
			
			if ( defined('REALM') )
			{
				$_response -> call("setPositions");
				$_response -> call("setCropMask");
			}
			
			return $_response;
		}
	}
	
	/**
	* delete file
	*
	* @package xAjax
	*
	* @param string $video_id - video id
	* @param string $dom_id   - dynamic object ID
	*
	**/
	function ajaxDelVideoFile( $video_id, $dom_id )
	{
		global $_response, $account_info, $rlSmarty, $lang, $video_allow;
		
		$video_id = (int)$video_id;
		
		if ( !$video_id )
			return $_response;
		
		if ( defined( 'IS_LOGIN' ) || defined('REALM') )
		{
			$video = $this -> fetch( array('Listing_ID', 'Video', 'Preview'), array('ID' => $video_id), null, 1, 'listing_video', 'row');
			
			$sql = "SELECT `T1`.`Account_ID`, `T2`.`Video`, `T2`.`Video_unlim` FROM `". RL_DBPREFIX ."listings` AS `T1` ";
			$sql .= "LEFT JOIN `". RL_DBPREFIX ."listing_plans` AS `T2` ON `T1`.`Plan_ID` = `T2`.`ID` ";
			$sql .= "WHERE `T1`.`ID` = '{$video['Listing_ID']}' LIMIT 1";
			$listing_info = $this -> getRow($sql);

			if ( $listing_info['Account_ID'] == $account_info['ID'] || defined('REALM') )
			{
				unlink( RL_FILES . $video['Video'] );
				unlink( RL_FILES . $video['Preview'] );

				$this -> query( "DELETE FROM `" . RL_DBPREFIX . "listing_video` WHERE `ID` = '{$video_id}' LIMIT 1" );
				
				$_response -> script( "$('#{$dom_id}').fadeOut()" );
				$_response -> script( "printMessage('notice', '{$lang['item_deleted']}')" );
				
				/* get listing video */
				$this -> setTable('listing_video');
				$videos = $this -> fetch( array('ID', 'Video', 'Preview', 'Type'), array( 'Listing_ID' => $video['Listing_ID'] ) );
				$rlSmarty -> assign_by_ref( 'videos', $videos );
				
				if ( !$listing_info['Video_unlim'] )
				{
					$video_allow++;
				}
				$rlSmarty -> assign_by_ref( 'video_allow', $video_allow );
				
				$tpl = 'blocks' . RL_DS . 'video_upload.tpl';
				$_response -> assign( 'video_upload_dom', 'innerHTML', $rlSmarty -> fetch( $tpl, null, null, false ) );
				
				$_response -> script("flynax.uploadVideoUI()");
				
				if ( count($videos) <= 0 )
				{
					$_response -> script("$('#fs_uploadList').fadeOut()");
				}
			}
		}
		
		return $_response;
	}
	
	/**
	* delete file | ADMIN PANEL
	*
	* @package xAjax
	*
	* @param string $video_id - video id
	*
	**/
	function ajaxDelVideoFileAP( $video_id )
	{
		global $_response, $rlSmarty, $lang, $video_allow;
		
		$video_id = (int)$video_id;
		
		if ( !$video_id )
			return $_response;
	
		$video = $this -> fetch( array('Listing_ID', 'Video', 'Preview', 'Type'), array('ID' => $video_id), null, 1, 'listing_video', 'row');
		
		$sql = "SELECT `T1`.`Account_ID`, `T2`.`Video`, `T2`.`Video_unlim` FROM `". RL_DBPREFIX ."listings` AS `T1` ";
		$sql .= "LEFT JOIN `". RL_DBPREFIX ."listing_plans` AS `T2` ON `T1`.`Plan_ID` = `T2`.`ID` ";
		$sql .= "WHERE `T1`.`ID` = '{$video['Listing_ID']}' LIMIT 1";
		$listing_info = $this -> getRow($sql);

		if ( $video['Type'] == 'local' )
		{
			unlink( RL_FILES . $video['Video'] );
			unlink( RL_FILES . $video['Preview'] );
		}

		$this -> query( "DELETE FROM `" . RL_DBPREFIX . "listing_video` WHERE `ID` = '{$video_id}' LIMIT 1" );
		$_response -> script( "$('#remove_{$video_id}').parent().fadeOut()" );
		
		/* get listing video */
		$this -> setTable('listing_video');
		$videos = $this -> fetch( array('ID'), array( 'Listing_ID' => $video['Listing_ID'] ) );
		
		if ( empty($videos) )
		{
			$_response -> script("
				$('#video_area').html(\"<div class='grey_middle'>{$lang['no_video_uploaded']}</div>\");
			");
		}
		if ( count($videos) < $listing_info['Video'] && !$listing_info['Video_unlim'] )
		{
			$_response -> script("$('#protect').slideDown().prev().slideUp();");
		}
		$_response -> script("printMessage('notice', '{$lang['item_deleted']}');");
		
		return $_response;
	}
	
	/**
	* delete listing file/image
	*
	* @package xAjax
	*
	* @param string $field  - listing field
	* @param string $value  - file/image name
	*
	**/
	function ajaxDeleteListingFile( $field, $value, $dom_id )
	{
		global $_response, $lang;

		$account_id = $_SESSION['id'];
		
		if ( defined( 'IS_LOGIN' ) )
		{
			$field = $this -> rlValid -> xSql( $field );
			$value = $this -> rlValid -> xSql( $value );
			
			$info = $this -> fetch( array('ID', 'Account_ID'), array( $field => $value ), null, 1, 'listings', 'row' );

			if ( $info['Account_ID'] == $account_id )
			{
				unlink( RL_FILES . $value );

				$this -> query( "UPDATE `" . RL_DBPREFIX . "listings` SET `{$field}` = '' WHERE `ID` = '{$info['ID']}' LIMIT 1" );
				
				$_response -> script("
					$('#{$dom_id}').slideUp('normal');
					printMessage('notice', '{$lang['item_deleted']}');
				");
			}
		}
		
		return $_response;
	}
	
	/**
	* tell a friend
	*
	* @package xAjax
	*
	* @param string $friend_name - friend name
	* @param string $friend_email  - friend email
	* @param string $your_name  - your name
	* @param string $your_email  - your email
	* @param string $message  - message
	* @param string $security_code  - security code
	* @param int $listing_id  - listing id
	*
	**/
	function ajaxTellFriend( $friend_name, $friend_email, $your_name = null, $your_email = null, $message, $security_code = false, $listing_id )
	{
		global $_response, $page_info;

		$errors = array();
		$error_fields = '';
		
		/* check required fields */
		if ( empty($friend_name) )
		{
			$errors[] = str_replace( '{field}', '<span class="field_error">"'.$GLOBALS['lang']['friend_name'].'"</span>', $GLOBALS['lang']['notice_field_empty']);
			$error_fields .= '#friend_name,';
		}
		
		if ( empty($friend_email) )
		{
			$errors[] = str_replace( '{field}', '<span class="field_error">"'.$GLOBALS['lang']['friend_email'].'"</span>', $GLOBALS['lang']['notice_field_empty']);
			$error_fields .= '#friend_email,';
		}
		
		if ( !empty($friend_email) && !$GLOBALS['rlValid'] -> isEmail( $friend_email ) )
		{
			$errors[] = $GLOBALS['lang']['notice_bad_email'];
		}
		
		if ( !empty($your_email) && !$GLOBALS['rlValid'] -> isEmail( $your_email ) )
		{
			$errors[] = $GLOBALS['lang']['notice_bad_email'];
		}
		
		if ( $GLOBALS['config']['security_img_tell_friend'] && $security_code != $_SESSION['ses_security_code'] && $_SESSION['ses_security_code'] )
		{
			$errors[] = $GLOBALS['lang']['security_code_incorrect'];
			$error_fields .= '#security_code,';
		}
		
		if (!empty($errors))
		{
			$error_content = '<ul>';
			foreach ($errors as $error)
			{
				$error_content .= "<li>" . $error . "</li>";
			}
			$error_content .= '</ul>';
			
			$error_fields = $error_fields ? substr($error_fields, 0, -1) : '';
			$_response -> script("printMessage('error', '{$error_content}', '{$error_fields}')");
		}
		else 
		{
			$this -> loadClass( 'Mail' );
			
			/* build link */
			$listing_id = (int)$listing_id;
			
			/* get listing info */
			$sql = "SELECT `T1`.*, `T2`.`Path`, `T2`.`Key` AS `Cat_key`, `T3`.`Image` ";
			$sql .= "FROM `" . RL_DBPREFIX . "listings` AS `T1` ";
			$sql .= "LEFT JOIN `" . RL_DBPREFIX . "categories` AS `T2` ON `T1`.`Category_ID` = `T2`.`ID` ";
			$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_plans` AS `T3` ON `T1`.`Plan_ID` = `T3`.`ID` ";
			$sql .= "WHERE `T1`.`ID` = '{$listing_id}' LIMIT 1";
			
			$listing_data = $this -> getRow( $sql );
			
			$listing_title = $this -> getListingTitle( $listing_data['Category_ID'], $listing_data );
			
			$link = $GLOBALS['config']['mod_rewrite'] ? RL_URL_HOME . $page_info['Path'] . '/' . $listing_data['Path'] . '/' . $GLOBALS['rlSmarty'] -> str2path( array('string' => $listing_title) ). '-l' . $listing_data['ID'] . '.html' : RL_URL_HOME . 'index.php?page=' . $page_info['Path'] . '&amp;id=' . $listing_data['ID'] ;
			$link = '<a href="' . $link . '">' . $link . '</a>';

			$mail_tpl = $GLOBALS['rlMail'] -> getEmailTemplate( 'tell_friend' );
			$mail_tpl['body'] = str_replace( array('{friend_name}', '{username}', '{message}', '{link}'), array($friend_name, $your_name, $message, $link), $mail_tpl['body'] );
			
			/* send e-mail for friend */
			$GLOBALS['rlMail'] -> send( $mail_tpl, $friend_email, null, $your_email, $your_name );
			
			$mess = $GLOBALS['lang']['notice_message_sent'];
			$_response -> script("printMessage('notice', '{$mess}')");
			$_response -> script( "$('#friend_name').val('')" );
			$_response -> script( "$('#friend_email').val('')" );
			$_response -> script( "$('#security_code').val('')" );
			$_response -> script( "$('img#security_img').attr('src', '".RL_LIBS_URL."kcaptcha/getImage.php?'+Math.random())" );
		}
		
		$_response -> script( "$('#tf_loading').fadeOut('normal');" );
		
		return $_response;
	}
	
	/**
	* delete listing
	*
	* @package xAjax
	*
	* @param string $id - listing ID
	*
	**/
	function ajaxDeleteListing( $id = false )
	{
		global $_response, $pages, $account_info, $info, $lang, $config, $page_info, $rlHook;
		
		if ( !$id )
			return;
		
		if ( defined( 'IS_LOGIN' ) )
		{
			$info_sql = "SELECT `T1`.`ID`, `T1`.`Category_ID`, `T2`.`Type`, `T1`.`Crossed`, `T1`.`Status`, `T2`.`Type` AS `Listing_type` ";
			$info_sql .= "FROM `" . RL_DBPREFIX . "listings` AS `T1` ";
			$info_sql .= "LEFT JOIN `" . RL_DBPREFIX . "categories` AS `T2` ON `T1`.`Category_ID` = `T2`.`ID` ";
			$info_sql .= "WHERE `T1`.`ID` = '{$id}' AND `T1`.`Account_ID` = '{$account_info['ID']}' AND `T1`.`Status` <> 'trash'";
			
			$info = $this -> getRow( $info_sql );
			
			if ( !empty($info) )
			{
				$rlHook -> load('phpListingsAjaxDeleteListing');
				
				if ( $config['trash'] )
				{
					/* decrease category listing */
					if ( $info['Category_ID'] && $this -> isActive($info['ID']) )
					{
						$GLOBALS['rlActions'] -> delete( array('ID' => $info['ID']), 'listings', $info['ID'], 1 );
						
						$this -> loadClass('Categories');
						$GLOBALS['rlCategories'] -> listingsDecrease($info['Category_ID'], $info['Listing_type']);
			
						/* crossed listings count control */
						if ( $info['Crossed'] )
						{
							$crossed = explode(',', $info['Crossed']);
							foreach ($crossed as $crossed_id)
							{
								$GLOBALS['rlCategories'] -> listingsDecrease($crossed_id);
							}
						}
					}
				}
				else
				{
					$this -> deleteListingData($info['ID'], $info['Category_ID'], $info['Crossed'], $info['Listing_type'] );
					
					$GLOBALS['rlActions'] -> delete( array('ID' => $info['ID']), 'listings', $info['ID'], 1 );
				}

				$exist_sql = "SELECT `T1`.`ID` FROM `" . RL_DBPREFIX . "listings` AS `T1` ";
				$exist_sql .= "LEFT JOIN `" . RL_DBPREFIX . "categories` AS `T2` ON `T1`.`Category_ID` = `T2`.`ID` ";
				$exist_sql .= "WHERE `T1`.`Account_ID` = '{$account_info['ID']}' AND `T1`.`Status` <> 'trash' AND `T2`.`Type` = '{$info['Type']}' ";

				$exist = $this -> getAll( $exist_sql );
				
				if ( empty($exist) )
				{
					$href = $config['mod_rewrite'] ? SEO_BASE . $pages['add_listing'] . '.html' : RL_URL_HOME . '?page=' . $pages['add_listing'] ;
					$replace = preg_replace('/(\[(.+)\])/', '<a href="'.$href.'">$2</a>', $lang['no_listings_here'] );
					$empty_mess = '<div class="info">'.$replace.'</div>';
					$_response -> assign( 'controller_area', 'innerHTML', $empty_mess );
				}
				
				$_response -> script( "$('#listing_{$id}').fadeOut('slow');" );
				$_response -> script("printMessage('notice', '{$lang['notice_listing_deleted']}')");

				/* redirect user to the first page if it was the latest listing on the current page */
				$listings_count = count($exist);
				$pages_count = ceil($listings_count / $config['listings_per_page']);
				
				$_SESSION['ml_deleted']++;
				
				if ( $listings_count <= ($config['listings_per_page'] * $_GET['pg'] ) && $_GET['pg'] > 1 || $_SESSION['ml_deleted'] == $config['listings_per_page'] )
				{
					$url = SEO_BASE;
					$url .= $config['mod_rewrite'] ? $page_info['Path'] .'.html' : '?page='. $page_info['Path'];
					$_response -> redirect($url);
				}
			}
		}

		return $_response;
	}
	
	/**
	* delete all listing data
	*
	* @param int $id - listing id
	* @param int $category_id - category id
	* @param array $crossed - crossed category IDs
	* @param string $type - listing type key
	*
	**/
	function deleteListingData( $id = false, $category_id = false, $crossed = false, $type = false )
	{
		if ( !$id )
			return false;

		/* decrease category listing */
		if ( $category_id && $this -> isActive($id) )
		{		
			$this -> loadClass('Categories');
			$GLOBALS['rlCategories'] -> listingsDecrease($category_id, $type);

			/* crossed listings count control */
			if ( $crossed )
			{
				$crossed = explode(',', $crossed);
				foreach ($crossed as $crossed_id)
				{
					$GLOBALS['rlCategories'] -> listingsDecrease($crossed_id);
				}
			}
		}
		
		/* delete related custom category */
		$this -> query("DELETE FROM `". RL_DBPREFIX ."tmp_categories` WHERE `Listing_ID` = '{$id}' LIMIT 1");
			
		/* get listing photos */
		$photos = $this -> fetch( array('Photo', 'Thumbnail', 'Original'), array( 'Listing_ID' => $id ), null, null, 'listing_photos' );

		$dir = explode('/', $photos[0]['Photo']);
		array_pop($dir);
		$dir = implode(RL_DS, $dir);
		
		/* delete photos files */
		foreach ($photos as $photo)
		{
			unlink( RL_FILES . str_replace('/', RL_DS, $photo['Photo']) );
			unlink( RL_FILES . str_replace('/', RL_DS, $photo['Thumbnail']) );
			unlink( RL_FILES . str_replace('/', RL_DS, $photo['Original']) );
		}
		
        /* remove data from db */
		$this -> query( "DELETE FROM `" . RL_DBPREFIX . "listing_photos` WHERE `Listing_ID` = '{$id}'" );
		
		/* get listing video */
		$videos = $this -> fetch( array('Video', 'Preview'), array( 'Listing_ID' => $id, 'Type' => 'local' ), null, null, 'listing_video' );
		
		/* delete video */
		foreach ( $videos as $video )
		{
			unlink( RL_FILES . str_replace('/', RL_DS, $video['Video']) );
			unlink( RL_FILES . str_replace('/', RL_DS, $video['Preview']) );
		}
		
		rmdir(RL_FILES . $dir);
		$this -> query( "DELETE FROM `" . RL_DBPREFIX . "listing_video` WHERE `Listing_ID` = '{$id}'" );
		
		/* delete listing shows */
		$this -> query( "DELETE FROM `" . RL_DBPREFIX . "listings_shows` WHERE `Listing_ID` = '{$id}'" );
	}
	
	/**
	* get featured listings
	*
	* @param string $type - listing type
	* @param int $limit - listings limit
	* @param string $field - filter field
	* @param string $value - filter value
	*
	* @return array - listings
	**/
	function getFeatured( $type = false, $limit = 10, $field = false, $value = false )
	{
		global $rlValid;
		
		if ( !$type )
		{
			return false;
		}
		
		$sql = "SELECT DISTINCT ";
		$sql .= "`T1`.*, `T4`.`Path`, `T4`.`Parent_ID`, `T4`.`Type` AS `Listing_type` ";
		$sql .= "FROM `" . RL_DBPREFIX . "listings` AS `T1` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_plans` AS `T2` ON `T1`.`Featured_ID` = `T2`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_plans` AS `T3` ON `T1`.`Plan_ID` = `T3`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "categories` AS `T4` ON `T1`.`Category_ID` = `T4`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "accounts` AS `T7` ON `T1`.`Account_ID` = `T7`.`ID` ";
		$sql .= "WHERE ( ";
		$sql .= " TIMESTAMPDIFF(HOUR, `T1`.`Featured_date`, NOW()) <= `T2`.`Listing_period` * 24 "; //round to hour
		$sql .= " OR `T2`.`Listing_period` = 0 ";
		$sql .= ") ";
		$sql .= "AND ( ";
		$sql .= " TIMESTAMPDIFF(HOUR, `T1`.`Pay_date`, NOW()) <= `T3`.`Listing_period` * 24 "; //round to hour
		$sql .= " OR `T3`.`Listing_period` = 0 ";
		$sql .= ") ";
		$sql .= "AND `T4`.`Type` = '{$type}' ";
		$sql .= "AND `T1`.`Status` = 'active' AND `T4`.`Status` = 'active' AND `T7`.`Status` = 'active' ";
		if ( $this -> selectedIDs )
		{
			$sql .= "AND FIND_IN_SET(`T1`.`ID`, '". implode(',', $this -> selectedIDs) ."') = 0 ";
		}
		if ( $field && $value )
		{
			$rlValid -> sql($field);
			$rlValid -> sql($value);
			
			$sql .= "AND `T1`.`{$field}` = '{$value}' ";
		}

		$GLOBALS['rlHook'] -> load('listingsModifyWhereFeatured');

		$sql .= "GROUP BY `T1`.`ID` ORDER BY `Last_show` ASC, RAND() ";
		$sql .= "LIMIT " . $limit;

		$listings = $this -> getAll( $sql );

		if ( empty($listings) )
		{
			return false;
		}

		foreach ( $listings as $key => $value )
		{
			/* get listing IDs */
			$this -> selectedIDs[] = $IDs[] = $value['ID'];
			
			/* populate fields */
			$fields = $this -> getFormFields( $value['Category_ID'], 'featured_form', $type );

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
			
			$listings[$key]['listing_title'] = $this -> getListingTitle( $value['Category_ID'], $value, $type );
		}

		/* save show date */
		if ( $IDs )
		{
			$this -> query("UPDATE `" . RL_DBPREFIX . "listings` SET `Last_show` = NOW() WHERE `ID` = ". implode(" OR `ID` = ", $IDs));
		}

		return $listings;
	}
	
	/**
	* get random listing
	*
	* @param string $type - listing type
	* @param string $mode - single, multi or list
	* @param string $number - number of listings (available in multi or list mode)
	*
	* @return array - listing information
	**/
	function getRandom( $type = false, $mode = 'single', $number = 10 )
	{
		if ( !$type )
		{
			return false;
		}

		$mode = in_array($mode, array('single', 'multi', 'list')) ? $mode : 'single';
		
		$sql = "SELECT DISTINCT `T1`.*, `T4`.`Path` ";
		$sql .= "FROM `" . RL_DBPREFIX . "listings` AS `T1` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_plans` AS `T2` ON `T1`.`Featured_ID` = `T2`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_plans` AS `T3` ON `T1`.`Plan_ID` = `T3`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "categories` AS `T4` ON `T1`.`Category_ID` = `T4`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "accounts` AS `T7` ON `T1`.`Account_ID` = `T7`.`ID` ";
		$sql .= "WHERE ( ";
		$sql .= " TIMESTAMPDIFF(HOUR, `T1`.`Featured_date`, NOW()) <= `T2`.`Listing_period` * 24 "; //round to hour
		$sql .= " OR `T2`.`Listing_period` = 0 ";
		$sql .= ") ";
		$sql .= "AND ( ";
		$sql .= " TIMESTAMPDIFF(HOUR, `T1`.`Pay_date`, NOW()) <= `T3`.`Listing_period` * 24 "; //round to hour
		$sql .= " OR `T3`.`Listing_period` = 0 ";
		$sql .= ") ";
		$sql .= "AND `T4`.`Type` = '{$type}' ";
		$sql .= "AND `T1`.`Status` = 'active' ";
		$sql .= "AND `T1`.`Photos_count` > 0 ";
		$sql .= "AND `T7`.`Status` = 'active' ";
		$sql .= "AND `T4`.`Status` = 'active' ";
		
		if ( $this -> selectedIDs )
		{
			$sql .= "AND `T1`.`ID` NOT IN ('". implode(',', $this -> selectedIDs) ."') ";
		}
		
		$sql .= "GROUP BY `T1`.`ID` ORDER BY `Last_show` ASC, RAND() ";
		if ( $mode == 'single' )
		{
			$sql .= "LIMIT 1";
			$listing = $this -> getRow($sql);
			
			if ( empty($listing) )
				return false;
			
			$this -> selectedIDs[] = $listing['ID'];
			
			$photos = $this -> fetch(array('Photo'), array('Listing_ID' => $listing['ID']), "ORDER BY `Type` DESC, `Position` ASC", null, 'listing_photos');
			if ( $photos )
			{
				foreach ($photos as $photo)
				{
					$listing['Photos'][] = $photo['Photo'];
				}
			}
			
			/* save show date */
			$this -> query("UPDATE `" . RL_DBPREFIX . "listings` SET `Last_show` = NOW() WHERE `ID` = '{$listing['ID']}'");
			
			/* populate fields */
			$fields = $this -> getFormFields( $listing['Category_ID'], 'featured_form', $type );
	
			foreach ( $fields as $fKey => $fValue )
			{
				if ( $first )
				{
					$fields[$fKey]['value'] = $GLOBALS['rlCommon'] -> adaptValue( $fValue, $listing[$fKey], 'listing', $listing['ID'] );
				}
				else
				{
					if ( $field['Condition'] == 'isUrl' || $field['Condition'] == 'isEmail' )
					{
						$fields[$fKey]['value'] = $listing[$item];
					}
					else
					{
						$fields[$fKey]['value'] = $GLOBALS['rlCommon'] -> adaptValue( $fValue, $listing[$fKey], 'listing', $listing['ID'] );
					}
				}
				$first++;
			}
			
			$listing['fields'] = $fields;
			$listing['listing_title'] = $this -> getListingTitle( $listing['Category_ID'], $listing, $type );
			
			return $listing;
		}
		else
		{
			$sql .= "LIMIT {$number}";
			$listings = $this -> getAll($sql);
			
			if ( empty($listings) )
				return false;
				
			foreach ( $listings as $key => $value )
			{
				/* get listing IDs */
				$IDs[] = $value['ID'];
				
				/* populate fields */
				$fields = $this -> getFormFields( $value['Category_ID'], 'featured_form', $type );
	
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
				$listings[$key]['Photo'] = $this -> getOne('Photo', "`Listing_ID` = {$value['ID']} ORDER BY `Type` DESC, `Position` ASC", 'listing_photos');
				
				$listings[$key]['listing_title'] = $this -> getListingTitle( $value['Category_ID'], $value, $type );
			}
	
			/* save show date */
			if ( $IDs )
			{
				$this -> query("UPDATE `" . RL_DBPREFIX . "listings` SET `Last_show` = NOW() WHERE `ID` = ". implode(" OR `ID` = ", $IDs));
			}
			
			return $listings;
		}
	}
	
	/**
	* count listings visits
	*
	* @param int $id - listing ID
	*
	**/
	function countVisit( $id )
	{
		/* define today period */
		$hours = date("G");
		$minutes = date("i");
		$seconds = date("s");

		$today_period = ($hours * 3600) + ($minutes * 60) + $seconds;

		/* get and check current IP address */
		$ip = $_SERVER['REMOTE_ADDR'];
		
		$ip_visit_sql = "SELECT `IP` FROM `" . RL_DBPREFIX . "listings_shows` ";
		$ip_visit_sql .= "WHERE UNIX_TIMESTAMP(DATE_ADD(`Date`, INTERVAL {$today_period} SECOND)) > UNIX_TIMESTAMP(NOW()) ";
		$ip_visit_sql .= "AND `Listing_ID` = '{$id}' AND `IP` = '{$ip}' ";
		$visit_ip = $this -> getRow( $ip_visit_sql );
		
		/* get and check current visitor cookie */
		$cookie_name = 'rl_visited_listings';
		$visit_listings = explode(',', $_COOKIE[$cookie_name]);

		if ( empty($visit_ip) && !in_array( $ip, $visit_listings ) )
		{
			/* save visit */
			$save_ip = array(
				'Listing_ID' => $id,
				'IP' => $ip,
				'Date' => 'NOW()',
				'Account_ID' => defined('IS_LOGIN') ? $_SESSION['id'] : 0
			);
			$this -> loadClass('Actions');
			$GLOBALS['rlActions'] -> insertOne($save_ip, 'listings_shows');
			
			/* save visit in cookie */
			$visit_listings[] = $id;
			$visit_listings = implode(',', $visit_listings);
			
			$expire_time = time()+(86400 - $today_period);

			setcookie( $cookie_name, $visit_listings, $expire_time, '/' );
			
			/* update shows */
			$this -> query("UPDATE `" . RL_DBPREFIX . "listings` SET `Shows` = `Shows` + 1 WHERE `ID` = '{$id}' LIMIT 1");
		}
	}
	
	/**
	* upgrade listing
	*
	* @param int $listing_id - listing ID
	* @param int $plan_id    - plan ID
	* @param int $account_id - account ID
	* @param string $txn_id  - txn ID
	* @param string $dateway - gateway name
	* @param double $total   - total summ
	*
	**/
	function upgradeListing( $listing_id, $plan_id, $account_id, $txn_id, $gateway, $total )
	{
		$this -> loadClass('Actions');
		$this -> loadClass('Categories');
		$this -> loadClass('Mail');
		$this -> loadClass('Cache');
		
		$txn_id = mysql_real_escape_string($txn_id);
		$gateway = mysql_real_escape_string($gateway);
		
		$plan_id = (int)$plan_id;
		$listing_id = (int)$listing_id;

		/* get plan info */
		$sql = "SELECT `T1`.`Type`, `T1`.`Listing_number`, `T1`.`Price`, `T1`.`Featured`, `T1`.`Advanced_mode`, `T1`.`Standard_listings`, `T1`.`Listing_period`, ";
		$sql .= "`T1`.`Featured_listings`, `T1`.`Image`,  `T1`.`Image_unlim`, `T1`.`Limit`, `T2`.`Listings_remains` AS `Using`, `T2`.`ID` AS `Plan_using_ID` ";
		$sql .= "FROM `". RL_DBPREFIX ."listing_plans` AS `T1` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_packages` AS `T2` ON `T1`.`ID` = `T2`.`Plan_ID` AND `T2`.`Account_ID` = '{$account_id}' AND `T2`.`Type` = 'limited' ";
		$sql .= "WHERE `T1`.`ID` = '{$plan_id}' LIMIT 1";
		$plan_info = $this -> getRow($sql);
		
		$listing_info = $this -> fetch( array('Account_ID', 'Category_ID', 'Featured_ID', 'Crossed', 'Last_type', 'Status', 'Photos_count'), array('ID' => $listing_id), null, null, 'listings', 'row' );
		$upgrade_date = 'IF(UNIX_TIMESTAMP(NOW()) > UNIX_TIMESTAMP(DATE_ADD(`Pay_date`, INTERVAL '. $plan_info['Listing_period'] .' DAY)) OR UNIX_TIMESTAMP(`Pay_date`) = 0, NOW(), DATE_ADD(`Pay_date`, INTERVAL '. $plan_info['Listing_period'] .' DAY))';
		$upgrade_fdate = 'IF(UNIX_TIMESTAMP(NOW()) > UNIX_TIMESTAMP(DATE_ADD(`Featured_date`, INTERVAL '. $plan_info['Listing_period'] .' DAY)) OR UNIX_TIMESTAMP(`Featured_date`) = 0, NOW(), DATE_ADD(`Featured_date`, INTERVAL '. $plan_info['Listing_period'] .' DAY))';

		if( $listing_info['Status'] != 'active' )
		{
			$status_update = $GLOBALS['config']['listing_auto_approval'] ? 'active' : 'pending';
		}

		if ( !empty($listing_info) && !empty($plan_info) )
		{
			switch ($plan_info['Type']){
				case 'listing':
					/* update listing data */
					$update = array(
						'fields' => array(
							'Pay_date' => $upgrade_date,
							'Plan_ID' => $plan_id,
							'Cron_notified' => '0'
						),
						'where' => array(
							'ID' => $listing_id
						)
					);
					if( $status_update )
					{
						$update['fields']['Status'] = $status_update;
						$update['fields']['Last_step'] = '';
					}

					if ( $plan_info['Featured'] )
					{
						$update['fields']['Featured_ID'] = $plan_id;
						$update['fields']['Featured_date'] = $upgrade_fdate;
					}
					
					$GLOBALS['rlActions'] -> updateOne($update, 'listings');
					
					/* increase counter */
					if ($GLOBALS['config']['listing_auto_approval'])
					{
						$GLOBALS['rlCategories'] -> listingsIncrease( $listing_info['Category_ID'] );
						
						if ( !empty($listing_info['Crossed']) )
						{
							$crossed_cats = explode(',', trim($listing_info['Crossed'], ','));
							foreach ($crossed_cats as $crossed_cat_id)
							{
								$GLOBALS['rlCategories'] -> listingsIncrease($crossed_cat_id);
							}
						}
					}
	
					/* update/insert limited plan using entry */
					if ( $plan_info['Limit'] > 0 )
					{
						if ( empty($plan_info['Using']) )
						{
							$plan_using_insert = array(
								'Account_ID' => $account_id,
								'Plan_ID' => $plan_id,
								'Listings_remains' => $plan_info['Limit']-1,
								'Type' => 'limited',
								'Date' => 'NOW()',
								'IP' => $_SERVER['REMOTE_ADDR']
							);
							
							$GLOBALS['rlActions'] -> insertOne($plan_using_insert, 'listing_packages');
						}
						else
						{
							$plan_using_update = array(
								'fields' => array(
									'Account_ID' => $account_id,
									'Plan_ID' => $plan_id,
									'Listings_remains' => $plan_info['Using']-1,
									'Type' => 'limited',
									'Date' => 'NOW()',
									'IP' => $_SERVER['REMOTE_ADDR']
								),
								'where' => array(
									'ID' => $plan_info['Plan_using_ID']
								)
							);
							
							$GLOBALS['rlActions'] -> updateOne($plan_using_update, 'listing_packages');
						}
					}
					
					break;
					
				case 'package':
					$update = array(
						'fields' => array(
							'Pay_date' => $upgrade_date,
							'Plan_ID' => $plan_id,
							'Cron_notified' => '0'
						),
						'where' => array(
							'ID' => $listing_id
						)
					);
					
					//if ( $plan_info['Featured'] && $plan_info['Advanced_mode'] && $plan_info['Featured_listings'] && $listing_info['Featured_ID'] )
					if ( $plan_info['Featured'] && (!$plan_info['Advanced_mode'] || ($plan_info['Advanced_mode'] && $_SESSION['add_listing']['listing_type'] == 'featured')) )
					{
						$update['fields']['Featured_ID'] = $plan_id;
						$update['fields']['Featured_date'] = $upgrade_fdate;
					}
					
					if ( $status_update )
					{
						$update['fields']['Status'] = $status_update;
						$update['fields']['Last_step'] = '';
					}

					$GLOBALS['rlActions'] -> updateOne($update, 'listings');
					
					$insert = array(
						'Account_ID' => $account_id,
						'Plan_ID' => $plan_id,
						'Listings_remains' => $plan_info['Listing_number'],
						'Type' => 'package',
						'Date' => 'NOW()',
						'IP' => $_SERVER['REMOTE_ADDR']
					);
					
					if ( $plan_info['Featured'] && $plan_info['Advanced_mode'] && $plan_info['Standard_listings'] )
					{
						$insert['Standard_remains'] = $plan_info['Standard_listings'];
						if ( !$listing_info['Featured_ID'] && $plan_info['Standard_listings'] > 0 )
						{
							$insert['Standard_remains'] = $plan_info['Standard_listings'] - 1;
							$insert['Listings_remains'] = $insert['Listings_remains'] - 1;
						}
					}
					
					if ( $plan_info['Featured'] && $plan_info['Advanced_mode'] && $plan_info['Featured_listings'] )
					{
						$insert['Featured_remains'] = $plan_info['Featured_listings'];
						if ( $listing_info['Featured_ID'] && $plan_info['Featured_listings'] > 0 )
						{
							$insert['Featured_remains'] = $plan_info['Featured_listings'] - 1;
							$insert['Listings_remains'] = $insert['Listings_remains'] - 1;
						}
					}
					
					if( $plan_info['Listing_number'] && !$plan_info['Standard_listings'] && !$plan_info['Featured_listings'] )
					{
						$insert['Listings_remains'] = $insert['Listings_remains'] - 1;
					}
					
					$GLOBALS['rlActions'] -> insertOne($insert, 'listing_packages');
					
					break;
					
				case 'featured':
					$update = array(
						'fields' => array(
							'Featured_ID' => $plan_id,
							'Featured_date' => 'NOW()',
							'Cron_featured' => '0'
						),
						'where' => array(
							'ID' => $listing_id
						)
					);
					$GLOBALS['rlActions'] -> updateOne($update, 'listings');
					
					/* update/insert limited plan using entry */
					if ( $plan_info['Limit'] > 0 )
					{
						if ( empty($plan_info['Using']) )
						{
							$plan_using_insert = array(
								'Account_ID' => $account_id,
								'Plan_ID' => $plan_id,
								'Listings_remains' => $plan_info['Limit']-1,
								'Type' => 'limited',
								'Date' => 'NOW()',
								'IP' => $_SERVER['REMOTE_ADDR']
							);
							
							$GLOBALS['rlActions'] -> insertOne($plan_using_insert, 'listing_packages');
						}
						else
						{
							$plan_using_update = array(
								'fields' => array(
									'Account_ID' => $account_id,
									'Plan_ID' => $plan_id,
									'Listings_remains' => $plan_info['Using']-1,
									'Type' => 'limited',
									'Date' => 'NOW()',
									'IP' => $_SERVER['REMOTE_ADDR']
								),
								'where' => array(
									'ID' => $plan_info['Plan_using_ID']
								)
							);
							
							$GLOBALS['rlActions'] -> updateOne($plan_using_update, 'listing_packages');
						}
					}
					break;
			}

			$GLOBALS['rlHook'] -> load('phpListingsUpgradeListing');

			/* send payment notification email */
			$account_info = $this -> fetch(array('Username', 'First_name', 'Last_name', 'Mail'), array('ID' => $account_id), null, 1, 'accounts', 'row');
			$account_name = $account_info['First_name'] || $account_info['Last_name'] ? $account_info['First_name'] .' '. $account_info['Last_name'] : $account_info['Username'];
			
			$search = array('{username}', '{gateway}', '{txn}', '{item}', '{price}', '{date}');
			$replace = array($account_name, $gateway, $txn_id, $GLOBALS['lang'][$plan_info['Type'].'_plan'], $plan_info['Price'], date(str_replace(array('b', '%'), array('M', ''), RL_DATE_FORMAT)));
			
			$mail_tpl = $GLOBALS['rlMail'] -> getEmailTemplate( 'payment_accepted' );

			$mail_tpl['body'] = str_replace( $search, $replace, $mail_tpl['body'] );
			$GLOBALS['rlMail'] -> send( $mail_tpl, $account_info['Mail'] );
			
			/* send admin notification */
			$mail_tpl = $GLOBALS['rlMail'] -> getEmailTemplate( 'admin_listing_paid' );
			$search = array('{id}', '{username}', '{gateway}', '{txn}', '{item}', '{price}', '{date}');
			$replace = array($listing_id, $account_info['Username'], $gateway, $txn_id, $GLOBALS['lang'][$plan_info['Type'].'_plan'], $plan_info['Price'], date(str_replace(array('b', '%'), array('M', ''), RL_DATE_FORMAT)));

			$mail_tpl['body'] = str_replace( $search, $replace, $mail_tpl['body'] );
			$GLOBALS['rlMail'] -> send( $mail_tpl, $GLOBALS['config']['notifications_email'] );
	
			/* save transaction details */
			$transaction = array(
				'Service' => $plan_info['Type'],
				'Item_ID' => $listing_id,
				'Account_ID' => $account_id,
				'Plan_ID' => $plan_id,
				'Txn_ID' => $txn_id,
				'Total' => $total,
				'Gateway' => $gateway,
				'Date' => 'NOW()'
			);
			$GLOBALS['rlActions'] -> insertOne($transaction, 'transactions');
			
			/* update listing images count if plan allows less photos then previous plan */
			if ( !$plan_info['Image_unlim'] && $plan_info['Image'] < $listing_info['Photos_count'] && $plan_info['Type'] != 'featured' )
			{
				$photos_count_update = array(
					'fields' => array(
						'Photos_count' => $plan_info['Image']
					),
					'where' => array(
						'ID' => $listing_id
					)
				);
				
				$GLOBALS['rlActions'] -> updateOne($photos_count_update, 'listings');
			}
			
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/**
	* upgrade package
	*
	* @param int $package_id - package entry ID
	* @param int $plan_id    - plan ID
	* @param int $account_id - account ID
	* @param string $txn_id  - txn ID
	* @param string $dateway - gateway name
	* @param double $total   - total summ
	*
	**/
	function upgradePackage( $package_id, $plan_id, $account_id, $txn_id, $gateway, $total )
	{
		$this -> loadClass('Actions');
		$this -> loadClass('Categories');
		$this -> loadClass('Mail');
		
		$txn_id = mysql_real_escape_string($txn_id);
		$gateway = mysql_real_escape_string($gateway);
		
		$plan_id = (int)$plan_id;
		$package_id = (int)$package_id;
		
		/* get plan info */
		$plan_info = $this -> fetch( array('Type', 'Listing_number', 'Price', 'Featured', 'Advanced_mode', 'Standard_listings', 'Featured_listings'), array('ID' => $plan_id), null, null, 'listing_plans', 'row' );
		$package_info = $this -> fetch( array('ID'), array('ID' => $package_id), null, null, 'listing_packages', 'row' );
		
		if ( $plan_info && $package_info )
		{
			/* check package exists */
			$package = $this -> fetch(array('ID', 'Listings_remains', 'Standard_remains', 'Featured_remains'), array('ID' => $package_id), null, 1, 'listing_packages', 'row');
			
			if ( empty($package) )
			{
				$insert = array(
					'Account_ID' => $account_id,
					'Plan_ID' => $plan_id,
					'Listings_remains' => $plan_info['Listing_number'],
					'Type' => 'package',
					'Date' => 'NOW()',
					'IP' => $_SERVER['REMOTE_ADDR']
				);
				
				if ( $plan_info['Featured'] && $plan_info['Advanced_mode'] && $plan_info['Standard_listings'] )
				{
					$insert['Standard_remains'] = $plan_info['Standard_listings'];
				}
				
				if ( $plan_info['Featured'] && $plan_info['Advanced_mode'] && $plan_info['Featured_listings'] )
				{
					$insert['Featured_remains'] = $plan_info['Featured_listings'];
				}
				
				$GLOBALS['rlActions'] -> insertOne($insert, 'listing_packages');
			}
			else
			{
				$update = array(
					'fields' => array(
						'Listings_remains' => $package['Listings_remains'] + $plan_info['Listing_number'],
						'Type' => 'package',
						'Date' => 'NOW()',
						'IP' => $_SERVER['REMOTE_ADDR']
					),
					'where' => array(
						'ID' => $package_id
					)
				);
				
				if ( $plan_info['Featured'] && $plan_info['Advanced_mode'] && $plan_info['Standard_listings'] )
				{
					$update['fields']['Standard_remains'] = $package['Standard_remains'] + $plan_info['Standard_listings'];
				}
				
				if ( $plan_info['Featured'] && $plan_info['Advanced_mode'] && $plan_info['Featured_listings'] )
				{
					$update['fields']['Featured_remains'] = $package['Featured_remains'] + $plan_info['Featured_listings'];
				}
				
				$GLOBALS['rlActions'] -> updateOne($update, 'listing_packages');
			}
			
			/* send payment notification email to user */
			$account_info = $this -> fetch(array('Username', 'First_name', 'Last_name', 'Mail'), array('ID' => $account_id), null, 1, 'accounts', 'row');
			$account_name = $account_info['First_name'] || $account_info['Last_name'] ? $account_info['First_name'] .' '. $account_info['Last_name'] : $account_info['Username'];
			
			$search = array('{username}', '{gateway}', '{txn}', '{item}', '{price}', '{date}');
			$replace = array($account_name, $gateway, $txn_id, $GLOBALS['lang'][$plan_info['Type'].'_plan'], $plan_info['Price'], date(str_replace(array('b', '%'), array('M', ''), RL_DATE_FORMAT)));
			
			$mail_tpl = $GLOBALS['rlMail'] -> getEmailTemplate( 'payment_accepted' );

			$mail_tpl['body'] = str_replace( $search, $replace, $mail_tpl['body'] );
			$GLOBALS['rlMail'] -> send( $mail_tpl, $account_info['Mail'] );
			
			/* send admin notification */
			$mail_tpl = $GLOBALS['rlMail'] -> getEmailTemplate( 'admin_listing_paid' );
			$search = array('{id}', '{username}', '{gateway}', '{txn}', '{item}', '{price}', '{date}');
			$replace = array($listing_id, $account_name, $gateway, $txn_id, $GLOBALS['lang'][$plan_info['Type'].'_plan'], $plan_info['Price'], date(str_replace(array('b', '%'), array('M', ''), RL_DATE_FORMAT)));

			$mail_tpl['body'] = str_replace( $search, $replace, $mail_tpl['body'] );
			$GLOBALS['rlMail'] -> send( $mail_tpl, $GLOBALS['config']['notifications_email'] );
	
			/* save transaction details */
			$transaction = array(
				'Service' => $plan_info['Type'],
				'Item_ID' => $package_id,
				'Account_ID' => $account_id,
				'Plan_ID' => $plan_id,
				'Txn_ID' => $txn_id,
				'Total' => $total,
				'Gateway' => $gateway,
				'Date' => 'NOW()'
			);
			$GLOBALS['rlActions'] -> insertOne($transaction, 'transactions');
			
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/**
	* purchase package
	*
	* @param int $id - plan ID
	* @param int $plan_id    - plan ID (duplicate parameter)
	* @param int $account_id - account ID
	* @param string $txn_id  - txn ID
	* @param string $dateway - gateway name
	* @param double $total   - total summ
	* @param bool $free - free plan mode
	*
	**/
	function purchasePackage( $id, $plan_id = false, $account_id, $txn_id, $gateway, $total, $free = false )
	{
		global $account_info, $config, $pages; // pages array will be available for FREE MODE only
		
		$this -> loadClass('Actions');
		$this -> loadClass('Categories');
		$this -> loadClass('Mail');
		$this -> loadClass('Lang');
		
		$txn_id = mysql_real_escape_string($txn_id);
		$gateway = mysql_real_escape_string($gateway);
		
		$plan_id = (int)$id;
		
		/* get plan info */
		$plan_info = $this -> fetch( array('Type', 'Listing_number', 'Price', 'Featured', 'Advanced_mode', 'Standard_listings', 'Featured_listings'), array('ID' => $id), null, null, 'listing_plans', 'row' );
		$plan_info = $this -> rlLang -> replaceLangKeys( $plan_info, 'listing_plans', 'name' );
		
		if ( $plan_info )
		{
			$insert = array(
				'Account_ID' => $account_id,
				'Plan_ID' => $plan_id,
				'Listings_remains' => $plan_info['Listing_number'],
				'Type' => 'package',
				'Date' => 'NOW()',
				'IP' => $_SERVER['REMOTE_ADDR']
			);
			
			if ( $plan_info['Featured'] && $plan_info['Advanced_mode'] && $plan_info['Standard_listings'] )
			{
				$insert['Standard_remains'] = $plan_info['Standard_listings'];
			}
			
			if ( $plan_info['Featured'] && $plan_info['Advanced_mode'] && $plan_info['Featured_listings'] )
			{
				$insert['Featured_remains'] = $plan_info['Featured_listings'];
			}
			
			$GLOBALS['rlActions'] -> insertOne($insert, 'listing_packages');
			
			if ( $free )
			{
				/* send notification letter to the user */
				$mail_tpl = $GLOBALS['rlMail'] -> getEmailTemplate( 'free_package_purchased' );

				$link = SEO_BASE;
				$link .= $config['mod_rewrite'] ? $pages['add_listing'] .'.html' : '?page='. $pages['add_listing'];
				
				$search = array('{plan_name}');
				$replace = array($plan_info['name']);
				$mail_tpl['body'] = str_replace( $search, $replace, $mail_tpl['body'] );
				$mail_tpl['body'] = preg_replace('/\[(.+)\]/', '<a href="'.$link.'">$1</a>', $mail_tpl['body']);
								
				$GLOBALS['rlMail'] -> send( $mail_tpl, $account_info['Mail'] );
			}
			else
			{
				/* send payment notification letter to the user */
				$account_info = $this -> fetch(array('Username', 'First_name', 'Last_name', 'Mail'), array('ID' => $account_id), null, 1, 'accounts', 'row');
				$account_name = $account_info['First_name'] || $account_info['Last_name'] ? $account_info['First_name'] .' '. $account_info['Last_name'] : $account_info['Username'];
				
				$search = array('{username}', '{gateway}', '{txn}', '{item}', '{price}', '{date}');
				$replace = array($account_name, $gateway, $txn_id, $GLOBALS['lang'][$plan_info['Type'].'_plan'], $plan_info['Price'], date(str_replace(array('b', '%'), array('M', ''), RL_DATE_FORMAT)));
				
				$mail_tpl = $GLOBALS['rlMail'] -> getEmailTemplate( 'payment_accepted' );
	
				$mail_tpl['body'] = str_replace( $search, $replace, $mail_tpl['body'] );
				$GLOBALS['rlMail'] -> send( $mail_tpl, $account_info['Mail'] );
				
				/* send admin notification */
				$mail_tpl = $GLOBALS['rlMail'] -> getEmailTemplate( 'admin_listing_paid' );
				$search = array('{id}', '{username}', '{gateway}', '{txn}', '{item}', '{price}', '{date}');
				$replace = array($listing_id, $account_name, $gateway, $txn_id, $GLOBALS['lang'][$plan_info['Type'].'_plan'], $plan_info['Price'], date(str_replace(array('b', '%'), array('M', ''), RL_DATE_FORMAT)));
	
				$mail_tpl['body'] = str_replace( $search, $replace, $mail_tpl['body'] );
				$GLOBALS['rlMail'] -> send( $mail_tpl, $GLOBALS['config']['notifications_email'] );
		
				/* save transaction details */
				$transaction = array(
					'Service' => $plan_info['Type'],
					'Item_ID' => $plan_id,
					'Account_ID' => $account_id,
					'Plan_ID' => $plan_id,
					'Txn_ID' => $txn_id,
					'Total' => $total,
					'Gateway' => $gateway,
					'Date' => 'NOW()'
				);
				$GLOBALS['rlActions'] -> insertOne($transaction, 'transactions');
			}
			
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/**
	* upload video
	* 
	* @param string $type - video type (youtube or local)
	* @param mixed $source - $_FILES in case of local and URL/EMBED in case of youtube
	* @param int $listing_id - related listing ID
	*
	* @return bool
	*
	**/
	function uploadVideo( $type = false, $source = false, $listing_id = false )
	{
		global $rlHook, $l_player_file_types, $lang, $rlActions, $rlValid, $errors, $rlResize, $rlCrop, $config;
		
		if ( !$type || !$listing_id )
			return false;
		
		/* file directories handler */
		$dir = RL_FILES . date('m-Y') . RL_DS .'ad'. $listing_id . RL_DS;
		$dir_name = date('m-Y') .'/ad'. $listing_id .'/';
		$url = RL_FILES_URL . $dir_name;
		$this -> rlMkdir($dir);
		
		$listing_id = (int)$listing_id;
		
		$possition = $this -> getRow("SELECT MAX(`Position`) AS `Position` FROM `". RL_DBPREFIX ."listing_video` WHERE `Listing_ID` = '{$listing_id}'");
		$possition = $possition['Position'] + 1;
		
		switch ($type){
			case 'local':
				$video_tmp = $source['video'];
				$preview_tmp = $source['preview'];
				
				$rlHook -> load('addVideoUpload');
				
				/* check video file format */
				$video_file_ext = array_reverse(explode('.', $video_tmp['name']));
				$video_file_ext = strtolower($video_file_ext[0]);

				if ( empty($video_tmp['tmp_name'] ) )
				{
					$errors[] = str_replace( '{field}', '<span class="field_error">"'. $lang['file'] .'"</span>', $lang['notice_field_empty']);
				}

				if ( !empty($video_tmp['tmp_name'] ) && !array_key_exists( $video_file_ext, $l_player_file_types ) )
				{
					$errors[] = str_replace( array( '{field}', '{ext}' ), array( '<span class="field_error">"'.$lang['file'].'"</span>', '<span class="field_error">"'.$video_file_ext.'"</span>' ), $lang['notice_bad_file_ext']);
				}
				
				/* check preview file format */
				$preview_file_ext = array_reverse(explode('.', $preview_tmp['name']));
				$preview_file_ext = $preview_file_ext[0];

				if ( empty($preview_tmp['tmp_name'] ) )
				{
					$errors[] = str_replace( '{field}', '<span class="field_error">"'. $lang['preview_image'] .'"</span>', $lang['notice_field_empty']);
				}

				if ( !$rlValid -> isImage( $preview_file_ext ) && !empty($preview_tmp['tmp_name'] ) )
				{
					$errors[] = str_replace( array( '{field}', '{ext}' ), array( '<span class="field_error">"'.$lang['preview_image'].'"</span>', '<span class="field_error">"'.$preview_file_ext.'"</span>' ), $lang['notice_bad_file_ext']);
				}

				/* move tmp files and insert video entry to DB */
				if ( empty($errors) )
				{
					$file_name = 'video' . '_' . time() . mt_rand() . '.' . $video_file_ext;
					$file_location = $dir . $file_name;
					
					$thumbnail_name = 'preview_' . time() . mt_rand() . '.' . $preview_file_ext;
					$thumbnail_location = $dir . $thumbnail_name;
					
					/* move preview file */
					if( move_uploaded_file( $video_tmp['tmp_name'], $file_location ) )
					{
						if ( move_uploaded_file( $preview_tmp['tmp_name'], $thumbnail_location ) )
						{
							$rlCrop -> loadImage($thumbnail_location);
							$rlCrop -> cropBySize(120, 90, ccCENTER);
							$rlCrop -> saveImage($thumbnail_location, $config['img_quality']);
							$rlCrop -> flushImages();
							
							$rlResize -> resize( $thumbnail_location, $thumbnail_location, 'C', array(120, 90), true, false );
						}
						
						if ( is_readable($thumbnail_location) && is_readable($file_location) )
						{
							$preview_info = array(
								'Listing_ID' => $listing_id,
								'Position' => $possition,
								'Video' => $dir_name . $file_name,
								'Preview' => $dir_name . $thumbnail_name,
								'Type' => $type
							);
							
							$success = $rlActions -> insertOne( $preview_info, 'listing_video' );
						}
						else
						{
							$GLOBALS['rlDebug'] -> logger("Can't upload video file or resize preview image.");
							$errors[] = $lang['error_video_upload_fail'];
						}
					}
				}
				
				break;
				
			case 'youtube':
				if ( empty($source) )
				{
					$errors[] = str_replace( '{field}', '<span class="field_error">"'.$lang['link_or_embed'].'"</span>', $lang['notice_field_empty']);
				}
				
				if ( !$errors )
				{
					/* parse video key from url */
					if ( 0 === strpos($source, 'http') )
					{
						/* parse from short link */
						if ( false !== strpos($source, 'youtu.be') )
						{
							$matches[1] = array_pop(explode('/', $source));
						}
						else
						{
							preg_match('/v=([\w-_]*)/', $source, $matches);
						}
					}
					/* parse video key from tags */
					else
					{
						preg_match('/src=".*v\/(.*)\?.*"/', $source, $matches);
						
						if ( !$matches[1] )
						{
							preg_match('/src=".*embed\/([\w\-]*)"/', $source, $matches);
						}
					}
					
					if ( $matches[1] )
					{
						$insert = array(
							'Listing_ID' => $listing_id,
							'Preview' => $matches[1],
							'Position' => $possition,
							'Type' => $type
						);
						
						$success = $rlActions -> insertOne($insert, 'listing_video');
					}
					else
					{
						$errors[] = $lang['unable_parse_video_key'];
					}
				}
				
				break;
		}
		
		return $success;
	}

	/**
	* get single listing by ID
	* 
	* @param int $id - listing ID
	* @param bool $listing_title - include listing title 
	* @param bool $fields - include short form fields
	*
	* @return array - listing data
	*
	**/
	function getListing( $id = false, $listing_title = false, $fields = false )
	{
		global $lang, $config, $rlListingType, $rlSmarty, $pages;
		
		if ( !$id )
			return false;
			
		$sql = "SELECT DISTINCT `T1`.*, `T1`.`Main_photo` AS `Photo`, ";
		$sql .= "`T4`.`Path` AS `Category_path`, `T4`.`Type` AS `Listing_type`, ";
		$sql .= "IF(TIMESTAMPDIFF(HOUR, `T1`.`Featured_date`, NOW()) <= `T2`.`Listing_period` * 24 OR `T2`.`Listing_period` = 0, '1', '0') AS `Featured_status`, ";
		$sql .= "IF((TIMESTAMPDIFF(HOUR, `T1`.`Pay_date`, NOW()) <= `T3`.`Listing_period` * 24 OR `T3`.`Listing_period` = 0) ";
		$sql .= "AND `T1`.`Status` = 'active' AND `T3`.`Status` = 'active' AND `T4`.`Status` = 'active' AND `T7`.`Status` = 'active', 1, 0) AS `Active_status` ";
		$sql .= "FROM `" . RL_DBPREFIX . "listings` AS `T1` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_plans` AS `T2` ON `T1`.`Featured_ID` = `T2`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_plans` AS `T3` ON `T1`.`Plan_ID` = `T3`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "categories` AS `T4` ON `T1`.`Category_ID` = `T4`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "accounts` AS `T7` ON `T1`.`Account_ID` = `T7`.`ID` ";
		$sql .= "WHERE `T1`.`ID` = '{$id}' GROUP BY `T1`.`ID` LIMIT 1 ";
		
		$listing = $this -> getRow($sql);
		
		if ( !$listing || !$listing['ID'] )
			return false;
		
		$listing_type = $rlListingType -> types[$listing['Listing_type']];
		
		if ( $listing_title )
		{
			$listing['listing_title'] = $this -> getListingTitle($listing['Category_ID'], $listing, $listing['Listing_type']);
			
			$listing['listing_link'] = SEO_BASE;
			$listing['listing_link'] = $config['mod_rewrite'] ? $pages[$listing_type['Page_key']] .'/'. $listing['Category_path'] .'/'. $rlSmarty -> str2path($listing['listing_title']) .'-'. $listing['ID'] .'.html' : '?page='. $pages[$listing_type['Page_key']] .'&amp;id='. $listing['ID'];
		}
		
		if ( $fields )
		{
			/* populate fields */
			$fields = $this -> getFormFields( $listing['Category_ID'], 'short_forms', $listing['Listing_type'] );
	
			foreach ( $fields as $fKey => $fValue )
			{
				if ( $first )
				{
					$fields[$fKey]['value'] = $GLOBALS['rlCommon'] -> adaptValue( $fValue, $listing[$fKey], 'listing', $listing['ID'] );
				}
				else
				{
					if ( $field['Condition'] == 'isUrl' || $field['Condition'] == 'isEmail' )
					{
						$fields[$fKey]['value'] = $listing[$item];
					}
					else
					{
						$fields[$fKey]['value'] = $GLOBALS['rlCommon'] -> adaptValue( $fValue, $listing[$fKey], 'listing', $listing['ID'] );
					}
				}
				$first++;
			}
			
			$listing['fields'] = $fields;
		}
		
		return $listing;
	}
	
	/**
	* build featured listing boxes
	*
	* @todo - get featured listings by listing types and assign them to related boxes
	*
	**/
	function buildFeaturedBoxes( $listing_type_key = false )
	{
		global $rlMobile, $rlListingTypes, $rlSmarty, $blocks, $config, $page_info;
		
		if ( $rlMobile -> isMobile && $config['mobile_version_module'] )
		{
			$this -> setTable('blocks');
			$mobile_featured_blocks = $this -> fetch(array('Key'), null, "WHERE `Key` RLIKE 'ltfb_(.*)[^_box0-9]$'");
			$this -> resetTable();
			$mobile_featured = array();
			
			if ( $mobile_featured_blocks )
			{
				$mobile_featured_count = $config['mobile_featured_number'];
				
				foreach ($mobile_featured_blocks as $mobile_featured_block)
				{
					if ( $mobile_featured_count <= 0 )
						break;
						
					$f_type = str_replace('ltfb_', '', $mobile_featured_block['Key']);
					
					if ( $rlListingTypes -> types[$f_type]['Photo'] )
					{
						if ( $featured = $this -> getFeatured( $f_type, $mobile_featured_count) )
						{
							$mobile_featured = $mobile_featured ? array_merge($mobile_featured, $featured) : $featured;
							unset($featured);
						
							$mobile_featured_count -= count($mobile_featured);
						}
					}
				}
				
				$rlSmarty -> assign_by_ref('mobile_featured', $mobile_featured);
			}
		}
		else
		{
			$requested_type = $rlListingTypes -> types[$listing_type_key];
			
			/* get random listing */
			if ( !isset($_GET['nvar_1']) && $page_info['Controller'] == 'listing_type' && $requested_type['Random_featured'] && ($requested_type['Photo'] || $requested_type['Random_featured_type'] == 'list') )
			{
				$random_featured = $this -> getRandom($listing_type_key, $requested_type['Random_featured_type'], $requested_type['Random_featured_number']);
				$rlSmarty -> assign_by_ref('random_featured', $random_featured);
			}
			
			/* generate featured listing blocks data */
			foreach ($blocks as $key => $value)
			{
				if ( strpos($key, 'ltfb_') === 0 )
				{
					/* get field/value */
					preg_match("/field\='(\w+)'\s+value='(\w+)'/", $value['Content'], $matches);
					
					if ($matches[1] && $matches[2])
					/* splitted mode */
					{
						$field = $matches[1];
						$value = $matches[2];
						
						$f_type = explode('_', $key);
						$f_type = $f_type[1];
						$f_type_var = 'featured_'. $f_type .'_'. $value;
						
						$$f_type_var = $this -> getFeatured( $f_type, $config['featured_per_page'], $field, $value);
						$rlSmarty -> assign_by_ref( $f_type_var, $$f_type_var );
					}
					else
					/* single/default mode */
					{
						$f_type = str_replace('ltfb_', '', $key);
						$f_type_var = 'featured_'. $f_type;
						
						$$f_type_var = $this -> getFeatured( $f_type, $config['featured_per_page']);
						$rlSmarty -> assign_by_ref( $f_type_var, $$f_type_var );
					}
				}
			}
		}
	}

	/**
	* replaces fields in the tpl with actual values for meta data of listing details page
	*
	* @param array $category_id - listing category id
	* @param array $listing_data   - listing data
	* @param array $type - keywords or description
	*
	**/
	function replaceMetaFields( $category_id = false, $listing_data = false, $type = 'description' )
	{
		$cat_info = $this -> fetch( array('Key', 'Parent_ID'), array('ID' => $category_id), null, null, 'categories', 'row' );

		if( $tpl = $GLOBALS['lang']['categories+listing_meta_'.$type.'+'.$cat_info['Key']] )
		{
			preg_match_all('/\{([^\{]+)\}+/', $tpl, $fields);
			$fields_info_tmp = $this -> fetch( "*", array('Status' => 'active'), "AND FIND_IN_SET(`Key`, '". implode(",", $fields[1]) ."')", null, 'listing_fields' ); 
			foreach($fields_info_tmp as $key => $field){
				$fields_info[ $field['Key'] ] = $field;
			}
			unset($fields_info_tmp);
			
			foreach( $fields[1] as $key => $field_key )
			{
				$replacement[$key] = $field_key == 'ID' ? $listing_data[$field_key] : $GLOBALS['rlCommon'] -> adaptValue( $fields_info[$field_key], $listing_data[$field_key] );
				$pattern[$key] = $fields[0][$key];
			}
			$tpl = str_replace( $pattern, $replacement, $tpl );

			return $tpl;
		}
		elseif( $cat_info['Parent_ID'] && ($GLOBALS['rlListingTypes'] -> types[ $listing_data['Cat_type'] ]['Cat_general_cat'] != $category_id) )
		{
			return $this -> replaceMetaFields( $cat_info['Parent_ID'], $listing_data, $type );
		}
		elseif( $GLOBALS['rlListingTypes'] -> types[ $listing_data['Cat_type'] ]['Cat_general_cat'] )
		{
			if( $category_id == $GLOBALS['rlListingTypes'] -> types[ $listing_data['Cat_type'] ]['Cat_general_cat'])
			{
				return false;
			}
			return $this -> replaceMetaFields( $GLOBALS['rlListingTypes'] -> types[ $listing_data['Cat_type'] ]['Cat_general_cat'], $listing_data, $type );
		}

		return false;
	}
	
	/**
	* update listing main photo and total photos count
	*
	* @param array $id - listing ID
	*
	**/
	function updatePhotoData( $id = false )
	{
		if ( !$id )
			return false;
			
		$sql = "SELECT DISTINCT SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT `T2`.`Thumbnail` ORDER BY `T2`.`Type` DESC, `T2`.`Position` ASC), ',', 1) AS `Main_photo`, ";
		$sql .= "COUNT(`T2`.`Thumbnail`) AS `Photos_count` ";
		$sql .= "FROM `" . RL_DBPREFIX . "listings` AS `T1` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_photos` AS `T2` ON `T1`.`ID` = `T2`.`Listing_ID` ";
		$sql .= "WHERE `T1`.`ID` = {$id} LIMIT 1";
		
		if ( $listing = $this -> getRow($sql) )
		{
			$update_sql = "UPDATE `". RL_DBPREFIX ."listings` SET `Main_photo` = '{$listing['Main_photo']}', `Photos_count` = '{$listing['Photos_count']}' ";
			$update_sql .= "WHERE `ID` = {$id} LIMIT 1";
			$this -> query($update_sql);
		}
	}
}
