<?php

/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: RLCOMMON.CLASS.PHP
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

class rlCommon extends reefless 
{
	/**
	* @var validator class object
	**/
	var $rlValid;
	
	/**
	* @var lang class object
	**/
	var $rlLang;
	
	/**
	* @var block keys
	**/
	var $block_keys;
	
	/**
	* @var error fields string
	**/
	var $error_fields;
	
	/**
	* class constructor
	**/
	function rlCommon()
	{
		global $rlValid, $rlLang;

		$this -> rlValid = & $rlValid;
		$this -> rlLang  = & $rlLang;
	}
		
	/**
	* define blocks existing for template sides
	*
	* @param array $blocks - blocks array
	*
	* @return mixed array
	**/
	function defineBlocksExist( &$blocks )
	{
		global $l_block_sides, $rlSmarty;
		
		/* unset all sides */
		foreach ($l_block_sides as $key => $value)
		{
			unset($blocks[$key]);
		}
		
		/* set available blocks sides */
		foreach ($blocks as $key => $value)
		{
			if ( array_key_exists($value['Side'], $l_block_sides) )
			{
				$blocks[$value['Side']] = true;
			}
		}
		
		/* detect wide mode */
		$wide_mode = true;
		if ( $blocks['right'] && $blocks['left'] )
		{
			$wide_mode = false;
		}
		$rlSmarty -> assign('wide_mode', $wide_mode);
	}
	
	/**
	* get bread crumbs info
	*
	* @param array $cPage - current page information
	*
	* @return array $bread_crumbs - page bread crumbs
	**/
	function getBreadCrumbs( $cPage )
	{
		global $lang;
		
		$bread_crumbs[] = array( 
			'name' => $lang['bread_crumbs_title'],
			'title' => $lang['bread_crumbs_name']
		);
		
		if ( $cPage['Parent_ID'] )
		{
			$add_bread_crumb = $this -> fetch( array('Parent_ID', 'Path', 'Key'), array( 'ID' => $cPage['Parent_ID'], 'Status' => 'active' ), null, 1, 'pages', 'row' );
			$add_bread_crumb = $this -> rlLang -> replaceLangKeys( $add_bread_crumb, 'pages', array( 'name', 'title' ) );

			if ( $add_bread_crumb['Parent_ID'] )
			{
				return $this -> getBreadCrumbs( $bread_crumbs );
			}
			
			$bread_crumbs[] = array(
				'name' => $add_bread_crumb['name'],
				'title' => $add_bread_crumb['title'],
				'path' => $add_bread_crumb['Path']
			);
		}
		
		$bread_crumbs[] = array(
			'name' => $cPage['name'],
			'title' => $cPage['title'],
			'path' => $cPage['Path']
		);

		return $bread_crumbs;
	}
	
	/**
	* get page title
	*
	* @param array $cPage - current page information
	*
	* @return array $bread_crumbs - page bread crumbs
	**/
	function pageTitle( $bread_crumbs = false )
	{
		global $rlSmarty, $page_info, $rlHook, $single_title_controllers;

		/* single title controllers */
		$single_title_controllers = array('listing_details', 'listing_type', 'news', 'recently_added');
		
		$title[] = $page_info['title'];
		
		if ( count($bread_crumbs) > 1 && !in_array($page_info['Controller'], $single_title_controllers) )
		{
			foreach ($bread_crumbs as $index => $item)
			{
				if ( $index > 1 )
				{
					$title[] = $item['title'] ? $item['title'] : $item['name'];
				}
			}
		}
		else if ( count($bread_crumbs) > 1 && in_array($page_info['Controller'], $single_title_controllers) )
		{
			$bread_crumbs = array_reverse($bread_crumbs);
			$title = array();
			$title[] = $bread_crumbs[0]['title'] ? $bread_crumbs[0]['title'] : $bread_crumbs[0]['name'];
		}
		
		$rlHook -> load('pageTitle');

		$title = array_reverse($title);
		$rlSmarty -> assign_by_ref('title', $title);
	}
	
	/**
	* build menus
	*
	* @todo generates and assigns menus to SMARTY
	**/
	function buildMenus()
	{
		global $rlSmarty, $fields, $main_menu, $tpl_settings, $account_info, $deny_pages, $config, $rlMobile;
		
		$fields = array( 'ID', 'Page_type', 'Key', 'Path', 'Get_vars', 'Controller', 'No_follow', 'Menus', 'Deny', 'Login' );

		$this -> setTable('pages');
		$menus = $this -> fetch( $fields, array( 'Status' => 'active' ), "ORDER BY `Position`" );
		$menus = $this -> rlLang -> replaceLangKeys( $menus, 'pages', array( 'name', 'title' ) );
		
		foreach ( $menus as $key => $value )
		{
			/* build mobile version menus */
			if ( $config['mobile_version_module'] && $rlMobile -> isMobile )
			{
				/* generate main menu */
				if ( in_array(5, explode(',', $value['Menus'])) )
				{
					$main_menu[] = $value;
				}
				
				/* generate user menu */
				if ( in_array(6, explode(',', $value['Menus'])) )
				{
					$user_menu[] = $value;
				}
				
				/* generate footer menu */
				if ( in_array(7, explode(',', $value['Menus'])) )
				{
					$footer_menu[] = $value;
				}
			}
			/* build standard version menus */
			else
			{
				/* generate main menu */
				if ( in_array(1, explode(',', $value['Menus'])) )
				{
					$main_menu[] = $value;
				}
				
				/* generate footer menu */
				if ( in_array(3, explode(',', $value['Menus'])) )
				{
					$footer_menu[] = $value;
				}
				
				/* generate account menu */
				if ( in_array(2, explode(',', $value['Menus'])) && (!in_array($account_info['Type_ID'], explode(',', $value['Deny'])) || !$account_info['Type_ID']) && (!in_array($value['Key'], $deny_pages) || !$deny_pages) )
				{
					$account_menu[] = $value;
				}
				
				/* generate inventory menu */
				if ( $tpl_settings['inventory_menu'] === true )
				{
					if ( in_array(4, explode(',', $value['Menus'])) )
					{
						$inventory_menu[] = $value;
					}
				}
			}
		}
		
		$rlSmarty -> assign_by_ref('main_menu', $main_menu);
		$rlSmarty -> assign_by_ref('footer_menu', $footer_menu);
		$rlSmarty -> assign_by_ref('account_menu', $account_menu);
		$rlSmarty -> assign_by_ref('user_menu', $user_menu);
		
		if ( $tpl_settings['inventory_menu'] === true )
		{
			$rlSmarty -> assign_by_ref( 'inventory_menu', $inventory_menu );
		}
	}
	
