<?php

/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: REEFLESS.CLASS.PHP
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

class reefless extends rlDb
{
	/**
	* @var int $time_limit - for net operations
	**/
	var $time_limit = 10;
	
	/**
	* @var int $attempts - attempts reached
	**/
	var $attempts = false;
	
	/**
	* @var int $attemptsLeft - attempts left
	**/
	var $attemptsLeft = false;
	
	/**
	* @var string $attemptsMessage - warning message text
	**/
	var $attemptsMessage = false;

	/**
	* requires php class file, creates global class object
	*
	* @param string $className  - loaded class name
	* @param string $type       - class type ( null or admin )
	* @param string $plugin     - plugin name
	* @param mixed $class_param - class parameter (optional)
	**/
	function loadClass( $className, $type = null, $plugin = false, $class_param = false )
	{
		$className = ucfirst($className);
		$className = 'rl' . $className;
		
		if ( !is_object( $className ) )
		{
			$path = $plugin ? RL_PLUGINS . $plugin . RL_DS :RL_CLASSES;
			
			if ( !empty( $type ) && $type == 'admin' )
			{
				$fileSource = $path . 'admin' . RL_DS .  $className . '.class.php' ;
			}
			elseif ( $type == null )
			{
				$fileSource = $path . $className . '.class.php';
			}
	
			global $$className;
			
			if ( !is_object( $$className ) )
			{
				if ( file_exists( $fileSource ) )
				{
					require_once( $fileSource );
				}
				else 
				{
					die( "The '{$className}' class not found" );
				}
				
				if ( $class_param )
				{
					eval('$'. $className .' = &new '. $className .'('. $class_param .');');
				}
				else
				{
					$$className = new $className;
				}
			
				$GLOBALS[$className] = $$className;
			}
		}
	}

	/**
	* redirect to referer
	*
	* @param string $varString - additional url string
	* 
	* @return redirect
	**/
	function referer( $vars = false, $cur_lang = false, $new_lang = false )
	{
		$request_url = $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : $_SERVER['REQUEST_URI'];
		
		if ( $GLOBALS['config']['mod_rewrite'] && $cur_lang )
		{
			$replace = $search = RL_URL_HOME;
			$search .= $GLOBALS['config']['lang'] == $cur_lang ? '' : $cur_lang . "/";
			$replace .= $GLOBALS['config']['lang'] == $new_lang ? '' : $new_lang . "/";
			
			$request_url = str_replace($search, $replace, $request_url);
		}
		
		if ( !empty($vars) )
		{
			$var_char = false !== strpos($request_url, '?') ? '&' : '?' ;
			$request_url .= $var_char . $vars;
		}
		
		header( "Location: " . $request_url );
		exit;
	}
	
	/**
	* redirect to ...
	*
	* @param array $varString - additional url array
	* @param string $target - target url
	* 
	* @return redirect
	**/
	function redirect( $vars = null, $target = false )
	{
		if ( !$vars && !$target )
			return false;
			
		
		if ($target)
		{
			header( "Location: " . $target );
			exit;
		}
		
		if (defined('REALM'))
		{
			$request_url = str_replace( trim(RL_DIR, RL_DS), '', $_SERVER['PHP_SELF']);
			$request_url = trim($request_url, '/');
		}
		else 
		{
			$request_url = str_replace( trim(RL_DIR, RL_DS), '', $_SERVER['REQUEST_URI']);
			$request_url = trim($request_url, '/');
		}

		$request_url = RL_URL_HOME . $request_url;
		
		if ( is_array($vars) )
		{
			if (defined('REALM'))
			{
				$request_url .= "?";
			}
			else
			{
				if ( false !== strpos( $request_url, '?' ) )
				{
					$request_url .= "&";
				}
				else
				{
					$request_url .= "?";
				}
			}
			
			foreach ( $vars as $var => $value )
			{
				$request_url .= $var."=".$value."&";
			}
			$request_url = substr($request_url, 0, -1);
		}

		header( "Location: " . $request_url );
		exit;
	}
	
