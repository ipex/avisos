<?php

/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: REMIND.INC.PHP
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

/* prepare change password for by requested hash */
if ( isset($_GET['hash']) )
{
	$hash = $rlValid -> xSql($_GET['hash']);
	$account_id = $reefless -> getRow("SELECT `ID` FROM `". RL_DBPREFIX ."accounts` WHERE CONCAT(MD5(`Password_hash`), MD5('{$config['security_key']}')) = '{$hash}'");
	
	if ( !$account_id )
	{
		$errors[] = $lang['remind_password_request_hash_fail'];
	}
	
	$rlHook -> load('remindValidate');
	
	if ( !$errors )
	{
		$profile_info = $rlAccount -> getProfile((int)$account_id['ID']);
		
		$rlSmarty -> assign('profile_info', $profile_info);
		$rlSmarty -> assign('change', true);
		
		if ( $_POST['change'] )
		{
			$password = $_POST['profile']['password'];
			$password_repeat = $_POST['password_repeat'];
			
			if ( strlen($password) <= 3 )
			{
				$errors[] = $lang['password_lenght_fail'];
			}
			
			if ( $password != $password_repeat )
			{
				$errors[] = $lang['notice_pass_bad'];
			}
			
			if ( !$errors )
			{
				$reefless -> loadClass('Mail');
				$mail_tpl = $rlMail -> getEmailTemplate('new_password_created');

				$url = SEO_BASE;
				$url .= $config['mod_rewrite'] ? $pages['login'] .'.html' : '?page='. $pages['login'];
				$login_replace = '<a href="'. $url .'">$2</a>';
				$mail_tpl['body'] = str_replace( array( '{login}', '{password}', '{username}' ), array( $profile_info['Username'], $password, $profile_info['Full_name'] ), $mail_tpl['body'] );
				$mail_tpl['body'] = preg_replace('/(\[(.*?)\])/', $login_replace, $mail_tpl['body']);
				
				$sql = "UPDATE `" . RL_DBPREFIX . "accounts` SET `Password` = MD5('{$password}'), `Password_hash` = '' WHERE `ID` = '{$account_id['ID']}' LIMIT 1";
				$rlDb -> query( $sql );
				
				$rlHook -> load('remindActivated');
				
				$rlMail -> send( $mail_tpl, $profile_info['Mail'] );
				
				$reefless -> loadClass('Notice');
		
				$url = SEO_BASE;
				$url .= $config['mod_rewrite'] ? $pages['remind'] .'.html' : '?page='. $pages['remind'];
				
				$rlNotice -> saveNotice( $lang['password_created'] );
				$reefless -> redirect(null, $url);
			}
		}
	}
}
/* get requested e-mail address from post/generate hash link */
else
{
	if ( $_POST['request'] )
	{
		$email = $rlValid -> xSql($_POST['email']);
		
		if ( !$rlValid -> isEmail($email) )
		{
			$errors[] = $lang['incorrect_email'];
		}
		
		if ( empty($errors) )
		{
			$account_id = $rlDb -> getOne('ID', "`Mail` = '{$email}'", 'accounts');
			
			if ( empty($account_id) )
			{
				$errors[] = $lang['email_account_not_found'];
			}
		}
		
		if ( !$errors )
		{
			/* get profile info */
			$reefless -> loadClass('Account');
			$profile_info = $rlAccount -> getProfile((int)$account_id);
			
			$reefless -> loadClass('Mail');
			$mail_tpl = $rlMail -> getEmailTemplate('remind_password_request');
			
			$hash_key = $reefless -> generateHash();
			$hash = md5($hash_key) . md5($config['security_key']);
		
			$sql = "UPDATE `" . RL_DBPREFIX . "accounts` SET `Password_hash` = '{$hash_key}' WHERE `ID` = '{$account_id}' LIMIT 1";
			$rlDb -> query( $sql );
			
			$link = SEO_BASE;
			$link .= $config['mod_rewrite'] ? $pages['remind'] .'.html?hash='. $hash : '?page='. $pages['remind'] .'&amp;hash='. $hash;
			$link = '<a href="'. $link .'">'. $link .'</a>';
			
			$mail_tpl['body'] = str_replace( array( '{link}', '{username}' ), array( $link, $profile_info['Full_name'] ), $mail_tpl['body'] );
			$rlMail -> send( $mail_tpl, $email );
		
			$reefless -> loadClass( 'Notice' );
			$rlNotice -> saveNotice( $lang['remind_password_request_sent'] );
			$reefless -> refresh();
		}
	}
}