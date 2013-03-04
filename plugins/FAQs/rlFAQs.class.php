<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LISENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: RLFAQS.CLASS.PHP
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

class rlFAQs extends reefless
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
	* @var configurations class object
	**/
	var $rlConfig;
	/**
	* @var calculate faqs
	**/
	var $calc_faqs;
	
	function rlFAQs()
	{
		global $rlLang, $rlValid, $rlConfig;
		
		$this -> rlLang   = & $rlLang;
		$this -> rlValid  = & $rlValid;
		$this -> rlConfig = & $rlConfig;
	}
	
	
	/**
	* get faqs
	*
	* @param int $id - faqs id
	* @param bool $page - page mode
	* @param int $pg - start position
	*
	* @return array - faqs array
	**/
	function get( $id = false, $page = false, $pg = 1 )
	{
		$sql = "SELECT SQL_CALC_FOUND_ROWS `ID`, `ID` AS `Key`, `Date`, `Path` FROM `" . RL_DBPREFIX . "faqs` ";
		$sql .= "WHERE `Status` = 'active' ";
		
		if ( $id )
		{
			$sql .= "AND `ID` = '{$id}'";
		}
		
		$GLOBALS['rlHook'] -> load('rlFAQsGetSql', $sql); // from v4.1.0
		
		$sql .= "ORDER BY `Date` DESC ";
		
		if ( $page === 'block' )
		{
			$sql .= "LIMIT " . $GLOBALS['config']['faqs_block_in_block'];
		}
		else
		{
			$start = 0;
			if( $pg > 1 )
			{
				$start = ($pg-1)*$GLOBALS['config']['faqs_at_page'];
			}
			
			$sql .= "LIMIT {$start}," . $GLOBALS['config']['faqs_at_page'];
		}
		
		if( $id )
		{
			$faqs = $this -> getRow( $sql );
		}
		else
		{
			$faqs = $this -> getAll( $sql );
		}
		
		$faqs_number = $this -> getRow( "SELECT FOUND_ROWS() AS `calc`" );
		$this -> calc_faqs = $faqs_number['calc'];
		
		$faqs = $this -> rlLang -> replaceLangKeys( $faqs, 'faqs', array( 'title', 'content' ) );
		
		return $faqs;
	}
	
	/**
	* delete FAQs
	*
	* @package ajax
	*
	* @param string $id - faq ID
	*
	**/
	function ajaxDeleteFAQs( $id )
	{
		global $_response, $lang;

		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}

		$lang_keys[] = array(
			'Key' => 'faqs+title+' . $id
		);
		$lang_keys[] = array(
			'Key' => 'faqs+content+' . $id
		);

		$GLOBALS['rlActions'] -> delete( array( 'ID' => $id ), array('faqs'), null, null, $id, $lang_keys );

		$del_mode = $GLOBALS['rlActions'] -> action;

		$_response -> script("
			faqsGrid.reload();
			printMessage('notice', '{$lang['faq_' . $del_mode]}');
		");

		return $_response;
	}
	
}