	/**
	* refresh page
	*
	* @param array $varString - additional url array
	* 
	* @return redirect
	**/
	function refresh()
	{
		$addUrl = str_replace( RL_DIR, '', $_SERVER['REQUEST_URI']);
		$addUrl = trim($addUrl, '/');
		$refresh = RL_URL_HOME . $addUrl;

		header( "Location: " . $refresh );
		exit;
	}
	
	/**
	* check admin session expire time
	**/
	function checkSessionExpire()
	{
		$sess_exp = session_cache_expire() * 60;

		if ( isset($_SESSION['admin_expire_time']) && time() - $_SESSION['admin_expire_time'] >= $sess_exp )
		{
			return false;
		}
		else
		{
			$_SESSION['admin_expire_time'] = $_SERVER['REQUEST_TIME'];
			return true;
		}
	}
	
	/**
	* read directory
	*
	* @param string $dir - directory path
	* @param bool $dir_mode - read directories only
	* @param bool $type - require content type
	* 
	* @return directory content
	**/
	function scanDir( $dir = null, $dir_mode = false, $type = false )
	{
		if (is_dir($dir)) 
		{
			if ($dh = opendir($dir)) 
			{
				$index = 0;
				while (($file = readdir($dh)) !== false) 
				{
					if (!ereg( '^\.{1,2}', $file ))
					{
						if ($type)
						{
							$content[$index]['name'] = $file;
							$content[$index]['type'] = filetype($dir . $file);
							$index++;
						}
						else
						{
							if ($dir_mode)
							{
								if (is_dir($dir . $file))
								{
									$content[] = $file;
								}
							}
							else
							{
								$content[] = $file;
							}
						}
					}
				}
				closedir($dh);
			}
		}

		return $content;
	}
	
	/**
	* get page content
	*
	* @param string $url - page url
	* 
	* @return page content
	**/
	function getPageContent($url)
	{
		$content = null;
		$user_agent = 'Flynax Bot';

		set_time_limit( $this -> time_limit );
		
		if (ini_get('allow_url_fopen'))
		{
			$stream = fopen($url, "r");
			if ($stream) {
			   while (!feof($stream)) {
			       $content .= fgets($stream, 4096);
			   }
			   fclose($stream);
			}
		}
		elseif (extension_loaded('curl'))
		{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
			curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
			$content = curl_exec($ch);
			curl_close($ch);
		}
		else
		{
			$GLOBALS['rlDebug'] -> logger("Unable to get content from: {$url}");
			return 'Unable to get content from: '. $url;
		}

		return $content;
	}
	
	/**
	* delete directory (recursive)
	*
	* @param string $dirname - directory name
	* @param bool $passive - passive mode, remove the requested direcotry in case if it is empty
	* 
	* @return bool
	**/
	function deleteDirectory( $dirname = false, $passive = false )
	{
		if ( is_dir($dirname) )
		{
			$dir_handle = opendir($dirname);
		}
		
		if ( !$dir_handle )
		{
			return false;
		}
		
		// passive mode
		if ( $passive )
		{
			$empty = true;
			$file = readdir($dir_handle);
			
			while( $file = readdir($dir_handle) )
			{
				if ( $file != "." && $file != ".." )
				{
					$empty = false;
				}
			}
			
			if ( $empty )
			{
				rmdir($dirname);
			}
			
			return true;
			exit;
		}
		
		while( $file = readdir($dir_handle) )
		{
			if ( $file != "." && $file != ".." )
			{
				if ( !is_dir($dirname . RL_DS . $file) )
   				{
					unlink($dirname . RL_DS . $file);
   				}
				else
				{
					$this -> deleteDirectory($dirname . RL_DS . $file);
				}
			}
		}
		
  		closedir($dir_handle);
		rmdir($dirname);
		
		return true;
	}

