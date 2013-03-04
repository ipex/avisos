<?php
/**copyrights**/

class rlSitemap extends reefless
{
	var $total = 0;
	var $limit_urls;
	var $total_pages = 0;
	var $total_categories = 0;
	var $total_listings = 0;
	var $total_accounts = 0;
	
	var $start = 0;
	var $lenght = 500;

	var $languages;
	var $languages_count = 1;
	
	var $base_path;
	var $pages;
	
	function rlSitemap()
	{
		global $rlLang, $pages;

		$this->limit_urls = $GLOBALS['config']['sitemap_limit_urls'] ? (int)$GLOBALS['config']['sitemap_limit_urls'] : 10000;
		$this->languages = $rlLang -> getLanguagesList();
		$this->languages_count = count( $this->languages );
		$this->lenght = $this -> limit_urls;

		$this->pages = &$pages;

		$this->base_path = RL_URL_HOME;
		
		if ( !$GLOBALS['config']['mod_rewrite'] )
		{
			$this->base_path .= 'index.php';
		}
	}

	function getListings( $first = false )
	{
		global $rlListings, $rlSmarty;

		$sql  = "SELECT SQL_CALC_FOUND_ROWS `T1`.*, `T2`.`Listing_period`, `T3`.`Path`, `T3`.`Type` AS `cat_type` ";
		$sql .= "FROM `".RL_DBPREFIX."listings` AS `T1` ";
		$sql .= "LEFT JOIN `".RL_DBPREFIX."listing_plans` AS `T2` ON `T1`.`Plan_ID` = `T2`.`ID` ";
		$sql .= "LEFT JOIN `".RL_DBPREFIX."categories` AS `T3` ON `T1`.`Category_ID` = `T3`.`ID` ";
		$sql .= "WHERE (UNIX_TIMESTAMP(DATE_ADD(`T1`.`Pay_date`, INTERVAL `T2`.`Listing_period` DAY)) > UNIX_TIMESTAMP(NOW()) OR `T2`.`Listing_period` = 0) ";
		$sql .= "AND `T1`.`Status` = 'active' ";
		$sql .= "ORDER BY `T1`.`ID` ASC ";

		if ( $this->languages_count > 1 )
		{
			$lenght = ceil( $this -> limit_urls / $this -> languages_count );

			if ( $this->start > 0 )
			{
				$start = ceil( $this -> start / $this -> languages_count );
			}
			else
			{
				$start = $this -> start;
			}
		}
		else
		{
			$lenght = $this -> limit_urls;
			$start = $this -> start;
		}

		$sql .= "LIMIT {$start},{$lenght}";

		$listings = $this -> getAll( $sql );
            
		if ( $first )
		{
			$calc = $this -> getRow( "SELECT FOUND_ROWS() AS `calc`" );
			$calc['calc'] = $this -> languages_count * $calc['calc'];
	   		$this -> total += $calc['calc'];
			$this -> total_listings = $calc['calc'];
		}

		$this -> languages_count > 1 ? $lang = '[lang]/' : $lang = ''; 

		foreach ( $listings as $key => $val )
		{
			$listings[$key]['listing_title'] = $rlListings -> getListingTitle( $val['Category_ID'], $val, $val['cat_type'] );
		   	$listings[$key]['url'] = $this->base_path . $lang . ( $GLOBALS['config']['mod_rewrite'] ? $this->pages['lt_'.$val['cat_type']] .'/'. $val['Path'] .'/'. $rlSmarty -> str2path($listings[$key]['listing_title']) . '-' . $val['ID'] . '.html' : '?page='. $this->pages['lt_'.$val['cat_type']] .'&amp;id=' . $val['ID'] );
		}

		return $listings;
	}

