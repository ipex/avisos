<?php

/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: RLHOOK.CLASS.PHP
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

class rlHook extends reefless
{
	/**
	* @var validator class object
	**/
	var $rlValid;

	/**
	* @var common class object
	**/
	var $rlCommon;

	/**
	* @var hooks array
	**/
	var $rlHooks;

	/**
	* @var index of func
	**/
	var $index = 1;

	/**
	* class constructor
	**/
	function rlHook()
	{
		global $rlValid, $rlCommon;

		$this -> rlValid = $rlValid;
		$this -> rlCommon = $rlCommon;

		/* get hooks */
		$this -> getHooks();
	}

	/**
	* get all active hooks
	*
	* @return array - hooks array
	**/
	function getHooks()
	{
		$this -> setTable('hooks');
		$tmp_hooks = $this -> fetch( array('Name', 'Code'), array('Status' => 'active') );
		$this -> resetTable();

		foreach ($tmp_hooks as $key => $value)
		{
			if (!$hooks[$tmp_hooks[$key]['Name']])
			{
				$hooks[$tmp_hooks[$key]['Name']] = $tmp_hooks[$key]['Code'];
			}
			else
			{
				$tmp_hook = $hooks[$tmp_hooks[$key]['Name']];

				unset($hooks[$tmp_hooks[$key]['Name']]);

				if (is_array($tmp_hook))
				{
					$tmp_hook[] = $tmp_hooks[$key]['Code'];
					$hooks[$tmp_hooks[$key]['Name']] = $tmp_hook;
				}
				else
				{
					$hooks[$tmp_hooks[$key]['Name']][] = $tmp_hooks[$key]['Code'];
					$hooks[$tmp_hooks[$key]['Name']][] = $tmp_hook;
				}
			}
			unset($tmp_hook);
		}

		unset($tmp_hooks);
		$this -> rlHooks = $hooks;
		$GLOBALS['hooks'] = $hooks;
	}

	/**
	* load hook
	*
	* @param string $name - hook name
	*
	* @param mixed $param1 - hook param by ref
	* @param mixed $param2 - hook param by ref
	* @param mixed $param3 - hook param by ref
	* @param mixed $param4 - hook param by ref
	* @param mixed $param5 - hook param by ref
	*
	**/
	function load( $name = false, &$param1, &$param2, &$param3, &$param4, &$param5 )
	{
		if ( is_array($name) )
		{
			$name = $name['name'];
			$hooks = $GLOBALS['hooks'];
		}
		else
		{
			$hooks = $this -> rlHooks;
		}

		$code = isset($hooks[$name]) ? $hooks[$name] : '';
		
		if ( !empty($code) )
		{
			if ( is_array($code) )
			{
				foreach( $code as $item )
				{
					$func = "{$name}Hook". $this -> index;
					$wrapper = "function {$func}(&\$param1, &\$param2, &\$param3, &\$param4, &\$param5) { ". PHP_EOL;
					$wrapper .= "[code]". PHP_EOL;
				   	$wrapper .= "}";
					eval(str_replace('[code]', $item, $wrapper));
					$func(&$param1, &$param2, &$param3, &$param4, &$param5);
					$this -> index++;
				}
			}
			else
			{
				$func = "{$name}Hook". $this -> index;
				$wrapper = "function {$func}(&\$param1, &\$param2, &\$param3, &\$param4, &\$param5) { ". PHP_EOL;
				$wrapper .= "[code]". PHP_EOL;
			   	$wrapper .= "}";
				eval(str_replace('[code]', $code, $wrapper));
				$func(&$param1, &$param2, &$param3, &$param4, &$param5);
				$this -> index++;
			}
		}
	}
}