	/**
	* get tmp file
	*
	* @param string $field - file input name
	* @param string $parent - parent file input name, ex: ... name="profile[photo]", 'profile' is parent, 'photo' is field
	* @param string $dom - id of the dom object
	* 
	* @return html dom block
	**/
	function getTmpFile( $aParams = false )
	{
		global $lang, $l_deny_files_regexp, $rlHook;
		
		$field = $aParams['field'];
		$parent = $aParams['parent'];
		$id = $aParams['id'] ? $field .'_'. $aParams['id'] .'_tmp' : false;
		
		if ( $_FILES[$parent]['name'][$field] || $_FILES[$field]['name'] )
		{
			$file_name = $parent && $_FILES[$parent] ? $_FILES[$parent]['name'][$field] : $_FILES[$field]['name'];
			
			/* prevent denied files upload */
			if ( preg_match($l_deny_files_regexp, $file_name) )
			{
				return false;
			}
			
			$file_name = mt_rand() .'_'. $file_name;
			$file_type = $parent && $_FILES[$parent] ? $_FILES[$parent]['type'][$field] : $_FILES[$field]['type'];
			$file_tmp_dir = $parent && $_FILES[$parent] ? $_FILES[$parent]['tmp_name'][$field] : $_FILES[$field]['tmp_name'];
			$file_dir = RL_UPLOAD . $file_name;
			
			$rlHook -> load('phpGetTmpFileFromFiles');
			
			if ( move_uploaded_file( $file_tmp_dir, $file_dir ) )
			{
				chmod(  $file_dir, 0777 );
				
				/* print image */
				if ( strpos($file_type, 'image') !== false && is_readable($file_dir) )
				{
					$file_info = getimagesize(RL_UPLOAD . $file_name);
					$resize_type = $file_info[0] > $file_info[1] ? 'width' : 'height';
					
					$content = '<img alt="" title="'. $file_name .'" style="'. $resize_type .': 250px;" src="'. RL_URL_HOME .'tmp/upload/'. $file_name .'" />';		
				}
				/* print file */
				else
				{
					$file_name_display = substr($file_name, strpos($file_name, '_')+1);
					$content = '<span style="font-style:italic;" class="dark_13" title="'. $file_name .'"><b>'. $file_name_display .'</b></span>';		
				}
				
				if ( $parent )
				{
					$_SESSION['tmp_files'][$parent][$field] = $file_name;
				}
				else
				{
					$_SESSION['tmp_files'][$field] = $file_name;
				}
			}
			else
			{
				trigger_error( "Can't move uploaded file", E_WARNING );
				$GLOBALS['rlDebug'] -> logger("Can't move uploaded file");
			}
		}
		elseif ( ($_SESSION['tmp_files'][$parent][$field] && is_readable(RL_UPLOAD . $_SESSION['tmp_files'][$parent][$field])) || ($_SESSION['tmp_files'][$field] && is_readable(RL_UPLOAD . $_SESSION['tmp_files'][$field])) )
		{
			$file_name = $_SESSION['tmp_files'][$parent][$field] ? $_SESSION['tmp_files'][$parent][$field] : $_SESSION['tmp_files'][$field];
			$file_info = getimagesize(RL_UPLOAD . $file_name);
			
			$rlHook -> load('phpGetTmpFileFromTmp');
			
			/* print image */
			if ( strpos($file_info['mime'], 'image') !== false )
			{
				$resize_type = $file_info[0] > $file_info[1] ? 'width' : 'height';
				$content = '<img class="thumbnail" alt="" title="'. $file_name .'" style="'. $resize_type .': 250px;" src="'. RL_URL_HOME .'tmp/upload/'. $file_name .'" />';
			}
			/* print file */
			else
			{
				$file_name_display = substr($file_name, strpos($file_name, '_')+1);
				$content = '<span style="font-style:italic;" class="dark_13" title="'. $file_name .'"><b>'. $file_name_display .'</b></span>';		
			}
		}
		
		if ( $content )
		{
			$side = RL_LANG_DIR == 'rtl' ? 'right' : 'left';
			$id = $id ? 'id="'. $id .'"' : '';
			$out = '<div '. $id .' class="fleft" style="margin: 0 0 5px;">'
					.'<div style="padding: 3px 0 5px;">'
					.'<table class="sTable"><tr><td>'
					.$lang['currently_uploaded_file'] 
					.'</td><td class="ralign" style="padding-'. $side .': 10px;">'
					.'<a href="javascript:void(0)" onclick="xajax_removeTmpFile(\''. $field .'\', \''. $parent .'\', \''. $id .'\');">'
					.$lang['remove']
					.'</a>'
					.'</td></tr></table>'
					.'</div>'
					. $content 
					.'</div><div class="clear"></div>';
		}
		
		return $out;
	}
	
