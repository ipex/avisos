<?php

/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: REGISTRATION.INC.PHP
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

$reefless -> loadClass('Plan');

if ( !$_POST && !isset($_GET['step']) )
{
	unset($_SESSION['ses_registration_data'], $_SESSION['registration_captcha_passed']);
}

/* disallow registration if user alrady logged in */
if ( defined('IS_LOGIN') && !$_GET['step'] )
{
	$reefless -> redirect(false, SEO_BASE);
}

/* build registration steps */
$reg_steps = array(
	'profile' => array(
		'name' => $lang['profile'],
		'caption' => true
	),
	'account' => array(
		'name' => $lang['personal_details'],
		'caption' => true
	)
);

$reg_steps['done']  = array(
	'name' => $lang['reg_done']
);

$show_step_caption = false;
$rlSmarty -> assign_by_ref( 'show_step_caption', $show_step_caption);

/* detect step from GET */
$cur_step = $_POST['reg_step'] ? $_POST['reg_step'] : 'profile';
$rlSmarty -> assign_by_ref('cur_step', $cur_step);

/* get prev/next step */
$tmp_steps = $reg_steps;
foreach ($tmp_steps as $t_key => $t_step)
{
	if ( $t_key != $cur_step )
	{
		next($reg_steps);
	}
	else
	{
		break;
	}
}
unset($tmp_steps);

$next_step = next($reg_steps);prev($reg_steps);
$prev_step = prev($reg_steps);

$rlSmarty -> assign('next_step', $next_step);
$rlSmarty -> assign('prev_step', $prev_step);

$show_step_caption = true;
$rlSmarty -> assign_by_ref( 'show_step_caption', $show_step_caption);

$rlSmarty -> assign_by_ref( 'reg_steps', $reg_steps);

/* get account types list */
$account_types = $rlAccount -> getAccountTypes('visitor');
$rlSmarty -> assign_by_ref('account_types', $account_types);

if ( $_SESSION['registr_account_type'] )
{
	$rlSmarty -> assign_by_ref('registr_account_type', $_SESSION['registr_account_type']);
}

if ( $_GET['step'] == 'done' )
{
	$rlHook -> load('registrationDone');
	unset($_SESSION['registration_captcha_passed']);
}
else
{
	/* register ajax methods */
	$rlXajax -> registerFunction( array( 'userExist', $rlAccount, 'ajaxUserExist' ) );
	$rlXajax -> registerFunction( array( 'emailExist', $rlAccount, 'ajaxEmailExist' ) );
	$rlXajax -> registerFunction( array( 'checkLocation', $rlAccount, 'ajaxCheckLocation' ) );
	$rlXajax -> registerFunction( array( 'validateProfile', $rlAccount, 'ajaxValidateProfile' ) );
	
	$rlXajax -> registerFunction( array( 'checkTypeFields', $rlAccount, 'ajaxCheckTypeFields' ) );
	$rlXajax -> registerFunction( array( 'submitProfile', $rlAccount, 'ajaxSubmitProfile' ) );
	$rlXajax -> registerFunction( array( 'removeTmpFile', $reefless, 'ajaxRemoveTmpFile' ) );

	$reefless -> loadClass('Categories');
	
	/* get domain name */
	$domain = $rlValid -> getDomain(RL_URL_HOME, true);
	$domain = $config['account_wildcard'] ? ltrim($domain, 'www.') : $domain;
	$rlSmarty -> assign_by_ref('domain', $domain);
	
	/* submit form handler */
	if ( isset($_POST['reg_step']) )
	{
		$profile_data = $rlValid -> xSql( $_POST['profile'] );
		$account_data = $_POST['account'];
		
		$type = $profile_data['type'];
		
		$fields = $rlAccount -> getFields($type);
		$fields = $rlLang -> replaceLangKeys( $fields, 'account_fields', array( 'name', 'default', 'description' ) );
		$fields = $rlCommon -> fieldValuesAdaptation($fields, 'account_fields');
	
		$rlSmarty -> assign_by_ref( 'fields', $fields );
		
		if ( $account_data )
		{
			if ( $back_errors = $rlCommon -> checkDynamicForm( $account_data, $fields, 'account' ) )
			{
				foreach ( $back_errors as $error )
				{
					$errors[] = $error;
					$rlSmarty -> assign('fixed_message', true);
				}
				
				if ( $rlCommon -> error_fields )
				{
					$error_fields = $rlCommon -> error_fields;
					$rlCommon -> error_fields = false;
				}
			}
		}
		
		/* check security image code */
		if ( $config['security_img_registration'] && !$_SESSION['registration_captcha_passed'] )
		{
			if ( $_POST['security_code'] != $_SESSION['ses_security_code'] || empty($_SESSION['ses_security_code']) )
			{
				$errors[] = $lang['security_code_incorrect'];
				$error_fields .= 'security_code,';
			}
		}
		
		/* check email */
		$email = $_POST['profile']['mail'];
		if ( !$rlValid -> isEmail( $email ) )
		{
			$errors[] = $lang['notice_bad_email'];
			$error_fields .= 'profile[mail],';
		}

		if ( $rlDb -> getOne('ID', "`Mail` = '{$email}'", 'accounts') )
		{
			$errors[] = str_replace('{email}', $email, $lang['notice_account_email_exist']);
			$error_fields .= 'profile[mail],';
		}

		$rlHook -> load('beforeRegister');
		
		if ( !$errors )
		{
			$reefless -> loadClass( 'Actions' );
			$reefless -> loadClass( 'Resize' );
			$reefless -> loadClass( 'Mail' );
			
			$_SESSION['ses_registration_data'] = array('email' => $profile_data['mail']);
			
			/* personal address handler */
			$profile_data['location'] = trim($profile_data['location']);
			
			if ( !$account_types[$profile_data['type']]['Own_location'] )
			{
				/* load the utf8 lib */
				loadUTF8functions('ascii', 'utf8_to_ascii', 'unicode');
				
				$username = $profile_data['username'];
				if ( !utf8_is_ascii( $username ) )
				{
					$username = utf8_to_ascii( $username );
				}
				
				$profile_data['location'] = $rlSmarty -> str2path($profile_data['username']);
			}
	
			if ( $rlAccount -> registration( $type, $profile_data, $account_data, $fields ) )
			{
				$rlHook -> load('registerSuccess');
				
				$_SESSION['registr_account_type'] = $profile_data['type'];
				
				$aUrl = array( "step" => "done" );
				
				if ( $account_types[$profile_data['type']]['Auto_login'] && !$account_types[$profile_data['type']]['Email_confirmation'] && !$account_types[$profile_data['type']]['Admin_confirmation'] )
				{
					$rlAccount -> login( $profile_data['username'], $profile_data['password'] );
				}
				$reefless -> redirect( $aUrl );
			}
		}
	}
}