	function getPages( $first = false )
	{
		$sql = "SELECT `ID`, `Path`, `Get_vars`, NOW() AS `Modified`, `No_follow`, `Status` FROM `" . RL_DBPREFIX . "pages` WHERE `Status` = 'active' AND `No_follow` = '0' ORDER BY `ID` ";
		$pages = $this -> getAll( $sql );

		if( $first )
		{
			$count = $this -> languages_count * count( $pages );
			$this -> total += $count;
			$this -> total_pages = $count;
		}
                                                                  
		$this -> languages_count > 1 ? $lang = '[lang]/' : $lang = ''; 

		foreach ( $pages as $key => $val )
		{
			if ( !empty( $val['Path'] ) )
			{
				$pages[$key]['url'] = $this -> base_path . $lang . ( $GLOBALS['config']['mod_rewrite'] ? $val['Path'] . '.html' : '?page=' . $val['Path'] );
			}
			else
			{
				unset( $pages[$key] );
			}
		}

		return $pages;
	}

	function getCategories( $first = false )
	{
		$sql = "SELECT SQL_CALC_FOUND_ROWS `ID`, `Path`, `Parent_ID`, `Status` , `Modified`, `Type` FROM `" . RL_DBPREFIX . "categories` WHERE `Status` = 'active' ";

		if ( $this -> languages_count > 1 )
		{
			$lenght = ceil( $this -> limit_urls / $this -> languages_count );

			if ( $this -> start > 0 )
			{
				$start = ceil( $this -> start / $this -> languages_count );
			}
			else
			{
				$start = $this -> start;
			}
		}
		else
		{
			$lenght = $this -> limit_urls;
			$start = $this -> start;
		}

		$sql .= "LIMIT {$start},{$lenght}";

		$categories = $this -> getAll( $sql );
             
		if ( $first )
		{
			$calc = $this -> getRow( "SELECT FOUND_ROWS() AS `calc`" );
			$calc['calc'] = $this -> languages_count * $calc['calc']; 
			$this -> total += (int)$calc['calc'];
			$this -> total_categories = (int)$calc['calc'];
		} 

		$this -> languages_count > 1 ? $lang = '[lang]' .'/' : $lang = ''; 

		foreach( $categories as $key => $val )
		{
			$categories[$key]['url'] = $this->base_path . $lang . ($GLOBALS['config']['mod_rewrite'] ? $this->pages['lt_'.$val['Type']] .'/'. $val['Path'] . '.html' : '?page=' . $this->pages['lt_'.$val['Type']] . '&amp;category=' . $val['ID']);
		}

		return $categories;
	}

	function getAccounts( $first = false )
	{
		$sql  = "SELECT SQL_CALC_FOUND_ROWS `T1`.`ID`, `T1`.`Own_address`, `T1`.`Type`, `T2`.`Own_location`, `T2`.`Page` ";
		$sql .= "FROM `".RL_DBPREFIX."accounts` AS `T1` ";
		$sql .= "LEFT JOIN `".RL_DBPREFIX."account_types` AS `T2` ON `T1`.`Type` = `T2`.`Key` ";
		$sql .= "WHERE `T1`.`Status` = 'active' AND `T2`.`Own_location` = '1' AND `T2`.`Page` = '1' ";
		$sql .= "ORDER BY `T1`.`ID` DESC ";

		if ( $this -> languages_count > 1 )
		{
			$lenght = ceil( $this -> limit_urls / $this -> languages_count );

			if ( $this -> start > 0 )
			{
				$start = ceil( $this -> start / $this -> languages_count );
			}
			else
			{
				$start = $this -> start;
			}
		}
		else
		{
			$lenght = $this -> limit_urls;
			$start = $this -> start;
		}

		$sql .= "LIMIT {$start},{$lenght}";

		$accounts = $this -> getAll( $sql );
             
		if ( $first )
		{
			$calc = $this -> getRow( "SELECT FOUND_ROWS() AS `calc`" );
			$calc['calc'] = $this -> languages_count * $calc['calc']; 
			$this -> total += (int)$calc['calc'];
			$this -> total_accounts = (int)$calc['calc'];
		} 

		$this -> languages_count > 1 ? $lang = '[lang]' .'/' : $lang = ''; 

		foreach( $accounts as $key => $val )
		{
			$accounts[$key]['url'] = $this->base_path . $lang . ($GLOBALS['config']['mod_rewrite'] ? $val['Own_address'] . '.html' : '?page=' . $this->pages['ac_'.$val['Type']] . '&amp;id=' .$val['ID']);
		}

		return $accounts;
	}