	/**
	* remove tmp file
	*
	* @param string $field - file input name
	* @param string $parent - parent file input name, ex: ... name="profile[photo]", 'profile' is parent, 'photo' is field
	* @param string $id - id of the dom object
	*
	* @package Ajax
	*
	**/
	function ajaxRemoveTmpFile( $field = false, $parent = false, $id = false )
	{
		global $_response, $rlHook;
		
		$rlHook -> load('ajaxRemoveTmpFile');
		
		$file_name = $parent ? $_SESSION['tmp_files'][$parent][$field] : $_SESSION['tmp_files'][$field];
		unlink( RL_UPLOAD . $file_name );
		
		$field_name = $parent ? $parent ."[{$field}]" : $field;
		
		if ( $id )
		{
			$_response -> script("$('div.#{$id}').slideUp('slow')");
		}
		else
		{
			$_response -> script("$('input[name=\"{$field_name}\"]').parent().find('div:first').slideUp('slow')");
		}
		
		return $_response;
	}
	
	/**
	* generate hash string
	*
	* @param int $number - number of characters
	* @param string $case - case of characters (lower, upper, hex, password)
	* @param bool $numbers - include numbers
	*
	**/
	function generateHash( $number = 32, $case = 'lower', $numbers = true )
	{
		switch ($case){
			case 'lower':
				$chars = range('a', 'z');
				break;
			case 'upper':
				$chars = range('A', 'Z');
				break;
			case 'hex':
				$chars = range('A', 'F');
				break;
			case 'password':
				$chars = range('a', 'z');
				$chars = array_merge( $chars, range('A', 'Z'));
				$chars = array_merge( $chars, array('!','@','#','^','*','(',')','[',']') );
				break;
		}
		
		for ($i = 0; $i < $number; $i++)
		{
			$turn = $numbers ? rand(0, 1) : 0;
			
			if ( $turn )
			{
				$string .= rand(0, 9);
			}
			else
			{
				$index = rand(0, count($chars)-1);
				$string .= $chars[$index];
			}
		}
		
		return $string;
	}
	
	/**
	* create new writable directory
	*
	* @param string $dir - directory to create
	*
	* @return bool
	*
	**/
	function rlMkdir( $dir = false )
	{
		global $rlHook;
		
		if ( !$dir )
			return false;
			
		$dir = str_replace(RL_ROOT, '', $dir);
		$dirs = explode(RL_DS, $dir);
		$directory = RL_ROOT;
		
		$rlHook -> load('phpMrDir');
		
		foreach ( $dirs as $next )
		{
			$directory .= $next . RL_DS;
			
			if ( is_dir($directory) )
			{
				if ( !is_writable($directory) )
				{
					chmod($directory, 0755);
					if ( !is_writable($directory) )
					{
						chmod($directory, 0777);
					}
				}
			}
			else
			{
				mkdir($directory);
				chmod($directory, 0755);
				if ( !is_writable($directory) )
				{
					chmod($directory, 0777);
				}
			}
		}
		
		return true;
	}
	
	/**
	* set writable permisions for directory
	*
	* @param string $dir - directory
	*
	**/
	function rlChmod( $dir = false )
	{
		global $rlHook;
		
		if ( !$dir )
			return;
		
		$rlHook -> load('phpChmod');
				
		chmod($dir, 0755);
		if ( !is_writable($dir) )
		{
			chmod($dir, 0777);
		}
	}
	
