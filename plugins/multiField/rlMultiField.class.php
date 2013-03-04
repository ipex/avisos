<?php


/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LISENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: RLMULTIFIELD.CLASS.PHP
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

class rlMultiField extends reefless 
{	
	/**
	* getNext - get next level 
	*
	* @package ajax
	*
	* @param string $value - value of current level
	* @param string $name - field name 
 	* @param string $form_key - form key which contain the field (for search forms only) 
	* @param int $levels - current number of selectors on the page
	* @param string $type - listing or account - to define target fields 
	* 
	**/

	function ajaxGetNext( $value = false, $name = false, $form_key = false, $levels = false, $type = 'listing', $order_type = false )
	{
		global $_response, $rlDb;

		$post_prefix = $type == 'account' && !defined('REALM') ? 'account' : 'f';
	
		$post_form_dom = $form_key ? 'form:has(input[value='. $form_key .'][name=post_form_key]) ' : '';

		preg_match('/'.$post_prefix.'\[(.*?)(_level([0-9]))?\]/i', $name, $match);

		$top_field = $match[1];
		$level = $match[3] ? $match[3] : 0;
		$next_field = $top_field ."_level". ($level + 1);

		$next_values = $this -> getMDF( $value, $order_type );

		$options = $empty_option = '<option value="0">'. $GLOBALS['lang']['any'] .'</option>';
		foreach( $next_values as $key => $option )
		{
			$options .='<option value="'. $option['Key'] .'">'. $option['name'] .'</option>';
		}

		for( $i=$level+2; $i<=(int)$levels; $i++ )
		{
			$_response -> script("$('". $post_form_dom ."select[name=\"". $post_prefix ."[". $top_field ."_level". $i ."]\"]').attr('disabled', 'disabled').val('". $empty_option ."')");
		}

		$options = $GLOBALS['rlValid'] -> xSql( $options );
		$_response -> script("$('". $post_form_dom ."select[name=\"". $post_prefix ."[". $next_field ."]\"]').html('". $options ."').removeAttr('disabled').removeClass('disabled')");

		return $_response;
	}


	/**
	* build - build related fields
	*
	* @package ajax
	*
	* @param string $last_value - value of last selected field
	* @param string $last_field - key of last selected field
 	* @param string $form_key - form key which contain the field (for search forms only) 
	* @param int $levels - current number of selectors on the page
	* @param string $type - listing or account - to define target fields 
	* 
	**/

	function ajaxBuild( $last_value = false, $last_field = false, $form_key = false, $levels = false, $type = 'listing', $order_type = false )
	{
		global $_response;		

		if( $GLOBALS['geo_filter_data']['geo_url'] && !$last_value )
		{
			if( $type == 'listing' )
			{
				$last_geo = array_slice( $GLOBALS['geo_filter_data']['lfields'], -1, 1);
			}else
			{
				$last_geo = array_slice( $GLOBALS['geo_filter_data']['afields'], -1, 1);
			}
			$last_field = current( array_keys($last_geo) );
			$last_value = current( array_values($last_geo) );
		}

		$post_form_dom = $form_key ? 'form:has(input[value='. $form_key .'][name=post_form_key]) ' : '';
		$post_prefix = $type == 'account' && !defined('REALM') ? 'account' : 'f';

		$tmp = explode('level', $last_field);

		$level = $tmp[1];
		$top_field = trim($tmp[0], '_');

		$parents[] = $last_value;
		$parents = $this -> getParents( $last_value, $parents );

		if( $parents && $last_value )
		{
			$top_value = $parents[count($parents)-1];
			$_response -> script("$('". $post_form_dom ."select[name=\"". $post_prefix ."[". $top_field ."]\"] option[value=". $top_value ."]').attr('selected', 'selected').removeAttr('disabled').removeClass('disabled')");
			$_response -> script("$('". $post_form_dom ."select[name=\"". $post_prefix ."[". $top_field ."]\"]').removeAttr('disabled').removeClass('disabled');");
		}else
		{
			$top_value = 0;
			$_response -> script("$('". $post_form_dom ."select[name=\"". $post_prefix ."[". $top_field ."]\"] option[value=". $top_value ."]').attr('selected', 'selected');");
			$_response -> script("$('". $post_form_dom ."select[name=\"". $post_prefix ."[". $top_field ."]\"]').removeAttr('disabled').removeClass('disabled')");
			
		}

		$empty_option = '<option value="0">'. $GLOBALS['lang']['any'] .'</option>';
		$lev_add = $last_value ? 2 : 1;

		for( $i=$level+$lev_add; $i<=(int)$levels; $i++ )
		{
			$_response -> script("$('". $post_form_dom ."select[name=\"". $post_prefix ."[". $top_field ."_level". $i ."]\"]').attr('disabled', 'disabled').val('". $empty_option ."')");
		}
		
		$level++;
		foreach( $parents as $key => $parent )
		{
			$values = $this -> getMDF( $parent, $order_type );

			$options = $empty_option;
			foreach($values as $opt_key => $option)
			{
				$sel = $option['Key'] == $parents[$key-1] ? 'selected="selected"' : '';

				$options .='<option '. $sel .' value="'. $option['Key'] .'">'. $GLOBALS['rlValid'] -> xSql( $option['name'] ) .'</option>';
			}

			$target = $level == 0 ? $top_field : $top_field. "_level". ($level);

			$_response -> script("$('". $post_form_dom ."select[name=\"". $post_prefix ."[". $target ."]\"]').html('". $options ."').removeAttr('disabled').removeClass('disabled')");
			$level--;
		}

		return $_response;
	}


	/**
	* get parents - get all parents of item 
	*
	* @param string $key - key
	* @param array $parents - parents
	* 
	* @return array 
	*
	**/

	function getParents( $key = false, $parents = false )
	{
		global $rlDb;

		if(!$key)
			return false;

		$sql ="SELECT `T2`.`Key`, `T2`.`Parent_ID` FROM `". RL_DBPREFIX ."data_formats` AS `T1` ";
		$sql .="JOIN `".RL_DBPREFIX."data_formats` AS `T2` ON `T1`.`Parent_ID` = `T2`.`ID` ";
		$sql .="WHERE `T1`.`Key` = '{$key}' ";

		$parent = $rlDb -> getRow($sql);

		if( $parent['Parent_ID'] == 0 && $parent['Key'])
		{
			return $parents;
		}else
		{
			$parents[] = $parent['Key'];
			return $this -> getParents($parent['Key'], $parents);
		}
		
		return $parent;
	}


	/**
	* get all data format
	*
	* @param string $key - format key
	* @param string $order - order type (alphabetic/position)
	*
	* @return array - data formats list
	**/

	function getMDF( $key = false, $order = false, $get_path = false, $path = false, $include_childs = false )
	{
		global $rlCache, $rlLang, $rlDb, $config;

		if ( !$key && !$path )
			return;

		if ( $config['cache'] && $include_childs && !defined('RL_MF_NOCACHE'))
		{
			$df = $this -> getCache();
			if ( $df )
			{
				$df = $GLOBALS['rlLang'] -> replaceLangKeys( $df, 'data_formats', array( 'name' ) );								
				return $df;
			}
			
			return false;
		}

		if( $key )
		{
			$parent_id = $this -> getOne('ID', "`Key` = '{$key}'", 'data_formats');
		}elseif( $path )
		{
			$parent_id = $this -> getOne('ID', "`Path` = '".trim($path, "/")."'", 'data_formats');
		}

		$sql = "SELECT `T1`.`Position`, `T1`.`ID`, `T1`.`Parent_ID`, `T1`.`Key`, `T2`.`Value` AS `name`, ";
		if( $get_path )
		{
			$sql .="CONCAT( `Path`, '/' ) as `Path`, ";
		}
		$sql .="CONCAT('data_formats+name+', `T1`.`Key`) as `pName` FROM `".RL_DBPREFIX."data_formats` AS `T1` ";
		$sql .="LEFT JOIN `".RL_DBPREFIX."lang_keys` AS `T2` ON `T2`.`Key` = CONCAT('data_formats+name+', `T1`.`Key`) AND `T2`.`Code` = '".RL_LANG_CODE."' ";
		$sql .="WHERE `Parent_ID` =".$parent_id." AND `T1`.`Status` = 'active' GROUP BY `T1`.`ID` ";

		if( $order == 'position' )
		{
			$sql .="ORDER BY `Position` ";
		}elseif( $order == 'alphabetic' )
		{
			$sql .="ORDER BY `T2`.`Value` ";
		}
		else
		{
			$sql .="ORDER BY `T1`.`ID`, `T1`.`Key` ";
		}

		$data = $rlDb -> getAll( $sql );

		foreach( $data as $dkey => &$value )
		{
			$GLOBALS['lang'][$value['pName']] = $value['name'];

			if( $include_childs )
			{	
				$value['childs'] = $this -> getMDF( $value['Key'], $order, $get_path, true, $include_childs-1 );
				if( $value['childs'][0]['childs'][0]['ID'] )
				{
					$value['subchilds'] = true;
				}
			}
		}
		
		if ( $order == 'alphabetic' )
		{
			$this -> rlArraySort($data, 'name');
		}

		return $data;
	}

