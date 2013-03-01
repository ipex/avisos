<?php

/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: RLNEWS.CLASS.PHP
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

class rlNews extends reefless
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
	* @var calculate news
	**/
	var $calc_news;
	
	/**
	* class constructor
	**/
	function rlNews()
	{
		global $rlLang, $rlValid, $rlConfig;
		
		$this -> rlLang   = & $rlLang;
		$this -> rlValid  = & $rlValid;
		$this -> rlConfig = & $rlConfig;
	}
	
	/**
	* get news
	*
	* @param int $id - news id
	* @param bool $page - page mode
	* @param int $pg - start position
	*
	* @return array - news array
	**/
	function get( $id = false, $page = false, $pg = 1 )
	{
		$sql = "SELECT SQL_CALC_FOUND_ROWS `ID`, `ID` AS `Key`, `Date`, `Path` FROM `" . RL_DBPREFIX . "news` ";
		$sql .= "WHERE `Status` = 'active' ";
		
		if ( $id )
		{
			$sql .= "AND `ID` = '{$id}'";
		}
		
		$GLOBALS['rlHook'] -> load('rlNewsGetSql', $sql); // from v4.1.0
		
		$sql .= "ORDER BY `Date` DESC ";
		
		if ( !$page )
		{
			$sql .= "LIMIT " . $GLOBALS['config']['news_block_news_in_block'];
		}
		else
		{
			$start = 0;
			if( $pg > 1 )
			{
				$start = ($pg-1)*$GLOBALS['config']['news_at_page'];
			}
			
			$sql .= "LIMIT {$start}," . $GLOBALS['config']['news_at_page'];
		}

		if( $id )
		{
			$news = $this -> getRow( $sql );
		}
		else
		{
			$news = $this -> getAll( $sql );
		}
		
		$news_number = $this -> getRow( "SELECT FOUND_ROWS() AS `calc`" );
		$this -> calc_news = $news_number['calc'];
		
		$news = $this -> rlLang -> replaceLangKeys( $news, 'news', array( 'title', 'content', 'meta_description', 'meta_keywords' ) );
		
		return $news;
	}
}