	/**
	* parse multilingual value
	*
	* @param string $string - value string
	* @param string $lang - return value by request language code
	*
	* @return array - values by languages, ex: array('en' => val, 'gr' => val2)
	*
	**/
	function parseMultilingual( $string = false, $lang = false )
	{
		global $config;
		
		preg_match_all('/\{\|([a-zA-Z]{2})\|\}([^\{\|]*){\|\/[a-zA-Z]{2}\|\}/', $string, $matches);
		$codes = $matches[1];
		$values = $matches[2];
		
		if ( $codes && $values )
		{
			foreach ( $codes as $index => $code )
			{
				if ( $values[$index] )
				{
					$out[$code] = $values[$index];
				}
			}
		}
		else
		{
			$out[RL_LANG_CODE] = $string;
		}
		
		if ( $lang )
		{
			if ( $out[$lang] )
			{
				return $out[$lang];
			}
			elseif ( $out[$config['lang']] )
			{
				return $out[$config['lang']];
			}
			else
			{
				return current($out);
			}
		}
		
		return $out ? $out : false;
	}
	
	/**
	* parse phone value
	*
	* @param string $string - value string
	* @param array $format - field details, format phone number if 
	*
	* @return array - phone detaails, ex: array('code' => val, 'area' => val2, 'number' => val3, 'ext' => val4)
	*
	**/
	function parsePhone( $string = false, $field = false )
	{
		global $config, $lang;
		
		preg_match('/(c:([0-9]+))?\|?(a:([0-9]+))?\|(n:([0-9]+))?\|?(e:([0-9]+))?/', $string, $matches);

		if ( !$matches )
			return false;
			
		$out['code'] = $matches[2];
		$out['area'] = $matches[4];
		$out['number'] = $matches[6];
		$out['ext'] = $matches[8];
		
		if ( $field )
		{
			if ( $field['Opt1'] && $out['code'] )
			{
				$phone = '+'. $out['code'] .' ';
			}
			if ( $out['area'] )
			{
				$phone .= "({$out['area']}) ";
			}
			if ( $out['number'] )
			{
				$phone .= $this -> flStrSplit($out['number'], 4, '-');
			}
			if ( $field['Opt2'] && $out['ext'] )
			{
				$phone .= ' '. $lang['phone_ext_out'] . $out['ext'];
			}
			return $phone;
		}
		
		return $out;
	}
	
	/**
	* fit char to the string by requested position
	*
	* @param string $string - string
	* @param int $pos - position in string to insert char to
	* @param string $char - char to be inserted
	*
	* @return array - formated string
	*
	**/
	function flStrSplit($string = false, $pos = false, $char = '-')
	{
		if ( !$string || !$char || !$pos )
			return $string;
		
		$splitted = str_split($string, $pos-1);
		$out = $splitted[0] . $char;
		array_shift($splitted);
		$out .= join($splitted);
		
		return $out;
	}
	
	/**
	* array multisort
	*
	* @param array $array - array to sort | by referent
	* @param string $field - field name to sort by
	* @param constant $sort_type - sorting type (array_multisort() function default params)
	*
	**/
	function rlArraySort( &$array, $field = false, $sort_type = SORT_ASC )
	{
		if ( !$array || !$field )
			return $array || false;
			
		foreach ($array as $key => $value)
		{
			$sort[] = $value[$field];
		}
		
		array_multisort($sort, $sort_type, $array);
		unset($sort);
	}
	
	/**
	* touch directory
	*
	* @param array $array - array to sort | by referent
	* @param string $field - field name to sort by
	* @param constant $sort_type - sorting type (array_multisort() function default params)
	*
	**/
	function flTouch( $dir = RL_ROOT, $ext = 'tpl' )
	{
		$files = $this -> scanDir($dir, false, true);
		
		if ( $files )
		{
			foreach ($files as $file)
			{
				if ( $file['type'] == 'dir' )
				{
					$this -> flTouch(rtrim($dir) . RL_DS . $file['name']. RL_DS, $ext);
				}
				elseif ( $file['type'] == 'file' )
				{
					if ( $ext )
					{
						$file_ext = array_reverse(explode('.', $file['name']));
						if ( $file_ext[0] == $ext )
						{
							touch(rtrim($dir) . RL_DS . $file['name']);
						}
					}
					else
					{
						touch(rtrim($dir) . RL_DS . $file['name']);
					}
				}
			}
		}
	}
	