	function cache()
	{
		$geo_format = $GLOBALS['rlDb'] -> getOne("Key", "`Geo_filter` = '1'", "multi_formats");

		if( !$geo_format )
			return false;

		$include_childs = $GLOBALS['config']['mf_geo_block_list'] && $GLOBALS['config']['mf_geo_multileveled'] ? $GLOBALS['config']['mf_geo_levels_toshow'] - 1: false;
		if(!$include_childs)
			return false;

		define('RL_MF_NOCACHE', true);
		$GLOBALS['reefless'] -> loadClass('Cache');
		$GLOBALS['rlCache'] -> file('mf_cache_data_formats');

		$file = RL_CACHE . $GLOBALS['config']['mf_cache_data_formats'];

		$out = $this -> getMDF( $geo_format, $GLOBALS['geo_filter_data']['order_type'], true, false, $include_childs );

		$fh = fopen($file, 'w');
		fwrite($fh, serialize($out)); 
		fclose($fh);
			
		unset($out);
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
	function getCache()
	{	
		global $config;

		$key = 'mf_cache_data_formats';

		$file = RL_CACHE . $config[$key];
				
		if ( empty($config[$key]) || !is_readable($file) )
		{
			return false;
		}
		
		$fh = fopen($file, 'r');
		$content = fread($fh, filesize($file));
		fclose($fh);
		
		$content = unserialize($content);
		
		return $content;
	}

	/**
	* add format item
	*
	* @package ajax
	*
	* @param string $key - key
	* @param array $names - names
	* @param string $status - status
	* @param string $format - parent format
	*
	**/

	function ajaxAddItem($key, $names, $status, $format, $path )
	{
		global $_response, $lang, $insert, $rlValid, $rlDb;

		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}

		loadUTF8functions('ascii', 'utf8_to_ascii', 'unicode');
			
		$key = utf8_is_ascii( $key ) ? $key : utf8_to_ascii($key);
		$item_key = $rlValid -> str2key($key);

		/* check key */
		if ( strlen($item_key) < 3 )
		{
			$errors[] = $lang['incorrect_phrase_key'];
		}
		
		$item_key = $format . '_' . $item_key;

		if ( !utf8_is_ascii( $item_key ) )
		{
			$errors[] = $lang['key_incorrect_charset'];
		}
		
		$key_exist = $rlDb -> getOne('ID', "`Key` = '{$item_key}'", 'data_formats');
		if( !empty($key_exist) )
		{
			$errors[] = str_replace( '{key}', "<b>{$item_key}</b>", $lang['notice_item_key_exist'] );
		}

		$parent_id = $rlDb -> getOne('ID', "`Key` = '{$format}'", 'data_formats');

		/*check path*/
		$head = $this -> getHead( $parent_id );
		$geo_filter = $rlDb -> getOne("Geo_filter", "`Key` = '".$head."'", 'multi_formats');

		if ( $geo_filter )
		{
			$path = $rlValid -> str2path( $path );
			$path = $path ? $path : $key;

			if ( strlen($path) < 3 )
			{
				$errors[] = $lang['mf_path_short'];
			}else
			{
				$parent_path = $rlDb -> getOne('Path', "`Key` = '{$format}'", 'data_formats');
				$item_path = $parent_path ? $parent_path ."/". $path : $path;
	
				$path_exist = $rlDb -> getOne('ID', "`Path` = '{$item_path}'", 'data_formats');
				if( !empty($path_exist) )
				{
					$errors[] = $lang['mf_path_exists'];
				}
			}
		}
		
		/* check names */
		$languages = $GLOBALS['languages'];
		foreach ($languages as $key => $value)
		{
			if( empty($names[$languages[$key]['Code']]) )
			{
				$names[$languages[$key]['Code']] = $names[$GLOBALS['config']['lang']];
			}
			if( empty($names[$languages[$key]['Code']]) )
			{
				$errors[] = str_replace( '{field}', "'<b>{$lang['value']} ({$languages[$key]['name']})</b>'", $lang['notice_field_empty']);
			}
		}

		if ( $errors )
		{	
			$out = '<ul>';
			/* print errors */
			foreach ($errors as $error)
			{
				$out .= '<li>'. $error .'</li>';
			}
			$out .= '</ul>';
			$_response -> script("printMessage('error', '{$out}');");
		}
		else
		{
			$level = $this -> getLevel( $parent_id );
			$module = $level >= 1 ? 'formats' : 'common';

			$max_position = $rlDb -> getOne("Position", "`Parent_ID` = ".$parent_id." ORDER BY `Position` DESC", "data_formats");
			
			$insert = array(
				'Parent_ID' => $parent_id,
				'Key' => $item_key,
				'Status' => $status,
				'Position' => $max_position+1,
				'Plugin' => $level ? 'multiField' : ''
			);

			if( $item_path )
			{
				$insert['Path'] = $item_path;
			}

			/* insert new item */
			if( $GLOBALS['rlActions'] -> insertOne($insert, 'data_formats') )
			{
				if( $level )
				{
					$listing_fields = $this -> createLevelField( $parent_id, 'listing' );
					$account_fields = $this -> createLevelField( $parent_id, 'account' );
				}

				if( $listing_fields || $account_fields )
				{
					$notice_out = '<ul>';
					$notice_out .="<li>".$lang['item_added']."</li>";
					
					foreach( $listing_fields as $k => $field )
					{
						$href = "index.php?controller=listing_fields&action=edit&field=".$field;
						$link = '<a target="_blank" href="'. $href .'">$1</a>';
						$row = preg_replace( '/\[(.+)\]/', $link, $lang['mf_lf_created'] );

						$notice_out .="<li>". $row ."</li>";
					}

					foreach( $account_fields as $k => $field )
					{
						$href = "index.php?controller=account_fields&action=edit&field=".$field;
						$link = '<a target="_blank" href="'. $href .'">$1</a>';
						$row = preg_replace( '/\[(.+)\]/', $link, $lang['mf_af_created'] );
						$notice_out .="<li>". $row ."</li>";
					}
					$notice_out .= '</ul>';
				}		
				
				/* save new item  name */
				foreach ($languages as $key => $value)
				{
					$lang_keys[] = array(
						'Code' => $languages[$key]['Code'],
						'Module' => $module,
						'Key' => 'data_formats+name+'.$item_key,
						'Value' => $names[$languages[$key]['Code']],
						'Plugin' => $level ? 'multiField' : ''
					);
				}
				
				if ($GLOBALS['rlActions'] -> insert($lang_keys, 'lang_keys'))
				{
					$mess = $notice_out ? $notice_out : $lang['item_added'];

					$_response -> script("printMessage('notice', '{$mess}')");

					$_response -> script( "itemsGrid.reload();" );
					$_response -> script( "$('#new_item').slideUp('normal')" );
				}

				if( $GLOBALS['config']['cache'] )
				{
				        $GLOBALS['rlCache'] -> updateDataFormats();
					if( $GLOBALS['config']['mf_geo_multileveled'] )
					{
				              $this -> cache();
			                }
	                	}
			}
		}

		$_response -> script( "$('input[name=item_submit]').val('{$lang['add']}');" );
		
		return $_response;
	}

	/**
	* create field
	*
	* check related fields and add listing fields  
	* if there are no field yet for this level
	*
	* @param int $parent_id 
	* @param string $type - listing or account
	*
	**/

	function createLevelField( $parent_id, $type = 'listing' )
	{
		global $languages, $rlDb;
		
		$out = array();

		$multi_format = $this -> getHead( $parent_id );
		if( !$multi_format )
		{
			return false;
		}

		$format_id = $rlDb -> getOne("ID", "`Key` = '".$multi_format."'", 'data_formats');
		$this -> getLevels( $format_id ); //update levels count in db

		$sql ="SELECT * FROM `".RL_DBPREFIX."{$type}_fields` WHERE `Condition` = '{$multi_format}' AND `Key` NOT REGEXP 'level[0-9]'";
		$related_fields = $rlDb -> getAll($sql);

		if(!$related_fields)
		{
			return false;
		}
	
		$level = $this -> getLevel( $parent_id );
		$level = $level ? $level : 1;
		
		foreach( $related_fields as $rlk => $field )
		{
			$field_key = $field['Key']."_level".$level;
			$prev_fk = $level == 1 ? $field['Key'] : $field['Key']."_level".($level-1);

			$sql ="SHOW FIELDS FROM `".RL_DBPREFIX."{$type}s` WHERE `Field` = '".$field_key."'";
			$field_exists = $rlDb -> getRow( $sql );

			if( !$field_exists )
			{
				$sql ="ALTER TABLE `".RL_DBPREFIX."{$type}s` ADD `".$field_key."` VARCHAR(255) NOT NULL AFTER `".$prev_fk."`";
				$rlDb -> query($sql);

				$sql ="SELECT `Key` FROM `".RL_DBPREFIX."{$type}_fields` WHERE `Key` = '".$field_key."'";
				$field_exists = $rlDb -> getRow( $sql );
			}

			if( !$field_exists )
			{
				$field_info = array(
					'Key' => $field_key,
					'Condition' => $multi_format,
					'Type' => 'select',
					'Status' => 'active'
				);

				if( $type == 'listing' )
				{
					$field_info['Add_page'] = '1';
					$field_info['Details_page'] = '1';
					$field_info['Readonly'] = '1';
				}

				preg_match('/country|location|state|region|province|address/i', $field_key, $match);
				if( $match )
				{						
					$field_info['Map'] = '1';
				}

				if( $GLOBALS['rlActions'] -> insertOne( $field_info, $type."_fields" ) )
				{
					$field_id = mysql_insert_id();

					if( $type == 'listing' )//add entry after the 'parent' field to the search and submit forms
					{
						$prev_field_id = $rlDb -> getOne("ID", "`Key` = '".$prev_fk."'", 'listing_fields');

						$sql ="UPDATE `".RL_DBPREFIX."listing_relations` SET `Fields` = TRIM(BOTH ',' FROM ( REPLACE( CONCAT(',',`Fields`,','), ',{$prev_field_id},', ',{$prev_field_id},{$field_id},'))) WHERE FIND_IN_SET('{$prev_field_id}', `Fields`) ";
						$rlDb -> query( $sql );

						$sql ="UPDATE `".RL_DBPREFIX."search_forms_relations` SET `Fields` = TRIM(BOTH ',' FROM ( REPLACE( CONCAT(',',`Fields`,','), ',{$prev_field_id},', ',{$prev_field_id},{$field_id},'))) WHERE FIND_IN_SET('{$prev_field_id}', `Fields`) ";
						$rlDb -> query( $sql );
					}elseif ( $type == 'account' )
					{
						$prev_field_id = $rlDb -> getOne("ID", "`Key` = '".$prev_fk."'", 'account_fields');
							
						$sql ="SELECT `Category_ID`, `Position`, `Group_ID` FROM `".RL_DBPREFIX."account_submit_form` WHERE `Field_ID` =".$prev_field_id;
						$afields = $rlDb -> getAll( $sql );
						foreach( $afields as $afk => $afield )
						{
							$sql = "UPDATE `".RL_DBPREFIX."account_submit_form` SET `Position` = `Position`+1 ";
							$sql .="WHERE `Position` > ".$afield['Position']." AND `Category_ID` = ".$afield['Category_ID'];
							$rlDb -> query($sql);

							$insert[$afk]['Position'] = $afield['Position']+1;
							$insert[$afk]['Category_ID'] = $afield['Category_ID'];
							$insert[$afk]['Group_ID'] = $afield['Group_ID'];
							$insert[$afk]['Field_ID'] = $field_id;
						}
						$GLOBALS['rlActions'] -> insert($insert, 'account_submit_form');
					}

					foreach ( $languages as $key => $value )
					{
						$lang_keys[] = array(
							'Code' => $languages[$key]['Code'],
							'Module' => 'common',
							'Key' => $type.'_fields+name+'.$field_key,
							'Value' => $GLOBALS['lang'][$type.'_fields+name+'.$field['Key']]." Level ".$level,
							'Plugin' => 'multiField'
						);
					}

					$GLOBALS['rlActions'] -> insert($lang_keys, 'lang_keys');
				}
				$out[] = $field_key;
			}
		}
		$GLOBALS['rlCache'] -> updateForms();
		return $out;
	}


