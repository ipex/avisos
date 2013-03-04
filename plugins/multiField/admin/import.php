<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LISENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: IMPORT.PHP
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

set_time_limit(0);

/* system config */
require_once( '../../../includes/config.inc.php' );
require_once( RL_ADMIN_CONTROL . 'ext_header.inc.php' );
/*require_once( RL_LIBS . 'system.lib.php' );*/

$reefless ->  loadClass('Json');
$reefless ->  loadClass('Actions');

$languages = $rlLang -> getLanguagesList();

$limit = $_SESSION['mf_import']['per_run'];
$start = (int)$_GET['index'];

$parents = $_SESSION['mf_import']['parents'];
$parent = $_SESSION['mf_import']['parents'][0];
$next_parent = $_SESSION['mf_import']['parents'][1];

if( $parent )
{
	$table = $_SESSION['mf_import']['table'];
	$parent_id = $_SESSION['mf_import']['parent_id'];
	$one_ignore = $_SESSION['mf_import']['one_ignore'];
	$top_key = $_SESSION['mf_import']['top_key'];

	$available_rows = $_SESSION['mf_import']['available_rows'];

	if( !$_SESSION['mf_import']['available_sub_rows'][$parent] )
	{
		$_SESSION['mf_import']['available_sub_rows'][$parent] = getFData( array("table" => $table, "getcount" => "true", "parent" => $parent) );
	}

	if( $next_parent )
	{
		if( !$_SESSION['mf_import']['available_sub_rows'][$next_parent] )
		{
			$_SESSION['mf_import']['available_sub_rows'][$next_parent] = getFData( array("table" => $table, "getcount" => "true", "parent" => $next_parent) );
		}
	}

	if( count($parents) == 1 && !$one_ignore && $_SESSION['mf_import']['total'] == 1 )
	{
		$without_parent = true;
		//import childs of one
		$data = getFData( array("parent" => $parent, "table" => $table, "including_childs" => true, "start" => $start, 'limit' => $limit ) );
	}
	else
	{
		//import all
		$data = getFData( array("parent" => $parent, "table" => $table, "including_childs" => true, "including_parent" => true, "start" => $start, 'limit' => $limit) );
	}

	importData( $data, $parent_id, $top_key, null, null, $without_parent, $parent );

	/** data importing **/
	$new_parent = false;

	if( count($data) < $limit ) //next parent
	{
		array_shift($_SESSION['mf_import']['parents']);
		$new_parent = true;

		if( $next_parent )
		{
			$items['sub_count'] = $_SESSION['mf_import']['available_sub_rows'][$next_parent];
			unset($_SESSION['mf_import']['available_sub_rows']);
		}
	}else
	{
		$items['sub_count'] = (int)$_SESSION['mf_import']['available_sub_rows'][$parent];
	}
	
	$items['count'] = $available_rows;
	$items['current'] = $available_rows - count($parents) + 1;
	$items['current_text'] = ucwords( str_replace("_", " ", $parent) );

	$items['index'] = $new_parent ? 0 : $start + $limit;
	$items['limit'] = $limit;

}else
{
	$items['finish'] = true;

	$mitem['Key'] = $GLOBALS['rlDb'] -> getOne("Key", "`ID` = '".$_SESSION['mf_import']['parent_id']."'", "data_formats");
	$mitem['ID'] = $_SESSION['mf_import']['parent_id'];

	unset($_SESSION['mf_import']);

	$reefless -> loadClass('MultiField', null, 'multiField');
	$reefless -> loadClass('Cache');
	
	$rlMultiField -> cache();

	if( $config['cache'] )
	{
		$rlCache -> updateDataFormats();
		if( $config['mf_geo_multileveled'] )
		{
			$rlMultiField -> cache();
		}
	}

	if( $GLOBALS['rlDb'] -> getOne("Geo_filter", "`Key` = '".$mitem['Key']."'", "multi_formats") )
	{
		$rlMultiField -> updatePath( $mitem );
	}
}

echo $rlJson ->  encode($items);
exit;

function importData( $data = false, $parent_id, $top_key = false, $level, $max_position = 1, $without_parent = false, $parentk = false )
{
	global $parents;

	foreach( $data as $key => $value )
	{
		$item_key = $top_key . "_" . $value -> Key;

		if( $without_parent && $parentk == $value -> Parent_key )
		{
			$parent_key = $top_key;
		}else
		{
			$parent_key = $top_key . "_" . $value -> Parent_key;
		}
		
		if( $parent_id && $parent_key == $top_key . "_")
		{
			$parent['ID'] = $parent_id;
			$parent['Level'] = 0;
		}else if( $_SESSION['mf_parent_ids'][$parent_key] )
		{
			$parent = $_SESSION['mf_parent_ids'][$parent_key];
		}else
		{
			$parent = $GLOBALS['rlDb'] -> fetch(array("ID"), array("Key" => $parent_key), null, null, "data_formats", "row");
			if( !$parent['ID'] )
			{
				continue;
			}
			$parent['Level'] = getLevel( $parent['ID'] );
			if( count($_SESSION['mf_parent_ids']) > 1000 )
			{
				unset($_SESSION['mf_parent_ids']);
			}
			$parent = $_SESSION['mf_parent_ids'][$parent_key] = $parent;
		}

		if( $parent )
		{
			$prev_level = $level;
			$level = $parent['Level'] + 1;

			if( $level != $prev_level )
			{
				$max_position = $GLOBALS['rlDb'] -> getOne("Position", "`Parent_ID` = ".$parent['ID']." ORDER BY `Position` DESC", "data_formats");
			}

			$max_position = is_numeric($max_position) ? $max_position : 0;

			$insert = array(
				'Parent_ID' => $parent['ID'],
				'Key' => $item_key,
				'Status' => 'active',
				'Position' => ++$max_position,
				'Plugin' => $level ? 'multiField' : ''
			);

			$module = $level > 1 ? 'formats' : 'common';

			if( $GLOBALS['rlActions'] -> insertOne($insert, 'data_formats') )
			{
				$insert_id = mysql_insert_id();				

				$lang_keys = array();
				foreach ($GLOBALS['languages'] as $lk => $lang)
				{
					$lang_keys[] = array(
						'Code' => $GLOBALS['languages'][$lk]['Code'],
						'Module' => $module,
						'Key' => 'data_formats+name+'.$insert['Key'],
						'Value' => $value -> Name,
						'Plugin' => $level > 1 ? 'multiField' : ''
					);
				}

				$GLOBALS['rlActions'] -> insert($lang_keys, 'lang_keys');
			}
		}
		else
		{
			var_dump( $item_key, $parent_key );
			exit;
		}
	}
}

function getFData( $params )
{
	global $reefless;

	set_time_limit(0);
	$reefless -> time_limit = 0;

	$vps = "http://66.225.241.80/~flsource/getdata.php?nv&domain={$GLOBALS['license_domain']}&license={$GLOBALS['license_number']}";  // vps4

	foreach( $params as $k => $p )
	{
		$vps .="&".$k."=".$p;
	}

	$content = $reefless -> getPageContent( $vps );

	return $GLOBALS['rlJson'] -> decode( $content );
}

function getLevel( $id, $level )
{
	if( !$id )
		return false;

	$parent = $GLOBALS['rlDb'] -> getOne("Parent_ID", "`ID`=".$id, "data_formats");

	if( $parent )
	{
		$level++;
		return getLevel( $parent, $level );
	}
	else
	{
		return $level;
	}
}