	/**
	* check dynamic form
	*
	* @param array $data - the form data (field => value)
	* @param array $fields - fields
	* @param string $prefix - field name prefix
	* @param bool $admin - admin mode
	*
	* @return array $errors - errors
	**/
	function checkDynamicForm( $data = false, $fields = false, $prefix = 'f', $admin = false )
	{
		global $error_fields, $lang, $languages, $l_deny_files_regexp;
		
		$errors = false;
		
		$flStrlenFunc = 'strlen';
		
		if ( function_exists('mb_strlen') && function_exists('mb_internal_encoding') )
		{
			mb_internal_encoding('UTF-8');
			$flStrlenFunc = 'mb_strlen';
		}
		
		if ( !$data || !$fields )
			return false;

		foreach ( $fields as $fIndex => $fRow )
		{
			$sFields[$fIndex] = $fields[$fIndex]['Key'];
		}

		$step = 0;
		
		foreach ($data as $f2 => $v2)
		{
			$poss = array_search( $f2, $sFields );

			if ( false !== $poss )
			{	
				switch ($fields[$poss]['Type']){
				
					case 'text':
						if ( $fields[$poss]['Required'] )
						{
							if ( $fields[$poss]['Multilingual'] )
							{
								$ml_empty = 0;
								foreach ($data[$f2] as $ml_key => $ml_val)
								{
									$ml_val = trim( $ml_val );
									if ( empty($ml_val) )
									{
										$ml_empty++;
										if ( $admin )
										{
											$error_fields[] = $prefix. "[{$fields[$poss]['Key']}][{$ml_key}]";
										}
										else
										{
											$this -> error_fields .= $prefix. "[{$fields[$poss]['Key']}][{$ml_key}],";
										}
									}
								}
								
								if ( count($data[$f2]) == $ml_empty )
								{
									$errors[$step] = str_replace('{field}', $fields[$poss]['name'], $lang['required_multilingual_error']);
								}
							}
							else
							{
								$data[$f2] = trim($data[$f2]);
								$data[$f2] = $fields[$poss]['Condition'] == 'isUrl' && !(bool)preg_match('/https?\:\/\//', $data[$f2]) && !empty($data[$f2]) ? 'http://'. $data[$f2] : $data[$f2];
								
								if (empty($data[$f2]))
								{
									$errors[$step] = str_replace( '{field}', '<span class="field_error">"'.$fields[$poss]['name'].'"</span>', $GLOBALS['lang']['notice_field_empty']);
								}
								elseif ($fields[$poss]['Condition'])
								{
									if ( !$this -> rlValid -> $fields[$poss]['Condition']($data[$f2]) )
									{
										$errors[$step] = str_replace( '{field}', '<span class="field_error">"'.$fields[$poss]['name'].'"</span>', $GLOBALS['lang']['notice_field_incorrect']);
									}
								}
							}
						}
						else 
						{
							$data[$f2] = trim($data[$f2]);
							$data[$f2] = $fields[$poss]['Condition'] == 'isUrl' && !(bool)preg_match('/https?\:\/\//', $data[$f2]) && !empty($data[$f2]) ? 'http://'. $data[$f2] : $data[$f2];
							if ($fields[$poss]['Condition'] && !empty($data[$f2]))
							{
								if ( !$this -> rlValid -> $fields[$poss]['Condition']($data[$f2]) )
								{
									$errors[$step] = str_replace( '{field}', '<span class="field_error">"'.$fields[$poss]['name'].'"</span>', $GLOBALS['lang']['notice_field_incorrect']);
								}
							}
						}
						break;
					
					case 'textarea':
						$limit = (int)$fields[$poss]['Values'];
						
						if ( $fields[$poss]['Multilingual'] )
						{
							$ml_empty = 0;
							foreach ($data[$f2] as $ml_key => $ml_val)
							{
								$ml_val = trim( $ml_val );
								
								/* check for empty value */
								if ( empty($ml_val) && $fields[$poss]['Required'] )
								{
									$ml_empty++;
									if ( $admin )
									{
										$error_fields[] = $prefix. "[{$fields[$poss]['Key']}][{$ml_key}]";
									}
									else
									{
										$this -> error_fields .= $prefix. "[{$fields[$poss]['Key']}][{$ml_key}],";
									}
								}
								
								/* check for exceeded limit */
								if ( $limit && $flStrlenFunc(strip_tags($ml_val)) > $limit )
								{
									if ( $admin )
									{
										$error_fields[] = $prefix. "[{$fields[$poss]['Key']}][{$ml_key}]";
									}
									else
									{
										$this -> error_fields .= $prefix. "[{$fields[$poss]['Key']}][{$ml_key}],";
									}
									
									$errors[$step] = str_replace(array('{field}', '{limit}'), array($fields[$poss]['name'] .' ('. $languages[$ml_key]['name'] .')', $limit), $lang['error_textarea_limit_exceeded']);
								}
							}
							
							if ( count($data[$f2]) == $ml_empty && $fields[$poss]['Required'] )
							{
								$errors[$step] = str_replace('{field}', $fields[$poss]['name'], $lang['required_multilingual_error']);
							}
						}
						else
						{
							$data[$f2] = trim( $data[$f2] );
							
							/* check for empty value */
							if ( empty($data[$f2]) && $fields[$poss]['Required'] )
							{
								if ( $admin )
								{
									$error_fields[] = $prefix. "[{$fields[$poss]['Key']}]";
								}
								else
								{
									$this -> error_fields .= $prefix. "[{$fields[$poss]['Key']}],";
								}
								$errors[$step] = str_replace( '{field}', '<span class="field_error">"'.$fields[$poss]['name'].'"</span>', $GLOBALS['lang']['notice_field_empty']);
							}
							
							/* check for exceeded limit */
							if ( $limit && $flStrlenFunc(strip_tags($data[$f2])) > $limit )
							{
								if ( $admin )
								{
									$error_fields[] = $prefix. "[{$fields[$poss]['Key']}]";
								}
								else
								{
									$this -> error_fields .= $prefix. "[{$fields[$poss]['Key']}],";
								}
								
								$errors[$step] = str_replace(array('{field}', '{limit}'), array($fields[$poss]['name'], $limit), $lang['error_textarea_limit_exceeded']);
							}
						}
						break;
					
					case 'number':
						$data[$f2] = trim($data[$f2]);
						
						if ( $fields[$poss]['Required'] || !empty($data[$f2]))
						{
							if ( $fields[$poss]['Values'] && $data[$f2] )
							{
								if ( strlen($data[$f2]) > $fields[$poss]['Values'] )
								{
									$errors[$step] = str_replace( array('{field}', '{max}'), array('<span class="field_error">"'.$fields[$poss]['name'].'"</span>', '<span class="field_error">"'.$fields[$poss]['Values'].'"</span>'), $GLOBALS['lang']['notice_number_incorrect']);
								}
							}
							else 
							{
								if ( empty($data[$f2]) )
								{
									$errors[$step] = str_replace( '{field}', '<span class="field_error">"'.$fields[$poss]['name'].'"</span>', $GLOBALS['lang']['notice_field_empty']);
								}
							}
						}
						break;
						
					case 'phone':
						if ( $fields[$poss]['Required'] && ((empty($data[$f2]['code']) && $fields[$poss]['Opt1']) || empty($data[$f2]['area']) || empty($data[$f2]['number'])) )
						{
							$errors[$step] = str_replace( '{field}', '<span class="field_error">"'.$fields[$poss]['name'].'"</span>', $GLOBALS['lang']['notice_phone_field_error']);
							
							if ( empty($data[$f2]['code']) && $fields[$poss]['Opt1'] )
							{
								if ( $admin )
								{
									$error_fields[$step] = $prefix. "[{$fields[$poss]['Key']}][code]";
								}
								else
								{
									$this -> error_fields .= $prefix. "[{$fields[$poss]['Key']}][code],";
								}
							}
							if ( empty($data[$f2]['area']) )
							{
								if ( $admin )
								{
									$error_fields[$step] = $prefix. "[{$fields[$poss]['Key']}][area]";
								}
								else
								{
									$this -> error_fields .= $prefix. "[{$fields[$poss]['Key']}][area],";
								}
							}
							if ( empty($data[$f2]['number']) )
							{
								if ( $admin )
								{
									$error_fields[$step] = $prefix. "[{$fields[$poss]['Key']}][number]";
								}
								else
								{
									$this -> error_fields .= $prefix. "[{$fields[$poss]['Key']}][number],";
								}
							}
						}
						elseif ( !$fields[$poss]['Required'] && (((!empty($data[$f2]['area']) && !$fields[$poss]['Condition']) || !empty($data[$f2]['number']) || (!empty($data[$f2]['code']) && $fields[$poss]['Opt1'])/* || (!empty($data[$f2]['ext']) && $fields[$poss]['Opt2'])*/) && (empty($data[$f2]['area']) || empty($data[$f2]['number']) || (empty($data[$f2]['code']) && $fields[$poss]['Opt1']) || (empty($data[$f2]['ext']) && $fields[$poss]['Opt2']))) )
						{
							$errors[$step] = str_replace( '{field}', '<span class="field_error">"'.$fields[$poss]['name'].'"</span>', $GLOBALS['lang']['notice_phone_field_error']);
						}
						break;
					
					case 'date':
						if ( $fields[$poss]['Default'] == 'single' )
						{
							if ( $fields[$poss]['Required'] && empty($data[$f2]) )
							{
								$errors[$step] = str_replace( '{field}', '<span class="field_error">"'.$fields[$poss]['name'].'"</span>', $GLOBALS['lang']['notice_field_empty']);
							}
							elseif ( !empty($data[$f2]) )
							{
								if( !(bool)preg_match('/^[0-9]{4}\-[0-1][0-9]\-[0-3][0-9]$/', $data[$f2]) )
								{
									$errors[$step] = str_replace( '{field}', '<span class="field_error">"'.$fields[$poss]['name'].'"</span>', $GLOBALS['lang']['notice_field_incorrect']);
								}
							}
						}
						elseif ( $fields[$poss]['Default'] == 'multi' )
						{
							if ( $fields[$poss]['Required'] && (empty($data[$f2]['from']) || empty($data[$f2]['to'])) )
							{
								$errors[$step] = str_replace( '{field}', '<span class="field_error">"'.$fields[$poss]['name'].'"</span>', $GLOBALS['lang']['notice_field_empty']);
								if ( $admin )
								{
									$error_fields[] = $prefix. "[{$fields[$poss]['Key']}][from]";
									$error_fields[] = $prefix. "[{$fields[$poss]['Key']}][to]";
								}
								else
								{
									$this -> error_fields .= $prefix. "[{$fields[$poss]['Key']}][from],";
									$this -> error_fields .= $prefix. "[{$fields[$poss]['Key']}][to],";
								}
							}
							elseif ( !empty($data[$f2]['from']) || !empty($data[$f2]['to']) )
							{
								if( !(bool)preg_match('/^[0-9]{4}\-[0-1][0-9]\-[0-3][0-9]$/', $data[$f2]['from']) && !empty($data[$f2]['from']) )
								{
									$errors[$step] = str_replace( '{field}', '<span class="field_error">"'.$fields[$poss]['name'].'" ('.$GLOBALS['lang']['from'].')</span>', $GLOBALS['lang']['notice_field_incorrect']);
									if ( $admin )
									{
										$error_fields[] = $prefix. "[{$fields[$poss]['Key']}][from]";
									}
									else
									{
										$this -> error_fields .= $prefix. "[{$fields[$poss]['Key']}][from],";
									}
								}
								if( !(bool)preg_match('/^[0-9]{4}\-[0-1][0-9]\-[0-3][0-9]$/', $data[$f2]['to']) && !empty($data[$f2]['to']) )
								{
									$errors[$step] = str_replace( '{field}', '<span class="field_error">"'.$fields[$poss]['name'].'" ('.$GLOBALS['lang']['to'].')</span>', $GLOBALS['lang']['notice_field_incorrect']);
									if ( $admin )
									{
										$error_fields[] = $prefix. "[{$fields[$poss]['Key']}][to]";
									}
									else
									{
										$this -> error_fields .= $prefix. "[{$fields[$poss]['Key']}][to],";
									}
								}
							}
						}
						
					break;
					
					case 'mixed':
					case 'price':
					case 'unit':
						$data[$f2]['value'] = trim( $data[$f2]['value'] );
						if ($fields[$poss]['Required'] && empty($data[$f2]['value']))
						{
							$errors[$step] = str_replace( '{field}', '<span class="field_error">"'.$fields[$poss]['name'].'"</span>', $GLOBALS['lang']['notice_field_empty']);
							if ( $admin )
							{
								$error_fields[] = $prefix. "[{$fields[$poss]['Key']}][value]";
							}
							else
							{
								$this -> error_fields .= $prefix. "[{$fields[$poss]['Key']}][value],";
							}
						}
						break;
						
					case 'select':
						$data[$f2] = trim( $data[$f2] );
						if ( $fields[$poss]['Required'] && empty($data[$f2]) )
						{
							$errors[$step] = str_replace( '{field}', '<span class="field_error">"'.$fields[$poss]['name'].'"</span>', $GLOBALS['lang']['notice_select_empty']);
						}
						break;
					
					case 'checkbox':
						unset($data[$f2][0]);
					case 'radio':
	
						if ($fields[$poss]['Required'] && empty($data[$f2]))
						{
							$errors[$step] = str_replace( '{field}', '<span class="field_error">"'.$fields[$poss]['name'].'"</span>', $GLOBALS['lang']['notice_checkbox_empty']);
						}
						break;
						
					case 'accept':
						if ($fields[$poss]['Required'] && $data[$f2] != 'yes')
						{
							$errors[$step] = str_replace( '{field}', '<span class="field_error">"'.$fields[$poss]['name'].'"</span>', $GLOBALS['lang']['notice_no_agree']);
						}
						break;
					
					case 'image':
						if ( ($fields[$poss]['Required'] && empty($_FILES[$f2]['name']) && empty($data[$f2])) || (!$fields[$poss]['Required'] && !empty($_FILES[$f2]['name'])) )
						{
							if ( empty($_FILES[$f2]['name']) )
							{
								$errors[$step] = str_replace( array( '{field}' ), array( '<span class="field_error">"'.$fields[$poss]['name'].'"</span>' ), $GLOBALS['lang']['notice_field_empty']);
							}
							else 
							{
								$file_ext = explode( '.', $_FILES[$f2]['name'] );
								$file_ext = array_reverse( $file_ext );
								$file_ext = $file_ext[0];
			
								if ( !$this -> rlValid -> isImage($file_ext) || preg_match($l_deny_files_regexp, $_FILES[$f2]['name']) )
								{
									$errors[$step] = str_replace( array( '{field}', '{ext}' ), array( '<span class="field_error">"'.$fields[$poss]['name'].'"</span>', '<span class="field_error">"'.$file_ext.'"</span>' ), $GLOBALS['lang']['notice_bad_file_ext']);
								}
							}
						}
						break;
					
					case 'file':
						if ( !empty($_FILES[$f2]['name']) || ( $fields[$poss]['Required'] && empty($data[$f2]) ) )
						{
							if ( empty($_FILES[$f2]['name']) )
							{
								$errors[$step] = str_replace( array( '{field}' ), array( '<span class="field_error">"'.$fields[$poss]['name'].'"</span>' ), $GLOBALS['lang']['notice_field_empty']);
							}
							else 
							{
								$file_ext = explode( '.', $_FILES[$f2]['name'] );
								$file_ext = array_reverse( $file_ext );
								$file_ext = $file_ext[0];
			
								if ( !$this -> rlValid -> isFile( $fields[$poss]['Default'], $file_ext) || preg_match($l_deny_files_regexp, $_FILES[$f2]['name']) )
								{
									$errors[$step] = str_replace( array( '{field}', '{ext}' ), array( '<span class="field_error">"'.$fields[$poss]['name'].'"</span>', '<span class="field_error">"'.$file_ext.'"</span>' ), $GLOBALS['lang']['notice_bad_file_ext']);
								}
							}
						}
						break;
				}
			
				if ( !in_array($fields[$poss]['Type'], array('phone')) )
				{
					if ( $errors[$step] )
					{
						$step++;
						if ( $admin )
						{
							$error_fields[] = $prefix. "[{$fields[$poss]['Key']}]";
						}
						else
						{
							$this -> error_fields .= $prefix. "[{$fields[$poss]['Key']}],";
						}
					}
					else
					{
						unset($errors[$step]);
					}
				}
			}
		}

		return $errors;
	}
	
