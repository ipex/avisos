<?php

/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: RLSMARTY.CLASS.PHP
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

class rlSmarty extends Smarty 
{
	/**
	* class constructor
	**/
	function rlSmarty()
	{
		global $config;
		
		//$this -> force_compile = true;
		
		if (defined('REALM'))
		{
			$this->template_dir = RL_ROOT . ADMIN_DIR . 'tpl' . RL_DS;
			$this->compile_dir = RL_TMP . 'aCompile';
		}
		else 
		{
			$this->template_dir = RL_ROOT . 'templates' . RL_DS . $config['template'] . RL_DS . 'tpl' . RL_DS;
			$this->compile_dir = RL_TMP . 'compile';
		}
		
		$this->cache_dir = RL_TMP . 'cache';
	}
	
	/**
	* create fckEditor
	*
	* @param array $aParams - editor options
	*
	**/
	function fckEditor( $aParams )
	{
		require_once( RL_LIBS . 'ckeditor' . RL_DS . 'ckeditor.php' );
		require_once( RL_LIBS . 'ckfinder' . RL_DS . 'ckfinder.php' );
		
		$name = $aParams['name'];
		$width = $aParams['width'];
		$height = $aParams['height'];
		$value = $aParams['value'];

		$CKEditor = new CKEditor();

		$CKEditor->config['language']	= RL_LANG_CODE ;
		$CKEditor->basePath = RL_LIBS_URL . 'ckeditor/';
		$CKEditor->config['width'] = empty($width) || $width == '100%' ? '97%' : $width;
		$CKEditor->config['height'] = $height;
		$CKEditor->config['entities'] = false;
		$CKEditor->config['basicEntities'] = false;

		$CKFinder = new CKFinder();
		$CKFinder->BasePath = '../libs/ckfinder/';
		$CKFinder->SetupCKEditorObject($CKEditor);
		
		$toolbar['Basic'] = array(
	 		array( 'Source', '-', 'Bold', 'Italic', 'Underline', 'Strike' ),
			array( 'Image', 'Flash', 'Link', 'Unlink', 'Anchor' ),
			array( 'Undo', 'Redo', '-', 'Find', 'Replace', '-', 'SelectAll', 'RemoveFormat'),
			array( 'TextColor', 'BGColor' )
	 	);
	 	
	 	/*$toolbar['Default'] = array(
			array( 'Source', 'DocProps', '-', 'Save', 'NewPage', 'Preview', '-', 'Templates' ),
			array( 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteWord', '-', 'Print', 'SpellCheck' ),
			array( 'Undo', 'Redo', '-', 'Find', 'Replace', '-', 'SelectAll', 'RemoveFormat' ),
			array( 'Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField' ),
			'/',
			array( 'Bold', 'Italic', 'Underline', 'StrikeThrough', '-', 'Subscript', 'Superscript' ),
			array( 'OrderedList', 'UnorderedList', '-', 'Outdent', 'Indent' ),
			array( 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyFull' ),
			array( 'Link', 'Unlink', 'Anchor' ),
			array( 'Image', 'Flash', 'Table', 'Rule', 'Smiley', 'SpecialChar', 'PageBreak' ),
			'/',
			array( 'Style', 'FontFormat', 'FontName', 'FontSize' ),
			array( 'TextColor', 'BGColor' ),
			array( 'FitWindow', '-', 'About' )
	 	);*/
	 	
	 	if ( $GLOBALS['config']['fckeditor_bar'] == 'Basic' )
	 	{
	 		$CKEditor->config['toolbar'] = $toolbar['Basic'];
	 	}
		
		$CKEditor->editor($name, $value);
	}
	
	/**
	* convert string to url path
	*
	* @param array $aParams - string
	*
	**/
	function str2path( $aParams )
	{
		$string = is_array($aParams) ? $aParams['string'] : $aParams ;
		$string = $GLOBALS['rlValid'] -> str2path($string);
		
		return $string;
	}

