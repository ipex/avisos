<?php

/**copyright*/

require_once( '../../includes/config.inc.php' );

error_reporting( E_ERROR );
ini_set("display_errors", "0");
set_error_handler(array( "rlDebug", "errorHandler"), E_ERROR );


if( $_GET['action'] == 'ping' )
{
	require_once( '../../includes/control.inc.php' );

	$n=0;
	/* save statistic*/ 
	foreach( explode(',',$_GET['ids']) as $id )
	{
		if($id)
		{
			$insert[$n]['Listing_ID'] = $id;
			$insert[$n]['Referer'] = $_SERVER['HTTP_REFERER'];
			$insert[$n]['IP'] = $_SERVER['REMOTE_ADDR'];
			$insert[$n]['Date'] = 'NOW()';
		}
		$n++;
	}
	$reefless -> loadClass('Actions');
	$rlActions -> insert($insert, 'remote_shows');
	exit;
}

$filename = RL_CACHE."js_blocks_".md5(serialize( $_GET));
$fmtime = filemtime( $filename );
$fmtime = false;

if( $fmtime && $fmtime + 600 > time() )
{
	header('Content-Type: text/javascript; charset=utf-8');
	$fh = fopen($filename, 'r');
	echo fread($fh, filesize($filename));
	fclose($fh);
	exit;
}else
{
	require_once( '../../includes/control.inc.php' );

	$config = $rlConfig -> allConfig();
	$rlSmarty -> assign_by_ref( 'config', $config );
	$GLOBALS['config'] = $config;

	$reefless -> loadClass('RemoteAdverts', null, 'js_blocks');
	$reefless -> loadClass('Listings');
	$reefless -> loadClass('Common');
	$reefless -> loadClass('Smarty');

	define( 'RL_DATE_FORMAT', $rlDb -> getOne('Date_format', "`Code` = '{$config['lang']}'", 'languages') );

	if( !empty($_GET['lang']) && $rlDb -> getOne('Code', "`Code` = '".$_GET['lang']."'", 'languages') )
	{
		define('RL_LANG_CODE', $_GET['lang']);
	}else
	{
		define('RL_LANG_CODE', $config['lang']);
	}

	$bPath = RL_URL_HOME;
	if ($config['lang'] != RL_LANG_CODE && $config['mod_rewrite'])
	{
		$bPath .= RL_LANG_CODE . '/';
	}
	if (!$config['mod_rewrite'])
	{
		$bPath .= 'index.php';
	}

	define( 'SEO_BASE', $bPath );

	$rlSmarty -> assign( 'rlBase',  $bPath);

	$limit = (int)$_GET['limit'] != 0 ? (int)$_GET['limit'] : 10;
	$order_field = !empty($_GET['order_by']) ? $_GET['order_by'] : 'Date' ;
	$order_type = !empty($_GET['order_type']) ? strtoupper($_GET['order_type']) : 'DESC' ;
	$featured = !empty($_GET['featured']) ? true : false ;
	$type = !empty($_GET['listing_type']) ? $_GET['listing_type'] : false ;

	$rlSmarty -> register_function('str2path', array( 'rlSmarty', 'str2path' ));

	$where = array();

	$disabled_fields = array(); //put here fields you do not want to be used in $where 

	$structure_tmp = $rlDb->getAll("SHOW FIELDS FROM `".RL_DBPREFIX."listings`");

	foreach($structure_tmp as $k => $v)
	{		
		if(!in_array($v['Field'], $disabled_fields))
		{
			$structure[] = strtolower($v['Field']);
		}
	}

	foreach($_GET as $k => $v)
	{
		$k = strtolower($k);
		$k = $k == 'category' ? 'kind_id' : $k;
		
		if($k == 'kind_id')
		{
			if(!is_int_val($v))
			{
			$v = $rlDb -> getOne('ID', "`Key` ='".$v."'", 'categories');
			}

		}
		
		if(in_array($k, $structure) && $v)
		{
			$where[$k] = $v;
		}
	}

	$listings = $rlRemoteAdverts -> getListings( $limit, $type, $where, $order_field, $order_type, $featured, $listing_type );
	$rlSmarty -> assign('listings', $listings);

	if($config['ra_statistics'])
	{
		/* save statistic*/ 
		$per_page = $_GET['per_page'] ? $_GET['per_page'] : 5;

		for( $i =0; $i < $per_page; $i++ )
		{
			if($listings[$i]['ID'])
			{
		//		$ids[] = $listings[$i]['ID'];
				$insert[$i]['Listing_ID'] = $listings[$i]['ID'];
				$insert[$i]['Referer'] = $_SERVER['HTTP_REFERER'];
				$insert[$i]['IP'] = $_SERVER['REMOTE_ADDR'];
				$insert[$i]['Date'] = 'NOW()';
			}
		}
	}

	$reefless -> loadClass('Actions');
	$rlActions -> insert($insert, 'remote_shows');

	$rlSmarty -> assign('tmp_code', md5(mt_rand()));

	header('Content-Type: text/javascript; charset=utf-8');

	$content = $rlSmarty -> fetch( RL_PLUGINS . 'js_blocks' . RL_DS . 'blocks.tpl', null, null, false );

	$fh = fopen($filename, 'w+');

	fwrite($fh, $content); 
	fclose($fh);

	header('Content-Type: text/javascript; charset=utf-8');

	echo $content;

//	$rlSmarty -> display( RL_PLUGINS . 'js_blocks' . RL_DS . 'blocks.tpl' );
}

function is_int_val($data)
{
	if (is_int($data) === true) 
	{
		return true;
	}	
	elseif (is_string($data) === true && is_numeric($data) === true)
	{
		return (strpos($data, '.') === false);
	}

	return false;
}

