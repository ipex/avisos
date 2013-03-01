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
	* @var calculate items
	**/
	var $calc;
	
	/**
	* @var listing fields list (view listing details mode)
	**/
	var $fieldsList;
	
	/**
	* class constructor
	**/
	function rlListings()
	{
		global $rlLang, $rlValid, $rlConfig, $rlCommon, $rlNotice;
		
		$this -> rlLang  = & $rlLang;
		$this -> rlValid = & $rlValid;
		$this -> rlConfig = & $rlConfig;
		$this -> rlCommon = & $rlCommon;
		$this -> rlNotice = & $rlNotice;
	}
	
	/**
	* create listing
	*
	* @param array $info  - general information
	* @param array $data   - listing data
	* @param array $fields - current listing kind fields
	*
	**/
	function create( $info, $data, $fields )
	{
		global $category;
		
		$listing = array(
			'Category_ID' => $category['ID'],
			'Account_ID' => $info['account_id'],
			'Plan_ID' => $data['l_plan'],
			'Date' => 'NOW()',
			'Pay_date' => "NOW()",
			'Status' => $info['status'],
			'Crossed' => $info['crossed']
		);

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
						$location[] = $GLOBALS['rlCommon'] -> adaptValue($value, $data[$fk]);
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
	
							$file_ext = explode( '.', $_FILES[$fk]['name'] );
							$file_ext = array_reverse( $file_ext );
							$file_ext = $file_ext[0];
							
							$tmp_location = RL_UPLOAD . 'listing_' . $fk . '_' . time() . mt_rand() . '.' . $file_ext;
							
							if( move_uploaded_file($_FILES[$fk]['tmp_name'], $tmp_location ))
							{
								chmod( $tmp_location, 0777 );
	
								$file_name = 'listing_' . $fk . '_' . time() . mt_rand() . '.' . $file_ext;
								$file = RL_FILES . $file_name;
								
								// get new image resize params
								$resize_type = $fields[$key]['Default'];
								
								$resolution  = strtoupper($resize_type) == 'C' ? explode('|', $fields[$key]['Values']) : $fields[$key]['Values'] ;
		
								if ( !empty($resolution) )
								{
									$GLOBALS['rlResize'] -> resize( $tmp_location, $file, $resize_type, $resolution );
								}
								else
								{
									copy( $tmp_location, $file );
								}
								
								if ( is_readable( $file ) )
								{
									$listing[$fk] = $file_name;
								}
								
								chmod( $file, 0644 );
								unlink( $tmp_location );
							}
							
							break;
						
						case 'file':
	
							$file_ext = explode( '.', $_FILES[$fk]['name'] );
							$file_ext = array_reverse( $file_ext );
							$file_ext = $file_ext[0];
							
							$tmp_location = RL_UPLOAD . 'listing_' . $fk . '_' . time() . mt_rand() . '.' . $file_ext;
							
							if( move_uploaded_file($_FILES[$fk]['tmp_name'], $tmp_location ))
							{
								chmod( $tmp_location, 0777 );
	
								$file_name = 'listing_' . $fk . '_' . time() . mt_rand() . '.' . $file_ext;
								$file = RL_FILES . $file_name;
		
								if ( copy( $tmp_location, $file ) )
								{
									if ( is_readable( $file ) )
									{
										$listing[$fk] = $file_name;
									}
									
									chmod( $file, 0644 );
									unlink( $tmp_location );
								}
							}
							
							break;
					}
				}
			}
			
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

			return $GLOBALS['rlActions'] -> insertOne( $listing, 'listings', $html_fields );
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
	* @param array $info  - general information
	* @param array $data   - listing data
	* @param array $fields - current listing kind fields
	*
	**/
	function edit( $info, $data, $fields )
	{
		global $rlCommon;
		
		$listing['where'] = array(
			'ID' => $info['id']
		);

		$listing['fields']['Account_ID'] = $info['Account_ID'];
		$listing['fields']['Status'] = $info['Status'];
		$listing['fields']['Plan_ID'] = $info['Plan_ID'];
		$listing['fields']['Crossed'] = $info['crossed'];	

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
						
						case 'price':
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
						
						case 'checkbox':
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
							$file_ext = explode( '.', $_FILES[$fk]['name'] );
							$file_ext = array_reverse( $file_ext );
							$file_ext = $file_ext[0];
							
							$tmp_location = RL_UPLOAD . 'listing_' . $fk . '_' . time() . mt_rand() . '.' . $file_ext;
							
							if( move_uploaded_file($_FILES[$fk]['tmp_name'], $tmp_location ))
							{
								chmod( $tmp_location, 0777 );
	
								$file_name = 'listing_' . $fk . '_' . time() . mt_rand() . '.' . $file_ext;
								$file = RL_FILES . $file_name;
								
								// get new image resize params
								$resize_type = $fields[$key]['Default'];
								
								$resolution  = strtoupper($resize_type) == 'C' ? explode('|', $fields[$key]['Values']) : $fields[$key]['Values'] ;
		
								if ( !empty($resolution) )
								{
									$GLOBALS['rlResize'] -> resize( $tmp_location, $file, $resize_type, $resolution );
								}
								else
								{
									copy( $tmp_location, $file );
								}
								
								if ( is_readable( $file ) )
								{
									$listing['fields'][$fk] = $file_name;
									unlink( RL_FILES . $data[$fk] );
								}
								
								chmod( $file, 0644 );
								unlink( $tmp_location );
							}
							
							break;
						
						case 'file':
							$file_ext = explode( '.', $_FILES[$fk]['name'] );
							$file_ext = array_reverse( $file_ext );
							$file_ext = $file_ext[0];
							
							$tmp_location = RL_UPLOAD . 'listing_' . $fk . '_' . time() . mt_rand() . '.' . $file_ext;
							
							if( move_uploaded_file($_FILES[$fk]['tmp_name'], $tmp_location ))
							{
								chmod( $tmp_location, 0777 );
	
								$file_name = 'listing_' . $fk . '_' . time() . mt_rand() . '.' . $file_ext;
								$file = RL_FILES . $file_name;
		
								if ( copy( $tmp_location, $file ) )
								{
									if ( is_readable( $file ) )
									{
										$listing['fields'][$fk] = $file_name;
										unlink( RL_FILES . $data[$fk] );
									}
									
									chmod( $file, 0644 );
									unlink( $tmp_location );
								}
							}
							
							break;
					}
				}
			}

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
						$title .= $GLOBALS['rlCommon'] -> adaptValue( $value, $listing[$value['Key']], 'listing', $listing['ID'], false, true ) . ', ';
					}
				}
			}
		}
		
		$title = substr( $title, 0, -2 );
		$title = empty($title) ? 'listing' : $title;
		
		return $title;
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
	* get parent category fields
	*
	* @param int $id - category id
	* @param string $table - table
	*
	* @return categories fields list
	**/
	function getParentCatFields( $id, $table )
	{
		$sql = "SELECT `T2`.`Key`, `T2`.`Type`, `T2`.`Default`, `T2`.`Condition`, `T2`.`Details_page` FROM `" . RL_DBPREFIX . $table . "` AS `T1` ";
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
	* delete listing
	*
	* @package xAjax
	*
	* @param string $id  - listing field
	* @param string $reason  - reason message
	*
	**/
	function ajaxDeleteListing( $id, $reason = false )
	{
		global $_response, $config, $lang, $pages, $listing;
		
		if ( !$id )
			return;
			
		$id = (int)$id;

		$sql = "SELECT `T1`.*, `T2`.`Type` AS `Listing_type` ";
		$sql .= "FROM `". RL_DBPREFIX ."listings` AS `T1` ";
		$sql .= "LEFT JOIN `". RL_DBPREFIX ."categories` AS `T2` ON `T1`.`Category_ID` = `T2`.`ID` ";
		$sql .= "WHERE `T1`.`ID` = {$id}";
		$listing = $this -> getRow($sql);

		$account_info = $GLOBALS['rlAccount'] -> getProfile((int)$listing['Account_ID']);
		
		/* Decrease listing count in category */
		if ( $listing['Status'] == 'active' )
		{
			$this -> loadClass('Categories');
			$GLOBALS['rlCategories'] -> listingsDecrease($listing['Category_ID'], $listing['Listing_type']);
			
			if ( !empty($listing['Crossed']) )
			{
				$crossed_cats = explode(',', trim($listing['Crossed'], ','));
				foreach ($crossed_cats as $crossed_cat_id)
				{
					$GLOBALS['rlCategories'] -> listingsDecrease($crossed_cat_id);
				}
			}
		}

		$GLOBALS['rlHook'] -> load('apPhpListingsAjaxDeleteListing');
		
		$GLOBALS['rlActions'] -> delete( array('ID' => $id), 'listings', $id, 1 );
	
		if ( !$config['trash'] )
		{
			$this -> deleteListingData($id);
		}

		$del_action = $GLOBALS['rlActions'] -> action;
		
		/* send notification to the owner */
		$this -> loadClass('Mail');
		$mail_tpl = $GLOBALS['rlMail'] -> getEmailTemplate('listing_removed_by_admin');
		
		$listing_title = $this -> getListingTitle($listing['Category_ID'], $listing, $listing['Listing_type']);
		$link = RL_URL_HOME;
		$link .= $config['mod_rewrite'] ? $pages['contact_us'] .'.html' : 'index.php?page='. $pages['contact_us'];
				
		$find = array(
			'{username}',
			'{listing_title}',
			'{reason}'
		);
		$replace = array(
			$account_info['Full_name'],
			$listing_title,
			$reason ? $reason : $lang['no_reason_specified']
		);
		
		$mail_tpl['body'] = str_replace($find, $replace, $mail_tpl['body']);
		$mail_tpl['body'] = preg_replace('/\[(.*)\]/', '<a href="'. $link .'">$1</a>', $mail_tpl['body']);
		
		$GLOBALS['rlMail'] -> send($mail_tpl, $account_info['Mail']);

		$_response -> script("printMessage('notice', '{$lang['mass_listings_'.$del_action]}');");

		$_response -> script( "listingsGrid.reload();" );

		return $_response;
	}
	
	/**
	* delete all listing data
	*
	* @param int $id - listing id
	**/
	function deleteListingData( $id )
	{
		/* get listing photos */
		$photos = $this -> fetch( array('Photo', 'Thumbnail', 'Original'), array( 'Listing_ID' => $id ), null, null, 'listing_photos' );
		
		if ( $photos )
		{
			$dir_item = explode('/', $photos[0]['Photo']);
			array_pop($dir_item);
			$dir = count($dir_item) ? implode(RL_DS, $dir_item) : false;
			
			/* delete photos/video one by one, related to the listing | < v4.0 */
			foreach ($photos as $pKey => $pValue)
			{
				unlink( RL_FILES . $photos[$pKey]['Photo'] );
				unlink( RL_FILES . $photos[$pKey]['Thumbnail'] );
				unlink( RL_FILES . $photos[$pKey]['Original'] );
			}
		}

		/* get listing video */
		$videos = $this -> fetch( array('Video', 'Preview'), array( 'Listing_ID' => $id, 'Type' => 'local' ), null, null, 'listing_video');
		unlink( RL_FILES . $videos['Video'] );
		unlink( RL_FILES . $videos['Preview'] );
		
		if ( !$dir && $videos )
		{
			$dir_item = explode('/', $videos[0]['Photo']);
			array_pop($dir_item);
			$dir = count($dir_item) ? implode(RL_DS, $dir_item) : false;
		}
		
		/* delete whole directory related to the listing | > v4.0 */
		if ( $dir )
		{
			$this -> deleteDirectory(RL_FILES . $dir . RL_DS);
		}
		
		$this -> query( "DELETE FROM `" . RL_DBPREFIX . "listing_photos` WHERE `Listing_ID` = '{$id}'" );
		$this -> query( "DELETE FROM `" . RL_DBPREFIX . "listing_video` WHERE `Listing_ID` = '{$id}'" );
	}
	
	/**
	* mass actions with listings
	*
	* @package xAjax
	*
	* @param string $ids     - listings ids
	* @param string $action  - mass action
	*
	**/
	function ajaxMassActions( $ids = false, $action = false )
	{
		global $_response, $rlSmarty, $config, $lang, $pages, $rlListingTypes;

		if ( !$ids || !$action )
			return $_response;
		
		$ids = explode('|', $ids);
		$this -> loadClass('Mail');
		
		/* inform listing owner about status changing */
		$mail_tpl = $GLOBALS['rlMail'] -> getEmailTemplate( $action == 'activate' ? 'listing_activated' : 'listing_deactivated' );
		$this -> loadClass('Categories');
		$this -> loadClass('Account');

		$this -> loadClass('Notice');
		if ( in_array($action, array('activate', 'approve')) )
		{
			foreach ($ids as $id)
			{
				$mail_tpl_copy = $mail_tpl;
				
				/* get listing info */
				$sql = "SELECT `T1`.*, UNIX_TIMESTAMP(`T1`.`Pay_date`) AS `Payed`, `T2`.`Username`, `T2`.`Mail`, `T3`.`Type` AS `Listing_type`, `T3`.`Path` AS `Category_path` ";
				$sql .= "FROM `".RL_DBPREFIX."listings` AS `T1` ";
				$sql .= "RIGHT JOIN `".RL_DBPREFIX."accounts` AS `T2` ON `T1`.`Account_ID` = `T2`.`ID` ";
				$sql .= "LEFT JOIN `".RL_DBPREFIX."categories` AS `T3` ON `T1`.`Category_ID` = `T3`.`ID` ";
				$sql .= "WHERE `T1`.`ID` = '{$id}'";
				
				$listing_info = $this -> getRow($sql);
				$set_status = $action == 'activate' ? 'active' : 'approval';
	
				/* get account info */
				$owner_info = $GLOBALS['rlAccount'] -> getProfile((int)$listing_info['Account_ID']);
				
				if ( $listing_info['Status'] == $set_status )
					continue;
				
				$listing_type = $rlListingTypes -> types[$listing_info['Listing_type']];
				
				/* generate link */
				if ($action == 'activate')
				{
					/* increase listings counter */
					if (!empty($listing_info['Payed']))
					{
						$GLOBALS['rlCategories'] -> listingsIncrease($listing_info['Category_ID']);
						if ( !empty($listing_info['Crossed']) )
						{
							$crossed_cats = explode(',', trim($listing_info['Crossed'], ','));
							foreach ($crossed_cats as $crossed_cat_id)
							{
								$GLOBALS['rlCategories'] -> listingsIncrease($crossed_cat_id);
							}
						}
					}
					
					$listing_title = $this -> getListingTitle($listing_info['Category_ID'], $listing_info, $listing_type['Key']);
					
					$link = RL_URL_HOME;
					$link .= $config['mod_rewrite'] ? $pages[$listing_type['Page_key']] .'/'. $listing_info['Category_path'] .'/'. $rlSmarty -> str2path($listing_title) .'-'.$id.'.html' : 'index.php?page='. $pages[$listing_type['Page_key']] .'&amp;id='.$id ;
				}
				else
				{
					/* deincrease listings counter */
					if (!empty($listing_info['Payed']))
					{
						$GLOBALS['rlCategories'] -> listingsDecrease($listing_info['Category_ID']);
						if ( !empty($listing_info['Crossed']) )
						{
							$crossed_cats = explode(',', trim($listing_info['Crossed'], ','));
							foreach ($crossed_cats as $crossed_cat_id)
							{
								$GLOBALS['rlCategories'] -> listingsDecrease($crossed_cat_id);
							}
						}
					}
				}
				
				$sql = "UPDATE `".RL_DBPREFIX."listings` SET `Status` = '{$set_status}' WHERE `ID` = '{$listing_info['ID']}'";
				$success = $this -> query($sql);

				$mail_tpl_copy['body'] = str_replace( array('{username}', '{link}'), array($owner_info['Full_name'], '<a href="'.$link.'">'.$link.'</a>'), $mail_tpl_copy['body'] );
				$GLOBALS['rlMail'] -> send( $mail_tpl_copy, $owner_info['Mail'] );
			}
			
			if ( $success )
			{
				$_response -> script("printMessage('notice', '{$lang['mass_action_completed']}')");
			}
			else
			{
				trigger_error("Can not run mass action with listings (MySQL Fail). Action: {$action}", E_USER_ERROR);
				$GLOBALS['rlDebug'] -> logger("Can not run mass action with listings (MySQL Fail). Action: {$action}");
			}
		}
		elseif ( $action == 'delete' )
		{
			$this -> loadClass('Categories');

			foreach ($ids as $id)
			{
				$listing = $this -> fetch( array('Category_ID', 'Crossed'), array('ID' => $id), null, 1, 'listings', 'row');

				/* Decrease listing count in category */
				$GLOBALS['rlCategories'] -> listingsDecrease($listing['Category_ID']);
				if ( !empty($listing['Crossed']) )
				{
					$crossed_cats = explode(',', trim($listing['Crossed'], ','));
					foreach ($crossed_cats as $crossed_cat_id)
					{
						$GLOBALS['rlCategories'] -> listingsDecrease($crossed_cat_id);
					}
				}
				
				$GLOBALS['rlActions'] -> delete( array('ID' => $id), 'listings', $id, 1 );
				
				if (!$GLOBALS['config']['trash'])
				{
					$this -> deleteListingData($id);
				}
			}
			
			$del_action = $GLOBALS['rlActions'] -> action;
			$_response -> script("printMessage('notice', '{$lang['mass_listings_'.$del_action]}')");
		}
		
		return $_response;
	}
	
	/**
	* make fetured
	*
	* @package xAjax
	*
	* @param string $ids  - listings ids
	* @param int $plan    - featured plan ID
	*
	**/
	function ajaxMakeFeatured( $ids = false, $plan = false )
	{
		global $_response, $controller, $lang, $pages, $rlListingTypes, $config, $rlSmarty;

		$ids = explode('|', $ids);
		$plan = (int)$plan;
		
		if ( empty($ids) || empty($plan) )
		{
			return $_response;
		}

		$this -> loadClass('Mail');
		$this -> loadClass('Categories');
		$this -> loadClass('Account');
		
		/* inform listing owner about status changing */
		$mail_tpl = $GLOBALS['rlMail'] -> getEmailTemplate( 'listing_added_to_featured' );
		
		$sql = "SELECT `T2`.*, `T1`.`Username`, `T1`.`First_name`, `T1`.`Last_name`, `T1`.`Mail` AS `Account_email`, `T4`.`Path` AS `category_path`, ";
		$sql .= "`T4`.`Type` AS `Listing_type`, `T4`.`ID` AS `Category_ID`, `T4`.`Path` AS `Category_path`, `T1`.`ID` AS `Account_ID` ";
		$sql .= "FROM `".RL_DBPREFIX."accounts` AS `T1`";
		$sql .= "LEFT JOIN `".RL_DBPREFIX."listings` AS `T2` ON `T1`.`ID` = `T2`.`Account_ID` ";
		$sql .= "LEFT JOIN `".RL_DBPREFIX."listing_plans` AS `T3` ON `T2`.`Plan_ID` = `T3`.`ID` ";
		$sql .= "LEFT JOIN `".RL_DBPREFIX."categories` AS `T4` ON `T2`.`Category_ID` = `T4`.`ID` ";
		$sql .= "WHERE (`T2`.`ID` = '" . implode("' OR `T2`.`ID` = '", $ids) . "') AND ";
		$sql .= "UNIX_TIMESTAMP(DATE_ADD(`T2`.`Featured_date`, INTERVAL `T3`.`Listing_period` DAY)) > UNIX_TIMESTAMP(NOW())";
		$accounts = $this -> getAll($sql);

		foreach ( $accounts as $key => $value )
		{
			$mail_tpl_out = $mail_tpl;
			
			$account_info = $GLOBALS['rlAccount'] -> getProfile((int)$value['Account_ID']);
			
			$listing_type = $rlListingTypes -> types[$value['Listing_type']];
			$listing_title = $this -> getListingTitle($value['Category_ID'], $value, $value['Listing_type']);
			
			$link = RL_URL_HOME;
			$link .= $config['mod_rewrite'] ? $pages[$listing_type['Page_key']] .'/'. $value['Category_path'] .'/'. $rlSmarty -> str2path($listing_title) .'-'. $value['ID'] .'.html' : 'index.php?page='. $pages[$listing_type['Page_key']] .'&amp;id='. $value['ID'] ;
			$link = '<a href="'. $link .'">'. $listing_title .'</a>';
			
			$admin = $_SESSION['sessAdmin']['name'] ? $_SESSION['sessAdmin']['name'] : $_SESSION['sessAdmin']['user'];
			$date = date(str_replace(array('b', '%'), array('M', ''), RL_DATE_FORMAT));
			
			$mail_tpl_out['body'] = str_replace(array('{username}', '{link}', '{admin}', '{date}'), array($account_info['Full_name'], $link, $admin, $date), $mail_tpl_out['body']);
			$GLOBALS['rlMail'] -> send($mail_tpl_out, $account_info['Mail']);
		}

		// update listings
		$this -> query("UPDATE `".RL_DBPREFIX."listings` SET `Featured_ID` = '{$plan}', `Featured_date` = NOW() WHERE `ID` = '" . implode("' OR `ID` = '", $ids) . "'");

		$_response -> script("printMessage('notice', '{$lang['listing_made_featured']}');");

		$filter = $controller == 'browse' && isset($_GET['id']) ? "new Array('Category_ID||".(int)$_GET['id']."')" : '';

		$_response -> script( "listingsGrid.reload()");
		$_response -> script( "$('#make_featured').slideUp('fast');");
		
		return $_response;
	}
	
	/**
	* annul fetured
	*
	* @package xAjax
	*
	* @param string $ids - listings ids
	*
	**/
	function ajaxAnnulFeatured( $ids )
	{
		global $_response, $controller, $lang, $pages, $rlListingTypes, $config, $rlSmarty;

		$ids = explode('|', $ids);
		
		if ( empty($ids) )
		{
			return $_response;
		}

		$this -> loadClass('Mail');
		$this -> loadClass('Categories');
		$this -> loadClass('Account');
		
		/* inform listing owner about status changing */
		$mail_tpl = $GLOBALS['rlMail'] -> getEmailTemplate('featured_listing_annulled');

		$sql = "SELECT `T2`.*, `T1`.`Username`, `T1`.`First_name`, `T1`.`Last_name`, `T1`.`Mail` AS `Account_email`, `T4`.`Path` AS `category_path`, ";
		$sql .= "`T4`.`Type` AS `Listing_type`, `T4`.`ID` AS `Category_ID`, `T4`.`Path` AS `Category_path`, `T1`.`ID` AS `Account_ID` ";
		$sql .= "FROM `".RL_DBPREFIX."accounts` AS `T1`";
		$sql .= "LEFT JOIN `".RL_DBPREFIX."listings` AS `T2` ON `T1`.`ID` = `T2`.`Account_ID` ";
		$sql .= "LEFT JOIN `".RL_DBPREFIX."listing_plans` AS `T3` ON `T2`.`Plan_ID` = `T3`.`ID` ";
		$sql .= "LEFT JOIN `".RL_DBPREFIX."categories` AS `T4` ON `T2`.`Category_ID` = `T4`.`ID` ";
		$sql .= "WHERE (`T2`.`ID` = '" . implode("' OR `T2`.`ID` = '", $ids) . "') AND `T2`.`Featured_ID` <> '' AND ";
		$sql .= "UNIX_TIMESTAMP(DATE_ADD(`T2`.`Featured_date`, INTERVAL `T3`.`Listing_period` DAY)) > UNIX_TIMESTAMP(NOW())";
		$accounts = $this -> getAll($sql);

		foreach ( $accounts as $key => $value )
		{
			$mail_tpl_out = $mail_tpl;
			
			$account_info = $GLOBALS['rlAccount'] -> getProfile((int)$value['Account_ID']);
			
			$listing_type = $rlListingTypes -> types[$value['Listing_type']];
			$listing_title = $this -> getListingTitle($value['Category_ID'], $value, $value['Listing_type']);
			
			$link = RL_URL_HOME;
			$link .= $config['mod_rewrite'] ? $pages[$listing_type['Page_key']] .'/'. $value['Category_path'] .'/'. $rlSmarty -> str2path($listing_title) .'-'. $value['ID'] .'.html' : 'index.php?page='. $pages[$listing_type['Page_key']] .'&amp;id='. $value['ID'] ;
			$link = '<a href="'. $link .'">'. $listing_title .'</a>';
			
			$admin = $_SESSION['sessAdmin']['name'] ? $_SESSION['sessAdmin']['name'] : $_SESSION['sessAdmin']['user'];
			$date = date(str_replace(array('b', '%'), array('M', ''), RL_DATE_FORMAT));
			
			$mail_tpl_out['body'] = str_replace(array('{username}', '{link}', '{admin}', '{date}'), array($account_info['Full_name'], $link, $admin, $date), $mail_tpl_out['body']);
			$GLOBALS['rlMail'] -> send( $mail_tpl_out, $account_info['Mail'] );
		}
		
		// update listings
		$this -> query("UPDATE `".RL_DBPREFIX."listings` SET `Featured_ID` = '', `Featured_date` = '' WHERE `ID` = '" . implode("' OR `ID` = '", $ids) . "'");

		if ( $controller == 'browse' && isset($_GET['id']) )
		{
			$browse_id = (int)$_GET['id'];
			$_response -> script( "listingsGrid.filters = new Array(); listingsGrid.filters.push('Category_ID||{$browse_id}');");
		}
		
		$_response -> script("
			listingsGrid.reload();
			printMessage('notice', '{$lang['listing_featured_annulled']}');
		");
		
		return $_response;
	}
	
	/**
	* move listings
	*
	* @package xAjax
	*
	* @param string $ids   - listings ids
	* @param int $category - category ID
	*
	**/
	function ajaxMoveListing( $ids, $category )
	{
		global $_response, $controller, $lang;

		$moved = 0;
		
		$ids = explode('|', $ids);
		$category = (int)$category;
		
		if ( empty($ids) || empty($category) )
		{
			return $_response;
		}
		
		$this -> loadClass('Mail');
		$mail_tpl_source = $GLOBALS['rlMail'] -> getEmailTemplate( 'listing_moved' );

		$this -> loadClass('Categories');
		
		foreach ( $ids as $id )
		{
			$mail_tpl = $mail_tpl_source;
			
			$sql = "SELECT `T1`.*, IF(UNIX_TIMESTAMP(DATE_ADD(`T1`.`Pay_date`, INTERVAL `T3`.`Listing_period` DAY)) > UNIX_TIMESTAMP(NOW()), 1, 0) `Paid`, `T2`.`Username`, `T2`.`First_name`, `T2`.`Last_name`, `T2`.`Mail` FROM `". RL_DBPREFIX ."listings` AS `T1` ";
			$sql .= "LEFT JOIN `". RL_DBPREFIX ."accounts` AS `T2` ON `T1`.`Account_ID` = `T2`.`ID` ";
			$sql .= "LEFT JOIN `". RL_DBPREFIX ."listing_plans` AS `T3` ON `T1`.`Plan_ID` = `T3`.`ID` ";
			$sql .= "WHERE `T1`.`ID` = '{$id}'";
			$listing_info = $this -> getRow($sql);
			
			if ( $category != $listing_info['Category_ID'] )
			{
				// update listings
				$this -> query("UPDATE `".RL_DBPREFIX."listings` SET `Category_ID` = '{$category}' WHERE `ID` = '{$id}'");
				
				$GLOBALS['rlCategories'] -> listingsDecrease($listing_info['Category_ID']);
				$GLOBALS['rlCategories'] -> listingsIncrease($category);
	
				$name = empty($listing_info['First_name']) && empty($listing_info['Last_name']) ? $listing_info['Username'].'ddd' : $listing_info['First_name'] .' '. $listing_info['Last_name'];
				$listing_type = $this -> getOne("Type", "`ID` = ".$category, "categories");

				$listing_title = $this -> getListingTitle( $listing_info['Category_ID'], $listing_info, $listing_type );

				$category_info = $GLOBALS['rlCategories'] -> getCategory($category);
				
				$link = RL_URL_HOME;
				if ( $listing_info['Paid'] && $listing_info['Status'] == 'active' )
				{
					$browse_path = $this -> getOne('Path', "`Key` = 'lt_".$listing_type."'", 'pages');

					$cat_path = $this -> getOne('Path', "`ID` = '{$category}'", 'categories');
					$link .= $GLOBALS['config']['mod_rewrite'] ? $browse_path .'/'. $cat_path .'/'. $GLOBALS['rlValid'] -> str2path($listing_title) . '-l' .$listing_info['ID'] . '.html' : 'index.php?page=' .$browse_path . '&id=' .$listing_info['ID'];
				}
				else
				{
					$my_listings_path = $this -> getOne('Path', "`Key` = 'my_".$listing_type."'", 'pages');
					$link .= $GLOBALS['config']['mod_rewrite'] ? $my_listings_path . '.html' : 'index.php?page=' .$my_listings_path;
				}
				
				$link = '<a href="'. $link .'">'. $link . '</a>';
				
				$mail_tpl['body'] = str_replace(array('{username}', '{listing_title}', '{category}', '{link}'), array($name, $listing_title, $category_info['name'], $link), $mail_tpl['body']);
				
				$GLOBALS['rlMail'] -> send( $mail_tpl, $listing_info['Mail'] );
				
				$moved ++;
			}
		}
		
		if ( $moved > 0 )
		{
			if ( $controller == 'browse' && isset($_GET['id']) )
			{
				$_response -> redirect(RL_URL_HOME . ADMIN . '/index.php?controller=browse&id='. $_GET['id']);
				return $_response;
			}
			
			$_response -> script("
				listingsGrid.reload();
				listingsGrid.checkboxColumn.clearSelections();
				printMessage('notice', '{$lang['listing_moved']}');
				$('#move_area').slideUp();
			");
		}
		
		return $_response;
	}
	
	/**
	* delete listing file/image
	*
	* @package xAjax
	*
	* @param string $field  - listing field
	* @param string $value  - file/image name
	* @param string $dom_id - dom area
	*
	**/
	function ajaxDeleteListingFile( $field, $value, $dom_id )
	{
		global $_response;
				
		$field = $this -> rlValid -> xSql( $field );
		$value = $this -> rlValid -> xSql( $value );
		
		$info = $this -> fetch( array('ID', 'Account_ID'), array( $field => $value ), null, 1, 'listings', 'row' );
		
		unlink( RL_FILES . $value );
		$this -> query( "UPDATE `" . RL_DBPREFIX . "listings` SET `{$field}` = '' WHERE `ID` = '{$info['ID']}' LIMIT 1" );
		
		$_response -> script( "$('#{$dom_id}').slideUp('normal');" );
		
		$mess = $GLOBALS['lang']['item_deleted'];
		$_response -> script( "$('#notice_obj').fadeOut('fast', function(){ $('#notice_message').html('{$mess}'); $('#notice_obj').fadeIn('slow'); $('#error_obj').fadeOut('fast');});" );
		
		return $_response;
	}
	
	/**
	* get listings by account ID
	*
	* @param string $account_id - account ID
	* @param bool $short - short details mode
	* @param string $order - field name for order
	* @param string $order_type - order type
	* @param int $start - start DB position
	* @param int $limit - listing number per request
	*
	* @return array - listings information
	**/
	function getListingsByAccount( $account_id = false, $short = false, $order = false, $order_type = 'ASC', $start = 0, $limit = false )
	{
		global $sorting, $sql;

		if ( !$account_id )
		{
			return false;
		}
		
		$start = $start > 1 ? ($start - 1) * $limit : 0;

		$sql = "SELECT ";
		if ( !$short )
		{
			$sql .= "SQL_CALC_FOUND_ROWS DISTINCT SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT `T6`.`Thumbnail` ORDER BY `T6`.`Type` DESC), ',', 1) AS `Main_photo`, ";
		}
		
		$sql .= "`T1`.*, `T3`.`Path` AS `Path`, ";
		
		if ( !$short )
		{
			$sql .= $GLOBALS['config']['grid_photos_count'] ? "COUNT(`T6`.`Thumbnail`) AS `Photos_count`, " : "";
		}
		
		if ( !$short )
		{
			//$GLOBALS['rlHook'] ->  load('listingsModifyFieldByAccount');
			// temporary commented, hooks does not available in AP yet
		}
		
		if ( defined('IS_LOGIN') )
		{
			$sql .= "`T4`.`Account_ID` AS `Favorite`, ";
		}
		$sql .= "IF(UNIX_TIMESTAMP(DATE_ADD(`T1`.`Featured_date`, INTERVAL `T2`.`Listing_period` DAY)) > UNIX_TIMESTAMP(NOW()), '1', '0') `Featured`, `T3`.`Parent_ID`, `T3`.`Key` AS `Cat_key`, `T3`.`Type` AS `Cat_type` ";
		$sql .= "FROM `" . RL_DBPREFIX . "listings` AS `T1` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_plans` AS `T2` ON `T1`.`Plan_ID` = `T2`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "categories` AS `T3` ON `T1`.`Category_ID` = `T3`.`ID` ";
		if ( !$short )
		{
			$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_photos` AS `T6` ON `T1`.`ID` = `T6`.`Listing_ID` ";
		}
		
		if ( !$short )
		{
			//$GLOBALS['rlHook'] -> load('listingsModifyJoinByAccount');
			// temporary commented, hooks does not available in AP yet
		}
		
		$sql .= "WHERE UNIX_TIMESTAMP(DATE_ADD(`T1`.`Pay_date`, INTERVAL `T2`.`Listing_period` DAY)) > UNIX_TIMESTAMP(NOW()) ";
		$sql .= "AND `T1`.`Account_ID` = '{$account_id}' ";
		$sql .= "AND `T1`.`Status` = 'active' AND `T3`.`Status` = 'active' AND `T3`.`Type` <> 'advertising' ";
		
		if ( !$short )
		{
			//$GLOBALS['rlHook'] ->  load('listingsModifyWhereByAccount');
			//$GLOBALS['rlHook'] ->  load('listingsModifyGroupByAccount');
			// temporary commented, hooks does not available in AP yet
		}
		
		if ( false === strpos($sql, 'GROUP BY') )
		{
			$sql .= " GROUP BY `T1`.`ID` ";
		}

		$sql .= "ORDER BY `Featured` DESC ";
		if ( $order )
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
		if ( $start && $limit )
		{
			$sql .= "LIMIT {$start}, {$limit} ";
		}

		$listings = $this -> getAll( $sql );
		
		if ( empty($listings) )
		{
			return false;
		}

		$calc = $this -> getRow( "SELECT FOUND_ROWS() AS `calc`" );
		$this ->calc = $calc['calc'];
		
		if ( $short )
		{
			return $listings;
		}
		
		foreach ( $listings as $key => $value )
		{
			$category_fields = $this -> getFormFields( $listings[$key] );
			$items = $category_fields[$listings[$key]['Category_ID']]['fields'];
			$listings[$key]['name'] = $category_fields[$listings[$key]['Category_ID']]['name'];
			
			$first = 1;
			
			foreach ($items as $item => $val)
			{
				$field = $category_fields[$listings[$key]['Category_ID']]['fields'][$item];

				if ($first != 1)
				{
					$field['value'] = $GLOBALS['rlCommon'] -> adaptValue( $field, $listings[$key][$item], 'listing', $listings[$key]['ID'] );
				}
				else
				{
					if ( $field['Condition'] == 'isUrl' || $field['Condition'] == 'isEmail' )
					{
						$field['value'] = $listings[$key][$item];
					}
					else
					{
						$field['value'] = $GLOBALS['rlCommon'] -> adaptValue( $field, $listings[$key][$item], 'listing', $listings[$key]['ID'] );
					}
				}

				$listings[$key]['fields'][] = $field;
				
				$first++;
			}
			
			$listings[$key]['listing_title'] = $this -> getListingTitle( $listings[$key]['Category_ID'], $listings[$key] );
		}

		return $listings;
	}
}