	/**
	* convert int format to money format
	*
	* @param array $aParams - string
	*
	**/
	function str2money( $aParams )
	{
		$string = is_array($aParams) ? $aParams['string'] : $aParams ;

		$len = strlen($string);
		$string = strrev($string);
		
		if ( strpos($string, '.') )
		{
			$rest = substr($string, 0, strpos($string, '.'));
			$string = substr($string, strpos($string, '.')+1, $len);
			$len -= strlen($rest)+1;
			$rest = strrev(substr(strrev($rest), 0, 2)) . ".";
		}
		elseif ( $GLOBALS['config']['show_cents'] )
		{
			$rest = '00.';
		}
		
		for ( $i = 0; $i <= $len; $i++ )
		{
			$val .= $string[$i];
			if ( (( $i + 1 ) % 3 == 0) && ( $i + 1 < $len ) )
			{
				$val .= $GLOBALS['config']['price_delimiter'];
			}
		}
		
		$val = strrev($rest.$val);
		
		return $val;
	}
	
	/**
	* build paging block
	*
	* @param int $calc - calculated items
	* @param int $total - total items
	* @param int $per_page - per page items number
	* @param string $add_url - additional url
	* @param string $var - non mod_revrite mod variable
	* @param string $controller - controller name
	* @param string $method - variables transfer method
	* @param string $custom - custom url
	* @param bool $full - use full initial page url
	*
	**/
	function paging( $aParams )
	{
		global $page_info, $lang, $config, $rlMobile;
		
		$display = 6; //integer only
		
		$calc = $aParams['calc'];
		$total = is_array($aParams['total']) ? count($aParams['total']) : $aParams['total'];
		$per_page = $aParams['per_page'];
		$add_url = $aParams['url'];
		$var = $aParams['var'];
		$controller = $aParams['controller'];
		$method = $aParams['method'];
		$custom = $aParams['custom'];
		$full = $aParams['full'];

		if ( !empty($controller) )
		{
			$page_info['Path'] = $controller;
		}
		
		$current = empty($aParams['current']) ? 1 : $aParams['current'] ;
		
		if ( $rlMobile -> isMobile )
		{
			$base = rtrim(RL_MOBILE_URL, 'index.php');
			if ($config['lang'] != RL_LANG_CODE && $config['mod_rewrite'])
			{
				$base .= RL_LANG_CODE . '/';
			}
		}
		else
		{
			$base = rtrim(SEO_BASE, 'index.php');
		}
		
		// build url
		if ( $config['mod_rewrite'] )
		{
			if ( empty($add_url) )
			{
				if ( $custom )
				{
					$first_url = $base . $custom;
					$tpl_url = $base . $custom . '/index[pg].html';
					$first_url = $full ? str_replace('[pg]', '1', $tpl_url) : $first_url;
				}
				else
				{
					$first_url = $base . $page_info['Path'] . '.html';
					$tpl_url = $base . $page_info['Path'] . '/index[pg].html';
				}
			}
			else
			{
				$first_url = $base . $page_info['Path'] . '/' . $add_url . '.html';
				$tpl_url = $base . $page_info['Path'] . '/' . $add_url . '/index[pg].html';
				$first_url = $full ? str_replace('[pg]', '1', $tpl_url) : $first_url;
			}
			
			if ( $method == 'get' )
			{
				preg_match('/^([^\?]*)\?/', $_SERVER['REQUEST_URI'], $matches);
				if ( $matches[0] )
				{
					$request_string = preg_replace('/^([^\?]*)\?/', '', $_SERVER['REQUEST_URI']);
					$first_url .= '?'. $request_string;
					$tpl_url .= '?'. $request_string;
				}
			}
		}
		else
		{
			$query_string = preg_replace('/(page\=[^\?\&]+)/', '', $_SERVER['QUERY_STRING']);
			$query_string = preg_replace('/(\&?\??pg\=[^\?\&]+)/', '', $query_string);
			
			//$first_url = $base . 'index.php?page=' . $page_info['Path'] . $query_string;
			$first_url = $tpl_url = $base . 'index.php?page=' . $page_info['Path'] . $query_string . '&amp;pg=1';
			$tpl_url = $tpl_url = $base . 'index.php?page=' . $page_info['Path'] . $query_string . '&amp;pg=[pg]';
			
			if ( $add_url )
			{
				$first_url .= $var ? '&amp;' . $var . '=' . $add_url : '&amp;'. $add_url;
				$tpl_url .= $var ? '&amp;' . $var . '=' . $add_url : '&amp;'. $add_url;
			}
		}

		if ( $calc > $total )
		{
			$pages = ceil($calc/$per_page);
			
			$content = '<ul class="paging">';
			
			if ( $current != 1 )
			{
				// first page
				$first_sign = $rlMobile -> isMobile ? '&nbsp;' : '&laquo;';
				$content .= '<li title="'.$GLOBALS['lang']['page'].' #1" class="navigator first"><a href="'.$first_url.'">'. $first_sign .'</a></li>';
				
				$previous = $current - 1;
				$prev_sign = $rlMobile -> isMobile ? '&nbsp;' : '&lsaquo;';
				// previous page
				$content .= '<li title="'.$GLOBALS['lang']['previous_page'].'" class="navigator ls"><a href="';
				$content .= $current == 2 ? $first_url : str_replace('[pg]', $previous, $tpl_url);
				$content .= '">'. $prev_sign .'</a></li>';
			}
			
			$limit = $pages;
			$from = 1;
			if( $pages > $display )
			{
				if ( $current > ($display/2)+1 )
				{
					$distance = floor(($display/2));
				}
				else
				{
					$distance = $display - ($current - 1);
				}
				
				$limit = $current + $distance;
				$from = $current - $distance;
				$from = $from < 1 ? 1 : $from;
				$limit = $from == 1 ? $limit - 1: $limit;
				$limit = $limit > $pages ? $pages : $limit;
				$from = $limit == $pages ? $from - floor($display/2) + ($pages - $current) + 1 : $from;
			}
			
			if ( $current > ($display/2)+1 && $pages > $display)
			{
				$content .= '<li class="point">...</li>';
			}
			
			if ( $rlMobile -> isMobile )
			{
				$content .= '<li class="left"></li>';
			}
			
			for ($i = $from; $i <= $limit; $i++)
			{
				$active = '';
				$title = $GLOBALS['lang']['page'].' #'.$i;
				
				if ( $i == $current )
				{
					$active = 'class="active"';
					$title = $GLOBALS['lang']['current_page'];
				}
		
				$url = $tpl_url;
				if ( $i == 1 )
				{
					$url = $first_url;
				}

				$content .= '<li title="'.$title.'" '.$active.'>';
				if ( $i != $current )
				{
					$content .= '<a href="'.str_replace('[pg]', $i, $url).'">'.$i.'</a>';
				}
				else
				{
					$content .= $i;
				}
				$content .= '</li>';
			}
			
			if ( $rlMobile -> isMobile )
			{
				$content .= '<li class="right"></li>';
			}
			
			if ( $current < $pages-($display/2) && $pages > $display )
			{
				$content .= '<li class="point">...</li>';
			}
			
			if ( $current != $pages )
			{
				// next page
				$next_sign = $rlMobile -> isMobile ? '&nbsp;' : '&rsaquo;';
				$content .= '<li title="'.$GLOBALS['lang']['next_page'].'" class="navigator rs"><a href="'.str_replace('[pg]', $current+1, $tpl_url).'">'.$next_sign.'</a></li>';
				
				// last page
				$last_sign = $rlMobile -> isMobile ? '&nbsp;' : '&raquo;';
				$content .= '<li title="'.$GLOBALS['lang']['page'].' #'.$pages.'" class="navigator last"><a href="'.str_replace('[pg]', $pages, $tpl_url).'">'. $last_sign .'</a></li>';
			}
			
			if ( !$rlMobile -> isMobile )
			{
				$content .= '<li class="transit">'. $lang['page'] .' <input maxlength="3" type="text" value="'. $current .'" /><input type="hidden" name="stats" value="'.$current.'|'.$pages.'" /><input type="hidden" name="pattern" value="'. $url .'" /><input type="hidden" name="first" value="'. $first_url .'" /> '. $lang['of'] .' '. $pages .'</li>';
			}
			
			$content .= '</ul><div class="clear"></div>';
		}
		
		return $content;
	}
	
