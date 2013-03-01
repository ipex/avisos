<?php

/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: RLMESSAGE.CLASS.PHP
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

class rlMessage extends reefless
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
	function rlMessage()
	{
		global $rlLang, $rlValid, $rlConfig;
		
		$this -> rlLang   = & $rlLang;
		$this -> rlValid  = & $rlValid;
		$this -> rlConfig = & $rlConfig;
	}
	
	/**
	* send message
	*
	* @package xAjax
	*
	* @param string $res_id  - recipient account id
	* @param string $message - message text
	* @param bool $admin - admin conversation mode
	*
	**/
	function ajaxSendMessage( $res_id = false, $message = false, $admin = false )
	{
		global $_response, $config, $account_info, $rlSmarty, $pages, $lang;

		if ( !$config['messages_module'] )
		{
			return $_response;
		}
		
		if ( defined('IS_LOGIN') || defined('REALM') )
		{
			$message = trim($message);
			
			if ( function_exists('mb_substr') && function_exists('mb_internal_encoding') )
			{
				mb_internal_encoding('UTF-8');
				$message = mb_substr($message, 0, $config['messages_length']);
			}
			else
			{
				$message = substr($message, 0, $config['messages_length']);
			}

			if ( empty($message) || $message == ' ' )
			{
				return $_response;
			}
			
			$insert = array(
				'From' => $account_info['ID'],
				'To' => (int)$res_id,
				'Message' => $message,
				'Date' => 'NOW()'
			);
			
			if ( $admin )
			{
				$insert['Admin'] = (int)$res_id;
			}

			$this -> loadClass( 'Actions' );
			$GLOBALS['rlActions'] -> insertOne( $insert, 'messages' );

			$GLOBALS['rlHook'] -> load('rlMessagesAjaxSendMessage', $res_id, $message, $admin); // from v4.1.0
			
//			if( $config['messages_notification'] )
//			{
//				$this -> loadClass( 'Mail' );
//				$mail_tpl = $GLOBALS['rlMail'] -> getEmailTemplate( 'contact_owner_user' );
//
//				if ( $admin )
//				{
//					$contact = $this -> fetch(array('ID', 'Name', 'Email'), array('ID' => $res_id), null, 1, 'admins', 'row');
//					
//					$link = RL_URL_HOME . ADMIN .'/index.php?controller=messages&id='. $account_info['ID'];
//					$recepient_email = $contact['Email'];
//				}
//				else
//				{
//					$link = $config['mod_rewrite'] ? RL_URL_HOME . $pages['my_messages'] .'.html' : RL_URL_HOME.'index.php?page='. $pages['my_messages'];
//					$recepient_email = $this -> getOne('Mail', "`ID` = {$res_id}", 'accounts');
//				}
//				
//				$pattern = array('{from_user}', '{my_messages_link}', '{message}');	
//				$replacement = array($account_info['Username'], $link, $message);
//				$mail_tpl = str_replace($pattern, $replacement, $mail_tpl);
//					
//				$GLOBALS['rlMail'] -> send( $mail_tpl, $recepient_email, null, $account_info['Mail'], $account_info['Full_name'] );
//			}
	
			$messages = $this -> getMessages( (int)$res_id );
			$GLOBALS['rlSmarty'] -> assign_by_ref( 'messages', $messages);
			
			$tpl = 'blocks' . RL_DS . 'messages_area.tpl';
			$_response -> assign( 'messages_area', 'innerHTML', $GLOBALS['rlSmarty'] -> fetch( $tpl, null, null, false ) );
			
			$_response -> script("
				$('#message_text').val('');
				$('#messages_area').scrollTop($('#messages_area')[0].scrollHeight);
			");
		}
		else
		{
			$_response -> script( "printMessage('error', '{$lang['notice_operation_inhibit']}')" );
		}
		
		return $_response;
	}
	
	/**
	* refresh messages area
	*
	* @package xAjax
	*
	* @param string $res_id  - recipient account id
	* @param string $checked - checked messages ids
	*
	**/
	function ajaxRefreshMessagesArea( $res_id = false, $checked = false )
	{
		global $_response, $pages, $config;

		$messages = $this -> getMessages( (int)$res_id );
		
		if ( empty($messages) )
		{
			$url = SEO_BASE;
			$url .= $config['mod_rewrite'] ? $pages['my_messages'] .'.html' : '?page=' .$pages['my_messages'];
			$_response -> redirect($url);
			
			return $_response;
		}
		
		$GLOBALS['rlSmarty'] -> assign_by_ref( 'messages', $messages);
		
		if ( $checked )
		{
			$GLOBALS['rlSmarty'] -> assign( 'checked_ids', explode(',', $checked));
		}

		$tpl = 'blocks' . RL_DS . 'messages_area.tpl';
		$_response -> assign( 'messages_area', 'innerHTML', $GLOBALS['rlSmarty'] -> fetch( $tpl, null, null, false ) );
		$_response -> script("checkboxControl();");
		
		return $_response;
	}
	
	/**
	* get account conacts
	*
	* @return array contacts
	**/
	function getContacts()
	{
		global $account_info, $lang;

		$sql = "SELECT DISTINCT `T1`.*, `T2`.`Username`, `T2`.`First_name`, `T2`.`Last_name`, `T2`.`Photo`, `T3`.`Name`, ";
		$sql .= "IF(`T1`.`From` = '{$account_info['ID']}', `T1`.`To`, `T1`.`From`) AS `From` ";
		$sql .= "FROM `" . RL_DBPREFIX . "messages` AS `T1` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "accounts` AS `T2` ON IF(`T1`.`From` = '{$account_info['ID']}', `T1`.`To`, `T1`.`From`) = `T2`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "admins` AS `T3` ON IF(`T1`.`From` = '{$account_info['ID']}', `T1`.`To`, `T1`.`From`) = `T2`.`ID` ";
		$sql .= "WHERE (`T1`.`To` = '{$account_info['ID']}' OR `T1`.`From` = '{$account_info['ID']}') ";
		$sql .= "AND ( FIND_IN_SET('from', `T1`.`Remove`) = 0 AND FIND_IN_SET('to', `T1`.`Remove`) = 0 ) ";
		$sql .= "GROUP BY `T1`.`ID` ORDER BY `ID` DESC ";
		
		$GLOBALS['rlHook'] -> load('rlMessagesGetContactsSql', $sql); // from v4.1.0
		
		$messages = $this -> getAll( $sql );

		foreach ($messages as $key => $value)
		{
			$index = $value['From'] == $account_info['ID'] ? 'To' : 'From';
			
			if ( $contacts[$value[$index]] )
			{
				$contacts[$value[$index]]['Count'] += $value['Status'] == 'new' && $value['To'] == $account_info['ID'] && $value['From'] == $value[$index] ? 1 : 0;
			}
			else
			{
				if ( $value['Admin'] == $value['From'] )
				{
					$name = $value['Name'] ? $value['Name'] : $lang['administrator'];
				}
				else
				{
					$name = $value['First_name'] || $value['Last_name'] ? $value['First_name'] .' '. $value['Last_name'] : $value['Username'];
				}
				
				$contacts[$value[$index]] = $value;
				$contacts[$value[$index]]['Full_name'] = $name;
				$contacts[$value[$index]]['Count'] = $value['Status'] == 'new' && $value['To'] == $account_info['ID'] ? 1 : 0;
			}
		}
		unset($messages);

		return $contacts;
	}
	
	/**
	* get contact messages
	*
	* @param int $id - contact id
	* @param bool $no_update - do not set message status as read
	*
	* @return array messages
	**/
	function getMessages( $id = false, $no_update = false )
	{
		global $account_info;

		$sql = "SELECT `T1`.*, `T2`.`Username`, `T2`.`First_name`, `T2`.`Last_name` ";
		$sql .= "FROM `" . RL_DBPREFIX . "messages` AS `T1` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "accounts` AS `T2` ON `T1`.`From` = `T2`.`ID` ";
		$sql .= "WHERE (`T1`.`To` = '{$account_info['ID']}' AND `T1`.`From` = '{$id}') OR (`T1`.`To` = '{$id}' AND `T1`.`From` = '{$account_info['ID']}') ";
		//$sql .= "AND FIND_IN_SET(IF (`T1`.`From` = '{$account_info['ID']}', 'from', 'to'), `T1`.`Remove`) = 0 ";
		
		$GLOBALS['rlHook'] -> load('rlMessagesGetMessagesSql', $sql); // from v4.1.0
		
		$sql .= "ORDER BY `ID` ASC";
		
		$messages = $this -> getAll( $sql );
		
		foreach ($messages as $key => $value)
		{
			$current = $value['From'] == $account_info['ID'] ? 'from' : 'to';
			if ( in_array($current, explode(',', $value['Remove'])) )
			{
				unset($messages[$key]);
			}
			elseif ( !empty($value['Remove']) && !in_array($current, explode(',', $value['Remove'])) )
			{
				$messages[$key]['Hide'] = true;
			}
		}
		
		/* set messages as readed */
		if ( !$no_update )
		{
			$update = array(
				'fields' => array(
					'Status' => 'readed'
				),
				'where' => array(
					'From' => $id,
					'To' => $account_info['ID']
				)
			);
			
			$this -> loadClass('Actions');
			$GLOBALS['rlActions'] -> updateOne( $update, 'messages' );
		}
		
		return $messages;
	}

	/**
	* contact owner
	*
	* @package xAjax
	*
	* @param string $name - name
	* @param string $email - email
	* @param string $phone - phone
	* @param string $message - message text
	* @param string $code - security code
	* @param int $listing_id - owner listing id
	*
	**/
	function ajaxContactOwner( $name = false, $email = false, $phone = false, $message = false, $code = false, $listing_id = false )
	{
		global $_response, $pages, $config, $lang, $account_info, $account, $rlHook, $sql, $rlListingTypes, $rlSmarty;
		
		/* cut message */
		if ( function_exists('mb_substr') && function_exists('mb_internal_encoding') )
		{
			mb_internal_encoding('UTF-8');
			$message = mb_substr($message, 0, $config['messages_length']);
		}
		else
		{
			$message = substr($message, 0, $config['messages_length']);
		}
		
		if ( !$config['messages_module'] )
		{
			return $_response;
		}
		
		$errors = array();
		
		if ( !defined('IS_LOGIN') )
		{
			/* check required fields */
			if ( empty($name) )
			{
				$errors[] = str_replace( '{field}', '<span class="field_error">"'.$lang['name'].'"</span>', $lang['notice_field_empty']);
			}
			
			if ( empty($email) )
			{
				$errors[] = str_replace( '{field}', '<span class="field_error">"'.$lang['mail'].'"</span>', $lang['notice_field_empty']);
			}
			
			if ( !empty($email) && !$GLOBALS['rlValid'] -> isEmail( $email ) )
			{
				$errors[] = $lang['notice_bad_email'];
			}
			
			if ( $code != $_SESSION['ses_security_code_contact_code'] || !$_SESSION['ses_security_code_contact_code'] )
			{
				$errors[] = $lang['security_code_incorrect'];
			}
		}

		$GLOBALS['rlHook'] -> load('rlMessagesAjaxContactOwnerValidate', $name, $email, $phone, $message, $listing_id); // from v4.1.0
		
		if ( empty($message) )
		{
			$errors[] = str_replace( '{field}', '<span class="field_error">"'.$lang['message'].'"</span>', $lang['notice_field_empty']);
		}

		if ( $errors )
		{
			$error_content = '<ul>';
			foreach ($errors as $error)
			{
				$error_content .= '<li>'. $error .'</li>';
			}
			$error_content .= '</ul>';
			
			$_response -> script("printMessage('error', '{$error_content}')");
		}
		else
		{
			$this -> loadClass('Mail');
			$this -> loadClass('Listings');
			$this -> loadClass('Actions');
			
			if ( $listing_id )
			{
				/* get listing/owner details */
				$sql = "SELECT `T1`.*, `T2`.`Type` AS `Listing_type_key`, `T2`.`Path` AS `Category_path`, `T3`.`Mail` AS `Owner_email`, ";
				$sql .= "`T3`.`Username` AS `Owner_username`, `T3`.`First_name` AS `Owner_first_name`, `T3`.`Last_name` AS `Owner_last_name` ";
				$sql .= "FROM `". RL_DBPREFIX ."listings` AS `T1` ";
				$sql .= "LEFT JOIN `". RL_DBPREFIX ."categories` AS `T2` ON `T1`.`Category_ID` = `T2`.`ID` ";
				$sql .= "LEFT JOIN `". RL_DBPREFIX ."accounts` AS `T3` ON `T1`.`Account_ID` = `T3`.`ID` ";
				$sql .= "WHERE `T1`.`ID` = {$listing_id} AND `T1`.`Status` = 'active' AND `T2`.`Status` = 'active' AND `T3`.`Status` = 'active'";
				
				$rlHook -> load('contactOwnerInfoSql');
				
				$info = $this -> getRow($sql);
				$owner_name = $info['Owner_first_name'] || $info['Owner_last_name'] ? $info['Owner_first_name'] . ' ' . $info['Owner_last_name'] : $info['Owner_username'];
				
				$listing_type = $rlListingTypes -> types[$info['Listing_type_key']];
				$listing_title = $GLOBALS['rlListings'] -> getListingTitle($info['Category_ID'], $info, $info['Listing_type_key']);
				
				$link = SEO_BASE;
				if ( $config['mod_rewrite'] )
				{
					$link .=  $pages[$listing_type['Page_key']] .'/'. $info['Category_path'] .'/'. $rlSmarty -> str2path($listing_title) .'-'. $info['ID'] .'.html';
				}
				else
				{
					$link .= '?page='. $pages[$listing_type['Page_key']] .'&amp;id='. $info['ID'];
				}
				$link = '<a href="'. $link .'">'.$listing_title.'</a>';
			}
			elseif ( is_array($account) )
			{
				$info['Account_ID'] = $account['ID'];
				$info['Owner_email'] = $account['Mail'];
				$info['Owner_username'] = $account['Username'];
				$info['Owner_first_name'] = $account['First_name'];
				$info['Owner_last_name'] = $account['Last_name'];
				$owner_name = $info['Owner_first_name'] || $info['Owner_last_name'] ? trim($info['Owner_first_name'] . ' ' . $info['Owner_last_name']) : $info['Owner_username'];
			}
			else
			{
				$link = $lang['not_available'];
			}
			
			$GLOBALS['rlHook'] -> load('rlMessagesAjaxContactOwnerSend', $name, $email, $phone, $message, $listing_id); // from v4.1.0
			
			/* logged in user mode */
			if ( defined('IS_LOGIN') )
			{
				$insert = array(
					'From' => $account_info['ID'],
					'To' => $info['Account_ID'],
					'Message' => $message,
					'Date' => 'NOW()'
				);
	
				$GLOBALS['rlActions'] -> insertOne( $insert, 'messages' );

				if ( $config['messages_notification'] )
				{
					$mail_tpl = $GLOBALS['rlMail'] -> getEmailTemplate('contact_owner_user');
	
					$reply_link = defined('REALM') && REALM == 'admin' ? RL_URL_HOME : SEO_BASE;
					$reply_link .= $config['mod_rewrite'] ? $pages['my_messages'] .'.html' : '?page='. $pages['my_messages'];
					$reply_link = '<a href="'. $reply_link .'">'. $reply_link .'</a>';
					
					$find = array('{owner_name}', '{listing_link}', '{message}', '{reply_link}', '{visitor_name}');	
					$replace = array($owner_name, $link, $message, $reply_link, $account_info['Full_name']);
	
					$mail_tpl['subject'] = str_replace( '{visitor_name}', $account_info['Full_name'], $mail_tpl['subject'] );
					$mail_tpl['body'] = str_replace($find, $replace, $mail_tpl['body']);

					$GLOBALS['rlMail'] -> send( $mail_tpl, $info['Owner_email'], null, $account_info['Mail'], $account_info['Full_name'] );
				}
				
				$_response -> script("
					printMessage('notice', '{$lang['notice_message_sent']}');
					$('#modal_block>div.inner>div.close').trigger('click');
				");
			}
			else
			/* visitor mode */
			{
				$message = preg_replace('/(\\n|\\t|\\r)/', '<br />', $message);
				
				$phone_line = $lang['contact_phone'] .': ';
				$phone_line = $phone ? $phone : $lang['not_available'];
				
				$mail_tpl = $GLOBALS['rlMail'] -> getEmailTemplate('contact_owner');
				
				$find = array('{owner_name}', '{visitor_name}', '{message}', '{listing_link}', '{contact_phone}');
				$replace = array($owner_name, $name, $message, $link, $phone_line);
				$mail_tpl['subject'] = str_replace( '{visitor_name}', $name, $mail_tpl['subject'] );
				$mail_tpl['body'] = str_replace( $find, $replace, $mail_tpl['body'] );
				
				/* send e-mail for friend */
				$GLOBALS['rlMail'] -> send( $mail_tpl, $info['Owner_email'], null, $email, $name );
				
				$_response -> script("
					printMessage('notice', '{$lang['notice_message_sent']}');
					$('#modal_block>div.inner>div.close').trigger('click');
					$('img#contact_code_security_img').attr('src', '".RL_LIBS_URL."kcaptcha/getImage.php?'+Math.random()+'&id=contact_code');
				");
			}
		}
		
		$_response -> script("
			$('form[name=contact_owner] input[type=submit]').val('{$lang['send']}');
		");

		return $_response;
	}
	
	/**
	* contact owner Admin Panel
	*
	* @package xAjax
	*
	* @param string $id - owner account ID
	* @param string $message - message
	*
	**/
	function ajaxContactOwnerAP( $id = false, $message = false )
	{
		global $_response, $pages, $config, $lang, $rlHook, $sql, $rlListingTypes, $rlSmarty, $seller_info;
		
		if ( !$config['messages_module'] || !id )
		{
			return $_response;
		}
		
		$this -> loadClass('Mail');
		
		$insert = array(
			'From' => $_SESSION['sessAdmin']['user_id'],
			'To' => $seller_info['ID'],
			'Admin' => $_SESSION['sessAdmin']['user_id'],
			'Message' => $message,
			'Date' => 'NOW()'
		);
		$GLOBALS['rlActions'] -> insertOne( $insert, 'messages' );
		
		if ( $config['messages_notification'] )
		{
			$mail_tpl = $GLOBALS['rlMail'] -> getEmailTemplate('contact_owner_admin');

			$reply_link = defined('REALM') && REALM == 'admin' ? RL_URL_HOME : SEO_BASE;
			$reply_link .= $config['mod_rewrite'] ? $pages['my_messages'] .'.html' : '?page='. $pages['my_messages'];
			$reply_link = '<a href="'. $reply_link .'">'. $reply_link .'</a>';
			
			$find = array('{owner_name}', '{message}', '{reply_link}');	
			$replace = array($seller_info['Full_name'], $message, $reply_link);

			$mail_tpl['body'] = str_replace($find, $replace, $mail_tpl['body']);

			$GLOBALS['rlMail'] -> send( $mail_tpl, $seller_info['Mail'], null, $_SESSION['sessAdmin']['mail'], $_SESSION['sessAdmin']['name'] );
		}
		
		$_response -> script("printMessage('notice', '{$lang['notice_message_sent']}')");

		return $_response;
	}
	
	/**
	* remove message
	*
	* @package xAjax
	*
	* @param string $ids         - message ids
	* @param string $contact_id  - contact id
	*
	**/
	function ajaxRemoveMsg( $ids = false, $contact_id = false )
	{
		global $_response, $account_info;

		/* check message owner */
		$ids = explode(',', $ids);
		
		if ( !empty($ids[0]) )
		{
			$GLOBALS['rlHook'] -> load('rlMessagesAjaxRemoveMsg', $ids, $contact_id); // from v4.1.0
			
			foreach ($ids as $id)
			{
				$res = $this -> fetch(array('ID', 'From', 'To', 'Remove'), null, "WHERE ((`From` = '{$account_info['ID']}' AND `To` = '{$contact_id}') OR (`From` = '{$contact_id}' AND `To` = '{$account_info['ID']}')) AND `ID` = '{$id}'", 1, 'messages', 'row' );
				if ( $res )
				{
					$request = $account_info['ID'] == $res['From'] ? 'from' : 'to';
					$update[] = array(
						'fields' => array('Remove' => $res['Remove'].','.$request),
						'where' => array('ID' => $id)
					);
				}
			}
			
			if ( !empty($update) )
			{
				$GLOBALS['rlActions'] -> update($update, 'messages');
				$_response -> script("xajax_refreshMessagesArea('{$contact_id}')");
			}
		}
		
		return $_response;
	}
	
	/**
	* remove contacts
	*
	* @package xAjax
	*
	* @param string $ids - contacts ids
	*
	**/
	function ajaxRemoveContacts( $ids = false )
	{
		global $_response, $pages, $account_info, $lang, $config;

		/* check message owner */
		$ids = explode(',', $ids);

		if ( !empty($ids[0]) )
		{
			$this -> loadClass('Actions');
			
			$GLOBALS['rlHook'] -> load('rlMessagesAjaxRemoveContacts', $ids); // from v4.1.0
			
			/* get contacts messages */
			foreach ($ids as $contact_id)
			{
				$messages = $this -> getMessages($contact_id, true);
				
				foreach ($messages as $key => $value)
				{
					if ( $value['From'] == $value['To'] )
					{
						$this -> query("DELETE FROM `". RL_DBPREFIX ."messages` WHERE `ID` = '{$value['ID']}' LIMIT 1");
						$deleted = true;
					}
					else
					{
						if ( in_array($account_info['ID'], array($value['From'], $value['To'])) )
						{
							$request = $account_info['ID'] == $value['From'] ? 'from' : 'to';
							$update[] = array(
								'fields' => array(
									'Remove' => $value['Remove'].','.$request,
									'Status' => 'readed'
								),
								'where' => array('ID' => $value['ID'])
							);
						}
					}
				}
			}
			
			if ( $update || $deleted )
			{
				if ( $update )
				{
					$GLOBALS['rlActions'] -> update($update, 'messages');
				}
				
				if ( defined('REALM') )
				{
					$_response -> script("
						printMessage('notice', '{$lang['notice_items_deleted']}');
						$('#item_". implode(",#item_", $ids) ."').fadeOut('slow', function(){
							$('#content table.table input.del_mess').remove();
							
							if ( $('#content table.list input.del_mess').length <= 0 )
							{
								$('#content table.table').after('<div>{$lang['no_messages']}</div>');
								$('#content table.list').remove();
								$('div.mass_actions_light').remove();
							}
						});
					");
				}
				else
				{
					$_response -> script("
						printMessage('notice', '{$lang['notice_items_deleted']}');
						$('#item_". implode(",#item_", $ids) ."').fadeOut('slow', function(){
							$('#controller_area table.list input.del_mess').remove();
							
							if ( $('#controller_area table.list input.del_mess').length <= 0 )
							{
								$('#controller_area table.list').after('<div class=\"info\">{$lang['no_messages']}</div>');
								$('#controller_area table.list').remove();
								$('div.mass_actions_light').remove();
							}
						});
					");
				}
			}
		}
		
		return $_response;
	}
}