	/**
	* adapt value
	*
	* @param array $field - field info
	* @param mixed $value - original value
	* @param string $type - field type
	* @param int $id - item id
	* @param bool $tags - use html tags
	* @param bool $strip_tags - strip tags for html fileds
	* @param bool $edit_mode - edit mode
	*
	* @return mixed - category fields list
	**/
	function adaptValue( &$field, &$value, $type = 'listing', $id = false, $tags = true, $strip_tags = false, $edit_mode = false )
	{
		global $lang, $config;
		
		$out = false;

		if ( empty($value) )
		{
			return false;
		}
		
		switch ($field['Type']){
			case 'price':
				$price = null;
				$price = explode( '|', $value );
				//$out = (defined('REALM') && REALM == 'admin') ? $GLOBALS['lang']['data_formats+name+'.$price[1]].$price[0] : $GLOBALS['lang']['data_formats+name+'.$price[1]].' '.$GLOBALS['rlSmarty'] -> str2money($price[0]);
				if ( $config['system_currency_position'] == 'before' )
				{
					$out = $lang['data_formats+name+'.$price[1]].' '.$GLOBALS['rlValid'] -> str2money($price[0]);
				}
				else
				{
					$out = $GLOBALS['rlValid'] -> str2money($price[0]).' '.$lang['data_formats+name+'.$price[1]];
				}
				break;
			
			case 'unit':
				$price = null;
				$price = explode( '|', $value );
				$out = $price[0]. ' ' .$GLOBALS['lang']['data_formats+name+'.$price[1]];
				break;
			
			case 'mixed':
				$df = null;
				$df = explode( '|', $value );
				
				if ( !empty($field['Condition']) )
				{
					$out = $df[0]. ' ' .$GLOBALS['lang']['data_formats+name+'.$df[1]];
				}
				else
				{
					$out = $df[0]. ' ' .$GLOBALS['lang'][$type . '_fields+name+'.$df[1]];
				}
				break;
			
			case 'date':
				if ( $field['Default'] == 'single' )
				{
					list($d_year, $d_month, $d_day) = explode('-', $value);
					$d_timestamp = mktime(0, 0, 0, $d_month, $d_day, $d_year);
					if ($d_timestamp <= 943920000)
					{
						return false;
					}
					$out = date(str_replace(array('%', 'b'), array('', 'M'), RL_DATE_FORMAT), $d_timestamp);
				}
				elseif ( $field['Default'] == 'multi' )
				{
					list($d_year, $d_month, $d_day) = explode('-', $value);
					$d_timestamp = mktime(0, 0, 0, $d_month, $d_day, $d_year);
					$d_date = date(str_replace(array('%', 'b'), array('', 'M'), RL_DATE_FORMAT), $d_timestamp);
					if ($d_timestamp > 943920000)
					{
						$out = $tags ? '<span class="grey_small">'. $GLOBALS['lang']['from'] .'</span> '. $d_date .' ' : $d_date;
					}

					if ( $id )
					{
						$multi_field = $this -> getOne($field['Key'].'_multi', "`ID` = '{$id}'", $type == 'listing' ? 'listings' : 'accounts');
						list($t_year, $t_month, $t_day) = explode('-', $multi_field);
						$t_timestamp = mktime(0, 0, 0, $t_month, $t_day, $t_year);
						if ($t_timestamp > 943920000)
						{
							$d_date_to = date(str_replace(array('%', 'b'), array('', 'M'), RL_DATE_FORMAT), $t_timestamp);
							$out .= $tags ? '<span class="grey_small">'. $GLOBALS['lang']['to'] .'</span> '. $d_date_to : ' - '.$d_date_to;
						}
					}
				}
				break;
			
			case 'text':
				if ( in_array($field['Condition'], array('isUrl', 'isDomain')) )
				{
					if (strlen($value) > 60)
					{
						$first_value = substr($value, 0, 30);
						$second_value = substr($value, -30, 30);
						$display_value = $first_value . '...' . $second_value;
					}
					else
					{
						$display_value = $value;
					}
					
					$value = !(bool)preg_match('/https?\:\/\//', $value) ? 'http://'.$value : $value;
					
					$out = '<a target="_blank" href="' . $value . '">' . str_replace('http://', '', $display_value) . '</a>';
				}
				elseif ($field['Condition'] == 'isEmail')
				{
					$out = '<a class="static" href="mailto:' . $value . '">' . $value . '</a>';
				}
				else
				{
					if ( $field['Multilingual'] )
					{
						$out = $this -> parseMultilingual($value, RL_LANG_CODE);
					}
					else
					{
						$out = $value;
					}
				}
				
				if ( $strip_tags )
				{
					$out = strip_tags($out);
				}
				break;
			
			case 'textarea':
				if ( $field['Multilingual'] )
				{
					$out = nl2br($this -> parseMultilingual($value, RL_LANG_CODE));
				}
				else
				{
					$out = nl2br($value);
				}
				
				if ( $field['Condition'] == 'html' && $strip_tags )
				{
					$out = strip_tags($out);
				}
				break;
				
			case 'phone':
				$out = $this -> parsePhone($value, $edit_mode ? false : $field);
				break;
				
			case 'number':
				$out = $value;
				break;
			
			case 'bool':
				if ( (bool)$value )
				{
					$out = $GLOBALS['lang']['yes'];
				}
				else
				{
					$out = $GLOBALS['lang']['no'];
				}
				break;
			
			case 'select':
				if ( !empty($field['Condition']) )
				{
					if ( $field['Condition'] != 'years' )
					{
						$out = $GLOBALS['lang']['data_formats+name+'.$value];
					}
					else
					{
						$out = $value;
					}
				}
				else
				{
					$out = $GLOBALS['lang'][$type . '_fields+name+'.$field['Key'].'_'.$value];
				}
				break;
			
			case 'radio':
			case 'checkbox':
				if ( !empty($field['Condition']) )
				{
					if ( $field['Condition'] != 'years' )
					{
						$vals = explode(',', $value);
						foreach ($vals as $val_item)
						{
							if ( !empty($GLOBALS['lang']['data_formats+name+'.$val_item]) )
							{
								$out .= $GLOBALS['lang']['data_formats+name+'.$val_item] . ', ';
							}
						}
						$out = substr( $out, 0, -2 );
					}
					else
					{
						$out = $value;
					}
				}
				else
				{
					$multi_values = explode( ',', $value );
					
					if ( !empty($multi_values[0]) )
					{
						foreach ( $multi_values as $chKey => $chVal )
						{
							$out .= $GLOBALS['lang'][$type . '_fields+name+'.$field['Key'].'_'.$multi_values[$chKey]] . ', ';
						}
						$out = substr( $out, 0, -2 );
					}
				}
				break;
			
			case 'image':
				if ( !$strip_tags )
				{
					$out = '<img alt="" src="' . RL_URL_HOME . 'files/' . $value . '" />';
				}
				break;
			
			case 'file':
				if ( !$strip_tags )
				{
					$out = '<a class="static" href="' . RL_URL_HOME . 'files/' . $value . '">' . $GLOBALS['lang']['download'] . '</a>';
				}
				break;
		}

		$GLOBALS['rlHook'] -> load('adaptValueBottom', $value, $field, $out);

		return $out;
	}
	