	/**
	* search form build
	*
	* @param string $key - form key
	* @param bool $photos - "with photos only" check box using
	* @param mixed - hidden fields, unlimited
	*
	**/
	function search( $aParams )
	{
		global $df, $reefless;

		$key = $aParams['key'];
		$photos = isset($aParams['photos']) && (bool)$aParams['photos'] === false ? false : true;
	
		if ( empty($df) )
		{
			$reefless -> loadClass('Categories');
			$df = $GLOBALS['rlCategories'] -> getDF();
			$this -> assign_by_ref( 'df', $df);
		}
		
		unset($aParams['key'], $aParams['photos']);
		
		if ( !empty($aParams) )
		{
			$GLOBALS['rlDb'] -> setTable('listing_fields');
			$available_fields = $GLOBALS['rlDb'] -> fetch( array('Key'), array('Status' => 'active') );
			$GLOBALS['rlDb'] -> resetTable();
			
			foreach ($available_fields as $afKey => $afVal)
			{
				$a_fields[] = $afVal['Key'];
			}
			
			unset($available_fields);
			
			foreach ( $aParams as $afKey => $akVal )
			{
				if ( !in_array($afKey, $a_fields) )
				{
					unset($aParams[$afKey]);
				}
			}
			
			$this -> assign( 'hidden_fields', $aParams );
		}
		
		/* get search forms */
		$GLOBALS['reefless'] -> loadClass( 'Search' );
		$form = $GLOBALS['rlSearch'] -> buildSearch( $key );
		$this -> assign_by_ref( 'form', $form );
		$this -> assign_by_ref( 'form_key', $key );
		$this -> assign_by_ref( 'use_photos', $photos );
		
		$this -> display( 'blocks' . RL_DS . 'search_block.tpl' );
	}
	
