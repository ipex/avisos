<?php

/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: SETTINGS.INC.PHP
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

/* update actions */
if (isset($_POST['a_config']))
{
	$dConfig = $_POST['config'];
	
	/* clear compile directory */
	if ( isset($_POST['config']['template']) && ($_POST['config']['template']['value'] != $config['template']))
	{
		$compile = $reefless -> scanDir( RL_TMP . 'compile' . RL_DS );
		foreach ($compile as $file)
		{
			unlink( RL_TMP . 'compile' . RL_DS . $file );
		}
		
		/* touch files */
		$reefless -> flTouch();
	}
	
	/* update cache */
	if ( isset($_POST['config']['cache']) && $_POST['config']['cache']['value'] && !$config['cache'] )
	{
		$config['cache'] = 1;
		$rlCache -> update();
	}
		
	$update = array();
	
	foreach ($dConfig as $key => $value)
	{
		if ($value['d_type'] == 'int')
		{
			$value['value'] = (int)$value['value'];
		}
		
		$rlValid -> sql( $value['value'] );
		
		$row['where']['Key'] = $key;
		$row['fields']['Default'] = $value['value'];
		array_push( $update, $row );
	}
	
	$reefless -> loadClass( 'Actions' );
	
	$rlHook -> load('apPhpConfigBeforeUpdate');
	
	if ($rlActions -> update( $update, 'config' ))
	{
		$rlHook -> load('apPhpConfigAfterUpdate');
		
		$reefless -> loadClass( 'Notice' );
		
		$aUrl = array( "controller" => $controller );
		if ( $_POST['group_id'] )
		{
			$aUrl['group'] = $_POST['group_id'];
		}
		$rlNotice -> saveNotice( $lang['config_saved'] );
		$reefless -> redirect( $aUrl );
	}
}

/* get all config groups */
$g_sql = "SELECT `T1`.*, `T2`.`Status` AS `Plugin_status` FROM `".RL_DBPREFIX."config_groups` AS `T1` ";
$g_sql .= "LEFT JOIN `".RL_DBPREFIX."plugins` AS `T2` ON `T1`.`Plugin` = `T2`.`Key` ";
$configGroups = $rlDb -> getAll($g_sql);
$configGroups = $rlLang -> replaceLangKeys( $configGroups, 'config_groups', 'name', RL_LANG_CODE, 'admin' );
$rlSmarty -> assign_by_ref( 'configGroups', $configGroups );

foreach ($configGroups as $key => $value)
{
	$groupIDs[] = $value['ID'];
}

/* get all configs */
$configsLsit = $rlDb -> fetch( '*', null, "WHERE `Group_ID` = '".implode("' OR `Group_ID` = '", $groupIDs).")' ORDER BY `Position`", null, 'config' );
$configsLsit = $rlLang -> replaceLangKeys( $configsLsit, 'config', array( 'name', 'des' ), RL_LANG_CODE, 'admin' );
$rlAdmin -> mixSpecialConfigs( $configsLsit );

foreach($configsLsit as $key => $value)
{
	$configs[$value['Group_ID']][] = $value;
}

$rlSmarty -> assign_by_ref( 'configs', $configs );
unset($configGroups, $configsLsit);

$rlHook -> load('apPhpConfigBottom');