	/**
	* fields types adaptation (select, checkBox, radio)
	*
	* @param array $values - type values
	* @param string $table - table name
	* @param string $listing_type - listing type
	*
	**/
	function fieldValuesAdaptation( $fields, $table, $listing_type = false )
	{
		global $config, $rlCache, $edit_mode;

		if ( !$GLOBALS['data_formats'] )
		{
			$format = $this -> fetch( array( 'ID', 'Key', 'Order_type'), array( 'Parent_ID' => '0', 'Status' => 'active' ) , 'ORDER BY `Key`', null, 'data_formats' );
			foreach ($format as $key => $val)
			{
				$data_formats[$format[$key]['Key']] = $val;
			}
			unset($format);
			$GLOBALS['data_formats'] = $data_formats;
		}
		else
		{
			$data_formats = $GLOBALS['data_formats'];
		}
		
		if ( !empty($fields) )
		{
			foreach ($fields as $index => $value)
			{
				if ( $fields[$index]['Type'] == 'select' || $fields[$index]['Type'] == 'checkbox' || $fields[$index]['Type'] == 'radio' || $fields[$index]['Type'] == 'mixed' )
				{
					// bind with data formats
					if ( $data_formats[$fields[$index]['Condition']] )
					{
						$format_values = false;
						
						if ($fields[$index]['Condition'] == 'years')
						{
							$step = 0;
							for( $i = date('Y')+2; $i>= 1940; $i-- )
							{
								$format_values[$step]['name'] = $i;
								$format_values[$step]['Key'] = $i;
								
								$step++;
							}
						}						
						else
						{
							$format_values = $GLOBALS['rlCategories'] -> getDF($fields[$index]['Condition'], $data_formats[$fields[$index]['Condition']]['Order_type']);
						}

						$fields[$index]['Values'] = $format_values;
						unset($format_values);
					}
					else 
					{
						$adapted = array();
						switch ($fields[$index]['Key']){
							case 'Category_ID':
								if( $GLOBALS['rlListingTypes'] -> types[$listing_type]['Search_multi_categories'] )
								{
									$fields[$index]['Values'] = $GLOBALS['rlCategories'] -> getCategories(0, $listing_type);
								}
								else
								{
									$fields[$index]['Values'] = $GLOBALS['rlCategories'] -> getCatTitles($listing_type);
								}
								break;
								
							case 'posted_by':
								$this -> loadClass('Account');
								$tmp_account_types = $GLOBALS['rlAccount'] -> getAccountTypes('visitor');
								foreach ($tmp_account_types as $tmp_account_type)
								{
									if ( $tmp_account_type['Abilities'] )
									{
										$adapted[$tmp_account_type['Key']] = array(
											'ID' => $tmp_account_type['Key'],
											'pName' => 'account_types+name+'. $tmp_account_type['Key']
										);
									}
								}
								$fields[$index]['Values'] = $adapted;
								unset($tmp_account_types, $adapted);
								break;
								
							default:
								$values = explode(',', $fields[$index]['Values']);
								
								if ($fields[$index]['Type'] == 'checkbox')
								{
									$default = explode(',', $fields[$index]['Default']);
									$fields[$index]['Default'] = $default;
								}

								foreach ($values as $row)
								{
									$adapted[$row]['Key'] = $fields[$index]['Key'] . '_' . $row;
									$adapted[$row]['pName'] = $table .'+name+'. $adapted[$row]['Key'];
									$adapted[$row]['ID'] = $row;
								}
								
								$fields[$index]['Values'] = $adapted;
								unset($adapted);
								break;
						}
					}
				}
			}
		}
		unset($data_formats);

		return $fields;
	}
	
