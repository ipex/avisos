<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LISENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: RLAUTOREGPREVENT.CLASS.PHP
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

class rlAutoRegPrevent extends reefless {

	/**
	* Fields to parse
	**/
	var $parseFields;

	/**
	* Class constructor
	**/
	function rlAutoRegPrevent()
	{
		$this -> parseFields = array('type', 'appears');
	}

	/**
	* Parse XML to assoc Array
	*
	* @param string $xml - xml code
	* @return array $array - assoc array or false
	**/
	function xml2array( $xml = '' ) {
		if ( empty($xml) ) return false;

		$parser = xml_parser_create();
		xml_parse_into_struct($parser, $xml, $vals, $ind);
		xml_parser_free($parser);

		$iteration = $index = 0;
		foreach( $vals as $val )
		{
			$tag = strtolower($val['tag']);

			if ( in_array($tag, $this -> parseFields) )
			{
				$array[$index][$tag] = $val['value'];
				$iteration++;

				if ( $iteration % count($this -> parseFields) == 0 )
				{
					$index++;
				}
			}
		}
		return count($array) ? $array : false;
	}

	/**
	* Save to base
	*
	* @param array $result - 
	* @param array &$formData - 
	* @return bool - true/false
	**/
	function saveToBase( $result = false, &$formData = array() ) {
		global $config, $rlValid;

		$reason = '';
		foreach ( $result as $key => $value )
		{
			if ( $value['appears'] == 'yes' )
			{
				$reason .= "{$value['type']},";
			}
		}
		$reason = trim($reason, ',');

		if ( !empty($reason) )
		{
			$sql  = "INSERT INTO `". RL_DBPREFIX ."reg_prevent` ( `Username`, `Mail`, `IP`, `Reason`, `Date`, `Status` ) VALUES ";
			$sql .= "( '". $rlValid -> xSql($formData['username']) ."', '". $rlValid -> xSql($formData['mail']) ."', '{$_SERVER['REMOTE_ADDR']}', '{$reason}', NOW(), 'block' )";
			$this -> query($sql);

			return true;
		}
		return false;
	}

	/**
	* Check spammers on local base
	*
	* @param string $where - part of sql query
	* @return string/bool - string status or bool false
	**/
	function checkBase( $where = '' ) {
		$result = $this -> getOne('Status', "{$where}", 'reg_prevent');
		return !empty($result) ? $result : false;
	}

	/**
	* Send request to server and check on spammers
	*
	* @param array $formData - registration form data
	* @return bool - true/false
	**/
	function check( &$formData ) {
		global $config, $rlValid;

		$where = '';
		$checkSpamURL = "http://www.stopforumspam.com/api?";
		if ( $config['autoRegPrevent_check_username'] ) 
		{
			$checkSpamURL .= "username={$formData['username']}&";
			$where .= "`Username` = '". $rlValid -> xSql($formData['username']) ."' AND";
		}
		if ( $config['autoRegPrevent_check_email'] ) 
		{
			$checkSpamURL .= "email={$formData['mail']}&";
			$where .= "`Mail` = '". $rlValid -> xSql($formData['mail']) ."' AND";
		}
		if ( $config['autoRegPrevent_check_ip'] ) 
		{
			$checkSpamURL .= "ip={$_SERVER['REMOTE_ADDR']}&";
			$where .= "`IP` = '{$_SERVER['REMOTE_ADDR']}' AND";
		}
		$checkSpamURL = trim($checkSpamURL, '&');
		$where = trim($where, 'AND');

		// check in local db
		if ( false === $dbStatus = $this -> checkBase($where) )
		{
			$xml = $this -> getPageContent($checkSpamURL);
			if ( false !== $result = $this -> xml2array($xml) )
			{
				if ( true === $this -> saveToBase($result, $formData) )
				{
					return false;
				}
			}
		}
		else
		{
			if ( $dbStatus == 'unblock' )
			{
				return true;
			}
			return false;
		}
		return true;
	}

	/**
	* Add new spammers to local database
	*
	* @param string $username - username
	* @param string $email - email
	* @param string $ip - IP
	*
	* @return object $_response
	**/
	function ajaxAddSpamers($username = false, $email = false, $ip = false)
	{
		global $_response, $rlValid, $lang;

		$username = $username ? $rlValid -> xSql($username) : 'N/A';
		$email = $email ? $rlValid -> xSql($email) : 'N/A';
		$ip = $ip ? $rlValid -> xSql($ip) : 'N/A';

		$sql  = "INSERT INTO `". RL_DBPREFIX ."reg_prevent` ( `Username`, `Mail`, `IP`, `Reason`, `Date`, `Status` ) VALUES ";
		$sql .= "( '{$username}', '{$email}', '{$ip}', '{$lang['autoRegPrevent_adminAdded']}', NOW(), 'block' )";
		$this -> query($sql);

		$_response -> script("$('input#arp_username').val('');$('input#arp_email').val('');$('input#arp_ip').val('');");
		$_response -> script("$('input[name=item_submit]').val('{$lang['add']}');autoRegPrevent.reload();");

		return $_response;
	}
}