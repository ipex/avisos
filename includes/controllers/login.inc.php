<?php

/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: LOGIN.INC.PHP
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

if ( !defined('IS_LOGIN') )
{
	if ( isset($_POST['action']) && $_POST['action'] == 'login')
	{
		$username = $_POST['username'];
		$password = $_POST['password'];
		
		if ( true === $res = $rlAccount -> login( $username, $password ) )
		{
			$rlHook -> load('loginSuccess');
			$reefless -> referer();
		}
		else 
		{
			/* login page mode */
			if ( $page_info['Prev'] == 'login' )
			{
				if ( $rlAccount -> messageType == 'error' )
				{
					$rlSmarty -> assign_by_ref( 'errors', $res );
				}
				else
				{
					$rlSmarty -> assign_by_ref( 'pAlert', $res[0] );
				}
			}
			/* remote pages mode */
			else
			{
				$reefless -> loadClass('Notice');
				$rlNotice -> saveNotice($res, 'error');
				if ( $page_info['prev'] == 'home' )
				{
					$url = SEO_BASE;
					$url .= $config['mod_rewrite'] ? $pages['login'] .'.html' : '?page='. $pages['login'];
					$reefless -> redirect(null, $url);
				}
				else
				{
					$reefless -> referer();
				}
			}
		}
	}
	
	if (isset($_GET['logout']))
	{
		$rlHook -> load('logOut');
		$rlSmarty -> assign( 'pNotice', $lang['notice_logged_out'] );
	}
}
else
{
	if ( isset($_GET['action']) && $_GET['action'] == 'logout')
	{
		$rlAccount -> logOut();
	}
}