	/**
	* check parent with enabled subcategories including | recursive method
	*
	* @param int $id - category id
	* @param array $mode - check in area
	*
	* @return bool
	**/
	function detectParentIncludes( $id, $mode = 'blocks' )
	{
		/* get parent */
		$parent = $this -> getOne('Parent_ID', "`ID` = '{$id}'", 'categories');
		
		if (!empty($parent))
		{
			/* check relations */
			$target = $this -> getOne('ID', "FIND_IN_SET('{$parent}', `Category_ID`) > 0  AND `Status` = 'active' AND `Subcategories` = '1'", $mode);

			if (empty($target))
			{
					return $this -> detectParentIncludes($parent, $mode);
			}
			
			return $parent;
		}
		else
		{
			return false;
		}
	}
	
	/**
	* get blocks
	*
	* @return array - blocks array
	**/
	function getBlocks()
	{
		global $page_info, $config;

		if ( $config['mod_rewrite'] )
		{
			$category_path = $_GET['rlVareables'];

			if( $_GET[$config['mod_rewrite'] ? 'listing_id' : 'id'] )
			{
				$category_path = preg_replace( '/\/[^\/]*$/', '', trim($category_path,'/') );
			}

/*			if ($GLOBALS['config']['lang'] != RL_LANG_CODE)
			{
				$category_path = trim(str_replace($page_info['Path']."/", '/', $category_path), '/');
			}*/

			$this -> rlValid -> sql( $category_path );
			/* get category info */
			$category_id = $this -> getOne('ID', "`Path` = CONVERT('{$category_path}' USING utf8)", 'categories');			
		}
		else
		{
			$category_id = (int)$_GET['category'];
		}

		$category_id = empty($category_id) ? 0 : $category_id;

		if ( !$GLOBALS['config']['ads_module'] )
		{
			$where .= "AND `Key` <> 'advertising' ";
		}

		/* redefine page ID with listing details page ID, 25 in this case */
		if ( $page_info['Controller'] == 'listing_type' && $_GET[$config['mod_rewrite'] ? 'listing_id' : 'id'] )
		{
			$page_info['ID'] = 25;
		}

		if ( $page_info['Controller'] == 'listing_type' )
		{
			if ($additional = $this -> detectParentIncludes($category_id))
			{
				$add_where = "OR (FIND_IN_SET('{$additional}', `Category_ID`) > 0 AND `Subcategories` = '1')";
			}

			$sql = "SELECT `ID`, `Key`, `Side`, `Type`, `Content`, `Tpl` FROM `". RL_DBPREFIX ."blocks`";
			$sql .= "WHERE (`Sticky` = '1' OR ";
			$sql .= "(FIND_IN_SET( '{$page_info['ID']}', `Page_ID` ) > 0 ";

			if( $category_id )
			{
				$sql .= "AND ((`Category_ID` <> '' AND FIND_IN_SET('{$category_id}', `Category_ID`) > 0) OR `Cat_sticky` = '1' {$add_where}) ";
			}
			else
			{
				$sql .= "AND (`Category_ID` = '' ) ";
			}

			$sql .= ") ";

			if( $category_id )
			{
				$sql .= "OR ( `Page_ID` ='' AND ((`Category_ID` <> '' AND FIND_IN_SET('{$category_id}', `Category_ID`) > 0) OR `Cat_sticky` = '1' {$add_where}) ) ";
			}

			$sql .= " ) ";
			$sql .= "AND `Status` = 'active' {$where} ";
			$sql .= "ORDER BY `Position` ";

			$tmp_blocks = $this -> getAll($sql);
		}
		else
		{
			$tmp_blocks = $this -> fetch( array('ID', 'Key', 'Side', 'Type', 'Content', 'Tpl'), array( 'Status' => 'active' ), "AND (FIND_IN_SET( '{$page_info['ID']}', `Page_ID` ) > '0' OR `Sticky` = '1') {$where} ORDER BY `Position`", null, 'blocks' );
		}
		
		if ( !empty($tmp_blocks) )
		{
			foreach ($tmp_blocks as $key => $value)
			{
				$block_keys[$value['Key']] = true;
				
				$blocks[$value['Key']] = $value;
				if ( $value['Type'] == 'html' )
				{
					$blocks[$value['Key']]['Content'] = $GLOBALS['lang']['blocks+content+'.$value['Key']];
				}
			}
			
			unset($tmp_blocks);
			$this -> block_keys = $block_keys;
			
			$blocks = $this -> rlLang -> replaceLangKeys( $blocks, 'blocks', array( 'name' ) );
			$this -> defineBlocksExist( $blocks );
		}

		return $blocks;
	}
	
