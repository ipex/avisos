<?php

/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: RLCONFIG.CLASS.PHP
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

class rlConfig extends reefless
{
	/**
	* get configuration value by configuration name
	*
	* @param string - configuration variable name
	* @return string - configuration variable value
	*
	**/
	function getConfig( $name )
	{
		if ( empty($GLOBALS['config']) )
		{
			$output = $this -> fetch( array('Default'), array( 'Key' => $name ), null, null, 'config', 'row' );
			
			return $output['Default'];
		}
		else
		{
			return $GLOBALS['config'][$name];
		}
	}
	
	/**
	* set value for configuration
	*
	* @param string $key   - configuration key
	* @param string $value - new value
	*
	**/
	function setConfig( $key, $value )
	{
		$data = array(
			'fields' => array( 'Default' => $value ),
			'where' => array( 'Key' => $key),
		);
		
		if ($GLOBALS['rlActions'] -> updateOne( $data, 'config' ))
		{
			return true;
		}
		
		return false;
	}
	
	/**
	* get all configuration by group id
	*
	* @param string - configuration group id
	* @return array - mixed
	*
	**/
	function allConfig( $group = null )
	{
		$where = !empty( $group )? array( 'Group_ID' => $group ) : "*";
		$output = $this -> fetch( array( 'Key', 'Default' ), $where, null, null, 'config' );
		
		if ( empty($GLOBALS['config']) )
		{
			foreach ($output as $key => $value)
			{
				$configs[$output[$key]['Key']] = $output[$key]['Default'];
			}
			
			$GLOBALS['config'] = $configs;
			
			return $configs;
		}
		else
		{
			return $GLOBALS['config'];
		}
	}
}