<?php

/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: RLVALID.CLASS.PHP
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

class rlValid extends reefless
{
	/**
	* escape string by mysql injection (by reference)
	*
	* @param array $data - requested string
	*
	* @return mixed valid data
	**/
	function sql( &$data )
	{
		if ( is_array( $data ))
		{
			foreach ($data as $string => $value)
			{
				$this -> sql($data[$string]);
			}
		}
		else 
		{
			if ( is_array($data) )
			{
				$data = array_map('trim', $data);
				if ( get_magic_quotes_gpc() )
				{
					$data = array_map('stripslashes', $data);
				}
				$data = str_replace("\'", "'", $data);
				$data = array_map('addslashes', $data);
			}
			else
			{
				$data = trim($data);
				if ( get_magic_quotes_gpc() )
				{
					$data = stripslashes($data);
				}
				$data = str_replace("\'", "'", $data);
				$data = addslashes($data);
			}
		}
	}
	
	/**
	* escape string by mysql injection
	*
	* @param array $data - requested string
	*
	* @return mixed valid data
	**/
	function xSql( $data )
	{
		if ( is_array( $data ))
		{
			foreach ($data as $string => $value)
			{
				$data[$string] = $this -> xSql($data[$string]);
			}
		}
		else 
		{
			if ( is_array($data) )
			{
				$data = array_map('trim', $data);
				if ( get_magic_quotes_gpc() )
				{
					$data = array_map('stripslashes', $data);
				}
				$data = str_replace("\'", "'", $data);
				$data = array_map('addslashes', $data);
			}
			else
			{
				$data = trim($data);
				if ( get_magic_quotes_gpc() )
				{
					$data = stripslashes($data);
				}
				$data = str_replace("\'", "'", $data);
				$data = addslashes($data);
			}
		}
		
		return $data;
	}
	
	/**
	* html tags conversion (by reference)
	*
	* @param array $data - requested string
	*
	* @return mixed valid data
	**/
	function html( &$data )
	{
		if ( is_array( $data ))
		{
			foreach ($data as $string => $value)
			{
				//$data[$string] = strip_tags( $data[$string] );
				$data[$string] = htmlspecialchars( $data[$string] );
			}
		}
		else 
		{
			//$data = strip_tags( $data );
			$data = htmlspecialchars( $data );
		}
	}
	
	/**
	* html tags conversion
	*
	* @param array $data - requested string
	*
	* @return mixed valid data
	**/
	function xHtml( $data )
	{
		if ( is_array( $data ))
		{
			foreach ($data as $string => $value)
			{
				if ( !is_array( $data[$string] ) )
				{
					//$data[$string] = strip_tags( $data[$string] );
					$data[$string] = htmlspecialchars( $data[$string] );
				}
			}
		}
		else 
		{
			//$data = strip_tags( $data );
			$data = htmlspecialchars( $data );
		}
		
		return $data;
	}
	