	/**
	* check messages exist
	*
	* @return array - messages info
	**/
	function checkMessages()
	{
		global $account_info;
		
		$sql = "SELECT COUNT(`ID`) AS `Count` FROM `" . RL_DBPREFIX . "messages` WHERE `To` = '{$account_info['ID']}' AND `Status` = 'new'";
		$out = $this -> getRow( $sql );
		
		return $out['Count'];
	}
	
	/**
	* get children
	*
	* @param int $id - item id
	* @param int $table - table name
	* @param string $status - items status
	* @param string $main_field - main item field
	* @param string $parent_field - parent item field
	* @param string $type - listing type key (in case when we work with categories table)
	*
	* @return array - children items
	**/
	function getChildren( $id, $table = null, $status = 'active', $main_field = 'ID', $parent_field = 'Parent_ID', $type = false )
	{
		global $rlCache, $config;
		
		if ( $table == null )
		{
			if ( $this -> tName != null )
			{
				$table = $this -> tName;
			}
			else 
			{
				return $this -> tableNoSel();
			}
		}
		
		/* get data from cache */
		if ( $table == 'categories' && $config['cache'] )
		{
			/* let's filter result by listing type to decrease data size */
			if ( $type )
			{
				$tmp_items = $rlCache -> get('cache_categories_by_type', $id, $type);
			}
			else
			{
				$tmp_items = $rlCache -> get('cache_categories_by_id');
			}
		}
		/* get data from DB */
		else
		{
			$where_status = $status == 'all' ? '' : "AND `Status` = '{$status}'";
			$tmp_items = $this -> fetch(array($main_field, $parent_field), null, "WHERE `{$main_field}` <> '{$id}' {$where_status}", null, $table);
		}
		
		foreach ($tmp_items as $key => $value)
		{
			$items[$value['ID']] = $value['Parent_ID'];
		}
		unset($tmp_items);
		
		foreach ($items as $key => $item)
		{
			if ( $item )
			{
				if ( $this -> checkRelation($key, $items, $id) )
				{
					$out[] = $key;
				}
			}
		}
		
		return $out;
	}
	