	/**
	* encode e-mail address to javascript code
	*
	* @param array $email - email to encode
	*
	**/
	function encodeEmail( $params = false )
	{
		global $rlValid, $reefless, $lang;
		
		$email = $params['email'];
		
		if ( !$email || !$rlValid -> isEmail($email) )
			return false;
			
		$out = '<a href="mailto:'. $email .'">'. $email .'</a>';
		$len = strlen($out);
		$step = rand(3, 7);
		$max = $len * $step;
		$range = range(0, $max-1);

		for ( $i = 0; $i < $len; $i++)
		{
			$index = $this -> encodeEmailSet($range);
			$array[$index] = $out[$i];
			$indexes[$i*$step] = $index;
		}
		
		for ( $i = 0; $i < $max; $i++)
		{
			if ( !isset($indexes[$i]) )
			{
				$index = $this -> encodeEmailSet($range);
				$array[$index] = $reefless -> generateHash(1, 'password');
				$indexes[$i] = $index;
			}
		}
		
		ksort($array);
		ksort($indexes);

		$js_l = "['". implode("','", $array) ."']";
		$js_i = "['". implode("','", $indexes) ."']";
		
		$var1 = $reefless -> generateHash(7, 'lower', false).'c';
		$var2 = $reefless -> generateHash(7, 'lower', false).'x';
		$var3 = $reefless -> generateHash(7, 'lower', false).'a';
		
		$code = <<<VS
<script type="text/javascript">//<![CDATA[
var $var1 = $js_l;var $var2 = $js_i;var $var3 = new Array();for(var i = 0; i<$var1.length;i+=$step){ $var3.push({$var1}[{$var2}[i]]); } for(var i = 0; i<$var3.length;i++){document.write({$var3}[i]);}
//]]></script><noscript>{$lang['noscript_show_email']}</noscript>
VS;
		echo $code;
	}
	
	/**
	* populate array | secondary methods for encodeEmail()
	*
	* @param array $array - array in use
	*
	**/
	function encodeEmailSet( &$range )
	{
		$i = rand(0, count($range) - 1);
		$t = $range[$i];
		unset($range[$i]);
		$range = array_values($range);
		
		return $t;
	}
}