	function build( $type, $number = false, $mod = false )
	{
		$number = (int)$number;
                     
		global $xml, $sitemap_options;

		$sitemap_options = array(
				'number' => $number,	
				'type' => $type,
				'mod' => $mod
			);

		$mod = str_replace( "_", "", $mod );

		if ( $type == 'google' && !empty( $number ) )
		{
			$xml = '<?xml version="1.0" encoding="UTF-8"?>';
			$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.sitemaps.org/schemas/sitemap-image/1.1" xmlns:video="http://www.sitemaps.org/schemas/sitemap-video/1.1">';

			if ( $number == 1 && !$mod )
			{  
				/* build urls of pages */
				$pages = $this -> getPages();

				foreach ( $this -> languages as $k => $lang )
				{                               
					foreach ( $pages as $key => $value )
					{
						$this -> add_lang_code ( $value['url'], $lang['Code'] );
						$xml .= $this -> _build_item ( $type, $value );
					}	
				}
			}
			elseif ( $number > 0 && $mod == 'category' )
			{
				/* build urls of categories */
				$this -> start = ( $number - 1 ) * $this -> limit_urls; 
				$categories = $this -> getCategories();
				$xml .= $this -> _build_sitemap_items( $type, $categories );

				unset( $categories );
			}
			elseif ( $number > 0 && $mod == 'dealer' )
			{
				/* build urls of categories */
				$this -> start = ( $number - 1 ) * $this->limit_urls; 
				$accounts = $this -> getAccounts();
				$xml .= $this -> _build_sitemap_items( $type, $accounts );

				unset( $accounts );
			}
			elseif ( $number > 0 && !$mod )
			{				
				/* build urls of listings */
				$this -> start = ( $number - 1 ) * $this->limit_urls;  
				$listings = $this -> getListings(); 
				$xml .= $this -> _build_sitemap_items( $type, $listings );
				unset( $listings );
			}

			$GLOBALS['rlHook'] -> load( 'sitemapAddUrlsInFile' );

			$xml .= '</urlset>';
		}
		else
		{
			$first = true;	
			$pages = $this -> getPages( $first );
			$categories = $this -> getCategories( $first );
			$listings = $this -> getListings( $first );
			$accounts = $this -> getAccounts( $first );

			$GLOBALS['rlHook'] -> load( 'sitemapTotalUrls' );

			if ( $this->total > $this->limit_urls )
			{
				$xml = "<?xml version='1.0' encoding='UTF-8'?>" . PHP_EOL;
				$xml .= "<sitemapindex xmlns='http://www.sitemaps.org/schemas/sitemap/0.9'>" . PHP_EOL;

				// pages
				$xml .= "<sitemap>". PHP_EOL;
				$xml .= "\t<loc>" . RL_URL_HOME. "sitemap1.xml</loc>". PHP_EOL;	
				$xml .= "\t<lastmod>" . str_replace( ' ', 'T', date( 'Y-m-d H:i:s' ) ) . "+00:00</lastmod>". PHP_EOL;
				$xml .= "</sitemap>". PHP_EOL;

				// categories
				$xml .= $this -> _build_sitemap_files( $this -> total_categories, 'category_' );

				// accounts
				$xml .= $this -> _build_sitemap_files( $this -> total_accounts, 'dealer_' );

				// listings
				$xml .= $this -> _build_sitemap_files( $this -> total_listings );
				
				$GLOBALS['rlHook'] -> load( 'sitemapAddNewFile' );

				$xml .= "</sitemapindex>";                                                                   
			}
			else
			{
				$type == 'google' ? $xml = '<?xml version="1.0" encoding="UTF-8"?>'. PHP_EOL .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.sitemaps.org/schemas/sitemap-image/1.1" xmlns:video="http://www.sitemaps.org/schemas/sitemap-video/1.1">' : '<pre>';
				$xml .= PHP_EOL;

				foreach( $this -> languages as $k => $lang )
				{
					/* build urls of pages */   
					foreach( $pages as $key => $value )
					{
						$this -> add_lang_code( $value['url'], $lang['Code'] );
						$xml .= $this -> _build_item( $type, $value );
					}
					unset( $pages );

					/* build urls of categories */ 			
					foreach( $categories as $key => $value )
					{
						$this -> add_lang_code( $value['url'], $lang['Code'] );
						$xml .= $this -> _build_item( $type, $value );
					}
					unset( $categories );

					/* build urls of accounts */ 			
					foreach( $accounts as $key => $value )
					{
						$this -> add_lang_code( $value['url'], $lang['Code'] );
						$xml .= $this -> _build_item( $type, $value );
					}
					unset( $accounts );		
					
					/* build urls of listings */ 
					foreach( $listings as $key => $value )
					{
						$this -> add_lang_code( $value['url'], $lang['Code'] );
						$xml .= $this -> _build_item( $type, $value );
					}
					unset( $listings );
				}
				
				$GLOBALS['rlHook'] -> load( 'sitemapAddUrlsCommon' );

				$type == 'google' ? $xml .= '</urlset>' : '</pre>';
			}
		}

		return $xml;
	}