	/**
	* deletes automatically added fields (listing fields and account fields) when you delete multi-format 
	*
	* @param string $format - multi_format key
	* @param string $type - listing or account
	*
	**/

	function deleteFormatChildFields($format, $type ='listing')
	{
		global $rlDb;

		$sql ="SELECT `Key`, `ID` FROM `".RL_DBPREFIX."{$type}_fields` WHERE `Condition` = '{$format}' AND `Key` REGEXP 'level[0-9]'";
		$related_fields = $rlDb -> getAll($sql);

		foreach( $related_fields as $rlk => $field )
		{
			$sql ="DELETE `T1`,`T2` FROM `".RL_DBPREFIX."{$type}_fields` AS `T1` ";
			$sql .="LEFT JOIN `".RL_DBPREFIX."lang_keys` AS `T2` ON (`T2`.`Key` = CONCAT('{$type}_fields+name+', `T1`.`Key`) OR `T2`.`Key` = CONCAT('{$type}_fields+des+', `T1`.`Key`)) ";
			$sql .="WHERE `T1`.`Key` ='".$field['Key']."'";

			$rlDb -> query( $sql );

			if( $type == 'listing' )
			{
				$sql ="UPDATE `".RL_DBPREFIX."listing_relations` SET `Fields` = TRIM(BOTH ',' FROM ( REPLACE( CONCAT(',',`Fields`,','), ',{$field['ID']},', ','))) WHERE FIND_IN_SET('{$field['ID']}', `Fields`) ";
				$rlDb -> query( $sql );
			
				$sql ="UPDATE `".RL_DBPREFIX."search_forms_relations` SET `Fields` = TRIM(BOTH ',' FROM ( REPLACE( CONCAT(',',`Fields`,','), ',{$field['ID']},', ','))) WHERE FIND_IN_SET('{$field['ID']}', `Fields`) ";

				$rlDb -> query( $sql );

				$sql = "DELETE FROM `".RL_DBPREFIX."short_forms` WHERE `Field_ID` = ".$field['ID'];
				$rlDb -> query( $sql );
			}else
			{
				$sql = "DELETE FROM `" . RL_DBPREFIX . "account_search_relations` WHERE `Field_ID` = '{$field['ID']}'";
				$rlDb -> query( $sql );
			
				$sql = "DELETE FROM `" . RL_DBPREFIX . "account_short_form` WHERE `Field_ID` = '{$field['ID']}'";
				$rlDb -> query( $sql );
			
				$sql = "DELETE FROM `" . RL_DBPREFIX . "account_submit_form` WHERE `Field_ID` = '{$field['ID']}'";
				$rlDb -> query( $sql );
			}

			$sql ="SHOW FIELDS FROM `".RL_DBPREFIX."{$type}s` WHERE `Field` = '".$field['Key']."'";
			$field_exists = $rlDb -> getRow( $sql );
			if( $field_exists )
			{
				$sql ="ALTER TABLE `".RL_DBPREFIX."{$type}s` DROP `".$field['Key']."`";
				$rlDb -> query( $sql );
			}
		}
	}


	/**
	* deletes automatically added fields (listing fields and account fields) when you delete field
	*
	* @param string $format - multi_format key
	* @param string $type - listing or account
	*
	**/

	function deleteFieldChildFields( $field_key, $type ='listing' )
	{
		global $rlDb;

		if( !$field_key || !$type )
		{
			return false;
		}

		$sql ="SELECT `Key`, `ID` FROM `".RL_DBPREFIX."{$type}_fields` WHERE `Key` REGEXP '".$field_key."_level[0-9]'";
		$related_fields = $rlDb -> getAll($sql);

		foreach( $related_fields as $rlk => $field )
		{
			$sql ="DELETE `T1`,`T2` FROM `".RL_DBPREFIX."{$type}_fields` AS `T1` ";
			$sql .="LEFT JOIN `".RL_DBPREFIX."lang_keys` AS `T2` ON (`T2`.`Key` = CONCAT('{$type}_fields+name+', `T1`.`Key`) OR `T2`.`Key` = CONCAT('{$type}_fields+des+', `T1`.`Key`)) ";
			$sql .="WHERE `T1`.`Key` ='".$field['Key']."'";

			$rlDb -> query( $sql );

			$sql ="SHOW FIELDS FROM `".RL_DBPREFIX."{$type}s` WHERE `Field` = '".$field['Key']."'";
			$field_exists = $rlDb -> getRow( $sql );

			if( $field_exists )
			{
				$sql ="ALTER TABLE `".RL_DBPREFIX."{$type}s` DROP `".$field['Key']."`";
				$rlDb -> query( $sql );
			}
		}
	}


	/**
	* preparing item editing
	*
	* @package ajax
	*
	* @param string $key - key
	*
	**/

	function ajaxPrepareEdit($key)
	{
		global $_response, $rlDb;
		
		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}
		
		/* get item info */
		$item = $rlDb -> fetch(array('ID', 'Key', 'Status', 'Default', 'Path'), array('Key' => $key), null, 1, 'data_formats', 'row');
		$item['Path'] = current(array_slice( array_reverse(explode("/",$item['Path'])), 0, 1 ));

		$GLOBALS['rlSmarty'] -> assign_by_ref('item', $item);
		
		/* get item names */
		$tmp_names = $rlDb -> fetch( array( 'Code', 'Value' ), array( 'Key' => 'data_formats+name+'.$key ), "AND `Status` <> 'trash'", null, 'lang_keys' );
		foreach ($tmp_names as $k => $v)
		{
			$names[$tmp_names[$k]['Code']] = $tmp_names[$k];
		}
		unset($tmp_names);

		$GLOBALS['rlSmarty'] -> assign_by_ref('names', $names);

		$tpl = RL_PLUGINS.'multiField' . RL_DS . 'admin' . RL_DS . 'edit_format_block.tpl';
		
		$_response -> assign("prepare_edit_area", 'innerHTML', $GLOBALS['rlSmarty'] -> fetch( $tpl, null, null, false ));
		$_response -> script("flynax.tabs();");
		