	/**
	* get children | requrcive method
	*
	* @param int $id - main item id
	* @param array $items - all items array
	* @param int $target - target item
	*
	* @return bool - search result
	**/
	function checkRelation($id, $items, $target)
	{
		if ( $parent = $items[$id] )
		{
			if ( $parent == $target )
			{
				return true;
			}
			
			if ( $poss = $items[$parent] )
			{
				if ( $poss == $target )
				{
					return true;
				}
				else
				{
					return $this -> checkRelation( $poss, $items, $target );
				}
			}
		}
		else
		{
			return false;
		}
	}
	
	/**
	* get available hooks
	*
	* @return array - available hooks array
	**/
	function getHooks($id, $items, $target)
	{
		$hooks = $this -> fetch(array('Plugin'), array('Status' => 'active'), "AND `Plugin` <> '' GROUP BY `Plugin`", null, 'hooks');
		foreach ($hooks as $key => $value)
		{
			$out[$hooks[$key]['Plugin']] = true;
		}

		$GLOBALS['aHooks'] = $out;
		$GLOBALS['rlSmarty'] -> assign_by_ref('aHooks', $out);
	}
	
	/**
	* get available hooks
	*
	* @return array - available hooks array
	**/
	function simulateCatBlocks()
	{
		global $rlListingTypes, $blocks, $rlCache, $rlSmarty, $rlCategories, $page_info;
		
		foreach ( $rlListingTypes -> types as $key => $value )
		{
			if ( in_array($page_info['ID'], explode(',', $value['Ablock_pages'])) )
			{
				$cat_blocks[$value['Ablock_position']][] = $key;
				$categories[$key] = $rlCategories -> getCategories(0, $key);
			}
		}

		if ( $cat_blocks )
		{
			$rlSmarty -> assign_by_ref('categories', $categories);
			
			foreach ($cat_blocks as $side => $types)
			{
				if ( $types[0] && $blocks['ltcb_'. $types[0]] )
				{
					$blocks['ltcb_'. $types[0]]['Content'] = '{include file="blocks"|cat:$smarty.const.RL_DS|cat:"categories_block.tpl" types="'. implode(',', $types) .'"}';
				}
				
				if ( count($types) > 1 )
				{
					foreach ($types as $key => $type)
					{
						if ( $key > 0 )
						{
							unset($blocks['ltcb_'. $type]);
						}
					}
				}
			}

			$this -> defineBlocksExist( $blocks );
		}
	}
	
	/**
	* Custom php function strlen
	*
	* @param string &$string - string for checking
	* @param string $sign - sign [<,>,<=,>=,==,!=]
	* @param int $len - length
	*
	* @return bool or false
	**/
	function strLen( &$string, $sign = '<', $len = 3 )
	{
		if ( function_exists('mb_strlen') )
		{
			eval( "\$res = ( mb_strlen( \$string ) {$sign} \$len );" );
			if ( $res ) return true;
		}
		else
		{
			eval( "\$res = ( strlen( \$string ) {$sign} \$len );" );
			if ( $res ) return true;
		}
		return false;
	}
}
