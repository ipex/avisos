<?php

/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: PLUGINS.INC.PHP
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

/* ext js action */
if ($_GET['q'] == 'ext')
{
	/* system config */
	require_once( '../../includes/config.inc.php' );
	require_once( RL_ADMIN_CONTROL . 'ext_header.inc.php' );
	require_once( RL_LIBS . 'system.lib.php' );
	
	/* date update */
	if ($_GET['action'] == 'update' )
	{
		$reefless -> loadClass( 'Actions' );
		
		$type = $rlValid -> xSql( $_GET['type'] );
		$field = $rlValid -> xSql( $_GET['field'] );
		$value = $rlValid -> xSql( nl2br($_GET['value']) );
		$id = (int)$_GET['id'];
		$key = $rlValid -> xSql( $_GET['key'] );

		$files_exist = true;
		
		if ( $field == 'Status' && $id )
		{
			/* activete/deactivate plugin */
			$plugin_info = $rlDb -> fetch( array('Key', 'Files'), array('ID' => (int)$id), null, 1, 'plugins', 'row' );
			
			if ( empty($plugin_info) )
			{
				exit;
			}

			if ( $value == 'active' )
			{
				$files = unserialize($plugin_info['Files']);
				foreach ($files as $file)
				{
					$file = str_replace(array('\\', '/' ), array(RL_DS, RL_DS), $file);
					
					if (!is_readable( RL_PLUGINS . $plugin_info['Key'] . RL_DS . $file ))
					{
						$files_exist = false;
						$files .= RL_DS . "plugins" . RL_DS . $plugin_info['Key'] . RL_DS . "<b>". $file . "</b><br />";
					}
				}
			}

			if ( $files_exist === true )
			{
				$tables = array( 'lang_keys', 'hooks', 'blocks', 'admin_blocks', 'pages', 'email_templates' );
	
				foreach ( $tables as $table )
				{
					unset($plugin_update);
					$plugin_update = array(
						'fields' => array(
							'Status' => $value
						),
						'where' => array(
							'Plugin' => $plugin_info['Key']
						)
					);
					$rlActions -> updateOne( $plugin_update, $table);
				}
			}
			else
			{
				$message = str_replace( '{files}', "<br />".$files, $lang['plugin_files_missed'] );
				echo $message;
			}
		}
		
		if ( $files_exist === true )
		{
			$updateData = array(
				'fields' => array(
					$field => $value
				),
				'where' => array(
					'ID' => $id
				)
			);
			
			$rlHook -> load('apExtPluginsUpdate');
			
			$rlActions -> updateOne( $updateData, 'plugins');
		}
		exit;
	}
	
	/* data read */
	$limit = $rlValid -> xSql( $_GET['limit'] );
	$start = $rlValid -> xSql( $_GET['start'] );

	$sql = "SELECT * FROM `" . RL_DBPREFIX . "plugins`";
	$data = $rlDb -> getAll( $sql );

	foreach ( $data as $key => $value )
	{
		$data[$key]['Status'] = $GLOBALS['lang'][$data[$key]['Status']];
		$insPlugins[$data[$key]['Key']] = $data[$key];
	}

	/* scan plugins directory */
	$plugins = $reefless -> scanDir(RL_PLUGINS, true);

	$pos = 0;
	foreach ($plugins as $key => $value)
	{
		if ( $key >= $start && $key < $start + $limit )
		{
			if ( isset($insPlugins[$plugins[$key]]) )
			{
				$plugins_out[$pos] = $insPlugins[$plugins[$key]];
			}
			else
			{
				$plugins_out[$pos] = array(
					'Name' => $plugins[$key],
					'Key' => $plugins[$key].'|not_installed',
					'Version' => $lang['not_available'],
					'Description' => $lang['not_available'],
					'Status' => 'not_installed'
				);
			}
			$pos++;
		}
	}
	
	$rlHook -> load('apExtPluginsData');

	$reefless -> loadClass( 'Json' );
	
	$output['total'] = count($plugins);
	$output['data'] = $plugins_out ? $plugins_out : array();

	echo $rlJson -> encode( $output );
}
/* ext js action end */

/* ajax action */
elseif ( $_REQUEST['q'] == 'ajax' )
{
	/* system config */
	require_once( '../../includes/config.inc.php' );
	require_once( RL_ADMIN_CONTROL . 'ext_header.inc.php' );
	require_once( RL_LIBS . 'system.lib.php' );
	
	$id = (int)$_GET['id'];
	
	if ( empty($id) )
		exit;
	
	if ( $_REQUEST['action'] == 'check_complete' )
	{
		$plugin_info = $rlDb -> fetch( array('Key', 'Files'), array('ID' => (int)$id), null, 1, 'plugins', 'row' );
			
		if ( empty($plugin_info) )
			exit;

		$files = unserialize($plugin_info['Files']);
		foreach ($files as $file)
		{
			$file = str_replace(array('\\', '/' ), array(RL_DS, RL_DS), $file);
			
			if (!is_readable( RL_PLUGINS . $plugin_info['Key'] . RL_DS . $file ))
			{
				$files_exist = false;
				$message .= RL_DS . "plugins" . RL_DS . $plugin_info['Key'] . RL_DS . "<b>". $file . "</b><br />";
			}
		}
		
		$reefless -> loadClass('Json');
		echo $rlJson -> encode( empty($message) ? true : str_replace( '{files}', "<br />".$message, $GLOBALS['lang']['plugin_files_missed'] ) );
	}
}
/* ajax action end */

else 
{
	$reefless -> loadClass( 'Plugin', 'admin' );

	/* register ajax methods */
	$rlXajax -> registerFunction( array( 'install', $rlPlugin, 'ajaxInstall' ) );
	$rlXajax -> registerFunction( array( 'remoteInstall', $rlPlugin, 'ajaxRemoteInstall' ) );
	$rlXajax -> registerFunction( array( 'unInstall', $rlPlugin, 'ajaxUnInstall' ) );
	$rlXajax -> registerFunction( array( 'checkForUpdate', $rlAdmin, 'ajaxCheckForUpdate' ) );
	$rlXajax -> registerFunction( array( 'remoteUpdate', $rlPlugin, 'ajaxRemoteUpdate' ) );
	$rlXajax -> registerFunction( array( 'update', $rlPlugin, 'ajaxUpdate' ) );
	$rlXajax -> registerFunction( array( 'browsePlugins', $rlPlugin, 'ajaxBrowsePlugins' ) );
	
	$rlHook -> load('apPhpPluginsBottom');
}