	function _build_sitemap_files($total_items = 0, $prefix = '')
	{
		$xml = '';
		$count_files =  ceil( $total_items / $this->limit_urls );
		
		for ( $i = 0; $i < $count_files; $i++ )
		{
			$xml .= "<sitemap>". PHP_EOL;
			$xml .= "\t<loc>" . RL_URL_HOME. $prefix. "sitemap".(!empty($prefix) ? $i + 1 : $i + 2).".xml</loc>". PHP_EOL;
			$xml .= "\t<lastmod>" . str_replace(' ', 'T', date('Y-m-d H:i:s')) . "+00:00</lastmod>". PHP_EOL;
			$xml .= "</sitemap>". PHP_EOL;
		}

		return $xml;
	}

	function _build_sitemap_items($type = 'google', $data = false)
	{
		$xml = '';

		foreach ( $this -> languages as $k => $lang )
		{                                 			
			foreach ( $data as $key => $value )
			{
				$this -> add_lang_code( $value['url'], $lang['Code'] );
				$xml .= $this -> _build_item( $type, $value );
			}
		}

		unset( $data );

		return $xml;
	}

	function _build_item( $type, $row )
	{
		switch( $type )
		{
			case 'google' :
				$xml = "\t<url>". PHP_EOL;
				$xml .= "\t\t<loc>".$row['url']."</loc>". PHP_EOL;

				if ( $row['Modified'] && $row['Modified'] != '0000-00-00 00:00:00' )
				{
					$xml .= "\t\t<lastmod>".str_replace(' ', 'T', $row['Modified'])."+00:00</lastmod>". PHP_EOL;
				}

				$xml .= "\t\t<changefreq>weekly</changefreq>". PHP_EOL;
				$xml .= "\t\t<priority>0.6</priority>". PHP_EOL;
				$xml .= "\t</url>". PHP_EOL;

				break;
			case 'yahoo' :
                $xml = $row['url'];

				break;
			case 'urllist' :
				$xml = $row['url'];

				break;
		}

		return $xml;
	}

	function add_lang_code( &$url, $lang = false ) 
	{
		if ( !empty( $lang ) )
		{
  			$url = $GLOBALS['config']['lang'] != $lang ? str_replace( "[lang]", $lang, $url ) : str_replace( "[lang]/", '', $url );
		}
	}
}