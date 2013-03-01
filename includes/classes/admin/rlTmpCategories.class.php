<?php

/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: RLTMPCATEGORIES.CLASS.PHP
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

class rlTmpCategories extends reefless 
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
	* @var actions class object
	**/
	var $rlActions;
	
	/**
	* @var notice class object
	**/
	var $rlNotice;
	
	/**
	* class constructor
	**/
	function rlTmpCategories()
	{
		global $rlLang, $rlActions, $rlNotice, $rlValid;
		
		$this -> rlLang  = & $rlLang;
		$this -> rlValid = & $rlValid;
		$this -> rlActions = & $rlActions;
		$this -> rlNotice = & $rlNotice;
	}
	
	/**
	* delete category
	*
	* @package ajax
	*
	* @param int $id - custom category ID
	*
	**/
	function ajaxDeleteTmpCategory( $id = false )
	{
		global $_response, $lang, $rlListingTypes, $config, $pages, $rlSmarty;

		if ( !$id )
			return $_response;
		
		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}

		/* inform category owner */
		$tmp_category = $this -> fetch(array('Parent_ID', 'Account_ID', 'Name', 'Listing_ID'), array('ID' => $id), null, 1, 'tmp_categories', 'row');
		$category = $this -> fetch(array('Type', 'Path', 'ID'), array('ID' => $tmp_category['Parent_ID']), null, 1, 'categories', 'row');
		
		$this -> loadClass('Account');
		$this -> loadClass('Mail');
		$this -> loadClass('Listings');
		
		$owner_info = $GLOBALS['rlAccount'] -> getProfile((int)$tmp_category['Account_ID']);
		
		$mail_tpl = $GLOBALS['rlMail'] -> getEmailTemplate('custom_category_deleted');
		$mail_tpl['body'] = str_replace(array('{category_name}', '{username}'), array($tmp_category['Name'], $owner_info['Full_name']), $mail_tpl['body']);
		
		if ( $tmp_category['Listing_ID'] )
		{
			$listing_info = $this -> fetch('*', array('ID' => $tmp_category['Listing_ID']), null, 1, 'listings', 'row');
			$listing_type = $rlListingTypes -> types[$category['Type']];
			$listing_title = $GLOBALS['rlListings'] -> getListingTitle($category['ID'], $listing_info, $category['Type']);
			
			$link = RL_URL_HOME;
			$link .= $config['mod_rewrite'] ? $pages[$listing_type['Page_key']] .'/'. $category['Path'] .'/'. $rlSmarty -> str2path($listing_title) .'-'. $listing_info['ID'] .'.html' : 'index.php?page='. $pages[$listing_type['Page_key']] .'&amp;id='. $listing_info['ID'] ;
			$link = '<a href="'. $link .'">'. $listing_title .'</a>';
			
			$mail_tpl['body'] = str_replace( array('{link}', '{if listing}', '{/if}'), array($link, '', ''), $mail_tpl['body'] );
		}
		else
		{
			$mail_tpl['body'] = preg_replace('/\{if listing\}(.*)\{\/if\}/', '', $mail_tpl['body']);
		}
		
		$GLOBALS['rlMail'] -> send( $mail_tpl, $owner_info['Mail'] );
		
		/* deelte category */
		$this -> rlActions -> delete( array( 'ID' => $id ), array('tmp_categories'), null, 1);
		
		$del_mode = $this -> rlActions -> action;

		$_response -> script("printMessage('notice', '{$lang['category_' . $del_mode]}');");
		
		$_response -> script("categoriesGrid.reload();");
		
		return $_response;
	}

	/**
	* activate custom category
	*
	* @package ajax
	*
	* @param int $id - custom category ID
	*
	**/
	function ajaxActivateTmpCategory( $id )
	{
		global $_response, $rlListingTypes, $pages, $lang, $rlCache, $config, $rlSmarty;
		
		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}
		
		$id = (int)$id;
		
		$sql = 'SELECT `T1`.*, `T2`.`ID` AS `Category_ID`, `T2`.`Position`, `T2`.`Path`, `T2`.`Level`, `T2`.`Type`, `T2`.`Tree` AS `Parent_Tree` ';
		$sql .=	'FROM `'.RL_DBPREFIX.'tmp_categories` AS `T1` ';
		$sql .= 'LEFT JOIN `'.RL_DBPREFIX.'categories` AS `T2` ON `T1`.`Parent_ID` = `T2`.`ID` ';
		$sql .= 'WHERE `T1`.`ID` = "'.$id.'" LIMIT 1';

		$tmp_category = $this -> getRow($sql);
		
		/* get listing type */
		$listing_type = $rlListingTypes -> types[$tmp_category['Type']];
		
		$max_position = $this -> getRow ("SELECT MAX(`Position`) AS `Max` FROM `".RL_DBPREFIX."categories` WHERE `Parent_ID` = {$tmp_category['Parent_ID']}" );
		$max_position = $max_position['Max']+1;
		
		/* load the utf8 lib */
		loadUTF8functions('ascii', 'utf8_to_ascii', 'unicode');
		
		$key = $tmp_category['Name'];
		
		/* check name */
		if ( !utf8_is_ascii( $key ) )
		{
			$key = utf8_to_ascii( $tmp_category['Name'] );
		}
		
		$path = $this -> rlValid -> str2path($key);
		$tc_key = 'custom_' . $this -> rlValid -> str2key($key);

		if ( $tmp_category['Listing_ID'] )
		{
			$l_sql = "SELECT `T1`.`ID` FROM `". RL_DBPREFIX ."listings` AS `T1` ";
			$l_sql .= "LEFT JOIN `". RL_DBPREFIX ."listing_plans` AS `T2` ON `T1`.`Plan_ID` = `T2`.`ID` ";
			$l_sql .= "WHERE UNIX_TIMESTAMP(DATE_ADD(`T1`.`Pay_date`, INTERVAL `T2`.`Listing_period` DAY)) > UNIX_TIMESTAMP(NOW()) AND `T1`.`Status` = 'active' ";
			$l_sql .= "AND `T1`.`ID` = '{$tmp_category['Listing_ID']}' ";
			$l_sql .= "LIMIT 1";
			
			$listing_active = $this -> getRow($l_sql);
		}
		
		if ( $tmp_category['Parent_ID'] )
		{
			$this -> loadClass('Categories');
			
			$parent_ids[] = $tmp_category['Parent_ID'];
			if ( $parents = $GLOBALS['rlCategories'] -> getParentIDs($tmp_category['Parent_ID']) )
			{
				$parent_ids = array_merge($parent_ids, $parents);
			}
			$parent_ids = implode(',', $parent_ids);
		}
		
		$insertData = array(
			'Position' => $max_position,
			'Path' => $tmp_category['Path'].'/'.$path,
			'Level' => $tmp_category['Level']+1,
			'Parent_ID' => $tmp_category['Parent_ID'],
			'Parent_IDs' => $parent_ids,
			'Type' => $tmp_category['Type'],
			'Tree' => $tmp_category['Parent_Tree'] .'.'. $max_position,
			'Key' => $tc_key,
			'Count' => empty($listing_active) ? 0 : 1,
			'Lock' => 0,
			'Modified' => 'NOW()',
			'Status' => 'active'
		);

		$this -> rlActions -> insertOne($insertData, 'categories');
		
		$insert_category_id = mysql_insert_id();

		if( $tmp_category['Listing_ID'] && $insert_category_id )
		{
			$updateData = array(
				'fields' => array('Category_ID' => $insert_category_id),
				'where' => array('ID' => $tmp_category['Listing_ID'])
			);
			
			$this -> rlActions -> updateOne( $updateData, 'listings');
		}
		
		$allLangs = $GLOBALS['languages'];

		foreach ($allLangs as $key => $value)
		{
			$lang_keys[] = array(
				'Code' => $allLangs[$key]['Code'],
				'Module' => 'common',
				'Status' => 'active',
				'Key' => 'categories+name+' . $tc_key,
				'Value' => $tmp_category['Name']
			);
		}
		
		$this -> rlActions -> insert( $lang_keys, 'lang_keys' );

		$this -> loadClass('Mail');
		$this -> loadClass('Account');
		$this -> loadClass('Listings');
		
		/* inform category owner */
		$owner_info = $GLOBALS['rlAccount'] -> getProfile((int)$tmp_category['Account_ID']);
		$cat_postfix = $listing_type['Cat_postfix'] ? '.html' : '/';
		
		$category_path = $this -> getOne('Path', "`ID` = '{$insert_category_id}'", 'categories');
		$category_location = RL_URL_HOME;
		$category_location .= $config['mod_rewrite'] ? $pages[$listing_type['Page_key']] .'/'. $category_path . $cat_postfix : 'index.php?page='.$pages[$listing_type['Page_key']].'&amp;category='.$insert_category_id ;
		$category_location = '<a href="'.$category_location.'">'.$category_location.'</a>';
		
		$mail_tpl = $GLOBALS['rlMail'] -> getEmailTemplate( 'custom_category_activated' );
		$mail_tpl['body'] = str_replace( array('{category_name}', '{category_location}', '{username}'), array($tmp_category['Name'], $category_location, $owner_info['Full_name']), $mail_tpl['body'] );
		
		if ( $tmp_category['Listing_ID'] )
		{
			$listing_info = $this -> fetch('*', array('ID' => $tmp_category['Listing_ID']), null, 1, 'listings', 'row');
			$listing_title = $GLOBALS['rlListings'] -> getListingTitle($tmp_category['Category_ID'], $listing_info, $tmp_category['Type']);
			
			$link = RL_URL_HOME;
			$link .= $config['mod_rewrite'] ? $pages[$listing_type['Page_key']] .'/'. $tmp_category['Path'] .'/'. $rlSmarty -> str2path($listing_title) .'-'. $tmp_category['Listing_ID'] .'.html' : 'index.php?page='. $pages[$listing_type['Page_key']] .'&amp;id='. $tmp_category['Listing_ID'] ;
			$link = '<a href="'. $link .'">'. $listing_title .'</a>';
			
			$mail_tpl['body'] = str_replace( array('{link}', '{if listing}', '{/if}'), array($link, '', ''), $mail_tpl['body'] );
		}
		else
		{
			$mail_tpl['body'] = preg_replace('/\{if listing\}(.*)\{\/if\}/', '', $mail_tpl['body']);
		}
		
		$GLOBALS['rlMail'] -> send( $mail_tpl, $owner_info['Mail'] );
		
		/* remove tmp category */
		$dsql = "DELETE FROM `".RL_DBPREFIX."tmp_categories` WHERE `ID` = '{$tmp_category['ID']}' LIMIT 1";
		$this -> query($dsql);
	
		$rlCache -> updateCategories();
		
		$_response -> script("
			categoriesGrid.reload();
			printMessage('notice', '{$lang['category_activated']}');
		");

		return $_response;
	}
}