		return $_response;
	}


	/**
	* edit format item
	*
	* @package ajax
	*
	* @param string $key - key
	* @param array $names - names
	* @param string $status - status
	* @param string $format - parent format
	*
	**/

	function ajaxEditItem($key, $names, $status, $format, $path)
	{
		global $_response, $lang, $update, $rlDb, $rlValid;

		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}

		$rlDb -> setTable('data_formats');
		$item_key = $rlValid -> xSql( trim($key) );

		/*check path*/
		$head = $this -> getHead( $rlDb -> getOne("ID", "`Key` = '".$format."'", 'data_formats') );
		$geo_filter = $rlDb -> getOne("Geo_filter", "`Key` = '".$head."'", 'multi_formats');

		if ( $geo_filter )
		{
			loadUTF8functions('ascii', 'utf8_to_ascii', 'unicode');
			
			$path = utf8_is_ascii( $path ) ? $path : utf8_to_ascii($path);
			$path = $rlValid -> str2key( $path );

			/* check key */
			if ( strlen($path) < 3 )
			{
				$errors[] = $lang['mf_path_short'];
			}else
			{
				$parent_path = $rlDb -> getOne('Path', "`Key` = '{$format}'", 'data_formats');
				$item_path = $parent_path ? $parent_path ."/". $path : $path;
	
				$path_exist = $rlDb -> getOne('Key', "`Path` = '{$item_path}'", 'data_formats');
	
				if( !empty($path_exist) && $path_exist != $key)
				{
					$errors[] = $lang['mf_path_exists'];
				}
			}
		}

		/* check names */
		$languages = $GLOBALS['languages'];
		foreach ($languages as $key => $value)
		{
			if (empty($names[$languages[$key]['Code']]))
			{
				$errors[] = str_replace( '{field}', "'<b>{$lang['value']} ({$languages[$key]['name']})</b>'", $lang['notice_field_empty']);
			}
		}

		if( $errors )
		{
			$out = '<ul>';
			/* print errors */
			foreach ($errors as $error)
			{
				$out .= '<li>'. $error .'</li>';
			}
			$out .= '</ul>';
			$_response -> script("printMessage('error', '{$out}');");
		}
		else
		{

			$update = array(
				'fields' => array(
					'Status' => $status,
					'Path' => $item_path
				),
				'where'	=> array(
					'Key' => $item_key
				)
			);
			
			/* update item */
			if ($GLOBALS['rlActions'] -> updateOne($update, 'data_formats'))
			{
				/* update item name */
				foreach ($languages as $key => $value)
				{
					if ( $rlDb -> getOne('ID', "`Key` = 'data_formats+name+{$item_key}' AND `Code` = '{$languages[$key]['Code']}'", 'lang_keys') )
					{
						$lang_keys[] = array(
							'fields' => array(
								'Value' => $names[$languages[$key]['Code']]
							),
							'where'	=> array(
								'Code' => $languages[$key]['Code'],
								'Key' => 'data_formats+name+'.$item_key
							)
						);
					}
					else
					{
						$insert_phrase[] = array(
							'Module' => 'common',
							'Value' => $names[$languages[$key]['Code']],
							'Code' => $languages[$key]['Code'],
							'Key' => 'data_formats+name+'.$item_key
						);
					}
				}
				
				$action = false;
				
				if ( !empty($lang_keys) )
				{
					$action = $GLOBALS['rlActions'] -> update($lang_keys, 'lang_keys');
				}
				if ( !empty($insert_phrase) )
				{
					$action = $GLOBALS['rlActions'] -> insert($insert_phrase, 'lang_keys');
				}
				
				if ( $action )
				{
					if( $GLOBALS['config']['cache'] )
					{
					$GLOBALS['rlCache'] -> updateDataFormats();
						if( $GLOBALS['config']['mf_geo_multileveled'] )
						{
							$this -> cache();
						}
					}

					$_response -> script("printMessage('notice', '{$lang['item_edited']}')");

					$_response -> script( "itemsGrid.reload()" );
					$_response -> script( "$('#edit_item').slideUp('normal')" );
				}
				else
				{
					trigger_error( "Can't edit data_format item, MySQL problems.", E_USER_WARNING );
					$GLOBALS['rlDebug'] -> logger("Can't edit data_format item, MySQL problems.");
				}
			}
		}

		$_response -> script( "$('input[name=item_edit]').val('{$lang['edit']}')" );
		
		return $_response;
	}


	/**
	* add format item
	*
	* @package ajax
	*
	* @param string $key - item key
	*
	**/

	function ajaxDeleteItem( $key = '', $only_childs = false )
	{
		global $_response, $lang, $rlDb, $rlValid;
		
		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}
		
		$key = $rlValid -> xSql( strtolower(trim($key)) );

		$item = $rlDb -> fetch( array('ID', 'Parent_ID'), array('Key' => $key), null, null, 'data_formats', 'row' );

		$sql ="DELETE `T1`, `T2` FROM `".RL_DBPREFIX."data_formats` AS `T1` ";
		$sql .="LEFT JOIN `".RL_DBPREFIX."lang_keys` AS `T2` ON `T2`.`Key` = CONCAT('data_formats+name+', `T1`.`Key`) ";

		$sql .="WHERE `T1`.`Key` LIKE '{$key}";
		if( $only_childs )
		{
			$sql .="_";
		}
		$sql .="%' ";

		$rlDb -> query($sql);
		