	/**
	* strip javascript tags
	*
	* @param array $data - requested string
	*
	* @return mixed valid data
	**/
	function stripJS( $data )
	{
		if ( is_array( $data ))
		{
			foreach ($data as $string => $value)
			{
				if ( !is_array( $data[$string] ) )
				{
					$data[$string] = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $data[$string]);
					$data[$string] = preg_replace('/[\r\n\t]/is', '', $data[$string]);
				}
			}
		}
		else 
		{
			$data = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $data);
			$data = preg_replace('/[\r\n\t]/is', '', $data);
		}
		
		return $data;
	}

	/**
	* validate e-mail
	*
	* @param string $mail - e-mail address
	*
	* @return bool
	**/
	function isEmail( $mail )
	{
		return (bool)preg_match('/^(?:(?:\"[^\"\f\n\r\t\v\b]+\")|(?:[\w\!\#\$\%\&\'\*\+\-\~\/\^\`\|\{\}]+(?:\.[\w\!\#\$\%\&\'\*\+\-\~\/\^\`\|\{\}]+)*))@(?:(?:\[(?:(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:[0-1]?[0-9]?[0-9]))\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:[0-1]?[0-9]?[0-9]))\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:[0-1]?[0-9]?[0-9]))\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:[0-1]?[0-9]?[0-9])))\])|(?:(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:[0-1]?[0-9]?[0-9]))\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:[0-1]?[0-9]?[0-9]))\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:[0-1]?[0-9]?[0-9]))\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:[0-1]?[0-9]?[0-9])))|(?:(?:(?:[A-Za-z0-9\-])+\.)+[A-Za-z\-]+))$/', $mail);
	}
	
	/**
	* validate URL address
	*
	* @param string $url - url address
	*
	* @return bool
	**/
	function isUrl( $url )
	{
		return (bool)preg_match('/^https?:\/\/[a-z0-9-]{2,63}(?:\.[a-z0-9-]{2,})+(?::[0-9]{0,5})?(?:\/|$|\?)\S*$/', $url);
	}
	
	/**
	* validate domain name
	*
	* @param string $domain - domain name
	*
	* @return bool
	**/
	function isDomain( $domain )
	{
		return (bool)preg_match('/^[^\.]([w]{3}[0-9]?\.?)?[a-zA-Z0-9\-\_\.]{2,68}\.[a-zA-Z0-9]{2,10}$/', $domain);
	}
	
	/**
	* check image extension
	*
	* @param string $extension - file extension
	*
	* @return bool
	**/
	function isImage( $extension )
	{
		// available image extensions
		$available_ext = array( 1 => 'jpg', 2 => 'jpeg', 3 => 'gif', 4 => 'png');

		if ( !array_search( strtolower($extension), $available_ext ) )
		{
			return false;
		}
		return true;
	}
	
	/**
	* check file extension
	*
	* @param string $type      - file type
	* @param string $extension - file extension
	*
	* @return bool
	**/
	function isFile( $type, $extension )
	{
		include_once( RL_LIBS . 'system.lib.php' );

		global $l_file_types;

		// available image extensions
		$available_ext = $l_file_types[$type]['ext'];

		if ( false === strpos( $available_ext, strtolower($extension) ) )
		{
			return false;
		}
		return true;
	}
	
	/**
	* get domain name from url
	*
	* @param string $url - url
	* @param bool $mode - allow local domain names, like: localhost
	*
	* @return string - domain name
	**/
	function getDomain( $url = null, $mode = false )
	{
		return parse_url($url, PHP_URL_HOST);
		
//		$sign = $mode ? '?' : '+';
//		if (preg_match('/^(?:http|https|ftp):\/\/((?:[A-Z0-9][A-Z0-9_-]*)(?:\.[A-Z0-9][A-Z0-9_-]*)+)(:(\d+))?/i', $url, $match))
//		{
//			return $match[1];
//		}
//
//		return false;
	}
	
	/**
	* convert string to key
	*
	* @param string $key - key
	* @param string $replace - replae simbol
	*
	* @return string - valid key
	**/
	function str2key( $key, $replace = '_' )
	{
		$key = preg_replace( '/[^a-zA-Z0-9\+]+/i', $replace, $key );
		//$key = preg_replace( '/\-+/', $replace, $key );
		//$key = strtolower( $key );
		$key = trim( $key, $replace );
	
		return empty($key) ? false : $key;
	}
	
	/**
	* convert string to path
	*
	* @param string $str - string
	*
	* @return string - valid key
	**/
	function str2path( $str, $keep_slashes = false )
	{
		if ( $keep_slashes )
		{
			$rx = '\/';
		}

		loadutf8functions('ascii', 'utf8_to_ascii', 'utf8_is_ascii');

		if ( !utf8_is_ascii( $str ) )
		{
			$str = utf8_to_ascii( $str );
		}

		$str = preg_replace("/[^a-z0-9{$rx}\.]+/i", '-', $str);
		$str = preg_replace('/\-+/', '-', $str);
		$str = strtolower( $str );
		$str = trim($str, '-');
		$str = trim($str, '/');
		$str = trim($str);

		return empty($str) ? false : $str;
	}
	
	/**
	* convert int format to money format
	*
	* @param array $aParams - string
	*
	**/
	function str2money( $aParams )
	{
		$string = is_array($aParams) ? $aParams['string'] : $aParams ;

		$len = strlen($string);
		$string = strrev($string);
		
		if ( strpos($string, '.') )
		{
			$rest = substr($string, 0, strpos($string, '.'));
			$string = substr($string, strpos($string, '.')+1, $len);
			$len -= strlen($rest)+1;
			$rest = strrev(substr(strrev($rest), 0, 2)) . ".";
		}
		elseif ( $GLOBALS['config']['show_cents'] )
		{
			$rest = '00.';
		}
		
		for ( $i = 0; $i <= $len; $i++ )
		{
			$val .= $string[$i];
			if ( (( $i + 1 ) % 3 == 0) && ( $i + 1 < $len ) )
			{
				$val .= $GLOBALS['config']['price_delimiter'];
			}
		}
		
		$val = strrev($rest.$val);
		
		return $val;
	}
	
	/**
	* make key unique
	*
	* @param string $dir - directory to create
	*
	* @return unique key
	*
	**/
	function uniqueKey( $key = false, $table = false, $keyField = 'Key' )
	{
		if ( !$key || !$table )
			return 'key_'. mt_rand();
			
		if ( $this -> getOne($keyField, "`{$keyField}` = '{$key}'", $table) )
		{
			$key .= rand(1, 9);
			return $this -> uniqueKey($key, $table, $keyField);
		}
		else
		{
			return $key;
		}
	}
}
