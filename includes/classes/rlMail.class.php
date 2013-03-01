<?php

/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: RLMAIL.CLASS.PHP
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

class rlMail extends reefless
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
	* @var Actions class object
	**/
	var $rlActions;
	
	/**
	* @var PHPMailer class object
	**/
	var $phpMailer;
	
	/**
	* class constructor
	**/
	function rlMail()
	{
		global $rlLang, $rlValid, $rlConfig, $rlActions;
		
		$this -> rlLang   = & $rlLang;
		$this -> rlValid  = & $rlValid;
		$this -> rlConfig = & $rlConfig;
		$this -> rlActions = & $rlActions;
		
		$php_version = version_compare(phpversion(), "5", ">") ? 5 : 4;
		
		/* load phpMailer class */
		include_once( RL_LIBS . 'phpmailer' . RL_DS . 'php'. $php_version . RL_DS . 'class.phpmailer.php' );
		$this -> phpMailer = new PHPMailer();
	}
	
	/**
	* send mail
	*
	* @param array $mail_tpl     - subject and body of message
	* @param array  $to          - recipient address
	* @param array  $attach_file - attach file
	* @param array  $from_mail   - from mail
	* @param array  $from_name   - from address
	*
	* @todo sand mail
	**/
	function send( $mail_tpl, $to, $attach_file = false, $from_mail = false, $from_name = false )
	{
		global $config, $lang;
		
		if ( !$mail_tpl['body'] )
		{
			return false;
		}

		if ( $GLOBALS['config']['mail_method'] == 'smtp' )
		{
			$this -> phpMailer -> IsSMTP();
			$this -> phpMailer -> Host = $config['smtp_server'];
			$this -> phpMailer -> Port = 465;
			$this -> phpMailer -> Username = $config['smtp_username'];
			$this -> phpMailer -> Password = $config['smtp_password'];

			if ( empty($config['smtp_username']) && empty($config['smtp_password']))
			{
				$this -> phpMailer -> SMTPAuth = false;
			}
		}

		$subject = $mail_tpl['subject'];
		$body    = $mail_tpl['body'];
		
		if ( $mail_tpl['Type'] == 'html' )
		{
			$tpl_base = RL_ROOT .'templates'. RL_DS . $config['template'] . RL_DS;
			$tpl_url = RL_URL_HOME .'templates/'. $config['template'] .'/';
			$html_source = $this -> getPageContent($tpl_base .'tpl'. RL_DS .'html_email_source.html' );
			
			if ( $html_source )
			{
				$find = array(
					'{content}',
					'{tpl_base}',
					'{site_name}',
					'{footer}',
					'{site_url}'
				);
				$replace = array(
					$body,
					$tpl_url,
					$lang['pages+title+home'],
					$lang['email_footer'],
					RL_URL_HOME
				);
				$body = str_replace($find, $replace, $html_source);
			}	
		}
		
		$this -> phpMailer -> From = $from_mail ? $from_mail : $config['site_main_email'];
		
		$this -> phpMailer -> FromName = $from_name ? $from_name : $config['owner_name'];
		
		$this -> phpMailer -> Subject = $subject;
		
		$this -> phpMailer -> AltBody = "To view the message, please use an HTML compatible email viewer!";

		$this -> phpMailer -> MsgHTML( $body );
		
		$this -> phpMailer -> AddAddress( $to );
		
		if ( $attach_file )
		{
			$this -> phpMailer -> AddAttachment( $attach_file );
		}
		
		if( !$this -> phpMailer -> Send() ) 
		{
			trigger_error( $mail->ErrorInfo, E_USER_WARNING );
			$this -> phpMailer -> ClearAddresses();
			$GLOBALS['rlDebug'] -> logger($mail->ErrorInfo);
		}
		else
		{
			$this -> phpMailer -> ClearAddresses();
			return true;
		}
	}
	
	/**
	* get email template by template key
	*
	* @param string $key - email template key
	*
	* @return array - email template subject and body
	**/
	function getEmailTemplate( $key )
	{
		$output = $this -> fetch( array('Key', 'Type'), array('Key' => $key, 'Status' => 'active' ), null, null, 'email_templates', 'row' );
		$output = $this -> rlLang -> replaceLangKeys( $output, 'email_templates', array( 'subject', 'body' ), RL_LANG_CODE, 'email_tpl' );
		
		$output = $this -> replaceVariables($output);
		
		return empty($output) ? false : $output ;
	}
	
	/**
	* replace email template variables
	*
	* @param array $email_tpl - email template
	*
	* @return array - replaces email template subject and body 
	**/
	function replaceVariables( $mail_tpl )
	{
		global $account_info, $lang, $config;
		
		$tpl_vars = array(
			'{site_name}' => $lang['pages+title+home'],
			'{site_url}' => '<a href="'. RL_URL_HOME .'">'. RL_URL_HOME .'</a>',
			'{site_email}' => '<a href="mailto:'.$config['site_main_email'].'">'.$config['site_main_email'].'</a>'
		);
		
		if ( !empty($account_info['Full_name']) && !defined('REALM') )
		{
			$tpl_vars['{username}'] = $account_info['Full_name'];
			//$tpl_vars['{name}'] = $account_info['Full_name'];
		}
		
		$mail_tpl['body'] = str_replace( PHP_EOL, '<br />', $mail_tpl['body'] );
		foreach ($tpl_vars as $key => $value)
		{
			$mail_tpl['subject'] = str_replace( $key, $value, $mail_tpl['subject'] );
			$mail_tpl['body'] = str_replace( $key, $value, $mail_tpl['body'] );
		}
		
		return $mail_tpl;
	}
}
