<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LISENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: AUTOCOMPLETE.INC.PHP
 *
 *	This script is a commercial software and any kind of using it must be 
 *	coordinate with Flynax Owners Team and be agree to Flynax License Agreement
 *
 *	This block may not be removed from this file or any other files with out 
 *	permission of Flynax respective owners.
 *
 *	Copyrights Flynax Classifieds Software | 2013
 *	http://www.flynax.com/
 *
 ******************************************************************************/

/* system config */
require_once( '../../includes/config.inc.php' );

session_start();
	
require_once( RL_CLASSES . 'rlDb.class.php' );
require_once( RL_CLASSES . 'reefless.class.php' );

$rlDb = new rlDb();
$reefless = new reefless();

/* load classes */
$reefless -> connect(RL_DBHOST, RL_DBPORT, RL_DBUSER, RL_DBPASS, RL_DBNAME);
$reefless -> loadClass( 'Debug' );
$reefless -> loadClass( 'Valid' );
$reefless -> loadClass( 'Lang' );
$reefless -> loadClass( 'Cache' );

$str = $rlValid -> xSql(trim($_GET['str']));
//$str = str_replace(' ', '(.*)', $str);

$config['lang'] = $_GET['lang'] ? $_GET['lang'] : $rlDb -> getOne("Default", "`Key` = 'lang'", "config");
define('RL_LANG_CODE', $config['lang']);

$geo_format = $rlDb -> fetch(array("Key", 'Levels'), array("Geo_filter" => '1'), null, null, "multi_formats", 'row');
$geo_format['Levels'] = $geo_format['Levels'] ? $geo_format['Levels'] : 1;

/*$df_id = $rlDb -> getOne("ID", "`Key` = '".$geo_format['Key']."'", 'data_formats');*/

/*$sql ="SELECT DISTINCT [sel] as `aValue`, [path] FROM `".RL_DBPREFIX."data_formats` AS `T0` ";
$sql .="LEFT JOIN `".RL_DBPREFIX."lang_keys` AS `L0` ON `L0`.`Key` = CONCAT('data_formats+name+', `T0`.`Key`) ";

$sel = "CONCAT(`L0`.`Value`";
for( $i=1; $i<$geo_format['Levels']; $i++ )
{
	$sql .="LEFT JOIN `".RL_DBPREFIX."data_formats` AS `T{$i}` ON `T".($i-1)."`.`ID` = `T{$i}`.`Parent_ID` ";
	$sql .="LEFT JOIN `".RL_DBPREFIX."lang_keys` AS `L{$i}` ON `L{$i}`.`Key` = CONCAT('data_formats+name+', `T{$i}`.`Key`) ";

	$sel .= ",', ',`L{$i}`.`Value`";
}

$path = "`T".($geo_format['Levels'] -1)."`.`Path` ";

$sel .=") ";
$sql .="WHERE [sel] REGEXP '{$str}' AND `T0`.`Parent_ID` = ".$df_id." ";

if( $config['mf_geo_autocomplete_limit'] = $rlDb -> getOne("Default", "`Key` = 'mf_geo_autocomplete_limit'", "config") )
{
	$sql .="LIMIT {$config['mf_geo_autocomplete_limit']}";
}

$sql = str_replace('[sel]', $sel, $sql);
$sql = str_replace('[path]', $path, $sql);
 */

$sql ="SELECT `Value`, `Key` FROM `".RL_DBPREFIX."lang_keys` WHERE `Value` LIKE '{$str}%' ";
$sql .= "AND SUBSTRING(`Key`, 19, ".strlen($geo_format['Key']).") = '{$geo_format['Key']}' ";

if( $config['mf_geo_autocomplete_limit'] = $rlDb -> getOne("Default", "`Key` = 'mf_geo_autocomplete_limit'", "config") )
{
	$sql .="LIMIT {$config['mf_geo_autocomplete_limit']}";
}

$output = $rlDb -> getAll( $sql );

if ( !empty($output) )
{
	foreach ($output as $key => $value)
	{	
		$item_key = str_replace('data_formats+name+', '', $value['Key']);
		$item = $rlDb -> fetch( array("Parent_ID", "Path"), array("Key" => $item_key), null, null, "data_formats", "row" );
		$echo[$key]['path'] = $item['Path'] . "/";
		
		if( $item['Parent_ID'] )
		{
			$paths = explode( "/", $item['Path'] );
			$max_key = count($paths) - 1;
			$cpath = '';
			foreach( $paths as $pk => $path )
			{
				if( $pk < $max_key )
				{
					$cpath .= "/".$path;

					$parent = $rlDb -> fetch( array("Key"), array("Path" => trim($cpath,"/")), null, null, "data_formats", "row" );
					$parent_name = $rlDb -> getOne("Value", "`Key` = 'data_formats+name+".$parent['Key']."' AND `Code` = '".RL_LANG_CODE."'", "lang_keys");
					$echo[$key]['name'] .= $parent_name.", ";
				}
			}
		}

		$echo[$key]['name'] .= $value['Value'];
	}	
}

unset($output);

$reefless -> loadClass( 'Json' );
echo $rlJson -> encode( $echo );
