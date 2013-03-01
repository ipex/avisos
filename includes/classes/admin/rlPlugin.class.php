<?php

/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: RLPLUGIN.CLASS.PHP
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

class rlPlugin extends reefless 
{
	var $inTag;
	var $level = 0;
	var $attributes;
	
	var $key;
	var $title;
	var $description;
	var $version;
	var $uninstall;
	var $hooks;
	var $phrases;
	var $configGroup;
	var $configs;
	var $blocks;
	var $aBlocks;
	var $pages;
	var $emails;
	var $files;
	var $notice;
	var $controller;
	
	var $updates;
	var $notices;
	var $controllerUpdate;
	
	var $noVersionTag = false;
	
	/**
	* install plugin
	*
	* @package xAjax
	*
	* @param string $key - plugin key
	*
	**/
	function ajaxInstall( $key = false, $remote_mode = false )
	{
		global $_response, $rlSmarty, $lang, $controller;

		$this -> noVersionTag = true;
		
		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}
		
		if ( !$key )
		{
			return $_response;
		}

		$path_to_install = RL_PLUGINS . $key . RL_DS. 'install.xml';
		
		if ( is_readable($path_to_install) )
		{
			require_once( RL_LIBS . 'saxyParser' . RL_DS . 'xml_saxy_parser.php' );

			$rlParser = new SAXY_Parser();
			$rlParser -> xml_set_element_handler(array(&$this, "startElement"), array(&$this, "endElement"));
			$rlParser -> xml_set_character_data_handler(array(&$this, "charData"));
			$rlParser -> xml_set_comment_handler(array(&$this, "commentElement"));

			// parse xml file
			$rlParser -> parse( file_get_contents($path_to_install) );

			$allLangs = $GLOBALS['languages'];
			
			$plugin = array(
				'Key' => $this -> key,
				'Name' => $this -> title,
				'Description' => $this -> description,
				'Version' => $this -> version,
				'Status' => 'approval',
				'Install' => 1,
				'Controller' => $this -> controller,
				'Uninstall' => $this -> uninstall,
				'Files' => serialize($this -> files)
			);
			
			$this -> loadClass( "Actions" );
			
			// install plugin
			if ( $GLOBALS['rlActions'] -> insertOne( $plugin, 'plugins' ) )
			{
				// install language's phrases
				$phrases = $this -> phrases;
				if ( !empty($phrases) )
				{
					unset($lang_keys);
					foreach ($phrases as $key => $value)
					{
						foreach ($allLangs as $lkey => $lval )
						{
							$lang_keys[] = array(
								'Code' => $allLangs[$lkey]['Code'],
								'Module' => $phrases[$key]['Module'],
								'Key' => $phrases[$key]['Key'],
								'Value' => $phrases[$key]['Value'],
								'Plugin' => $this -> key,
								'Status' => 'approval'
							);
						}
					}
				}

				// install hooks
				$hooks = $this -> hooks;
				if ( !empty($hooks) )
				{
					$GLOBALS['rlActions'] -> insert($hooks, 'hooks');
				}
				
				// install configs
				$cGroup = $configGroup = $this -> configGroup;
				if ( !empty($configGroup) )
				{
					$cg_max_poss = $this -> getRow("SELECT MAX(`Position`) AS `max` FROM `" . RL_DBPREFIX . "config_groups` LIMIT 1");
					unset($cGroup['Name']);
					$cGroup['Position'] = $cg_max_poss['max']+1;
					
					$GLOBALS['rlActions'] -> insertOne($cGroup, 'config_groups');
					$group_id = mysql_insert_id();
					
					// add config group phrases
					foreach ($allLangs as $lkey => $lval )
					{
						$lang_keys[] = array(
							'Code' => $allLangs[$lkey]['Code'],
							'Module' => 'admin',
							'Key' => 'config_groups+name+'.$configGroup['Key'],
							'Value' => $configGroup['Name'],
							'Plugin' => $this -> key,
							'Status' => 'approval'
						);
					}
				}
				$group_id = empty($group_id) ? 0 : $group_id;

				$configs = $this -> configs;
				if (!empty($configs))
				{
					foreach ($configs as $key => $value)
					{
						foreach ($allLangs as $lkey => $lval )
						{
							$lang_keys[] = array(
								'Code' => $allLangs[$lkey]['Code'],
								'Module' => 'admin',
								'Key' => 'config+name+'.$configs[$key]['Key'],
								'Value' => $configs[$key]['Name'],
								'Plugin' => $this -> key,
								'Status' => 'approval'
							);
							
							if ( !empty($configs[$key]['Description']) )
							{
								$lang_keys[] = array(
									'Code' => $allLangs[$lkey]['Code'],
									'Module' => 'admin',
									'Key' => 'config+des+'.$configs[$key]['Key'],
									'Value' => $configs[$key]['Description'],
									'Plugin' => $this -> key,
									'Status' => 'approval'
								);
							}
						}
						$position = $key;
						
						if ( $configs[$key]['Group'] )
						{
							$max_pos = $this -> getRow("SELECT MAX(`Position`) AS `Max` FROM `".RL_DBPREFIX."config` WHERE `Group_ID` = '{$configs[$key]['Group']}' LIMIT 1");
							$position = $max_pos['Max'] + $key;
						}
						
						$configs[$key]['Position'] = $position;
						$configs[$key]['Group_ID'] = !$group_id ? $configs[$key]['Group'] : $group_id;
						unset($configs[$key]['Name']);
						unset($configs[$key]['Description']);
						unset($configs[$key]['Group']);
						unset($configs[$key]['Version']);
					}
					$GLOBALS['rlActions'] -> insert($configs, 'config');
				}
				
				// install blocks
				$blocks = $this -> blocks;
				if (!empty($blocks))
				{
					foreach ( $blocks as $key => $value )
					{
						$block_max_poss = $this -> getRow("SELECT MAX(`Position`) AS `max` FROM `" . RL_DBPREFIX . "blocks` LIMIT 1");
						$blocks[$key]['Position'] = $block_max_poss['max']+1;
						
						if ( in_array(strtolower($blocks[$key]['Type']), array('html', 'php', 'smarty')) )
						{
							// add name phrases
							foreach ($allLangs as $lkey => $lval )
							{
								$lang_keys[] = array(
									'Code' => $allLangs[$lkey]['Code'],
									'Module' => 'common',
									'Key' => 'blocks+name+'.$blocks[$key]['Key'],
									'Value' => $blocks[$key]['Name'],
									'Plugin' => $this -> key,
									'Status' => 'avtive'
								);
							}
	
							if (strtolower($blocks[$key]['Type']) == 'html')
							{
								foreach ($allLangs as $lkey => $lval )
								{
									$lang_keys[] = array(
										'Code' => $allLangs[$lkey]['Code'],
										'Module' => 'common',
										'Key' => 'blocks+content+'.$blocks[$key]['Key'],
										'Value' => $blocks[$key]['Content'],
										'Plugin' => $this -> key,
										'Status' => 'avtive'
									);
								}
								unset($blocks[$key]['Content']);
							}
							
							unset($blocks[$key]['Name']);
							unset($blocks[$key]['Version']);
						}
						else
						{
							unset($blocks[$key]);
						}
					}
					$GLOBALS['rlActions'] -> insert($blocks, 'blocks');
				}
				
				// install admin panel blocks
				$aBlocks = $this -> aBlocks;
				if (!empty($aBlocks))
				{
					foreach ( $aBlocks as $key => $value )
					{
						$aBlock_max_poss = $this -> getRow("SELECT MAX(`Position`) AS `max` FROM `" . RL_DBPREFIX . "admin_blocks` WHERE `Column` = 'column{$value['Column']}' LIMIT 1");
						$aBlocks[$key]['Position'] = $aBlock_max_poss['max']+1;

						// add name phrases
						foreach ($allLangs as $lkey => $lval )
						{
							$lang_keys[] = array(
								'Code' => $lval['Code'],
								'Module' => 'admin',
								'Key' => 'admin_blocks+name+'.$value['Key'],
								'Value' => $value['Name'],
								'Plugin' => $this -> key,
								'Status' => 'active'
							);
						}
						
						$aBlocks[$key]['name'] = $aBlocks[$key]['Name'];
						$rlSmarty -> assign('block', $aBlocks[$key]);
						
						unset($aBlocks[$key]['Name']);
						unset($aBlocks[$key]['name']);
						unset($aBlocks[$key]['Version']);
						$aBlocks[$key]['Column'] = 'column'. $aBlocks[$key]['Column'];
						
						if ( $remote_mode )
						{
							// append new block
							$tpl = 'blocks' . RL_DS . 'homeDragDrop_block.tpl';
							$_response -> append('tmp_dom_blocks_store', 'innerHTML', $rlSmarty -> fetch( $tpl, null, null, false ));
							$_response -> script("
								$('#tmp_dom_blocks_store div.block').hide();
								$('td.column{$value['Column']} div.sortable').append($('#tmp_dom_blocks_store div.block'));
								$('td.column{$value['Column']} div.sortable div.block:last').fadeIn('slow');
							");
							if ( $aBlocks[$key]['Ajax'] )
							{
								$_response -> call( 'aBlockInit' );
							}
						}
					}
					$GLOBALS['rlActions'] -> insert($aBlocks, 'admin_blocks');
				}

				// install pages
				$pages = $this -> pages;
				if ( !empty($pages) )
				{
					foreach ( $pages as $key => $value )
					{
						$page_max_poss = $this -> getRow("SELECT MAX(`Position`) AS `max` FROM `" . RL_DBPREFIX . "pages` LIMIT 1");
						$pages[$key]['Position'] = $page_max_poss['max']+1;
						
						if ( in_array($pages[$key]['Page_type'], array('system', 'static', 'external')) )
						{
							// add name phrases
							foreach ($allLangs as $lkey => $lval )
							{
								$lang_keys[] = array(
									'Code' => $allLangs[$lkey]['Code'],
									'Module' => 'common',
									'Key' => 'pages+name+'.$pages[$key]['Key'],
									'Value' => $pages[$key]['Name'],
									'Plugin' => $this -> key,
									'Status' => 'active'
								);
								$lang_keys[] = array(
									'Code' => $allLangs[$lkey]['Code'],
									'Module' => 'common',
									'Key' => 'pages+title+'.$pages[$key]['Key'],
									'Value' => $pages[$key]['Name'],
									'Plugin' => $this -> key,
									'Status' => 'active'
								);
							}
	
							switch ($pages[$key]['Page_type']){
								case 'static':
									foreach ($allLangs as $lkey => $lval )
									{
										$lang_keys[] = array(
											'Code' => $allLangs[$lkey]['Code'],
											'Module' => 'common',
											'Key' => 'pages+content+'.$pages[$key]['Key'],
											'Value' => $pages[$key]['Content'],
											'Plugin' => $this -> key,
											'Status' => 'active'
										);
									}
									break;
								case 'system':
									$pages[$key]['Controller'] = $pages[$key]['Controller'];
									break;
								case 'external':
									$pages[$key]['Controller'] = $pages[$key]['Content'];
									break;
							}
							unset($pages[$key]['Name']);
							unset($pages[$key]['Content']);
							unset($pages[$key]['Version']);
						}
						else
						{
							unset($pages[$key]);
						}
					}
					$GLOBALS['rlActions'] -> insert($pages, 'pages');
				}
				
				// install email templates
				$emails = $this -> emails;
				if ( !empty($emails) )
				{
					foreach ( $emails as $key => $value )
					{
						$email_max_poss = $this -> getRow("SELECT MAX(`Position`) AS `max` FROM `" . RL_DBPREFIX . "email_templates` LIMIT 1");
						$emails[$key]['Position'] = $email_max_poss['max']+1;
						
						// add name phrases
						foreach ($allLangs as $lkey => $lval )
						{
							$lang_keys[] = array(
								'Code' => $allLangs[$lkey]['Code'],
								'Module' => 'email_tpl',
								'Key' => 'email_templates+subject+'.$emails[$key]['Key'],
								'Value' => $emails[$key]['Subject'],
								'Plugin' => $this -> key,
								'Status' => 'active'
							);
							$lang_keys[] = array(
								'Code' => $allLangs[$lkey]['Code'],
								'Module' => 'email_tpl',
								'Key' => 'email_templates+body+'.$emails[$key]['Key'],
								'Value' => $emails[$key]['Body'],
								'Plugin' => $this -> key,
								'Status' => 'active'
							);
						}
						unset($emails[$key]['Subject']);
						unset($emails[$key]['Body']);
						unset($emails[$key]['Version']);
					}
					$GLOBALS['rlActions'] -> insert($emails, 'email_templates');
				}
				
				// add phrases
				if ( !empty($lang_keys) )
				{
					$GLOBALS['rlActions'] -> insert($lang_keys, 'lang_keys');
				}
				
				// eval install code
				if ( !empty($this -> install) )
				{
					@eval($this -> install);
				}
				
				// check plugin files exist
				$files = $this -> files;
				$files_exist = true;
				
				foreach ( $files as $file )
				{
					$file = str_replace(array('\\', '/'), array(RL_DS, RL_DS), $file);
					
					if (!is_readable( RL_PLUGINS . $this -> key . RL_DS . $file ))
					{
						$files_exist = false;
						
						$missed_files .= '/plugins/'. $this -> key .'/<b>'. $file .'</b><br />';
						
						$message = str_replace('{files}', "<br />".$missed_files, $lang['plugin_files_missed']);
						$_response -> script("printMessage('alert', '{$message}');");
					}
				}
				
				// activate plugin
				if ( $files_exist === true )
				{
					$tables = array( 'lang_keys', 'hooks', 'blocks', 'admin_blocks', 'pages', 'email_templates' );
					
					foreach ( $tables as $table )
					{
						unset($update);
						$update = array(
							'fields' => array(
								'Status' => 'active'
							),
							'where' => array(
								'Plugin' => $this -> key
							)
						);
						$GLOBALS['rlActions'] -> updateOne( $update, $table );
					}
					
					unset($update);
					$update = array(
						'fields' => array(
							'Status' => 'active'
						),
						'where' => array(
							'Key' => $this -> key
						)
					);
					$GLOBALS['rlActions'] -> updateOne( $update, 'plugins' );
					
					if ( $this -> notice || is_array($this -> notices) )
					{
						$post_notice = is_array($this -> notices) ? $this -> notices[0]['Content'] : $this -> notice;
						$post_install_notice = "<br /><b>" . $lang['notice'] .":</b> ". $post_notice;
					}
					$notice = $lang['notice_plugin_installed'] . $post_install_notice;
					$_response -> script("printMessage('notice', '{$notice}');");
					
					/* add menu item */
					if ( $this -> controller )
					{
						$menu_item = '<div class="mitem" id="mPlugin_'.$this -> key.'"><a href="'. RL_URL_HOME . ADMIN .'/index.php?controller='.$this -> controller.'">'.$this -> title.'<\/a><\/div>';
						$_response -> script( "
							$('#plugins_section').append('{$menu_item}');
							apMenu['plugins']['". $this -> key ."'] = new Array();
							apMenu['plugins']['". $this -> key ."']['Name'] = '". $this -> title ."';
							apMenu['plugins']['". $this -> key ."']['Controller'] = '". $this -> controller ."';
							apMenu['plugins']['". $this -> key ."']['Vars'] = '';
						" );
					}
				}
			}
			else
			{
				trigger_error("Can not install plugin (".$this -> title."), insert command failed", E_USER_WARNING);
				$GLOBALS['rlDebug'] -> logger("Can not install plugin (".$this -> title."), insert command failed");
			}

			if ( $remote_mode )
			{
				$callBack = $controller == 'home' ? 'xajax_getPluginsLog()' : "$(area).closest('li').fadeOut(); pluginsGrid.reload();";
				$_response -> script("
					var area = $('div.changelog_item a[name={$this -> key}]').closest('div.changelog_item');
					$(area).next().find('div.progress').html('{$lang['remote_progress_installation_completed']}');
					setTimeout(function(){ {$callBack} }, 1000);
					
					actions_locked = false;
				");
			}
			else
			{
				// reload grid
				$_response -> script("pluginsGrid.reload();");
			}
		}
		else
		{
			$_response -> script("printMessage('error', '{$lang['install_not_found']}');");
		}
		return $_response;
	}
	
	/**
	* update plugin
	*
	* @package xAjax
	*
	* @param string $key - plugin key
	* @param boolian $remote_mode - remote mode
	*
	**/
	function ajaxUpdate( $key = false, $remote_mode = false )
	{
		global $_response, $lang, $rlSmarty;

		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}
		
		if ( !$key )
		{
			return $_response;
		}
		
		$current_version = $this -> getOne('Version', "`Key` = '{$key}'", 'plugins');
		
		$path_to_update = RL_UPLOAD . $key . RL_DS. 'install.xml';
		
		if ( is_readable($path_to_update) )
		{
			require_once( RL_LIBS . 'saxyParser' . RL_DS . 'xml_saxy_parser.php' );

			$rlParser = new SAXY_Parser();
			$rlParser -> xml_set_element_handler(array(&$this, "startElement"), array(&$this, "endElement"));
			$rlParser -> xml_set_character_data_handler(array(&$this, "charData"));
			$rlParser -> xml_set_comment_handler(array(&$this, "commentElement"));

			// parse xml file
			$rlParser -> parse( file_get_contents($path_to_update) );

			$allLangs = $GLOBALS['languages'];
			
			$plugin = array(
				'fields' => array(
					'Name' => $this -> title,
					'Description' => $this -> description,
					'Version' => $this -> version,
					'Controller' => $this -> controller,
					'Uninstall' => $this -> uninstall,
					'Files' => serialize($this -> files)
				),
				'where' => array(
					'Key' => $this -> key
				)
			);
			
			$this -> loadClass( "Actions" );
			
			// update plugin
			foreach ( $this -> updates as $update_index => $update_item )
			{
				$success = true;

				if ( version_compare($update_item['Version'], $current_version) > 0 )
				{
					$lang_keys_insert = array();
					$lang_keys_update = array();
					
					/* import/update plugin files */
					if ( !empty( $update_item['Files'] ) )
					{
						$update_files = explode(',', $update_item['Files']);
						foreach ($update_files as $update_file)
						{
							$file_to_copy = str_replace(array('\\', '/'), array(RL_DS, RL_DS), $update_file);
							$this -> rlChmod(RL_PLUGINS . $key . RL_DS);
							
							if ( is_readable(RL_UPLOAD . $key . RL_DS . $file_to_copy) && is_writable(RL_PLUGINS . $key . RL_DS) )
							{
								/* warnings/errors output is disabled for the copy() operation, see error log */
								if ( false !== strpos($file_to_copy, RL_DS) )
								{
									$create_dir = substr($file_to_copy, 0, strrpos($file_to_copy, RL_DS));
									$this -> rlMkdir(RL_PLUGINS . $key . RL_DS . $create_dir);
									
									if ( is_readable(RL_PLUGINS . $key . RL_DS . $create_dir) && is_writable(RL_PLUGINS . $key . RL_DS . $create_dir) )
									{
										@copy( RL_UPLOAD . $key . RL_DS . $file_to_copy, RL_PLUGINS . $key . RL_DS . $file_to_copy );
									}
									else
									{
										$success = false;
										$GLOBALS['rlDebug'] -> logger("Plugin updating: unable to create directory {$create_dir} (mkdir) in ". $this -> key ." plugin.");
									}
								}
								else
								{
									$this -> rlChmod(RL_PLUGINS . $key . RL_DS . $file_to_copy);
									@copy( RL_UPLOAD . $key . RL_DS . $file_to_copy, RL_PLUGINS . $key . RL_DS . $file_to_copy );
								}
							}
							else
							{
								$success = false;
								$GLOBALS['rlDebug'] -> logger("Plugin updating: unable to copy/overwrite file {$update_file} in ". $this -> key ." plugin.");
							}
						}
					}
				
					if ( $success )
					{
						// install language's phrases
						$phrases = $this -> phrases;
						if ( !empty($phrases) )
						{
							foreach ($phrases as $key => $value)
							{
								if ( version_compare($value['Version'], $update_item['Version']) == 0 )
								{
									foreach ($allLangs as $lkey => $lval )
									{
										if ( $this -> getOne('ID', "`Key` = '{$phrases[$key]['Key']}' AND `Code` = '{$lval['Code']}'", 'lang_keys') )
										{
											/* update */
											$lang_keys_update[] = array(
												'fields' => array(
													'Module' => $phrases[$key]['Module'],
													'Value' => $phrases[$key]['Value']
												),
												'where' => array(
													'Code' => $lval['Code'],
													'Key' => $phrases[$key]['Key']
												)
											);
										}
										else
										{
											/* insert */
											$lang_keys_insert[] = array(
												'Code' => $lval['Code'],
												'Module' => $phrases[$key]['Module'],
												'Key' => $phrases[$key]['Key'],
												'Value' => $phrases[$key]['Value'],
												'Plugin' => $this -> key,
												'Status' => 'active'
											);
										}
									}
								}
							}
						}
						
						// update hooks
						$hooks = $this -> hooks;
						if ( !empty($hooks) )
						{
							foreach ($hooks as $key => $value)
							{
								if ( version_compare($value['Version'], $update_item['Version']) == 0 )
								{
									if ( $this -> getOne('ID', "`Name` = '{$value['Name']}' AND `Plugin` = '". $this -> key ."'", 'hooks') )
									{
										/* update */
										$hooks_update[] = array(
											'fields' => array(
												'Code' => $value['Code']
											),
											'where' => array(
												'Name' => $value['Name'],
												'Plugin' => $this -> key
											)
										);
									}
									else
									{
										/* insert */
										$hooks_insert_item = $value;
										unset($hooks_insert_item['Version']);
										$hooks_insert_item['Status'] = 'active';
										$hooks_insert[] = $hooks_insert_item;
									}
								}
							}
							
							if ( $hooks_update )
							{
								$GLOBALS['rlActions'] -> update($hooks_update, 'hooks');
							}
							
							if ( $hooks_insert )
							{
								$GLOBALS['rlActions'] -> insert($hooks_insert, 'hooks');
							}
						}
	
						// update configs' group
						$cGroup = $configGroup = $this -> configGroup;
						if ( !empty($configGroup) )
						{
							if ( version_compare($configGroup['Version'], $update_item['Version']) == 0 )
							{
								if ( $this -> getOne('ID', "`Key` = '{$configGroup['Key']}' AND `Plugin` = '". $this -> key ."'", 'config_groups') )
								{
									/* update */
									foreach ($allLangs as $lkey => $lval )
									{
										$lang_keys_update[] = array(
											'fields' => array(
												'Value' => $configGroup['Name']
											),
											'where' => array(
												'Code' => $lval['Code'],
												'Key' => 'config_groups+name+'. $configGroup['Key']
											)
										);
									}
								}
								else
								{
									/* insert */
									$cg_max_poss = $this -> getRow("SELECT MAX(`Position`) AS `max` FROM `" . RL_DBPREFIX . "config_groups` LIMIT 1");
									unset($cGroup['Name']);
									unset($cGroup['Version']);
									$cGroup['Position'] = $cg_max_poss['max']+1;
									
									$GLOBALS['rlActions'] -> insertOne($cGroup, 'config_groups');
									$group_id = mysql_insert_id();
									
									// add config group phrases
									foreach ($allLangs as $lkey => $lval )
									{
										$lang_keys_insert[] = array(
											'Code' => $lval['Code'],
											'Module' => 'admin',
											'Key' => 'config_groups+name+'.$configGroup['Key'],
											'Value' => $configGroup['Name'],
											'Plugin' => $this -> key,
											'Status' => 'active'
										);
									}
								}
							}
						}
						
						$group_id = empty($group_id) ? 0 : $group_id;
		
						// update configs
						$configs = $this -> configs;
						if (!empty($configs))
						{
							foreach ($configs as $key => $value)
							{
								if ( version_compare($value['Version'], $update_item['Version']) == 0 )
								{
									if ( $this -> getOne('ID', "`Key` = '{$value['Key']}' AND `Plugin` = '". $this -> key ."'", 'config') )
									{
										/* update */
										$configs_update[] = array(
											'fields' => array(
												'Default' => $value['Default'],
												'Values' => $value['Values'],
												'Type' => $value['Type'],
												'Data_type' => $value['Data_type']
											),
											'where' => array(
												'Key' => $value['Key'],
												'Plugin' => $this -> key
											)
										);
										
										foreach ($allLangs as $lkey => $lval )
										{
											if ( $this -> getOne('ID', "`Key` = 'config+name+{$value['Key']}' AND `Code` = '{$lval['Code']}'", 'lang_keys') )
											{
												/* update */
												$lang_keys_update[] = array(
													'fields' => array(
														'Value' => $value['Name']
													),
													'where' => array(
														'Code' => $lval['Code'],
														'Key' => 'config+name+'. $value['Key']
													)
												);
											}
											else
											{
												/* insert */
												$lang_keys_insert[] = array(
													'Code' => $lval['Code'],
													'Module' => 'admin',
													'Key' => 'config+name+'. $value['Key'],
													'Value' => $value['Name'],
													'Plugin' => $this -> key,
													'Status' => 'active'
												);
											}
										}
										
										if ( !empty($value['Description']) )
										{
											foreach ($allLangs as $lkey => $lval )
											{
												if ( !$this -> getOne('ID', "`Key` = 'config+des+{$value['Key']}' AND `Code` = '{$lval['Code']}'", 'lang_keys') )
												{
													$lang_keys_insert[] = array(
														'Code' => $lval['Code'],
														'Module' => 'admin',
														'Key' => 'config+des+'.$value['Key'],
														'Value' => $value['Description'],
														'Plugin' => $this -> key,
														'Status' => 'active'
													);
												}
											}
										}
									}
									else
									{
										/* insert */
										foreach ($allLangs as $lkey => $lval )
										{
											$lang_keys_insert[] = array(
												'Code' => $lval['Code'],
												'Module' => 'admin',
												'Key' => 'config+name+'.$value['Key'],
												'Value' => $value['Name'],
												'Plugin' => $this -> key,
												'Status' => 'active'
											);
											
											if ( !empty($value['Description']) )
											{
												$lang_keys_insert[] = array(
													'Code' => $lval['Code'],
													'Module' => 'admin',
													'Key' => 'config+des+'.$value['Key'],
													'Value' => $value['Description'],
													'Plugin' => $this -> key,
													'Status' => 'active'
												);
											}
										}
										$position = $key;
										
										if ( $configs[$key]['Group'] )
										{
											$max_pos = $this -> getRow("SELECT MAX(`Position`) AS `Max` FROM `".RL_DBPREFIX."config` WHERE `Group_ID` = '{$value['Group']}' LIMIT 1");
											$position = $max_pos['Max'] + $key;
										}
										
										if ( $configGroup['Key'] )
										{
											$group_id = $this -> getOne('ID', "`Key` = '{$configGroup['Key']}' AND `Plugin` = '". $this -> key ."'", 'config_groups');
										}
										
										$configs_insert[] = array(
											'Group_ID' => !$group_id ? $value['Group'] : $group_id,
											'Position' => $position,
											'Key' => $value['Key'],
											'Default' => $value['Default'],
											'Values' => $value['Values'],
											'Type' => $value['Type'],
											'Data_type' => $value['Data_type'],
											'Plugin' => $this -> key
										);
									}
								}
							}
							
							if ( !empty($configs_update) )
							{
								$GLOBALS['rlActions'] -> update($configs_update, 'config');
							}
							
							if ( !empty($configs_insert) )
							{
								$GLOBALS['rlActions'] -> insert($configs_insert, 'config');
							}
						}
	
						// update blocks
						$blocks = $this -> blocks;
						if (!empty($blocks))
						{
							foreach ( $blocks as $key => $value )
							{
								if ( version_compare($value['Version'], $update_item['Version']) == 0 )
								{
									if ( in_array(strtolower($value['Type']), array('html', 'php', 'smarty')) )
									{
										if ( $this -> getOne('ID', "`Key` = '{$value['Key']}' AND `Plugin` = '". $this -> key ."'", 'blocks') )
										{
											/* update */
											$block_update = array(
												'fields' => array(
													'Type' => $value['Type'],
													'Content' => $value['Content'],
													'Readonly' => $value['Readonly']
												),
												'where' => array(
													'Key' => $value['Key'],
													'Plugin' => $this -> key
												)
											);
											
											if ( strtolower($value['Type']) == 'html' )
											{
												unset($block_update['fields']['Content']);
											}
											
											$GLOBALS['rlActions'] -> updateOne($block_update, 'blocks');
										}
										else
										{
											$block_max_poss = $this -> getRow("SELECT MAX(`Position`) AS `max` FROM `" . RL_DBPREFIX . "blocks` LIMIT 1");
											$blocks[$key]['Position'] = $block_max_poss['max']+1;
											
											// add name phrases
											foreach ($allLangs as $lkey => $lval )
											{
												$lang_keys_insert[] = array(
													'Code' => $lval['Code'],
													'Module' => 'common',
													'Key' => 'blocks+name+'.$value['Key'],
													'Value' => $value['Name'],
													'Plugin' => $this -> key,
													'Status' => 'active'
												);
											}
					
											if ( strtolower($value['Type']) == 'html' )
											{
												foreach ($allLangs as $lkey => $lval )
												{
													$lang_keys_insert[] = array(
														'Code' => $lval['Code'],
														'Module' => 'common',
														'Key' => 'blocks+content+'. $value['Key'],
														'Value' => $value['Content'],
														'Plugin' => $this -> key,
														'Status' => 'active'
													);
												}
												unset($blocks[$key]['Content']);
											}
											unset($blocks[$key]['Name']);
											unset($blocks[$key]['Version']);
											$blocks[$key]['Status'] = 'active';
											
											$GLOBALS['rlActions'] -> insertOne($blocks[$key], 'blocks');
										}
									}
								}
							}
						}
						
						// update admin panel blocks
						$aBlocks = $this -> aBlocks;
						if (!empty($aBlocks))
						{
							foreach ( $aBlocks as $key => $value )
							{
								if ( version_compare($value['Version'], $update_item['Version']) == 0 )
								{
									if ( $this -> getOne('ID', "`Key` = '{$value['Key']}' AND `Plugin` = '". $this -> key ."'", 'admin_blocks') )
									{
										/* update */
										$aBlock_update = array(
											'fields' => array(
												'Ajax' => $value['Ajax'],
												'Content' => $value['Content'],
												'Fixed' => $value['Fixed']
											),
											'where' => array(
												'Key' => $value['Key'],
												'Plugin' => $this -> key
											)
										);
										
										$GLOBALS['rlActions'] -> updateOne($aBlock_update, 'admin_blocks');
									}
									else
									{
										$aBlock_max_poss = $this -> getRow("SELECT MAX(`Position`) AS `max` FROM `" . RL_DBPREFIX . "admin_blocks` WHERE `Column` = 'column{$value['Column']}' LIMIT 1");
										$aBlocks[$key]['Position'] = $aBlock_max_poss['max']+1;
				
										// add name phrases
										foreach ($allLangs as $lkey => $lval )
										{
											$lang_keys_insert[] = array(
												'Code' => $lval['Code'],
												'Module' => 'admin',
												'Key' => 'admin_blocks+name+'.$value['Key'],
												'Value' => $value['Name'],
												'Plugin' => $this -> key,
												'Status' => 'active'
											);
										}
										
										$aBlocks[$key]['name'] = $aBlocks[$key]['Name'];
										$rlSmarty -> assign('block', $aBlocks[$key]);
										
										unset($aBlocks[$key]['Name']);
										unset($aBlocks[$key]['name']);
										unset($aBlocks[$key]['Version']);
										$aBlocks[$key]['Column'] = 'column'. $aBlocks[$key]['Column'];
										$aBlocks[$key]['Status'] = 'active';
											
										$GLOBALS['rlActions'] -> insertOne($aBlocks[$key], 'admin_blocks');
										
										// append new block
										$tpl = 'blocks' . RL_DS . 'homeDrugDrop_block.tpl';
										$_response -> append('tmp_dom_blocks_store', 'innerHTML', $rlSmarty -> fetch( $tpl, null, null, false ));
										$_response -> script("
											$('#tmp_dom_blocks_store div.block').hide();
											$('td.column{$value['Column']} div.sortable').append($('#tmp_dom_blocks_store div.block'));
											$('td.column{$value['Column']} div.sortable div.block:last').fadeIn('slow');
										");
									}
								}
							}
						}
	
						// update pages
						$pages = $this -> pages;
						if ( !empty($pages) )
						{
							foreach ( $pages as $key => $value )
							{
								if ( in_array($value['Page_type'], array('system', 'static', 'external')) )
								{
									if ( version_compare($value['Version'], $update_item['Version']) == 0 )
									{
										if ( $this -> getOne('ID', "`Key` = '{$value['Key']}' AND `Plugin` = '". $this -> key ."'", 'pages') )
										{
											$page_update = array(
												'fields' => array(
													'Page_type' => $value['Page_type'],
													'Get_vars' => $value['Get_vars'],
													'Controller' => $value['Controller'],
													'Deny' => $value['Deny'],
													'Tpl' => $value['Tpl'],
													'Readonly' => $value['Readonly']
												),
												'where' => array(
													'Key' => $key['Key'],
													'Plugin' => $this -> key
												)
											);
											
											$GLOBALS['rlActions'] -> updateOne($page_update, 'pages');
										}
										else
										{
											$page_max_poss = $this -> getRow("SELECT MAX(`Position`) AS `max` FROM `" . RL_DBPREFIX . "pages` LIMIT 1");
											$pages[$key]['Position'] = $page_max_poss['max']+1;
	
											// add name phrases
											foreach ($allLangs as $lkey => $lval )
											{
												$lang_keys_insert[] = array(
													'Code' => $lval['Code'],
													'Module' => 'common',
													'Key' => 'pages+name+'.$value['Key'],
													'Value' => $value['Name'],
													'Plugin' => $this -> key,
													'Status' => 'active'
												);
												
												$lang_keys_insert[] = array(
													'Code' => $lval['Code'],
													'Module' => 'common',
													'Key' => 'pages+title+'.$value['Key'],
													'Value' => $value['Name'],
													'Plugin' => $this -> key,
													'Status' => 'active'
												);
											}
					
											switch ($value['Page_type']){
												case 'static':
													foreach ($allLangs as $lkey => $lval )
													{
														$lang_keys_insert[] = array(
															'Code' => $lval['Code'],
															'Module' => 'common',
															'Key' => 'pages+content+'.$value['Key'],
															'Value' => $value['Content'],
															'Plugin' => $this -> key,
															'Status' => 'active'
														);
													}
													break;
												case 'system':
													/* reassign to referent :) */
													$pages[$key]['Controller'] = $pages[$key]['Controller'];
													break;
												case 'external':
													$pages[$key]['Controller'] = $pages[$key]['Content'];
													break;
											}
											
											unset($pages[$key]['Name']);
											unset($pages[$key]['Content']);
											unset($pages[$key]['Version']);
											$pages[$key]['status'] = 'active';
											
											$GLOBALS['rlActions'] -> insertOne($pages[$key], 'pages');
										}
									}
								}
							}
						}
	
						// update email templates
						$emails = $this -> emails;
						if ( !empty($emails) )
						{
							foreach ( $emails as $key => $value )
							{
								if ( version_compare($value['Version'], $update_item['Version']) == 0 )
								{
									if ( !$this -> getOne('ID', "`Key` = '{$value['Key']}' AND `Plugin` = '". $this -> key ."'", 'email_templates') )
									{
										$email_max_poss = $this -> getRow("SELECT MAX(`Position`) AS `max` FROM `" . RL_DBPREFIX . "email_templates` LIMIT 1");
										$emails[$key]['Position'] = $email_max_poss['max']+1;
										
										// add name phrases
										foreach ($allLangs as $lkey => $lval )
										{
											$lang_keys_insert[] = array(
												'Code' => $lval['Code'],
												'Module' => 'email_tpl',
												'Key' => 'email_templates+subject+'.$value['Key'],
												'Value' => $value['Subject'],
												'Plugin' => $this -> key,
												'Status' => 'active'
											);
											$lang_keys_insert[] = array(
												'Code' => $lval['Code'],
												'Module' => 'email_tpl',
												'Key' => 'email_templates+body+'.$value['Key'],
												'Value' => $value['Body'],
												'Plugin' => $this -> key,
												'Status' => 'active'
											);
										}
										unset($emails[$key]['Subject']);
										unset($emails[$key]['Body']);
										unset($emails[$key]['Version']);
										$emails[$key]['Status'] = 'active';
										
										$GLOBALS['rlActions'] -> insertOne($emails[$key], 'email_templates');
									}
								}
							}
						}
						
						// eval update code
						if ( !empty($update_item['Code']) )
						{
							@eval($update_item['Code']);
						}
						
						// add phrases
						if ( !empty($lang_keys_insert) )
						{
							$GLOBALS['rlActions'] -> insert($lang_keys_insert, 'lang_keys');
						}
						
						// update phrases
						if ( !empty($lang_keys_update) )
						{
							$GLOBALS['rlActions'] -> update($lang_keys_update, 'lang_keys');
						}
						
						$plugin_version_update = array(
							'fields' => array(
								'Version' => $update_item['Version']
							),
							'where' => array(
								'Key' => $this -> key
							)
						);
						
						$GLOBALS['rlActions'] -> updateOne( $plugin_version_update, 'plugins' );
					}
				}
			}
				
			/* delete unzipped plugin from TMP */
			$this -> deleteDirectory(RL_UPLOAD . $key . RL_DS);

			if ( $success && $GLOBALS['rlActions'] -> updateOne( $plugin, 'plugins' ) )
			{	
				$post_update_notice = $lang['plugin_updated'];
				
				/* print notices */
				if ( !empty($this -> notices) )
				{
					$post_update_notice .= "<br /><br /><ul>";
					foreach ( $this -> notices as $key => $value )
					{
						if ( version_compare($value['Version'], $current_version) > 0 )
						{
							$post_update_notice .= "<li><b>" . $lang['notice'] ." ({$lang['version']} {$value['Version']}):</b> ". $value['Content'] ."</li>";
						}
					}
					$post_update_notice .= "</ul>";
				}
				
				$_response -> script("printMessage('notice', '{$post_update_notice}');");
				
				/* add menu item */
				if ( $this -> controller && version_compare($this -> controllerUpdate, $current_version) > 0 )
				{
					$menu_item = '<div class="mitem" id="mPlugin_'.$this -> key.'"><a href="'. RL_URL_HOME . ADMIN .'/index.php?controller='.$this -> controller.'">'.$this -> title.'<\/a><\/div>';
					$_response -> script( "
						$('#plugins_section').append('{$menu_item}');
						apMenu['plugins']['". $this -> key ."'] = new Array();
						apMenu['plugins']['". $this -> key ."']['Name'] = '". $this -> title ."';
						apMenu['plugins']['". $this -> key ."']['Controller'] = '". $this -> controller ."';
						apMenu['plugins']['". $this -> key ."']['Vars'] = '';
					" );
				}
				
				if ( $remote_mode )
				{
					$_response -> script("
						var area = $('div.changelog_item a[name=". $this -> key ."]').closest('div.changelog_item');
						$(area).next().find('div.progress').html('{$lang['remote_progress_update_completed']}');
						setTimeout(function(){ xajax_getPluginsLog() }, 1000);
						
						actions_locked = false;
					");
					
					return $_response;
				}
				else
				{
					// reload grid
					$_response -> script("
						pluginsGrid.reload();
						$('#update_area').fadeOut();
					");
				}
			}
			else
			{
				$_response -> script("printMessage('error', '{$lang['install_fail_files_upload']}');");
				$GLOBALS['rlDebug'] -> logger("Cannot update plugin (".$this -> title."), success variable returned FALSE.");
			}
		}
		else
		{
			$_response -> script("printMessage('error', '{$lang['install_not_found']}');");
			$GLOBALS['rlDebug'] -> logger("Cannot update plugin (".$this -> title."), '{$path_to_update}' does not found.");
		}
		
		$_response -> call('hideProgressBar');
		
		return $_response;
	}
	
	function startElement($parser, $name, $attributes)
	{
		$this->level++;
		$this->inTag = $name;
		$this->attributes = $attributes;
				
		if( $this->inTag == 'plugin' && isset($attributes['name']) )
		{
			$this -> key = $GLOBALS['rlValid'] -> xSql($attributes['name']);
		}

		$this->path[] = $name;
	}

	function endElement($parser, $name)
	{
		$this -> level--;
	}

	function charData($parser, $text)
	{
		switch($this->inTag)
		{
			case "hook":
				$this -> hooks[] = array(
					"Name"		=> $this -> attributes['name'],
					"Version"	=> $this -> attributes['version'],
					"Code"		=> $text,
					"Plugin"	=> $this -> key,
					"Status"	=> "approval"
				);
				
				/* remove Version index */
				if ( $this -> noVersionTag )
				{
					$itemIndex = count($this -> hooks) - 1;
					unset($this -> hooks[$itemIndex]['Version']);
				}
				
				break;
			case "phrase":
				$this -> phrases[] = array(
					"Key"		=> $this -> attributes['key'],
					"Version"	=> $this -> attributes['version'],
					"Module"	=> $this -> attributes['module'],
					"Value"		=> $text
				);
				break;
			case 'configs':
				$this -> configGroup = array(
					"Key"		=> $this -> attributes['key'],
					"Version"	=> $this -> attributes['version'],
					"Name"		=> $this -> attributes['name'],
					"Plugin"	=> $this -> key
				);
				
				/* remove Version index */
				if ( $this -> noVersionTag )
				{
					unset($this -> configGroup['Version']);
				}
				
				break;
			case "config":
				$this -> configs[] = array(
					"Key"		=> $GLOBALS['rlValid'] -> xSql($this -> attributes['key']),
					"Version"	=> $this -> attributes['version'],
					"Group"		=> $GLOBALS['rlValid'] -> xSql($this -> attributes['group']),
					"Name"		=> $GLOBALS['rlValid'] -> xSql($this -> attributes['name']),
					"Description"=> $GLOBALS['rlValid'] -> xSql($this -> attributes['description']),
					"Default"	=> $text,
					"Values"	=> $GLOBALS['rlValid'] -> xSql($this -> attributes['values']),
					"Type"		=> $GLOBALS['rlValid'] -> xSql($this -> attributes['type']),
					"Data_type"	=> $GLOBALS['rlValid'] -> xSql($this -> attributes['validate']),
					"Plugin"	=> $this -> key
				);
				break;
			case "block":
				$this -> blocks[] = array(
					"Key"		=> $GLOBALS['rlValid'] -> xSql($this -> attributes['key']),
					"Version"	=> $this -> attributes['version'],
					"Name"		=> $GLOBALS['rlValid'] -> xSql($this -> attributes['name']),
					"Side"		=> $GLOBALS['rlValid'] -> xSql($this -> attributes['side']),
					"Type"		=> $GLOBALS['rlValid'] -> xSql($this -> attributes['type']),
					"Readonly"	=> (isset($this -> attributes['lock']) && $this -> attributes['lock'] == 0) ? 0 : 1,
					"Tpl"		=> (int)$this -> attributes['tpl'],
					"Content"	=> $text,
					"Plugin"	=> $this -> key,
					"Status"	=> "approval",
					"Sticky"	=> 1
				);
				break;
			case "aBlock":
				$this -> aBlocks[] = array(
					"Key"		=> $GLOBALS['rlValid'] -> xSql($this -> attributes['key']),
					"Version"	=> $this -> attributes['version'],
					"Name"		=> $GLOBALS['rlValid'] -> xSql($this -> attributes['name']),
					"Content"	=> $text,
					"Plugin"	=> $this -> key,
					"Status"	=> "approval",
					"Column"	=> (int)$this -> attributes['column'],
					"Ajax"		=> (int)$this -> attributes['ajax'],
					"Fixed"		=> (int)$this -> attributes['fixed']
				);
				break;
			case "page":
				$this -> pages[] = array(
					"Key"		=> $GLOBALS['rlValid'] -> xSql($this -> attributes['key']),
					"Version"	=> $this -> attributes['version'],
					"Login"		=> (int)$this -> attributes['login'],
					"Name"		=> $GLOBALS['rlValid'] -> xSql($this -> attributes['name']),
					"Page_type"	=> $GLOBALS['rlValid'] -> xSql($this -> attributes['type']),
					"Path"		=> $GLOBALS['rlValid'] -> xSql($this -> attributes['path']),
					"Get_vars"	=> $GLOBALS['rlValid'] -> xSql($this -> attributes['get']),
					"Controller"=> $GLOBALS['rlValid'] -> xSql($this -> attributes['controller']),
					"Menus"		=> $GLOBALS['rlValid'] -> xSql($this -> attributes['menus']),
					"Tpl"		=> (int)$this -> attributes['tpl'],
					"Content"	=> $text,
					"Plugin"	=> $this -> key
				);
				break;
			case "email":
				$this -> emails[] = array(
					"Key"		=> $GLOBALS['rlValid'] -> xSql($this -> attributes['key']),
					"Version"	=> $this -> attributes['version'],
					"Subject"	=> $GLOBALS['rlValid'] -> xSql($this -> attributes['subject']),
					"Body"		=> $text,
					"Plugin"	=> $this -> key
				);
				break;
			case "update":
				$this -> updates[] = array(
					"Version"	=> $this -> attributes['version'],
					"Files"		=> $this -> attributes['files'],
					"Code"		=> $text
				);
				break;
			case "notice":
				$this -> notices[] = array(
					"Version"	=> $this -> attributes['version'],
					"Content"	=> $text
				);
				break;
			case 'file';
				$this -> files[] = $text;
				break;
			case "version":
			case "date":
			case "title":
			case "description":
			case "author":
			case "owner":
			case "controller":
				$this -> controllerUpdate = $this -> attributes['version'];
			case "install":
			case "uninstall":
			case "notice":
				$this -> {$this->inTag} = $text;
			break;
		}
	}
	
	/**
	* uninstall plugin
	*
	* @package xAjax
	*
	* @param string $name - plugin key
	*
	**/
	function ajaxUnInstall( $key = false )
	{
		global $_response, $lang;

		/* get plugin info */
		$plugin_info = $this -> fetch( array('Files', 'Uninstall'), array('Key' => $key), null, 1, 'plugins', 'row' );
		
		if ( !empty($plugin_info) )
		{
			/* uninstall components */
			$tables = array( 'lang_keys', 'hooks', 'config', 'config_groups', 'blocks', 'admin_blocks', 'pages', 'email_templates' );
						
			foreach ( $tables as $table )
			{
				$this -> query( "DELETE FROM `". RL_DBPREFIX . $table . "` WHERE `Plugin` = '{$key}'" );
			}
	
			/* uninstall plugin data */
			$this -> query( "DELETE FROM `". RL_DBPREFIX . "plugins` WHERE `Key` = '{$key}'" );
			
			/* eval uninstall code */
			if ( !empty($plugin_info['Uninstall']) )
			{
				@eval($plugin_info['Uninstall']);
			}
			
			// reload grid
			$_response -> script("pluginsGrid.reload();");
			$_response -> script("printMessage('notice', '{$lang['notice_plugin_uninstalled']}');");
			
			/* remove menu item */
			$_response -> script( "
				$('#mPlugin_{$key}').remove();
				apMenu['plugins']['{$key}'] = false;
			" );
		}
		
		return $_response;
	}
	
	/**
	* remote plugin installtion
	*
	* @package xAjax
	*
	* @param string $key - plugin key
	* @param bool $direct - direct install through plugins manager
	*
	**/
	function ajaxRemoteInstall( $key = false, $direct = false )
	{
		global $_response, $lang;
		
		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}
		
		eval(base64_decode(RL_SETUP));
		
		if ( $key && $license_domain && $license_number )
		{
			$destination = RL_PLUGINS . $key . '.zip';
			$copy = "http://www.flynax.com/_request/remote-plugin-upload.php?key={$key}&domain={$license_domain}&license={$license_number}";
			$target = RL_PLUGINS . $key . '/';
			
			/* change progress status */
			$_response -> script("
				var area = $('div.changelog_item a[name={$key}]').closest('div.changelog_item');
				$(area).next().find('div.progress').html('{$lang['remote_progress_download']}');
			");
			
			//exit; // TO AVOID STABLE PLUGINS OVERWRITING

			/* copy remote file */
			if ( @fopen($copy, 'r') )
			{
				// do direct copy using copy() function
				if ( !copy($copy, $destination) )
				{
					// alternative copy by stream to stream copy
					$source = file_get_contents($copy);
	
					$handle = fopen($destination, "w");
					fwrite($handle, $source);
					fclose($handle);
				}
				
				$this -> rlChmod($destination);
				
				if ( is_readable( $destination ) )
				{
					// create plugin folder
					$this -> rlMkdir($target);
					
					/* unzip file */
					require_once( RL_CLASSES . 'dUnzip2.class.php' );
					$unzip = new dUnzip2($destination);
					$unzip -> unzipAll($target);
					$unzip -> __destroy();
					unset($unzip);
	
					/* remove zip archive */
					unlink($destination);
	
					if ( is_readable( "{$target}install.xml" ) )
					{
						/* call direct install method */
						$_response -> script("setTimeout(function(){ continueInstallation('{$key}') }, 1000);");
					}
					else
					{
						$_response -> script("printMessage('error', '{$lang['plugin_download_fail']}');");
						$GLOBALS['rlDebug'] -> logger("Unable to use remote plugin downloading wizard, downloading/extracting file fail.");
						
						$_response -> call('hideProgressBar');
					}
					
					return $_response;
				}
				else
				{
					$_response -> script("printMessage('error', '{$lang['plugin_download_fail']}');");
					$GLOBALS['rlDebug'] -> logger("Unable to use remote plugin downloading wizard, downloading/extracting file fail.");
				}
			}
			else
			{
				$_response -> script("printMessage('error', '{$lang['flynax_connect_fail']}');");
				$GLOBALS['rlDebug'] -> logger("Unable to use remote plugin downloading wizard, connect fail.");
			}
		}
		else
		{
			$_response -> script("printMessage('alert', '{$lang['plugin_download_deny']}');");
			$GLOBALS['rlDebug'] -> logger("Unable to use remote plugin downloading wizard, license conflict.");
		}
		
		$_response -> call('hideProgressBar');
		
		return $_response;
	}
	
	/**
	* remote plugin update
	*
	* @package xAjax
	*
	* @param string $key - plugin key
	* @param bool $direct - direct update through plugins manager
	*
	**/
	function ajaxRemoteUpdate( $key = false, $direct = false )
	{
		global $_response, $lang;
		
		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}
		
		eval(base64_decode(RL_SETUP));
		
		if ( $key && $license_domain && $license_number )
		{
			/* get plugin info */
			$plugin = $this -> fetch(array('Version'), array('Key' => $key), null, 1, 'plugins', 'row');
			
			/* backup current plugin version */
			if ( is_writable( RL_ROOT .'backup'. RL_DS .'plugins'. RL_DS ) )
			{
				$this -> loadClass('Zip');
				
				$plugin_path = RL_PLUGINS . $key . RL_DS;
				$plugin_backup_path = RL_ROOT .'backup'. RL_DS .'plugins'. RL_DS . $key ."({$plugin['Version']})_". date('d.m.Y') .".zip";
				
				$GLOBALS['rlZip'] -> get_files_from_folder($plugin_path, '');
				$file = fopen($plugin_backup_path, "wb");
				fwrite($file, $GLOBALS['rlZip'] -> getZippedfile());
				fclose($file);
				
				/* backup hooks */
				$this -> setTable('hooks');
				$backup_hooks = $this -> fetch(array('Name', 'Code'), array('Plugin' => $key));
				if ( $backup_hooks )
				{	
					foreach ($backup_hooks as $index => $backup_hook)
					{
						$file_content .= <<< VS
{$backup_hook['Name']}\r\n{$backup_hook['Code']}\r\n\r\n
VS;
					}
					
					$hooks_backup_path = RL_ROOT .'backup'. RL_DS .'plugins'. RL_DS . $key ."({$plugin['Version']})_". date('d.m.Y') .".txt";
					$file = fopen($hooks_backup_path, 'w+');
					
					fwrite($file, $file_content);
					fclose($file);
				}
				
				$destination = RL_UPLOAD . $key . '.zip';
				$copy = "http://www.flynax.com/_request/remote-plugin-upload.php?key={$key}&domain={$license_domain}&license={$license_number}";
				$target = RL_UPLOAD . $key . '/';
				
				/* copy remote file */
				if ( @fopen($copy, 'r') )
				{
					/* change progress status */
					if ( $direct )
					{
						$_response -> script("$('div#progress div.progress').html('{$lang['remote_progress_download']}')");
					}
					else
					{
						$_response -> script("
							var area = $('div.changelog_item a[name={$key}]').closest('div.changelog_item');
							$(area).next().find('div.progress').html('{$lang['remote_progress_download']}');
						");
					}
					
					// do direct copy using copy() function
					if ( !copy($copy, $destination) )
					{
						// alternative copy by stream to stream copy
						$source = file_get_contents($copy);
		
						$handle = fopen($destination, "w");
						fwrite($handle, $source);
						fclose($handle);
					}

					$this -> rlChmod($destination);
					
					if ( is_readable($destination) )
					{
						// create plugin folder
						$this -> rlMkdir($target);
						
						/* unzip file */
						require_once( RL_CLASSES . 'dUnzip2.class.php' );
						$unzip = new dUnzip2($destination);
						$unzip -> unzipAll($target);
						$unzip -> close();
						unset($unzip);
						
						/* remove zip archive */
						unlink($destination);
						
						if ( is_readable( "{$target}install.xml" ) )
						{
							/* call direct install method */
							$_response -> script("setTimeout(function(){ continueUpdating('{$key}') }, 1000);");
						}
						else
						{
							if ( $direct )
							{
								$_response -> script("$('#update_info').fadeIn();");
							}
							$_response -> script("printMessage('error', '{$lang['plugin_download_fail']}');");
							$GLOBALS['rlDebug'] -> logger("Unable to use remote plugin downloading wizard, downloading/extracting file fail.");

							$_response -> call('hideProgressBar');
						}
						
						return $_response;
					}
					else
					{
						if ( $direct )
						{
							$_response -> script("$('#update_info').fadeIn();");
						}
						$_response -> script("printMessage('error', '{$lang['plugin_download_fail']}');");
						$_response -> script( "setTimeout(function(){ xajax_getPluginsLog() }, 1000);" );
						$GLOBALS['rlDebug'] -> logger("Unable to use remote plugin downloading wizard, downloading/extracting file fail.");
					}
				}
				else
				{
					if ( $direct )
					{
						$_response -> script("$('#update_info').fadeIn();");
					}
					$_response -> script("printMessage('error', '{$lang['flynax_connect_fail']}');");
					$GLOBALS['rlDebug'] -> logger("Unable to use remote plugin downloading wizard, connect fail.");
				}
			}
			else
			{
				if ( $direct )
				{
					$_response -> script("$('#update_info').fadeIn();");
				}
				$_response -> script("printMessage('alert', '{$lang['plugin_backingup_deny']}');");
				$_response -> script( "setTimeout(function(){ xajax_getPluginsLog() }, 1000);" );
				
				$GLOBALS['rlDebug'] -> logger("Unable to backup current plugin version.");
			}
		}
		else
		{
			if ( $direct )
			{
				$_response -> script("$('#update_info').fadeIn();");
			}
			$_response -> script("printMessage('alert', '{$lang['plugin_download_deny']}');");
			$GLOBALS['rlDebug'] -> logger("Unable to use remote plugin downloading wizard, license conflict.");
		}
		
		$_response -> call('hideProgressBar');
		
		return $_response;
	}
	
	/**
	* browse plugins
	*
	* @package xAjax
	*
	**/
	function ajaxBrowsePlugins()
	{
		global $_response, $config, $lang, $rlSmarty;
		
		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}

		/* scan plugins directory */
		$plugins_exist = $this -> scanDir(RL_PLUGINS, true);
		
		/* parse xml */
		$plugins = $this -> getPageContent($config['flynax_plugins_browse_feed']);

		$this -> loadClass( 'Rss' );
		$GLOBALS['rlRss'] -> items_number = 200;
		$GLOBALS['rlRss'] -> items = array('key', 'name', 'version', 'date');
		$GLOBALS['rlRss'] -> createParser($plugins);
		$plugins = $GLOBALS['rlRss'] -> getRssContent();

		if ( !$plugins )
		{
			$_response -> script("
				printMessage('error', '{$lang['flynax_connect_fail']}');
				$('#nav_bar span.center_search').html('{$lang['browse']}');
			");
			return $_response;
		}
		
		foreach ($plugins as $key => $plugin)
		{
			if ( false !== array_search($plugin['key'], $plugins_exist) )
			{
				unset($plugins[$key]);
			}
		}

		$rlSmarty -> assign_by_ref('plugins', $plugins);
		
		/* build DOM */
		$tpl = 'blocks'. RL_DS .'flynaxPluginsBrowse.block.tpl';
		$_response -> assign('browse_content', 'innerHTML', $rlSmarty -> fetch( $tpl, null, null, false ) );
		$_response -> script("
			$('#update_area').slideUp('fast');
			$('#browse_area').slideDown('normal');
			$('#nav_bar span.center_search').html('{$lang['browse']}');
			plugins_loaded = true;
		");
		
		$_response -> call('rlPluginRemoteInstall');
		
		return $_response;
	}
}