/*		$sql = "SELECT `ID` FROM `".RL_DBPREFIX."data_formats` WHERE `Parent_ID` = ".$item['ID'];
		$child_t = $rlDb -> getAll( $sql );
		foreach( $child_t as $ck => $cv ){
			$child .= $cv['ID'].",";
		}

		if( !$only_childs )
		{

			$rlDb -> query("DELETE FROM `".RL_DBPREFIX."data_formats` WHERE `Key` = '{$key}' LIMIT 1");
		

			$rlDb -> query("DELETE FROM `".RL_DBPREFIX."lang_keys` WHERE `Key` = 'data_formats+name+{$key}'");
		}
		
		$this -> deleteChildItems( rtrim($child, ",") ); //delete all child items 
 */		
		if( $GLOBALS['config']['cache'] )
		{
		$GLOBALS['rlCache'] -> updateDataFormats();

			if( $GLOBALS['config']['mf_geo_multileveled'] )
			{
		$this -> cache();
			}
		}

		$GLOBALS['rlHook'] -> load('apPhpFormatsAjaxDeleteItem');

		$_response -> script("printMessage('notice', '{$lang['item_deleted']}')");
		$_response -> script( "$('#loading').fadeOut('normal');" );
		
		$_response -> script( "itemsGrid.reload()" );
		$_response -> script( "$('#edit_item').slideUp('normal');" );
		$_response -> script( "$('#new_item').slideUp('normal');" );

		return $_response;
	}

	
	/**
	* delete format 
	*
	* @package ajax
	*
	* @param string $key - key
	*
	**/

	function ajaxDeleteFormat( $key )
	{
		global $_response, $lang, $rlDb, $rlValid;

		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}

		/* delete item */
		$rlDb -> query("DELETE FROM `".RL_DBPREFIX."multi_formats` WHERE `Key` = '{$key}' LIMIT 1");
		$format_id = $rlDb -> getOne("ID", "`Key` ='".$key."'", 'data_formats');

		if( $format_id )
		{
			$sql = "SELECT `ID` FROM `".RL_DBPREFIX."data_formats` WHERE `Parent_ID` = ".$format_id;
			$child_t = $rlDb -> getAll( $sql );
			foreach( $child_t as $ck => $cv ){
				$child .= $cv['ID'].",";
			}

			$sql = "SELECT `ID` FROM `".RL_DBPREFIX."data_formats` WHERE FIND_IN_SET(`Parent_ID`, '".rtrim( $child, ",")."')";
			$child_t = $rlDb -> getAll( $sql );
			$child = '';
			foreach( $child_t as $ck => $cv ){
				$child .= $cv['ID'].",";
			}

			$this -> deleteChildItems( rtrim( $child, ",") ); //delete all child items except 1st level (which Data Entries can use)

			$this -> deleteFormatChildFields($key, 'listing');
			$this -> deleteFormatChildFields($key, 'account');

			$GLOBALS['rlCache'] -> updateDataFormats();
			$GLOBALS['rlCache'] -> updateForms();

			$GLOBALS['rlHook'] -> load('apPhpFormatsAjaxDeleteItem');
		}

		$_response -> script("printMessage('notice', '{$lang['item_deleted']}')");
		$_response -> script( "$('#loading').fadeOut('normal');" );
		
		$_response -> script( "multiFieldGrid.reload()" );
		$_response -> script( "$('#edit_item').slideUp('normal');" );
		$_response -> script( "$('#new_item').slideUp('normal');" );

		return $_response;
	}

	
	/**
	* delete child items | recursive method
	*
	* @param int $parent_ids -  parent_ids
	*
	* @return boolean
	**/

	function deleteChildItems( $ids )
	{
		global $rlDb;

		if(!$ids)
			return false;		

		/*get childs for next recursion*/
		$sql = "SELECT `ID` FROM `".RL_DBPREFIX."data_formats` WHERE FIND_IN_SET(`Parent_ID`, '".$ids."')";
		$child_t = $rlDb -> getAll( $sql );
		$child = '';
		foreach( $child_t as $ck => $cv ){
			$child .= $cv['ID'].",";
		}

		/*delete current level items and langs*/
		$sql = "DELETE `T1`, `T2` FROM `".RL_DBPREFIX."data_formats` AS `T1` ";
		$sql .="LEFT JOIN `".RL_DBPREFIX."lang_keys` AS `T2` ON `T2`.`Key` = CONCAT('data_formats+name+', `T1`.`Key`) ";
		$sql .="WHERE FIND_IN_SET(`T1`.`ID`, '".$ids."')";
		
		$rlDb -> query( $sql );

		if( $child )
		{
			return $this -> deleteChildItems( rtrim( $child, ",") );
		}else
		{
			return true;
		}
	}


	/**
	* get bread crumbs | recursive method
	*
	* @param int $parent_id -  parent_id
	*
	* @return array  
	**/
	
	function getBreadCrumbs( $parent_id = false, $bc = false )
	{
		$sql = "SELECT `T1`.`ID`, `T1`.`Parent_ID`, `T1`.`Key`, `T2`.`Value` AS `name` FROM `".RL_DBPREFIX."data_formats` AS `T1` ";
		$sql .="LEFT JOIN `".RL_DBPREFIX."lang_keys` AS `T2` ON CONVERT( `T2`.`Key` USING utf8) = CONCAT('data_formats+name+', `T1`.`Key`) AND `T2`.`Code` = '".RL_LANG_CODE."' ";
		$sql .="WHERE `T1`.`Status` = 'active' AND `T1`.`ID` = '".$parent_id."'";

		$info = $GLOBALS['rlDb'] -> getRow($sql);

		if ( !empty($info) )
		{
			$bc[]  = $info;
		}
		else
		{
			$bc = false;
		}

		if (!empty($info['Parent_ID']))
		{
			return $this -> getBreadCrumbs( $info['Parent_ID'], $bc );
		}
		else
		{
			return $bc;
		}
	}


	/**
	* 
	* get level of item
	*
	* @param int $id - id
	* @param int $level - level
	*
	* @return int
	*
	**/

	function getLevel( $id, $level )
	{
		if( !$id )
			return false;

		$parent = $GLOBALS['rlDb'] -> getOne("Parent_ID", "`ID`=".$id, "data_formats");

		if( $parent )
		{
			$level++;
			return $this -> getLevel( $parent, $level );
		}
		else
		{
			return $level;
		}
	}


	/**
	*
	* get total levels of the format
	*
	* @param int $id - id
	* @param int $levels - levels
	*
	* @return int
	*
	**/

	function getLevels( $id, $updatedb = true )
	{
		global $rlDb;

		if( !$id )
			return false;
		
		$i = 2;

		$sql = "SELECT `T{$i}`.`ID` FROM `".RL_DBPREFIX."data_formats` AS `T1` ";
		$sql_join .= "LEFT JOIN `".RL_DBPREFIX."data_formats` AS `T2` ON `T2`.`Parent_ID` = `T1`.`ID` ";
		$sql2 = "WHERE `T1`.`Parent_ID` = ".$id." ";
		$sql3 = "ORDER BY `T2`.`ID` DESC LIMIT 1";

		$tmp = $rlDb -> getRow( $sql . $sql_join . $sql2 . $sql3 );
		
		if( $tmp['ID'] )
		{
			$levels = 1;

			while( $tmp['ID'] )
			{
				$i++;
				$levels++;

				$sql = "SELECT `T{$i}`.`ID` FROM `".RL_DBPREFIX."data_formats` AS `T1` ";
				$sql_join .="LEFT JOIN `".RL_DBPREFIX."data_formats` AS `T{$i}` ON `T{$i}`.`Parent_ID` = `T".($i-1)."`.`ID`";
				$sql3 ="ORDER BY `T{$i}`.`ID` DESC LIMIT 1";

				$tmp = $rlDb -> getRow( $sql . $sql_join . $sql2 . $sql3 );
			}

			if( $updatedb )
			{
				$sql ="UPDATE `".RL_DBPREFIX."multi_formats` SET `Levels` = ".$levels." WHERE `Key` = '".$this -> getHead( $id )."'";
				$rlDb -> query($sql);
			}

			return $levels;
		}

		
		return 0;
	}


	/**
	* 
	* get top level element key of the data/multi format
	*
	* @param int $id - id
	* @param string $key - key
	*
 	* @return string
	*
	**/

	function getHead( $id, $key )
	{

		if( !$id && !$key )
			return false;

		if($id)
		{
			$parent = $GLOBALS['rlDb'] -> getOne("Parent_ID", "`ID`=".$id, "data_formats");
		}elseif($key)
		{
			$parent = $GLOBALS['rlDb'] -> getOne("Parent_ID", "`Key`='".$key."'", "data_formats");
		}else
		{
			return false;
		}

		if( $parent )
		{
			return $this -> getHead( $parent );
		}
		else
		{	
			return $GLOBALS['rlDb'] -> getOne("Key", "`ID`=".$id, "data_formats");
		}
	}


	/**
	* create sub fields
	*
	* @param array $field_info - field info
	* @param string $type - type
	*
	**/

	function createSubFields( $field_info, $type = 'listing' )
	{
		global $rlDb;

		if( strpos($field_info['key'], 'level') || !$field_info['key'])
		{
			return false;
		}

		$format_id = $rlDb -> getOne("ID", "`Key` = '".$field_info['data_format']."'", 'data_formats');

		$head_field_key = $field_info['key'];

		if( !$format_id )
			return false;

		$levels = $this -> getLevels( $format_id );

		if( $levels < 2 )
			return false;

		for( $level=1; $level < $levels; $level++ )
		{
			$field_key = $head_field_key."_level".$level;
			$prev_fk = $level == 1 ? $head_field_key : $head_field_key."_level".($level-1);

			$sql ="SHOW FIELDS FROM `".RL_DBPREFIX."{$type}s` WHERE `Field` = '".$field_key."'";
			$field_exists = $rlDb -> getRow( $sql );

			if( !$field_exists )
			{
				$sql ="SELECT `Key` FROM `".RL_DBPREFIX."{$type}_fields` WHERE `Key` = '".$field_key."'";
				$field_exists = $rlDb -> getRow( $sql );
			}

			if( !$field_exists )
			{
				$sql ="ALTER TABLE `".RL_DBPREFIX."{$type}s` ADD `".$field_key."` VARCHAR(255) NOT NULL AFTER `".$prev_fk."`";
				$rlDb -> query($sql);

				$field_insert_info = array(
					'Key' => $field_key,
					'Condition' => $field_info['data_format'],
					'Type' => 'select',
					'Status' => 'active'
				);

				if( $type == 'listing' )
				{
					$field_insert_info['Add_page'] = 1;
					$field_insert_info['Details_page'] = 1;
					$field_insert_info['Readonly'] = 1;
				}
				
				preg_match('/country|location|state|region|province|address/i', $head_field_key, $match);
				if( $match )
				{						
					$field_insert_info['Map'] = 1;
				}

				if( $GLOBALS['rlActions'] -> insertOne( $field_insert_info, $type."_fields" ) )
				{
					$field_id = mysql_insert_id();

					if( $type == 'listing' )//add entry after the 'parent' field to the search and submit forms
					{
						$prev_field_id = $rlDb -> getOne("ID", "`Key` = '".$prev_fk."'", 'listing_fields');

						$sql ="UPDATE `".RL_DBPREFIX."listing_relations` SET `Fields` = TRIM(BOTH ',' FROM ( REPLACE( CONCAT(',',`Fields`,','), ',{$prev_field_id},', ',{$prev_field_id},{$field_id},'))) WHERE FIND_IN_SET('{$prev_field_id}', `Fields`) ";
						$rlDb -> query( $sql );

						$sql ="UPDATE `".RL_DBPREFIX."search_forms_relations` SET `Fields` = TRIM(BOTH ',' FROM ( REPLACE( CONCAT(',',`Fields`,','), ',{$prev_field_id},', ',{$prev_field_id},{$field_id},'))) WHERE FIND_IN_SET('{$prev_field_id}', `Fields`) ";
						$rlDb -> query( $sql );
					}elseif ( $type == 'account' )
					{
						$prev_field_id = $rlDb -> getOne("ID", "`Key` = '".$prev_fk."'", 'account_fields');
							
						$sql ="SELECT `Category_ID`, `Position`, `Group_ID` FROM `".RL_DBPREFIX."account_submit_form` WHERE `Field_ID` =".$prev_field_id;
						$afields = $rlDb -> getAll( $sql );
						foreach( $afields as $afk => $afield )
						{
							$sql = "UPDATE `".RL_DBPREFIX."account_submit_form` SET `Position` = `Position`+1 ";
							$sql .="WHERE `Position` > ".$afield['Position']." AND `Category_ID` = ".$afield['Category_ID'];
							$rlDb -> query($sql);

							$insert[$afk]['Position'] = $afield['Position']+1;
							$insert[$afk]['Category_ID'] = $afield['Category_ID'];
							$insert[$afk]['Group_ID'] = $afield['Group_ID'];
							$insert[$afk]['Field_ID'] = $field_id;
						}
						$GLOBALS['rlActions'] -> insert($insert, 'account_submit_form');
					}

					$head_field_lkey = $type.'_fields+name+'.$head_field_key;

					foreach ( $GLOBALS['languages'] as $key => $value )
					{
						$head_field_name = $rlDb -> getOne("Value", "`Key` ='{$head_field_lkey}' AND `Code` = '".$GLOBALS['languages'][$key]['Code']."'", "lang_keys");
						$lang_keys[] = array(
							'Code' => $GLOBALS['languages'][$key]['Code'],
							'Module' => 'common',
							'Key' => $type.'_fields+name+'.$field_key,
							'Value' => $head_field_name." Level ".$level,
							'Plugin' => 'multiField'
						);
					}

					$GLOBALS['rlActions'] -> insert($lang_keys, 'lang_keys');
				}
			}
		}
		$GLOBALS['rlCache'] -> updateForms();
	}


	/**
	* delete sub fields
	*
 	* @param array $field_info - field info
	* @param string $type - type
	*
	**/

	function deleteSubFields( $field_info, $type = 'listing' )
	{
		global $rlDb;

		if( strpos($field_info['key'], 'level') )
		{
			return false;
		}

		$field_key = $field_info['key'];

		if( !$field_key )
		{
			return false;
		}

		$old_format = $rlDb -> getOne("Condition", "`Key` = '".$field_key."'", $type.'_fields');
		
		$sql ="SELECT * FROM `".RL_DBPREFIX."listing_fields` WHERE `Condition` = '".$old_format."' AND `Key` REGEXP '".$field_key."_level[0-9]'";
		$fields = $rlDb -> getAll( $sql );

		if( !$fields )
		{
			$sql ="SHOW FIELDS FROM `".RL_DBPREFIX."{$type}s` WHERE `Field` REGEXP '".$field_key."_level[0-9]'";
			$fields_struct = $rlDb -> getAll( $sql );

			foreach( $fields_struct as $key => $field )
			{
				$sql ="ALTER TABLE `".RL_DBPREFIX."{$type}s` DROP `".$field['Field']."`";
				$rlDb -> query( $sql );
			}
		}

		foreach( $fields as $key => $field )
		{
			$sql ="ALTER TABLE `".RL_DBPREFIX."{$type}s` DROP `".$field['Key']."`";
			$rlDb -> query( $sql );

			if( $type == 'listing' )
			{
				$sql ="UPDATE `".RL_DBPREFIX."listing_relations` SET `Fields` = TRIM(BOTH ',' FROM ( REPLACE( CONCAT(',',`Fields`,','), ',{$field['ID']},', ','))) WHERE FIND_IN_SET('{$field['ID']}', `Fields`) ";
				$rlDb -> query( $sql );
			
				$sql ="UPDATE `".RL_DBPREFIX."search_forms_relations` SET `Fields` = TRIM(BOTH ',' FROM ( REPLACE( CONCAT(',',`Fields`,','), ',{$field['ID']},', ','))) WHERE FIND_IN_SET('{$field['ID']}', `Fields`) ";
				$rlDb -> query( $sql );

				$sql = "DELETE FROM `".RL_DBPREFIX."short_forms` WHERE `Field_ID` = ".$field['ID'];
				$rlDb -> query( $sql );
			}elseif( $type == 'account' )
			{
				$sql = "DELETE FROM `" . RL_DBPREFIX . "account_search_relations` WHERE `Field_ID` = '{$field['ID']}'";
				$rlDb -> query( $sql );
			
				$sql = "DELETE FROM `" . RL_DBPREFIX . "account_short_form` WHERE `Field_ID` = '{$field['ID']}'";
				$rlDb -> query( $sql );
			
				$sql = "DELETE FROM `" . RL_DBPREFIX . "account_submit_form` WHERE `Field_ID` = '{$field['ID']}'";
				$rlDb -> query( $sql );
			}
		}

		$sql = "DELETE `T1`, `T2` FROM `".RL_DBPREFIX."{$type}_fields` AS `T1` ";
		$sql .="LEFT JOIN `".RL_DBPREFIX."lang_keys` AS `T2` ON `T2`.`Key` = CONCAT('{$type}_fields+name+', `T1`.`Key`) ";
		$sql .="WHERE `T1`.`Condition` = '".$old_format."' AND `T1`.`Key` REGEXP '".$field_key."_level[0-9]'";

		$rlDb -> query($sql);

		$GLOBALS['rlCache'] -> updateForms();
	}


	/**
	* adapt form 
	* 
	* @param array $form - fields form
	*
 	* @return array
	*
	**/

	function adaptForm( $form )
	{
		global $rlDb;

		foreach( $form as $fk => $group )
		{
			foreach( $group['Fields'] as $grk => $field )
			{
				if( $GLOBALS['multi_formats'][ $field['Condition'] ] && strpos($field['Key'], 'level') > 0)
				{
					preg_match('/(.*)_level([0-9])/i', $field['Key'], $match);

					if( $top_field = $match[1] )
					{
						$level = $match[2];
						$prev_field = $level > 1 ? $top_field.($level-1) : $top_field;

						if( $_POST['f'][$prev_field] )
						{
							$prev_value = $_POST['f'][$prev_field];
						}else
						{
							$prev_value = $rlDb -> getOne('Default', "`Key` = '".$field['Condition']."'", 'multi_formats');
						}

						if( $prev_value )
						{
							$format_values = $this -> getMDF( $prev_value, 'alphabetic' );
						}

						$form[$fk]['Fields'][$grk]['Values'] = $format_values;
					}
				}
			}
		}
	
		return $form;
	}

	/**
	* rebuild multi fields - rebuild sub fields 
	*
	* @package ajax
	*
	* @param string $self - button id 
	* @param string $mode - can be false or delete_existing
	* 
	**/

	function ajaxRebuildMultiField($self, $mode = false)
	{
		global $_response, $lang, $rlDb;

		// check admin session expire
		if ( $this -> checkSessionExpire() === false && !$direct )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}

		$sql = "SELECT * FROM `".RL_DBPREFIX."multi_formats` WHERE 1 ";
		$multi_formats = $rlDb -> getAll( $sql );
		
		foreach( $multi_formats as $key => $format)
		{
			$sql ="SELECT `Condition` as `data_format`, `Key` as `key` FROM `".RL_DBPREFIX."listing_fields` WHERE `Condition` = '{$format['Key']}' AND `Key` NOT REGEXP 'level[0-9]'";
			$related_listing_fields = $rlDb -> getAll($sql);

			foreach($related_listing_fields as $rfKey => $rfield )
			{
				if( $mode == 'delete_existing' )
				{
					$this -> deleteSubFields($rfield, 'listing');
				}
				$this -> createSubFields( $rfield, 'listing' );
			}
		
			$sql ="SELECT `Condition` as `data_format`, `Key` as `key` FROM `".RL_DBPREFIX."account_fields` WHERE `Condition` = '{$format['Key']}' AND `Key` NOT REGEXP 'level[0-9]'";
			$related_account_fields = $rlDb -> getAll($sql);

			foreach($related_account_fields as $rfKey => $rfield )
			{
				if( $mode == 'delete_existing' )
				{
					$this -> deleteSubFields( $rfield, 'account' );
				}
				$this -> createSubFields( $rfield, 'account' );
			}
		}

		$_response -> script( "printMessage('notice', '{$lang['mf_fields_rebuilt']}')" );
		$_response -> script( "$('{$self}').val('{$lang['rebuild']}');" );

		return $_response;		
	}

	/**
	* 
	* getFData - get data from flynax source server 
	* 
	* @param array $params - params to get data
 	* @return json string
	*
	**/

	function getFData( $params )
	{
		set_time_limit(0);
		$this -> time_limit = 0;

		$vps = "http://205.234.232.103/~flsource/getdata.php?nv&domain={$GLOBALS['license_domain']}&license={$GLOBALS['license_number']}";  // vps4

		foreach( $params as $k => $p )
		{
			$vps .="&".$k."=".$p;
		}

		$content = $this -> getPageContent( $vps );

		$GLOBALS['reefless'] -> loadClass("Json");

		return $GLOBALS['rlJson'] -> decode( $content );
	}

	/**
	* ajaxListSources - lists available on server databases
	* 
	* @package ajax
	*
	**/

	function ajaxListSources()
	{
		global $_response;

		$data = $this -> getFData( array("listdata" => true) );
		$GLOBALS['rlSmarty'] -> assign( "data", $data );

		$tpl = RL_PLUGINS.'multiField' . RL_DS . 'admin' . RL_DS . 'flsource.tpl';
		$_response -> assign("flsource_container", 'innerHTML', $GLOBALS['rlSmarty'] -> fetch( $tpl, null, null, false ));
		$_response -> script("$('#flsource_container').removeClass('block_loading');");
		$_response -> script("$('#flsource_container').css('height', 'auto').fadeIn('normal')");
		

		return $_response;	
	}

	/**
	* ajaxExpandSource - lists available data items
	* 
	* @package ajax
	*
	**/
	function ajaxExpandSource( $table )
	{
		global $_response;

		$data = $this -> getFData( array("table" => $table) );
		$GLOBALS['rlSmarty'] -> assign('topdata', $data);
		$GLOBALS['rlSmarty'] -> assign('table', $table);

		$tpl = RL_PLUGINS.'multiField' . RL_DS . 'admin' . RL_DS . 'flsource.tpl';
		$_response -> assign("flsource_container", 'innerHTML', $GLOBALS['rlSmarty'] -> fetch( $tpl, null, null, false ));
		$_response -> script("$('#flsource_container').fadeIn('normal')");
		$_response -> script("$('html, body').animate({ scrollTop: $('#flsource_container').offset().top-25 }, 'slow');");
		$_response -> call('handleSourceActs');

		return $_response;
	}

	/**
	* ajaxImportSource - imports data from server
	* 
	* @package ajax
	*
	**/
	function ajaxImportSource( $parents = '', $table = false, $one_ignore = false, $resume = false )
	{
		global $_response;

		if( !$resume )
		{
			if( empty($parents) )
			{
				$data = $this -> getFData( array("table" => $table) );
				$parents = "";
				foreach($data as $val)
				{
					$parents .= $val -> Key . ",";
				}
			}

			$one_ignore = !empty($one_ignore) && $one_ignore != "false" ? 1 : 0;
			$parents = explode( ",", trim($parents, ",") );

			unset( $_SESSION['mf_parent_ids'] );
			$_SESSION['mf_import']['total'] = count($parents);
			$_SESSION['mf_import']['parents'] = $parents;
			$_SESSION['mf_import']['table'] = $table;
			$_SESSION['mf_import']['one_ignore'] = $one_ignore;
			$_SESSION['mf_import']['top_key'] = $_GET['parent'];
			$_SESSION['mf_import']['parent_id'] = $GLOBALS['rlDb'] -> getOne("ID", "`Key` = '".$_GET['parent']."'", "data_formats");
			$_SESSION['mf_import']['per_run'] = $GLOBALS['config']['mf_import_per_run'];
			$_SESSION['mf_import']['available_rows'] = count($parents);
		}

		$_response -> script("$('#load_cont').fadeOut();");
		if( $parents )
		{
			$_response -> script("$('body').animate({ scrollTop: $('#flsource_container').offset().top-90 }, 'slow', function() { importExport.start(); });");
		}else
		{
			$_response -> script("$('body').animate({ scrollTop: $('#flsource_container').offset().top-90 }, 'slow');");
			$_response -> script("printMessage('error', 'nothing selected')");
		}

		return $_response;
	}


	/**
	* ajax rebuildPath - paths rebuilding 
	* 
	* @package ajax
	*
	**/
	function ajaxRebuildPath( $self )
	{
		global $_response;
		
		$geo_format = $GLOBALS['rlDb'] -> getOne("Key", "`Geo_filter` = '1'", "multi_formats");
		$format = $GLOBALS['rlDb'] -> fetch(array("ID", "Key"), array("Key" => $geo_format ), null, null, "data_formats", "row");

		if( $format )
		{
			$this -> updatePath( $format );
			$message = $GLOBALS['lang']['mf_geo_path_rebuilt'];
		}else
		{
			$message = $GLOBALS['lang']['mf_geo_path_nogeo'];
		}

		if( $GLOBALS['config']['cache'] && $GLOBALS['config']['mf_geo_multileveled'] )
		{
			$this -> cache();
		}

		$_response -> script("printMessage('notice', '{$message}')");
		$_response -> script( "$('{$self}').val('{$GLOBALS['lang']['mf_refresh']}');" );

		return $_response;
	}

	/**
	* ajaxRebuildPath - paths rebuilding, recursive function
	* 
	*
	**/
	function updatePath( $parent )
	{
		$items = $GLOBALS['rlDb'] -> fetch( array("Key", "ID"), array("Parent_ID" => $parent['ID']), null, null, "data_formats" );

		foreach( $items as $key => $item )
		{
			$path = $parent['Path'] ? $parent['Path']."/" : '';
			$path .= $GLOBALS['rlValid'] -> str2path( str_replace($parent['Key']."_", "", $item['Key']) );

			$sql ="UPDATE `".RL_DBPREFIX."data_formats` SET `Path` = '".$path."' WHERE `ID` = ".$item['ID'];
			$GLOBALS['rlDb'] -> query($sql);

			$item['Path'] = $path;
			$this -> updatePath( $item );
		}
	}

	/**
	* ajaxGeoGetNext - get items to next selector
	* 
	* @package ajax
	*
	**/
	function ajaxGeoGetNext( $path = false, $level = 0, $levels )
	{
		global $_response, $rlDb, $geo_format;

		$level = $level ? $level : 0;

		$next_values = $this -> getMDF( $key, $GLOBALS['geo_filter_data']['order_type'], true, $path );
		
		$options = $empty_option = '<option value="0">'. $GLOBALS['lang']['any'] .'</option>';
		foreach( $next_values as $key => $option )
		{
			$options .='<option value="'. $option['Path'] .'">'. $option['name'] .'</option>';
		}

		for( $i=$level+2; $i<=(int)$levels; $i++ )
		{
			$_response -> script("$('#geo_selector_level". $i ."').attr('disabled', 'disabled').val('". $empty_option ."')");
		}
		
		$options = $GLOBALS['rlValid'] -> xSql( $options );
		$target = "geo_selector_level".($level+1);

		$_response -> script("$('#".$target."').html('". $options ."').removeAttr('disabled').removeClass('disabled')");

		return $_response;
	}


	/**
	* build - build related fields
	*
	* @package ajax
	*
	* @param string $last_value - value of last selected field
	* @param string $last_field - key of last selected field
 	* @param string $form_key - form key which contain the field (for search forms only) 
	* @param int $levels - current number of selectors on the page
	* @param string $type - listing or account - to define target fields 
	* 
	**/

	function ajaxGeoBuild( $last_value = false, $last_field = false, $form_key = false )
	{
		global $_response, $geo_filter_data;

		$target = "geo_selector";
		$path1 = $GLOBALS['rlDb'] -> getOne("Path", "`Key` = '".$geo_filter_data['location'][0]['Key']."'", 'data_formats');
		$_response -> script( "$('#geo_selector').val('".$path1."/')" );

		$empty_option = '<option value="0">'. $GLOBALS['lang']['any'] .'</option>';
		$level = 1;

		foreach( $geo_filter_data['location'] as $key => $item)
		{
			$values = $this -> getMDF( $item['Key'], $geo_filter_data['order_type'], true );

			$target = "geo_selector_level".$level;

			$options = $empty_option;
			foreach($values as $opt_key => $option)
			{
				$sel = $option['Key'] == $geo_filter_data['location'][$key+1]['Key'] ? 'selected="selected"' : '';
				$options .='<option '. $sel .' value="'. $option['Path'] .'">'. $option['name'] .'</option>';
			}
			$_response -> script("$('#". $target ."').html('". $options ."').removeAttr('disabled').removeClass('disabled')");
			$level++;		
		}

		return $_response;
	}

	/**
	* prepareGet - get variables preparation
	*
	* @return array
	*
	**/
	function prepareGet()
	{
		global $rlDb, $geo_format;

		if( !$_GET['page'] && !isset($_GET['reset_location']))
		{
			return false;
		}

		if( strlen($_GET['page']) == 2 )
		{
			$lang = $_GET['page'];

			$tmp = explode("/", $_GET['rlVareables']);
			$page = array_splice($tmp, 0, 1);

			if( $rlDb -> getOne("Key", "`Path` = '".$page[0]."'", 'data_formats') )
			{
				$_GET['page'] = $page[0];
				$_GET['rlVareables'] = implode("/", $tmp);
			}else
			{
				return false;
			}
		}

		if( $_GET['page'] && $location[0] = $rlDb -> getOne("Key", "`Path` = '".$_GET['page']."'", 'data_formats') )
		{
			$page_old = $_GET['page'];
			$tmp_vars = $tmp_vars_old = explode("/", trim( $_GET['rlVareables'], "/" ));
			$tmp_vars = $this -> prepareGetVars( $geo_format, $_GET['page'], $tmp_vars, 1, $location );

			$tmp_vars = array_values( $tmp_vars );

			if( $lang )
			{
				$_GET['page'] = $lang;
			}else
			{
				$_GET['page'] = $tmp_vars[0];
				unset($tmp_vars[0]);			
			}
			
			$loc_url = $page_old . "/". implode("/", array_diff( $tmp_vars_old, array_merge( array(0 => $_GET['page']), $tmp_vars ) ));

			$_GET['rlVareables'] = '';
			foreach( $tmp_vars as $key => $value )
			{
				$_GET['rlVareables'] .= $value."/";
			}
	
			$_GET['rlVareables'] = trim( $_GET['rlVareables'], "/");
		}elseif( $lang )
		{
			$_GET['page'] = $lang;
		}

		$lfields = $rlDb -> fetch( array("Key"), array('Condition' => $geo_format, 'Status' => 'active'), "ORDER BY `Key`", null, 'listing_fields' );
		foreach( $lfields as $key => $field )
		{
			$out['lfields_list'][] = $field['Key'];

			preg_match('/(.*)(_level([0-9]))/si', $field['Key'], $match);
			
			if( !$match[3] && $location[0])
			{
				$out['lfields'][$field['Key']] = $location[0];
			}elseif( $location[$match[3]] )
		 	{
				$out['lfields'][$field['Key']] = $location[$match[3]];
			}
		}

		$afields = $rlDb -> fetch( array("Key"), array('Condition' => $geo_format, 'Status' => 'active'), "ORDER BY `Key`", null, 'account_fields' );
		foreach( $afields as $key => $field )
		{
			preg_match('/(.*)(_level([0-9]))/si', $field['Key'], $match);

			if( !$match[3] && $location[0])
			{
				$out['afields'][$field['Key']] = $location[0];
			}elseif( $location[$match[3]] )
			{
				$out['afields'][$field['Key']] = $location[$match[3]];
			}
		}

		foreach( $rlDb -> fetch(array("Path"), array("Geo_exclude" => '1'), null, null, 'pages') as $page )
		{
			$out['clean_pages'][] = $page['Path'];
		}

		$out['order_type'] = $rlDb -> getOne("Order_type", "`Key` = '".$geo_format."'", "data_formats");
		$out['location'] = $location[0] ? $location : $_SESSION['geo_location'];
		$out['geo_url'] = trim($loc_url, "/");

		if( isset($_GET['reset_location']) )
		{
			unset($_SESSION['geo_url']);
			unset($_SESSION['geo_location']);
			$out['geo_url'] = '';
			$out['location'] = '';
		}
		elseif( !$out['geo_url'] && !isset($_GET['reset_location']))
		{
			$out['geo_url'] = $_SESSION['geo_url'];
		}else
		{
			$_SESSION['geo_url'] = $out['geo_url'];
			$_SESSION['geo_location'] = $out['location'];
		}

		return $out;
	}

	/**
	* prepareGet - get variables preparation, recursive function
	*
	* @return array
	*
	**/
	function prepareGetVars( $geo_format, $page, $tmp_vars, $level = 1, &$location = false )
	{
		global $rlDb;

		if( $location[$level] = $rlDb -> getOne("Key", "`Path` = '".$page."/".$tmp_vars[0]."'", 'data_formats') )
		{
			$page = $page."/".$tmp_vars[0];
			unset( $tmp_vars[0] );

			$level++;

			return $this -> prepareGetVars( $geo_format, $page, array_values($tmp_vars), $level, $location );
		}else
		{
			unset($location[$level]);
		}

		return $tmp_vars;
	}

	/**
	* adaptCategories - recount categories depending of current location
	*
	* @param array $categories - categories 
	*
	* @return array
	*
	**/
	function adaptCategories( $categories )
	{
		global $geo_filter_data;

		if( !$geo_filter_data['geo_url'] )
			return $categories;

		foreach( $categories as $key => &$category )
		{
			$sql = "SELECT COUNT(`T1`.`ID`) AS `Count` FROM `" . RL_DBPREFIX . "listings` AS `T1` ";
			$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_plans` AS `T2` ON `T1`.`Plan_ID` = `T2`.`ID` ";
			$sql .= "LEFT JOIN `" . RL_DBPREFIX . "categories` AS `T3` ON `T1`.`Category_ID` = `T3`.`ID` ";
			$sql .= "WHERE (`T1`.`Category_ID` = '{$category['ID']}' OR FIND_IN_SET('{$category['ID']}', `Crossed`) > 0 ";

			if ( $GLOBALS['config']['lisitng_get_children'] )
			{
				$sql .= "OR FIND_IN_SET('{$category['ID']}', `T3`.`Parent_IDs`) > 0 ";
			}

			$sql .= ") AND `T1`.`Status` = 'active' ";
			$sql .= "AND (UNIX_TIMESTAMP(DATE_ADD(`T1`.`Pay_date`, INTERVAL `T2`.`Listing_period` DAY)) > UNIX_TIMESTAMP(NOW()) OR `T2`.`Listing_period` = 0) ";

			foreach( $geo_filter_data['lfields'] as $field => $value )
			{
				$sql .="AND `T1`.`{$field}` = '{$value}' ";
			}
			
			$cat_listings = $GLOBALS['rlDb'] -> getRow( $sql );

			$category['Count'] = $cat_listings['Count'];
		}

		return $categories;
	}

	/**
	* getGeoBlockData - gets data to geo filtering box
	*
	* @param string $geo_format - key of format used for geo filtering
	*
	* @return array
	*
	**/
	function getGeoBlockData( $geo_format )
	{
		global $rlDb, $config;

		$this -> detectLocation();

		$include_childs = $config['mf_geo_block_list'] && $config['mf_geo_multileveled'] ? $config['mf_geo_levels_toshow'] - 1: false;

		$start = 0;		

		if( $config['mf_geo_block_list'] && !$config['mf_geo_multileveled'] && $GLOBALS['geo_filter_data']['location'][0] )
		{
			$parent = current(array_slice( $GLOBALS['geo_filter_data']['location'], -1, 1 ));	
		}else
		{
			$parent = $geo_format;
		}

		$data = $this -> getMDF( $parent, $GLOBALS['geo_filter_data']['order_type'], true, false, $include_childs );

		$tmp_paths = explode("/", $GLOBALS['geo_filter_data']['geo_url']);

		foreach( $GLOBALS['geo_filter_data']['location'] as $key => $item )
		{
			if( $item )
			{
				$tmp[$key]['Key'] = $item;
				$tmp[$key]['name'] = $GLOBALS['lang']['data_formats+name+'.$item] ? $GLOBALS['lang']['data_formats+name+'.$item] : $rlDb -> getOne("Value", "`Key` = 'data_formats+name+".$item."'", "lang_keys");
				$tmp_path2 = array_slice($tmp_paths, 0, $key);
				$tmp[$key]['prev_path'] = $tmp_path2 ? implode("/", $tmp_path2)."/" : "";
			}
		}

		$GLOBALS['geo_filter_data']['location'] = $tmp;

		return $data;
	}

	/**
	* 
	* seoBaseHook - hook code for geo filtering module
	* 
	**/

	function seoBaseHook()
	{
		global $geo_filter_data, $bPath;

		if( !$GLOBALS['geo_format'] )
		{
			return false;
		}

		$_SERVER['REQUEST_URI'] = str_replace('?reset_location', '', $_SERVER['REQUEST_URI']);

		if( $geo_filter_data['geo_url'] )
		{
			$tmp = trim( preg_replace( '/'.str_replace("/", "\/", $geo_filter_data['geo_url']).'(\/)?/i', "[geo_url]", $_SERVER['REQUEST_URI']), "/" );
		}else
		{
			if( $GLOBALS['config']['lang'] != RL_LANG_CODE )
			{
				$tmp = RL_LANG_CODE."/";
			}
			
			$tmp .= '[geo_url]';
			$tmp .= trim(str_replace(RL_LANG_CODE,'', $_SERVER['REQUEST_URI']), "/");
		}

		$geo_filter_data['clean_url'] = RL_URL_HOME . $tmp;

		$geo_filter_data['bPath'] = $bPath;

		if( $geo_filter_data['geo_url'] )
		{
			$bPath .= $geo_filter_data['geo_url'] . "/";
		}
	}

	/**
	* detectLocation 
	*
	**/
	function detectLocation()
	{
		global $rlDb, $rlValid, $geo_filter_data;

		if( $_GET['q'] == 'ext' || $_POST['xjxfun'] || !$GLOBALS['config']['mf_geo_autodetect'] || isset($_GET['reset_location']) || $_COOKIE['mf_geo_detected'] || $GLOBALS['rlMobile'] -> isMobile )
		{
			if(isset($_GET['reset_location']))
			{
				setcookie( 'mf_geo_detected', true, time()+360, '/' );
			}
			return false;
		}

		$exclude = $rlDb -> getOne("Geo_exclude", "`Path` = '".$_GET['page']."'", 'pages');	

		if( !$_COOKIE['mf_geo_loc'] && $geo_filter_data['geo_url'] || ( $_COOKIE['mf_geo_loc'] && !$_COOKIE['PHPSESSID'] && $_COOKIE['mf_geo_loc'] != $geo_filter_data['geo_url'] ) )
		{
			//first time but location in url or cookie location different with url location. rewrite cookie
			$expire_time = time()+( 86400 * $GLOBALS['config']['mf_geo_cookie_lifetime'] );
			setcookie( 'mf_geo_loc', $geo_filter_data['geo_url'], $expire_time, '/' );
			setcookie( 'mf_geo_detected', true, time()+360, '/' );
		}elseif( !$geo_filter_data['geo_url'] && $_COOKIE['mf_geo_loc'] )
		{
			//cookie exists but location not in url or session
			$_SERVER['REQUEST_URI'] = str_replace('?reset_location', '', $_SERVER['REQUEST_URI']);

			$tmp = '[geo_url]';
			$tmp .= trim($_SERVER['REQUEST_URI'], "/");

			if( !$rlDb -> getOne("ID", "`Path` = '".$_COOKIE['mf_geo_loc']."'", "data_formats") )
			{
				return false;
			}

			if( $exclude )
			{
				$_SESSION['geo_url'] = $_COOKIE['mf_geo_loc'];
				$GLOBALS['reefless'] -> redirect();
			}
			else
			{
				$redirect_url = str_replace( '[geo_url]', $_COOKIE['mf_geo_loc'], RL_URL_HOME . $tmp );
				$GLOBALS['reefless'] -> redirect( null, $redirect_url );
			}
		}
		elseif( !$_COOKIE['mf_geo_loc'] && !$geo_filter_data['geo_url'] && !$_COOKIE['mf_geo_detected'] )
		{
			$country_path = $rlValid -> str2path( $_SESSION['GEOLocationData'] -> Country_name );
			$region_path = $rlValid -> str2path( $_SESSION['GEOLocationData'] -> Region );
			$city_path = $rlValid -> str2path( $_SESSION['GEOLocationData'] -> City );

			$full_path = '';
			if( $rlDb -> getOne("ID", "`Path` = '".$country_path."'", "data_formats") )
			{
				$ip_path = $country_path;
				$full_path .= $country_path;
			}elseif( $rlDb -> getOne("ID", "`Path` = '".$full_path."/".$region_path."'", "data_formats") )
			{
				$full_path .="/".$region_path;
				$ip_path = $full_path;
			}
			if( $rlDb -> getOne("ID", "`Path` = '".$full_path."/".$city_path."'", "data_formats") )
			{
				$ip_path = $full_path ."/". $city_path;
			}

			$ip_path = preg_replace('/\/+/', '/', $ip_path);

			if( !$ip_path )
			{
				return false;
			}
			$ip_path .="/";

			$expire_time = time()+( 86400 * $GLOBALS['config']['mf_geo_cookie_lifetime'] );
			setcookie( 'mf_geo_loc', $ip_path, $expire_time, '/' );
			setcookie( 'mf_geo_detected', true, $expire_time, '/' );

			if( $exclude )
			{					
				$_SESSION['geo_url'] = $ip_path;
				$GLOBALS['reefless'] -> redirect();
			}
			else
			{
				$GLOBALS['reefless'] -> redirect( null, RL_URL_HOME . $ip_path );
			}			
		}
	}

	function adaptPageInfo()
	{
		global $geo_filter_data;

		$k=1;
		foreach( $geo_filter_data['location'] as $key => $litem )
		{
			$loc_all .= $litem['name']." / ";

			$pattern[] = '{location_level'.$k.'}';
			$replacement[] = $litem['name'];

			$k++;
		}
		$loc_all = trim($loc_all, " / ");
		$pattern[] = '{location}';
		$replacement[] = $loc_all;

		$areas = array('name', 'meta_description', 'meta_keywords', 'meta_title');

		foreach( $areas as $area )
		{
			if( $GLOBALS['page_info'][ $area ] )
			{
				$GLOBALS['page_info'][ $area ] = str_replace( $pattern, $replacement, $GLOBALS['page_info'][$area ] );
				$GLOBALS['page_info'][ $area ] = preg_replace('/\{if location\}(.*?)\{\/if\}/smi', $geo_filter_data['location'] ? '\\1' : '', $GLOBALS['page_info'][ $area ]);
			}
		}
	}

	function adaptPageTitle( $title )
	{
		$k=0;
		foreach( $GLOBALS['geo_filter_data']['location'] as $key => $litem )
		{
			$loc_all .= $litem['name']." / ";

			$pattern[] = '{location_level'.$k.'}';
			$replacement[] = $litem['name'];

			$k++;
		}
		$loc_all = trim($loc_all, " / ");
		$pattern[] = '{location}';
		$replacement[] = $loc_all;

		foreach( $title as $key => $item )
		{
			if( $title[$key] )
			{
				$title[ $key ] = str_replace( $pattern, $replacement, $title[$key] );
				$title[ $key ] = preg_replace('/\{if location\}(.*?)\{\/if\}/smi', $GLOBALS['geo_filter_data']['location'] ? '\\1' : '', $title[$key]);
			}
		}

		return $title;
	}
}