	/**
	* www. prefix redirect, relates to RL_URL_HOME config
	*
	* @param bool $admin - admin panel mode
	*
	* @todo - redirect to www.domainc.com or domain.com, depended of RL_URL_HOME config
	**/
	function wwwRedirect( $admin = false )
	{
		global $rlValid;
		
		$host_s = $rlValid -> getDomain(RL_URL_HOME);//system host
		$host_r = $_SERVER['HTTP_HOST'];//requested host
		
		preg_match('/^(www\.)?(.*)/', $host_s, $matches_s);
		preg_match('/^(www\.)?(.*)/', $host_r, $matches_r);
		
		if ( (!$matches_s[1] && $matches_r[1]) || ($matches_s[1] && !$matches_r[1]) && $matches_s[2] == $matches_r[2] )
		{
			$request_url = $admin ? ADMIN .'/' : ltrim($_SERVER['REQUEST_URI'], '/');
			$request_url = ltrim($request_url, RL_DIR);

			header('HTTP/1.1 301 Moved Permanently');
			header('Location: '. RL_URL_HOME . $request_url);
			exit;
		}
	}
	
	/**
	* login attempt control
	*
	* @param bool $admin - admin panel mode
	*
	* @todo - count available attempts count
	**/
	function loginAttempt( $admin = false )
	{
		global $config, $rlSmarty, $lang;
		
		$mode = $admin ? 'admin' : 'user';
		
		if ( !$config['security_login_attempt_'. $mode .'_module'] )
			return;

		$sql = "SELECT `IP` AS `Count` FROM `". RL_DBPREFIX ."login_attempts` ";
		$sql .= "WHERE `IP` = '{$_SERVER['REMOTE_ADDR']}' AND `Status` = 'fail' AND `Interface` = '{$mode}' ";
		$sql .= "GROUP BY `Date` ";
		$sql .= "HAVING TIMESTAMPDIFF(HOUR, `Date`, NOW()) < {$config['security_login_attempt_'. $mode .'_period']} ";

//		$sql = "SELECT COUNT(*) AS `Count` FROM `". RL_DBPREFIX ."login_attempts` ";
//		$sql .= "WHERE `IP` = '{$_SERVER['REMOTE_ADDR']}' AND `Status` = 'fail' AND `Interface` = 'admin' ";
//		$sql .= "GROUP BY `IP`";
//		$sql .= "HAVING TIMESTAMPDIFF(HOUR, MAX(`Date`), NOW()) < {$config['security_login_attempt_'. $mode .'_period']} ";

		$attempts = $this -> getAll($sql);
		$count = count($attempts);
		
		if ( $count )
		{
			$this -> attempts = $count;
			$this -> attemptsLeft = $config['security_login_attempt_'. $mode .'_attempts'] - $count;
			
			$message .= preg_replace('/(\[([^\]].*)\])/', '<span class="red">$2</span>', str_replace('{number}', '<b>'. $this -> attemptsLeft .'</b>', $lang['login_attempt_warning']));
			$this -> attemptsMessage = $message;
			$rlSmarty -> assign('loginAttemptsMess', $message);
		}
		else
		{
			$this -> attempts = 0;
			$this -> attemptsLeft = $config['security_login_attempt_'. $mode .'_attempts'];
		}
		
		$rlSmarty -> assign('loginAttempts', $this -> attempts);
		$rlSmarty -> assign('loginAttemptsLeft', $this -> attemptsLeft);
	}
	
	/**
	* set current timezone to PHP and MySQL
	**/
	function setTimeZone()
	{
		global $config, $l_timezone;
		
		if ( !$config['timezone'] )
			return;
		
		/* set PHP timezone */
		@date_default_timezone_set($config['timezone']);
		
		/* set MySQL timezone */
		$this -> query("SET time_zone = '{$l_timezone[$config['timezone']][0]}'");
	}
}

/**
* deep stripslashes | NOT REEFLESS CLASS FUNCTION
*
* @param mixed $array - array or string to apply stripslashes recursive
*
**/
function flStripslashes( $value = false)
{
	return is_array($value) ? array_map('flStripslashes', $value) : stripslashes($value);
}
